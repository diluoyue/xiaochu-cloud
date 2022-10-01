<?php

/**
 * cool模板
 */

use Medoo\DB\SQL;

if (!defined('IN_CRONLITE')) die;
$DB = SQL::DB();
$ClassData = $DB->select('class', '*', [
    'state' => 1,
    'ORDER' => [
        'sort' => 'DESC'
    ]
]);

?>
<!DOCTYPE html>
<html lang="zh"
      style="font-size: 102.4px;<?= background::image() == false ? 'background:linear-gradient(to right, #bdc3c7, #2c3e50);' : background::image() ?>">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover,user-scalable=no">
    <script>
        document.documentElement.style.fontSize = document.documentElement.clientWidth / 750 * 40 + "px";
    </script>
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-param" content="_csrf">
    <title><?= $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <meta name="viewport" content="width=device-width, viewport-fit=cover">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/foxui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/style.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/foxui.diy.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/iconfont.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/index.css">
    <script src="<?php echo $cdnpublic; ?>jquery/3.4.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="<?= ROOT_DIR ?>assets/favicon.ico" type="image/x-icon"/>
    <script src="<?php echo $cdnserver; ?>assets/template/cool/assets/js/swiper-3.3.1.min.js"
            type="text/javascript"></script>
    <link href="<?php echo $cdnserver; ?>assets/layuiadmin/layui/css/layui.css" rel="stylesheet"/>
</head>
<style type="text/css">
    body {
        position: absolute;;

        margin: auto;
    }

    .fui-page.fui-page-from-center-to-left,
    .fui-page-group.fui-page-from-center-to-left,
    .fui-page.fui-page-from-center-to-right,
    .fui-page-group.fui-page-from-center-to-right,
    .fui-page.fui-page-from-right-to-center,
    .fui-page-group.fui-page-from-right-to-center,
    .fui-page.fui-page-from-left-to-center,
    .fui-page-group.fui-page-from-left-to-center {
        -webkit-animation: pageFromCenterToRight 0ms forwards;
        animation: pageFromCenterToRight 0ms forwards;
    }
</style>
<style>
    .fix-iphonex-bottom {
        padding-bottom: 34px;
    }
