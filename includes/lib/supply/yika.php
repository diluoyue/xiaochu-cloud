<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/21 10:45
// +----------------------------------------------------------------------
// | Filename: Yika.php
// +----------------------------------------------------------------------
// | Explain: 卡卡云对接操作类
// +----------------------------------------------------------------------

namespace lib\supply;


use Medoo\DB\SQL;

class yika
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '亿卡系统',
        'image' => '../assets/img/def.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => -1,
        'ip' => -1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '站点域名',
                'tips' => '请填写包含http(s):// 和 / 的域名地址!',
                'type' => 1,
            ],
            'username' => [
                'name' => '用户编号',
                'tips' => '请填写对接站的用户编号!',
                'type' => 1,
            ],
            'password' => [
                'name' => '对接密钥',
                'tips' => '请填写好对接站的对接密钥!',
                'type' => 1,
            ],
            'secret' => [
                'name' => '交易密码',
                'tips' => '请填写交易密码,对接下单时使用',
                'type' => 1,
            ],
            'pattern' => [
                'name' => '目录CID',
                'tips' => '请填写需要对接的目录CID参数',
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
            'type' => [
                'type' => 1,
                'name' => '发货方式',
                'reminder' => '1卡密，2代刷'
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
        global $date;
        $DataPost = [
            'customerid' => $TypeSupply['username'],
            'orderno' => $Order['order_id'],
            'sign' => md5($TypeSupply['username'] . $Order['order_id'] . $TypeSupply['password'])
        ];

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::Curl($TypeSupply['url'] . 'api.php/buyer/orderInfo', $DataPost);

        $DataCurl = json_decode($DataCurl, TRUE);

        if (empty($DataCurl) || $DataCurl['code'] != 1000) {
            return [];
        }

        //补卡措施
        $DataCurl = $DataCurl['data'];
        if (isset($DataCurl['cards']) && count($DataCurl['cards']) >= 1) {
            //有卡
            $DB = SQL::DB();
            $TokenGet = $DB->count('token', [
                    'order' => $Order['order'],
                ]) - 0;
            if ($TokenGet < count($DataCurl['cards'])) {
                //补卡
                $SQL = [];
                foreach ($DataCurl['cards'] as $v) {
                    if (empty($v['card_no']) && empty($v['card_password'])) {
                        continue;
                    }
                    $SQL[] = [
                        'uid' => $Order['uid'],
                        'gid' => $Order['gid'],
                        'code' => json_decode($Order['input'], true)[0],
                        'token' => ($v['card_no'] ? $v['card_no'] . ' - ' : '') . $v['card_password'],
                        'ip' => $Order['ip'],
                        'order' => $Order['order'],
                        'endtime' => $date,
                        'addtime' => $date,
                    ];
                }
                $DB->insert('token', $SQL);
            }
        }

        switch ((int)$DataCurl['ostatus']) {
            case 1:
                $order_state = '等待处理';
                $StateNum = 2;
                break;
            case 2:
                $order_state = '处理中';
                $StateNum = 4;
                break;
            case 3:
                $order_state = '充值成功';
                $StateNum = 1;
                break;
            case 4:
                $order_state = '充值失败';
                $StateNum = 3;
                break;
            case 5:
                $order_state = '已退款';
                $StateNum = 5;
                break;
            default:
                $order_state = '未知状态';
                $StateNum = 3;
                break;
        }
        return [
            'ApiType' => 1,
            'ApiNum' => $DataCurl['ocount'],
            'ApiTime' => date('Y-m-d H:i:s', $DataCurl['dualTime'] ?? $DataCurl['time']),
            'ApiInitial' => '无',
            'ApiPresent' => '无',
            'ApiState' => $order_state,
            'ApiError' => $DataCurl['retinfo'] ?? '',
            'ApiStateNum' => $StateNum,
        ];
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
            'customerid' => $TypeSupply['username'],
            'goodsid' => $GoodsData['gid'],
            'quantity' => ($OrderData['num'] * $Goods['quantity']),
            'tradepassword' => $TypeSupply['secret'],
            'mark' => 'Api对接下单',
            'sign' => md5($TypeSupply['username'] . $GoodsData['gid'] . $TypeSupply['password'])
        ];

        if ($GoodsData['type'] == 1) {
            //发卡商品
            $url = 'api.php/buyer/buyCardGoodOrder';
        } else {
            //代充商品
            $url = 'api.php/buyer/buyGoodOrder';
            foreach ($InputArray as $key => $value) {
                $DataPost += [
                    'lblName' . $key => $value,
                ];
            }
        }

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . $url, $DataPost);
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

        if (!isset($DataCurl['code']) || $DataCurl['code'] != 1000) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => $DataCurl['info'] ?? '下单信息获取失败!,请检查对接配置!',
                'money' => 0,
                'order' => -1,
            ];
        }


        /**
         * 卡密转换
         */
        if (isset($DataCurl['data'][0]['kmlist'])) {
            $SQL = [];
            foreach ($DataCurl['data'][0]['kmlist'] as $v) {
                if (empty($v['card_no'])) {
                    continue;
                }
                $SQL[] = [
                    'uid' => $OrderData['uid'],
                    'gid' => $Goods['gid'],
                    'code' => $InputArray[0],
                    'token' => $v['card_no'],
                    'ip' => $OrderData['ip'],
                    'order' => $OrderData['order'],
                    'endtime' => $date,
                    'addtime' => $date,
                ];
            }
            $DB->insert('token', $SQL);
        }

        return [
            'code' => $conf['SubmitStateSuccess'],
            'docking' => 1,
            'msg' => $DataCurl['info'],
            'money' => -1,
            'order' => $DataCurl['data']['orderno'] ?? $DataCurl['data'][0]['orderno'],
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
            'alert' => $Goods['alert'],
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
        if (empty($GoodsData['gid'])) {
            return [
                'code' => -1,
                'msg' => '此商品对接参数缺失，无法完成商品状态监控！',
            ];
        }

        $Post = [
            'customerid' => $Supply['username'],
            'goodsid' => $GoodsData['gid'],
            'sign' => md5($Supply['username'] . ($GoodsData['gid'] - 0) . $Supply['password'])
        ];

        $Supply['url'] = StringCargo::UrlVerify($Supply['url']);
        $CurlData = Api::Curl($Supply['url'] . 'api.php/buyer/getGood', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['code'] !== 1000) {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['info'],
            ];
        }

        $GoodsDataApi = $CurlDataJson['data'];

        if ($GoodsDataApi['count'] == 0 && $GoodsDataApi['type'] == 2) {
            $GoodsDataApi['count'] = 99999;
        }

        /**
         * 解析数据
         */
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => $GoodsDataApi['count'],
                'state' => ($GoodsDataApi['goods_status'] != 1 ? 2 : 1),
                'money' => (float)$GoodsDataApi['money'],
                'specification' => false,
            ]
        ];
    }

    /**
     * 获取商品详情
     */
    public static function ProductDetails($Data)
    {
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'password', 'pattern'], [
            'id' => (int)$Data['sqid'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }

        $Post = [
            'customerid' => $SourceData['username'],
            'goodsid' => $Data['gid'],
            'sign' => md5($SourceData['username'] . ($Data['gid'] - 0) . $SourceData['password'])
        ];

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'api.php/buyer/getGood', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['code'] !== 1000) {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['info'],
            ];
        }

        $Goods = $CurlDataJson['data'];

        if ((int)$Goods['type'] === 1) {
            $Input[0][] = '提卡密码';
            $Input[1][] = 'input1';
        } else if ((int)$Goods['type'] === 2) {
            foreach ($Goods['tpl'] as $key => $val) {
                if ($val['type'] == 'text') {
                    $Input[0][] = $val['name'];
                    $Input[1][] = 'input' . ($key + 1);
                } else {
                    $Input[0][] = $val['name'] . '{' . $val['value'] . '}';
                    $Input[1][] = 'input' . ($key + 1);
                }
            }
            if (count($Goods['tpl']) == 0) {
                $Input[0][] = (empty($Goods['templates']) ? '下单信息' : $Goods['templates']);
                $Input[1][] = 'input1';
            }
            if ($Goods['count'] == 0) {
                $Goods['count'] = 99999;
            }
        } else {
            return [
                'code' => -1,
                'msg' => '商品详情获取失败！',
            ];
        }

        return [
            'code' => 1,
            'msg' => '对接参数自动填写成功！<br>商品进货成本：' . $Goods['money'] . '元',
            'data' => [
                'name' => $Goods['name'],
                'image' => $Goods['img'],
                'docs' => $Goods['desc'],
                'money' => $Goods['money'],
                'min' => $Goods['min'],
                'max' => $Goods['max'],
                'quota' => $Goods['count'],
                'alert' => $Goods['note'],
                'quantity' => 1,
                'extend' => [
                    'gid' => $Goods['id'],
                    'parameter' => implode(',', $Input[1]),
                    'type' => $Goods['type'],
                ],
                'units' => ((int)$Goods['type'] === 1 ? '张' : '个'),
                'input' => implode('|', $Input[0]),
            ]
        ];
    }

    public static function GoodsList($Data)
    {
        if ($Data['type'] != 2 && !empty($_SESSION['YikaGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['YikaGoodsList_' . $Data['id']] as $val) {
                $GoodsList[] = [
                    'gid' => $val['id'],
                    'name' => $val['name'] . ' - 售价：' . ($val['money'] - 0),
                ];
            }
            return [
                'code' => 1,
                'msg' => '可对接商品列表获取成功！',
                'data' => $GoodsList
            ];
        }
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'password', 'pattern'], [
            'id' => (int)$Data['id'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }

        $Post = [
            'customerid' => $SourceData['username'],
            'type' => 1,
            'word' => $SourceData['pattern'],
            'sign' => md5($SourceData['username'] . 1 . $SourceData['password']),
        ];

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . '/api.php/buyer/getGoods', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['code'] === 1000) {
            $GoodsList = [];

            $_SESSION['YikaGoodsList_' . $Data['id']] = $CurlDataJson['data'];

            foreach ($CurlDataJson['data'] as $val) {
                $GoodsList[] = [
                    'gid' => $val['id'],
                    'name' => $val['name'] . ' - 售价：' . ($val['money'] - 0),
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
            'msg' => $CurlDataJson['info'],
        ];
    }
}
