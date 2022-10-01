<?php

/**
 * 公共顶部文件
 */
$protect_admin = true;
include '../includes/fun.global.php';
global $conf, $_QET;
if (isset($_QET['Loggedout'])) {
    $_SESSION['ADMIN_TOKEN'] = null;
    show_msg('操作成功', '成功退出登陆!', '1', './login.php');
}
if ((int)$conf['AdminHeaderTem'] === 2) {
    include 'headerTem2.php';
} else {
    include 'headerTem1.php';
}
