<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2022/1/14 9:17
// +----------------------------------------------------------------------
// | Filename: SMS.php
// +----------------------------------------------------------------------
// | Explain: 短信操作类
// +----------------------------------------------------------------------
namespace extend;

use Curl\Curl;
use Exception;
use lib\Guard\Guard;
use lib\Hook\Hook;
use Medoo\DB\SQL;
use query;

/**
 * 短信操作类
 */
class SMS
{

    /**
     * @var int 发信类型 1验证码，2工单，3订单
     */
    public static $Type = 1;

    /**
     * 获取服务端短信数据
     */
    public static function SmsData()
    {
        $Data = Curl::curl(false, ['act' => 11], true, 1, 2);
        if (empty($Data) || empty($Data['code'])) {
            dies(-1, '短信数据获取失败！');
        }
        dier($Data);
    }

    /**
     * @param $phone
     * 修改手机号
     */
    public static function SmsUserSet($phone)
    {
        $Data = Curl::curl(false, ['act' => 12, 'Mobile' => $phone], true, 1, 2);
        if (empty($Data) || empty($Data['code'])) {
            dies(-1, '修改失败,数据获取异常！');
        } else if ((int)$Data['code'] === 1) {
            return true;
        }
        dier($Data);
    }

    /**
     * @param $phone
     * 手机号登陆
     */
    public static function SmsAdminLogin($phone)
    {
        $Data = Curl::curl(false, ['act' => 13, 'mobile' => $phone, 'type' => 1], true, 1, 2);
        if (empty($Data) || empty($Data['code'])) {
            dies(-1, '短信发送失败，请使用其它方式登陆！');
        }
        dier($Data);
    }

    /**
     * @param $Code
     * 验证短信是否正确！
     */
    public static function SmsAdminVerify($Code)
    {
        global $date;
        $Data = Curl::curl(false, ['act' => 14, 'code' => $Code], true, 1, 2);

        if (empty($Data) || empty($Data['code'])) {
            dies(-1, '数据获取失败，请重新测试！');
        }

        if ($Data['code'] >= 0) {
            $DB = SQL::DB();
            $DB->insert('login', [
                'token' => $Data['token'],
                'ip' => userip(),
                'state' => 1,
                'finish_time' => $date,
                'date_created' => $date,
            ]);
            Guard::DataSync();
            $_SESSION['ADMIN_TOKEN'] = $Data['token'];
            dier(['code' => 1, 'msg' => $Data['msg']]);
        }
        dier($Data);
    }

    /**
     * @param $code
     * @param $phone
     * 发送短信
     */
    public static function SmsSend($code = '', $phone = '')
    {
        global $conf;
        //SmsSendAliyun

        if ((int)$conf['SMSChannelConfiguration'] === 2) {
            //阿里云
            $data = self::SmsSendAliyun($phone, $code);
            if ($data['Code'] == 'OK') {
                return [
                    'code' => 1, '验证码发送成功！'
                ];
            }
            return [
                'code' => -1, '验证码发送失败：' . $data['Message']
            ];
        }
        $DataPost = [
            'act' => 'send',
            'code' => $code,
            'mobile' => $phone,
            'type' => self::$Type,
        ];
        return Curl::curl(false, $DataPost, true, '/SMS/index', 2);
    }

