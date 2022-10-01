<?php
/**
 * Author：晴玖天
 * Creation：2020/5/21 14:58
 * Filename：AppOperation.php
 * 应用商店操作类
 */

namespace lib\AppStore;


use CookieCache;
use Curl\Curl;
use Exception;
use extend\DownLoad;
use file_operations;
use lib\Hook\Hook;
use Medoo\DB\SQL;

class AppList
{
    /**
     * @param $Input //字段名称
     * 下单信息接口匹配！
     * 1、验证是否有验证规则
     * 2、验证是否包含
     * 3、验证是否有同名字段
     * 4、如果有，输出对应内容！
     */
    public static function MatchingInput($Input)
    {
        $Rule = Rule;
        if (count($Rule) === 0) {
            return false;
        }
        foreach ($Rule as $key => $value) {
            if (strpos($key, $Input) === false) continue;
            if (in_array($Input, explode('|', $key))) {
                if (($value['type'] == -1 || $value['type'] == 2) && strpos($value['url'], '[url]') !== false) {
                    $value['url'] = str_replace('[url]', href(2) . ROOT_DIR_S, $value['url']);
                }
                return $value;
            }
        }
        return false;
    }

    /**
     * @param $Arr
     * 更新悬停列表数据！
     */
    public static function HoverSet($Arr)
    {
        $myfile = fopen(ROOT . 'includes/lib/AppStore/Hover.json', 'w') or dies(-1, '悬停配置文件写入失败！');
        $r = fwrite($myfile, json_encode($Arr, JSON_UNESCAPED_UNICODE));
        fclose($myfile);
        if ($r) {
            return true;
        }
        return false;
    }

    /**
     * @param array $Rule
     * 存储规则！
     */
    public static function RuleSet($Rule = [])
    {
        $RuleFs = fopen(ROOT . 'includes/lib/rule/rule.php', 'w') or dies(-1, '无法读取规则配置文件，请初始化规则！');
        $Test = '';
        foreach ($Rule as $key => $value) {

            switch ($value['type']) {
                case -1:
                    $Test .= "
    '" . $key . "' => [
        'name' => '" . $value['name'] . "',
        'type' => -1,
        'url' => '" . $value['url'] . "',
        'way' => " . $value['way'] . ",
        'placeholder' => '" . $value['placeholder'] . "',
    ],";
                    break;
                case 1:
                    $Test .= "
    '" . $key . "' => [
        'name' => '" . $value['name'] . "',
        'type' => 1,
        'way' => 1,
        'placeholder' => '" . $value['placeholder'] . "',
    ],";
                    break;
                case 2:
                    $Test .= "
    '" . $key . "' => [
        'name' => '" . $value['name'] . "',
        'type' => 2,
        'url' => '" . $value['url'] . "',
        'way' => 1,
        'placeholder' => '" . $value['placeholder'] . "',
    ],";
                    break;
                default:
                    dies(-1, '扩展参数未配置！');
            }
        }
        $Php = '<?php
const Rule = [' . $Test . '
];
';
        if (fwrite($RuleFs, $Php)) {
            fclose($RuleFs);
            return [
                'code' => 1,
                'msg' => '配置文件写入成功',
            ];
        } else return [
            'code' => -1,
            'msg' => '配置文件写入失败',
        ];
    }

    /**
     * 获取应用总数
     */
    public static function ApplyCount($Data)
    {
        if ($Data['cache'] == 2) {
            self::AppDataDelete();
        }
        $Data = self::AppListData($Data);
        dier([
            'code' => 1,
            'msg' => '数据获取成功！',
            'count' => count($Data['List'])
        ]);
    }

