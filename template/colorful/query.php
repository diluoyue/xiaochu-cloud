<?php $User = login_data::user_data(); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no,user-scalable=0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta http-equiv="cache-control" content="max-age=30"/>
    <meta name="renderer" content="webkit"/>
    <title><?= $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <meta name="robots" content="all">
    <meta name="generator" content="Microsoft">
    <meta name="revisit-after" content="1 days">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layui/css/layui.css"/>
    <link href="<?php echo $cdnpublic; ?>mdui/1.0.1/css/mdui.min.css" rel="stylesheet">
    <link type="text/css" href="<?php echo $cdnserver; ?>assets/template/colorful/assets/css/argon.css"
          rel="stylesheet">
    <link href="<?php echo $cdnpublic; ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnpublic; ?>jqPlot/1.0.9/jquery.jqplot.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?= ROOT_DIR ?>assets/favicon.ico" type="image/x-icon"/>
    <style>
        .icon {
            width: 1em;
            height: 1em;
            vertical-align: -0.15em;
            fill: currentColor;
            overflow: hidden;
        }

        .imagesr {
            width: 100%;
            height: 450px;
            text-align: center;
        }

        .zoomify {
            cursor: pointer;
            cursor: -webkit-zoom-in;
            cursor: zoom-in
        }

        .zoomify.zoomed {
            cursor: -webkit-zoom-out;
            cursor: zoom-out;
            padding: 0;
            margin: 0;
            border: none;
            border-radius: 0;
            box-shadow: none;
            position: relative;
            z-index: 1501
        }

        .zoomify-shadow {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            display: block;
            z-index: 1500;
            background: rgba(0, 0, 0, .3);
            opacity: 0
        }

        .zoomify-shadow.zoomed {
            opacity: 1;
            cursor: pointer;
            cursor: -webkit-zoom-out;
            cursor: zoom-out
        }

        .mdui-textfield-label {
            display: unset;
        }

        .mdui-explode {
            height: 12px;
        }

        .mdui-card {
            border-radius: 8px;
        }

        .sideImg {
            position: relative;
            width: 100%;
            height: 150px;
            background-position: center center;
            background-size: cover;
        }

        .side-info-head {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 65px;
            height: 65px;
            border-radius: 65px;
            background-position: center center;
            background-size: cover;
        }

        .side-info-more {
            position: absolute;
            bottom: 0;
            left: 0;
            box-sizing: border-box;
            padding: 20px;
            color: #fff;
            font-size: 17px;
        }

        .side-info-oth {
            color: hsla(0, 0%, 100%, .7);
            font-size: 13px;
        }

        ::-webkit-scrollbar {
            width: 6px;
            background-color: transparent;
        }

        ::-webkit-scrollbar-thumb {
            -webkit-border-radius: 8px;
            background-color: rgba(0, 0, 0, .16);
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0, 0, 0, .2);
        }

        .panel {
            border-radius: 10px;
            border-color: #cfdbe2
        }

        .panel-title {
            font-size: 15px;
            color: #fff;
        }

        .panel-heading {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        body {
            font-family: "微软雅黑";
        }

        @charset "UTF-8";

        @font-face {
            font-family: toast;
            src: url(/public/Index/toast/fonts/toast.eot?76tjxy);
            src: url(/public/Index/toast/fonts/toast.eot?76tjxy#iefix) format("embedded-opentype"), url(/public/Index/toast/fonts/toast.ttf?76tjxy) format("truetype"), url(/public/Index/toast/fonts/toast.woff?76tjxy) format("woff"), url(/public/Index/toast/fonts/toast.svg?76tjxy#toast) format("svg");
            font-weight: 400;
            font-style: normal
        }

        i.toast-icon {
            font-family: toast !important;
            speak: none;
            font-style: normal;
            font-weight: 400;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale
        }

        .toast-icon-error:before {
            content: ""
        }

        .toast-icon-info:before {
            content: ""
        }

        .toast-icon-notice:before {
            content: ""
        }

        .toast-icon-success:before {
            content: ""
        }

        .toast-icon-warning:before {
            content: ""
        }

        .toast-item-wrapper {
            min-width: 250px;
            padding: 10px;
            box-sizing: border-box;
            color: #fff;
            overflow: hidden;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none
        }

        .toast-item-wrapper i.toast-icon {
            position: absolute;
            top: 12px;
            left: 0;
            width: 50px;
            text-align: center;
            vertical-align: middle;
            font-size: 2rem
        }

        .toast-item-wrapper .toast-close {
            font-size: 1.2rem;
            position: absolute;
            top: 0;
            right: 0;
            width: 20px;
            text-align: center;
            cursor: pointer
        }

        .toast-item-wrapper.success {
            background-color: #29ab9f;
            border: 1px solid #1a9581
        }

        .toast-item-wrapper.error {
            background-color: #ff7946;
            border: 1px solid #f35818
        }

        .toast-item-wrapper.warning {
            background-color: #fff1c0;
            border: 1px solid #f0c948;
            color: #333
        }

        .toast-item-wrapper.notice {
            background-color: #48a9f8;
            border: 1px solid #208ce4
        }

        .toast-item-wrapper.info {
            background-color: #7f97a3;
            border: 1px solid #6b8699
        }

        .toast-item-wrapper.toast-top-left {
            left: 20px;
            top: 20px
        }

        .toast-item-wrapper.toast-top-right {
            right: 20px;
            top: 20px
        }

        .toast-item-wrapper.toast-top-center {
            margin: 0 auto;
            top: 20px
        }

        .toast-item-wrapper.toast-bottom-left {
            left: 20px;
            bottom: 20px
        }

        .toast-item-wrapper.toast-bottom-right {
            right: 20px;
            bottom: 20px
        }

        .toast-item-wrapper.toast-bottom-center {
            margin: 0 auto;
            bottom: 20px
        }

        .toast-item-wrapper.fullscreen {
            left: 20px;
            right: 20px;
            width: calc(100% - 40px)
        }

        .toast-item-wrapper p {
            margin: 0
        }

        .toast-item-wrapper .toast-message {
            font-size: .87rem
        }

        .toast-item-wrapper .toast-progress {
            width: 0;
            height: 3px;
            background-color: rgba(0, 0, 0, .5);
            position: absolute;
            bottom: 0;
            right: 0
        }

        .toast-item-wrapper.rtl {
            direction: rtl;
            text-align: right
        }

        .toast-item-wrapper.rtl i.toast-icon {
            left: auto;
            right: 0
        }

        .toast-item-wrapper.rtl .toast-close {
            right: auto;
            left: 0
        }

        .toast-item-wrapper.rtl p {
            text-align: right
        }

        .toast-item-wrapper.rtl .toast-progress {
            left: auto;
            right: 0
        }

        .form-control {
            display: block;
            width: 100%;
            height: 100%;
            padding: 7.5px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #ccc;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
            -webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
            -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
            transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
        }

        .form-control:focus {
            border-color: #66afe9;
            outline: 0;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6)
        }

        .form-control::-moz-placeholder {
            color: #999;
            opacity: 1
        }

        .form-control:-ms-input-placeholder {
            color: #999
        }

        .form-control::-webkit-input-placeholder {
            color: #999
        }

        .form-control::-ms-expand {
            background-color: transparent;
            border: 0
        }

        .form-control[disabled],
        .form-control[readonly],
        fieldset[disabled] .form-control {
            background-color: #eee;
            opacity: 1
        }

        @media (min-width: 0px) {
            .shopimg {
                height: 160px;
            }
        }

        @media (min-width: 500px) {
            .shopimg {
                height: 200px;
            }
        }

        .addclass,
        .kuaishou_select {
            border: 3px solid #5ccdde;
        }

        .addclass_black {
            border: 3px solid #000;
        }

        .addclass_red {
            color: red;
        }

        .addclass_pink {
            border: 3px solid pink;
        }

        .modal {
            z-index: 5999;
        }

        .gradient {
            background: linear-gradient(to right, #5ccdde, #C018F1, #7D1F9B, #F33BD9, #3BE750, #84048D, #81814E, #523273);
            background-size: 2000%;
            animation: gradientBackground 5s alternate ease-out;
            animation-iteration-count: infinite;
        }

        .gradient0 {
            background: linear-gradient(to right, #5ccdde, #639820, #4F6C70, #9E2A54, #CD3032, #1A0C31, #9E5912, #CE6FDC);
            background-size: 1967%;
            animation: gradientBackground 10s alternate ease-out;
            animation-iteration-count: infinite;
        }

        .gradient1 {
            background: linear-gradient(to right, #5ccdde, #15225F, #A686C3, #BDF7E0, #72E094, #38E678, #81C023, #3F242B);
            background-size: 1888%;
            animation: gradientBackground 10s alternate ease-out;
            animation-iteration-count: infinite;
        }

        .gradient2 {
            background: linear-gradient(to right, #5ccdde, #E061D9, #F391E4, #E01950, #00CE25, #2D6C4D, #967110, #19B462);
            background-size: 1941%;
            animation: gradientBackground 8s alternate ease-out;
            animation-iteration-count: infinite;
        }

        .gradient3 {
            background: linear-gradient(to right, #5ccdde, #B0DF58, #C3D81B, #12D9D4, #122B27, #B0CCF6, #2250D0, #9B4F5E);
            background-size: 1559%;
            animation: gradientBackground 10s alternate ease-out;
            animation-iteration-count: infinite;
        }

        .gradient4 {
            background: linear-gradient(to right, #5ccdde, #2AB61C, #AD0F17, #AF40CF, #D257E7, #9908EA, #F7A438, #0C6429);
            background-size: 1724%;
            animation: gradientBackground 9s alternate ease-out;
            animation-iteration-count: infinite;
        }

        .gradient5 {
            background: linear-gradient(to right, #5ccdde, #E04989, #EBABEE, #0AAB89, #AC828C, #E44765, #EF644C, #09644B);
            background-size: 1519%;
            animation: gradientBackground 8s alternate ease-out;
            animation-iteration-count: infinite;
        }

        label {
            font-weight: unset;
        }

        tbody > tr > td {
            /*white-space: nowrap;*/
        }

        #desc_text img {
            max-width: 100%;
        }
    </style>
    <script>
        function isIe() {
            return ("ActiveXObject" in window);
        }

        if (isIe() == true) {
            setTimeout(function () {
                $("body").empty();
                document.write(`<!DOCTYPE html><html><body><h1>您使用的已经是过时的浏览器，请下载最新版<a href="https://www.google.cn/chrome/">Chrome浏览器</a></h1></body></html>`);
            }, 200);
        }
    </script>
</head>

<body class="mdui-drawer-body-left mdui-appbar-with-toolbar mdui-theme-primary-indigo mdui-theme-accent-indigo mdui-loaded ">
<div class="mdui-text-center" id="page-wrapper"
     style="display:none;position: fixed;top: 0;right: 0;bottom: 0;left: 0;width: 100%;height: 100%;background-color: #fff;z-index: 99999;">
    <div style="top:45%;width: 50px;height: 50px;" class="mdui-spinner mdui-spinner-colorful"></div>
</div>
<div class="mdui-appbar mdui-appbar-fixed">
    <div class="mdui-toolbar mdui-text-color-white"
         style="background:linear-gradient(to left, rgb(240,101,235), #5ccdde 100%); left: 0px;">
        <a href="javascript:" class="mdui-btn mdui-btn-icon mdui-ripple" mdui-drawer="{target:'#drawer',swipe:false}"><i
                    class="mdui-icon material-icons">&#xe5d2;</i></a>
        <a style="margin: 0 10px;" href="./" class="mdui-typo-headline"><?= $conf['sitename'] ?></a>
        <div class="mdui-toolbar-spacer"></div>
        <?php if ($User == false) { ?>
            <span style="margin-right:7px;width: 40px;min-width: 40px;height: 40px;"
                  class="mdui-btn mdui-btn-icon mdui-ripple-white" onclick="window.location.href='./?mod=route&p=User';"
                  mdui-tooltip="{content: '点击登录',position: 'left'}">
                    <i class="mdui-icon material-icons">&#xe853;</i></span>
        <?php } else { ?>
            <span style="margin-right:7px;width: 40px;min-width: 40px;height: 40px;"
                  class="mdui-btn mdui-btn-icon mdui-ripple-white" mdui-menu="{target: '#user_menu'}"
                  mdui-tooltip="{content: '<?= $User['qq'] ?>',position: 'left'}">
                    <img id="headface" src="https://q4.qlogo.cn/headimg_dl?dst_uin=<?= $User['qq'] ?>&spec=100"
                         width="100%">
                </span>
            <ul class="mdui-menu" id="user_menu">
                <li class="mdui-menu-item">
                    <a href="./user/set.php" target="_block" class="mdui-ripple">
                        <i class="mdui-menu-item-icon mdui-icon material-icons">&#xe8a6;</i>我的信息
                    </a>
                </li>
                <li class="mdui-menu-item">
                    <a href="javascript:" class="mdui-ripple">
                        <i class="mdui-menu-item-icon mdui-icon material-icons">&#xe227;</i><?= $User['money'] ?>元
                    </a>
                </li>
                <li class="mdui-divider"></li>
                <li class="mdui-menu-item">
                    <a href="./user/?act=close">
                        <i class="mdui-menu-item-icon mdui-icon material-icons">&#xe899;</i>退出
                    </a>
                </li>
            </ul>
        <?php } ?>
    </div>
</div>
<div class="mdui-progress loading-progress" style="display:none;">
    <div class="mdui-progress-determinate loading-determinate" style="width: 2%;"></div>
</div>
<div class="mdui-drawer" id="drawer">
    <div class="sideImg" style="display: block;">
        <img src="<?php echo $cdnserver; ?>assets/template/colorful/assets/img/side_img.jpg" height="150px;"
             width="100%" style="filter: blur(4px);object-fit: cover;">
        <a target="_block" href="http://wpa.qq.com/msgrd?v=3&uin=<?= $conf['kfqq'] ?>&site=qq&menu=yes"
           class="side-info-head mdui-shadow-3 mdui-ripple"
           style="background-image:url(//q4.qlogo.cn/headimg_dl?dst_uin=<?= $conf['kfqq'] ?>&spec=100)"></a>
        <div class="side-info-more">QQ:<?= $conf['kfqq'] ?><br><span class="side-info-oth">本站站长兼客服</span></div>
    </div>
    <ul class="mdui-list" style="margin-bottom:53px;">
        <a class="mdui-list-item mdui-ripple" href="./">
            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-blue">&#xe854;</i>
            <div class="mdui-list-item-content">自助下单</div>
        </a>
        <?php if ($conf['FluctuationsPrices'] == 1) { ?>
            <a class="mdui-list-item mdui-ripple " target="_blank" href="?mod=UpAndDown">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-deep-purple-a200">&#xe043;</i>
                <div class="mdui-list-item-content">价格波动</div>
            </a>
        <?php } ?>
        <a class="mdui-list-item mdui-ripple mdui-list-item-active mdui-text-color-theme" href="./?mod=query">
            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-pink">&#xe8b6;</i>
            <div class="mdui-list-item-content">订单查询</div>
        </a>
        <?php if ($User == false) { ?>
            <a class="mdui-list-item mdui-ripple" href="./user/login.php">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-indigo">&#xe7ef;</i>
                <div class="mdui-list-item-content">登录/注册</div>
            </a>
        <?php } else { ?>
            <a class="mdui-list-item mdui-ripple " href="./user/index.php">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-purple">&#xe7ef;</i>
                <div class="mdui-list-item-content">用户中心</div>
            </a>
            <?php if ($conf['userleague'] == 1) { ?>
                <a class="mdui-list-item mdui-ripple "
                   href="./user/<?= ($User['grade'] < $conf['userleaguegrade'] ? 'grade.php' : 'management.php') ?>">
                    <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-pink">&#xe894;</i>
                    <div class="mdui-list-item-content"><?= ($User['grade'] < $conf['userleaguegrade'] ? '成为分销' : '我的店铺') ?></div>
                </a>
            <?php } ?>
            <a class="mdui-list-item mdui-ripple " href="./user/index.php?act=welfare">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-orange">&#xe150;</i>
                <div class="mdui-list-item-content">每日签到</div>
            </a>
            <a class="mdui-list-item mdui-ripple " href="./user/journal.php">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-cyan">&#xe263;</i>
                <div class="mdui-list-item-content">资金明细</div>
            </a>
            <a class="mdui-list-item mdui-ripple " href="./user/tickets.php">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-green">&#xe87f;</i>
                <div class="mdui-list-item-content">售后列表</div>
            </a>
            <a class="mdui-list-item mdui-ripple " href="./user/?act=close">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-indigo">&#xe899;</i>
                <div class="mdui-list-item-content">退出登录</div>
            </a>
        <?php } ?>
    </ul>
</div>
<div class="mdui-container" style="width:100%;">
    <div class="mdui-explode"></div>
    <div id="main">
        <style>
            @media (min-width: 300px) {
                .shopimg {
                }
            }

            @media (min-width: 769px) {
                .shopimg {
                    height: 200px;
                }
            }

            @media (min-width: 1366px) {
                .shopimg {
                    height: 220px;
                }
            }
        </style>
        <div class="mdui-panel">
            <?= ($conf['notice_check'] == '<p><br></p>' ? '' : $conf['notice_check']) ?>
        </div>
        <div class="mdui-explode"></div>
        <div class="mdui-panel" id="Appid">

            <div class="mdui-textfield mdui-textfield-floating-label">
                <label class="mdui-textfield-label">下单信息</label>
                <input class="mdui-textfield-input" v-model="seek" type="text" placeholder="请输入购买时填写的第一行下单信息"/>
            </div>
            <button style="width:50%;background: linear-gradient(to right, rgb(240, 101, 235), #5ccdde 100%); left: 0px;color:#fff;"
                    @click="GetOrderList(-2)" class="mdui-btn mdui-ripple">查询有绑订单
            </button>
            <button style="width:50%;background: linear-gradient(to left, rgb(240, 101, 235), #5ccdde 100%); left: 0px;color:#fff;"
                    @click="GetOrderList(-3)" class="mdui-btn mdui-ripple">查询游客订单
            </button>
            <div class="card" style="padding: 1em;">
                <div class="card-block">
                    <div class="bl_more">
                        <div class="text-left">
                            <ul class="pagination">
                                <li>
                                    <span class="rows">已读取 {{ OrderData.length }} 条订单</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="layui-row layui-col-space15">
                        <div class="layui-col-md4 layui-col-sm6" v-for="(item, index) in OrderData">
                            <div class="mdui-card"
                                 style="box-shadow: 3px 3px 16px #eee;padding: 0.5em;margin-bottom: 1em;border-radius: 0.5em;"
                            >
                                <div class="mdui-card-header">
                                    <img class="mdui-card-header-avatar" :src="item.image"/>
                                    <div class="mdui-card-header-title">状态:
                                        <span
                                                style="display: none;background-color: rgba(0,182,95,0.89)"
                                                :style="item.state==1?'display:inline-block;':'display: none;'"
                                                class="layui-badge">已完成</span>
                                        <span style="display: none;background-color: rgba(36,62,198,0.77)"
                                              :style="item.state==2?'display:inline-block;':'display: none;'"
                                              class="layui-badge">待处理</span>
                                        <span style="display: none;background-color: rgba(255,0,0,0.68)"
                                              :style="item.state==3?'display:inline-block;':'display: none;'"
                                              class="layui-badge">异常中</span>
                                        <span style="display: none;background-color: rgba(255,132,30,0.78)"
                                              :style="item.state==4?'display:inline-block;':'display: none;'"
                                              class="layui-badge">处理中</span>
                                        <span style="display: none;background-color: #A6A6A6"
                                              :style="item.state==5?'display:inline-block;':'display: none;'"
                                              class="layui-badge">已退款</span>
                                        <span style="display: none;background-color: #ff4254"
                                              :style="item.state==6?'display:inline-block;':'display: none;'"
                                              class="layui-badge">售后中</span>
                                        <span style="display: none;background-color: #18b50c"
                                              :style="item.state==7?'display:inline-block;':'display: none;'"
                                              class="layui-badge">已评价</span>
                                    </div>
                                    <div class="mdui-card-header-subtitle">
                                        单号：{{ item.order }}
                                    </div>
                                </div>
                                <div class="mdui-card-primary mdui-p-t-0">
                                    <div class="mdui-card-primary-title"><a :href="'./?mod=shop&gid=' + item.gid">{{item.name==null?'商品已删除':item.name}}</a>
                                    </div>
                                    <div v-if="item.coupon!=-1" class="mdui-card-primary-subtitle">订单金额：<font
                                                color="red"
                                                size="4">{{
                                            item.price }}元<span class="layui-word-aux"
                                                                style="font-size:0.8em;text-decoration:line-through;">{{ item.originalprice }}元</span></font>
                                    </div>
                                    <div v-else class="mdui-card-primary-subtitle">订单金额：<font color="red">{{ item.price
                                            +
                                            (item.payment != '积分兑换' ? '元' : item.currency) }}</font>
                                    </div>
                                    <div class="mdui-card-primary-subtitle">购买方式：{{ item.payment }}</div>
                                    <div class="mdui-card-primary-subtitle">付款时间：{{ item.addtime }}</div>
                                </div>
                                <div class="mdui-card-actions mdui-p-t-0">
                                    <button @click="GetQuery(item.id,item.order)"
                                            class="mdui-btn mdui-btn-raised mdui-btn-dense mdui-ripple mdui-color-red-a200">
                                        订单详情
                                    </button>
                                    <a target="_blank" :href="Tracking + item.logistics.order"
                                       class="mdui-btn mdui-btn-raised mdui-btn-dense mdui-ripple mdui-color-deep-purple-a200"
                                       v-if="item.logistics!=-1">{{item.logistics.name}}</a>
                                    <button class="mdui-btn mdui-btn-icon mdui-float-right"
                                            :onclick="'workOrder('+item.id+')'">
                                        <i class="mdui-icon material-icons">&#xe000;</i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div @click="GetOrderList(page + 1)" style="text-align:center">
                    <button class="mdui-btn mdui-btn-raised mdui-ripple">查看更多</button>
                </div>
            </div>
        </div>
        <a class="mdui-fab mdui-fab-fixed mdui-ripple mdui-color-pink" id="top_btn" style="display:none;"><i
                    class="mdui-icon material-icons">&#xe5d8;</i></a>
    </div>
</div>
<div style="height:56px;"></div>

<div class="mdui-panel" style="text-align: center;">
    <?= $conf['notice_bottom'] ?><br>
    <?= $conf['statistics'] ?>
</div>

<script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
<script src="<?php echo $cdnpublic; ?>jquery/3.4.1/jquery.min.js"></script>
<script src="<?php echo $cdnpublic; ?>mdui/0.4.1/js/mdui.min.js"></script>
<script src="<?php echo $cdnpublic; ?>jqPlot/1.0.9/jquery.jqplot.min.js"></script>
<script src="<?php echo $cdnpublic; ?>twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/colorful/assets/js/argon.js"></script>
<script src="<?php echo $cdnpublic; ?>jqueryui/1.12.1/jquery-ui.js"></script>
<script src="<?php echo $cdnpublic; ?>jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/colorful/assets/js/query.js?vs=<?= $accredit['versions'] ?>"></script>
<script>
    $(window).scroll(function () {
        if ($(window).scrollTop() > 388) {
            $("#top_btn").fadeIn(588);
        } else {
            $("#top_btn").fadeOut(288);
        }
    });

    $("#top_btn").click(function () {
        $('body,html').animate({
            scrollTop: 0,
        }, 688);
    });


    function workOrder(id) {
        mdui.alert("需要售后时可将单号复制给本站客服<br>或点我<a href='./user/tickets_new.php?id=" + id + "' style='color:blue'  target='_blank' >提交售后工单</a>,需先登录哦！", '温馨提示')
    }
</script>
</body>

</html>