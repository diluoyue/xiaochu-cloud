<?php
/**
 * Author：晴玖天
 * Creation：2021/4/20 9:11
 * Filename：StringCargo.php
 *
 */

namespace lib\supply;


use CookieCache;
use Curl\Curl;

class StringCargo
{
    /**
     * @var string[]  '需要排除的系统文件'
     */
    public static $exclude = [
        'Api.php', 'faka.php', 'GoodsMonitoring.php',
        'Price.php', 'Order.php', 'ProductsExchange.php',
        'ProductsExchange.TP', 'StringCargo.php'
    ];

    /**
     * @var array 数据适配【旧=>新】
     */
    private static $DataAdapter = [
        '1' => 'jiuwu', '2' => 'yile', '3' => 'xiaochu', '5' => 'skyun', '6' => 'official', '7' => 'Rainbow',
        '8' => 'kakayun', '9' => 'kasw', '10' => 'YunBao', '11' => 'lwcms',
    ];

    /**
     * @param $ClassName
     * @return mixed|string
     * 数据转换
     */
    public static function DataConversion($ClassName)
    {
        if ($ClassName && array_key_exists((string)$ClassName, self::$DataAdapter)) {
            $ClassName = self::$DataAdapter[(string)$ClassName];
        }
        return $ClassName;
    }

    /**
     * @param $Cache
     * 推广货源列表
     */
    public static function DockingAdvertising($Cache = true)
    {
        if ($Cache) {
            $Res = CookieCache::Return();
            if ($Res) {
                return json_decode($Res, true);
            }
        }
        $ListData = Curl::Get('/api/Recommend/index', [
            'act' => 'List'
        ]);
        $ListData = json_decode(xiaochu_de($ListData), true);
        if (empty($ListData) || $ListData['code'] < 0) {
            $ListData = [];
        } else {
            $ListData = $ListData['data'];
        }
        $Data = [];
        foreach ($ListData as $value) {
            $Data[$value['class_name']][] = $value;
        }
        $DockingList = [];
        $List = StringCargo::Docking();
        foreach ($List as $key => $value) {
            $DockingList[$key] = false;
            if (isset($Data[$key])) {
                $DockingList[$key] = $Data[$key];
            }
        }
        unset($List);
        CookieCache::add($DockingList, 300);//五分钟更新一次
        return $DockingList;
    }

    /***
     * @param $ClassName //货源标识
     * @param $type //是否需要更新列表数据
     * @return array|false|mixed
     * 获取配置信息
     */
    public static function Docking($ClassName = false, $type = false)
    {
        $ClassName = self::DataConversion($ClassName);
        $File = for_dir(SYSTEM_ROOT . 'lib/supply', self::$exclude);
        $CacheName = md5(json_encode($File));
        mkdirs(SYSTEM_ROOT . 'extend/log/Supply');
        $ConfFile = SYSTEM_ROOT . 'extend/log/Supply/Docking_' . $CacheName . '.json';
        $Data = [];
        if (!$type && file_exists($ConfFile)) {
            $Data = json_decode(file_get_contents($ConfFile), true);
        } else {
            foreach ($File as $value) {
                $Exp = explode('.', $value);
                $Self = '\\lib\\supply\\' . $Exp[0];
                if (!class_exists($Self)) {
                    continue;
                }
                if (!property_exists($Self, 'Config')) {
                    continue;
                }
                if (!method_exists($Self, 'AdminOrigin')) {
                    continue;
                }
                $Data[$Exp[0]] = array_merge([
                    'ClassName' => $Exp[0]
                ], $Self::$Config);
            }
            file_put_contents($ConfFile, json_encode($Data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
        if ($ClassName && array_key_exists($ClassName, $Data)) {
            return $Data[$ClassName];
        }
        if ($ClassName) {
            return false;
        }
        return $Data;
    }

    /**
     * @param int $Type
     * Url验证,修复
     * type=1,补齐http:// or / = 2 去除
     */
    public static function UrlVerify($Url, $Type = 1)
    {
        if (empty($Url)) {
            return false;
        }
        if ($Type === 1) {
            #http://baidu.com/
            if (strpos($Url, 'http://') !== false) {
                $Url = 'http://' . self::UrlVerify($Url, 2) . '/';
            } else if (strpos($Url, 'https://') !== false) {
                $Url = 'https://' . self::UrlVerify($Url, 2) . '/';
            } else {
                $Url = 'http://' . self::UrlVerify($Url, 2) . '/';
            }
        } else if ($Type === 2) {
            #baidu.com
            $Url = str_replace(['https://', 'http://'], '', $Url);
            if (substr($Url, -1) === '/') {
                $Url = substr($Url, 0, -1);
            }
        } else {
            #http://baidu.com
            $Url = substr_replace(self::UrlVerify($Url), '', -1);
        }
        return $Url;
    }

    /**
     * @param $Data
     * @return mixed
     * 对接货源参数请求分发
     */
    public static function Distribute($Data)
    {
        $Source = self::Docking($Data['Source']); //数据源
        if (!$Source) {
            return false;
        }
        $path = SYSTEM_ROOT . 'lib/supply/' . $Source['ClassName'] . '.php';
        if (is_file($path)) {
            include_once $path;
        } else {
            return false;
        }
        $new = '\\lib\\supply\\' . $Source['ClassName'];
        if (!class_exists($new)) {
            return false;
        }
        if (!method_exists($new, 'AdminOrigin')) {
            return false;
        }
        if (!method_exists($new, $Data['controller'])) {
            return false;
        }
        return $new::AdminOrigin($Data, $Source);
    }
}
