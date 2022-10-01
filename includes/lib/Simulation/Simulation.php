<?php
// +----------------------------------------------------------------------
// | Project: shop
// +----------------------------------------------------------------------
// | Creation: 2022/2/9
// +----------------------------------------------------------------------
// | Filename: Simulation.php
// +----------------------------------------------------------------------
// | Explain: 对接货源请求模拟,方便其他系统通过热门货源对接本系统!
// +----------------------------------------------------------------------

namespace lib\Simulation;

use extend\UserConf;
use lib\supply\Order;
use lib\supply\Price;
use Medoo\DB\SQL;
use voku\helper\AntiXSS;

class Simulation
{
    /**
     * @param $Data
     * 玖伍模拟
     */
    public static function jiuwu($Data)
    {
        if (isset($Data['c'])) {
            $Data['c'] = strtolower($Data['c']);
        }

        if (isset($Data['a'])) {
            $Data['a'] = strtolower($Data['a']);
        }

        switch ($Data['c']) {
            case 'api':
                $user = self::jiuwu_user($Data);
                if ($Data['a'] === 'user_get_goods_lists_details') {
                    self::jiuwu_GoodsList($user);
                } else if ($Data['a'] === 'get_goods_lists') {
                    self::jiuwu_GoodsListSimplify();
                }
                break;
            case 'goods':
                if ($Data['a'] === 'detail') {
                    $user = self::jiuwu_user_cookies();
                    self::jiuwu_GoodsDetail($user, $Data['id']);
                }
                break;
            case 'user':
                if ($Data['a'] === 'login') {
                    self::jiuwu_UserLogin($Data);
                }
                break;
            case 'order':
                $user = self::jiuwu_user($Data);
                if ($Data['a'] === 'add') {
                    self::jiuwu_buy($user, $Data);
                } else if ($Data['a'] === 'query_orders_detail') {
                    self::jiuwu_Order($user, $Data);
                }
                break;
        }
        self::jiuwu_error('此接口尚未模拟，无法获取对应数据[bbs.79tian.com]！');
    }

    /**
     * 通过cookie获取用户信息
     */
    public static function jiuwu_user_cookies()
    {
        $antiXss = new AntiXSS();
        $auto_uid = $antiXss->xss_clean(trim($_COOKIE['auto_uid']));
        $auto_token = $antiXss->xss_clean(trim($_COOKIE['auto_token']));

        $DB = SQL::DB();
        $User = $DB->get('user', '*', [
            'id' => $auto_uid,
            'state' => 1
        ]);
        if (!$User) {
            self::jiuwu_error('用户不存在，或已被禁止登录!');
        }

        if (md5($User['user_idu']) !== $auto_token) {
            self::jiuwu_error('用户token验证失败,请重新获取Cookie数据！');
        }

        return $User;
    }

    /**
     * @param $Data
     * 获取用户数据,通过，Api_UserName和Api_UserMd5Pass参数获取
     */
    public static function jiuwu_user($Data)
    {
        $DB = SQL::DB();
        $User = $DB->get('user', '*', [
            'id' => (int)$Data['Api_UserName'],
            'state' => 1
        ]);

        if (!$User) {
            self::jiuwu_error('用户不存在，或已被禁止登录，请使用本站用户后台的对接ID和对接密钥来进行对接!');
        }

        if (empty($User['token']) || md5($User['token']) !== $Data['Api_UserMd5Pass']) {
            self::jiuwu_error('对接密钥不正确，无法获取数据！');
        }

        if ($User['ip_white_list'] == '') {
            self::jiuwu_error('IP：' . userip() . ' 未设置对接白名单！');
        }

        $ArrayIp = explode('|', $User['ip_white_list']);
        if (!in_array(userip(), $ArrayIp)) {
            self::jiuwu_error('IP：' . userip() . ' 未设置对接白名单！');
        }

        return $User;
    }

