<?php

use lib\Hook\Hook;
use Medoo\DB\SQL;

include '../includes/fun.global.php';
global $conf, $title, $cdnserver;
if ((int)$conf['ShutDownUserSystem'] === -1) {
    show_msg('温馨提示', $conf['ShutDownUserSystemCause'], 1, '/');
}
$UserData = login_data::login_verify();
if ((int)$UserData['state'] === 2) {
    setcookie("THEKEY", null, time() - 1, '/');
    show_msg('账户冻结提醒', '您已被禁止登陆,请联系管理了解详情!', 2, '../');
}
if (isset($_QET['act']) && $_QET['act'] === 'close') {
    if (setcookie("THEKEY", null, time() - 66, '/')) {
        Hook::execute('UserLogout', [
            'name' => $UserData['name'],
            'id' => $UserData['id']
        ]);
        show_msg('操作成功', '成功退出登陆!', '1', './login.php');
    } else show_msg('操作失败', '退出登录失败！', '4', './login.php');
}
$DB = SQL::DB();
$CountN = $DB->count('tickets', [
    'state[!]' => 3,
    'type[<]' => 3,
    'uid' => $UserData['id']
]);
$CountN2 = $DB->count('mark', [
    'state' => 2,
    'uid' => $UserData['id'],
]);
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8"/>
    <title><?= $title ?> [<?= $UserData['name'] ?>的后台]</title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Coderthemes" name="author"/>
    <link rel="shortcut icon" href="../assets/favicon.ico">
    <link href="../assets/css/vendor/jquery-jvectormap-1.2.2.css"
          rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css"
          href="../assets/layui/css/layui.css"/>
    <link href="../assets/css/icons.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="../assets/css/app.min.css" rel="stylesheet" type="text/css"/>
    <link href="../assets/mdui/css/mdui.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css"
          href="../assets/user/css/Global.css"/>
</head>
<style>
    .card-title {
        font-weight: 300;
    }
</style>

<body>
<div id="loading" style="opacity: 1;display:block;">
    <div id="loading-center">
        <div id="loading-center-absolute">
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
        </div>
    </div>
