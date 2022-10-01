<?php

use lib\Hook\Hook;
use lib\Pay\Pay;
use lib\supply\Price;
use Medoo\DB\SQL;
use voku\helper\AntiXSS;

/**
 * 计算不提交商品规则模式下的提交参数
 */
function RuleSubmitParameters($RuleArr, $InputData)
{
    if (is_array($RuleArr)) {
        $is = count($RuleArr);
    } else {
        $is = 1;
    }
    $cc = 0;
    $DataIn = [];
    foreach ($InputData as $value) {
        ++$cc;
        if ($cc <= $is) continue;
        $DataIn[] = $value;
    }
    return $DataIn;
}

function array_sort($array, $keys, $type = 'asc')
{
    $Data = [];
    foreach ($array as $key => $v) {
        $Data[$key] = $v[$keys];
    }
    array_multisort($Data, ($type == 'asc' ? SORT_ASC : SORT_DESC), $array);
    return $array;
}

/**
 * @param $specification //规格开关
 * @param $specification_spu //规格名称
 * @param $Input //下单输入框
 * 下单输入框解析，传值均未JSON解码
 */
function CommodityInputBoxName($Input, $specification, $specification_spu)
{
    $CacheName = md5($Input . $specification . $specification_spu);
    if (!empty(CookieCache::$Cache['CommodityInputBoxName'][md5($CacheName)])) {
        return CookieCache::$Cache['CommodityInputBoxName'][md5($CacheName)];
    }
    $InputName = [];
    if ((int)$specification === 2) {
        foreach (json_decode($specification_spu, true) as $key => $vs) {
            $InputName[] = $key;
        }
    }
    foreach (explode('|', $Input) as $value) {
        if (strpos($value, '{') !== false && strpos($value, '}') !== false) {
            $value = explode('{', $value)[0];
            $InputName[] = $value;
        } else {
            $InputName[] = $value;
        }
    }
    CookieCache::$Cache['CommodityInputBoxName'][md5($CacheName)] = $InputName;
    return $InputName;
}

/**
 * @param $db 数据库
 * @return false|Redis
 * Redis数据库连接
 */
function Redis($db = 0)
{
    return false;
    global $RedisConfig;
    if ($RedisConfig['REDIS_TYPE'] === true) {
        $Redis = new Redis();
        $Redis->connect($RedisConfig['REDIS_HOST'], $RedisConfig['REDIS_PORT']);
        if ($RedisConfig['REDIS_PASSWORD'] !== false) {
            $Redis->auth($RedisConfig['REDIS_PASSWORD']);
        }
        $Redis->select($db);
        if (!$Redis->ping()) {
            return false;
        }
        return $Redis;
    }

    return false;
}

/**
 * 数据重复提交验证
 * @param $Lab_ms 拦截毫秒时间 1000 = 1秒
 * @param $smg 返回格式，JSON，HTML
 * @param $type = 1，根据请求内容验证，type = 2 不根据请求内容，根据IP验证
 * @param $Lab_type 拦截模式，1强硬，2软弱
 * @param $Lab_state 拦截范围 1整站请求，2、根据IP，若type=2则此项失效！
 * @param $Msg 自定义说明
 */
function RVS($Lab_ms = 100, $smg = 'JSON', $type = 1, $Lab_type = 1, $Lab_state = 2, $Msg = null)
{
    global $_QET, $RedisVerifyConfig;
    if (($type === 1) && count($_QET) === 0) {
        return;
    }

    if ($RedisVerifyConfig['REDIS_TYPE'] === true) {
        //走本地Redis模式
        RedisRvs($Lab_ms, $smg, $type, $Lab_type, $Lab_state, $Msg);
    } else {
        //走本地模式
        $QetData = $_QET;
        unset($_QET);
        array_multisort($QetData);
        if ($type === 2) {
            $Md5 = md5(userip());
        } else {
            $Md5 = md5(json_encode($QetData) . ($Lab_state === 1 ? '' : userip()));
        }
        $Flie = ROOT . 'includes/extend/log/Home/Validation_' . $Md5;
        if (file_exists($Flie) === false) {
            @file_put_contents($Flie, msectime());
        } else {
            $Mstime = file_get_contents($Flie);
            $MSV = (msectime() - $Mstime); //执行时间
            if ($MSV < $Lab_ms) {
                if ($Lab_type === 1 && $MSV > 50) {
                    @file_put_contents($Flie, msectime());
                }
                if ($smg === 'JSON') {
                    if ($type === 1) {
                        dier([
                            'code' => -2,
                            'msg' => ($Msg === null ? '请不要重复提交相同内容,请稍后再试！' : $Msg) . '，请于' . (($Lab_ms - $MSV) / 1000) . '秒后重试！',
                        ]);
                    } else {
                        dier([
                            'code' => -2,
                            'msg' => ($Msg === null ? '请求太过于频繁,您已被小储安全系统拦截！' : $Msg) . '，请于' . (($Lab_ms - $MSV) / 1000) . '秒后重试！',
                        ]);
                    }
                } else {
                    if ($type === 1) {
                        show_msg(($Msg === null ? '请不要重复提交相同内容,请于' . (($Lab_ms - $MSV) / 1000) . '秒后再试！！<hr><a href=javascript:location.reload() >刷新界面</a>' : $Msg), 2, '/', false, '6');
                    } else {
                        show_msg(($Msg === null ? '访问太过于频繁,您已被安全系统拦截,请于' . (($Lab_ms - $MSV) / 1000) . '秒后再试！<hr><a href=javascript:location.reload() >刷新界面</a>' : $Msg), 2, '/', false, '6');
                    }
                }
            } else {
                @file_put_contents($Flie, msectime());
            }
        }
    }
}

function RedisRvs($Lab_ms = 100, $smg = 'JSON', $type = 1, $Lab_type = 1, $Lab_state = 2, $Msg = null)
{
    global $RedisVerifyConfig, $_QET;
    $Redis = Redis(12);
    if ($Redis === false) {
        $RedisVerifyConfig['REDIS_TYPE'] = false;
        RVS($Lab_ms, $smg, $type, $Lab_type, $Lab_state, $Msg);
        return;
    }
    $QetData = $_QET;
    unset($_QET);
    array_multisort($QetData);
    if ($type === 2) {
        $Md5 = md5(userip());
    } else {
        $Md5 = md5(json_encode($QetData) . ($Lab_state === 1 ? '' : userip()));
    }
    if (empty($Redis->get($Md5))) {
        $Redis->psetex($Md5, $Lab_ms, msectime());
    } else {
        $MSV = (msectime() - $Redis->get($Md5));
        if ($MSV < $Lab_ms) {
            if ($Lab_type === 1 && $MSV > 50) {
                $Redis->psetex($Md5, $Lab_ms, msectime());
            }
            if ($smg == 'JSON') {
                if ($type === 1) {
                    dier([
                        'code' => -2,
                        'msg' => ($Msg == null ? '请不要重复提交相同内容,请稍后再试！' : $Msg) . '，请于' . (($Lab_ms - $MSV) / 1000) . '秒后重试！',
                    ]);
                } else {
                    dier([
                        'code' => -2,
                        'msg' => ($Msg == null ? '请求太过于频繁,您已被小储安全系统拦截！' : $Msg) . '，请于' . (($Lab_ms - $MSV) / 1000) . '秒后重试！',
                    ]);
                }
            } else if ('HTML') {
                if ($type === 1) {
                    show_msg('警告', ($Msg == null ? '请不要重复提交相同内容,请于' . (($Lab_ms - $MSV) / 1000) . '秒后再试！！<hr><a href=javascript:location.reload() >刷新界面</a>' : $Msg), 2, './', false);
                } else {
                    show_msg('警告', ($Msg == null ? '访问太过于频繁,您已被安全系统拦截,请于' . (($Lab_ms - $MSV) / 1000) . '秒后再试！<hr><a href=javascript:location.reload() >刷新界面</a>' : $Msg), 2, './', false);
                }
            }
        } else {
            $Redis->psetex($Md5, $Lab_ms, msectime());
        }
    }
}

/**
 * 获取时间戳，毫秒级
 */
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

/**
 * @param false|array $User
 * 获取用户头像
 */
