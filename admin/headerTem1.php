<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/26 15:16
// +----------------------------------------------------------------------
// | Filename: headerTem1.php
// +----------------------------------------------------------------------
// | Explain: 全局模板1
// +----------------------------------------------------------------------
global $title, $cdnserver, $tise, $cdnpublic;
?>
<!DOCTYPE html>
<html lang="cn" style="font-size:96%">

<head>
    <meta charset="utf-8"/>
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="max-age=7200"/>
    <link rel="shortcut icon" href="../assets/favicon.ico">
    <link href="../assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="../assets/layui/css/layui.css"/>
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"/>
    <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="../assets/css/app.min.css" rel="stylesheet" type="text/css"/>
    <link href="../assets/mdui/css/mdui.min.css" rel="stylesheet" type="text/css"/>
</head>
<!--[if lt IE 9]>
<script src="<?= $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="<?= $cdnpublic ?>lib.baomitu.com/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<body data-layout="topnav">
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
<div class="wrapper">
    <div class="content-page">
        <div class="content">

            <div class="navbar-custom topnav-navbar">
                <div class="container-fluid">

                    <a href="./index.php" class="topnav-logo">
                            <span class="topnav-logo-lg">
                                <img src="../assets/img/loginheader.png" alt="" height="26">
                            </span>
                        <span class="topnav-logo-sm">
                                <img src="../assets/img/loginheader.png" alt="" height="26">
                            </span>
                    </a>

                    <ul class="list-unstyled topbar-right-menu float-right mb-0">
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle arrow-none" target="_blank"
                               href="https://cdn.79tian.com/api/wxapi/view/flock.php" role="button"
                               aria-haspopup="false" aria-expanded="false">
                                <i class="layui-icon layui-icon-service noti-icon"></i>
                            </a>
                        </li>
                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle arrow-none" href="admin.user.list.php" role="button"
                               aria-haspopup="false" aria-expanded="false">
                                <i class="layui-icon layui-icon-username noti-icon"></i>
                            </a>
                        </li>

                        <li class="dropdown notification-list">
                            <a class="nav-link dropdown-toggle nav-user arrow-none mr-0" data-toggle="dropdown" href="#"
                               role="button" aria-haspopup="false" aria-expanded="false">
                                    <span class="account-user-avatar">
                                        <img id="head_image" src="../assets/img/image_index.png" alt="user-image"
                                             class="rounded-circle bg-warning" style="border: solid 2px #ccc">
                                    </span>
                                <span>
                                        <span class="account-user-name">Super</span>
                                        <span class="account-position" id="header_name">站长</span>
                                    </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                                <div class=" dropdown-header noti-title">
                                    <h6 class="text-overflow m-0">快捷导航</h6>
                                </div>
                                <a href="admin.user.list.php" class="dropdown-item notify-item">
                                    <i class="layui-icon layui-icon-user mr-1"></i>
                                    <span>用户管理</span>
                                </a>
                                <a href="../" target="_blank" class="dropdown-item notify-item">
                                    <i class="layui-icon layui-icon-home mr-1"></i>
                                    <span>前往首页</span>
                                </a>
                                <a href="?Loggedout=1" class="dropdown-item notify-item">
                                    <i class="layui-icon layui-icon-logout mr-1"></i>
                                    <span>注销账号</span>
                                </a>
                            </div>
                        </li>
                    </ul>

                    <a class="navbar-toggle" data-toggle="collapse" data-target="#topnav-menu-content">
                        <div class="lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </a>

                    <div class="app-search">
                        <form>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dw" placeholder="输入下单信息搜索订单">
                                <span class="layui-icon layui-icon-search"></span>
                                <div class="input-group-append">
                                    <button class="btn btn-primary"
                                            onclick="window.location.href='admin.order.list.php?name='+$('#dw').val()"
                                            type="button">搜索
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="topnav">
                <div class="container-fluid">
                    <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                        <div class="collapse navbar-collapse" id="topnav-menu-content">
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-dashboards"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-speedometer mr-1"></i>数据中心
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-dashboards">
                                        <a href="./index.php" class="dropdown-item">数据统计</a>
                                        <a href="./admin.goods.rank.php" class="dropdown-item">销量排行</a>
                                        <a href="./admin.discuss.list.php" class="dropdown-item">评论审核</a>
                                        <a href="./admin.user.pay.php" class="dropdown-item">提现审核</a>
                                        <a href="http://bbs.79tian.com/" target="_blank" class="dropdown-item">官方论坛</a>
                                        <a href="https://cdn.79tian.com/api/wxapi/view/index.php" target="_blank"
                                           class="dropdown-item">授权管理</a>
                                        <a href="./admin.update.php" class="dropdown-item">系统升级</a>
                                    </div>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-apps"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-apps mr-1"></i>商品相关
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-apps">
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                商品管理
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.goods.add.php" class="dropdown-item">添加商品</a>
                                                <a href="./admin.goods.list.php" class="dropdown-item">商品列表</a>
                                                <a href="./admin.goods.sort.php" class="dropdown-item">商品排序</a>
                                                <a href="./admin.goods.monitoring.php" class="dropdown-item">商品监控</a>
                                                <a href="../?mod=UpAndDown" target="_blank"
                                                   class="dropdown-item">价格波动</a>

                                                <a href="./admin.goods.dockingSystem.php" class="dropdown-item">一键串货</a>
                                            </div>
                                        </div>
                                        <a href="./admin.goods.supply.php" class="dropdown-item">供货大厅</a>
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                分类管理
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.class.add.php" class="dropdown-item">添加分类</a>
                                                <a href="./admin.class.list.php" class="dropdown-item">分类列表</a>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                货源管理
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.source.add.php" class="dropdown-item">添加货源</a>
                                                <a href="./admin.source.list.php" class="dropdown-item">货源列表</a>
                                                <a href="./admin.source.ip.php" class="dropdown-item">代理IP</a>
                                                <a href="./admin.source.promotion.php" class="dropdown-item">货源推广</a>
                                            </div>
                                        </div>
                                        <a href="./admin.goods.express.php" class="dropdown-item">运费模板</a>
                                        <a href="./admin.InputValidation.php" class="dropdown-item">输入规则</a>
                                        <a href="./admin.goods.token.php" class="dropdown-item">卡密库存</a>
                                    </div>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-google-pages mr-1"></i>订单相关
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                        <a href="./admin.order.list.php" class="dropdown-item">订单列表</a>
                                        <a href="./admin.docking.log.php" class="dropdown-item">对接日志</a>
                                        <a href="./admin.order.derive.php" class="dropdown-item">订单导出</a>
                                        <a href="./admin.order.pay.php" class="dropdown-item">支付订单</a>
                                    </div>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layouts"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="layui-icon layui-icon-username mr-1"></i>用户相关
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-layouts">
                                        <a href="./admin.user.add.php" class="dropdown-item">添加用户</a>
                                        <a href="./admin.user.list.php" class="dropdown-item">用户列表</a>
                                        <a href="./admin.user.log.php" class="dropdown-item">操作日志</a>
                                        <a href="./tickets.php" class="dropdown-item">售后工单</a>
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                等级配置
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.level.list.php" class="dropdown-item">等级列表</a>
                                                <a href="./admin.level.add.php" class="dropdown-item">新增等级</a>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                加价规则
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.increasePrice.list.php" class="dropdown-item">规则列表</a>
                                                <a href="./admin.increasePrice.add.php" class="dropdown-item">新增规则</a>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                充值卡密
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.recharge.add.php" class="dropdown-item">生成充值卡</a>
                                                <a href="./admin.recharge.list.php" class="dropdown-item">充值卡列表</a>
                                            </div>
                                        </div>
                                        <a href="./admin.user.set.php" class="dropdown-item">用户配置</a>
                                    </div>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layouts"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="layui-icon layui-icon-read mr-1"></i>公告通知
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-layouts">
                                        <a href="./admin.article.list.php" class="dropdown-item">公告列表</a>
                                        <a href="./admin.article.add.php" class="dropdown-item">发布公告</a>
                                    </div>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layouts"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="layui-icon layui-icon-senior mr-1"></i>宝塔分销
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-layouts">
                                        <div class="dropdown">
                                            <a href="./admin.server.add.php" class="dropdown-item">添加服务器</a>
                                        </div>
                                        <div class="dropdown">
                                            <a href="./admin.server.list.php" class="dropdown-item">服务器列表</a>
                                        </div>
                                        <div class="dropdown">
                                            <a href="./admin.host.list.php" class="dropdown-item">主机列表</a>
                                        </div>
                                        <div class="dropdown">
                                            <a href="./admin.host.set.php" class="dropdown-item">主机配置</a>
                                        </div>
                                    </div>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layouts"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="layui-icon layui-icon-gift mr-1"></i>活动推广
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-layouts">
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                优惠券管理
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.coupon.add.php" class="dropdown-item">添加优惠券</a>
                                                <a href="./admin.coupon.list.php" class="dropdown-item">优惠券列表</a>
                                                <a href="./admin.coupon.set.php" class="dropdown-item">优惠券配置</a>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                限购秒杀
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.seckill.add.php" class="dropdown-item">添加活动</a>
                                                <a href="./admin.seckill.list.php" class="dropdown-item">活动列表</a>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                商品兑换卡
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.goods.cash.add.php" class="dropdown-item">添加商品兑换卡密</a>
                                                <a href="./admin.goods.cash.list.php" class="dropdown-item">管理商品兑换卡</a>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layouts"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="layui-icon layui-icon-set mr-1"></i>站点配置
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-layouts">
                                        <a href="./admin.notice.set.php" class="dropdown-item">全局公告</a>
                                        <a href="./admin.Payments.php" class="dropdown-item">支付配置</a>
                                        <a href="./admin.template.set.php" class="dropdown-item">网站模板</a>
                                        <a href="./admin.sms.set.php" class="dropdown-item">短信通知</a>
                                        <a href="./admin.app.set.php" class="dropdown-item">网站配置</a>
                                        <a href="./admin.login.set.php" class="dropdown-item">登录配置</a>
                                        <a href="./admin.api.set.php" class="dropdown-item">节点配置</a>
                                        <a href="./admin.monitoring.list.php" class="dropdown-item">监控中心</a>
                                        <div class="dropdown">
                                            <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                               id="topnav-project" role="button" data-toggle="dropdown"
                                               aria-haspopup="true" aria-expanded="false">
                                                APP配置
                                                <div class="arrow-down"></div>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="topnav-project">
                                                <a href="./admin.apps.list.php" class="dropdown-item">生成列表</a>
                                                <a href="./admin.apps.set.php" class="dropdown-item">相关配置</a>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layouts"
                                       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="layui-icon layui-icon-component mr-1"></i>应用商店
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-layouts">
                                        <a href="./admin.store.php" class="dropdown-item">应用列表</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <?php if ($tise == 'index') { ?>
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <form class="form-inline">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-light"
                                                       id="HomeDate"/>
                                                <div class="input-group-append">
                                                        <span class="input-group-text bg-primary border-primary text-white">
                                                            <i class="layui-icon layui-icon-search font-13"></i>
                                                        </span>
                                                    <a href="javascript: vm.DataAnalysis(2)"
                                                       class="btn btn-primary ml-2">
                                                        <i class="mdi mdi-autorenew"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <h4 class="page-title" style="font-weight:300;"><?= $title ?></h4>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="page-title-box">
                                        <div class="page-title-right">
                                            <ol class="breadcrumb m-0">
                                                <li class="breadcrumb-item"><a href="index.php">首页</a></li>
                                                <li class="breadcrumb-item active"><?= $title ?></li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title" style="font-weight:300;"><?= $title ?></h4>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
