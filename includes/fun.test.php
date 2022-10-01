<?php

/**
 * 数据安装验证操作类
 * 此模块仅可在站长后台数据校验界面调用！
 */

namespace ins;

use config;
use extend\Maintain;
use Medoo\DB\SQL;

/**
 * Class install
 * @package ins
 * sql数据验证校准类，便于后续升级维护
 * 此处未固有数据，跟随版本变化
 * 核心部分已转移到软件商店内！
 */
class install
{
    /**
     * @param string $type html 返回 json 返回
     * 此处为conf默认配置
     */
    public static function config_verify($type = 'html')
    {
        self::update_calibration();
        self::config_unset($type);
        self::MysqlRepair();
        $DB = SQL::DB();
        if (!$DB) {
            return false;
        }
        $data = [
            'template' => 'PcStore|PC端模板',
            'template_m' => 'default|手机端模板',
            'cdnpublic' => '1|CDN节点',
            'cdnserver' => '2|动态加速节点',
            'sitename' => '晴玖商城|商城名称',
            'kfqq' => '123456|客服QQ',
            'Communication' => '|官方群链接',
            'award' => '1000|邀请奖励货币数量',
            'keywords' => '晴玖商城,实物商城,流量卡商城,淘宝,京东,拼多多|网站关键词',
            'description' => '晴玖商城是一个线上购物网站，你应该可以在里面找到你需要的商品哦！|网站描述语句',
            'logo' => ROOT_DIR . 'assets/img/logo.png|网站logo图地址',
            'currency' => '积分|积分名称',
            'appurl' => '|APP下载地址',
            'navigation' => '百度一下,http://baidu.com|前台导航链接',
            'getinreturn' => '5|每日单一用户可用积分兑换商品的次数(包含免费商品)',
            'getinreturn_all' => '100|每日整个网站可用积分兑换商品的次数(包含免费商品)',
            'prevent_url' => '|防洪接口',
            'prevent_switch' => '-1|-1关闭,1开启',
            'background' => '4|背景图片',
            'secret' => md5(random_int(10000, 1000000) . time()) . '|对接密钥',
            'sms_switch_user' => '-1|-1关闭，1开启，用户后台短信开关',
            'sms_switch_order' => '-1|-1关闭，1开启，订单购买短信开关',
            'banner' => ROOT_DIR . 'assets/img/pay.jpg*/user|首页banner图',
            'weix_notice' => '-1|订单通知',
            'userdeposit' => '1|提现开关',
            'userleague' => '1|加盟开关',
            'userleaguegrade' => '2|加盟等级限制',
            'userdepositgrade' => '2|提现等级限制',
            'usergradenotice' => '2|加盟用户多少级可以自定义公告，客服？',
            'usergradeprofit' => '3|加盟店多少级可以自定义利润？,只能多不能少',
            'usergradegoodsstate' => '4|加盟店多少级可以自定义商品状态？',
            'userdefaultgrade' => '1|用户注册默认等级',
            'userbinding' => '1|是否可以打开未绑定域名的站点',
            'usersmsbinding' => '3|每日用户换绑短信发送次数',
            'usersmslogin' => '3|每日用户登陆短信发送次数',
            'userdepositservice' => '5|用户提现手续费',
            'usergradecost' => '-1|用户升级费用是否奖励给他的上级？如果有的话',
            'userupgradeprofit' => '50|升级利润分成百分比！',
            'userdepositmin' => '10|用户最少提现多少钱？',
            'userdeposittype' => '1,1,1|用户提现支持方式',
            'usergradetem' => '4|用户多少级可以自定义模板？',
            'userdomain' => href() . '|加盟店铺可选域名主体',
            'userdomainretain' => 'www,m|主站保留前缀',
            'userdomainsetmoney' => '5|域名修改价格',
            'userdomaintype' => '1|1=泛解析模式,2=cookie绑定模式',
            'blacklist' => '|禁止下单信息',
            'SecurityCenter' => '-1|安全中心开关',
            'title' => '晴玖商城|网站副标题',
            'share_image' => '1|分享海报获取方式,1=本地获取,2=云端获取',
            'compression' => '0.9|图片上传压缩比例',
            'CartPaySet' => '1,2,3,4,5|购物车支持付款方式',
            'OredeQueue' => '1|订单队列模式,1不使用,2使用',
            'ForcedLanding' => '1|1不强制用户登陆,2强制用户必须登陆,3看到商品后才提示登陆！',
            'CartState' => '1|购物车开关',
            'SubmitState' => '2|对接货源下单失败后的状态设置',
            'OrderAstrict' => '120|相同下单信息的商品提交间隔限制,秒',
            'TicketsClass' => '订单问题,充值问题,网站BUG,优化建议|工单类型,可自定义',
            'prevent_return' => 'ae_url|防红自定义返回字段名称',
            'prevent_field' => '[url]|防洪自定义提交字段名称',
            'Homecaching' => '60|首页数据缓存周期',
            'SalesSum' => '0|保底销量',
            'WeixQqValidation' => '2|验证是否是在QQ内打开,是的话拦截！',
            'CloseWebsite' => '|关闭网站的原因,留空不关闭！',
            'YzfSign' => '|云智服SIGN',
            'SupervisorySwitch' => '1|监控总开关',
            'SupervisoryError' => '2|监控到对接商品下架后操作',
            'SupervisorySuccess' => '2|监控商品上架后操作',
            'SupervisoryCycle' => '60|静默监控间隔时间',
            'SupervisoryMsg' => '当前商品有新的调整|监控商品价格状态有变化时提示信息！',
            'Protect' => '|目录保护,留空不保护！',
            'AccountPasswordLogin' => '1|1=开启,2=关闭账号密码登录',
            'PayConQQ' => '-1|QQ支付(新)',
            'PayConWX' => '-1|微信支付(新)',
            'PayConZFB' => '-1|支付宝支付(新)',
            'SubmitStateSuccess' => '1|下单成功后状态',
            'Tracking' => 'https://m.kuaidi100.com/result.jsp?nu=|物流单号接口',
            'ServiceImage' => '|客服二维码，留空不显示',
            'GoodsRecommendation' => '1|开启或关闭APP模板商品推荐',
            'InviteValve' => '0|邀请阈值,必须消费这么多金额才可领取奖励！',
            'DefaultLabel' => '质量|默认标签名称',
            'DynamicMessage' => '1|站点动态消息开关',
            'LevelDisplay' => '1|是否显示商品等级列表',
            'SimilarRecommend' => '1|是否开启同分类商品推荐',
            'ShutDownUserSystem' => '1|是否关闭用户系统',
            'CartNotice' => '付款后订单未到账可联系客服处理！|购物车公告内容',
            'ShutDownUserSystemCause' => '因网站调整，已暂时关闭了用户系统，若需要购买商品，可以直接在线付款哦|关闭用户系统原因',
            'CouponMinimumType' => '1|优惠券保险措施',
            'CouponUseIpType' => '2|优惠券IP使用限制',
            'CouponApiDockingOthers' => '1|优惠券对接他人时自动使用',
            'CouponApiBeDocking' => '1|是否防止其他对接站点使用优惠券',
            'CouponUsableMax' => '18|每个用户每天可使用的券数量限制',
            'CouponGetMax' => '18|每个用户每天可领取的券码数量限制',
            'HomeLimit' => '12|每页展示数据条数',
            'userregister' => '1|用户注册开关',
            'appiconid' => '19843|App图标id',
            'appbackgroundid' => '19920|App启动图id',
            'appprice' => '2|App生成价格',
            'appthemecolor' => '#1c97f5|App导航栏颜色',
            'apploadthemecolor' => '#1c97f5|App加载条颜色',
            'appcontent' => '欢迎下载本APP，助您购物更便捷！|App简单介绍',
            'userappstate' => '1|是否开启用户后台App生成',
            'AntiReptile' => '1|是否开启反爬虫功能',
            'AdminHeaderTem' => '1|框架模板',
            'QQInternetChoice' => '1|QQ互联接口通道选择,1默认官方，2自定义，3不启用!',
            'QQInternetID' => '|QQ互联接口应用ID!',
            'QQInternetKey' => '|QQ互联接口应用Key!',
            'QQInternetCallback' => href(2) . ROOT_DIR_S . '/includes/LoginCallback.php' . '|QQ互联回调URL地址',
            'CaptchaType' => '1|验证码类型',
            'OrderModification' => '1|待处理订单自助修改',
            'rechargepurchaseurl' => '|充值卡购买地址',
            'FluctuationsPrices' => '1|价格波动开关,1开启，其他关闭',
            'hostSwitch' => '1|1开启,-1关闭主机系统',
            'QuotaRestrictionOperation' => '1|1,直接关闭,-1仅后台提醒',
            'FreeHostRenewal' => '-1|1,允许续费，-1不允许',
            'PayRates' => '0|在线支付通道费率',
            'PasswordAccess' => '|输密访问，留空不验证！',
            'PasswordAccessTips' => '请联系客服获取密码哦|输密界面提示说明,纯文字',
            'GuardStr' => '*|规避词替换内容',
            'AppGlobals' => '-1|是否开启全局应用执行槽位',
            'SMSChannelConfiguration' => '1|1官方,2阿里云3腾讯云4七牛云',
            'SMSSignName' => '|短信签名',
            'SMSAccessKeyId' => '|短信KeyID',
            'SMSAccessKeySecret' => '|短信KeySec',
            'SMSTemplateCode' => '|模板CODE[验证码]',
            'PMIPState' => '-1|代理IP开关',
            'PMIPPort' => '|代理IP端口号',
            'PMIP' => '|代理IP地址',
            'InfiniteDivision' => '1|无限级分成开关,无视用户等级分成',
            'SortingRules' => 'sort|首页排序规则',
            'RechargeLimit' => '0.01-2000|用户后台最低最高充值金额，用-分割',
            'SignAway' => '1|签到赠送内容，1积分，2余额',
            'GiftContent' => '1-10|每次签到赠送的内容范围，用-分割',
            'CommitsDistribute' => '1|是否将提成强制分配给绑定的上级?',
            'inItRegister' => '2|是否开启邀请注册功能'
        ];
        $config = new config();
        $i = 0;
        $counts = (int)file_get_contents(ROOT . 'assets/log/MaintainSmdd.lock');
        if ($type !== 'html' || $counts !== count($data)) {
            file_put_contents(ROOT . 'assets/log/MaintainSmdd.lock', count($data));
            foreach ($data as $k => $v) {
                $sq = explode('|', $v);
                $Res = $DB->insert('config', [
                    'K' => $k,
                    'V' => $sq[0],
                    'C' => $sq[1],
                ]);
                if ($Res) {
                    ++$i;
                }
            }
            if ($i > 0) {
                if ($type === 'html') {
                    $config->unset_cache();
                    @Maintain::databaseCalibre();
                    show_msg('恭喜', '成功校准全局数据！', 1, false, false);
                } else {
                    $config->unset_cache();
                    if ($type === false) {
                        return true;
                    }
                    dies(1, '成功校准全局数据!');
                }
            }
        }
    }

