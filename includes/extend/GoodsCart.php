<?php

/**
 * Author：晴玖天
 * Creation：2020/4/6 15:23
 * Filename：GoodsCart.php
 * 简单购物车操作类
 */

namespace extend;


use config;
use lib\Hook\Hook;
use lib\Pay\Pay;
use lib\supply\Order;
use lib\supply\Price;
use login_data;
use Medoo\DB\SQL;
use query;

class GoodsCart
{
    /**
     * @param $Datas //下单参数
     * @param $GidData //商品信息
     * @param $uid //下单用户ID
     * 创建队列订单，正常下单模式
     */
    public static function OredeQueue($Datas, $GidData, $uid)
    {
        global $date, $conf, $times;
        $DB = SQL::DB();
        if ($Datas['type'] == 1) {

            /**
             * 在线支付，计算价格
             */

            if ($Datas['mode'] === 'alipay') {
                $payment = 'alipay';
            } else if ($Datas['mode'] === 'wxpay') {
                $payment = 'wxpay';
            } else {
                $payment = 'qqpay';
            }

            if ((int)$GidData['freight'] !== -1) {
                $freight = $DB->get('freight', '*', ['id' => $GidData['freight']]);
                if ($freight) {
                    $price = Price::Freight($freight, $Datas['data'], $GidData['price'], $Datas['num']);
                } else {
                    $price = $GidData['price'] * $Datas['num'];
                }
            } else {
                $price = $GidData['price'] * $Datas['num'];
            }

            if ($price < 0) {
                $price = 0.01;
            }

            $price = round($price, 8);
        } else if ($Datas['type'] == 2) {
            $payment = '余额付款';
            if ((int)$GidData['freight'] !== -1) {
                $freight = $DB->get('freight', '*', ['id' => $GidData['freight']]);
                if ($freight) {
                    $price = Price::Freight($freight, $Datas['data'], $GidData['price'], $Datas['num']);
                }
            } else {
                $price = $GidData['price'] * $Datas['num'];
            }
            if ($price < 0) {
                $price = 0.01;
            }
        } else {
            $payment = '积分兑换';
            $price = $GidData['points'] * $Datas['num'];
            if ($price < 0) $price = 1;
        }

        if ($price === 0) {
            $payment = '免费领取';
            $Money = -1;
            $Datas['CouponId'] = -1;
        } else {
            /**
             * 验证优惠券！
             */
            if (!empty($Datas['CouponId']) && $Datas['CouponId'] != -1 && $GidData['gid'] >= 1 && $uid >= 1) {
                if ($payment == '积分兑换') dies(-1, '优惠券不可用于' . $conf['currency'] . '兑换,仅可用于在线支付或余额付款！');
                $Coupon = $DB->get('coupon', '*', ['id' => $Datas['CouponId'], 'uid' => (int)$uid, 'oid' => -1]);
                if (!$Coupon) dies(-1, '优惠券不存在或未绑定到您的账户下,或已经使用！');
                switch ($Coupon['apply']) {
                    case 1:
                        if ($GidData['gid'] != $Coupon['gid']) dies(-1, '此券不能用于此商品！');
                        break;
                    case 2:
                        if ($GidData['cid'] != $Coupon['cid']) dies(-1, '此券不能用于此商品！');
                        break;
                }

                if ($Coupon['term_type'] == 1) {
                    $TIME = strtotime($Coupon['gettime']) + (60 * 60 * 24 * $Coupon['indate']);
                } else {
                    $TIME = strtotime($Coupon['expirydate']);
                }
                if (time() > $TIME) dies(-1, '此优惠券已过期');

                $CSQL = [
                    'uid' => $uid,
                    'oid[!]' => -1,
                    'endtime[>]' => $times
                ];
                if ($conf['CouponUseIpType'] == 1) {
                    $CSQL = [
                        'OR' => [
                            'uid' => $uid,
                            'ip' => userip(),
                        ],
                        'oid[!]' => -1,
                        'endtime[>]' => $times
                    ];
                }
                $CountCoupon = $DB->count('coupon', $CSQL);
                if ($CountCoupon >= $conf['CouponUsableMax']) dies(-1, '每天最多可使用' . $conf['CouponUsableMax'] . '张优惠券,今日已经使用了' . $CountCoupon . '张！');

                switch ($Coupon['type']) {
                    case 1:
                        if ($Coupon['minimum'] > $price) dies(-1, '此优惠券订单付款金额需满' . $Coupon['minimum'] . '元才可使用！');
                        $PriceCou = $price - $Coupon['money'];
                        break;
                    case 2:
                        $PriceCou = $price - $Coupon['money'];
                        break;
                    case 3:
                        if ($Coupon['minimum'] > $price) dies(-1, '此优惠券订单付款金额需满' . $Coupon['minimum'] . '元才可使用！');
                        $PriceCou = ($price * ($Coupon['money'] / 100));
                        break;
                }

                if ($conf['CouponMinimumType'] == 1) {
                    if ($PriceCou <= ($GidData['money'] * $GidData['num'])) {
                        $PriceCou = ($GidData['money'] * $GidData['num']);
                    }
                } else {
                    if ($PriceCou <= 0) {
                        $PriceCou = 0;
                    }
                }

                $Money = $price; //原价
                $price = $PriceCou; //现价
            } else {
                $Money = -1;
                $Datas['CouponId'] = -1;
            }
        }

        $Data = [
            'uid' => $uid,
            'ip' => userip(),
            'input' => json_encode($Datas['data'], JSON_UNESCAPED_UNICODE),
            'num' => $Datas['num'],
            'gid' => $Datas['gid'],
            'payment' => $payment,
            'price' => $price,
            'money' => $Money,
            'coupon' => (empty($Datas['CouponId']) ? -1 : $Datas['CouponId'])
        ];

        $order = self::CartOrder($Data);
        if ($order == false) dies(-1, '订单队列创建失败，请联系管理员处理！');
        /**
         * 队列创建成功
         * 则执行付款操作！
         */
        $price = (float)$price;
        if ($price == 0 && $Datas['CouponId'] == -1) {
            $UserData = login_data::user_data();
            if ($UserData['id'] < 1) dies('-2', '免费商品领取必须先登陆才可领取！');
            if ($UserData['state'] <> 1) dies(-1, '您当前账号已被禁封，无法购买商品！');

            $re = $DB->update('queue', [
                'type' => 2,
                'remark' => '当前已经免费领取了商品,系统会自动提交订单,请耐心等待',
                'trade_no' => '免费领取无订单',
            ], [
                'order' => $order,
                'payment' => '免费领取'
            ]);
            if ($re) {
                dies(1, '领取成功,点击查看您的商品订单!');
            } else dies(-1, '领取失败,请联系管理员处理！');
        }

        if ($Datas['type'] == 2 || $Datas['type'] == 3) {
            $UserData = login_data::user_data();
            if ($UserData['id'] < 1) dies('-2', '请先登陆!');
            if ($UserData['state'] <> 1) dies(-1, '您当前账号已被禁封，无法购买商品！');
        }

        switch ((int)$Datas['type']) {
            case 1:
                $Res = Pay::PrepaidPhoneOrders([
                    'type' => $Datas['mode'],
                    'uid' => $uid,
                    'gid' => -2,
                    'input' => [$order],
                    'num' => 1,
                    'CouponId' => -1,
                ], $GidData);
                dier($Res);
                break;
            case 2: //余额付款

                $UserMoney = login_data::UserMoney($UserData['id']);
                if (!$UserMoney) {
                    dies(-1, '用户状态异常，无法完成购买！');
                }
                if ($UserMoney['money'] < $price) {
                    dies(-1, '余额不足,还差' . ($price - $UserMoney['money']) . '元！');
                }
                if (($UserMoney['money'] - $price) < 0) {
                    dies(-1, '余额异常,请充值再购买！');
                }

                $Res = $DB->update('user', [
                    'money[-]' => $price
                ], [
                    'id' => $UserData['id'],
                ]);

                if ($Res) {
                    $re = $DB->update('queue', [
                        'type' => 2,
                        'remark' => '当前已经使用余额付款,系统会自动提交订单,请耐心等待！',
                        'trade_no' => '余额付款无支付订单',
                    ], [
                        'order' => $order,
                        'payment' => '余额付款'
                    ]);
                    if ($re) {
                        Hook::execute('PayMoney', [
                            'cause' => '创建队列订单,购买了商品' . $GidData['name'] . ',付款金额为：' . round($price, 8) . '元',
                            'money' => $price,
                            'uid' => $UserData['id'],
                        ]);
                        dies(1, '付款成功,点击查看您的商品订单!');
                    } else dies(-1, '付款失败,请联系管理员处理！');
                } else dies(-1, '购买失败，无法完成付款');
                break;
            case 3: //积分付款

                $UserMoney = login_data::UserMoney($UserData['id']);
                if (!$UserMoney) {
                    dies(-1, '用户状态异常，无法完成购买！');
                }

                if ($UserMoney['currency'] < $price) dies(-1, $conf['currency'] . '不足,还差' . ($price - $UserMoney['currency']) . $conf['currency'] . '！');
                if (($UserMoney['currency'] - $price) < 0) dies(-1, $conf['currency'] . '异常！');

                $Res = $DB->update('user', [
                    'currency[-]' => $price
                ], [
                    'id' => $UserData['id'],
                ]);

                if ($Res) {
                    $re = $DB->update('queue', [
                        'type' => 2,
                        'remark' => "当前已经使用" . $conf['currency'] . "兑换商品,系统会自动提交订单,请耐心等待！",
                        'trade_no' => '积分兑换无支付订单',
                    ], [
                        'order' => $order,
                        'payment' => '积分兑换'
                    ]);
                    if ($re) {
                        Hook::execute('PayPoints', [
                            'cause' => '创建队列订单,兑换了商品' . $GidData['name'] . ',消耗了' . $price . $conf['currency'],
                            'currency' => $price,
                            'uid' => $UserData['id'],
                        ]);
                        dies(1, '兑换成功,点击查看您的商品订单!');
                    } else {
                        dies(-1, '兑换失败,请联系管理员处理！');
                    }
                } else {
                    dies(-1, '兑换失败，无法完成兑换');
                }
                break;
            default:
                dies(-1, '未知付款方式！');
                break;
        }
    }

