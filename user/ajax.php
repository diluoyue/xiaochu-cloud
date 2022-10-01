<?php
/**
 * 用户后台操作处理
 */
include '../includes/fun.global.php';

use extend\GoodsCart;
use extend\ImgThumbnail;
use extend\QuickLogin;
use extend\SMS;
use extend\UserConf;
use extend\VerificationCode;
use lib\App\App;
use lib\Hook\Hook;
use lib\supply\Price;
use lib\supply\StringCargo;
use Medoo\DB\SQL;
use Server\Server;

header('Content-Type: application/json; charset=UTF-8');
$UserData = login_data::user_data();
global $conf, $_QET, $date, $accredit, $times;
if ($conf['ShutDownUserSystem'] == -1) {
    dies(-1, $conf['ShutDownUserSystemCause']);
}
switch ($_QET['act']) {
    case 'user_set': //修改用户信息
        if (!$UserData) {
            dies(-1, '请先登陆！');
        }
        if (empty($_QET['mobile']) || empty($_QET['name']) || empty($_QET['qq']) || empty($_QET['username'])) {
            dies(-1, '请填写完整！');
        }
        if (strlen((int)$_QET['mobile']) <> 11) {
            dies(-1, '手机号输入有误！');
        }

        preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $_QET['username'], $arr_pr);
        if (empty($arr_pr[0])) {
            dies(-1, '登陆账号只可为英文或数字,并且长度要大于6小于18位！');
        }

        preg_match('/^[0-9]{6,12}+$/u', $_QET['qq'], $arr_pr);
        if (empty($arr_pr[0])) {
            dies(-1, 'QQ号码格式不正确！');
        }

        if ($_QET['username'] == $_QET['password'] && !empty($_QET['password'])) dies(-1, '账号和密码不能一样！');

        preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $_QET['password'], $arr_pr);
        if (empty($arr_pr[0]) && !empty($_QET['password'])) {
            dies(-1, '登陆密码只可为英文或数字,并且长度要大于6小于18位！');
        }

        $DB = SQL::DB();

        $Res = $DB->select('user', '*', [
            'OR' => [
                'username' => $_QET['username'],
                'qq' => $_QET['qq'],
                'mobile' => $_QET['mobile'],
            ],
            'id[!]' => $UserData['id'],
        ]);


        foreach ($Res as $value) {
            if ($value['username'] == $_QET['username']) dies(-1, '账号[' . $_QET['username'] . ']已经被Ta人绑定过了,换个账号吧~');
            if ($value['qq'] == $_QET['qq']) dies(-1, 'QQ[' . $_QET['qq'] . ']已经被Ta人绑定过了,换个QQ吧~');
            if ($value['mobile'] == $_QET['mobile']) dies(-1, '手机号[' . $_QET['mobile'] . ']已经被Ta人绑定过了,换个手机号吧~');
        }

        $Res = $DB->update('user', [
            'mobile' => $_QET['mobile'],
            'qq' => $_QET['qq'],
            'image' => 'https://q4.qlogo.cn/headimg_dl?dst_uin=' . $_QET['qq'] . '&spec=100',
            'username' => $_QET['username'],
            'password' => (!empty($_QET['password']) ? md5($_QET['password']) : $UserData['password']),
            'name' => $_QET['name'],
        ], ['id' => $UserData['id']]);

        if ($Res) {
            userlog('修改信息', '用户[' . $UserData['id'] . ']于' . $date . '修改了个人信息！', $UserData['id'], '0');
            if ($_QET['username'] <> $UserData['username']) {
                setcookie('THEKEY', null, time() - 1, '/');
                dies(2, '修改成功，由于修改了登录账号，您需要重新登陆！');
            } else {
                dies(1, '修改成功！');
            }
        } else {
            dies(-1, '修改失败!');
        }
        break;
    case 'Send_verification_login': //手机号登陆
        SMS::UserLoginVerify($_QET['code']);
        break;
    case 'Send_verification_code_login': //发送短信
        if (empty($_QET['code'])) {
            dies(-2, '请将验证码填写完整！');
        }
        if (empty($_SESSION['Login_sms_vis']) || $_SESSION['Login_sms_vis'] !== md5($_QET['code'] . href())) {
            $_SESSION['Login_sms_vis'] = null;
            dies(-2, '验证码有误！');
        } else {
            $_SESSION['Login_sms_vis'] = null;
            SMS::UserLogin($_QET['mobile'], $conf['usersmslogin']);
        }
        break;
    case 'LogOut': //退出登录
        if (!$UserData) {
            dies(-1, '您当前还未登录！');
        }
        if (setcookie('THEKEY', null, time() - 66, '/')) {
            Hook::execute('UserLogout', [
                'name' => $UserData['name'],
                'id' => $UserData['id']
            ]);
            dies(1, '退出成功');
        }
        dies(-1, '退出失败，请重新尝试，或手动清除浏览器Cookie！');
        break;
    case 'login_account': //账号密码登陆
        if ($UserData) dies(1, '您已登陆！');

        if (empty($_SESSION['Login_vc']) || $_SESSION['Login_vc'] <> md5($_QET['vercode'] . href())) {

            $_SESSION['Login_vc'] = null;
            dies(-2, '验证码有误！');
        } else {

            $_SESSION['Login_vc'] = null;
        }

        if ((int)$conf['AccountPasswordLogin'] !== 1) {
            dies(-1, '当前站点未开启账号登陆,请使用其他登陆方式登陆！');
        }
        if (empty($_QET['user']) || empty($_QET['pass']) || empty($_QET['vercode'])) {
            dies(-1, '请填写完整！');
        }
        preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $_QET['user'], $arr_pr);
        if (empty($arr_pr[0])) dies(-1, '账号输入有误！');
        $DB = SQL::DB();

        $Res = $DB->get('user', '*', [
            'username' => (string)$_QET['user'],
            'password' => md5($_QET['pass']),
            'LIMIT' => 1,
        ]);

        if (!$Res) dies(-1, '用户不存在或账号密码错误！');

        if ($Res['state'] != 1) dies(-1, '您的账号已被禁封，无法登陆');

        userlog('后台登陆', '用户于' . $date . '通过手机号成功登陆后台！', $Res['id'], '0');

        setcookie('THEKEY', $Res['user_idu'], time() + 3600 * 12 * 15, '/');
        query::OrderUser($Res['id']);

        Hook::execute('UserLogin', [
            'name' => $Res['name'],
            'id' => $Res['id']
        ]);
        GoodsCart::UserCookieDer($Res['id']);
        dies(1, '登陆成功，[' . $Res['name'] . ']欢迎回来！');
        break;
    case 'login_register': //用户注册！

        if ($UserData) dies(1, '您已登陆！');

        if ($conf['inItRegister'] == 1 && empty($_COOKIE['INVITED_STATUS'])) {
            dies(-1, '当前站点开启了邀请注册功能，您只能够通过已注册用户的邀请链接才可以注册为平台用户！');
        }

        if ((int)$conf['userregister'] !== 1) dies(-1, '当前站点未开启用户注册！');

        if ((int)$conf['AccountPasswordLogin'] !== 1) dies(-1, '当前站点未开启账号登陆,请换一种登陆方式！');

        test(['username|e', 'password|e', 'vercode|e', 'qq|e'], '请将内容填写完整!');

        if (empty($_SESSION['Login_res']) || $_SESSION['Login_res'] <> md5($_QET['vercode'] . href())) {
            $_SESSION['Login_res'] = null;
            dies(-2, '验证码有误！');
        } else {
            $_SESSION['Login_res'] = null;
        }

        if ($_QET['username'] == $_QET['password']) dies(-1, '账号和密码不能一样！');

        preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $_QET['username'], $arr_pr);
        if (empty($arr_pr[0])) dies(-1, '登陆账号只可为英文或数字,并且长度要大于6小于18位！');

        preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $_QET['password'], $arr_pr);
        if (empty($arr_pr[0])) dies(-1, '登陆密码只可为英文或数字,并且长度要大于6小于18位！');

        preg_match('/^[0-9]{5,12}+$/u', $_QET['qq'], $arr_pr);
        if (empty($arr_pr[0])) dies(-1, 'QQ号码格式不正确！');

        /**
         * 验证数据层
         */

        $DB = SQL::DB();

        $Res = $DB->select('user', '*', [
            'OR' => [
                'username' => $_QET['username'],
                'qq' => $_QET['qq'],
            ]
        ]);

        foreach ($Res as $value) {
            if ($value['username'] == $_QET['username']) dies(-1, '账号[' . $_QET['username'] . ']已经被Ta人注册过了,换个账号吧~');
            if ($value['qq'] == $_QET['qq']) dies(-1, 'QQ[' . $_QET['qq'] . ']已经被Ta人绑定过了,换个QQ吧~');
        }

        /**
         * 注册开始
         */

        $uid_md = md5($_SESSION['Login_res'] . time() . rand(10000, 999999));

        $Uty = UserConf::judge();
        if ($Uty == false) {
            $Uty = 0;
        } else $Uty = $Uty['id'];

        $Data = [
            'grade' => $conf['userdefaultgrade'],
            'user_idu' => $uid_md,
            'qq' => $_QET['qq'],
            'currency' => 0,
            'ip' => userip(),
            'image' => 'https://q4.qlogo.cn/headimg_dl?dst_uin=' . $_QET['qq'] . '&spec=100',
            'name' => $_QET['name'],
            'state' => 1,
            'recent_time' => $date,
            'found_date' => $date,
            'username' => $_QET['username'],
            'password' => md5($_QET['password']),
            'superior' => $Uty,
        ];

        $invite = -1;
        if (!empty($_COOKIE['INVITED_STATUS'])) {
            $C = $DB->get('user', ['id'], ['id' => (int)$_COOKIE['INVITED_STATUS'], 'LIMIT' => 1]);
            setcookie('INVITED_STATUS', null, time() - 3600 * 12 * 15, '/');
            if ($C) {
                $invite = $C['id'];
            }
        }
        $Res = $DB->insert('user', $Data);
        if ($Res) {
            $ID = $DB->id();
            $IP = userip();
            $GETID = $DB->get('user', '*', ['id' => $ID]);
            if (!$GETID) {
                dies(-1, '注册失败，请重新尝试！');
            }
            if ($invite >= 1) {
                $FTR = $DB->get('invite', '*', ['ip' => $IP, 'LIMIT' => 1]);
                if (!$FTR) {
                    $award = $conf['award'];
                    userlog('邀请奖励', '恭喜您成功邀请到用户[' . $_QET['name'] . ']特奖励您' . $award . $conf['currency'] . '！,再接再厉哦', $invite, $award);
                    $DB->insert('invite', [
                        'uid' => $invite,
                        'invitee' => $ID,
                        'award' => $award,
                        'ip' => $IP,
                        'creation_time' => $date,
                    ]);
                    Hook::execute('UserInvite', [
                        'id' => $invite,
                        'yid' => $ID,
                        'num' => $award
                    ]);
                } else {
                    userlog('失败邀请', '系统判断您的邀请对象：[' . $_QET['name'] . ']已经在其他账号接收过邀请,无法奖励！,请邀请真实用户！', $_COOKIE['INVITED_STATUS'], 0);
                }
            }

            if ($GETID) {
                query::OrderUser($ID);
                GoodsCart::UserCookieDer($ID);
            }

            setcookie('THEKEY', $uid_md, time() + 3600 * 12 * 15, '/');
            Hook::execute('UserRegister', [
                'id' => $GETID['id'],
                'name' => $GETID['name']
            ]);
            dies(1, '恭喜你,注册成功，欢迎入驻本平台！');
        } else {
            dies(-1, '注册失败!');
        }
        break;
    case 'VerificationCode': //创建登陆二维码图片
        if (empty($_QET['n'])) dies(-1, '请填写完整！');
        VerificationCode::RandomVerificationCode($_QET['n']);
        break;
    case 'prevent':
        if ($conf['userdomaintype'] == 1) {
            $url = is_https(false) . $UserData['domain'];
        } else $url = href(2) . '?t=' . $UserData['domain'];
        if ($conf['prevent_switch'] == 1) {
            dies(1, reward::prevent($url, 2));
        } else dies(1, $url);
        break;
    case 'user_pay':
        if (!$UserData) dies(-1, '请先完成登陆！');
        if (empty((float)$_QET['money']) || empty($_QET['type'])) dies(-1, '请提交完整哦!');
        $Money = (float)$_QET['money'];
        $Ex = explode('-', $conf['RechargeLimit'] ?? '0.01-2000');
        if ($Money < $Ex[0] || $Money > $Ex[1]) {
            dies(-1, "最少充值{$Ex[0]}元，最多充值{$Ex[1]}元，若要充值大额，可联系客服或分多次进行充值！");
        }
        reward::user_payet($_QET['money'], $_QET['type'], $UserData);
        break;
    case 'add_token': #生成
        if (!$UserData) dies(-1, '请先完成登陆！');
        $token = md5($UserData['token'] . rand(10000, 9999999999) . time());
        $DB = SQL::DB();
        $Res = $DB->update('user', [
            'token' => $token,
        ], [
            'id' => $UserData['id']
        ]);
        if ($Res) {
            dier([
                'code' => 1,
                'msg' => '对接密钥初始化成功！',
                'token' => $token,
            ]);
        } else {
            dies(-1, 'token更新失败');
        }
        break;
    case 'ip_data':
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if (empty($_QET['ip_data'])) dies(-1, '请填写完整');

        if ((int)$_QET['type'] === 2) {
            $Ip = $_QET['ip_data'];
        } else {
            $Ip = implode('|', $_QET['ip_data']);
        }
        $DB = SQL::DB();
        $a = $DB->update('user', [
            'ip_white_list' => $Ip
        ], [
            'id' => $UserData['id']
        ]);
        if ($a) {
            dier([
                'code' => 1,
                'msg' => '设置成功',
                'ip_data' => $Ip,
            ]);
        } else {
            dies(-1, '设置失败');
        }
        break;
    case 'user_updating':
        if (!$UserData) dies(-1, '请先完成登陆！');
        header('Content-Type:text/html;charset=utf8');
        QuickLogin::QQ_RebindingQuickLogin($_QET);
        break;
    case 'connected': //重新绑定QQ互联
        if (!$UserData) dies(-1, '请先完成登陆！');
        QuickLogin::QQ_Internet(ROOT_DIR . 'user/ajax.php?act=user_updating');
        break;
    case 'Send_verification_code':
        $num = $conf['usersmsbinding'];
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if (empty((int)$_QET['mobile'])) {
            dies(-1, '请将手机号填写完整！');
        }
        $DB = SQL::DB();
        $count = $DB->count('journal', '*', [
            'name' => '短信验证',
            'date' => $times,
            'uid' => $UserData['id']
        ]);
        if ($count >= $num) {
            dies(-1, '今日短信验证次数已经耗尽,每日仅可发送' . $num . '次验证码！');
        }
        if ($UserData['mobile'] === $_QET['mobile']) {
            dies(-1, '新手机号不能和旧的相同！');
        }

        $VerIf = $DB->get('user', ['id'], [
            'mobile' => (string)$_QET['mobile'],
            'id' => (int)$_QET['id']
        ]);
        if ($VerIf) {
            dies(-1, '此手机号已被用户：' . $VerIf['id'] . '绑定！');
        }
        $_SESSION['VerifyCode'] = SMS::randString();
        $_SESSION['Mobile'] = (int)$_QET['mobile'];

        $Res = SMS::SmsSend($_SESSION['VerifyCode'], $_SESSION['Mobile']);

        if ($Res['code'] == 1) {
            userlog('短信验证', '用户于' . $date . ' 为绑定手机号[' . $_SESSION['Mobile'] . '] 发送了短信验证码!', $UserData['id']);
            dier($Res);
        } else {
            dier($Res);
        }
        break;
    case 'Send_verification':
        if (!$UserData) dies(-1, '请先完成登陆！');
        if (empty($_SESSION['VerifyCode']) || empty($_SESSION['Mobile'])) dies(-1, '请先发送验证码！');
        if (empty((int)$_QET['code'])) dies(-1, '请填写完整！');
        if ($_SESSION['VerifyCode'] <> $_QET['code']) dies(-2, '验证码有误,请核对后再输入！');
        $DB = SQL::DB();
        $re = $DB->update('user', [
            'mobile' => $_SESSION['Mobile']
        ], [
            'id' => $UserData['id']
        ]);
        if ($re) {
            userlog('手机绑定', '用户于' . $date . '将绑定手机号修改为：' . $_SESSION['Mobile'], $UserData['id']);
            dies(1, '手机号绑定成功<br>您以后可以用手机号：' . $_SESSION['Mobile'] . '登陆后台了！!');
        } else dies(-1, '绑定失败！');
        break;
    case 'configuration_save':
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if ($conf['userleague'] <> 1) {
            dies(-1, '当前商城未开启用户加盟权限！');
        }
        if ($UserData['grade'] < $conf['userleaguegrade']) {
            dies(-1, '您当前的等级无法开通店铺,请先去提升等级！');
        }
        if (empty($_QET['type'])) {
            dies(-1, '请填写完整！');
        }

        $DB = SQL::DB();

        if ($UserData['configuration'] == '') {
            $configuration_arr = [];
        } else {
            $configuration_arr = config::common_unserialize($UserData['configuration']);
        }
        if (empty($configuration_arr)) {
            $configuration_arr = [];
        }
        if ($_QET['type'] !== 'notice') {
            foreach ($_QET as $key => $value) {
                $_QET[$key] = DeleteHtml($value);
            }
        }

        switch ($_QET['type']) {
            case 'domain':
                if ($conf['userdomaintype'] == 1) {
                    if (empty($_QET['domain']) || empty($_QET['prefix'])) dies(-1, '请填写完整！');
                    preg_match('/^[a-zA-Z0-9]{2,6}+$/u', $_QET['prefix'], $arr_pr);
                    if (empty($arr_pr[0])) dies(-1, '前缀只可为a-Z,0-9的字符串<br>并且前缀长度必须在2-6之间！');
                    $domain = $_QET['prefix'] . '.' . $_QET['domain'];
                    if (in_array($_QET['prefix'], explode(',', $conf['userdomainretain']))) dies(-1, '此域名前缀不可使用，换一个吧！');

                    if ($DB->get('user', ['domain'], ['domain' => $domain, 'id[!]' => $UserData['id']])) dies(-1, '域名（' . $domain . '）已经被他人绑定过了，换一个吧');
                    if ($domain === $UserData['domain']) {
                        dies(-1, '店铺域名无变化，修改失败');
                    }
                    if ($UserData['domain'] == '') {
                        $re = $DB->update('user', [
                            'domain' => $domain
                        ], [
                            'id' => $UserData['id']
                        ]);
                        $money = 0;
                    } else {
                        $money = $conf['userdomainsetmoney'];
                        if (explode('.', $UserData['domain'])[0] == $_QET['prefix']) $money = 0;
                        if ($UserData['money'] < $money) dies(-1, '余额不足,无法完成域名换绑！<br>还差' . ($money - $UserData['money']) . '元,快去充值吧');

                        $re = $DB->update('user', [
                            'domain' => $domain,
                            'money[-]' => $money
                        ], [
                            'id' => $UserData['id']
                        ]);
                    }
                    if ($re) {
                        userlog('域名绑定', '您在店铺管理界面绑定了域名：' . $domain, $UserData['id'], $money);
                        dies(1, '店铺域名绑定成功！<br>您以后可以用域名：' . $domain . '<br>访问您的店铺首页了哦！');
                    } else dies(-1, '店铺域名绑定失败,请联系管理员处理！');
                } else {
                    if (empty($_QET['prefix'])) dies(-1, '请填写完整！');
                    preg_match('/^[a-zA-Z0-9]{2,6}+$/u', $_QET['prefix'], $arr_pr);
                    if (empty($arr_pr[0])) dies(-1, '后缀小尾巴只可为a-Z,0-9的字符串<br>并且前缀长度必须在2-6之间！');
                    $domain = (string)$_QET['prefix'];
                    if (in_array($_QET['prefix'], explode(',', $conf['userdomainretain']))) dies(-1, '此域名小尾巴不可使用，换一个吧！');
                    if ($domain === $UserData['domain']) {
                        dies(-1, '店铺域名无变化，修改失败');
                    }
                    $vs = $DB->get('user', ['domain'], ['domain' => $domain, 'id[!]' => $UserData['id']]);
                    if ($vs) dies(-1, '域名小尾巴（' . $domain . '）已经被他人绑定过了，换一个吧');
                    if ($UserData['domain'] == '') {
                        $re = $DB->update('user', [
                            'domain' => $domain,
                        ], [
                            'id' => $UserData['id']
                        ]);
                        $money = 0;
                    } else {
                        $money = $conf['userdomainsetmoney'];
                        if (explode('.', $UserData['domain'])[0] == $_QET['prefix']) $money = 0;
                        if ($UserData['money'] < $money) dies(-1, '余额不足,无法完成域名换绑！<br>还差' . ($money - $UserData['money']) . '元,快去充值吧');
                        $re = $DB->update('user', [
                            'domain' => $domain,
                            'money[-]' => $money
                        ], [
                            'id' => $UserData['id']
                        ]);
                    }
                    if ($re) {
                        userlog('域名绑定', '您在店铺管理界面绑定了域名小尾巴：' . $domain, $UserData['id'], $money);
                        dies(1, '店铺域名绑定成功！<br>您以后可以用域名：' . href(2) . '?t=' . $domain . '<br>访问您的店铺首页了哦！');
                    } else dies(-1, '店铺域名绑定失败,请联系管理员处理！');
                }
                break;
            case 'template':
                if ($UserData['grade'] < $conf['usergradetem']) dies(-1, '您当前等级无法装修店铺,请先去提升等级吧！');
                $template_arr = for_dir(ROOT . 'template/');
                if (!in_array($_QET['template'], $template_arr) && $_QET['template'] != -1 && $_QET['template'] != -2) {
                    dies(-1, 'PC端模板[' . $_QET['template'] . ']不存在');
                }
                if (!in_array($_QET['template_m'], $template_arr) && $_QET['template_m'] != -1) {
                    dies(-1, '移动端模板[' . $_QET['template_m'] . ']不存在');
                }
                if (!in_array($_QET['background'], ['1', '2', '3', '4'])) {
                    dies(-1, '背景序列号不存在!');
                }
                $configuration_arr = array_merge($configuration_arr, [
                    'template' => $_QET['template'],
                    'template_m' => $_QET['template_m'],
                    'background' => $_QET['background'],
                    'banner' => $_QET['banner'],
                ]);
                $configuration_arr = serialize($configuration_arr);
                break;
            case 'webset':
                if ($UserData['grade'] < $conf['usergradenotice']) dies(-1, '您当前等级无法配置店铺基础信息,请先去提升等级吧！');
                if ((int)$_QET['award'] < 0) dies(-1, '请填写完整!');
                $award = $DB->get('config', '*', ['K' => 'award'])['V'];
                if ((int)$award < 0) dies(-1, '主站数据获取失败！');
                if ((int)$_QET['award'] > (int)$award) dies(-1, '邀请奖励不可高于主站哦<br>主站奖励为:' . $award . '!');
                $configuration_arr = array_merge($configuration_arr, [
                    'sitename' => $_QET['sitename'],
                    'keywords' => $_QET['keywords'],
                    'description' => $_QET['description'],
                    'kfqq' => $_QET['kfqq'],
                    'Communication' => $_QET['Communication'],
                    'award' => $_QET['award'],
                    'appurl' => $_QET['appurl'],
                    'currency' => $_QET['currency'],
                    'ForcedLanding' => $_QET['ForcedLanding'],
                    'GoodsRecommendation' => $_QET['GoodsRecommendation'],
                    'YzfSign' => ($_QET['YzfSign'] == '' ? -1 : $_QET['YzfSign']),
                    'CartState' => ($_QET['CartState'] == 1 ? 1 : 2),
                    'DynamicMessage' => $_QET['DynamicMessage'],
                    'ServiceImage' => $_QET['ServiceImage'],
                    'ServiceTips' => $_QET['ServiceTips'],
                    'SimilarRecommend' => ($_QET['SimilarRecommend'] == 1 ? 1 : -1),
                ]);
                $configuration_arr = serialize($configuration_arr);
                break;
            case 'notice':
                if ($UserData['grade'] < $conf['usergradenotice']) dies(-1, '您当前等级无法配置店铺公告哦,请先去提升等级吧！');
                $configuration_arr = array_merge($configuration_arr, [
                    'notice_top' => base64_encode($_QET['notice_top']),
                    'notice_check' => base64_encode($_QET['notice_check']),
                    'notice_bottom' => base64_encode($_QET['notice_bottom']),
                    'PopupNotice' => base64_encode($_QET['PopupNotice']),
                ]);
                $configuration_arr = serialize($configuration_arr);
                break;
            default:
                dies(-1, '未知访问');
                break;
        }

        $i = config::common_unserialize($configuration_arr);
        if (!$i || count($i) < 5) {
            dies(-1, '数据保存失败,请减少配置项里面的特殊符号，或者清空公告信息！');
        }

        $re = $DB->update('user', [
            'configuration' => $configuration_arr,
        ], [
            'id' => $UserData['id']
        ]);
        if ($re) {
            userlog('更新数据', '用户更新了加盟站点数据！', $UserData['id'], '0');
            dies(1, '数据保存成功！');
        } else dies(-1, '数据保存失败！');
        break;
    case 'WithdrawDeposit':
        if (!$UserData) dies(-1, '请先完成登陆！');
        if ($conf['userdeposit'] <> 1) dies(-1, '当前站点未开启提现功能！');
        if ($UserData['grade'] < $conf['userdepositgrade']) dies(-1, '您当前等级无法使用提现功能,请先去提升等级吧！');
        $arr_type = explode(',', $conf['userdeposittype']);
        if ($_QET['type'] == 'alipay' && $arr_type[0] <> 1) dies(-1, '当前支付宝提现方式未开启！');
        if ($_QET['type'] == 'wxpay' && $arr_type[1] <> 1) dies(-1, '当前微信提现方式未开启！');
        if ($_QET['type'] == 'qqpay' && $arr_type[2] <> 1) dies(-1, '当前QQ提现方式未开启！');
        if ((float)$_QET['money'] < $conf['userdepositmin']) dies(-1, '最低提现金额为' . $conf['userdepositmin'] . '元,提现条件未满足！');
        if ((float)$_QET['money'] > $UserData['money']) dies(-1, '余额不足,提现条件未满足！');
        $timestamp = md5($UserData['id'] . '晴玖');
        $images = ROOT . 'assets/img/withdraw/' . $timestamp . '/' . $UserData['id'] . '.png';
        if (!file_exists($images)) dies(-1, '请先上传收款二维码！');
        $moneys = (float)$_QET['money'];

        $DB = SQL::DB();

        $res = $DB->update('user', [
            'money[-]' => $moneys,
        ], [
            'id' => $UserData['id']
        ]);

        $re = $DB->insert('withdrawal', [
            'type' => $_QET['type'],
            'name' => $_QET['name'],
            'account_number' => $_QET['account_number'],
            'uid' => $UserData['id'],
            'remarks' => $_QET['remarks'],
            'money' => $_QET['money'],
            'addtime' => $date,
        ]);
        if ($res && $re) {
            userlog('余额提现', '您于' . $date . '进行了提现处理操作,提现金额为：' . $moneys, $UserData['id'], $moneys);
            Hook::execute('WithdrawNew', [
                'image' => $images,
                'type' => $_QET['type'],
                'name' => $_QET['name'],
                'account_number' => $_QET['account_number'],
                'uid' => $UserData['id'],
                'remarks' => $_QET['remarks'],
                'money' => $_QET['money'],
                'addtime' => $date,
            ]);
            dies(1, '提现请求成功');
        } else dies(-1, '提现请求失败,请联系管理员处理！');
        break;
    case 'image_content': #编辑器专用
        unset($_QET['act']);
        $ImageArr = [];
        $timestamp = date('Ymd');
        mkdirs('../assets/img/image/' . $timestamp . '/');
        foreach ($_QET as $key => $value) {
            if ($value['type'] !== 'image/png' && $value['type'] !== 'image/gif' && $value['type'] !== 'image/jpeg') dies(-1, '只可上传png/jpg/gif类型的图片文件！');
            $ImageName = md5_file($value['tmp_name']);
            switch ($value['type']) {
                case 'image/png':
                    $ImageName .= '.png';
                    break;
                case 'image/gif':
                    $ImageName .= '.gif';
                    break;
                case 'image/jpeg':
                    $ImageName .= '.jpeg';
                    break;
                default:
                    $ImageName .= '.png';
                    break;
            }

            move_uploaded_file($value['tmp_name'], '../assets/img/image/' . $timestamp . '/' . $ImageName);
            $images = '/assets/img/image/' . $timestamp . '/' . $ImageName;
            new ImgThumbnail(ROOT . $images, $conf['compression'], ROOT . $images, 2);
            $ImageArr[] = ['src' => ImageUrl($images), 'size' => $value['size'] / 1000 . 'kb', 'name' => $value['name']];
        }
        dier([
            'code' => 1,
            'msg' => '图片上传成功,本次共成功上传' . count($ImageArr) . '张图片',
            'SrcArr' => $ImageArr,
        ]);
        break;
    case 'image_up': #上传图片
        $image = explode('.', $_QET['file']['name']);
        if ($_QET['file']['type'] !== 'image/png' && $_QET['file']['type'] !== 'image/gif' && $_QET['file']['type'] !== 'image/jpeg') dies(-1, '只可上传png/jpg/gif类型的图片文件！');
        $timestamp = date('Ymd');
        mkdirs('../assets/img/image/' . $timestamp . '/');
        $ImageName = md5_file($_QET['file']['tmp_name']);
        switch ($_QET['file']['type']) {
            case 'image/png':
                $ImageName .= '.png';
                break;
            case 'image/gif':
                $ImageName .= '.gif';
                break;
            case 'image/jpeg':
                $ImageName .= '.jpeg';
                break;
            default:
                $ImageName .= '.png';
                break;
        }
        move_uploaded_file($_QET['file']['tmp_name'], '../assets/img/image/' . $timestamp . '/' . $ImageName);
        $images = '/assets/img/image/' . $timestamp . '/' . $ImageName;
        new ImgThumbnail(ROOT . $images, $conf['compression'], ROOT . $images, 2);
        if (isset($_QET['type']) && (int)$_QET['type'] === 2) {
            dier([
                'location' => ImageUrl($images),
            ]);
        }
        dier([
            'code' => 0,
            'msg' => '上传成功,上传的图片大小为：' . $_QET['file']['size'] / 1000 . 'kb',
            'src' => ImageUrl($images),
        ]);
        break;
    case 'DoGatheringFigure':
        if (!$UserData) dies(-1, '请先完成登陆！');
        if ($conf['userdeposit'] <> 1) dies(-1, '当前站点未开启提现,无法上传收款图！');
        if ($UserData['grade'] < $conf['userdepositgrade']) dies(-1, '您当前等级无法使用提现功能,请先去提升等级吧！');
        if ($_QET['file']['type'] !== 'image/png' && $_QET['file']['type'] !== 'image/gif' && $_QET['file']['type'] !== 'image/jpeg') dies(-1, '只可上传png/jpg/gif类型的图片文件！');
        $image = explode('.', $_QET['file']['name']);
        $timestamp = md5($UserData['id'] . '晴玖');
        mkdirs('../assets/img/withdraw/' . $timestamp . '/');
        $image = $UserData['id'] . '.png';
        move_uploaded_file($_QET['file']['tmp_name'], '../assets/img/withdraw/' . $timestamp . '/' . $image);
        $images = '/assets/img/withdraw/' . $timestamp . '/' . $image;
        new ImgThumbnail(ROOT . $images, $conf['compression'], ROOT . $images, 2);
        dier(['code' => 0, 'msg' => '上传成功,上传的收款码大小为：' . $_QET['file']['size'] / 1000 . 'kb', 'src' => href(2) . ROOT_DIR . $images . '?time=' . time()]);
        break;
    case 'withdraw_deposit':
        if (!$UserData) dies(-1, '请先完成登陆！');
        if ($conf['userdeposit'] <> 1) {
            dier([
                'code' => 0,
                'msg' => '当前站点未开启提现',
                'count' => 0,
                'data' => []
            ]);
        }
        if ($UserData['grade'] < $conf['userdepositgrade']) {
            dier([
                'code' => 0,
                'msg' => '您当前等级无法使用提现功能,请先去提升等级吧！',
                'count' => 0,
                'data' => []
            ]);
        }

        $page = ((int)$_GET['page'] - 1) * (int)$_GET['limit'];
        $limit = (int)$_GET['limit'];
        $DB = SQL::DB();

        $Res = $DB->select('withdrawal', '*', [
            'uid' => $UserData['id'],
            'ORDER' => [
                'id' => 'DESC'
            ],
            'LIMIT' => [$page, $limit]
        ]);
        $Data = [];
        foreach ($Res as $v) {
            $v['arrival_amount'] = round($v['money'] - (($conf['userdepositservice'] / 100) * $v['money']), 2);
            $Data[] = $v;
        }

        dier([
            'code' => 0,
            'msg' => '数据获取成功',
            'count' => $DB->count('withdrawal', ['uid' => $UserData['id']]) - 0,
            'data' => $Data
        ]);
        break;
    case 'Tickets': //工单管理
        if (!$UserData) dies(-1, '请先完成登陆！');
        if (!isset($_QET['type'])) dies(-1, '请填写完整');
        $DB = SQL::DB();
        switch ($_QET['type']) {
            case 'list':
                if ($_QET['state'] === 'all') {
                    $List = $DB->select('tickets', ['id', 'name', 'class', 'state', 'addtime', 'grade', 'type'], ['uid' => $UserData['id'], 'ORDER' => ['id' => 'DESC']]);
                } else {
                    $List = $DB->select('tickets', ['id', 'name', 'class', 'state', 'addtime', 'grade', 'type'], ['uid' => $UserData['id'], 'state' => $_QET['state'], 'ORDER' => ['id' => 'DESC']]);
                }
                dier([
                    'code' => 1,
                    'msg' => '工单列表获取成功',
                    'data' => $List,
                    'url' => href(2),
                ]);
                break;
            case 'details': //工单详情
            case 'Supplementary': //补充内容
            case 'Finish': //完结工单
            case 'Grade': //评分
                $Tickets = $DB->get('tickets', '*', ['id' => (int)$_QET['id'], 'uid' => (int)$UserData['id']]);
                if (!$Tickets) dies(-1, '订单不存在！');
                if ($Tickets['message'] == '') {
                    $Data = [];
                } else {
                    $Data = config::common_unserialize($Tickets['message']);
                }
                if ($_QET['type'] === 'details') {
                    dier([
                        'code' => 1,
                        'msg' => '数据获取成功',
                        'data' => $Data,
                        'class' => $Tickets['class'],
                        'time' => $Tickets['timetips'],
                        'order' => ($Tickets['order'] == null ? '无相关订单' : $Tickets['order']),
                        'state' => $Tickets['state'],
                        'grade' => $Tickets['grade'],
                        'count' => count($Data),
                        'type' => $Tickets['type'],
                    ]);
                } else if ($_QET['type'] === 'Supplementary') {
                    if ($Tickets['type'] == 4) dies(-1, '此工单已经关闭,请重新创建新的工单！');
                    if ($Tickets['type'] >= 3 || $Tickets['state'] == 3) dies(-1, '工单已完结,若要提交内容请创建新的工单!');

                    $Data = array_merge($Data, [
                        $date => [
                            'type' => 1,
                            'content' => $_QET['content'],
                        ]
                    ]);

                    $Res = $DB->update('tickets', [
                        'message' => $Data,
                        'type' => 1,
                    ], [
                        'id' => $_QET['id'],
                        'uid' => $UserData['id'],
                    ]);
                    if ($Res) {
                        Hook::execute('WorkOrderReply', [
                            'identity' => $UserData['id'],
                            'content' => $_QET['content'],
                            'id' => $_QET['id']
                        ]);
                        dies(1, '工单信息补充成功！');
                    } else dies(-1, '工单信息补充失败');
                } else if ($_QET['type'] == 'Finish') {
                    $Res = $DB->update('tickets', [
                        'state' => 1,
                        'type' => 3,
                        'endtime' => $date,
                    ], [
                        'id' => $_QET['id'],
                        'uid' => $UserData['id'],
                    ]);
                    if ($Res) {
                        if ($Tickets['order'] <> '不选择相关订单') {
                            @$DB->update('order', ['state' => 1], ['order' => $Tickets['order']]);
                        }
                        userlog('完结工单', '用户将工单[' . $_QET['id'] . ']的状态设置为已完结状态', $UserData['id'], 0);
                        Hook::execute('WorkOrderEnd', [
                            'identity' => $UserData['id'],
                            'id' => $_QET['id']
                        ]);
                        dies(1, '工单[' . $_QET['id'] . ']成功结单,若有其他问题可再创建工单哦！');
                    } else dies(-1, '结单失败！');
                } else if ($_QET['type'] == 'Grade') {
                    if ($Tickets['grade'] <> '') dies(-1, '此工单无法评分,条件不足！');
                    $Res = $DB->update('tickets', [
                        'grade' => (int)$_QET['n'],
                    ], [
                        'id' => $_QET['id'],
                        'uid' => $UserData['id'],
                    ]);
                    if ($Res) {
                        dies(1, '工单[' . $_QET['id'] . ']成功打分成功,感谢您的支持！');
                    } else dies(-1, '打分失败！');
                }
                break;
            case 'New': //新建工单
                $TicketsClass = explode(',', $conf['TicketsClass']);
                if (!in_array($_QET['class'], $TicketsClass)) dies(-1, '参数异常！');
                if (empty($_QET['title']) || empty($_QET['content']) || empty($_QET['time'])) dies(-1, '请提交完整！');
                if ($_QET['order'] <> '不选择相关订单') {
                    $Order = $DB->get('order', '*', ['order' => (string)$_QET['order'], 'uid' => (int)$UserData['id']]);
                    if (!$Order) {
                        dies(-1, '订单不存在,或此订单非你所有!');
                    }
                }

                $Vs = $DB->count('tickets', [
                        'uid' => $UserData['id'], 'state' => 2, 'type' => [1, 2],
                        'name' => $_QET['title']
                    ]) - 0;
                if ($Vs >= 1) {
                    dies(-1, '请勿提交重复标题的工单！');
                }

                $Count = $DB->count('tickets', ['uid' => $UserData['id'], 'state' => 2, 'type' => [1, 2]]);
                if ($Count > 6) {
                    dies(-1, '进行中的工单最多只可同时存在6个！');
                }
                $Res = $DB->insert('tickets', [
                    'uid' => $UserData['id'],
                    'order' => $_QET['order'],
                    'name' => $_QET['title'],
                    'content' => $_QET['content'],
                    'class' => $_QET['class'],
                    'timetips' => $_QET['time'],
                    'addtime' => $date,
                ]);

                $ID = $DB->id();

                if ($Res) {
                    if ($_QET['order'] <> '不选择相关订单') {
                        $DB->update('order', ['state' => 6], ['order' => $_QET['order']]);
                    }

                    Hook::execute('WorkOrderNew', [
                        'uid' => $UserData['id'],
                        'order' => $_QET['order'],
                        'name' => $_QET['title'],
                        'content' => $_QET['content'],
                        'class' => $_QET['class'],
                        'timetips' => $_QET['time'],
                        'addtime' => $date,
                    ]);

                    dies(1, '您的工单已经创建成功,分配的工单号为：' . $ID);
                } else dies(-1, '工单创建失败,请联系管理员处理！');
                break;
            default:
                dies(-1, '403');
                break;
        }
        break;
    case 'Mark': //评论管理
        if (!$UserData) dies(-1, '请先完成登陆！');
        $DB = SQL::DB();
        switch ($_QET['type']) {
            case 'List':
                $Res = $DB->select('mark', '*', [
                    'ORDER' => [
                        'id' => 'DESC'
                    ],
                    'uid' => $UserData['id'],
                    'LIMIT' => [(($_QET['page'] - 1) * $_QET['limit']), $_QET['limit']],
                ]);
                $Count = $DB->count('mark', ['uid' => $UserData['id']]);

                $Data = [];
                foreach ($Res as $val) {
                    $val['ImageArr'] = explode('|', $val['image']);
                    $Arr = [];
                    foreach ($val['ImageArr'] as $v) {
                        $Arr[] = ImageUrl($v);
                    }
                    $val['ImageArr'] = $Arr;
                    $Data[] = $val;
                }

                dier([
                    'code' => 0,
                    'msg' => '获取成功',
                    'data' => $Data,
                    'count' => $Count,
                ]);
                break;
        }
        break;
    case 'AlterPrice': //商品价格调整！
        if (!$UserData) dies(-1, '请先完成登陆！');
        if ($UserData['grade'] < $conf['usergradeprofit']) dies(-1, '您当前等级无法设置商品价格,请先去提升等级吧！');
        test(['gid|e', 'rise|i']);
        if ((float)$_QET['rise'] < 0) dies(-1, '价格涨幅比例不能低于0%！');
        if ((float)$_QET['rise'] > 1000) dies(-1, '价格涨幅比例不能高于1000%！');
        if (empty($_QET['gid']) != -1 && empty($_QET['gid']) < 0) dies(-1, '商品ID有误！');
        $DB = SQL::DB();
        $Deploy = json_decode($UserData['pricehike'], TRUE); //商品配置

        if ((int)$_QET['gid'] == -1) {
            /**
             * 调整全部
             */
            $GoodsList = $DB->select('goods', ['gid'], [
                'name[~]' => $_QET['name'],
                'state' => 1
            ]);

            if (count($GoodsList) == 0) dies(-1, '无可改价商品！');
            foreach ($GoodsList as $value) {
                $Deploy[$value['gid']]['rise'] = (float)$_QET['rise'];
            }
            $Msg = '恭喜,您本次成功将' . count($GoodsList) . '个商品的价格涨幅比设置为：' . (float)$_QET['rise'] . '%';
            $log = '用户[' . $UserData['id'] . ']于' . $date . '成功将' . count($GoodsList) . '个商品的价格涨幅比设置为：' . (float)$_QET['rise'] . '%';
        } else {
            /**
             * 调整单个
             */
            $Get = $DB->get('goods', ['name', 'state', 'gid'], ['gid' => (int)$_QET['gid']]);
            if (!$Get) dies(-1, '编号为：' . $_QET['gid'] . '的商品不存在！');
            if ($Get['state'] != 1) dies(-1, '编号为：' . $_QET['gid'] . '的商品已经下架！');
            $Deploy[$Get['gid']]['rise'] = (float)$_QET['rise'];

            $Msg = '恭喜,您成功将商品[' . $Get['name'] . ']的价格涨幅比设置为：' . (float)$_QET['rise'] . '%';
            $log = '用户[' . $UserData['id'] . ']于' . $date . '将商品[' . $Get['name'] . ']的价格涨幅比设置为：' . (float)$_QET['rise'] . '%';
        }

        $Res = $DB->update('user', [
            'pricehike' => json_encode($Deploy, JSON_UNESCAPED_UNICODE)
        ], [
            'id' => $UserData['id']
        ]);

        if ($Res) {
            userlog('商品改价', $log, $UserData['id'], (float)$_QET['rise']);
            dies(1, $Msg);
        } else dies(-1, '调整失败！');
        break;
    case 'GoodsState': //调整商品上下架状态
        if (!$UserData) dies(-1, '请先完成登陆！');
        if ($UserData['grade'] < $conf['usergradegoodsstate']) dies(-1, '您当前等级无法设置商品上下架状态,请先去提升等级吧！');
        test(['gid|e', 'state|e']);
        if ((int)$_QET['state'] != 1 && (int)$_QET['state'] != -1) dies(-1, '状态有误！');
        if (empty($_QET['gid']) != -1 && empty($_QET['gid']) < 0) dies(-1, '商品ID有误！');
        $DB = SQL::DB();
        $Deploy = json_decode($UserData['pricehike'], TRUE); //商品配置

        if ((int)$_QET['gid'] == -1) {
            /**
             * 调整全部
             */
            $GoodsList = $DB->select('goods', ['gid'], [
                'name[~]' => $_QET['name'],
                'state' => 1
            ]);

            if (count($GoodsList) == 0) dies(-1, '无可设置商品！');
            foreach ($GoodsList as $value) {
                $Deploy[$value['gid']]['state'] = (int)$_QET['state'];
            }
            $Msg = '恭喜,您本次成功将' . count($GoodsList) . '个商品的状态设置为' . ($_QET['state'] == 1 ? '上架' : '下架');
            $log = '用户[' . $UserData['id'] . ']于' . $date . '成功将' . count($GoodsList) . '个商品的状态设置为：' . ($_QET['state'] == 1 ? '上架' : '下架');
        } else {
            /**
             * 调整单个
             */
            $Get = $DB->get('goods', ['name', 'state', 'gid'], ['gid' => (int)$_QET['gid']]);
            if (!$Get) dies(-1, '编号为：' . $_QET['gid'] . '的商品不存在！');
            if ($Get['state'] != 1) dies(-1, '编号为：' . $_QET['gid'] . '的商品已经下架！');
            $Deploy[$Get['gid']]['state'] = (int)$_QET['state'];

            $Msg = '恭喜,您成功将商品[' . $Get['name'] . ']的状态设置为：' . ($_QET['state'] == 1 ? '上架' : '下架');
            $log = '用户[' . $UserData['id'] . ']于' . $date . '将商品[' . $Get['name'] . ']的状态设置为：' . ($_QET['state'] == 1 ? '上架' : '下架');
        }

        $Res = $DB->update('user', [
            'pricehike' => json_encode($Deploy, JSON_UNESCAPED_UNICODE)
        ], [
            'id' => $UserData['id']
        ]);

        if ($Res) {
            userlog('状态调整', $log, $UserData['id'], (float)$_QET['rise']);
            dies(1, $Msg);
        } else dies(-1, '调整失败！');
        break;
    case 'LogList': //获取日志列表
        if (!$UserData) dies(-1, '请先完成登陆！');
        test(['page|e', 'uid|e']);

        $DB = SQL::DB();
        $LIMIT = 8;
        $Page = ($_QET['page'] - 1) * $LIMIT;

        if ($_QET['uid'] == -1) {
            $Res = $DB->select('journal', '*', [
                'name[~]' => $_QET['name'],
                'LIMIT' => [$Page, $LIMIT],
                'uid' => $UserData['id'],
                'ORDER' => [
                    'id' => 'DESC'
                ]
            ]);

            if (!empty($_QET['name'])) {
                $Count = $DB->count('journal', ['name[~]' => $_QET['name'], 'uid' => $UserData['id']]);
            } else $Count = $DB->count('journal', ['uid' => $UserData['id']]);
        } else {
            if (!empty($_QET['name'])) {
                $Res = $DB->select('journal', '*', [
                    'name[~]' => $_QET['name'],
                    'content[~]' => `[` . $_QET['uid'] . `]`,
                    'LIMIT' => [$Page, $LIMIT],
                    'uid' => $UserData['id'],
                    'ORDER' => [
                        'id' => 'DESC'
                    ]
                ]);
            } else {
                $Res = $DB->select('journal', '*', [
                    'name[~]' => ['货币提成', '余额提成', '升级提成'],
                    'content[~]' => `[` . $_QET['uid'] . `]`,
                    'LIMIT' => [$Page, $LIMIT],
                    'uid' => $UserData['id'],
                    'ORDER' => [
                        'id' => 'DESC'
                    ]
                ]);
            }

            if (!empty($_QET['name'])) {
                $Count = $DB->count('journal', ['name[~]' => $_QET['name'], 'uid' => $UserData['id'], 'content[~]' => `[` . $_QET['uid'] . `]`]);
            } else $Count = $DB->count('journal', ['uid' => $UserData['id'], 'name[~]' => ['货币提成', '余额提成', '升级提成'], 'content[~]' => `[` . $_QET['uid'] . `]`]);
        }

        $Data = [];
        foreach ($Res as $v) {
            if ($v['name'] == '余额提成' || $v['name'] == '货币提成' || $v['name'] == '余额提成(无效)' || $v['name'] == '货币提成(无效)') {
                $v['content'] = explode('！', $v['content'])[0] . '！';
            }
            $Data[] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'ip' => $v['ip'],
                'date' => $v['date'],
                'count' => round($v['count'], 8),
                'content' => $v['content']
            ];
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
            'count' => $Count
        ]);
        break;
    case 'GoodsList': //获取商品列表
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }

        if (!$UserData || empty($UserData['grade'])) {
            $grade = 1;
        } else {
            $grade = (int)$UserData['grade'];
        }

        test(['page|e']);

        $DB = SQL::DB();

        $LIMIT = $_QET['limit'];
        if (empty($LIMIT)) {
            $LIMIT = 8;
        }
        $Page = ($_QET['page'] - 1) * $LIMIT;

        $Res = $DB->select(
            'goods',
            [
                '[>]class' => ['cid' => 'cid']
            ],
            [
                'money',
                'profits',
                'gid',
                'selling',
                'method',
                'sales',
                'goods.name',
                'goods.image',
                'accuracy',
                'quota',
                'quantity',
                'goods.docs',
                'units'
            ],
            [
                'goods.state' => 1,
                'class.state' => 1,
                'goods.name[~]' => (empty($_QET['name']) ? '' : $_QET['name']),
                'class.grade[<=]' => $grade,
                'LIMIT' => [$Page, $LIMIT],
                'ORDER' => [
                    'gid' => 'DESC'
                ]
            ]
        );
        $Data = [];
        $Deploy = json_decode($UserData['pricehike'], TRUE); //商品配置
        foreach ($Res as $res) {
            $price = Price::Get($res['money'], $res['profits'], $UserData['grade'], $res['gid'], $res['selling']);
            $res['method'] = PaymentMethodAnalysis(json_decode($res['method'], true));

            if ($price['price'] < 0) $price['price'] = 0.01;
            $res['price'] = round($price['price'], 8);
            $res['points'] = round($price['points']);

            $res['sales'] = CommoditySalesAnalysis($res['gid'], $res['sales']);

            if (isset($Deploy[$res['gid']]['rise']) && !empty((float)$Deploy[$res['gid']]['rise'])) {
                $Rise = ((float)$Deploy[$res['gid']]['rise'] <= 0 ? 0 : (float)$Deploy[$res['gid']]['rise']);
            } else {
                $Rise = 0;
            }

            $Data[] = [
                'gid' => $res['gid'],
                'name' => $res['name'],
                'image' => ImageUrl(json_decode($res['image'], true)[0]),
                'method' => $res['method'],
                'price' => round($res['price'], $res['accuracy']), //成本
                'points' => $res['points'],
                'quota' => $res['quota'],
                'quantity' => (int)$res['quantity'],
                'docs' => $res['docs'],
                'sales' => $res['sales'],
                'state' => ($Deploy[$res['gid']]['state'] != -1 ? 1 : -1), //商品自定义状态
                'rise' => $Rise,  //商品价格涨幅百分比0~100
                'units' => $res['units']
            ];
        }

        $Count = $DB->count('goods', [
            '[>]class' => ['cid' => 'cid']
        ], [
            'goods.gid'
        ], [
            'goods.state' => 1,
            'class.state' => 1,
            'goods.name[~]' => (empty($_QET['name']) ? '' : $_QET['name']),
            'class.grade[<=]' => $grade,
        ]);

        dier([
            'code' => 1,
            'data' => $Data,
            'count' => $Count
        ]);
        break;
    case 'SubordinateUserMoney': //查询下级用户带来的收益
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if ($UserData['grade'] < 2) {
            dies(-1, '权限不足！');
        }
        test(['uid|e'], '请将用户ID填写完整！');
        CookieCache::read();
        $Data = [
            'code' => 1,
            'msg' => '获利数据获取成功',
            'data' => SubordinateUserMoney($UserData['id'], $_QET['uid'])
        ];
        CookieCache::add($Data, 600);
        dier($Data);
        break;
    case 'SubordinateUser': //获取下级用户列表
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if ($UserData['grade'] < 2) {
            dies(-1, '权限不足！');
        }
        test(['page|e', 'type|e', 'limit|e'], '请填写完整哦！');
        if ($_QET['type'] == 1) {
            CookieCache::read(md5('SubordinateUser' . $_QET['page'] . $_QET['limit']));
        }
        $Data = SubordinateUser($UserData, 1, $UserData['id']);
        if ($Data === -1) {
            dier([
                'code' => 1,
                'data' => [],
                'count' => 0
            ]);
        } else {
            if ($Data != -1) {
                $Data = array_sort($Data, 'id', 'desc');
            }
            $limit = $_QET['limit'];
            if (empty($limit)) {
                $limit = 8;
            }
            $page = ($_QET['page'] - 1) * $limit;
            $limit = $limit + $page;
            $ArrayData = [];
            for ($i = $page; $i < $limit; $i++) {
                if (empty($Data[$i])) {
                    continue;
                }
                $Data[$i]['money'] = -1;
                $Data[$i]['currency'] = -1;
                $ArrayData[] = $Data[$i];
            }
            $DataS = [
                'code' => 1,
                'data' => $ArrayData,
                'count' => count($Data),
                'currency' => $conf['currency']
            ];
            CookieCache::add($DataS, 600000);
            dier($DataS);
        }
        break;
    case 'CouponList': //获取优惠券列表
        if (!$UserData) dies(-1, '请先完成登陆！');
        $DB = SQL::DB();
        test(['type|e'], '请提交需要获取的类型');
        $LIMIT = $_QET['limit'];
        if (empty($LIMIT)) {
            $LIMIT = 16;
        }
        $Page = ($_QET['page'] - 1) * $LIMIT;

        $SQL = [
            'LIMIT' => [$Page, $LIMIT],
            'uid' => $UserData['id'],
            'ORDER' => [
                'id' => 'DESC'
            ]
        ];


        if ($_QET['type'] == 1) {
            $SQL = array_merge($SQL, ['oid' => -1]);
            $SQLC = [
                'uid' => $UserData['id'],
                'oid' => -1,
            ];
        } else {
            $SQL = array_merge($SQL, ['oid[!]' => -1]);
            $SQLC = [
                'uid' => $UserData['id'],
                'oid[!]' => -1,
            ];
        }

        if (!empty($_QET['gid'])) {
            $SQL = array_merge($SQL, ['gid' => $_QET['gid']]);
            $SQLC = array_merge($SQLC, ['gid' => $_QET['gid']]);
        }

        if (!empty($_QET['name'])) {
            $SQL = array_merge($SQL, [
                'OR' => [
                    'id[~]' => $_QET['name'],
                    'oid[~]' => $_QET['name'],
                    'token[~]' => $_QET['name'],
                    'ip[~]' => $_QET['name'],
                    'name[~]' => $_QET['name'],
                ]
            ]);

            $SQLC = array_merge($SQLC, [
                'OR' => [
                    'id[~]' => $_QET['name'],
                    'oid[~]' => $_QET['name'],
                    'token[~]' => $_QET['name'],
                    'ip[~]' => $_QET['name'],
                    'name[~]' => $_QET['name'],
                ]
            ]);
        }

        $Res = $DB->select('coupon', '*', $SQL);
        if (count($Res) == 0) dies(-1, '空空如也！');

        $Data = [];
        foreach ($Res as $v) {
            if ($v['term_type'] == 1) {
                $TIME = strtotime($v['gettime']) + (60 * 60 * 24 * $v['indate']);
            } else {
                $TIME = strtotime($v['expirydate']);
            }
            unset($v['uid'], $v['ip'], $v['indate'], $v['expirydate'], $v['get_way'], $v['limit'], $v['limit_token'], $v['addtime']);
            $v['minimum'] = round($v['minimum'], 8);
            $v['money'] = round($v['money'], 8);
            $v['expirydate'] = (($TIME - time()) <= 0 ? '已过期' : Sec2Time($TIME - time()));
            $Data[] = $v;
        }

        $Count = $DB->count('coupon', $SQLC);

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
            'count' => $Count
        ]);
        break;
    case 'CouponConversion': //兑换优惠券
        if (!$UserData) dies(-1, '请先完成登陆！');
        $DB = SQL::DB();
        test(['token|e'], '请提交优惠券 券码!');
        $User = $UserData;

        $Coupon = $DB->get('coupon', '*', [
            'token' => (string)$_QET['token'],
            'oid' => -1,
            'uid' => -1,
        ]);

        if (!$Coupon) dies(-1, '您提交的券码可能是无效券码，请核对后再来兑换！');

        $Count = $DB->count('coupon', [
            'limit_token' => $Coupon['limit_token'],
            'uid' => $User['id'],
        ]);

        if ($Count >= $Coupon['limit']) dies(-1, '此批优惠券每个用户最多兑换' . $Coupon['limit'] . '张，无法继续兑换！');

        $CSQL = [
            'uid' => $User['id'],
            'gettime[>]' => $times,
        ];

        if ($conf['CouponUseIpType'] == 1) {
            $CSQL = [
                'OR' => [
                    'uid' => $User['id'],
                    'ip' => userip()
                ],
                'gettime[>]' => $times,
            ];
        }

        $CountUser = $DB->count('coupon', $CSQL);

        if ($CountUser >= $conf['CouponGetMax']) {
            dies(-1, '今日最多可获得' . $conf['CouponGetMax'] . '张优惠券,您已经获得了' . $CountUser . '张,请改日再来');
        }


        $Res = $DB->update('coupon', [
            'uid' => $User['id'],
            'gettime' => $date,
            'ip' => userip()
        ], [
            'id' => $Coupon['id'],
        ]);
        if ($Res) {
            userlog('兑换优惠券', '用户[' . $User['id'] . ']于' . $date . '兑换了优惠券[' . $Coupon['token'] . ']', $User['id'], '0');
            dies(1, '恭喜你兑换成功！');
        } else dies(-1, '抱歉，兑换失败!');
        break;
    case 'AppUploading': //上传
        if (!$UserData) dies(-1, '请先完成登陆！');
        $Data = App::AppUploading();
        if ($Data['code'] >= 1 && $_QET['id'] >= 1 && $_QET['type'] >= 1) {
            $DB = SQL::DB();
            $Res = $DB->update('app', [
                ($_QET['type'] == 1 ? 'icon' : 'background') => $Data['id'],
            ], [
                'id' => $_QET['id'],
                'TaskID' => -1
            ]);
        }
        dier($Data);
        break;
    case 'AppCount': //APP数量
        if (!$UserData) dies(-1, '请先完成登陆！');
        $DB = SQL::DB();
        $SQL = [
            'uid' => $UserData['id'],
        ];
        if (!empty($_QET['name'])) {
            $SQL['name[~]'] = $_QET['name'];
        }
        $Res = $DB->count('app', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'AppList': //生成列表
        if (!$UserData) dies(-1, '请先完成登陆！');
        test(['page|e', 'limit|e'], '参数缺失');
        $Data = $_QET;
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
            'uid' => $UserData['id'],
        ];

        $DB = SQL::DB();

        if (!empty($Data['name'])) {
            $SQL['name[~]'] = $Data['name'];
        }
        $Res = $DB->select('app', [
            'id', 'uid', 'TaskID', 'TaskMsg',
            'name', 'url', 'content', 'state',
            'theme', 'load_theme', 'money',
            'icon', 'background', 'endtime',
            'addtime'
        ], $SQL);
        if (!$Res) {
            $Res = [];
        }

        $SQL = [
            'uid' => $UserData['id'],
        ];
        if (!empty($_QET['name'])) {
            $SQL['name[~]'] = $_QET['name'];
        }
        $Count = $DB->count('app', $SQL);

        $money = $conf['appprice'];
        if (strstr($money, '|')) {
            $ex = explode('|', $money);
            $price = $ex[(int)$UserData['grade']];
            if (empty($price)) {
                $price = $ex[0];
            }
            $price = (float)$price;
        } else {
            $price = (float)$money;
        }
        if ($price < 0) {
            $price = 2;
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Res,
            'count' => $Count,
            'ImageUrl' => href(2) . ROOT_DIR . 'ajax.php?act=AppImage&id=',
            'Price' => $price,
            'action' => href(2) . ROOT_DIR . 'user/ajax.php?act=AppUploading'
        ]);
        break;
    case 'AppAdd': //创建App生成任务
        if (!$UserData) dies(-1, '请先完成登陆！');
        test(['name|e', 'url|e'], '请提交完整！');

        if ($_QET['url'] === '-1') {
            //匹配用户URL
            if (empty($UserData['domain'])) {
                dies(-1, '您的站点还未绑定店铺域名！');
            }
            if ((int)$conf['userdomaintype'] === 1) {
                $_QET['url'] = is_https(false) . $UserData['domain'];
            } else {
                $_QET['url'] = href(2) . '?t=' . $UserData['domain'];
            }
        }
        /**
         * 定价
         */
        $money = $conf['appprice'];
        if (strstr($money, '|')) {
            $ex = explode('|', $money);
            $price = $ex[(int)$UserData['grade']];
            if (empty($price)) {
                $price = $ex[0];
            }
            $price = (float)$price;
        } else {
            $price = (float)$money;
        }
        if ($price < 0) {
            $price = 2;
        }

        if ((float)$UserData['money'] < $price) {
            dies(-1, '当前余额不足' . $price . '元，无法创建App打包任务！');
        }

        $SQL = [
            'uid' => $UserData['id'],
            'name' => $_QET['name'],
            'url' => StringCargo::UrlVerify($_QET['url'], 3),
            'theme' => $conf['appthemecolor'],
            'load_theme' => $conf['apploadthemecolor'],
            'icon' => $conf['appiconid'],
            'background' => $conf['appbackgroundid'],
            'addtime' => $date,
            'TaskMsg' => '任务待提交',
            'money' => $price,
        ];
        $DB = SQL::DB();
        $Res = $DB->insert('app', $SQL);
        if ($Res) {
            $ID = $DB->id();
            $DB->update('user', [
                'money[-]' => $price,
            ], [
                'id' => $UserData['id']
            ]);
            userlog('App打包', '用户[' . $UserData['id'] . ']在分店后台创建了App打包任务！，任务ID为：' . $ID, $UserData['id'], $price);
            dies(1, 'App打包任务创建成功');
        }
        dies(-1, '任务创建失败！');
        break;
    case 'AppColorSet': //修改App任务配色
        if (!$UserData) dies(-1, '请先完成登陆！');
        RVS(1000);
        test(['type|e', 'color|e', 'id']);
        if (strlen($_QET['color']) !== 7 || !strstr($_QET['color'], '#')) {
            dies(-1, '颜色格式有误！' . $_QET['color']);
        }
        $DB = SQL::DB();
        $Res = $DB->update('app', [
            ($_QET['type'] == 1 ? 'theme' : 'load_theme') => $_QET['color'],
        ], [
            'id' => $_QET['id'],
            'uid' => $UserData['id']
        ]);
        if ($Res) {
            dies(1, ($_QET['type'] == 1 ? '主题颜色' : '加载条颜色') . '修改成功！');
        } else dies(-1, '修改失败！');
        break;
    case 'AppSet':
        if (!$UserData) dies(-1, '请先完成登陆！');
        test(['id|e', 'field|e', 'value|i'], '参数未提交完整！');
        if ($_QET['field'] === 'url') {
            $_QET['value'] = StringCargo::UrlVerify($_QET['value'], 3);
        }
        if ($_QET['field'] !== 'name' && $_QET['field'] !== 'url' && $_QET['field'] !== 'content') {
            dies(-1, '数据异常，修改失败！');
        }
        $SQL = [
            $_QET['field'] => $_QET['value']
        ];
        $DB = SQL::DB();
        $Res = $DB->update('app', $SQL, [
            'id' => $_QET['id'],
            'uid' => $UserData['id']
        ]);
        if ($Res) {
            dies(1, '调整成功！');
        } else dies(-1, '调整失败！');
        break;
    case 'AppSubmit': //提交App打包任务！
        if (!$UserData) dies(-1, '请先完成登陆！');
        App::AppSubmit($_QET['id'], $UserData['id']);
        break;
    case 'AppCalibration': //同步任务
        if (!$UserData) dies(-1, '请先完成登陆！');
        App::AppCalibration($_QET['id'], $UserData['id']);
        break;
    case 'AppDownload': //获取地址或部署
        if (!$UserData) dies(-1, '请先完成登陆！');
        test(['id|e', 'type|e'], '数据未提交完整！');
        App::AppDownload($_QET['id'], $_QET['type'], $UserData['id']);
        break;
    case 'AppDelete': //删除App
        if (!$UserData) dies(-1, '请先完成登陆！');
        test(['id|e'], '请填写完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('app', [
            'id' => $_QET['id'],
            'uid' => $UserData['id'],
        ]);
        if ($Res) {
            dies(1, 'App打包任务[' . $_QET['id'] . ']删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'QQ_QuickLogin': //QQ互联快捷登录
        QuickLogin::QQ_Internet(ROOT_DIR . 'api.php?act=qq_login');
        break;
    case 'HostCount': //主机总数
        if (!$UserData) dies(-1, '请先完成登陆！');
        $DB = SQL::DB();
        $SQL = [
            'uid' => $UserData['id'],
        ];
        $Res = $DB->count('mainframe', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'HostList': //主机列表
        if (!$UserData) dies(-1, '请先完成登陆！');
        $Data = $_QET;
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'uid' => $UserData['id'],
            'LIMIT' => [$Page, $Limit],
        ];

        $DB = SQL::DB();

        $Res = $DB->select('mainframe', [
            'id', 'RenewPrice', 'RenewalType', 'addtime',
            'concurrencyall', 'concurrencyip', 'endtime',
            'filesize', 'identification', 'maxdomain',
            'state', 'status', 'traffic', 'type'
        ], $SQL);
        if (!$Res) {
            dies(-1, ' 没有更多了');
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Res,
            'count' => $DB->count('mainframe', ['uid' => $UserData['id']]),
        ]);
        break;
    case 'LogHostBackground': //登陆主机后台
        if (!$UserData) dies(-1, '请先完成登陆！');
        test(['key|e']);
        $DB = SQL::DB();
        $Vs = $DB->get('mainframe', ['id'], [
            'identification' => (string)$_QET['key'],
            'uid' => $UserData['id']
        ]);
        if (!$Vs) {
            dies(-1, '需要登陆的主机不存在，或未绑定到您账户！');
        }

        $_SESSION[Server::$SessionName] = (string)$_QET['key'];
        dier([
            'code' => 1,
            'msg' => '登陆数据写入成功！',
            'url' => href(2) . ROOT_DIR . 'HostAdmin/index.php',
        ]);
        break;
    case 'CarmiActivation': //卡密激活
        if (!$UserData) dies(-1, '请先完成登陆！');
        test(['token']);
        $DB = SQL::DB();

        $Get = $DB->get('recharge', '*', [
            'token' => (string)$_QET['token'],
        ]);

        if (!$Get) {
            dies(-1, '卡密不存在！');
        }
        if ($Get['uid'] != -1) {
            dies(-1, '此充值卡已被使用，使用者ID：' . $Get['uid']);
        }

        $Res = $DB->update('recharge', [
            'uid' => $UserData['id'],
            'ip' => userip(),
            'endtime' => $date,
        ], [
            'id' => $Get['id'],
        ]);

        if ($Res) {
            userlog('充值卡激活', '用户' . $UserData['id'] . '于' . $date . '使用了(' . ($Get['type'] == 1 ? '余额' : $conf['currency']) . ')充值卡：' . $_QET['token'], $UserData['id'], $Get['money']);
            $DB->update('user', [
                ($Get['type'] == 1 ? 'money[+]' : 'currency[+]') => $Get['money']
            ], [
                'id' => $UserData['id'],
            ]);
            dies(1, '充值卡使用成功，面额：' . $Get['money'] . '，卡密类型：' . ($Get['type'] == 1 ? '余额' : $conf['currency']) . '充值卡！');
        } else {
            dies(-1, '卡密使用失败，请重新尝试！');
        }
        break;
    case 'HierarchicalData': //等级页面数据
        if (!$UserData) {
            dies(-2, '请先完成登陆！');
        }
        $DB = SQL::DB();

        $RankList = RatingParameters($UserData, 2);
        $Giv = RatingParameters($UserData);
        $ListRank = [];
        foreach ($RankList as $key => $val) {
            if ($Giv['sort'] <= $key) {
                $Price = $val['money'] - $Giv['money'];
                if ($Price <= 0) {
                    $Price = 0;
                }
            } else {
                $Price = 0;
            }
            if ($val['money'] <= 0) {
                $val['money'] = 0;
            }
            $ListRank[] = [
                'name' => (empty($val['name']) ? 'Lv' . ($key + 1) : $val['name']),
                'price' => $val['money'],
                'UpPrice' => $Price,
                'content' => (empty($val['content']) ? '这个等级没有相关介绍' : $val['content']),
                'state' => ($Giv['sort'] > $key),
            ];
            unset($Price);
        }
        if (empty($Giv)) {
            $Giv = [
                'name' => '平台用户',
                'mid' => $UserData['grade'],
            ];
        } else {
            $Giv = [
                'name' => $Giv['name'],
                'mid' => $Giv['sort'],
            ];
        }
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'User' => [
                'name' => (empty($UserData['name']) ? '平台用户' : $UserData['name']),
                'image' => UserImage($UserData),
                'grade' => $Giv,
                'money' => round($UserData['money'], 8),
                'currency' => $UserData['currency'],
            ],
            'Conf' => [
                'currency' => $conf['currency'],
                'userleaguegrade' => $conf['userleaguegrade'],
                'usergradenotice' => $conf['usergradenotice'],
                'usergradeprofit' => $conf['usergradeprofit'],
                'usergradegoodsstate' => $conf['usergradegoodsstate'],
                'usergradetem' => $conf['usergradetem'],
                'userdepositgrade' => $conf['userdepositgrade'],
            ],
            'RankList' => $ListRank,
        ]);
        break;
    case 'LevelUp': //等级提升
        test(['update|e']);
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        $DB = SQL::DB();
        $RankList = RatingParameters($UserData, 2);
        $update = (int)$_QET['update'];
        if ($update <= 0 || $update > count($RankList)) {
            dies(-1, '参数异常');
        }
        if ($UserData['grade'] > $update) {
            dies(-1, '您当前等级大于' . $update . '级！');
        }
        $UserGid = $UserData['grade'] - 1;
        $Giv = $RankList[$UserGid];
        $ListRank = [];
        if (empty($Giv)) {
            dies(-1, '数据异常');
        }
        foreach ($RankList as $key => $val) {
            if ($UserGid < $key) {
                $Price = $val['money'] - $Giv['money'];
            } else {
                $Price = 0;
            }
            $ListRank[] = [
                'name' => $val['name'],
                'UpPrice' => $Price,
            ];
            unset($Price);
        }
        $Data = $ListRank[$update];
        if (empty($Data)) {
            dies(-1, '数据异常');
        }
        if ($UserData['money'] < $Data['UpPrice']) {
            dies(-2, '当前账户余额不足' . $Data['UpPrice'] . '元，请先充值！');
        }
        if ($Data['UpPrice'] <= 0) {
            $Data['UpPrice'] = 0;
        }

        $UpRes = $DB->update('user', [
            'money[-]' => $Data['UpPrice'],
            'grade' => $update + 1
        ], [
            'id' => $UserData['id']
        ]);
        if ($UpRes) {
            UserConf::Commission($UserData['id'], $Data['UpPrice'], $UserData['superior'], $update + 1);
            userlog('等级提升', '您在后台消耗余额将用户等级提升为' . $Data['name'] . ',消耗金额为：' . $Data['UpPrice'] . '元！', $UserData['id'], $Data['UpPrice']);
            Hook::execute('UserLevelUp', [
                'uid' => $UserData['id'],
                'money' => $Data['UpPrice'],
                'grade' => $update + 1
            ]);
            dies(1, '恭喜，您当前花费' . $Data['UpPrice'] . '元成功将等级提升至：' . $Data['name']);
        } else {
            dies(-1, '升级失败，请重新尝试！');
        }
        break;
    case 'PayData': //钱包数据
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        $DB = SQL::DB();

        $withdrawal = $DB->get('withdrawal', '*', [
            'uid' => $UserData['id'],
            'ORDER' => [
                'addtime' => 'DESC'
            ]
        ]);

        if (!$withdrawal) {
            $withdrawal = [];
        }

        $images = 'assets/img/withdraw/' . md5($UserData['id'] . '晴玖') . '/' . $UserData['id'] . '.png';

        /**
         * 返回内容：
         * 1、支付接口开关
         * 2、余额
         * 3、积分，积分名
         * 支持提现的通道，以及当前用户是否支持提现
         */

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'User' => [ //用户数据
                'money' => round($UserData['money'], 8),
                'currency' => $UserData['currency'],
                'name' => $withdrawal['name'],
                'type' => $withdrawal['type'],
                'account_number' => $withdrawal['account_number'],
                'remarks' => $withdrawal['remarks'],
                'image' => (file_exists(ROOT . $images) ? href(2) . ROOT_DIR . $images . '?t=' . time() : false), //收款码
                'action' => href(2) . ROOT_DIR . 'user/ajax.php?act=DoGatheringFigure'
            ],
            'Conf' => [ //充值配置
                'Pay' => [ //支付接口数据
                    'alipay' => ((int)$conf['PayConZFB'] != -1),
                    'wxpay' => ((int)$conf['PayConWX'] != -1),
                    'qqpay' => ((int)$conf['PayConQQ'] != -1)
                ],
                'RsPay' => explode(',', $conf['userdeposittype']),
                'PayLog' => [
                    'a' => $DB->sum('withdrawal', 'money', ['uid' => $UserData['id'], 'state' => 3]) - 0,
                    'b' => $DB->sum('withdrawal', 'money', ['uid' => $UserData['id']]) - 0,
                    'c' => $DB->sum('withdrawal', 'money', ['uid' => $UserData['id'], 'state' => 1]) - 0
                ],
                'Rdined' => $conf['userdepositservice'], //费率
                'Minimum' => $conf['userdepositmin'], //最低提现金额
                'currency' => $conf['currency'],
                'PayUrl' => $conf['rechargepurchaseurl'], //充值卡购买地址
                'RechargeRange' => $conf['RechargeLimit'],
            ]
        ]);
        break;
    case 'SignData': //签到数据
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        $DB = SQL::DB();
        $Vs = $DB->get('journal', '*', [
            'name' => '每日签到',
            'uid' => $UserData['id'],
            'date[>=]' => $times
        ]);
        $Limit = (int)$_QET['limit'];
        $Page = ($_QET['page'] - 1) * $Limit;

        $List = $DB->select('journal', [
            'ip', 'count', 'date'
        ], [
            'uid' => $UserData['id'],
            'name' => '每日签到',
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ]);
        $Count = $DB->count('journal', [
            'uid' => $UserData['id'],
            'name' => '每日签到',
        ]);

        $State = ($conf['SignAway'] == 1 ? $conf['currency'] : '余额');

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'type' => (bool)$Vs,
                'date' => $Vs['date'],
                'list' => $List,
                'count' => $Count,
                'currency' => $State,
                'number' => $conf['GiftContent'] ?? ($State == '余额' ? '0.01-0.1' : '1-100')
            ]
        ]);
        break;
    case 'SignIn': //每日签到
        reward::welfare($UserData, 2);
        break;
    case 'ShopData': //店铺数据
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if ($conf['userleague'] != 1) dies(-1, '当前系统未开启小店权限，！');
        if ($UserData['grade'] < $conf['userleaguegrade']) dies(-1, '您当前的等级无法开通小店,请先去提升等级！，需达到' . $conf['userleaguegrade'] . '级！');
        $DB = SQL::DB();
        $count_1 = $DB->sum('journal', 'count', [
                'uid' => $UserData['id'],
                'name' => ['余额提成', '升级提成']
            ]) - 0; //累计余额收益
        $count_2 = $DB->sum('journal', 'count', [
                'uid' => $UserData['id'],
                'name' => ['余额提成', '升级提成'],
                'date[>=]' => $times
            ]) - 0; //今日余额收益
        $count_3 = $DB->sum('journal', 'count', [
                'uid' => $UserData['id'],
                'name' => ['货币提成', '每日签到', '邀请奖励'],
            ]) - 0; //累计货币收益
        $count_4 = $DB->sum('journal', 'count', [
                'uid' => $UserData['id'],
                'name' => ['货币提成', '每日签到', '邀请奖励'],
                'date[>=]' => $times
            ]) - 0; //今日货币收益
        $count_5 = $DB->count('journal', [
                'name' => ['货币提成', '余额提成'],
                'uid' => $UserData['id'],
            ]) - 0; //累计订单总数
        $count_6 = $DB->count('journal', [
                'name' => ['货币提成', '余额提成'],
                'uid' => $UserData['id'],
                'date[>=]' => $times
            ]) - 0; //今日订单总数
        $count_7 = $DB->count('user', [
                'superior' => $UserData['id']
            ]) - 0; //直系下级
        $count_8 = $DB->count('goods', [
                'state' => 1
            ]) - 0; //商品总数

        $DataSite = config::common_unserialize($UserData['configuration']);
        if (!$DataSite) {
            $DataSite = $conf;
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [ //店铺数据
                'a1' => $count_1, //累计余额收益
                'a2' => $count_2, //今日余额收益
                'b1' => $count_3, //累计货币收益
                'b2' => $count_4, //今日货币收益
                'c1' => $count_5, //累计订单总数
                'c2' => $count_6, //今日新增订单
                'd1' => $count_7, //直系下级总数
                'd2' => $count_8, //平台商品总数
            ],
            'conf' => [ //店铺配置
                'DomainType' => !empty($UserData['domain']), //是否已绑定店铺域名
                'Editor' => ($UserData['grade'] >= $conf['usergradenotice']), //小店信息编辑开关(包含公告)
                'PriceAllocation' => ($UserData['grade'] >= $conf['usergradeprofit']), //价格配置开关
                'CommodityStatus' => ($UserData['grade'] >= $conf['usergradegoodsstate']), //商品状态
                'ShopDecoration' => ($UserData['grade'] >= $conf['usergradetem']), //店铺装修
                'currency' => (empty($DataSite['currency']) ? $conf['currency'] : $DataSite['currency']),
            ],
            'user' => [ //用户数据
                'image' => UserImage($UserData),
                'name' => (empty($UserData['name']) ? '平台用户' : $UserData['name']),
                'sitename' => (empty($DataSite['sitename']) ? $conf['sitename'] : $DataSite['sitename']),
                'money' => $UserData['money'],
            ]
        ]);
        break;
    case 'DomainData': //获取域名绑定数据
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if ($conf['userleague'] != 1) dies(-1, '当前系统未开启小店权限，！');
        if ($UserData['grade'] < $conf['userleaguegrade']) dies(-1, '您当前的等级无法开通小店,请先去提升等级！，需达到' . $conf['userleaguegrade'] . '级！');


        if ($conf['userdomaintype'] == 1) {
            $Domain = [];
            if (!empty($UserData['domain'])) {
                $StrArr = explode('.', $UserData['domain']);
                $Domain[0] = $StrArr[0];
                unset($StrArr[0]);
                $Domain[1] = implode('.', $StrArr);

                if (empty($Domain[0])) {
                    $Domain[0] = '';
                }
                if (empty($Domain[1])) {
                    $Domain[1] = '';
                }
            }
        } else {
            $Domain = $UserData['domain'];
        }


        if ($conf['userdomaintype'] == 1) {
            //泛解析模式
            $Url = false;
            if (!empty($UserData['domain'])) {
                $Url = is_https(false) . $UserData['domain'];
            }
        } else {
            $Url = $UserData['domain'];
            if (empty($Domain)) {
                $Url = false;
            } else {
                $Url = href(2) . '?t=' . $Domain;
            }
        }

        if ($Url && $conf['prevent_switch'] == 1) {
            //一般变短链接
            $shareLink = reward::prevent($Url, 2);
            if ($shareLink == $Url) {
                $shareLink = false;
            }
        } else {
            $shareLink = false;
        }

        dier([
            'code' => 1,
            'msg' => '域名数据获取成功',
            'data' => [
                'Money' => round($UserData['money'], 2),
                'Price' => $conf['userdomainsetmoney'], //修改价格
                'Type' => $conf['userdomaintype'], //泛解析模式，Cookie绑定模式
                'DomainList' => explode(',', $conf['userdomain']), //可选域名
                'Domain' => (empty($Domain) ? false : $Domain), //站点绑定的域名前缀或后缀
                'Blacklist' => explode(',', $conf['userdomainretain']), //前缀或后缀的黑名单
                'url' => href(2),
                'ssl' => is_https(false),
                'f_url' => $shareLink,
            ]
        ]);
        break;
    case 'StoreConf': //店铺配置信息
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if ($conf['userleague'] != 1) dies(-1, '当前系统未开启小店权限，！');
        if ($UserData['grade'] < $conf['userleaguegrade']) dies(-1, '您当前的等级无法开通小店,请先去提升等级！，需达到' . $conf['userleaguegrade'] . '级！');
        if ($UserData['grade'] < $conf['usergradenotice']) dies(-1, '您当前等级无法配置店铺基础信息,请先去提升等级吧！');

        $DataConf = config::common_unserialize($UserData['configuration']);
        if ($DataConf) {
            $DataConf = array_merge($conf, $DataConf);
        } else {
            $DataConf = $conf;
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'sitename' => $DataConf['sitename'],
                'keywords' => $DataConf['keywords'],
                'description' => $DataConf['description'],
                'kfqq' => $DataConf['kfqq'],
                'ServiceTips' => $DataConf['ServiceTips'],
                'CartState' => $DataConf['CartState'],
                'ServiceImage' => $DataConf['ServiceImage'],
                'Communication' => $DataConf['Communication'],
                'award' => $DataConf['award'],
                'currency' => $DataConf['currency'],
                'appurl' => $DataConf['appurl'],
                'ForcedLanding' => $DataConf['ForcedLanding'],
                'DynamicMessage' => $DataConf['DynamicMessage'],
                'GoodsRecommendation' => $DataConf['GoodsRecommendation'],
                'YzfSign' => $DataConf['YzfSign'],
                'SimilarRecommend' => $DataConf['SimilarRecommend']
            ],
            'conf' => [
                'award' => $conf['award'], //邀请奖励
            ]
        ]);
        break;
    case 'StoreInform': //店铺公告
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if ($conf['userleague'] != 1) dies(-1, '当前系统未开启小店权限，！');
        if ($UserData['grade'] < $conf['userleaguegrade']) dies(-1, '您当前的等级无法开通小店,请先去提升等级！，需达到' . $conf['userleaguegrade'] . '级！');
        if ($UserData['grade'] < $conf['usergradenotice']) dies(-1, '您当前等级无法配置店铺基础信息,请先去提升等级吧！');

        $DataConf = config::common_unserialize($UserData['configuration']);
        $Data = ['notice_top', 'notice_check', 'notice_bottom', 'PopupNotice'];
        $Array = [];

        foreach ($Data as $val) {
            if (!$DataConf || !$DataConf[$val] || $DataConf[$val] === null) {
                $Array[$val] = $conf[$val];
            } else {
                $Array[$val] = base64_decode($DataConf[$val], TRUE);
            }
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Array
        ]);
        break;
    case 'TemplateSettings': //站点装修
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        if ($conf['userleague'] != 1) dies(-1, '当前系统未开启小店权限，！');
        if ($UserData['grade'] < $conf['userleaguegrade']) dies(-1, '您当前的等级无法开通小店,请先去提升等级！，需达到' . $conf['userleaguegrade'] . '级！');
        if ($UserData['grade'] < $conf['usergradetem']) dies(-1, '您当前等级无法装修店铺,请先去提升等级吧！');
        $TemArr = for_dir(ROOT . 'template/');

        $Data = [
            'PC' => [],
            'M' => [],
        ];
        $Data['PC'][] = [
            'name' => '关闭模板',
            'image' => ImageUrl('/assets/img/close.png'),
            'index' => -1,
        ];
        $Data['PC'][] = [
            'name' => '套娃模式',
            'image' => ImageUrl('/assets/img/matryoshka.png'),
            'index' => -2,
        ];
        $Data['M'][] = [
            'name' => '关闭模板',
            'image' => ImageUrl('/assets/img/close.png'),
            'index' => -1,
        ];
        foreach ($TemArr as $value) {
            $file_path = ROOT . 'template/' . $value . '/conf.json';
            if (!file_exists($file_path)) {
                $Data['PC'][] = [
                    'name' => $value,
                    'image' => ImageUrl('/template/' . $value . '/index.png'),
                    'index' => $value,
                ];
                $Data['M'][] = [
                    'name' => $value,
                    'image' => ImageUrl('/template/' . $value . '/index.png'),
                    'index' => $value,
                ];
            } else {
                $Json = json_decode(file_get_contents($file_path), true);
                if (!empty($Json)) {
                    if ((int)$Json['type'] === 1) {
                        $Data['PC'][] = [
                            'name' => $Json['name'],
                            'image' => ImageUrl('/template/' . $value . '/index.png'),
                            'index' => $value,
                        ];
                    } else if ((int)$Json['type'] === 2) {
                        $Data['M'][] = [
                            'name' => $Json['name'],
                            'image' => ImageUrl('/template/' . $value . '/index.png'),
                            'index' => $value,
                        ];
                    } else {
                        $Data['PC'][] = [
                            'name' => $Json['name'],
                            'image' => ImageUrl('/template/' . $value . '/index.png'),
                            'index' => $value,
                        ];
                        $Data['M'][] = [
                            'name' => $Json['name'],
                            'image' => ImageUrl('/template/' . $value . '/index.png'),
                            'index' => $value,
                        ];
                    }
                } else {
                    $Data['PC'][] = [
                        'name' => $value,
                        'image' => ImageUrl('/template/' . $value . '/index.png'),
                        'index' => $value,
                    ];
                    $Data['M'][] = [
                        'name' => $value,
                        'image' => ImageUrl('/template/' . $value . '/index.png'),
                        'index' => $value,
                    ];
                }
            }
        }

        $DataConf = config::common_unserialize($UserData['configuration']);
        if ($DataConf) {
            $conf = array_merge($conf, $DataConf);
        }

        if ($conf['userdomaintype'] == 1) {
            //泛解析模式
            $Domain = false;
            if (!empty($UserData['domain'])) {
                $Domain = is_https(false) . $UserData['domain'];
            }
        } else {
            $Domain = $UserData['domain'];
            if (empty($Domain)) {
                $Domain = false;
            } else {
                $Domain = href(2) . '?t=' . $Domain;
            }
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
            'conf' => [
                'template' => $conf['template'],
                'template_m' => $conf['template_m'],
                'background' => $conf['background'],
                'banner' => $conf['banner'],
                'action' => href(2) . ROOT_DIR . 'user/ajax.php?act=image_up'
            ],
            'domain' => $Domain
        ]);
        break;
    case 'InviteData': //邀请详情
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }

        $Url = 's=' . md5($UserData['user_idu']) . '&id=' . $UserData['id'];

        if ($conf['userdomaintype'] == 1) {
            //泛解析模式
            $Domain = false;
            if (!empty($UserData['domain'])) {
                $Domain = is_https(false) . $UserData['domain'] . ROOT_DIR_S . '/?' . $Url;
            }
        } else {
            $Domain = $UserData['domain'];
            if (empty($Domain)) {
                $Domain = false;
            } else {
                $Domain = href(2) . '?t=' . $Domain . '&' . $Url;
            }
        }

        if (!$Domain) {
            $Url = href(2) . ROOT_DIR_S . '/?' . $Url;
        } else {
            $Url = $Domain;
        }

        if ($conf['prevent_switch'] == 1) {
            //一般变短链接
            $shareLink = reward::prevent($Url, 2);
            if ($shareLink != $Url) {
                $Type = 1;
            }
        }
        $DB = SQL::DB();
        $count_1 = $DB->count('invite', [
            'uid' => $UserData['id'],
        ]);
        $count_2 = $DB->count('invite', [
            'uid' => $UserData['id'],
            'award' => 0,
        ]);
        $rand = random_int(1, 13);
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'Url' => $Url,
                'a1' => $count_1,
                'a2' => $count_2,
                'award' => $conf['award'],
                'image' => href(2) . ROOT_DIR . 'user/image/api.php?url=' . base64_encode($Url) . '&type=' . $Type . '&ids=' . $rand . '&images=uid' . $UserData['id'] . '_' . $rand . '.jpg',
                'List' => reward::Invite_statistics($UserData, $_QET['page'], $_QET['limit']),
                'count' => $count_1,
                'currency' => $conf['currency']
            ],
        ]);
        break;
    case 'ReceiveAward': //领取奖励
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        test(['id|e']);
        reward::issue_reward($UserData, $_QET['id'], 2);
        break;
    case 'UserGet': //用户信息
        if (!$UserData) {
            dies(-1, '请先完成登陆！');
        }
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'name' => $UserData['name'],
                'qq' => $UserData['qq'],
                'mobile' => $UserData['mobile'],
                'username' => $UserData['username'],
                'password' => empty($UserData['password']),
                'id' => $UserData['id'],
                'token' => (empty($UserData['token']) ? false : $UserData['token']),
                'IPList' => explode('|', $UserData['ip_white_list']),
                'url' => href(2)
            ]
        ]);
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        dies(-2, '访问路径不存在！');
        break;
}
