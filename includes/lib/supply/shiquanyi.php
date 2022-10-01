<?php

/**
 * Author：晴玖天
 * Creation：2020/4/22 13:31
 * Filename：shiquanyi.php
 * 视权益对接
 */

namespace lib\supply;


use Medoo\DB\SQL;

class shiquanyi
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '视权益',
        'image' => '../assets/img/shiquanyi.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => '搜索公众号：视权益 获取对应的对接API【需要联系客服获取】<br>接口地址请填写：http://dcapi.shiquanyi.com/',
        'ip' => 1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '社区域名',
                'type' => 1,
                'tips' => '接口地址,为了防止后期接口变动,不设为固定'
            ],
            'username' => [
                'name' => '用户ID',
                'tips' => '请填写您获取的用户ID!',
                'type' => 1,
            ],
            'password' => [
                'name' => '对接密钥',
                'tips' => '请填写对接密钥!',
                'type' => 1,
            ],
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

    /**
     * 获取sign签名
     * @param array $param 参数数据
     * @param string $key key
     * @return string
     */
    public static function getSign($param, $key)
    {
        ksort($param);
        $signPars = http_build_query($param);
        $signPars = trim($signPars, '&');
        $signPars .= '&appsecret=' . $key;
        return strtoupper(md5($signPars));
    }

    /**
     * @param $Data
     * @return array
     * 获取可对接商品列表
     */
    public static function GoodsList($Data)
    {
        if ($Data['type'] != 2 && !empty($_SESSION['ShiQyGoodsList_' . $Data['id']])) {
            return [
                'code' => 1,
                'msg' => '可对接商品列表获取成功！',
                'data' => $_SESSION['ShiQyGoodsList_' . $Data['id']]
            ];
        }

        $DB = SQL::DB();

        $SourceData = $DB->get('shequ', ['url', 'username', 'password'], [
            'id' => $Data['id'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);

        $Url = $SourceData['url'] . 'dcapi/queryCard';
        $Post = [
            'appid' => $SourceData['username'],
        ];
        $Post['sign'] = self::GetSign($Post, $SourceData['password']);

        $CurlData = Api::Curl($Url, $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        $Url = $SourceData['url'] . 'dcapi/card/queryCard';
        $Post = [
            'appid' => $SourceData['username'],
        ];
        $Post['sign'] = self::GetSign($Post, $SourceData['password']);

        $CurlData2 = Api::Curl($Url, $Post);
        $CurlDataJson2 = json_decode($CurlData2, true);
        if (empty($CurlDataJson2)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] == 1 && $CurlDataJson2['code'] == 1) {
            $goodsList = $CurlDataJson['result'];
            $goodsList2 = $CurlDataJson2['result'];
            $List = [];
            $GoodsList = [];
            foreach ($goodsList as $value) {
                foreach ($value['list'] as $v) {
                    $GoodsList[] = $v;
                    $List[] = [
                        'gid' => $v['id'],
                        'name' => $v['card_name'] . ' - ' . $v['name'] . ' 进货价：' . round($v['price'], 8) . '元 | ' . ($v['avg'] > 0 ? '正常开单' : '无法充值'),
                        'cid' => 1
                    ];
                }
            }

            foreach ($goodsList2 as $value) {
                foreach ($value['list'] as $v) {
                    $GoodsList[] = $v;
                    if ($v['num'] == 0) {
                        $Price = 8888888;
                    } else {
                        $Price = $v['priceList'][0]['price'];
                    }
                    $List[] = [
                        'gid' => $v['id'],
                        'name' => $v['card_name'] . ' - ' . $v['name'] . ' 进货价：' . round($Price, 8) . '元 | 库存：' . $v['num'] . ' | 类型：' . $v['typeName'],
                        'cid' => 2
                    ];
                }
            }

            $_SESSION['ShiQyGoodsList_' . $Data['id']] = $List;
            $_SESSION['ShiQyGoodsList2_' . $Data['id']] = $GoodsList;
            return [
                'code' => 1,
                'msg' => '可对接商品列表获取成功！',
                'data' => $List
            ];
        }

        return [
            'code' => -1,
            'msg' => $CurlDataJson['msg'],
        ];
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
            'label' => $Goods['label'],
            'state' => $Goods['state'] ?? 1,
            'alert' => '',
            'docs' => $Goods['docs'],
            'money' => $Goods['money'],
            'units' => (empty($Goods['units']) ? '个' : $Goods['units']),
            'deliver' => -1,
            'sqid' => $Data['sqid'],
            'specification' => 1,
            'specification_type' => 2,
            'specification_spu[JSON]' => [],
            'specification_sku[JSON]' => [],
            'extend[JSON]' => $Goods['extend'],
            'date' => $date,
        ];
        return $SQL;
    }


    /**
     * @param $Goods
     * @param $Supply
     * 商品详细状态
     * @return array
     */
    public static function CommodityStatus($Goods, $Supply)
    {
        $GoodsData = json_decode($Goods['extend'], true);
        //1_1，1_2_1
        $GoodsEx = explode('-', $GoodsData['gid']);
        if (empty($GoodsEx[0])) {
            return [
                'code' => -1,
                'msg' => '此商品对接参数缺失，无法完成商品状态监控！',
            ];
        }

        $Supply['url'] = StringCargo::UrlVerify($Supply['url']);

        if (count($GoodsEx) != 3) {
            $Post = [
                'appid' => $Supply['username'],
                'id' => (int)$GoodsData['gid'],
            ];
            $Post['sign'] = self::GetSign($Post, $Supply['password']);
            $CurlData = Api::Curl($Supply['url'] . 'dcapi/queryCardInfo', $Post);
        } else {
            $Post = [
                'appid' => $Supply['username'],
                'card_id' => (int)$GoodsData['gid'],
            ];
            $Post['sign'] = self::GetSign($Post, $Supply['password']);
            $CurlData = Api::Curl($Supply['url'] . 'dcapi/card/queryCardDetail', $Post);
        }

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ((int)$CurlDataJson['code'] !== 1) {
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
        $GoodsDataApi = $CurlDataJson['result'];
        /**
         * 解析数据
         */

        if (count($GoodsEx) != 3) {
            $AVG = (int)$GoodsDataApi['avg'];
            //耗时计算
            if ($AVG > 0) {
                $label = '官方|充值平均耗时:' . Sec2Time($AVG);
                if ($AVG >= 1 && $AVG <= 100) {
                    $quota = 300;
                } else if ($AVG >= 101 && $AVG <= 1000) {
                    $quota = 150;
                } else if ($AVG >= 1001 && $AVG <= 3000) {
                    $quota = 100;
                } else if ($AVG >= 3001 && $AVG <= 5000) {
                    $quota = 50;
                } else {
                    $quota = 10;
                }
            } else {
                $label = '无人接单';
                $quota = 0;
            }

            //同步到账耗时
            $DB = SQL::DB();
            $DB->update('goods', [
                'label' => $label,
                'state' => ($AVG === 0 ? 2 : 1)
            ], [
                'gid' => $Goods['gid']
            ]);
            return [
                'code' => 1,
                'msg' => '数据获取成功',
                'data' => [
                    'inventory' => $quota,
                    'state' => ($AVG === 0 ? 2 : 1),
                    'money' => (float)$GoodsDataApi['price'],
                    'specification' => false,
                ]
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '数据获取成功',
                'data' => [
                    'inventory' => $GoodsDataApi['num'],
                    'state' => ($GoodsDataApi['num'] === 0 ? 2 : 1),
                    'money' => ($GoodsDataApi['num'] === 0 ? 8888888 : (float)$GoodsDataApi['priceList'][0]['price']),
                    'specification' => false,
                ]
            ];
        }
    }

    /**
     * @param $Data
     * @return array
     * 获取商品详情
     */
    public static function ProductDetails($Data)
    {
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'password'], [
            'id' => $Data['sqid'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }

        if (empty($_SESSION['ShiQyGoodsList2_' . $Data['sqid']])) {
            return [
                'code' => -1,
                'msg' => '数据源获取失败，请重新获取一遍可对接商品列表！',
            ];
        }
        $Goods = $_SESSION['ShiQyGoodsList2_' . $Data['sqid']][$Data['key']];
        if ($Goods['id'] != $Data['gid']) {
            return [
                'code' => -1,
                'msg' => '商品键值和数据源不匹配，请重新获取商品列表数据！',
            ];
        }

        if (isset($Goods['avg'])) {
            $AVG = (int)$Goods['avg'];
            //耗时计算
            if ($AVG > 0) {
                $label = '官方|充值平均耗时:' . Sec2Time($AVG);
                if ($AVG >= 1 && $AVG <= 100) {
                    $quota = 300;
                } else if ($AVG >= 101 && $AVG <= 1000) {
                    $quota = 150;
                } else if ($AVG >= 1001 && $AVG <= 3000) {
                    $quota = 100;
                } else if ($AVG >= 3001 && $AVG <= 5000) {
                    $quota = 50;
                } else {
                    $quota = 10;
                }
            } else {
                $label = '无人接单';
                $quota = 0;
            }

            return [
                'code' => 1,
                'msg' => '对接参数自动填写成功！',
                'data' => [
                    'name' => $Goods['card_name'] . ' - ' . $Goods['name'],
                    'image' => $Goods['card_logo'],
                    'docs' => $Goods['card_name'],
                    'money' => (float)$Goods['price'],
                    'min' => 1,
                    'max' => 999999,
                    'quota' => $quota, //同步库存[根据开单耗时]
                    'quantity' => 1,
                    'state' => ($AVG === 0 ? 2 : 1),
                    'extend' => [
                        'gid' => $Goods['id'] . '-1', //直充=1
                    ],
                    'units' => '个',
                    'label' => $label,
                    'input' => $Goods['type_name'],
                ]
            ];
        } else {
            // 卡密商品
            return [
                'code' => 1,
                'msg' => '对接参数自动填写成功！',
                'data' => [
                    'name' => $Goods['card_name'] . ' - ' . $Goods['name'],
                    'image' => $Goods['logo'],
                    'docs' => $Goods['notice'],
                    'money' => ($Goods['num'] == 0 ? 8888888 : (float)$Goods['priceList'][0]['price']),
                    'min' => 1,
                    'max' => 999999,
                    'quota' => $Goods['num'],
                    'quantity' => 1,
                    'state' => ($Goods['num'] == 0 ? 2 : 1),
                    'extend' => [
                        'gid' => $Goods['id'] . '-2-' . $Goods['type'], //卡密=2,type=类型 卡密类型 0 官方激活码 1 卡号和密码 2 图片二维码 3 链接+卡密 4 链接直兑
                    ],
                    'units' => '张',
                    'input' => '提卡密码',
                ]
            ];
        }
    }

    /**
     * @param $OrderData //订单信息
     * @param $Goods //商品信息
     * @param $TypeSupply //对接货源信息
     * 提交下单信息！
     */
    public static function Submit($OrderData, $Goods, $TypeSupply)
    {
        global $conf;
        $InputArray = json_decode($OrderData['input'], TRUE);

        $GoodsData = json_decode($Goods['extend'], true);

        $Msg = '下单信息获取失败!,请检查对接配置！';
        $code = $conf['SubmitState'];
        $money = 0;
        $order = -1;
        $docking = 2;
        //1_1，1_2_1
        $GoodsEx = explode('-', $GoodsData['gid']);
        if (count($GoodsEx) != 3) {
            //常规
            $Post = [
                'appid' => $TypeSupply['username'],
                'card_id' => $GoodsEx[0],
                'account' => $InputArray[0],
                'number' => ($OrderData['num'] * $Goods['quantity']),
                'out_order_id' => $OrderData['order'],
                'datetime' => time(),
                'notify_url' => urlencode(href(2) . ROOT_DIR . 'includes/lib/supply/shiquanyi.php?Callback'),
            ];

            $Post['sign'] = self::GetSign($Post, $TypeSupply['password']);

            $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

            $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'dcapi/pay', $Post);

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
            if (isset($DataCurl['code'])) {
                $Msg = $DataCurl['msg'];
                $code = ($DataCurl['code'] == 1 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']);
                $money = 0;
                $order = ($DataCurl['code'] == 1 ? $DataCurl['result'] : -1);
                $docking = ($DataCurl['code'] == 1 ? 1 : 2);
            }
        } else {
            //发卡
            $Post = [
                'appid' => $TypeSupply['username'],
                'card_id' => $GoodsEx[0],
                'num' => ($OrderData['num'] * $Goods['quantity']),
                'auto_confirm_hour' => 10,
                'out_order_id' => $OrderData['order'],
                'order_money' => 0,
            ];
            $Post['sign'] = self::GetSign($Post, $TypeSupply['password']);
            $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
            $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'dcapi/card/payOrder', $Post);
            $DataCurl = json_decode($DataCurl, TRUE);
            if (isset($DataCurl['code'])) {
                if (isset($DataCurl['result']) && count($DataCurl['result']) >= 1) {
                    $SQL = [];
                    $DB = SQL::DB();
                    global $date;
                    $price = 0;
                    foreach ($DataCurl['result'] as $v) {
                        $price += (float)$v['money'];
                        switch ((int)$GoodsEx[2]) {
                            case 0:
                                $token = $v['card_num'];
                                break;
                            case 2:
                                $token = "图片二维码：" . $v['card_num'] . "，二维码内容：" . $v['card_pass'];
                                break;
                            case 3:
                                $token = "卡密：" . $v['card_num'] . "，兑换地址：" . $v['card_pass'];
                                break;
                            case 4:
                                $token = "充值地址：" . $v['card_num'] . "，充值说明：" . $v['card_pass'];
                                break;
                            default:
                                $token = "卡号：" . $v['card_num'] . "，卡密：" . $v['card_pass'];
                                break;
                        }
                        $SQL[] = [
                            'uid' => $OrderData['uid'],
                            'gid' => $OrderData['gid'],
                            'code' => json_decode($OrderData['input'], true)[0],
                            'token' => $token,
                            'ip' => $OrderData['ip'],
                            'order' => $OrderData['order'],
                            'endtime' => $date,
                            'addtime' => $date,
                        ];
                    }
                    if ($price > 0) {
                        $DataCurl['msg'] . "，本单消耗余额：" . $price;
                        $DB->update("order", [
                            'money' => $price,
                        ], [
                            'order' => $OrderData['order']
                        ]);
                    }
                    $TokenGet = $DB->count('token', [
                            'order' => $OrderData['order'],
                        ]) - 0;
                    if ($TokenGet < count($DataCurl['result'])) {
                        $DB->insert('token', $SQL);
                    }
                }
            }
            $Msg = $DataCurl['msg'];
            $code = ($DataCurl['code'] == 1 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']);
            $order = ($DataCurl['code'] == 1 ? $DataCurl['result'][0]['order_id'] : -1);
            $docking = ($DataCurl['code'] == 1 ? 1 : 2);
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
    public static function Query($Order, $TypeSupply)
    {
        $DB = SQL::DB();
        $Goods = $DB->get("goods", ['extend'], [
            'gid' => $Order['gid']
        ]);

        if ($Goods) {
            $GoodsEx = explode('-', $Goods['extend']);
            if (count($GoodsEx) == 3) {
                //卡密
                $DataPost = [
                    'appid' => $TypeSupply['username'],
                    'out_order_id' => $Order['order'],
                ];

                $DataPost['sign'] = self::GetSign($DataPost, $TypeSupply['password']);

                $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

                $DataCurl = Api::Curl($TypeSupply['url'] . 'dcapi/card/queryOrder', $DataPost);

                $DataCurl = json_decode($DataCurl, TRUE);

                if (empty($DataCurl) || $DataCurl['code'] != 1) {
                    return [];
                }
                $Data = $DataCurl['result'];
                $StatusCn = self::StatusCn2($Data[0]['status']);
                $StateNum = $StatusCn[0];
                $order_state = $StatusCn[1];

                if (isset($DataCurl['result']) && count($DataCurl['result']) >= 1) {
                    $SQL = [];
                    $DB = SQL::DB();
                    global $date;
                    $price = 0;
                    foreach ($DataCurl['result'] as $v) {
                        $price += (float)$v['money'];
                        switch ((int)$GoodsEx[2]) {
                            case 0:
                                $token = $v['card_num'];
                                break;
                            case 2:
                                $token = "图片二维码：" . $v['card_num'] . "，二维码内容：" . $v['card_pass'];
                                break;
                            case 3:
                                $token = "卡密：" . $v['card_num'] . "，兑换地址：" . $v['card_pass'];
                                break;
                            case 4:
                                $token = "充值地址：" . $v['card_num'] . "，充值说明：" . $v['card_pass'];
                                break;
                            default:
                                $token = "卡号：" . $v['card_num'] . "，卡密：" . $v['card_pass'];
                                break;
                        }
                        $SQL[] = [
                            'uid' => $Order['uid'],
                            'gid' => $Order['gid'],
                            'code' => json_decode($Order['input'], true)[0],
                            'token' => $token,
                            'ip' => $Order['ip'],
                            'order' => $Order['order'],
                            'endtime' => $date,
                            'addtime' => $date,
                        ];
                    }
                    if ($price > 0) {
                        $DB->update("order", [
                            'money' => $price,
                        ], [
                            'order' => $Order['order']
                        ]);
                    }
                    $TokenGet = $DB->count('token', [
                            'order' => $Order['order'],
                        ]) - 0;
                    if ($TokenGet < count($DataCurl['result'])) {
                        $DB->insert('token', $SQL);
                    }
                }

                return [
                    'ApiType' => 1,
                    'ApiNum' => '无',
                    'ApiTime' => date('Y-m-d H:i', $Data[0]['create_time']),
                    'ApiInitial' => '无',
                    'ApiPresent' => '无',
                    'ApiState' => $order_state,
                    'ApiError' => '无',
                    'ApiStateNum' => $StateNum,
                ];
            }
        }

        $DataPost = [
            'appid' => $TypeSupply['username'],
            'order_id' => $Order['order_id'],
        ];

        $DataPost['sign'] = self::GetSign($DataPost, $TypeSupply['password']);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

        $DataCurl = Api::Curl($TypeSupply['url'] . 'dcapi/queryOrder', $DataPost);

        $DataCurl = json_decode($DataCurl, TRUE);

        if (empty($DataCurl)) {
            return [];
        }

        if ($DataCurl['code'] != 1) {
            return [];
        }

        $Data = $DataCurl['result'];
        $StatusCn = self::StatusCn($Data['status']);
        $StateNum = $StatusCn[0];
        $order_state = $StatusCn[1];

        return [
            'ApiType' => 1,
            'ApiNum' => '无',
            'ApiTime' => date('Y-m-d H:i', $Data['create_time']),
            'ApiInitial' => '无',
            'ApiPresent' => '无',
            'ApiState' => $order_state,
            'ApiError' => '无',
            'ApiStateNum' => $StateNum,
        ];
    }

    /**
     * @param $state
     * 返回状态码
     * 0=匹配的本地状态码
     * 1=对接站点的状态码,文字描述
     */
    public static function StatusCn2($state)
    {
        switch ($state) {
            case 2:
                $order_state = '充值中';
                $StateNum = 4;
                break;
            case 3:
                $order_state = '已完成';
                $StateNum = 1;
                break;
            case 4:
                $order_state = '纠纷中';
                $StateNum = 6;
                break;
            case 5:
                $order_state = '纠纷已完成';
                $StateNum = 5;
                break;
            case 6:
                $order_state = '已取消';
                $StateNum = 5;
                break;
            default:
                $order_state = '未知状态';
                $StateNum = 3;
                break;
        }

        return [
            $StateNum,
            $order_state
        ];
    }

    /**
     * @param $state
     * 返回状态码
     * 0=匹配的本地状态码
     * 1=对接站点的状态码,文字描述
     */
    public static function StatusCn($state)
    {
        switch ($state) {
            case 1:
                $order_state = '待充值';
                $StateNum = 2;
                break;
            case 2:
                $order_state = '充值中';
                $StateNum = 4;
                break;
            case 3:
                $order_state = '充值成功-待确认';
                $StateNum = 1;
                break;
            case 4:
                $order_state = '充值成功-已完成';
                $StateNum = 1;
                break;
            case 5:
                $order_state = '充值成功- 纠纷中';
                $StateNum = 6;
                break;
            case 6:
                $order_state = '充值失败-订单关闭';
                $StateNum = 5;
                break;
            case 7:
                $order_state = '充值匹配中-等待开单';
                $StateNum = 2;
                break;
            default:
                $order_state = '未知状态';
                $StateNum = 3;
                break;
        }

        return [
            $StateNum,
            $order_state
        ];
    }
}

//订异步回调
if (isset($_GET['Callback'])) {
    include_once '../../../includes/fun.global.php';
    global $_QET;
    test(['out_order_id|e', 'order_id|e', 'status|e']);
    $DB = SQL::DB();
    $Order = $DB->get('order', '*', [
        'order_id' => $_QET['order_id'],
    ]);

    if (!$Order) {
        dies(-1, '数据获取失败！');
    }

    $Sheau = $DB->get('shequ', [
        '[>]goods' => ['id' => 'sqid']
    ], [
        'password',
    ]);
    if (!$Sheau) {
        dies(-1, '对接配置信息获取失败，当前站点可能已经删除了此订单对接货源的配置信息！');
    }

    $New = new shiquanyi();

    $Res = $DB->update('order', [
        'return' => '订单已同步[异步更新]',
        'state' => $New->StatusCn($_QET['status'])[0],
    ], [
        'id' => $Order['id']
    ]);
    if ($Res) {
        die('success');
    } else {
        dies(-1, '数据调整失败，请重新尝试！');
    }
}