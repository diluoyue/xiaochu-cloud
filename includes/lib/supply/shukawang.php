<?php

/**
 * Author：数卡网-小蕾Gg Q541628358
 * Creation：2022/3/17
 * Filename：shukawang.php
 * 数卡网
 */

namespace lib\supply;


use Medoo\DB\SQL;

class shukawang
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '数卡网',
        'image' => '../assets/img/Grade.png',
        'PriceMonitoring' => 1, //是否支持价格监控
        'BatchShelves' => 1, //批量上架
        'help' => -1,
        'ip' => 1,
        'field' => [ //添加货源的字段名称
            'url' => [
                'name' => '域名',
                'tips' => '请填写包含http(s):// 和 / 的域名地址!',
                'type' => 1,
            ],
            'username' => [
                'name' => '登录账号',
                'tips' => '请填写货源站的登录账号!',
                'type' => 1,
            ],
            'password' => [
                'name' => '登录密码',
                'tips' => '请填写货源站的登录账号!',
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
            ],
            'commtype' => [
                'type' => 1,
                'name' => '充值类型',
                'reminder' => '请点击获取数据后，选择对应商品自动获取'
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
        if ($Data['type'] != 2 && !empty($_SESSION['ShuKaWangGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['ShuKaWangGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['id'],
                    'name' => '[' . ($v['commpar'] - 0) . '元]' . $v['cname'] . ' - ' . ((int)$v['saletype'] === 0 ? '正常对接' : '已下架'),
                    'cid' => $v['commtype'],
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

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);

        $url = $SourceData['url'] . 'goodapi/CommListserverqb';

        $CurlData = Api::Curl($url . "?username=" . $SourceData['username'] . "&userpass=" . $SourceData['password']);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] === 300) {
            return [
                'code' => -1,
                'msg' => '账号密码有误，无法完成对接！',
            ];
        }

        if ((int)$CurlDataJson['code'] === 10000) {
            $_SESSION['ShuKaWangGoodsList_' . $Data['id']] = $CurlDataJson['data'];
            $GoodsList = [];
            foreach ($CurlDataJson['data'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['id'],
                    'name' => '[' . ($v['commpar'] - 0) . '元]' . $v['cname'] . ' - ' . ((int)$v['saletype'] === 0 ? '正常对接' : '已下架'),
                    'cid' => $v['commtype'],
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
        $urls = $Supply['url'] . '/Goodapi/supanv/';
        $CurlData = Api::Curl($urls . "?username={$Supply['username']}&userpass={$Supply['password']}&commnavid={$GoodsData['gid']}");
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] !== 10000 || $CurlDataJson['data']['saletype'] != 0) {
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
                'inventory' => 99999,
                'state' => 1,
                'money' => $GoodsDataApi['money'],
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

        $urls = $SourceData['url'] . '/Goodapi/supanv/';
        $CurlData = Api::Curl($urls . "?username={$SourceData['username']}&userpass={$SourceData['password']}&commnavid={$Data['gid']}");
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }

        if ($CurlDataJson['code'] !== 10000) {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['message'] ?? '商品详情获取失败！',
            ];
        }

        $Goods = $CurlDataJson['data'];

        $Input = [];
        foreach ($Goods['tpl'] as $val) {
            $Input[0][] = $val['name']; //下单信息
            $Input[1][] = $val['name']; //对接参数
        }

        $Image = $Goods['image'];
        if (!strstr($Image, 'http')) {
            $Image = $SourceData['url'] . $Image;
        }

        return [
            'code' => 1,
            'msg' => '对接参数自动填写成功！',
            'data' => [
                'name' => $Goods['name'],
                'image' => $Image,
                //'alert' => $Goods['eject'],
                'docs' => $Goods['introduction'] ?? $Goods['eject'],
                'money' => ((float)$Goods['money'] * $Goods['commtopnumber']),
                'min' => 1,
                'max' => ($Goods['commnumber'] / $Goods['commtopnumber']),
                'quota' => 9999999,
                'quantity' => $Goods['commtopnumber'],
                'extend' => [
                    'gid' => $Goods['id'],
                    'parameter' => implode(',', $Input[1]),
                    'commtype' => $Goods['commtype'], //3卡密，其他人工充值
                ],
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
        //获取商品模板数据
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);

        $Field = explode(',', $GoodsData['parameter']);
        $PostInput = [];
        foreach ($Field as $key => $value) {
            $PostInput[$value] = $InputArray[$key];
        }
        $Post = [
            'username' => $TypeSupply['username'],
            'userpass' => $TypeSupply['password'],
            'Paypass' => $TypeSupply['secret'],
            'commid' => $GoodsData['gid'],
            'number' => ($OrderData['num'] * $Goods['quantity']),
            'CommQQ' => $InputArray[0],
        ];
        if ($GoodsData['commtype'] != 3) {
            $Post['Content'] = base64_encode(serialize($PostInput));
        }
        $url = $TypeSupply['url'] . '/Goodapi/buyGoodOrder';
        $DataCurl = Api::SuppluCurl($url . '?' . http_build_query($Post), 0, [
            'Content-Type' => 'text/html; charset=utf-8'
        ]);
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

        if (!isset($DataCurl['code']) || $DataCurl['code'] != 10000) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => (empty($DataCurl) ? '下单信息获取失败!,请检查对接配置!' : ($DataCurl['data'] ?? $DataCurl['message'])),
                'money' => 0,
                'order' => -1,
            ];
        }
        return [
            'code' => $conf['SubmitStateSuccess'],
            'docking' => 1,
            'msg' => $DataCurl['message'],
            'order' => $DataCurl['data']['number'],
        ];
    }


    /**
     * @param $Order 订单信息
     * @param $TypeSupply 对接货源信息
     */
    public static function Query($Order, $TypeSupply)
    {
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url'], 3);
        $url = $TypeSupply['url'] . '/goodapi/orderInfo';
        $Post = [
            'username' => $TypeSupply['username'],
            'userpass' => $TypeSupply['password'],
            'orderno' => $Order['order_id']
        ];
        $DataCurl = Api::Curl($url . '?' . http_build_query($Post));
        $DataCurl = json_decode($DataCurl, TRUE);
        if (empty($DataCurl)) {
            return [];
        }
        if ($DataCurl['code'] <> 10000) {
            return [];
        }
        $Data = $DataCurl['data'];

        switch ($Data['ostatus']) {
            case 1:
                $order_state = '待处理';
                $StateNum = 2;
                break;
            case 6:
                $order_state = '处理中';
                $StateNum = 4;
                break;
            case 2:
                $order_state = '已完成';
                $StateNum = 1;
                break;
            case 7:
                $order_state = '已退单';
                $StateNum = 5;
                break;
            default:
                $order_state = '未知状态';
                $StateNum = 3;
                break;
        }


        if (isset($DataCurl['cards	']) && count($DataCurl['cards']) >= 1) {
            //有卡
            $DB = SQL::DB();
            $TokenGet = $DB->count('token', [
                    'order' => $Order['order'],
                ]) - 0;
            if ($TokenGet < count($DataCurl['cards'])) {
                global $date;
                //补卡
                $SQL = [];
                foreach ($DataCurl['cards'] as $v) {
                    if (empty($v['card_no'])) {
                        continue;
                    }
                    $SQL[] = [
                        'uid' => $Order['uid'],
                        'gid' => $Order['gid'],
                        'code' => json_decode($Order['input'], true)[0],
                        'token' => ($v['card_no'] ? '卡号：' . $v['card_no'] : '') . ($v['card_password'] ? ' 卡密：' . $v['card_password'] : '') . ($v['card_zp'] ? ' 赠品：' . $v['card_zp'] : ''),
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
            'ApiNum' => $Data['ocount'],
            'ApiTime' => $Data['time'],
            'ApiInitial' => $Data['start_num'] ?? 0,
            'ApiPresent' => $Data['now_num'] ?? 0,
            'ApiState' => $order_state,
            'ApiError' => ($Data['remark'] <> null ? $Data['remark'] : ''),
            'ApiStateNum' => $StateNum,
        ];
    }
}
