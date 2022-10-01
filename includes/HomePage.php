<?php

use extend\UserConf;
use lib\AppStore\AppList;
use lib\Hook\Hook;
use lib\supply\Order;
use lib\supply\Price;
use Medoo\DB\SQL;

/**
 * Class HomePage
 * 商城主页部分功能整合
 */
class HomePage
{
    /**
     * @var array
     * 不显示商品列表
     */
    public static $Hide = false;

    public static $User = false;

    /**
     * @param $DataRe
     * 猜你喜欢~
     */
    public static function GuessYouLike($DataRe)
    {
        test(['gid|e'], '请将需要排除的商品ID提交完整！');
        self::load();
        self::$Hide[] = $DataRe['gid'];
        return self::GoodsList($DataRe);
    }

    /**
     * 初始化加载
     */
    public static function load()
    {
        if (!self::$Hide || !self::$User) {
            self::$Hide = UserConf::GoodsHide();
            if (!self::$Hide) {
                self::$Hide = [];
            }
            self::$User = login_data::user_data();
            return true;
        }
        return false;
    }

    /**
     * 限价秒杀活动商品列表
     * 展示所有未结束的活动商品！
     */
    public static function ActivitiesGoods()
    {
        global $date;
        $DB = SQL::DB();
        $GoodsList = $DB->select('seckill', '*', [
            'end_time[>]' => $date,
        ]);

        if (!$GoodsList || count($GoodsList) === 0) {
            dies(-2, '无活动商品！');
        }

        $Data = [];
        foreach ($GoodsList as $key => $value) {
            $Data[] = $value['gid'];
        }
        $Data = self::GoodsList([
            'show' => $Data,
            'page' => -1,
            'Seckill' => $GoodsList
        ]);
        if (count($Data['data']) === 0) {
            dies(-2, '无活动商品！');
        }
        dier($Data);
    }