    /**
     * 校准公告代码问题
     */
    public static function update_calibration()
    {
        $DB = SQL::DB();
        $SQL_S = [
            'notice_top' => '请手动编辑公告|首页公告',
            'notice_check' => '请手动编辑公告|首页查单公告',
            'notice_bottom' => '请手动编辑公告|全局底部公告',
            'notice_user' => '请手动编辑公告|分站后台公告',
            'PopupNotice' => '|首页弹窗公告',
            'statistics' => '|IP统计代码',
            'ServiceTips' => '请截图扫码添加客服好友哦，添加上时直接说明来意！|客服提示语',
            'HostAnnounced' => '|主机后台公告'
        ];

        mkdirs(ROOT . 'includes/extend/log/Notice/');

        foreach ($SQL_S as $k => $v) {
            if (file_exists(ROOT . 'includes/extend/log/Notice/' . $k . '.nc')) continue;
            $A = $DB->get('config', ['V'], [
                'K' => $k
            ]);
            if (!empty($A['V'])) {
                $C = htmlspecialchars_decode(base64_decode($A['V']));
                if ($k === 'ServiceTips') $C = html_entity_decode($A['V']);
                file_put_contents(ROOT . 'includes/extend/log/Notice/' . $k . '.nc', $C);
            } else {
                file_put_contents(ROOT . 'includes/extend/log/Notice/' . $k . '.nc', '');
            }
        }
    }

