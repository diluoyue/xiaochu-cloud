<?php

/**
 * Author：晴玖天
 * Creation：2020/4/22 13:32
 * Filename：Order.php
 * 订单操作类
 */

namespace lib\supply;


use CookieCache;
use extend\GoodsCart;
use extend\SMS;
use extend\UserConf;
use lib\Hook\Hook;
use lib\Pay\Pay;
use login_data;
use Medoo\DB\SQL;

class Order
{
    private static $User; //用户信息

    /**
     * @param $PayOrder //支付订单号
     * @param $Data //支付回调信息
     * @param $Type =2则返回show_msg信息
     */
    public static function SubmitPay($Queue, $Data, $Type = 1)
    {
        global $date;
        $DB = SQL::DB();
        $Goods = $DB->get('goods', '*', ['gid' => (int)$Queue['gid']]);
        if (!$Goods) return ['code' => -1, 'msg' => '此订单商品不存在,无法提交至服务器,请联系管理员处理！'];

        $Sku = json_decode($Queue['input'], TRUE);

        if ((int)$Goods['specification'] === 2) {
            $SpRule = RlueAnalysis($Goods, 1);
            $SkuData = [
                'data' => $SpRule,
                'SPU' => json_decode($Goods['specification_spu'], TRUE),
            ];

            $KeyName = [];
            $SpuIn = 0; //初始化
            $InputArr = [];
            foreach ($SkuData['SPU'] as $val) {
                $input = $Sku[$SpuIn];
                foreach ($Sku as $v) {
                    if (in_array($v, $val)) {
                        $input = $v;
                    }
                }
                $InputArr[$SpuIn] = $input;
                ++$SpuIn;
            }
            foreach ($InputArr as $v) {
                $KeyName[] = $v;
            }

            $DataRule = $SkuData['data']['Parameter'][implode('`', $KeyName)];
            if (empty($DataRule)) {
                dies(-1, '商品规格选择错误,请选择正确的参数!');
            }
            $Goods = array_merge($Goods, $DataRule);
        }

        $Queue['InitialPrice'] = $Queue['price']; //原始价格
        $Queue['Coupon'] = $Queue['coupon']; //优惠券id

        $Goods['price'] = $Queue['money'];
        $Queue['price'] = $Queue['money'];
        $Goods['points'] = $Queue['money'];
        $Goods['num'] = $Queue['num'];

        $Queue['money'] = $Goods['money'] * $Queue['num'];
        $Queue['Goods'] = $Goods;
        $Queue['DataBuy'] = [
            'gid' => $Goods['gid'],
            'num' => $Queue['num'],
            'data' => $Sku,
        ];

        $Queue['payment'] = $Queue['type'];
        $Queue['trade_no'] = $Data['trade_no'];
        $Queue['order'] = date('YmdHis') . rand(11111, 99999);

        if ($Queue['uid'] != -1) {
            $User = $DB->get('user', '*', ['id' => (int)$Queue['uid']]);
        } else $User = false;

        $ID = self::Creation(false, $User, $Queue, $Type);
        if ($ID <> false) {
            $re = $DB->update('pay', [
                'state' => 1,
                'trade_no' => $Data['trade_no'],
                'endtime' => $date,
                'oid' => $ID,
            ], ['id' => $Queue['id']]);

            if ($re) {
                userlog('在线购买', '用户[' . $Queue['uid'] . ']在' . $date . '在线支付了' . $Queue['price'] . '元购买了商品[' . $Goods['name'] . ']！', $Queue['uid'], $Queue['price']);
                $Pids = $DB->get('pay', '*', ['id' => (int)$Queue['id']]);
                Hook::execute('PaySuccess', [
                    'PayOrder' => $Pids
                ]);

                return [
                    'code' => 1,
                    'msg' => '商品[' . $Goods['name'] . ']购买成功!',
                    'id' => $ID
                ];
            }

            return [
                'code' => -1,
                'msg' => '订单提交失败,无法提交到服务器!',
            ];
        }

        return [
            'code' => -1,
            'msg' => '订单提交失败,无法提交到服务器,商品对接发生异常!',
        ];
    }