    /**
     * @param $Data
     * 取出商品列表
     */
    public static function GoodsList($DataRe)
    {
        global $conf, $date;
        self::load();
        test(['page|e'], '请将参数提交完整！', $DataRe);

        if (!self::$User || empty(self::$User['grade'])) {
            $grade = 1;
        } else {
            $grade = (int)self::$User['grade'];
        }

        Hook::execute('GoodsList', [(self::$User === false ? -1 : self::$User['id']), 'data' => $DataRe]);
        $DB = SQL::DB();
        /**
         * 初始化，分页+类型选择
         * cid：分类ID
         * page:页码
         * SortingType：排序参数名称
         * Sorted：1|2
         * name：要搜索的商品名称
         */

        $SQL = [
            'goods.state' => 1,
            'ORDER' => [
                'goods.' . ($conf['SortingRules'] ?? 'sort') => ((int)$DataRe['Sorted'] === 2 ? 'ASC' : 'DESC')
            ]
        ];

        if (isset($DataRe['page']) && (int)$DataRe['page'] >= 1) {
            $LIMIT = (empty($conf['HomeLimit']) ? 12 : $conf['HomeLimit']) - 0;
            $Page = ($DataRe['page'] - 1) * $LIMIT;
            $SQL['LIMIT'] = [$Page, $LIMIT];
        }

        if (isset($DataRe['cid']) && (int)$DataRe['cid'] === -1) {
            unset($DataRe['cid']);
        }

        /**
         * 多功能排序
         * 综合排序 sort
         * 根据价格排序 money
         * 根据库存 quota
         * 类型 cid
         */

        if (isset($DataRe['SortingType'])) {
            switch ((int)$DataRe['SortingType']) {
                case 2: //价格排序 money
                    $SQL['ORDER'] = [
                        'money' => ((int)$DataRe['Sorted'] === 2 ? 'ASC' : 'DESC')
                    ];
                    break;
                case 3: //根据库存 quota
                    $SQL['ORDER'] = [
                        'quota' => ((int)$DataRe['Sorted'] === 2 ? 'ASC' : 'DESC')
                    ];
                    break;
                case 4: //类型 cid
                    $SQL['ORDER'] = [
                        'goods.cid' => ((int)$DataRe['Sorted'] === 2 ? 'ASC' : 'DESC')
                    ];
                    break;
            }
        }

        /**
         * 根据分类或直接获取全部
         */
        if (isset($DataRe['cid']) && !empty($DataRe['cid'])) {
            $SQL['goods.cid'] = $DataRe['cid'];
            $SQL['ORDER'] = [
                'goods.sort' => ((int)$DataRe['Sorted'] === 2 ? 'ASC' : 'DESC')
            ];
        }

        /**
         * 商品名称搜索
         */
        if (isset($DataRe['name']) && !empty($DataRe['name'])) {
            $SQL['goods.name[~]'] = $DataRe['name'];
        }

        /**
         * 隐藏指定
         */
        if (count(self::$Hide) >= 1) {
            $SQL['gid[!]'] = self::$Hide;
        }

        if (isset($DataRe['show']) && count($DataRe['show']) >= 1) {
            $SQL['gid'] = $DataRe['show'];
        }

        /**
         * 验证分类状态
         */
        $SQL['class.grade[<=]'] = $grade;
        $SQL['class.state'] = 1;

        $Res = $DB->select('goods', [
            '[>]class' => ['cid' => 'cid'],
        ], [
            'goods.gid', 'goods.cid', 'goods.name', 'goods.image', 'money', 'profits', 'freight', 'method',
            'quantity', 'quota', 'deliver', 'label', 'units', 'sales', 'selling', 'accuracy'
        ], $SQL);

        if (!$Res) {
            $Res = [];
        }
        $Data = [];
        foreach ($Res as $res) {
            $Price = Price::Get($res['money'], $res['profits'], (self::$User === false ? -1 : self::$User['grade']), $res['gid'], $res['selling']);
            $res['quota'] -= 0;
            $res['level'] = $Price['name'];

            $Image = json_decode($res['image'], true);
            if (!$Image) {
                $res['image'] = ImageUrl($res['image']);
            } else {
                $res['image'] = ImageUrl($Image[0]);
            }
            $res['price'] = round($Price['price'], $res['accuracy']);
            $res['points'] = round($Price['points'], 0);
            if ((int)$res['deliver'] === 3) {
                $res['quota'] = $DB->count('token', [
                    "AND" => [
                        "uid" => 1,
                        "gid" => $res['gid']
                    ],
                ]);
            }

            unset($res['profits'], $res['money'], $res['deliver'], $res['selling'], $Image);
            $res['method'] = PaymentMethodAnalysis(json_decode($res['method']), true);
            $res['sales'] = CommoditySalesAnalysis($res['gid'], $res['sales']);
            $res['label'] = LabelaAnalysis($res['label']);

            $res['currency'] = $conf['currency'];
            if (empty($res['units'])) {
                $res['units'] = '个';
            }


            if (isset($DataRe['show']) && count($DataRe['show']) >= 1) {
                /**
                 * 匹配优惠数据，并且载入
                 */
                foreach ($DataRe['Seckill'] as $k => $v) {
                    if ($DataRe['Seckill'][$k]['gid'] === $res['gid']) {
                        unset($DataRe['Seckill'][$k]);
                        $res['Seckill'] = SeckillAnalysis($v);
                    }
                }
            }
            $Data[] = $res;
        }
        unset($SQL['LIMIT']);
        $Count = $DB->count('goods', ['[>]class' => ['cid' => 'cid']], 'goods.gid', $SQL);

        $Cis = [];
        if (isset($DataRe['cid']) && (int)$DataRe['cid'] >= 1) {
            $Cis = $DB->get('class', [
                'cid', 'name', 'image', 'content',
            ], ['cid' => (int)$DataRe['cid'], 'state' => 1]);
            if (!$Cis) {
                dies(-2, '当前所选分类不存在！');
            }
        }

        $Seckill = $DB->count('seckill', '*', [
                'end_time[>]' => $date,
            ]) - 0;

        return [
            'code' => 1,
            'msg' => '商品列表获取成功',
            'data' => $Data,
            'Currency' => $conf['currency'], //货币名称
            'CidArr' => (isset($DataRe['cid']) && (int)$DataRe['cid'] >= 1 ? $Cis : []),
            'Seckill' => $Seckill, //秒杀活动
            'tips' => '共找到' . $Count . '个' . (!empty($DataRe['name']) ? '带有[' . $DataRe['name'] . ']关键词的' : '') . '商品！',
        ];
    }

