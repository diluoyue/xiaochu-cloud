<?php
// +----------------------------------------------------------------------
// | Project: xc
// +----------------------------------------------------------------------
// | Creation: 2022/7/13
// +----------------------------------------------------------------------
// | Filename: OnebuttonDockingSystem.php
// +----------------------------------------------------------------------
// | Explain: 同系统一键对接
// +----------------------------------------------------------------------
namespace extend;

use lib\AppStore\AppList;
use lib\supply\Api;
use lib\supply\Price;
use lib\supply\StringCargo;
use lib\supply\xiaochu;
use Medoo\DB\SQL;

class OnebuttonDockingSystem
{
    /**
     * @param array $Data
     * 方法入口文件
     */
    public static function origin(array $Data)
    {
        $AdminVerify = AppList::AdminVerify();
        if (!$AdminVerify && $Data['TypeS'] !== 'GoodsList' && $Data['TypeS'] !== 'Detection' && $Data['TypeS'] !== 'FreightList') {
            dies(-1, '无访问权限！');
        }
        switch ($Data['TypeS']) {
            case 'Test':
                test(['domain|e', 'uid|e', 'token|e'], '请将参数提交完整！');
                dier(self::Test($Data));
            case 'Start': //开始
                if (!$AdminVerify) {
                    dies(-1, '无访问权限！');
                }
                self::Start($Data);
                break;
            case 'GoodsList':
                dier(self::GoodsList($Data));
                break;
            case 'FreightList':
                self::FreightList($Data);
                break;
            case 'Caching': //创建商品缓存数据
                self::Caching();
                break;
            case 'Count':
                $DB = SQL::DB();
                dier([
                    'code' => 1,
                    'msg' => '数据获取成功',
                    'GoodsCount' => $DB->count('goods', []),
                    'ClassCount' => $DB->count('class', []),
                ]);
            case 'Detection': //探测是否存在此插件
                dies(1, 'success');
            default:
                dies(-1, '404');
        }
    }

    public static function Start($Data)
    {
        ini_set("max_execution_time", "600");
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        $List = self::Test($Data);
        if ($List['code'] !== 1) {
            dier($List);
        }
        $DB = SQL::DB();
        //创建货源
        $SQL = [
            'url' => $Data['domain'],
            'class_name' => 'xiaochu',
            'username' => $Data['uid'],
            'password' => $Data['token'],
            'secret' => 1,
            'type' => -1,
        ];
        $Shequ = $DB->get('shequ', '*', $SQL);
        if (!$Shequ) {
            $DB->insert('shequ', $SQL);
            $SQL['id'] = $DB->id();
            $Shequ = $SQL;
        }

        $Shequ['url'] = StringCargo::UrlVerify($Shequ['url']);

        unset($SQL);

        $DB->delete('goods', []);
        $DB->delete('class', []);

        //创建分类
        $ClassCount = self::CreateClass($List['ClassList'], $Data);

        //创建商品
        $GoodsCount = self::CreateGoods($List['GoodsList'], $Shequ, $Data);

        dies(1, '本次成功创建了' . $ClassCount . '个分类！,' . $GoodsCount . '个商品！');
    }

    /**
     * @param $ClassList
     * 创建分类
     */
    public static function CreateClass($ClassList, $Data)
    {
        global $date;
        $DB = SQL::DB();
        $Success = 0;

        if ($Data['ScreeningMode'] == 3 || $Data['ScreeningMode'] == 4 && $Data['FilterContent'] !== '') {
            $Type = true;
        } else {
            $Type = false;
        }

        foreach ($ClassList as $value) {
            $value['date'] = $date;
            unset($value['count']);
            if ($Type && strstr($value['name'], $Data['FilterContent'])) {
                continue;
            }
            if ($DB->insert('class', $value)) {
                ++$Success;
            }
            unset($value);
        }
        return $Success;
    }

    /**
     * 运费模板
     */
    public static function FreightList($Data)
    {
        //验证
        xiaochu::verify($Data);
        $DB = SQL::DB();
        $Res = $DB->select('freight', '*', []);
        if (!$Res) {
            dies(-1, '运费模板取出失败！');
        }
        dier([
            'code' => 1,
            'msg' => '数据获取成功！',
            'data' => $Res
        ]);
    }

