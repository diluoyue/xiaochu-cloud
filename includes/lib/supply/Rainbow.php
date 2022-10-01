<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/20 15:49
// +----------------------------------------------------------------------
// | Filename: Rainbow.php
// +----------------------------------------------------------------------
// | Explain: 彩虹代刷对接操作类
// +----------------------------------------------------------------------

namespace lib\supply;


use Medoo\DB\SQL;

class Rainbow
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '彩虹系统',
        'image' => '../assets/img/ds.jpg',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => '可填写彩虹的分站登录账号和密码来进行对接！',
        'ip' => -1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '站点域名',
                'tips' => '请填写包含http(s):// 和 / 的域名地址!',
                'type' => 1,
            ],
            'username' => [
                'name' => '登录账号',
                'tips' => '分站的登录账号',
                'type' => 1,
            ],
            'password' => [
                'name' => '登录密码',
                'tips' => '分站的登录密码!',
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
     * @param $id
     * @param $TypeSupply
     */
    public static function Query($id, $TypeSupply)
    {
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $Data = Api::Curl($TypeSupply['url'] . 'api.php?act=search&id=' . $id);
        if (empty($Data)) {
            return [];
        }
        $Data = json_decode($Data, TRUE);

        if ($Data['code'] >= 0) {
            switch ((int)$Data['status']) {
                case 1:
                    $Data['stduy'] = '已完成';
                    $StateNum = 1;
                    break;
                case 2:
                    $Data['stduy'] = '处理中';
                    $StateNum = 4;
                    break;
                case 3:
                    $Data['stduy'] = '异常';
                    $StateNum = 3;
                    break;
                case 4:
                    $Data['stduy'] = '已退款';
                    $StateNum = 5;
                    break;
                case 5:
                    $Data['stduy'] = '已撤回';
                    $StateNum = 3;
                    break;
                default:
                    $Data['stduy'] = '待处理';
                    $StateNum = 2;
                    break;
            }

            return [
                'ApiType' => 1,
                'ApiNum' => '无',
                'ApiTime' => '无',
                'ApiInitial' => '无',
                'ApiPresent' => '无',
                'ApiState' => $Data['stduy'],
                'ApiError' => '',
                'ApiStateNum' => $StateNum,
            ];
        }

        return [];
    }

    public static function Submit($OrderData, $Goods, $TypeSupply)
    {
        global $date, $conf;
        $DB = SQL::DB();

        $DataPost = [];
        $InputArray = json_decode($OrderData['input'], TRUE);
        if ($Goods['specification'] == 2 && $Goods['specification_type'] == 2) {
            $InputArray = RuleSubmitParameters(json_decode($Goods['specification_spu'], TRUE), $InputArray);
        }

        $i = 1;
        foreach ($InputArray as $value) {
            $DataPost += [
                'input' . $i => $value,
            ];
            $i++;
        }

        $GoodsData = json_decode($Goods['extend'], true); //对接数据

        $DataPost = array_merge([
            'tid' => $GoodsData['gid'],
            'user' => $TypeSupply['username'],
            'pass' => $TypeSupply['password'],
            'num' => $OrderData['num'],
        ], $DataPost);


        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'api.php?act=pay', $DataPost);

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

        if (isset($DataCurl['message'])) {
            $DataCurl['msg'] = $DataCurl['message'];
        }

        if (!isset($DataCurl['code']) || $DataCurl['code'] != 0) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => (empty($DataCurl['msg']) ? '下单信息获取失败!,请检查对接配置!' : $DataCurl['msg']),
                'money' => 0,
                'order' => -1,
            ];
        }

        if (!empty($DataCurl['kmdata']) && count($DataCurl['kmdata']) >= 1) {
            $SQL = [];
            foreach ($DataCurl['kmdata'] as $v) {
                $SQL[] = [
                    'uid' => $OrderData['uid'],
                    'gid' => $OrderData['gid'],
                    'code' => json_decode($OrderData['input'], TRUE)[0],
                    'token' => $v['card'] . (!empty($v['pass']) ? '，使用密码：' . $v['pass'] : ''),
                    'ip' => $OrderData['ip'],
                    'order' => $OrderData['order'],
                    'endtime' => $date,
                    'addtime' => $date,
                ];
            }
            $DB->insert('token', $SQL);
        }

        return [
            'code' => ($DataCurl['code'] >= 0 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']),
            'docking' => ($DataCurl['code'] >= 0 ? 1 : 2),
            'msg' => $DataCurl['msg'],
            'money' => 0,
            'order' => $DataCurl['orderid'],
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
            'user' => $Supply['username'],
            'pass' => $Supply['password'],
            'tid' => (int)$GoodsData['gid'],
        ];
        $CurlData = Api::Curl($Supply['url'] . 'api.php?act=goodsdetails', $Post);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['code'] !== 0 || (int)$CurlDataJson['data']['close'] !== 0) {
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
        if ($GoodsDataApi['value'] <= 1) {
            $GoodsDataApi['value'] = 1;
        }

        if ($GoodsDataApi['stock'] === '' || $GoodsDataApi['stock'] === null) {
            $GoodsDataApi['stock'] = 9999;
        }
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => $GoodsDataApi['stock'],
                'state' => 1,
                'money' => $GoodsDataApi['price'] / $GoodsDataApi['value'],
                'specification' => false,
            ]
        ];
    }

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
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);

        $Post = [
            'user' => $SourceData['username'],
            'pass' => $SourceData['password'],
            'tid' => (int)$Data['gid'],
        ];

        $CurlData = Api::Curl($SourceData['url'] . 'api.php?act=goodsdetails', $Post);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        $Goods = $CurlDataJson['data'];
        if ((int)$Goods['close'] !== 0) {
//            return [
//                'code' => -1,
//                'msg' => '此商品已关闭下单！',
//            ];
        }

        if ($CurlDataJson['code'] === 0) {

            $Input = explode('|', $Goods['input'] . (empty($Goods['inputs']) ? '' : '|' . $Goods['inputs']));
            $parameter = [];
            foreach ($Input as $k => $v) {
                $parameter[$k] = 'input' . $k;
            }

            if ((!empty($Goods['shopimg']) && strpos($Goods['shopimg'], 'http') === false)) {
                $Goods['shopimg'] = $SourceData['url'] . $Goods['shopimg'];
            }

            if (empty($Goods['max'])) {
                $Goods['max'] = 9999;
            }

            if ($Goods['min'] <= 0) {
                $Goods['min'] = 1;
            }

            if ((int)$Goods['value'] <= 1) {
                $Goods['value'] = 1;
            }

            if ($Goods['stock'] === '' || $Goods['stock'] === null) {
                $Goods['stock'] = 9999;
            } else {
                $Goods['stock'] = $Goods['stock'];
            }

            return [
                'code' => 1,
                'msg' => '对接参数自动填写成功！',
                'data' => [
                    'name' => $Goods['name'],
                    'image' => ImageUrl($Goods['shopimg']),
                    'docs' => $Goods['desc'],
                    'money' => (float)$Goods['price'],
                    'min' => $Goods['min'],
                    'max' => $Goods['max'],
                    'quota' => $Goods['stock'],
                    'quantity' => $Goods['value'],
                    'extend' => [
                        'gid' => $Goods['tid'],
                        'parameter' => implode(',', $parameter),
                    ],
                    'input' => $Goods['input'] . (empty($Goods['inputs']) ? '' : '|' . $Goods['inputs']),
                ]
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

    public static function GoodsList($Data)
    {
        if ($Data['type'] != 2 && !empty($_SESSION['ChaiHongGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['ChaiHongGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['tid'],
                    'name' => $v['name'] . ' - ' . ((int)$v['close'] === 0 ? '正常对接' : '关闭下单') . ' - ' . $v['price'] . '元1份',
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

        $PostData = [
            'user' => $SourceData['username'],
            'pass' => $SourceData['password'],
        ];

        $CurlData = Api::Curl($SourceData['url'] . 'api.php?act=goodslist', $PostData);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] >= 0) {

            $_SESSION['ChaiHongGoodsList_' . $Data['id']] = $CurlDataJson['data'];

            $GoodsList = [];
            foreach ($CurlDataJson['data'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['tid'],
                    'name' => $v['name'] . ' - ' . ((int)$v['close'] === 0 ? '正常对接' : '关闭下单') . ' - ' . $v['price'] . '元1份',
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
}
