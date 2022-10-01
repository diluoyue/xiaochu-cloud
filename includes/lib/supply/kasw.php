<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/21 15:34
// +----------------------------------------------------------------------
// | Filename: kasw.php
// +----------------------------------------------------------------------
// | Explain: 卡商网对接
// +----------------------------------------------------------------------

namespace lib\supply;


use Medoo\DB\SQL;

class kasw
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '卡商网',
        'image' => '../assets/img/kasw.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => '可对接商品填写说明：可填写<font color="red">我要进货</font>界面下商品名称后面的编号<br>如：<font color="red">腾讯视频vip会员1天-仅充QQ号(41753)</font>，只需要取出<font color="red">41753</font>即可！<br>多个商品编号用符号 | 进行分割！,如：123456|789456|3333 等',
        'ip' => -1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '站点域名',
                'tips' => '请填写包含http(s):// 和 / 的域名地址!',
                'type' => 1,
            ],
            'username' => [
                'name' => '商家编号',
                'tips' => '请填写对接站的用户id!',
                'type' => 1,
            ],
            'password' => [
                'name' => '接口密钥',
                'tips' => '请填写在后台安全设置内获取的安全密钥!',
                'type' => 1,
            ],
            'secret' => [
                'name' => '可对接商品',
                'tips' => '请填写可对接的商品ID，中间用符号 | 分割！',
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

    public static function Query($Order, $TypeSupply)
    {
        $DataPost = [
            'customer_id' => $TypeSupply['username'],
            'timestamp' => time(),
            'order_id' => $Order['order_id']
        ];

        $DataPost = array_merge([
            'sign' => self::getKkySign($DataPost, $TypeSupply['password'])
        ], $DataPost);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::Curl($TypeSupply['url'] . 'api/order', $DataPost);
        $DataCurl = json_decode($DataCurl, TRUE);
        if (empty($DataCurl)) {
            return [];
        }
        if ($DataCurl['code'] === 'ok') {
            $Data = $DataCurl['data'];
            switch ((int)$Data['state']) {
                case 100:
                    $order_state = '等待发货';
                    $StateNum = 4;
                    break;
                case 200:
                    $order_state = '交易成功';
                    $StateNum = 1;
                    break;
                case 500:
                    $order_state = '交易失败';
                    $StateNum = 3;
                    break;
                default:
                    $order_state = '未知状态';
                    $StateNum = 3;
                    break;
            }
            return [
                'ApiType' => 1,
                'ApiNum' => $Data['quantity'],
                'ApiTime' => $Data['created_at'],
                'ApiInitial' => ($Data['progress_init'] === null ? '无' : $Data['progress_init']),
                'ApiPresent' => ($Data['progress_now'] === null ? '无' : $Data['progress_now']),
                'ApiState' => $order_state,
                'ApiError' => (!empty($Data['recharge_info']) ? $Data['recharge_info'] : ''),
                'ApiStateNum' => $StateNum,
            ];
        }

        return [];
    }

    public static function Submit($OrderData, $Goods, $TypeSupply)
    {
        global $conf, $date;
        $DB = SQL::DB();
        $InputArray = json_decode($OrderData['input'], TRUE);
        if ($Goods['specification'] == 2 && $Goods['specification_type'] == 2) {
            $InputArray = RuleSubmitParameters(json_decode($Goods['specification_spu'], TRUE), $InputArray);
        }
        $GoodsData = json_decode($Goods['extend'], true); //对接数据

        $DataPost = [
            'customer_id' => $TypeSupply['username'],
            'timestamp' => time(),
            'product_id' => $GoodsData['gid'],
            'quantity' => $OrderData['num'],
            'recharge_account' => $InputArray[0],
        ];
        if ($GoodsData['parameter'] != -1) {
            foreach (explode(',', $GoodsData['parameter']) as $k => $v) {
                $DataPost['recharge_template_input_items[' . $v . ']'] = $InputArray[$k];
            }
        }
        $DataPost = array_merge($DataPost, [
            'sign' => self::getKkySign($DataPost, $TypeSupply['password']),
        ]);
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'api/buy', $DataPost);
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
        if ($DataCurl['code'] !== 'ok') {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => $DataCurl['message'],
                'money' => 0,
                'order' => 0,
            ];
        }

        if (count($DataCurl['data']['cards']) >= 1) {
            $SQL = [];
            foreach ($DataCurl['data']['cards'] as $v) {
                if (empty($v)) {
                    continue;
                }
                $SQL[] = [
                    'uid' => $OrderData['uid'],
                    'gid' => $Goods['gid'],
                    'code' => $InputArray[0],
                    'token' => '卡号：' . $v['card_no'] . (!empty($v['card_password']) ? ' 卡密：' . $v['card_password'] : ''),
                    'ip' => $OrderData['ip'],
                    'order' => $OrderData['order'],
                    'endtime' => $date,
                    'addtime' => $date,
                ];
            }
            $DB->insert('token', $SQL);
        }

        if (!empty($CurlData['data']['recharge_url'])) {
            $DB->update('order', [
                'remark' => '卡密充值地址：' . $CurlData['data']['recharge_url'],
            ], [
                'id' => $OrderData['id']
            ]);
        }

        return [
            'code' => $conf['SubmitStateSuccess'],
            'docking' => 1,
            'msg' => '下单成功,共消耗：' . $DataCurl['data']['total_price'] . '元！',
            'money' => $DataCurl['data']['total_price'],
            'order' => $DataCurl['data']['order_id'],
        ];
    }

    private static function getKkySign($param, $userkey)
    {
        ksort($param);
        reset($param);
        $text = '';
        foreach ($param as $key => $val) {
            if (empty($val)) continue;
            $text .= $key . '' . $val;
        }
        return md5($userkey . $text);
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
            'alert' => '',
            'docs' => '',
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
        $Post = [
            'customer_id' => $Supply['username'],
            'timestamp' => time(),
            'product_id' => $GoodsData['gid'],
        ];

        $Post = array_merge($Post, [
            'sign' => self::getKkySign($Post, $Supply['password']),
        ]);

        $Supply['url'] = StringCargo::UrlVerify($Supply['url']);
        $CurlData = Api::Curl($Supply['url'] . 'api/product', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ($CurlDataJson['code'] !== 'ok' || (int)$CurlDataJson['data']['supply_state'] !== 1) {
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
        $GoodsDataApi = $CurlDataJson['data'];
        /**
         * 解析数据
         */
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => ($GoodsDataApi['stock_state'] !== 1 ? 0 : ($Goods['quota'] <= 0 ? 999999 : $Goods['quota'])),
                'state' => 1,
                'money' => (float)$GoodsDataApi['price'],
                'specification' => false,
            ]
        ];
    }

    public static function ProductDetails($Data)
    {
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'password'], [
            'id' => (int)$Data['sqid'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }

        $Post = [
            'customer_id' => $SourceData['username'],
            'timestamp' => time(),
            'product_id' => $Data['gid'],
        ];

        $Post = array_merge($Post, [
            'sign' => self::getKkySign($Post, $SourceData['password']),
        ]);

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'api/product', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ($CurlDataJson['code'] !== 'ok') {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['message'],
            ];
        }

        $Goods = $CurlDataJson['data'];
        if ((int)$Goods['supply_state'] !== 1) {
//            return [
//                'code' => -1,
//                'msg' => '此商品已维护或已下架！',
//            ];
        }

        if ((int)$Goods['stock_state'] !== 1) {
//            return [
//                'code' => -1,
//                'msg' => '此商品已断货！',
//            ];
        }

        $Num = explode('-', $Goods['valid_purchasing_quantity']);
        $Input = [];
        if ($Goods['type'] === 1) {
            //充值类商品
            $Post = [
                'customer_id' => $SourceData['username'],
                'timestamp' => time(),
                'product_id' => $Data['gid'],
            ];

            $Post = array_merge($Post, [
                'sign' => self::getKkySign($Post, $SourceData['password']),
            ]);
            $Params = Api::Curl($SourceData['url'] . 'api/product/recharge-params', $Post);
            $CurlParams = json_decode($Params, true);
            if (empty($CurlParams)) {
                return [
                    'code' => -1,
                    'msg' => '商品充值参数获取失败！',
                ];
            }
            if ($CurlParams['code'] === 'ok') {
                foreach ($CurlParams['data']['recharge_params'] as $v) {
                    if ($v['type'] === 'text' || $v['type'] === 'password') {
                        $Input[0][] = $v['name'];
                    } else {
                        $Input[0][] = $v['name'] . '{' . $v['options'] . '}';
                    }
                    $Input[1][] = $v['name'];
                }
                if (count($CurlParams['data']['recharge_params']) === 0) {
                    $Input[0][] = $CurlParams['data']['recharge_account_label'];
                    $Input[1][] = -1;
                }
            } else {
                return [
                    'code' => -1,
                    'msg' => '商品充值参数获取失败！',
                ];
            }
        } else {
            $Input[0][] = '下单信息';
            $Input[1][] = '-1';
        }

        return [
            'code' => 1,
            'msg' => '对接参数自动填写成功！',
            'data' => [
                'name' => $Goods['name'],
                'money' => $Goods['price'],
                'min' => $Num[0],
                'max' => $Num[1],
                'quota' => 999999,
                'quantity' => 1,
                'extend' => [
                    'gid' => $Goods['id'],
                    'parameter' => implode(',', $Input[1]),
                ],
                'units' => $Goods['unit'],
                'input' => implode('|', $Input[0]),
            ]
        ];

    }

    public static function GoodsList($Data)
    {
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'password', 'secret'], [
            'id' => (int)$Data['id'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        if (empty($SourceData['secret'])) {
            return [
                'code' => -1,
                'msg' => '尚未设置可对接商品列表！,请打开货源列表添加！',
            ];
        }

        $GoodsList = [];
        foreach (explode('|', $SourceData['secret']) as $v) {
            $GoodsList[] = [
                'gid' => $v,
                'name' => $v . ' - 点击查看详情',
                'cid' => $v,
            ];
        }

        return [
            'code' => 1,
            'msg' => '列表获取成功',
            'data' => $GoodsList
        ];

    }
}
