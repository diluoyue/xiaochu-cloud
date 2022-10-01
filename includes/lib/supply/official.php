<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/16 10:15
// +----------------------------------------------------------------------
// | Filename: official.php
// +----------------------------------------------------------------------
// | Explain: 服务端货源站对接扩展
// +----------------------------------------------------------------------

namespace lib\supply;


use Medoo\DB\SQL;

class official
{

    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '官方服务端货源站',
        'image' => '../assets/img/logo.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => '可打开服务端：<a href="https://cdn.79tian.com/api/wxapi/view/login.php" target="_blank">打开</a>，用户中心->对接密钥 内获取自己的密钥来进行对接！<br>默认对接节点：<font color="red">http://hyapi.79tian.com/</font> ，复制后填写至下方即可，若节点失效，可联系官方客服获取最新节点！',
        'ip' => 1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '节点域名',
                'tips' => '可填写：http://hyapi.79tian.com/',
                'type' => 1,
            ],
            'username' => [
                'name' => '对接密钥',
                'tips' => '请填写服务端获取的对接密钥！',
                'type' => 1,
            ],
            'secret' => [
                'name' => '排序方式',
                'tips' => [
                    '1' => '升序(商品ID从低到高进行排序)',
                    '2' => '降序(商品ID从高到低进行排序)',
                ],
                'type' => 2,
            ],
            'pattern' => [
                'name' => '亏损拦截',
                'tips' => [
                    '1' => '开启(当订单成本低于进货价时拦截)',
                    '2' => '关闭(不进行拦截！)',
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
                    'controller' => 'ProductDetails', //请求的控制器名称
                ],
                'GetListData' => [ //获取列表时的请求数据，提交的是当前所选对接社区内的值+当前已选参数的值
                    'id' => false,
                    'type' => 1,
                    'controller' => 'GoodsList', //请求的控制器名称
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
                            'controller' => 'GoodsList', //请求的控制器名称
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
    public static function AdminOrigin($Data = [], $Source = [], $DataSo = [])
    {
        switch ($Data['controller']) {
            case 'GoodsList': //获取可对接的商品列表
                return self::GoodsList($Data);
            case 'ProductDetails': //获取商品详情
                return self::ProductDetails($Data);
            case 'Submit': //对接下单
                return self::Submit($Data['Order'], $Data['Goods'], $Data['Supply']);
            case 'Query': //查询订单
                return self::Query($Data['Order'], $Data['Supply']);
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

    public static function GoodsList($Data)
    {
        if ($Data['type'] != 2 && !empty($_SESSION['OffIcIaLGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['OffIcIaLGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['gid'],
                    'name' => $v['name'] . ' - ' . ($v['BuyingRestrictions'] === false ? '正常对接' : '未满足对接条件') . ' - ' . $v['count'] . $v['units'] . $v['price'] . '元',
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
        $SourceData = $DB->get('shequ', ['url', 'username', 'secret'], [
            'id' => (int)$Data['id'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }

        $Post = [
            'type' => 7,    //一次获取全部商品（存在30分钟缓存！）
            'sorted' => $SourceData['secret'],  //排序方式
        ];
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'api/client/GoodsQuery/', $Post, [
            'token' => $SourceData['username']
        ]);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }


        if ($CurlDataJson['state'] === 1000) {

            $_SESSION['OffIcIaLGoodsList_' . $Data['id']] = $CurlDataJson['data'];

            $GoodsList = [];
            foreach ($CurlDataJson['data'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['gid'],
                    'name' => $v['name'] . ' - ' . ($v['BuyingRestrictions'] === false ? '正常对接' : '未满足对接条件') . ' - ' . $v['count'] . $v['units'] . $v['price'] . '元',
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
            'msg' => $CurlDataJson['message'],
        ];
    }

    public static function ProductDetails($Data)
    {
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'secret'], [
            'id' => (int)$Data['sqid'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'api/client/GoodsQuery/' . $Data['gid'], 0, [
            'token' => $SourceData['username']
        ]);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        $Goods = $CurlDataJson['data'];
        if ($Goods['Restriction'] !== false) {
//            return [
//                'code' => -1,
//                'msg' => '您的账户需满足以下条件才可对接此商品：' . implode('<br>', $Goods['Restriction']),
//            ];
        }

        if ($CurlDataJson['state'] === 1000) {
            $Input = [[], []];
            /**
             * 商品规格
             * specification
             * specification_type 1
             * specification_spu  规格名称
             * specification_sku  规格参数
             */

            if ($Goods['specification'] !== false) {
                $specification = true;
                $specification_type = true;
                $specification_spu = json_encode($Goods['specification']['rule'], JSON_UNESCAPED_UNICODE);
                $Sku = [];
                foreach ($Goods['specification']['parameter'] as $key => $val) {
                    $Sku[$key] = [
                        'image' => (empty($val['image']) ? '' : $val['image']),
                        'alert' => (empty($val['alert']) ? '' : $val['alert']),
                        'money' => (empty($val['price']) ? '' : $val['price']),
                        'quantity' => (empty($val['count']) ? '' : $val['count']),
                        'quota' => (!empty($val['inventory']) && (int)$val['inventory'] === -1 ? 999999 : (empty($val['inventory']) ? '' : (int)$val['inventory'])),
                        'min' => (empty($val['min']) ? '' : $val['min']),
                        'max' => (empty($val['max']) ? '' : $val['max']),
                        'units' => (empty($val['units']) ? '' : $val['units']),
                    ];
                }
                $specification_sku = json_encode($Sku, JSON_UNESCAPED_UNICODE);

                $I = 1;
                $Rule = [];
                foreach ($Goods['specification']['rule'] as $key => $val) {
                    $Rule[] = $key;
                }

                foreach ($Goods['input'] as $key => $val) {
                    if (in_array($key, $Rule)) {
                        continue;
                    }
                    if ($val['type'] === 3) {
                        //下拉框
                        $Input[0][] = $key . '{' . implode(',', $val['data']) . '}';
                    } else {
                        //输入框
                        $Input[0][] = $key;
                    }
                    $Input[1][] = 'INPUT' . $I; //对接参数
                    ++$I;
                }

            } else {
                $specification = false;
                $specification_type = false;
                $specification_spu = [];
                $specification_sku = [];

                $I = 1;
                foreach ($Goods['input'] as $key => $val) {
                    if ($val['type'] === 3) {
                        //下拉框
                        $Input[0][] = $key . '{' . implode(',', $val['data']) . '}';
                    } else {
                        //输入框
                        $Input[0][] = $key;
                    }
                    $Input[1][] = 'INPUT' . $I; //对接参数
                    ++$I;
                }
            }

            if ($Goods['count'] <= 1) {
                $Goods['count'] = 1;
            }

            return [
                'code' => 1,
                'msg' => '对接参数自动填写成功！<br>供货商ID：' . $Goods['uid'] . '<br>供货商押金：' . $Goods['deposit'] . '元<br>上架时间：' . $Goods['addtime'],
                'data' => [
                    'name' => $Goods['name'],
                    'image' => ImageUrl($Goods['image']),
                    'docs' => $Goods['content'],
                    'money' => $Goods['price'],
                    'min' => $Goods['min'],
                    'max' => $Goods['max'],
                    'quota' => ((int)$Goods['inventory'] === -1 ? 999999 : $Goods['inventory']),
                    'specification' => $specification,
                    'specification_type' => $specification_type,
                    'specification_spu' => $specification_spu,
                    'specification_sku' => $specification_sku,
                    'quantity' => $Goods['count'],
                    'extend' => [
                        'gid' => $Goods['gid'],
                        'parameter' => implode(',', $Input[1]),
                    ],
                    'alert' => $Goods['alert'],
                    'units' => $Goods['units'],
                    'input' => implode('|', $Input[0]),
                ]
            ];
        }

        return [
            'code' => -1,
            'msg' => $CurlDataJson['message'],
        ];

    }

    /**
     * @param $OrderData
     * @param $Goods
     * @param $TypeSupply
     * @return array
     * 提交订单
     */
    public static function Submit($OrderData, $Goods, $TypeSupply)
    {
        global $conf, $accredit;

        $GoodsData = json_decode($Goods['extend'], true); //对接数据

        $InputArray = json_decode($OrderData['input'], TRUE);

        if ((int)$Goods['specification'] === 2 && (int)$Goods['specification_type'] === 2) {
            $InputArray = RuleSubmitParameters(json_decode($Goods['specification_spu'], TRUE), $InputArray);
        }

        $DataPost = [
            'count' => $OrderData['num'],//购买份数
            'callback_url' => href(2) . ROOT_DIR_S . '/api.php?act=OffCiaLQuery&Token=' . md5($accredit['token'] . href()),
            'data' => $InputArray,
            'callback_type' => false,
        ];

        if ((int)$TypeSupply['pattern'] !== 2) {
            $DataPost['price'] = $OrderData['money'];
        }

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'api/client/BuyGoods/' . $GoodsData['gid'], $DataPost, [
            'token' => $TypeSupply['username']
        ]);
        $DataCurl = json_decode($DataCurl, TRUE);
        if (empty($DataCurl)) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => '对接返回信息有误，请根据对接日志调试！',
                'money' => 0,
                'order' => 0,
            ];
        }

        if (isset($DataCurl['state']) && (int)$DataCurl['state'] === 1018) {
            //队列提交成功！
            return [
                'code' => $conf['SubmitStateSuccess'],
                'docking' => 1,
                'msg' => '订单提交成功，若订单进度未能自动更新，可通过查看订单详情来更新订单数据！',
                'money' => -1,
                'order' => $DataCurl['order'],
            ];
        }

        if ($DataCurl['message'] === '您提交的回调地址无法访问，请检查！') {
            $DataCurl['message'] = '当前订单回调地址：' . $DataPost['callback_url'] . '，无法被服务端访问！';
        }

        return [
            'code' => $conf['SubmitState'],
            'docking' => 2,
            'msg' => (empty($DataCurl['message']) ? '对接失败，请根据对接日志查看详情！' : $DataCurl['message']),
            'money' => 0,
            'order' => 0,
        ];
    }

    /**
     * @param $id
     * @param $TypeSupply
     * 查询订单
     */
    public static function Query($Order, $TypeSupply)
    {
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $Data = Api::Curl($TypeSupply['url'] . 'api/client/OrderInquiry/' . $Order['order_id'], 0, [
            'token' => $TypeSupply['username']
        ]);
        $Data = json_decode($Data, true);

        if (empty($Data)) {
            return [];
        }
        if ($Data['state'] === 1000) {
            $Data = $Data['data'];
            if ((int)$Order['user_rmb'] === -1) {
                self::CallBack([
                    'state' => 1008,
                    'message' => $Data['statements'],
                    'data' => [
                        'state' => $Data['state'],
                        'oid' => $Data['oid'],
                        'order' => $Data['order'],
                        'money' => $Data['money'],
                        'price' => $Data['price'],
                        'result' => $Data['remark_receipt'],
                        'logistics' => (!$Data['logistics'] ? -1 : $Data['logistics']),
                        'KamiList' => (!$Data['KamiList'] ? -1 : $Data['KamiList']['List']),
                    ]
                ], 2, 2);
            }

            switch ($Data['state']) {
                case 1:
                    $State = '已收货';
                    $StateNum = 1;
                    break;
                case 2:
                    $State = '待付款';
                    $StateNum = 3;
                    break;
                case 3:
                    $State = '待发货';
                    $StateNum = 2;
                    break;
                case 4:
                    $State = '处理中';
                    $StateNum = 4;
                    break;
                case 5:
                    $State = '已发货';
                    $StateNum = 1;
                    break;
                case 6:
                    $State = '已退款';
                    $StateNum = 5;
                    break;
                case 7:
                    $State = '退款中';
                    $StateNum = 3;
                    break;
                case 8:
                    $State = '已结单';
                    $StateNum = 1;
                    break;
                default:
                    $State = '未知状态';
                    $StateNum = 3;
                    break;
            }

            if ($Data['logistics']) {
                //同步物流信息
                $DB = SQL::DB();
                $DB->update('order', [
                    'logistics' => $Data['logistics'],
                ], [
                    'id' => $Order['id'],
                ]);
            }

            return [
                'ApiType' => 1,
                'ApiNum' => $Data['BuyCount'] * $Data['GoodsCount'],
                'ApiTime' => $Data['addtime'],
                'ApiInitial' => 0,
                'ApiPresent' => 0,
                'ApiState' => $State,
                'ApiError' => $Data['remark_receipt'],
                'ApiStateNum' => $StateNum,
            ];
        }

        return [];
    }

    /**
     * @param $Data
     * 订单回调接收或还可用于更新订单数据!
     */
    public static function CallBack($Data, $type = 1, $State = 1)
    {
        global $date, $conf, $accredit;
        $Data['Token'] = $Data['Token'] ?? '';
        if (md5($accredit['token'] . href()) !== $Data['Token'] && $State === 1) {
            if ($type !== 1) {
                return false;
            }
            dies(-1, '回调密钥有误!');
        }
        $DB = SQL::DB();
        $Order = $DB->get('order', '*', [
            'order_id' => (string)$Data['data']['order'],
        ]);
        if (!$Order) {
            if ($type !== 1) {
                return false;
            }
            dies(-1, '本地订单号不存在!');
        }

        if (empty($Data['state']) || empty($Data['data']['order'])) {
            if ($type !== 1) {
                return false;
            }
            dies(-1, '参数缺失!');
        }

        if ((int)$Data['state'] === 1008) {
            $SQL = [
                'state' => $conf['SubmitStateSuccess'],
                'return' => $Data['data']['result'],
                'user_rmb' => $Data['data']['money'],
                'money' => $Data['data']['price'],
                'finishtime' => $date,
            ];
            if ($Data['data']['logistics'] != -1) {
                $SQL['remark'] = '物流单号|' . $Data['data']['logistics'];
            }
            if ($Data['data']['KamiList'] != -1) {
                $SQLKAMI = [];
                $KaCount = $DB->count('token', [
                        'order' => $Order['order'],
                        'uid' => $Order['uid'],
                        'gid' => $Order['gid'],
                    ]) - 0;
                foreach ($Data['data']['KamiList'] as $v) {
                    $SQLKAMI[] = [
                        'uid' => $Order['uid'],
                        'gid' => $Order['gid'],
                        'code' => json_decode($Order['input'], TRUE)[0],
                        'token' => '卡号：' . $v['token'] . (!empty($v['pass']) ? '密码：' : $v['pass']) . (!empty($v['content']) ? '说明：' : $v['content']),
                        'ip' => $Order['ip'],
                        'order' => $Order['order'],
                        'endtime' => $date,
                        'addtime' => $date,
                    ];
                }
                if ($KaCount < count($SQLKAMI)) {
                    $DB->insert('token', $SQLKAMI);
                }
            }
            $Res = $DB->update('order', $SQL, [
                'id' => $Order['id']
            ]);
        } else {
            $Res = $DB->update('order', [
                'state' => 2,
                'return' => (empty($Data['message']) ? '回调异常,请直接在后台查询订单,会自动同步!' : $Data['message']),
            ], [
                'id' => $Order['id']
            ]);
        }
        if ($Res) {
            if ($type != 1) return true;
            dies(1, '操作成功!');
        } else {
            if ($type != 1) return false;
            dies(-1, '操作失败!');
        }
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
        $CurlData = Api::Curl($Supply['url'] . 'api/client/CommodityPriceInquiry/' . $GoodsData['gid'], 0, [
            'token' => $Supply['username']
        ]);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ($CurlDataJson['state'] !== 1000 || $CurlDataJson['data']['BuyingRestrictions'] != false) {
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
        if ($CurlDataJson['data']['specification'] !== false) {
            $specification = [];
            foreach ($CurlDataJson['data']['specification']['parameter'] as $key => $val) {
                if ($val['price'] === '') {
                    $Price = '';
                } else {
                    $Price = $val['price'] / (empty($val['count']) ? $CurlDataJson['data']['count'] : $val['count']);
                }
                if ($val['inventory'] === '') {
                    $inventory = '';
                } else {
                    $inventory = ($val['inventory'] === -1 ? 999999 : $val['inventory']);
                }
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
                'inventory' => ($CurlDataJson['data']['inventory'] === -1 ? 999999 : $CurlDataJson['data']['inventory']),
                'state' => ($CurlDataJson['data']['state'] === 1 ? 1 : 2),
                'money' => ($CurlDataJson['data']['price'] / $CurlDataJson['data']['count']),
                'specification' => $specification,
            ]
        ];
    }

    /**
     * @param $Data
     * 快速添加商品配置解析
     */
    public static function CommodityAnalysis($Data)
    {
        global $date;
        $Goods = json_decode($Data['data'], true);
        $DB = SQL::DB();
        $Get = $DB->get('shequ', ['id'], [
            'OR' => [
                'type' => 6,
                'class_name' => 'official'
            ]
        ]);
        if (!$Get) {
            dies(-1, '服务端对接配置不存在，请手动添加对接！');
        }
        $Input = [[], []];
        if ($Goods['specification'] !== false) {
            $specification = 2;
            $specification_type = 1;
            $specification_spu = $Goods['specification']['rule'];
            $Sku = [];
            foreach ($Goods['specification']['parameter'] as $key => $val) {
                $Sku[$key] = [
                    'image' => (empty($val['image']) ? '' : $val['image']),
                    'alert' => (empty($val['alert']) ? '' : $val['alert']),
                    'money' => (empty($val['price']) ? '' : $val['price']),
                    'quantity' => (empty($val['count']) ? '' : $val['count']),
                    'quota' => (!empty($val['inventory']) && (int)$val['inventory'] === -1 ? 999999 : (empty($val['inventory']) ? '' : (int)$val['inventory'])),
                    'min' => (empty($val['min']) ? '' : $val['min']),
                    'max' => (empty($val['max']) ? '' : $val['max']),
                    'units' => (empty($val['units']) ? '' : $val['units']),
                ];
            }
            $specification_sku = $Sku;

            $I = 1;
            $Rule = [];
            foreach ($Goods['specification']['rule'] as $key => $val) {
                $Rule[] = $key;
            }

            foreach ($Goods['input'] as $key => $val) {
                if (in_array($key, $Rule)) {
                    continue;
                }
                if ($val['type'] === 3) {
                    //下拉框
                    $Input[0][] = $key . '{' . implode(',', $val['data']) . '}';
                } else {
                    //输入框
                    $Input[0][] = $key;
                }
                $Input[1][] = 'INPUT' . $I; //对接参数
                ++$I;
            }

        } else {
            $specification = 1;
            $specification_type = 2;
            $specification_spu = [];
            $specification_sku = [];

            $I = 1;
            foreach ($Goods['input'] as $key => $val) {
                if ($val['type'] === 3) {
                    //下拉框
                    $Input[0][] = $key . '{' . implode(',', $val['data']) . '}';
                } else {
                    //输入框
                    $Input[0][] = $key;
                }
                $Input[1][] = 'INPUT' . $I; //对接参数
                ++$I;
            }
        }

        $SQL = [
            'cid' => $Data['cid'],
            'sort' => $Data['sort'],
            'name' => $Data['name'],
            'image[JSON]' => [ImageUrl($Goods['image'])],
            'min' => $Goods['min'],
            'max' => $Goods['max'],
            'quota' => ((int)$Goods['inventory'] === -1 ? 999999 : $Goods['inventory']),
            'input' => implode('|', $Input[0]),
            'quantity' => $Goods['count'],
            'alert' => $Goods['alert'],
            'docs' => $Goods['content'],
            'money' => $Goods['price'],
            'units' => $Goods['units'],
            'deliver' => -1,
            'sqid' => $Get['id'],
            'specification' => $specification,
            'specification_type' => $specification_type,
            'specification_spu[JSON]' => $specification_spu,
            'specification_sku[JSON]' => $specification_sku,
            'extend[JSON]' => [
                'gid' => $Goods['gid'],
                'parameter' => implode(',', $Input[1]),
            ],
            'date' => $date,
        ];
        return $SQL;
    }

    /**
     * @param $id
     * @param int $page
     * 获取指定供货商的商品列表
     */
    public static function SupplyList($id, $page = 1)
    {
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'secret'], [
            'OR' => [
                'type' => 6,
                'class_name' => 'official',
            ]
        ]);

        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接配置读取失败，请手动前往添加货源内添加服务端对接配置！',
            ];
        }

        $Post = [
            'type' => 1,
            'page' => $page,
            'sorted' => $SourceData['secret'],  //排序方式
            'store_id' => $id,
        ];
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'api/client/GoodsQuery/', $Post, [
            'token' => $SourceData['username']
        ]);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['state'] !== 1000) {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['message'],
            ];
        }

        return [
            'code' => 1,
            'msg' => $CurlDataJson['message'],
            'data' => $CurlDataJson,
        ];

    }
}