function UserImage($User = false)
{
    if (empty($User)) {
        return href(2) . ROOT_DIR . 'assets/img/user.png';
    }
    $Image = '';
    if (!empty($User['qq'])) {
        $Image = 'https://q4.qlogo.cn/headimg_dl?dst_uin=' . $User['qq'] . '&spec=100';
    } else {
        $Image = ImageUrl($User['image']);
    }
    if (empty($Image)) {
        return href(2) . ROOT_DIR . 'assets/img/user.png';
    }
    return $Image;
}

/**
 * @param $User
 * @param int $type
 * @return array|int|mixed
 * 根据用户ID取出对应等级参数
 * 或，给出等级列表
 */
function RatingParameters($User, $type = 1)
{
    $Name = md5('RatingParameters');
    if (!empty(CookieCache::$Cache['GradeList'][$Name])) {
        $mid_arr = CookieCache::$Cache['GradeList'][$Name];
    } else {
        $mid_arr = SQL::DB()->select('price', '*', ['state' => 1, 'ORDER' => ['sort' => 'DESC']]);
        if (!$mid_arr) {
            $mid_arr = [];
        }
        CookieCache::$Cache['GradeList'][$Name] = $mid_arr;
    }

    if ($type === 1 && ($User == -1 || $User == false)) {
        return -1;
    }

    if ($type === 1 && count($mid_arr) === 0) {
        return -1;
    }

    $Name2 = md5('RatingParameters2' . $User['grade'] . $type);
    if (!empty(CookieCache::$Cache['GradeList'][$Name2])) {
        return CookieCache::$Cache['GradeList'][$Name2];
    }

    if ($User['grade'] >= count($mid_arr)) {
        $User['grade'] = count($mid_arr);
    }

    $UserGid = $User['grade'] - 1;
    $MidData = [];
    foreach (array_reverse($mid_arr) as $key => $val) {
        $val['sort'] = ($key + 1);
        $val['mid'] = ($key + 1);
        $MidData[] = $val;
    }

    if ($type === 1) {
        $Data = $MidData[$UserGid];
        CookieCache::$Cache['GradeList'][$Name2] = $Data;
        return $Data;
    }
    CookieCache::$Cache['GradeList'][$Name2] = $MidData;
    return $MidData;
}

function SubordinateUserMoney($Uid, $id)
{
    $DB = SQL::DB();
    $Money = $DB->sum('journal', 'count', [
        'uid' => $Uid,
        'name' => '余额提成',
        'content[~]' => `[` . $id . `]`,
    ]);

    $Money += $DB->sum('journal', 'count', [
        'uid' => $Uid,
        'name' => '升级提成',
        'content[~]' => `[` . $id . `]`,
    ]);

    $Currency = $DB->sum('journal', 'count', [
        'uid' => $Uid,
        'name' => '货币提成',
        'content[~]' => `[` . $id . `]`,
    ]);

    return [
        'money' => ($Money <= 0 ? 0 : $Money),
        'currency' => ($Currency <= 0 ? 0 : $Currency),
        'uid' => $id,
    ];
}

/**
 * @param $User
 * @param int $index
 * @param false $Uid
 * @return array|int
 * 取出下级列表！+收益！
 */
function SubordinateUser($User, $index = 1, $Uid = false)
{
    $DB = SQL::DB();
    $Res = $DB->select('user', ['image', 'qq', 'grade', 'id', 'found_date(date)', 'superior', 'name'], [
        'id[!]' => $User['id'],
        'state' => 1,
        'grade[<]' => $User['grade'],
        'superior' => $User['id'],
    ]);

    if (count($Res) >= 1) {
        $Datas = [];
        foreach ($Res as $v) {
            $v['id'] -= 0;
            $v['index'] = $index;
            $MidData = RatingParameters($v);
            $v['gradename'] = ($MidData === -1 ? $v['grade'] : $MidData['name'] . '(' . $v['grade'] . ')');
            $Datas[] = $v;
            $Image = '';
            if (!empty($v['qq'])) {
                $Image = 'https://q4.qlogo.cn/headimg_dl?dst_uin=' . $v['qq'] . '&spec=100';
            } else {
                $Image = $v['image'];
            }
            $v['image'] = $Image;
            $v['name'] = (empty($v['name']) ? '平台用户' : $v['name']);
            unset($v['qq'], $Image);
            $Data = SubordinateUser($v, ($index + 1));
            if ($Data === -1) continue;
            $Datas += $Data;
        }
        return $Datas;
    }

    return -1;
}

/**
 * @param $data
 * 活动参数解析
 */
function SeckillAnalysis($v)
{
    $T1 = strtotime($v['start_time']);
    $T2 = strtotime($v['end_time']);
    $DB = SQL::DB();
    $v['attend'] = $DB->count('order', [
            'gid' => $v['gid'],
            'addtitm[>]' => $v['start_time'],
            'addtitm[<]' => $v['end_time']
        ]) - 0;

    if ($v['attend'] >= $v['astrict']) {
        $v['attend'] = $v['astrict'];
    }

    if ($T1 >= time()) {
        $v['start'] = Sec2Time($T1 - time()) . '后开始';
        $v['end'] = '活动未开始';
        $v['state'] = 2;
    } else if ($T2 - time() <= 0) {
        $v['start'] = '活动已结束';
        $v['end'] = '活动已结束';
        $v['state'] = -1;
    } else {
        $v['start'] = '活动已开始';
        $v['end'] = Sec2Time($T2 - time()) . '后结束';
        $v['state'] = 1;
    }

    $v['start_time'] = $T1 - time();
    $v['end_time'] = $T2 - time();

    $v['depreciate'] -= 0;
    $v['astrict'] -= 0;
    unset($v['id']);

    return $v;
}

/**
 * @param $Data
 * 标签解析为数组
 */
function LabelaAnalysis($Data)
{
    if (!empty(CookieCache::$Cache['LabelaAnalysis'][md5($Data)])) {
        return CookieCache::$Cache['LabelaAnalysis'][md5($Data)];
    }
    global $conf;
    if (empty($Data)) {
        $Data = explode('|', $conf['DefaultLabel']);
    } else {
        $Data = explode('|', $Data);
        if (count($Data) === 0) {
            $Data = explode('|', $conf['DefaultLabel']);
        }
    }
    if (count($Data) === 0) {
        $Data = ['官方'];
    }
    $Label = [];
    foreach ($Data as $v) {
        $v = explode(',', $v);
        $Label[] = [
            'name' => $v[0],
            'color' => (empty($v[1]) ? 'red' : $v[1])
        ];
    }
    CookieCache::$Cache['LabelaAnalysis'][md5(json_encode($Data))] = $Label;
    return $Label;
}


/**
 * @param $Gid
 * @return false|int|mixed|string
 * 商品销量快速解析
 */
function CommoditySalesAnalysis($Gid, $sales = 0)
{
    global $conf;
    if (!empty($sales) && $sales >= 1) {
        $sums = $sales;
    } else {
        $sums = $conf['SalesSum'];
    }
    $DB = SQL::DB();
    if (empty($_SESSION['GIDSALES_' . $Gid])) {
        $SALES = $sums + round(($sums * (rand(1, 50) / 100)), 0);

        $_SESSION['GIDSALES_' . $Gid] = $SALES;

    } else {
        $SALES = $_SESSION['GIDSALES_' . $Gid];
    }
    return $DB->sum('order', 'num', ['gid' => $Gid]) + $SALES;
}

/**
 * @param $Data
 * @return int
 * 支持的付款方式解析
 */
function PaymentMethodAnalysis($Data)
{
    $Type = 2;
    if (in_array(2, $Data) && in_array(3, $Data)) {
        //支持余额+积分
        $Type = 1;
    } elseif (in_array(2, $Data) && !in_array(3, $Data)) {
        //支持余额不支持积分
        $Type = 2;
    } elseif (!in_array(2, $Data) && in_array(3, $Data)) {
        //不支持余额支持积分
        $Type = 3;
    }
    return $Type;
}

/**
 * @param array $Goods //商品数据
 * @param int $type //1显示成本，2不显示，3不显示详细参数
 * @param false $User //用户数据
 * @param false $pushMoney //是否获取原价，不计算店铺加价
 * @return array|int
 * 商品规格解析模块
 */
