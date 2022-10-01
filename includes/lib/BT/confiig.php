<?php
/**
 * Author：晴天 QQ：1186258278
 * Creation：2020/4/12 13:47
 * Filename：confiig.php
 * 宝塔服务器控制类
 */

namespace BT;

class Confiig
{
    public static $BT_KEY;  //接口密钥
    public static $BT_PANEL; //面板地址
    public static $TYPE; //存放分类
    public static $PATH; //存放根目录

    /**
     * @param array $Data
     * 初始化服务器状态
     */
    public static function Conf($Data = [])
    {
        self::$BT_KEY = $Data['token'];
        self::$BT_PANEL = $Data['url'];
        self::$TYPE = $Data['type'];
        self::$PATH = $Data['path'];
    }

    //签名

    /**
     * 发起POST请求
     * @param String $url 目标网填，带http://
     * @param Array|String $data 欲提交的数据
     * @return string
     */
    public static function HttpPostCookie($url, $data, $timeout = 120)
    {
        //定义cookie保存位置
        mkdirs(ROOT . '/includes/extend/log/Cookie/');
        $cookie_file = ROOT . '/includes/extend/log/Cookie/BT_' . md5(self::$BT_PANEL) . '.cookie';
        if (!file_exists($cookie_file)) {
            $fp = fopen($cookie_file, 'w+');
            fclose($fp);
        }

        $data = array_merge(self::GetKeyData(), $data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function GetKeyData()
    {
        $now_time = time();
        $p_data = [
            'request_token' => md5($now_time . '' . md5(self::$BT_KEY)),
            'request_time' => $now_time
        ];
        return $p_data;
    }
}