    /**
     * @param $Data 订单信息
     * 创建订单队列！
     */
    public static function CartOrder($Data)
    {
        global $date;
        $DB = SQL::DB();
        $order = date("YmdHis") . rand(11111, 99999999);
        $re = $DB->insert('queue', [
            'type' => '3',
            'order' => $order,
            'uid' => $Data['uid'],
            'ip' => $Data['ip'],
            'input' => $Data['input'],
            'num' => $Data['num'],
            'gid' => $Data['gid'],
            'payment' => $Data['payment'],
            'price' => $Data['price'],
            'money' => (empty($Data['money']) ? -1 : $Data['money']),
            'coupon' => (empty($Data['coupon']) ? -1 : $Data['coupon']),
            'remark' => '待付款订单已经创建成功,请尽快完成付款，待付款订单有效期为30分钟！',
            'addtime' => $date
        ]);
        if ($re && !empty($DB->id())) {
            query::QueueCookie($order);
            return $order;
        }

        return false;
    }

    /**
     * @param $DataArr
     * 创建订单缓存，提交订单！
     */
    public static function CartPay($DataArr = [], $type = 1, $mode = 'alipay', $CartKey = -1)
    {
        global $times, $conf;
        $Array = self::CartList($DataArr, $CartKey);
        $UserData = login_data::user_data();
        $DB = SQL::DB();
        /**
         * 校验商品！
         */
        $arr = [];
        $text = '';
        $i = 1;
        $price = 0; //余额
        $points = 0; //积分
        $CartDet = $DataArr; //待清理购物车订单！

        if ($type == 1) {
            if ($mode === 'alipay') {
                $payment = 'alipay';
            } else if ($mode === 'wxpay') {
                $payment = 'wxpay';
            } else {
                $payment = 'qqpay';
            }
        } else if ($type == 2) {
            $payment = '余额付款';
            if ($UserData == false) dies(-1, '请先完成登陆！');
            if ($UserData['state'] <> 1) dies(-1, '您当前账号已被禁封，无法购买商品，请联系客服处理！');
        } else {
            $payment = '积分兑换';
            if ($UserData == false) dies(-1, '请先完成登陆！');
            if ($UserData['state'] <> 1) dies(-1, '您当前账号已被禁封，无法购买商品，请联系客服处理！');
        }

        foreach ($Array['data'] as $key => $v) {
            if ((int)$v['GidData']['quota'] < (int)$v['GidData']['Count']) {
                $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '当前库存不足' . $v['GidData']['Count'] . '份,当前仅剩' . $v['GidData']['quota'] . '份,无法完成结算<br>';
                unset($CartDet[$i - 1]);
                ++$i;
                continue;
            }

            if ((int)$v['num'] <= 0) {
                $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '下单数量异常,无法完成结算<br>';
                unset($CartDet[$i - 1]);
                ++$i;
                continue;
            }

            if ($v['GidData']['communityId'] == '3') {
                $count_kami = $DB->count('token', [
                    'AND' => [
                        'gid' => $v['gid'],
                        'uid' => 1,
                    ]
                ]);
                if ($count_kami < (int)$v['GidData']['Count']) {
                    $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '卡密库存总数不足' . $v['GidData']['Count'] . '个,无法完成结算,当前仅剩' . $count_kami . '张卡<br>';
                    unset($CartDet[$i - 1]);
                    ++$i;
                    continue;
                }
            }

            if ($payment === '积分付款') {
                if ($UserData == false) {
                    $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '此商品必须登陆才可兑换哦,无法完成结算<br>';
                    unset($CartDet[$i - 1]);
                    ++$i;
                    continue;
                }

                $B = $DB->count('journal', [
                    'name' => ['积分兑换', '免费领取'],
                    'uid' => $UserData['id'],
                    'date[>]' => $times
                ]);
                if (($B + $v['count']) > $conf['getinreturn']) {
                    $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '，购物车内此商品总数为：' . $v['count'] . '份！您今日最多可兑换：' . $conf['getinreturn'] . '份！,无法完成结算<br>';
                    unset($CartDet[$i - 1]);
                    ++$i;
                    continue;
                }

                $B = $DB->count('journal', [
                    'name' => ['积分兑换', '免费领取'],
                    'date[>]' => $times
                ]);
                if (($B + $v['count']) > $conf['getinreturn_all']) {
                    $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '本站今日可兑换次数已经耗尽,本站今日提供的' . $conf['getinreturn_all'] . '此商品兑换次数不能满足当前商品下单总数(' . $v['GidData']['Count'] . ')！,无法完成结算<br>';
                    unset($CartDet[$i - 1]);
                    ++$i;
                    continue;
                }
                unset($B);
            }

            if ($v['GidData']['price'] === 0 && $v['GidData']['points'] === 0) {
                if (!$UserData) {
                    $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '此商品必须登陆才可领取哦,无法完成结算<br>';
                    unset($CartDet[$i - 1]);
                    ++$i;
                    continue;
                }

                $Day = $DB->count('journal', [
                    'name' => ['积分兑换', '免费领取'],
                    'uid' => $UserData['id'],
                    'date[>]' => $times,
                ]);
                if (($Day + $v['Charge']) > (int)$conf['getinreturn']) {
                    $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '，购物车内此商品总数为：' . $v['Charge'] . '份！您今日最多可领取：' . $conf['getinreturn'] . '份！,无法完成结算<br>';
                    unset($CartDet[$i - 1]);
                    ++$i;
                    continue;
                }
                $Day = $DB->count('journal', [
                    'name' => ['积分兑换', '免费领取'],
                    'date[>]' => $times,
                ]);
                if (($Day + $v['Charge']) > (int)$conf['getinreturn_all']) {
                    $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '本站今日可领取次数已经耗尽,本站今日提供的' . $conf['getinreturn_all'] . '次商品领取次数不能满足当前商品下单总数！,无法完成结算<br>';
                    unset($CartDet[$i - 1]);
                    ++$i;
                    continue;
                }
                unset($Day);
            }

            $Data = [
                'uid' => ($UserData == false ? '-1' : $UserData['id']),
                'ip' => userip(),
                'input' => json_encode($v['input'], JSON_UNESCAPED_UNICODE),
                'num' => $v['num'],
                'gid' => $v['gid'],
                'payment' => ($v['GidData']['points'] == 0 && $v['GidData']['price'] == 0 ? '免费领取' : $payment),
                'price' => ($payment == '积分兑换' ? $v['GidData']['points'] : $v['GidData']['price']),
            ];

            if ($Data['payment'] !== '免费领取') {
                //启用支付验证！
                if ($payment === '积分兑换') {
                    if ((!in_array(3, json_decode($v['GidData']['method'], TRUE)))) {
                        $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '，不支持积分兑换，无法完成结算！<br>';
                        unset($CartDet[$i - 1]);
                        ++$i;
                        continue;
                    }
                } else if ($payment === '余额付款') {
                    if ((!in_array(2, json_decode($v['GidData']['method'], TRUE)))) {
                        $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '，不支持余额购买，无法完成结算！<br>';
                        unset($CartDet[$i - 1]);
                        ++$i;
                        continue;
                    }
                } else {
                    if ((!in_array(1, json_decode($v['GidData']['method'], TRUE)))) {
                        $text .= '<font color="red">第' . $i . '件商品</font>：' . $v['GidData']['name'] . '，不支持在线付款，无法完成结算！<br>';
                        unset($CartDet[$i - 1]);
                        ++$i;
                        continue;
                    }
                }
            }

            $re = self::CartOrder($Data);

            if ($re == false) {
                $text .= '第' . $i . '件商品：' . $v['GidData']['name'] . '订单队列创建失败,无法完成结算<br>';
                unset($CartDet[$i - 1]);
                ++$i;
                continue;
            }

            $arr[] = $re;

            $price += $v['GidData']['price'];
            $points = $price + $v['GidData']['points'];
            ++$i;
        }

        if (count($arr) === 0) {
            dies(-1, '当前无可付款商品！<br>' . $text);
        }
        self::CartDet($CartDet, 2);

        if ($price < 0) {
            $price = 0.01;
        }
        if ($points <= 1 && $points <> 0) {
            $points = 1;
        }

        $price = (float)$price;
        $points = (float)$points;

        if ($price === 0 || $points === 0) {
            $re = $DB->update('queue', [
                'type' => 2,
                'remark' => '当前已经免费领取了商品,系统会自动提交订单,请耐心等待！',
                'trade_no' => '免费领取无订单',
            ], [
                'payment' => '免费领取',
                'order' => $arr
            ]);
            if ($re) {
                dies(1, '领取成功,点击查看您的商品订单！<br>' . $text);
            } else {
                dies(-4, '领取失败,请联系管理员处理！');
            }
        }

        switch ($type) {
            case 1:
                $Res = Pay::PrepaidPhoneOrders([
                    'type' => $mode,
                    'uid' => (!$UserData ? '-1' : $UserData['id']),
                    'gid' => -2,
                    'input' => $arr,
                    'num' => 1
                ]);
                dier($Res);
                break;
            case 2: //余额付款
                if ($UserData['money'] < $price) {
                    dies(-4, '当前余额不足' . $price . '元，无法结算商品，已经帮您创建了待付款订单，快去看看吧');
                }
                $Res = $DB->update('user', [
                    'money[-]' => $price,
                ], [
                    'id' => $UserData['id'],
                ]);

                if ($Res) {
                    $re = $DB->update('queue', [
                        'type' => 2,
                        'remark' => '当前已经使用余额付款,系统会自动提交订单,请耐心等待！',
                        'trade_no' => '余额付款无支付订单',
                    ], [
                        'payment' => '余额付款',
                        'order' => $arr
                    ]);

                    if ($re) {
                        Hook::execute('PayMoney', [
                            'cause' => '结算购物车,批量购买了' . count($arr) . '件商品,付款金额为：' . round($price, 8) . '元',
                            'money' => $price,
                            'uid' => $UserData['id'],
                        ]);
                        dies(1, '购买成功,点击查看您的商品订单！' . (empty($text) ? '' : '<br>' . $text));
                    } else dies(-4, '购买失败,若已扣款请联系管理员处理！');
                } else dies(-4, '支付失败，无法完成付款！');
                break;
            case 3: //积分付款
                $points = round($points);
                if ($UserData['currency'] < $points) dies(-4, '当前' . $conf['currency'] . '不足' . $points . '点，已经帮您创建了待付款订单，快去看看吧');

                $Res = $DB->update('user', [
                    'currency[-]' => $price,
                ], [
                    'id' => $UserData['id'],
                ]);

                if ($Res) {
                    $re = $DB->update('queue', [
                        'type' => 2,
                        'remark' => "当前已经使用" . $conf['currency'] . "兑换商品,系统会自动提交订单,请耐心等待！",
                        'trade_no' => '积分兑换无支付订单',
                    ], [
                        'payment' => '积分兑换',
                        'order' => $arr
                    ]);
                    if ($re) {
                        Hook::execute('PayPoints', [
                            'cause' => '结算购物车,批量兑换了' . count($arr) . '件商品,累计消耗' . $price . $conf['currency'],
                            'currency' => $price,
                            'uid' => $UserData['id'],
                        ]);
                        dies(1, '兑换成功,点击查看您的商品订单！<br>' . $text);
                    } else dies(-4, '兑换失败,若已扣款请联系管理员处理！');
                } else dies(-4, '兑换失败，无法完成扣款！');
                break;
            default:
                dies(-4, '未知付款方式！');
                break;
        }
    }

