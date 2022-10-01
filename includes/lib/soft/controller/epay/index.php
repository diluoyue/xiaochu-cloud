<?php

namespace lib;

use lib\AppStore\AppList;
use lib\Pay\Pay;
use Medoo\DB\SQL;

class epay
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
            'url' => is_https(false) . href() . '/?AppApies&identification=epay&pid=' . $Pid . '&typeS=2',
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
        require_once("epay/epay_submit.class.php");

        $alipay_config = [
            'apiurl' => $PayData[0],
            'partner' => $PayData[1],
            'key' => $PayData[2],
            'sign_type' => strtoupper('MD5'),
            'input_charset' => strtolower('utf-8'),
            'transport' => is_https(false, 2),
        ];
        /**
         * 构建参数
         */
        $parameter = [
            'pid' => trim($alipay_config['partner']),
            'type' => $Order['type'],
            'notify_url' => is_https(false) . href() . '/includes/CallBack.php?uis=3HsDepay', //异步
            'return_url' => is_https(false) . href() . '/includes/CallBack.php?uis=4HsDepay', //同步
            'out_trade_no' => $Order['order'],
            'name' => $Order['name'],
            'money' => sprintf("%.2f", round($Order['money'], 2)),
            'sitename' => $conf['sitename']
        ];
        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter);
        die($html_text);
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
        self::obstruct($Data['out_trade_no'], 2);
        global $conf;
        $DB = SQL::DB();
        $Order = $DB->get('pay', '*', [
            'order' => $Data['out_trade_no']
        ]);
        if (!$Order) {
            die('fail');
        }
        $DB = SQL::DB();
        $Order = $DB->get('pay', '*', [
            'order' => $Data['out_trade_no']
        ]);
        if (!$Order) {
            show_msg('警告', '充值订单不存在，请直接关闭界面!', 2, '/?mod=route&p=Order');
        }
        /**
         * 清除无关参数
         */
        unset($Data['typeS'], $Data['uis']);
        /**
         * 开始验证订单
         */
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

        require_once("epay/epay_notify.class.php");

        $alipay_config = [
            'apiurl' => $PayData[0],
            'partner' => $PayData[1],
            'key' => $PayData[2],
            'sign_type' => strtoupper('MD5'),
            'input_charset' => strtolower('utf-8'),
            'transport' => is_https(false, 2),
        ];

        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify($Data);

        if ($verify_result) {
            if ($Data['trade_status'] == 'TRADE_SUCCESS' && $Order['state'] == 2) {
                $Vs = $DB->get('order', ['trade_no'], ['trade_no' => $Data['trade_no']]);
                $Vs2 = $DB->get('queue', ['trade_no'], ['trade_no' => $Data['trade_no']]);
                if ($Vs || $Vs2) {
                    die('success');
                }
                $Rs = Pay::PaySuccess($Order, $Data);
                if ($Rs['code'] >= 0) {
                    die('success');
                }
                die('success');
            }
            die('success');
        }
        die('fail');
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
        self::obstruct($Data['out_trade_no'], 1);
        global $conf;
        $DB = SQL::DB();
        $Order = $DB->get('pay', '*', [
            'order' => $Data['out_trade_no']
        ]);
        if (!$Order) show_msg('警告', '充值订单不存在，请直接关闭界面!', 2, '/?mod=route&p=Order');
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

        require_once("epay/epay_notify.class.php");

        $alipay_config = [
            'apiurl' => $PayData[0],
            'partner' => $PayData[1],
            'key' => $PayData[2],
            'sign_type' => strtoupper('MD5'),
            'input_charset' => strtolower('utf-8'),
            'transport' => is_https(false, 2),
        ];

        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify($Data);

        if ($verify_result) {
            if ($Data['trade_status'] == 'TRADE_SUCCESS' && $Order['state'] == 2) {
                $Vs = $DB->get('order', ['trade_no'], ['trade_no' => $Data['trade_no']]);
                $Vs2 = $DB->get('queue', ['trade_no'], ['trade_no' => $Data['trade_no']]);
                if ($Vs || $Vs2) {
                    show_msg('温馨提示', '订单已完成，请点击下方按钮继续！', 1, '/?mod=route&p=Order');
                }
                $Rs = Pay::PaySuccess($Order, $Data);
                if ($Rs['code'] >= 0) {
                    show_msg('温馨提示', $Rs['msg'], 1, '/?mod=route&p=Order');
                } else {
                    show_msg('温馨提示', ($Rs['msg'] == '' ? '订单已完成,请点击下方按钮继续！' : $Rs['msg']), 1, '/?mod=route&p=Order');
                }
            } else {
                show_msg('温馨提示', '订单已完成，请点击下方按钮继续！', 1, '/?mod=route&p=Order');
            }
        } else {
            show_msg('温馨提示', '订单验证失败，请联系管理员处理！', false, '/?mod=route&p=Order');
        }
    }
}