    /**
     * @param $Data
     * @return array
     * 获取商品详情
     */
    public static function ProductDetails($Data)
    {
        global $conf, $date, $accredit;
        self::load();

        if (in_array($Data['gid'], self::$Hide)) {
            return [
                'code' => -1,
                'msg' => '商品已下架!',
            ];
        }

        $DB = SQL::DB();
        $Res = $DB->get('goods', '*', [
            'gid' => (int)$Data['gid'],
            'state' => 1
        ]);

        if (!$Res) {
            return [
                'code' => -1,
                'msg' => '当前商品已下架或根本不存在!',
            ];
        }

        $ImageArr = [];
        $ImageData = json_decode($Res['image'], true);
        if (!$ImageData) {
            $ImageArr[] = ImageUrl($Res['image']);
        } else {
            foreach ($ImageData as $v) {
                $ImageArr[] = ImageUrl($v);
            }
        }
        unset($ImageData);

        if ($Res['min'] === null || $Res['min'] === '') {
            $Res['min'] = 1;
        }
        if ($Res['max'] === null || $Res['max'] === '') {
            $Res['max'] = 10000;
        }

        /*
         * 商品运费模板
         */
        if ((int)$Res['freight'] !== -1) {
            $freight = $DB->get('freight', '*', [
                'id' => (int)$Res['freight']
            ]);
            if ($freight) {
                $Res['freight'] = $freight;
            } else {
                $Res['freight'] = false;
            }
        }

        $Input = []; //state = 1,普通输入框，2 = 接口输入框，3 = 收货地址输入框，4 = 下拉多选框 5 = 规格选择框

        if ((int)$Res['specification'] === 2) {
            $SpRule = RlueAnalysis($Res);
            $Input = [[
                'state' => 5,
                'data' => $SpRule,
                'SPU' => json_decode($Res['specification_spu'], true),
            ]];
        }

        if (!empty($Res['input'])) {
            foreach (explode('|', $Res['input']) as $value) {
                if (strpos($value, '{') !== false && strpos($value, '}') !== false) {
                    $value = explode('{', $value);
                    $value[1] = explode(',', getSubstr('{' . $value[1], '{', '}'));
                    $Input[] = [
                        'state' => 4,
                        'Data' => $value
                    ];
                } else {
                    $S = AppList::MatchingInput($value);
                    if ($S === false) {
                        $Input[] = [
                            'state' => 1,
                            'Data' => (empty($value) ? '备注信息' : $value)
                        ];
                    } else if ((int)$S['type'] === 1) {
                        $Input[] = [
                            'state' => 3,
                            'Data' => $value,
                        ];
                    } else if ((int)$S['type'] === 2) {
                        $Input[] = [
                            'state' => 6,
                            'Data' => [
                                $value,
                                $S
                            ],
                        ];
                    } else if ((int)$S['type'] === -1) {
                        $Input[] = [
                            'state' => 2,
                            'Data' => [
                                $value,
                                $S
                            ],
                        ];
                    } else {
                        $Input[] = [
                            'state' => 1,
                            'Data' => (empty($value) ? '备注信息' : $value)
                        ];
                    }
                }
            }
        }

        $Res['cart_count'] = extend\GoodsCart::CartCount();
        $Res['method'] = json_decode($Res['method'], true);

        /**
         * 验证是否支持选择多份
         */
        if (in_array(7, $Res['method'])) {
            $RS = 1;
        } else {
            $RS = -1;
        }

        $Res['method'] = PaymentMethodAnalysis($Res['method']);

        $SQL = [
            'cid' => $Res['cid'],
            'state' => 1,
            'ORDER' => [
                'sort' => 'DESC'
            ],
            'LIMIT' => 12,
        ];

        self::$Hide[] = $Res['gid'];
        if (count(self::$Hide) >= 1) {
            $SQL = array_merge($SQL, [
                'gid[!]' => self::$Hide
            ]);
        }

        $Push = [];
        if ((int)$conf['SimilarRecommend'] === 1) {
            $PushArr = $DB->select('goods', [
                'image', 'gid', 'name'
            ], $SQL);
            foreach ($PushArr as $val) {
                $Image = json_decode($val['image'], true);
                if (!$Image) {
                    $val['image'] = ImageUrl($val['image']);
                } else {
                    $val['image'] = ImageUrl($Image[0]);
                }
                $Push[] = $val;
                unset($Image);
            }
        }

        /**
         * 同步卡密库存
         */
        if ((int)$Res['deliver'] === 3) {
            $Res['quota'] = $DB->count('token', [
                "AND" => [
                    "uid" => 1,
                    "gid" => $Res['gid']
                ],
            ]);
        }

        $Res['label'] = LabelaAnalysis($Res['label']);
        $Price = Price::Get($Res['money'], $Res['profits'], (self::$User === false ? -1 : self::$User['grade']), $Res['gid'], $Res['selling']);
        if ((int)$conf['LevelDisplay'] === 1) {
            $PriceList = Price::List($Res['money'], $Res['profits'], $Res['gid'], $Res['selling']);
        } else {
            $PriceList[] = $Price;
        }

        if (count($Input) === 0) {
            $Input[] = [
                'state' => 1,
                'Data' => '下单信息'
            ];
        }

        /**
         * 同步限价秒杀活动
         * SeckillAnalysis
         */
        $Seckill = $DB->get('seckill', '*', [
            'end_time[>]' => $date,
            'gid' => (int)$Res['gid']
        ]);
        if ($Seckill) {
            $Seckill = SeckillAnalysis($Seckill);
        } else {
            $Seckill = -1;
        }

        if (is_file(ROOT . 'includes/extend/log/PriceChangeGoods/' . md5($accredit['token']) . '_' . $Res['gid'] . '.log')) {
            $Log = true;
        } else {
            $Log = false;
        }

        if ($Res['min'] <= 1) {
            $Res['min'] = 1;
        }
        if ($Res['max'] <= 1) {
            $Res['max'] = 1;
        }

        $GoodsType = 1;
        switch ((int)$Res['deliver']) {
            case 1:
                $GoodsType = 1;
                break;
            case 2:
            case 4:
                $GoodsType = 3;
                break;
            case 3:
                $GoodsType = 2;
                break;
        }

        $Data = [
            'cid' => (int)$Res['cid'],
            'gid' => (int)$Res['gid'],
            'name' => $Res['name'],
            'image' => $ImageArr, //商品图片
            'method' => $Res['method'], //支持的付款方式
            'state' => $Res['state'], //商品状态
            'input' => $Input, //下单编辑框
            'quantity' => (int)$Res['quantity'], //每份数量
            'docs' => htmlspecialchars_decode($Res['docs']),
            'alert' => htmlspecialchars_decode($Res['alert']),
            'min' => (int)$Res['min'], //最低购买份数
            'max' => (int)$Res['max'], //最多购买份数
            'currency' => $conf['currency'], //积分名称
            'price' => round($Price['price'], $Res['accuracy']), //售价
            'points' => round($Price['points'], 0), //积分
            'level' => $Price['name'], //当前等级
            'level_arr' => $PriceList, //等级列表
            'freight' => $Res['freight'], //运费模板
            'cart_count' => $Res['cart_count'], //购物车商品数量
            'sales' => CommoditySalesAnalysis($Res['gid'], $Res['sales']), //商品销量
            'quota' => (int)$Res['quota'], //剩余库存
            'Push' => $Push, //推送商品
            'CartState' => $conf['CartState'], //购物车开关
            'label' => $Res['label'], //标签
            'User' => (self::$User === false && $conf['ForcedLanding'] != 1 ? -1 : 1), //是否开启强制登陆
            'repetition' => $RS, //是否允许一次购买多份
            'units' => (empty($Res['units']) ? '个' : $Res['units']),
            'accuracy' => $Res['accuracy'],
            'Seckill' => $Seckill, //限购秒杀
            'PriceLog' => $Log, //价格波动
            'GoodsType' => $GoodsType, //商品类型 1自营，2发卡，3对接
            'UserState' => (self::$User === false ? -1 : [
                'name' => (empty(self::$User['name']) ? '平台用户' : self::$User['name']),
                'image' => UserImage(self::$User),
                'money' => round(self::$User['money'], 8),
                'currency' => self::$User['currency'],
            ]), //用户登陆状态，余额，头像等信息
        ];
        Hook::execute('GoodsDetails', [
            'gid' => (int)$Res['gid'],
            'uid' => (self::$User === false ? -1 : self::$User['id']),
            'data' => $Data
        ]);
        return [
            'code' => 1,
            'msg' => '商品详情获取成功',
            'data' => $Data
        ];
    }