    /**
     * @param array $array
     * @return array
     * 取出购物车订单列表
     */
    public static function CartList($array = [], $CartKey = -1, $User = -2)
    {
        global $conf, $times, $date;
        $DB = SQL::DB();
        if ($User == -2) {
            $User = login_data::user_data();
        }
        if ($User === false) {
            if ($CartKey != '-1' && $CartKey != '' && strlen($CartKey) === 32) {
                $_COOKIE['CartKey'] = $CartKey;
            }
            if (!empty($_COOKIE['CartKey'])) {
                if (strlen($_COOKIE['CartKey']) !== 32) {
                    dies(-1, '购物车缓存参数异常,请手动清除Cookie！');
                }
                $CartData = $DB->get('cart', '*', [
                    'cookie' => (string)$_COOKIE['CartKey']
                ]);
                if (!$CartData) {
                    dies(-2, '购物车空空如也');
                }
            } else {
                dies(-2, '购物车空空如也');
            }
        } else {
            $CartData = $DB->get('cart', '*', [
                'uid' => (int)$User['id']
            ]);
            if (!$CartData) {
                dies(-2, '购物车空空如也');
            }
        }

        $DataArr = config::common_unserialize($CartData['content']);
        if (count($DataArr) === 0) {
            dies(-2, '购物车空空如也');
        }
        $DataGoods = [];
        $UnsetKey = []; //无效购物车参数标记
        foreach ($DataArr as $key => $v) {
            /**
             * 防止二次获取卡慢,建立函数缓存！
             */
            if ($v['num'] <= 0) {
                $v['num'] = 1;
            }
            $v['num'] -= 0;

            $Gid = 'GID' . $v['gid']; //商品信息
            $Count = 'Count' . $v['gid']; //同一商品下单总份数！
            $Fhuo = 'Fhuo' . $v['gid']; //发货模板信息
            $Seckill = 'Seckill' . $v['gid'];

            if (empty($$Gid)) {
                $$Gid = $DB->get('goods', '*', [
                    'gid' => (int)$v['gid'],
                    'state' => 1
                ]);
                if (!$$Gid) {
                    continue;
                }

                $v['num'] = ((!in_array(7, json_decode($$Gid['method'], TRUE))) ? 1 : $v['num']);

                $$Count = $v['num'];
                /**
                 * 计算出剩余下单数量！
                 * quota 今日剩余库存
                 * quantity 下单数量
                 */

                if ((int)$$Gid['freight'] !== -1) {
                    $$Fhuo = $DB->get('freight', '*', [
                        'id' => $$Gid['freight']
                    ]);
                } else {
                    $$Fhuo = [];
                }
                unset($price, $points, $atl);

                $$Seckill = $DB->get('seckill', '*', [
                    'end_time[>]' => $date,
                    'start_time[<]' => $date,
                    'gid' => (int)$v['gid']
                ]);
            } else {
                if (!$$Gid) {
                    continue;
                }
                $v['num'] = ((!in_array(7, json_decode($$Gid['method'], TRUE))) ? 1 : $v['num']);
                $$Count += $v['num']; //下单总份数
            }

            $Goods = $$Gid;

            $Seckills = $$Seckill; //商品折扣

            /**
             * 开始计算商品价格
             */
            if ((int)$Goods['specification'] === 1) {
                $Price = Price::Get($Goods['money'], $Goods['profits'], login_data::user_grade(($User === false ? -1 : $User['id'])), $Goods['gid'], $Goods['selling']);
            } else {
                $SpRule = RlueAnalysis($Goods, 1, $User);
                $SkuData = [
                    'data' => $SpRule,
                    'SPU' => json_decode($Goods['specification_spu'], TRUE),
                ];

                $KeyName = [];
                $SpuIn = 0; //初始化
                $InputArr = [];
                foreach ($SkuData['SPU'] as $val) {
                    $input = $v['input'][$SpuIn];
                    foreach ($v['input'] as $vs) {
                        if (in_array($vs, $val)) {
                            $input = $vs;
                        }
                    }
                    $InputArr[$SpuIn] = $input;
                    ++$SpuIn;
                }
                foreach ($InputArr as $vs) {
                    $KeyName[] = $vs;
                }
                $DataRule = $SkuData['data']['Parameter'][implode('`', $KeyName)];
                if (empty($DataRule)) {
                    $UnsetKey[] = $key;
                    continue;
                }
                $Goods += $DataRule;
                $Price = [
                    'price' => $Goods['price'],
                    'points' => $Goods['points']
                ];
            }

            if ($Seckills) {
                $attend = $DB->count('order', [
                        'gid' => $Goods['gid'],
                        'addtitm[>]' => $Seckills['start_time'],
                        'addtitm[<]' => $Seckills['end_time']
                    ]) - 0;
                if ($attend < $Seckills['astrict']) {
                    $Seckills = $Seckills['depreciate'] - 0;
                    $Price['price'] -= $Price['price'] * ($Seckills / 100);
                    $Price['points'] -= $Price['points'] * ($Seckills / 100);
                } else {
                    $Seckills = -1;
                }
            }

            $Price['points'] *= $v['num']; //根据原始价格计算兑换价

            if ((int)$Goods['freight'] !== -1) {
                $Price['price'] = Price::Freight($$Fhuo, $v['input'], $Price['price'], $v['num']);
            } else {
                $Price['price'] *= $v['num']; //根据原始价格计算售价
            }

            /**
             * 开始计算最大最小下单份数！
             */

            if ((int)$Goods['min'] <= 0) {
                $Goods['min'] = 1;
            }
            if ((int)$Goods['max'] <= 0) {
                $Goods['max'] = 9999999;
            }
            if ((int)$Goods['min'] > $v['num']) {
                $v['num'] = $Goods['min'];
            }
            if ($v['num'] > (int)$Goods['max']) {
                $v['num'] = $Goods['max'];
            }

            $v['GidData'] = [
                'gid' => (int)$Goods['gid'],
                'name' => $Goods['name'],
                'image' => ImageUrl(json_decode($Goods['image'], true)[0]),
                'price' => round($Price['price'], 8),
                'points' => round(($Price['points'] < 1 && $Price['points'] !== 0 ? 1 : $Price['points']), 0),
                'cid' => (int)$Goods['cid'],
                'count_type' => ((!in_array(7, json_decode($Goods['method'], TRUE))) ? -1 : 1),
                'min' => (int)$Goods['min'],
                'max' => (int)$Goods['max'],
                'quota' => (int)$Goods['quota'],
                'quantity' => (int)$Goods['quantity'],
                'Count' => $$Count,
                'communityId' => $Goods['deliver'],
                'method' => $Goods['method'],
                'step' => 1,
            ];
            unset($Goods, $Price);
            $DataGoods[] = $v;
        }

        self::CartDet($UnsetKey, 2);

        $DataAsrr = [];
        foreach ($DataGoods as $v) {
            $SCount = 'Count' . $v['gid'];
            $v['GidData']['Count'] = $$SCount;
            $DataAsrr[] = $v;
        }

        $Ap = 0; //免费商品总数

        if (!empty($array)) {
            $DataGoods = [];

            foreach (array_reverse($DataAsrr) as $key => $value) {
                if (!in_array($key, $array)) continue;
                if ($value['GidData']['price'] == 0 && $value['GidData']['points'] == 0) {
                    ++$Ap;
                }
                $DataGoods[] = $value;
            }
            $DataAsrr = array_reverse($DataGoods);
        }

        return [
            'code' => 1,
            'msg' => '购物车内共有' . count($DataAsrr) . '款有效商品!',
            'data' => array_reverse($DataAsrr),
            'count' => count($DataAsrr),
            'sitename' => $conf['sitename'],
            'Charge' => $Ap,
        ];
    }

