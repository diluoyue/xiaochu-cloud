<?php

/**
 * Author：晴玖天
 * Creation：2020/4/22 13:31
 * Filename：xiaochu.php
 * 小储对接模块
 */

namespace lib\supply;


use extend\UserConf;
use Medoo\DB\SQL;

class xiaochu
{

    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '同系统对接',
        'image' => '../assets/img/logo.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => -1,
        'ip' => 1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '社区域名',
                'tips' => '请填写包含http(s):// 和 / 的域名地址!',
                'type' => 1,
            ],
            'username' => [
                'name' => '对接ID',
                'tips' => '请填写好你在后台获取的ID!',
                'type' => 1,
            ],
            'password' => [
                'name' => '对接KEY',
                'tips' => '请填写好你在后台获取的key!',
                'type' => 1,
            ],
            'secret' => [
                'name' => '支付方式',
                'tips' => [
                    '1' => '余额付款',
                    '2' => '积分付款',
                ],
                'type' => 2,
            ]
        ],
        'InputField' => [ //选择对接商品时的输入框字段
            'ProductList' => [
                'type' => 2,
                'name' => '可对接商品', //下拉列表名称
                'request' => [ //提交的是当前选择参数数据类的值 + 当前的货源社区ID加类型键值！
                    'gid' => false,
                    'type' => 1, //固定提交的参数
                    'controller' => 'GoodsArray', //请求的控制器名称
                ],
                'GetListData' => [ //获取列表时的请求数据，提交的是当前所选对接社区内的值+当前已选参数的值
                    'id' => false,
                    'type' => 1,
                    'controller' => 'GoodsListArr', //请求的控制器名称
                ],
                'RequestDataSources' => '-1', //点击列表参数请求替换源，data() { return (全部可用) }，-1为内置源GoodsData，其他为App[xxx]
                'GetListDataSources' => 'DockingData', //列表请求替换源，data() { return (全部可用) }
                'BtnName' => '获取数据', //获取列表时的按钮名称
                'button' => [ //辅助按钮
                    [
                        'name' => '获取最新数据',
                        'GetListDataSources' => 'DockingData', //请求替换源，data() { return (全部可用) }
                        'class' => 'badge badge-primary-lighten',
                        'request' => [
                            'id' => false,
                            'type' => 2,
                            'controller' => 'GoodsListArr', //请求的控制器名称
                        ],
                    ]
                ]
            ],
            'gid' => [
                'type' => 1,
                'name' => '商品ID',
                'reminder' => '请点击获取数据后，选择对应商品获取'
            ],
            'parameter' => [
                'type' => 1,
                'name' => '对接参数',
                'reminder' => '请点击获取数据后，选择对应商品获取'
            ]
        ]
    ];

    /**
     * @param $Data //用户提交的数据
     * @param $Source //货源详情
     * @param $DataSo //提交按钮详情
     */
    public static function AdminOrigin($Data, $Source = [], $DataSo = [])
    {
        switch ($Data['controller']) {
            case 'GoodsListArr': //获取可对接的商品列表
                return self::GoodsListArr($Data);
            case 'GoodsArray': //获取商品详情
                return self::GoodsArray($Data);
            case 'Submit': //对接下单
                return self::Submit($Data['Order'], $Data['Goods'], $Data['Supply']);
            case 'Query': //查询订单
                return self::Query($Data['Order']['order_id'], $Data['Supply']);
            case 'CommodityStatus': //商品状态详情
                return self::CommodityStatus($Data['Goods'], $Data['Supply']);
            case 'CommodityAnalysis': //解析商品配置信息
                return self::CommodityAnalysis($Data);
        }
        return [
            'code' => -1,
            'msg' => '请求错误',
        ];
    }

    /**
     * @param $User 用户信息
     * @param $Data 传递信息
     *
     */
    public static function QueryApi($User, $Data)
    {
        $Res = Order::Query($Data['order'], $User['id'], 3);
        $Res = $Res['data'];
        dier([
            'state' => 1,
            'msg' => '订单[' . $Data['order'] . ']数据获取成功',
            'data' => [
                'code' => ((int)$Res['ApiType'] == 1 ? $Res['ApiState'] : $Res['state']),
                'state' => ((int)$Res['ApiType'] == 1 ? $Res['ApiState'] : $Res['state']),
                'addtiem' => $Res['addtiem'],
                'num' => ((int)$Res['ApiType'] == 1 ? (int)$Res['ApiNum'] : (int)$Res['num']),
                'price' => (float)$Res['price'],
                'remark' => ($Res['remark'] == null ? $Res['ApiError'] : $Res['remark']),
                'input' => $Res['input'],
                'Initial' => ((int)$Res['ApiType'] == 1 ? $Res['ApiInitial'] : 0),
                'Present' => ((int)$Res['ApiType'] == 1 ? $Res['ApiPresent'] : 0),
                'Explain' => (!empty($Res['Explain']) ? $Res['Explain'] : -1),
                'StateNum' => $Res['stateid'],
            ]
        ]);
    }

    /**
     * @param $Data 传递数据
     * 同系统互交数据验证模块
     */
    public static function verify($Data)
    {
        global $accredit, $conf;
        if ($conf['ShutDownUserSystem'] == -1) {
            dies(-1, $conf['ShutDownUserSystemCause']);
        }
        if (empty($Data['sign']) || empty($Data['url']) || empty((int)$Data['id'])) {
            dies(-1, '对接数据缺失,请提交完整(sign,url,id)！');
        }
        if (strlen($Data['sign']) !== 32) {
            dies(-1, 'sign长度不正确,正常长度应该是32位,请重置您的token对接密钥再试试！');
        }
        $DB = SQL::DB();
        $User = $DB->get('user', '*', ['id' => $Data['id']]);
        if (!$User) {
            dies(-1, '用户验证失败,无此用户！');
        }
        if ($User['state'] <> 1) {
            dies(-1, '您的账户已被禁封！');
        }
        $ArrayIp = explode('|', $User['ip_white_list']);
        if (!in_array(userip(), $ArrayIp)) {
            dies(-1, 'IP：' . userip() . ' 未设置对接白名单！');
        }
        if ($Data['sign'] != self::Sign($Data, $User['token'])) {
            dies(-1, 'sign验证失败，请检测您的站点版本是否和此站版本(' . $accredit['versions'] . ')一致');
        }
        if ($Data['url'] == href()) dies(-1, '不能对接自己哦！');
        return $User;
    }

    /**
     * @param $User 用户信息
     * @param $Data 下单商品数据
     * api对接下单！
     */
    public static function Buy($User, $Data)
    {
        global $conf;
        $DB = SQL::DB();
        $GoodsHide = UserConf::GoodsHide(); //取出不显示商品ID

        if (count($GoodsHide) >= 1) {
            if (in_array($Data['gid'], $GoodsHide)) dies(-1, '商品已下架！');
        }

        $Goods = $DB->get('goods', '*',
            ['gid' => $Data['gid'], 'state' => 1, 'method[~]' => '4']);
        if (!$Goods) {
            dies(-1, '抱歉,此商品已下架或不存在,无法被对接!');
        }

        $DataInput = explode('|', $Goods['input']);

        if ($Goods['specification'] == 2) {
            $SpRule = RlueAnalysis($Goods, 3);
            if ($SpRule != -1) {
                $DataInput = array_merge($SpRule['MasterRule'], $DataInput);
            }
            if (empty($Goods['input']) && $SpRule != -1) {
                $DataInput = $SpRule['MasterRule'];
            }
        }
        $InputArr = [];
        $i = 1;
        foreach ($DataInput as $value) {
            if (strstr($value, '{') && strstr($value, '}')) {
                $value = explode('{', $value)[0];
            }
            if (empty($Data['value' . $i])) dies(-1, '下单信息缺失,请将[' . $value . ']填写完整');
            $InputArr[] = $Data['value' . $i];
            $i++;
        }

        if ($conf['CouponApiBeDocking'] == 2) {
            $CouponId = -1;
        } else {
            $Coupon = (empty($Data['coupon']) ? -1 : $Data['coupon']);
            if ($Coupon == -1) {
                $CouponId = -1;
            } else $CouponId = $Data['coupon'];
        }

        $DataBuy = [
            'gid' => $Data['gid'],
            'type' => ($Data['type'] + 1),
            'num' => ($Data['num'] / $Goods['quantity']),
            'data' => $InputArr,
            'mode' => 'alipay',
            'Api' => 1,
            'CouponId' => $CouponId
        ];

        $order = Order::Creation($DataBuy, $User);
        if ($order) {
            $OrderData = $DB->get('order', ['order'], [
                'id' => $order,
            ]);
            $User = $DB->get('user', [
                'money', 'currency'
            ], ['id' => $User['id']]);

            $Return = [
                'code' => 1,
                'order' => $OrderData['order'],
                'money' => ($Data['type'] == 1 ? $User['money'] : $User['currency']),
                'msg' => '订单创建成功,购买后剩余' . ($Data['type'] == 1 ? $User['money'] . '余额!' : $User['currency'] . '积分!'),
            ];

            $OrderToken = $DB->select('token', ['token'], ['order' => $OrderData['order']]);
            if ($OrderToken && count($OrderToken) >= 1) {
                $ArrayToken = [];
                foreach ($OrderToken as $value) {
                    $ArrayToken[] = $value['token'];
                }
                $Return['token'] = json_encode($ArrayToken);
                $Return['msg'] .= '，另,本次共发卡' . count($ArrayToken) . '张！';
            }
            dier($Return);
        } else {
            dies(-1, '订单创建失败！');
        }
    }

    /**
     * @return array
     * 取出商品列表
     */
    public static function GoodsList()
    {
        $DB = SQL::DB();
        $GoodsHide = UserConf::GoodsHide(); //取出不显示商品ID
        $SQL = [
            'ORDER' => ['sort' => 'DESC'],
            'method[~]' => '4',
        ];
        if (count($GoodsHide) >= 1) {
            $SQL = array_merge($SQL, [
                'gid[!]' => $GoodsHide
            ]);
        }

        $Count = $DB->count('goods', $SQL);
        if ($Count === 0) {
            dies(-1, '一个可对接商品都没有！');
        }

        $Res = $DB->select('goods', [
            'gid', 'cid', 'name', 'state',
        ], $SQL);
        $Data = [];
        foreach ($Res as $v) {
            $Data[] = $v;
        }
        dier([
            'code' => 1,
            'msg' => '商品列表获取成功',
            'data' => $Data
        ]);
    }

    /**
     * @param $User
     * @param $Gid
     * @return array
     * 获取商品详情
     */
    public static function GoodsDetails($User, $Gid)
    {
        $DB = SQL::DB();

        $GoodsHide = UserConf::GoodsHide();

        if (count($GoodsHide) >= 1) {
            if (in_array($Gid, $GoodsHide)) dies(-1, '商品已下架！');
        }

        $Goods = $DB->get('goods', [
            'gid', 'cid', 'name', 'image',
            'money', 'profits', 'min', 'max',
            'quota', 'freight', 'method',
            'input', 'quantity', 'docs', 'alert', 'units',
            'state', 'specification', 'specification_type',
            'specification_spu', 'specification_sku', 'label',
            'date', 'selling', 'deliver'
        ], [
            'gid' => $Gid,
            'LIMIT' => 1,
            'method[~]' => '4',
            'state[!]' => 2,
        ]);

        if (!$Goods) {
            dies(-1, '商品已下架或不存在，或无法被对接！');
        }

        /**
         * 同步卡密库存
         */
        if ((int)$Goods['deliver'] === 3) {
            $Goods['quota'] = $DB->count('token', [
                "AND" => [
                    "uid" => 1,
                    "gid" => $Goods['gid']
                ],
            ]);
        }

        if ((int)$Goods['specification'] === 2) {
            $Goods['specification_sku'] = json_decode($Goods['specification_sku'], TRUE);
            $Goods['specification_spu'] = json_decode($Goods['specification_spu'], TRUE);
            $GoodsSpu = [];
            foreach ($Goods['specification_sku'] as $key => $value) {
                if ($value['money'] != "") {
                    $GoodsPrice = Price::Get($value['money'], $Goods['profits'], $User['grade'], $Goods['gid'], $Goods['selling']);
                    $value['money'] = $GoodsPrice['price'];
                    unset($GoodsPrice);
                }
                $GoodsSpu[$key] = $value;
            }
            $Goods['specification_sku'] = $GoodsSpu;
        } else {
            $Goods['specification_sku'] = [];
            $Goods['specification_spu'] = [];
        }
        $GoodsPrice = Price::Get($Goods['money'], $Goods['profits'], $User['grade'], $Goods['gid'], $Goods['selling']);
        $Goods['money'] = round($GoodsPrice['price'], 8);
        $Goods['points'] = $GoodsPrice['points'];
        if (!empty($Goods['image'])) {
            $Goods['image'] = json_decode($Goods['image'], TRUE);
        }
        if (!empty($Goods['input'])) {
            $Ex = explode('|', $Goods['input']);
            $Input = [];
            foreach ($Ex as $value) {
                if (strstr($value, '{') && strstr($value, '}')) {
                    $Name = explode('{', $value);
                    $Input[] = [
                        'type' => 2,
                        'name' => $Name[0],
                        'data' => explode(',', substr_replace($Name[1], "", -1)),
                    ];
                } else {
                    $Input[] = [
                        'type' => 1,
                        'name' => $value,
                    ];
                }
            }
            $Goods['input'] = $Input;
        } else {
            $Goods['input'] = [];
        }


        unset($Goods['method'], $Goods['profits'], $Goods['selling'], $Goods['deliver']);

        return [
            'code' => 1,
            'msg' => '商品详情获取成功',
            'data' => $Goods,
        ];
    }

    public static function GoodsListArr($Data)
    {

        if ($Data['type'] != 2 && !empty($_SESSION['XiaoChuGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['XiaoChuGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['gid'],
                    'name' => $v['name'] . ' - ' . ((int)$v['state'] === 1 ? '上架中' : ((int)$v['state'] === 2 ? '已下架' : '已隐藏')),
                    'cid' => $v['cid'],
                ];
            }
            return [
                'code' => 1,
                'msg' => '可对接商品列表获取成功！',
                'data' => $GoodsList
            ];
        }

        $DB = SQL::DB();
        $TypeSupply = $DB->get('shequ', ['username', 'password', 'url'], [
            'id' => $Data['id'],
        ]);
        if (!$TypeSupply) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $DataPost = [
            'act' => 'DockingGoodsList',
            'url' => href(),
            'id' => $TypeSupply['username'],
        ];

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $TypeSupply['password'])
        ], $DataPost);
        $CurlData = Api::Curl($TypeSupply['url'] . 'api.php', $DataPost);
        $CurlDataJson = json_decode($CurlData, true);

        if (empty($CurlData)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $TypeSupply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] === 1) {
            $_SESSION['XiaoChuGoodsList_' . $Data['id']] = $CurlDataJson['data'];
            $GoodsList = [];
            foreach ($CurlDataJson['data'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['gid'],
                    'name' => $v['name'] . ' - ' . ((int)$v['state'] === 1 ? '上架中' : '已下架'),
                    'cid' => $v['cid'],
                ];
            }

            return [
                'code' => 1,
                'msg' => '可对接商品列表获取成功！',
                'data' => $GoodsList
            ];
        }

        return [
            'code' => -1,
            'msg' => $CurlDataJson['msg'],
        ];
    }

    /**
     * @param $param [需要签名的数组]
     * @param $key [对接密钥]
     * @return string
     * 签名计算
     */
    public static function Sign($param, $key)
    {
        $signPars = '';
        ksort($param);
        foreach ($param as $k => $v) {
            $k = trim($k);
            $v = (string)trim($v);
            if ($k !== 'sign' && $v !== '') {
                $signPars .= $k . '=' . $v . '&';
            }
        }
        $signPars = rtrim($signPars, '&');
        $signPars .= $key;
        return md5($signPars);
    }

    /**
     * @param $Data
     * @return array
     * 获取新增商品SQL数据
     */
    public static function CommodityAnalysis($Data)
    {
        global $date;
        $Goods = json_decode($Data['data'], true);
        $Goods['specification_spu'] = json_decode($Goods['specification_spu'], true);
        $Goods['specification_sku'] = json_decode($Goods['specification_sku'], true);
        $SQL = [
            'cid' => $Data['cid'],
            'sort' => $Data['sort'],
            'name' => $Data['name'],
            'image[JSON]' => [ImageUrl($Goods['image'])],
            'min' => $Goods['min'],
            'max' => $Goods['max'],
            'quota' => $Goods['quota'],
            'input' => $Goods['input'],
            'quantity' => $Goods['quantity'],
            'alert' => $Goods['alert'],
            'docs' => $Goods['docs'],
            'money' => $Goods['money'],
            'units' => (empty($Goods['units']) ? '个' : $Goods['units']),
            'deliver' => -1,
            'sqid' => $Data['sqid'],
            'specification' => ($Goods['specification'] === true ? 2 : 1),
            'specification_type' => ($Goods['specification_type'] === true ? 1 : 2),
            'specification_spu[JSON]' => $Goods['specification_spu'],
            'specification_sku[JSON]' => $Goods['specification_sku'],
            'extend[JSON]' => $Goods['extend'],
            'date' => $date,
        ];
        return $SQL;
    }


    /**
     * @param $Goods
     * @param $SourceData
     * 商品详细状态
     */
    public static function CommodityStatus($Goods, $Supply)
    {
        $GoodsData = json_decode($Goods['extend'], true);
        if (empty($GoodsData['gid'])) {
            return [
                'code' => -1,
                'msg' => '此商品对接参数缺失，无法完成商品状态监控！',
            ];
        }


        $Supply['url'] = StringCargo::UrlVerify($Supply['url']);

        $DataPost = [
            'act' => 'Docking_goods',
            'url' => href(),
            'id' => $Supply['username'],
            'gid' => $GoodsData['gid'],
        ];

        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $Supply['password'])
        ], $DataPost);

        $CurlData = Api::Curl($Supply['url'] . 'api.php', $DataPost);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ($CurlDataJson['code'] !== 1 || $CurlDataJson['data']['state'] !== 1) {
            return [
                'code' => 1,
                'msg' => '数据获取成功',
                'data' => [
                    'state' => 2,
                    'money' => 0,
                    'inventory' => 0,
                ]
            ];
        }

        /**
         * 解析数据
         */
        if ($CurlDataJson['data']['SKU'] !== false) {
            $specification = [];
            foreach ($CurlDataJson['data']['SKU'] as $key => $val) {
                if ($val['money'] === "") {
                    $Price = "";
                } else {
                    $Price = $val['money'] / (empty($val['quantity']) ? $CurlDataJson['data']['count'] : $val['quantity']);
                }
                $inventory = $val['quota'];
                $specification[$key] = [
                    'money' => $Price,
                    'inventory' => $inventory,
                ];
                unset($Price, $inventory);
            }
        } else {
            $specification = false;
        }

        if ($CurlDataJson['data']['count'] <= 1) {
            $CurlDataJson['data']['count'] = 1;
        }

        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => $CurlDataJson['data']['inventory'] ?? ($Goods['quota'] <= 0 ? 999999 : $Goods['quota']),
                'state' => $CurlDataJson['data']['state'],
                'money' => ($CurlDataJson['data']['Price'] / $CurlDataJson['data']['count']),
                'specification' => $specification,
            ]
        ];
    }


    public static function GoodsArray($Data)
    {
        $DB = SQL::DB();
        $TypeSupply = $DB->get('shequ', '*', [
            'id' => $Data['sqid'],
        ]);
        if (!$TypeSupply) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

        $DataPost = [
            'act' => 'DockingGoodsLog',
            'url' => href(),
            'id' => $TypeSupply['username'],
            'gid' => $Data['gid'],
        ];

        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $TypeSupply['password'])
        ], $DataPost);

        $CurlData = Api::Curl($TypeSupply['url'] . 'api.php', $DataPost);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlData)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $TypeSupply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] === 1) {
            $Goods = $CurlDataJson['data'];
            $DataImage = [];
            foreach ($Goods['image'] as $v) {
                if (!strstr($v, 'http')) {
                    $v = $TypeSupply['url'] . $v;
                }
                $DataImage[] = $v;
            }
            $Goods['image'] = $DataImage;

            $Input = [];
            foreach ($Goods['input'] as $k => $v) {
                $Input['parameter'][] = 'INPUT' . ($k + 1);
                if ($v['type'] === 1) {
                    $Input['input'][] = $v['name'];
                } else {
                    $Input['input'][] = $v['name'] . '{' . implode(',', $v['data']) . '}';
                }
            }

            if ($Goods['quantity'] <= 1) {
                $Goods['quantity'] = 1;
            }

            return [
                'code' => 1,
                'msg' => '对接参数自动填写成功！',
                'data' => [
                    'name' => $Goods['name'],
                    'image' => implode('\n', $Goods['image']),
                    'docs' => $Goods['docs'],
                    'alert' => $Goods['alert'],
                    'money' => $Goods['money'],
                    'min' => (int)$Goods['min'],
                    'max' => (int)$Goods['max'],
                    'quota' => (int)$Goods['quota'],
                    'quantity' => (int)$Goods['quantity'],
                    'extend' => [
                        'gid' => (int)$Goods['gid'],
                        'parameter' => implode(',', $Input['parameter']),
                    ],
                    'units' => $Goods['units'],
                    'input' => implode('|', $Input['input']),
                    'specification' => ((int)$Goods['specification'] === 2 ? true : false),
                    'specification_type' => ((int)$Goods['specification_type'] === 1 ? true : false),
                    'specification_spu' => json_encode($Goods['specification_spu'], JSON_UNESCAPED_UNICODE),
                    'specification_sku' => json_encode($Goods['specification_sku'], JSON_UNESCAPED_UNICODE),
                ]
            ];
        }

        return [
            'code' => -1,
            'msg' => $CurlDataJson['msg'],
        ];
    }

    /**
     * @param $OrderData 订单信息
     * @param $Goods 商品信息
     * @param $TypeSupply 对接货源信息
     * 提交下单信息！
     */
    public static function Submit($OrderData, $Goods, $TypeSupply)
    {
        global $date, $conf;
        $DB = SQL::DB();

        $DataPost = [];
        $i = 1;

        $InputArray = json_decode($OrderData['input'], TRUE);

        if ($Goods['specification'] == 2 && $Goods['specification_type'] == 2) {
            $InputArray = RuleSubmitParameters(json_decode($Goods['specification_spu'], TRUE), $InputArray);
        }

        foreach ($InputArray as $value) {
            $DataPost += [
                'value' . $i => $value,
            ];
            ++$i;
        }

        $coupon = ($conf['CouponApiDockingOthers'] == 1 ? -2 : -1);

        $GoodsData = json_decode($Goods['extend'], true); //对接数据

        $DataPost = array_merge([
            'act' => 'Docking_buy',
            'url' => href(),
            'id' => $TypeSupply['username'],
            'num' => ($OrderData['num'] * $Goods['quantity']),
            'gid' => $GoodsData['gid'],
            'type' => ($TypeSupply['secret'] == '' ? 1 : ($TypeSupply['secret'] != 1 && $TypeSupply['secret'] != 2 ? 1 : $TypeSupply['secret'])),
            'coupon' => $coupon,
        ], $DataPost);

        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $TypeSupply['password'])
        ], $DataPost);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'api.php', $DataPost);
        if (empty($DataCurl)) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => '对接返回信息有误，请根据对接日志调试！',
                'money' => 0,
                'order' => 0,
            ];
        }
        $DataCurl = json_decode($DataCurl, TRUE);
        if ($DataCurl['code'] >= 0 && isset($DataCurl['code'])) {
            /**
             * 卡密转换
             */
            if (isset($DataCurl['token'])) {
                $SQL = [];
                foreach (json_decode($DataCurl['token'], TRUE) as $v) {
                    if ($v == '') {
                        continue;
                    }
                    $SQL[] = [
                        'uid' => $OrderData['uid'],
                        'gid' => $Goods['gid'],
                        'code' => $InputArray[0],
                        'token' => $v,
                        'ip' => $OrderData['ip'],
                        'order' => $OrderData['order'],
                        'endtime' => $date,
                        'addtime' => $date,
                    ];
                }
                $DB->insert('token', $SQL);
            }

            $Msg = $DataCurl['msg'];
            $code = ($DataCurl['code'] >= 0 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']);
            $money = ($DataCurl['code'] >= 0 ? $DataCurl['money'] : 0);
            $order = ($DataCurl['code'] >= 0 ? $DataCurl['order'] : -1);
            $docking = ($DataCurl['code'] >= 0 ? 1 : 2);
        } else {
            $Msg = (empty($DataCurl['msg']) ? '下单信息获取失败!,请检查对接配置！' : $DataCurl['msg']);
            $code = $conf['SubmitState'];
            $money = 0;
            $order = -1;
            $docking = 2;
        }

        return [
            'code' => $code,
            'docking' => $docking,
            'msg' => $Msg,
            'money' => $money,
            'order' => $order,
        ];
    }

    /**
     * @param $id 订单号
     * @param $TypeSupply 对接货源信息
     */
    public static function Query($id, $TypeSupply)
    {
        $DataPost = [
            'act' => 'DockingQuery',
            'url' => href(),
            'id' => $TypeSupply['username'],
            'encrypt' => 1,
            'order' => $id
        ];

        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $TypeSupply['password'])
        ], $DataPost);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

        $DataCurl = Api::Curl($TypeSupply['url'] . 'api.php', $DataPost);
        if (empty($DataCurl)) {
            return false;
        }
        $DataCurl = json_decode($DataCurl, TRUE);

        if ((int)$DataCurl['state'] !== 1) {
            return false;
        }
        $Data = $DataCurl['data'];
        return [
            'ApiType' => 1,
            'ApiNum' => $Data['num'],
            'ApiTime' => $Data['addtiem'],
            'ApiInitial' => $Data['Initial'],
            'ApiPresent' => $Data['Present'],
            'ApiState' => $Data['state'],
            'ApiError' => ($Data['remark'] <> null ? $Data['remark'] : '无'),
            'ApiExplain' => (empty($Data['Explain']) ? -1 : $Data['Explain']),
            'ApiStateNum' => $DataCurl['StateNum'],
        ];
    }
}
