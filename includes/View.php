<?php

use lib\Guard\Guard;
use lib\Hook\Hook;
use lib\Simulation\Simulation;
use Medoo\DB\SQL;

/**
 * Class View
 * 视图引导类
 */
class View
{
    /**
     * @param $mod
     */
    public static function Home($mod = 0)
    {
        global $_QET, $times, $cdnpublic, $accredit, $cdnserver, $conf;

        if (($conf['WeixQqValidation'] == 1) && self::WeixQqV()) {
            include ROOT . 'template' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'intercept.php';
            die;
        }
        if ($conf['CloseWebsite'] !== '') {
            show_msg('闭站通知', $conf['CloseWebsite'], '4', false, false);
        }
        if ((int)$conf['userbinding'] === -1 && (int)$conf['userdomaintype'] === 1 && href() !== $accredit['url'] && href() !== 'www.' . $accredit['url']) {
            $DB = SQL::DB();
            $Get = $DB->get('user', ['domain'], [
                'domain' => (string)href()
            ]);
            if (!$Get) {
                show_msg('域名未绑定提醒', '<center><h4>当前访问的域名：' . href() . '未绑定，无法访问！</h4><br></center>', 4);
            }
        }

        if (isset($_QET['m'])) {
            Simulation::jiuwu($_QET);
        }

        $conf = Guard::Filtrate($conf);
        self::HomePasswordAccess();
        self::AntiReptile();
        self::ForcedLanding();
        if (self::isMobile() == true) {
            if ((int)$conf['template_m'] === -1) {
                show_msg('温馨提示', '<div style="text-align: center"><h4>请使用电脑打开本站<br><br>当前访问地址：<a target="_blank" href="' . href(2) . $_SERVER['REQUEST_URI'] . '">' . href(2) . $_SERVER['REQUEST_URI'] . '</a></h4></div>', false, false, false);
            }
            if ((int)$conf['template'] === -2 && strpos($_SERVER['REQUEST_URI'], ROOT_DIR_S . '/?mod=Frame') !== false) {
                header('Location:' . ROOT_DIR);
            }
            $conf['template'] = $conf['template_m'];
        } else if ((int)$conf['template'] === -1) {
            $Image = QR_Code(1, href(2) . $_SERVER['REQUEST_URI'], 10, 1, SYSTEM_ROOT . 'extend/log/ShopImage/' . md5(href(2) . $_SERVER['REQUEST_URI']) . '.png', '/includes/extend/log/ShopImage/' . md5(href(2) . $_SERVER['REQUEST_URI']) . '.png');
            show_msg('温馨提示', '<div style="text-align: center"><h4>请使用手机打开本站<br><br>当前访问地址：<a target="_blank" href="' . href(2) . $_SERVER['REQUEST_URI'] . '">' . href(2) . $_SERVER['REQUEST_URI'] . '</a></h4><hr><img style="box-shadow: 3px 3px 16px #ccc;border-radius: 0.5rem" src="' . $Image . '" width="350px" ><h4><br>请拿出手机扫一扫进入</h4></div>', false, false, false);
        } else if ((int)$conf['template'] === -2 && strpos($_SERVER['REQUEST_URI'], ROOT_DIR_S . '/?mod=Frame') !== false) {
            $conf['template'] = $conf['template_m'];
        } else if ((int)$conf['template'] === -2 && strpos($_SERVER['REQUEST_URI'], ROOT_DIR . '?mod=Frame') === false) {
            $conf['template'] = $conf['template_m'];
            echo '<script> if(window.top === window.self){window.location= "' . ROOT_DIR . '?mod=Frame&Url=' . xiaochu_en(href(2) . $_SERVER['REQUEST_URI']) . '" }</script>' . "\n";
        }
        if (!empty($mod)) {
            Hook::execute('VisitMod', $_QET);
            $file = ROOT . 'template/' . $conf['template'] . '/' . $mod . '.php';
            if (!file_exists($file)) {
                $file = ROOT . 'template/default/' . $mod . '.php';
                if (!file_exists($file)) {
                    show_msg('警告', $mod . '.php扩展文件不存在,点击下方按钮返回!', 3, ROOT_DIR);
                }
            }
        } else {
            $file = ROOT . 'template/' . $conf['template'] . '/index.php';
            if (!file_exists($file)) {
                $file = ROOT . 'template/default/index.php';
            }
            Hook::execute('VisitHome');
        }
        //载入模板配置
        $TemState = TemConf($conf['template']);
        $path = ROOT . 'includes/extend/log/Home/';
        mkdirs($path);
        $tempFlie = @tempnam($path, 'Vie');
        if (!$tempFlie) {
            include $file;
            return;
        }
        $html = file_get_contents($file);
        $temp = fopen($tempFlie, 'w+');
        fwrite($temp, Guard::Filtrate($html));
        fclose($temp);
        include $tempFlie;
        unlink($tempFlie);
    }

