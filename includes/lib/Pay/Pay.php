<?php

/**
 * Author：晴玖天
 * Creation：2020/7/15 7:58
 * Filename：Pay.php
 * 在线支付模块
 */

namespace lib\Pay;


use lib\AppStore\AppList;
use lib\Hook\Hook;
use lib\supply\Order;
use Medoo\DB\SQL;
use query;
use Server\Server;

class Pay
{
    /**
     * @param $Data
     * @param array $Goods
     * 创建充值订单
     * @return bool
     */
    public static function PrepaidPhoneOrders($Data, $Goods = [])
    {
        global $date, $date_30, $conf, $times;
        /**
         * 验证支付通道是否可以使用
         * 取出该支付通道的配置数据
         */
        $PayType = self::VerifyPaymentChannels($Data['type']);
        $DB = SQL::DB();
        switch ($Data['gid']) {
            case -1: //在线充值

                break;
            case -2: //队列支付
                $Goods = $DB->get('queue',
                    [
                        '[>]goods' => ['gid' => 'gid']
                    ], [
                        'goods.gid',
                        'goods.name',
                        'goods.cid',
                        'queue.price',
                    ], [
                        'queue.order' => (string)$Data['input'][0],
                        'queue.addtime[>]' => $date_30,
                        'queue.type' => 3
                    ]);

                if (count($Data['input']) > 1) {
                    $Price = $DB->sum('queue', 'price', [
                        'order' => $Data['input'],
                        'addtime[>]' => $date_30,
                        'type' => 3
                    ]);
                    $Goods = [
                        'name' => $Goods['name'] . '等' . count($Data['input']) . '个商品...',
                        'price' => $Price
                    ];
                }
                /**
                 * 队列支付已经计算过一次优惠券使用，无需再次计算！
                 */
                $Data['CouponId'] = -1;
                break;
            case -3: //主机续期

                break;
        }

        if ($Goods['price'] < 0.01) {
            $Goods['price'] = 0.01;
        }

        /**
         * 创建支付订单
         */
        $OrderNumber = date('YmdHis') . rand(11111, 99999);

        $PayRates = (float)$conf['PayRates'];

        /**
         * 验证优惠券！
         */
        if (!empty($Data['CouponId']) && $Data['CouponId'] != -1 && $Data['gid'] >= 1 && $Data['uid'] >= 1) {
            $Coupon = $DB->get('coupon', '*', ['id' => (int)$Data['CouponId'], 'uid' => (int)$Data['uid'], 'oid' => -1]);
            if (!$Coupon) dies(-1, '优惠券不存在或未绑定到您的账户下,或已经使用！');
            switch ($Coupon['apply']) {
                case 1:
                    if ($Goods['gid'] != $Coupon['gid']) dies(-1, '此券不能用于此商品！');
                    break;
                case 2:
                    if ($Goods['cid'] != $Coupon['cid']) dies(-1, '此券不能用于此商品！');
                    break;
            }

            if ($Coupon['term_type'] == 1) {
                $TIME = strtotime($Coupon['gettime']) + (60 * 60 * 24 * $Coupon['indate']);
            } else {
                $TIME = strtotime($Coupon['expirydate']);
            }
            if (time() > $TIME) dies(-1, '此优惠券已过期');

            $CSQL = [
                'uid' => $Data['uid'],
                'oid[!]' => -1,
                'endtime[>]' => $times
            ];
            if ($conf['CouponUseIpType'] == 1) {
                $CSQL = [
                    'OR' => [
                        'uid' => $Data['uid'],
                        'ip' => userip(),
                    ],
                    'oid[!]' => -1,
                    'endtime[>]' => $times
                ];
            }
            $CountCoupon = $DB->count('coupon', $CSQL);
            if ($CountCoupon >= $conf['CouponUsableMax']) {
                dies(-1, '每天最多可使用' . $conf['CouponUsableMax'] . '张优惠券,今日已经使用了' . $CountCoupon . '张！');
            }


            switch ($Coupon['type']) {
                case 1:
                    if ($Coupon['minimum'] > $Goods['price']) {
                        dies(-1, '此优惠券订单付款金额需满' . $Coupon['minimum'] . '元才可使用！');
                    }
                    $PriceCou = $Goods['price'] - $Coupon['money'];
                    break;
                case 2:
                    $PriceCou = $Goods['price'] - $Coupon['money'];
                    break;
                case 3:
                    if ($Coupon['minimum'] > $Goods['price']) {
                        dies(-1, '此优惠券订单付款金额需满' . $Coupon['minimum'] . '元才可使用！');
                    }
                    $PriceCou = ($Goods['price'] * ($Coupon['money'] / 100));
                    break;
            }

            if ($conf['CouponMinimumType'] == 1) {
                if ($PriceCou <= ($Goods['money'] * $Goods['num'])) {
                    $PriceCou = ($Goods['money'] * $Goods['num']);
                }
            } else if ($PriceCou <= 0) {
                $PriceCou = 0;
            }

            $PriceY = sprintf('%.2f', round($Goods['price'], 2));
            $CouponId = $Data['CouponId'];

            if ($PayRates > 0) {
                //载入奸商费率
                $Rates = $PriceCou * ($PayRates / 100);
                $PriceCou += $Rates;
                $Goods['name'] .= ' - 费率' . $PayRates . '%';
            }
            $Money = sprintf('%.2f', round($PriceCou, 2));
        } else {
            $PriceY = -1;
            $CouponId = -1;

            if ($PayRates > 0) {
                //载入奸商费率
                $Rates = $Goods['price'] * ($PayRates / 100);
                $Goods['price'] += $Rates;
                $Goods['name'] .= ' - 费率' . $PayRates . '%';
            }
            $Money = sprintf('%.2f', round($Goods['price'], 2));
        }
        //由于在线支付仅支持0.01元及以上的金额，所以需要四舍五入保留2位小数

        if ($Money <= 0) {
            dies(-1, '实际需要付款的金额小于或等于0元，无法使用在线支付，请使用其他付款方式购买商品！');
        }

        $SQL = [
            'order' => $OrderNumber,
            'type' => $Data['type'],
            'uid' => $Data['uid'],
            'gid' => $Data['gid'],
            'name' => $Goods['name'],
            'money' => $Money, //实际付款金额
            'price' => $PriceY, //原价
            'coupon' => $CouponId, //优惠券id
            'ip' => userip(),
            'input' => json_encode($Data['input'], JSON_UNESCAPED_UNICODE),
            'num' => $Data['num'],
            'addtime' => $date
        ];
        $Res = $DB->insert('pay', $SQL);
        $ID = $DB->id();
        if ($Res && $ID) {
            mkdirs(ROOT . 'includes/extend/log/Order/');
            if ($Data['gid'] != -1) {
                query::OrderCookie($OrderNumber);
            }
            self::ArousePayment($ID, $PayType);
        } else {
            dies(-1, '支付订单创建失败,请重新尝试！');
        }

        return true;
    }