    /**
     * @param $id
     * @param int $type
     * 移出购物车！删除
     */
    public static function CartDet($id, $type = 1)
    {
        $re = self::CartContent();
        if ($re == false) dies(-1, '操作失败,购物车空空如也!');
        $a = 0;
        $Data = [];

        foreach (array_reverse($re['data']) as $v) {
            if ($type == 1) {
                if ($a != $id) {
                    $Data[] = $v;
                }
            } else {
                if (!in_array($a, $id)) {
                    $Data[] = $v;
                }
            }
            ++$a;
        }
        $res = self::CartAddEdit(serialize(array_reverse($Data)), $re['id']);
        if ($res === true) {
            Hook::execute('CartDel', [
                'id' => $re['id'],
                'data' => $Data
            ]);
            if ($type === 1) {
                dies(1, '移出购物车成功！');
            } else return true;
        }

        if ($type === 1) {
            dies(1, '移出购物车失败！');
        } else {
            return false;
        }
    }

    /**
     * 取出购物车原始数据+数据ID
     */
    public static function CartContent()
    {
        $DB = SQL::DB();
        $User = login_data::user_data();
        if (!$User) {
            if (!empty($_COOKIE['CartKey'])) {
                if (strlen($_COOKIE['CartKey']) <> 32) dies(-1, '购物车缓存参数异常,请手动清除Cookie！');
                $CartData = $DB->get('cart', '*', [
                    'cookie' => (string)$_COOKIE['CartKey']
                ]);
                if (!$CartData) return false;
            } else return false;
        } else {
            $CartData = $DB->get('cart', '*', [
                'uid' => (int)$User['id']
            ]);
            if (!$CartData) return false;
        }
        $DataArr = config::common_unserialize($CartData['content']);

        return [
            'id' => $CartData['id'],
            'data' => $DataArr,
        ];
    }

