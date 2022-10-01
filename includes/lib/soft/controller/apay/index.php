<?php

namespace lib;

use lib\AppStore\AppList;
use lib\Pay\Pay;
use Medoo\DB\SQL;

class apay
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
        }
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
            'url' => is_https(false) . href() . '/?AppApies&identification=apay&pid=' . $Pid . '&typeS=2',
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
        if (isset($_COOKIE['league'])) {
            file_put_contents(ROOT . 'includes/extend/log/Order/' . $Order['order'] . '.log', xiaochu_de($_COOKIE['league']));
        }

        /**
         * 唤起支付接口
         */
        $config = array(
            //签名方式,默认为RSA2(RSA2048)
            'sign_type' => "RSA2",
            //支付宝公钥
            'alipay_public_key' => $PayData[1],
            //商户私钥
            'merchant_private_key' => $PayData[2],
            //编码格式
            'charset' => "UTF-8",
            //支付宝网关
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
            //应用ID
            'app_id' => $PayData[3],
            //异步通知地址,只有扫码支付预下单可用
            'notify_url' => is_https(false) . href() . '/index.php?AppApies=1&identification=apay&typeS=3',
            //最大查询重试次数
            'MaxQueryRetry' => "10",
            //查询间隔
            'QueryDuration' => "3",
        );
        $Pay = [
            'order' => $Order['order'],
            'money' => sprintf("%.2f", round($Order['money'], 2)),
            'name' => ($PayData[0] <> -1 ? $PayData[0] : $Order['name']),
            'addtime' => $Order['addtime'],
        ];

        require_once("apay/alipay.php");
        die();
    }

    /**
     * 异步通知
     */
    public static function asynchronization($Data)
    {
        $Data = $_REQUEST;
        self::obstruct($Data['out_trade_no'], 2);
        global $conf;
        $DB = SQL::DB();
        $Order = $DB->get('pay', '*', [
            'order' => $Data['out_trade_no']
        ]);
        @header('Content-Type: text/html; charset=UTF-8');

        if (!$Order) die('fail');

        /**
         * 清除无关参数
         */
        unset($Data['AppApies'], $Data['identification'], $Data['typeS']);
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

        $config = array(
            //签名方式,默认为RSA2(RSA2048)
            'sign_type' => "RSA2",
            //支付宝公钥
            'alipay_public_key' => $PayData[1],
            //商户私钥
            'merchant_private_key' => $PayData[2],
            //编码格式
            'charset' => "UTF-8",
            //支付宝网关
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
            //应用ID
            'app_id' => $PayData[3],
            //异步通知地址,只有扫码支付预下单可用
            'notify_url' => is_https(false) . href() . '/index.php?AppApies=1&identification=apay&typeS=3',
            //最大查询重试次数
            'MaxQueryRetry' => "10",
            //查询间隔
            'QueryDuration' => "3",
        );

        require_once("apay/AlipayTradeService.php");

        $alipaySevice = new \AlipayTradeService($config);
        $verify_result = $alipaySevice->check($Data);
        if ($verify_result && $PayData[3] == (float)$Data['app_id']) {
            if ($Data['trade_status'] == 'TRADE_SUCCESS' && $Order['state'] == 2) {
                $Vs = $DB->get('order', ['trade_no'], ['trade_no' => $Data['trade_no']]);
                $Vs2 = $DB->get('queue', ['trade_no'], ['trade_no' => $Data['trade_no']]);
                if ($Vs || $Vs2) {
                    die('success');
                }

                $Data['money'] = $Data['buyer_pay_amount'];
                $Rs = Pay::PaySuccess($Order, $Data);
                if ($Rs['code'] >= 0) {
                    die('success');
                } else die('success');
            } else  die('success');
        } else {
            //验证失败
            echo "fail";
        }

        die();
    }

    /**
     * @param $Order 订单号
     * 防止异步同步同时进行！
     */
    public static function obstruct($Order, $Type = 1)
    {
        @header('Content-Type: text/html; charset=UTF-8');
        $Flie = ROOT . '/includes/extend/log/Astrict/OrderApay_' . $Order . '.log';
        if (file_exists($Flie)) {
            if ($Type == 1) {
                show_msg('温馨提示', '当前订单已经完成,若有漏单情况可联系客服处理！', 2, false, false);
            } else die('success');
        } else @file_put_contents($Flie, time());
    }
}