<?php

/**
 * Author：晴玖天
 * Creation：2020/4/22 13:31
 * Filename：szhanw.php
 * 商站汪
 */

namespace lib\supply;


use Medoo\DB\SQL;

class szhanw
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '商站网',
        'image' => '../assets/img/shangzw.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => -1,
        'ip' => 1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '域名地址',
                'tips' => '请填写包含http(s):// 和 / 的域名地址!',
                'type' => 1,
            ],
            'username' => [
                'name' => '用户编号',
                'tips' => '请填写用户编号!',
                'type' => 1,
            ],
            'password' => [
                'name' => '对接key',
                'tips' => '请填写对接key!',
                'type' => 1,
            ],
            'secret' => [
                'name' => '交易密码',
                'tips' => '请填写交易密码!',
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

    /**
     * @param $Data
     * @return array
     * 获取可对接商品列表
     */
    public static function GoodsList($Data)
    {
        if ($Data['type'] != 2 && !empty($_SESSION['ShangzhanwGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['ShangzhanwGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['id'],
                    'name' => $v['name'],
                    'cid' => $v['type'],
                ];
            }
            return [
                'code' => 1,
                'msg' => '可对接商品列表获取成功！',
                'data' => $GoodsList
            ];
        }

        $DB = SQL::DB();
        $SourceData = $DB->get('shequ', ['url', 'username', 'password', 'secret'], [
            'id' => $Data['id'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $requestURI = '/api.php/Client/goodsList';
        $Post = [
            'customerid' => $SourceData['username'],
            'sign' => md5($SourceData['username'] . $SourceData['password']),
        ];
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url'], 3);
        $CurlData = Api::Curl($SourceData['url'] . $requestURI, $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] === 1000) {
            $_SESSION['ShangzhanwGoodsList_' . $Data['id']] = $CurlDataJson['data'];
            $GoodsList = [];
            foreach ($CurlDataJson['data'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['id'],
                    'name' => $v['name'],
                    'cid' => $v['type'],
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
    public static function CommodityStatus($Goods, $SourceData)
    {
        $GoodsData = json_decode($Goods['extend'], true);
        if (empty($GoodsData['gid'])) {
            return [
                'code' => -1,
                'msg' => '此商品对接参数缺失，无法完成商品状态监控！',
            ];
        }

        $requestURI = '/api.php/Client/goodsInfo';
        $Post = [
            'customerid' => $SourceData['username'],
            'sign' => md5($SourceData['username'] . $GoodsData['gid'] . $SourceData['password']),
            'id' => $GoodsData['gid']
        ];
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url'], 3);
        $CurlData = Api::Curl($SourceData['url'] . $requestURI, $Post);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ((int)$CurlDataJson['code'] !== 1000 || $CurlDataJson['data']['supply_state'] != 1) {
            return [
                'code' => 1,
                'msg' => '数据获取成功',
                'data' => [
                    'state' => 2,
                    'money' => 0,
                    'inventory' => $CurlDataJson['data']['stock_num'] ?? 0,
                ]
            ];
        }
        $GoodsDataApi = $CurlDataJson['data'];

        if ($GoodsDataApi['type'] == 2) {
            $GoodsDataApi['stock_num'] = ($Goods['quota'] <= 0 ? 999999 : $Goods['quota']);
        }

        /**
         * 解析数据
         */
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => $GoodsDataApi['stock_num'],
                'state' => 1,
                'money' => (float)$GoodsDataApi['price'],
                'specification' => false,
            ]
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
        $SourceData = $DB->get('shequ', ['url', 'username', 'password', 'secret'], [
            'id' => $Data['sqid'],
        ]);
        if (!$SourceData) {
            return [
                'code' => -1,
                'msg' => '对接社区数据获取失败，请检查此对接社区是否存在！',
            ];
        }
        $requestURI = '/api.php/Client/goodsInfo';
        $Post = [
            'customerid' => $SourceData['username'],
            'sign' => md5($SourceData['username'] . $Data['gid'] . $SourceData['password']),
            'id' => $Data['gid']
        ];
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url'], 3);
        $CurlData = Api::Curl($SourceData['url'] . $requestURI, $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] !== 1000) {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['info']
            ];
        }

        $Goods = $CurlDataJson['data'];
        if ($Goods['supply_state'] !== 1) {
//            return [
//                'code' => -1,
//                'msg' => '此商品已关闭下单！',
//            ];
        }

        $Input = [];
        $Input[0][] = $Goods['template']['account']; //下单信息
        $Input[1][] = 'INPUT1';
        foreach ($Goods['template']['content'] as $key => $val) {
            $Input[0][] = $val['name']; //下单信息
            $Input[1][] = 'INPUT' . ($key + 2); //对接参数
        }

        if ($Goods['type'] == 2) {
            $Goods['stock_num'] = 999999;
        }

        if (isset($Goods['recharge_url'])) {
            $explain = '卡密兑换地址：' . $Goods['explain'];
        } else {
            $explain = '';
        }

        return [
            'code' => 1,
            'msg' => '对接参数自动填写成功！',
            'data' => [
                'name' => $Goods['name'],
                'image' => $Goods['img'],
                'alert' => $Goods['notice'],
                'docs' => $Goods['info'],
                'money' => (float)$Goods['price'],
                'min' => 1,
                'max' => $Goods['quantity'],
                'quota' => $Goods['stock_num'],
                'quantity' => 1,
                'explain' => $explain,
                'extend' => [
                    'gid' => $Goods['id'],
                    'parameter' => implode(',', $Input[1]),
                ],
                'units' => $Goods['个'],
                'input' => implode('|', $Input[0]),
            ]
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
        global $conf;
        $InputArray = json_decode($OrderData['input'], TRUE);
        if ($Goods['specification'] == 2 && $Goods['specification_type'] == 2) {
            $InputArray = RuleSubmitParameters(json_decode($Goods['specification_spu'], TRUE), $InputArray);
        }

        $GoodsData = json_decode($Goods['extend'], true); //对接数据
        $Field = explode(',', $GoodsData['parameter']);
        $PostInput = [];
        foreach ($Field as $key => $value) {
            if ($key == 0) {
                $accountname = $InputArray[0];
            } else {
                $PostInput['lblName' . ($key - 1)] = $InputArray[$key];
            }
        }

        $requestURI = '/api.php/Client/createOrder';

        $Body = [
            'customerid' => $TypeSupply['username'],
            'goodsid' => $GoodsData['gid'],
            'accountname' => $accountname,
            'quantity' => ($OrderData['num'] * $Goods['quantity']),
            'tradepassword' => $TypeSupply['secret'],
            'mark' => '小储api对接',
            'external_orderno' => $OrderData['order'],
            'safe_price' => $OrderData['price'],
            'sign' => md5($TypeSupply['username'] . $GoodsData['gid'] . $TypeSupply['password']),
        ];

        $Body = array_merge($Body, $PostInput);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);
        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . $requestURI, $Body);

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

        if (!isset($DataCurl['code'])) {
            $Msg = '下单信息获取失败!,请检查对接配置！';
            $code = $conf['SubmitState'];
            $money = 0;
            $order = -1;
            $docking = 2;
        } else {
            $Msg = $DataCurl['info'];
            $code = ($DataCurl['code'] === 1000 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']);
            $money = -1;
            $order = ($DataCurl['code'] === 1000 ? $DataCurl['data']['orderno'] : -1);
            $docking = ($DataCurl['code'] === 1000 ? 1 : 2);
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
     * @param $Order 订单信息
     * @param $TypeSupply 对接货源信息
     */
    public static function Query($Order, $TypeSupply)
    {
        $requestURI = '/api.php/Client/orderDetail';
        $Post = [
            'customerid' => $TypeSupply['username'],
            'orderno' => $Order['order_id'],
            'external_orderno' => $Order['order'],
            'sign' => md5($TypeSupply['username'] . $TypeSupply['password'])
        ];

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);
        $DataCurl = Api::Curl($TypeSupply['url'] . $requestURI, $Post);

        $DataCurl = json_decode($DataCurl, TRUE);

        if (empty($DataCurl)) {
            return false;
        }

        if ($DataCurl['code'] !== 1000) {
            return false;
        }

        $Data = $DataCurl['data'];
        switch ((int)$Data['orderstate']) {
            case 1:
                $order_state = '待处理';
                $StateNum = 2;
                break;
            case 2:
                $order_state = '处理中';
                $StateNum = 4;
                break;
            case 3:
                $order_state = '交易成功';
                $StateNum = 1;
                break;
            case 4:
                $order_state = '处理失败';
                $StateNum = 3;
                break;
            case 5:
                $order_state = '已经退款';
                $StateNum = 5;
                break;
            case 6:
                $order_state = '订单异常';
                $StateNum = 3;
                break;
            default:
                $order_state = '未知状态';
                $StateNum = 3;
                break;
        }

        if (isset($Data['recharge']['card']) && count($Data['recharge']['card']) >= 1) {
            $TokenList = $Data['recharge']['card'];
            $DB = SQL::DB();
            $TokenGet = $DB->count('token', [
                    'order' => $Order['order'],
                ]) - 0;
            if ($TokenGet < count($TokenList)) {
                global $date;
                //补卡
                $SQL = [];
                foreach ($TokenList as $v) {
                    if (empty($v)) {
                        continue;
                    }
                    $SQL[] = [
                        'uid' => $Order['uid'],
                        'gid' => $Order['gid'],
                        'code' => json_decode($Order['input'], true)[0],
                        'token' => '卡号：' . $v['card_no'] . ' 卡密：' . $v['card_password'] ?? '无' . ' 卡密密码：' . $v['card_rect'] ?? '无',
                        'ip' => $Order['ip'],
                        'order' => $Order['order'],
                        'endtime' => $date,
                        'addtime' => $date,
                    ];
                }
                $DB->insert('token', $SQL);
            }
        }

        return [
            'ApiType' => 1,
            'ApiNum' => $Data['target_progress'],
            'ApiTime' => date('Y-m-d H:i', $Data['submittime']),
            'ApiInitial' => $Data['start_progress'],
            'ApiPresent' => $Data['current_progress'],
            'ApiState' => $order_state,
            'ApiError' => $Data['returninfo'] ?? '',
            'ApiStateNum' => $StateNum,
        ];
    }
}