function RlueAnalysis($Goods = [], $type = 2, $User = false, $pushMoney = false)
{
    $SkuJson = json_decode($Goods['specification_sku'], true);
    if (empty($SkuJson)) {
        return -1;
    }
    $min = 99999998861;
    $max = 0;
    $minSup = 99999998861;
    $maxSup = 0;
    $mininventory = 99999998861;
    $maxinventory = 0;
    $mincount = 99999998861;
    $maxcount = 0;
    if ($User === false || $User === -1) {
        $User = login_data::user_data();
    }

    $ImageGoods = [];
    if (is_array($Goods['image'])) {
        $ImageGoodsArr = $Goods['image'];
    } else {
        $ImageGoodsArr = json_decode($Goods['image'], true);
    }
    if (!is_array($ImageGoodsArr)) {
        $ImageGoods[] = ImageUrl($Goods['image']);
    } else {
        foreach ($ImageGoodsArr as $v) {
            $ImageGoods[] = ImageUrl($v);
        }
    }
    unset($ImageGoodsArr);
    $LevelName = '';
    $SkuData = [];
    foreach ($SkuJson as $key => $value) {
        foreach ($value as $k => $v) {
            if ($value[$k] === "") {
                if ($k === 'image') {
                    $value[$k] = $ImageGoods;
                } else {
                    if (!isset($Goods[$k])) {
                        $Goods[$k] = "";
                    }
                    $value[$k] = $Goods[$k];
                }
            } else if ($k === 'image') {
                $value[$k] = [ImageUrl($value[$k])];
            }

            if ($k === 'quantity' || $k === 'min' || $k === 'max') {
                $value[$k] = (int)$value[$k];
                if ($value[$k] <= 0) {
                    $value[$k] = 1;
                }
            }

            if ($k === 'quota') {
                $value[$k] = (int)$value[$k];
            }
        }

        /**
         * 计算剩余库存
         */
        if ($value['quota'] >= $maxinventory) {
            $maxinventory = $value['quota'];
        }

        if ($mininventory >= $value['quota']) {
            $mininventory = $value['quota'];
        }

        /**
         * 此处计算价格最大最小值，计算价格区间
         */

        if ($value['quantity'] >= $maxcount) {
            $maxcount = $value['quantity'];
        }

        if ($mincount >= $value['quantity']) {
            $mincount = $value['quantity'];
        }

        if ($pushMoney === false) {
            $PriceList = Price::List($value['money'], $Goods['profits'], $Goods['gid'], $Goods['selling']);
            $Price = Price::Get($value['money'], $Goods['profits'], (!$User ? 1 : $User['grade']), $Goods['gid'], $Goods['selling']);
        } else {
            $PriceList = Price::List($value['money'], $Goods['profits'], false, $Goods['selling']);
            $Price = Price::Get($value['money'], $Goods['profits'], (!$User ? 1 : $User['grade']), false, $Goods['selling']);
        }

        $LevelName = $Price['name'];
        $value['price'] = $Price['price'];
        $value['points'] = $Price['points'];

        if ($type === 2) {
            unset($value['money']);
        }

        if ($value['price'] >= $max) {
            $max = $value['price'];
        }

        if ($min >= $value['price']) {
            $min = $value['price'];
        }

        if ($value['points'] >= $maxSup) {
            $maxSup = $value['points'];
        }

        if ($minSup >= $value['points']) {
            $minSup = $value['points'];
        }

        $value['level_arr'] = $PriceList;
        $SkuData[$key] = $value;
    }

    if ($min === 99999998861) {
        $min = 0;
    }
    if ($minSup === 99999998861) {
        $minSup = 0;
    }
    if ($mininventory === 99999998861) {
        $mininventory = 0;
    }
    if ($mincount === 99999998861) {
        $mincount = 0;
    }
    $MasterRule = [];
    foreach (json_decode($Goods['specification_spu'], true) as $key => $v) {
        $MasterRule[] = $key;
    }

    $Data = [
        'Parameter' => $SkuData,
        'level' => $LevelName,
        'Price' => [
            'min' => $min,
            'max' => $max
        ],
        'Points' => [
            'min' => $minSup,
            'max' => $maxSup
        ],
        'Inventory' => [ //剩余库存
            'min' => $mininventory,
            'max' => $maxinventory
        ],
        'Quantity' => [ //数量区间
            'min' => $mincount,
            'max' => $maxcount
        ],
        'MasterRule' => $MasterRule,
    ];

    if ($type === 3) {
        unset($Data['Parameter']);
    }

    return $Data;
}

/**
 * @param $Url
 * @return array
 * 取出火山ID+视频ID
 */
function GetHSID($Url)
{
    if (substr($Url, 0, 4) != "http") {
        $Url = substr($Url, strpos($Url, "http"));
        if (strpos($Url, " ") > 0) {
            $Url = substr($Url, 0, strpos($Url, " "));
        }
    }
    $arope = parse_url($Url);
    if (substr($arope["host"], -11) == "huoshan.com") {
        if ($arope["host"] == "share.huoshan.com") {
            $proc = get_curl($Url, 0, 0, 0, 1);
            preg_match("/Location: (.*?)\r\n/", urldecode($proc), $cmlpt);
            if (!$cmlpt[1]) dies(-1, '链接解析失败');
            $Url = $cmlpt[1];
        }

        if (strpos($Url, "item_id=")) {
            $data_arl = substr($Url, -19);
        } else dies(-1, '火山小视频链接解析失败');
        $video = 'https://api.huoshan.com/hotsoon/item/video/_reflow/?video_id=v0200cc40000bnjsc6cm7fi328mvli10&line=0&app_id=0&vquality=normal&watermark=2&long_video=0&sf=5&ts=' . time() . '&item_id=' . $data_arl;
        $data = get_curl($video);
        preg_match_all("/href=\"([^\"]+)/", $data, $Array);
        return [
            'code' => 1,
            'msg' => '火山小视频ID获取成功',
            'hsid' => $data_arl,
            'video' => $Array[1][0]
        ];
    } else dies(-1, '请输入正确的链接');
}

/**
 * @param $Url
 * 获取微视频ID
 */
function GetWSID($Url)
{
    if (!strstr($Url, '.qq.com')) dies(-1, '请填写正确的链接！');

    if (strstr($Url, 'feed/')) {
        $Url = strstr($Url, 'feed/');
        $Url = strstr(explode('feed/', $Url)[1], '/', true);
    } else if (strstr($Url, 'personal/')) {
        $Url = strstr($Url, 'personal/');
        $Url = strstr(explode('personal/', $Url)[1], '/', true);
    } else if (strstr($Url, 'id=')) {
        $Url = strstr($Url, 'id=');
        $Url = strstr(explode('id=', $Url)[1], '&', true);
    } else dies(-1, 'ID获取失败,请填写正确的链接！');
    if ($Url == '') dies(-1, 'ID获取失败,请填写正确的链接！');
    return [
        'code' => 1,
        'msg' => '微视ID获取成功',
        'wsid' => $Url,
    ];
}

function GetJRTTID($Url)
{
    if (!strstr($Url, '.toutiaocdn.com')) dies(-1, '请填写正确的链接！');
    if (strstr($Url, 'group/')) {
        $Url = strstr($Url, 'group/');
        $Url = strstr(explode('group/', $Url)[1], '/', true);
    } else if (strstr($Url, 'group_id=')) {
        $Url = strstr($Url, 'group_id=');
        $Url = explode('group_id=', $Url)[1];
    } else dies(-1, 'ID获取失败,请填写正确的链接！');
    if ($Url == '') dies(-1, 'ID获取失败,请填写正确的链接！');

    return [
        'code' => 1,
        'msg' => '微视ID获取成功',
        'jrttid' => $Url,
    ];
}

/**
 * @param $Url
 * @return array
 * 获取皮皮虾ID
 */
function PPXID($Url)
{
    if (substr($Url, 0, 4) != "http") {
        $Url = substr($Url, strpos($Url, "http"));
        if (strpos($Url, "，") > 0) {
            $Url = substr($Url, 0, strpos($Url, "，"));
        }
    }
    $Curls = parse_url($Url);
    if (substr($Curls["host"], -9) == "pipix.com") {
        $proc = get_curl($Url, 0, 0, 0, 1);
        preg_match("/location: (.*?)\r\n/", urldecode($proc), $cmlpt);
        if (!$cmlpt[1]) dies(-1, '链接解析失败');
        $Url = getSubstr($cmlpt[1], 'item/', '?app_id=');
        if ($Url == '') dies(-1, 'ID获取失败！');
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'ppxid' => $Url,
        ];
    } else  dies(-1, '请输入正确的链接！');
}

