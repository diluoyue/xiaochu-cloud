<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/13 18:06
// +----------------------------------------------------------------------
// | Filename: Server.php
// +----------------------------------------------------------------------
// | Explain: BT服务器操作类
// +----------------------------------------------------------------------

namespace Server;

use BT\Config as BTC;
use BT\Construct as BTCO;
use lib\Pay\Pay;
use Medoo\DB\SQL;

class Server
{
    /**
     * @var string
     * 登陆状态Session名称
     */
    public static $SessionName = 'HostToken';

    /**
     * @param int $price
     * @param string $type
     * @param $name
     * @param int $day
     * @param $MainframeData
     * 主机在线付款续期！
     */
    public static function HostPay($price = 0, $type = 'alipay', $name, $day = 30, $MainframeData)
    {
        $Res = Pay::PrepaidPhoneOrders([
            'type' => $type,
            'uid' => $MainframeData['uid'],
            'gid' => -3,
            'input' => ['id' => $MainframeData['id']],
            'num' => $day
        ], [
            'name' => $name,
            'price' => $price
        ]);
        dier($Res);
    }

    /**
     * @param $Data
     * 测试服务器是否可以打开
     */
    public static function Test($Data)
    {
        BTC::Conf($Data);
        $Get = BTCO::Get('/system?action=GetNetWork', []);
        if (isset($Get['status']) && $Get['status'] === false) {
            dies(-1, (empty($Get['msg']) ? '数据获取失败，请检查对接配置！' : $Get['msg']));
        }

        return $Get;
    }

    /**
     * @param $id
     * @param int $month
     * 主机续期！
     */
    public static function Renewal($id, $Day = 30)
    {
        global $date;
        $DB = SQL::DB();

        $Dv = BTCO::DataV($id);
        $MainframeData = $Dv['MainframeData'];

        if ($MainframeData['endtime'] <= $date) { //已经过期,则按照当前日期续时长
            $DataEnd = date("Y-m-d H:i:s", strtotime(" + $Day day"));
            if ((int)$MainframeData['type'] === 1) {
                $re = BTCO::GetSwitchState($MainframeData['siteId'], $MainframeData['identification'] . '.com', 1);
                if ($re['status'] === true) {
                    userlog('状态切换', '续期成功，自动开启网站主机空间(' . $MainframeData['id'] . ')空间！', $MainframeData['uid']);
                    $DB->update('mainframe', ['status' => 1], ['id' => $MainframeData['id']]);
                    BTCO::WriteException($re['id'], $re['msg'], 2);
                }
            }
        } else {
            $DataEnd = date("Y-m-d H:i:s", strtotime($MainframeData['endtime'] . " + $Day day"));
        }
        $re = BTCO::Getendtime($MainframeData['siteId'], $DataEnd);
        if ($re['status'] === true) {
            $DB->update('mainframe', ['endtime' => $DataEnd], ['id' => $MainframeData['id']]);
            BTCO::GetSwitchState($MainframeData['siteId'], $MainframeData['identification'] . '.com', 1);
            return true;
        }
        BTCO::WriteException($re['id'], $re['msg'], 2);
        return false;
    }

    /**
     * 退出登录
     */
    public static function LogOut()
    {

        $_SESSION[self::$SessionName] = null;

        dies(1, '恭喜你，成功退出登录！');
    }

    /**
     * 返回当前主机的登陆状态
     */
    public static function LoginStatus()
    {
        $Token = $_SESSION[self::$SessionName] ?? null;
        if (empty($Token) || strlen($Token) !== 32) {
            return false;
        }
        global $date;
        $DB = SQL::DB();
        $Data = $DB->get('mainframe', [
            '[>]server' => ['server' => 'id'],
        ], [
            'mainframe.id',
            'mainframe.identification',
            'mainframe.uid',
            'server.name',
            'mainframe.username',
            'mainframe.filesize',
        ], [
            'mainframe.identification' => (string)$Token, //密钥验证
            'mainframe.state' => 1, //主机状态
            'server.endtime[>]' => $date, //服务器到期？
            'server.state' => 1, //服务器状态
        ]);
        if (!$Data) {
            return false;
        }
        return $Data;
    }


    /**
     * @param array $Data
     * 主机后台登陆验证
     */
    public static function LoginVerification($Data)
    {
        test(['username|e', 'password|e', 'vercode|e'], '请将数据提交完整！');

        if (empty($_SESSION['LoginHost']) || $_SESSION['LoginHost'] !== md5(trim($Data['vercode']) . href())) {
            $_SESSION['LoginHost'] = null;
            dies(-1, '验证码有误！');
        } else {
            $_SESSION['LoginHost'] = null;
        }
        $DB = SQL::DB();
        global $date;
        $Verification = $DB->get('mainframe', ['id', 'identification', 'uid'], [
            'username' => trim($Data['username']),
            'password' => md5(trim((string)$_POST['password'])),
        ]);

        if (!$Verification) {
            dies(-1, '验证失败，账号或密码错误！');
        }

        /**
         * 不管是否绑定用户，都需要写入日志
         */
        userlog('主机登陆', 'ID为：' . $Verification['id'] . ' 的主机于' . $date . '被登陆，登陆IP为：' . userip(), $Verification['uid']);
        $_SESSION[self::$SessionName] = $Verification['identification'];

        dies(1, '登陆成功！');
    }
}