    /**
     * 清理已废弃参数！
     */
    public static function config_unset($type)
    {
        $DBS = SQL::DB();
        $data = [
            'pay_qqapy' => '-1|QQ支付接口,-1关闭,1易支付,2码支付',
            'pay_wxpay' => '-1|微信支付接口,-1关闭,1易支付,2码支付',
            'pay_alipay' => '-1|支付宝支付接口,-1关闭,1易支付,2码支付',
            'pay_url' => '|易支付接口域名',
            'pay_partner' => '|易支付商户ID',
            'pay_key' => '|易支付商户token',
            'cpay_id' => '|码支付ID',
            'cpay_key' => '|码支付密钥',
            'prevent_type' => 'qq|防洪生成类型qq/vx',
            'prevent_dwzapi' => '2|网址缩短类型',
            'prevent_format' => 'json|防洪返回类型[json|txt|qrcode]',
            'Interface' => '1|API接口列表',
            'alipay_public_key' => '|当面付公钥',
            'merchant_private_key' => '|当面付私钥',
            'alipay_fpay_id' => '|支付宝应用ID',
            'alipay_name' => '|当面付商品名称自定义！',
            'HomePageDisplay' => '1|1=固定排序,2=随机排序',
            'share_url' => '我钱不够买这个东西，能够帮我买一下嘛~，这是付款订单,谢谢啦 [url]|商品代付款分享语句',
            'QrCodeApi' => 'http://qr.topscan.com/api.php?text=|二维码生成接口!',
            'notice_top' => '请手动编辑公告|首页公告',
            'notice_check' => '请手动编辑公告|首页查单公告',
            'notice_bottom' => '请手动编辑公告|全局底部公告',
            'notice_user' => '请手动编辑公告|分站后台公告',
            'PopupNotice' => '|首页弹窗公告',
            'statistics' => '|IP统计代码',
            'ServiceTips' => '请截图扫码添加客服好友哦，添加上时直接说明来意！|客服提示语',
            'Signin' => '100|签到赠送货币数量',
        ];
        $i = 0;

        $counts = (int)file_get_contents(ROOT . 'assets/log/MaintainUnset.lock');
        if ($counts <> count($data)) {
            file_put_contents(ROOT . 'assets/log/MaintainUnset.lock', count($data));
            foreach ($data as $k => $v) {
                $re = $DBS->delete('config', [
                    'K' => $k
                ]);
                if ($re) {
                    ++$i;
                }
            }
            if ($i > 0) {
                $config = new config();
                if ($type == 'html') {
                    $config->unset_cache();
                    show_msg('恭喜', '成功校准已废弃全局参数,请刷新界面继续！', 1, false, false);
                } else {
                    $config->unset_cache();
                    if ($type === false) {
                        return true;
                    }
                    dies(1, '成功校准已废弃全局参数,请继续校准!');
                }
            }
        }
        return true;
    }