/**
 * @param $Url
 * @return array
 * 获取小红书ID
 */
function XHSID($Url)
{
    if (substr($Url, 0, 4) != "http") {
        $Url = substr($Url, strpos($Url, "http"));
        if (strpos($Url, "，") > 0) {
            $Url = substr($Url, 0, strpos($Url, "，"));
        }
    }
    $Curls = parse_url($Url);

    if (substr($Curls["host"], 0) == "xhslink.com") {
        $proc = get_curl($Url, 0, 0, 0, 1);
        preg_match("/Location: (.*?)\r\n/", urldecode($proc), $cmlpt);

        if (!$cmlpt[1]) dies(-1, '链接解析失败');
        $Url = getSubstr($cmlpt[1], 'discovery/item/', '?xhsshare=');
        if ($Url == '') dies(-1, 'ID获取失败！');
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'xhsid' => $Url,
        ];
    } else  dies(-1, '请输入正确的链接！');
}

/**
 * @param $Url
 * @return array
 * 获取B站ID
 */
function BLIID($Url)
{
    if (substr($Url, 0, 4) != "http") {
        $Url = substr($Url, strpos($Url, "http"));
        if (strpos($Url, "，") > 0) {
            $Url = substr($Url, 0, strpos($Url, "，"));
        }
    }
    $Curls = parse_url($Url);
    if (substr($Curls["host"], 0) == "b23.tv" || substr($Curls["host"], 0) == "www.bilibili.com") {
        $proc = get_curl($Url, 0, 0, 0, 1);
        preg_match("/Location: (.*?)\r\n/", urldecode($proc), $cmlpt);
        if (strstr($cmlpt[1], 'https://www.bilibili.com/bangumi/play/')) {
            $Url = explode('/play/', strstr($cmlpt[1], '/play/'))[1];
        } else {
            if (!$cmlpt[1]) dies(-1, '链接解析失败');
            $Url = getSubstr($cmlpt[1], 'video/', '?p=1');
        }
        if ($Url == '') dies(-1, 'ID获取失败！');
        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'bliid' => $Url,
        ];
    } else  dies(-1, '请输入正确的链接！');
}

/**
 * @param $Url
 * @return array
 * 获取小红书ID
 */
function MPID($Url)
{
    if (!strstr($Url, '.meipai.com')) dies(-1, '请填写正确的链接！');
    if (strstr($Url, 'media/')) {
        $Url = strstr($Url, 'media/');
        $Url = strstr(explode('media/', $Url)[1], '?client_id=', true);
    } else dies(-1, 'ID获取失败,请填写正确的链接！');
    if ($Url == '') dies(-1, 'ID获取失败,请填写正确的链接！');

    return [
        'code' => 1,
        'msg' => 'ID获取成功',
        'mpid' => $Url,
    ];
}

/**
 * 在字符串内提取出URL
 * 单个！
 */
function URLExtraction($Url)
{
    $Url = UrlExtA($Url);
    if (strstr($Url, " ")) {
        $ar = explode(" ", $Url);
        if (strstr($ar[0], 'http')) {
            $Url = $ar[0];
        } else $Url = $ar[1];
    }
    return trim($Url);
}

function UrlExtA($Url)
{
    if (!strstr($Url, '//')) return trim($Url);
    if (strstr($Url, 'http://')) {
        $Url = 'http://' . explode('http://', $Url)[1];
    } else if (strstr($Url, 'https://')) {
        $Url = 'https://' . explode('https://', $Url)[1];
    }
    if (strstr($Url, '，')) $Url = strstr($Url, '，', true);
    $S = "/^(http|https|ftp):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\”])*$/u";
    preg_match_all($S, $Url, $Array);
    if ($Array[0][0] != '') {
        $Url = trim($Array[0][0]);
    }
    preg_match_all("/https:[\/]{2}[a-z]+[.]{1}[a-z\d\-]+[.]{1}[a-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d][\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*/", $Url, $Array2);
    if ($Array2[0][0] != '') {
        $Url = trim($Array2[0][0]);
    }
    preg_match_all("/http:[\/]{2}[a-z]+[.]{1}[a-z\d\-]+[.]{1}[a-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d][\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*/", $Url, $Array2);
    if ($Array2[0][0] != '') {
        $Url = trim($Array2[0][0]);
    }
    $Url2 = preg_replace('/([\x80-\xff]*)/i', '', $Url);
    if ($Url2) {
        $Url = $Url2;
    }
    return trim($Url);
}

/**
 * Class query
 * 前台查单类
 */
class query
{
    /**
     * @param $val 查单信息
     * 根据下单信息取出未绑定用户的订单！
     */
    public static function QueryList($val)
    {
        global $conf;
        if (empty($val)) {
            dies(-2, '请将搜索内容填写完整！');
        }
        $DB = SQL::DB();

        $SQL = [
            'order.uid[<]' => 1,
            'order.input[~]' => $val,
            'ORDER' => [
                'order.id' => 'DESC',
            ]
        ];

        if (strstr($val, '#')) {
            $Ex = explode('#', $val);
            $SQL['order.input[~]'] = $Ex[0] ?? $Ex[1] ?? $Ex[2] ?? $Ex[3];
        }

        $Res = $DB->select('order', [
            '[>]goods' => ['gid', 'gid'],
            '[>]mark' => ['order' => 'order'],
            '[>]tickets' => ['order' => 'order']
        ], [
            'order.id',
            'order.order',
            'order.ip',
            'order.input',
            'order.num',
            'order.gid',
            'order.payment',
            'order.price',
            'order.originalprice',
            'order.coupon',
            'order.take',
            'goods.quantity',
            'goods.profits',
            'goods.selling',
            'goods.specification',
            'goods.specification_spu',
            'goods.specification_sku',
            'order.addtitm(addtime)',
            'order.finishtime(endtime)',
            'order.state',
            'order.remark',
            'goods.name',
            'goods.image',
            'goods.units',
            'goods.input(value)',
            'order.logistics',
            'mark.content',
            'mark.addtime(markaddtime)',
        ], $SQL);
        $Data = [];
        foreach ($Res as $value) {
            $Input = json_decode($value['input'], TRUE);
            if ($Input[0] <> $val) continue;
            unset($Input);
            $Input = [];

            foreach (explode('|', $value['value']) as $k => $v) {
                if (strstr($v, '{') && strstr($v, '}')) {
                    $Input[] = explode('{', $v)[0];
                } else if ($value['specification'] == 2) {
                    $Input[] = $v;
                } else {
                    $Input[] = (empty($v) ? '输入框' . ($k + 1) : $v);
                }
            }

            if ($value['specification'] == 2) {
                $SpRule = RlueAnalysis($value, 3);
                if ($SpRule != -1) {
                    if (empty($Input[0]) && empty($Input[1])) {
                        $Input = $SpRule['MasterRule'];
                    } else $Input = array_merge($SpRule['MasterRule'], $Input);
                }
            }

            $Token = $DB->select('token', ['token'], ['order' => $value['order']]);

            if (empty($value['remark'])) {
                $value['remark'] = -1;
            }
            if (empty($value['units'])) {
                $value['units'] = '个';
            }
            if (!empty($value['logistics'])) {
                $value['logistics'] = explode('|', $value['logistics']);
            } else {
                $value['logistics'] = -1;
            }

            if (empty($value['quantity'])) {
                $value['quantity'] = 1;
            }

            $value['take'] = (int)$value['take'];
            $value['Token'] = $Token;
            $value['state'] = (int)$value['state'];
            $value['price'] = round($value['price'], 8);
            $value['input'] = json_decode($value['input']);
            $value['value'] = $Input;
            $value['image'] = ImageUrl(json_decode($value['image'], true)[0]);
            $value['currency'] = $conf['currency'];
            if ($value['originalprice'] != -1) {
                $value['originalprice'] = round($value['originalprice'], 2);
            }
            $Data[] = $value;
            unset($Token, $value, $Input);
        }

        dier([
            'code' => 1,
            'msg' => '订单查询成功',
            'data' => $Data,
        ]);
    }