    /**
     * @param $type
     * @return mixed
     * 验证支付通道
     * 并取出支付配置信息
     */
    public static function VerifyPaymentChannels($type)
    {
        global $conf;
        switch ($type) {
            case 'qqpay':
                if ($conf['PayConQQ'] == -1) dies(-1, '当前QQ支付通道未开启！');
                $vis = AppList::AppConf($conf['PayConQQ']);
                $Data = $vis['Data'][0];
                $Type = 'PayConQQ';
                break;
            case 'wxpay':
                if ($conf['PayConWX'] == -1) dies(-1, '当前微信支付通道未开启！');
                $vis = AppList::AppConf($conf['PayConWX']);
                $Data = $vis['Data'][1];
                $Type = 'PayConWX';
                break;
            case 'alipay':
                if ($conf['PayConZFB'] == -1) dies(-1, '当前支付宝支付通道未开启！');
                $vis = AppList::AppConf($conf['PayConZFB']);
                $Data = $vis['Data'][2];
                $Type = 'PayConZFB';
                break;
            default:
                dies(-1, '未知付款方式！');
                break;
        }
        if (empty($vis['versions']) || empty($vis['state'])) dies(-1, '当前支付通道异常，无法完成付款！');

        foreach ($Data as $v) {
            if ($v == '') dies(-1, '此支付通道未配置完善,无法使用！');
        }

        return $Type;
    }

    /**
     * 获取付款地址
     */
    public static function ArousePayment($id, $PayType)
    {
        global $conf;
        $Rs = AppList::Api($conf[$PayType], [
            'pid' => $id,
            'typeS' => 1,
        ], false);
        if ($Rs === false) {
            dies(-1, '此支付通道有异常，可能是未安装，也可能是已经关闭，请换一个支付通道，或联系平台客服处理！');
        }
        dier($Rs);
    }