    /**
     * 数据库时间修复
     * 0000-00-00 00:00:00
     */
    public static function MysqlRepair()
    {
        $Mysql = [
            'pay' => [
                'endtime',
            ],
            'queue' => [
                'endtime',
            ],
            'order' => [
                'finishtime',
            ],
            'token' => [
                'endtime',
            ],
            'withdrawal' => [
                'endtime',
            ],
            'login' => [
                'finish_time',
            ],
            'invite' => [
                'draw_time',
            ],
            'goods' => [
                'date',
            ],
            'coupon' => [
                'endtime',
                'gettime',
                'expirydate'
            ],
        ];
        $DBS = SQL::DB();
        $I = 0;
        foreach ($Mysql as $k => $v) {
            foreach ($v as $vs) {
                $Res = $DBS->update($k, [
                    $vs => NULL
                ], [
                    $vs => '0000-00-00 00:00:00'
                ]);
                if ($Res) ++$I;
            }
        }
        return '本次共成功调整' . $I . '个表';
    }

    /**
     * @return array
     * 输出需要验证的数据库数组
     */
    public function sql_arr()
    {
        /**
         * 数组结构说明：
         * （$arr[0] 类型）
         * （$arr[1] 长度）
         * （$arr[2] 小数点）
         * （$arr[3] 不是null）
         * （$arr[4] 是否递增）
         * （$arr[5] 默认填写）
         */
        $array = [
            'sky_class' => [
                'cid' => 'int/11/0/1/1/NULL',
                'sort' => 'int/11/0/0/0/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'image' => 'text/0/0/0/0/NULL',
                'grade' => 'int/255/0/0/0/1',
                'content' => 'text/0/0/0/0/NULL',
                'state' => 'int/11/0/0/0/1',
                'support' => 'varchar/255/0/0/0/\'1,1,1,1,1\'',
                'date' => 'datetime/0/0/0/0/NULL'
            ],
            'sky_goods' => [
                'gid' => 'int/11/0/1/1/NULL',
                'cid' => 'int/11/0/0/0/1',
                'sort' => 'int/11/0/0/0/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'image' => 'text/0/0/0/0/NULL',
                'money' => 'decimal/24/8/0/0/NULL',
                'profits' => 'decimal/24/0/0/0/100',
                'min' => 'int/255/0/0/0/1',
                'max' => 'int/255/0/0/0/1',
                'quota' => 'int/255/0/0/0/9999',
                'freight' => 'varchar/255/0/0/0/-1',
                'method' => 'varchar/255/0/0/0/[1,2,3,4,5,6,7]',
                'input' => 'text/0/0/0/0/NULL',
                'quantity' => 'varchar/255/0/0/0/NULL',
                'docs' => 'text/0/0/0/0/NULL',
                'alert' => 'text/0/0/0/0/NULL',
                'units' => 'varchar/255/0/0/0/个',
                'deliver' => 'int/255/0/0/0/NULL',
                'sqid' => 'int/11/0/0/0/NULL',
                'state' => 'int/11/0/0/0/1',
                'note' => 'text/0/0/0/0/NULL',
                'sales' => 'int/255/0/0/0/0',
                'selling' => 'text/0/0/0/0/NULL',
                'accuracy' => 'int/255/0/0/0/2',
                'explain' => 'text/0/0/0/0/NULL',
                'specification' => 'int/1/0/0/0/1',
                'specification_type' => 'int/1/0/0/0/1',
                'specification_spu' => 'text/0/0/0/0/NULL',
                'specification_sku' => 'text/0/0/0/0/NULL',
                'extend' => 'text/0/0/0/0/NULL',
                'label' => 'text/0/0/0/0/NULL',
                'date' => 'datetime/0/0/0/0/NULL',
                'update_dat' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_invite' => [
                'id' => 'int/11/0/1/1/NULL',
                'uid' => 'int/11/0/0/0/NULL',
                'invitee' => 'int/11/0/0/0/NULL',
                'award' => 'decimal/11/0/0/0/NULL',
                'ip' => 'varchar/255/0/0/0/NULL',
                'draw_time' => 'datetime/0/0/0/0/NULL',
                'creation_time' => 'datetime/0/0/0/0/NULL'
            ],
            'sky_journal' => [
                'id' => 'int/11/0/1/1/NULL',
                'ip' => 'varchar/255/0/0/0/NULL',
                'uid' => 'int/11/0/0/0/NULL',
                'count' => 'decimal/24/8/0/0/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'date' => 'datetime/0/0/0/0/NULL'
            ],
            'sky_login' => [
                'id' => 'int/11/0/1/1/NULL',
                'token' => 'varchar/255/0/0/0/NULL',
                'ip' => 'varchar/255/0/0/0/NULL',
                'state' => 'int/11/0/0/0/3',
                'finish_time' => 'datetime/0/0/0/0/NULL',
                'date_created' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_notice' => [
                'id' => 'int/11/0/1/1/NULL',
                'title' => 'varchar/255/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'image' => 'text/0/0/0/0/NULL',
                'browse' => 'text/0/0/0/0/NULL',
                'PV' => 'int/255/0/0/0/0',
                'state' => 'int/11/0/0/0/1',
                'type' => 'int/11/0/0/0/1',
                'date' => 'datetime/0/0/0/0/NULL'
            ],
            'sky_order' => [
                'id' => 'int/11/0/1/1/NULL',
                'order' => 'varchar/255/0/0/0/NULL',
                'trade_no' => 'varchar/255/0/0/0/NULL',
                'uid' => 'int/11/0/0/0/NULL',
                'muid' => 'int/11/0/0/0/-1',
                'ip' => 'varchar/255/0/0/0/NULL',
                'input' => 'text/0/0/0/0/NULL',
                'state' => 'int/11/0/0/0/NULL',
                'docking' => 'int/11/0/0/0/-1',
                'num' => 'int/11/0/0/0/1',
                'return' => 'text/0/0/0/0/NULL',
                'gid' => 'int/11/0/0/0/NULL',
                'order_id' => 'text/0/0/0/0/NULL',
                'money' => 'decimal/24/8/0/0/NULL',
                'originalprice' => 'decimal/24/8/0/0/-1',
                'coupon' => 'varchar/255/0/0/0/-1',
                'payment' => 'varchar/255/0/0/0/NULL',
                'take' => 'int/1/0/0/0/1',
                'price' => 'decimal/24/8/0/0/NULL',
                'logistics' => 'text/0/0/0/0/NULL',
                'user_rmb' => 'decimal/24/8/0/0/NULL',
                'remark' => 'text/0/0/0/0/NULL',
                'finishtime' => 'timestamp/0/0/0/0/NULL',
                'addtitm' => 'datetime/0/0/0/0/NULL'
            ],
            'sky_pay' => [
                'id' => 'int/11/0/1/1/NULL',
                'order' => 'varchar/255/0/0/0/NULL',
                'trade_no' => 'varchar/255/0/0/0/NULL',
                'type' => 'varchar/11/0/0/0/NULL',
                'uid' => 'int/11/0/0/0/NULL',
                'gid' => 'int/11/0/0/0/NULL',
                'oid' => 'int/22/0/0/0/-1',
                'name' => 'varchar/255/0/0/0/NULL',
                'money' => 'decimal/24/8/0/0/NULL',
                'price' => 'decimal/24/8/0/0/-1',
                'coupon' => 'varchar/255/0/0/0/-1',
                'ip' => 'varchar/255/0/0/0/NULL',
                'input' => 'text/0/0/0/0/NULL',
                'num' => 'int/11/0/0/0/1',
                'state' => 'int/11/0/0/0/2',
                'verify' => 'varchar/255/0/0/0/-1',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_shequ' => [
                'id' => 'int/11/0/1/1/NULL',
                'url' => 'varchar/255/0/0/0/NULL',
                'type' => 'int/11/0/0/0/1',
                'class_name' => 'varchar/255/0/0/0/-1',
                'username' => 'varchar/255/0/0/0/NULL',
                'password' => 'varchar/255/0/0/0/NULL',
                'secret' => 'varchar/255/0/0/0/NULL',
                'pattern' => 'varchar/255/0/0/0/1',
                'annotation' => 'varchar/255/0/0/0/NULL',
                'date' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_token' => [
                'kid' => 'int/11/0/1/1/NULL',
                'uid' => 'int/11/0/0/0/1',
                'gid' => 'int/11/0/0/0/NULL',
                'code' => 'varchar/255/0/0/0/NULL',
                'token' => 'varchar/255/0/0/0/NULL',
                'ip' => 'varchar/255/0/0/0/NULL',
                'order' => 'varchar/255/0/0/0/NULL',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_user' => [
                'id' => 'int/11/0/1/1/NULL',
                'user_idu' => 'varchar/255/0/0/0/NULL',
                'username' => 'text/0/0/0/0/NULL',
                'password' => 'text/0/0/0/0/NULL',
                'token' => 'varchar/255/0/0/0/NULL',
                'ip_white_list' => 'text/0/0/0/0/NULL',
                'superior' => 'int/11/0/0/0/NULL',
                'currency' => 'decimal/24/0/0/0/0',
                'ip' => 'varchar/255/0/0/0/NULL',
                'money' => 'decimal/24/8/0/0/0.00000000',
                'grade' => 'int/255/0/0/0/1',
                'domain' => 'varchar/255/0/0/0/NULL',
                'configuration' => 'text/0/0/0/0/NULL',
                'image' => 'varchar/255/0/0/0/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'pricehike' => 'text/0/0/0/0/NULL',
                'qq' => 'varchar/255/0/0/0/NULL',
                'mobile' => 'varchar/255/0/0/0/NULL',
                'state' => 'int/11/0/0/0/NULL',
                'recent_time' => 'timestamp/0/0/0/0/NULL',
                'found_date' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_price' => [
                'mid' => 'int/12/0/1/1/NULL',
                'sort' => 'int/12/0/0/0/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'priceis' => 'text/0/0/0/0/NULL',
                'pointsis' => 'text/0/0/0/0/NULL',
                'ActualProfit' => 'varchar/255/0/0/0/100',
                'ProfitThreshold' => 'varchar/255/0/0/0/100',
                'money' => 'decimal/12/2/0/0/0',
                'state' => 'int/12/0/0/0/1',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_freight' => [
                'id' => 'int/12/0/1/1/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'region' => 'text/0/0/0/0/NULL',
                'money' => 'decimal/12/2/0/0/0',
                'nums' => 'int/12/0/0/0/NULL',
                'exceed' => 'decimal/12/2/0/0/0',
                'threshold' => 'decimal/12/2/0/0/0',
                'date' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_withdrawal' => [
                'id' => 'int/12/0/1/1/NULL',
                'type' => 'varchar/255/0/0/0/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'account_number' => 'varchar/255/0/0/0/NULL',
                'uid' => 'int/12/0/0/0/NULL',
                'remarks' => 'text/0/0/0/0/NULL',
                'state' => 'int/1/0/0/0/3',
                'result_code' => 'text/0/0/0/0/NULL',
                'money' => 'decimal/12/2/0/0/0',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_queue' => [
                'id' => 'int/22/0/1/1/NULL',
                'type' => 'int/22/0/0/0/2',
                'order' => 'varchar/255/0/0/0/NULL',
                'trade_no' => 'varchar/255/0/0/0/NULL',
                'uid' => 'int/25/0/0/0/NULL',
                'ip' => 'varchar/255/0/0/0/NULL',
                'input' => 'text/0/0/0/0/NULL',
                'num' => 'int/25/0/0/0/NULL',
                'gid' => 'int/25/0/0/0/NULL',
                'payment' => 'varchar/255/0/0/0/NULL',
                'price' => 'decimal/25/8/0/0/0',
                'money' => 'decimal/24/8/0/0/-1',
                'coupon' => 'varchar/255/0/0/0/-1',
                'remark' => 'text/0/0/0/0/NULL',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_cart' => [
                'id' => 'int/22/0/1/1/NULL',
                'uid' => 'int/22/0/0/0/-1',
                'cookie' => 'text/0/0/0/0/NULL',
                'ip' => 'varchar/255/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_mark' => [
                'id' => 'int/22/0/1/1/NULL',
                'gid' => 'int/25/0/0/0/NULL',
                'order' => 'varchar/255/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'image' => 'text/0/0/0/0/NULL',
                'name' => 'text/0/0/0/0/NULL',
                'uid' => 'int/24/0/0/0/NULL',
                'seller' => 'varchar/255/0/0/0/NULL',
                'score' => 'varchar/255/0/0/0/NULL',
                'state' => 'int/2/0/0/0/3',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_tickets' => [
                'id' => 'int/22/0/1/1/NULL',
                'uid' => 'int/25/0/0/0/NULL',
                'order' => 'varchar/255/0/0/0/NULL',
                'name' => 'text/0/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'message' => 'text/0/0/0/0/NULL',
                'state' => 'int/2/0/0/0/2',
                'type' => 'int/2/0/0/0/1',
                'grade' => 'int/25/0/0/0/NULL',
                'class' => 'text/0/0/0/0/NULL',
                'timetips' => 'varchar/255/0/0/0/NULL',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_coupon' => [
                'id' => 'int/255/0/1/1/NULL',
                'oid' => 'text/0/0/0/0/NULL',
                'uid' => 'int/255/0/0/0/-1',
                'name' => 'text/0/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'gid' => 'int/255/0/0/0/-1',
                'cid' => 'int/255/0/0/0/-1',
                'token' => 'text/0/0/0/0/NULL',
                'money' => 'decimal/65/2/0/0/NULL',
                'ip' => 'varchar/255/0/0/0/NULL',
                'minimum' => 'decimal/65/2/0/0/0',
                'type' => 'int/255/0/0/0/1',
                'apply' => 'int/255/0/0/0/1',
                'term_type' => 'int/255/0/0/0/1',
                'indate' => 'int/255/0/0/0/30',
                'expirydate' => 'datetime/0/0/0/0/NULL',
                'get_way' => 'int/255/0/0/0/1',
                'limit' => 'int/255/0/0/0/1',
                'limit_token' => 'text/0/0/0/0/NULL',
                'gettime' => 'datetime/0/0/0/0/NULL',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_app' => [
                'id' => 'int/255/0/1/1/NULL',
                'uid' => 'int/255/0/0/0/NULL',
                'TaskID' => 'int/255/0/0/0/-1',
                'TaskMsg' => 'text/0/0/0/0/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'url' => 'varchar/255/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'state' => 'int/255/0/0/0/4',
                'theme' => 'varchar/255/0/0/0/NULL',
                'load_theme' => 'varchar/255/0/0/0/NULL',
                'money' => 'decimal/12/2/0/0/0',
                'icon' => 'varchar/255/0/0/0/NULL',
                'background' => 'varchar/255/0/0/0/NULL',
                'download' => 'text/0/0/0/0/NULL',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_mainframe' => [
                'id' => 'int/255/0/1/1/NULL',
                'oid' => 'int/255/0/0/0/-1',
                'identification' => 'varchar/255/0/0/0/NULL',
                'siteId' => 'int/255/0/0/0/-1',
                'uid' => 'int/255/0/0/0/NULL',
                'server' => 'int/255/0/0/0/NULL',
                'type' => 'int/10/0/0/0/2',
                'sql_user' => 'varchar/255/0/0/0/NULL',
                'sql_name' => 'varchar/255/0/0/0/NULL',
                'sql_pass' => 'varchar/255/0/0/0/NULL',
                'domain' => 'text/0/0/0/0/NULL',
                'RenewalType' => 'int/255/0/0/0/1',
                'RenewPrice' => 'decimal/16/8/0/0/0',
                'maxdomain' => 'int/255/0/0/0/3',
                'concurrencyall' => 'int/255/0/0/0/NULL',
                'concurrencyip' => 'int/255/0/0/0/NULL',
                'traffic' => 'int/255/0/0/0/NULL',
                'filesize' => 'int/255/0/0/0/NULL',
                'sizespace' => 'decimal/65/2/0/0/200',
                'currentsize' => 'decimal/65/2/0/0/0',
                'status' => 'int/2/0/0/0/1',
                'state' => 'int/2/0/0/0/1',
                'username' => 'text/0/0/0/0/NULL',
                'password' => 'text/0/0/0/0/NULL',
                'return' => 'text/0/0/0/0/NULL',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_server' => [
                'id' => 'int/255/0/1/1/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'url' => 'varchar/255/0/0/0/NULL',
                'domain' => 'text/0/0/0/0/NULL',
                'data' => 'text/0/0/0/0/NULL',
                'path' => 'varchar/255/0/0/0/NULL',
                'root_directory' => 'varchar/255/0/0/0/NULL',
                'token' => 'varchar/255/0/0/0/NULL',
                'HostSpace' => 'decimal/65/2/0/0/200',
                'system' => 'int/255/0/0/0/1',
                'type' => 'int/255/0/0/0/0',
                'state' => 'int/10/0/0/0/1',
                'sqlurl' => 'text/0/0/0/0/NULL',
                'error' => 'text/0/0/0/0/NULL',
                'content' => 'text/0/0/0/0/NULL',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_recharge' => [
                'id' => 'int/255/0/1/1/NULL',
                'name' => 'varchar/255/0/0/0/NULL',
                'uid' => 'int/255/0/0/0/-1',
                'money' => 'decimal/16/2/0/0/0',
                'type' => 'int/2/0/0/0/1',
                'token' => 'varchar/255/0/0/0/NULL',
                'ip' => 'varchar/255/0/0/0/NULL',
                'endtime' => 'datetime/0/0/0/0/NULL',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ],
            'sky_seckill' => [
                'id' => 'int/255/0/1/1/NULL',
                'gid' => 'int/255/0/0/0/NULL',
                'depreciate' => 'int/255/0/0/0/0',
                'start_time' => 'datetime/0/0/0/0/NULL',
                'end_time' => 'datetime/0/0/0/0/NULL',
                'astrict' => 'int/255/0/0/0/1',
                'addtime' => 'datetime/0/0/0/0/NULL',
            ]
        ];
        return $array;
    }
}
