<?php $UserData = login_data::user_data(); ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <!-- Vendor styles -->
    <link rel="icon" href="<?= ROOT_DIR ?>assets/favicon.ico" type="image/x-icon"/>
    <link href="<?php echo $cdnpublic; ?>material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css"
          rel="stylesheet">
    <link href="<?php echo $cdnpublic; ?>animate.css/3.7.2/animate.min.css" rel="stylesheet">
    <link href="<?php echo $cdnpublic; ?>jquery.scrollbar/0.2.11/jquery.scrollbar.css" rel="stylesheet">
    <link href="<?php echo $cdnpublic; ?>fullcalendar/4.0.0-alpha.4/fullcalendar.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layui/css/layui.css"/>
    <!-- App styles -->
    <link rel="stylesheet" href="<?php echo $cdnserver; ?>assets/template/cloud/assets/css/app.min.css">
    <link rel="stylesheet" href="<?php echo $cdnserver; ?>assets/template/cloud/assets/css/fz.min.css">
</head>

<body data-ma-theme="green" id="xiaoxuan_thmem_color" style="<?= background::image() ?>;">
<main class="main">
    <!-- 初始化加载 -->
    <div class="page-loader">
        <div class="page-loader__spinner">
            <svg viewBox="25 25 50 50">
                <circle cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
            </svg>
        </div>
    </div>

    <!-- 头部 -->
    <header class="header">
        <!-- 打开导航栏按钮 -->
        <div class="navigation-trigger hidden-xl-up" data-ma-action="aside-open" data-ma-target=".sidebar">
            <div class="navigation-trigger__inner">
                <i class="navigation-trigger__line"></i>
                <i class="navigation-trigger__line"></i>
                <i class="navigation-trigger__line"></i>
            </div>
        </div>

        <!-- 网站LOGO -->
        <div class="header__logo ">
            <h1><a href="./"><?= $conf['sitename'] ?></a></h1>
        </div>
        <!-- 头部搜索框 -->
        <div class="search">
            <div class="search__inner">
                <!-- <input type="text" class="search__text" id="query_goods" placeholder="输入商品关键词 [暂未开放搜索] ..." required/>
            <span id="doSearch" class="goodTypeChange"></span>
            <i class="zmdi zmdi-search search__helper" data-ma-action="search-close"></i> -->
            </div>
        </div>

        <!-- 左上角功能标签 -->
        <ul class="top-nav">
            <!-- <li class="hidden-xl-up"><a href="" data-ma-action="search-open"><i class="zmdi zmdi-search"></i></a></li> -->
            <li class="dropdown">
                <a onclick="$('.dropdown-menu-right').show()"><i class="zmdi zmdi-more-vert"></i></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-item theme-switch">
                        普通主题
                        <div class="btn-group btn-group-toggle btn-group--colors" data-toggle="buttons">
                            <label class="btn bg-green"><input type="radio" id="xiaoxuan_color" value="green"
                                                               autocomplete="off"></label>
                            <label class="btn bg-blue"><input type="radio" id="xiaoxuan_color" value="blue"
                                                              autocomplete="off"></label>
                            <label class="btn bg-red"><input type="radio" id="xiaoxuan_color" value="red"
                                                             autocomplete="off"></label>
                            <label class="btn bg-orange"><input type="radio" id="xiaoxuan_color" value="orange"
                                                                autocomplete="off"></label>
                            <label class="btn bg-teal"><input type="radio" id="xiaoxuan_color" value="teal"
                                                              autocomplete="off"></label>
                            <div class="clearfix mt-2"></div>
                            <label class="btn bg-cyan"><input type="radio" id="xiaoxuan_color" value="cyan"
                                                              autocomplete="off"></label>
                            <label class="btn bg-blue-grey"><input type="radio" id="xiaoxuan_color" value="blue-grey"
                                                                   autocomplete="off"></label>
                            <label class="btn bg-purple"><input type="radio" id="xiaoxuan_color" value="purple"
                                                                autocomplete="off"></label>
                            <label class="btn bg-indigo"><input type="radio" id="xiaoxuan_color" value="indigo"
                                                                autocomplete="off"></label>
                            <label class="btn bg-brown"><input type="radio" id="xiaoxuan_color" value="brown"
                                                               autocomplete="off"></label>
                            <div class="clearfix mt-2"></div>
                            <p>透明主题</p>
                            <label class="btn bg-transparent_1"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_1" autocomplete="off"></label>
                            <label class="btn bg-transparent_2"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_2" autocomplete="off"></label>
                            <label class="btn bg-transparent_3"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_3" autocomplete="off"></label>
                            <label class="btn bg-transparent_4"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_4" autocomplete="off"></label>
                            <label class="btn bg-transparent_5"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_5" autocomplete="off"></label>
                            <div class="clearfix mt-2"></div>
                            <label class="btn bg-transparent_6"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_6" autocomplete="off"></label>
                            <label class="btn bg-transparent_7"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_7" autocomplete="off"></label>
                            <label class="btn bg-transparent_8"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_8" autocomplete="off"></label>
                            <label class="btn bg-transparent_9"><input type="radio" id="xiaoxuan_color"
                                                                       value="transparent_9" autocomplete="off"></label>
                            <label class="btn bg-transparent_10"><input type="radio" id="xiaoxuan_color"
                                                                        value="transparent_10"
                                                                        autocomplete="off"></label>
                            <div class="clearfix mt-2"></div>
                            <p>渐变主题</p>
                            <label class="btn bg-transparent_11"><input type="radio" id="xiaoxuan_color"
                                                                        value="transparent_11"
                                                                        autocomplete="off"></label>
                            <label class="btn bg-transparent_12"><input type="radio" id="xiaoxuan_color"
                                                                        value="transparent_12"
                                                                        autocomplete="off"></label>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

    </header>
    <!-- 头部结束 -->

    <!-- 导航栏 -->
    <aside class="sidebar">
        <div class="scrollbar-inner">
            <!-- 用户信息 -->
            <?php if ($UserData == false) { ?>
                <div class="user">
                    <div class="user__info">
                        <img class="user__img" src=".\assets\template\cloud\assets\image\user.gif">
                        <div>
                            <div class="user__name" onclick="location.href='./?mod=route&p=User'">用户登录</div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="user">
                    <div class="user__info"
                         onclick="layer.msg('你好<?= $UserData['name'] ?><br>您当前拥有：<?= $UserData['currency'] . $conf['currency'] ?><br>剩余余额为：<?= round($UserData['money'], 2) ?>元！',{icon:1})">
                        <img class="user__img" src="<?= UserImage($UserData) ?>">
                        <div>
                            <div class="user__name"><?= round($UserData['money'], 2) ?>元</div>
                            <div class="user__name"><?= $UserData['currency'] . $conf['currency'] ?></div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <!-- 导航栏标签 -->
            <ul class="navigation">
                <li><a href="./"><i class="zmdi zmdi-home"></i> 在线商城</a></li>
                <?php if ($conf['FluctuationsPrices'] == 1) { ?>
                    <li><a href="./?mod=UpAndDown" target="_blank"><i class="zmdi zmdi-flattr"></i> 价格波动</a></li>
                <?php } ?>
                <li><a href="./?mod=query"><i class="zmdi zmdi-search"></i> 订单查询</a></li>
                <li><a href="./?mod=share"><i class="zmdi zmdi-share"></i> 推广有奖</a></li>
                <li><a href="./?mod=route&p=User"><i class="zmdi zmdi-account"></i> 用户后台</a></li>
                <li><a href="./?mod=article"><i class="zmdi zmdi-local-library"></i> 公告通知</a></li>
                <li <?php echo $conf['appurl'] == '' ? 'style="display: none"' : '' ?>><a href="<?= $conf['appurl'] ?>"
                                                                                          target="_blank"><i
                                class="zmdi zmdi-android-alt"></i> APP下载</a></li>
                <?php
                $navigation = explode('|', $conf['navigation']);
                foreach ($navigation as $v) {
                    $data_header = explode(',', $v);
                    echo '<li><a href="' . $data_header[1] . '" target="_blank"><i class="zmdi zmdi-flattr"></i> ' . $data_header[0] . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </aside>
    <!-- 导航栏结束 -->

    <section class="content">
        <div class="content__inner">