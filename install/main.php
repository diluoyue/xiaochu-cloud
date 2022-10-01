<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/7 13:02
// +----------------------------------------------------------------------
// | Filename: main.php
// +----------------------------------------------------------------------
// | Explain: 程序安装处理模块
// +----------------------------------------------------------------------

use Curl\Curl;
use extend\Maintain;
use voku\helper\AntiXSS;

error_reporting(0);
session_start();
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: application/json; charset=UTF-8');
const SYSTEM_ROOT = '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
const ROOT = '..' . DIRECTORY_SEPARATOR;
require './db.class.php';
include './class.php';
include '../includes/fun.core.php';
include '../includes/deploy.php';
include '../includes/Curl.php';
include '../includes/CookieCache.php';
include '../includes/lib/Guard/Guard.php';
include '../vendor/autoload.php';
include '../includes/extend/Maintain.php';
include '../includes/lib/medoo/config.php';
include '../includes/lib/medoo/Medoo.php';

global $accredit, $dbconfig;
$antiXss = new AntiXSS();
$_QET = $antiXss->xss_clean($_REQUEST);

switch ($_QET['act']) {
    case 'InstallData':
        $PhpState = 1;
        if (PHP_VERSION_ID < 70000 || PHP_VERSION_ID >= 80000) {
            $PhpState = -1;
        }

        $InstallType = 1;
        if (!file_exists('install.lock')) {
            $InstallType = -1;
        }

        if ($InstallType === 1) {
            $accredit = [
                'versions' => $accredit['versions'],
            ];
            $dbconfig = [];
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => [
                'state' => $InstallType,
                'accredit' => $accredit,
                'dbconfig' => $dbconfig,
                'ApiList' => Curl::ApiLists(),
                'Verification' => [
                    0 => [
                        'name' => 'PHP 7.0+',
                        'state' => $PhpState,
                        'content' => '本程序支持PHP7.0+ 至 PHP7.4+',
                    ],
                    1 => [
                        'name' => 'curl_exec()',
                        'state' => (function_exists('curl_exec') ? 1 : -1),
                        'content' => '抓取网页内容等',
                    ],
                    2 => [
                        'name' => 'file_get_contents()',
                        'state' => (function_exists('file_get_contents') ? 1 : -1),
                        'content' => '读取文件',
                    ],
                    3 => [
                        'name' => 'PDO',
                        'state' => (class_exists('PDO') ? 1 : -1),
                        'content' => '数据库连接',
                    ]
                ]
            ]
        ]);
        break;
    case 'Install': //安装程序
        test(['host', 'port', 'dbname', 'pwd', 'token', 'state', 'user', 'versions']);
        if (file_exists('install.lock')) {
            dies(-1, '您已经安装过了，请删除！/install/install.lock 文件后再来安装！');
        }

        $DB = DB::connect($_QET['host'], $_QET['user'], $_QET['pwd'], $_QET['dbname'], $_QET['port']);

        if (!$DB) {
            switch (DB::connect_errno()) {
                case 2002:
                    dies(2002, '连接数据库失败，数据库地址填写错误！');
                    break;
                case 1045:
                    dies(1045, '连接数据库失败，数据库用户名或密码填写错误！');
                    break;
                case 1049:
                    dies(1049, '连接数据库失败，数据库名不存在！');
                    break;
                default:
                    dies(DB::connect_errno(), '连接数据库失败' . DB::connect_error());
                    break;
            }
        }

        $Res = install::ModifyFileContents([
            'url' => href(),
            'host' => $_QET['host'],
            'port' => $_QET['port'],
            'user' => $_QET['user'],
            'pwd' => $_QET['pwd'],
            'dbname' => $_QET['dbname'],
            'token' => $_QET['token'],
            'versions' => $_QET['versions'],
        ]);

        if ($Res['code'] !== 1) {
            dies(-1, $Res['msg']);
        }

        /**
         * 数据处理
         */

        //判断安装模式
        $Type = DB::get_row("show variables like '%version%'");
        if (!$Type || (float)$Type['Value'] < 5.7) {
            //兼容模式
            $SQL = file_get_contents('install.sql');
            $SQL = explode(';', $SQL);
            $success = 0;
            $error = 0;
            $content = '';
            $State = (int)$_QET['state'];
            $Data = [];
            foreach ($SQL as $v) {
                $v = str_replace([PHP_EOL, "\n", "\r"], '', $v);
                if (empty($v) || $v == '' || ($State == 2 && strpos($v, 'DROP TABLE IF EXISTS'))) {
                    continue;
                }
                $Data[] = $v;
                if (DB::query(trim($v))) {
                    $success++;
                } else {
                    $error++;
                    $content .= DB::error() . '[' . $v . ']<br/>';
                }
            }
            if ($success !== 0) {
                $_SESSION['ApiState'] = md5($_QET['token']);
                @unlink('../assets/log/MaintainSmdd.lock');
                @unlink('../assets/log/MaintainUnset.lock');
                file_put_contents('install.lock', '安装锁');
            }
            dier([
                'code' => ($success !== 0 ? 1 : -1),
                'msg' => '安装结果如下：<br>数据写入成功数：' . $success . '条<br>失败数：' . $error . '条<br>点击下一步继续！' . (empty($content) ? '' : '<br>' . $content),
                'type' => 1,
            ]);
        } else {
            //新模式
            DB::query("SET sql_mode = ''");
            DB::query('SET NAMES utf8mb4;');
            DB::query('default-storage-engine=INNODB');

            $Res = Maintain::databaseCalibre($_QET['state'] == 1 ? 2 : 1);

            if (!$Res) {
                dies(-1, '安装失败，数据校准文件不存在！');
            }
            if ($Res['code'] == 1) {
                $_SESSION['ApiState'] = md5($_QET['token']);
                file_put_contents('install.lock', '安装锁');
                @unlink('../assets/log/MaintainSmdd.lock');
                @unlink('../assets/log/MaintainUnset.lock');
                $Res['type'] = 2;
                dier($Res);
            } else {
                dies(-1, $Res['msg']);
            }
        }
        break;
    case 'Ping': //节点Ping
        test(['id']);
        Curl::Ping($_QET['id']);
        break;
    case 'ApiSet': //切换节点
        if (file_exists('install.lock')) {
            if (!isset($_SESSION['ApiState']) || $_SESSION['ApiState'] != md5($accredit['token'])) {
                dies(-1, '您已经安装过了，请删除！/install/install.lock 文件后再来安装！');
            }
        }
        test(['id']);
        if (file_put_contents('../assets/log/sequence.lock', ($_QET['id'] + 1))) {
            dies(1, '切换成功');
        } else {
            dies(-1, '切换失败');
        }
        break;
    default:
        dies(-1, '路径不存在！');
        break;
}