</div>
<!-- Begin page -->
<div class="wrapper">
    <div class="left-side-menu" style="z-index: 10">
        <div class="slimscroll-menu" id="left-side-menu-container">
            <!-- LOGO -->
            <!--- Sidemenu -->
            <ul class="metismenu side-nav">

                <li class="side-nav-title side-nav-item" style="font-size: 2em;color: white;font-weight: 300">用户管理后台
                </li>
                <li class="side-nav-item">
                    <a href="index.php" class="side-nav-link">
                        <i class="layui-icon layui-icon-home"></i>
                        <span> 用户首页 </span>
                    </a>
                </li>
                <?php if ($conf['userleague'] == 1) { ?>
                    <li class="side-nav-item">
                        <a href="javascript: void(0);" class="side-nav-link">
                            <i class="layui-icon layui-icon-chart"></i>
                            <span> 店铺管理 </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="side-nav-second-level" aria-expanded="false">
                            <li>
                                <a href="management.php">我的店铺</a>
                            </li>
                            <?php if ($UserData['grade'] > 1) { ?>
                                <li>
                                    <a href="subordinate.php">我的下级</a>
                                </li>
                            <?php } ?>
                            <?php if ($UserData['grade'] >= $conf['usergradeprofit'] || $UserData['grade'] >= $conf['usergradegoodsstate']) { ?>
                                <li>
                                    <a href="goods_manage.php">店铺商品</a>
                                </li>
                            <?php } ?>
                            <?php if ($UserData['grade'] >= 1 && $conf['userappstate'] == 1) { ?>
                                <li>
                                    <a href="applist.php">App生成</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="layui-icon layui-icon-rmb"></i>
                        <span> 我的钱包</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li>
                            <a href="pay.php">资金管理</a>
                        </li>

                        <li>
                            <a href="coupon.php">优惠卡券</a>
                        </li>
                    </ul>
                </li>
                <li class="side-nav-item">
                    <a href="grade.php" class="side-nav-link">
                        <i class="layui-icon layui-icon-vercode"></i>
                        <span> 我的等级 </span>
                    </a>
                </li>
                <?php if ($conf['hostSwitch'] == 1) { ?>
                    <li class="side-nav-item">
                        <a href="HostList.php" class="side-nav-link">
                            <i class="layui-icon layui-icon-senior"></i>
                            <span> 我的主机 </span>
                        </a>
                    </li>
                <?php } ?>
                <li class="side-nav-item">
                    <a href="tickets.php" class="side-nav-link">
                        <i class="layui-icon layui-icon-survey"></i>
                        <span> 售后工单 <?= ($CountN == 0 ? '' : '<span class="layui-badge layui-bg-red">' . $CountN . '</span>') ?></span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="discuss.php" class="side-nav-link">
                        <i class="layui-icon layui-icon-dialogue"></i>
                        <span> 我的评论 <?= ($CountN2 == 0 ? '' : '<span class="layui-badge layui-bg-red">' . $CountN2 . '</span>') ?></span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="activity.php" class="side-nav-link">
                        <i class="layui-icon layui-icon-gift"></i>
                        <span> 邀请奖励 </span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="journal.php" class="side-nav-link">
                        <i class="layui-icon layui-icon-log"></i>
                        <span> 操作日志 </span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="?act=close" class="side-nav-link">
                        <i class="layui-icon layui-icon-logout"></i>
                        <span> 退出登陆 </span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Sidebar -left -->
    </div>
    <!-- Left Sidebar End -->
    <div class="content-page">
        <div class="content">
            <!-- Topbar Start -->
            <div class="navbar-custom">
                <ul class="list-unstyled topbar-right-menu float-right mb-0">
                    <li class="dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <i class="layui-icon layui-icon-email noti-icon"></i>
                        </a>
                    </li>
                    <li class="dropdown notification-list">
                        <a class="nav-link dropdown-toggle nav-user arrow-none mr-0" data-toggle="dropdown" href="#"
                           role="button" aria-haspopup="false" aria-expanded="false">
                                <span class="account-user-avatar">
                                    <img src="<?= UserImage($UserData) ?>" alt="user-image"
                                         class="rounded-circle bg-warning" style="border: solid 2px #ccc">
                                </span>
                            <span>
                                    <span class="account-user-name">平台用户</span>
                                    <span class="account-position"><?= $UserData['name'] ?></span>
                                </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                            <!-- item-->
                            <div class=" dropdown-header noti-title">
                                <h6 class="text-overflow m-0">快捷导航</h6>
                            </div>
                            <!-- item-->
                            <a href="http://wpa.qq.com/msgrd?v=3&uin=<?= $conf['kfqq'] ?>&site=qq&menu=yes"
                               target="_blank" class="dropdown-item notify-item">
                                <i class="layui-icon layui-icon-chat mr-1"></i>
                                <span>联系官方</span>
                            </a>
                            <!-- item-->
                            <a href="../" target="_blank" class="dropdown-item notify-item">
                                <i class="layui-icon layui-icon-home mr-1"></i>
                                <span>前往首页</span>
                            </a>
                            <a href="set.php" class="dropdown-item notify-item">
                                <i class="layui-icon layui-icon-set mr-1"></i>
                                <span>信息编辑</span>
                            </a>
                            <!-- item-->
                            <a href="?act=close" class="dropdown-item notify-item">
                                <i class="layui-icon layui-icon-logout mr-1"></i>
                                <span>注销账号</span>
                            </a>
                        </div>
                    </li>
                </ul>
                <button class="button-menu-mobile open-left disable-btn">
                    <i class="layui-icon layui-icon-spread-left"></i>
                </button>
                <div class="app-search">
                    <form>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dw" placeholder="查看我的操作日志">
                            <span class="layui-icon layui-icon-search"></span>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button"
                                        onclick="window.location.href = './journal.php'+$('#dw').val()">
                                    查看日志
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end Topbar -->

            <!-- start page title -->
            <div class="row" style="width: 98%;margin: auto">
                <div class="col-12">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="index.php">首页</a></li>
                                        <li class="breadcrumb-item active"><?= $title ?></li>
                                    </ol>
                                </div>
                                <h4 class="page-title" style="font-weight: 400"><?= $title ?></h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                </div>
            </div>
            <!-- end page title -->
            <style>
                .note-popover {
                    display: none
                }

                .note-toolbar {
                    z-index: 9 !important;
                }

                .panel-heading {
                    border-bottom: 1px solid #ccc;
                }
            </style>
