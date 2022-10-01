<?php
if (!defined('IN_CRONLITE')) {
    die;
}
?>
<html lang="en">

<head>
    <title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>"/>
    <meta name="description" content="<?php echo $conf['description'] ?>"/>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui"/>
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/bootstrap.min.css"/>
    <link href="<?php echo $cdnpublic; ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/style.css?v=2"/>
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/pcoded-horizontal.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/mobile.css"/>
    <link href="<?php echo $cdnserver; ?>assets/layui/css/layui.css" rel="stylesheet"/>
    <style>
        th,
        td {
            white-space: nowrap;
        }

        .icolist {
            display: -webkit-flex;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: flex-star;
        }

        .icolistson {
            width: 25%;
            flex: 1 1 auto;
            max-width: 25%;
        }

        .icolistson img {
            border-radius: 10px;
            height: 3.5rem;
            width: 3.5rem;
        }

        .icolistson a {
            text-decoration: none;
        }

        .kuls {
            color: #000;
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
            bottom: 10%;
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
</head>

<body class="index" id="app">
<div id="pcoded" class="pcoded" nav-type="st1" theme-layout="horizontal" horizontal-placement="top"
     horizontal-layout="widebox" pcoded-device-type="phone" hnavigation-view="view1" fream-type="theme1"
     sidebar-img="false" sidebar-img-type="img1" layout-type="light">
    <div class="pcoded-container">
        <nav class="navbar header-navbar pcoded-header" header-theme="theme5" pcoded-header-position="fixed">
            <div class="navbar-wrapper">
                <div class="navbar-logo" logo-theme="theme1">
                    <a href="./"><img class="img-fluid" src="<?= $conf['logo'] ?>" alt="<?php echo $conf['sitename'] ?>"
                                      style="max-height: 40px;"/></a>
                    <a class="mobile-options">
                        更多
                        <i class="fa fa-ellipsis-v"></i>
                    </a>
                </div>

                <div class="navbar-container container-fluid">
                    <ul class="nav-right">
                        <li></li>
                        <li>
                            <a href="./">
                                <i class="fa fa-opera"></i>
                                首页
                            </a>
                        <li>
                        <li>
                            <a href="#" @click="AlertKefu">
                                <i class="fa fa-commenting"></i>
                                联系客服
                            </a>
                        </li>
                        <li>
                            <a v-if="Service.url != -1" :href="Service.url" target="_blank">
                                <i class="fa fa-commenting"></i>
                                在线咨询
                            </a>
                        </li>
                        <?php if ($conf['FluctuationsPrices'] == 1) { ?>
                            <li>
                                <a href="./?mod=UpAndDown" target="_blank">
                                    <i class="fa fa-pie-chart"></i>
                                    价格波动
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a v-if="InformData.Appurl != ''" :href="InformData.Appurl" target="_blank">
                                <i class="fa fa-android"></i>

                                下载APP
                            </a>
                        </li>
                        <li>
                            <a href="./?mod=query">
                                <i class="fa fa-search"></i>
                                查询订单
                            </a>
                        </li>
                        <?php if ($User != false) { ?>
                            <li><a href="./?mod=route&p=User">我的后台</a></li>
                        <?php } else { ?>
                            <li>
                                <a href="./?mod=route&p=User" style="color:red">
                                    <img src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/image/nav_money.png"
                                         alt="开通分站"/>
                                    开通分站
                                </a>
                            </li>
                            <li><a href="./?mod=route&p=User">后台登录</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="pcoded-main-container" style="margin-top: 62.4px;">
            <div class="pcoded-wrapper d-block d-md-none">
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-body">
                                <div style="display: none;padding: 10px;margin-top: 1em;margin-bottom:10px"
                                     :style="InformData.NoticeTop != ''?'display:block;':'display: none;'"
                                     class="card" v-html="InformData.NoticeTop"></div>

                                <div style="display: none;padding: 10px;margin-top: 1em;margin-bottom:10px"
                                     :style="ActivitiesGoods.length>=1?'display:block;':'display: none;'"
                                     class="card">
                                    <h4 class="sub-title" style="margin-bottom: 5px;color: red">限购秒杀</h4>
                                    <div v-if="ActivitiesGoods.length>=1"
                                         style="display: block;"
                                         class="layui-row layui-col-space2">
                                        <div class="layui-col-xs4"
                                             style="text-align: center"
                                             v-for="(item,index) in ActivitiesGoods">
                                            <a class="card" :href="'./?mod=shop&gid='+item.gid"
                                               style="color: #000">
                                                <div class="card-body" style="padding:1em 0;">
                                                    <div v-if="item.Seckill.state == 1 && item.Seckill.attend < item.Seckill.astrict"
                                                         class="layui-badge"
                                                         style="position: absolute;">
                                                        进行中
                                                    </div>
                                                    <div v-else-if="item.Seckill.state == 1 && item.Seckill.attend >= item.Seckill.astrict"
                                                         class="layui-badge layui-bg-gray"
                                                         style="position: absolute;">
                                                        已结束
                                                    </div>
                                                    <div v-else class="layui-badge layui-bg-gray"
                                                         style="position: absolute;">
                                                        筹备中
                                                    </div>
                                                    <div class="layui-row" style="padding:0">
                                                        <img
                                                                :src="item.image"
                                                                style="width: 68px; height: 68px; border-radius: 0.5em; box-shadow: rgb(238, 238, 238) 3px 3px 16px;">
                                                        <div class="layui-col-xs12"
                                                             style="font-size: 80%;">
                                                            <div class="mt-2 layui-elip">{{item.name}}
                                                            </div>
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
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
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
                                </div>

                                <div class="input-group input-group-primary" style="margin-bottom: 10px;">
                                    <button class="btn btn-primary btn-mini btn-block"
                                            onclick="window.location.href='./?mod=query'">
												<span class="">
													<i class="fa fa-search"></i>
													<strong>订单查询</strong>
												</span>
                                    </button>
                                </div>
                                <div class="Coupons" @click="Coupon(-1)" style="display: none;"
                                     :style="CouponData.length>=1?'display:block;':'display: none;'"
                                     title="领取惊喜优惠券！">
                                    <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"/>
                                    <br>
                                    <span style="font-size: 0.5em;">惊喜优惠券</span>
                                </div>
                                <div class="page-body" style="display: none;"
                                     :style="ClassData!=[]?'display:block;':'display: none;'">
                                    <div class="card" v-for="(item, index) in ClassData"
                                         style="margin-bottom: 10px;">
                                        <div class="card-block">
                                            <div class="form-horizontal">
                                                <div class="row">
                                                    <h4 class="sub-title"
                                                        style="margin-bottom: 0px; margin-top: -6px; width: 100%;">
                                                        <i class="fa fa-shopping-cart"></i>
                                                        <strong>{{ item.name }}</strong>
                                                    </h4>

                                                    <div class="card-block p-0 col-md-12"
                                                         style="display: none;cursor: pointer;"
                                                         :style="CouponDatas[(item.cid)].length>=1?'display:block;':'display: none;'">
                                                        <div class="alert alert-danger m-2"
                                                             @click="Coupon((item.cid))">
                                                            <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"
                                                                 style="width:1.6em;height:1.6em"/>
                                                            您有{{ CouponDatas[(item.cid)].length }}个优惠券待领取
                                                        </div>
                                                    </div>

                                                    <div class="row list_1" v-if="GoodsData[item.cid].length >= 1"
                                                         v-for="(items, i) in GoodsData[item.cid]">
                                                        <div class="col-4 aqytupian">
                                                            <a :href="
																				items.quota == 0
																					? 'javascript:alert(\'商品无货,请联系客服补货哦！\')'
																					: './?mod=shop&gid=' + items.gid
																			">
                                                                <img :src="items.image" :alt="item.name"
                                                                     onerror="this.src='<?= $conf['logo'] ?>';this.onerror='null' "
                                                                     class="lazy"/>
                                                            </a>
                                                        </div>
                                                        <div class="col-8 xiadanq">
                                                            <div class="col-12 qupad">
                                                                <a :href="
																					items.quota == 0
																						? 'javascript:alert(\'商品无货,请联系客服补货哦！\')'
																						: './?mod=shop&gid=' + items.gid
																				">
                                                                    <h6>{{ items.name }}</h6>
                                                                </a>
                                                            </div>
                                                            <div class="col-12 qupad jine"
                                                                 style="text-align: left;margin-bottom:0.5em">
                                                                <span class="qian" style="font-size: 1.2rem"
                                                                      v-html="Price(items)"></span>
                                                                <span class="qian"
                                                                      style="font-size: 1rem;font-weight: 500;"
                                                                > × {{items.quantity}}{{items.units}}</span>
                                                            </div>
                                                            <div class="col-12 qupad xiao_1">
                                                                <div class="row">
                                                                    <div class="col-4 qupad">
                                                                        <span class="daichong">{{ items.label[0].name }}</span>
                                                                    </div>
                                                                    <div class="col-4 qupad">
                                                                        <span class="xiaoliang">销量:{{ (items.sales>=1000?'1000+':items.sales) }}</span>
                                                                    </div>
                                                                    <div class="col-4 qupad kucun">
																				<span class="daichong"
                                                                                      :class="items.quota == 0 ? ' kuls' : ''">
																					{{
																							items.quota >= 20
																								? '库存充足'
																								: items.quota >= 5
																								? '库存较少'
																								: items.quota >= 1
																								? '库存极少'
																								: '暂无库存'
																						}}
																				</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div style="width: 100%;height: 1.5em;line-height: 3em;text-align: center;color: #CCCCCC;"
                                                         v-if="GoodsData[item.cid].length == 0">
                                                        此分类无商品哦
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <?= $conf['statistics'] ?>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="hi-Modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">温馨提示</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true"></span></button>
                </div>
                <div class="modal-body" id="modals" v-html="InformData.PopupNotice"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary waves-effect waves-light " data-dismiss="modal">确认
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery.min.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery-ui.min.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/bootstrap.min.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery.slimscroll.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/modernizr.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/pcoded.min.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery.mcustomscrollbar.concat.min.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/script.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/wapindex.js?vs=<?= $accredit['versions'] ?>"></script>
</div>
</body>

</html>