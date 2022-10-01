<?php

namespace lib;

use lib\AppStore\AppList;
use lib\Pay\Pay;
use Medoo\DB\SQL;

class cpay
{
    /**
     * @param array $Data
     * 控制器入口
     */
    public static function origin(array $Data)
    {
        switch ($Data['typeS']) {
            case 1: //获取付款地址
                self::payment($Data['pid']);
                break;
            case 2: //唤起支付
                self::arouse($Data['pid']);
                break;
            case 3: //异步通知
                self::asynchronization($Data);
                break;
            case 4: //同步通知
                self::synchronization($Data);
                break;
            case 5: //获取支付界面
                self::PaymentInterface($Data);
                break;
        }
    }

    /**
     * @param $Data
     */
    public static function PaymentInterface($Data)
    {
        global $conf;
        unset($Data['AppApies'], $Data['identification'], $Data['typeS'], $Data['AppApies'], $Data['AppApies'], $Data['AppApies']);
        $_SERVER['QUERY_STRING'] = str_replace("AppApies&identification=cpay&typeS=5&", '', $_SERVER['QUERY_STRING']);
        error_reporting(E_ALL & ~E_NOTICE);
        date_default_timezone_set('PRC');
        ob_clean();
        header('Content-type:text/html;charset=utf-8');
        $codepay_html = '';
        $api_url = 'http://api4.xiuxiu888.com/creat_order/?';
        $timeout = 3;
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url . $_SERVER['QUERY_STRING']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            if (!empty($Data)) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($Data));
            }
            $codepay_html = curl_exec($ch);
            curl_close($ch);
        } else if (function_exists('file_get_contents')) {
            $context = array();
            if (!empty($Data)) {
                ksort($Data);
                $context['http'] = array('timeout' => $timeout, 'method' => 'POST', 'content' => http_build_query($Data, '', '&'));
            } else {
                $context['http'] = array('timeout' => $timeout, 'method' => 'GET');
            }
            $codepay_html = file_get_contents($api_url . $_SERVER['QUERY_STRING'], false, stream_context_create($context));
        }
        if (!empty($codepay_html)) {
            $codepay_html = str_replace(array('https://codepay.fateqq.com/', 'https://codepay.yy2169.com'), array('https://api.xiuxiu888.com/', 'https://api.xiuxiu888.com'), $codepay_html);
            echo $codepay_html;
            exit(0);
        }
        $user_data["pay_url"] = "https://api.xiuxiu888.com/creat_order/?" . (empty($Data) ? $_SERVER['QUERY_STRING'] : http_build_query($Data, '', '&'));
        die(`<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Language" content="zh-cn">
    <meta name="apple-mobile-web-app-capable" content="no"/>
    <meta name="apple-touch-fullscreen" content="yes"/>
    <meta name="format-detection" content="telephone=no,email=no"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="white">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>扫码支付 - (` . $conf['sitename'] . `)</title>
    <script src="https://codepay.fateqq.com/js/jquery-1.10.2.min.js"></script>
</head>
<body>
<div id="showPage" class="showPage">loading... <a href="javascript:pay();">立即跳转</a></div>
<script>
    var user_data = ` . json_encode($user_data) . `;
    function pay(){
        window.location.href=user_data['pay_url'];
    }
    $(document).ready(function () {
        htmlobj = $.ajax({url: user_data['pay_url'], async: false});
        $("#showPage").html(htmlobj.responseText);
    });
</script>
</body>
</html>`);
    }

    /**
     * @param $Pid 商品订单ID
     * 获取付款链接
     */
    public static function payment($Pid)
    {
        dier([
            'code' => 2,
            'msg' => '付款链接创建成功！',
            'url' => is_https(false) . href() . '/?AppApies&identification=cpay&pid=' . $Pid . '&typeS=2',
            'pid' => $Pid
        ]);
    }

    /**
     * 唤起支付
     */
    public static function arouse($Pid)
    {
        global $conf;
        $DB = SQL::DB();

        $Order = $DB->get('pay', '*', [
            'id' => (int)$Pid,
        ]);

        @header('Content-Type: text/html; charset=UTF-8');

        if (!$Order) show_msg('警告', '支付Pid[' . $Pid . ']无对应支付订单,请直接关闭当前界面！', 2, false, false);

        if ($Order['state'] == 1) show_msg('恭喜', '此订单已完成，若有漏单情况，可联系客服处理！', 2, false, false);

        switch ($Order['type']) {
            case 'qqpay':
                if ($conf['PayConQQ'] == -1) show_msg('警告', '当前QQ支付通道未开启!', 2, false, false);
                $PayData = AppList::AppConf($conf['PayConQQ'])['Data'][0];
                break;
            case 'wxpay':
                if ($conf['PayConWX'] == -1) show_msg('警告', '当前微信支付通道未开启!', 2, false, false);
                $PayData = AppList::AppConf($conf['PayConWX'])['Data'][1];
                break;
            case 'alipay':
                if ($conf['PayConZFB'] == -1) show_msg('警告', '当前支付宝支付通道未开启!', 2, false, false);
                $PayData = AppList::AppConf($conf['PayConZFB'])['Data'][2];
                break;
            default:
                show_msg('警告', '当前选择的付款方式不存在!', 2, false, false);
                break;
        }

        foreach ($PayData as $v) {
            if ($v == '') show_msg('警告', '此支付通道未配置完善,无法使用！', 2, false, false);
        }

        /**
         * 写入异步回调日志
         */
        if ($conf['userdomaintype'] == 2 && isset($_COOKIE['league'])) {
            file_put_contents(ROOT . 'includes/extend/log/Order/' . $Order['order'] . '.log', xiaochu_de($_COOKIE['league']));
        }

        /**
         * 唤起支付接口
         */
        require_once("cpay/cpay.submit.class.php");

        switch ($Order['type']) {
            case 'wxpay': #微信
                $Typs = 3;
                break;
            case 'qqpay': #QQ
                $Typs = 2;
                break;
            case 'alipay': #支付宝
                $Typs = 1;
                break;
            default:
                $Typs = 3; //默认为微信付款
                break;
        }

        $url = \cpay::cpay_sgin([
            'order' => $Order['order'],
            'money' => sprintf("%.2f", round($Order['money'], 2)),
        ], [
            'type' => $Typs,
            'chartchart' => strtolower('utf-8'),
            'page' => 1,
            'style' => 1,
            'outTime' => 300,
            'min' => 0.01,
            'pay_type' => 1
        ], [
            'notify_url' => is_https(false) . href() . '/includes/CallBack.php?uis=3HsDcpay', //异步
            'return_url' => is_https(false) . href() . '/includes/CallBack.php?uis=4HsDcpay', //同步
            'cpay_id' => $PayData[0],
            'cpay_key' => $PayData[1],
        ]);
        die("<script>window.location.href='{$url}';</script>");
    }

    /**
     * @param $Order 订单号
     * 防止异步同步同时进行！
     */
    public static function obstruct($Order, $Type = 1)
    {
        $Flie = ROOT . '/includes/extend/log/Astrict/OrderEpay_' . $Order . '.log';
        if (file_exists($Flie)) {
            $DB = SQL::DB();
            $Order = $DB->get('pay', ['state', 'gid', 'money'], [
                'order' => $Order,
                'state' => 1
            ]);
            if ($Order) {
                if ($Type == 1) {
                    switch ((int)$Order['gid']) {
                        case -2:
                            show_msg('温馨提示', '订单提交成功，请前往查看', 1, '/?mod=route&p=Order');
                            break;
                        case -3:
                            show_msg('温馨提示', '主机续期成功，请前往查看', 1, '/HostAdmin');
                            break;
                        case -1:
                            show_msg('温馨提示', '恭喜您成功充值：' . round($Order['money'], 2) . '元余额！', 1, '/?mod=route&p=User');
                            break;
                        default:
                            show_msg('温馨提示', '商品购买成功，请前往查看', 1, '/?mod=route&p=Order');
                            break;
                    }
                } else {
                    die('success');
                }
            }
        } else {
            @file_put_contents($Flie, time());
        }
    }

    /**
     * 异步通知
     */
    public static function asynchronization($Data)
    {
        self::obstruct($Data['pay_id'], 2);
        global $conf;
        $DB = SQL::DB();
        $Order = $DB->get('pay', '*', [
            'order' => $Data['pay_id']
        ]);
        header('Content-Type: text/html; charset=UTF-8');
        if (!$Order) {
            die('fail');
        }
        /**
         * 清除无关参数
         */
        unset($Data['typeS'], $Data['uis']);
        /**
         * 开始验证订单
         */
        @header('Content-Type: text/html; charset=UTF-8');
        switch ($Order['type']) {
            case 'qqpay':
                if ($conf['PayConQQ'] == -1) die('fail');
                $PayData = AppList::AppConf($conf['PayConQQ'])['Data'][0];
                break;
            case 'wxpay':
                if ($conf['PayConWX'] == -1) die('fail');
                $PayData = AppList::AppConf($conf['PayConWX'])['Data'][1];
                break;
            case 'alipay':
                if ($conf['PayConZFB'] == -1) die('fail');
                $PayData = AppList::AppConf($conf['PayConZFB'])['Data'][2];
                break;
            default:
                die('fail');
                break;
        }

        ksort($Data); //排序post参数
        reset($Data); //内部指针指向数组中的第一个元素
        $sign = '';
        $urls = '';
        foreach ($Data as $key => $val) {
            if ($val == '') continue;
            if ($key != 'sign') {
                if ($sign != '') {
                    $sign .= "&";
                    $urls .= "&";
                }
                $sign .= "$key=$val"; //拼接为url参数形式
                $urls .= "$key=" . urlencode($val); //拼接为url参数形式
            }
        }

        if (empty($Data['pay_no']) || md5($sign . $PayData[1]) <> $Data['sign']) {
            die('fail');
        }

        /**
         * 成功返回
         */
        if ($Order['state'] == 2) {
            $Vs = $DB->get('order', ['trade_no'], ['trade_no' => $Data['pay_no']]);
            $Vs2 = $DB->get('queue', ['trade_no'], ['trade_no' => $Data['pay_no']]);
            if ($Vs || $Vs2) {
                die('success');
            }
            $Data = [
                'out_trade_no' => $Data['pay_id'],
                'money' => $Data['price'],
                'trade_no' => $Data['pay_no'],
            ];
            $Rs = Pay::PaySuccess($Order, $Data);
            if ($Rs['code'] >= 0) {
                die('success');
            }
            die('success');
        }
        die('success');
    }

    /**
     * 同步通知
     */
    public static function synchronization($Data)
    {
        /**
         * 同步暂停0.3秒防止和异步冲突！
         */
        usleep(1000 * 1000 * 0.3);
        self::obstruct($Data['pay_id'], 1);
        global $conf;
        $DB = SQL::DB();
        $Order = $DB->get('pay', '*', [
            'order' => $Data['pay_id']
        ]);
        @header('Content-Type: text/html; charset=UTF-8');
        if (!$Order) show_msg('警告', '订单不存在!', 2, false, false);

        /**
         * 清除无关参数
         */
        unset($Data['typeS'], $Data['uis']);
        /**
         * 开始验证订单
         */

        switch ($Order['type']) {
            case 'qqpay':
                if ($conf['PayConQQ'] == -1) show_msg('警告', '当前QQ支付通道未开启,无法发完成付款回调!', 2, false, false);
                $PayData = AppList::AppConf($conf['PayConQQ'])['Data'][0];
                break;
            case 'wxpay':
                if ($conf['PayConWX'] == -1) show_msg('警告', '当前微信支付通道未开启,无法发完成付款回调!', 2, false, false);
                $PayData = AppList::AppConf($conf['PayConWX'])['Data'][1];
                break;
            case 'alipay':
                if ($conf['PayConZFB'] == -1) show_msg('警告', '当前支付宝支付通道未开启,无法发完成付款回调!', 2, false, false);
                $PayData = AppList::AppConf($conf['PayConZFB'])['Data'][2];
                break;
            default:
                show_msg('警告', '当前选择的付款方式不存在,无法发完成付款回调!', 2, false, false);
                break;
        }

        ksort($Data); //排序post参数
        reset($Data); //内部指针指向数组中的第一个元素
        $sign = '';
        $urls = '';
        foreach ($Data as $key => $val) {
            if ($val == '') continue;
            if ($key != 'sign') {
                if ($sign != '') {
                    $sign .= "&";
                    $urls .= "&";
                }
                $sign .= "$key=$val"; //拼接为url参数形式
                $urls .= "$key=" . urlencode($val); //拼接为url参数形式
            }
        }

        if (empty($Data['pay_no']) || md5($sign . $PayData[1]) <> $Data['sign']) {
            show_msg('温馨提示', '订单验证失败，请联系管理员处理！', 4, '/?mod=route&p=Order');
        } else {
            /**
             * 成功返回
             */
            if ($Order['state'] == 2) {
                $Vs = $DB->get('order', ['trade_no'], ['trade_no' => $Data['pay_no']]);
                $Vs2 = $DB->get('queue', ['trade_no'], ['trade_no' => $Data['pay_no']]);
                if ($Vs || $Vs2) {
                    show_msg('温馨提示', '订单已完成，点击下方按钮继续！', 1, '/?mod=route&p=Order');
                }

                $Data = [
                    'out_trade_no' => $Data['pay_id'],
                    'money' => $Data['price'],
                    'trade_no' => $Data['pay_no'],
                ];

                $Rs = Pay::PaySuccess($Order, $Data);
                if ($Rs['code'] >= 0) {
                    show_msg('温馨提示', $Rs['msg'], 1, '/?mod=route&p=Order');
                } else show_msg('温馨提示', ($Rs['msg'] == '' ? '订单已完成！' : $Rs['msg']), 1, '/?mod=route&p=Order');
            } else  show_msg('温馨提示', '订单已完成，点击下方按钮继续！', 1, '/?mod=route&p=Order');
        }
    }
}