    /**
     * @param $Data
     * 验证商品订单
     */
    public static function CreateOrder($Data)
    {
        global $conf, $times;
        self::load();
        if (self::$User === false && (int)$conf['ForcedLanding'] != 1) {
            dies(-3, '您必须先登陆用户中心才可购买商品，点击确定登陆！');
        }
        $Gid = (int)$Data['gid'];
        $Num = (int)$Data['num'];
        unset($Data['gid'], $Data['num'], $Data['act']);
        $Goods = Order::VerifyBuy($Gid, $Num, $Data, self::$User);
        $Data = $Goods['InputData'];
        $DB = SQL::DB();
        if ((int)$Goods['freight'] !== -1) {
            $Fre = $DB->get('freight', '*', [
                'id' => (int)$Goods['freight'],
                'LIMIT' => 1,
            ]);
            if ($Fre) {
                $price = Price::Freight($Fre, $Data, $Goods['price'], $Goods['num']);
            } else {
                $price = $Goods['price'] * $Goods['num'];
            }
        } else {
            $price = $Goods['price'] * $Goods['num'];
        }

        $Goods['method'] = PaymentMethodAnalysis(json_decode($Goods['method'], true));
        $Input = [];
        $InputArr = [];
        foreach (explode('|', $Goods['input']) as $key => $value) {
            if (strstr($value, '{') && strstr($value, '}')) {
                $Ex = explode('{', $value);
                $Input[] = $Ex[0];
                $InputArr[$key] = [
                    'type' => 888,
                    'data' => explode(',', explode('}', $Ex[1])[0]),
                ];
            } else {
                $Input[] = $value;
                $InputArr[$key] = AppList::MatchingInput(rtrim($value, '：'));
            }
        }

        foreach ($InputArr as $key => $value) {
            if (!$value) {
                continue;
            }

            //必需是单输入框内容替换+请求接口
            if ($value['type'] == -1 && $value['way'] == 1) {
                //请求接口验证
                $Get = get_curl($value['url'], [
                    'value' => $Data[$key],
                ]);
                if ($Get) {
                    $Json = json_decode($Get, true);
                    if ($Json['code'] >= 1 && !empty($Json['value'])) {
                        //替换内容
                        $Data[$key] = $Json['value'];
                    }
                }
            }

            //下拉选择框参数验证
            if ($value['type'] == 888) {
                if (!in_array($Data[$key], $value['data'])) {
                    dies(-1, '输入内容有误,请在(' . implode(',', $value['data']) . ')参数内选择一个当做下单信息来进行提交！');
                }
            }
        }

        if ((int)$Goods['specification'] === 2) {
            $SpRule = RlueAnalysis($Goods, 3);
            if ($SpRule !== -1) {
                $Input = array_merge($SpRule['MasterRule'], $Input);
            }
        }

        $points = round($Goods['points'] * $Goods['num']);

        if (!self::$User) {
            $price = round($price, $Goods['accuracy']);
            $Coupon = [];
            $CouponOff = [];
        } else {
            $CouponList = $DB->select('coupon', '*', [
                'OR' => [
                    'apply' => 3,
                    'gid' => $Goods['gid'],
                    'cid' => $Goods['cid'],
                ],
                'oid' => -1,
                'uid' => self::$User['id'],
            ]);
            $Coupon = [];
            $CouponOff = [];
            if (!$CouponList) {
                $CouponList = [];
            }
            foreach ($CouponList as $v) {
                $Types = 1;
                switch ($v['apply']) {
                    case 1:
                        if ($Goods['gid'] != $v['gid']) {
                            $Types = 2;
                        }
                        break;
                    case 2:
                        if ($Goods['cid'] != $v['cid']) {
                            $Types = 2;
                        }
                        break;
                }

                if ($v['term_type'] == 1) {
                    $TIME = strtotime($v['gettime']) + (60 * 60 * 24 * $v['indate']);
                } else {
                    $TIME = strtotime($v['expirydate']);
                }

                if (time() > $TIME) {
                    $Types = 2;
                }

                if ($Types == 2) {
                    continue;
                }

                switch ($v['type']) {
                    case 1:
                        if ($v['minimum'] > $price) {
                            $Types = 2;
                            $explain = '订单付款金额需满' . $v['minimum'] . '元才可使用，满' . $v['minimum'] . '元可优惠' . $v['money'] . '元！';
                        } else $explain = '满' . $v['minimum'] . '元可优惠' . $v['money'] . '元！';
                        $PriceCou = $price - $v['money'];
                        $Money = '减免' . $v['money'] . '元';
                        break;
                    case 2:
                        $explain = '下单即可优惠' . $v['money'] . '元！';
                        $PriceCou = $price - $v['money'];
                        $Money = '减免' . $v['money'] . '元';
                        break;
                    case 3:
                        if ($v['minimum'] > $price) {
                            $Types = 2;
                            $explain = '订单付款金额需满' . $v['minimum'] . '元才可使用，满' . $v['minimum'] . '元可享' . $v['money'] / 10 . '折优惠！';
                        } else $explain = '满' . $v['minimum'] . '元可享' . $v['money'] / 10 . '折优惠,约减免' . round($price - $PriceCou, 2) . '元！';
                        $PriceCou = ($price * ($v['money'] / 100));
                        $Money = ($v['money'] / 10) . '折购';
                        break;
                }

                $CSQL = [
                    'uid' => self::$User['id'],
                    'oid[!]' => -1,
                    'endtime[>]' => $times
                ];
                if ($conf['CouponUseIpType'] == 1) {
                    $CSQL = [
                        'OR' => [
                            'uid' => self::$User['id'],
                            'ip' => userip(),
                        ],
                        'oid[!]' => -1,
                        'endtime[>]' => $times
                    ];
                }
                $Count = $DB->count('coupon', $CSQL);
                if ($Count >= $conf['CouponUsableMax']) {
                    $explain = '每天最多可使用' . $conf['CouponUsableMax'] . '张优惠券,今日已经使用了' . $Count . '张！';
                    $Types = 2;
                }

                if ($Types == 2) {
                    $CouponOff[] = [
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'content' => $v['content'],
                        'explain' => $explain,
                        'ExpirationDate' => date("Y-m-d H:i:s", $TIME),
                        'Time' => Sec2Time($TIME - time()),
                        'Money' => $Money,
                        'type' => $v['type'],
                        'minimum' => $v['minimum'],
                        'derate' => $v['money'],
                    ];
                } else {
                    if ($conf['CouponMinimumType'] == 1) {
                        if ($PriceCou <= ($Goods['money'] * $Goods['num'])) {
                            $PriceCou = ($Goods['money'] * $Goods['num']);
                        }
                    } else {
                        if ($PriceCou <= 0) {
                            $PriceCou = 0;
                        }
                    }
                    $Coupon[] = [
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'content' => $v['content'],
                        'explain' => $explain,
                        'ExpirationTime' => date("Y-m-d H:i:s", $TIME),
                        'ExpirationDate' => Sec2Time($TIME - time()),
                        'Price' => round($PriceCou, 2),
                        'Money' => $Money,
                        'type' => $v['type'],
                        'minimum' => $v['minimum'],
                        'derate' => $v['money'],
                    ];
                }
            }

            $price = round($price, $Goods['accuracy']);
        }

        if (count($Coupon) >= 2) {
            $Coupon = array_sort($Coupon, 'Price');
        } //给出最佳优惠

        /**
         * 返回，商品价格，商品名称，下单信息，下单填写框，商品参数,购买数量，可用优惠券+不可用优惠券
         */

        return [
            'code' => 1,
            'msg' => '商品订单信息获取成功',
            'data' => [
                'gid' => $Gid,
                'num' => $Goods['num'],
                'name' => $Goods['name'],
                'quantity' => $Goods['quantity'] * $Goods['num'],
                'data' => $Data,
                'price' => $price,
                'points' => round($points, 0),
                'type' => 1,
                'method' => $Goods['method'],
                'currency' => $conf['currency'],
                'input' => $Input,
                'units' => $Goods['units'],
                'Seckill' => $Goods['Seckill'],
            ],
            'Coupon' => $Coupon,
            'CouponOff' => $CouponOff,
        ];
    }

