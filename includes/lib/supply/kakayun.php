<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/21 10:45
// +----------------------------------------------------------------------
// | Filename: kakayun.php
// +----------------------------------------------------------------------
// | Explain: 卡卡云对接操作类
// +----------------------------------------------------------------------

namespace lib\supply;


use Medoo\DB\SQL;

class kakayun
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '卡卡云系统',
        'image' => '../assets/img/kakayun.png',
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
                'name' => '用户ID',
                'tips' => '请填写对接站的用户id!',
                'type' => 1,
            ],
            'password' => [
                'name' => '对接密钥',
                'tips' => '请填写好对接站的对接密钥!',
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
            'userid' => $TypeSupply['username'],
            'orderno' => $Order['order_id'],
            //'dockapiorderno' => $Order['order']
        ];

        $DataPost = array_merge([
            'sign' => self::getKkySign($DataPost, $TypeSupply['password'])
        ], $DataPost);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::Curl($TypeSupply['url'] . 'dockapi/index/queryorder.html', $DataPost);

        $DataCurl = json_decode($DataCurl, TRUE);

        if (empty($DataCurl)) {
            return [];
        }

        if ($DataCurl['code'] == 1) {
            $Data = $DataCurl['data'];
            switch ((int)$Data['status']) {
                case 0:
                    $order_state = '未使用';
                    $StateNum = 4;
                    break;
                case 1:
                    $order_state = '已发卡';
                    $StateNum = 1;
                    break;
                case 2:
                    $order_state = '未付款';
                    $StateNum = 3;
                    break;
                case 3:
                    $order_state = '进行中';
                    $StateNum = 4;
                    break;
                case 4:
                    $order_state = '已撤回';
                    $StateNum = 5;
                    break;
                case 5:
                    $order_state = '已完成';
                    $StateNum = 1;
                    break;
                default:
                    $order_state = '未知状态';
                    $StateNum = 3;
                    break;
            }
            return [
                'ApiType' => 1,
                'ApiNum' => $Data['buynum'],
                'ApiTime' => '无',
                'ApiInitial' => '无',
                'ApiPresent' => '无',
                'ApiState' => $order_state,
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
            'userid' => $TypeSupply['username'],
            //'outorderno' => $OrderData['order'], //是否验证本地订单？防止重复提交
            'goodsid' => $GoodsData['gid'],
            'buynum' => $OrderData['num'],
            'maxmoney' => $OrderData['money'],
            'attach' => json_encode($InputArray),
        ];
        $DataPost = array_merge($DataPost, [
            'sign' => self::getKkySign($DataPost, $TypeSupply['password']),
        ]);
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'dockapi/index/buy.html', $DataPost);
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

        if (!isset($DataCurl['code']) || $DataCurl['code'] != 1) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => (empty($DataCurl['msg']) ? '下单信息获取失败!,请检查对接配置!' : $DataCurl['msg']),
                'money' => 0,
                'order' => -1,
            ];
        }

        if (isset($DataCurl['cardlist']) && count($DataCurl['cardlist']) >= 1) {
            $SQL = [];
            foreach ($DataCurl['cardlist'] as $v) {
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

        return [
            'code' => ($DataCurl['code'] >= 1 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']),
            'docking' => ($DataCurl['code'] >= 1 ? 1 : 2),
            'msg' => $DataCurl['msg'],
            'money' => $DataCurl['money'],
            'order' => $DataCurl['orderno'],
        ];
    }

    private static function getKkySign($param, $userkey)
    {
        ksort($param); //排序post参数
        reset($param); //内部指针指向数组中的第一个元素
        $signtext = '';
        foreach ($param as $key => $val) { //遍历POST参数
            if ($val == '' || $key == 'sign') continue; //跳过这些不签名
            if ($signtext) $signtext .= '&'; //第一个字符串签名不加& 其他加&连接起来参数
            $signtext .= "$key=$val"; //拼接为url参数形式
        }
        return md5($signtext . $userkey);
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
        sleep(3);
        $GoodsData = json_decode($Goods['extend'], true);
        if (empty($GoodsData['gid'])) {
            return [
                'code' => -1,
                'msg' => '此商品对接参数缺失，无法完成商品状态监控！',
            ];
        }

        $Post = [
            'userid' => $Supply['username'],
            'goodsid' => $GoodsData['gid']
        ];

        $Post = array_merge($Post, [
            'sign' => self::getKkySign($Post, $Supply['password']),
        ]);

        $Supply['url'] = StringCargo::UrlVerify($Supply['url']);
        $CurlData = Api::Curl($Supply['url'] . 'dockapi/index/goodsdetails.html', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['code'] === -1) {
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
        $GoodsDataApi = $CurlDataJson['goodsdetails'];

        /**
         * 解析数据
         */

        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => $GoodsDataApi['stock'] ?? $Goods['quota'],
                'state' => ($GoodsDataApi['goodsstatus'] != 1 ? 2 : 1),
                'money' => $CurlDataJson['price']['data']['goodsprice'],
                'specification' => false,
            ]
        ];
    }

    /**
     * 获取商品详情
     */
    public static function ProductDetails($Data)
    {
        sleep(3);
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
            'userid' => $SourceData['username'],
            'goodsid' => $Data['gid']
        ];

        $Post = array_merge($Post, [
            'sign' => self::getKkySign($Post, $SourceData['password']),
        ]);

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'dockapi/index/goodsdetails.html', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['code'] === -1) {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['msg'],
            ];
        }

        $Goods = $CurlDataJson['goodsdetails'];
