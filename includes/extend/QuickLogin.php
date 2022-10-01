<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/10 17:03
// +----------------------------------------------------------------------
// | Filename: QuickLogin.php
// +----------------------------------------------------------------------
// | Explain: 快捷登录操作类，QQ,WX,短信,GitHub
// +----------------------------------------------------------------------

namespace extend;


use lib\Hook\Hook;
use login_data;
use Medoo\DB\SQL;
use query;

class QuickLogin
{
    /**
     * QQ互联快捷登录
     */
    public static function QQ_Internet($Route = '/api.php?act=qq_login')
    {
        global $conf;
        header('Content-Type: text/html; charset=UTF-8');

        switch ($conf['QQInternetChoice']) {
            case 1: //官方内置
                self::QQ_LoginRequest($Route);
                break;
            case 2: //自定义通道
                self::QQ_CustomLoginRequest($Route);
                break;
            default:
                show_msg('抱歉，当前站点未开启QQ快捷登录方式！');
        }
    }

    /**
     * @param $code
     * @param $Data
     * 快捷登录回调分发
     */
    public static function CallbackDistribution($Data)
    {
        global $conf;

        if (empty($Data['state'])) {
            show_msg('温馨提示', '参数缺失，请重新提交！', '3', ROOT_DIR_S . '/?mod=route&p=User');
        }
        $CallbackData = json_decode(xiaochu_de($Data['state'], $conf['secret']), true);
        switch ($CallbackData['type']) {
            case 'QQ': //QQ快捷登录分发
                self::QQ_QuickLogin($Data['code'], $CallbackData);
                break;
            default:
                show_msg('温馨提示', '请求快捷登陆方式不存在，请重新提交！', '3', ROOT_DIR_S . '/?mod=route&p=User');
                break;
        }
    }

    /**
     * @param $code
     * @param $Data
     * QQ快捷登录分发
     */
    public static function QQ_QuickLogin($code, $Data)
    {
        global $conf;
        if (empty($code) || empty($Data) || empty($Data['type']) || empty($Data['CallbackUrl']) || empty($Data['Url']) || empty($Data['Pattern'])) {
            show_msg('温馨提示', '登陆参数缺失，请重新尝试！(1000)', '3', ROOT_DIR_S . '/?mod=route&p=User');
        }

        $ApiData_1 = get_curl('https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=' . $conf['QQInternetID'] . '&client_secret=' . $conf['QQInternetKey'] . '&code=' . $code . '&redirect_uri=' . $conf['QQInternetCallback'] . '&fmt=json');
        $ApiData_1 = json_decode($ApiData_1, true);
        $access_token = $ApiData_1['access_token'];
        $refresh_token = $ApiData_1['refresh_token'];

        if (empty($access_token) || empty($refresh_token)) {
            show_msg('温馨提示', '参数缺失，请重新尝试！(1001)', '3', $Data['Url'] . ROOT_DIR_S . '/?mod=route&p=User');
        }

        $ApiData_2 = get_curl('https://graph.qq.com/oauth2.0/me?access_token=' . $access_token . '&unionid=1&fmt=json');
        $openid = json_decode($ApiData_2, true);
        if (empty($openid)) {
            show_msg('温馨提示', '参数缺失，请重新尝试！(1002)', '3', $Data['Url'] . ROOT_DIR_S . '/?mod=route&p=User');
        }
        $ApiData_3 = get_curl('https://graph.qq.com/user/get_user_info?access_token=' . $access_token . '&oauth_consumer_key=' . $openid['client_id'] . '&openid=' . $openid['openid']);
        $ApiData_3 = json_decode($ApiData_3, true);

        if (empty($ApiData_3) || empty($ApiData_3['figureurl_2'])) {
            show_msg('温馨提示', '参数缺失，请重新尝试！(1003)', '3', $Data['Url'] . ROOT_DIR_S . '/?mod=route&p=User');
        }

        if (empty($openid['unionid'])) {
            show_msg('温馨提示', '此接口未获取 unionid(平台统一ID信息)，无法生成数据,请联系客服处理！(1004)', '3', $Data['Url'] . ROOT_DIR_S . '/?mod=route&p=User');
        }

        $Token = md5($openid['unionid'] . '晴玖天网络科技有限公司'); //此处文字不可修改，否则将无法登陆

        $Base64 = base64_encode(json_encode([
            'code' => 1,
            'msg' => '登陆数据获取成功',
            'type' => 1,
            'user_idu' => $Token,
            'data' => $ApiData_3,
            'date' => time(),
        ]));

        if ($Data['Url'] == href(2)) {
            //本地模式
            if ((int)$Data['Pattern'] === 1) {
                self::QQ_LoginCallback(['data' => $Base64]);
            } else {
                self::QQ_RebindingQuickLogin(['data' => $Base64]);
            }
        }

        //分店模式
        header("Location:" . $Data['CallbackUrl'] . "&data=" . $Base64);
        die('跳转中,请稍后...');
    }