    /**
     * @param $Gid
     * 取出商品价格波动，单商品
     */
    public static function ChangesCommodityPricesGoodsList($Gid)
    {
        global $accredit;
        $KeyName = md5($accredit['token']) . '_';
        mkdirs(ROOT . 'includes/extend/log/PriceChangeGoods/');
        if (is_file(ROOT . 'includes/extend/log/PriceChangeGoods/' . $KeyName . $Gid . '.log')) {
            $file = ROOT . 'includes/extend/log/PriceChangeGoods/' . $KeyName . $Gid . '.log';
        } else {
            dies(-2, '空空如也');
        }

        $PriceChangeData = json_decode(file_get_contents($file), true);
        if (empty($PriceChangeData)) {
            $PriceChangeData = [];
        }

        $User = login_data::user_data();
        $Leve = (!$User ? 1 : (int)$User['grade']);

        $DB = SQL::DB();
        $Goods = $DB->get('goods', [
            'profits', 'selling', 'gid', 'image', 'state'
        ], [
            'gid' => (int)$Gid,
        ]);
        if (!$Goods) {
            dies(-2, '商品不存在！');
        }
        $Data = [];
        $NameLevel = '平台用户';

        $Image = json_decode($Goods['image'], true);
        if (!$Image) {
            $Goods['image'] = ImageUrl($Goods['image']);
        } else {
            $Goods['image'] = ImageUrl($Image[0]);
        }

        foreach ($PriceChangeData as $val) {
            $NewPrice = Price::Get($val['money'], $Goods['profits'], $Leve, $Goods['gid'], $Goods['selling']);
            $UsedPrice = Price::Get($val['OriginalPrice'], $Goods['profits'], $Leve, $Goods['gid'], $Goods['selling']);
            if ($NewPrice['price'] === $UsedPrice['price']) {
                continue;
            }
            $NameLevel = $NewPrice['name'];
            if ((int)$val['type'] === 2) {
                //跌
                $Percentage = round(($NewPrice['price'] - $UsedPrice['price']) / $NewPrice['price'] * 100, 3) . '%';
            } else {
                //涨
                $Percentage = round(($NewPrice['price'] - $UsedPrice['price']) / $UsedPrice['price'] * 100, 3) . '%';
            }

            $Data[] = [
                'Gid' => $val['gid'],
                'Name' => $val['name'],
                'image' => $Goods['image'],
                'NewPrice' => $NewPrice['price'], //变动后的价格
                'UsedPrice' => $UsedPrice['price'], //变动前的价格
                'Percentage' => $Percentage, //涨幅，跌幅百分比
                'key' => $val['key'], //规格组合
                'state' => $Goods['state'],
                'type' => $val['type'], //1涨，2跌
                'date' => $val['date'],
            ];
        }

        if (count($Data) === 0) {
            dies(-2, '没有任何价格波动日志');
        }

        dier([
            'code' => 1,
            'msg' => '商品价格波动数据获取成功！',
            'data' => array_reverse($Data),
            'GradeName' => $NameLevel,
        ]);
    }