    /**
     * @param $DataBuy //下单信息
     * 下单信息处理模块
     * 下单验证，在线付款链接生成，
     */
    public static function Creation($DataBuy, $User, $Queue = false, $Type = 1)
    {
        global $conf, $date, $times;
        $DB = SQL::DB();
        self::$User = $User;
        if ($Queue === false) {
            if ($User === false && (int)$DataBuy['type'] !== 1) {
                dies(-2, '请先登陆');
            }
            $Goods = self::VerifyBuy($DataBuy['gid'], $DataBuy['num'], $DataBuy['data'], $User, $DataBuy);
            $DataBuy['data'] = $Goods['InputData'];
            $Money = $Goods['money'] * $Goods['num'];

            if (!empty($DataBuy['CouponId']) && $DataBuy['CouponId'] == -2 && $User['id'] >= 1 && ($DataBuy['type'] == 1 || $DataBuy['type'] == 2)) {
                /**
                 * 自动选择最优优惠券!
                 */
                $PirceCou = ($Goods['price'] * $Goods['num']);
                $CouponList = $DB->select('coupon', '*', [
                    'OR' => [
                        'apply' => 3,
                        'gid' => $Goods['gid'],
                        'cid' => $Goods['cid'],
                    ],
                    'oid' => -1,
                    'uid' => $User['id'],
                ]);
                $Coupon = [];
                foreach ($CouponList as $v) {
                    switch ($v['apply']) {
                        case 1:
                            if ($Goods['gid'] != $v['gid']) {
                                $CouponId = -1;
                            }
                            break;
                        case 2:
                            if ($Goods['cid'] != $v['cid']) {
                                $CouponId = -1;
                            }
                            break;
                    }
                    if ($CouponId === -1) continue;

                    if ($v['term_type'] == 1) {
                        $TIME = strtotime($v['gettime']) + (60 * 60 * 24 * $v['indate']);
                    } else {
                        $TIME = strtotime($v['expirydate']);
                    }
                    if (time() > $TIME) continue;
                    switch ($v['type']) {
                        case 1:
                            if ($v['minimum'] > $PirceCou) $CouponId = -1;
                            $PriceCou = $PirceCou - $v['money'];
                            break;
                        case 2:
                            $PriceCou = $PirceCou - $v['money'];
                            break;
                        case 3:
                            if ($v['minimum'] > $PirceCou) $CouponId = -1;
                            $PriceCou = ($PirceCou * ($v['money'] / 100));
                            break;
                    }
                    if ($CouponId === -1) continue;

                    $CSQL = [
                        'uid' => $User['id'],
                        'oid[!]' => -1,
                        'endtime[>]' => $times
                    ];
                    if ($conf['CouponUseIpType'] == 1) {
                        $CSQL = [
                            'OR' => [
                                'uid' => $User['id'],
                                'ip' => userip(),
                            ],
                            'oid[!]' => -1,
                            'endtime[>]' => $times
                        ];
                    }
                    $Count = $DB->count('coupon', $CSQL);
                    if ($Count >= $conf['CouponUsableMax']) {
                        continue;
                    }

                    if ($conf['CouponMinimumType'] == 1) {
                        if ($PriceCou <= ($Goods['money'] * $Goods['num'])) {
                            $PriceCou = ($Goods['money'] * $Goods['num']);
                        }
                    } else {
                        if ($PriceCou <= 0) {
                            $PriceCou = 0;
                        }
                    }
                    $Coupon[] = [
                        'id' => $v['id'],
                        'Price' => round($PriceCou, 2),
                    ];
                }

                if (count($Coupon) >= 1) {
                    $Coupon = array_sort($Coupon, 'Price');
                    $DataBuy['CouponId'] = $Coupon[0]['id'];
                } else {
                    $DataBuy['CouponId'] = -1;
                }
            }

            $DataBuy['num'] = $Goods['num'];
            if ($conf['OredeQueue'] == 2 && $DataBuy['Api'] <> 1) {
                if ($DataBuy['type'] == 2 || $DataBuy['type'] == 3 || $DataBuy['type'] == 4) {
                    if ($User == false) dies(-1, '请先完成登陆');
                    if ($User['state'] != 1) dies(-1, '当前账户已被禁封，无法进行购买！');
                }
                GoodsCart::OredeQueue($DataBuy, $Goods, ($User == false ? -1 : $User['id']));
                return true;
            }
        } else {
            $Goods = $Queue['Goods'];
        }

        /**
         * 支付购买商品
         * 运费模板只对在线下单和余额付款开放！
         */

        if ($Queue == false) {
            if ($Goods['price'] == 0 && $Goods['points'] == 0) {
                $DataBuy['type'] = 4;
            } else {
                if ((int)$DataBuy['type'] > 3 || (int)$DataBuy['type'] < 1) dies(-1, '数据异常！');
            }
        } else {
            $DataBuy['type'] = 5;
        }

        switch ((int)$DataBuy['type']) {
            case 1: //在线付款
                if (!in_array(1, json_decode($Goods['method'], TRUE))) {
                    dies(-1, '当前商品未开启在线付款购买方式，请使用其他付款方式购买！');
                }

                $Price = $Goods['price'];
                if ($Goods['freight'] != -1) {
                    $Fre = $DB->get('freight', '*', [
                        'id' => $Goods['freight'],
                        'LIMIT' => 1,
                    ]);
                    if ($Fre) {
                        $Price = Price::Freight($Fre, $DataBuy['data'], $Price, $DataBuy['num']);
                    }
                } else {
                    $Price = $Price * $DataBuy['num'];
                }
                if ((float)$Price < 0.01) $Price = 0.01;

                $Goods['price'] = $Price;

                /**
                 * 调用支付模块
                 */

                $Res = Pay::PrepaidPhoneOrders([
                    'type' => $DataBuy['mode'],
                    'uid' => ($User == false ? -1 : $User['id']),
                    'gid' => $DataBuy['gid'],
                    'input' => $DataBuy['data'],
                    'num' => $DataBuy['num'],
                    'CouponId' => (empty($DataBuy['CouponId']) ? -1 : $DataBuy['CouponId']),
                ], $Goods);
                dier($Res);
                break;
            case 2: //余额付款
                if (!in_array(2, json_decode($Goods['method'], TRUE))) {
                    dies(-1, '当前商品未开启余额付款购买方式！，请使用其他付款方式购买！');
                }
                if ($User['state'] != 1) dies(-1, '当前账号已被禁封，无法进行购买！');

                if ((int)$Goods['freight'] !== -1) {
                    $Fre = $DB->get('freight', '*', [
                        'id' => $Goods['freight'],
                        'LIMIT' => 1,
                    ]);
                    if ($Fre) {
                        $Price = Price::Freight($Fre, $DataBuy['data'], $Goods['price'], $DataBuy['num']);
                    } else $Price = $Goods['price'] * $DataBuy['num'];
                } else $Price = $Goods['price'] * $DataBuy['num'];

                /**
                 * 验证优惠券！
                 */
                if (!empty($DataBuy['CouponId']) && $DataBuy['CouponId'] != -1 && $User['id'] >= 1) {
                    $Coupon = $DB->get('coupon', '*', ['id' => (int)$DataBuy['CouponId'], 'uid' => (int)$User['id'], 'oid' => -1]);
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
                        'uid' => $User['id'],
                        'oid[!]' => -1,
                        'endtime[>]' => $times
                    ];
                    if ($conf['CouponUseIpType'] == 1) {
                        $CSQL = [
                            'OR' => [
                                'uid' => $User['id'],
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
                            if ($Coupon['minimum'] > $Price) dies(-1, '此优惠券订单付款金额需满' . $Coupon['minimum'] . '元才可使用！');
                            $PriceCou = $Price - $Coupon['money'];
                            break;
                        case 2:
                            $PriceCou = $Price - $Coupon['money'];
                            break;
                        case 3:
                            if ($Coupon['minimum'] > $Price) dies(-1, '此优惠券订单付款金额需满' . $Coupon['minimum'] . '元才可使用！');
                            $PriceCou = ($Price * ($Coupon['money'] / 100));
                            break;
                    }

                    if ($conf['CouponMinimumType'] == 1) {
                        if ($PriceCou <= $Money) {
                            $PriceCou = $Money;
                        }
                    } else {
                        if ($PriceCou <= 0) {
                            $PriceCou = 0;
                        }
                    }

                    $InitialPrice = $Price;
                    $CouponId = $DataBuy['CouponId'];
                    $Price = $PriceCou;
                } else {
                    $InitialPrice = -1;
                    $CouponId = -1;
                }

                $UserMoney = login_data::UserMoney($User['id']);
                if (!$UserMoney) {
                    dies(-1, '用户状态异常，无法完成购买！');
                }

                if ($UserMoney['money'] < $Price) {
                    dies(-1, '余额不足,还差' . ($Price - $UserMoney['money']) . '元！');
                }
                if (($UserMoney['money'] - $Price) < 0) {
                    dies(-1, '余额异常,请充值再购买！');
                }

                $Deduct = $DB->update('user', [
                    'money[-]' => $Price,
                ], [
                    'id' => $User['id'],
                ]);

                if ($Deduct) {
                    userlog('余额购买', '用户[' . $User['id'] . ']在' . $date . '用' . $Price . '元余额购买了商品[' . $Goods['name'] . ']！', $User['id'], $Price);
                    $OrData = self::OrderAwait($InitialPrice, $CouponId, '余额付款', '余额付款,无支付订单', $User['id'], $DataBuy, $Price, $Money, false, 1);
                    if ($OrData == false) {
                        userlog('订单创建失败', '用户[' . $User['id'] . ']在' . $date . '用' . $Price . '元余额购买了商品[' . $Goods['name'] . ']，购买失败' . $Price . '元余额未补款到账户内,请手动补款！', $User['id'], $Price);
                        dies(-1, '订单创建失败,若已扣款,请联系客服补款');
                    }
                    return self::OrderSubmit($OrData, $Goods);
                } else dies(-1, '支付失败,无法完成扣款,请联系管理员处理！');
                break;
            case 3: //积分兑换
                if (!in_array(3, json_decode($Goods['method'], TRUE))) {
                    dies(-1, '当前商品未开启积分兑换购买方式！，请使用其他付款方式购买！');
                }

                if ($User['state'] != 1) dies(-1, '当前账号已被禁封，无法进行购买！');

                if ($DataBuy['CouponId'] != -1) dies(-1, '优惠券不可用于' . $conf['currency'] . '兑换,仅可用于在线支付或余额付款！');

                $Day = $DB->count('journal', [
                    'name' => ['积分兑换', '免费领取'],
                    'uid' => $User['id'],
                    'date[>]' => $times,
                ]);

                if ($Day >= $conf['getinreturn']) dies(-1, '您今日积分兑换商品次数已达上限,请明天再来或直接付款购买,次日0点更新额度！,您今日最多可兑换' . $conf['getinreturn'] . '次商品哦！');

                $Day = $DB->count('journal', [
                    'name' => ['积分兑换', '免费领取'],
                    'date[>]' => $times,
                ]);

                if ($Day >= $conf['getinreturn_all']) dies(-1, '本站今日积分兑换商品次数已达上限,请明天再来或直接付款购买,次日0点更新额度！,本站今日提供了' . $conf['getinreturn_all'] . '次商品兑换次数！');

                $Price = $Goods['points'] * $DataBuy['num'];

                $UserMoney = login_data::UserMoney($User['id']);
                if (!$UserMoney) {
                    dies(-1, '用户状态异常，无法完成购买！');
                }

                if ($UserMoney['currency'] < $Price) dies(-1, $conf['currency'] . '不足,还差' . ($Price - $UserMoney['currency']) . $conf['currency'] . '！');
                if (($UserMoney['currency'] - $Price) < 0) dies(-1, $conf['currency'] . '异常！');

                $Deduct = $DB->update('user', [
                    'currency[-]' => $Price,
                ], [
                    'id' => $User['id'],
                ]);

                if ($Deduct) {
                    userlog('积分兑换', '用户[' . $User['id'] . ']在' . $date . '用' . $Price . $conf['currency'] . '兑换了商品[' . $Goods['name'] . ']！', $User['id'], $Price);
                    $OrData = self::OrderAwait(-1, -1, '积分兑换', '积分兑换,无支付订单', $User['id'], $DataBuy, $Price, $Money, false, 1);
                    if ($OrData == false) {
                        userlog('订单创建失败', '用户[' . $User['id'] . ']在' . $date . '用' . $Price . $conf['currency'] . '兑换了商品[' . $Goods['name'] . ']，购买失败' . $Price . $conf['currency'] . '未补款到账户内,请手动补款！', $User['id'], $Price);
                        dies(-1, '订单创建失败,若已扣款,请联系客服补款');
                    }
                    return self::OrderSubmit($OrData, $Goods);
                } else dies(-1, '兑换失败,无法完成扣款,请联系管理员处理！');
                break;
            case 4: //每份领取
                /**
                 * 免费商品
                 */
                if ($User == false) dies(-2, '领取免费商品需要先登陆用户后台哦');
                if ($User['state'] != 1) dies(-1, '当前账号已被禁封，无法进行购买！');
                userlog('免费领取', '用户[' . $User['id'] . ']在' . $date . '免费领取了商品[' . $Goods['name'] . ']！', $User['id'], 0);
                $OrData = self::OrderAwait(-1, -1, '免费领取', '免费商品,无支付订单', $User['id'], $DataBuy, 0, $Money);
                if ($OrData == false) dies(-1, '订单创建失败,无法领取此商品！');
                return self::OrderSubmit($OrData, $Goods);
            case 5:
                /**
                 * 直接下单
                 */
                $OrData = self::OrderAwait($Queue['InitialPrice'], $Queue['Coupon'], $Queue['payment'], $Queue['trade_no'], $Queue['uid'], $Queue['DataBuy'], $Queue['price'], $Queue['money'], $Queue['order'], false, $Type);

                if ($OrData == false) dies(-1, '订单创建失败,无法提交！');
                return self::OrderSubmit($OrData, $Goods);
            default:
                dies(-1, '未知付款方式！');
                break;
        }
    }

    /**
     * @param $Gid //商品ID
     * @param $num //下单数量
     * @param $InputData //下单信息
     * 下单参数验证模块
     */
    public static function VerifyBuy($Gid, $num, $InputData, $User = -1, $DataRequest = false)
    {
        global $conf, $times, $date;

        $arr_blacklist = explode(',', $conf['blacklist']);
        $idd = 1;
        foreach ($InputData as $key => $v) {
            if (empty($v)) {
                dies(-1, '请将第' . trim($idd) . '行输入框填写完整！');
            }
            $InputData[$key] = $v;
            ++$idd;
        }
        foreach ($arr_blacklist as $v) {
            if (empty($v)) continue;
            if (strpos(json_encode($InputData), $v) !== false) {
                dies(-1, '下单信息：' . $v . ' 已被站长设置为下单违禁词！');
            }
        }

        $DB = SQL::DB();
        $Goods = $DB->get('goods', '*', ['gid' => (int)$Gid, 'state' => 1]);
        if (!$Goods) dies(-1, '商品不存在或已下架！');

        if ($DataRequest !== false) {
            //验证付款方式
            $PayType = $DataRequest['type'];
            $methods = json_decode($Goods['method'], true);
            if ($PayType === 2 && !in_array(2, $methods)) {
                dies(-1, '此商品不支持使用余额购买！');
            } else if ($PayType === 3 && !in_array(3, $methods)) {
                dies(-1, '此商品不支持使用积分兑换！');
            }
            /**
             * 验证分类付款支持
             */
            $Class = $DB->get('class', ['support'], [
                'cid' => (int)$Goods['cid']
            ]);
            if ($Class) {
                $support = explode(',', $Class['support']);
                if ($PayType === 2 && $support[3] != 1) {
                    dies(-1, '此商品不支持使用余额购买！');
                } else if ($PayType === 3 && $support[4] != 1) {
                    dies(-1, '此商品不支持使用积分兑换！');
                }
            }
        }

        if ($num < 1 || !in_array(7, json_decode($Goods['method'], TRUE))) {
            $num = 1;
        }

        if ((int)$Goods['specification'] === 2) {
            $SpRule = RlueAnalysis($Goods, 1, $User);
            $SkuData = [
                'data' => $SpRule,
                'SPU' => json_decode($Goods['specification_spu'], TRUE),
            ];

            $KeyName = [];
            $SpuIn = 0; //初始化
            $InputArr = [];
            foreach ($SkuData['SPU'] as $val) {
                $input = $InputData[$SpuIn];
                foreach ($InputData as $v) {
                    if (in_array($v, $val)) {
                        $input = $v;
                    }
                }
                $InputArr[$SpuIn] = $input;
                ++$SpuIn;
            }
            $InputDataArray = $InputData;
            foreach ($InputArr as $k => $v) {
                $InputDataArray[$k] = $v;
                $KeyName[] = $v;
            }
            $InputData = $InputDataArray;
            $DataRule = $SkuData['data']['Parameter'][implode('`', $KeyName)];
            if (empty($DataRule)) {
                dies(-1, '商品规格选择错误,请选择正确的参数!');
            }
            $Goods = array_merge($Goods, $DataRule);
        } else {
            $Pricer = Price::Get($Goods['money'], $Goods['profits'], ($User === false ? -1 : $User['grade']), $Goods['gid'], $Goods['selling']);
            $Goods['price'] = $Pricer['price'];
            $Goods['points'] = $Pricer['points'];
        }

        $Goods['InputData'] = $InputData;

        if ((int)$Goods['deliver'] === 3) {
            $Goods['quota'] = $DB->count('token', [
                    'uid' => 1,
                    'gid' => $Gid
                ]) - 0;
            if ($Goods['quota'] < $num) {
                dies(-1, $Goods['name'] . ' - 卡密库存不足' . $num . '份,' . ($num <= 1 ? '无法购买' : '请减少购买份数') . '！');
            }
        } else if ((int)$Goods['quota'] <= 0) {
            dies(-1, '商品:' . $Goods['name'] . '，库存不足，无法完成购买！');
        }

        if (((int)$Goods['quota'] + $num) <= 0) {
            dies(-1, '商品:' . $Goods['name'] . '，库存不足' . $num . '份,请减少购买份数！');
        }

        if ((float)$Goods['price'] <= 0 || (($DataRequest !== false && (float)$Goods['points'] <= 0) || (int)$DataRequest['type'] === 3)) {
            /**
             * 此处验证免费商品和积分兑换商品
             */
            if ($User === false) {
                dies(-1, '免费/兑换类商品必须登陆才可领取或兑换！');
            }
            if ((int)$User['state'] !== 1) {
                dies(-1, '当前账号已被禁封，无法领取或兑换此商品！');
            }
            $Day = $DB->count('journal', [
                'name' => ['积分兑换', '免费领取'],
                'uid' => $User['id'],
                'date[>]' => $times,
            ]);
            if ($Day > (int)$conf['getinreturn']) {
                dies(-1, '您今日可领取或兑换商品次数已达上限,请明天再来或看看其他付费商品,次日0点更新购买额度！,您今日最多可领取或兑换' . $conf['getinreturn'] . '次商品！');
            }
            $Day = $DB->count('journal', [
                'name' => ['积分兑换', '免费领取'],
                'date[>]' => $times,
            ]);
            if ($Day > (int)$conf['getinreturn_all']) {
                dies(-1, '本站今日可免费领取或兑换的商品额度已经耗尽,请明天再来或看看其他付费商品,次日0点刷新额度！,本站今日提供了' . $conf['getinreturn_all'] . '次商品领取/兑换次数！');
            }
        }

        if ((int)$Goods['min'] <= 0) {
            $Goods['min'] = 1;
        }
        if ((int)$Goods['max'] <= 0) {
            $Goods['max'] = 9999999;
        }
        if ((int)$Goods['min'] > $num) {
            dies(-1, '当前商品最低购买' . $Goods['min'] . '份！');
        }
        if ($num > (int)$Goods['max']) {
            dies(-1, '当前商品最多购买' . $Goods['max'] . '份！');
        }

        $Goods['num'] = $num;


        /**
         * 计算商品折扣
         */
        $Seckill = $DB->get('seckill', '*', [
            'end_time[>]' => $date,
            'start_time[<]' => $date,
            'gid' => (int)$Gid
        ]);
        if ($Seckill) {
            $attend = $DB->count('order', [
                    'gid' => $Gid,
                    'addtitm[>]' => $Seckill['start_time'],
                    'addtitm[<]' => $Seckill['end_time']
                ]) - 0;
            if ($attend < $Seckill['astrict']) {
                $Seckill = $Seckill['depreciate'] - 0;
                $Goods['price'] -= $Goods['price'] * ($Seckill / 100);
                $Goods['points'] -= $Goods['points'] * ($Seckill / 100);
                $Goods['Seckill'] = $Seckill;
            } else {
                $Goods['Seckill'] = -1;
            }
        } else {
            $Goods['Seckill'] = -1;
        }
        return $Goods;
    }

    /**
     * @param $InitialPrice //原始订单金额
     * @param $Coupon //优惠券id，仅在优惠券id不在-1时生效！
     * @param $payment //支付方式
     * @param $trade_no //支付订单
     * @param $Uid //下单用户
     * @param $DataBuy //下单信息
     * @param $Price //商品金额
     * @param $Money //商品成本
     * @param $OrderID //生成指定的商品订单号
     * 创建待处理订单，内置重复提交验证模块
     */
    public static function OrderAwait($InitialPrice = -1, $Coupon = -1, $payment, $trade_no, $Uid, $DataBuy, $Price, $Money, $OrderID = false, $Type = false, $TypeMsg = 1)
    {
        global $date, $conf;
        $DB = SQL::DB();
        if ($OrderID == false) {
            $OrderNumber = date('YmdHis') . rand(11111, 99999);
        } else $OrderNumber = $OrderID;
        mkdirs(ROOT . 'includes/extend/log/Astrict/');
        $Flie = ROOT . 'includes/extend/log/Astrict/Order_' . md5(json_encode($DataBuy['data']) . $DataBuy['gid'] . $DataBuy['num']) . '.log';
        if (file_exists($Flie)) {
            $tiem = (float)file_get_contents($Flie);
            if ((time() - $tiem) < $conf['OrderAstrict']) {
                $Sels = $DB->select('order', ['id'], [
                    'order' => $OrderNumber
                ]);
                if (count($Sels) >= 1) {
                    $re = $DB->update('queue', [
                        'type' => 1,
                        'endtime' => $date,
                        'remark' => '您的商品已经成功提交到服务器!',
                    ], ['id' => $Sels[0], 'order' => $OrderNumber]);
                    $DB->delete('queue', ['order' => $OrderNumber, 'id[!]' => $Sels[0]]);
                    if ($re) {
                        self::PriceRefund($payment, $Type, $Uid, $Price, $DataBuy, '当前订单已经提交到服务器了,请勿重复提交哦!');
                    }
                    self::PriceRefund($payment, $Type, $Uid, $Price, $DataBuy, '当前订单已经提交到服务器了,请勿重复提交哦!');
                    if ($TypeMsg == 2) {
                        show_msg('温馨提示', '商品已经付款成功！', 1, ROOT_DIR_S . '/?mod=route&p=Order');
                    } else if ($TypeMsg == 3) {
                        die('success');
                    } else {
                        dies(1, '当前订单已经提交到服务器了,请勿重复提交哦!');
                    }
                }

                self::PriceRefund($payment, $Type, $Uid, $Price, $DataBuy, '重复订单有提交间隔期 ，请于' . ($conf['OrderAstrict'] - (time() - $tiem)) . '秒后再提交!', -1);

                if ($TypeMsg == 2) {
                    show_msg('温馨提示', '商品已经付款成功！', 1, ROOT_DIR_S . '/?mod=route&p=Order');
                } else if ($TypeMsg == 3) {
                    die('success');
                } else dies(-1, '重复下单数据有提交间隔期 ,请于' . ($conf['OrderAstrict'] - (time() - $tiem)) . '秒后再提交!');
            }
            @file_put_contents($Flie, time());
        } else {
            @file_put_contents($Flie, time());
        }

        $Uty = UserConf::judge();

        $Order = $DB->insert('order', [
            'order' => $OrderNumber,
            'trade_no' => $trade_no,
            'uid' => $Uid,
            'muid' => (!$Uty ? -1 : $Uty['id']),
            'ip' => userip(),
            'input' => json_encode($DataBuy['data'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'state' => 2,
            'num' => $DataBuy['num'],
            'return' => '用户已付款,订单尚未处理！',
            'gid' => $DataBuy['gid'],
            'money' => $Money,
            'originalprice' => $InitialPrice,
            'coupon' => $Coupon,
            'payment' => $payment,
            'price' => $Price,
            'docking' => 3,
            'addtitm' => $date,
        ]);
        $ID = $DB->id();
        if ($Order) {
            $Goods = $DB->get('goods', '*', ['gid' => $DataBuy['gid']]);
            self::ReduceStocks($Goods, $DataBuy['num'], $ID, $DataBuy['data']);
            if ($Coupon != -1) {
                $CouponRes = $DB->update('coupon', [
                    'oid' => $ID,
                    'ip' => userip(),
                    'endtime' => $date,
                ], [
                    'id' => $Coupon
                ]);
                userlog('使用优惠券', '用户[' . $Uid . ']使用优惠券[' . $Coupon . ']购买了商品[' . $Goods['name'] . ']！,商品原价[' . round($InitialPrice, 8) . '元],优惠后价格为[' . round($Price, 8) . '元],共优惠[' . round($InitialPrice - $Price, 8) . '元],优惠券使用后若订单退款不退还！', $Uid, round($InitialPrice - $Price, 8));
                if (!$CouponRes) {
                    //保险措施，优惠券状态调整失败，直接删除
                    $DB->delete('coupon', ['id' => $Coupon]);
                }
            }

            Hook::execute('OrderAdd', [
                'order' => $OrderNumber,
                'trade_no' => $trade_no,
                'uid' => $Uid,
                'ip' => userip(),
                'input' => json_encode($DataBuy['data'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'state' => 2,
                'num' => $DataBuy['num'],
                'return' => '用户已付款,订单尚未处理！',
                'gid' => $DataBuy['gid'],
                'money' => $Money,
                'payment' => $payment,
                'price' => $Price,
                'originalprice' => $InitialPrice,
                'coupon' => $Coupon,
                'addtitm' => $date,
                'name' => $Goods['name']
            ]);

            return [
                'id' => $ID,
                'order' => $OrderNumber,
            ];
        }

        return false;
    }

    /**
     * @param $payment 类型
     * @param $Type 判断
     * @param $Uid 用户ID
     * @param $Price 退款金额
     * @param $DataBuy 信息
     * @param $Msg 返回信息
     *
     */
    public static function PriceRefund($payment = false, $Type = false, $Uid = -1, $Price = 0, $DataBuy = [], $Msg = '', $Code = 1)
    {
        global $date;
        $DB = SQL::DB();
        if ($payment == '余额付款' || ($payment == '积分兑换' && $Type)) {
            userlog('补款提醒', '用户[' . $Uid . ']在' . $date . '用' . $Price . ($payment == '余额付款' ? '元余额' : '点积分') . '购买了商品[' . $DataBuy['gid'] . ']，重复扣款' . $Price . ($payment == '余额付款' ? '元余额' : '点积分') . '未补款到账户内, 请联系客服手动补款！', $Uid, $Price);
            dies($Code, $Msg);
            /**
             * 执行退款操作
             */

            /*$Deducts = $DB->update('user', [
                ($payment == '余额付款' ? 'money' : 'currency') . '[+]' => $Price,
            ], [
                'id' => $Uid,
            ]);
            if ($Deducts) {
                userlog('订单退款', '用户[' . $Uid . ']在' . $date . '用' . $Price . ($payment == '余额付款' ? '元余额' : '点积分') . '购买了商品[' . $DataBuy['gid'] . ']，重复扣款' . $Price . ($payment == '余额付款' ? '元余额' : '点积分') . '已经补款到账户内！', $Uid, $Price);
                dies($Code, $Msg);
            } else {
                userlog('退款失败', '用户[' . $Uid . ']在' . $date . '用' . $Price . ($payment == '余额付款' ? '元余额' : '点积分') . '购买了商品[' . $DataBuy['gid'] . ']，重复扣款' . $Price . ($payment == '余额付款' ? '元余额' : '点积分') . '未补款到账户内, 请联系客服手动补款！', $Uid, $Price);
                dies($Code, $Msg);
            }*/
        }
    }

    /**
     * @param array $Goods
     * @param $Num //购买份数
     * @param $Oid //订单ID
     * @param $INPUT //下单信息
     * @return bool
     * 减少商品库存
     */
    public static function ReduceStocks(array $Goods, $Num, $Oid, $INPUT)
    {
        if ((int)$Goods['deliver'] === 3) {
            return true;
        }

        $DB = SQL::DB();
        $GoodsShi = $DB->query('select specification_sku,quota from sky_goods where  gid=:gid  for update;', [
            ':gid' => $Goods['gid'],
        ])->fetchAll()[0];

        if ((int)$Goods['specification'] === 2) {
            //如果已经开启商品规格配置！,并且商品库存里面的内容不是默认，那么只减少规格内的商品库存！
            $SKU = json_decode($GoodsShi['specification_sku'], TRUE);
            $SPU = json_decode($Goods['specification_spu'], TRUE);
            $KeyName = [];
            $SpuIn = 0; //初始化
            $InputArr = [];
            foreach ($SPU as $val) {
                $input = $INPUT[$SpuIn];
                foreach ($INPUT as $v) {
                    if (in_array($v, $val)) {
                        $input = $v;
                    }
                }
                $InputArr[$SpuIn] = $input;
                ++$SpuIn;
            }
            foreach ($InputArr as $v) {
                $KeyName[] = $v;
            }

            $NameKey = implode('`', $KeyName);
            if (empty($NameKey)) {
                $SQL = [
                    'quota[-]' => $Num,
                ];
            } else {
                if ((int)$SKU[$NameKey]['quota'] >= 1) {
                    $SKU[$NameKey]['quota'] -= $Num;
                    if ($SKU[$NameKey]['quota'] < 0) {
                        $SKU[$NameKey]['quota'] = 0;
                    }
                    $SQL = [
                        'specification_sku' => json_encode($SKU),
                    ];
                } else {
                    $SQL = [
                        'quota[-]' => $Num,
                    ];
                }
            }
        } else {
            $SQL = [
                'quota[-]' => $Num,
            ];
        }

        $Res = $DB->update('goods', $SQL, [
            'gid' => $Goods['gid'],
        ]);

        $DB->query('commit;'); //释放锁

        if ($Res) {
            return true;
        }
        return false;
    }

    /**
     * @param $OrData //商品订单号！
     * @param $Goods //下单商品信息
     * @param $Type 1正常，2补单
     * 订单创建后的执行操作
     */
    public static function OrderSubmit($OrData, $Goods, $Type = 1)
    {
        global $conf;
        switch ((int)$Goods['deliver']) {
            case 1:
                /**
                 * 自营商品
                 */
                return self::Autotrophy($OrData, $Goods, $Type);
            case 2:
                /**
                 * API对接商品
                 */
                $Data = Api::Submit($OrData, $Goods);
                if (!$Data) {
                    return $OrData['id'];
                }
                return self::OrderSet($OrData['id'], ($Data['code'] >= 0 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']), $Data['msg'], $Data['order'], $Data['money'], $Type, $Data['docking']);
            case 3:
                /**
                 * 发卡商品
                 */
                $Data = faka::Submit($OrData, $Goods);
                if (!$Data) {
                    return $OrData['id'];
                }
                return self::OrderSet($OrData['id'], ($Data['code'] >= 0 ? $conf['SubmitStateSuccess'] : $conf['SubmitState']), $Data['msg'], $Data['order'], 0, $Type);
            case 4:
                /**
                 * 显示隐藏内容
                 */
                return self::OrderSet($OrData['id'], $conf['SubmitStateSuccess'], '商品购买成功，请在订单内查看详情！', '-1', '0', $Type);
            case 5:
                /**
                 * 主机空间发货
                 */
                $Data = self::HostCreation($OrData, $Goods);
                if (!$Data) {
                    return $OrData['id'];
                }
                return self::OrderSet($OrData['id'], $Data['code'], $Data['msg'], -1, 0, $Type, $Data['docking']);
            default:
                /**
                 * 对接货源
                 */
                $DB = SQL::DB();
                $TypeSupply = $DB->get('shequ', '*', [
                    'id' => (int)$Goods['sqid'],
                ]);
                if (!$TypeSupply) {
                    return self::OrderSet($OrData['id'], $conf['SubmitState'], '请检查编号[' . $Goods['sqid'] . ']的货源是否存在，可以尝试重新配置商品对接解决此问题！', -1, 0, $Type);
                }

                if ((string)$TypeSupply['class_name'] === '-1' || $TypeSupply['class_name'] === '') {
                    $TypeSupply['class_name'] = StringCargo::DataConversion($TypeSupply['type']);
                }
                if ((string)$TypeSupply['class_name'] === '0') {
                    $TypeSupply['class_name'] = 'jiuwu';
                }

                $TypeSupply['url'] = StringCargo::UrlVerify($TypeSupply['url']);

                $Source = StringCargo::Docking($TypeSupply['class_name']);
                if (empty($Source)) {
                    return self::OrderSet($OrData['id'], $conf['SubmitState'], '可对接货源类型【' . $TypeSupply['class_name'] . '】不存在！,可能此对接方式已被移除！', -1, 0, $Type);
                }
                $Order = $DB->get('order', '*', ['id' => (int)$OrData['id']]);
                if ((int)$Order['docking'] === 4) {
                    dies(-1, '已经提交过了,无法重复提交对接！');
                }

                $Data = StringCargo::Distribute([
                    'Goods' => $Goods,
                    'Order' => $Order,
                    'Supply' => $TypeSupply,
                    'Source' => $TypeSupply['class_name'], //对接数据类型
                    'controller' => 'Submit', //载入提交订单控制器！
                ]);

                /**
                 * 返回内容如下：
                 * 1、订单处理说明(msg)
                 * 2、对接订单状态(docking) 1,对接成功,2,对接失败(会因为余额不足或其他而出现),3待提交对接(对接错误，可能是配置信息有误！),4无需对接,-1,其他状态
                 * 3、本地订单状态(code) 本地订单处理状态
                 * 3、对接余额（可有可无）
                 * 4、远程订单号（必须有）
                 * 如果有卡密对接信息，则在控制器内写入到本地卡密内！
                 */

                if (!$Data || $Data === -1) {
                    return $OrData['id'];
                }
                return self::OrderSet($OrData['id'], $Data['code'], $Data['msg'], $Data['order'], $Data['money'], $Type, $Data['docking']);
        }
    }

    /**
     * @param $OrData
     * @param $Goods
     * 主机空间发货
     * 注：此商品无法使用积分兑换(影响续费价格配置!)
     * @return array
     */
    public static function HostCreation($OrData, $Goods)
    {
        global $conf, $date;
        $GoodsData = json_decode($Goods['extend'], true); //对接数据
        if (empty((int)$GoodsData['id'])) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => '对接配置有误，请确保商品配置内的节点配置正确！',
                'money' => 0,
                'order' => 0,
            ];
        }

        $DB = SQL::DB();

        $Order = $DB->get('order', '*', ['id' => (int)$OrData['id']]);

        /**
         * 获取到期时间
         * 计算公式：商品发货数量 * 商品份数 * 30天
         */
        $month = $Order['num'] * $Goods['quantity'];
        $EndDate = date('Y-m-d H:i:s', strtotime(' + ' . ($month * 30) . ' day'));
        /**
         * 获取主机每月续期价格
         * 计算公式：订单手机 / (发货数量 * 商品份数)(1=30天)
         */
        $Price = (float)$Order['price'] / $month;

        $identification = md5(random_int(99999, 9999999) . '晴玖天商城系统' . time());

        $InputData = json_decode($Order['input'], TRUE);

        $Confs = [
            'concurrencyall' => (int)$InputData[0],
            'concurrencyip' => ceil($InputData[0] / 10),
            'traffic' => (int)str_replace('KB', '', $InputData[1]),
            'filesize' => (int)str_replace('MB', '', $InputData[2]),
            'maxdomain' => (int)str_replace('个', '', $InputData[3]),
        ];

        foreach ($Confs as $key => $val) {
            if (empty($val)) {
                return [
                    'code' => $conf['SubmitState'],
                    'docking' => 2,
                    'msg' => '主机发货参数规格配置有误，请按照后台提示进行添加！',
                    'money' => 0,
                    'order' => 0,
                ];
            }
        }

        $SQL = [
            'oid' => $Order['id'],
            'identification' => $identification,
            'uid' => ($Order['uid'] <= 0 ? -1 : (int)$Order['uid']),
            'RenewPrice' => $Price,
            'server' => $GoodsData['id'],
            'sql_user' => '待生成',
            'sql_name' => '待生成',
            'sql_pass' => '待生成',
            'maxdomain' => $Confs['maxdomain'],
            'concurrencyall' => $Confs['concurrencyall'],
            'concurrencyip' => $Confs['concurrencyip'],
            'traffic' => $Confs['traffic'],
            'filesize' => $Confs['filesize'],
            'username' => $InputData[4],
            'password' => (empty($InputData[5]) ? '' : md5($InputData[5])),
            'endtime' => $EndDate,
            'addtime' => $date,
        ];


        if (!empty($Data['username'])) {
            $Vs1 = $DB->get('mainframe', ['id'], [
                'username' => (string)$Data['username'],
            ]);

            if ($Vs1) {
                return [
                    'code' => $conf['SubmitState'],
                    'docking' => 2,
                    'msg' => '此用户名已被其他主机占用！',
                    'money' => 0,
                    'order' => 0,
                ];
            }
        }

        $Vs = $DB->get('server', ['id', 'HostSpace'], [
            'id' => (int)$GoodsData['id'],
        ]);

        if (!$Vs) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => '服务器节点不存在！',
                'money' => 0,
                'order' => 0,
            ];
        }

        $SQL['sizespace'] = $Vs['HostSpace'];

        $Res = $DB->insert('mainframe', $SQL);

        if ($Res) {
            if ($SQL['uid'] > 0) {
                userlog('主机创建', '主机空间发货成功，主机ID为：' . $DB->id(), $SQL['uid']);
            }
            return [
                'code' => $conf['SubmitStateSuccess'],
                'docking' => 1,
                'msg' => '主机创建成功，请前往主机管理列表查看，注：主机需要手动激活才可使用！',
                'money' => 0,
                'order' => 0,
            ];
        }

        return [
            'code' => $conf['SubmitState'],
            'docking' => 2,
            'msg' => '创建失败！，请重新尝试！',
            'money' => 0,
            'order' => 0,
        ];
    }

    /**
     * @param $OrData 订单ID+订单号
     * @param $Goods 商品信息
     * 自营商品提交,自营商品需扩展api控制订单
     */
    public static function Autotrophy($OrData, $Goods, $Type)
    {
        global $conf;
        return self::OrderSet($OrData['id'], $conf['SubmitState'], '请联系客服查询具体进度!', '-1', '0', $Type);
    }

    /**
     * @param $state //订单状态
     * @param $return //对接返回信息
     * @param int $order_id 货源订单号
     * @param int $user_rmb 货源余额
     * @param int $Type 1订单创建后操作，2补单
     * 修改订单状态！写入社区订单号
     */
    public static function OrderSet($id, $state, $return, $order_id = -1, $user_rmb = 0, $Type = 1, $docking = 4)
    {
        global $conf, $accredit;
        $DB = SQL::DB();
        $Res = $DB->update('order', [
            'state' => $state,
            'return' => $return,
            'order_id' => $order_id,
            'user_rmb' => $user_rmb,
            'docking' => $docking,
        ], ['id' => $id]);
        if ($Res && $Type === 1) {
            $Order = $DB->get('order', [
                '[>]goods' => ['gid' => 'gid'],
            ], [
                'order.gid',
                'order.uid',
                'order.price',
                'order.num',
                'order.input',
                'goods.name',
                'order.payment',
                'order.order',
            ], ['id' => (int)$id]);
            if ($Order) {
                $OrderGet = $DB->get('order', '*', ['id' => (int)$id]);
                $OrderGet['name'] = $Order['name'];
                try {
                    if ($Order['uid'] >= 1 && $conf['sms_switch_order'] == 1) {
                        SMS::OrderTips($Order['uid'], $Order['order']);
                    }
                    UserConf::PushMoney($Order['uid'], $Order['order'], $Order['gid'], (float)$Order['price'], ($Order['payment'] == '积分兑换' ? 2 : 1), $Order['num'], json_decode($Order['input'], TRUE));
                    SMS::OrderEmailTips($Order['order'], $Order['name']);
                    Hook::execute('OrderFinish', $OrderGet);
                } catch (\Exception $e) {
                    return $id;
                }
            }
        }
        return $id;
    }

    /**
     * @param bool $ID 订单ID
     * @param int $num 批量提交数量
     * 提交订单队列内容！
     */
    public static function SubmitOrderQueue($ID = false, $num = 2)
    {
        $DB = SQL::DB();
        if (!$ID) {
            $QueueArr = $DB->select('queue', ['id'], [
                'type' => 2,
                'ORDER' => 'id',
                'LIMIT' => $num
            ]);
            if (!$QueueArr) {
                dies(-2, '无需监控,队列内无订单!');
            }
            $i = 0;
            $e = 0;
            foreach ($QueueArr as $value) {
                if (self::OrderQueueId($value['id'])['code'] == -2) {
                    ++$e;
                } else {
                    ++$i;
                }
            }
            dier([
                'code' => 1,
                'msg' => '本次共需提交' . $num . '条订单,成功' . $i . '条，失败' . $e . '条！',
            ]);
        } else {
            dier(self::OrderQueueId($ID));
        }
    }

    /**
     * @param $ID 订单ID
     * 提交单个订单商品到订单队列！
     */
    public static function OrderQueueId($ID)
    {
        global $date, $conf;
        $DB = SQL::DB();
        $Queue = $DB->get('queue', '*', [
            'id' => (int)$ID,
            'type' => 2,
        ]);

        if (!$Queue) return ['code' => -1, 'msg' => '队列订单不存在或已经提交至服务器！'];

        $Order = $DB->get('order', '*', [
            'order' => (string)$Queue['order']
        ]);

        if (!$Order) {
            $Goods = $DB->get('goods', '*', ['gid' => (int)$Queue['gid']]);
            if (!$Goods) return ['code' => -1, 'msg' => '此订单商品不存在,无法提交至服务器,请联系管理员处理！'];
            $User = $DB->get('user', '*', ['id' => (int)$Queue['uid']]);
            if (!$User) {
                $User = false;
            }
            $Goods = self::VerifyBuy($Goods['gid'], $Queue['num'], json_decode($Queue['input'], TRUE), ($User === false ? -1 : $User));
            $Queue['input'] = json_encode($Goods['InputData']);
            /**
             * 提交队列订单！
             * 生成指定订单号商品
             */
            $Queue['InitialPrice'] = $Queue['money']; //原始价格
            $Queue['Coupon'] = $Queue['coupon']; //优惠券id

            $Queue['money'] = $Goods['money'] * $Queue['num'];
            $Queue['Goods'] = $Goods;
            $Queue['DataBuy'] = [
                'gid' => $Goods['gid'],
                'num' => $Queue['num'],
                'data' => json_decode($Queue['input'], TRUE),
            ];

            $Data = self::Creation(false, $User, $Queue);

            if ($Data) {
                if ($Queue['payment'] === '免费领取') {
                    userlog('免费领取', '您于' . $date . '领取了商品' . $Goods['name'] . ',订单号为：' . $Queue['order'], $Queue['uid']);
                } else if ($Queue['payment'] === '积分兑换') {
                    userlog('积分兑换', '您于' . $date . '兑换了商品' . $Goods['name'] . ',消耗了' . $Queue['price'] . $conf['currency'] . '！', $Queue['uid'], $Queue['price']);
                } else if ($Queue['payment'] === '余额付款') {
                    userlog('余额购买', '您于' . $date . '购买了商品' . $Goods['name'] . ',付款金额为：' . round($Queue['price'], 2) . '元！', $Queue['uid'], $Queue['price']);
                }

                $re = $DB->update('queue', [
                    'type' => 1,
                    'endtime' => $date,
                    'remark' => '您的商品：' . $Goods['name'] . '订单已经成功提交到服务器!',
                ], ['id' => $ID]);

                if ($re) {
                    return [
                        'code' => 1,
                        'msg' => '订单已经成功提交到了服务器',
                        'id' => $ID
                    ];
                }

                return [
                    'code' => -1,
                    'msg' => '队列订单状态修改失败,无需重复提交,已经提交到服务器了!',
                ];
            }

            return [
                'code' => -2,
                'msg' => '订单提交失败!',
            ];
        }

        $re = $DB->update('queue', [
            'type' => 1,
            'endtime' => $date,
            'remark' => '您已经手动提交到了服务器!',
        ], ['id' => $ID]);
        if ($re) {
            return ['code' => -1, 'msg' => '订单已经提交到服务器,无法重复提交！'];
        }

        return ['code' => -1, 'msg' => '订单状态修改失败,此订单已经被提交到了服务器,无需重复提交！'];
    }

    /**
     * @param $oid
     * @param $order
     * @param $Uid
     * @param $data
     * 修改下单信息
     */
    public static function OrderModification($oid, $OrderNum, $Uid, $data)
    {
        global $conf;
        if ((int)$conf['OrderModification'] !== 1) {
            dies(-1, '当前站点未开启下单信息自助修改！');
        }
        $DB = SQL::DB();
        if (!$Uid) {
            $SQL = [
                'id' => (int)$oid,
                'uid' => -1,
                'order' => (string)$OrderNum,
            ];
        } else {
            $SQL = [
                'id' => (int)$oid,
                'uid' => [(int)$Uid, -1],
                'order' => (string)$OrderNum,
            ];
        }
        $Order = $DB->get('order', '*', $SQL);
        if (!$Order) {
            dies(-1, '订单不存在，无法修改！');
        }
        if ((int)$Order['docking'] === 1) {
            dies(-1, '此商品已经提交至货源站，无法进行修改！');
        }
        $Goods = $DB->get('goods', '*', [
            'gid' => (int)$Order['gid'],
        ]);
        if (!$Goods) {
            dies(-1, '此商品已经下架，无法修改！');
        }
        if ((int)$Goods['specification'] === 2 && (int)$Goods['specification_type'] === 1) {
            dies(-1, '自选规格类商品无法修改！');
        }
        if ((int)$Goods['deliver'] === 5) {
            dies(-1, '主机类商品无法修改下单信息！');
        }
        if ($Order['state'] != 2 && $Order['state'] != 3) {
            dies(-1, '此订单不满足修改条件，仅能修改待处理和异常订单的下单信息！');
        }
        $Input = json_decode($Order['input'], true);
        if (count($Input) !== count($data)) {
            dies(-1, '填写的参数异常！');
        }
        $Res = $DB->update('order', [
            'input' => json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ], [
            'id' => $Order['id'],
        ]);
        if ($Res) {
            userlog('订单修改', (!$Uid ? '游客' : '用户:' . $Uid) . '修改了订单[ ' . $oid . ' ]的下单信息，新：' . json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) . '，旧：' . $Order['input'], (!$Uid ? 0 : $Uid));
            dies(1, '修改成功！');
        } else {
            dies(-1, '修改失败！');
        }
    }

    /**
     * 订单状态监控
     */
    public static function OrderStatusMonitoring()
    {
        $DB = SQL::DB();

        $Array = file_get_contents(ROOT . '/assets/log/OrderList.log');
        $Array = explode('|', $Array);
        $Time_3s = date('Y-m-d H:i:s', strtotime('-3 day'));
        $Count = $DB->count('order', [
            'state[!]' => 5,
            'docking' => 1,
            'order_id[!]' => [-1, ''],
            'addtitm[>]' => $Time_3s,
        ]);

        if ($Count === 0) {
            dies(-1, '没有需要同步的订单');
        }

        $Order = $DB->get('order', '*', [
            'state[!]' => 5,
            'docking' => 1,
            'order_id[!]' => [-1, ''],
            'addtitm[>]' => $Time_3s,
            'ORDER' => [
                'id' => 'DESC'
            ],
            'id[!]' => $Array,
        ]);

        if ($Order) {
            $Goods = $DB->get('goods', '*', [
                'gid' => (int)$Order['gid'],
            ]);

            if (!$Goods) {
                file_put_contents(ROOT . '/assets/log/OrderList.log', $Order['id'] . '|', FILE_APPEND);
                dies(-1, '此订单无需监控，商品已删除');
            }

            $TypeSupply = $DB->get('shequ', '*', [
                'id' => (int)$Goods['sqid'],
            ]);
            if (!$TypeSupply) {
                file_put_contents(ROOT . 'assets/log/OrderList.log', $Order['id'] . '|', FILE_APPEND);
                dies(-1, '对接货源不存在，无法完成订单监控！');
            }

            if ((string)$TypeSupply['class_name'] === '-1' || $TypeSupply['class_name'] === '') {
                $TypeSupply['class_name'] = StringCargo::DataConversion($TypeSupply['type']);
            }
            if ((string)$TypeSupply['class_name'] === '0') {
                $TypeSupply['class_name'] = 'jiuwu';
            }

            $ApiData = StringCargo::Distribute([
                'Order' => $Order,
                'Supply' => $TypeSupply,
                'Source' => $TypeSupply['class_name'],
                'controller' => 'Query',
            ]);
            if (count($ApiData) === 0 || empty($ApiData['ApiStateNum'])) {
                file_put_contents(ROOT . '/assets/log/OrderList.log', $Order['id'] . '|', FILE_APPEND);
                dies(-1, '无法获取远程订单数据，无法完成更新！');
            }

            $Res = $DB->update('order', [
                'state' => $ApiData['ApiStateNum'],
            ], [
                'id' => $Order['id'],
            ]);
            if ($Res) {
                file_put_contents(ROOT . '/assets/log/OrderList.log', $Order['id'] . '|', FILE_APPEND);
                dies(1, '订单[' . $Order['id'] . ']数据同步成功，此订单状态为：【' . $ApiData['ApiState'] . '】，即将开始同步下一个订单！');
            }
        }

        unlink(ROOT . '/assets/log/OrderList.log');
        dies(-1, '无可监控订单,即将开始下一轮监控！', 2);
    }

    /**
     * @param $ID //订单ID
     * @param false $Uid 用户ID,无则为游客
     * @param int $Type 1普通用户查询，2主站后台查询
     * @param false $OrderNum 订单号，游客查询时需要！
     * @return array
     * 查询订单进度！
     */
    public static function Query($ID, $Uid = false, $Type = 1, $OrderNum = false)
    {
        global $conf;
        $DB = SQL::DB();
        if ($Type === 2) {
            //主站后台
            $Order = $DB->get('order', '*', [
                'id' => (int)$ID,
            ]);
        } else if ($Type === 1) {
            //前台用户查单
            if (empty($OrderNum)) {
                dies(-1, '订单号不能为空！');
            }
            if (!$Uid) {
                //游客
                $SQL = [
                    'id' => (int)$ID,
                    'uid' => -1,
                    'order' => (string)$OrderNum,
                ];
            } else {
                //指定用户
                $SQL = [
                    'id' => (int)$ID,
                    'uid' => [(int)$Uid, -1],
                    'order' => (string)$OrderNum,
                ];
            }
            $Order = $DB->get('order', '*', $SQL);
        } else if ($Type === 3) {
            //api对接查单
            $Order = $DB->get('order', '*', [
                'OR' => [
                    'order' => (string)$ID,
                    'id' => (int)$ID,
                ],
                'uid' => (int)$Uid,
                'LIMIT' => 1,
            ]);
        } else {
            dies(-1, '查单方式不存在！');
        }

        if (!$Order) {
            dies(-1, '订单不存在！');
        }

        $Data = [
            'id' => $Order['id'],
            'gid' => $Order['gid'],
            'order' => $Order['order'],
            'ip' => $Order['ip'],
            'price' => round((float)$Order['price'], 8), //购买金额
            'originalprice' => round((float)$Order['originalprice'], 8), //原价
            'coupon' => $Order['coupon'],
            'discounts' => ($Order['coupon'] == -1 ? -1 : round($Order['originalprice'] - ($Order['price']), 8)),
            'addtiem' => $Order['addtitm'],
            'finishtime' => $Order['finishtime'],
            'state' => self::OrderState((int)$Order['state']),
            'stateid' => (int)$Order['state'],
            'type' => $Order['payment'],
            'remark' => $Order['remark'],
            'num' => $Order['num'], //购买份数
            'take' => $Order['take'], //收货状态
            'logistics' => $Order['logistics'],
            'uid' => $Order['uid'],
        ];

        $Goods = $DB->get('goods', '*', [
            'gid' => (int)$Order['gid'],
        ]);

        if (!$Goods) {
            //商品丢失，简查询
            $Input = [];
            foreach (json_decode($Order['input'], true) as $key => $v) {
                $Input[] = '输入框' . ($key + 1) . '：' . $v;
            }
            $Data['input'] = $Input;
            $Data['name'] = '商品已删除';
            $Data['docs'] = '商品已丢失，无法查看商品说明';
            $Data['ApiSn'] = -1;
            $Data['quantity'] = 1;
            $Data['units'] = '个';
            $Data['Explain'] = -1;
        } else {
            //存在，复杂查询
            if ($Goods['deliver'] == -1 && $Order['order_id'] != -1 && $Order['order_id'] != '' && $Order['order_id'] != 1) {
                //对接查询
                $TypeSupply = $DB->get('shequ', '*', [
                    'id' => (int)$Goods['sqid'],
                ]);
                if ($TypeSupply) {
                    //根据可对接列表查询，便于后期扩展！
                    /**
                     * 'ApiType' => 订单查询状态,
                     * 'ApiNum' => 对接购买数量,
                     * 'ApiTime' => 订单创建时间,
                     * 'ApiInitial' => 订单完成时间,
                     * 'ApiPresent' => 现在完成数量,
                     * 'ApiState' => 现在订单状态(文字说明),
                     * 'ApiError' => 订单异常信息，或处理结果,
                     */
                    if ((string)$TypeSupply['class_name'] === '-1' || $TypeSupply['class_name'] === '') {
                        $TypeSupply['class_name'] = StringCargo::DataConversion($TypeSupply['type']);
                    }
                    if ((string)$TypeSupply['class_name'] === '0') {
                        $TypeSupply['class_name'] = 'jiuwu';
                    }
                    $ApiData = StringCargo::Distribute([
                        'Order' => $Order,
                        'Supply' => $TypeSupply,
                        'Source' => $TypeSupply['class_name'], //对接数据类型
                        'controller' => 'Query', //载入查询订单控制器！
                    ]);
                }

                if ($ApiData && $ApiData !== -1 && count($ApiData) >= 1) {
                    //返回数据正常的话，则合并
                    $Data = array_merge($Data, $ApiData);
                }
            }

            $InputArr = json_decode($Order['input'], true);
            $i = 0;
            $GoodsInputArr = explode('|', $Goods['input']);

            if ($Goods['specification'] == 2) {
                $SpRule = RlueAnalysis($Goods, 3);
                if ($SpRule != -1) {
                    $GoodsInputArr = array_merge($SpRule['MasterRule'], $GoodsInputArr);
                }
            }

            $GoodsInput = [];
            foreach ($InputArr as $value) {
                if (empty($GoodsInputArr[$i])) $GoodsInputArr[$i] = '输入框' . ($i + 1);
                if (strstr($GoodsInputArr[$i], '{') && strstr($GoodsInputArr[$i], '}')) {
                    $GoodsInput[] = explode('{', $GoodsInputArr[$i])[0] . '：' . $value;
                } else {
                    $GoodsInput[] = $GoodsInputArr[$i] . '：' . $value;
                }
                ++$i;
            }

            $Data['input'] = $GoodsInput;
            $Data['name'] = $Goods['name'];
            $Data['docs'] = htmlspecialchars_decode($Goods['docs']);
            $Data['ApiSn'] = (count($ApiData) <= 2 ? -1 : 1);
            $Data['quantity'] = $Goods['quantity'];
            $Data['units'] = $Goods['units'];
            $Data['Explain'] = (empty($Goods['explain']) ? -1 : $Goods['explain']);
        }

        $CountHost = $DB->get('mainframe', ['id'], [
            'oid' => (int)$Order['id'],
        ]);
        if ($CountHost) {
            $Data['HostType'] = 1;
        } else {
            $Data['HostType'] = -1;
        }

        if ($Type === 2) {
            //主站可观测数据
            $Data['return'] = $Order['return'];
            $Data['trade_no'] = $Order['trade_no'];
            $Data['muid'] = $Order['muid'];
            $Data['docking'] = $Order['docking']; //对接状态
            $Data['order_id'] = $Order['order_id'];
            $Data['money'] = round($Order['money'], 8); //成本
            $Data['user_rmb'] = round($Order['user_rmb'], 8); //对接余额
        }

        $GetToken = $DB->select('token', ['token'], ['order' => $Order['order']]);
        if (count($GetToken) >= 1) {
            $TokenArr = [];
            foreach ($GetToken as $value) {
                $TokenArr[] = $value['token'];
            }
        } else {
            $TokenArr = -1;
        }
        $Data['token_arr'] = $TokenArr;

        return [
            'code' => 1,
            'msg' => '订单查询成功',
            'data' => $Data,
            'OrderModification' => $conf['OrderModification']
        ];
    }

    /**
     * @param $Id // 类型参数
     * 返回状态
     */
    private static function OrderState($Id)
    {
        switch ($Id) {
            case 1:
                return '已完成';
            case 2:
                return '待处理';
            case 3:
                return '异常';
            case 4:
                return '正在处理';
            case 5:
                return '已退款';
            case 6:
                return '售后中';
            case 7:
                return '已评价';
            default:
                return '未知状态';
        }
    }

    /**
     * @param $ID //订单补单！
     * 目前只有补单功能独立于订单创建外,其他均要验证订单！
     */
    public static function Retry($ID)
    {
        global $conf;
        $DB = SQL::DB();
        $Order = $DB->get('order', '*', [
            'id' => (int)$ID,
        ]);
        if ((int)$Order['state'] === 1) {
            dies(-1, '已完成订单无法补单！');
        }
        $Data = [
            'id' => $ID,
            'order' => $Order['order'],
        ];
        $Goods = $DB->get('goods', '*', [
            'gid' => (int)$Order['gid'],
        ]);

        if (!$Goods) dies(-1, '商品不存在,无法补单！');

        $Sku = json_decode($Order['input'], TRUE);

        if ($Goods['specification'] == 2) {
            $SpRule = RlueAnalysis($Goods, 1);
            $SkuData = [
                'data' => $SpRule,
                'SPU' => json_decode($Goods['specification_spu'], TRUE),
            ];

            $KeyName = [];
            $SpuIn = 0; //初始化
            $InputArr = [];
            foreach ($SkuData['SPU'] as $val) {
                $input = $Sku[$SpuIn];
                foreach ($Sku as $v) {
                    if (in_array($v, $val)) {
                        $input = $v;
                    }
                }
                $InputArr[$SpuIn] = $input;
                ++$SpuIn;
            }
            foreach ($InputArr as $v) {
                $KeyName[] = $v;
            }
            $DataRule = $SkuData['data']['Parameter'][implode('`', $KeyName)];
            if (empty($DataRule)) {
                dies(-1, '商品规格选择错误,请选择正确的参数!');
            }
            $Goods = array_merge($Goods, $DataRule);
        }

        $Goods['price'] = $Order['price'];
        $Goods['points'] = $Order['price'];
        $Goods['num'] = $Order['num'];

        $Re = self::OrderSubmit($Data, $Goods, 2);
        CookieCache::del('OrderListCountAll');
        if ($Re == false) dies(-1, '补单失败，无法获取返回数据！');
        $return = $Order['return'];
        $Order = $DB->get('order', ['return', 'state'], [
            'id' => (int)$ID,
        ]);
        if ($Order['return'] == $return && $Order['state'] != $conf['SubmitStateSuccess']) dies(-1, '补单失败:<br>' . $Order['return']);
        if ($Order['state'] != $conf['SubmitStateSuccess']) {
            dies(-1, '补单失败：' . $Order['return']);
        } else  dies(1, '补单成功：' . $Order['return']);
    }
}
