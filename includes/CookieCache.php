<?php
/**
 * Class CookieCache
 * CK缓存模块
 * 由于小主机用户较多，暂不使用Redis
 * Ck存储不了太多数据，实际数据写入在session内！
 */

class CookieCache
{
    /**
     * @var array
     * 全局通用函数缓存，防止多次载入相同数据
     */
    public static $Cache = [];
    /**
     * @var int
     * 缓存时长
     */
    public static $ms = 10;
    /**
     * @var bool|string
     * 缓存命名，可在外部命名
     */
    public static $Key = false;
    /**
     * @var bool|string
     * 缓存前缀，便于区分
     */
    public static $prefix = false;
    /**
     * @var bool|string
     * 请求数据，前缀后面的数据,仅key为空时有效
     */
    public static $query = false;

    /**
     * @var bool|string
     * 可自定义token，如果为false，则自动替换为当前域名！
     */
    public static $token = false;
    /**
     * @var bool
     * 开启或关闭，数据时间显示！方便调试
     */
    private static $debug = false;

    /**
     * @return bool|string
     */
    private static function loading()
    {
        if (empty($_SESSION['CookieCacheList'])) {
            $_SESSION['CookieCacheList'] = [];
        }
        if (self::$Key === false) {
            global $_QET;
            if (!self::$prefix) {
                self::$prefix = (!empty($_QET['act']) ? $_QET['act'] : 'general');
            }
            if (!self::$query) {
                self::$query = md5(json_encode($_QET) . (!self::$token ? href() : self::$token));
            }
            self::$Key = self::$prefix . '_' . self::$query;
            $_SESSION[self::$prefix] = (empty($_SESSION[self::$prefix]) ? '' : $_SESSION[self::$prefix] . '|') . self::$Key;
        }

        return true;
    }

    /**
     * @param false|string $Name
     * 读取验证，可单独命名
     */
    public static function read($Name = false)
    {
        self::loading();
        if ($Name) {
            self::$Key = $Name;
        }

        if (empty($_SESSION['CookieCacheList'])) {
            return false;
        }
        if (isset($_SESSION['CookieCacheList'][self::$Key]) && !empty($_SESSION['CookieCacheList'][self::$Key]) && !empty($_SESSION['CookieCache'][self::$Key]) && (int)$_SESSION['CookieCacheList'][self::$Key] >= time()) {
            die($_SESSION['CookieCache'][self::$Key]);
        }
        return false;
    }

    /**
     * @param false|string $Name
     * 读取验证，非拦截返回
     */
    public static function Return($Name = false)
    {
        self::loading();
        if ($Name) {
            self::$Key = $Name;
        }
        if (empty($_SESSION['CookieCacheList'])) {
            return false;
        }
        if (!empty($_SESSION['CookieCacheList'][self::$Key]) && !empty($_SESSION['CookieCache'][self::$Key]) && (int)$_SESSION['CookieCacheList'][self::$Key] >= time()) {
            return $_SESSION['CookieCache'][self::$Key];
        }
        return false;
    }

    /**
     * @param $Data
     * @param false $ms
     * @param string $path
     * @return bool
     * 添加缓存
     */
    public static function add($Data, $ms = 10)
    {
        self::loading();
        if (self::$debug && !empty($Data['msg'])) {
            global $date;
            $Data['msg'] .= '，数据创建时间：' . $date;
        }
        if (!$ms) {
            $ms = self::$ms;
        }

        $_SESSION['CookieCacheList'] = array_merge($_SESSION['CookieCacheList'], [
            self::$Key => (time() + $ms)
        ]);
        $_SESSION['CookieCache'][self::$Key] = json_encode($Data);

        return true;
    }

    /**
     * @param string $prefix
     * @return bool
     * 根据前缀，删除缓存
     */
    public static function del($prefix = '')
    {
        if ($prefix === '') {
            return false;
        }
        if (empty($_SESSION['CookieCacheList'])) {
            return false;
        }
        $arr = explode('|', $_SESSION[$prefix]);

        foreach ($arr as $v) {
            $_SESSION['CookieCacheList'][$v] = null;
            $_SESSION['CookieCache'][$v] = null;
        }
        $_SESSION[$prefix] = null;

        return true;
    }
}