//        if ($Goods['goodsstatus'] != 0) {
//            return [
//                'code' => -1,
//                'msg' => '此商品已关闭下单！',
//            ];
//        }

        $Input = [];
        foreach ($CurlDataJson['attach'] as $key => $v) {
            $Input[0][] = $v['title'];
            $Input[1][] = 'input' . ($key + 1);
        }

        if (count($CurlDataJson['attach']) === 0) {
            $Input[0][] = '下单信息';
            $Input[1][] = 'input1';
        }

        $Price = $CurlDataJson['price']['data']['goodsprice'];

        return [
            'code' => 1,
            'msg' => '对接参数自动填写成功！<br>商品进货成本：1' . ($Goods['unit'] ?? '份') . ' ' . $Price . '元',
            'data' => [
                'name' => $Goods['goodsname'],
                'image' => $Goods['imgurl'],
                'docs' => $Goods['details'],
                'money' => $Price,
                'min' => $Goods['buyminnum'],
                'max' => $Goods['buymaxnum'],
                'alert' => $Goods['exchangeinfo'],
                'quota' => $Goods['stock'],
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
        if ($Data['type'] != 2 && !empty($_SESSION['KaKayunGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['KaKayunGoodsList_' . $Data['id']] as $val) {
                if (count($val['goods']) === 0) {
                    continue;
                }
                foreach ($val['goods'] as $v) {
                    $GoodsList[] = [
                        'gid' => $v['id'],
                        'name' => $v['goodsname'] . ' - ' . ((int)$v['goodsstatus'] === 1 ? '正常对接' : '已下架'),
                        'cid' => $v['cid'],
                    ];
                }
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
            'userid' => $SourceData['username'],
        ];

        $Post = array_merge($Post, [
            'sign' => self::getKkySign($Post, $SourceData['password']),
        ]);
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $CurlData = Api::Curl($SourceData['url'] . 'dockapi/index/getallgoods.html', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ((int)$CurlDataJson['code'] === 1) {
            $GoodsList = [];

            $_SESSION['KaKayunGoodsList_' . $Data['id']] = $CurlDataJson['data'];

            foreach ($CurlDataJson['data'] as $val) {
                if (count($val['goods']) === 0) {
                    continue;
                }
                foreach ($val['goods'] as $v) {
                    $GoodsList[] = [
                        'gid' => $v['id'],
                        'name' => $v['goodsname'] . ' - ' . ((int)$v['goodsstatus'] === 1 ? '正常对接' : '已下架'),
                        'cid' => $v['cid'],
                    ];
                }
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
}