    /**
     * @param $User
     * @param $Data
     * 查询商品订单
     */
    public static function jiuwu_Order($User, $Data)
    {
        $Res = Order::Query($Data['orders_id'], $User['id'], 3);
        $Res = $Res['data'];
        $input = [
            'aa' => "",
            'bb' => "",
            'cc' => "",
            'dd' => "",
            'ee' => "",
        ];

        $input_arr = [];
        foreach ($Res['input'] as $value) {
            $Ex = explode('：', $value);
            $input_arr[] = $Ex[1];
        }
        $i = 0;
        foreach ($input as $key => $val) {
            $input[$key] = ($input_arr[$i] ?? '');
            ++$i;
        }

        $array = [
            'id' => $Res['order'],
            'user_note' => '',
            'need_num_0' => ((int)$Res['ApiType'] == 1 ? (int)$Res['ApiNum'] : (int)$Res['num']),
            'need_num_1' => 0,
            'need_num_2' => 0,
            'need_num_3' => 0,
            'start_num' => ((int)$Res['ApiType'] == 1 ? $Res['ApiInitial'] : 0),
            'end_num' => 0,
            'now_num' => ((int)$Res['ApiType'] == 1 ? $Res['ApiPresent'] : 0),
            'login_state' => 0,
            'start_time' => $Res['addtime'],
            'end_time' => $Res['endtime'],
            'add_time' => $Res['addtime'],
            'order_amount' => (float)$Res['price'],
            'order_cardnum' => 0,
            'tui_amount' => 0,
            'tui_cardnum' => 0,
            'user_id' => $User['id'],
            'card_id' => '',
            'order_pay_type' => ($Res['type'] == '余额付款' ? 1 : 0),
            'goods_id' => $Res['gid'],
            'goods_type' => $Res['gid'],
            'goods_type_title' => $Res['gid'],
        ];

        switch ($Res['stateid']) {
            case 1: //已完成
                $array['order_state'] = 3;
                break;
            case 2: //未开始
                $array['order_state'] = 1;
                break;
            case 3: //退单中
                $array['order_state'] = 5;
                break;
            case 4: //进行中
                $array['order_state'] = 2;
                break;
            case 5: //已退单
                $array['order_state'] = 4;
                break;
            default:
                $array['order_state'] = 6;
                break;
        }
        dier([
            'status' => true,
            'rows' => [array_merge($array, $input)],
            'total' => 1
        ]);
    }

    /**
     * @param $User
     * @param $Data
     * 商品下单
     */
    public static function jiuwu_buy($User, $Data)
    {
        $DB = SQL::DB();
        $GoodsHide = UserConf::GoodsHide();

        if (count($GoodsHide) >= 1) {
            if (in_array($Data['goods_id'], $GoodsHide)) {
                self::jiuwu_error('商品已下架！');
            }
        }

        $Goods = $DB->get('goods', '*',
            ['gid' => (int)$Data['goods_id'], 'state' => 1, 'method[~]' => '4']);
        if (!$Goods) {
            self::jiuwu_error('抱歉,此商品已下架或不存在,无法被对接！');
        }

        $DataInput = explode('|', $Goods['input']);
        $InputArr = [];
        $i = 1;
        foreach ($DataInput as $value) {
            if (strstr($value, '{') && strstr($value, '}')) {
                $value = explode('{', $value)[0];
            }
            if (empty($Data['value' . $i])) {
                self::jiuwu_error('下单信息缺失,请将[' . $value . ']填写完整');
            }
            $InputArr[] = $Data['value' . $i];
            $i++;
        }

        $DataBuy = [
            'gid' => $Data['goods_id'],
            'type' => ($Data['pay_type'] == 1 ? 2 : 3),
            'num' => ($Data['need_num_0'] / $Goods['quantity']),
            'data' => $InputArr,
            'mode' => 'alipay',
            'Api' => 1,
            'CouponId' => -1
        ];

        $order = Order::Creation($DataBuy, $User);
        if ($order) {
            $OrderData = $DB->get('order', ['order'], [
                'id' => (int)$order,
            ]);

            $User = $DB->get('user', [
                'money', 'currency'
            ], ['id' => (int)$User['id']]);

            $Return = [
                'status' => 1,
                'order_id' => $OrderData['order'],
                'after_use_rmb' => ($Data['pay_type'] == 1 ? $User['money'] : $User['currency']),
                'after_use_cardnum' => ($Data['pay_type'] == 1 ? $User['money'] : $User['currency']),
                'msg' => '订单创建成功,购买后剩余' . ($Data['pay_type'] == 1 ? $User['money'] . '余额!' : $User['currency'] . '积分!'),
            ];

            $OrderToken = $DB->select('token', ['token'], ['order' => $OrderData['order']]);
            if ($OrderToken && count($OrderToken) >= 1) {
                $ArrayToken = [];
                foreach ($OrderToken as $value) {
                    $ArrayToken[] = $value['token'];
                }
                $Return['token'] = json_encode($ArrayToken);
                $Return['msg'] .= '，另,本次共发卡' . count($ArrayToken) . '张！';
            }
            dier($Return);
        } else {
            self::jiuwu_error('订单创建失败！');
        }
    }

