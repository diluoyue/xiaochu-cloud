<?php

use extend\ImgThumbnail;
use extend\SMS;
use extend\UserConf;
use extend\VerificationCode;
use lib\AppStore\AppList;
use lib\Hook\Hook;
use lib\supply\Order;
use Medoo\DB\SQL;

include '../includes/fun.global.php';
header('Content-Type: application/json; charset=UTF-8');
global $conf, $date, $_QET, $accredit;
admin::safety('login_account,login,login_log,login_token,Send_verification_code_login,VerificationCode,Send_verification_login,login_scan,login_app');
switch ($_QET['act']) {
    case 'SavePayData': //保存支付接口配置数据
        test(['data|i', 'type|i', 'id|i'], '请填写完整！');
        AppList::SavePayData($_REQUEST['data'], $_REQUEST['id'], $_REQUEST['type']);
        break;
    case 'PaySet': //切换支付
        test(['type|i', 'id|i'], '请提交完整！');
        switch ($_QET['type']) {
            case 1:
                $ar = admin::config(['PayConQQ' => $_QET['id']]);
                break;
            case 2:
                $ar = admin::config(['PayConWX' => $_QET['id']]);
                break;
            case 3:
                $ar = admin::config(['PayConZFB' => $_QET['id']]);
                break;
            default:
                dies(-1, '数据异常！');
                break;
        }
        if ($ar) {
            config::unset_cache();
            dies(1, '保存成功,本次成功保存' . count($ar) . '个数据！');
        }

        dies(-1, '保存失败');
        break;
    case 'PayData': //获取支付接口列表数据
        /**
         * 取出支付列表 + 支付配置数据 + 当前选择数据
         */
        $Data = AppList::PayConf();
        $InputArr = [];
        $i = 0;
        foreach ($Data as $v) {

            $InputData = [];
            $ConData[] = AppList::AppConf($v['identification']);

            if ($ConData[$i]['state'] == 2) {
                if ($conf['PayConQQ'] == $v['identification']) {
                    $conf['PayConQQ'] = -1;
                }
                if ($conf['PayConWX'] == $v['identification']) {
                    $conf['PayConWX'] = -1;
                }
                if ($conf['PayConZFB'] == $v['identification']) {
                    $conf['PayConZFB'] = -1;
                }
                ++$i;
                continue;
            }
            $InputArr[] = [
                'InputData' => $ConData[$i]['Data'],
                'name' => $v['name'],
                'type' => explode(',', $v['state']),
                'id' => $v['identification'],
                'input' => explode(',', $v['input']),
            ];
            ++$i;
        }

        if (!file_exists(ROOT . 'includes/lib/soft/conf/' . $conf['PayConQQ'] . '/payconf.json')) $conf['PayConQQ'] = -1;
        if (!file_exists(ROOT . 'includes/lib/soft/conf/' . $conf['PayConWX'] . '/payconf.json')) $conf['PayConWX'] = -1;
        if (!file_exists(ROOT . 'includes/lib/soft/conf/' . $conf['PayConZFB'] . '/payconf.json')) $conf['PayConZFB'] = -1;

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $InputArr,
            'PayConQQ' => $conf['PayConQQ'],
            'PayConWX' => $conf['PayConWX'],
            'PayConZFB' => $conf['PayConZFB'],
            'PayRates' => $conf['PayRates']
        ]);
        break;
    case 'ApplyList': //取出应用列表
        $List = AppList::ApplyList($_QET);
        dier($List);
        break;
    case 'ApplyCount': //获取应用总数
        $List = AppList::ApplyCount($_QET);
        dier($List);
        break;
    case 'AppId': //查询应用ID
        test(['id|e'], '请将应用标识填写完整！');
        $Data = AppList::AppDataS($_QET['id']);
        dier($Data);
        break;
    case 'app_install': //安装指定应用
        if (empty($_QET['identification']) || empty($_QET['type'])) dies(-1, '请将参数填写完整');
        AppList::install($_QET['identification'], (int)$_QET['type']);
        break;
    case 'AppFlieInstAll': //本地应用安装
        if (!strstr($_QET['file']['name'], '.zip')) {
            dies(-1, '只可上传ZIP格式的文件！');
        }
        mkdirs(SYSTEM_ROOT . 'extend/log/Apply'); //安装包存储目录
        move_uploaded_file($_QET['file']['tmp_name'], SYSTEM_ROOT . 'extend/log/Apply/' . $_QET['file']['name']);
        $identification = explode('.', $_QET['file']['name'])[0];
        AppList::install($identification, 3);
        break;
    case 'app_unload': //卸载指定应用
        if (empty($_QET['identification'])) dies(-1, '请将参数填写完整');
        AppList::unload($_QET['identification']);
        break;
    case 'app_view': //打开视图文件！
        if (empty($_QET['path']) || empty($_QET['id'])) show_msg('警告', '提交参数不完整！', false, false, false);
        AppList::view($_QET['id'], $_QET['path']);
        break;
    case 'app_state_set':
        if (empty($_QET['id']) || empty($_QET['type'])) dies(-1, '请将参数填写完整');
        AppList::state_set($_QET['id'], $_QET['type']);
        break;
    case 'app_prolong': //延长到期时间
        if (empty($_QET['id'])) dies(-1, '请将参数填写完整');
        AppList::prolong($_QET['id'], (empty((int)$_QET['value']) ? 1 : $_QET['value']));
        break;
    case 'app_update_log': //更新
        if (empty($_QET['identification'])) dies(-1, '请将参数填写完整');
        $Data = AppList::appdata($_QET['identification']);
        dier($Data);
        break;
    case 'app_pay': //购买
        if (empty($_QET['id'])) dies(-1, '请将参数填写完整');
        AppList::pay($_QET['id'], (empty((int)$_QET['value']) ? 1 : $_QET['value']));
        break;
    case 'app_users': //用户信息查询
        AppList::users();
        break;
    case 'app_help':
        AppList::AppHelp($_QET['id']);
        break;
    case 'prevent':
        if ($conf['prevent_switch'] == 1) {
            dies(1, reward::prevent(href(2), 1));
        } else {
            dies(1, href(2));
        }
        break;
    case 'setGoodsSort': //修改商品排序
        $gid = (int)$_QET['gid'];
        $type = (int)$_QET['type'];
        if (shop::setSort($gid, $type)) {
            dies(1, '修改成功!');
        } else {
            dies(-1, '修改失败!');
        }
        break;
    case 'setClassSort': //修改分类排序
        $cid = (int)$_QET['cid'];
        $type = (int)$_QET['type'];
        if (shop::setSort($cid, $type, 2)) {
            dies(1, '修改成功!');
        } else {
            dies(-1, '修改失败!');
        }
        break;
    case 'NewCarriageTemplate': //运费模板
        $DB = SQL::DB();
        test(['name|e', 'money|i', 'nums|e', 'exceed|e', 'threshold|e'], '请提交完整参数！');
        $SQL = [
            'name' => $_QET['name'],
            'region' => $_QET['region'],
            'money' => $_QET['money'],
            'nums' => $_QET['nums'],
            'exceed' => $_QET['exceed'],
            'threshold' => $_QET['threshold'],
        ];
        if ($_QET['state'] != -1) {
            $Res = $DB->update('freight', $SQL, [
                'id' => $_QET['state']
            ]);
        } else {
            $SQL['date'] = $date;
            $Res = $DB->insert('freight', $SQL);
        }
        if ($Res) {
            dies(1, ($_QET['state'] != -1 ? '修改' : '添加') . '成功!');
        } else {
            dies(-1, '操作失败');
        }
        break;
    case 'setlevelSort': //修改等级排序
        $mid = (int)$_QET['mid'];
        $type = (int)$_QET['type'];
        if (shop::setSort($mid, $type, 3)) {
            dies(1, '修改成功!');
        } else {
            dies(-1, '修改失败!');
        }
        break;
    case 'order_remark': //填写订单备注
        $_QET['id'] = (int)$_QET['id'];
        $_QET['txt'] = (string)$_QET['txt'];
        if (empty($_QET['id']) || empty($_QET['txt'])) dies(-1, '请提交完整参数！');
        $DB = SQL::DB();
        $re = $DB->update('order', [
            'remark' => $_QET['txt'],
        ], [
            'id' => $_QET['id']
        ]);
        if ($re) {
            CookieCache::del('OrderListCountAll');
            dies(1, '修改成功！');
        } else dies(-1, '修改失败');
        break;
    case 'order_logistics': //填写订单物流单号
        $_QET['id'] = (int)$_QET['id'];
        $_QET['txt'] = (string)$_QET['txt'];
        test(['id|e', 'txt|e'], '请提交完整参数!');
        $DB = SQL::DB();
        $re = $DB->update('order', [
            'logistics' => $_QET['txt'],
        ], [
            'id' => $_QET['id']
        ]);
        if ($re) {
            CookieCache::del('OrderListCountAll');
            dies(1, '修改成功！');
        } else {
            dies(-1, '修改失败');
        }
        break;
    case 'BatchFreightTem': //获取商品的运费模板列表
        $DB = SQL::DB();
        $Res = $DB->select('freight', [
            'id', 'name'
        ], [
            'ORDER' => ['id' => 'DESC']
        ]);
        dier([
            'code' => 1,
            'data' => $Res,
        ]);
        break;
    case 'BatchFreightTemEdit': //批量设置商品的运费模板
        test(['id|e'], '请提交完整参数！');
        $DB = SQL::DB();
        $Res = $DB->update('goods', [
            'freight' => $_QET['id'],
        ], [
            'gid' => $_QET['gid'],
        ]);
        if ($Res) {
            dies(1, '批量设置成功');
        } else {
            dies(-1, '批量设置失败！');
        }
        break;
    case 'BatchProfitEdit': //批量设置商品利润比例！
        test(['profits|e'], '请提交完整参数!');
        $DB = SQL::DB();
        $Res = $DB->update('goods', [
            'profits' => $_QET['profits']
        ], [
            'gid' => $_QET['gid']
        ]);
        if ($Res) {
            dies(1, '操作成功');
        } else {
            dies(-1, '操作失败！');
        }
        break;
    case 'ChangeOrders': //修改订单状态
        test(['id|e', 'did|e'], '请提交完整参数！');
        $DB = SQL::DB();
        if ($_QET['id'] == 8) { //删
            $a = $DB->delete('order', [
                'id' => $_QET['did'],
            ]);
        } else { //改
            if ($_QET['id'] == 5) { //退
                $re = $DB->get('order', ['uid', 'payment', 'order'], [
                    'id' => (int)$_QET['did'],
                ]);
                $price = (float)$_QET['money'];
                if ($re['uid'] >= 1) {
                    if ($re['payment'] == '积分兑换') {
                        UserConf::DeductCommission($re['order'], '货币提成');
                        $msg = '管理员帮您在后台进行了退款操作，退款订单号为：' . $re['order'] . ',退款数为：' . round($price, 2) . $conf['currency'] . '！';
                        $DB->update('user', [
                            'currency[+]' => $price,
                        ], [
                            'id' => $re['uid']
                        ]);
                    } else {
                        UserConf::DeductCommission($re['order'], '余额提成');
                        $msg = '管理员帮您在后台进行了退款操作，退款订单号为：' . $re['order'] . ',退款金额为：' . round($price, 2) . '元！';
                        $DB->update('user', [
                            'money[+]' => $price,
                        ], [
                            'id' => $re['uid']
                        ]);
                    }
                    userlog('订单退款', $msg, $re['uid'], $price);
                    $DB->update('order', [
                        'remark' => $msg,
                    ], [
                        'id' => $_QET['did']
                    ]);
                } else {
                    if ($re['payment'] == '积分兑换') {
                        UserConf::DeductCommission($re['order'], '货币提成');
                        $msg = '管理员已经将此订单设置为退款状态，退款订单号为：' . $re['order'] . ',退款数为：' . round($price, 2) . $conf['currency'] . '！';
                    } else {
                        UserConf::DeductCommission($re['order'], '余额提成');
                        $msg = '管理员已经将此订单设置为退款状态，退款订单号为：' . $re['order'] . ',退款金额为：' . round($price, 2) . '元！';
                    }
                    $DB->update('order', [
                        'remark' => $msg,
                    ], [
                        'id' => $_QET['did']
                    ]);
                }
            }
            $a = $DB->update('order', [
                'state' => $_QET['id'],
            ], [
                'id' => $_QET['did']
            ]);
        }
        if ($a) {
            CookieCache::del('OrderListCountAll');
            dies(1, '操作成功');
        } else dies(-1, '操作失败！');
        break;
    case 'replenishment': //订单补单
        test(['id|e'], '请提交完整参数！');
        Order::Retry($_QET['id']);
        break;
    case 'order_details': //查询订单详情
        test(['id|e'], '请提交完整参数！');
        $id = (int)$_QET['id'];
        dier(Order::Query($id, false, 2));
        break;
    case 'level_add': // 保存等级
        test(['name|e', 'money|i', 'pointsis|i', 'ActualProfit|i', 'ProfitThreshold|i', 'content|e']);
        $DB = SQL::DB();
        if (empty($_QET['mid'])) { //新建
            $sort = $DB->get('price', ['sort'], [
                'ORDER' => [
                    'sort' => 'DESC'
                ]
            ]);
            $sort = $sort['sort'] + 1;
            $Res = $DB->insert('price', [
                'sort' => $sort,
                'name' => $_QET['name'],
                'content' => $_POST['content'],
                'priceis' => $_QET['priceis'],
                'pointsis' => $_QET['pointsis'],
                'ActualProfit' => $_QET['ActualProfit'],
                'ProfitThreshold' => $_QET['ProfitThreshold'],
                'money' => $_QET['money'],
                'rule' => $_QET['rule'],
                'addtime' => $date
            ]);
        } else { //保存
            $Res = $DB->update('price', [
                'name' => $_QET['name'],
                'content' => $_POST['content'],
                'priceis' => $_QET['priceis'],
                'pointsis' => $_QET['pointsis'],
                'ActualProfit' => $_QET['ActualProfit'],
                'ProfitThreshold' => $_QET['ProfitThreshold'],
                'rule' => $_QET['rule'],
                'money' => $_QET['money'],
            ], [
                'mid' => $_QET['mid']
            ]);
        }
        if ($Res) {
            dies(1, '操作成功');
        } else dies(-1, '操作失败！');
        break;
    case 'Goods_State_all': //批量调整商品状态
        $_QET['type'] = (int)$_QET['type'];
        if (empty($_QET['type']) || count($_QET['arr']) === 0 || empty($_QET['cid'])) dies(-1, '请提交完整参数');
        $DB = SQL::DB();

        switch ($_QET['type']) {
            case 1: //上架
                foreach ($_QET['arr'] as $value) {
                    $s = $DB->get('goods', ['gid', 'name'], [
                        'gid' => $value
                    ]);
                    if (!$s) dies(-1, '编号为' . $value . '的商品不存在！');
                    Hook::execute('GoodsShow', ['gid' => $value, 'name' => $s['name']]);
                }
                $Res = $DB->update('goods', [
                    'state' => 1,
                ], [
                    'gid' => $_QET['arr']
                ]);
                break;
            case 2: //下架
                foreach ($_QET['arr'] as $value) {
                    $s = $DB->get('goods', ['gid', 'name'], [
                        'gid' => $value
                    ]);
                    if (!$s) dies(-1, '编号为' . $value . '的商品不存在！');
                    Hook::execute('GoodsHide', ['gid' => $value, 'name' => $s['name']]);
                }
                $Res = $DB->update('goods', [
                    'state' => 2,
                ], [
                    'gid' => $_QET['arr']
                ]);
                break;
            case 3: //隐藏
                foreach ($_QET['arr'] as $value) {
                    $s = $DB->get('goods', ['gid', 'name'], [
                        'gid' => $value
                    ]);
                    if (!$s) dies(-1, '编号为' . $value . '的商品不存在！');
                    Hook::execute('GoodsConceal', ['gid' => $value, 'name' => $s['name']]);
                }
                $Res = $DB->update('goods', [
                    'state' => 3,
                ], [
                    'gid' => $_QET['arr']
                ]);
                break;
            case 4: //删
                foreach ($_QET['arr'] as $value) {
                    $s = $DB->get('goods', ['gid', 'name'], [
                        'gid' => $value
                    ]);
                    if (!$s) dies(-1, '编号为' . $value . '的商品不存在！');
                    Hook::execute('GoodsDel', ['gid' => $value, 'name' => $s['name']]);
                }
                $Res = $DB->delete('goods', [
                    'gid' => $_QET['arr']
                ]);
                break;
            case 5: //参数改
                $method = [];
                foreach ($_QET['cid'] as $v) {
                    $method[] = (float)$v;
                }
                $method = json_encode($method);
                $Res = $DB->update('goods', [
                    'method' => $method,
                ], [
                    'gid' => $_QET['arr']
                ]);
                break;
            case 6: //分类
                $Res = $DB->update('goods', [
                    'cid' => $_QET['cid'],
                ], [
                    'gid' => $_QET['arr']
                ]);
                break;
            default:
                dies(-1, '提交的参数不正确');
                break;
        }
        if ($Res) {
            dies(1, '商品调整成功<br>' . json_encode($_QET['arr']));
        } else {
            dies(1, '商品调整失败<br>' . json_encode($_QET['arr']));
        }
        break;
    case 'set_order_all'://批量修改订单状态
        $_QET['type'] = (int)$_QET['type'];
        $_QET['pre'] = (float)$_QET['pre'];
        if (empty($_QET['type']) || count($_QET['arr']) === 0) {
            dies(-1, '请提交完整参数');
        }
        $DB = SQL::DB();
        if ($_QET['type'] === 5) {
            if (empty($_QET['per'])) {
                dies(-1, '退款百分比未填写，无法完成退款！');
            }
            if ($_QET['per'] < 0 || $_QET['per'] > 100) {
                dies(-1, '退款百分比填写错误，仅可填写0-100！，还可填写小数点！');
            }
            //批量退款
            $Res = $DB->select('order', ['uid', 'order', 'payment', 'price'], [
                'state[!]' => $_QET['type'],
                'id' => $_QET['arr'],
            ]);
            try {
                $Count = UserConf::OrderBulkRefund($Res, $_QET['per']);
            } catch (Exception $e) {
                dies(-1, '批量退款订单数太多，崩溃了，请重新尝试，或刷新查看本次批量退款成功数~');
            }
        }
        $Res = $DB->update('order', [
            'state' => $_QET['type'],
        ], [
            'state[!]' => $_QET['type'],
            'id' => $_QET['arr'],
        ]);
        if ($Res) {
            CookieCache::del('OrderListCountAll');
            dies(1, ($_QET['type'] !== 5 ? '批量修改成功！' : '订单批量退款成功,本次共成功退款了' . $Count . '条订单！'));
        } else {
            dies(-1, '批量修改失败！');
        }
        break;
    case 'order_delete_all': //批量删除订单
        $_QET['type'] = (int)$_QET['type'];
        if (count($_QET['arr']) === 0) dies(-1, '请提交完整参数');
        $DB = SQL::DB();
        $Res = $DB->delete('order', [
            'id' => $_QET['arr'],
        ]);
        if ($Res) {
            CookieCache::del('OrderListCountAll');
            dies(1, '批量删除成功！');
        } else {
            dies(-1, '批量删除失败！');
        }
        break;
    case 'order_set': //编辑订单
        test(['id|e', 'field|e', 'value|i', 'name|e'], '请将参数填写完整哦！');
        $DB = SQL::DB();
        $Res = $DB->update('order', [
            $_QET['field'] => $_QET['value']
        ], [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            CookieCache::del('OrderListCountAll');
            dies(1, '成功将订单ID为[' . $_QET['id'] . ']的' . $_QET['name'] . '修改为：' . $_QET['value']);
        } else {
            dies(-1, '修改失败！');
        }
        break;
    case 'set_order': //更新订单下单信息
        $_QET['id'] = (int)$_QET['id'];
        if (empty($_QET['id']) || empty($_QET['input'])) dies(-1, '请提交完整参数');
        $input = json_decode($_POST['input'], TRUE);
        if (count($input) === 0) dies(-1, '数据结构解析异常,请检查是否输入有误?');
        $input = json_encode($input, JSON_UNESCAPED_UNICODE);
        $DB = SQL::DB();
        $Res = $DB->update('order', [
            'input' => $input
        ], [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            CookieCache::del('OrderListCountAll');
            dies(1, '修改成功!');
        } else {
            dies(-1, '执行失败!');
        }
        break;
    case 'add_category': //添加/修改分类
        test(['name|e', 'state|e', 'image|e'], '请提交完整参数');
        $DB = SQL::DB();

        $_QET['state'] = (int)$_QET['state'];

        if (!empty($_QET['cid']) && $_QET['cid'] >= 1) {
            $Class = $DB->get('class', ['state'], ['cid' => $_QET['cid']]);
            if (!$Class) dies(-1, '分类不存在！');
            $Res = $DB->update('class', [
                'content' => $_POST['content'],
                'name' => $_QET['name'],
                'image' => $_QET['image'],
                'state' => $_QET['state'],
                'grade' => $_QET['grade']
            ], [
                'cid' => $_QET['cid']
            ]);
            if ($Res) if ($Class['state'] != $_QET['state']) {
                Hook::execute(($_QET['state'] == 1 ? 'ClassShow' : 'ClassHide'), $_QET);
            }
        } else {
            $Sort = $DB->get('class', ['sort'], [
                'ORDER' => [
                    'sort' => 'DESC'
                ],
                'LIMIT' => 1
            ]);
            $Sort = $Sort['sort'] + 1;

            $Res = $DB->insert('class', [
                'sort' => $Sort,
                'content' => $_POST['content'],
                'name' => $_QET['name'],
                'image' => $_QET['image'],
                'state' => $_QET['state'],
                'grade' => $_QET['grade'],
                'date' => $date
            ]);

            if ($Res) {
                Hook::execute('ClassAdd', $_QET);
            }
        }
        if ($Res) {
            dies(1, '操作成功！');
        } else {
            dies(-1, '操作失败!');
        }
        break;
    case 'article_msg': //添加/修改文章
        test(['title|e', 'content|e', 'image|e']);
        $DB = SQL::DB();
        if (!empty($_QET['aid'])) {
            $Res = $DB->update('notice', [
                'image' => $_QET['image'],
                'title' => $_QET['title'],
                'content' => $_REQUEST['content'],
                'PV' => $_QET['PV'],
            ], [
                'id' => $_QET['aid']
            ]);
        } else {
            $Res = $DB->insert('notice', [
                'image' => $_QET['image'],
                'title' => $_QET['title'],
                'content' => $_REQUEST['content'],
                'PV' => $_QET['PV'],
                'date' => $date,
            ]);
        }
        if ($Res) {
            dies(1, '保存成功!');
        } else dies(-1, '保存失败!');
        break;
    case 'config_set': //保存网站信息
        $str = 'notice_top,notice_check,notice_bottom,notice_user,statistics,PopupNotice,ServiceTips,HostAnnounced';
        $Res = admin::config($_QET, $str, $_POST);
        if ($Res) {
            (new config())->unset_cache();
            dies(1, '保存成功,本次成功保存' . count($Res) . '个数据！');
        } else  dies(-1, '保存失败！');
        break;
    case 'login_scan': //获取登陆二维码
        admin::login_scan();
        break;
    case 'login_app': //登陆后台APP
        admin::login_fa(2);
        break;
    case 'login': //登陆后台
        admin::login_fa(1);
        break;
    case 'login_account': //账号密码登陆
        if (empty($_QET['vercode'])) dies(-2, '请将验证码填写完整！');
        if (empty($_SESSION['Login_uvc']) || $_SESSION['Login_uvc'] <> md5($_QET['vercode'] . href())) dies(-2, '验证码有误！');

        $_SESSION['Login_uvc'] = null;

        admin::login_account($_QET);
        break;
    case 'login_log': //确认消息
        admin::login_log();
        break;
    case 'login_token': //确认消息2
        admin::login_token($_QET['token']);
        break;
    case 'UpdateInspection': //站长数据+版本检测
        admin::UpdateInspection(($_QET['type'] == 2 ? 2 : 1));
        break;
    case 'Get_notice_ch': //获取彩虹代刷公告
        admin::Get_notice_ch($_QET['url']);
        break;
    case 'Get_notice_xc': //获取小储系统公告
        admin::Get_notice_xc($_QET['url']);
        break;
    case 'image_content': //编辑器专用
        unset($_QET['act']);
        $ImageArr = [];
        $FileName = date('Ymd');
        mkdirs('../assets/img/image/' . $FileName . '/');
        foreach ($_QET as $key => $value) {
            $ImageName = md5_file($value['tmp_name']);
            switch ($value['type']) {
                case 'image/gif':
                    $ImageName .= '.gif';
                    break;
                case 'image/jpeg':
                    $ImageName .= '.jpeg';
                    break;
                case 'image/png':
                default:
                    $ImageName .= '.png';
                    break;
            }
            move_uploaded_file($value['tmp_name'], '../assets/img/image/' . $FileName . '/' . $ImageName);
            $images = '/assets/img/image/' . $FileName . '/' . $ImageName;
            new ImgThumbnail(ROOT . $images, $conf['compression'], ROOT . $images, 2);
            $ImageArr[] = ['src' => ImageUrl($images), 'size' => $value['size'] / 1000 . 'kb', 'name' => $value['name']];
        }
        dier([
            'code' => 1,
            'msg' => '图片上传成功,本次共成功上传' . count($ImageArr) . '张图片',
            'SrcArr' => $ImageArr,
        ]);
        break;
    case 'image_up': //上传图片
        $image = explode('.', $_QET['file']['name']);
        if ($_QET['file']['type'] !== 'image/png' && $_QET['file']['type'] !== 'image/gif' && $_QET['file']['type'] !== 'image/jpeg') {
            dies(-1, '只可上传png/jpg/gif类型的图片文件！');
        }
        $FileName = date('Ymd');
        mkdirs('../assets/img/image/' . $FileName . '/');
        $ImageName = md5_file($_QET['file']['tmp_name']);
        switch ($_QET['file']['type']) {
            case 'image/gif':
                $ImageName .= '.gif';
                break;
            case 'image/jpeg':
                $ImageName .= '.jpeg';
                break;
            case 'image/png':
            default:
                $ImageName .= '.png';
                break;
        }
        move_uploaded_file($_QET['file']['tmp_name'], '../assets/img/image/' . $FileName . '/' . $ImageName);
        $images = '/assets/img/image/' . $FileName . '/' . $ImageName;
        new ImgThumbnail(ROOT . $images, $conf['compression'], ROOT . $images, 2);
        dier([
            'code' => 0,
            'msg' => '上传成功,上传的图片大小为:' . $_QET['file']['size'] / 1000 . 'kb',
            'src' => ImageUrl($images)
        ]);
        break;
    case 'conversion':
        (new admin)->conversion($_QET);
        break;
    case 'goods_content': //获取商品内容
        test(['gid|e', 'field|e'], '请将商品ID填写完整！');
        $DB = SQL::DB();
        $Res = $DB->get('goods', [$_QET['field']], [
            'gid' => (int)$_QET['gid'],
        ]);
        if (!$Res) dies(-1, '数据获取失败！');
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Res[$_QET['field']]
        ]);
        break;
    case 'BatchSort': //获取商品列表(批量排序)
        $DB = SQL::DB();
        $Res = $DB->select('goods', ['gid', 'name', 'sort', 'money', 'quota', 'state', 'sqid'], [
            'ORDER' => [
                'sort' => 'ASC'
            ],
            'LIMIT' => 100
        ]);
        $Data = [];
        foreach ($Res as $v) {
            if ((int)$v['sqid'] === -3) {
                $v['quota'] = $DB->count('token', [
                    'uid' => 1,
                    'gid' => $v['gid'],
                ]);
            }
            $Data[] = $v;
        }
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
            'count' => $DB->count('goods'),
        ]);
        break;
    case 'BatchSortSettings': //批量商品排序设置
        test(['id|e', 'type|e']);
        $DB = SQL::DB();
        $_QET['type'] = (int)$_QET['type'];

        switch ((int)$_QET['id']) {
            case 1: //商品ID
                $SQL = [
                    'ORDER' => [
                        'gid' => ($_QET['type'] == 1 ? 'DESC' : 'ASC')
                    ],
                ];
                break;
            case 2: //商品名称
                $SQL = [
                    'ORDER' => [
                        'name' => ($_QET['type'] == 1 ? 'DESC' : 'ASC')
                    ],
                ];
                break;
            case 3: //商品成本
                $SQL = [
                    'ORDER' => [
                        'money' => ($_QET['type'] == 1 ? 'DESC' : 'ASC')
                    ],
                ];
                break;
            case 4: //商品库存
                $SQL = [
                    'ORDER' => [
                        'quota' => ($_QET['type'] == 1 ? 'DESC' : 'ASC')
                    ],
                ];
                break;
            case 5: //商品状态
                $SQL = [
                    'ORDER' => [
                        'state' => ($_QET['type'] == 1 ? 'DESC' : 'ASC')
                    ],
                ];
                break;
            default:
                dies(-1, '提交有误！');
                break;
        }

        $Res = $DB->select('goods', ['gid', 'name', 'sort', 'money', 'quota', 'state'], $SQL);
        $Data = ['success' => 0, 'error' => []];
        foreach ($Res as $k => $v) {
            $Re = $DB->update('goods', [
                'sort' => ($k + 1)
            ], [
                'gid' => $v['gid']
            ]);
            if ($Re) {
                ++$Data['success'];
            } else $Data['error'][$k] = '数据写入错误';
        }
        dier([
            'code' => 1,
            'msg' => '商品调整成功！',
            'data' => $Data,
            'count' => count($Res),
        ]);
        break;
    case 'CouponExport': //优惠券导出
        if (!isset($_QET['gid']) || empty($_QET['type'])) show_msg('温馨提示', '请将参数填写完整！', 2);
        $DB = SQL::DB();

        switch ((int)$_QET['type']) {
            case 1: //全部
                $SQL = [
                    'ORDER' => [
                        'id' => 'DESC'
                    ],
                ];
                break;
            case 2: //未兑换
                $SQL = [
                    'ORDER' => [
                        'id' => 'DESC'
                    ],
                    'uid' => -1
                ];
                break;
            case 3: //已兑换
                $SQL = [
                    'ORDER' => [
                        'id' => 'DESC'
                    ],
                    'uid[!]' => -1,
                ];
                break;
            default:
                dies(-1, '未知请求！');
                break;
        }

        if (empty($_QET['gid'])) {
            $GID = '';
        } else {
            $SQL = array_merge($SQL, [
                'gid' => $_QET['gid']
            ]);
        }
        $Res = $DB->select('coupon', ['token', 'money', 'minimum', 'type', 'name', 'uid', 'oid', 'content'], $SQL);
        $Data = [];
        foreach ($Res as $v) {
            switch ($v['type']) {
                case 1:
                    $msg = '满减券(' . $v['name'] . ')---' . $v['token'] . '---付款金额满' . round($v['minimum'], 2) . '元,优惠' . round($v['money'], 2) . '元';
                    break;
                case 2:
                    $msg = '立减券(' . $v['name'] . ')---' . $v['token'] . '---付款时可优惠' . round($v['money'], 2) . '元';
                    break;
                case 3:
                    $msg = '折扣券(' . $v['name'] . ')---' . $v['token'] . '---付款金额满' . round($v['minimum'], 2) . '元,可享' . ($v['money'] / 10) . '折!';
                    break;
            }
            $Data[] = $msg . '---' . $v['content'] . ($v['uid'] != -1 ? '---领取者UID(' . $v['uid'] . ')' : '') . ($v['oid'] != -1 ? '---使用订单id(' . $v['oid'] . ')' : '');
        }
        if (count($Data) == 0) show_msg('温馨提示', '导出失败,无可导出优惠券！', 2);

        $FilePath = '/includes/extend/log/Cache/Coupon_' . md5($accredit['token']) . '_' . (empty($_QET['gid']) ? 'all' : $_QET['gid']) . '_' . $_QET['type'] . '.txt';
        if (file_put_contents(ROOT . $FilePath, implode("\n", $Data))) {
            $file = ROOT . $FilePath;
            if (file_exists($file)) {
                header('Content-type:application/octet-stream');
                $filename = basename($file);
                header('Content-Disposition:attachment;filename = ' . $filename);
                header('Accept-ranges:bytes');
                header('Accept-length:' . filesize($file));
                readfile($file);
            } else show_msg('温馨提示', '导出失败,请检查includes目录是否开启了文件创建写入权限！', 2);
        } else show_msg('温馨提示', '导出失败,请联系管理员处理！', 2);
        break;
    case 'kami_derive': //卡密导出
        if (!isset($_QET['gid']) || empty($_QET['type'])) show_msg('温馨提示', '请将参数填写完整！', 2);
        $DB = SQL::DB();
        switch ((int)$_QET['type']) {
            case 1: //全部
                $SQL = [
                    'ORDER' => [
                        'kid' => 'DESC'
                    ],
                ];
                break;
            case 2: //未使用
                $SQL = [
                    'ORDER' => [
                        'kid' => 'DESC'
                    ],
                    'uid' => 1,
                ];
                break;
            case 3: //已使用
                $SQL = [
                    'ORDER' => [
                        'kid' => 'DESC'
                    ],
                    'uid[!]' => 1,
                ];
                break;
            default:
                dies(-1, '未知请求！');
                break;
        }

        if (empty($_QET['gid'])) {
            $GID = '';
        } else {
            $SQL['gid'] = $_QET['gid'];
        }
        $Res = $DB->select('token', ['token'], $SQL);
        foreach ($Res as $v) $Data[] = $v['token'];

        if (count($Data) == 0) show_msg('温馨提示', '导出失败,无可导出卡密！', 2);
        mkdirs(ROOT . 'includes/extend/log/Cache/');
        $FilePath = '/includes/extend/log/Cache/Cache_' . md5($accredit['token']) . '_' . (empty($_QET['gid']) ? 'all' : $_QET['gid']) . '_' . $_QET['type'] . '.txt';
        if (file_put_contents(ROOT . $FilePath, implode("\n", $Data))) {
            $file = ROOT . $FilePath;
            if (file_exists($file)) {
                header('Content-type:application/octet-stream');
                $filename = basename($file);
                header('Content-Disposition:attachment;filename = ' . $filename);
                header('Accept-ranges:bytes');
                header('Accept-length:' . filesize($file));
                readfile($file);
            } else {
                show_msg('温馨提示', '导出失败,请检查includes目录是否开启了文件创建写入权限！', 2);
            }
        } else {
            show_msg('温馨提示', '导出失败,请联系管理员处理！', 2);
        }
        break;
    case 'explain_set': //商品说明书
        if (empty($_QET['explain']) || empty($_QET['gid'])) dies(-1, '参数不完整！');
        $DB = SQL::DB();
        $Res = $DB->update('goods', [
            'explain' => $_QET['explain']
        ], [
            'gid' => $_QET['gid'],
        ]);
        if ($Res) {
            dies(1, '说明书设置成功，用户购买此商品后可看到您设置的提示内容！');
        } else dies(-1, '说明书设置失败');
        break;
    case 'note_set': //商品备注信息
        if (empty($_QET['note']) || empty($_QET['gid'])) dies(-1, '参数不完整！');
        $DB = SQL::DB();
        $Res = $DB->update('goods', [
            'note' => $_QET['note']
        ], [
            'gid' => $_QET['gid'],
        ]);
        if ($Res) {
            dies(1, '备注设置成功！');
        } else {
            dies(-1, '备注设置失败');
        }
        break;
    case 'kami_add': //添加卡密
        if (empty($_QET['kam_arr']) || empty($_QET['gid'])) {
            dies(-1, '参数不完整！');
        }
        if (count($_QET['kam_arr']) === 0) {
            dies(-1, '请添加多张卡密！');
        }
        $_QET['gid'] = (int)$_QET['gid'];
        $DB = SQL::DB();
        $SQL = [];
        foreach ($_QET['kam_arr'] as $v) {
            if (empty($v)) continue;
            $SQL[] = [
                'gid' => $_QET['gid'],
                'token' => $v,
                'addtime' => $date,
            ];
        }
        $Res = $DB->insert('token', $SQL);
        CookieCache::del('TokenListCountAll');
        dies(1, '本次成功添加' . count($SQL) . '张卡密！');
        break;
    case 'kami_empty': //清空卡密
        $DB = SQL::DB();
        $SQL = [];
        if (!empty($_QET['gid'])) {
            $SQL['gid'] = $_QET['gid'];
        }
        $Res = $DB->delete('token', $SQL);
        if ($Res) {
            CookieCache::del('TokenListCountAll');
            dies(1, '卡密清空成功！');
        } else {
            dies(-1, '卡密清空失败');
        }
        break;
    case 'kami_empty_use': //清空库存卡
        $DB = SQL::DB();
        if (empty($_QET['gid'])) {
            $SQL = [
                'order[!]' => '',
            ];
        } else {
            $SQL = [
                'order[!]' => '',
                'gid' => $_QET['gid']
            ];
        }
        $Res = $DB->delete('token', $SQL);
        if ($Res) {
            CookieCache::del('TokenListCountAll');
            dies(1, '卡密清空成功！');
        } else dies(-1, '卡密清空失败');
        break;
    case 'sms_data': //获取短信信息
        SMS::SmsData();
        break;
    case 'config_set_sms': //保存短信配置
        if (empty($_QET['sms_switch_order']) || empty((int)$_QET['Mobile'])) {
            $Res = admin::config($_QET, '', $_POST);
            if ($Res) {
                (new config())->unset_cache();
                dies(1, '保存成功,本次成功保存' . count($Res) . '个数据！');
            } else  dies(-1, '保存失败！');
        }
        $str = '';
        $arr = ['sms_switch_user' => $_QET['sms_switch_user'], 'sms_switch_order' => $_QET['sms_switch_order'], 'weix_notice' => $_QET['weix_notice']];
        $smsset = SMS::SmsUserSet($_QET['Mobile']);
        $Res = admin::config($arr, $str, $arr);
        if ($Res && $smsset == true) {
            config::unset_cache();
            dier(['code' => '1', 'msg' => '保存成功,本次成功保存' . (count($Res) + 1) . '个数据！']);
        }
        dier([
            'code' => -1,
            'msg' => '数据保存失败！',
        ]);
        break;
    case 'VerificationCode': //创建登陆二维码图片
        if (empty($_QET['n'])) dies(-1, '请提交完整参数！');
        VerificationCode::RandomVerificationCode($_QET['n']);
        break;
    case 'Send_verification_code_login': //短信登陆
        test(['code|e'], '请将验证码填写完整!');
        if (empty($_SESSION['AdminLogin_sms_vis']) || $_SESSION['AdminLogin_sms_vis'] != md5($_QET['code'] . href())) {
            dies(-2, '验证码有误！');
        }
        $_SESSION['AdminLogin_sms_vis'] = null;
        if (empty((int)$_QET['mobile'])) {
            dies(-1, '请将手机号填写完整！');
        }
        SMS::SmsAdminLogin($_QET['mobile']);
        break;
    case 'Send_verification_login': //短信验证
        test(['code|e'], '请将验证码填写完整!');
        SMS::SmsAdminVerify($_QET['code']);
        break;
    case 'admin_ip': //服务器IP地址
        die(get_curl(href(2) . ROOT_DIR_S . '/api.php?act=ip'));
        break;
    case 'withdraw_deposit':
        $DB = SQL::DB();
        $LIMIT = $_QET['limit'];
        $Page = ($_QET['page'] - 1) * $LIMIT;
        $data = $DB->select('withdrawal', '*', [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $LIMIT],
        ]);
        $count = $DB->count('withdrawal');
        $DataArr = [];
        foreach ($data as $re) {
            $FileName = md5($re['uid'] . '晴玖');
            $images = ROOT_DIR . 'assets/img/withdraw/' . $FileName . '/' . $re['uid'] . '.png?time=' . time();
            $re['image'] = $images;
            $re['arrival_money'] = round($re['money'] - (($conf['userdepositservice'] / 100) * $re['money']), 2);
            $DataArr[] = $re;
        }
        dier([
            'code' => 0,
            'msg' => '数据获取成功',
            'count' => $count,
            'data' => $DataArr
        ]);
        break;
    case 'withdraw_deposit_data': //获取提现数据，计算单用户总待提现金额
        $id = (int)$_QET['id'];
        if (empty($id)) dies(-1, '请提交完整参数！');
        $DB = SQL::DB();
        $data = $DB->get('withdrawal', '*', [
            'id' => $id
        ]);
        if (!$data) dies(-1, '数据获取失败！');
        if ($data['state'] == 3) { //待处理
            $count = $DB->count('withdrawal', [
                'uid' => $data['uid'],
                'state' => 3,
            ]);
            //获取相同的数据
            $FileName = md5($data['uid'] . '晴玖');
            $images = ROOT_DIR . 'assets/img/withdraw/' . $FileName . '/' . $data['uid'] . '.png?time=' . time();
            $count_pay_1 = $DB->sum('withdrawal', 'money', [
                    'uid' => $data['uid'],
                    'state' => 3,
                ]) - 0;
            if ($count > 1) { //有多个待提现数据！
                $count_pay = $DB->sum('withdrawal', 'money', [
                        'uid' => $data['uid'],
                        'state' => 3,
                    ]) - 0;
                $data_arr = [
                    'code' => 1,
                    'msg' => '数据获取成功',
                    'uid' => $data['uid'],
                    'money_ar' => $count_pay_1,
                    'money' => round($count_pay_1 - (($conf['userdepositservice'] / 100) * $count_pay_1), 2),
                    'account' => $data['account_number'], //收款账号
                    'name' => $data['name'],
                    'remark' => $data['remarks'],
                    'image' => $images,
                    'count' => $count
                ];
            } else { //只有一个
                $data_arr = [
                    'code' => 1,
                    'msg' => '数据获取成功',
                    'uid' => $data['uid'],
                    'money_ar' => $count_pay_1,
                    'money' => round($count_pay_1 - (($conf['userdepositservice'] / 100) * $count_pay_1), 2),
                    'account' => $data['account_number'], //收款账号
                    'name' => $data['name'],
                    'remark' => $data['remarks'],
                    'image' => $images,
                    'count' => 1
                ];
            }
            dier($data_arr);
        } else { //删除
            $Res = $DB->delete('withdrawal', [
                'id' => $id,
            ]);
            if ($Res) {
                dies(2, '删除成功');
            } else {
                dies(-1, '删除失败');
            }
        }
        break;
    case 'withdraw_deposit_result':
        if (empty($_QET['uid']) || empty($_QET['id']) || empty($_QET['type']) || empty($_QET['money'])) dies(-1, '请提交完整参数！');
        $DB = SQL::DB();

        $Res = $DB->update('withdrawal', [
            'state' => ($_QET['type'] == 1 ? 1 : 2),
            'result_code' => $_QET['result'],
            'endtime' => $date,
        ], [
            'uid' => $_QET['uid'],
            'state' => 3
        ]);
        if ($Res) {
            if ($_QET['type'] == 1) {
                Hook::execute('WithdrawAudit', $_QET);
                $get = $DB->get('user', '*', [
                    'id' => $_QET['uid'],
                ]);
                userlog('提现成功', '管理员已经帮您处理提现，累计提现金额为：' . $_QET['money'] . ',再接再厉！', $_QET['uid'], $_QET['money']);
            } else { //退款
                userlog('提现失败', '提现审核失败(' . $_QET['result'] . ')金额' . $_QET['money'] . '元已退回到您的账户！', $_QET['uid'], $_QET['money']);
                $MONEYS = (float)$_QET['money'];
                @$DB = $DB->update('user', [
                    'money[+]' => $MONEYS,
                ], [
                    'id' => $_QET['uid']
                ]);
            }
            dies(1, '提现状态修改成功！');
        } else dies(-1, '提现处理失败！');
        break;
    case 'security_filename_log':
        $page = ((int)$_GET['page'] - 1) * (int)$_GET['limit'];
        $limit = (int)$_GET['limit'] + $page;
        mkdirs(ROOT . 'includes/extend/log/safety/');
        $CONTENT = json_decode(file_get_contents(ROOT . 'includes/extend/log/safety/' . $_QET['filename']), TRUE);

        $array = [];
        $list = 0;
        foreach ($CONTENT as $key => $value) {
            ++$list;
            if ($list >= $page && $list <= $limit) {
                $value['date'] = $key;
                $array[] = $value;
            } else continue;
        }
        dier([
            'code' => 0,
            'msg' => '日志获取成功',
            'count' => count($CONTENT),
            'data' => $array
        ]);

        break;
    case 'security_log':
        $page = ((int)$_GET['page'] - 1) * (int)$_GET['limit'];
        $limit = (int)$_GET['limit'] + $page;
        $dir = ROOT . 'includes/extend/log/safety/';
        $file = scandir($dir);
        $array = [];
        $list = 0;
        foreach (array_reverse($file) as $value) {
            if ($value == '.' || $value == '..') continue;
            ++$list;
            if ($list >= $page && $list <= $limit) {
                $arr = explode('_', $value);
                $CONTENT = json_decode(file_get_contents(ROOT . 'includes/extend/log/safety/' . $value), TRUE);
                $array[] = ['date' => explode('.', $arr[1])[0], 'filename' => $value, 'count' => count($CONTENT) . '条'];
            } else continue;
        }
        dier([
            'code' => 0,
            'msg' => '日志获取成功',
            'count' => count($file) - 2,
            'data' => $array
        ]);
        break;
    case 'OrderDel':
        if (empty((int)$_QET['id'])) dies(-1, '请提交完整参数！');
        $id = (int)$_QET['id'];
        $DB = SQL::DB();
        $Res = $DB->delete('queue', [
            'id' => $id,
        ]);
        if ($Res) {
            CookieCache::del('OrderListCountAll');
            dies(1, '编号' . $id . '的订单已经成功删除！');
        } else dies(-1, '订单删除失败');
        break;
    case 'Tickets': //工单管理
        if (!isset($_QET['type'])) dies(-1, '请提交完整参数');
        $DB = SQL::DB();
        switch ($_QET['type']) {
            case 'list':
                if ($_QET['state'] == 'all') {
                    $List = $DB->select('tickets', ['id', 'name', 'class', 'type', 'state', 'addtime', 'grade'], ['ORDER' => ['id' => 'DESC']]);
                } else {
                    $List = $DB->select('tickets', ['id', 'name', 'class', 'type', 'state', 'addtime', 'grade'], ['state' => $_QET['state'], 'ORDER' => ['id' => 'DESC']]);
                }
                dier([
                    'code' => 1,
                    'msg' => '工单列表获取成功',
                    'data' => $List,
                ]);
                break;
            case 'details': //工单详情
            case 'Supplementary': //补充内容
            case 'Finish': //完结工单
                $Tickets = $DB->get('tickets', '*', ['id' => $_QET['id']]);
                if (!$Tickets) dies(-1, '订单不存在！');
                if ($Tickets['message'] == '') {
                    $Data = [];
                } else {
                    $Data = config::common_unserialize($Tickets['message']);
                }
                if ($_QET['type'] == 'details') {
                    dier([
                        'code' => 1,
                        'msg' => '数据获取成功',
                        'data' => $Data,
                        'class' => $Tickets['class'],
                        'time' => $Tickets['timetips'],
                        'order' => ($Tickets['order'] == null ? '无相关订单' : $Tickets['order']),
                        'state' => $Tickets['state'],
                        'grade' => $Tickets['grade'],
                        'count' => count($Data),
                        'type' => $Tickets['type'],
                    ]);
                } else if ($_QET['type'] == 'Supplementary') {
                    if ($Tickets['type'] == 4) dies(-1, '此工单已经关闭,请重新创建新的工单！');
                    if ($Tickets['type'] >= 3 || $Tickets['state'] == 3) dies(-1, '工单已完结,若要提交内容请创建新的工单!');

                    $Data = array_merge($Data, [
                        $date => [
                            'type' => 2,
                            'content' => $_QET['content'],
                        ]
                    ]);

                    $Res = $DB->update('tickets', [
                        'message' => $Data,
                        'type' => 2,
                    ], [
                        'id' => $_QET['id'],
                    ]);
                    if ($Res) {
                        Hook::execute('WorkOrderReply', [
                            'identity' => '客服',
                            'content' => $_QET['content'],
                            'id' => $_QET['id']
                        ]);
                        dies(1, '工单回复成功！');
                    } else dies(-1, '工单回复失败');
                } else if ($_QET['type'] == 'Finish') {
                    $Res = $DB->update('tickets', [
                        'state' => 3,
                        'type' => 4,
                        'endtime' => $date,
                    ], [
                        'id' => $_QET['id'],
                    ]);
                    if ($Res) {
                        if ($Tickets['order'] <> '不选择相关订单') {
                            @$DB->update('order', ['state' => 1], ['order' => $Tickets['order']]);
                        }
                        Hook::execute('WorkOrderEnd', [
                            'identity' => '客服',
                            'id' => $_QET['id']
                        ]);
                        dies(1, '工单[' . $_QET['id'] . ']成功关闭,用户将无法回复工单！');
                    } else dies(-1, '关闭失败！');
                }
                break;
            default:
                dies(-1, '403');
                break;
        }
        break;
    case 'Mark': //评论管理
        $DB = SQL::DB();
        switch ($_QET['type']) {
            case 'List':
                $Res = $DB->select('mark', '*', [
                    'ORDER' => [
                        'id' => 'DESC'
                    ],
                    'LIMIT' => [(($_QET['page'] - 1) * $_QET['limit']), $_QET['limit']],
                ]);
                $Count = $DB->count('mark');
                dier([
                    'code' => 0,
                    'msg' => '获取成功',
                    'data' => $Res,
                    'count' => $Count,
                ]);
                break;
            case 'state':
                if ($_QET['state'] == 4) {
                    $Res = $DB->delete('mark', ['id' => $_QET['id']]);
                } else {
                    $Res = $DB->update('mark', [
                        'state' => $_QET['state'],
                    ], ['id' => $_QET['id']]);
                }
                if ($Res) {
                    Hook::execute('AppraiseAudit', [
                        'state' => $_QET['state'],
                        'id' => $_QET['id']
                    ]);
                    dies(1, '操作成功');
                } else {
                    dies(-1, '评论审核失败!');
                }
                break;
            case 'stateAll': //批量调整
                switch ($_QET['state']) {
                    case 1:
                        $Res = $DB->update('mark', [
                            'state' => 1,
                        ], [
                            'id' => $_QET['arr'],
                        ]);
                        break;
                    case 2:
                        $Res = $DB->update('mark', [
                            'state' => 3,
                        ], [
                            'id' => $_QET['arr'],
                        ]);
                        break;
                    case 3:
                        $Res = $DB->delete('mark', [
                            'id' => $_QET['arr'],
                        ]);
                        break;
                    default:
                        dies(-1, '参数异常！');
                        break;
                }
                if (!$Res) {
                    dies(-1, '操作失败！');
                }
                dies(1, '操作成功！');
                break;
            default:
                dies(-1, '参数异常');
                break;
        }
        break;
    case 'RuleList': //获取规则
        if (count(Rule) == 0) dies(-1, '无任何规则！');
        dier([
            'code' => 1,
            'msg' => '规则获取成功',
            'data' => Rule
        ]);
        break;
    case 'RulePreserve': //保存规则
        test(['id|e']);
        $Data = [];
        foreach (Rule as $key => $value) {
            if ($key == $_REQUEST['id']) {
                $value = $_REQUEST['data'];
            }
            $Data += [$key => $value];
        }
        $Res = AppList::RuleSet($Data);
        if ($Res['code'] >= 0) {
            dies(1, '匹配字段[' . $_REQUEST['id'] . ']的规则保存成功！');
        } else dies(-1, '保存失败！');
        break;
    case 'RuleMatching': //修改匹配规则键值
        test(['id|e', 'value|e']);
        $Data = [];
        if ($_REQUEST['id'] == $_REQUEST['value']) dies(-1, '无改动！');
        foreach (Rule as $key => $value) {
            if ($key == $_REQUEST['id']) {
                $key = $_REQUEST['value'];
            }
            $Data += [$key => $value];
        }
        $Res = AppList::RuleSet($Data);
        if ($Res['code'] >= 0) {
            dies(1, '匹配字段[' . $_REQUEST['id'] . ']成功修改为[' . $_QET['value'] . ']！');
        } else dies(-1, '保存失败！');
        break;
    case 'RuleAdd':
        test(['id|e'], '请将匹配字段填写完整！');

        if (empty($_QET['data']['name']) || empty($_QET['data']['placeholder']) || empty($_QET['data']['type'])) dies(-1, '请提交完整参数！');

        switch ((int)$_REQUEST['data']['type']) {
            case -1:
                if (empty($_REQUEST['data']['url']) || empty($_REQUEST['data']['way'])) {
                    dies(-1, '请提交完整参数！');
                }
                $Data = [$_REQUEST['id'] => [
                    'name' => $_REQUEST['data']['name'],
                    'type' => -1,
                    'url' => $_REQUEST['data']['url'],
                    'way' => $_REQUEST['data']['way'],
                    'placeholder' => $_REQUEST['data']['placeholder'],
                ]];
                break;
            case 1:
                $Data = [$_REQUEST['id'] => [
                    'name' => $_REQUEST['data']['name'],
                    'type' => 1,
                    'way' => 1,
                    'placeholder' => $_REQUEST['data']['placeholder'],
                ]];
                break;
            case 2:
                $Data = [$_REQUEST['id'] => [
                    'name' => $_REQUEST['data']['name'],
                    'type' => 2,
                    'url' => $_REQUEST['data']['url'],
                    'way' => 1,
                    'placeholder' => $_REQUEST['data']['placeholder'],
                ]];
                break;
            default:
                dies(-1, '数据提交有误！');
        }
        $Data = $Data + Rule;
        $Res = AppList::RuleSet($Data);
        if ($Res['code'] >= 0) {
            dies(1, '成功将匹配字段[' . $_QET['id'] . ']新增至规则列表！');
        } else dies(-1, '新增失败！');
        break;
    case 'RuleUnset': //删除规则列表！
        test(['id|e'], '请将键值填写完整！');
        $Data = [];
        foreach (Rule as $key => $value) {
            if ($key == $_QET['id']) continue;
            $Data += [$key => $value];
        }
        $Res = AppList::RuleSet($Data);
        if ($Res['code'] >= 0) {
            dies(1, '匹配字段[' . $_QET['id'] . ']删除成功！');
        } else dies(-1, '删除失败！');
        break;
    case 'HoverList': //读取悬停应用列表！
        $Data = AppList::HoverList();
        if ($Data == false) dies(-1, '无有效悬停应用！');
        dier([
            'code' => 1,
            'msg' => '悬停应用列表获取成功',
            'data' => $Data
        ]);
        break;
    case 'HoverOn': //开启悬停
        test(['id|e'], '请将需要开启悬停的应用ID填写完整！');
        $dir = ROOT . 'includes/lib/soft/conf/' . $_QET['id'];
        if (!is_dir($dir)) dies(-1, 'id为[' . $_QET['id'] . ']的应用不存在！');
        $Data = AppList::HoverList(2);
        if ($Data == false) {
            $Data = [$_QET['id']];
        } else {
            if (count($Data) >= 9) dies(-1, '最多一次悬停9个应用哦！');
            if (in_array($_QET['id'], $Data)) dies(-1, '此应用已经开启悬停,无需重复开启！');
            $Data = array_merge([$_QET['id']], $Data);
        }
        $Res = AppList::HoverSet($Data);
        if ($Res) {
            dies(1, '启用成功！');
        } else dies(-1, '启用失败！');
        break;
    case 'HoverOff': //关闭悬停
        test(['id|e']);
        $dir = ROOT . 'includes/lib/soft/conf/' . $_QET['id'];
        if (!is_dir($dir)) dies(-1, 'id为[' . $_QET['id'] . ']的应用不存在！');
        $Data = AppList::HoverList(2);
        if (!in_array($_QET['id'], $Data)) dies(-1, '此应用未开启悬停,无法执行关闭操作！');
        $Arr = [];
        foreach ($Data as $v) {
            if ($v == $_QET['id']) continue;
            $Arr[] = $v;
        }
        $Res = AppList::HoverSet($Arr);
        if ($Res) {
            dies(1, '关闭成功！');
        } else dies(-1, '关闭失败！');
        break;
    case 'ToHot': //热卖商品统计
        $DB = SQL::DB();
        $DateDay = date('Y-m-d') . ' 00:00:01';
        $YesterdayDate = date('Y-m-d', strtotime('-' . 1 . ' day')) . ' 00:00:01';

        $limit = $_QET['limit'];
        $page = ($_QET['page'] - 1) * $limit;

        switch ($_QET['type']) {
            case 'All':
                $Res = $DB->query("SELECT a.gid,a.name,(select count(*) from `sky_order` as b where b.gid = a.gid ) as count ,(select sum(money) from `sky_order` as b where b.gid = a.gid ) as cost,(select sum(price) from `sky_order` as b where b.gid = a.gid and b.payment != '积分兑换' ) as money FROM  `sky_goods` as a  ORDER BY `count` DESC LIMIT $page,$limit ")->fetchAll();
                $Count = $DB->count('goods');
                break;
            case 'Day':
                $Res = $DB->query("SELECT a.gid,a.name,(select count(*) from `sky_order` as b where b.gid = a.gid and  b.addtitm > '$DateDay' ) as count ,(select sum(money) from `sky_order` as b where b.gid = a.gid and  b.addtitm > '$DateDay' ) as cost,(select sum(price) from `sky_order` as b where b.gid = a.gid and b.payment != '积分兑换' and  b.addtitm > '$DateDay' ) as money FROM  `sky_goods` as a ORDER BY `count` DESC LIMIT $page,$limit ")->fetchAll();
                $Count = $DB->count('goods');
                break;
            case 'Yesterday':
                $Res = $DB->query("SELECT a.gid,a.name,(select count(*) from `sky_order` as b where b.gid = a.gid and  b.addtitm < '$DateDay' and b.addtitm > '$YesterdayDate' ) as count ,(select sum(money) from `sky_order` as b where b.gid = a.gid and  b.addtitm < '$DateDay' and b.addtitm > '$YesterdayDate' ) as cost,(select sum(price) from `sky_order` as b where b.gid = a.gid and b.payment != '积分兑换' and  b.addtitm < '$DateDay' and b.addtitm > '$YesterdayDate' ) as money FROM  `sky_goods` as a ORDER BY `count` DESC LIMIT $page,$limit ")->fetchAll();
                $Count = $DB->count('goods');
                break;
        }

        $Data = [];
        foreach ($Res as $v) {
            $Data[] = [
                'gid' => $v['gid'],
                'name' => $v['name'],
                'count' => $v['count'],
                'money' => ($v['money'] <= 0 ? 0 : $v['money']),
                'cost' => ($v['cost'] <= 0 ? 0 : $v['cost'])
            ];
        }

        dier([
            'code' => 0,
            'msg' => '数据获取成功',
            'count' => $Count,
            'data' => $Res
        ]);
        break;
    case 'CouponAdd': //批量生成优惠券
        $DB = SQL::DB();
        $_QET['minimum'] = (empty($_QET['minimum']) ? 0 : $_QET['minimum']);
        if (!is_numeric($_QET['money']) || !is_numeric($_QET['minimum']) || !is_numeric($_QET['gid']) || !is_numeric($_QET['cid'])) dies(-1, '参数提交异常');
        if ($_QET['num'] < 1) dies(-1, '每次最少生成一张优惠券！');
        if ($_QET['limit'] < 1) dies(-1, '领取数量限制异常，最低设置为1！');
        if ($_QET['apply'] == 1 && $_QET['gid'] == -1) dies(-1, '请选择需要发放优惠券的商品！');
        if ($_QET['apply'] == 2 && $_QET['cid'] == -1) dies(-1, '请选择需要发放优惠券的分类！');
        if ($_QET['get_way'] == 2 && $_QET['gid'] == -1) dies(-1, '请选择需要投放显示优惠券的商品');
        if ($_QET['get_way'] == 3 && $_QET['cid'] == -1) dies(-1, '请选择需要投放显示优惠券的分类');

        $Num = (int)$_QET['num'];
        $SQL = [
            'oid' => -1,
            'uid' => -1,
            'name' => $_QET['name'],
            'content' => $_QET['content'],
            'gid' => (empty($_QET['gid']) ? -1 : $_QET['gid']),
            'cid' => (empty($_QET['cid']) ? -1 : $_QET['cid']),
            'money' => $_QET['money'],
            'minimum' => $_QET['minimum'],
            'type' => $_QET['type'],
            'apply' => $_QET['apply'],
            'term_type' => $_QET['term_type'],
            'indate' => $_QET['indate'],
            'get_way' => $_QET['get_way'],
            'limit' => $_QET['limit'],
            'limit_token' => TokenCreate('XC'), //同批次的token相同
            'addtime' => $date,
        ];
        if (!empty($_QET['expirydate'])) {
            $SQL = array_merge($SQL, ['expirydate' => $_QET['expirydate']]);
        }
        $TokenArr = [];
        for ($i = 0; $i < $Num; $i++) {
            $Token = TokenCreate(rand(1000, 99999));
            $SQL['token'] = $Token;
            $Res = $DB->insert('coupon', $SQL);
            if ($Res) {
                $TokenArr[] = $Token;
            }
        }
        dier([
            'code' => 1,
            'msg' => '本次需生成：' . $Num . '张<br>共成功：' . count($TokenArr) . '张！',
            'data' => $TokenArr,
        ]);
        break;
    case 'CouponDeletet': //删除指定优惠券
        $DB = SQL::DB();
        if (empty($_QET['type'])) {
            test(['id|e'], '请输入优惠券id');
            $Res = $DB->delete('coupon', [
                'id' => $_QET['id'],
            ]);
        } else {
            switch ($_QET['type']) {
                case 1: //全部
                    $SQL = [];
                    break;
                case 2: //未兑换
                    $SQL = [
                        'uid' => -1,
                    ];
                    break;
                case 3: //已兑换未使用
                    $SQL = [
                        'uid[!]' => -1,
                        'oid' => -1
                    ];
                    break;
                case 4: //已兑换已使用
                    $SQL = [
                        'uid[!]' => -1,
                        'oid[!]' => -1
                    ];
                    break;
                default:
                    dies(-1, '错误请求！');
                    break;
            }

            if (!empty($_QET['gid'])) {
                $SQL = array_merge($SQL, ['gid' => $_QET['gid']]);
            }
            $Res = $DB->delete('coupon', $SQL);
        }

        if ($Res) {
            dies(1, '删除成功');
        } else dies(-1, '删除失败！');
        break;
    case 'CouponList': //获取优惠券列表
        $DB = SQL::DB();
        $LIMIT = $_QET['limit'];
        $Page = ($_QET['page'] - 1) * $LIMIT;

        $SQL = [
            'LIMIT' => [$Page, $LIMIT],
            'ORDER' => [
                'id' => 'DESC'
            ]
        ];

        $SQLC = [];

        if (!empty($_QET['gid'])) {
            $SQL = array_merge($SQL, ['gid' => $_QET['gid']]);
            $SQLC = ['gid' => $_QET['gid']];
        }

        if (!empty($_QET['name'])) {
            $SQL = array_merge($SQL, [
                'OR' => [
                    'id[~]' => $_QET['name'],
                    'oid[~]' => $_QET['name'],
                    'token[~]' => $_QET['name'],
                    'ip[~]' => $_QET['name'],
                    'name[~]' => $_QET['name'],
                ]
            ]);

            $SQLC = array_merge($SQLC, [
                'OR' => [
                    'id[~]' => $_QET['name'],
                    'oid[~]' => $_QET['name'],
                    'token[~]' => $_QET['name'],
                    'ip[~]' => $_QET['name'],
                    'name[~]' => $_QET['name'],
                ]
            ]);
        }

        $Res = $DB->select('coupon', '*', $SQL);
        if (count($Res) == 0) dies(-1, '空空如也！');

        $Count = $DB->count('coupon', $SQLC);

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Res,
            'count' => $Count
        ]);
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        dies(-2, '访问路径不存在！');
        break;
}