    /**
     * @param string
     * 用户强制登陆
     */
    public static function ForcedLanding()
    {
        global $conf;
        if (empty($_COOKIE['THEKEY']) && (int)$conf['ForcedLanding'] === 2) {
            include ROOT . 'template/default/login.php';
            die;
        }
    }

    /**
     * 首页加密访问验证
     */
    public static function HomePasswordAccess($pass = false, $tkn = false)
    {
        global $conf;
        $token = md5(userip() . href() . date('Y-m-d'));
        if ($pass != false) {
            if ($tkn != $token) {
                dies(-1, '请刷新页面重试！');
            }
            if ($pass != $conf['PasswordAccess']) {
                dies(-1, '密码错误，请重新尝试！');
            }
            $_SESSION['PasswordAccess'] = $conf['PasswordAccess'];
            dies(1, '验证通过，欢迎访问本站！');
        }
        if (!empty($conf['PasswordAccess']) && $conf['PasswordAccess'] != 0) {
            if (empty($_SESSION['PasswordAccess']) || $_SESSION['PasswordAccess'] != $conf['PasswordAccess']) {
                include ROOT . 'template/default/encrypt.php';
                die;
            }
        }
    }

    /**
     * @return false
     * 初级反爬虫
     */
    public static function AntiReptile()
    {
        global $conf, $accredit, $_QET;
        if ((int)$conf['AntiReptile'] !== 1) {
            return false;
        }
        $Key = md5(userip() . '_' . href() . $accredit['token']);
        if (!empty($_QET[$Key])) {

            $_SESSION['AntiReptile'] = $Key;

            die('<script type="text/javascript">window.location.href="' . ROOT_DIR_S . '/?' . xiaochu_de($_QET[$Key]) . '";</script>');
        }
        if ($_SESSION['AntiReptile'] !== $Key) {
            $url = ROOT_DIR_S . '/' . (empty($_SERVER['QUERY_STRING']) ? '?' . $Key . '=' : '?' . $_SERVER['QUERY_STRING'] . '&' . $Key . '=') . xiaochu_en($_SERVER['QUERY_STRING']);
            die('正在载入中...<script type="text/javascript">window.location.href="' . $url . '";</script>');
        }
        return true;
    }

    /**
     * @return bool
     * 判断是否是移动端打开
     */
    public static function isMobile()
    {
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        if (isset($_SERVER['HTTP_VIA'])) {
            return stripos($_SERVER['HTTP_VIA'], 'wap') !== false ? true : false;
        }
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = ['nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            ];
            if (preg_match('/(' . implode('|', $clientkeywords) . ')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'MicroMessenger');
            if (preg_match('/(' . implode('|', $clientkeywords) . ')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断是否在QQ或微信内打开
     */
    public static function WeixQqV()
    {
        $bro_msg = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($bro_msg, 'iPhone') || strpos($bro_msg, 'iPad')) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], ' QQ') !== false) {
                return 'iosqq';
            }
        }
        if (strpos($bro_msg, 'Android') && (strpos($bro_msg, 'MQQBrowser') !== false) && strpos($bro_msg, ' QQ') !== false) {
            return 'qq';
        }
        if (strpos($bro_msg, 'MicroMessenger') !== false) {
            return 'wx';
        }
        return false;
    }

    /**
     * @param int $nus
     * 缓存文件清理
     */
    public static function clear($nus = 60)
    {
        $Flies = ROOT . 'includes/extend/log/Home/';
        $arr = scandir($Flies);
        foreach ($arr as $re) {
            if ($re == '.' || $re == '..') continue;
            $fea = $Flies . $re;
            if (is_file($fea) && (time() - filemtime($fea)) < $nus) {
                @unlink($fea);
            }
        }
    }
}