    /**
     * 查询订单队列内的订单
     * 待付款+待提交！
     */
    public static function QueueList($User)
    {
        global $conf, $date_30;
        $DB = SQL::DB();
        if ($User == false) {
            $Array = self::VisitorsOrderQueue();
            $SQL = [
                'queue.order' => $Array,
                'ORDER' => [
                    'queue.id' => 'DESC'
                ],
                'queue.type[!]' => 1
            ];
        } else {
            $SQL = [
                'queue.uid' => $User['id'],
                'ORDER' => [
                    'queue.id' => 'DESC'
                ],
                'queue.type[!]' => 1
            ];
        }

        $Res = $DB->select('queue', [
            '[>]goods' => [
                'gid' => 'gid'
            ]
        ], [
            'queue.id',
            'queue.type',
            'queue.order',
            'queue.ip',
            'queue.input',
            'queue.num',
            'queue.payment',
            'queue.price',
            'queue.remark',
            'queue.addtime',
            'goods.name',
            'goods.image',
            'goods.gid',
            'goods.input(value)',
            'goods.specification',
            'goods.specification_spu',
            'goods.specification_sku',
            'goods.profits',
            'goods.selling',
        ], $SQL);

        $Data = [];
        foreach ($Res as $value) {
            $Input = [];
            if (empty($value['value']) && empty($value['gid'])) {
                //商品丢失
                foreach (json_decode($value['input'], true) as $key => $v) {
                    $Input[] = '输入框' . ($key + 1);
                }
            } else {
                $Input = [];
                foreach (explode('|', $value['value']) as $v) {
                    if (strstr($v, '{') && strstr($v, '}')) {
                        $Input[] = explode('{', $v)[0];
                    } else {
                        $Input[] = $v;
                    }
                }
            }


            if ($value['specification'] == 2) {
                $SpRule = RlueAnalysis($value, 3);
                if ($SpRule != -1) {
                    $Input = array_merge($SpRule['MasterRule'], $Input);
                }
            }


            /**
             * 订单超时判断
             */
            if ($value['addtime'] < $date_30 && $value['type'] == 3) {
                $value['state'] = -1;
            } else {
                $value['state'] = 1;
            }

            $value['addtime'] = TimeLag($value['addtime']);

            $value['price'] = round($value['price'], 8);
            $value['input'] = json_decode($value['input'], true);
            $value['value'] = $Input;
            $value['image'] = ImageUrl(json_decode($value['image'], true)[0]);
            $value['currency'] = $conf['currency'];
            $Data[] = $value;
        }

        dier([
            'code' => 1,
            'msg' => '未完成订单列表获取成功！',
            'data' => $Data
        ]);
    }

    /**
     * 取出队列订单缓存列表
     * 只取出订单号！
     */
    public static function VisitorsOrderQueue()
    {
        $Array = json_decode(xiaochu_de($_COOKIE['QUEUEORDER'], 'Queue'), TRUE);
        if (count($Array) == 0) return [];

        $DB = SQL::DB();
        $Res = $DB->select('queue', ['order', 'trade_no'], [
            'order' => $Array,
            'uid' => -1,
            'type[!]' => 1
        ]);
        $Data = [];
        foreach ($Res as $v) {
            $Data = array_merge($Data, [$v['order']]);
        }
        return $Data;
    }

    /**
     * @param $ID
     * @param $User
     * 确认收货
     */
    public static function QueryTake($ID, $User)
    {
        $DB = SQL::DB();

        if ($User == false) {
            $Array = self::VisitorsOrder();
            $SQL = [
                'id' => (int)$ID,
                'trade_no' => $Array
            ];
        } else {
            $SQL = [
                'id' => (int)$ID,
                'uid' => (int)$User['id']
            ];
        }

        $Order = $DB->get('order', '*', $SQL);
        if (!$Order) dies(-1, '此订单非你所有！');

        $Res = $DB->update('order', [
            'state' => 1,
            'take' => 2,
        ], [
            'id' => $ID
        ]);

        if ($Res) {
            userlog('收货确认', '用户成功将订单号[' . $Order['id'] . ']设置为确认收货状态!', $Order['uid'], 0);
            $Order['state'] = 1;
            $Order['take'] = 2;
            Hook::execute('ConfirmReceipt', [
                'Order' => $Order
            ]);
            dies(1, '确认收货成功!');
        } else dies(-1, '确认收货失败,请联系管理员处理！');
    }

    /**
     * 取出已付款游客订单列表
     */
    public static function VisitorsOrder()
    {
        $Array = json_decode(xiaochu_de($_COOKIE['QUERYORDER'], 'Query'), TRUE);
        if (count($Array) === 0) {
            return [];
        }
        $DB = SQL::DB();
        $Res = $DB->select('pay', ['order', 'trade_no'], [
            'order' => $Array,
            'uid' => -1,
        ]);
        $Data = [];
        foreach ($Res as $v) {
            if (empty($v['trade_no'])) {
                continue;
            }
            $Data[] = $v['trade_no'];
        }
        return $Data;
    }

    /**
     * @param $ID
     * @param $User
     * 删除待付款队列订单
     */
    public static function QueryDelete($ID, $User)
    {
        $DB = SQL::DB();

        if (!$User) {
            $Array = self::VisitorsOrderQueue();
            $SQL = [
                'id' => (int)$ID,
                'order' => $Array
            ];
        } else {
            $SQL = [
                'id' => (int)$ID,
                'uid' => $User['id']
            ];
        }

        $Order = $DB->get('queue', '*', $SQL);
        if (!$Order) dies(-1, '此订单非你所有！');

        if ($Order['type'] != 3) dies(-1, '仅可删除待付款订单哦！');

        $Res = $DB->delete('queue', [
            'id' => $ID
        ]);

        if ($Res) {
            userlog('删除订单', '用户成功将待付款订单[' . $ID . ']删除!', $Order['uid'], 0);
            dies(1, '待付款订单删除成功!');
        } else dies(-1, '待付款订单删除失败！');
    }

    /**
     * 提交商品评论内容
     */
    public static function QueryMark($Data, $User)
    {
        global $date;
        if ($User == false) dies(-1, '评价商品必须先登陆！');
        if ($Data['grade'] > 5 || $Data['grade'] < 1) dies(-1, '评分异常！');
        $DB = SQL::DB();
        $Order = $DB->get('order', [
            '[>]goods' => 'gid',
        ], [
            'order.take',
            'order.uid',
            'order.id',
            'order.state',
            'goods.name',
            'order.gid',
            'order.order',
            'order.num',
            'goods.quantity'
        ], [
            'id' => (int)$Data['id'],
            'uid' => (int)$User['id']
        ]);

        if (!$Order) dies(-1, '订单不存在,或未绑定到您的登陆账户?');
        if ($Order['take'] == 1) dies(-1, '请先确认收货!');
        if ($Order['state'] == 7) dies(-1, '您已经评价过此订单了!');

        $Re = $DB->insert('mark', [
            'gid' => $Order['gid'],
            'order' => $Order['order'],
            'content' => $Data['content'],
            'image' => $Data['image'],
            'name' => $Order['name'] . ',购买数量:' . ($Order['num'] * $Order['quantity']),
            'uid' => $User['id'],
            'seller' => '-1',
            'score' => $Data['grade'],
            'state' => 2,
            'addtime' => $date,
        ]);

        if ($Re) {
            @$DB->update('order', [
                'state' => 7
            ], [
                'id' => $Data['id'],
                'uid' => $User['id']
            ]);
            userlog('评价商品', '用户成功评价商品[' . $Order['name'] . ']的订单,订单号为:' . $Order['id'], $Order['uid'], 0);

            Hook::execute('AppraiseNew', [
                'gid' => $Order['gid'],
                'order' => $Order['order'],
                'content' => $Data['content'],
                'image' => $Data['image'],
                'name' => $Order['name'] . ',购买数量:' . ($Order['num'] * $Order['quantity']),
                'uid' => $User['id'],
                'score' => $Data['grade'],
                'addtime' => $date,
            ]);

            dies(1, '评价成功,为防止政治敏感等不当言论，您的评价内容需要官方审核!');
        } else {
            dies(-1, '评价失败,无法保存评价内容!');
        }
    }

