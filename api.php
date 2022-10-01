<?php

/**
 * api模块
 */

include 'includes/fun.global.php';
header('Content-Type: application/json; charset=UTF-8');

global $conf, $_QET, $accredit;

use BT\monitoring;
use extend\OnebuttonDockingSystem;
use extend\QuickLogin;
use extend\SqlBackups;
use extend\UserConf;
use lib\AppStore\AppList;
use lib\Hook\Hook;
use lib\supply\GoodsMonitoring;
use lib\supply\official;
use lib\supply\Order;
use lib\supply\Price;
use lib\supply\xiaochu;
use Medoo\DB\SQL;

switch ($_QET['act']) {
    case 'ip':
        dier([
            'code' => 1,
            'msg' => 'IP地址获取成功',
            'ip' => userip()
        ]);
        break;
    case 'operation':
        admin::operation($_QET);
        break;
    case 'WebsiteData': #获取网站基础数据
        dier(['code' => 1, 'msg' => '数据获取成功', 'data' => [
            'sitename' => $conf['sitename'], //网站名称
            'kfqq' => $conf['kfqq'], //客服QQ
            'notice_top' => $conf['notice_top'], //首页公告
            'notice_check' => $conf['notice_check'], //查单公告
            'notice_bottom' => $conf['notice_bottom'], //底部公告
            'notice_user' => $conf['notice_user'], //分站后台公告
            'PopupNotice' => $conf['PopupNotice'], //首页弹窗公告
            'HostAnnounced' => $conf['HostAnnounced'], //主机后台公告
            'currency' => $conf['currency'], //货币名称
            'domain' => $accredit['url'], //域名
            'versions' => $accredit['versions'], //版本
        ], 'time' => time()]);
        break;
    case 'DockingGoodsLog': //商品详情
        $User = xiaochu::verify($_QET);
        dier(xiaochu::GoodsDetails($User, $_QET['gid']));
        break;
    case 'DockingClassList': //分类列表[用户等级会影响到分类显示]
        $User = xiaochu::verify($_QET);
        $_SESSION['UserGoods'] = xiaochu_en(json_encode(['uid' => $User['id'], 'grade' => $User['grade'], 'GoodsRis' => ($User['pricehike'] == '' ? [] : json_decode($User['pricehike'], TRUE))], JSON_UNESCAPED_UNICODE), $accredit['token']);
        $Hide = UserConf::GoodsHide();
        if (!$User || empty($User['grade'])) {
            $grade = 1;
        } else {
            $grade = (int)$User['grade'];
        }
        $DB = SQL::DB();
        $Res = $DB->select('class', '*', [
            'grade[<=]' => $grade,
            'ORDER' => [
                'sort' => 'DESC'
            ],
            'state' => 1,
        ]);
        $Data = [];
        if (!$Res) {
            $Res = [];
        }
        foreach ($Res as $v) {
            $v['image'] = ImageUrl($v['image']);
            $v['count'] = $DB->count('goods', ['cid' => $v['cid'], 'state' => 1, 'gid[!]' => (count($Hide) === 0 ? -1 : $Hide)]);
            $Data[] = $v;
        }
        dier([
            'code' => 1,
            'msg' => '商品分类数据获取成功',
            'data' => $Data
        ]);
        break;
    case 'DockingGoodsList': //获取商品列表
        $User = xiaochu::verify($_QET);
        xiaochu::GoodsList();
        break;
    case 'Docking_buy': //对接下单
        $User = xiaochu::verify($_QET);
        xiaochu::Buy($User, $_QET);
        break;
    case 'DockingQuery': //订单查询
        $User = xiaochu::verify($_QET);
        xiaochu::QueryApi($User, $_QET);
        break;
    case 'Docking_goods': //获取商品状态/价格等
        $User = xiaochu::verify($_QET);
        $DB = SQL::DB();
        $gid = (int)$_QET['gid'];
        $Goods = $DB->get('goods', '*', ['gid' => $gid, 'method[~]' => '4']);
        if (!$Goods) {
            dies(-1, '商品不存在或此商品无法被对接！');
        }

        /**
         * 同步卡密库存
         */
        if ((int)$Goods['deliver'] === 3) {
            $Goods['quota'] = $DB->count('token', [
                'AND' => [
                    'uid' => 1,
                    'gid' => $Goods['gid']
                ],
            ]);
        }

        if ((int)$Goods['specification'] === 2) {
            $Goods['specification_sku'] = json_decode($Goods['specification_sku'], TRUE);
            $GoodsSku = [];
            foreach ($Goods['specification_sku'] as $key => $value) {
                if ($value['money'] !== '') {
                    $GoodsPrice = Price::Get($value['money'], $Goods['profits'], $User['grade'], $Goods['gid'], $Goods['selling']);
                    $value['money'] = $GoodsPrice['price'];
                    unset($GoodsPrice);
                }
                $GoodsSku[$key] = $value;
            }
            $Sku = $GoodsSku;
        } else {
            $Sku = false;
        }
        $Price = Price::Get($Goods['money'], $Goods['profits'], $User['grade'], $gid, $Goods['selling']);
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'Price' => (float)$Price['price'], //售价
                'Currency' => $Price['points'], //兑换价
                'inventory' => (int)$Goods['quota'], //库存
                'count' => (int)$Goods['quantity'], //每份发货数量
                'SKU' => $Sku,
                'state' => (int)$Goods['state'],
            ],
        ]);
        break;
    case 'UserMoneyApi': //用户加款/扣钱
        if (empty((string)$_QET['token']) || empty((int)$_QET['uid']) || empty((int)$_QET['type']) || empty((float)$_QET['money'])) dies(-1, '请填写完整！');
        if ((string)$_QET['token'] !== $conf['secret']) {
            dies(-1, 'API对接密钥有误！');
        }
        $uid = (int)$_QET['uid'];
        $money = (float)$_QET['money'];
        $DB = SQL::DB();
        $dr = $DB->get('user', ['money'], [
            'id' => $uid,
        ]);
        if (!$dr) dies(-1, '用户不存在！');
        switch ((int)$_QET['type']) {
            case 1: #充值
                $m = $dr['money'] + $money;
                $re = $DB->update('user', [
                    'money' => $m,
                ], [
                    'id' => $uid
                ]);
                break;
            case 2: #扣款
                $m = $dr['money'] - $money;
                if ($m < 0) {
                    dies(-1, '用户余额低于0,无法扣款！');
                }
                $re = $DB->update('user', [
                    'money' => $m,
                ], [
                    'id' => $uid
                ]);
                break;
            default:
                $re = false;
                break;
        }
        if ($re) {
            $msg = '成功通过api为用户(' . $uid . ')' . ($_QET['type'] == 1 ? '加款' : '扣款') . $money . '元,操作后余额为:' . $m . '元!';
            userlog(($_QET['type'] == 1 ? '后台加款' : '后台扣款'), $msg, $uid, $money);
            dies(1, $msg);
        } else {
            dies(-1, ($_QET['type'] == 1 ? '加款' : '扣款') . '失败！');
        }
        break;
    case 'OrdersTesting': //漏单监控(目前只支持易支付！)
        if ($conf['secret'] !== $_QET['token']) {
            dies(-1, 'API对接密钥有误！');
        }
        price_monitoring::pay_order();
        break;
    case 'SubmitOrder': //订单队列监控
        if ($conf['secret'] !== $_QET['token']) {
            dies(-1, 'API对接密钥有误！');
        }
        if (empty((int)$_QET['num'])) {
            $_QET['num'] = 2;
        }
        if ($_QET['num'] > 10) dies(-1, '最多一次监控提交10份订单！');
        Order::SubmitOrderQueue(false, (int)$_QET['num']);
        break;
    case 'SqlBackupsRecovery':
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        SqlBackups::SqlBackupsRecovery($_QET['name'], $_QET['page'], $_QET['limit']);
        break;
    case 'SqlBackupsDownload':
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        SqlBackups::SqlBackupsDownload($_QET['name'], $_QET['date']);
        break;
    case 'SqlBackupsDel':
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        SqlBackups::SqlBackupsDel($_QET['name']);
        break;
    case 'SqlBackupsList':
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        SqlBackups::SqlBackupsList($_QET);
        break;
    case 'SqlBackupsUpdate':
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        SqlBackups::SqlBackupsUpdate($_QET['name']);
        break;
    case 'SqlBackupsDownloadLocal':
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        SqlBackups::SqlBackupsDownloadLocal();
        break;
    case 'SqlBackups':
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        $re = SqlBackups::MysqlBackups();
        if ($re['code'] <> 1) dies(-1, '备份失败！');
        dier($re);
        break;
    case 'OrderList': //取出订单列表
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        if (empty((int)$_QET['state'])) dies(-1, '请将需要取出的订单类型填写完整!<br>类型：1成功，2待处理，3异常，4正在处理，5退款,6售后维权,7已评价');
        if (empty((int)$_QET['limit'])) dies(-1, '请将需要取出的数量填写完整!');
        $DB = SQL::DB();
        $Res = $DB->select('order', ['id', 'order', 'trade_no', 'uid', 'ip', 'input', 'state', 'num', 'return', 'gid', 'order_id', 'money', 'payment', 'take', 'price', 'user_rmb', 'remark', 'finishtime(endtime)', 'addtitm(addtime)'], ['state' => $_QET['state'], 'ORDER' => [
            'id' => 'DESC',
        ], 'LIMIT' => $_QET['limit']]);
        if (!$Res) dies(-1, '订单列表获取失败！');
        dier([
            'code' => 1,
            'msg' => '订单数据获取成功！',
            'class' => '类型(state)：1成功，2待处理，3异常，4正在处理，5退款,6售后维权,7已评价',
            'data' => $Res,
        ]);
        /**
         * 1、打开数据库
         * 2、选择sky_config数据表
         * 3、找到secret对应的参数【密钥】
         * 4、打开：站点/api.php?act=OrderList&token=【密钥】&state=2&limit=9999
         * 5、将取出的数据，全部复制，打开https://www.bt.cn/tools/json.html 格式化
         * 6、取出的数据就是订单数据，state后面的=2是状态码，可以切换：类型(state)：1成功，2待处理，3异常，4正在处理，5退款,6售后维权,7已评价
         */
        break;
    case 'GoodsSet': //修改或读取指定商品参数
        if ($conf['secret'] <> $_QET['token']) dies(-1, 'API对接密钥有误！');
        test(['gid|e', 'data|e', 'type|e'], '参数不完整,请参考开发文档提交完整参数！');
        /**
         * type =1 读取商品，=2修改商品！
         * 当读取商品时：data参数（数组形式）内存放需要读取的字段然后提交即可！
         * GET提交格式：data[]=name&data[]=money
         * POST提交格式：data = ['name','money'];
         * 以上均可读取出商品名称+成本
         *
         * 当修改商品时
         * GET提交格式：data[name]=需要修改的内容&data['money']=需修改的内容
         * POST提交格式：data['name] = xxx;
         */
        $DB = SQL::DB();
        if ((int)$_QET['type'] === 1) {
            $Goods = $DB->get('goods', $_QET['data'], ['gid' => $_QET['gid']]);
            if (!$Goods) {
                dier([
                    'code' => -1,
                    'msg' => '商品读取失败！',
                ]);
            } else dier([
                'code' => 1,
                'msg' => '商品[' . $Goods['name'] . ']参数读取成功！',
                'data' => $Goods,
            ]);
        } else if ((int)$_QET['type'] === 2) {
            $Res = $DB->update('goods', $_QET['data'], ['gid' => $_QET['gid']]);
            if (!$Res) {
                dier([
                    'code' => -1,
                    'msg' => '商品修改失败!',
                ]);
            } else {
                $Goods = $DB->get('goods', '*', ['gid' => $_QET['gid']]);
                Hook::execute('GoodsSet', $Goods);
                dier([
                    'code' => 1,
                    'msg' => '商品参数修改成功！',
                ]);
            }
        } else dies(-1, '未知操作类型！');
        break;
    case 'OrderSet': //修改订单状态,可修改订单状态,返回内容,货源余额,订单备注
        if ($conf['secret'] != $_QET['token']) dies(-1, 'API对接密钥有误！');
        if (empty((int)$_QET['state'])) dies(-1, '请将订单修改后的状态填写完整:state！<br>类型：1成功，2待处理，3异常，4正在处理，5退款,6售后维权,7已评价');
        if (empty($_QET['remark'])) dies(-1, '请将订单备注填写完整:remark!');
        if (empty((float)$_QET['user_rmb'])) $_QET['user_rmb'] = 0;
        if (empty($_QET['order_id'])) dies(-1, '请将货源订单号填写完整:order_id!');
        if (empty($_QET['return'])) dies(-1, '请将对接返回信息填写完整:return!');
        if (empty($_QET['order'])) dies(-1, '请将订单号填写完整:order!');

        $DB = SQL::DB();
        $Res = $DB->update('order', [
            'state' => $_QET['state'],
            'remark' => $_QET['remark'],
            'user_rmb' => $_QET['user_rmb'],
            'return' => $_QET['return'],
            'order_id' => $_QET['order_id'],
        ], [
            'order' => $_QET['order'],
        ]);
        if ($Res) {
            dies(1, '订单[' . $_QET['order'] . ']状态修改成功');
        } else dies(-1, '修改订单状态失败！');
        /**
         * 修改订单数据
         * 1、打开数据库
         * 2、选择sky_config数据表
         * 3、找到secret对应的参数【密钥】
         * 4、打开：站点/api.php?act=OrderSet&token=【密钥】&state=【修改后的状态】&remark=订单备注&order_id=-1&return=-1&order=【根据上方订单获取到的订单号】
         * 5、state后面的=2是状态码，可以切换：类型(state)：1成功，2待处理，3异常，4正在处理，5退款,6售后维权,7已评价
         * 6、order是订单号，长串数字，根据订单号修改订单状态
         */
        break;
    case 'Supervisory': //商品价格监控(轮询接口)
        if ((int)$conf['SupervisorySwitch'] !== 1) {
            dies(-1, '商品价格监控功能未开启！请前往主站后台手动开启！');
        }
        $count = $_QET['num'] ?? 5; //每次监控的商品数量
        if ($count > 60 || $count < 1) {
            dies(-1, '单次商品的监控数量参数提交有误,监控数量范围：1 - 60');
        }
        GoodsMonitoring::BatchMonitoring($count);
        break;
    case 'app_install': #安装指定应用
        test(['identification|e', 'type|e', 'token|e'], '请将参数填写完整哦');
        if ($_QET['token'] != md5($accredit['token'])) dies(-1, '监控密钥有误，无法完成操作！');
        AppList::install($_QET['identification'], (int)$_QET['type']);
        break;
    case 'OffCiaLQuery': //异步更新服务端订单
        official::CallBack($_QET);
        break;
    case 'qq_login': //QQ快捷登录回调
        if ($conf['QQInternetChoice'] === -1) {
            dies(-1, '当前站点QQ快捷登录方式未开启！', 2);
        }
        QuickLogin::QQ_LoginCallback($_QET);
        break;
    case 'HostMonitoring': //主机监控
        if ($conf['secret'] != $_QET['token']) {
            dies(-1, 'API对接密钥有误！');
        }
        monitoring::execute();
        break;
    case 'HostSpaceMonitoring': //主机空间大小配额监控
        if ($conf['secret'] != $_QET['token']) {
            dies(-1, 'API对接密钥有误！');
        }
        monitoring::HostSpaceMonitoring();
        break;
    case 'OrderStatusMonitoring': //订单状态监控
        if ($conf['secret'] != $_QET['token']) {
            dies(-1, 'API对接密钥有误！');
        }
        Order::OrderStatusMonitoring();
        break;
    case 'ConfigSet': //全局数据修改
        if ($accredit['token'] !== $_QET['token']) {
            dies(-1, '通讯密钥有误！');
        }
        unset($_QET['act'], $_QET['token']);
        if (count($_QET) === 0) {
            dies(-1, '请提交完整！');
        }
        $Data = admin::config($_QET);
        if (!$Data || count($Data) === 0) {
            dies(-1, '本次共成功修改了0条数据！');
        } else {
            (new config())->unset_cache();
            dies(1, '数据修改成功,本次成功修改了' . count($Data) . '条数据！');
        }
        break;
    case 'OnebuttonDockingSystem': //同系统对接模块
        OnebuttonDockingSystem::origin($_QET);
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        dies(-2, '访问路径不存在！');
        break;
}