    /**
     * @param $GoodsList
     * @param $Shequ
     * 创建商品
     */
    public static function CreateGoods($GoodsList, $Shequ, $Data)
    {
        $Success = 0;
        //探测对方站点是否安装了此插件
        if ($Data['Forced'] == 1) {
            $Get = Api::Curl($Shequ['url'] . 'api.php?act=OnebuttonDockingSystem&TypeS=Detection');
            $Get = json_decode($Get, true);
        } else {
            $Get = false;
        }

        $DB = SQL::DB();

        if ($Data['ScreeningMode'] == 2 || $Data['ScreeningMode'] == 4 && !empty($Data['FilterContent'])) {
            $Type = true;
        } else {
            $Type = false;
        }
        if (!$Get || $Get['code'] < 0) {
            foreach ($GoodsList as $value) {
                $Goods = self::GetGoods($value['gid'], $Shequ);
                if ($Goods['code'] !== 1) {
                    if (strstr($Goods['msg'], '未设置对接白名单')) {
                        dies(-1, $Goods['msg']);
                    }
                    continue;
                } else {
                    if ($Type && strstr($Goods['data']['name'], $Data['FilterContent'])) {
                        continue;
                    }
                    $DB->insert('goods', $Goods['data']);
                    if ($DB->error()['0'] == '00000') {
                        ++$Success;
                    } else {
                        dies(-1, '请确认对接站的站点版本和当前站点版本一致！<br>错误码：【' . $DB->error()[1] . '】 错误详情：' . $DB->error()[2]);
                    }
                }
                unset($value, $Goods); //释放内存
            }
        } else {
            $Success += self::GetGoodsList($GoodsList, $Shequ, $Type, $Data['FilterContent'], $Data);
        }
        return $Success;
    }