</style>
<style>
    .goods_sort {
        position: relative;
        width: 100%;

        -webkit-box-align: center;
        padding: .4rem 0;
        background: #fff;
        -webkit-box-align: center;
        -ms-flex-align: center;
        -webkit-align-items: center;
    }

    .goods_sort:after {
        content: " ";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        border-bottom: 1px solid #e7e7e7;
    }

    .footer ul {
        display: flex;
        width: 80%;
        margin: 0 auto;
    }

    .footer ul li {
        list-style: none;
        flex: 1;
        text-align: center;
        position: relative;
        line-height: 2rem;
    }

    .footer ul li:after {
        content: '';
        position: absolute;
        right: 0;
        top: .8rem;
        height: 10px;
        border-right: 1px solid #999;


    }

    .footer ul li:nth-last-of-type(1):after {
        display: none;
    }

    .footer ul li a {
        color: #999;
        display: block;
        font-size: .6rem;
    }

    .goods_sort .item {
        position: relative;
        width: 1%;
        display: table-cell;
        text-align: center;
        font-size: 0.7rem;
        border-left: 1px solid #e7e7e7;
        color: #666;
    }

    .goods_sort .item .sorting {
        width: .2rem;
        position: relative;
        line-height: 1.5em;
        height: 2em;
    }

    .fui-goods-item .detail .price .buy {
        color: #fff;
        background: #1492fb;
        border-radius: 3px;
        line-height: 1.1rem;
    }

    .fui-goods-item .detail .sale {
        height: 1.7rem;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        font-size: 0.65rem;
        line-height: 0.9rem;
    }

    .goods_sort .item:first-child {
        border: 0;
    }

    .goods_sort .item.on .text {
        color: #fd5454;
    }

    .goods_sort .item .sorting .icon {
        /*font-size: 11px;*/
        position: absolute;
        -webkit-transform: scale(0.6);
        -ms-transform: scale(0.6);
        transform: scale(0.6);
    }

    .goods_sort .item-price .sorting .icon-sanjiao1 {
        top: .15rem;
        left: 0;
    }

    .goods_sort .item-price .sorting .icon-sanjiao2 {
        top: -.15rem;
        left: 0;
    }

    .goods_sort .item-price.DESC .sorting .icon-sanjiao1 {
        color: #ef4f4f
    }

    .goods_sort .item-price.ASC .sorting .icon-sanjiao2 {
        color: #ef4f4f
    }

    .fui-goods-item .detail .price .buy {
        color: #fff;
        background: #1492fb;
        border-radius: 3px;
        line-height: 1.1rem;
    }

    .goods-category {
        display: flex;
        background: #fff;
        flex-wrap: wrap;
    }

    .goods-category li {
        width: 25%;
        list-style: none;
        margin: 0.4rem 0;
        color: #666;
        font-size: 0.65rem;

    }

    .goods-category li.active p {
        background: #1492fb;
        color: #fff;
    }

    body {
        padding-bottom: constant(safe-area-inset-bottom);
        padding-bottom: env(safe-area-inset-bottom);
    }

    .goods-category li p {
        width: 4rem;
        height: 2rem;
        text-align: center;
        line-height: 2rem;
        border: 1px solid #ededed;
        margin: 0 auto;
        -webkit-border-radius: 0.1rem;
        -moz-border-radius: 0.1rem;
        border-radius: 0.1rem;
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

    .Coupons {
        position: fixed;
        bottom: 20%;
        right: 5%;
        width: 3rem;
        height: 3rem;
        z-index: 100;
        cursor: pointer;
        color: red;
        text-align: center;
        animation: Coupons 2s 0.2s linear infinite alternate;
    }

    .Coupons img {
        width: 3rem;
        height: 3rem;
    }

    @keyframes Coupons {
        0% {
            transform: scale(1);
        }

        25% {
            transform: scale(1.3);
        }

        50% {
            transform: scale(1);
        }

        75% {
            transform: scale(1.3);
        }
    }
</style>


<body style="overflow: auto;height: auto !important;max-width: 600px;">
<div id="body">
    <div class="fui-page-group " style="height: auto">
        <div class="fui-page  fui-page-current " style="height:auto; overflow: inherit">
            <div class="fui-content navbar" id="container" style="background-color: #fafafc;overflow: inherit">
                <div class="default-items">
                    <div class="fui-swipe">
                        <style>
                            .fui-swipe-page .fui-swipe-bullet {
                                background: #ffffff;
                                opacity: 0.5;
                            }

                            .fui-swipe-page .fui-swipe-bullet.active {
                                opacity: 1;
                            }
                        </style>
                        <div class="fui-swipe-wrapper" style="transition-duration: 500ms;">
                            <?php
                            $banner = explode('|', $conf['banner']);
                            foreach ($banner as $v) {
                                $image_url = explode('*', $v);
                                echo '<a class="fui-swipe-item" href="' . $image_url['1'] . '">
                                <img src="' . $image_url['0'] . '" style="display: block; width: 100%; height: auto;" />
                            </a>';
                            }
                            ?>
                        </div>
                        <div class="fui-swipe-page right round" style="padding: 0 5px; bottom: 5px; ">
                        </div>
                    </div>
                    <div class="fui-notice">
                        <div class="image">
                            <img src="<?php echo $cdnserver; ?>assets/template/cool/assets/picture/1571065042489353.jpg">
                        </div>
                        <div class="text" style="height: 1.2rem;line-height: 1.2rem">
                            <ul>
                                <li><a href="JavaScript:void(0)" onclick="$('.tzgg4').show()">
                                        <marquee behavior="alternate">
                                            <span style="color:red">平台24小时自助下单，欢迎代理加盟~</span>
                                        </marquee>
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="fui-searchbar bar">
                        <div class="searchbar center searchbar-active" style="padding-right:2.5rem">
                            <input type="button" class="searchbar-cancel searchbtn" value="搜索">
                            <div class="search-input" style="border: 0px;padding-left:0px;padding-right:0px;">
                                <i class="icon icon-search"></i>
                                <input type="text" value="" name="name"
                                       placeholder="输入商品关键字..."
                                       onBlur="vm.Search(this.value)">
                            </div>
                        </div>
                    </div>
                    <div class="device" style="padding-bottom:20px">
                        <div class="swiper-container list-guanggaowei-horizontal">
                            <div class="swiper-wrapper"
                                 style="transform: translate3d(0px, 0px, 0px); transition-duration: 0ms;">
                                <?php
                                $arry = 0;
                                $au = 1;
                                foreach ($ClassData as $v) {
                                    if (($arry / 10) == ($au - 1)) {
                                        echo '<div class="swiper-slide swiper-slide-visible swiper-slide-prev" data-swiper-slide-index="' . $au . '" style="width: 375px;margin: auto;margin-top: 0px;">
                                        <div class="content-slide">';
                                    }
                                    echo '<a href="?cid=' . $v['cid'] . '">
                                               <div class="mbg">
                                                   <p class="ico"><img src="' . $v['image'] . '" onerror="this.src=\'./assets/img/404.png\'"></p>
                                                   <p class="icon-title">' . $v['name'] . '</p>
                                              </div>
                                          </a>';
                                    if ((($arry + 1) / 10) == ($au)) {
                                        echo '</div>
                                        </div>';
                                        $au++;
                                        $_SESSION['au'] = $au;
                                    }
                                    $arry++;
                                    $_SESSION['arry'] = $arry;
                                }
                                if (floor((($_SESSION['arry']) / 10)) != (($_SESSION['arry']) / 10)) {
                                    echo '</div></div>';
                                }
                                ?>
                            </div>
                            <div class="pagination swiper-pagination-clickable swiper-pagination-bullets">
                                <span class="swiper-pagination-bullet"></span>
                            </div>
                        </div>
                        <script>
                            jQuery(function ($) {
                                $(window).resize(function () {
                                    var width = $('#js-com-header-area').width();
                                    $('.touchslider-item a').css('width', width);
                                    $('.touchslider-viewport').css('height', 200 * (width / 640));
                                }).resize();
                            });
                            if ($(".swiper-wrapper .content-slide").length > 1) {
                                $(".device").css("padding-bottom", "20px");
                                $(".pagination").show();
                                var mySwiper = new Swiper('.swiper-container', {
                                    pagination: '.pagination',
                                    loop: true,
                                    grabCursor: true,
                                    autoplay: 100000000,
                                    autoplayDisableOnInteraction: false,
                                    paginationClickable: true
                                })
                            }
                        </script>
                    </div>
                    <div style="height: 1px"></div>
                </div>
                <div class="fui-notice">
                    <div class="text" style="height: 1.2rem;line-height: 1.2rem">
                        <ul>
                            <li onclick="$('.tzgg5').show()"><a href="JavaScript:void(0)">
                                    <marquee direction="up" behavior="alternate">
                                        <div align="center">
                                            <font color="#FF0000">点</font>
                                            <font color="#D5002A">击</font>
                                            <font color="#AB0054">这</font>
                                            <font color="#81007E">里</font>
                                            <font color="#5700A8">查</font>
                                            <font color="#2D00D2">看</font>
                                            <font color="#0000FF">商</font>
                                            <font color="#2A00D5">品</font>
                                            <font color="#5400AB">下</font>
                                            <font color="#7E0081">单</font>
                                            <font color="#A80057">说</font>
                                            <font color="#D2002D">明</font>
                                        </div>
                                    </marquee>
                                </a></li>
                        </ul>
                    </div>
                </div>
                <div id="GoodsApp">
                    <div class="content-slide" style="background-color: #FFFFFF;padding:1em;"
                         v-if="ActivitiesGoods.length>=1">
                        <div style="color: red;font-size:1em;margin-bottom: 1em;">
                            限购秒杀
                        </div>

                        <a v-for="(item,index) in ActivitiesGoods" :href="'?mod=shop&gid='+item.gid">
                            <div class="mbg">
                                <div v-if="item.Seckill.state == 1 && item.Seckill.attend < item.Seckill.astrict"
                                     class="layui-badge" style="position: absolute;">
                                    进行中
                                </div>
                                <div v-else-if="item.Seckill.state == 1 && item.Seckill.attend >= item.Seckill.astrict"
                                     class="layui-badge layui-bg-gray" style="position: absolute;">
                                    已结束
                                </div>
                                <div v-else class="layui-badge layui-bg-gray" style="position: absolute;">
                                    筹备中
                                </div>
                                <p class="ico">
                                    <img :src="item.image"
                                         style="width: 4em;height: 4em;"
                                         onerror="this.src='./assets/img/404.png'">
                                </p>
                                <p class="icon-title layui-elip">{{item.name}}</p>
                                <div class="goodsPrice">
                                    <div v-if="PriceS(item)['state'] === 1">
                                        <span style="font-size: 13px;" :style="'color:' + PriceS(item)['color']">￥{{
                                            MoneyS(item) }}
                                        </span>
                                        <span style="color: #9e9e9e;text-decoration:line-through;font-size:8px;margin-left: 4px;">
                                            {{ PriceS(item)['price'] }}
                                        </span>
                                    </div>
                                    <div v-else-if="PriceS(item)['state'] === 2">
                                        <span :style="'color:' + PriceS(item)['color']">{{ PriceS(item)['price'] }}
                                        </span>
                                    </div>
                                    <div v-else>
                                        <span style="font-size: 13px;color:#f4a300">{{ MoneyS(item) }}</span>
                                        <span style="color: #9e9e9e;text-decoration:line-through;font-size:8px;margin-left: 4px;">
                                            {{ PriceS(item)['price'] }}
                                        </span>
                                        <span style="font-size: 13px;color:#f4a300;margin-left: 4px;font-size: 10px;">{{
                                            item.currency }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <style>
                            .goodsPrice {
                                text-overflow: ellipsis;
                                overflow: hidden;
                                white-space: nowrap;
                                color: #ff3636;
                                font-size: 30 rpx;
                                line-height: 42 rpx;
                                display: flex;
                                justify-content: center;
                                font-weight: 500;
                            }
                        </style>
                    </div>

                    <div class="goods_sort">
                        <div @click="SortSet(1)" class="item item-price"><span
                                    class="text">综合</span>
                            <span class="sorting">
                                <i :style="SortingType===1&&Sorted===1?'color:red':''" class="icon icon-sanjiao2"></i>
                                <i :style="SortingType===1&&Sorted===2?'color:red':''" class="icon icon-sanjiao1"
                                   style="margin-top:0.1em;"></i>
                	        </span>
                        </div>
                        <div @click="SortSet(2)" class="item item-price"><span class="text">售价</span>
                            <span class="sorting">
                    		<i :style="SortingType===2&&Sorted===1?'color:red':''" class="icon icon-sanjiao2"></i>
                    		<i :style="SortingType===2&&Sorted===2?'color:red':''" class="icon icon-sanjiao1"
                               style="margin-top:0.1em;"></i>
                	    </span>
                        </div>
                        <div @click="SortSet(3)" class="item item-price" data-order="price" data-sort="ASC"><span
                                    class="text">库存</span>
                            <span class="sorting">
                    		<i :style="SortingType===3&&Sorted===1?'color:red':''" class="icon icon-sanjiao2"></i>
                    		<i :style="SortingType===3&&Sorted===2?'color:red':''" class="icon icon-sanjiao1"
                               style="margin-top:0.1em;"></i>
                	    </span>
                        </div>
                        <div class="item">
                            <span @click="state=(state==1?2:1)" class="text">
                                <a href="javascript:;">
                                <i :class="'icon icon-'+(state==1?'sort':'app')"
                                   style="font-size:20px;"></i></a>
                            </span>
                        </div>
                    </div>
                    <div class="fui-goods-group block three" style="background: #f3f3f3;">
                        <div style="color: #e29117;text-align: center;font-weight: 700;font-size: 1.1em;line-height:2em;">
                            <a href="./" style="color:red">{{cid==-1?'本系统':GoodsDataClass.name}}</a>
                            <span v-if="name===''">
                                共<font color="red">{{GoodsData.length}}</font>个商品
                            </span>
                            <span v-else>
                                共<font color="red">{{GoodsData.length}}</font>个带有【{{name}}】关键词的商品
                            </span>
                            <?php if ($conf['FluctuationsPrices'] == 1) { ?>
                                <a style="font-size:1em;color:#3c86e0" href="./?mod=UpAndDown"
                                   target="_blank">[价格波动]</a>
                            <?php } ?>
                        </div>
                        <div class="Coupons" @click="Coupon" style="display: none;"
                             :style="CouponData.length>=1?'display:block;':'display: none;'" title="领取惊喜优惠券！">
                            <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"/>
                            <br>
                            <span style="font-size: 0.5em;">{{ (type==3?'惊喜优惠券':'分类优惠券') }}</span>
                        </div>
                        <div v-if="state==1">
                            <a v-for="(item,index) in GoodsData" class="fui-goods-item"
                               :href="'?mod=shop&gid=' + item.gid">
                                <div class="image">
                                    <img :src="item.image" style="min-height:120px !important;"
                                         onerror="this.src='<?= ROOT_DIR ?>assets/img/404.png'"/>
                                    <img class="lazy" v-show="item.quota==0" alt=""
                                         style="width:100%;top: 0;position: absolute;height:100%"
                                         src="<?= ROOT_DIR ?>assets/img/ysb.png"/>
                                </div>
                                <div class="detail">
                                    <div class="name">
                                        {{ item.name }}
                                    </div>
                                    <div class="price" style="margin-top: 0.3rem;font-size:0.8em;color:red">
                                        <span v-html="Price(item)"></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div v-else class="layui-row">
                            <div class="layui-col-md12" v-for="(item,index) in GoodsData">
                                <a :href="'?mod=shop&gid=' + item.gid" class="layui-panel"
                                   :title="item.name"
                                   style="padding: 0.5em;margin-bottom: 0.5em;height:20vh;box-shadow: none;display:block;">
                                    <div class="layui-row">
                                        <div class="layui-col-xs4" style="height:16vh;">
                                            <img
                                                    :src="item.image"
                                                    class="lazy"
                                                    onerror="this.src='<?= ROOT_DIR ?>assets/img/404.png'"
                                                    style="max-height:100%;width:80%;border-radius: 0.5em;box-shadow:1px 1px 16px #eee;"/>
                                            <img
                                                    v-if="item.quota==0"
                                                    src="<?= ROOT_DIR ?>assets/img/ysb.png"
                                                    style="top: 0;left:0;position: absolute;width:80%"/>
                                        </div>
                                        <div class="layui-col-xs8" style="padding:0.5em;height:16vh;">
                                            <div class="name" style="color: #000000;">{{item.name}}</div>
                                            <div style="margin-top: 0.5em;color:#999;font-size:0.8em;">
                                                剩余库存：{{item.quota}}份
                                            </div>
                                            <div style="margin-top: 0.5em;color:#999;font-size:0.8em;">
                                                商品销量：{{item.sales}}
                                            </div>
                                            <div>
                                                <span v-html="Price(item)"
                                                      style="color:#ff5555;-webkit-box-flex:1;font-size:.8rem;position: absolute;bottom: 0;left:0;"></span>
                                            </div>
                                            <div style="position: absolute;bottom: 0;right:0;">
                                                 <span v-if="item.quota==0" class="buy"
                                                       style="background: rgba(111,100,99,0.42);box-shadow:0 2px 8px rgb(255 97 74 / 24%);color:#fff;display: inline-block;height: 1.1rem;line-height: 1rem;float: right;padding: 0rem 0.35rem;width:4em;border-radius: 0.1rem;border: 1px solid transparent;text-align:center;">
                                                    缺货
                                                </span>
                                                <span v-else class="buy"
                                                      style="background: #ff614a;box-shadow:0 2px 8px rgb(255 97 74 / 24%);color:#fff;display: inline-block;height: 1.1rem;line-height: 1rem;float: right;padding: 0rem 0.35rem;width:4em;border-radius: 0.1rem;border: 1px solid transparent;text-align:center;">
                                                    购买
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="fui-goods-group block three" style="background: #f3f3f3;" id="goods-list-container">
                        <div class="footer" style="width:100%; margin-top:0.5rem;margin-bottom:2.5rem;display: block;">
                            <ul>
                                <li>版权归@<?php echo $conf['sitename'] ?>所有</li>
                            </ul>
                            <p style="text-align: center"><?php echo $conf['statistics'] ?></p>
                            <p style="text-align: center"><?php echo $conf['notice_bottom'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fui-navbar" style="bottom:-34px;background-color: white;max-width: 600px">
            </div>
            <div class="fui-navbar" style="max-width: 600px">
                <a href="index.php" class="nav-item  "> <span class="icon icon-home"></span> <span
                            class="label">首页</span>
                </a>
                <a href="./?mod=query" class="nav-item "> <span class="icon icon-dingdan1"></span> <span
                            class="label">订单</span> </a>
                <a href="./?mod=kf" class="nav-item "> <span class="icon icon-qq"></span> <span class="label">客服</span>
                </a>

                <a href="./?mod=route&p=User" class="nav-item "> <span class="icon icon-person2"></span> <span
                            class="label">会员中心</span> </a>
            </div>

            <div style="width: 100%;height: 100vh;position: fixed;top: 0px;left: 0px;opacity: 0.5;background-color: black;display: none;z-index: 10000"
                 class="tzgg2"></div>
            <div class="tzgg2" type="text/html" style="display: none">
                <div class="account-layer" style="z-index: 100000000;">
                    <div class="account-main" style="padding:0.8rem;height: auto">

                        <div class="account-title">系 统 公 告</div>

                        <div class="account-verify"
                             style="  display: block;    max-height: 15rem;    overflow: auto;margin-top: -10px">
                            <?= $conf['notice_top'] ?>
                        </div>
                    </div>
                    <div class="account-btn" style="display: block" onclick="gbtzgg2()">确认</div>
                </div>
            </div>

            <div style="width: 100%;height: 100vh;position: fixed;top: 0px;left: 0px;opacity: 0.5;background-color: black;display: none;z-index: 10000"
                 class="tzgg3"></div>
            <div class="tzgg3" type="text/html" style="display: none">
                <div class="account-layer" style="z-index: 100000000;">
                    <div class="account-main" style="padding:0rem;height: auto">

                        <div class="account-title"
                             style="height: 2rem;border-bottom: 1px solid #d3d7d4;background: #F2F2F2;border-top-right-radius:0.25rem;border-top-left-radius:0.25rem">
                            系 统 公 告
                        </div>

                        <div class="account-verify"
                             style="    display: block;    max-height: 15rem;    overflow: auto;margin-top: 10px;padding: 0px 0.8rem;margin-bottom: 10px">
                            <?= $conf['notice_top'] ?>
                        </div>
                    </div>
                    <div class="account-btn" style="display: block" onclick="gbtzgg3()">确认</div>
                </div>
            </div>

            <div style="width: 100%;height: 100vh;position: fixed;top: 0px;left: 0px;opacity: 0.5;background-color: black;display: none;z-index: 10000"
                 class="tzgg4"></div>
            <div class="tzgg4" type="text/html" style="display: none">
                <div class="account-layer" style="z-index: 100000000;">
                    <div class="account-main" style="padding:0rem;height: auto">

                        <div class="account-title"
                             style="height: 2rem;border-bottom: 1px solid #d3d7d4;background: #F2F2F2;border-top-right-radius:0.25rem;border-top-left-radius:0.25rem">
                            平 台 公 告
                        </div>

                        <div class="account-verify"
                             style="    display: block;    max-height: 15rem;    overflow: auto;margin-top: 10px;padding: 0px 0.8rem;margin-bottom: 10px">
                            <?= $conf['notice_top'] ?>
                        </div>
                    </div>
                    <div class="account-btn" style="display: block" onclick="$('.tzgg4').hide()">确认</div>
                </div>
            </div>
            <div style="width: 100%;height: 100vh;position: fixed;top: 0px;left: 0px;opacity: 0.5;background-color: black;display: none;z-index: 10000"
                 class="tzgg5"></div>
            <div class="tzgg5" type="text/html" style="display: none">
                <div class="account-layer" style="z-index: 100000000;">
                    <div class="account-main" style="padding:0rem;height: auto">

                        <div class="account-title"
                             style="height: 2rem;border-bottom: 1px solid #d3d7d4;background: #F2F2F2;border-top-right-radius:0.25rem;border-top-left-radius:0.25rem">
                            下 单 须 知
                        </div>

                        <div class="account-verify"
                             style="    display: block;    max-height: 15rem;    overflow: auto;margin-top: 10px;padding: 0px 0.8rem;margin-bottom: 10px">
                            <?= $conf['PopupNotice'] ?>
                        </div>
                    </div>
                    <div class="account-btn" style="display: block" onclick="$('.tzgg5').hide()">确认</div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo $cdnserver; ?>assets/template/cool/assets/js/foxui.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/cool/assets/js/core.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
    <script>
        var cid = <?= (empty($_QET['cid']) ? -1 : $_QET['cid']) ?>;
    </script>
    <script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/cool/assets/js/index.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>