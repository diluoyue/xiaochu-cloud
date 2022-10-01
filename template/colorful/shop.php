<?php $User = login_data::user_data(); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no,user-scalable=0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta http-equiv="cache-control" content="max-age=30"/>
    <meta name="renderer" content="webkit"/>
    <title>
        <?= $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?>
    </title>
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
    <link rel="stylesheet" href="<?= ROOT_DIR ?>assets/css/Global.css">
    <link href="<?php echo $cdnpublic; ?>Swiper/4.5.1/css/swiper.min.css" rel="stylesheet">
    <style>
        .icon {
            width: 1em;
            height: 1em;
            vertical-align: -0.15em;
            fill: currentColor;
            overflow: hidden;
        }

        img.logo {
            width: 22px;
            margin: -2px 5px 0 5px;
        }

        .swiper-slide {
            text-align: center;
        }

        .swiper-slide img {
            width: auto;
            max-height: 18em;
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

        .CouponCss {
            margin-bottom: 0.2em;
        }

        .CouponCss .layui-card {
            padding: 0;
            box-shadow: 3px 3px 12px #ddd;
            cursor: pointer;
            transition: all .6s;
            overflow: hidden;
            border-radius: 0.3em;
        }

        .CouponCss .layui-card:hover {
            box-shadow: 3px 3px 18px rgb(187, 184, 184);
        }

        .CouponCss img {
            width: 3em;
            height: 3em;
            display: block;
            margin: 0.5em auto;
        }

        .CouponCss ul {
            padding: 0;
            margin: 0;
        }

        .CouponCss ul li {
            font-size: 0.6em;
            color: rgba(0, 0, 0, 0.527);
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
        <a style="margin: 0 10px;" href="./" class="mdui-typo-headline">
            <?= $conf['sitename'] ?>
        </a>
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
                        <i class="mdui-menu-item-icon mdui-icon material-icons">&#xe227;</i>
                        <?= $User['money'] ?>元
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
        <div class="side-info-more">QQ:
            <?= $conf['kfqq'] ?><br><span class="side-info-oth">本站站长兼客服</span>
        </div>
    </div>
    <ul class="mdui-list" style="margin-bottom:53px;">
        <a class="mdui-list-item mdui-ripple mdui-list-item-active mdui-text-color-theme" href="./">
            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-blue">&#xe854;</i>
            <div class="mdui-list-item-content">自助下单</div>
        </a>
        <?php if ($conf['FluctuationsPrices'] == 1) { ?>
            <a class="mdui-list-item mdui-ripple " target="_blank" href="?mod=UpAndDown">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-deep-purple-a200">&#xe043;</i>
                <div class="mdui-list-item-content">价格波动</div>
            </a>
        <?php } ?>
        <a class="mdui-list-item mdui-ripple " href="?mod=query">
            <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-pink">&#xe8b6;</i>
            <div class="mdui-list-item-content">订单查询</div>
        </a>
        <?php if ($User == false) { ?>
            <a class="mdui-list-item mdui-ripple" href="./?mod=route&p=User">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-indigo">&#xe7ef;</i>
                <div class="mdui-list-item-content">登录/注册</div>
            </a>
        <?php } else { ?>
            <a class="mdui-list-item mdui-ripple " href="./?mod=route&p=User">
                <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-purple">&#xe7ef;</i>
                <div class="mdui-list-item-content">用户中心</div>
            </a>
            <?php if ($conf['userleague'] == 1) { ?>
                <a class="mdui-list-item mdui-ripple "
                   href="./user/<?= ($User['grade'] < $conf['userleaguegrade'] ? 'grade.php' : 'management.php') ?>">
                    <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-pink">&#xe894;</i>
                    <div class="mdui-list-item-content">
                        <?= ($User['grade'] < $conf['userleaguegrade'] ? '成为分销' : '我的店铺') ?>
                    </div>
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
                    height: 180px;
                }
            }

            @media (min-width: 769px) {
                .shopimg {
                    height: 320px;
                }
            }

            @media (min-width: 1366px) {
                .shopimg {
                    height: 380px;
                }
            }

            .mdui-select {
                width: 100%;
                font-size: 1.2rem;
                border: none;
                border-bottom: solid 1px #716377;
                margin-bottom: 0.4em;
            }

            .mdui-select-menu {
                overflow-y: hidden;
                overflow-y: auto;
            }

            .mdui-grid-tile-title font {
                margin-right: 0.5rem;
            }
        </style>
        <div class="mdui-explode"></div>
        <div class="mdui-panel">
            <?= ($conf['notice_top'] == '<p><br></p>' ? '' : $conf['notice_top']) ?>
        </div>
        <div class="mdui-explode"></div>
        <div class="mdui-row" id="Appid">
            <div style="display: none" :style="Goods.name!=-1?'display: ':'display: none'">
                <div class="card-header bg-transparent" style="border-bottom: none;">
                    <a :href="'./?cid='+ Goods.cid"
                       class="mdui-btn mdui-btn-raised mdui-btn-dense mdui-color-theme-accent">返回分类</a>
                </div>
                <div class="mdui-col-xs-12 mdui-col-sm-5 mdui-p-a-3">
                    <div class="mdui-grid-tile">
                        <a href="javascript:"><img class="shopimg mdui-shadow-8" @click="ShareGoods"
                                                   style="object-fit: cover;/*margin-bottom:57px;*/"
                                                   :src="Goods.image[0]"/></a>
                        <div class="mdui-grid-tile-actions mdui-grid-tile-actions-top" v-if="Goods.Seckill !== -1"
                             :class="Goods.Seckill.state === 1 && Goods.Seckill.attend < Goods.Seckill.astrict?' mdui-color-red':' mdui-color-grey-700'"
                        >
                            <div class="mdui-grid-tile-text">
                                <div class="mdui-grid-tile-title"
                                     v-if="Goods.Seckill.state === 1 && Goods.Seckill.attend < Goods.Seckill.astrict">
                                    限购秒杀活动将于{{Goods.Seckill.end}}
                                </div>
                                <div class="mdui-grid-tile-title"
                                     v-else-if="Goods.Seckill.state === 1 && Goods.Seckill.attend >= Goods.Seckill.astrict">
                                    限购秒杀活动参与人数已达上限
                                </div>
                                <div class="mdui-grid-tile-title" v-else>
                                    限购秒杀活动将于{{Goods.Seckill.start}}
                                </div>
                            </div>
                        </div>

                        <div class="mdui-grid-tile-actions" style="background:rgba(255,255,255,.5)">
                            <div class="mdui-grid-tile-text">
                                <div class="mdui-grid-tile-title">
                                    <font v-if="Goods.state == 3" color="#FF0FFF">
                                        发卡
                                    </font>
                                    <font v-else v-for="(item, index) in Goods.label" :color="item.color">
                                        {{ item.name }}
                                    </font>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-sm-7 mdui-p-a-3">
                    <h5>商品名称：
                        <div style="width:60%;display: inline-block"><select
                                    class="mdui-select" style="width: 100%;" mdui-select id="Pushs"></select></div>
                    </h5>
                    <h5 class="mdui-m-t-2" v-if="Goods.Seckill === -1">购买价格：
                        <font v-if="Goods.price == 0 && Goods.points == 0" color="#18B566">免费</font>
                        <font v-else>
                            <font color="red" v-if="Goods.method == 1 || Goods.method == 2">
                                {{ NumRound(Goods.price * num) }} 元
                            </font>

                            <font v-else color="#ffaa00">
                                {{ Goods.points * num }}
                                <font style="font-size: 0.72rem;margin-left: 0.2rem;">{{ Goods.currency }}</font>
                            </font>
                        </font>
                    </h5>
                    <h5 class="mdui-m-t-2" v-else>购买价格：
                        <font v-if="Goods.price == 0 && Goods.points == 0" color="#18B566">免费</font>
                        <font v-else>
                            <font color="red" v-if="Goods.method == 1 || Goods.method == 2">
                                {{ (NumRound(Goods.price) - NumRound(Goods.price * Goods.Seckill.depreciate) /
                                100).toFixed(Goods.accuracy) * num }}元
                                <span style="color: #9e9e9e;text-decoration:line-through;font-size:14px;margin-left: 4px;">
                                    {{ NumRound(Goods.price * num) }}
                                </span>
                            </font>

                            <font v-else color="#ffaa00">
                                {{ (Goods.points - (Goods.points * Goods.Seckill.depreciate) / 100).toFixed(0) * num }}
                                {{ Goods.currency }}
                                <span style="color: #9e9e9e;text-decoration:line-through;font-size:14px;margin-left: 4px;">{{ Goods.points * num }}{{ Goods.currency }}</span>
                            </font>
                        </font>
                    </h5>
                    <h5 style="display: none;" :style="CouponData.length>=1?'display:block;':'display: none;'"
                        class="mdui-m-t-2">商品福利：
                        <div class="mdui-chip mdui-ripple mdui-hoverable"
                             style="margin-bottom: -0.5em;margin-left:-0.3em" @click="Coupon">
                            <span class="mdui-chip-icon mdui-color-red-accent"><i class="mdui-icon material-icons">receipt</i></span>
                            <span class="mdui-chip-title">{{ CouponData.length }}个优惠券待领取</span>
                        </div>
                    </h5>
                    <h5 class="mdui-m-t-2" v-if="Goods.Seckill !== -1">活动内容：
                        {{'降价 ' + Goods.Seckill.depreciate + '%'}}
                    </h5>
                    <h5 class="mdui-m-t-2" v-if="Goods.Seckill !== -1">活动名额：
                        {{'还剩 ' + (Goods.Seckill.astrict - Goods.Seckill.attend) + '个名额'}}
                    </h5>
                    <h5 class="mdui-m-t-2">购买数量：
                        {{Goods.quantity * num}} {{Goods.units}}
                    </h5>
                    <h5 class="mdui-m-t-2">我的身份：<font v-html="Goods.level"></font> <i
                                class="mdui-icon material-icons"
                                style="font-size: 1.4rem;color:#7E57C2;margin-bottom: 0.1rem;" id="level"
                                @click="LeveLs">&#xe887;</i></h5>
                    <h5 class="mdui-m-t-2">剩余库存：{{Goods.quota}}份
                    </h5>

                    <div class="mdui-textfield">

                        <label class="mdui-slider" v-if="min<max">
                            <input type="range" :step="step" :min="min" :max="max"
                                   @input="PriceLs" v-model.number="num"/>
                        </label>

                        <label class="mdui-textfield-label">下单份数</label>
                        <input class="mdui-textfield-input" @input="PriceLs()" v-else type="number"
                               v-model.number="num"/>
                    </div>

                    <div v-for="(item, index) of Goods.input" :key="index">
                        <div v-if="item.state == 1" class="mdui-textfield mdui-textfield-floating-label">
                            <label class="mdui-textfield-label">{{ item.Data }}</label>
                            <textarea class="mdui-textfield-input" v-model="form[index]"
                                      :placeholder="'请将' + item.Data + '填写完整!'"></textarea>
                        </div>

                        <div v-if="item.state == 2" class="mdui-textfield mdui-textfield-floating-label">
                            <label class="mdui-textfield-label">{{ item.Data[0] }}</label>
                            <textarea class="mdui-textfield-input" v-model="form[index]"
                                      @blur="extend(index, item.Data[1].way, item.Data[1].url,2)"
                                      :placeholder="item.Data[1].placeholder"></textarea>
                            <div style="z-index: 10;" class="mdui-textfield-helper mdui-text-color-deep-purple"
                                 @click="extend(index, item.Data[1].way, item.Data[1].url)">{{ item.Data[1].name}}
                            </div>
                        </div>

                        <div v-if="item.state == 3" class="mdui-textfield mdui-textfield-floating-label">
                            <label class="mdui-textfield-label">{{ item.Data }}</label>
                            <textarea class="mdui-textfield-input" v-model="form[index]"
                                      :placeholder="'请将' + item.Data + '填写完整!'"></textarea>
                            <div style="z-index: 10;" class="mdui-textfield-helper mdui-text-color-deep-purple"
                                 id="Selectaddress" :index="index">选择地址
                            </div>
                        </div>

                        <div v-if="item.state == 4" class="mdui-textfield mdui-textfield-floating-label">
                            <label class="mdui-textfield-label">{{ item.Data[0] }}</label>
                            <select v-model="form[index]" class="mdui-select">
                                <option v-for="o in item.Data[1]" :value="o">{{ o }}</option>
                            </select>
                        </div>

                        <div class="mdui-textfield mdui-textfield-floating-label" v-if="item.state == 5">
                            <div style="color: red;"
                                 v-if="Object.values(Combination).length === 0">
                                商品下单参数未配置完善，请联系客服处理！
                            </div>

                            <div v-if="Object.values(Combination).length >= 1" class="layui-card-body"
                                 style="padding:0;">
                                <div v-for="(item, index) in SkuBtn">
                                    <label class="mdui-textfield-label">{{ index }}</label><br>
                                    <div style="display:inline-block;" v-for="(ts, is) in item" :key="is">
                                        <button
                                                type="button"
                                                v-if="FormSp[index] == is && ts.type == 1"
                                                class="mdui-btn mdui-btn-raised mdui-color-deep-purple-a200 mdui-btn-dense mdui-ripple mdui-m-a-1"
                                                @click="BtnClick(index, is, ts.type)"
                                        >
                                            {{ is }}
                                        </button>
                                        <button
                                                type="button"
                                                v-else-if="FormSp[index] != is && ts.type == 1"
                                                class="mdui-btn mdui-btn-raised mdui-btn-dense mdui-ripple mdui-m-a-1"
                                                @click="BtnClick(index, is, ts.type)"
                                        >
                                            {{ is }}
                                        </button>
                                        <button
                                                type="button"
                                                v-else
                                                class="mdui-btn mdui-btn-raised mdui-btn-dense mdui-color-grey-300 mdui-ripple mdui-m-a-1"
                                                @click="BtnClick(index, is, ts.type)"
                                        >
                                            {{ is }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="item.state == 6" class="mdui-textfield mdui-textfield-floating-label">
                            <label class="mdui-textfield-label">{{ item.Data[0] }}</label>
                            <textarea class="mdui-textfield-input" v-model="form[index]"
                                      :placeholder="item.Data[1].placeholder"></textarea>
                            <div style="z-index: 10;" class="mdui-textfield-helper mdui-text-color-deep-purple"
                                 id="QrId" :url="item.Data[1].url" :index="index">{{ item.Data[1].name}}
                            </div>
                        </div>

                    </div>
                    <button @click="submit"
                            class="mdui-btn mdui-btn-block mdui-color-red mdui-btn-raised mdui-ripple mdui-m-t-2"
                            style="background: linear-gradient(to right, rgb(240, 101, 235), rgb(92, 205, 222) 100%);">
                        购买商品
                    </button>
                </div>
            </div>
            <div class="mdui-panel editor" v-if="Goods.docs!=''&&Goods.docs!='<p><br></p>'" mdui-panel>
                <div class="mdui-panel-item mdui-panel-item-open">
                    <div class="mdui-panel-item-header mdui-p-a-2 mdui-p-r-2">商品说明</div>
                    <div class="mdui-panel-item-body mdui-p-l-2 mdui-p-r-2" v-html="Goods.docs">
                    </div>
                </div>
            </div>

            <div class="mdui-dialog" id="CouponMsg">
                <div class="mdui-dialog-title">优惠券列表</div>
                <div class="mdui-dialog-content">
                    <ul class="mdui-list">
                        <li class="mdui-list-item mdui-ripple" v-for="(item,index) in CouponData"
                            @click="CouponAdd(item.limit_token)">
                            <div class="mdui-list-item-avatar mdui-color-white">
                                <img v-if="item.type==1" src="<?= ROOT_DIR ?>assets/img/coupon_1.png"
                                     style="border-radius:0"/>
                                <img v-if="item.type==2" src="<?= ROOT_DIR ?>assets/img/coupon_2.png"
                                     style="border-radius:0"/>
                                <img v-if="item.type==3" src="<?= ROOT_DIR ?>assets/img/coupon_3.png"
                                     style="border-radius:0"/>
                            </div>
                            <div class="mdui-list-item-content">
                                <div v-if="item.type==1" class="mdui-list-item-title mdui-text-color-blue">{{
                                    item.name }}
                                </div>
                                <div v-if="item.type==2" class="mdui-list-item-title mdui-text-color-deep-orange-a400">
                                    {{ item.name }}
                                </div>
                                <div v-if="item.type==3" class="mdui-list-item-title mdui-text-color-amber">{{
                                    item.name }}
                                </div>
                                <div class="mdui-list-item-title mdui-text-color-deep-purple-a100">{{
                                    item.scope }}
                                </div>
                                <div v-if="item.type==1" class="mdui-list-item-text mdui-text-color-blue">满{{
                                    item.minimum }}元可使用此券抵扣{{ item.money }}元
                                </div>
                                <div v-if="item.type==2" class="mdui-list-item-text mdui-text-color-red">
                                    无门槛,下单即可抵扣{{ item.money }}元
                                </div>
                                <div v-if="item.type==3" class="mdui-list-item-text mdui-text-color-amber">满{{
                                    item.minimum }}元可使用此券获得{{ item.money/10 }}折优惠
                                </div>
                                <div class="mdui-list-item-text">{{ item.content }}</div>
                                <div v-if="item.count>=1" class="mdui-list-item-text mdui-text-color-red">持有{{
                                    item.count }}张
                                </div>
                            </div>
                            <button class="layui-btn layui-btn-sm mdui-btn-raised mdui-color-red-accent mdui-hidden-xs-down mdui-ripple">
                                {{
                                item.count>=1?'再领一张':'领取' }}
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="mdui-dialog-actions">
                    <button class="mdui-btn mdui-ripple" mdui-dialog-cancel>关闭列表</button>
                </div>
            </div>

        </div>
    </div>
    <div style="height:56px;"></div>

    <div class="mdui-panel" style="text-align: center;">
        <?= $conf['notice_bottom'] ?><br>
        <?= $conf['statistics'] ?>
    </div>
    <script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
    <script src="<?php echo $cdnpublic; ?>Swiper/4.5.1/js/swiper.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>jquery/3.4.1/jquery.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>mdui/0.4.1/js/mdui.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>jqPlot/1.0.9/jquery.jqplot.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/colorful/assets/js/argon.js"></script>
    <script src="<?php echo $cdnpublic; ?>jqueryui/1.12.1/jquery-ui.js"></script>
    <script src="<?php echo $cdnpublic; ?>jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/js/axios.min.js"></script>
    <script src="./assets/js/distpicker.min.js"></script>
    <script>
		var gid = <?= (empty($_QET['gid']) ? -1 : $_QET['gid']) ?>;
    </script>
    <script src="./assets/template/colorful/assets/js/shop.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>