    /**
     * @param $GoodsList //商品列表[简洁]
     * @param $Shequ //对接数据
     * @param $Type //数据筛选类型
     * @param $FilterContent //需要筛选的内容
     * @param $Data //提交的数据
     * @return int
     * 批量创建商品【通过插件Api对接】
     */
    public static function GetGoodsList($GoodsList, $Shequ, $Type, $FilterContent, $Data)
    {
        global $date;
        $List = [];
        foreach ($GoodsList as $value) {
            $List[] = $value['gid'];
        }
        $DataPost = [
            'url' => href(),
            'id' => $Shequ['username'],
            'goodsList' => base64_encode(json_encode($List)),
            'act' => 'OnebuttonDockingSystem',
            'TypeS' => 'GoodsList',
            'Caching' => $Data['Caching'],
        ];

        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $Shequ['password'])
        ], $DataPost);
        $CurlData = Api::Curl($Shequ['url'] . 'api.php', $DataPost);

        $CurlDataJson = json_decode($CurlData, true);
        unset($DataPost, $CurlData);
        if (empty($CurlDataJson)) {
            dies(-1, '对接网站：' . $Shequ['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率');
        }

        if ($CurlDataJson['code'] !== 1) {
            dies(-1, $CurlDataJson['msg']);
        }

        //写入商品数据
        $GoodsList = $CurlDataJson['data'];
        $DB = SQL::DB();
        $Success = 0;
        foreach ($GoodsList as $SQL) {
            if ($Type && strstr($SQL['name'], $FilterContent)) {
                continue;
            }
            unset($SQL['points']);
            $SQL['image'] = json_encode($SQL['image']);
            $SQL['extend'] = json_encode($SQL['extend']);
            $SQL['specification_sku'] = json_encode($SQL['specification_sku']);
            $SQL['specification_spu'] = json_encode($SQL['specification_spu']);
            $SQL['update_dat'] = $date;
            $SQL['deliver'] = -1;
            $SQL['sqid'] = $Shequ['id'];
            $DB->insert('goods', $SQL);
            if ($DB->error()['0'] == '00000') {
                ++$Success;
            } else {
                dies(-1, '请确认对接站的站点版本和当前站点版本一致！<br>错误码：【' . $DB->error()[1] . '】 错误详情：' . $DB->error()[2]);
            }
            unset($SQL);
        }

        //开始写入运费模板
        $DataPost = [
            'url' => href(),
            'id' => $Shequ['username'],
            'AppApies' => '1',
            'identification' => 'OnebuttonDockingSystem',
            'TypeS' => 'FreightList'
        ];
        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $Shequ['password'])
        ], $DataPost);
        $CurlData = Api::Curl($Shequ['url'] . 'api.php', $DataPost);
        $CurlDataJson = json_decode($CurlData, true);
        unset($DataPost, $CurlData);
        if (empty($CurlDataJson)) {
            return $Success;
        }

        if ($CurlDataJson['code'] !== 1) {
            return $Success;
        }

        $DB->delete('freight', []);
        $DB->insert('freight', $CurlDataJson['data']);
        unset($CurlDataJson);

        return $Success;
    }

    public static function Caching()
    {
        ini_set("max_execution_time", "600");
        set_time_limit(0);
        ini_set("memory_limit", "-1");

        $Success = 0;
        $DB = SQL::DB();

        $GoodsHide = UserConf::GoodsHide(); //取出不显示商品ID
        $SQL = [
            'ORDER' => ['sort' => 'DESC'],
            'method[~]' => '4',
        ];
        if (count($GoodsHide) >= 1) {
            $SQL = array_merge($SQL, [
                'gid[!]' => $GoodsHide
            ]);
        }

        $Res = $DB->select('goods', [
            'gid'
        ], $SQL);
        $Data = [];
        foreach ($Res as $v) {
            $Data[] = $v['gid'];
        }

        //取出可以被对接的商品列表
        $GoodsList = base64_encode(json_encode($Data));

        $List = $DB->select('price', ['mid'], ['state' => 1, 'ORDER' => ['sort' => 'ASC']]);
        foreach ($List as $key => $value) {
            $Su = self::GoodsList([
                'goodsList' => $GoodsList
            ], 2, ($key + 1));
            if ($Su['code'] >= 1) {
                ++$Success;
            }
        }
        dies(1, '共有' . count($List) . '个用户等级，本次成功创建了' . $Success . '个数据，有效期12小时，12小时内其他用户对接你的站点，都会读取到缓存数据，减轻服务器压力！，如果需要实时获取最新数据，可以让对方调整『对接配置内的数据缓存配置』，由于商品监控接口的存在，所以无需担心缓存数据的库存问题，当用户打开商品时，会自动同步库存！');
    }

    /**
     * 取出所有商品[用于给对方对接]，插件内置接口
     * 如果探测存在此插件，则调用此接口
     */
    public static function GoodsList($Data, $type = 1, $level = 1)
    {
        $DB = SQL::DB();
        if ($type === 1) {
            $User = xiaochu::verify($Data);
            $Count = $DB->count('price', [
                    'state' => 1
                ]) - 0;
            if ($User['grade'] >= $Count) {
                $User['grade'] = $Count;
            }

            ini_set("max_execution_time", "600");
            set_time_limit(0);
            ini_set("memory_limit", "-1");
            test(['goodsList|e']);
        } else {
            $User = [
                'grade' => $level
            ];
        }

        $Caching = $Data['Caching'] ?? 1; //默认使用缓存数据

        //缓存名称
        mkdirs(ROOT . 'includes/extend/log/Home');
        $Token = ROOT . 'includes/extend/log/Home/ODS_' . md5($Data['goodsList'] . '_' . $User['grade']) . '.json';
        if ($type === 1 && $Caching == 1 && is_file($Token)) {
            //验证到期时间
            $TimeFile = (float)filemtime($Token);
            if ((time() - $TimeFile) < 43200) {
                return json_decode(file_get_contents($Token), TRUE);
            }
        }

        $goodsList = json_decode(base64_decode($Data['goodsList']), true);

        $GoodsHide = UserConf::GoodsHide(); //取出不显示商品ID
        $SQL = [
            'gid' => $goodsList,
            'method[~]' => '4',
            'state[!]' => 2,
        ];

        if (count($GoodsHide) >= 1) {
            $SQL = array_merge($SQL, [
                'gid[!]' => $GoodsHide
            ]);
        }

        $GoodsList = $DB->select('goods', '*', $SQL);
        if (!$GoodsList) {
            return [
                'code' => -1,
                'msg' => '商品列表取出失败！',
            ];
        }
        $List = [];
        foreach ($GoodsList as $Goods) {
            /**
             * 同步卡密库存
             */
            if ((int)$Goods['deliver'] === 3) {
                $Goods['quota'] = $DB->count('token', [
                    "AND" => [
                        "uid" => 1,
                        "gid" => $Goods['gid']
                    ],
                ]);
            }

            if ((int)$Goods['specification'] === 2) {
                $Goods['specification_sku'] = json_decode($Goods['specification_sku'], TRUE);
                $Goods['specification_spu'] = json_decode($Goods['specification_spu'], TRUE);
                $GoodsSpu = [];
                foreach ($Goods['specification_sku'] as $key => $value) {
                    if ($value['money'] != "") {
                        $GoodsPrice = Price::Get($value['money'], $Goods['profits'], $User['grade'], $Goods['gid'], $Goods['selling']);
                        $value['money'] = $GoodsPrice['price'];
                        unset($GoodsPrice);
                    }
                    $GoodsSpu[$key] = $value;
                    unset($value);
                }
                $Goods['specification_sku'] = json_encode($GoodsSpu);
            } else {
                $Goods['specification_sku'] = [];
                $Goods['specification_spu'] = [];
            }

            $GoodsPrice = Price::Get($Goods['money'], $Goods['profits'], $User['grade'], $Goods['gid'], $Goods['selling']);
            $Goods['money'] = round($GoodsPrice['price'], 8);
            $Goods['points'] = $GoodsPrice['points'];

            $Parameter = [];
            if (!empty($Goods['input'])) {
                $Ex = explode('|', $Goods['input']);
                foreach ($Ex as $key => $val) {
                    $Parameter[] = 'INPUT' . ($key + 1);
                }
            }

            $Goods['extend'] = [
                'gid' => $Goods['gid'],
                'parameter' => implode(',', $Parameter)
            ];

            $DataImage = [];
            foreach (json_decode($Goods['image'], true) as $v) {
                if (!strstr($v, 'http')) {
                    $v = href(2) . '/' . $v;
                }
                $DataImage[] = $v;
                unset($v);
            }
            $Goods['image'] = $DataImage;
            unset($Goods['selling'], $DataImage, $Goods['explain'], $Goods['sales'], $Goods['sqid'], $Goods['note'], $Goods['profits'], $Goods['deliver'], $Parameter);
            $List[] = $Goods;
            unset($Goods);
        }

        //写入缓存数据！
        $Array = [
            'code' => 1,
            'msg' => '商品对接参数获取成功！',
            'data' => $List
        ];
        //写入缓存数据
        file_put_contents($Token, json_encode($Array));
        return $Array;
    }

    /**
     * @param $Gid
     * @param $Shequ
     * 获取商品详细信息，通过对接接口！
     */
    public static function GetGoods($Gid, $Shequ)
    {
        $Shequ['url'] = StringCargo::UrlVerify($Shequ['url'], 3);
        $DataPost = [
            'act' => 'DockingGoodsLog',
            'url' => href(),
            'id' => $Shequ['username'],
            'gid' => $Gid,
        ];
        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $Shequ['password'])
        ], $DataPost);

        $CurlData = Api::Curl($Shequ['url'] . '/api.php', $DataPost);
        $CurlDataJson = json_decode($CurlData, true);
        if (empty($CurlData)) {
            return [
                'code' => -1,
                'msg' => '对接网站：' . $Shequ['url'] . ' 打开失败，请检查域名是否有勿，或手动Ping测试连通率',
            ];
        }
        if ($CurlDataJson['code'] !== 1) {
            return [
                'code' => -1,
                'msg' => $CurlDataJson['msg']
            ];
        }
        $Goods = $CurlDataJson['data'];
        unset($CurlDataJson, $DataPost, $CurlData);

        $input = [];
        $Parameter = [];
        foreach ($Goods['input'] as $k => $val) {
            $Parameter[] = 'INPUT' . ($k + 1);
            if ($val['type'] == 1) {
                $input[] = $val['name'];
            } else {
                $input[] = $val['name'] . '{' . implode(',', $val['data']) . '}';
            }
        }

        $DataImage = [];
        foreach ($Goods['image'] as $v) {
            if (!strstr($v, 'http')) {
                $v = $Shequ['url'] . $v;
            }
            $DataImage[] = $v;
            unset($v);
        }
        $Goods['image'] = $DataImage;
        unset($DataImage);

        $SQL = [
            'gid' => $Goods['gid'],
            'cid' => $Goods['cid'],
            'sort' => $Goods['gid'],
            'name' => $Goods['name'],
            'image' => json_encode($Goods['image']),
            'money' => $Goods['money'], //成本
            'min' => $Goods['min'],
            'max' => $Goods['max'],
            'quota' => $Goods['quota'],
            'input' => implode('|', $input),
            'quantity' => $Goods['quantity'],
            'docs' => $Goods['docs'],
            'alert' => $Goods['alert'],
            'units' => $Goods['units'],
            'deliver' => -1,
            'sqid' => $Shequ['id'],
            'specification' => $Goods['specification'],
            'specification_type' => $Goods['specification_type'],
            'specification_spu' => json_encode($Goods['specification_spu'], JSON_UNESCAPED_UNICODE),
            'specification_sku' => json_encode($Goods['specification_sku'], JSON_UNESCAPED_UNICODE),
            'extend' => json_encode([
                'gid' => $Goods['gid'],
                'parameter' => implode(',', $Parameter),
            ]),
            'label' => $Goods['label'],
            'date' => $Goods['date'],
        ];
        unset($Goods, $Parameter, $input);
        return [
            'code' => 1,
            'data' => $SQL
        ];
    }

    /**
     * @param $Data
     * 测试是否可以对接
     */
    public static function Test($Data)
    {
        $Token = md5($Data['token'] . $Data['uid'] . $Data['domain']);
        if ($_SESSION[$Token] && $Data['Caching'] == 1) {
            return $_SESSION[$Token];
        }
        $DataPost = [
            'url' => href(),
            'id' => $Data['uid'],
            'act' => 'DockingGoodsList'
        ];
        $DataPost = array_merge([
            'sign' => self::Sign($DataPost, $Data['token'])
        ], $DataPost);
        $Data['domain'] = StringCargo::UrlVerify($Data['domain']);

        $GetDataTest = Api::Curl($Data['domain'] . 'api.php?act=WebsiteData');
        $GetDataTest = json_decode($GetDataTest, true);
        if (!$GetDataTest || $GetDataTest['code'] !== 1 || empty($GetDataTest['data']['sitename'])) {
            return [
                'code' => -1,
                'msg' => '对方站点数据获取失败，请确认当前需要对接的站点是和您一样的系统[同系统]',
            ];
        }

        $GetData = Api::Curl($Data['domain'] . 'api.php', $DataPost);
        if (!$GetData) {
            return [
                'code' => -1,
                'msg' => '对接数据获取失败，请检查对接站是否正常打开！'
            ];
        }
        $GetData = json_decode($GetData, true);
        if ($GetData['code'] !== 1) {
            return [
                'code' => -1,
                'msg' => $GetData['msg']
            ];
        }
        //获取分类
        $ClassList = Api::Curl($Data['domain'] . 'main.php?act=class&num=99999');
        if (!$ClassList) {
            return [
                'code' => -1,
                'msg' => '分类列表获取失败，请前往对方站点检查是否存在分类！'
            ];
        }
        $ClassList = json_decode($ClassList, true);

        $_SESSION[$Token] = [
            'code' => 1,
            'msg' => '测试成功，对接站点分类总数：' . count($ClassList['data']) . ' ,商品总数：' . count($GetData['data']),
            'GoodsList' => $GetData['data'],
            'ClassList' => $ClassList['data']
        ];
        return $_SESSION[$Token];
    }

    public static function Sign($param, $key)
    {
        $signPars = '';
        ksort($param);
        foreach ($param as $k => $v) {
            $k = trim($k);
            $v = trim($v);
            if ($k !== 'sign' && $v !== '') {
                $signPars .= $k . '=' . $v . '&';
            }
        }
        $signPars = trim($signPars, '&');
        $signPars .= $key;
        return md5($signPars);
    }
}