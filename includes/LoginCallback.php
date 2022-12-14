<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/10 20:34
// +----------------------------------------------------------------------
// | Filename: LoginCallback.php
// +----------------------------------------------------------------------
// | Explain: 快捷登录回调地址
// +----------------------------------------------------------------------

use extend\QuickLogin;

include '../includes/fun.global.php';
global $_QET;
if (empty($_QET)) {
    show_msg('温馨提示', '参数缺失，请重新尝试！', '3', ROOT_DIR_S . '/?mod=route&p=User');
}
QuickLogin::CallbackDistribution($_QET);
die('正在跳转中...');