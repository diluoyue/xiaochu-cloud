<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/14 9:57
// +----------------------------------------------------------------------
// | Filename: skyun.php
// +----------------------------------------------------------------------
// | Explain: 时空云社区对接文件
// +----------------------------------------------------------------------

namespace lib\supply;

use Medoo\DB\SQL;

class skyun
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '时空云社区',
        'image' => '../assets/img/sky.png',
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
                'name' => '登录账号',
                'tips' => '请填写此社区的登录账号！',
                'type' => 1,
            ],
            'password' => [
                'name' => '安全密匙',
                'tips' => '请填写好此社区的安全密钥!',
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

    public static function Query($id, $TypeSupply)
    {
        $DataPost = [
            'api_user' => $TypeSupply['username'],
            'api_pwd' => $TypeSupply['password'],
            'id' => $id
        ];

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);
        $DataCurl = Api::Curl($TypeSupply['url'] . '.skysq.top/api/user/order/query', $DataPost);
        if (empty($DataCurl)) {
            return false;
        }
        $DataCurl = json_decode($DataCurl, TRUE);
        if ($DataCurl['status'] <> 0) {
            return false;
        }
        $Data = $DataCurl['data'][0];

        switch ($Data['status']) {
            case 0:
                $order_state = '待处理';
                $StateNum = 2;
                break;
            case 1:
                $order_state = '处理中';
                $StateNum = 4;
                break;
            case 3:
                $order_state = '已退单';
                $StateNum = 5;
                break;
            case 4:
                $order_state = '有异常';
                $StateNum = 3;
                break;
            case 5:
                $order_state = '补单中';
                $StateNum = 4;
                break;
            case 6:
                $order_state = '已完成';
                $StateNum = 1;
                break;
            case 7:
                $order_state = '已退款';
                $StateNum = 5;
                break;
            case 8:
                $order_state = '待补单';
                $StateNum = 4;
                break;
            case 9:
                $order_state = '退款中';
                $StateNum = 3;
                break;
            default:
                $order_state = '未知状态';
                $StateNum = 3;
                break;
        }

        return [
            'ApiType' => 1,
            'ApiNum' => $Data['num'],
            'ApiTime' => $Data['created_at'],
            'ApiInitial' => $Data['start_num'],
            'ApiPresent' => $Data['now_num'],
            'ApiState' => $order_state,
            'ApiError' => ($Data['remark'] <> null ? $Data['remark'] : ''),
            'ApiStateNum' => $StateNum,
        ];
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
                'value' . $i => $value,
            ];
            $i++;
        }

        $GoodsData = json_decode($Goods['extend'], true); //对接数据

        $DataPost = array_merge([
            'api_user' => $TypeSupply['username'],
            'api_pwd' => $TypeSupply['password'],
            'sid' => $GoodsData['gid'],
            'num' => ($OrderData['num'] * $Goods['quantity']),
        ], $DataPost);
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);

        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . '.skysq.top/api/user/order', $DataPost);

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

        if (!isset($DataCurl['status']) || $DataCurl['status'] != 0) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => (empty($DataCurl['message']) ? '下单信息获取失败!,请检查对接配置!' : $DataCurl['message']),
                'money' => 0,
                'order' => -1,
            ];
        }

        /**
         * 卡密转换
         */
        if (isset($DataCurl['type']) && (int)$DataCurl['type'] === 0) {
            $SQL = [];
            foreach ($DataCurl['kms2'] as $v) {
                if (empty($v)) {
                    continue;
                }
                $SQL[] = [
                    'uid' => $OrderData['uid'],
                    'gid' => $Goods['gid'],
                    'code' => $InputArray[0],
                    'token' => ($v['card'] ? '卡号：' . $v['card'] : '') . ($v['card'] ? '，卡密：' . $v['pass'] : ''),
                    'ip' => $OrderData['ip'],
                    'order' => $OrderData['order'],
                    'endtime' => $date,
                    'addtime' => $date,
                ];
            }
            $DB->insert('token', $SQL);
        }

        $money = ($DataCurl['status'] >= 0 ? $DataCurl['rmb'] : 0);

        return [
            'code' => ($DataCurl['status'] >= 0 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']),
            'docking' => ($DataCurl['status'] >= 0 ? 1 : 2),
            'msg' => '对接提交成功！',
            'money' => $money,
            'order' => $DataCurl['message'],
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

        $Supply['url'] = StringCargo::UrlVerify($Supply['url'], 3);

        $Post = [
            'api_user' => $Supply['username'],
            'sid' => (int)$GoodsData['gid'],
        ];

        $CurlData = Api::Curl($Supply['url'] . '.skysq.top/api/user/goods/info', $Post);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['status'] !== 0 || (int)$CurlDataJson['data']['close'] !== 0) {
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
                'inventory' => ($Goods['quota'] <= 0 ? 999999 : $Goods['quota']),
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
            'id' => $Data['sqid'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url'], 3);

        $Post = [
            'api_user' => $SourceData['username'],
            'sid' => (int)$Data['gid'],
        ];

        $CurlData = Api::Curl($SourceData['url'] . '.skysq.top/api/user/goods/info', $Post);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        $Goods = $CurlDataJson['data'];
        if ((int)$Goods['close'] === 1) {
//            return [
//                'code' => -1,
//                'msg' => '此商品已关闭下单！',
//            ];
        }

        if ($CurlDataJson['status'] === 0) {
            $Input = [[], []];
            foreach ($Goods['post'] as $key => $val) {
                $Input[0][$key] = $val['name']; //下单信息
                $Input[1][$key] = $val['param']; //对接参数
            }
            if ((!empty($Goods['image_url']) && strpos($Goods['image_url'], 'http') === false)) {
                $Goods['image_url'] = $SourceData['url'] . '/' . $Goods['image_url'];
            }

            if ($Goods['minnum'] <= 1) {
                $Goods['minnum'] = 1;
            }
            if ($Goods['maxnum'] <= 1) {
                $Goods['maxnum'] = 1;
            }

            return [
                'code' => 1,
                'msg' => '对接参数自动填写成功！',
                'data' => [
                    'name' => $Goods['name'],
                    'image' => ImageUrl($Goods['image_url']),
                    'docs' => htmlspecialchars_decode(base64_decode($Goods['content'])),
                    'money' => ((float)$Goods['price'] * (int)$Goods['minnum']),
                    'min' => 1,
                    'max' => ($Goods['maxnum'] / $Goods['minnum']),
                    'quota' => 9999999,
                    'quantity' => $Goods['minnum'],
                    'extend' => [
                        'gid' => $Goods['goodsid'],
                        'parameter' => implode(',', $Input[1]),
                    ],
                    'units' => $Goods['unit'],
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
        if ($Data['type'] != 2 && !empty($_SESSION['SkYunGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['SkYunGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['goodsid'],
                    'name' => $v['name'] . ' - ' . ((int)$v['close'] === 0 ? '正常对接' : '关闭下单') . ' - ' . $v['minnum'] . $v['unit'] . ($v['minnum'] * $v['price']) . '元',
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
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url'], 3);

        $Post = [
            'api_user' => $SourceData['username'],
        ];

        $CurlData = Api::Curl($SourceData['url'] . '.skysq.top/api/user/goods/list', $Post);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['status'] === 0) {

            $_SESSION['SkYunGoodsList_' . $Data['id']] = $CurlDataJson['data'];
            $GoodsList = [];
            foreach ($CurlDataJson['data'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['goodsid'],
                    'name' => $v['name'] . ' - ' . ((int)$v['close'] === 0 ? '正常对接' : '关闭下单') . ' - ' . $v['minnum'] . $v['unit'] . ($v['minnum'] * $v['price']) . '元',
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
