<?php
// +----------------------------------------------------------------------
// | Project: fun.global.php
// +----------------------------------------------------------------------
// | Creation: 2021/11/29 10:13
// +----------------------------------------------------------------------
// | Filename: Guard.php
// +----------------------------------------------------------------------
// | Explain: 关键词保护模块
// +----------------------------------------------------------------------

namespace lib\Guard;


use CookieCache;
use Curl\Curl;

class Guard
{
    /**
     * @var string
     * 将站点内的违规关键词替换为什么？，默认 *
     */
    private static $str = '*';

    /**
     * @var array|bool
     * 需过滤的关键词
     */
    private static $arr = false;

    /**
     * @return false|int
     * 从服务端同步规避关键词
     */
    public static function DataSync()
    {
        $Data = Curl::Get('/api/shield/index');
        $Data = json_decode(xiaochu_de($Data), true);
        /**
         * 将数据同步到本地
         */
        if ($Data['code'] >= 1) {
            return file_put_contents(ROOT . 'includes/lib/Guard/guard.log', json_encode($Data['data'], JSON_UNESCAPED_UNICODE));
        }
        return false;
    }

    /**
     * @param array|string $data
     * 关键词筛选，替换，仅替换字符串！
     */
    public static function Filtrate($data = [])
    {
        if (!is_string($data) && !is_array($data)) {
            return $data;
        }

        if (is_array($data)) {
            $TokenName = sha1(json_encode($data));
        } else {
            $TokenName = sha1($data);
        }

        if (!empty(CookieCache::$Cache['Guard'][$TokenName])) {
            return CookieCache::$Cache['Guard'][$TokenName];
        }

        self::initialize();
        if (count(self::$arr) === 0) {
            return $data;
        }
        if (is_array($data)) {
            $arr = [];
            foreach ($data as $key => $val) {
                $arr[$key] = self::Filtrate($val);
            }
            CookieCache::$Cache['Guard'][$TokenName] = $arr;
            return CookieCache::$Cache['Guard'][$TokenName];
        }

        $str = str_replace(self::$arr, self::$str, $data);
        CookieCache::$Cache['Guard'][$TokenName] = $str;
        return CookieCache::$Cache['Guard'][$TokenName];
    }

    /**
     * @param $str1
     * @param $str2
     */
    public static function InStr($str1, $str2)
    {
        if (is_string($str1)) {
            return (string)$str2;
        }
        return $str1;
    }

    /**
     * @return bool
     * 初始化参数
     */
    public static function initialize()
    {
        global $conf;
        $conf['GuardStr'] = $conf['GuardStr'] ?? '';
        if ($conf['GuardStr'] !== '' && $conf['GuardStr'] != '*') {
            self::$str = $conf['GuardStr'];
        }

        if (self::$arr !== false) {
            return true;
        }
        $data = file_get_contents(ROOT . 'includes/lib/Guard/guard.log');
        if (empty($data)) {
            if (!self::DataSync()) {
                self::$arr = [];
                return false;
            }
            $data = file_get_contents(ROOT . 'includes/lib/Guard/guard.log');
            if (empty($data)) {
                self::$arr = [];
                return false;
            }
        }
        self::$arr = json_decode($data, true);
        return true;
    }
}
