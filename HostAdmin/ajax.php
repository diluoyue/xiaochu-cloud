<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/13 18:04
// +----------------------------------------------------------------------
// | Filename: ajax.php
// +----------------------------------------------------------------------
// | Explain: 主机控制
// +----------------------------------------------------------------------

include '../includes/fun.global.php';
global $_QET, $date;

global $conf;
if ((int)$conf['hostSwitch'] !== 1) {
    dies(-1, '当前站点的主机管理系统未开启！');
}

use BT\Config as BTC;
use BT\Construct as BTCO;
use Medoo\DB\SQL;
use Server\Server;

$DB = SQL::DB();

$UserData = Server::LoginStatus();
if (!$UserData && $_QET['act'] !== 'LoginVerification') {
    dies(-1, '主机不存在或此主机已被管理员关闭，或此主机绑定的服务器节点已关闭！');
}

switch ($_QET['act']) {
    case 'LoginVerification': //登陆主机面板(唯一无需验证可放行接口)
        Server::LoginVerification($_QET);
        break;
    case 'LogOut': //退出登录
        Server::LogOut();
        break;
    case 'Expenditure': //主机续费
        test(['num|e', 'type|e'], '请提交完整数据！');
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];
        $month = ((int)$_QET['num'] <= 1 ? 1 : $_QET['num']);
        /**
         * 扣费
         */
        if ($month > 12) {
            dies(-1, '一次最多可以续费12个月！');
        }
        $price = (float)$MainframeData['RenewPrice'] * $month;

        if ($price <= 0 && (int)$conf['FreeHostRenewal'] !== 1) {
            dies(-1, '免费主机无法进行续期！');
        }

        $Day = $month * 30;

        switch ((int)$_QET['type']) {
            case 4: //余额续期
                $Vs = $DB->get('user', ['money'], [
                    'state' => 1,
                    'id' => (int)$MainframeData['uid'],
                ]);
                if (!$Vs) {
                    dies(-1, '用户不存在或已被禁封');
                }
                if ($price > $Vs['money']) {
                    dies(-1, '余额不足,无法完成续期，还缺' . ($price - $Vs['money']) . '元！');
                }
                $Res = $DB->update('user', [
                    'money[-]' => $price,
                ], [
                    'id' => $MainframeData['uid'],
                ]);
                if (!$Res) {
                    dies(-1, '扣款失败，请重新尝试！');
                }
                break;
            default:
                Server::HostPay($price, ($_QET['type'] == 1 ? 'alipay' : ($_QET['type'] == 2 ? 'wxpay' : 'qqpay')), '主机[ ' . $MainframeData['id'] . ' ]续期' . $Day . '天', $Day, $MainframeData);
                break;
        }
        $Res = Server::Renewal($MainframeData['id'], $Day);
        if ($Res) {
            dies(1, '续期成功！');
        } else dies(-1, '扣费失败,无法续期,请联系管理员处理！');
        break;
    case 'DatabaseInformation': //数据库信息
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'user' => $MainframeData['sql_user'],
                'name' => $MainframeData['sql_name'],
                'pass' => $MainframeData['sql_pass'],
                'url' => $ServerData['sqlurl']
            ]
        ]);
        break;
    case 'SpaceDeleteSite': //删除站点
        $MainframeData = $DB->get('mainframe', '*', [
            'id' => (int)$UserData['id'],
        ]);
        if (!$MainframeData) dies(-1, '网站主机空间不存在,或已失效！');
        $ServerData = $DB->get('server', '*', ['id' => (int)$MainframeData['server'], 'state' => 1]);
        if (!$ServerData) {
            dies(-1, '节点不存在,或已经关闭,请咨询管理员！');
        }
        BTC::Conf($ServerData); //赋值
        if ($MainframeData['type'] != 1) {
            //只删除数据
            $re = $DB->delete('mainframe', ['id' => $UserData['id']]);
        } else {
            //删除网站+数据
            $DLE = BTCO::GetDeleteSite($MainframeData['siteId'], $MainframeData['identification'] . '.com');
            if ($DLE['status'] === true) {
                $re = $DB->delete('mainframe', ['id' => $UserData['id']]);
            } else {
                BTCO::WriteException($UserData['id'], $DLE['msg'], 2);
                dies(-1, '删除失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
            }
        }
        if ($re) {
            userlog('删除主机', '您永久删除了网站主机空间' . $MainframeData['id'] . '！', $MainframeData['uid']);
            dies(0, '删除成功,且无法恢复！');
        } else {
            dies(-1, '删除失败,请联系管理员处理！');
        }
        break;
    case 'SpaceRenewalSet': //自动续期
        $Res = $DB->update('mainframe', [
            'RenewalType' => $_QET['state']
        ], ['id' => $UserData['id']]);
        if ($Res) {
            userlog('自动续期状态', '您切换了网站主机空间(' . $UserData['id'] . ')的自动续期状态！', $UserData['uid']);
            dies(0, '自动续期功能' . ($_QET['state'] == 2 ? '关闭' : '开启') . '成功');
        } else {
            dies(-1, '自动续期状态切换失败！');
        }
        break;
    case 'SpaceStatus': //切换主机开启关闭状态
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];
        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $re = BTCO::GetSwitchState($MainframeData['siteId'], $MainframeData['identification'] . '.com', ($_QET['state'] == 2 ? 2 : 1));
        if ($re['status'] === true) {
            userlog('状态切换', '您切换了网站主机空间(' . $MainframeData['id'] . ')的状态!', $MainframeData['uid']);
            $DB->update('mainframe', ['status' => ($_QET['state'] == 2 ? 2 : 1)], ['id' => $MainframeData['id']]);
            dies(0, ($_QET['state'] == 2 ? '关闭' : '开启') . '成功');
        } else {
            BTCO::WriteException($UserData['id'], $re['msg'], 2);
            dies(-1, '切换失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'server_activate': //激活服务器空间
        $MainframeData = $DB->get('mainframe', '*', [
            'id' => $UserData['id'],
            'type' => 2,
            'state' => 1,
        ]);
        if (!$MainframeData) {
            dies(-1, '激活失败,主机空间不存在或已经激活过了！');
        }
        $ServerData = $DB->get('server', '*', ['id' => (int)$MainframeData['server'], 'state' => 1]);
        if (!$ServerData) {
            dies(-1, '主机空间不存在,或已经关闭！');
        }
        BTC::Conf($ServerData); //赋值
        $Data = BTCO::Getaddsite($MainframeData);
        if ($Data['siteStatus'] === true && $Data['databaseStatus'] === true) {
            //当前时间 + 激活可用时长时间戳;
            $Time = time() + (strtotime($MainframeData['endtime']) - strtotime($MainframeData['addtime']));
            $EndDate = date("Y-m-d H:i", $Time);

            $re = $DB->update('mainframe', [
                'endtime' => $EndDate,
                'siteId' => $Data['siteId'],
                'sql_user' => $Data['databaseUser'],
                'sql_name' => $Data['databaseUser'],
                'sql_pass' => $Data['databasePass'],
                'type' => 1,
                'status' => 1,
                'return' => '服务器开通成功,服务器ID为:' . $Data['siteId'],
            ], [
                'id' => $UserData['id']
            ]);
            //调整主机状态
            BTCO::Getendtime($Data['siteId'], $EndDate);
            BTCO::GetSetLimitNet($Data['siteId'], $MainframeData);
            BTCO::GetSessionPath($Data['siteId']);
            dies(0, '主机空间激活成功！');
        } else {
            if (isset($Data['databaseStatus']) && $Data['databaseStatus'] === false) {
                BTCO::WriteException($UserData['id'], '数据库环境不完善，无法创建数据库，请在宝塔的软件商店安装：phpMyAdmin 5.0！', 2);
            } else {
                BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            }

            dies(-1, '网站主机空间开通失败,请联系管理员处理！');
        }
        break;
    case 'HostDetails': //主机详情
        $Res = $DB->get('mainframe', [
            '[>]server' => ['server' => 'id'],
            '[>]user' => ['uid' => 'id'],
        ], [
            'server.name',
            'mainframe.id',
            'mainframe.uid',
            'mainframe.oid',
            'mainframe.RenewPrice',
            'mainframe.identification',
            'mainframe.type',
            'mainframe.sql_user',
            'mainframe.sql_pass',
            'mainframe.sql_name',
            'mainframe.domain',
            'mainframe.RenewalType',
            'mainframe.maxdomain',
            'mainframe.concurrencyall',
            'mainframe.concurrencyip',
            'mainframe.traffic',
            'mainframe.filesize',
            'mainframe.status',
            'mainframe.state',
            'mainframe.sizespace',
            'mainframe.currentsize',
            'mainframe.username',
            'mainframe.password',
            'mainframe.endtime',
            'mainframe.addtime',
            'server.content',
            'server.sqlurl',
            'user.money',
        ], [
            'mainframe.id' => (int)$UserData['id'],
            'server.state' => 1,
        ]);
        if (!$Res) {
            dies(-1, '无法获取主机信息，可能是主机不存在或母机已关闭，又或者未绑定到您账户！');
        }

        global $conf;
        $Res['HostAnnounced'] = $conf['HostAnnounced'];

        $Res['sizespace'] -= 0;
        $Res['currentsize'] -= 0;

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Res
        ]);
        break;
    case 'SizeCalibration': //同步主机空间大小
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];
        $Path = $ServerData['path'] . $MainframeData['identification'];
        $Data = BTCO::GetPathSize($Path);
        if (empty($Data) || empty($Data['size'])) {
            dies(-1, '数据校准失败！' . $Data['msg']);
        }
        $DB = SQL::DB();
        $Size = round($Data['size'] / (1024 * 1024), 2);
        $Res = $DB->update('mainframe', [
            'currentsize' => $Size
        ], [
            'id' => $UserData['id']
        ]);
        if ($Res) {
            dies(1, '校准成功，此空间大小为：' . $Size . 'MB');
        } else {
            dies(-1, '校准失败！');
        }
        break;
    case 'SpaceDNSList': //获取主机绑定域名列表
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];
        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $DirsList = BTCO::GetDirBinding($MainframeData['siteId']); //获取子目录列表
        $DataList = json_decode($MainframeData['domain'], TRUE);
        $Array = [];
        foreach ($DataList as $key => $value) {
            foreach ($DirsList['binding'] as $values) {
                if (in_array($value['name'], $values)) {
                    $value = ['id' => $values['id'], 'name' => $values['domain'], 'path' => $values['path'], 'port' => $values['port'], 'addtime' => $values['addtime']];
                }
            }
            $Array[] = $value;
        }
        if (count($Array) !== 0) {
            $DB->update('mainframe', ['domain[JSON]' => $Array], ['id' => $UserData['id']]);
            $DataList = $Array;
        }

        dier([
            'code' => 0,
            'msg' => '数据获取成功',
            'dirs' => array_merge(['/'], $DirsList['dirs']),
            'data' => $DataList,
            'count' => count($DataList),
            'domain' => $ServerData['domain'],
            'domain_sum' => $MainframeData['maxdomain'],
        ]);
        break;
    case 'SpaceDNSAdd': //新增域名
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        if (count($_QET['domain']) == 0 || $_QET['domain'][0] == '') {
            dies(-1, '最少添加一个域名！');
        }
        $DomainArray = json_decode($MainframeData['domain'], TRUE);

        if (count($DomainArray) >= (int)$MainframeData['maxdomain']) {
            dies(-1, '当前可绑定域名已达上限(' . $MainframeData['maxdomain'] . ')个！');
        }

        $Data = [];
        foreach ($_QET['domain'] as $value) {
            $DomainName = explode(':', $value);
            if (in_array($DomainName[0], $DomainArray)) dies(-1, '域名' . $value . '已经绑定过了！');
            if (strpos($value, '-') !== false) dies(-1, '为防止出错，不允许绑定中文域名！');
            if (strpos($value, '*') !== false) {
                $arr = explode('.', $value);
                if (count($arr) <= 2) dies(-1, '泛解析域名验证失败！,正确格式：*.xxx.xx，您当前格式：' . $DomainName[0]);
            } else {
                preg_match('/^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/', $DomainName[0], $arr_pr);
                if (empty($arr_pr[0])) {
                    dies(-1, '您输入的域名有误！');
                }
            }
            $Data[] = [
                'id' => '',
                'name' => strtolower($DomainName[0]),
                'path' => $_QET['dirs'],
                'port' => ($DomainName[1] ?? 80),
                'addtime' => $date,
            ];
        }

        if (count($DomainArray) == 0) {
            $Array = $Data;
        } else {
            $Array = array_merge($Data, $DomainArray);
        }

        if (count($Array) > (int)$MainframeData['maxdomain']) {
            dies(-1, '您当前绑定的数量加上原来添加的域名绑定数量已经超出了此空间域名绑定上限(' . $MainframeData['maxdomain'] . ')个！');
        }
        if ($_QET['dirs'] === '/') {
            $Dass = BTCO::GetAddDomain($MainframeData['siteId'], $MainframeData['identification'] . '.com', implode(',', $_QET['domain']));
        } else {
            if (count($_QET['domain']) > 1) dies(-1, '绑定到子目录每次只可添加一个域名,不可添加多个！');
            $Dass = BTCO::GetAddDirBinding($MainframeData['siteId'], $_QET['domain'][0], $_QET['dirs']);
        }

        if ($Dass['status'] === true) {
            userlog('域名绑定', '您绑定了' . count($Data) . '个主机域名(' . implode(',', $_QET['domain']) . ')，主机ID：' . $UserData['id'] . '！', $UserData['uid']);
            $DB->update('mainframe', ['domain[JSON]' => $Array], ['id' => $UserData['id']]);
            dies(0, $Dass['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dass['msg'], 2);
            dies(-1, '主机域名绑定失败,请联系管理员处理，或规范域名规则，重新尝试添加！');
        }
        break;
    case 'SpaceDeleteDomain': //删除域名
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];
        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $DomainArray = json_decode($MainframeData['domain'], TRUE);
        $Data = [];
        $Delarr = [];
        foreach ($DomainArray as $value) {
            if ($value['name'] == $_QET['name']) {
                $Delarr = $value;
                continue;
            }
            $Data[] = $value;
        }

        if ($Delarr == []) dies(-1, '域名不存在！');
        if ($Delarr['path'] == '/') {
            $Dass = BTCO::GetDelDomain($MainframeData['siteId'], $MainframeData['identification'] . '.com', $Delarr['name'], $Delarr['port']);
        } else {
            $Dass = BTCO::GetDelDirBinding($Delarr['id']);
        }
        if ($Dass['status'] === true) {
            userlog('删除域名', '删除了网站主机空间(' . $UserData['id'] . ')的域名(' . $Delarr['name'] . ')！', $UserData['uid']);
            $DB->update('mainframe', ['domain[JSON]' => $Data], ['id' => $UserData['id']]);
            dies(0, $Dass['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dass['msg'], 2);
            dies(-1, '域名删除失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceDirRewrite': //验证是否存在伪静态规则文件
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Array = [];
        $DomainArray = json_decode($MainframeData['domain'], TRUE);
        foreach ($DomainArray as $value) {
            if ($value['name'] == $_QET['name']) {
                $Array = $value;
            }
        }
        if ($Array == []) dies(-1, '域名不存在！');

        if ((int)$ServerData['system'] !== 1) {
            $Arr = [
                'id' => $Array['id'],
            ];
            if (!empty($_QET['add'])) {
                $Arr['add'] = 1;
            }
            $Darr = BTCO::Get('/site?action=GetDirRewrite', $Arr);
        } else {
            $Darr = BTCO::GetDirRewrite($Array['id'], (empty($_QET['add']) ? false : 1));
        }
        if ($Darr['status'] === false && empty($_QET['add'])) {
            dies(2, '你真的要为这个子目录创建独立的伪静态规则吗？');
        } else if ($Darr['status'] === true || !empty($_QET['add'])) {

            if ((int)$ServerData['system'] !== 1) {
                $Darr['filename'] = $_QET['path'];
            } else {
                $Darr['filename'] = (empty($Darr['filename']) ? $_QET['path'] : $Darr['filename']);
            }

            dier([
                'code' => 1,
                'msg' => '获取成功',
                'data' => $Darr['data'],
                'rlist' => $Darr['rlist'],
                'filename' => $Darr['filename'],
            ]);
        } else {
            BTCO::WriteException($UserData['id'], $Darr['msg'], 2);
            dies(-1, '获取失败,请联系管理员处理！');
        }
        break;
    case 'SpaceFileBody': //获取伪静态规则内容
        if (empty($_QET['Rewrite'])) dies(-1, '请填写完整！');
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        if ((int)$ServerData['system'] !== 1) {
            $Path = $ServerData['root_directory'] . 'panel/rewrite/nginx/' . $_QET['Rewrite'] . '.conf';
        } else {
            $Path = $ServerData['root_directory'] . 'server/panel/rewrite/nginx/' . $_QET['Rewrite'] . '.conf';
        }
        $Darr = BTCO::GetFileBody($Path);
        if ($Darr['status'] === true) {
            dier(['code' => 0, 'msg' => $_QET['Rewrite'] . '规则获取成功', 'data' => $Darr['data']]);
        } else {
            BTCO::WriteException($UserData['id'], $Darr['msg'], 2);
            dies(-1, '获取失败,请联系管理员处理！');
        }
        break;
    case 'SpaceSaveFileBody': //保存伪静态内容(子目录)
        if (empty($_QET['path'])) {
            dies(-1, '请填写完整！');
        }
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Array = [];
        $DomainArray = json_decode($MainframeData['domain'], TRUE);
        foreach ($DomainArray as $value) {
            if ($value['name'] == $_QET['name']) {
                $Array = $value;
            }
        }
        if ($Array == []) dies(-1, '域名不存在！');

        if ((int)$ServerData['system'] !== 1) {
            $Darr = BTCO::Get('/site?action=SetSiteRewrite', [
                'siteName' => $MainframeData['identification'] . '.com_' . $_QET['path'],
                'data' => $_POST['data'],
            ]);
        } else {
            $Darr = BTCO::GetSaveFileBody(saddslashes($_POST['path']), saddslashes($_POST['data']));
        }
        if ($Darr['status'] === true) {
            dier(['code' => 0, 'msg' => $Darr['msg']]);
        } else {
            BTCO::WriteException($UserData['id'], $Darr['msg'], 2);
            dies(-1, '静态规则保存失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceSaveFileBodys': //保存主目录伪静态规则
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if ((int)$ServerData['system'] !== 1) {
            $Data = BTCO::Get('/site?action=SetSiteRewrite', ['siteName' => $MainframeData['identification'] . '.com', 'data' => $_POST['data']]);
        } else {
            $Data = BTCO::Get('/files?action=SaveFileBody', ['path' => $ServerData['root_directory'] . 'server/panel/vhost/rewrite/' . $MainframeData['identification'] . '.com.conf', 'encoding' => 'utf-8', 'data' => $_POST['data']]);
        }

        if ($Data['status'] === true) {
            dies(0, '主目录伪静态规则保存成功！');
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, '主目录伪静态规则保存失败,请注意格式！');
        }
        break;
    case 'SpaceDirList': //获取文件列表
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie;
        $DirsList = BTCO::GetDir($Path, $_QET['page'], $_QET['limit']); //获取目录列表

        if (!isset($DirsList['status'])) {
            $count = explode('条数据</span>', explode('class=\'Pcount\'>共', $DirsList['PAGE'])[1])[0];
            $Data = [];
            $i = 1;

            foreach ($DirsList['DIR'] as $value) {
                $value = explode(';', $value);
                $Data[] = [
                    'id' => $i,
                    'filename' => $value[0],
                    'size' => $value[1],
                    'ModificationTime' => date("Y-m-d H:i:s", $value[2]),
                    'authority' => $value[3],
                    'possessor' => (empty($value[4]) ? '---' : $value[4]),
                    'type' => 1,
                ];
                ++$i;
            }

            foreach ($DirsList['FILES'] as $value) {
                $type = 1;
                $value = explode(';', $value);
                if ($value[0] == '.user.ini') {
                    $count = $count - 1;
                    continue;
                }

                $size = (float)$value[1];
                if ($size >= 1024) {
                    $dw = ['KB', 1024];
                }
                if ($size >= 1024000) {
                    $dw = ['MB', 1024000];
                }
                if ($size >= 1024000000) {
                    $dw = ['GB', 1024000000];
                }

                if ($size >= 1024000000000) {
                    $dw = ['TB', 1024000000000];
                }

                if ($size < 1024) {
                    $size = $size . 'B';
                } else {
                    $size = round($size / $dw[1], 2) . $dw[0];
                }

                $TypeS = explode('.', $value[0]);
                $TypeSCount = count($TypeS);
                $TypeS = $TypeS[$TypeSCount - 1];

                switch (strtolower($TypeS)) {
                    case 'php':
                        $type = 2; //php 文件
                        break;
                    case 'html':
                    case 'xhtml':
                    case 'htx':
                    case 'htm':
                    case 'lock':
                    case 'cache':
                    case 'htt':
                    case 'jsp':
                    case 'sql':
                    case 'txt':
                    case 'log':
                    case 'vue':
                        $type = 3; //html格式文件
                        break;
                    case 'rar':
                    case 'zip':
                    case 'tar':
                    case 'gz':
                        $type = 4; //压缩文件
                        break;
                    case 'jpg':
                    case 'bmp':
                    case 'tiff':
                    case 'gif':
                    case 'jpeg':
                    case 'svg':
                    case 'psd':
                    case 'cdr':
                    case 'eps':
                    case 'png':
                    case 'webp':
                        $type = 5; //图片文件
                        break;
                    case 'js':
                    case 'ts':
                        $type = 6; //js文件
                        break;
                    case 'css':
                        $type = 7; //css文件
                        break;
                    default:
                        $type = 8; //其他未知文件
                        break;
                }

                $Data[] = [
                    'id' => $i,
                    'filename' => $value[0],
                    'size' => $size,
                    'ModificationTime' => date("Y-m-d H:i:s", $value[2]),
                    'authority' => $value[3],
                    'possessor' => (empty($value[4]) ? '---' : $value[4]),
                    'type' => $type,
                ];
                ++$i;
                unset($size, $type, $TypeS);
            }

            dier([
                'code' => 0,
                'msg' => '文件列表获取成功',
                'count' => (int)$count,
                'data' => $Data,
            ]);

        } else {
            BTCO::WriteException($UserData['id'], $DirsList['msg'], 2);
            dies(-1, '文件列表获取失败,请联系管理员处理！');
        }
        break;
    case 'SpacePathSize': //获取文件夹大小
        if (empty($_QET['flie'])) dies(-1, '请填写完整！');
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];
        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Path = $ServerData['path'] . $MainframeData['identification'] . '/' . implode('/', explode('.', $_QET['flie']));

        $DirsSize = BTCO::GetPathSize($Path); //获取子目录列表
        if (!isset($DirsSize['status'])) {

            $size = (float)$DirsSize['size'];
            if ($size >= 1024) {
                $dw = ['KB', 1024];
            }
            if ($size >= 1024000) {
                $dw = ['MB', 1024000];
            }
            if ($size >= 1024000000) {
                $dw = ['GB', 1024000000];
            }

            if ($size >= 1024000000000) {
                $dw = ['TB', 1024000000000];
            }

            if ($size < 1024) {
                $size = $size . 'B';
            } else {
                $size = round($size / $dw[1], 2) . $dw[0];
            }
            dier([
                'code' => 0,
                'msg' => '大小获取成功',
                'size' => $size
            ]);
        } else {
            BTCO::WriteException($UserData['id'], $DirsSize['msg'], 2);
            dies(-1, '文件大小获取失败,请联系管理员处理！');
        }
        break;
    case 'SpaceSetBatchData': //批量操作文件
        if (empty($_QET['data']) || empty((int)$_QET['type'])) dies(-1, '请填写完整！');

        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];
        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie;
        $Data = $_POST['data'];

        if (in_array('.user.ini', $Data)) {
            dies(-1, '违规操作！');
        }

        if ($_QET['type'] == 1) { //复制
            $_SESSION['BatchData'] = 1;
        } else if ($_QET['type'] == 2) { //剪切
            $_SESSION['BatchData'] = 2;
        }

        $Dirs = BTCO::SetBatchData($Path, $_QET['type'], $Data); //批量操作

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '操作失败，请联系管理员查明原因！');
        }
        break;
    case 'SpaceDownloadFile': //远程下载路径
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if (strstr($_POST['flieS'], '..') || strstr($_POST['fliename'], '..')) dies(-1, '不安全的路径');
        $Path = $ServerData['path'] . $MainframeData['identification'] . $_POST['flieS'];
        //验证文件的大小
        $DisSize = get_headers($_QET['curl'], TRUE)['Content-Length'];
        if ($DisSize == '') {
            dies(-1, '上传文件大小获取失败,无法完成下载!<br>请确认远程文件是否存在？');
        }
        if ((float)$DisSize > (1024000 * $MainframeData['filesize'])) {
            dies(-1, '文件过大,最大只可下载' . $MainframeData['filesize'] . 'MB大小的文件！');
        }

        /**
         * 验证文件是否超出可用空间大小
         */
        if ($DisSize > (($MainframeData['sizespace'] - $MainframeData['currentsize']) * 1024000)) {
            dies(-1, '当前主机可用空间不足' . round($DisSize / 1024000, 3) . 'MB，无法完成上传！');
        }

        $Dirs = BTCO::GetDownloadFile($Path, $_POST['curl'], $_POST['fliename']);

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg'] . '<br>请稍后刷新查看！');
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '远程下载失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceUpdate': //上传文件
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie;
        if ($_FILES['file']['size'] > (1024000 * $MainframeData['filesize'])) {
            dies(-1, '最多只可上传' . $MainframeData['filesize'] . 'MB大小的文件');
        }

        /**
         * 验证文件是否超出可用空间大小
         */
        if ($_FILES['file']['size'] > (($MainframeData['sizespace'] - $MainframeData['currentsize']) * 1024000)) {
            dies(-1, '当前主机可用空间不足' . round($_FILES['file']['size'] / 1024000, 3) . 'MB，无法完成上传！');
        }

        if ($_FILES["file"]["name"] == '.user.ini') {
            dies(-1, '无法上传此文件');
        }
        $Dirs = BTCO::GetUpdate($Path, $_FILES);

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '文件上传失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . ',或手动点击重新尝试！');
        }
        break;
    case 'SpaceCreateFile': //新建文件
        if (empty($_QET['name'])) dies(-1, '请填写完整！');
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if ($_QET['name'] == '.user.ini') dies(-1, '无法新建此文件');

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie . '/' . $_QET['name'];
        $Dirs = BTCO::GetCreateFile($Path);

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '新建文件失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceCreateDir': //新建目录
        if (empty($_QET['name'])) dies(-1, '请填写完整！');
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie . '/' . $_QET['name'];
        $Dirs = BTCO::GetCreateDir($Path);

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '新建目录失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceSetBatchZip': //压缩文件
        if (empty($_QET['data']) || empty($_QET['z_type']) || empty($_QET['dfile'])) dies(-1, '请填写完整！');
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Path = $ServerData['path'] . $MainframeData['identification'];

        if (strstr($_POST['dfile'], '..')) {
            dies(-1, '不安全的路径');
        }

        $dfile = $Path . '/' . $_POST['dfile'];

        $Dirs = BTCO::GetZip($Path . $flie, $_QET['z_type'], $dfile, implode(',', $_POST['data']));

        if ($Dirs['status'] === true) {
            //BTCO::GetTaskLists(); //获取操作队列
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '压缩失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceSetBatchPasteV': //粘贴冲突验证
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie;
        $Dirs = BTCO::GetCheckExistsFiles($Path);
        if (isset($Dirs['status']) && $Dirs['status'] == false) {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '验证失败，无法完成粘贴操作！');
        } else {
            if (count($Dirs) == 0) {
                dier(['code' => 0, 'msg' => '可直接粘贴,无冲突文件']);
            } else {
                $arr = [];
                foreach ($Dirs as $v) {
                    $size = (float)$v['size'];
                    if ($size >= 1024) {
                        $dw = ['KB', 1024];
                    }
                    if ($size >= 1024000) {
                        $dw = ['MB', 1024000];
                    }
                    if ($size >= 1024000000) {
                        $dw = ['GB', 1024000000];
                    }

                    if ($size >= 1024000000000) {
                        $dw = ['TB', 1024000000000];
                    }

                    if ($size < 1024) {
                        $size = $size . 'B';
                    } else {
                        $size = round($size / $dw[1], 2) . $dw[0];
                    }
                    $arr[] = [
                        'filename' => $v['filename'],
                        'size' => $size,
                        'mtime' => date("Y-m-d H:i:s", $v['mtime'])
                    ];
                }
                dier(['code' => -2, 'msg' => '有冲突文件,请先确认！', 'data' => $arr]);
            }
        }
        break;
    case 'SpaceSetBatchPaste': //粘贴全部文件
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie;
        $Dirs = BTCO::GetBatchPaste($Path);

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg'] . (empty((int)$_SESSION['BatchData']) ? ' | 复制' : ' | 剪切'));
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '粘贴全部文件失败，请联系管理员查明原因');
        }
        break;
    case 'SpaceMvFile': //文件重命名
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie;

        if ($_QET['sfile'] == '.user.ini' || $_QET['dfile'] == '.user.ini') dies(-1, '违规操作！');

        $sfile = $Path . '/' . $_POST['sfile'];

        $dfile = $Path . '/' . $_POST['dfile'];

        $Dirs = BTCO::GetMvFile($sfile, $dfile);

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '文件重命名失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceDeleteFile': //删除文件(单文件)
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if ($_QET['name'] == '.user.ini') dies(-1, '违规操作！');

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie . '/' . $_POST['name'];

        $Dirs = BTCO::GetDeleteFile($Path);

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '删除文件失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceShareDownload': //创建分享外链,供下载！
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if ($_QET['name'] == '.user.ini') dies(-1, '违规操作！');

        $filename = $ServerData['path'] . $MainframeData['identification'] . $flie . '/' . $_POST['name'];

        $Dirs = BTCO::GetShareDownload($filename, $_POST['name'], 1);

        if ($_QET['name'] == '.user.ini') dies(-1, '违规操作！');

        if ($Dirs['status'] === true) {
            dier([
                'code' => 0,
                'msg' => '文件外链创建成功,点击查看！',
                'name' => $Dirs['msg']['ps'],
                'url' => $ServerData['url'] . '/down/' . $Dirs['msg']['token'],
                'password' => $Dirs['msg']['password'],
                'addtime' => date("Y-m-d H:i:s", $Dirs['msg']['addtime']),
                'expire' => date("Y-m-d H:i:s", $Dirs['msg']['expire'])
            ]);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, $Dirs['msg']);
        }
        break;
    case 'SpaceEditContent': //获取编辑器内容！
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if ($_QET['name'] == '.user.ini') dies(-1, '违规操作！');

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie . '/' . $_POST['name'];

        $Dirs = BTCO::GetFileBody($Path);

        if ($Dirs['status'] === true) {
            dier([
                'code' => 0,
                'msg' => '文件内容读取成功',
                'data' => $Dirs['data'],
                'encoding' => $Dirs['encoding'],
            ]);
        } else {
            if ($Dirs['msg'] === '文件编码不被兼容，无法正确读取文件!') {
                dies(-1, '文件编码不被兼容，无法正确读取文件!,可能此文件是加密文件！');
            }
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '获取失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceEditSave': //保存编辑器的内容
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if ($_QET['name'] == '.user.ini') dies(-1, '违规操作！');

        $Path = $ServerData['path'] . $MainframeData['identification'] . $flie . '/' . $_POST['name'];

        $Dirs = BTCO::GetSaveFileBody($Path, $_POST['content'], $_QET['encoding']);

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '保存失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceSetBatchUnZip': //解压文件
        $Dv = BTCO::DataV($UserData['id'], $_QET['flie']);
        $MainframeData = $Dv['MainframeData'];
        $flie = $Dv['flie'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if ($_QET['name'] == '.user.ini') dies(-1, '违规操作！');

        $sfile = $ServerData['path'] . $MainframeData['identification'] . $flie . '/' . $_POST['name'];

        if (strstr($_POST['dfile'], '..')) dies(-1, '不安全的路径');

        $dfile = $ServerData['path'] . $MainframeData['identification'] . $_POST['dfile']; //解压目录

        $names = explode('.', $_POST['name']);
        unset($names[0]);
        $type = implode('.', $names);
        if ($type == 'tar.gz') $type = 'tar';

        $Dirs = BTCO::GetUnZip($sfile, $dfile, $type, $_QET['coding'], ($_POST['password'] == '' ? false : $_POST['password']));

        if ($Dirs['status'] === true) {
            dies(0, $Dirs['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Dirs['msg'], 2);
            dies(-1, '解压失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceSetAccountModification': //修改登录账号密码
        test(['username|e']);
        $SQL = [
            'username' => trim($_QET['username']),
        ];
        if (!empty($_QET['password'])) {
            $SQL['password'] = (empty($_POST['password']) ? '' : md5(trim($_POST['password'])));
        }

        $Vs1 = $DB->get('mainframe', ['id'], [
            'username' => (string)$SQL['username'],
            'id[!]' => (int)$UserData['id'],
        ]);

        if ($Vs1) {
            dies(-1, '此用户名已被其他主机占用，请重新配置！');
        }

        $Res = $DB->update('mainframe', $SQL, [
            'id' => (int)$UserData['id'],
        ]);
        if ($Res) {
            dies(1, '修改成功！');
        }
        dies(-1, '修改失败，请重新尝试！');
        break;
    case 'SpacePhpVersions': //获取PHP版本
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Path = $MainframeData['identification'] . '.com';
        $PHP_BS = BTCO::GetSitePHPVersion($Path);

        if (!isset($_SESSION['PHP_LIST_' . $ServerData['id']])) {
            $PHP_List = BTCO::GetPHPVersion($Path);
            if (count($PHP_List) >= 1) {

                $_SESSION['PHP_LIST_' . $ServerData['id']] = $PHP_List;

            }
        } else {
            $PHP_List = $_SESSION['PHP_LIST_' . $ServerData['id']];
        }

        if (isset($PHP_BS['phpversion'])) {
            dier([
                'code' => 0,
                'msg' => 'PHP版本列表获取成功',
                'list' => $PHP_List,
                'Version' => $PHP_BS['phpversion'],
            ]);
        } else {
            BTCO::WriteException($UserData['id'], $PHP_BS['msg'], 2);
            dies(-1, 'php版本列表获取失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpacePhpSave': //修改PHP版本
        if ($_QET['php'] < 56 && $_QET['php'] != '00') dies(-1, 'PHP版本号异常！');
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if (empty($_QET['php'])) {
            dies(-1, '请将PHP版本填写完整！');
        }

        $Data = BTCO::Get('/site?action=SetPHPVersion', ['siteName' => $MainframeData['identification'] . '.com', 'version' => $_QET['php']]);

        if ($Data['status'] === true) {
            dies(0, 'PHP版本切换成功！');
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, 'PHP版本切换失败！');
        }
        break;
    case 'SpaceGetDir': //获取网站运行目录
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Path = $ServerData['path'] . $MainframeData['identification'];
        $Data = BTCO::Get('/site?action=GetDirUserINI', ['id' => $MainframeData['siteId'], 'path' => $Path]);
        if (isset($Data['runPath']['runPath']) && isset($Data['userini'])) {

            if ($Data['userini'] == false) {
                if (!in_array($Data['runPath'], $Data['dirs'])) {
                    BTCO::Get('/site?action=SetSiteRunPath', ['id' => $MainframeData['siteId'], 'runPath' => '/']);
                }
                BTCO::Get('/site?action=SetDirUserINI', ['path' => $Path]);
            }

            $_SESSION['runPathDirs_' . $UserData['id']] = $Data['runPath']['dirs'];

            dier([
                'code' => 0,
                'msg' => '网站目录获取成功',
                'list' => $Data['runPath']['dirs'],
                'runPath' => $Data['runPath']['runPath'],
            ]);
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, '网站目录获取失败！');
        }
        break;
    case 'SpaceSetSiteRunPath': //保存网站运行目录
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        if (!in_array($_QET['dirs'], $_SESSION['runPathDirs_' . $UserData['id']])) dies(-1, '违规目录！');

        $Data = BTCO::Get('/site?action=SetSiteRunPath', ['id' => $MainframeData['siteId'], 'runPath' => $_QET['dirs']]);

        if ($Data['status'] === true) {
            dies(0, '网站运行目录切换成功！');
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, '运行目录切换失败！');
        }
        break;
    case 'SpaceGetRewriteList': //获取主目录伪静态规则内容 + 主目录保存的规则内容
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Path = $ServerData['path'] . $MainframeData['identification'];
        $DataList = BTCO::Get('/site?action=GetRewriteList', ['siteName' => $MainframeData['identification'] . '.com']);

        if ((int)$ServerData['system'] !== 1) {
            //Windows
            $DataBody = BTCO::Get('/site?action=GetSiteRewrite', ['siteName' => $MainframeData['identification'] . '.com']);
        } else {
            //Linux
            $path = $ServerData['root_directory'] . 'server/panel/vhost/rewrite/' . $MainframeData['identification'] . '.com.conf';
            $DataBody = BTCO::Get('/files?action=GetFileBody', ['path' => $path]);
        }

        if ($DataBody['status'] === true) {
            dier([
                'code' => 0,
                'msg' => '规则内容获取成功',
                'encoding' => $DataBody['encoding'],
                'data' => $DataBody['data'],
                'list' => $DataList['rewrite'],
            ]);
        } else {
            BTCO::WriteException($UserData['id'], $DataBody['msg'], 2);
            dies(-1, '规则内容获取失败！');
        }
        break;
    case 'SpaceGetSSL': //获取SSL配置信息
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Data = BTCO::Get('/site?action=GetSSL', ['siteName' => $MainframeData['identification'] . '.com']);

        if (isset($Data['status']) && isset($Data['key']) && isset($Data['csr'])) {
            dier([
                'code' => 0,
                'type' => $Data['type'],
                'cert_data' => $Data['cert_data'],
                'key' => $Data['key'],
                'csr' => $Data['csr'],
                'httpTohttps' => $Data['httpTohttps'],
            ]);

        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, 'SSL配置数据获取失败，请联系管理员查明原因，节点名称：' . $ServerData['name'] . '！');
        }
        break;
    case 'SpaceCoerceSSL': //关闭强制HTTPS
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Data = BTCO::Get('/site?action=' . ($_QET['type'] == 1 ? 'CloseToHttps' : 'HttpToHttps'),
            [
                'siteName' => $MainframeData['identification'] . '.com',
            ]);
        if ($Data['status'] === true) {
            dies(0, $Data['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, $Data['msg']);
        }
        break;
    case 'SpaceSetSSL': //保存SSL证书
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Data = BTCO::Get('/site?action=SetSSL',
            [
                'type' => 1,
                'siteName' => $MainframeData['identification'] . '.com',//$_POST['siteName'],
                'key' => $_POST['key'],
                'csr' => $_POST['csr']
            ]);

        if ($Data['status'] === true) {
            dies(0, $Data['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, $Data['msg']);
        }
        break;
    case 'SpaceCloseSSLConf': //关闭证书
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Data = BTCO::Get('/site?action=CloseSSLConf', ['updateOf' => 1, 'siteName' => $MainframeData['identification'] . '.com']);

        if ($Data['status'] === true) {
            dies(0, $Data['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, $Data['msg']);
        }
        break;
    case 'SpaceGetIndex': //默认文件列表获取
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Data = BTCO::Get('/site?action=GetIndex', ['id' => $MainframeData['siteId']]);
        if (!empty($Data)) {
            dies(0, explode(',', $Data));
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, $Data['msg']);
        }
        break;
    case 'SpaceSetIndex': //保存默认文档
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];
        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Index = implode(',', $_POST['RewriteCountent']);
        $Data = BTCO::Get('/site?action=SetIndex', ['id' => $MainframeData['siteId'], 'Index' => $Index]);
        if ($Data['status'] === true) {
            dies(0, $Data['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, $Data['msg']);
        }
        break;
    case 'SpaceGetSiteErrorLogs': //主机错误日志
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Data = BTCO::Get('/site?action=get_site_errlog', ['siteName' => $MainframeData['identification'] . '.com']);
        if ($Data['status'] === true) {
            dies(0, $Data['msg']);
        } else {
            if ($Data['msg'] === '指定参数无效!') {
                dies(0, '此节点不可观测错误日志！');
            }
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, $Data['msg']);
        }
        break;
    case 'SpaceGetSiteLogs': //响应日志
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Data = BTCO::Get('/site?action=GetSiteLogs', ['siteName' => $MainframeData['identification'] . '.com']);
        if ($Data['status'] === true) {
            dies(0, $Data['msg']);
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, $Data['msg']);
        }
        break;
    case 'Calibration': //校准数据
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['type'] == 2) {
            dies(-1, '请先激活！');
        }

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $Data = BTCO::Get('/data?action=getData', [
            'tojs' => 'get_list',
            'table' => 'databases',
            'limit' => 1,
            'p' => 1,
            'search' => $MainframeData['identification'],
            'order' => 'id desc',
        ]);

        if (isset($Data['data'][0])) {
            $Data = $Data['data'][0];
            $DB->update('mainframe', [
                'sql_user' => $Data['username'],
                'sql_name' => $Data['name'],
                'sql_pass' => $Data['password'],
            ], [
                'id' => $MainframeData['id'],
            ]);
        }

        BTCO::Getendtime($MainframeData['siteId'], $MainframeData['endtime']);
        BTCO::GetSetLimitNet($MainframeData['siteId'], $MainframeData);

        dies(1, '校准成功！');
        break;
    case 'ResDatabasePassword': //重置数据库密码
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        if ($MainframeData['type'] == 2) {
            dies(-1, '请先激活！');
        }

        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：您当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }

        $Pass = 'sql_pass_' . md5($MainframeData['identification'] . random_int(10, 99999));

        $Data = BTCO::Get('/data?action=getData', [
            'tojs' => 'get_list',
            'table' => 'databases',
            'limit' => 1,
            'p' => 1,
            'search' => $MainframeData['identification'],
            'order' => 'id desc',
        ]);

        if (isset($Data['data'][0])) {
            $Data = $Data['data'][0];
            $DataS = BTCO::Get('/database?action=ResDatabasePassword', [
                'id' => $Data['id'],
                'name' => $MainframeData['sql_name'],
                'password' => $Pass,
            ]);
            if ($DataS['status'] === true) {
                $Res = $DB->update('mainframe', [
                    'sql_pass' => $Pass,
                ], [
                    'id' => $MainframeData['id'],
                ]);
                if ($Res) {
                    dies(1, '数据库密码重置成功！');
                } else {
                    dies(-1, '重置失败，请点击校准主机数据！');
                }
            } else {
                BTCO::WriteException($UserData['id'], $DataS['msg'], 2);
                dies(-1, '重置数据库密码失败，请联系客服处理！');
            }
        } else {
            BTCO::WriteException($UserData['id'], $Data['msg'], 2);
            dies(-1, '数据库信息获取失败！');
        }
        break;
    case 'SpaceVi': //安全检测
        $Dv = BTCO::DataV($UserData['id']);
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];

        $Path = $ServerData['path'] . $MainframeData['identification'];
        $Data = BTCO::Get('/site?action=GetDirUserINI', ['id' => $MainframeData['siteId'], 'path' => $Path]);
        if (isset($Data['userini'])) {
            if ($Data['userini'] === false) {
                if (!in_array($Data['runPath'], $Data['dirs'])) {
                    BTCO::Get('/site?action=SetSiteRunPath', ['id' => $MainframeData['siteId'], 'runPath' => '/']);
                }
                BTCO::Get('/site?action=SetDirUserINI', ['path' => $Path]);
                dies(1, '数据获取成功！！');
            }
            dies(1, '数据获取成功！');
        } else {
            dies(-1, '数据获取失败！');
        }
        break;
    default:
        dies(-1, '403');
        break;
}
