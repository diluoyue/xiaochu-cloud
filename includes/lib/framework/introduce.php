<?php
/**
 * Author：晴玖天
 * Creation：2020/2/28 12:09
 * Filename：introduce.php
 * 引入全局方法文件
 */

class Autoloader
{
    public static function getLoader()
    {
        $classMap = require __DIR__ . '/autoload_static.php';
        foreach ($classMap as $fileIdentifier => $file) {
            Requirea($fileIdentifier, $file);
        }
    }

    public static function Reload()
    {
        require_once __DIR__ . '/initialize.php';
        Initialize::execute();
    }
}

function Requirea($fileIdentifier, $file)
{
    if (empty($GLOBALS['autoload_files'][$fileIdentifier])) {
        $flo = substr(__DIR__, 0, -10);
        require_once $flo . $file;
        $GLOBALS['autoload_files'][$fileIdentifier] = true;
    }
}