    /**
     * @param $code
     * 用户后台登陆验证
     */
    public static function UserLoginVerify($code = '')
    {
        global $date, $conf;
        if (empty($_SESSION['VerifyCodeLogin']) || empty($_SESSION['MobileLogin'])) {
            dies(-2, '请先发送验证码！');
        }
        if (empty($code)) {
            dies(-1, '请填写完整！');
        }
        if ($_SESSION['VerifyCodeLogin'] != $code) {
            dies(-1, '验证码有误,请核对后再输入！');
        }
        $DB = SQL::DB();
        $Res = $DB->get('user', '*', [
            'mobile' => (string)$_SESSION['MobileLogin'],
        ]);
        if ($Res) {
            userlog('后台登陆', '用户于' . $date . '通过手机号成功登陆后台！', $Res['id']);
            setcookie('THEKEY', $Res['user_idu'], time() + 3600 * 12 * 30, ROOT_DIR);
            query::OrderUser($Res['id']);
            Hook::execute('UserLogin', [
                'name' => $Res['name'],
                'id' => $Res['id']
            ]);
            GoodsCart::UserCookieDer($Res['id']);
            dies(1, '短信验证通过,登录成功！');
        } else {
            if ((int)$conf['userregister'] !== 1) {
                dies(-1, '当前站点未开放注册');
            }
            if ($conf['inItRegister'] == 1 && empty($_COOKIE['INVITED_STATUS'])) {
                dies(-1, '当前站点开启了邀请注册功能，您只能够通过已注册用户的邀请链接才可以注册为平台用户！');
            }
            $uid_md = md5($_SESSION['MobileLogin'] . href());
            $Uty = UserConf::judge();
            if (!$Uty) {
                $Uty = 0;
            } else {
                $Uty = $Uty['id'];
            }

            $SQL = [
                'mobile' => $_SESSION['MobileLogin'],
                'grade' => $conf['userdefaultgrade'],
                'user_idu' => $uid_md,
                'superior' => $Uty,
                'currency' => 0,
                'ip' => userip(),
                'image' => ImageUrl($conf['logo']),
                'name' => '平台用户',
                'state' => 1,
                'recent_time' => $date,
                'found_date' => $date
            ];

            $invite = -1;
            if (!empty($_COOKIE['INVITED_STATUS'])) {
                $InvId = $DB->get('user', ['id'], ['id' => (int)$_COOKIE['INVITED_STATUS'], 'LIMIT' => 1]);
                setcookie('INVITED_STATUS', null, time() - 1, ROOT_DIR);
                if ($InvId) {
                    $invite = $InvId['id'];
                }
            }

            $Res = $DB->insert('user', $SQL);
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
                        userlog('邀请奖励', '恭喜您成功邀请到用户[' . $_SESSION['MobileLogin'] . ']特奖励您' . $award . $conf['currency'] . '！,再接再厉哦', $invite, $award);
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
                        userlog('失败邀请', '系统判断您的邀请对象：[' . $_SESSION['MobileLogin'] . ']已经在其他账号接收过邀请,无法奖励！,请邀请真实用户！', $_COOKIE['INVITED_STATUS'], 0);
                    }
                }
                if ($GETID) {
                    query::OrderUser($ID);
                    GoodsCart::UserCookieDer($ID);
                }
                setcookie('THEKEY', $uid_md, time() + 3600 * 12 * 30, ROOT_DIR);
                Hook::execute('UserRegister', [
                    'id' => $GETID['id'],
                    'name' => $GETID['name']
                ]);
                dies(1, '恭喜你,注册成功，欢迎入驻本平台！');
            } else {
                dies(1, '平台账号创建失败,无法完成登陆,请使用其他方式登录！');
            }
        }
    }

    /**
     * @param $phone
     * @param $num
     * 发送用户登陆短信验证码
     */
    public static function UserLogin($phone = '', $num = 3)
    {
        global $times, $conf;
        if ((int)$conf['sms_switch_user'] !== 1) {
            dies(-1, '当前站点未开启短信登陆/注册方式,可使用下方QQ互联登陆/注册！');
        }

        $IP = userip();
        if (empty($phone)) {
            dies(-1, '请将手机号填写完整！');
        }
        $DB = SQL::DB();
        $count = $DB->count('journal', [
            'name' => '短信验证',
            'date[>]' => $times,
            'ip' => $IP
        ]);
        if ($count >= $num) {
            dies(-1, '今日短信登陆次数已经耗尽，每日只可发送' . $num . '次短信验证码登陆！');
        }

        $_SESSION['VerifyCodeLogin'] = self::randString();
        $_SESSION['MobileLogin'] = $phone;

        $Res = self::SmsSend($_SESSION['VerifyCodeLogin'], $_SESSION['MobileLogin']);
        if ((int)$Res['code'] === 1) {
            userlog('短信验证', $IP, '-1');
        }
        dier($Res);
    }

    /**
     * @param $order
     * @param $name
     * @return bool
     * 发送订单邮件通知
     */
    public static function OrderEmailTips($order, $name)
    {
        global $conf;
        if ((int)$conf['weix_notice'] === -1) {
            return false;
        }
        $DB = SQL::DB();
        $Res = $DB->get('order', '*', [
            'order' => (string)$order
        ]);
        if (!$Res) {
            return false;
        }
        $data = [
            'act' => 15,
            'order' => $order,
            'price' => $Res['price'],
            'return' => ($Res['return'] === '' ? '未知下单返回' : $Res['return']),
            'uid' => $Res['uid'],
            'date' => $Res['addtitm'],
            'balance' => $Res['user_rmb'],
            'state' => $Res['state'],
            'money' => $Res['money'],
            'name' => $name,
            'payment' => $Res['payment'],
        ];
        Curl::curl(false, $data, true, 1, 2);
        return true;
    }

    /**
     * @param $uid
     * @param $order
     * 发送新订单通知【短信提醒】
     * @return bool
     */
    public static function OrderTips($uid, $order)
    {
        $DB = SQL::DB();
        $UID = $DB->get('user', [
            'mobile'
        ], [
            'id' => (int)$uid,
            'state' => 1
        ]);
        if (!$UID || empty($UID['mobile'])) {
            return false;
        }
        $order = $DB->get('order', ['id'], ['order' => (string)$order]);
        if (!$order) {
            return false;
        }
        $arr_post = [
            'act' => 'send',
            'code' => $order['id'],
            'mobile' => $UID['mobile'],
            'type' => 3,
        ];
        Curl::curl(false, $arr_post, true, '/SMS/index', 2);
        return true;
    }


    /**
     * @param $PhoneNumbers //手机号
     * @param $TemplateParam //验证码
     * 阿里云发送短信
     */
    public static function SmsSendAliyun($PhoneNumbers, $TemplateParam)
    {
        global $conf;
        $params = [];
        $params['SignName'] = $conf['SMSSignName'];
        $accessKeyId = $conf['SMSAccessKeyId'];
        $accessKeySecret = $conf['SMSAccessKeySecret'];
        $params['TemplateCode'] = $conf['SMSTemplateCode'];
        // *** 需用户填写部分 ***
        // fixme 必填：是否启用https
        $security = false;
        // fixme 必填: 短信接收号码
        $params['PhoneNumbers'] = $PhoneNumbers;
        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = [
            'code' => $TemplateParam,
        ];
        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = '';
        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = '';
        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params['TemplateParam']) && is_array($params['TemplateParam'])) {
            $params['TemplateParam'] = json_encode($params['TemplateParam'], JSON_UNESCAPED_UNICODE);
        }

        $content = self::AliyRequest(
            $accessKeyId,
            $accessKeySecret,
            'dysmsapi.aliyuncs.com',
            array_merge($params, [
                'RegionId' => 'cn-hangzhou',
                'Action' => 'SendSms',
                'Version' => '2017-05-25',
            ]),
            $security
        );

        return $content;
    }

    /**
     * 产生随机数串
     * @param int $len 随机数字长度
     * @return string
     */
    public static function randString($len = 6)
    {
        $chars = str_repeat('123456789', 3);
        $chars = str_repeat($chars, $len);
        $chars = str_shuffle($chars);
        return substr($chars, 0, $len);
    }

    /**
     * 生成签名并发起请求
     * 阿里云
     * @param $accessKeyId string AccessKeyId (https://ak-console.aliyun.com/)
     * @param $accessKeySecret string AccessKeySecret
     * @param $domain string API接口所在域名
     * @param $params array API具体参数
     * @param $security bool 使用https
     * @param $method bool 使用GET或POST方法请求，VPC仅支持POST
     * @return bool|stdClass 返回API接口调用结果，当发生错误时返回false
     */
    public static function AliyRequest($accessKeyId, $accessKeySecret, $domain, $params, $security = false, $method = 'POST')
    {
        $apiParams = array_merge([
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => uniqid(mt_rand(0, 0xffff), true),
            'SignatureVersion' => '1.0',
            'AccessKeyId' => $accessKeyId,
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'Format' => 'JSON',
        ], $params);
        ksort($apiParams);
        $sortedQueryStringTmp = '';
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= '&' . self::encode($key) . '=' . self::encode($value);
        }
        $stringToSign = "${method}&%2F&" . self::encode(substr($sortedQueryStringTmp, 1));
        $sign = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        $signature = self::encode($sign);
        $url = ($security ? 'https' : 'http') . "://{$domain}/";
        try {
            $content = self::fetchContent($url, $method, "Signature={$signature}{$sortedQueryStringTmp}");
            return json_decode($content, TRUE);
        } catch (Exception $e) {
            return false;
        }
    }

    private static function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    private static function fetchContent($url, $method, $body)
    {
        $ch = curl_init();
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            $url .= '?' . $body;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'x-sdk-client' => 'php/2.0.0'
        ));
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $rtn = curl_exec($ch);
        if ($rtn === false) {
            trigger_error('[CURL_' . curl_errno($ch) . ']: ' . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);
        return $rtn;
    }

}
