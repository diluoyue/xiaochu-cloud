<?php
// +----------------------------------------------------------------------
// | Project: xc
// +----------------------------------------------------------------------
// | Creation: 2022/7/19
// +----------------------------------------------------------------------
// | Filename: yunyeka.php
// +----------------------------------------------------------------------
// | Explain: 云夜卡
// +----------------------------------------------------------------------
namespace lib\supply;

use Medoo\DB\SQL;

class yunyeka
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '微时空/小夕云',
        'image' => '../assets/img/apps.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => -1,
        'ip' => 1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '货源地址',
                'tips' => '请填写包含http(s):// 和 / 的域名地址!',
                'type' => 1,
            ],
            'username' => [
                'name' => 'TokenID',
                'tips' => '请填写用户TokenID!',
                'type' => 1,
            ],
            'password' => [
                'name' => '对接密钥',
                'tips' => '请填写对接密匙!',
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
            ],
            'type' => [
                'type' => 1,
                'name' => '发货类型',
                'reminder' => '1=卡密商品，0=普通商品'
            ],
            'name' => [
                'type' => 1,
                'name' => '原始名称',
                'reminder' => '商品原始名称'
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
     * @param $Data
     * @return array
     * 获取可对接商品列表
     */
    public static function GoodsList($Data)
    {
        if ($Data['type'] != 2 && !empty($_SESSION['YunyekaGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['YunyekaGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['id'],
                    'name' => $v['g_name'] . ' | 售价：' . round($v['g_price'], 8),
                ];
            }
            return [
                'code' => 1,
                'msg' => '可对接商品列表获取成功！',
                'data' => $GoodsList
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

        $Post = [
            'tokenId' => $SourceData['username'],
            'tokenKey' => $SourceData['password'],
        ];
        $CurlData = Api::Curl($SourceData['url'] . 'api/goods/', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['status'] === 1) {
            $_SESSION['YunyekaGoodsList_' . $Data['id']] = $CurlDataJson['data'];
            $GoodsList = [];
            foreach ($CurlDataJson['data'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['id'],
                    'name' => $v['g_name'] . ' | 售价：' . round($v['g_price'], 8),
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

    /**
     * @param $Data
     * @return array
     * 获取商品详情
     */
    public static function ProductDetails($Data)
    {
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', '*', [
            'id' => (int)$Data['sqid'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }

        if (empty($_SESSION['YunyekaGoodsList_' . $Data['sqid']])) {
            return [
                'code' => -1,
                'msg' => '数据源获取失败，请重新获取一遍可对接商品列表！',
            ];
        }
        $Goods = $_SESSION['YunyekaGoodsList_' . $Data['sqid']][$Data['key']];
        if ($Goods['id'] != $Data['gid']) {
            return [
                'code' => -1,
                'msg' => '商品键值和数据源不匹配，请重新获取商品列表数据！',
            ];
        }
        $Params = [];
        $input = [];
        foreach ($Goods['params'] as $v) {
            $input[] = $v['name'];
            $Params[] = $v['model'];
        }
        if (count($Goods['params']) === 0) {
            $input[] = '联系QQ';
            $Goods['params_value'] = 'qq';
        }
        return [
            'code' => 1,
            'msg' => '对接参数获取成功！',
            'data' => [
                'name' => $Goods['g_name'],
                'image' => (empty($Goods['g_image']) ? ROOT_DIR . 'assets/img/logo.png' : $Goods['g_image']),
                'docs' => $Goods['g_desc'],
                'money' => round($Goods['g_price'], 8),
                'min' => 1,
                'max' => round($Goods['g_max_num'] / $Goods['g_min_num'], 0),
                'quota' => 999999,
                'quantity' => $Goods['g_min_num'],
                'extend' => [
                    'gid' => $Goods['id'],
                    'parameter' => implode('|', $Params),
                    'type' => $Goods['g_type'],
                    'name' => $Goods['g_name']
                ],
                'units' => $Goods['g_unit'] ?? '个',
                'input' => implode('|', $input),
            ]
        ];
    }

    /**
     * @param $OrderData //订单信息
     * @param $Goods //商品信息
     * @param $TypeSupply //对接货源信息
     * 提交下单信息！
     */
    public static function Submit($OrderData, $Goods, $TypeSupply)
    {
        global $date, $conf;
        $DB = SQL::DB();

        $DataPost = [];
        $InputArray = json_decode($OrderData['input'], TRUE);
        if ($Goods['specification'] == 2 && $Goods['specification_type'] == 2) {
            $InputArray = RuleSubmitParameters(json_decode($Goods['specification_spu'], TRUE), $InputArray);
        }
        $GoodsData = json_decode($Goods['extend'], true); //对接数据
        $Parameter = explode('|', $GoodsData['parameter']);
        foreach ($Parameter as $key => $value) {
            $DataPost[$value] = $InputArray[$key];
        }

        $Post = [
            'tokenId' => $TypeSupply['username'],
            'tokenKey' => $TypeSupply['password'],
            'orderNum' => (int)($OrderData['num'] * $Goods['quantity']),
            'goodsType' => (int)$GoodsData['type'],
            'goodsId' => (int)$GoodsData['gid'],
        ];

        if ((int)$GoodsData['type'] === 1) {
            $Post['qq'] = (string)$InputArray[0];
        } else {
            $Post['goodsModel'] = (string)json_encode($DataPost);
        }

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'api/order/', $Post);

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
        /**
         * 卡密转换
         */
        if (isset($DataCurl['data']['card'])) {
            $ii = 0;
            $SQL = [];
            foreach ($DataCurl['data'] as $v) {
                if (empty($v)) {
                    continue;
                }
                $SQL[] = [
                    'uid' => $OrderData['uid'],
                    'gid' => $Goods['gid'],
                    'code' => $InputArray[0],
                    'token' => (implode(',', $v)) . (empty($DataCurl['data'][$ii]['pass']) ? '' : ' | ' . $DataCurl['data'][$ii]['pass']),
                    'ip' => $OrderData['ip'],
                    'order' => $OrderData['order'],
                    'endtime' => $date,
                    'addtime' => $date,
                ];
                $ii++;
            }
            $DB->insert('token', $SQL);
        }

        if (!isset($DataCurl['status'])) {
            $Msg = '下单信息获取失败!,请检查对接配置！';
            $code = $conf['SubmitState'];
            $money = 0;
            $order = -1;
            $docking = 2;
        } else {
            $Msg = $DataCurl['message'];
            $code = ($DataCurl['status'] >= 1 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']);
            $money = 0;
            $order = ($DataCurl['status'] >= 1 ? $DataCurl['id'] : -1);
            $docking = ($DataCurl['status'] >= 1 ? 1 : 2);
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
            'orderId' => $id,
            'type' => 1
        ];

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::Curl($TypeSupply['url'] . 'api/order/query', $DataPost);

        $DataCurl = json_decode($DataCurl, TRUE);

        if (empty($DataCurl)) {
            return [];
        }

        if ($DataCurl['status'] !== 1) {
            return false;
        }
        $Data = $DataCurl['data'][0];
        switch ((int)$Data['order_code']) {
            case 0:
                $order_state = '待处理';
                $StateNum = 2;
                break;
            case 1:
                $order_state = '处理中';
                $StateNum = 4;
                break;
            case 2:
                $order_state = '有异常';
                $StateNum = 3;
                break;
            case 90:
                $order_state = '已退款';
                $StateNum = 5;
                break;
            case 91:
                $order_state = '退单中';
                $StateNum = 3;
                break;
            case 92:
                $order_state = '已完成';
                $StateNum = 1;
                break;
            default :
                $order_state = '未知' . $Data['order_code'];
                $StateNum = 3;
                break;
        }


        return [
            'ApiType' => 1,
            'ApiNum' => $Data['order_num'],
            'ApiTime' => $Data['order_date'],
            'ApiInitial' => $Data['order_start_num'],
            'ApiPresent' => $Data['order_end_num'],
            'ApiState' => $order_state,
            'ApiError' => ($Data['order_card'] <> null ? $Data['order_card'] : ''),
            'ApiStateNum' => $StateNum,
        ];
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
        $Supply['url'] = StringCargo::UrlVerify($Supply['url']);

        $Post = [
            'tokenId' => $Supply['username'],
            'tokenKey' => $Supply['password'],
            'goodsName' => $GoodsData['name'],
        ];

        $CurlData = Api::Curl($Supply['url'] . 'api/goods/', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['status'] !== 1 || count($CurlDataJson['data']) === 0) {
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
        $GoodsDataApi = false;
        foreach ($CurlDataJson['data'] as $v) {
            if ($v['id'] == $GoodsData['gid']) {
                $GoodsDataApi = $v;
            }
        }

        if (!$GoodsDataApi) {
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
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => ($Goods['quota'] <= 0 ? 999999 : $Goods['quota']),
                'state' => 1,
                'money' => round((float)$GoodsDataApi['g_price'] / $GoodsDataApi['g_min_num'], 8),
                'specification' => false,
            ]
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
}