    /**
     * @param $order //订单号
     * @return bool
     * 创建游客订单查询缓存
     */
    public static function OrderCookie($order)
    {
        $UserData = login_data::user_data();
        if ($UserData !== false) {
            return false;
        }
        if (isset($_COOKIE['QUERYORDER'])) {
            $cookie = xiaochu_de($_COOKIE['QUERYORDER'], 'Query');
            $cookie = json_decode($cookie, TRUE);
            if (in_array($order, $cookie)) return true;
            $cookie = array_merge($cookie, [$order]);
            $cookie = xiaochu_en((json_encode($cookie)), 'Query');
        } else {
            $cookie = xiaochu_en(json_encode([$order]), 'Query');
        }
        setcookie("QUERYORDER", $cookie, time() + 60 * 60 * 24 * 30, '/');
        return true;
    }

    /**
     * @param $order //订单号
     * @return bool
     * 创建订单队列游客缓存列表
     */
    public static function QueueCookie($order)
    {
        $UserData = login_data::user_data();
        if ($UserData !== false) return false;
        if (isset($_COOKIE['QUEUEORDER'])) {
            $cookie = xiaochu_de($_COOKIE['QUEUEORDER'], 'Queue');
            $cookie = json_decode($cookie, TRUE);
            if (in_array($order, $cookie)) return true;
            $cookie = array_merge($cookie, [$order]);
            $cookie = xiaochu_en((json_encode($cookie)), 'Queue');
        } else {
            $cookie = xiaochu_en(json_encode([$order]), 'Queue');
        }
        setcookie("QUEUEORDER", $cookie, time() + 60 * 60 * 24 * 30, '/');
        return true;
    }

    /**
     * @param $uid
     * @return bool
     * 绑定游客订单
     */
    public static function OrderUser($uid)
    {
        $DB = SQL::DB();
        if (isset($_COOKIE['QUERYORDER'])) {
            $Array = self::VisitorsOrder();

            if (count($Array) >= 1) {
                @$DB->update('order', [
                    'uid' => $uid
                ], [
                    'trade_no' => $Array,
                    'uid' => -1
                ]);
            }

            $Array2 = self::VisitorsOrderQueue();
            if (count($Array2) >= 1) {
                @$DB->update('queue', [
                    'uid' => $uid
                ], [
                    'order' => $Array2,
                    'uid' => -1
                ]);
            }
            setcookie("QUERYORDER", null, time() - 60 * 60 * 24 * 30, '/');
            setcookie("QUEUEORDER", null, time() - 60 * 60 * 24 * 30, '/');
            return true;
        } else return false;
    }

    /**
     * @param $id
     * 订单队列补单
     */
    public static function OrderPay($id)
    {
        global $times, $date_30, $date, $conf;
        $DB = SQL::DB();
        $re = $DB->get('queue', '*', [
            'id' => (int)$id,
            'type' => 3,
            'addtime[>]' => $date_30
        ]);
        if (!$re) dies(-1, '订单不存在,或非待付款订单,或已超时,请直接删除此订单！');
        $UserData = login_data::user_data();
        if ($UserData && (int)$UserData['state'] !== 1) dies(-1, '您当前账号已被禁封，请联系管理员处理！');

        $Goods = $DB->get('goods', '*', [
            'gid' => (int)$re['gid'],
            'state[!]' => 2
        ]);
        if (!$Goods) dies(-1, '商品已下架,无法购买！');

        $Goods['count'] = $Goods['quota'];

        if ($Goods['count'] <= 0) {
            dies(-3, '商品:' . $Goods['name'] . '，商品库存已经没了！');
        } else {
            if (($Goods['count'] - $re['num']) < 0) {
                dies(-3, '当前商品库存，不足以购买' . $re['num'] . '份！,当前仅剩' . $Goods['count'] . '份商品库存！');
            }
        }

        if ($Goods['deliver'] == 3) {
            /**
             * 验证卡密库存
             */
            $CountKami = $DB->count('token', [
                "gid" => $Goods['gid'],
                'uid' => 1
            ]);

            if ($CountKami < $re['num']) {
                dies(-1, '商品:' . $Goods['name'] . '卡密库存不足,无法购买！');
            }
        }

        if ((float)$re['price'] === 0) {
            if (!$UserData) dies(-2, '领取免费商品必须先登陆！');
            $Res = $DB->update('queue', [
                'type' => 2,
                'remark' => '当前已经免费领取了商品,系统会自动提交订单,请耐心等待！',
                'trade_no' => '免费领取无订单'
            ], [
                'id' => $re['id'],
                'payment' => '免费领取'
            ]);

            if ($Res) {
                userlog('免费领取', '您于' . $date . '领取了' . $Goods['name'] . ',订单号为：', $re['order']);
                dies(1, '领取成功，点击刷新！');
            } else dies(-1, '领取失败,请联系管理员处理！');
        }

        $price = (float)$re['price'];

        switch ($re['payment']) {
            case '余额付款':
                if ($UserData == false) dies('-2', '请先登陆!');

                if ($UserData['money'] < $price) dies(-1, '当前余额不足' . $price . '元无法完成付款！');

                $Res = $DB->update('user', [
                    'money[-]' => $price,
                ], [
                    'id' => $re['uid']
                ]);

                if ($Res) {
                    userlog('余额购买', '您于' . $date . '购买了商品' . $Goods['name'] . ',付款金额为：' . $price . '元！', $UserData['id'], $price);
                    $Re = $DB->update('queue', [
                        'type' => 2,
                        'remark' => '当前已经使用余额付款,系统会自动提交订单,请耐心等待！',
                        'trade_no' => '余额付款无支付订单'
                    ], [
                        'id' => $re['id'],
                        'payment' => '余额付款'
                    ]);
                    if ($Re) {
                        Hook::execute('PayMoney', [
                            'cause' => '订单队列补单，购买了商品' . $Goods['name'] . ',付款金额为：' . $price . '元',
                            'money' => $price,
                            'uid' => $UserData['id'],
                        ]);
                        dies(1, '购买成功,点击刷新！');
                    } else dies(-1, '订单状态调整失败，若扣款请联系管理员处理！');
                } else dies(-1, '扣款失败，无法完成购买！');
                break;
            case '积分兑换':
                if (reward::user_landing() !== true) dies('-2', '请先登陆!');
                if ($UserData['currency'] < $price) dies(-1, '当前' . $conf['currency'] . '不足' . $price . '个无法兑换！');
                $Res = $DB->update('user', [
                    'currency[-]' => $price
                ], [
                    'id' => $UserData['id']
                ]);
                if ($Res) {
                    userlog('积分兑换', '您于' . $date . '兑换了商品' . $Goods['name'] . ',花费了' . $price . $conf['currency'], $UserData['id'], $price);
                    $Re = $DB->update('queue', [
                        'type' => 2,
                        'remark' => "当前已经使用" . $conf['currency'] . "兑换商品,系统会自动提交订单,请耐心等待！",
                        'trade_no' => '积分兑换无支付订单'
                    ], [
                        'id' => $re['id'],
                        'payment' => '积分兑换'
                    ]);
                    if ($Re) {
                        Hook::execute('PayPoints', [
                            'cause' => '订单队列补单，兑换了商品' . $Goods['name'] . ',花费了' . $price . $conf['currency'],
                            'currency' => $price,
                            'uid' => $UserData['id'],
                        ]);
                        dies(1, '兑换成功,点击刷新！');
                    } else dies(-1, '订单状态调整失败，若扣款请联系管理员处理！');
                } else dies(-1, '兑换失败，无法完成购买！');

                break;
            default:
                $Res = Pay::PrepaidPhoneOrders([
                    'type' => $re['payment'],
                    'uid' => ($UserData == false ? '-1' : $UserData['id']),
                    'gid' => -2,
                    'input' => [$re['order']],
                    'num' => 1
                ]);
                dier($Res);
                break;
        }
    }