    /**
     * @param $suffix
     * 自定义QQ快捷登录通道
     */
    public static function QQ_CustomLoginRequest($suffix)
    {
        global $conf, $accredit;
        $suffix = xiaochu_en(json_encode([
            'CallbackUrl' => href(2) . $suffix,
            'Url' => href(2),
            'type' => 'QQ',
            'Pattern' => ($suffix === ROOT_DIR . 'api.php?act=qq_login' ? 1 : 2),
        ]), $conf['secret']);
        $redirect_uri = urlencode($conf['QQInternetCallback']);
        $url = 'https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=' . $conf['QQInternetID'] . '&redirect_uri=' . $redirect_uri . '&state=' . $suffix . '&scope=get_user_info,list_album,upload_pic,do_like&display=';
        setcookie("PHPTOKEN", md5($accredit['token'] . 'login' . userip()), time() + 3600, '/');
        header("Location: $url");
    }

    /**
     * @param $suffix
     * 内置快捷登录通道
     */
    public static function QQ_LoginRequest($suffix)
    {
        global $accredit;
        $url = href() . $suffix;
        $url = xiaochu_de('axHiRa0occDhouvyLu2nNkbi43OXRpYW4uY29tL3FxYXBpL2FwaS5waHAoo00oYWN0PXNwb25zb3ImdXJsPQO0O0OO0O0O') . $url;
        header('Location: ' . $url);
    }

    /**
     * @param $Data
     * 重新绑定QQ互联
     */
    public static function QQ_RebindingQuickLogin($Data)
    {
        global $date, $UserData;

        if (empty($UserData)) {
            $UserData = login_data::user_data();
        }

        if (empty($UserData['user_idu'])) {
            show_msg('温馨提示', '请先完成登陆！', '3', ROOT_DIR_S . '/?mod=route&p=User');
        }

        $DB = SQL::DB();
        if (empty($Data['data'])) {
            show_msg('数据异常', '检测到数据异常,请重新发起验证', '3', ROOT_DIR_S . '/?mod=route&p=User');
        }

        $Data = base64_decode($Data['data']);
        $Data = json_decode($Data, true);

        if ((int)$Data['code'] !== 1) {
            show_msg('温馨提示', $Data['msg'] . '=>' . $Data['error'], '3', ROOT_DIR_S . '/?mod=route&p=User');
        }

        $UserAuth = $DB->get('user', ['id'], [
            'user_idu' => (string)$Data['user_idu'],
            'id[!]' => (int)$UserData['id']
        ]);

        if ($UserAuth) {
            show_msg('警告', '您当前新绑定的QQ账号 [ <img src="' . $Data['data']['figureurl_2'] . '" width="20" /> ' . $Data['data']['nickname'] . ' ] 已经被用户：' . $UserAuth['id'] . ' 绑定过了', '3', ROOT_DIR_S . '/?mod=route&p=User');
        }

        if ($UserData['user_idu'] === $Data['user_idu']) {
            show_msg('温馨提示', '新绑定的QQ和旧的一致，无需修改！', '3', ROOT_DIR_S . '/?mod=route&p=User');
        }

        if (empty($Data['user_idu'])) {
            show_msg('温馨提示', '绑定验证信息获取失败，请重新尝试！', '3', ROOT_DIR_S . '/?mod=route&p=User');
        }

        $Res = $DB->update('user', [
            'user_idu' => $Data['user_idu'],
            'image' => $Data['data']['figureurl_2'],
            'name' => $Data['data']['nickname'],
        ], [
            'id' => $UserData['id'],
            'user_idu' => $UserData['user_idu']
        ]);
        if ($Res) {
            userlog('QQ换绑', '用户于' . $date . '将QQ登陆账号修改为：' . $Data['user_idu'], $UserData['id']);
            setcookie("THEKEY", $Data['user_idu'], time() + 3600 * 12 * 15, '/');
            show_msg('温馨提示', '您的账户绑定QQ已经修改为 [ <img src="' . $Data['data']['figureurl_2'] . '" width="20" /> ' . $Data['data']['nickname'] . ' ] <br>以后用新绑定的QQ账号登陆即可！', '1', ROOT_DIR_S . '/?mod=route&p=User');
        } else {
            show_msg('警告', '绑定失败,请重新尝试！', '3', ROOT_DIR_S . '/?mod=route&p=User');
        }
    }