    /**
     * @param int $Type
     * @return bool
     * 缓存清除
     * 1、清空本地数据缓存，2、更新服务端应用缓存，3、更新服务端支付缓存，4、清空全部缓存
     */
    public static function AppDataDelete(int $Type = 1)
    {
        switch ($Type) {
            case 1:
                CookieCache::del('AppDataMerge');
                break;
            case 2:
                self::AppListSync(1);
                CookieCache::del('AppDataMerge');
                break;
            case 3:
                self::AppPaySync(1);
                CookieCache::del('AppDataMerge');
                break;
            case 4:
                self::AppListSync(1);
                self::AppPaySync(1);
                CookieCache::del('AppDataMerge');
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * 应用商店远程应用列表同步，并且取出对应数据！
     */
    public static function AppListSync($Type = 2)
    {
        if ($Type == 1) {
            $List = Curl::Get('/api/AppStore/api', [
                'act' => 'AppList',
            ]);
            $Data = json_decode(xiaochu_de($List), true);
            if (empty($Data) || $Data['code'] != 1) {
                dies(-1, '应用商店数据同步失败，请切换服务端对接节点后重新尝试！');
            }
            file_put_contents(SYSTEM_ROOT . 'lib/AppStore/AppList.log', xiaochu_en(json_encode($Data)));
            return $Data;
        }

        if (!is_file(ROOT . 'includes/lib/AppStore/AppList.log')) {
            $Data = self::AppListSync(1);
        } else {
            $Data = file_get_contents(ROOT . 'includes/lib/AppStore/AppList.log');
            $Data = json_decode(xiaochu_de($Data), true);
        }
        return $Data;
    }

    /**
     * 应用商店支付数据同步，并取出对应数据！
     */
    public static function AppPaySync($Type = 2)
    {
        if ($Type == 1) {
            $List = Curl::Get('/api/AppStore/api', [
                'act' => 'AppPayList',
            ]);
            $Data = json_decode(xiaochu_de($List), true);
            if (empty($Data)) {
                dies(-1, '应用商店数据同步失败，请切换服务端对接节点后重新尝试！');
            }
            if ($Data['code'] != 1) {
                dies(-1, $Data['msg']);
            }
            file_put_contents(ROOT . 'includes/lib/AppStore/AppPay.log', xiaochu_en(json_encode($Data['data'])));
            return $Data['data'];
        } else {
            if (!is_file(ROOT . 'includes/lib/AppStore/AppPay.log')) {
                $Data = self::AppPaySync(1);
            } else {
                $Data = file_get_contents(ROOT . 'includes/lib/AppStore/AppPay.log');
                $Data = json_decode(xiaochu_de($Data), true);
            }
        }
        return $Data;
    }

    /**
     * @param $Data
     * 根据参数取出对应数据
     */
    public static function AppListData($Data)
    {
        test(['type|i', 'SortType|i', 'Search|i', 'cache|i'], '请将参数提交完整！', $Data);
        /**
         * 此处未来可能会存放一些钩子
         */
        return self::AppDataMerge($Data);
    }

    /**
     * @param $Data
     * 数据合并
     */
    public static function AppDataMerge($Data)
    {
        CookieCache::$prefix = 'AppDataMerge';
        CookieCache::$query = md5(json_encode([
                'type' => $Data['type'],
                'SortType' => $Data['SortType'],
                'Search' => $Data['Search'],
            ]) . '_AppDataMerge');

        $CacheList = json_decode(CookieCache::Return(), true);
        if ($Data['cache'] == 1 && !empty($CacheList)) {
            return $CacheList;
        }
        global $date;
        $ListData = self::AppListSync(); //应用商店应用大全
        $PayData = self::AppPaySync(); //到期时间
        $ListLocal = self::AppListLocal(); //本地应用
        $ListMerge = []; //应用商店+本地应用+购买订单数据合并请求！
        /**
         * DeployState：1、已部署，2、未部署到本地，3独立安装文件或已下架文件【已部署到本地可直接使用】
         * PayType：1、已购买，2、已购买已到期，3、未购买，4、无需购买，本地应用，或已移除的应用【不存在于应用列表内!】
         * UpdateState：1、无需更新，2、需要更新
         */
        $TagsData = [];
        $Tags = [];
        foreach ($ListData['tags'] as $value) {
            $Tags[] = [
                'id' => $value['id'],
                'name' => $value['name'],
                'content' => $value['content']
            ];
            $TagsData[$value['id']] = $value;
        }

        foreach ($ListData['data'] as $key => $value) {

            if (!isset($value['url'])) {
                $value['url'] = 'http://bbs.79tian.com';
            }
            $Arr = [
                'id' => $value['id'],
                'type' => $value['type'],
                'price' => $value['price'],
                'name' => $value['name'],
                'content' => $value['content'],
                'source' => $value['source'],
                'image' => $value['image'],
                'pictures' => $value['pictures'],
                'grade' => $value['grade'],
                'versions' => $value['versions'],
                'identification' => $value['identification'],
                'versions_astrict' => self::VersionsAstrict($value['versions_arr']),
                'discounts' => $value['discounts'],
                'url' => $value['url'],
            ];

            /**
             * 条件：
             * 1、已安装 √
             * 2、已安装列表存在对应的identification
             * 3、当前唯一识别码的ID和已安装列表的ID不一致(应用造假)
             */
            $UnList = array_column($ListLocal, 'id', 'identification');
            if (isset($UnList[$value['identification']]['id']) && $UnList[$value['identification']]['id'] !== $value['id']) {
                //删除对应的应用数据
                unset($ListLocal[$UnList[$value['identification']]]);
            }
            unset($UnList);

            $Arr['UpdateState'] = 1;
            if (deep_in_array($key, $ListLocal)) {
                //本地已安装
                $Arr['DeployState'] = 1;
                if ($ListLocal[$key]['versions'] < $value['versions']) {
                    $Arr['UpdateState'] = 2;
                    $Arr['update_instructions'] = (!$value['update_instructions'] ? '无说明~' : $value['update_instructions']);
                }
                $Arr['state'] = $ListLocal[$key]['state'];
                if (empty($Arr['state'])) {
                    $Arr['state'] = 2;
                }
            } else {
                $Arr['DeployState'] = 2;
                $Arr['state'] = 2;
            }
            if (deep_in_array($key, $PayData, 'aid')) {
                $Arr['PayType'] = 1;
                $Arr['endtime'] = $PayData[$key]['endtime'];
                if ($PayData[$key]['endtime'] <= $date) {
                    $Arr['PayType'] = 2;
                }
            } else {
                $Arr['PayType'] = 3;
            }
            $ListMerge[] = $Arr;
            unset($ListLocal[$key]);
        }
        $DataLocal = [];
        foreach ($ListLocal as $value) {
            if (!isset($value['url'])) {
                $value['url'] = 'http://bbs.79tian.com';
            }
            if (!isset($value['source']) || $value['source'] === '') {
                $value['source'] = '未知';
            }
            if (!isset($value['versions_arr'])) {
                $value['versions_astrict'] = '自行测试';
            } else {
                $value['versions_astrict'] = self::VersionsAstrict($value['versions_arr']);
            }

            $value['DeployState'] = 3;
            $value['PayType'] = 4;
            $value['UpdateState'] = 1;

            $value['grade'] = -1;
            $value['price'] = 0;

            $DataLocal[] = $value;
        }
        if (count($DataLocal) >= 1) {
            $ListMerge = array_merge($DataLocal, $ListMerge);
        }

        if (count($ListMerge) === 0) {
            return [
                'List' => [],
                'Tags' => $Tags
            ];
        }

        $List = [];
        foreach ($ListMerge as $value) {
            krsort($value);

            if (!empty($Data['Search']) && !strstr($value['name'], $Data['Search'])) {
                //搜索筛选
                continue;
            }

            if (@!isset($value['type'])) {
                $value['type'] = 1;
            } else {
                $value['type'] -= 0;
            }

            //菜单筛选
            switch ($Data['type']) {
                case 1: //插件
                    if ($value['type'] !== 1) {
                        continue 2;
                    }
                    break;
                case 2: //模板
                    if ($value['type'] !== 2) {
                        continue 2;
                    }
                    break;
                case 3: //已安装
                    if ($value['DeployState'] === 2) {
                        continue 2;
                    }
                    break;
            }

            //标签筛选SortType

            if ($Data['SortType'] != 0) {
                $Tags = $TagsData[$Data['SortType']]['apply'] ?? [];
                if (!in_array($value['id'], $Tags)) {
                    continue;
                }
            }

            $List[] = $value;
        }

        // 释放内存
        unset($TagsData, $ListData, $PayData, $ListLocal, $ListMerge, $DataLocal);

        CookieCache::add([
            'List' => $List,
            'Tags' => $Tags
        ], 86400);

        return [
            'List' => $List,
            'Tags' => $Tags
        ];
    }

    /**
     * 获取本地应用列表
     * 列表附带应用数据！
     */
    public static function AppListLocal()
    {
        global $accredit;
        $ListFlies = for_dir(SYSTEM_ROOT . 'lib/soft/conf');
        if (count($ListFlies) === 0) {
            return [];
        }
        $Data = [];
        foreach ($ListFlies as $v) {
            $flies = SYSTEM_ROOT . 'lib/soft/conf/' . $v . '/' . $accredit['token'] . '_conf.json';
            if (!file_exists($flies)) {
                continue;
            }
            $Array = json_decode(file_get_contents($flies), true);
            $Data[$Array['id']] = $Array;
        }
        return $Data;
    }

    /**
     * @param $versions
     * 返回对应的版本说明
     */
    public static function VersionsAstrict($versions)
    {
        if ($versions === 'all') {
            return '全版本兼容';
        }
        if (strstr($versions, ',')) {
            return str_replace(',', '~', $versions) . '可用';
        }
        return '大于' . $versions . '版本';
    }

    /**
     * @param $Data
     * @param int $type
     * 获取应用列表
     * 采用本地文件缓存！
     */
    public static function ApplyList($Data)
    {
        test(['page|i', 'limit|e'], '请将参数提交完整！');
        $AppData = self::AppListData($Data);
        $LIMIT = $Data['limit'];
        $Page = ($Data['page'] - 1) * $LIMIT;

        /**
         * Hover
         */
        $DataList = [];
        $DataApp = array_slice($AppData['List'], $Page, $LIMIT);
        if (count($DataApp) >= 1) {
            $HoverList = self::HoverList(2);
        } else {
            $HoverList = [];
        }
        foreach ($DataApp as $value) {
            if (count($HoverList) !== 0 && in_array($value['identification'], $HoverList)) {
                $value['Hover'] = 1;
            } else {
                $value['Hover'] = 2;
            }
            $DataList[] = $value;
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功！',
            'data' => $DataList,
            'Tags' => $AppData['Tags'],
        ]);

    }

    /**
     * @param int $type 1读取详细，2读取大概
     * @return array|bool
     * 读取悬停应用列表！
     * 未安装应用会过滤
     */
    public static function HoverList(int $type = 1)
    {
        $flie_name = ROOT . 'includes/lib/AppStore/Hover.json'; //配置文件名
        $Data = json_decode(file_get_contents($flie_name), TRUE);
        if (count($Data) == 0) return false;
        $DataArr = [];
        foreach ($Data as $value) {
            $dir = ROOT . 'includes/lib/soft/conf/' . $value;
            if (!is_dir($dir)) continue;
            if ($type == 1) {
                $App = self::AppConf($value);
                $DataArr[] = [
                    'id' => $value,
                    'name' => $App['name'],
                    'versions' => $App['versions'],
                    'state' => $App['state'],
                    'image' => ImageUrl($App['image'])
                ];
            } else $DataArr[] = $value;
        }
        if (count($DataArr) == 0) {
            return false;
        }

        return $DataArr;
    }

    /**
     * @param $identification
     * 查询配置文件
     */
    public static function AppConf($identification)
    {
        global $accredit;
        $flie_name = ROOT . 'includes/lib/soft/conf/' . $identification . '/' . $accredit['token'] . '_conf.json'; //配置文件名
        return json_decode(file_get_contents($flie_name), TRUE);
    }

    /**
     * @param $id
     * @return bool|mixed
     * 查询应用详细信息，无下载地址
     */
    public static function AppDataS($id)
    {
        $AppData = Curl::curl(false, ['act' => 'AppDataId', 'id' => $id], true, '/wxapi/view/ajax/application.ajax', 2);
        return $AppData;
    }

    /**
     * 安装预装应用
     */
    public static function PreloadedApp()
    {
        $PathDir = SYSTEM_ROOT . 'extend/log/' . __FUNCTION__;
        if (!is_dir($PathDir)) {
            return false;
        }
        $AppList = for_dir($PathDir);
        if (count($AppList) === 0) {
            DelDir($PathDir);
            return false;
        }
        $Text = '开始安装预装应用<hr>';
        mkdirs(SYSTEM_ROOT . 'extend/log/Apply');
        mkdirs(SYSTEM_ROOT . 'extend/log/PreloadedAppBackups');
        foreach ($AppList as $value) {
            if (!copy($PathDir . '/' . $value, SYSTEM_ROOT . 'extend/log/Apply/' . $value)) {
                $Text .= '应用包[' . $value . ']安装失败,无法移动安装包文件位置<br>';
                continue;
            }
            //移动到备份目录,后续如果需要再安装,可以用到
            copy($PathDir . '/' . $value, SYSTEM_ROOT . 'extend/log/PreloadedAppBackups/' . $value);
            $state = self::install(explode('.', $value)[0], 3, true);
            if (!$state || $state['code'] < 0) {
                $Text .= '应用包<span style="color:#FFFF00">[' . $value . ']安装失败</span>：' . $state['msg'] . '<hr>';
            } else {
                $Text .= '应用包<span style="color:#3CB371">[' . $value . ']安装成功</span>：' . $state['msg'] . '<hr>';
            }
        }
        $Text .= '预装应用安装执行完毕，请刷新页面继续！';
        DelDir($PathDir);
        show_msg('内置应用安装结果', $Text, 1);
    }

    /**
     * @param $identification //应用唯一参数
     * @param int $Type 1 安装，2升级，3本地应用安装【只需提供安装包路径即可！】
     * 安装程序
     */
    public static function install($identification, $Type = 1, $ret = false)
    {
        global $accredit;
        self::AppDataDelete();
        if ($Type == 1 || $Type == 2) {
            //获取远程数据
            $Data = Curl::Get('/api/AppStore/api', [
                'act' => 'InstAll',
                'identification' => $identification,
            ]);
            $Data = json_decode(xiaochu_de($Data), true);
            if (empty($Data) || $Data['code'] < 0) {
                if ($ret) {
                    return [
                        'code' => -1,
                        'msg' => '应用数据获取失败，' . $Data['msg']
                    ];
                }
                dies(-1, '应用数据获取失败，' . $Data['msg']);
            }
            $download = new DownLoad();
        }

        mkdirs(SYSTEM_ROOT . 'extend/log/Apply'); //安装包存储目录
        mkdirs(SYSTEM_ROOT . 'extend/log/Apply/file'); //文件拆解目录

        if ($Type !== 2) {
            mkdirs(ROOT . 'includes/lib/soft/controller/');
            mkdirs(ROOT . 'includes/lib/soft/conf/');
            mkdirs(ROOT . 'includes/lib/soft/view/');
        }

        /**
         * 需要的数据：
         * id，name,versions,state,identification
         * visit,image,versions_arr,url,
         */
        switch ($Type) {
            case 1: //安装
                $ZipFile = SYSTEM_ROOT . 'extend/log/Apply/' . $Data['data']['identification'] . '.zip';
                try {
                    $download->setUrl($Data['data']['download'])->saveFile($ZipFile);
                } catch (Exception $e) {
                    if ($ret) {
                        return [
                            'code' => -1,
                            'msg' => $e->getMessage()
                        ];
                    }
                    dies(-1, $e->getMessage());
                }
                break;
            case 2: //升级
                $ZipFile = SYSTEM_ROOT . 'extend/log/Apply/' . $Data['data']['identification'] . '_update.zip';
                try {
                    $download->setUrl($Data['data']['download_update'])->saveFile($ZipFile);
                } catch (Exception $e) {
                    if ($ret) {
                        return [
                            'code' => -1,
                            'msg' => $e->getMessage() . '，请重新尝试！'
                        ];
                    }
                    dies(-1, $e->getMessage() . '，请重新尝试！');
                }
                break;
            case 3: //安装本地应用
                $ZipFile = SYSTEM_ROOT . 'extend/log/Apply/' . $identification . '.zip';
                break;
            default:
                if ($ret) {
                    return [
                        'code' => -1,
                        'msg' => '模式不存在！'
                    ];
                }
                dies(-1, '模式不存在！');
                break;
        }

        if ($Type === 3) {
            if (!file_exists($ZipFile)) {
                if ($ret) {
                    return [
                        'code' => -1,
                        'msg' => '安装包文件不存在，请将程序安装包手动上传至：' . SYSTEM_ROOT . 'extend/log/Apply/ 目录，并且将压缩包重命名为：' . $identification . '.zip'
                    ];
                }
                dies(-1, '安装包文件不存在，请将程序安装包手动上传至：' . SYSTEM_ROOT . 'extend/log/Apply/ 目录，并且将压缩包重命名为：' . $identification . '.zip');
            }
            if (!file_operations::zipExtract($ZipFile, SYSTEM_ROOT . 'extend/log/Apply/file')) {
                DelDir(SYSTEM_ROOT . 'extend/log/Apply/file');
                if ($ret) {
                    return [
                        'code' => -1,
                        'msg' => '无法解压文件,ZipArchive类无法使用！'
                    ];
                }
                dies(-1, '无法解压文件,ZipArchive类无法使用！');
            }
            if (!file_exists(SYSTEM_ROOT . 'extend/log/Apply/file/install.json')) {
                DelDir(SYSTEM_ROOT . 'extend/log/Apply/file');
                if ($ret) {
                    return [
                        'code' => -1,
                        'msg' => '安装包解析失败，安装包内的安装引导文件install.json不存在！'
                    ];
                }
                dies(-1, '安装包解析失败，安装包内的安装引导文件install.json不存在！');
            }
            $Data = [];
            $Data['Json'] = ['id', 'name', 'source', 'versions', 'type', 'visit', 'image', 'image', 'url', 'versions_arr', 'path', 'identification', 'addtime', 'Global'];
            $Data['data'] = json_decode(file_get_contents(SYSTEM_ROOT . 'extend/log/Apply/file/install.json'), TRUE);
            unlink(SYSTEM_ROOT . 'extend/log/Apply/file/install.json');
            if (!$Data['data']) {
                DelDir(SYSTEM_ROOT . 'extend/log/Apply/file');
                if ($ret) {
                    return [
                        'code' => -1,
                        'msg' => '安装包配置文件无法读取，请确认应用安装包为有效安装包！'
                    ];
                }
                dies(-1, '安装包配置文件无法读取，请确认应用安装包为有效安装包！');
            }
            foreach ($Data['Json'] as $val) {
                if (empty($Data['data'][$val])) {
                    if ($ret) {
                        return [
                            'code' => -1,
                            'msg' => '应用安装包参数缺失，无法完成安装！，缺失参数：' . $val
                        ];
                    }
                    dies(-1, '应用安装包参数缺失，无法完成安装！，缺失参数：' . $val);
                }
            }
            mkdirs(ROOT . $Data['data']['path']);
            if (!DirCopy(SYSTEM_ROOT . 'extend/log/Apply/file', ROOT . $Data['data']['path'])) {
                if ($ret) {
                    return [
                        'code' => -1,
                        'msg' => '应用安装失败，应用文件移动失败，请确认目录：' . ROOT . $Data['data']['path'] . '存在！'
                    ];
                }
                dies(-1, '应用安装失败，应用文件移动失败，请确认目录：' . ROOT . $Data['data']['path'] . '存在！');
            } else {
                DelDir(SYSTEM_ROOT . 'extend/log/Apply/file');
            }
            $flieState = true;
        } else {
            mkdirs(ROOT . $Data['data']['path']);
            $flieState = file_operations::zipExtract($ZipFile, ROOT . $Data['data']['path']);
        }
        /**
         * 文件解压 or 移动成功后的操作！
         */
        if ($flieState) {
            if (file_exists($ZipFile)) {
                unlink($ZipFile);
                DelDir(SYSTEM_ROOT . 'extend/log/Apply/file');
            }
            /**
             * 应用配置数据
             */
            $DataS = [
                'id' => $Data['data']['id'],
                'name' => $Data['data']['name'],
                'versions' => $Data['data']['versions'],
                'state' => 1,
                'visit' => $Data['data']['visit'],
                'image' => $Data['data']['image'],
                'url' => $Data['data']['url'],
                'identification' => $Data['data']['identification'],
                'versions_arr' => $Data['data']['versions_arr'],
                'source' => $Data['data']['source'],
                'type' => $Data['data']['type'],
                'addtime' => $Data['data']['addtime'],
            ];

            $flie_name = SYSTEM_ROOT . 'lib/soft/conf/' . $Data['data']['identification'] . '/' . $accredit['token'] . '_conf.json'; //配置文件名

            if (file_exists($flie_name)) {
                //升级时，获取配置数据【支付】
                $PayType = json_decode(file_get_contents($flie_name), TRUE);
            } else {
                $PayType = false;
            }

            //全局插件配置【后台开启默认关闭！】
            if ($Data['data']['Global'] == 1) {
                $AroConf = self::ConfGlobal();
                self::ConfGlobal(2, array_merge($AroConf, [$Data['data']['identification']]));
            }

            /**
             * 验证是否为支付接口应用！
             */
            $flie = SYSTEM_ROOT . 'lib/soft/conf/' . $Data['data']['identification'] . '/payconf.json';
            if (file_exists($flie)) {
                $vis = json_decode(file_get_contents($flie), TRUE);
                $arr = [];
                for ($s = 0; $s < 3; $s++) {
                    for ($i = 0, $iMax = count(explode(',', $vis['input'])); $i < $iMax; $i++) {
                        if ($PayType !== false && $PayType !== null) {
                            $arr[$s][] = $PayType['Data'][$s][$i];
                        } else {
                            $arr[$s][] = '';
                        }
                    }
                }
                $DataS = array_merge($DataS, [
                    'Data' => $arr,
                ]);
                self::pay_set($Data['data']);
            }
            /**
             * 验证是否需要将此应用注册到对应钩子
             */
            $flie = SYSTEM_ROOT . 'lib/soft/conf/' . $Data['data']['identification'] . '/Hook.json';
            if (file_exists($flie)) {
                $HookArr = json_decode(file_get_contents($flie), TRUE);
                foreach ($HookArr as $value) {
                    Hook::add($value, $Data['data']['identification']);
                }
            }
            if (file_exists(SYSTEM_ROOT . 'lib/soft/install.json')) {
                unlink(SYSTEM_ROOT . 'lib/soft/install.json');
            }
            $FileType = file_put_contents($flie_name, json_encode($DataS, JSON_UNESCAPED_UNICODE));
            if ($FileType) {
                if ($ret) {
                    return [
                        'code' => 1,
                        'msg' => $Data['data']['name'] . ($Type == 1 || $Type == 3 ? '安装成功！' : '更新成功！')
                    ];
                }
                dies(1, $Data['data']['name'] . ($Type == 1 || $Type == 3 ? '安装成功！' : '更新成功！'));
            } else {
                if ($ret) {
                    return [
                        'code' => -1,
                        'msg' => $Data['data']['name'] . ($Type == 1 || $Type == 3 ? '安装失败！' : '更新失败！') . '，请检查目录：' . SYSTEM_ROOT . 'lib/soft/conf/ 是否有写入权限！'
                    ];
                }
                dies(-1, $Data['data']['name'] . ($Type == 1 || $Type == 3 ? '安装失败！' : '更新失败！') . '，请检查目录：' . SYSTEM_ROOT . 'lib/soft/conf/ 是否有写入权限！');
            }
        } else {
            if (file_exists($ZipFile)) {
                unlink($ZipFile);
            }
            if (file_exists(SYSTEM_ROOT . 'lib/soft/install.json')) {
                unlink(SYSTEM_ROOT . 'lib/soft/install.json');
            }
            if ($ret) {
                return [
                    'code' => -1,
                    'msg' => '无法解压文件,ZipArchive类无法使用！'
                ];
            }
            dies(-1, '无法解压文件,ZipArchive类无法使用！');
        }
    }

    /**
     * @param array $Data 数据
     * @param int $Type 类型 1=读取，2=写入
     * 全局文件调用管理
     */
    public static function ConfGlobal($Type = 1, array $Data = [])
    {
        $file_name = SYSTEM_ROOT . 'lib/AppStore/confs.json';
        $aRRS = json_decode(file_get_contents($file_name), TRUE);
        if ($Type == 1) {
            return $aRRS;
        } else {
            $file = fopen($file_name, 'w') or dies(-1, '程序配置文件写入失败！');
            $r = fwrite($file, json_encode($Data, JSON_UNESCAPED_UNICODE));
            fclose($file);
            if ($r) {
                return true;
            } else return false;
        }
    }

    /**
     * @param $data //应用数据
     * @param int $type 1=新增，2=删除
     * 支付JSON配置文件调整
     * @return bool
     */
    public static function pay_set($data, $type = 1)
    {
        $Fvis = self::PayConf($data['identification']);
        if ($type == 1) {
            $vis = json_decode(file_get_contents(ROOT . 'includes/lib/soft/conf/' . $data['identification'] . '/payconf.json'), TRUE);
            $Vie = json_decode(file_get_contents(ROOT . 'includes/lib/AppStore/pay.json'), TRUE);
            $TypeDis = false;
            if (count($Vie) >= 1) {
                foreach ($Vie as $key => $vi) {
                    if ($vi['identification'] == $data['identification']) {
                        $TypeDis = true;
                        $Fvis[$key] = [
                            'name' => $data['name'],
                            'state' => $vis['state'],
                            'identification' => $data['identification'],
                            'input' => $vis['input'],
                        ];
                    }
                }
            }
            if (!$TypeDis) {
                $Fvis = array_merge($Fvis, [[
                    'name' => $data['name'],
                    'state' => $vis['state'],
                    'identification' => $data['identification'],
                    'input' => $vis['input'],
                ]]);
            }
        } else {
            $Ds = [];
            foreach ($Fvis as $v) {
                if ($v['identification'] != $data['identification']) {
                    $Ds[] = $v;
                }
            }
            $Fvis = $Ds;
        }

        $file = fopen(ROOT . 'includes/lib/AppStore/pay.json', 'w') or dies(-1, '程序配置文件写入失败！');
        $r = fwrite($file, json_encode($Fvis, JSON_UNESCAPED_UNICODE));
        fclose($file);
        if ($r) {
            return true;
        }
        return false;
    }

    /**
     * 读取支付配置列表
     */
    public static function PayConf()
    {
        $flie_name = ROOT . 'includes/lib/AppStore/pay.json'; //配置文件名
        return json_decode(file_get_contents($flie_name), TRUE);
    }

    /**
     * @param $ID
     * 获取指定ID的应用数据,用于安装！
     */
    public static function appdata($ID)
    {
        return Curl::curl(false, ['act' => 'AppData', 'id' => $ID], true, '/wxapi/view/ajax/application.ajax', 2);
    }

    /**
     * @param $identification
     * 卸载指定应用！
     */
    public static function unload($identification)
    {
        $msg = '卸载状态：<br>';
        $Array = ['conf', 'controller', 'view'];
        self::AppDataDelete();
        /**
         * 验证是否为支付接口应用，如果是则删除！
         */
        $file = ROOT . 'includes/lib/soft/conf/' . $identification . '/payconf.json';
        if (file_exists($file)) {
            self::pay_set(['identification' => $identification], 2);
            $msg .= '对应支付配置清除成功<br>';
        }

        /**
         * 删除对应钩子！
         */
        $file = ROOT . 'includes/lib/soft/conf/' . $identification . '/Hook.json';
        if (file_exists($file)) {
            $HookArr = json_decode(file_get_contents($file), TRUE);
            foreach ($HookArr as $value) {
                Hook::delete($value, $identification);
            }
            $msg .= '钩子配置(' . count($HookArr) . ')个,清除成功<br>';
        }

        foreach ($Array as $value) {
            $file_name = ROOT . 'includes/lib/soft/' . $value . '/' . $identification . '/'; //配置文件名
            $a = file_operations::deldir($file_name);
            switch ($value) {
                case 'conf':
                    if ($a) {
                        $msg .= '配置文件删除成功<br>';
                    } else $msg .= '配置文件删除失败<br>';
                    break;
                case 'controller':
                    if ($a) {
                        $msg .= '控制器文件删除成功<br>';
                    } else $msg .= '控制器文件删除失败<br>';
                    break;
                case 'view':
                    if ($a) {
                        $msg .= '视图文件删除成功<br>';
                    } else $msg .= '视图文件删除失败<br>';
                    break;
            }
        }

        $AroConf = self::ConfGlobal();
        foreach ($AroConf as $k => $v) {
            if ($v == $identification) unset($AroConf[$k]);
        }

        self::ConfGlobal(2, $AroConf);

        $file_name = ROOT . 'template/' . $identification . '/';
        file_operations::deldir($file_name);

        dies(1, $msg);
    }

    /**
     * @param $identification //软件标识
     * @param $path //访问目录！
     */
    public static function view($identification, $path)
    {
        global $conf, $cdnserver, $cdnpublic, $dbconfig, $accredit;
        $dir = SYSTEM_ROOT . 'lib/soft/conf/' . $identification;
        $vis = json_decode(file_get_contents($dir . '/' . $accredit['token'] . '_conf.json'), TRUE);
        if (empty($vis['versions']) || empty($vis['state'])) {
            self::error(404, '应用未安装');
        }
        if ($vis['state'] != 1) {
            self::error(401, '应用未启用，请先启用~');
        }
        $Data = self::AppListData([
            'Search' => '',
            'type' => 3,
            'SortType' => 0,
            'cache' => 1
        ]);
        $AppData = false;
        foreach ($Data['List'] as $value) {
            if ($value['identification'] == $identification) {
                $AppData = $value;
            }
        }
        if (!$AppData) {
            self::error(500, '应用不存在,请检查应用标识是否有误！');
        }

        if ($AppData['PayType'] === 2) {
            self::error(500, '应用已到期，请先续期应用！');
        }

        if ($AppData['PayType'] === 3) {
            self::error(500, '应用未购买，请先购买应用！');
        }

        $DB = SQL::DB();
        $file = SYSTEM_ROOT . 'lib/soft/view/' . $identification . '/' . $path . '.TP';
        if (!file_exists($file)) {
            self::error(404, '此应用无控制面板！');
        }
        header('Content-Type: text/html; charset=UTF-8');
        include $file;
        die;
    }

    /**
     * @param int $state 状态码
     * @param string $msg 说明
     * 异常反馈,短文字
     */
    public static function error($state = 404, $msg = '页面不存在')
    {
        header('Content-Type: text/html; charset=UTF-8');
        header('HTTP/1.1 ' . $state . ' ' . $msg);
        $Data = file_get_contents(ROOT . 'includes/lib/AppStore/error.TP');
        $Data = str_replace('[STATE]', $state, $Data);
        die(str_replace('[MSG]', $msg, $Data));
    }

    /**
     * @param $Data //配置数据
     * @param $identification //标识
     * @param $type //类型
     */
    public static function SavePayData($Data, $identification, $type)
    {
        global $accredit;
        $i = 1;
        foreach ($Data as $v) {
            if ($v == '') {
                dies(-1, '请将第' . $i . '个输入框填写完整！');
            }
            ++$i;
        }
        $flie_name = ROOT . 'includes/lib/soft/conf/' . $identification . '/' . $accredit['token'] . '_conf.json'; //配置文件内容读取
        $vis = json_decode(file_get_contents($flie_name), TRUE);
        if (empty($vis['versions']) || empty($vis['state'])) {
            dies(-1, '当前应用尚未安装，配置文件不存在！');
        }
        $vis['Data'][$type] = $Data;
        $myfile = fopen($flie_name, 'w') or dies(-1, '程序配置文件读取失败！');
        fwrite($myfile, json_encode($vis, JSON_UNESCAPED_UNICODE));
        fclose($myfile);
        dies(1, $vis['name'] . '的' . ($type == 0 ? 'QQ支付配置' : ($type == 1 ? '微信支付配置' : '支付宝支付配置')) . '保存成功!');
    }

    /**
     * @param $identification //标识
     * @param int $type //类型
     * 应用状态修改！
     */
    public static function state_set($identification, $type = 1)
    {
        global $accredit;
        $file_name = ROOT . 'includes/lib/soft/conf/' . $identification . '/' . $accredit['token'] . '_conf.json'; //配置文件名
        $vis = json_decode(file_get_contents($file_name), TRUE);
        if (empty($vis['versions']) || empty($vis['state'])) {
            dies(-1, '当前应用尚未安装，配置文件不存在！');
        }
        $file = fopen($file_name, 'w+') or dies(-1, '程序配置文件读取失败！');
        $vis['state'] = ($type == 1 ? 1 : 2);
        fwrite($file, json_encode($vis, JSON_UNESCAPED_UNICODE));
        fclose($file);
        self::AppDataDelete();
        dies(1, $vis['name'] . '已' . ($type == 1 ? '启用' : '停用') . '!');
    }

    /**
     * @param $id 应用ID
     * 延长到期时间
     */
    public static function prolong($id, $count = 1)
    {
        $AppData = Curl::curl(false, ['act' => 'AppProlong', 'id' => $id, 'count' => $count], true, '/wxapi/view/ajax/application.ajax', 2);
        self::AppDataDelete(3);
        dier($AppData);
    }

    /**
     * @param $ID
     * 购买应用！
     */
    public static function pay($ID, $count = 1)
    {
        $AppData = Curl::curl(false, ['act' => 'AppPay', 'id' => $ID, 'count' => $count], true, '/wxapi/view/ajax/application.ajax', 2);
        self::AppDataDelete(3);
        dier($AppData);
    }

    /**
     * 获取用户等级
     */
    public static function users()
    {
        $AppData = Curl::curl(false, ['act' => 'AppUsers'], true, '/wxapi/view/ajax/application.ajax', 2);
        dier($AppData);
    }

    /**
     * @param null $identification 应用标识
     * 帮助文档
     */
    public static function AppHelp($identification = null)
    {
        $file = ROOT . 'includes/lib/soft/conf/' . $identification . '/help.html';
        if (!file_exists($file)) {
            self::error(404, '帮助文档不存在');
        }
        header('Content-Type: text/html; charset=UTF-8');
        include $file;
    }

    /**
     * @param array $Data
     * 全局拦截器
     */
    public static function Globals(array $Data)
    {
        $flie_name = ROOT . 'includes/lib/AppStore/confs.json';
        $DataFlie = file_get_contents($flie_name);
        $aRRS = json_decode($DataFlie, TRUE);
        if (count($aRRS) === 0) {
            return;
        }
        foreach ($aRRS as $v) {
            self::Api($v, $Data, false);
        }
    }

    /**
     * @param $identification
     * @param array $Data
     * @param bool $Types
     * Api钩子,执行应用方法
     */
    public static function Api($identification = null, $Data = [], $Types = true)
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            if (empty($identification)) {
                if ($Types === true) {
                    dies(-1, '应用标识为空,请联系开发者处理！');
                }
                return false;
            }

            if (!file_exists(ROOT . 'includes/lib/soft/controller/' . $identification . '/index.php')) {
                if ($Types === true) {
                    dies(-1, '应用' . $identification . '控制器不存在！');
                }
                return false;
            }

            if (self::AppConf($identification)['state'] !== 1) {
                if ($Types === true) {
                    dies(-1, '应用' . self::AppConf($identification)['name'] . '处于关闭状态！');
                }
                return false;
            }

            $ArrConf = explode(',', self::AppConf($identification)['visit']);
            if (in_array('Admin', $ArrConf)) {
                if (!self::AdminVerify()) return false;
            } else if (in_array('User', $ArrConf) && !self::UserVerify()) {
                return false;
            }

            $nAM = '\\lib\\' . $identification;
            if (!class_exists($nAM) || !method_exists($nAM, 'origin')) {
                if ($Types === true) {
                    dies(-1, '指定对接操作类不存在！，请检查：' . $nAM);
                }
                return false;
            }
            $C = new $nAM();
            $C->origin($Data);
        } catch (Exception $e) {
            if ($Types === true) {
                dies(-1, '应用' . self::AppConf($identification)['name'] . '存在异常，无法正常使用！');
            }
            return false;
        }
    }

    /**
     * 站长后台登陆验证，插件版
     */
    public static function AdminVerify()
    {
        $DB = SQL::DB();
        if (empty($_SESSION['ADMIN_TOKEN'])) {
            return false;
        }

        if (strlen($_SESSION['ADMIN_TOKEN']) !== 32) {
            $_SESSION['ADMIN_TOKEN'] = null;
            return false;
        }

        if (!$DB->get('login', ['token'], ['token' => (string)$_SESSION['ADMIN_TOKEN'], 'state' => 1, 'LIMIT' => 1])) {
            $_SESSION['ADMIN_TOKEN'] = null;
            return false;
        }
        return true;
    }

    /**
     * 用户后台登陆验证，插件版
     */
    public static function UserVerify()
    {
        $DB = SQL::DB();
        if (empty($_COOKIE['THEKEY'])) {
            return false;
        }

        if (strlen($_COOKIE['THEKEY']) === 32) {
            $user_row = $DB->get('user', ['id', 'user_idu', 'state'], ['user_idu' => (string)$_COOKIE['THEKEY'], 'state' => 1, 'LIMIT' => 1]);
        } else {
            $user_row = $DB->get('user', ['id', 'wx_idu', 'state'], ['wx_idu' => (string)$_COOKIE['THEKEY'], 'state' => 1, 'LIMIT' => 1]);
        }
        if (!$user_row) {
            setcookie('THEKEY', null, time() - 3600 * 12 * 15, '/');
            return false;
        }

        return true;
    }
}