    /**
     * @param $Name
     * 取出价格波动列表
     */
    public static function ChangesCommodityPricesList($Name)
    {
        global $accredit;
        $KeyName = md5($accredit['token']) . '_';
        mkdirs(ROOT . 'includes/extend/log/PriceChange/');
        if (empty($Name) || !is_file(ROOT . 'includes/extend/log/PriceChange/' . $KeyName . $Name . '.log')) {
            $file = ROOT . 'includes/extend/log/PriceChange/' . $KeyName . date("Ymd") . '.log';
            $Name = date("Ymd");
        } else {
            $file = ROOT . 'includes/extend/log/PriceChange/' . $KeyName . $Name . '.log';
        }

        $PriceChangeData = json_decode(file_get_contents($file), true);
        if (empty($PriceChangeData)) {
            $PriceChangeData = [];
        }

        $GoodsListArr = [];
        foreach ($PriceChangeData as $val) {
            if (in_array((int)$val['gid'], $GoodsListArr, true)) {
                continue;
            }
            $GoodsListArr[] = (int)$val['gid'];
        }

        $User = login_data::user_data();
        $Leve = (!$User ? 1 : (int)$User['grade']);

        $DB = SQL::DB();
        $GoodsList = $DB->select('goods', [
            'profits', 'selling', 'gid', 'image', 'state'
        ], [
            'gid' => $GoodsListArr,
        ]);
        $Data = [];

        $NameLevel = '平台用户';

        if ($GoodsList && count($GoodsList) >= 1) {
            $GoodsListArr = [];
            foreach ($GoodsList as $val) {
                $Image = json_decode($val['image'], true);
                if (!$Image) {
                    $val['image'] = ImageUrl($val['image']);
                } else {
                    $val['image'] = ImageUrl($Image[0]);
                }
                unset($Image);
                $GoodsListArr[$val['gid']] = $val;
            }
            foreach ($PriceChangeData as $val) {
                $Goods = $GoodsListArr[$val['gid']];
                if (empty($Goods)) {
                    continue;
                }

                $NewPrice = Price::Get($val['money'], $Goods['profits'], $Leve, $Goods['gid'], $Goods['selling']);
                $UsedPrice = Price::Get($val['OriginalPrice'], $Goods['profits'], $Leve, $Goods['gid'], $Goods['selling']);

                if ($NewPrice['price'] === $UsedPrice['price']) {
                    continue;
                }

                $NameLevel = $NewPrice['name'];

                if ((int)$val['type'] === 2) {
                    //跌
                    $Percentage = round(($NewPrice['price'] - $UsedPrice['price']) / $NewPrice['price'] * 100, 3) . '%';
                } else {
                    //涨
                    $Percentage = round(($NewPrice['price'] - $UsedPrice['price']) / $UsedPrice['price'] * 100, 3) . '%';
                }

                $Data[] = [
                    'Gid' => $val['gid'],
                    'Name' => $val['name'],
                    'image' => $Goods['image'],
                    'NewPrice' => $NewPrice['price'], //变动后的价格
                    'UsedPrice' => $UsedPrice['price'], //变动前的价格
                    'Percentage' => $Percentage, //涨幅，跌幅百分比
                    'key' => $val['key'], //规格组合
                    'state' => $Goods['state'],
                    'type' => $val['type'], //1涨，2跌
                    'date' => $val['date'],
                ];
            }
        }

        $ListArr = scandir(ROOT . 'includes/extend/log/PriceChange/');
        $List = [];
        foreach ($ListArr as $v) {
            if (empty($v) || $v === '.' || $v === '..') {
                continue;
            }
            $matches = [];
            preg_match_all("/_(.+)\./i", $v, $matches);
            $List[] = $matches[1][0];
        }
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'List' => array_reverse($List),
                'Data' => array_reverse($Data),
            ],
            'GradeName' => $NameLevel,
            'ListName' => $Name,
        ]);
    }

    /**
     * @param $User
     * 取出用户详情,默认模板专用
     */
    public static function UserData($User)
    {
        global $conf, $times;
        $DB = SQL::DB();
        $Giv = RatingParameters($User);
        if ($Giv === -1) {
            $Giv = [
                'name' => '平台用户',
                'mid' => $User['grade'],
            ];
        } else {
            $Giv = [
                'name' => $Giv['name'],
                'mid' => $Giv['sort'],
            ];
        }
        /**
         * 签到验证
         */
        $Vs = $DB->get('journal', ['id'], [
            'name' => '每日签到',
            'uid' => (int)$User['id'],
            'date[>=]' => $times
        ]);
        $List_1 = [];
        $List_1[] = [
            'name' => '会员中心', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/a3.png', 'path' => 'pages/user/grade/grade', 'type' => 1,
            'data' => ['type' => 'info', 'text' => 'Lv' . $Giv['mid'], 'state' => true]
        ];
        $List_1[] = [
            'name' => '我的钱包', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/a4.png', 'path' => 'pages/user/pay/pay', 'type' => 1,
            'data' => ['type' => 'warning', 'text' => round($User['money'], 2) . '元', 'state' => true]
        ];
        $List_1[] = [
            'name' => '每日签到',
            'icon' => href(2) . ROOT_DIR . 'assets/img/user/a1.png', //图标地址(完整链接)
            'path' => 'pages/user/sign/sign', //跳转地址
            'type' => 1, //1跳转路由，2链接跳转(新窗口)，3打开内置窗口，4退出登录
            'data' => [
                'type' => ($Vs ? 'success' : 'error'),
                'text' => ($Vs ? '已签到' : '未签到'),
                'state' => true,
            ]
        ];

        if ($conf['userleague'] == 1) {
            $List_1[] = [
                'name' => '我的小店', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/a2.png', 'path' => 'pages/user/shop/shop', 'type' => 1,
                'data' => ['type' => ($User['grade'] < $conf['userleaguegrade'] ? 'error' : 'success'), 'text' => ($User['grade'] < $conf['userleaguegrade'] ? '未开通' : '已开通'), 'state' => true]
            ];
        }
        if ($conf['userleague'] == 1 && $User['grade'] >= $conf['userleaguegrade']) {
            $AppCount = $DB->count('app', ['uid' => $User['id']]);
            $List_1[] = [
                'name' => '我的App', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/a5.png', 'path' => 'pages/user/App/App', 'type' => 1,
                'data' => ['type' => ($AppCount >= 1 ? 'success' : 'warning'), 'text' => ($AppCount >= 1 ? ' 已生成' : '未生成'), 'state' => true]
            ];
        } else if (!empty($conf['appurl'])) {
            $List_1[] = [
                'name' => '软件下载', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/a5.png', 'path' => $conf['appurl'], 'type' => 3,
                'data' => ['type' => 'error', 'text' => '客户端', 'state' => true]
            ];
        }

        $List_1[] = [
            'name' => '优惠卡券', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/a6.png', 'path' => 'pages/user/coupon/coupon', 'type' => 1,
            'data' => ['type' => 'warning', 'text' => '', 'state' => false]
        ];
        $List_2 = [];

        $inviteCount = $DB->sum('invite', 'award', [
            'uid' => $User['id'],
            'award[>]' => 0,
        ]);
        $List_2[] = [
            'name' => '邀请有奖', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/b4.png', 'path' => 'pages/user/invite/invite', 'type' => 1,
            'data' => ['type' => 'success', 'text' => '可领取:' . $inviteCount . $conf['currency'], 'state' => ($inviteCount > 0 ? true : false)]
        ];

        $Count = $DB->count('mainframe', [
                'uid' => $User['id']
            ]) - 0;

        if ($Count >= 1) {
            $List_2[] = [
                'name' => '主机管理', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/b8.png', 'path' => 'pages/user/HostManagement/HostManagement', 'type' => 1,
                'data' => ['type' => 'success', 'text' => '共' . $Count . '台', 'state' => true]
            ];
        }
        $ticketsCount = $DB->count('tickets', [
            'uid' => $User['id'],
            'state' => 2
        ]);
        $List_2[] = [
            'name' => '我的工单', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/b2.png', 'path' => 'pages/user/ticket/ticket', 'type' => 1,
            'data' => ['type' => 'primary', 'text' => $ticketsCount . '单未结', 'state' => ($ticketsCount >= 1)]
        ];
        $List_2[] = [
            'name' => '我的点评', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/b3.png', 'path' => 'pages/user/evaluate/evaluate', 'type' => 1,
            'data' => ['type' => 'primary', 'text' => '', 'state' => false]
        ];
        $List_2[] = [
            'name' => '收支明细', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/b1.png', 'path' => 'pages/user/journal/journal', 'type' => 1,
            'data' => ['type' => 'warning', 'text' => '', 'state' => false]
        ];
        $List_2[] = [
            'name' => '站内通知', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/b5.png', 'path' => 'pages/index/article', 'type' => 1,
            'data' => ['type' => 'success', 'text' => '', 'state' => false]
        ];
        $List_2[] = [
            'name' => '联系客服', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/b6.png', 'path' => 'pages/index/service', 'type' => 1,
            'data' => ['type' => 'success', 'text' => '', 'state' => false]
        ];
        $List_2[] = [
            'name' => '退出登录', 'icon' => href(2) . ROOT_DIR . 'assets/img/user/b7.png', 'path' => '', 'type' => 4,
            'data' => ['type' => 'error', 'text' => '', 'state' => false]
        ];

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'conf' => [ //控制开关
                'currency' => $conf['currency'],
            ],
            'order' => [ //订单配置
                'a1' => 0, //全部
                'a2' => $DB->count('order', ['uid' => $User['id'], 'state' => [2, 3, 4, 6], 'take' => 1]), //未完成
                'a3' => $DB->count('order', ['uid' => $User['id'], 'state' => 1, 'take' => 1]), //待收货
                'a4' => 0, //已完成
                'a5' => 0, //已取消
            ],
            'list' => [$List_1, $List_2],
            'data' => [
                'id' => $User['id'],
                'currency' => $User['currency'],
                'money' => $User['money'],
                'grade' => $Giv,
                'domain' => ($conf['userdomaintype'] == 1 ? $User['domain'] : href() . $User['domain']),
                'image' => UserImage($User),
                'name' => (empty($User['name']) ? '平台用户' : $User['name']),
                'qq' => $User['qq'],
                'mobile' => $User['mobile'],
                'addtime' => $User['found_date'],
                'uptime' => $User['recent_time'],
                'notice_user' => (empty($conf['notice_user']) ? false : $conf['notice_user']),
            ]
        ]);
    }
}
