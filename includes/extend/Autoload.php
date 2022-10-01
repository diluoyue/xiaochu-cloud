<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城
// +----------------------------------------------------------------------
// | Creation: 2021/12/16 10:02
// +----------------------------------------------------------------------
// | Filename: Autoload.php
// +----------------------------------------------------------------------
// | Explain: 自动载入类 spl_autoload_register
// +----------------------------------------------------------------------
namespace extend;

class Autoload
{
    /**
     * @var array
     * 路径定制
     */
    private static $Custom = [
        'Medoo\DB\SQL' => [
            'lib/medoo/config.php',
            'lib/medoo/Medoo.php'
        ],
        'lib\AppStore\AppList' => 'lib/AppStore/AppOperation.php',
        'extend\ImgThumbnail' => 'extend/GoodsImage.php',
        'Curl\Curl' => 'Curl.php',
        'BT\Config' => 'lib/BT/config.php',
        'BT\Construct' => 'lib/BT/construct.php',
        'BT\monitoring' => 'lib/BT/monitoring.php',
        'FC\Captcha' => 'lib/VerCode/Captcha.php',
        'FC\GIF\GIFEncoder' => 'lib/VerCode/GIFEncoder.php',
        'Admin\Admin' => 'Admin.php',
        'Server\Server' => 'Server.php',
        'QRcode' => 'extend/phpqrcode.php',
    ];

    /**
     * @return void
     * 自动装载，数据转换
     */
    public static function AutoloadRegister()
    {
        $Data = [];
        foreach (self::$Custom as $key => $value) {
            $keys = str_replace('\\', DIRECTORY_SEPARATOR, $key);
            if (is_array($value)) {
                $arr = [];
                foreach ($value as $k => $v) {
                    $arr[$k] = str_replace('/', DIRECTORY_SEPARATOR, $v);
                }
                $Data[$keys] = $arr;
                unset($arr);
            } else {
                $Data[$keys] = str_replace('/', DIRECTORY_SEPARATOR, $value);
            }
        }
        self::$Custom = $Data;
        unset($Data);
        spl_autoload_register([new self(), 'Autoload']);
    }

    /**
     * @param $Name
     * 按需引入
     */
    public static function Autoload($Name)
    {
        $Name = str_replace('\\', DIRECTORY_SEPARATOR, $Name);
        $Ex = explode(DIRECTORY_SEPARATOR, $Name);
        if (array_key_exists($Name, self::$Custom)) {
            $path = self::$Custom[$Name];
            if (is_array($path)) {
                foreach ($path as $value) {
                    if (is_file(SYSTEM_ROOT . $value)) {
                        require_once(SYSTEM_ROOT . $value);
                    }
                }
            } else {
                require_once(SYSTEM_ROOT . $path);
            }
        } else if (is_file(SYSTEM_ROOT . $Name . '.php')) {
            require_once(SYSTEM_ROOT . $Name . '.php');
        } else if (count($Ex) === 2 && $Ex[0] === 'lib' && is_file(SYSTEM_ROOT . 'lib/soft/controller/' . $Ex[1] . '/index.php')) {
            require_once(SYSTEM_ROOT . 'lib/soft/controller/' . $Ex[1] . '/index.php');
        } else {
            return false;
            //dies(-1, '无法寻找到指定操作类文件路径，请联系开发者处理！，类名：' . $Name);
        }
        return true;
    }
}