    /**
     * @param string $Content
     * @param $id 购物车订单编号
     * 更新购物车数据！
     */
    public static function CartAddEdit($Content = '', $id)
    {
        $DB = SQL::DB();
        $Res = $DB->update('cart', [
            'content' => $Content
        ], [
            'id' => $id
        ]);
        if ($Res) {
            return true;
        }
        return false;
    }

    /**
     * @param $Data
     * 购物车商品下单数量调整
     */
    public static function CartNum($Data)
    {
        $DB = SQL::DB();
        if ((int)$Data['num'] <= 0) {
            dies(-1, '下单份数异常！');
        }
        $kid = (int)$Data['id'];
        $DataCart = self::CartContent();
        $Datas = [];
        $a = 0;
        foreach (array_reverse($DataCart['data']) as $v) {
            if ($kid == $a) {
                $data_v = $v;
                $Goods = $DB->get('goods', '*', ['gid' => (int)$v['gid'], 'state' => 1]);
                if (!$Goods) {
                    dies(-1, '商品不存在或已下架');
                }

                if (in_array(7, json_decode($Goods['method'], TRUE))) {
                    $Goods['min'] = 1;
                    $Goods['max'] = 1;
                }

                if (empty($Goods['min'])) {
                    $Goods['min'] = 1;
                }
                if (empty($Goods['max'])) {
                    $Goods['max'] = 1;
                }

                if ($Data['num'] < $Goods['min']) {
                    dies(-1, '当前商品最低购买' . $Goods['min'] . '份！');
                }

                if ($Data['num'] < $Goods['max']) {
                    dies(-1, '当前商品最多购买' . $Goods['max'] . '份！');
                }

                $price = $data_v['price'] / $data_v['num'];


                $Num = $Data['num'];
                $Pirc = round($price * $Num, 8);
                $data_v['num'] = $Num;
                $data_v['price'] = $Pirc;
                $Datas[] = $data_v;
            } else {
                $Datas[] = $v;
            }
            ++$a;
        }

        $res = self::CartAddEdit(serialize(array_reverse($Datas)), $DataCart['id']);
        if ($res) dies(1, '调整成功！');
        dies(-1, '调整失败！');
    }

