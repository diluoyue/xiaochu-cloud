<?php

/**
 * Class install
 */
class install
{
    /**
     * @param $dbconfig
     * @return array
     * 文件修改操作
     */
    public static function ModifyFileContents($dbconfig)
    {
        $FILE = '../includes/deploy.php';
        $data = "<?php
/*数据库配置*/
$" . "dbconfig" . " = [
    'host' => '" . $dbconfig['host'] . "', //数据库服务器
    'port' => " . $dbconfig['port'] . ", //数据库端口
    'user' => '" . $dbconfig['user'] . "', //数据库用户名
    'pwd' => '" . $dbconfig['pwd'] . "', //数据库密码
    'dbname' => '" . $dbconfig['dbname'] . "', //数据库名
];
$" . "accredit" . " = [
    'token' => '" . $dbconfig['token'] . "',
    'url' => '" . $dbconfig['url'] . "',
    'versions' => '" . $dbconfig['versions'] . "'
];";
        if (file_put_contents($FILE, $data)) {
            return ['code' => 1, 'msg' => '数据更新成功！'];
        }
        return ['code' => -1, 'msg' => '写入失败或者文件(deploy.php)没有写入权限，注意检查！'];
    }
}