    /**
     * @param $Data
     * 登录回调(通用接口)
     */
    public static function QQ_LoginCallback($Data)
    {
        global $date, $conf, $accredit;
        $DB = SQL::DB();
        $IP = userip();
        if (empty($Data['data'])) {
            show_msg('数据异常', '检测到数据异常,请重新登陆', '4', ROOT_DIR_S . '/?mod=route&p=User');
        }
        $Data = base64_decode($Data['data']);
        $Data = json_decode($Data, true);
        $InvitationStatus = false;

        //此处用于替换QQ头像链接，防止在开启了SSL后无法正常显示！
        $Data['data']['figureurl_2'] = str_replace('http://', 'https://', $Data['data']['figureurl_2']);

        if ((int)$Data['code'] !== 1) {
            show_msg('温馨提示', $Data['msg'] . '=>' . $Data['error'], '4', ROOT_DIR_S . '/?mod=route&p=User');
        }

        $UserAuth = $DB->get('user', ['id'], [
            'user_idu' => (string)$Data['user_idu'],
        ]);
        if ($UserAuth) {
            userlog('用户登陆', '用户登陆了后台', $UserAuth['id']);
            $UserUpdate = $DB->update('user', [
                'ip' => $IP,
                'image' => $Data['data']['figureurl_2'],
                'name' => $Data['data']['nickname']
            ], [
                'user_idu' => $Data['user_idu']
            ]);
            $ExName = 'UserLogin';
        } else {
            if ((int)$conf['userregister'] !== 1) {
                show_msg('温馨提示', '当前站点未开放注册！', '4', ROOT_DIR_S . '/?mod=route&p=User');
            }
            if ($conf['inItRegister'] == 1 && empty($_COOKIE['INVITED_STATUS'])) {
                show_msg('温馨提示', '当前站点开启了邀请注册功能，您只能够通过已注册用户的邀请链接才可以注册为平台用户！', '4', ROOT_DIR_S . '/?mod=route&p=User');
            }
            //查询是否是在加盟站点注册，获取上级id
            $UserSuperior = UserConf::judge();
            if ($UserSuperior === false) {
                $UserSuperior = 0;
            } else {
                $UserSuperior = $UserSuperior['id'];
            }
            if (!empty($_COOKIE['INVITED_STATUS'])) {
                //如果是通过邀请链接注册的
                $UserAuth = $DB->get('user', ['id'], [
                    'id' => (int)$_COOKIE['INVITED_STATUS'],
                ]);
                if ($UserAuth) {
                    $InvitationStatus = true;
                } else {
                    setcookie("INVITED_STATUS", null, time() - 3600, '/');
                }
            }
            $UserUpdate = $DB->insert('user', [
                'grade' => $conf['userdefaultgrade'],
                'user_idu' => $Data['user_idu'],
                'superior' => $UserSuperior,
                'currency' => 0,
                'ip' => $IP,
                'image' => $Data['data']['figureurl_2'],
                'name' => $Data['data']['nickname'],
                'state' => 1,
                'recent_time' => $date,
                'found_date' => $date
            ]);
            $ExName = 'UserRegister';
        }
        if ($UserUpdate) {
            $User = $DB->get('user', ['id', 'name'], [
                'user_idu' => (string)$Data['user_idu'],
                'state' => 1
            ]);
            if ($InvitationStatus === true) {
                //邀请奖励
                $InvitationUser = $DB->get('invite', ['id'], [
                    'ip' => (string)$IP
                ]);
                if (!$InvitationUser) {
                    userlog('邀请奖励', '恭喜您成功邀请到用户[' . $Data['data']['nickname'] . ']特奖励您' . $conf['award'] . $conf['currency'] . '！,再接再厉哦', $_COOKIE['INVITED_STATUS'], $conf['award']);
                    $DB->insert('invite', [
                        'uid' => $_COOKIE['INVITED_STATUS'],
                        'invitee' => $User['id'],
                        'award' => $conf['award'],
                        'ip' => $IP,
                        'creation_time' => $date
                    ]);
                    Hook::execute('UserInvite', [
                        'id' => $_COOKIE['INVITED_STATUS'],
                        'yid' => $User['id'],
                        'num' => $conf['award']
                    ]);
                } else {
                    userlog('失败邀请', '系统判断您的邀请对象：[' . $Data['data']['nickname'] . ']已经在其他账号接收过邀请,无法奖励！,请邀请真实用户！', $_COOKIE['INVITED_STATUS']);
                }
            }
            if ($User) {
                query::OrderUser($User['id']);
                GoodsCart::UserCookieDer($User['id']);
            }
            setcookie("THEKEY", $Data['user_idu'], time() + 3600 * 12 * 15, '/');
            $url = href(2) . ROOT_DIR_S . '/?mod=route&p=User';

            Hook::execute($ExName, [
                'name' => $User['name'],
                'id' => $User['id']
            ]);
            header("Location:$url");
            die('页面跳转中...');
        }

        show_msg('温馨提示', '登陆失败,请重新登陆', '4', ROOT_DIR_S . '/?mod=route&p=User');
    }
}