    /**
     * @param $Order //订单数据
     * @param $Data //支付回调数据
     * 付款成功后的回调
     */
    public static function PaySuccess($Order, $Data)
    {
        global $conf, $date;
        $DB = SQL::DB();
        header('Content-Type: application/json; charset=UTF-8');
        switch ((int)$Order['gid']) {
            case -2: //队列订单处理
                $Res = $DB->update('pay', [
                    'trade_no' => $Data['trade_no'],
                    'state' => 1,
                    'endtime' => $date,
                ], [
                    'order' => $Data['out_trade_no'],
                ]);

                if ($Res) {
                    $Pids = $DB->get('pay', '*', [
                        'order' => (string)$Data['out_trade_no'],
                    ]);
                    Hook::execute('PaySuccess', [
                        'PayOrder' => $Pids
                    ]);

                    /**
                     * 开始批量修改订单队列订单！
                     */
                    $Array = json_decode($Order['input'], TRUE);
                    if (count($Array) == 0) return [
                        'code' => -1,
                        'msg' => '订单异常，无法完成提交！'
                    ];
                    $i = 0;
                    foreach ($Array as $value) {
                        $DB->update('queue', [
                            'type' => 2,
                            'trade_no' => $Data['trade_no'],
                            'endtime' => $date,
                            'remark' => '您已经付款成功，我们会尽快为您提交到服务器！',
                        ], [
                            'order' => $value
                        ]);
                        $Queue = $DB->get('queue', ['money', 'coupon'], [
                            'order' => (string)$value
                        ]);
                        if ($Queue['coupon'] != -1) {
                            @$DB->update('coupon', [
                                'oid' => -2,
                                'ip' => userip(),
                                'endtime' => $date,
                            ], [
                                'id' => $Queue['coupon']
                            ]);
                        }
                    }
                    return [
                        'code' => 1,
                        'msg' => '订单提交成功！<br>本次提交订单总数:' . count($Array) . '个<br>成功提交' . $i . '个'
                    ];
                }

                return [
                    'code' => -1,
                    '订单状态修改失败,请联系管理员处理！'
                ];
            case -1: //用户充值订单处理
                $Res = $DB->update('pay', [
                    'trade_no' => $Data['trade_no'],
                    'state' => 1,
                    'endtime' => $date,
                ], [
                    'order' => $Data['out_trade_no'],
                ]);

                if ($Res) {
                    $Pids = $DB->get('pay', '*', [
                        'order' => (string)$Data['out_trade_no'],
                    ]);
                    Hook::execute('PaySuccess', [
                        'PayOrder' => $Pids
                    ]);

                    $Rs = $DB->update('user', [
                        'money[+]' => round($Pids['money'], 2)
                    ], [
                        'id' => $Pids['uid'],
                    ]);
                    if ($Rs) {
                        userlog('在线充值', '用户' . $Pids['uid'] . '于' . $date . '成功充值' . round($Pids['money'], 2) . '元,支付ID:' . $Pids['id'] . '！', $Pids['uid'], round($Pids['money'], 2));
                        return [
                            'code' => 1,
                            'msg' => '恭喜您成功充值：' . round($Pids['money'], 2) . '元余额！'
                        ];
                    }

                    return [
                        'code' => -1,
                        'msg' => '余额充值失败,请联系客服补款：' . round($Pids['money'], 2) . '元！'
                    ];
                }
                return [
                    'code' => -1,
                    '订单状态修改失败,请联系管理员处理！'
                ];
            case -3: //主机续期订单处理
                $Res = $DB->update('pay', [
                    'trade_no' => $Data['trade_no'],
                    'state' => 1,
                    'endtime' => $date,
                ], [
                    'order' => $Data['out_trade_no'],
                ]);
                if ($Res) {
                    $Pids = $DB->get('pay', '*', [
                        'order' => (string)$Data['out_trade_no'],
                    ]);
                    if ($Pids) {
                        $Pids['input'] = json_decode($Pids['input'], true)['id'];
                        $Res = Server::Renewal($Pids['input'], $Pids['num']);
                        if ($Res) {
                            userlog('主机续期', 'ID为' . $Pids['input'] . '的主机空间用户，于' . $date . '在线支付' . round($Pids['money'], 2) . '元续期成功,支付ID:' . $Pids['id'] . '！', $Pids['uid'], round($Pids['money'], 2));
                            return [
                                'code' => 1,
                                'msg' => '主机续期成功！',
                            ];
                        }
                        userlog('主机续期失败', 'ID为' . $Pids['input'] . '的主机空间用户，于' . $date . '在线支付' . round($Pids['money'], 2) . '元后充值失败，请联系客服手动退款,支付ID:' . $Pids['id'] . '！', $Pids['uid'], round($Pids['money'], 2));
                        return [
                            'code' => -1,
                            'msg' => '主机续期失败!,请联系客服退款,支付ID:' . $Pids['id'] . '！',
                        ];
                    }

                    return [
                        'code' => -1,
                        'msg' => '充值订单不存在！',
                    ];
                }
                break;
            default: //商品订单处理
                $DataR = Order::SubmitPay($Order, $Data);
                if ($DataR['code'] >= 0) {
                    return [
                        'code' => 1,
                        'msg' => $DataR['msg']
                    ];
                }
                return [
                    'code' => -1,
                    'msg' => $DataR['msg']
                ];
        }
    }
}
