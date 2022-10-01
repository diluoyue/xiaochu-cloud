<?php

/**
 * Author：晴玖天
 * Creation：2020/2/28 12:07
 * Filename：initialize.php
 * 初始化程序数据
 */
class Initialize
{
    public static function execute()
    {
        $temp_da = '<?php
/**
 * Author：晴玖天
 * Creation：2020-04-22 12:12:11
 * Filename：autoload_static.php
 * 此文件为自动生成,程序静态类装载
 */

//$vendorDir = dirname(dirname(__FILE__));
//$baseDir = dirname($vendorDir);

return array(
[array]);';

        $vendorDir = dirname(dirname(__FILE__));
        $baseDir = dirname($vendorDir);

        $array_do = '';
        $flie = __DIR__ . '/../../lib';
        $do_file_arr = json_decode(self::list_file($flie), TRUE);
        if (count($do_file_arr, 1) == 0) show_msg('警告', '核心扩展目录文件获取失败<br>文件目录：includes/lib/<br>请确认目录是否存在?', 4);
        $to_array = [];
        foreach ($do_file_arr as $v) {
            if (strpos($v, 'framework') !== false || strpos($v, 'index.php') !== false || strpos($v, 'autoload.php') !== false || !strstr($v, '.php') || strstr($v, '/soft/')) continue;
            $k = str_replace('..', '', $v);
            $k = substr($k, 1, -4);
            $v = substr($v, 4, -1) . 'p';
            $kr = explode('/', $k);
            $kr = implode('\\' . '\\', $kr);
            $to_array += [$kr => $v];
        }
        foreach ($to_array as $key => $value) {
            $array_do .= "      '" . $key . "' => '" . $value . "',\n";
        }
        $data = str_replace("[array]", $array_do, $temp_da);

        $numbytes = file_put_contents($baseDir . '/lib/framework/autoload_static.php', $data);
        if ($numbytes) {
            return true;
        }

        show_msg('警告', '核心文件写入失败或文件夹没有写入权限<br>文件目录：includes/lib/framework');
    }

    /**
     * @param $file
     * @param int $type
     * @return false|string
     * 输出指定目录下的全部文件
     */
    public static function list_file($file = '', $type = 1)
    {
        $temp = scandir($file);
        $array = [];
        foreach ($temp as $v) {
            $a = $file . '/' . $v;
            if (is_dir($a)) {
                if ($v == '.' || $v == '..') continue;
                $array[] = [json_decode(self::list_file($a, 2), TRUE)];
            } else {
                $array[] = [$a];
            }
        }
        if ($type == 1) {
            return json_encode(self::array_valye_all($array));
        }

        return json_encode($array);
    }

    public static function array_valye_all($array)
    {
        $data_json = [];
        foreach ($array as $value) {
            $data = json_encode($value);
            $data = str_replace(['[', ']'], '', $data);
            $data = json_decode('[' . $data . ']', TRUE);
            $Daa = [];
            if (empty($data) || count($data) === 0) {
                continue;
            }
            foreach ($data as $vs) {
                $vs = explode('/../..', $vs)[1];
                $Daa[] = $vs;
            }
            $data_json = array_merge($Daa, $data_json);
        }
        return $data_json;
    }
}