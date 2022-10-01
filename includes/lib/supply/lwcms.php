<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/31 19:06
// +----------------------------------------------------------------------
// | Filename: YunBao.php
// +----------------------------------------------------------------------
// | Explain: 云宝发卡对接(2.0)
// +----------------------------------------------------------------------

namespace lib\supply;

use Medoo\DB\SQL;

class lwcms
{

    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '乐商网(LWSCMS)',
        'image' => '../assets/img/lwfaka.png',
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
                'name' => '登陆账号',
                'tips' => '请填写登陆账号',
                'type' => 1,
            ],
            'password' => [
                'name' => '对接密钥',
                'tips' => '请将对接密钥填写完整！',
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
            'Type' => [
                'type' => 1,
                'name' => '商品类型',
                'reminder' => '1，发卡商品，2代充商品'
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

    public static function getKkySign($Data)
    {
        return md5('user=' . $Data['user'] . '&&pass=' . $Data['pass']);
    }

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
            'units' => '个',
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

    public static function CommodityStatus($Goods, $SourceData)
    {
        $GoodsData = json_decode($Goods['extend'], true);
        if (empty($GoodsData['gid'])) {
            return [
                'code' => -1,
                'msg' => '此商品对接参数缺失，无法完成商品状态监控！',
            ];
        }

        $Post = [
            'username' => $SourceData['username'],


            'goods_id' => $GoodsData['gid']
        ];
        $Post['sign'] = self::getSign($Post, $SourceData['password']);

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'api/goods/apiGetGood', $Post);


        $CurlDataJson = json_decode($CurlData, true);


        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] !== 0) {
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

        if ($CurlDataJson['data']['is_automatic'] == 1) {
            $Count = self::InventoryAcquisition($GoodsData['gid'], $SourceData['id']);
            if ($Count['code'] === 1) {
                $inventory = $Count['count'];
            } else {
                $inventory = 0;
            }

            if ($Count['count'] == -1) {
                $inventory = 999;
            }
        } elseif ($CurlDataJson['data']['is_automatic'] == 2) {
            $Count = self::InventoryAcquisition($GoodsData['gid'], $SourceData['id']);

            if ($Count['count'] > 0) {
                $inventory = $Count['count'];
            }
            if ($Count['count'] == 0) {
                $inventory = 0;
            }
            if ($Count['count'] == -1) {
                $inventory = 999;
            }
        } else {
            $inventory = 999;
        }


        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => $inventory,
                'state' => 1,
                'money' => (float)$CurlDataJson['data']['price'],
                'specification' => false,
            ]
        ];
    }

    public static function Query($Order, $TypeSupply)
    {
        global $date;
        $DataPost = [
            'username' => $TypeSupply['username'],
            'order_no' => $Order['order_id'],
        ];
        $DataPost['sign'] = self::getSign($DataPost, $TypeSupply['password']);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $Data = Api::Curl($TypeSupply['url'] . 'api/goods/getOrderStatus', $DataPost);
        $Data = json_decode($Data, true);


        if (empty($Data)) {
            return [];
        }

        if ($Data['code'] === 0) {
            $StateNum = 1;
            switch ((int)$Data['data']['status']) {
                case 1:
                    $State = '待付款';
                    $StateNum = 3;
                    break;
                case 2:

                    $State = '已付款';
                    $StateNum = 4;
                    if ($Data['data']['is_automatic'] == 1) {
                        $State = '已发货';
                        $StateNum = 1;
                    }


                    break;
                case 9:
                    $State = '处理中';
                    $StateNum = 4;
                    break;
                case 3:
                case 4:
                    $State = '待发货';
                    $StateNum = 2;
                    break;
                case 5:
                    $State = '已发货';
                    $StateNum = 1;
                    break;
                case 6:
                    $State = '退款中';
                    $StateNum = 5;
                    break;
                case 7:
                    $State = '已退款';
                    $StateNum = 5;
                    break;

                default:
                    $State = '未知状态';
                    $StateNum = 3;
                    break;
            }

            return [
                'ApiType' => 1,
                'ApiNum' => '无',
                'ApiTime' => '无',
                'ApiInitial' => '无',
                'ApiPresent' => '无',
                'ApiState' => $State,
                'ApiError' => '无',
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
            'username' => $TypeSupply['username'],
            'gid' => $GoodsData['gid'],
            'num' => $OrderData['num'],
            'money' => (string)(round($OrderData['money'], 2) + 0.1),
            'order' => $OrderData['order'],
        ];


        if ($GoodsData['Type'] == 1) {

        } else {
            $DataPost['beizhu'] = base64_encode(json_encode($InputArray));
        }

        $DataPost['sign'] = self::getSign($DataPost, $TypeSupply['password']);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'api/goods/pay', $DataPost);


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

        if (!isset($DataCurl['code']) || $DataCurl['code'] != 0) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => (empty($DataCurl['message']) ? '下单信息获取失败!,请检查对接配置!' : $DataCurl['message']),
                'money' => 0,
                'order' => -1,
            ];
        }


        if (isset($DataCurl['data']['kami']) && count($DataCurl['data']['kami']) >= 1) {
            $SQL = [];
            foreach ($DataCurl['data']['kami'] as $v) {
                if (empty($v)) {
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


        if (!isset($DataCurl['code'])) {
            $Msg = '下单信息获取失败!,请检查对接配置！';
            $code = $conf['SubmitState'];
            $money = 0;
            $order = -1;
            $docking = 2;
        } else {
            $Msg = $DataCurl['message'];
            $code = ($DataCurl['code'] == 0 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']);
            $money = ($DataCurl['code'] == 0 ? -1 : 0);
            $order = ($DataCurl['code'] == 0 ? $DataCurl['data']['pay_no'] : -1);
            $docking = ($DataCurl['code'] == 0 ? 1 : 2);
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
     * @param $Gid
     * @param $Id
     * @return array
     * 查询库存
     */
    public static function InventoryAcquisition($Gid, $Id)
    {
        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'password'], [
            'id' => (int)$Id,
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $Post = [

            'username' => $SourceData['username'],
            'goods_id' => $Gid,

        ];
        $Post['sign'] = self::getSign($Post, $SourceData['password']);

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'api/goods/apiGetGood', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }


        if ($CurlDataJson['code'] === 0) {
            return [
                'code' => 1,
                'msg' => '库存获取成功',
                'count' => (int)$CurlDataJson['data']['goods_kucun']
            ];
        }

        return [
            'code' => -1,
            'msg' => '库存获取失败！' . $CurlDataJson['message'],
        ];
    }

    /**
     * 获取商品详情
     */
    public static function ProductDetails($Data)
    {
        if (empty($_SESSION['LwcmsGoodsList_' . $Data['sqid']])) {
            return [
                'code' => -1,
                'msg' => '数据源获取失败，请重新获取一遍可对接商品列表！',
            ];
        }
        $Goods = $_SESSION['LwcmsGoodsList_' . $Data['sqid']][$Data['key']];

        if ($Goods['goods_id'] != $Data['gid']) {
            return [
                'code' => -1,
                'msg' => '商品键值和数据源不匹配，请重新获取商品列表数据！',
            ];
        }
        if ($Goods['is_automatic'] == 1) {
            //卡密商品
            $Count = self::InventoryAcquisition($Data['gid'], $Data['sqid']);


            if ($Count['code'] === 1) {
                $inventory = $Count['count'];
            } else {
                $inventory = 0;
            }

            if ($Count['count'] == -1) {
                $inventory = 999;
            }

        } elseif ($Goods['is_automatic'] == 2) {
            $Count = self::InventoryAcquisition($Data['gid'], $Data['sqid']);

            if ($Count['count'] > 0) {
                $inventory = $Count['count'];
            }

            if ($Count['count'] == 0) {
                $inventory = 0;
            }
            if ($Count['count'] == -1) {
                $inventory = 999;
            }

        } else {
            //快充商品
            $inventory = 999;
        }


        $template = [];
        $Goods['inventory'] = $inventory;
        if ($Goods['is_automatic'] == 2) {
            //卡密商品
            if (!empty($Goods['template_content'])) {
                $template_content = explode("----", $Goods['template_content']);
                foreach ($template_content as $v) {
                    $template[] = @explode(":", $v)['1'];
                }
                $input = implode('|', $template);
            } else {
                $input = '下单备注';
            }
        } else {
            $input = '下单备注';
        }
        return [
            'code' => 1,
            'msg' => '对接参数自动填写成功！<br>商品进货成本：1份 ' . round($Goods['money'], 8) . '元',
            'data' => [
                'name' => $Goods['goods_name'],
                'image' => $Goods['goods_thumbnail'] ?? '/assets/img/logo.png',
                'docs' => @$Goods['goods_describe'],
                'money' => round($Goods['money'], 8),
                'min' => 1,
                'max' => 1,
                'alert' => '',
                'quota' => $Goods['inventory'],
                'quantity' => 1,
                'extend' => [
                    'gid' => $Goods['goods_id'],
                    'parameter' => $Goods['beizhutrue'],
                    'Type' => $Goods['is_automatic'],
                ],
                'input' => $input,
            ]
        ];
    }

    static private function getSign($param, $userkey)
    {
        ksort($param);
        $sign_str = $userkey;
        foreach ($param as $kcc => $vcc) {
            $sign_str = $sign_str . $kcc . $vcc;
        }
        $params['sign'] = md5($sign_str);


        return $params['sign'];
    }

    public static function GoodsList($Data)
    {
        if ($Data['type'] != 2 && !empty($_SESSION['LwcmsGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['LwcmsGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [

                    'name' => $v['goods_name'] . ' - ' . round($v['money'], 8) . '元',

                    'cid' => $v['goods_categoryid'],
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
            'id' => (int)$Data['id'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $Post = [

            'username' => $SourceData['username'],
            'is_automatic' => 0,

        ];
        $Post['sign'] = self::getSign($Post, $SourceData['password']);

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'api/goods/getGoods', $Post);
        $CurlDataJson = json_decode($CurlData, true);


        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ($CurlDataJson['code'] !== 0) {
            return [
                'code' => -1,
                'msg' => '数据获取失败！' . $CurlDataJson['message'],
            ];
        }

        $GoodsList = [];

        $_SESSION['LwcmsGoodsList_' . $Data['id']] = $CurlDataJson['data'];

        foreach ($CurlDataJson['data'] as $v) {
            $GoodsList[] = [
                'gid' => $v['goods_id'],
                'name' => $v['goods_name'] . ' - ' . round($v['money'], 8) . '元',
                'cid' => $v['goods_categoryid'],
            ];
        }
        return [
            'code' => 1,
            'msg' => '可对接商品列表获取成功！',
            'data' => $GoodsList
        ];

    }
}
