<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/26 15:16
// +----------------------------------------------------------------------
// | Filename: headerTem2.php
// +----------------------------------------------------------------------
// | Explain: 全局模板2
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

<body>
<div id="loading" style="opacity: 1;display:block;z-index:100;">
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
<style>
    .layui-timeline-axis, .layui-table-fixed {
        z-index: 1
    }
</style>
<div class="wrapper">
    <div class="left-side-menu">
        <div class="slimscroll-menu" style="z-index: 10002;" id="left-side-menu-container">
            <ul class="metismenu side-nav">
                <li class="side-nav-title side-nav-item" style="font-size: 2em;color: white;font-weight: 300">站长后台
                </li>
                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="layui-icon layui-icon-home"></i>
                        <span> 数据中心</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li>
                            <a href="./index.php">数据统计</a>
                        </li>
                        <li>
                            <a href="./admin.goods.rank.php">销量排行</a>
                        </li>
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">审核相关
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.discuss.list.php">评论审核</a>
                                </li>
                                <li>
                                    <a href="./admin.user.pay.php">提现审核</a>
                                </li>
                            </ul>
                        </li>
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">其他杂项
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="http://bbs.79tian.com/" target="_blank">官方论坛</a>
                                </li>
                                <li>
                                    <a href="https://cdn.79tian.com/api/wxapi/view/index.php" target="_blank">授权管理</a>
                                </li>
                                <li>
                                    <a href="./admin.update.php">系统升级</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="dripicons-view-apps"></i>
                        <span> 商品相关 </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">商品管理
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.goods.add.php">添加商品</a>
                                </li>
                                <li>
                                    <a href="./admin.goods.list.php">商品列表</a>
                                </li>
                                <li>
                                    <a href="./admin.goods.sort.php">商品排序</a>
                                </li>
                                <li>
                                    <a href="./admin.goods.monitoring.php">商品监控</a>
                                </li>
                                <li>
                                    <a href="../?mod=UpAndDown" target="_blank">价格波动</a>
                                </li>
                                <li>
                                    <a href="./admin.goods.dockingSystem.php">一键串货</a>
                                </li>
                            </ul>
                        </li>
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">分类管理
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.class.add.php">添加分类</a>
                                </li>
                                <li>
                                    <a href="./admin.class.list.php">分类列表</a>
                                </li>
                            </ul>
                        </li>
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">货源管理
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.source.add.php">添加货源</a>
                                </li>
                                <li>
                                    <a href="./admin.source.list.php">货源列表</a>
                                </li>
                                <li>
                                    <a href="./admin.source.ip.php">代理IP</a>
                                </li>
                                <li>
                                    <a href="./admin.source.promotion.php">货源推广</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="./admin.goods.supply.php">供货大厅</a>
                        </li>
                        <li>
                            <a href="./admin.goods.express.php">运费模板</a>
                        </li>
                        <li>
                            <a href="./admin.InputValidation.php">输入规则</a>
                        </li>
                        <li>
                            <a href="./admin.goods.token.php">卡密库存</a>
                        </li>
                    </ul>
                </li>

                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="layui-icon layui-icon-transfer"></i>
                        <span> 订单相关</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li>
                            <a href="./admin.order.list.php">订单列表</a>
                        </li>
                        <li>
                            <a href="./admin.docking.log.php">对接日志</a>
                        </li>
                        <li>
                            <a href="./admin.order.derive.php">订单导出</a>
                        </li>
                        <li>
                            <a href="./admin.order.pay.php">支付订单</a>
                        </li>
                    </ul>
                </li>

                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="layui-icon layui-icon-username"></i>
                        <span> 用户相关</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li>
                            <a href="./admin.user.add.php">添加用户</a>
                        </li>
                        <li>
                            <a href="./admin.user.list.php">用户列表</a>
                        </li>
                        <li>
                            <a href="./admin.user.log.php">操作日志</a>
                        </li>
                        <li>
                            <a href="./tickets.php">售后工单</a>
                        </li>
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">等级配置
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.level.list.php">等级列表</a>
                                </li>
                                <li>
                                    <a href="./admin.level.add.php">新增等级</a>
                                </li>
                            </ul>
                        </li>

                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">加价规则
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.increasePrice.list.php">规则列表</a>
                                </li>
                                <li>
                                    <a href="./admin.increasePrice.add.php">新增规则</a>
                                </li>
                            </ul>
                        </li>

                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">充值卡密
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.recharge.add.php">生成充值卡</a>
                                </li>
                                <li>
                                    <a href="./admin.recharge.list.php">充值卡列表</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="./admin.user.set.php">用户配置</a>
                        </li>
                    </ul>
                </li>

                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="layui-icon layui-icon-read"></i>
                        <span> 公告通知 </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li>
                            <a href="admin.article.list.php">公告列表</a>
                        </li>
                        <li>
                            <a href="admin.article.add.php">发布公告</a>
                        </li>
                    </ul>
                </li>

                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="layui-icon layui-icon-senior"></i>
                        <span> 宝塔分销 </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li>
                            <a href="admin.server.add.php">添加服务器</a>
                        </li>
                        <li>
                            <a href="admin.server.list.php">服务器列表</a>
                        </li>
                        <li>
                            <a href="admin.host.list.php">主机列表</a>
                        </li>
                        <li>
                            <a href="admin.host.set.php">主机配置</a>
                        </li>
                    </ul>
                </li>

                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="layui-icon layui-icon-gift"></i>
                        <span> 活动推广</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">优惠券管理
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.coupon.add.php">添加优惠券</a>
                                </li>
                                <li>
                                    <a href="./admin.coupon.list.php">优惠券列表</a>
                                </li>
                                <li>
                                    <a href="./admin.coupon.set.php">优惠券配置</a>
                                </li>
                            </ul>
                        </li>
                    </ul>

                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">限购秒杀
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.seckill.add.php">添加活动</a>
                                </li>
                                <li>
                                    <a href="./admin.seckill.list.php">活动列表</a>
                                </li>
                            </ul>
                        </li>
                    </ul>

                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">商品兑换卡
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.goods.cash.add.php">添加兑换卡</a>
                                </li>
                                <li>
                                    <a href="./admin.goods.cash.list.php">管理兑换卡</a>
                                </li>
                            </ul>
                        </li>
                    </ul>

                </li>

                <li class="side-nav-item">
                    <a href="javascript: void(0);" class="side-nav-link">
                        <i class="layui-icon layui-icon-set"></i>
                        <span> 站点配置 </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul class="side-nav-second-level" aria-expanded="false">
                        <li>
                            <a href="./admin.notice.set.php">全局公告</a>
                        </li>
                        <li>
                            <a href="./admin.Payments.php">支付配置</a>
                        </li>
                        <li>
                            <a href="./admin.template.set.php">网站模板</a>
                        </li>
                        <li>
                            <a href="./admin.sms.set.php">短信通知</a>
                        </li>
                        <li>
                            <a href="./admin.app.set.php">网站配置</a>
                        </li>
                        <li>
                            <a href="./admin.login.set.php">登录配置</a>
                        </li>
                        <li>
                            <a href="./admin.api.set.php">节点配置</a>
                        </li>
                        <li>
                            <a href="./admin.monitoring.list.php">监控中心</a>
                        </li>
                        <li class="side-nav-item">
                            <a href="javascript: void(0);" aria-expanded="false">APP配置
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-third-level" aria-expanded="false">
                                <li>
                                    <a href="./admin.apps.list.php">生成列表</a>
                                </li>
                                <li>
                                    <a href="./admin.apps.set.php">相关配置</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="side-nav-item">
                    <a href="admin.store.php" class="side-nav-link">
                        <i class="layui-icon layui-icon-component"></i>
                        <span> 应用商店 </span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="?Loggedout=1" class="side-nav-link">
                        <i class="layui-icon layui-icon-logout"></i>
                        <span> 退出登陆 </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="content-page">
        <div class="content">
            <!-- Topbar Start -->
            <div class="navbar-custom">
                <ul class="list-unstyled topbar-right-menu float-right mb-0">
                    <li class="dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none" target="_blank"
                           href="https://cdn.79tian.com/api/wxapi/view/flock.php" role="button" aria-haspopup="false"
                           aria-expanded="false">
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
                <button class="button-menu-mobile open-left disable-btn">
                    <i class="layui-icon layui-icon-spread-left"></i>
                </button>
                <div class="app-search">
                    <form>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dw" placeholder="输入下单信息搜索订单">
                            <span class="layui-icon layui-icon-search"></span>
                            <div class="input-group-append">
                                <button class="btn btn-primary"
                                        onclick="window.location.href='admin.order.list.php?val='+$('#dw').val()"
                                        type="button">搜索
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row AppOn" style="width: 98%;margin: auto">
                <div class="col-12">
                    <?php if ($tise == 'index') { ?>
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <form class="form-inline">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-light" id="HomeDate"/>
                                            <div class="input-group-append">
                                                    <span class="input-group-text bg-primary border-primary text-white">
                                                        <i class="layui-icon layui-icon-search font-13"></i>
                                                    </span>
                                                <a href="javascript: vm.DataAnalysis(2)" class="btn btn-primary ml-2">
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