    /**
     * @param $Data
     * 添加购物车
     */
    public static function CartAdd($Data)
    {
        global $conf;
        $DB = SQL::DB();
        $User = login_data::user_data();
        $Gid = (int)$Data['gid'];
        $Goods = $DB->get('goods', '*', [
            'gid' => $Gid,
            'state' => 1
        ]);

        if (!$Goods) {
            dies(-1, '商品不存在或已下架！');
        }

        if (!in_array(7, json_decode($Goods['method'], TRUE))) {
            $Data['num'] = 1;
        }

        $arr_blacklist = explode(',', $conf['blacklist']);
        foreach ($arr_blacklist as $v) {
            if (strpos(json_encode($Data['data']), $v) !== false) {
                dies(-1, '下单信息：' . $v . ' 已被站长设置为下单违禁词！');
            }
        }

        $Goods = Order::VerifyBuy($Goods['gid'], $Data['num'], $Data['data'], ($User === false ? -1 : $User));
        $Data['data'] = $Goods['InputData'];

        if ((int)$Goods['freight'] !== -1) {
            $freight = $DB->get('freight', '*', [
                'id' => $Goods['freight']
            ]);
            if ($freight) {
                $price = Price::Freight($freight, $Data['data'], $Goods['price'], $Data['num']);
            } else {
                $price = $Goods['price'] * $Data['num'];
            }
        } else {
            $price = $Goods['price'] * $Data['num'];
        }

        $price = round($price, 8);

        if (empty($Data['conts'])) {
            $Input = [];
            foreach (explode('|', $Goods['input']) as $value) {
                if (strpos($value, '{') !== false && strpos($value, '}') !== false) {
                    $Input[] = explode('{', $value)[0];
                } else {
                    $Input[] = $value;
                }
            }
            if ((int)$Goods['specification'] === 2) {
                $SpRule = RlueAnalysis($Goods, 3);
                if ($SpRule !== -1) {
                    $Input = array_merge($SpRule['MasterRule'], $Input);
                }
            }
            $is = 0;
            foreach ($Data['data'] as $v) {
                $Data['conts'] .= (empty($Input[$is]) ? '输入框' . ($is + 1) : $Input[$is]) . '：' . $v . ' ';
                ++$is;
            }
        }
        /**
         * 走cookie模式
         */
        if ($User === false) {
            if (!empty($_COOKIE['CartKey'])) {
                /**
                 * Cookie存在，必然未登录！
                 */
                if (strlen($_COOKIE['CartKey']) !== 32) {
                    dies(-1, '购物车缓存参数异常,请手动清除Cookie！');
                }
                $GetKey = $DB->get('cart', ['id', 'content'], [
                    'cookie' => (string)$_COOKIE['CartKey']
                ]);
                if (!$GetKey) {
                    $Key = md5(time() . userip() . random_int(1000, 9999999));
                    $Content = [[
                        'gid' => (int)$Data['gid'],
                        'input' => $Data['data'],
                        'num' => $Data['num'],
                        'conts' => $Data['conts'],
                        'price' => $price,
                    ]];
                    $re = self::CartEstablish(-1, $Key, serialize($Content));
                    if ($re) {
                        setcookie("CartKey", $Key, time() + 3600 * 24 * 30 * 12, '/');

                        Hook::execute('CartAdd', [
                            'gid' => (int)$Data['gid'],
                            'input' => $Data['data'],
                            'num' => $Data['num'],
                            'conts' => $Data['conts'],
                            'price' => $price,
                        ]);

                        dier([
                            'code' => 1,
                            'msg' => '已经成功添加到购物车!',
                            'count' => 1,
                        ]);
                    } else {
                        dies(-1, '购物车添加失败，请联系管理员处理！');
                    }
                }
                $DataArr = config::common_unserialize($GetKey['content']);
                $count = count($DataArr);

                if ($count === 0) {
                    $Content = [[
                        'gid' => (int)$Data['gid'],
                        'input' => $Data['data'],
                        'num' => $Data['num'],
                        'conts' => $Data['conts'],
                        'price' => $price,
                    ]];
                    $re = self::CartAddEdit(serialize($Content), $GetKey['id']);

                    if ($re) {
                        Hook::execute('CartAdd', [
                            'gid' => (int)$Data['gid'],
                            'input' => $Data['data'],
                            'num' => $Data['num'],
                            'conts' => $Data['conts'],
                            'price' => $price,
                        ]);
                        dier([
                            'code' => 1,
                            'msg' => '已经成功添加到购物车!',
                            'count' => 1,
                        ]);
                    } else {
                        dies(-1, '购物车添加失败，请联系管理员处理！');
                    }
                } else {
                    $DataArra = [];
                    $state = 1;
                    foreach ($DataArr as $value) {
                        /**
                         * 商品ID,下单信息一致
                         */
                        if ($value['gid'] == $Data['gid'] && $value['input'] == $Data['data'] && in_array(7, json_decode($Goods['method'], TRUE))) {
                            $state = 2;
                            $DataArra[] = [
                                'gid' => (int)$Data['gid'],
                                'input' => $Data['data'],
                                'num' => (int)$Data['num'] + (int)$value['num'],
                                'conts' => $Data['conts'],
                                'price' => $price + (float)$value['price'],
                            ];
                        } else {
                            $DataArra[] = $value;
                        }
                    }

                    if ($state === 1) {
                        $Content = array_merge($DataArr, [[
                            'gid' => (int)$Data['gid'],
                            'input' => $Data['data'],
                            'num' => $Data['num'],
                            'conts' => $Data['conts'],
                            'price' => $price,
                        ]]);
                    } else {
                        $Content = $DataArra;
                    }
                }

                $re = self::CartAddEdit(serialize($Content), $GetKey['id']);
                if ($re) {
                    Hook::execute('CartAdd', [
                        'gid' => (int)$Data['gid'],
                        'input' => $Data['data'],
                        'num' => $Data['num'],
                        'conts' => $Data['conts'],
                        'price' => $price,
                    ]);
                    dier([
                        'code' => 1,
                        'msg' => '已经成功添加到购物车!',
                        'count' => count($Content),
                    ]);
                } else {
                    dies(-1, '购物车添加失败，请联系管理员处理！');
                }
            } else {
                $Key = md5(time() . userip() . random_int(1000, 9999999));
                $Content = [[
                    'gid' => (int)$Data['gid'],
                    'input' => $Data['data'],
                    'num' => $Data['num'],
                    'conts' => $Data['conts'],
                    'price' => $price,
                ]];
                $re = self::CartEstablish(-1, $Key, serialize($Content));
                if ($re) {
                    setcookie("CartKey", $Key, time() + 3600 * 24 * 30 * 12, '/');
                    Hook::execute('CartAdd', [
                        'gid' => (int)$Data['gid'],
                        'input' => $Data['data'],
                        'num' => $Data['num'],
                        'conts' => $Data['conts'],
                        'price' => $price,
                    ]);
                    dier([
                        'code' => 1,
                        'msg' => '已经成功添加到购物车!',
                        'count' => 1,
                    ]);
                } else {
                    dies(-1, '购物车添加失败，请联系管理员处理！');
                }
            }
        } else {
            $GetUid = $DB->get('cart', '*', [
                'uid' => (int)$User['id']
            ]);
            if (!$GetUid) {
                /**
                 * 新插入
                 */
                $Content = [[
                    'gid' => (int)$Data['gid'],
                    'input' => $Data['data'],
                    'num' => $Data['num'],
                    'conts' => $Data['conts'],
                    'price' => $price,
                ]];
                $re = self::CartEstablish($User['id'], '', serialize($Content));
                if ($re == true) {
                    Hook::execute('CartAdd', [
                        'gid' => (int)$Data['gid'],
                        'input' => $Data['data'],
                        'num' => $Data['num'],
                        'conts' => $Data['conts'],
                        'price' => $price,
                    ]);
                    dier([
                        'code' => 1,
                        'msg' => '已经成功添加到购物车!',
                        'count' => 1,
                    ]);
                } else {
                    dies(-1, '购物车添加失败，请联系管理员处理！');
                }
            } else {
                /**
                 * 写入数据
                 */
                $DataArr = config::common_unserialize($GetUid['content']);
                $count = count($DataArr);
                if ($count === 0) {
                    $Content = [[
                        'gid' => (int)$Data['gid'],
                        'input' => $Data['data'],
                        'num' => $Data['num'],
                        'conts' => $Data['conts'],
                        'price' => $price,
                    ]];
                    $re = self::CartAddEdit(serialize($Content), $GetUid['id']);

                    if ($re) {
                        Hook::execute('CartAdd', [
                            'gid' => (int)$Data['gid'],
                            'input' => $Data['data'],
                            'num' => $Data['num'],
                            'conts' => $Data['conts'],
                            'price' => $price,
                        ]);

                        dier([
                            'code' => 1,
                            'msg' => '已经成功添加到购物车!',
                            'count' => 1,
                        ]);
                    } else dies(-1, '购物车添加失败，请联系管理员处理！');
                } else {
                    /**
                     * 若遇到相同的参数，则直接合并
                     */
                    $DataArra = [];
                    $state = 1;
                    foreach ($DataArr as $value) {
                        /**
                         * 商品ID,下单信息一致
                         */
                        if ($value['gid'] == $Data['gid'] && $value['input'] == $Data['data'] && in_array(7, json_decode($Goods['method'], TRUE))) {
                            $state = 2;
                            $DataArra[] = [
                                'gid' => (int)$Data['gid'],
                                'input' => $Data['data'],
                                'num' => (int)$Data['num'] + (int)$value['num'],
                                'conts' => $Data['conts'],
                                'price' => $price + (float)$value['price'],
                            ];
                        } else {
                            $DataArra[] = $value;
                        }
                    }

                    if ($state === 1) {
                        $Content = array_merge($DataArr, [[
                            'gid' => (int)$Data['gid'],
                            'input' => $Data['data'],
                            'num' => $Data['num'],
                            'conts' => $Data['conts'],
                            'price' => $price,
                        ]]);
                    } else {
                        $Content = $DataArra;
                    }

                    $re = self::CartAddEdit(serialize($Content), $GetUid['id']);
                    if ($re) {
                        Hook::execute('CartAdd', [
                            'gid' => (int)$Data['gid'],
                            'input' => $Data['data'],
                            'num' => $Data['num'],
                            'conts' => $Data['conts'],
                            'price' => $price,
                        ]);
                        dier([
                            'code' => 1,
                            'msg' => '已经成功添加到购物车!',
                            'count' => count($Content),
                        ]);
                    } else {
                        dies(-1, '购物车添加失败，请联系管理员处理！');
                    }
                }
            }
        }
    }

