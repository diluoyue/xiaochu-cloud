<?php

/**
 * Author：晴玖天
 * Creation：2020/4/22 13:31
 * Filename：zhike.php
 * 直客
 */

namespace lib\supply;


use Medoo\DB\SQL;

class zhike
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '直客商城',
        'image' => '../assets/img/zhike.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => '同时支持直客小店+直客SUP对接,如果对接失败,请检查是否是自己的站点无法和对接站点互通',
        'ip' => 1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '商城域名',
                'tips' => '请填写包含http(s):// 和 / 的域名地址!',
                'type' => 1,
            ],
            'username' => [
                'name' => 'AppId',
                'tips' => '请填写AppId!',
                'type' => 1,
            ],
            'password' => [
                'name' => 'AppSecret',
                'tips' => '请填写AppSecret!',
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

        if ($Data['type'] != 2 && !empty($_SESSION['ZhikeGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['ZhikeGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['gid'],
                    'name' => $v['name'] . ' - ' . ($v['close'] === 0 ? '正常对接' : '关闭下单'),
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
        $requestURI = '/api/client/goods/v2/goods/list';
        $header = [
            'AppId' => $SourceData['username'],
            'AppTimestamp' => time(),
            'AppToken' => self::GetSign($SourceData, $requestURI),
        ];
        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url'], 3);
        $CurlData = Api::Curl($SourceData['url'] . $requestURI, [], $header);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] === 100) {
            $_SESSION['ZhikeGoodsList_' . $Data['id']] = $CurlDataJson['data'];
            $GoodsList = [];
            foreach ($CurlDataJson['result']['data'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['goodsSN'],
                    'name' => $v['goodsName'],
                    'cid' => $v['categoryId'],
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
            'msg' => $CurlDataJson['msg'],
        ];
    }

    /**
     * @param $array
     * @return string
     * 直客签名
     */
    public static function GetSign($array, $requestURI = '')
    {
        return sha1($array['username'] . $array['password'] . $requestURI . time());
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

        $Supply['url'] = StringCargo::UrlVerify($Supply['url'], 3);

        $requestURI = '/api/client/goods/v2/goods?goodsSN=' . $GoodsData['gid'];
        $header = [
            'AppId' => $Supply['username'],
            'AppTimestamp' => time(),
            'AppToken' => self::GetSign($Supply, $requestURI),
        ];

        $CurlData = Api::Curl($Supply['url'] . $requestURI, [], $header);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ((int)$CurlDataJson['code'] !== 100 || (int)$CurlDataJson['result']['isClose'] !== 0) {
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
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'inventory' => ($GoodsDataApi['goodsStock'] == -1 ? 99999 : $GoodsDataApi['goodsStock']),
                'state' => 1,
                'money' => (float)$GoodsDataApi['goodsPrice'],
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

        $requestURI = '/api/client/goods/v2/goods?goodsSN=' . $Data['gid'];
        $header = [
            'AppId' => $SourceData['username'],
            'AppTimestamp' => time(),
            'AppToken' => self::GetSign($SourceData, $requestURI),
        ];
        $CurlData = Api::Curl($SourceData['url'] . $requestURI, [], $header);

        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] !== 100) {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['msg']
            ];
        }

        $Goods = $CurlDataJson['result'];
        if ($Goods['isClose']) {
//            return [
//                'code' => -1,
//                'msg' => '此商品已关闭下单！',
//            ];
        }

        $Input = [];
        foreach ($Goods['paramsTemplate'] as $val) {
            $Input[0][] = $val['name']; //下单信息
            $Input[1][] = $val['alias']; //对接参数
        }

        $Image = $Goods['goodsThumb'];
        if (!strstr($Image, 'http')) {
            $Image = $SourceData['url'] . $Image;
        }

        return [
            'code' => 1,
            'msg' => '对接参数自动填写成功！',
            'data' => [
                'name' => $Goods['goodsName'],
                'image' => $Image,
                'alert' => $Goods['goodsDesc'],
                'docs' => $Goods['goodsDetail'],
                'money' => ((float)$Goods['goodsPrice'] * $Goods['minOrderNum']),
                'min' => 1,
                'max' => ($Goods['maxOrderNum'] / $Goods['minOrderNum']),
                'quota' => ($Goods['goodsStock'] == -1 ? 99999 : $Goods['goodsStock']),
                'quantity' => $Goods['minOrderNum'],
                'extend' => [
                    'gid' => $Goods['goodsSN'],
                    'parameter' => implode(',', $Input[1]),
                ],
                'units' => $Goods['goodsUnit'],
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
        $i = 0;
        foreach ($Field as $value) {
            $PostInput[] = [
                'alias' => $value,
                'value' => $InputArray[$i]
            ];
            $i++;
        }

        $requestURI = '/api/client/goods/v2/order';

        $header = [
            'AppId' => $TypeSupply['username'],
            'AppTimestamp' => time(),
            'AppToken' => self::GetSign($TypeSupply, $requestURI),
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        $Body = [
            'goodsSN' => $GoodsData['gid'],
            'customOrderSN' => $OrderData['order'],
            'number' => ($OrderData['num'] * $Goods['quantity']),
            'orderNote' => '小储api对接',
            'buyNotify' => -1,
            'params' => $PostInput,
        ];

        $Json = json_encode($Body);

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);
        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . $requestURI, $Json, $header);

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
            $Msg = $DataCurl['msg'];
            $code = ($DataCurl['code'] === 100 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']);
            $money = self::MoneyGet($TypeSupply) ?? -1;
            $order = ($DataCurl['code'] === 100 ? $DataCurl['result']['orderSN'] : -1);
            $docking = ($DataCurl['code'] === 100 ? 1 : 2);
        }

        if ($DataCurl['code'] === 100) {
            //同步卡密库存
            $OrderData['order_id'] = $DataCurl['result']['orderSN'];
            self::Query($OrderData, $TypeSupply);
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
     * 查询余额
     */
    public static function MoneyGet($TypeSupply)
    {
        $requestURI = '/api/client/account/v2/profile';
        $header = [
            'AppId' => $TypeSupply['username'],
            'AppTimestamp' => time(),
            'AppToken' => self::GetSign($TypeSupply, $requestURI),
        ];
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);
        $DataCurl = Api::Curl($TypeSupply['url'] . $requestURI, [], $header);
        $DataCurl = json_decode($DataCurl, true);
        if (empty($DataCurl) || $DataCurl['code'] !== 100) {
            return false;
        }
        return $DataCurl['result']['balance'];
    }

    /**
     * @param $Order 订单信息
     * @param $TypeSupply 对接货源信息
     */
    public static function Query($Order, $TypeSupply)
    {

        $requestURI = '/api/client/goods/v2/order?orderSN=' . $Order['order_id'];
        $header = [
            'AppId' => $TypeSupply['username'],
            'AppTimestamp' => time(),
            'AppToken' => self::GetSign($TypeSupply, $requestURI)
        ];

        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);
        $DataCurl = Api::Curl($TypeSupply['url'] . $requestURI, 0, $header);

        $DataCurl = json_decode($DataCurl, TRUE);

        if (empty($DataCurl)) {
            return false;
        }

        if ($DataCurl['code'] <> 100) {
            return false;
        }

        $Data = $DataCurl['result'];

        switch ($Data['orderState']) {
            case 1:
                $order_state = '已付款';
                $StateNum = 2;
                break;
            case 2:
                $order_state = '处理中';
                $StateNum = 4;
                break;
            case 3:
                $order_state = '待确认';
                $StateNum = 2;
                break;
            case 4:
                $order_state = '已完成';
                $StateNum = 1;
                break;
            case 5:
                $order_state = '退单中';
                $StateNum = 6;
                break;
            case 6:
                $order_state = '已退单';
                $StateNum = 5;
                break;
            case 7:
                $order_state = '已退款';
                $StateNum = 5;
                break;
            case 8:
                $order_state = '待处理';
                $StateNum = 2;
                break;
            case -1:
                $order_state = '待付款';
                $StateNum = 3;
                break;
            default:
                $order_state = '未知状态';
                $StateNum = 3;
                break;
        }

        if (isset($Data['cardNumber']) && count(explode(',', $Data['cardNumber'])) >= 1) {
            $TokenList = explode("\t\n", $Data['cardNumber']);
            if (count($TokenList) == 1) {
                $TokenList2 = explode("\r\n", $Data['cardNumber']);
                if ($TokenList2 > $TokenList) {
                    $TokenList = $TokenList2;
                }
            }
            if (count($TokenList) == 1) {
                $TokenList2 = explode("\n", $Data['cardNumber']);
                if ($TokenList2 > $TokenList) {
                    $TokenList = $TokenList2;
                }
            }
            //有卡
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
                        'token' => $v,
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
            'ApiNum' => $Data['orderNum'],
            'ApiTime' => $Data['createdAt'],
            'ApiInitial' => $Data['startNum'],
            'ApiPresent' => $Data['currentNum'],
            'ApiState' => $order_state,
            'ApiError' => $Data['orderRemark'] ?? '',
            'ApiStateNum' => $StateNum,
        ];
    }
}
