<?php
/**
 * Author：晴天 QQ：1186258278
 * Creation：2020/8/10 13:33
 * Filename：CallBack.php
 * 支付回调端,用于适配彩虹易支付返回转义&的问题
 */

include './fun.global.php';

use lib\AppStore\AppList;

if (empty($_REQUEST['uis'])) {
    die('参数缺失,回调数据不完整！');
}
$Data = json_decode(xiaochu_de($_REQUEST['uis']), TRUE);
if (empty($Data['t']) || empty($Data['i'])) {
    $Arrex = explode('HsD', $_REQUEST['uis']);
    $Data['t'] = $Arrex[0];
    $Data['i'] = $Arrex[1];
}
unset($_REQUEST['uis']);
if (empty($Data['t']) || empty($Data['i'])) die('参数缺失,回调数据异常！');
$_REQUEST['typeS'] = $Data['t'];
AppList::Api($Data['i'], $_REQUEST);
die;