    /**
     * @param int $Uid 用户ID
     * @param null $CartKey 缓存ID
     * @param array $Content 商品序列化信息！
     * 创建购物车订单,新增！
     */
    public static function CartEstablish($Uid = -1, $CartKey = null, $Content = '')
    {
        global $date;
        $DB = SQL::DB();
        $Res = $DB->insert('cart', [
            'uid' => $Uid,
            'cookie' => $CartKey,
            'content' => $Content,
            'ip' => userip(),
            'addtime' => $date
        ]);
        if ($Res) {
            return true;
        }
        return false;
    }

    /**
     * @return int
     * 获取当前用户购物车商品数量
     */
    public static function CartCount(): int
    {
        $DB = SQL::DB();
        $User = login_data::user_data();
        if ($User === false) {
            if (empty($_COOKIE['CartKey'])) {
                return 0;
            }
            if (strlen($_COOKIE['CartKey']) !== 32) {
                dies(-1, '购物车缓存参数异常,请手动清除Cookie！');
            }
            $re = $DB->get('cart', ['content'], [
                'cookie' => (string)$_COOKIE['CartKey']
            ]);
        } else {
            $re = $DB->get('cart', ['content'], [
                'uid' => (int)$User['id']
            ]);
        }
        if (!$re) {
            return 0;
        }
        $DataArr = config::common_unserialize($re['content']);
        return count($DataArr);
    }