    /**
     * @param null $msg 搜索内容
     * @param int $state 订单状态
     * @param int $Page 页码
     * @param bool $User 用户数据
     * 取出订单列表
     */
    public static function OrderAll($id = false, $msg = '', $state = 1, $Page = 1, $User = false, $limit = 6)
    {
        global $conf;
        $DB = SQL::DB();
        if (empty($limit)) {
            $limit = 6;
        }
        $LIMIT = $limit;
        $Page = ($Page - 1) * $LIMIT;

        switch ($state) {
            case 2: //未完成
                $SQL2 = [
                    'order.take' => 1,
                    'order.state' => [2, 3, 4, 6]
                ];
                break;
            case 3: //待收货
                $SQL2 = [
                    'order.take' => 1,
                    'order.state' => 1
                ];
                break;
            case 4: //已完成
                $SQL2 = [
                    'order.take' => 2,
                    'order.state' => [1, 7]
                ];
                break;
            case 5: //已取消
                $SQL2 = [
                    'order.state' => 5,
                ];
                break;
            default:
                $SQL2 = [];
                break;
        }

        if ($msg != '') {
            $SQL2 = array_merge($SQL2, [
                'order.input[~]' => $msg
            ]);
        }

        if (!$User) {
            $Array = self::VisitorsOrder();
            $SQL = [
                'order.trade_no' => $Array, 'LIMIT' => [$Page, $LIMIT],
                'ORDER' => [
                    'order.id' => 'DESC'
                ],
            ];
            $SQL = array_merge($SQL, $SQL2);
        } else {
            $SQL = [
                'order.uid' => $User['id'], 'LIMIT' => [$Page, $LIMIT],
                'ORDER' => [
                    'order.id' => 'DESC'
                ],
            ];
            $SQL = array_merge($SQL, $SQL2);
        }

        if ($id != false) {
            $SQL = array_merge($SQL, [
                'order.id' => $id
            ]);
        }

        $Res = $DB->select('order', [
            '[>]goods' => ['gid', 'gid'],
            '[>]mark' => ['order' => 'order'],
            '[>]tickets' => ['order' => 'order']
        ], [
            'order.id',
            'order.order',
            'order.ip',
            'order.input',
            'order.num',
            'order.gid',
            'order.payment',
            'order.price',
            'order.originalprice',
            'order.coupon',
            'order.take',
            'goods.quantity',
            'goods.profits',
            'goods.selling',
            'goods.specification',
            'goods.specification_spu',
            'goods.specification_sku',
            'order.addtitm(addtime)',
            'order.finishtime(endtime)',
            'order.state',
            'order.remark',
            'goods.name',
            'goods.image',
            'goods.units',
            'goods.input(value)',
            'order.logistics',
            'mark.content',
            'mark.addtime(markaddtime)',
        ], $SQL);

        unset($SQL['LIMIT']);
        $count = $DB->count('order', $SQL);

        $Data = [];
        foreach ($Res as $value) {
            $Input = [];

            foreach (explode('|', $value['value']) as $k => $v) {
                if (strstr($v, '{') && strstr($v, '}')) {
                    $Input[] = explode('{', $v)[0];
                } else if ($value['specification'] == 2) {
                    $Input[] = $v;
                } else {
                    $Input[] = (empty($v) ? '输入框' . ($k + 1) : $v);
                }
            }

            if ($value['specification'] == 2) {
                $SpRule = RlueAnalysis($value, 3);
                if ($SpRule != -1) {
                    if (empty($Input[0]) && empty($Input[1])) {
                        $Input = $SpRule['MasterRule'];
                    } else {
                        $Input = array_merge($SpRule['MasterRule'], $Input);
                    }
                }
            }

            $Token = $DB->select('token', ['token'], ['order' => $value['order']]);

            if (empty($value['remark'])) {
                $value['remark'] = -1;
            }
            if (empty($value['units'])) {
                $value['units'] = '个';
            }
            if (!empty($value['logistics'])) {
                $logistics = explode('|', $value['logistics']);
                $value['logistics'] = [
                    'name' => (empty($logistics[1]) ? '物流单号' : $logistics[1]),
                    'order' => $logistics[0],
                ];
            } else {
                $value['logistics'] = -1;
            }

            if (empty($value['quantity'])) {
                $value['quantity'] = 1;
            }

            $Image = json_decode($value['image'], true);
            if (!$Image) {
                $value['image'] = ImageUrl($value['image']);
            } else {
                $value['image'] = ImageUrl($Image[0]);
            }

            $value['take'] = (int)$value['take'];
            $value['Token'] = $Token;
            $value['state'] = (int)$value['state'];
            $value['price'] = round($value['price'], 8);
            $value['input'] = json_decode($value['input'], true);
            $value['value'] = $Input; //下单信息
            $value['currency'] = $conf['currency'];
            if ($value['originalprice'] != -1) {
                $value['originalprice'] = round($value['originalprice'], 2);
            }
            unset($value['specification'], $value['specification_sku'], $value['specification_spu']);
            $Data[] = $value;
        }

        dier([
            'code' => 1,
            'msg' => '订单列表获取成功',
            'data' => $Data,
            'Tracking' => $conf['Tracking'],
            'count' => $count
        ]);
    }
}

/**
 * Class price_monitoring
 * 商品价格监控
 * 已经监控过的商品写入缓存？
 */
class price_monitoring
{
    public static function pay_order()
    {
        global $conf;
        $DB = SQL::DB();

        $Res = $DB->select('pay', [
            '[>]goods' => ['gid' => 'gid']
        ], [
            'pay.order',
            'pay.type',
            'pay.uid',
            'pay.gid',
            'pay.name',
            'pay.num',
            'pay.money',
            'pay.input',
            'goods.cost',
        ]);

        $arr_wx = [];
        $arr_qq = [];
        $arr_zfb = [];
        foreach ($Res as $res) {
            if ($res['type'] === 'wxpay') {
                $arr_wx[] = [$res['order'], $res['type'], $res['uid'], $res['gid'], $res['name'], $res['num'], $res['money'], $res['input'], ($res['cost'] == null ? 0 : $res['cost'])];
            }

            if ($res['type'] === 'qqpay') {
                $arr_qq[] = [$res['order'], $res['type'], $res['uid'], $res['gid'], $res['name'], $res['num'], $res['money'], $res['input'], ($res['cost'] == null ? 0 : $res['cost'])];
            }

            if ($res['type'] === 'alipay') {
                $arr_zfb[] = [$res['order'], $res['type'], $res['uid'], $res['gid'], $res['name'], $res['num'], $res['money'], $res['input'], ($res['cost'] == null ? 0 : $res['cost'])];
            }
        }

        $arr_epay = [];
        $arr_cpay = [];
        $arr_apay = [];

        if ($conf['pay_qqapy'] == 1) {
            $arr_epay = array_merge($arr_epay, $arr_qq);
        }
        if ($conf['pay_wxpay'] == 1) {
            $arr_epay = array_merge($arr_epay, $arr_wx);
        }
        if ($conf['pay_alipay'] == 1) {
            $arr_epay = array_merge($arr_epay, $arr_zfb);
        }

        if ($conf['pay_qqapy'] == 2) {
            $arr_cpay = array_merge($arr_cpay, $arr_qq);
        }
        if ($conf['pay_wxpay'] == 2) {
            $arr_cpay = array_merge($arr_cpay, $arr_wx);
        }
        if ($conf['pay_alipay'] == 2) {
            $arr_cpay = array_merge($arr_cpay, $arr_zfb);
        }

        if ($conf['pay_qqapy'] == 3) {
            $arr_apay = array_merge($arr_apay, $arr_qq);
        }
        if ($conf['pay_wxpay'] == 3) {
            $arr_apay = array_merge($arr_apay, $arr_wx);
        }
        if ($conf['pay_alipay'] == 3) {
            $arr_apay = array_merge($arr_apay, $arr_zfb);
        }
        self::epay($arr_epay);
    }

    public static function epay($arr)
    {
        if ($arr == []) dies(-1, '无漏单');
        $data_arr = self::epay_list();
        if ($data_arr['code'] <> 1) dier($data_arr);

        $arrt = [];
        foreach ($arr as $vs) {
            $arrt[] = $vs[0];
        }

        $array_sucees = [];
        foreach ($data_arr['data'] as $v) {
            if (in_array($v['order'], $arrt)) {
                $array_sucees[] = ['succ' => $v, 'ors' => self::order_msg($arr, $v['order'])];
            } else continue;
        }

        self::order_stater($arrt);
        if (count($array_sucees) == 0) dies(1, '无需监控订单！');
        $i = 0;
        foreach ($array_sucees as $vt) {
            if ($vt['ors'][3] == '-1') {
                $a = self::userpay($vt['ors'][2], $vt['succ']['money'], $vt['succ']);
            } else {
                $a = self::order_buy($vt['ors'], $vt['succ']);
            }
            if ($a == true) {
                $i++;
            }
        }
        dies(1, '共有' . count($array_sucees) . '个支付订单需要补单,补单成功：' . $i . '个！');
    }