    /**
     * @param $Data
     * 通对接数据获取登录token【UserToken】
     * username
     * username_password
     */
    public static function jiuwu_UserLogin($Data)
    {
        $DB = SQL::DB();
        $User = $DB->get('user', ['id', 'user_idu', 'ip_white_list', 'state'], [
            'id' => (int)$Data['username'],
            'token' => (string)$Data['username_password']
        ]);
        if (!$User) {
            self::jiuwu_error('用户名或密码不正确！');
        }

        if ($User['state'] != 1) {
            self::jiuwu_error('当前账户已被禁止登录！');
        }

        if ($User['ip_white_list'] == '') {
            self::jiuwu_error('IP：' . userip() . ' 未设置对接白名单！');
        }

        $ArrayIp = explode('|', $User['ip_white_list']);
        if (!in_array(userip(), $ArrayIp)) {
            self::jiuwu_error('IP：' . userip() . ' 未设置对接白名单！');
        }

        $time = (time() + 86400) * 7;
        setcookie("auto_uid", $User['id'], $time, "/");
        setcookie("auto_token", md5($User['user_idu']), $time, "/");
        setcookie("auto_time", $time, $time, "/");

        die('<div class="system-message">
		<!-- <h1>:)</h1> -->
	<p class="success">登录成功！</p>
		<p class="detail"></p>
	<p class="jump"><b id="wait">1</b> 秒后页面将自动跳转</p>
	<div>
		<a id="href" class="href" href="/index.php">立即跳转</a> 
		<button id="btn-stop" type="button" onclick="stop()">停止跳转</button> 
		<a class="href" href="/">到网站首页</a>
	</div>
</div>');
    }


    /**
     * @param $User
     * @param $Gid
     * 获取商品详情
     */
    public static function jiuwu_GoodsDetail($User, $Gid)
    {
        global $conf;
        $DB = SQL::DB();
        $GoodsHide = UserConf::GoodsHide();

        if (count($GoodsHide) >= 1 && in_array($Gid, $GoodsHide)) {
            self::jiuwu_error('此商品已下架！');
        }

        $Goods = $DB->get('goods', '*', [
            'gid' => (int)$Gid,
            'state' => 1,
            'method[~]' => '4',
            'specification[!]' => 2
        ]);

        if (!$Goods) {
            self::jiuwu_error('商品不存在或已下架!');
        }

        $input = '';
        $i = 1;
        if (!empty($Goods['input'])) {
            $Ex = explode('|', $Goods['input']);
            foreach ($Ex as $value) {
                if (strstr($value, '{') && strstr($value, '}')) {
                    $Name = explode('{', $value);
                    $input .= '<li><span class="fixed-width-right-80">' . $Name[0] . '：</span><input name="value' . $i . '" type="text" placeholder="请输入' . $Name[0] . '"/></li>' . "\n";
                } else {
                    $input .= '<li><span class="fixed-width-right-80">' . $value . '：</span><input name="value' . $i . '" type="text" placeholder="请输入' . $value . '"/></li>' . "\n";
                }
                ++$i;
            }
        } else {
            $input .= '<li><span class="fixed-width-right-80">下单账号：</span><input name="value' . $i . '" type="text" placeholder="请输入下单账号"/></li>' . "\n";
        }

        $Pricer = Price::Get($Goods['money'], $Goods['profits'], $User['grade'], $Goods['gid'], $Goods['selling']);
        $Goods['price'] = $Pricer['price'];
        $Goods['image'] = json_decode($Goods['image'], true)[0];
        $HtmlTP = file_get_contents(SYSTEM_ROOT . 'lib/Simulation/jiuwu.TP');

        $HtmlTP = str_replace("[content]", $Goods['docs'], $HtmlTP);
        $HtmlTP = str_replace("[name]", $Goods['name'], $HtmlTP);
        $HtmlTP = str_replace("[title]", $conf['sitename'], $HtmlTP);
        $HtmlTP = str_replace("[username]", $User['id'], $HtmlTP);
        $HtmlTP = str_replace("[money]", round($User['money']), $HtmlTP);
        $HtmlTP = str_replace("[integration]", $User['currency'], $HtmlTP);
        $HtmlTP = str_replace("[UnitPrice]", ($Goods['price'] / $Goods['quantity']), $HtmlTP);
        $HtmlTP = str_replace("[gid]", $Goods['gid'], $HtmlTP);
        $HtmlTP = str_replace("[cid]", $Goods['cid'], $HtmlTP);
        $HtmlTP = str_replace("[min]", ($Goods['min'] * $Goods['quantity']), $HtmlTP);
        $HtmlTP = str_replace("[max]", ($Goods['max'] * $Goods['quantity']), $HtmlTP);
        $HtmlTP = str_replace("[MinPrice]", ($Goods['min'] * ($Goods['price'] / $Goods['quantity'])), $HtmlTP);
        $HtmlTP = str_replace("[input]", $input, $HtmlTP);
        $HtmlTP = str_replace("[units]", $Goods['units'], $HtmlTP);

        die($HtmlTP);
    }

