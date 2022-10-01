<?php
/**
 * Author：晴玖天
 * Creation：2020/2/29 19:44
 * Filename：config.php
 * 数据库配置文件
 */

namespace Medoo\DB;

use DB;
use Medoo\Medoo;

class SQL
{
    /**
     * @return Medoo PDO操作类
     */

    /**
     * @var bool|array
     * 防止数据库重复打开
     */
    private static $NewData = false;

    private static $NewDatas = false;

    /**
     * @return bool|DB
     * DBS 原生DB类调用
     */
    public static function DBS()
    {
        if (self::$NewDatas) {
            return self::$NewDatas;
        }
        global $dbconfig;
        self::$NewDatas = new DB($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname'], $dbconfig['port']);
        return self::$NewDatas;
    }

    public static function DB()
    {
        if (self::$NewData) {
            return self::$NewData;
        }
        global $dbconfig;
        self::$NewData = new Medoo([
            'database_type' => 'mysql',
            'database_name' => $dbconfig['dbname'],
            'server' => $dbconfig['host'],
            'username' => $dbconfig['user'],
            'password' => $dbconfig['pwd'],
            'charset' => 'utf8mb4',
            'port' => $dbconfig['port'],
            'prefix' => 'sky_'
        ]);
        return self::$NewData;
    }
}