    /**
     * 查询易支付订单列表，50条
     */
    public static function epay_list()
    {
        global $conf;
        $curl = $conf['pay_url'] . '/api.php?act=orders&pid=' . $conf['pay_partner'] . '&key=' . $conf['pay_key'] . '&limit=50';
        $data = json_decode(get_curl($curl), TRUE);

        if ($data['code'] == 1) {
            $orders = [];
            foreach ($data['data'] as $v) {
                if ($v['status'] == 1) {
                    $orders[] = ['name' => $v['name'], 'money' => $v['money'], 'ip' => $v['ip'], 'order' => $v['out_trade_no'], 'trade_no' => $v['trade_no'], 'addtime' => $v['addtime']];
                }
            }
            return ['code' => 1, 'msg' => '易支付订单查询成功,前50条成功的订单', 'data' => $orders];
        } else {
            return ['code' => -1, 'msg' => '无法访问易支付查单链接：' . $curl];
        }
    }

    /**
     * @param $arr 需取出数组
     * @param $order 订单号
     * 根据订单去除详细日志数据！
     */
    public static function order_msg($arr, $order)
    {
        foreach ($arr as $v) {
            if ($v[0] == $order) {
                return $v;
            }
        }
    }

    /**
     * @param $arr 订单状态
     * 批量修改订单状态！
     */
    public static function order_stater($arr)
    {
        $DB = SQL::DB();
        $Res = $DB->update('pay', [
            'verify' => 1
        ], [
            'order' => $arr
        ]);
        if ($Res) {
            return true;
        } else return false;
    }

    /**
     * 用户充值补单
     */
    public static function userpay($uid, $price, $succ)
    {
        global $dbconfig;
        $DB = SQL::DB();
        $price = (float)$price;
        $Res = $DB->update('user', [
            'money[+]' => $price,
        ], [
            'id' => $uid,
        ]);
        if ($Res) {
            userlog('在线充值', '用户' . $uid . '于' . $succ['addtime'] . '成功充值' . $price . '元！', $uid, $price);
            $DB->update('pay', [
                'trade_no' => $succ['trade_no'],
                'state' => 1,
                'endtime' => $succ['addtime'],
                'verify' => 1,
            ], [
                'order' => $succ['order']
            ]);
            return true;
        }

        return false;
    }

    /**
     * @param $ors  本地订单
     * @param $succ 易支付订单
     */
    public static function order_buy($ors, $succ)
    {
        $DB = SQL::DB();
        $Res = $DB->insert('order', [
            'order' => $succ['order'],
            'trade_no' => $succ['trade_no'],
            'uid' => $ors[2],
            'ip' => $succ['ip'],
            'input' => $ors[8],
            'state' => 2,
            'num' => $ors[5],
            'return' => '监控补单,请手动提交!',
            'gid' => $ors[3],
            'order_id' => $ors[1],
            'money' => $succ['money'],
            'addtitm' => $succ['addtime'],
        ]);
        if ($Res) {
            $DB->update('pay', [
                'trade_no' => $succ['trade_no'],
                'state' => 1,
                'endtime' => $succ['addtime'],
                'verify' => 1,
            ], [
                'order' => $succ['order']
            ]);
            return true;
        }

        return false;
    }

    /**
     * @param $arr  数组
     * @param int $sum 每组数量
     * @return array
     * 数据分组
     */
    public static function grouping($arr, $sum = 50)
    {
        if (count($arr) <= $sum) return $arr;
        $arr_gr = [];
        $count = ceil(count($arr) / $sum);
        for ($a = 0; $a < $count; $a++) {
            $a_c = $a * $sum;
            $ccos = [];
            for ($b = $a; $b < $sum; $b++) {
                if (!$arr[($b + $a_c)]) continue;
                $ccos[] = $arr[($b + $a_c)];
            }
            $arr_gr['count_' . $a] = $ccos;
        }
        return $arr_gr;
    }

    /**
     * @param $arr
     * @return bool
     * 码支付补单监控
     */
    public static function cpay($arr)
    {
        if ($arr == []) return true;
        return true;
    }

    /**
     * @param $arr
     * 当面付补单查询
     * https://openapi.alipay.com/gateway.do
     */
    public static function apay($arr)
    {

    }
}

/**
 * @param $Pid //订单ID
 * @param $type =1根据ID查询，=2根据支付订单查询
 * 返回订单状态，如发卡普通提示等！
 * 此处只验证普通订单，不验证队列订单！
 */
function OrderStatus($Pid, $type = 1)
{
    global $conf;
    $DB = SQL::DB();

    if ($type === 1) {
        $SQL = ['id' => (int)$Pid];
    } else {
        $SQL = ['trade_no' => (string)$Pid];
    }

    $Order = $DB->get('order', [
        '[>]goods' => ['gid' => 'gid']
    ], [
        'order.id',
        'goods.gid',
        'goods.name',
        'goods.deliver',
        'order.state',
        'order.return'
    ], $SQL);

    if (!$Order) dier([
        'code' => -3,
        'msg' => '订单不存在，或还在队列订单内，未提交服务器！',
        'pid' => $Order['id']
    ]);

    switch ((int)$Order['deliver']) {
        case 1: //自营
            dier([
                'code' => 1,
                'msg' => '商品订单创建成功,请耐心等待发货！',
                'pid' => $Order['id']
            ]);
            break;
        case 2: //api
            if ($Order['state'] == $conf['SubmitStateSuccess']) {
                $msg = '订单已经提交至服务器,快去订单列表看看吧！';
            } else {
                $msg = '商品订单创建成功,请耐心等待发货！';
            }
            dier([
                'code' => 1,
                'msg' => $msg,
                'pid' => $Order['id']
            ]);
            break;
        case 3: //发卡
        case 4: //购买后显示隐藏内容
            dier([
                'code' => 1,
                'msg' => $Order['return'],
                'pid' => $Order['id']
            ]);
            break;
        default: //对接
            if ($Order['state'] == $conf['SubmitStateSuccess']) {
                $msg = '订单已经开始处理,请耐心等待到账！';
            } else {
                $msg = '商品订单创建成功,请耐心等待发货！';
            }
            dier([
                'code' => 1,
                'msg' => $msg,
                'pid' => $Order['id']
            ]);
            break;
    }
}

/**
 * 目录保护
 */
function DirectoryProtection()
{
    $antiXss = new AntiXSS();
    global $conf;
    if ($conf['Protect'] != '' && isset($conf['Protect']) && empty($_SESSION['ADMIN_TOKEN']) && empty($_COOKIE[$conf['Protect']])) {
        $pref = explode('/', $_SERVER['REQUEST_URI'])[count(explode('/', $_SERVER['REQUEST_URI'])) - 1];
        $pref = $antiXss->xss_clean($pref);
        if ($pref == 'login.php?' . $conf['Protect'] || $pref == 'login.php?Protect=' . $conf['Protect']) {
            setcookie($conf['Protect'], 1);
        } else if ($pref != '?' . $conf['Protect'] && $pref != '?Protect=' . $conf['Protect'] && !empty($pref) && $pref !== 'login.php') {
            header('HTTP/1.1 403 No access');
            show_msg('抱歉', '目录保护密码验证失败，密码不正确，请重新输入！', 4, './');
        } else {
            header('HTTP/1.1 403 No access');
            $pref = explode('/', $_SERVER['REQUEST_URI'])[1];
            $pref = $antiXss->xss_clean($pref);
            show_msg('目录保护密码验证', '无访问权限！，<br>若您是站长却忘记了目录保护密码,可打开数据库：sky_config，内的Protect参数查看<br>或清空后再删除sky_cache表的全部内容，即可直接进入，不验证目录保护密码！<hr>若您记得目录保护密码，也可访问：' . href(2) . ROOT_DIR_S . '/' . $pref . '/login.php?目录保护密码，进入登录哦<hr>
        <form class="layui-form" action="./login.php?">
            <div class="layui-form-item layui-form-pane">
                <label class="layui-form-label">保护密码</label>
                <div class="layui-input-block">
                    <input type="text" name="Protect" required  lay-verify="required" placeholder="请输入目录保护密码" autocomplete="off" class="layui-input">
                </div>
            </div>
            <button class="layui-btn layui-btn-normal layui-btn-fluid bg-danger text-white" lay-submit>验证目录保护密码</button>
        </form>
        ', 4, 0, false);
        }
    }
}