    /**
     * 获取商品列表[精简]
     */
    public static function jiuwu_GoodsListSimplify()
    {
        $List = [];
        $DB = SQL::DB();
        $GoodsHide = UserConf::GoodsHide();
        $SQL = [
            'ORDER' => ['sort' => 'DESC'],
            'method[~]' => '4',
            'specification[!]' => 2 //不取出规格商品
        ];
        if (count($GoodsHide) >= 1) {
            $SQL = array_merge($SQL, [
                'gid[!]' => $GoodsHide
            ]);
        }

        $Count = $DB->count('goods', $SQL);
        if ($Count === 0) {
            dies(-1, '一个可对接商品都没有！');
        }

        $GoodsList = $DB->select('goods', [
            'gid', 'cid', 'name', 'image', 'units', 'quantity', 'min', 'max'
        ], $SQL);

        foreach ($GoodsList as $Goods) {
            if (!empty($Goods['image'])) {
                $Goods['image'] = json_decode($Goods['image'], TRUE)[0];
            }
            $List[] = [
                'id' => (int)$Goods['gid'],
                'title' => $Goods['name'] ?? '',
                'streamline_title' => $Goods['name'],
                'thumb' => $Goods['image'] ?? '',
                'unit' => $Goods['units'] ?? '个',
                'minbuynum_0' => ($Goods['min'] * $Goods['quantity']),
                'maxbuynum_0' => ($Goods['max'] * $Goods['quantity']),
                'goods_type' => (int)$Goods['cid'],
                'goods_type_title' => '分类：' . $Goods['cid'],
            ];
            unset($Goods);
        }

        dier([
            'status' => 1,
            'msg' => '获取成功',
            'goods_rows' => $List,
        ]);
    }

    /**
     * @param $User
     * 获取商品列表
     */
    public static function jiuwu_GoodsList($User)
    {
        $List = [];
        $DB = SQL::DB();
        $GoodsHide = UserConf::GoodsHide();
        $SQL = [
            'ORDER' => ['sort' => 'DESC'],
            'method[~]' => '4',
            'specification[!]' => 2 //不取出规格商品
        ];
        if (count($GoodsHide) >= 1) {
            $SQL = array_merge($SQL, [
                'gid[!]' => $GoodsHide
            ]);
        }

        $Count = $DB->count('goods', $SQL);
        if ($Count === 0) {
            dies(-1, '一个可对接商品都没有！');
        }

        $GoodsList = $DB->select('goods', [
            'gid', 'cid', 'name', 'state', 'image',
            'units', 'selling', 'profits', 'money',
            'quantity', 'min', 'max', 'alert'
        ], $SQL);

        foreach ($GoodsList as $Goods) {

            if (!empty($Goods['image'])) {
                $Goods['image'] = json_decode($Goods['image'], TRUE)[0];
            }

            $Pricer = Price::Get($Goods['money'], $Goods['profits'], $User['grade'], $Goods['gid'], $Goods['selling']);
            $Goods['price'] = $Pricer['price'];

            $List[] = [
                'id' => (int)$Goods['gid'],
                'title' => $Goods['name'],
                'thumb' => $Goods['image'],
                'unit' => $Goods['units'],
                'goods_unitprice' => ($Goods['price'] / $Goods['quantity']),
                'user_unitprice' => ($Goods['price'] / $Goods['quantity']),
                'no_display' => 0,
                'goods_status' => ($Goods['state'] == 1 ? 0 : 1),
                'goods_close_msg' => $Goods['alert'],
                'minbuynum_0' => ($Goods['min'] * $Goods['quantity']),
                'maxbuynum_0' => ($Goods['max'] * $Goods['quantity']),
                'goods_type' => (int)$Goods['cid'],
                'goods_type_title' => '分类：' . $Goods['cid'],
            ];
        }

        dier([
            'status' => true,
            'msg' => '获取成功',
            'user_goods_lists_details' => $List,
        ]);
    }


    /**
     * @param $msg
     * @return never
     * 玖伍错误提示信息
     */
    public static function jiuwu_error($msg = '错误提示!')
    {
        dier([
            'status' => false,
            'msg' => '<p class="error">' . $msg . '</p>',
        ]);
//        die('<div class="system-message">
//		<!-- <h1>:(</h1> -->
//	<p class="error">' . $msg . '</p>
//		<p class="detail"></p>
//	<p class="jump"><b id="wait">3</b> 秒后页面将自动跳转</p>
//	<div>
//		<a id="href" class="href" href="javascript:history.back(-1);">立即跳转</a>
//		<button id="btn-stop" type="button" onclick="stop()">停止跳转</button>
//		<a class="href" href="/">到网站首页</a>
//	</div>
//</div>');
    }
}