    /**
     * @param $Uid 用户编号
     * 将未登录用户购物车的缓存数据，添加到已登陆用户中！
     */
    public static function UserCookieDer($Uid)
    {
        $DB = SQL::DB();
        if (!empty($_COOKIE['CartKey'])) {
            $Cookey = $_COOKIE['CartKey'];
            setcookie("CartKey", null, time() - 3600 * 24 * 30 * 12, '/');
            if (strlen($Cookey) <> 32) dies(-1, '购物车缓存参数异常,请手动清除Cookie！');
            $res = $DB->get('cart', '*', [
                'cookie' => (string)$Cookey
            ]);
            if (!$res) return false;
            $re = $DB->get('cart', '*', [
                'uid' => (int)$Uid
            ]);
            if ($re) {
                $DataArr1 = config::common_unserialize($res['content']);
                $DataArr2 = config::common_unserialize($re['content']);
                $DataArr3 = array_merge($DataArr2, $DataArr1);
                self::CartAddEdit(serialize($DataArr3), $re['id']);
                $DB->delete('cart', [
                    'id' => $res['id']
                ]);
                return true;
            } else {
                $DB->update('cart', [
                    'uid' => $Uid,
                    'cookie' => ''
                ], [
                    'cookie' => $Cookey
                ]);
                return true;
            }
        } else return false;
    }
}
