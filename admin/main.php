<?php

/**
 * 新后台操作类
 */

use BT\Config as BTC;
use BT\Construct as BTCO;
use Curl\Curl;
use extend\Maintain;
use lib\App\App;
use lib\AppStore\AppList;
use lib\Forum\Forum;
use lib\Hook\Hook;
use lib\Pay\Pay;
use lib\supply\official;
use lib\supply\ProductsExchange;
use lib\supply\StringCargo;
use Medoo\DB\SQL;
use Server\Server;

include '../includes/fun.global.php';
header('Content-Type: application/json; charset=UTF-8');
global $conf, $date, $_QET;
admin::safety(''); //放行白名单

switch ($_QET['act']) {
    case 'ApplyingUpdate': //更新数据
        AppList::AppDataDelete(4);
        dies(1, '更新成功！');
        break;
    case 'DataStatistics': //首页数据统计
        CookieCache::read();
        $Data = \Admin\Admin::HomeData(((int)$_QET['type'] === 1 ? 1 : 2), (empty($_QET['date']) ? date('Y-m-d') : $_QET['date']));
        CookieCache::add([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
        ], 60);
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
        ]);
        break;
    case 'SalesChart': //销量排行榜(10条)
        CookieCache::read();
        test(['type|e'], '请填写完整！');
        $Data = \Admin\Admin::SalesChart($_QET['type']);
        CookieCache::add([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
        ], 60);
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
        ]);
        break;
    case 'GoodsCount': //商品数量
        $DB = SQL::DB();
        $SQL = [];
        if ($_QET['cid'] >= 1) {
            $SQL['cid'] = $_QET['cid'];
        }
        if (!empty($_QET['name'])) {
            $SQL['name[~]'] = $_QET['name'];
        }
        dier([
            'code' => 1,
            'msg' => '商品数量获取成功',
            'count' => $DB->count('goods', $SQL)
        ]);
        break;
    case 'GoodsList': //商品列表
        test(['limit|e', 'page|e', 'name|i'], '请提交完整！');
        $Data = \Admin\Admin::GoodsList($_QET);
        dier([
            'code' => 1,
            'msg' => '商品列表获取成功!',
            'data' => $Data
        ]);
        break;
    case 'GoodsCopy': //复制商品
        test(['gid|e'], '请提交完整！');
        $Data = \Admin\Admin::GoodsCopy($_QET['gid']);
        dier($Data);
        break;
    case 'GoodsSpu': //获取规格数据
        test(['gid|e|请将商品ID提交完整']);
        $DB = SQL::DB();
        $Goods = $DB->get('goods', ['specification', 'specification_spu', 'specification_sku', 'name'], ['gid' => $_QET['gid']]);
        if (!$Goods) dies(-1, '商品不存在！');
        if ($Goods['specification'] == 2) {
            dier([
                'code' => 1,
                'msg' => '规格数据获取成功',
                'SPU' => json_decode($Goods['specification_spu'], TRUE),
                'SKU' => json_decode($Goods['specification_sku'], TRUE),
                'name' => $Goods['name'],
            ]);
        } else dies(-1, '此商品未设置商品规格');
        break;
    case 'GoodsStateSet': //商品状态设置
        test(['gid|e', 'type|e', 'name|e'], '请填提交完整!');
        $DB = SQL::DB();
        $Res = $DB->update('goods', [
            'state' => $_QET['type'],
            'update_dat' => $date,
        ], [
            'gid' => $_QET['gid']
        ]);
        if ($Res) {
            $Hook = ($_QET['state'] == 1 ? 'GoodsShow' : 'GoodsHide');
            Hook::execute($Hook, [
                'gid' => $_QET['gid'],
                'name' => $_QET['name']
            ]);
            dies(1, '商品[' . $_QET['name'] . ']状态调整成功!');
        } else {
            dies(-1, '商品[' . $_QET['name'] . ']状态调整失败!');
        }
        break;
    case 'ClassStateSet': //分类状态设置
        test(['cid|e', 'type|e', 'name|e'], '请填提交完整!');
        $DB = SQL::DB();
        $Res = $DB->update('class', [
            'state' => $_QET['type'],
        ], [
            'cid' => $_QET['cid']
        ]);
        if ($Res) {
            dies(1, '分类[' . $_QET['name'] . ']状态调整成功!');
        } else {
            dies(-1, '分类[' . $_QET['name'] . ']状态调整失败!');
        }
        break;
    case 'LevelStateSet': //等级状态设置
        test(['mid|e', 'type|e', 'name|e'], '请填提交完整!');
        $DB = SQL::DB();
        $Res = $DB->update('price', [
            'state' => $_QET['type'],
        ], [
            'mid' => $_QET['mid']
        ]);
        if ($Res) {
            dies(1, '等级[' . $_QET['name'] . ']状态调整成功!');
        } else {
            dies(-1, '等级[' . $_QET['name'] . ']状态调整失败!');
        }
        break;
    case 'GoodsDelete': //删除商品
        test(['gid|e', 'name|e'], '请填写完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('goods', [
            'gid' => $_QET['gid']
        ]);
        if ($Res) {
            Hook::execute('GoodsDel', [
                'gid' => $_QET['gid'],
                'name' => $_QET['name']
            ]);
            dies(1, '商品[' . $_QET['name'] . ']删除成功!');
        } else {
            dies(-1, '商品[' . $_QET['name'] . ']删除失败!');
        }
        break;
    case 'ClassDelete': //删除分类
        test(['cid|e', 'name|e'], '请填写完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('class', [
            'cid' => $_QET['cid']
        ]);
        if ($Res) {
            Hook::execute('ClassDel', ['cid' => $_QET['cid'], 'name' => $_QET['name']]);
            CookieCache::del('ClassListCountAll');
            dies(1, '分类[' . $_QET['name'] . ']删除成功');
        } else {
            dies(-1, '分类[' . $_QET['name'] . ']删除失败!');
        }
        break;
    case 'LevelDelete': //删除等级
        test(['mid|e', 'name|e'], '请填写完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('price', [
            'mid' => $_QET['mid']
        ]);
        if ($Res) {
            dies(1, '等级[' . $_QET['name'] . ']删除成功！');
        } else {
            dies(-1, '等级[' . $_QET['name'] . ']删除失败!');
        }
        break;
    case 'ClassList': //取出商品分类列表
        CookieCache::$prefix = 'ClassListCountAll';
        CookieCache::read();
        $DB = SQL::DB();
        $Res = $DB->select('class', '*', [
            'ORDER' => [
                'sort' => 'DESC',
            ],
        ]);
        if (!$Res) {
            dies(-1, '一个商品分类都没有');
        }
        if (isset($_QET['type']) && (int)$_QET['type'] === 2) {
            $Data = [];
            foreach ($Res as $v) {
                $v['count'] = $DB->count('goods', ['cid' => $v['cid']]);
                $v['support'] = explode(',', $v['support']);
                $Data[] = $v;
            }
            $Res = $Data;
        }
        CookieCache::add([
            'code' => 1,
            'msg' => '分类列表获取成功',
            'data' => $Res
        ], 0.5);
        dier([
            'code' => 1,
            'msg' => '分类列表获取成功',
            'data' => $Res
        ]);
        break;
    case 'ImageUp': //编辑器图片上传
        unset($_QET['act']);
        $Data = \Admin\Admin::ImageUp($_QET);
        dier($Data);
        break;
    case 'VideoUp': //编辑器视频上传
        unset($_QET['act']);
        $Data = \Admin\Admin::VideoUp($_QET);
        dier($Data);
        break;
    case 'ClassPaySet': //设置分类支付状态
        test(['cid|e', 'key|i'], '请提交完整参数!');
        $DB = SQL::DB();
        $Class = $DB->get('class', ['cid', 'support'], ['cid' => $_QET['cid']]);
        if ($Class) {
            $Ex = explode(',', $Class['support']);
            $Ex[$_QET['key']] = ((int)$Ex[$_QET['key']] === 2 ? 1 : 2);
            $Res = $DB->update('class', [
                'support' => implode(',', $Ex),
            ], [
                'cid' => $Class['cid'],
            ]);
            if ($Res) {
                CookieCache::del('ClassListCountAll');
                dies(1, '切换成功!');
            } else {
                dies(-1, '切换失败');
            }
        } else {
            dies(-1, '分类不存在!');
        }
        break;
    case 'SourceCache': //更新数据
        $File = for_dir(SYSTEM_ROOT . 'lib/supply', StringCargo::$exclude);
        $CacheName = md5(json_encode($File));
        mkdirs(SYSTEM_ROOT . "extend/log/Supply/");
        $ConfFile = SYSTEM_ROOT . 'extend/log/Supply/Docking_' . $CacheName . '.json';
        @unlink($ConfFile);
        dies(1, '更新成功');
        break;
    case 'SourceDataList': //获取已对接货源列表
        CookieCache::$prefix = 'SourceListCountAll';
        CookieCache::read();
        $DB = SQL::DB();
        $SQL = [
            'ORDER' => [
                'id' => 'DESC'
            ]
        ];
        if (!empty($_QET['id'])) {
            $SQL['id'] = $_QET['id'];
        }
        $Res = $DB->select('shequ', [
            'id', 'date', 'pattern', 'type',
            'secret', 'url', 'username', 'annotation',
            'class_name'
        ], $SQL);
        if (!empty($_QET['id']) && count($Res) === 0) {
            dies(-1, '对接货源不存在!');
        }
        $List = [];
        foreach ($Res as $key => $val) {
            $val['ping'] = '未测速';
            $val['username'] = substr_cut($val['username'], ceil(strlen($val['username']) / 5), ceil(strlen($val['username']) / 5));
            if ((string)$val['class_name'] === '-1' || $val['class_name'] === '') {
                $val['class_name'] = StringCargo::DataConversion($val['type']);
            }
            if ((string)$val['class_name'] === '0') {
                $val['class_name'] = 'jiuwu';
            }
            $SQ = \Admin\Admin::CommunityParameter($val['class_name']);
            $val['name'] = $SQ['name'];
            $val['image'] = $SQ['image'];
            unset($val['type']);
            $val['count'] = $DB->count('goods', ['sqid' => $val['id']]);
            $List[$key] = $val;
        }
        $Data = [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $List,
            'Docking' => StringCargo::Docking(),
        ];
        CookieCache::add($Data);
        dier($Data);
        break;
    case 'SourceDelete': //删除货源
        test(['id|e'], '请提交完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('shequ', [
            'id' => $_QET['id'],
        ]);
        if ($Res) {
            CookieCache::del('SourceListCountAll');
            dies(1, '删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'Ping': //域名测速
        test(['url|e'], '请将需要测速的域名提交完整!');
        dier([
            'code' => 1,
            'msg' => '测速完成,延迟越小越好!',
            'ping' => \Admin\Admin::Ping(StringCargo::UrlVerify($_QET['url']))
        ]);
        break;
    case 'DockingAdvertising'://对接广告列表
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => StringCargo::DockingAdvertising($_QET['cache'] ?? true),
        ]);
        break;
    case 'SourceList': //获取可对接货源列表
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => StringCargo::Docking(false, $_QET['cache'] ?? false),
        ]);
        break;
    case 'SourceAdd': //添加一个货源
        $Data = StringCargo::Docking($_QET['class_name']);
        $_QET['url'] = StringCargo::UrlVerify($_QET['url']);
        $InData = [];
        foreach ($Data['field'] as $key => $val) {
            if (!isset($_QET[$key])) {
                dies(-1, '请将' . $val['name'] . '参数提交完整!');
            }
            $InData[$key] = $_QET[$key];
        }
        $InData['annotation'] = $_QET['annotation'];
        $DB = SQL::DB();

        //已废弃参数
        $InData['type'] = -1;

        if (!empty($_QET['SQID'])) {
            if ($InData['username'] === '已隐藏,不改动就不修改') {
                unset($InData['username']);
            }
            if ($InData['password'] === '已隐藏,不改动就不修改') {
                unset($InData['password']);
            }
            $InData['class_name'] = $_QET['class_name'];
            $Res = $DB->update('shequ', $InData, [
                'id' => $_QET['SQID'],
            ]);
        } else {
            $InData['date'] = $date;
            $InData['class_name'] = $_QET['class_name'];
            $Res = $DB->insert('shequ', $InData);
        }
        if ($Res) {
            CookieCache::del('SourceListCountAll');
            dies(1, '操作成功!');
        } else {
            dies(-1, '操作失败!');
        }
        break;
    case 'ip': //获取IP
        if ($_QET['ip'] == 1) {
            dier([
                'code' => 1,
                'msg' => 'IP获取成功',
                'ip' => userip()
            ]);
        } else {
            die(get_curl(href(2) . ROOT_DIR_S . '/api.php?act=ip'));
        }
        break;
    case 'TokenList': //卡密列表
        CookieCache::$prefix = 'TokenListCountAll';
        CookieCache::read();
        test(['page|e', 'gid|i', 'limit|e'], '请提交完整!');
        $DB = SQL::DB();
        $Data = $_QET;
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'kid' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        if (!empty($_QET['gid'])) {
            $SQL['token.gid'] = $_QET['gid'];
        }

        if (!empty($_QET['name'])) {
            $SQL['OR'] = [
                'token.kid' => $_QET['name'],
                'token.order[~]' => $_QET['name'],
                'token.token[~]' => $_QET['name'],
                'token.code[~]' => $_QET['name'],
                'token.ip' => $_QET['name'],
                'token.uid' => $_QET['name'],
            ];
        }
        $Res = $DB->select('token', [
            '[>]goods' => ['gid' => 'gid']
        ], [
            'goods.name',
            'goods.gid',
            'token.kid',
            'token.uid',
            'token.token',
            'token.code',
            'token.ip',
            'token.order',
            'token.endtime',
            'token.addtime',
        ], $SQL);

        $Data = [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Res,
        ];
        CookieCache::add($Data);
        dier($Data);
        break;
    case 'TokenSum': //卡密数量
        CookieCache::$prefix = 'TokenListCountAll';
        CookieCache::read();
        $DB = SQL::DB();
        $SQL = [];
        if (!empty($_QET['gid'])) {
            $SQL['gid'] = $_QET['gid'];
        }
        if (!empty($_QET['name'])) {
            $SQL['OR'] = [
                'kid' => $_QET['name'],
                'order[~]' => $_QET['name'],
                'token[~]' => $_QET['name'],
                'code[~]' => $_QET['name'],
                'ip' => $_QET['name'],
                'uid' => $_QET['name'],
            ];
        }
        $Data = [
            'code' => 1,
            'msg' => '商品数量获取成功',
            'count' => $DB->count('token', $SQL)
        ];
        CookieCache::add($Data);
        dier($Data);
        break;
    case 'TokenDe': //删除卡密
        test(['kid|e'], '请提交完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('token', [
            'kid' => $_QET['kid'],
        ]);
        if ($Res) {
            CookieCache::del('TokenListCountAll');
            dies(1, '删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'freightList': //获取运费模板列表
        $DB = SQL::DB();
        $SQL = '*';
        if ((int)$_QET['type'] === 2) {
            $SQL = ['name', 'id'];
        }
        $Res = $DB->select('freight', $SQL, [
            'ORDER' => [
                'id' => 'ASC'
            ]
        ]);
        dier([
            'code' => 1,
            'msg' => '运费模板数据获取成功',
            'data' => $Res
        ]);
        break;
    case 'UserLevelReset': //等级重置
        $DB = SQL::DB();
        $re = $DB->query('DELETE FROM `sky_price`');
        if ($re) {
            $DB->query("INSERT INTO `sky_price` (`mid`,`sort`, `name`, `content`, `priceis`,  `pointsis`,`rule`, `money`, `addtime`,`ActualProfit`,`ProfitThreshold`)  VALUES ('1','1','普通用户', '普通用户，和游客没什么区别!', '30', '3000',1, '0.00','$date','50','20');");
            $DB->query("INSERT INTO `sky_price` (`mid`,`sort`, `name`, `content`, `priceis`,  `pointsis`,`rule`, `money`, `addtime`,`ActualProfit`,`ProfitThreshold`)  VALUES ('2','2','铜牌代理', '铜牌代理，可以加盟分店了,其他用户在你加盟分店下单你可以获得提成!', '28', '2800',1, '10.00','$date','50','20');");
            $DB->query("INSERT INTO `sky_price` (`mid`,`sort`, `name`, `content`, `priceis`,  `pointsis`,`rule`, `money`, `addtime`,`ActualProfit`,`ProfitThreshold`)  VALUES ('3','3','银牌代理', '银牌代理，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '26', '2600',1, '20.00','$date','50','20');");
            $DB->query("INSERT INTO `sky_price` (`mid`,`sort`, `name`, `content`, `priceis`,  `pointsis`,`rule`, `money`, `addtime`,`ActualProfit`,`ProfitThreshold`)  VALUES ('4','4','金牌代理', '金牌代理，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '24', '2400',1, '30.00','$date','50','20');");
            $DB->query("INSERT INTO `sky_price` (`mid`,`sort`, `name`, `content`, `priceis`,  `pointsis`,`rule`, `money`, `addtime`,`ActualProfit`,`ProfitThreshold`)  VALUES ('5','5','小站长', '小站长，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '22', '2200',1, '40.00','$date','50','20');");
            $DB->query("INSERT INTO `sky_price` (`mid`,`sort`, `name`, `content`, `priceis`,  `pointsis`,`rule`, `money`, `addtime`,`ActualProfit`,`ProfitThreshold`)  VALUES ('6','6','平台站长', '平台站长，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '20', '2000',1, '50.00','$date','50','20');");
            $DB->query("INSERT INTO `sky_price` (`mid`,`sort`, `name`, `content`, `priceis`,  `pointsis`,`rule`, `money`, `addtime`,`ActualProfit`,`ProfitThreshold`)  VALUES ('7','7','高级站长', '高级站长，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '18', '1800',1, '60.00','$date','50','20');");
            $DB->query("INSERT INTO `sky_price` (`mid`,`sort`, `name`, `content`, `priceis`,  `pointsis`,`rule`, `money`, `addtime`,`ActualProfit`,`ProfitThreshold`)  VALUES ('8','8','领袖站长', '领袖站长，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '16', '1600',1, '70.00','$date','50','20');");
            dies(1, '重置成功！');
        }
        dies(-1, '重置失败！');
        break;
    case 'UserLevelList': //获取等级列表
        $DB = SQL::DB();
        $SQL = '*';
        if ((int)$_QET['type'] === 2) {
            $SQL = ['name', 'priceis', 'pointsis'];
        }
        $Res = $DB->select('price', $SQL, [
            'ORDER' => [
                'sort' => 'DESC'
            ]
        ]);
        dier([
            'code' => 1,
            'msg' => '等级数据获取成功',
            'data' => $Res
        ]);
        break;
    case 'GoodsAdd': //保存/更新商品数据
        test(['name', 'image', 'quantity', 'quota', 'money|i', 'profits|i', 'cid'], '请将参数提交完整！');
        $Data = \Admin\Admin::GoodsAdd($_REQUEST);
        dier($Data);
        break;
    case 'GoodsData':
        test(['gid'], '请将参数提交完整！');
        $Data = \Admin\Admin::GoodsData($_QET['gid']);
        dier($Data);
        break;
    case 'DataInterface': //第三方货源数据获取接口
        test(['Source|e'], '参数缺失');
        $Data = \Admin\Admin::DockingRequestDistribution($_QET);
        dier($Data);
        break;
    case 'SupplyList': //获取供货商商品列表
        test(['id|e', 'page|e'], '参数缺失');
        $Data = official::SupplyList($_QET['id'], $_QET['page']);
        dier($Data);
        break;
    case 'OrderCount': //计算订单数量
        CookieCache::$prefix = 'OrderListCountAll';
        CookieCache::read();
        $DB = SQL::DB();
        $SQL = [];
        if (!empty($_QET['name'])) {
            $SQL['OR'] = [
                'input[~]' => $_QET['name'],
                'order' => $_QET['name'],
                'id' => $_QET['name'],
            ];
        }
        if (!empty($_QET['date'])) {
            if (!empty($_QET['date'][0])) {
                $SQL['addtitm[>]'] = $_QET['date'][0];
            }
            if (!empty($_QET['date'][1])) {
                $SQL['addtitm[<]'] = $_QET['date'][1];
            }
        }
        if (!empty($_QET['state'])) {
            $SQL['state'] = $_QET['state'];
        }
        if (!empty($_QET['gid'])) {
            $SQL['gid'] = $_QET['gid'];
        }

        if (!empty($_QET['uid'])) {
            $SQL['uid'] = $_QET['uid'];
        }

        $Res = $DB->count('order', $SQL);
        $Data = [
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ];
        CookieCache::add($Data, 30);
        dier($Data);
        break;
    case 'OrderList': //取出订单列表
        CookieCache::$prefix = 'OrderListCountAll';
        CookieCache::read();
        test(['page|e', 'limit|e'], '参数缺失');
        $Data = \Admin\Admin::OrderList($_QET);
        CookieCache::add($Data, 30);
        dier($Data);
        break;
    case 'UserCount': //用户数量
        $DB = SQL::DB();
        $SQL = [];

        if ((int)$_QET['GradeIndex'] !== -1) {
            //获取指定等级的用户
            $Count = $DB->count('price', ['state' => 1]);
            if ((int)$_QET['GradeIndex'] === $Count) {
                //最大
                $SQL['grade[>=]'] = (int)$_QET['GradeIndex'];
            } else {
                //范围之内
                $SQL['grade'] = (int)$_QET['GradeIndex'];
            }
        }

        if (!empty($_QET['name'])) {
            $SQL['OR'] = [
                'name[~]' => $_QET['name'],
                'id' => $_QET['name'],
                'qq' => $_QET['name'],
                'superior' => $_QET['name'],
                'domain' => $_QET['name'],
                'mobile' => $_QET['name'],
                'username' => $_QET['name'],
                'ip' => $_QET['name'],
            ];
        }

        $Res = $DB->count('user', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'UserList': //用户列表
        test(['page|e', 'limit|e'], '参数缺失');
        $Data = \Admin\Admin::UserList($_QET);
        dier($Data);
        break;
    case 'SeckillCount': //计算秒杀活动数量
        $DB = SQL::DB();
        $SQL = [];
        if (!empty($_QET['name'])) {
            $SQL['OR'] = [
                'id' => $_QET['name'],
                'gid' => $_QET['name'],
            ];
        }
        $Res = $DB->count('seckill', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'SeckillList': //秒杀活动列表
        test(['limit|e', 'page|e', 'name|i'], '请提交完整！');
        $Data = \Admin\Admin::SeckillList($_QET);
        dier([
            'code' => 1,
            'msg' => '列表获取成功!',
            'data' => $Data
        ]);
        break;
    case 'UserRedact': //用户单参数编辑(不会写入日志)
        test(['id|e', 'field|e', 'value|i'], '参数未提交完整！');
        \Admin\Admin::UserRedact($_QET);
        break;
    case 'UserLogin': //登录用户后台
        test(['id|e']);
        \Admin\Admin::UserLogin($_QET['id']);
        break;
    case 'DeleteUser': //删除用户
        test(['id|e']);
        $DB = SQL::DB();

        $Res = $DB->delete('user', [
            'id' => $_QET['id']
        ]);

        if ($Res) {
            dies(1, '删除成功！');
        } else {
            dies(-1, '删除失败！');
        }
        break;
    case 'UserBatchEditor': //批量设置
        test(['type|e']);
        \Admin\Admin::UserBatchEditor($_QET['type']);
        break;
    case 'UserLogCount': //日志数量
        $DB = SQL::DB();
        $SQL = [
            'uid[>=]' => 1
        ];

        if (!empty($_QET['name'])) {
            $SQL['name'] = $_QET['name'];
        }

        if (!empty($_QET['uid'])) {
            $SQL['uid'] = $_QET['uid'];
        }

        $Res = $DB->count('journal', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'UserLogList': //日志列表
        test(['page|e', 'limit|e'], '参数缺失');
        $Data = \Admin\Admin::UserLogList($_QET);
        dier($Data);
        break;
    case 'DeleteUserLog': //删除操作日志
        test(['id|e'], '请填写完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('journal', [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            dies(1, '删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'UserDetail': //用户收益明细(暂未开放)
        test(['id|e'], '参数缺失');
        $Data = \Admin\Admin::UserDetail($_QET['id']);
        dier($Data);
        break;
    case 'UserAdd': //添加用户
        test(['name|e', 'qq|e', 'username|e', 'password|e']);
        unset($_QET['act']);
        $Data = \Admin\Admin::UserAdd($_QET);
        dier($Data);
        break;
    case 'DockingCount': //对接日志数量
        global $accredit;
        $Flie = ROOT . 'includes/extend/log/Supply/Supply_' . $accredit['token'] . '.log';
        if (file_exists($Flie)) {
            $DataR = explode('SEGMENTATION', file_get_contents($Flie));
            $Data = [];
            foreach ($DataR as $value) {
                if (empty($value)) continue;
                $Data[] = json_decode($value, TRUE);
            }
        } else {
            $Data = [];
        }

        $_SESSION['DockingList'] = $Data;

        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => count($Data)
        ]);
        break;
    case 'DockingList': //获取对接日志列表
        test(['page|e', 'limit|e'], '参数缺失');
        $Data = \Admin\Admin::DockingList($_QET);
        dier($Data);
        break;
    case 'NoticeCount':
        $DB = SQL::DB();
        $Res = $DB->count('notice', []);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'NoticeList': //公告列表
        $DB = SQL::DB();
        $Limit = (int)$_QET['limit'];
        $Page = ($_QET['page'] - 1) * $Limit;
        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];
        $Res = $DB->select('notice', '*', $SQL);
        if (!$Res) {
            dies(-1, '空空如也');
        }
        dier([
            'code' => 1,
            'msg' => '公告列表获取成功！',
            'data' => $Res
        ]);
        break;
    case 'NoticeDelete': //删除公告
        test(['id|e'], '请填写完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('notice', [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            dies(1, '公告[' . $_QET['name'] . ']删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'NoticeStateSet': //调整公告
        test(['id|e', 'type|e', 'name|e'], '请填提交完整!');
        $DB = SQL::DB();

        $SQL = [];

        switch ((int)$_QET['type']) {
            case 1: //隐藏
                $SQL['state'] = 2;
                break;
            case 2: //显示
                $SQL['state'] = 1;
                break;
            case 3: //部分人可见
                $SQL['type'] = 2;
                break;
            case 4: //全部可见
                $SQL['type'] = 1;
                break;
        }

        $Res = $DB->update('notice', $SQL, [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            dies(1, '公告[' . $_QET['name'] . ']状态调整成功!');
        } else {
            dies(-1, '公告[' . $_QET['name'] . ']状态调整失败!');
        }
        break;
    case 'UpdateData': //获取更新数据
        dier(admin::deployment_update());
        break;
    case 'Update': //版本升级
        admin::deployment_update_install();
        break;
    case 'AppImage': //App图片预览接口
        test(['id|e'], '请将图片ID提交完整！');
        App::ImagePreview($_QET['id']);
        break;
    case 'AppUploading': //上传
        $Data = App::AppUploading();
        if ($Data['code'] >= 1 && $_QET['id'] >= 1 && $_QET['type'] >= 1) {
            $DB = SQL::DB();
            $Res = $DB->update('app', [
                ($_QET['type'] == 1 ? 'icon' : 'background') => $Data['id'],
            ], [
                'id' => $_QET['id']
            ]);
        }
        dier($Data);
        break;
    case 'AppCount': //APP数量
        $DB = SQL::DB();
        $SQL = [];
        if (!empty($_QET['uid'])) {
            $SQL['uid'] = $_QET['uid'];
        }
        if (!empty($_QET['name'])) {
            $SQL['name[~]'] = $_QET['name'];
        }
        $Res = $DB->count('app', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'AppList': //生成列表
        test(['page|e', 'limit|e'], '参数缺失');
        $Data = \Admin\Admin::AppList($_QET);
        dier($Data);
        break;
    case 'AppAdd': //创建App生成任务
        test(['uid|e', 'name|e', 'url|e'], '请提交完整！');
        $SQL = [
            'uid' => ((int)$_QET['uid'] >= 1 ? $_QET['uid'] : -1),
            'name' => $_QET['name'],
            'url' => StringCargo::UrlVerify($_QET['url'], 3),
            'theme' => $conf['appthemecolor'],
            'load_theme' => $conf['apploadthemecolor'],
            'icon' => $conf['appiconid'],
            'background' => $conf['appbackgroundid'],
            'addtime' => $date,
            'TaskMsg' => '任务待提交',
            'money' => 0,
        ];
        $DB = SQL::DB();
        $Res = $DB->insert('app', $SQL);
        if ($Res) {
            if ((int)$_QET['uid'] >= 1) {
                userlog('App打包', '官方客服在后台为您创建了App打包任务！，任务ID为：' . $DB->id(), $_QET['uid']);
            }
            dies(1, 'App打包任务创建成功');
        }
        dies(-1, '任务创建失败！');
        break;
    case 'AppColorSet': //修改App任务配色
        RVS(1000);
        test(['type|e', 'color|e', 'id']);
        if (strlen($_QET['color']) !== 7 || !strstr($_QET['color'], '#')) {
            dies(-1, '颜色格式有误！' . $_QET['color']);
        }
        $DB = SQL::DB();
        $Res = $DB->update('app', [
            ($_QET['type'] == 1 ? 'theme' : 'load_theme') => $_QET['color'],
        ], [
            'id' => $_QET['id'],
        ]);
        if ($Res) {
            dies(1, ($_QET['type'] == 1 ? '主题颜色' : '加载条颜色') . '修改成功！');
        } else dies(-1, '修改失败！');
        break;
    case 'AppSet':
        test(['id|e', 'field|e', 'value|i'], '参数未提交完整！');
        \Admin\Admin::AppSet($_QET);
        break;
    case 'AppSubmit': //提交App打包任务！
        App::AppSubmit($_QET['id']);
        break;
    case 'AppCalibration': //同步任务
        App::AppCalibration($_QET['id']);
        break;
    case 'AppDownload': //获取地址或部署
        test(['id|e', 'type|e'], '数据未提交完整！');
        App::AppDownload($_QET['id'], $_QET['type']);
        break;
    case 'AppDelete': //删除App
        test(['id|e'], '请填写完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('app', [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            dies(1, 'App打包任务[' . $_QET['id'] . ']删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'ExportOrders': //订单导出
        \Admin\Admin::ExportOrders($_QET);
        break;
    case 'MonitoredType': //可监控商品列表
        \Admin\Admin::MonitoredType();
        break;
    case 'ServerList': //可用服务器列表
        \Admin\Admin::ServerList($_QET['name']);
        break;
    case 'ServerStatusMonitoring': //实时监听服务器状态
        test(['id|e'], '请提交完整数据！');
        \Admin\Admin::ServerStatusMonitoring($_QET['id']);
        break;
    case 'ReMemory': //清理服务器内存碎片
        test(['id|e'], '请提交完整数据！');
        \Admin\Admin::ReMemory($_QET['id']);
        break;
    case 'CreateHostSpace': //创建主机空间
        \Admin\Admin::CreateHostSpace($_QET);
        break;
    case 'DeleteServer': //删除服务器节点
        test(['id|e']);
        $DB = SQL::DB();
        $Res = $DB->delete('server', [
            'id' => $_QET['id'],
        ]);
        if ($Res) {
            dies(1, '删除成功');
        }
        dies(-1, '删除失败！');
        break;
    case 'HostCount': //主机总数
        $DB = SQL::DB();
        $SQL = [];

        if (!empty($_QET['sid'])) {
            $SQL['server'] = $_QET['sid'];
        }

        if (!empty($_QET['name'])) {
            $SQL['OR'] = [
                'identification' => $_QET['name'],
                'id' => $_QET['name'],
                'username[~]' => $_QET['name'],
                'server' => $_QET['name'],
                'uid' => $_QET['name'],
            ];
        }
        $Res = $DB->count('mainframe', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'HostList': //主机列表
        \Admin\Admin::HostList($_QET);
        break;
    case 'ModifyHostSpace': //修改数据
        \Admin\Admin::ModifyHostSpace($_QET);
        break;
    case 'HostActivation': //激活空间
        $DB = SQL::DB();
        $MainframeData = $DB->get('mainframe', '*', [
            'id' => $_QET['id'],
            'type' => 2,
            'state' => 1,
        ]);
        if (!$MainframeData) {
            dies(-1, '激活失败,主机空间不存在或已经激活过了！');
        }
        $ServerData = $DB->get('server', '*', ['id' => $MainframeData['server'], 'state' => 1]);
        if (!$ServerData) {
            dies(-1, '服务器不存在,或已经关闭！');
        }
        BTC::Conf($ServerData); //赋值
        $Data = BTCO::Getaddsite($MainframeData);
        if ($Data['siteStatus'] === true && $Data['databaseStatus'] === true) {
            //当前时间 + 激活可用时长时间戳;
            $Time = time() + (strtotime($MainframeData['endtime']) - strtotime($MainframeData['addtime']));
            $EndDate = date('Y-m-d H:i', $Time);

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
                'id' => $_QET['id']
            ]);
            //调整主机状态
            BTCO::Getendtime($Data['siteId'], $EndDate);
            BTCO::GetSetLimitNet($Data['siteId'], $MainframeData);
            BTCO::GetSessionPath($Data['siteId']);
            dies(0, '主机空间激活成功！');
        } else {
            if (isset($Data['databaseStatus']) && $Data['databaseStatus'] === false) {
                BTCO::WriteException($_QET['id'], '数据库环境不完善，无法创建数据库，请在宝塔的软件商店安装：phpMyAdmin 5.0！', 2);
            } else {
                BTCO::WriteException($_QET['id'], $Data['msg'], 2);
            }

            dies(-1, $Data['msg']);
        }
        break;
    case 'SpaceStatus': //切换主机开启关闭状态
        $Dv = BTCO::DataV($_QET['id']);
        $DB = SQL::DB();
        $MainframeData = $Dv['MainframeData'];
        $ServerData = $Dv['ServerData'];
        if ($MainframeData['endtime'] <= $date) {
            dies(-1, '<font color="red" size="3">警告：当前服务器主机空间已经到期<br>数据和文件只会保留3天,3天后自动删除,清除后无法恢复！</font>');
        }
        $re = BTCO::GetSwitchState($MainframeData['siteId'], $MainframeData['identification'] . '.com', ($_QET['state'] == 2 ? 2 : 1));
        if ($re['status'] === true) {
            userlog('状态切换', '您切换了网站主机空间(' . $MainframeData['id'] . ')的状态!', $MainframeData['uid']);
            $DB->update('mainframe', ['status' => ($_QET['state'] == 2 ? 2 : 1)], ['id' => $MainframeData['id']]);
            dies(0, ($_QET['state'] == 2 ? '关闭' : '开启') . '成功');
        } else {
            BTCO::WriteException($_QET['id'], $re['msg'], 2);
            dies(-1, '切换失败!' . $re['msg']);
        }
        break;
    case 'HostStatusModification': //修改主机状态
        test(['id|e', 'state|e']);
        $DB = SQL::DB();
        $Res = $DB->update('mainframe', ['state' => ($_QET['state'] == 2 ? 2 : 1)], ['id' => $_QET['id']]);
        if ($Res) {
            dies(1, '调整成功');
        } else {
            dies(-1, '调整失败！');
        }
        break;
    case 'DeleteHost': //删除空间
        test(['id|e']);
        $DB = SQL::DB();
        $MainframeData = $DB->get('mainframe', '*', [
            'id' => $_QET['id'],
        ]);
        if (!$MainframeData) {
            dies(-1, '网站主机空间不存在,或已失效！');
        }
        $ServerData = $DB->get('server', '*', ['id' => $MainframeData['server'], 'state' => 1]);
        if (!$ServerData) {
            dies(-1, '节点不存在,或已经关闭！');
        }
        BTC::Conf($ServerData);
        if ($MainframeData['type'] != 1) {
            //只删除数据
            $re = $DB->delete('mainframe', ['id' => $_QET['id']]);
        } else {
            //删除网站+数据
            $DLE = BTCO::GetDeleteSite($MainframeData['siteId'], $MainframeData['identification'] . '.com');
            if ($DLE['status'] === true) {
                $re = $DB->delete('mainframe', ['id' => $_QET['id']]);
            } else {
                BTCO::WriteException($_QET['id'], $DLE['msg'], 2);
                dies(-1, '删除失败：' . $DLE['msg'] . '！');
            }
        }
        if ($re) {
            dies(0, '删除成功,且无法恢复！');
        } else {
            dies(-1, '删除失败！');
        }
        break;
    case 'SizeCalibration': //校准主机数据
        test(['id|e']);
        $Dv = BTCO::DataV($_QET['id']);
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
            'id' => $_QET['id']
        ]);
        if ($Res) {
            dies(1, '校准成功，此空间大小为：' . $Size . 'MB');
        } else {
            dies(-1, '校准失败！');
        }
        break;
    case 'LogHostBackground': //登陆主机后台
        test(['key|e']);

        $_SESSION[Server::$SessionName] = $_QET['key'];

        dier([
            'code' => 1,
            'msg' => '登陆数据写入成功！',
            'url' => '../HostAdmin/index.php',
        ]);
        break;
    case 'CreateServer': //创建/修改服务器
        \Admin\Admin::CreateServer($_QET);
        break;
    case 'ServerGet': //获取服务器信息
        $DB = SQL::DB();
        $Res = $DB->get('server', '*', [
            'id' => $_QET['id'],
        ]);
        if (!$Res) {
            dies(-1, '数据获取失败！');
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Res
        ]);
        break;
    case 'DockingDataServer': //获取服务端对接配置
        \Admin\Admin::DockingDataServer();
        break;
    case 'AddProductsQuickly': //快速添加商品
        \Admin\Admin::AddProductsQuickly($_POST);
        break;
    case 'MyIsland': //商城海个人中心
        \Admin\Admin::MyIsland();
        break;
    case 'MyIslandSet': //修改数据
        \Admin\Admin::MyIslandSet($_QET);
        break;
    case 'MyIslandSynchronization': //同步数据
        \Admin\Admin::MyIslandSynchronization();
        break;
    case 'MyIslandState': //修改状态
        \Admin\Admin::MyIslandState($_QET['state']);
        break;
    case 'MyIslandPurchase': //购买热度
        \Admin\Admin::MyIslandPurchase($_QET['money']);
        break;
    case 'MyIslandSignDaily': //每日热度签到
        \Admin\Admin::MyIslandSignDaily();
        break;
    case 'MyIslandList': //商城海列表
        \Admin\Admin::MyIslandList();
        break;
    case 'MyIslandGiveThumbs': //点赞或踩
        test(['id', 'type', 'state']);
        RVS(500);
        \Admin\Admin::MyIslandGiveThumbs($_QET['id'], $_QET['type'], $_QET['state']);
        break;
    case 'MyPayList': //获取服务端支付通道列表
        RVS(500);
        \Admin\Admin::MyPayList();
        break;
    case 'MyPayOrder': //创建服务端充值订单
        test(['money', 'type']);
        RVS(500);
        \Admin\Admin::MyPayOrder($_QET['money']);
        break;
    case 'MyPay': //获取充值订单详情
        test(['order']);
        RVS(500);
        \Admin\Admin::MyPay($_QET['order']);
        break;
    case 'MyPayTs': //获取付款参数
        test(['order', 'key', 'type']);
        RVS(500);
        \Admin\Admin::MyPayTs($_QET);
        break;
    case 'MyIslandCommentList': //评论列表
        test(['id']);
        \Admin\Admin::MyIslandCommentList($_QET['id']);
        break;
    case 'MyIslandInitiateComments': //发起评论
        test(['id', 'score', 'msg']);
        \Admin\Admin::MyIslandInitiateComments($_QET);
        break;
    case 'RechargeAdd': //充值卡生成
        test(['name', 'money', 'count', 'type']);
        \Admin\Admin::RechargeAdd($_QET);
        break;
    case 'RechargeList': //充值卡列表
        $DB = SQL::DB();
        $Limit = (int)$_QET['limit'];
        $Page = ($_QET['page'] - 1) * $Limit;
        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        if (!empty($_QET['name'])) {
            $SQL['OR'] = [
                'name' => $_QET['name'],
                'uid' => $_QET['name'],
                'token' => $_QET['name'],
            ];
        }

        $Res = $DB->select('recharge', '*', $SQL);
        if (!$Res) {
            dies(-1, '空空如也');
        }
        dier([
            'code' => 1,
            'msg' => '充值卡列表获取成功！',
            'data' => $Res
        ]);
        break;
    case 'RechargeCount': //充值卡数量
        $DB = SQL::DB();
        $SQL = [];

        if (!empty($_QET['name'])) {
            $SQL = [
                'OR' => [
                    'name[~]' => $_QET['name'],
                    'uid' => $_QET['name'],
                    'token' => $_QET['name'],
                ]
            ];
        }

        $Res = $DB->count('recharge', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'DeleteRecharge': //删除
        test(['id|e'], '请提交完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('recharge', [
            'id' => $_QET['id'],
        ]);
        if ($Res) {
            dies(1, '删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'TemData': //获取模板列表
        \Admin\Admin::TemData();
        break;
    case 'TemConfSet': //保存模板配置界面
        \Admin\Admin::TemConfSet($_QET);
        break;
    case 'ApiPing': //节点Ping
        test(['id']);
        Curl::Ping($_QET['id']);
        break;
    case 'ApiSet': //切换节点
        test(['id']);
        if (file_put_contents('../assets/log/sequence.lock', ($_QET['id'] + 1))) {
            dies(1, '切换成功');
        } else {
            dies(-1, '切换失败');
        }
        break;
    case 'ApiList': //节点列表
        dier([
            'code' => 1,
            'msg' => '节点列表获取成功',
            'data' => Curl::ApiLists()
        ]);
        break;
    case 'BannerList':
        \Admin\Admin::BannerList($_QET['type']);
        break;
    case 'BannerGiveThumbs':
        test(['id', 'type', 'state']);
        RVS(500);
        \Admin\Admin::BannerGiveThumbs($_QET['id'], $_QET['type'], $_QET['state']);
        break;
    case 'PriceAccuracy': //修改商品精度
        test(['id', 'value|i']);
        $DB = SQL::DB();
        $num = (int)$_QET['value'];
        if ($num < 0 || $num > 8) {
            dies(-1, '参数范围异常！');
        }
        $Res = $DB->update('goods', [
            'accuracy' => $num,
            'update_dat' => $date,
        ], [
            'gid' => $_QET['id'],
        ]);
        if ($Res) {
            dies(1, '修改成功，本次成功修改了' . count($_QET['id']) . '个商品！');
        } else {
            dies(-1, '修改失败！');
        }
        break;
    case 'NumberOrders': //商品份数修改
        test(['id', 'min', 'max']);
        $DB = SQL::DB();
        $min = (int)$_QET['min'];
        $max = (int)$_QET['max'];
        if ($min < 1 || $max < $min) {
            dies(-1, '参数范围异常！');
        }
        $Res = $DB->update('goods', [
            'min' => $min,
            'max' => $max,
            'update_dat' => $date,
        ], [
            'gid' => $_QET['id'],
        ]);
        if ($Res) {
            dies(1, '修改成功，本次成功修改了' . count($_QET['id']) . '个商品！');
        } else {
            dies(-1, '修改失败！');
        }
        break;
    case 'InventoryChanges': //商品库存修改
        test(['id', 'value']);
        $DB = SQL::DB();
        $value = (int)$_QET['value'];
        if ($value < 0) {
            dies(-1, '商品库存不可低于0，可以设置等于0！');
        }
        $Res = $DB->update('goods', [
            'quota' => $value,
            'update_dat' => $date,
        ], [
            'gid' => $_QET['id'],
        ]);
        if ($Res) {
            dies(1, '库存修改成功，本次成功修改了' . count($_QET['id']) . '个商品！');
        } else {
            dies(-1, '修改失败！');
        }
        break;
    case 'RechargeBatchRemove': //删除充值卡
        test(['type|e', 'state|e'], '请提交完整!');
        $DB = SQL::DB();
        $SQL = [];
        if ($_QET['type'] == 2) {
            $_QET['type'] = 1;
        } else if ($_QET['type'] == 3) {
            $_QET['type'] = 2;
        }
        if ($_QET['state'] == 1) {
            $SQL['uid'] = -1;
        } else {
            $SQL['uid[!]'] = -1;
        }
        if (!empty($_QET['money'])) {
            $SQL['money'] = $_QET['money'];
        }
        $Res = $DB->delete('recharge', $SQL);
        if ($Res) {
            dies(1, '批量删除成功!');
        } else {
            dies(-1, '批量删除失败!');
        }
        break;
    case 'PrepaidCardExported': //导出充值卡
        test(['money|e', 'type|e', 'state|e']);
        $DB = SQL::DB();

        $SQL = [
            'type' => $_QET['type'],
            'money' => $_QET['money'],
        ];

        if ($_QET['state'] == 1) {
            $SQL['uid'] = -1;
        } else {
            $SQL['uid[!]'] = -1;
        }
        $Res = $DB->select('recharge', [
            'name', 'token', 'money'
        ], $SQL);

        $Data = [];
        $Name = '面额：' . round($_QET['money'], 2) . '' . ($_QET['type'] == 2 ? $conf['currency'] . '的充值卡' : '余额的充值卡') . '(' . ($_QET['state'] == 2 ? '已使用' : '未使用') . ')';
        $Data[] = '类型：' . ($_QET['type'] == 2 ? '积分充值卡' : '余额充值卡');
        $Data[] = '状态：' . ($_QET['state'] == 2 ? '已使用' : '未使用');
        $Data[] = '额度：' . $_QET['money'] . ($_QET['type'] == 2 ? $conf['currency'] : '余额');
        $Data[] = '卡密导出时间：' . $date;
        $Data[] = '-------------------------------------------';
        foreach ($Res as $key => $val) {
            $Data[] = $val['token'];
        }

        if (count($Res) === 0) {
            dies(-1, '没有可以导出的内容！');
        }

        dier([
            'code' => 1,
            'name' => $Name,
            'msg' => '本次共导出了' . count($Res) . '条数据！',
            'data' => $Data
        ]);
        break;
    case 'AddSeckill': //创建秒杀活动
        test(['gid', 'depreciate', 'start_time', 'end_time', 'astrict']);
        \Admin\Admin::AddSeckill($_QET);
        break;
    case 'SeckillDelete':
        test(['id|e'], '请提交完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('seckill', [
            'id' => $_QET['id'],
        ]);
        if ($Res) {
            dies(1, '删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'AddCardSecret': #添加卡密
        $DB = SQL::DB();
        $Goods = $DB->get('goods', ['gid'], [
            'gid' => $_QET['gid']
        ]);
        if (!$Goods) {
            dies(-1, '商品不存在，请前往商品列表获取商品ID');
        }
        ProductsExchange::AddCardSecret($_QET['gid'], $_QET['count']);
        break;
    case 'CardCountGoods': //兑换卡数量
        ProductsExchange:: CardCountGoods($_QET['gid'], $_QET['type']);
        break;
    case 'CardListGoods': #兑换卡列表
        ProductsExchange::CardListGoods($_QET['page'], $_QET['limit'], $_QET['gid'], $_QET['type']);
        break;
    case 'DeleteGoodsCard': #删除卡密
        //1根据前一个参数删除，2删除全部已使用，3删除全部未使用，4删除全部！
        ProductsExchange::DeleteGoodsCard($_QET['id'], $_QET['state']);
        break;
    case 'HtmlGoodsCard': //生成独立html兑换页面
        ProductsExchange::HtmlGoodsCard();
        break;
    case 'ForumList': //获取论坛公告信息
        Forum::getUrl($_QET['type'] ?? 1);
        break;
    case 'ForumUrl':
        header('Location:' . Forum::$Url);
        break;
    case 'OrderPayCount': //获取当前支付订单数量
        $DB = SQL::DB();
        $SQL = [];

        if (!empty($_QET['name'])) {
            $SQL = [
                'OR' => [
                    'input[~]' => $_QET['name'],
                    'uid' => $_QET['name'],
                    'id' => $_QET['name'],
                    'trade_no' => $_QET['name'],
                    'name' => $_QET['name']
                ]
            ];
        }
        $Res = $DB->count('pay', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'OrderPayList': //获取当前支付订单列表
        $DB = SQL::DB();
        $Limit = (int)$_QET['limit'];
        $Page = ($_QET['page'] - 1) * $Limit;
        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        if (!empty($_QET['name'])) {
            $SQL['OR'] = [
                'input[~]' => $_QET['name'],
                'uid' => $_QET['name'],
                'id' => $_QET['name'],
                'trade_no' => $_QET['name'],
                'name' => $_QET['name']
            ];
        }

        $Res = $DB->select('pay', '*', $SQL);
        if (!$Res) {
            dies(-1, '空空如也');
        }
        $Data = [];
        foreach ($Res as $v) {
            if ($v['input'] != -1) {
                $v['input'] = implode(',', json_decode($v['input'], true));
            }
            $Data[] = $v;
        }
        dier([
            'code' => 1,
            'msg' => '充值订单列表获取成功！',
            'data' => $Data
        ]);
        break;
    case 'OrderPayDelete': //删除指定订单
        test(['id|e'], '请提交完整!');
        $DB = SQL::DB();
        $Res = $DB->delete('pay', [
            'id' => $_QET['id'],
        ]);
        if ($Res) {
            dies(1, '删除成功!');
        } else {
            dies(-1, '删除失败!');
        }
        break;
    case 'OrderPaySubmit': //提交补单
        test(['id|e'], '请提交完整!');
        $DB = SQL::DB();
        $Order = $DB->get('pay', '*', [
            'id' => $_QET['id']
        ]);
        if (!$Order) {
            dies(-1, '支付订单不存在！');
        }
        dier(Pay::PaySuccess($Order, [
            'out_trade_no' => $Order['order'],
            'money' => $Order['money'],
            'trade_no' => '手动补单,无对接订单号',
        ]));
        break;
    case 'promotionList': //获取货源定向推广列表
        $ListData = Curl::Get('/api/Recommend/index', [
            'act' => 'ListData',
        ]);
        $ListData = json_decode(xiaochu_de($ListData), true);
        if (empty($ListData) || $ListData['code'] < 0) {
            dies(-1, $ListData['msg'] ?? '服务端数据获取失败！');
        }
        foreach (StringCargo::Docking(false) as $key => $value) {
            $ListData['SourceList'][$key] = $value['name'];
        }
        foreach ($ListData['data'] as $k => $v) {
            $Time = strtotime($v['endtime']);
            if ($Time < time()) {
                $v['type'] = -1;
            } else {
                $v['type'] = 1;
            }
            $v['className'] = $ListData['SourceList'][$v['class_name']] ?? $v['class_name'];
            $ListData['data'][$k] = $v;
        }
        $ListData['money'] = round($ListData['money'], 3);
        dier($ListData);
        break;
    case 'promotionCreate': //创建货源定向推广列表
        test(['name|e', 'url|e', 'content|e', 'class_name|e'], '请提交完整！');
        unset($_QET['id']);
        $_QET['act'] = 'Create';
        $_QET['count'] = 1; //默认1月
        $_QET['url'] = StringCargo::UrlVerify($_QET['url']);
        $ListData = Curl::Get('/api/Recommend/index', $_QET);
        $ListData = json_decode(xiaochu_de($ListData), true);
        if (empty($ListData) || $ListData['code'] < 0) {
            dies(-1, $ListData['msg'] ?? '服务端数据获取失败！');
        }
        dier($ListData);
        break;
    case 'promotionRenew': //续期货源定向推广列表
        test(['id|e', 'count|e'], '请将参数提交完整！');
        $_QET['act'] = 'Renew';
        $ListData = Curl::Get('/api/Recommend/index', $_QET);
        $ListData = json_decode(xiaochu_de($ListData), true);
        if (empty($ListData) || $ListData['code'] < 0) {
            dies(-1, $ListData['msg'] ?? '服务端数据获取失败！');
        }
        dier($ListData);
        break;
    case 'promotionEditing': //编辑货源定向推广列表
        test(['name|e', 'url|e', 'content|e', 'class_name|e'], '请提交完整！');
        $_QET['act'] = 'Editing';
        $ListData = Curl::Get('/api/Recommend/index', $_QET);
        $ListData = json_decode(xiaochu_de($ListData), true);
        if (empty($ListData) || $ListData['code'] < 0) {
            dies(-1, $ListData['msg'] ?? '服务端数据获取失败！');
        }
        dier($ListData);
        break;
    case 'CalibrationDatabase': //数据库校准
        dier(Maintain::databaseCalibre());
        break;
    case 'SaveFareIncreaseRule': //保存加价规则
        test(['id|e', 'name|e', 'rules|e']);
        $DB = SQL::DB();
        $SQL = [
            'name' => $_QET['name'],
            'rules[JSON]' => $_QET['rules'],
        ];
        if ($_QET['id'] == -1) {
            $SQL['addtime'] = $date;
            $Res = $DB->insert('profit_rule', $SQL);
        } else {
            $Res = $DB->update('profit_rule', $SQL, [
                'id' => $_QET['id'],
            ]);
        }
        if ($Res) {
            dies(1, '操作成功');
        }
        dies(-1, '操作失败！');
        break;
    case 'ListFareIncreaseRule': //规则列表
        global $date;
        $Data = $_QET;
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        $DB = SQL::DB();

        if (!empty($Data['name'])) {
            $SQL['OR'] = [
                'id' => $Data['name'],
                'name[~]' => $Data['name'],
            ];
        }
        $Res = $DB->select('profit_rule', '*', $SQL);
        if (!$Res) {
            dies(-1, ' 没有更多了');
        }
        $DataList = [];
        foreach ($Res as $v) {
            $content = [];
            foreach (json_decode($v['rules'], true) ?? [] as $value) {
                $content[] = "当商品成本大于等于{$value['min']}元，并且小于等于{$value['max']}元时「商品利润比降为{$value['profit']}%」";
            }
            $v['rules'] = implode("<br>", $content);
            unset($content);
            $pr_ni = $DB->select('price', ['mid', 'name'], ['rule' => $v['id']]);
            if ($pr_ni) {
                $pr_con = [];
                foreach ($pr_ni as $i) {
                    $pr_con[] = $i['name'] . '(' . $i['mid'] . ')';
                }
            }
            $v['call'] = (!$pr_ni ? false : implode('<br>', $pr_con));
            unset($pr_ni, $pr_con);
            $DataList[] = $v;
        }
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $DataList,
        ]);
        break;
    case 'CountFareIncreaseRule'://数量
        $DB = SQL::DB();
        $SQL = [];
        if (!empty($_QET['name'])) {
            $SQL = [
                'OR' => [
                    'id' => $_QET['name'],
                    'name[~]' => $_QET['name'],
                ]
            ];
        }
        $Res = $DB->count('profit_rule', $SQL);
        dier([
            'code' => 1,
            'msg' => '计算成功',
            'count' => $Res
        ]);
        break;
    case 'StateFareIncreaseRule': //修改状态
        test(['id|e', 'state|e']);
        $DB = SQL::DB();
        $Res = $DB->update('profit_rule', [
            'state' => $_QET['state'],
        ], [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            dies(1, '修改成功');
        }
        dies(-1, '修改失败！');
        break;
    case 'DeleteFareIncreaseRule': //删除规则
        test(['id|e']);
        $DB = SQL::DB();
        $Res = $DB->delete('profit_rule', [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            dies(1, '删除成功');
        }
        dies(-1, '删除失败！');
        break;
    default:
        dies(-1, '无效访问!');
}
