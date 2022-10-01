<?php

/**
 * Author：晴玖天
 * Creation：2020/4/22 13:31
 * Filename：jiuwu.php
 * 玖伍
 */

namespace lib\supply;

use Medoo\DB\SQL;

class jiuwu
{
    /**
     * @var array
     * 对接配置信息
     */
    public static $Config = [
        'name' => '玖伍社区', //货源站系统名称
        'image' => '../assets/img/jiuwu.png', //图标地址
        'PriceMonitoring' => 1, //是否支持价格监控，1 or -1
        'BatchShelves' => 1, //是否支持批量上架，1 or -1
        'help' => '若本地对接无效可使用IP代理方式对接!', //添加货源时的提示信息
        'ip' => -1, //是否需要在底部显示当前站点IP，1 or -1
        'field' => [ //添加货源的字段名称，可用字段：url,username,password,secret,pattern 这些字段都可以自定义配置,具体调用方法随便你怎么定
            'url' => [ //键值是对应的数据表里面的名称
                'name' => '社区域名', //输入框名称
                'tips' => '请填写包含http(s):// 和 / 的域名地址!', //输入框提示信息
                'type' => 1, //输入框类型=1就是输入框
            ],
            'username' => [
                'name' => '登陆账号',
                'tips' => '请填写玖伍社区的登录账号!',
                'type' => 1, //输入框类型=1就是输入框
            ],
            'password' => [
                'name' => '登陆密码',
                'tips' => '请填写玖伍社区的登录密码!',
                'type' => 1, //输入框类型=1就是输入框
            ],
            'pattern' => [
                'name' => '支付方式',
                'tips' => [
                    '1' => '余额', //可选参数
                    '0' => '点数', //可选参数
                ],
                'type' => 2, //输入框类型=2就是下拉框
            ],
        ],
        'InputField' => [ //选择对接商品时的输入框字段
            'ProductList' => [
                'type' => 2,
                'name' => '可对接商品', //下拉列表名称
                'request' => [ //提交的是当前选择参数数据类的值 + 当前的货源社区ID加类型键值！
                    'gid' => false,
                    'cid' => false,
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
            'cid' => [
                'type' => 1,
                'name' => '分类ID',
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
     * @param $Data
     * @param $Source
     * @param $DataSo
     * 获取可对接商品列表
     */
    public static function GoodsList($Data)
    {
        if ($Data['type'] != 2 && !empty($_SESSION['JiuWuGoodsList_' . $Data['id']])) {
            $GoodsList = [];
            foreach ($_SESSION['JiuWuGoodsList_' . $Data['id']] as $v) {
                $GoodsList[] = [
                    'gid' => $v['id'],
                    'name' => $v['title'] . ' - ' . round((float)$v['user_unitprice'] * $v['minbuynum_0'], 8) . '元,' . $v['minbuynum_0'] . $v['unit'],
                    'cid' => $v['goods_type'],
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
        $Post = [
            'Api_UserName' => $SourceData['username'],
            'Api_UserMd5Pass' => md5($SourceData['password']),
        ];
        $CurlData = Api::Curl($SourceData['url'] . 'index.php?m=home&c=api&a=user_get_goods_lists_details', $Post);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlDataJson)) {
            $Error = self::error($CurlData, 2);
            return [
                'code' => -1,
                'msg' => (!$Error ? '对接网站：' . $SourceData['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率' : '对接站点：' . $SourceData['url'] . ' 提示<br>' . $Error),
            ];
        }
        if ($CurlDataJson['status']) {
            $_SESSION['JiuWuGoodsList_' . $Data['id']] = $CurlDataJson['user_goods_lists_details'];
            $GoodsList = [];
            foreach ($CurlDataJson['user_goods_lists_details'] as $v) {
                $GoodsList[] = [
                    'gid' => $v['id'],
                    'name' => $v['title'] . ' - ' . round((float)$v['user_unitprice'] * $v['minbuynum_0'], 8) . '元,' . $v['minbuynum_0'] . $v['unit'],
                    'cid' => $v['goods_type'],
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
     * @param $data
     * @return string
     * 取出玖伍错误信息！
     */
    public static function error($data, $type = 1)
    {
        if (empty($data)) {
            if ($type === 2) {
                return false;
            }
            return '数据获取失败,返回的数据为空！';
        }
        $regex = "/<p class=\"error\".*?>(.*?)<\/p>/";
        preg_match($regex, $data, $matches);
        if (!$matches || empty($matches[1])) {
            if ($type === 2) {
                return false;
            }
            return '无法取出错误信息！';
        }
        return $matches[1];
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
        mkdirs(ROOT . 'includes/extend/log/JiuwuListLog/');
        $file = ROOT . 'includes/extend/log/JiuwuListLog/' . md5(json_encode($Supply)) . '.log';
        /**
         * 由于玖伍无单商品读取接口，故创建接口缓存文件
         * 1分钟更新4次，玖伍接口10分钟最多可请求50次
         */
        if (is_file($file)) {
            $CurlDataJson = json_decode(file_get_contents($file), true);
            if (empty($CurlDataJson)) {
                unlink($file);
                return self::CommodityStatus($Goods, $Supply);
            }
            $Time = filectime($file);
            if ($Time < (time() - 15)) {
                unlink($file);
            }
        } else {
            $Supply['url'] = StringCargo::UrlVerify($Supply['url']);
            $Post = [
                'Api_UserName' => $Supply['username'],
                'Api_UserMd5Pass' => md5($Supply['password']),
            ];
            $CurlData = Api::Curl($Supply['url'] . 'index.php?m=home&c=api&a=user_get_goods_lists_details', $Post);
            $CurlDataJson = json_decode($CurlData, true);
            if (empty($CurlDataJson)) {
                return [
                    'code' => -1,
                    'msg' => '对接网站：' . $Supply['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
                ];
            }
            if ($CurlDataJson['status'] !== true) {
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
            file_put_contents($file, json_encode($CurlDataJson));
        }

        $GoodsDataApi = false;
        foreach ($CurlDataJson['user_goods_lists_details'] as $v) {
            if (!$GoodsDataApi && (int)$v['id'] === (int)$GoodsData['gid']) {
                $GoodsDataApi = $v;
            }
        }

        if (!$GoodsDataApi || $GoodsDataApi['goods_status'] != 0) {
            return [
                'code' => 1,
                'msg' => ($GoodsDataApi['goods_close_msg'] ?? '数据获取成功'),
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
                'money' => (float)$GoodsDataApi['user_unitprice'],
                'specification' => false,
            ]
        ];
    }

    /**
     * @param $data
     * 取出玖伍下单参数！
     */
    public static function TakeOutTheData($data)
    {
        $Cookie = self::LoginCookie($data);
        $CurlHtml = $data['url'] . 'index.php?m=home&c=goods&a=detail&id=' . $data['gid'] . '&goods_type=' . $data['tyid'];
        if (!$Cookie) {
            return [
                'data' => [
                    'input' => ['下单账号'],
                    'parameter' => ['zh']
                ],
                'docs' => '',
                'price' => false,
                'balance' => false,
                'url' => $CurlHtml
            ];
        }
        $HtmlData = self::CurlCookieObtain($CurlHtml, $Cookie);
        preg_match('/class="order_post_form".*?<ul>(.*?)<\/ul>.*?<\/form>/us', $HtmlData, $tabs);
        if (empty($tabs) || empty($tabs[0])) {
            return [
                'data' => [
                    'input' => ['下单账号'],
                    'parameter' => ['zh']
                ],
                'docs' => '',
                'price' => false,
                'balance' => false,
                'url' => $CurlHtml
            ];
        }
        if (!$tabs || count($tabs) <= 0) {
            return [
                'data' => [
                    'input' => ['下单账号'],
                    'parameter' => ['zh']
                ],
                'docs' => '',
                'price' => false,
                'balance' => false,
                'url' => $CurlHtml
            ];
        }
        if (empty($tabs[1])) {
            $HtmlDataT2 = $tabs[0];
        } else {
            $HtmlDataT2 = $tabs[1];
        }
        preg_match_all('/<.*?<span class="fixed-width-right-80">(.*?)：<\/span>(?:.*?)[<textarea|<input](?:.*?)name="(.*?)"/si', $HtmlDataT2, $tabs2);
        if (!$tabs2 && count($tabs2) < 3) {
            return [
                'data' => [
                    'input' => ['下单账号'],
                    'parameter' => ['zh']
                ],
                'docs' => '',
                'price' => false,
                'balance' => false,
                'url' => $CurlHtml
            ];
        }
        $Data = [];
        foreach ($tabs2[0] as $key => $value) {
            if (in_array($tabs2[2][$key], ['', ' ', 'ssnr', 'pay_type', 'need_num_0', 'qmkg_url', 'kszp_url', 'kszy_url', 'kszp_dwz', 'qint'], true)) {
                continue;
            }
            $Data['input'][] = $tabs2[1][$key];
            $Data['parameter'][] = $tabs2[2][$key];
        }
        preg_match_all('/<div class="col-md-12 banner">(.*?)<\/div>.*?<\/div>.*?<\/div>.*?<!--内容-->/s', $HtmlData, $docs);
        $docs = str_replace(["\n", "\r", "\t"], '', $docs[1][0]);
        if (empty($docs)) {
            $docs = '';
        }
        preg_match('/现金单价：<\/span><span(?:.*?)title="(.*?)">/s', $HtmlData, $money);

        if ($money[1] && strpos($money[1], '单价为') !== false && strpos($money[1], '元') !== false) {
            //取详细单价
            $Price = (float)getSubstr($money[1], '单价为', '元');
        } else {
            $Price = false;
        }
        preg_match('/<span class="user_rmb">(.*?)<\/span>元/', $HtmlData, $balance);
        if ($balance[1]) {
            //取账户余额
            $Money = $balance[1];
        } else {
            $Money = false;
        }
        return [
            'data' => $Data,
            'docs' => $docs,
            'price' => $Price,
            'balance' => $Money,
            'url' => false
        ];
    }

    /**
     * @param $data //用户账号密码数据
     * 获取登陆cookie
     */
    public static function LoginCookie($data)
    {
        mkdirs(SYSTEM_ROOT . 'extend/log/Cookie/');
        $cookie = SYSTEM_ROOT . 'extend/log/Cookie/' . md5($data['url'] . $data['username']) . '.cookie';
        //60秒过期
        if (file_exists($cookie)) {
            $lock_dates = filemtime($cookie);
            if ($lock_dates < (time() - 60)) {
                @unlink($cookie);
                return self::LoginCookie($data);
            }
            return $cookie;
        }
        $Curl_url = $data['url'] . 'index.php?m=Home&c=User&a=login';
        $Post_User = ['username' => $data['username'], 'username_password' => $data['password'], 'user_remember' => 1, 'sendpass_username' => '', 'reg_username' => '', 'reg_password' => '', 'reg_sex' => 0, 'reg_qq' => '', 'code' => '', 'id' => $data['gid'], 'goods_type' => $data['tyid']];
        $data = self::CurlCookieObtain($Curl_url, 0, $Post_User, $cookie);
        if (!$data) {
            @unlink($cookie);
            return false;
            //dies(-1, '当前对接的站点无法打开：' . $data['url']);
        } else if (strpos($data, '登录成功！')) {
            return $cookie;
        } else {
            @unlink($cookie);
            $data = self::error($data, 2);
            if ($data === '用户名或密码不正确！') {
                dies(-1, '用户名或密码不正确！');
            }
            dies(-1, '对接站点登陆Cookie获取失败！' . $data);
        }
    }

    /**
     * @param $Data
     * @return array
     * 获取商品详情,并且返回需替换的商品内容
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

        if (empty($_SESSION['JiuWuGoodsList_' . $Data['sqid']])) {
            return [
                'code' => -1,
                'msg' => '数据源获取失败，请重新获取一遍可对接商品列表！',
            ];
        }
        $Goods = $_SESSION['JiuWuGoodsList_' . $Data['sqid']][$Data['key']];
        if ($Goods['id'] != $Data['gid']) {
            return [
                'code' => -1,
                'msg' => '商品键值和数据源不匹配，请重新获取商品列表数据！',
            ];
        }

        $SourceData['url'] = StringCargo::UrlVerify($SourceData['url']);
        $SourceData['gid'] = $Goods['id'];
        $SourceData['tyid'] = $Goods['goods_type'];
        $His = self::TakeOutTheData($SourceData);

        if ($Goods['thumb']) {
            if (getSubstr($Goods['thumb'], '/image/', '/') < '2020-08-01') {
                $Goods['thumb'] = 'https://all-pt-upyun-cdn.95at.cn' . $Goods['thumb'];
            }
            if (strpos($Goods['thumb'], '//') === false) {
                $SourceData['url'] = StringCargo::UrlVerify($SourceData['url'], 2);
                $Goods['thumb'] = $SourceData['url'] . $Goods['thumb'];
            }
        } else {
            global $conf;
            $Goods['thumb'] = $conf['logo'];
        }

        if ($Goods['minbuynum_0'] <= 1) {
            $Goods['minbuynum_0'] = 1;
        }

        if ($His['price']) {
            //替换页面获取的真实单价
            $Goods['user_unitprice'] = $His['price'];
        }

        return [
            'code' => 1,
            'msg' => '对接参数获取成功！' . ($His['balance'] ? '<br>当前账户余额：<font color=red>' . $His['balance'] . '元</font>' : '') . ($His['url'] ? ' <hr> <span style="color:red">商品详情页：【' . $His['url'] . '】 打开失败，无法获取正确的对接信息和下单信息，请手动打开此页面获取！</span>' : ''),
            'data' => [
                'name' => $Goods['title'],
                'image' => $Goods['thumb'],
                'docs' => $His['docs'],
                'money' => $Goods['user_unitprice'] * $Goods['minbuynum_0'],
                'min' => 1,
                'max' => ($Goods['maxbuynum_0'] / $Goods['minbuynum_0']),
                'alert' => $Goods['goods_close_msg'],
                'quota' => 10000,
                'quantity' => $Goods['minbuynum_0'],
                'extend' => [
                    'gid' => $Goods['id'],
                    'cid' => $Goods['goods_type'],
                    'parameter' => implode(',', $His['data']['parameter']),
                ],
                'units' => $Goods['unit'],
                'input' => implode('|', $His['data']['input']),
            ]
        ];
    }


    /**
     * @param $url
     * @param $cookie
     * @param $post
     * @param int $cookie_fils
     * @return bool|string
     * Curl 携带或获取Cookie数据
     */
    public static function CurlCookieObtain($url, $cookie = 0, $post = 0, $cookie_fils = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        $httpheader[] = "Content-Type: application/x-www-form-urlencoded; charset=UTF-8";
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        }
        global $conf;
        if ($conf['PMIPState'] == 1) {
            curl_setopt($ch, CURLOPT_PROXY, $conf['PMIP']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $conf['PMIPPort']);
        }
        if ($cookie_fils) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_fils);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36');
        curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    /**
     * @param $OrderData //订单信息
     * @param $Goods //商品信息
     * @param $TypeSupply //对接货源信息
     * 提交下单信息！
     */
    public static function Submit($OrderData, $Goods, $TypeSupply)
    {
        global $conf;

        $GoodsData = json_decode($Goods['extend'], true); //对接数据

        $DataPost = [
            'Api_UserName' => $TypeSupply['username'],
            'Api_UserMd5Pass' => md5($TypeSupply['password']),
            'goods_id' => $GoodsData['gid'],
            'goods_type' => $GoodsData['cid'],
            'need_num_0' => ($Goods['quantity'] * $OrderData['num']), //份数×数量
            'pay_type' => $TypeSupply['pattern'],
        ];
        $InputArray = json_decode($OrderData['input'], TRUE);

        if ((int)$Goods['specification'] === 2 && (int)$Goods['specification_type'] === 2) {
            $InputArray = RuleSubmitParameters(json_decode($Goods['specification_spu'], TRUE), $InputArray);
        }

        $Field = explode(',', $GoodsData['parameter']);
        $PostInput = [];
        $i = 0;
        foreach ($Field as $value) {
            $PostInput += [$value => $InputArray[$i]];
            $i++;
        }

        $DataPost = array_merge($DataPost, $PostInput);

        $DataCurl = Api::SuppluCurl($TypeSupply['url'] . 'index.php?m=home&c=order&a=add', $DataPost);
        $DataCurlCopy = $DataCurl;
        $DataCurl = json_decode($DataCurl, TRUE);

        if (empty($DataCurl)) {
            $Error = self::error($DataCurlCopy, 2);
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => (!$Error ? '对接返回信息有误，请根据对接日志调试！' : $Error),
                'money' => 0,
                'order' => 0,
            ];
        }

        if (!isset($DataCurl['status'])) {
            $Msg = self::error($DataCurlCopy);
            $code = $conf['SubmitState'];
            $money = 0;
            $order = -1;
            $docking = 2;
        } else {
            $Msg = $DataCurl['info'];
            $code = ($DataCurl['status'] >= 0 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']);
            $money = ($DataCurl['status'] >= 0 ? $DataCurl['after_use_rmb'] : 0);
            $order = ($DataCurl['status'] >= 0 ? $DataCurl['order_id'] : -1);

            $docking = ($DataCurl['status'] >= 0 ? 1 : 2);
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
            'Api_UserName' => $TypeSupply['username'],
            'Api_UserMd5Pass' => md5($TypeSupply['password']),
            'return_fields' => 'id,user_note,aa,bb,cc,dd,ee,need_num_0,need_num_1,need_num_2,need_num_3,start_num,end_num,now_num,order_state,login_state,start_time,end_time,add_time,order_amount,order_cardnum,tui_amount,tui_cardnum,user_id,card_id,order_pay_type,goods_id,goods_type,goods_type_title',
            'orders_id' => $id
        ];
        $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);
        $Data = Api::Curl($TypeSupply['url'] . 'index.php?m=Home&c=Order&a=query_orders_detail', $DataPost);
        $Data = json_decode($Data, true);
        if ($Data == null || $Data['status'] <> true) return [];
        $Data = $Data['rows'][0];
        switch ($Data['order_state']) {
            case 0:
            case 1:
                $order_state = '未开始';
                $StateNum = 4;
                break;
            case 2:
                $order_state = '进行中';
                $StateNum = 4;
                break;
            case 3:
                $order_state = '已完成';
                $StateNum = 1;
                break;
            case 4:
                $order_state = '已退单';
                $StateNum = 5;
                break;
            case 5:
                $order_state = '退单中';
                $StateNum = 3;
                break;
            case 6:
                $order_state = '续费中';
                $StateNum = 4;
                break;
            case 7:
                $order_state = '补单中';
                $StateNum = 4;
                break;
            case 8:
                $order_state = '改密中';
                $StateNum = 3;
                break;
            case 9:
                $order_state = '登录失败';
                $StateNum = 3;
                break;
            default:
                $order_state = '未知状态';
                $StateNum = 3;
                break;
        }

        switch ($Data['login_state']) {
            case 0:
                $login_state = '权限异常！';
                break;
            case 1:
                $login_state = '登录保护！';
                break;
            case 2:
                $login_state = '号码冻结！';
                break;
            case 3:
                $login_state = '密码错误！';
                break;
            default:
                $login_state = '未知异常!';
                break;
        }

        return [
            'ApiType' => 1,
            'ApiNum' => $Data['need_num_0'],
            'ApiTime' => $Data['add_time'],
            'ApiInitial' => $Data['start_num'],
            'ApiPresent' => $Data['now_num'],
            'ApiState' => $order_state,
            'ApiError' => ($Data['order_state'] === 9 ? $login_state : ''),
            'ApiStateNum' => $StateNum,
        ];
    }
}
