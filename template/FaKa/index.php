<?php
if (!defined('IN_CRONLITE')) die;
$User = login_data::user_data();
if (View::isMobile() == true) {
    include ROOT . "template/FaKa/wapindex.php";
    die;
}
?>
<!DOCTYPE html>
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
    <link href="<?php echo $cdnserver; ?>assets/layui/css/layui.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/mobile.css"/>
    <style>
        @media (min-width: 992px) {
            .icolistson {
                width: 25% !important;
                max-width: 25% !important;
            }
        }

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

        .table1 {
            width: 100%;
            max-width: 100%;
            background-color: transparent;
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
            width: 6rem;
            height: 6rem;
            z-index: 100;
            cursor: pointer;
            color: red;
            text-align: center;
            animation: Coupons 2s 0.2s linear infinite alternate;
        }

        .Coupons img {
            width: 6rem;
            height: 6rem;
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
<div class="theme-loader">
    <div class="loader-track">
        <div class="loader-bar"></div>
    </div>
</div>
<div id="pcoded" class="pcoded" nav-type="st1" theme-layout="horizontal" horizontal-placement="top"
     horizontal-layout="widebox" pcoded-device-type="tablet" hnavigation-view="view1" fream-type="theme1"
     sidebar-img="false" sidebar-img-type="img1" layout-type="light">
    <div class="pcoded-container">
        <nav class="navbar header-navbar pcoded-header" header-theme="theme5" pcoded-header-position="fixed">
            <div class="navbar-wrapper">
                <div class="navbar-logo" logo-theme="theme1">
                    <a href="./"><img class="img-fluid" src="<?= $conf['logo'] ?>" alt="<?php echo $conf['sitename'] ?>"
                                      style="max-height: 35px;"/></a>
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
                        </li>
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

        <div class="Coupons" @click="Coupon(-1)" style="display: none;"
             :style="CouponData.length>=1?'display:block;':'display: none;'" title="领取惊喜优惠券！">
            <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"/>
            <br>
            <span style="font-size: 0.5em;">惊喜优惠券</span>
        </div>

        <div class="pcoded-main-container" style="margin-top: 56px;">
            <div class="pcoded-wrapper d-none d-sm-block">
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                <div class="page-body" style="font-size: 100%">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="card" style="display: none;margin-bottom: 10px;"
                                                 :style="ClassData.length >= 1?'display:block;':'display: none;'"
                                                 v-for="(item, index) in ClassData">
                                                <div class="card-block" style="margin-bottom: -45px;">
                                                    <div class="form-horizontal">
                                                        <div class="row">
                                                            <h4 class="sub-title"
                                                                style="margin-bottom: 0px; margin-top: -6px; width: 100%;">
                                                                <i class="fa fa-shopping-cart"></i>
                                                                {{ item.name }}
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
                                                            <div class="card-block p-0 col-md-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-hover ">
                                                                        <thead>
                                                                        <tr>
                                                                            <th class="text-center">商品名称</th>
                                                                            <th class="text-center">商品标签</th>
                                                                            <th class="text-center">商品库存</th>
                                                                            <th class="text-center">商品销量</th>
                                                                            <th class="text-center">商品价格</th>
                                                                            <th class="text-center">操作</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        <tr v-for="(items, i) in GoodsData[item.cid]">
                                                                            <td class="text-center"
                                                                                style="white-space: normal;">
																						<span style="margin-top: 1px;color: #666666;">
																							<strong>{{ items.name }}</strong>
																						</span>
                                                                            </td>

                                                                            <td class="text-center" width="100px">
																						<span style="margin-top: 1px; border-radius: 4px; padding: 4px 7px; color: #fff !important; font-weight: 400; font-size: 75%;margin-left: 5px;"
                                                                                              v-for="(iteme, ins) in items.label"
                                                                                              :style="'background-color: ' + iteme.color">
																							{{ iteme.name }}
																						</span>
                                                                            </td>
                                                                            <td class="text-center" width="100px">
																						<span style="margin-top: 1px;color: #666666;">
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
                                                                            </td>
                                                                            <td class="text-center" width="100px">
																						<span style="margin-top: 1px;color: #666666;">
																							<i class="glyphicon glyphicon-fire"></i>
																							{{ items.sales }}
																						</span>
                                                                            </td>
                                                                            <td class="text-center" width="100px">
                                                                                <span class="text-primary product-price"
                                                                                      v-html="Price(items)"></span>
                                                                            </td>
                                                                            <td class="text-center" width="80px">
                                                                                <a :href="
																									items.quota == 0
																										? 'javascript:layer.alert(\'商品无货,请联系客服补货哦！\',{icon:2})'
																										: './?mod=shop&gid=' + items.gid
																								"
                                                                                   class="btn btn-mini btn-primary"
                                                                                   :class="items.quota == 0 ? ' btn-disabled' : ''">
                                                                                    {{ items.quota == 0 ? '商品无货' :
                                                                                    '立即购买' }}
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                        </tbody>
                                                                        <div style="height: 4em;line-height: 4em;text-align: center;color: #CCCCCC;"
                                                                             v-if="GoodsData[item.cid].length == 0">
                                                                            此分类无商品哦
                                                                        </div>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card" style="display: none;margin-bottom: 10px;"
                                                 :style="InformData.NoticeTop != ''?'display:block;':'display: none;'">
                                                <div class="card-block">
                                                    <h4 class="sub-title" style="margin-bottom: 5px;">网站公告</h4>
                                                    <div class="row">
                                                        <div class="col-sm-12" v-html="InformData.NoticeTop"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card" style="margin-bottom: 10px;"
                                            >
                                                <div class="card-block" v-if="ActivitiesGoods.length>=1">
                                                    <h4 class="sub-title" style="margin-bottom: 5px;">限购秒杀</h4>
                                                    <div v-if="ActivitiesGoods.length>=1"
                                                         style="display: block;"
                                                         class="layui-row layui-col-space2">
                                                        <div class="layui-col-sm4"
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
                                            </div>
                                            <div class="card" style="display: none;margin-bottom: 10px;"
                                                 :style="ArticleList.data.length >= 1?'display:block;':'display: none;'">
                                                <div class="card-block">
                                                    <h4 class="sub-title" style="margin-bottom: 5px;">问题中心 - (第{{
                                                        ArticleList.page }}页)</h4>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <a v-for="(item, index) in ArticleList.data"
                                                               class="list-group-item" href="#"
                                                               @click="AlertMsg(item.content, item.title)"
                                                               style="color:#73b4ff" :title="'于' + item.addtime + '发布'">
                                                                <img :src="item.image" width="15" height="15"
                                                                     class="image_sc"/>
                                                                {{ item.title }}
                                                            </a>
                                                            <a v-if="ArticleList.page != 1" href="#" title="上一页"
                                                               class="btn btn-default btn-xs" style="float: left;"
                                                               @click="GetArticleList(3)">
                                                                上一页
                                                            </a>
                                                            <a v-if="ArticleList.state == true" href="#" title="下一页"
                                                               class="btn btn-default btn-xs" style="float: right;"
                                                               @click="GetArticleList(2)">
                                                                下一页
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="default-grid-item">
                                                <div class="card gallery-desc" style="margin-bottom: 10px;">
                                                    <div class="masonry-media">
                                                        <a class="media-middle" href="#!"><img class="img-fluid"
                                                                                               src="<?= ROOT_DIR ?>assets/template/FaKa/assets/image/shoucang.png"/></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="default-grid-item">
                                                <div class="card gallery-desc" style="margin-bottom: 10px;">
                                                    <div class="masonry-media">
                                                        <a class="media-middle" href="./?mod=route&p=User">
                                                            <img class="img-fluid"
                                                                 src="<?= ROOT_DIR ?>assets/template/FaKa/assets/image/jiameng.png"/>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card" style="display: none;margin-bottom: 10px;"
                                                 :style="InformData.Navigation.length != 0?'display:block;':'display: none;'">
                                                <div class="card-block" style="">
                                                    <h4 class="sub-title" style="margin-bottom: 5px;">快捷导航</h4>
                                                    <div class="row">
                                                        <div class="col-sm-12"
                                                             v-for="(item, index) in InformData.Navigation">
                                                            <a :href="item.url" target="_blank">> {{ item.name }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="InformData.NoticeBottom != ''" class="card mt10"
                                                 style="margin-bottom: 10px;">
                                                <div class="card-block" v-html="InformData.NoticeBottom"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div><?= $conf['statistics'] ?></div>
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
                <button type="button" class="btn btn-primary waves-effect waves-light " data-dismiss="modal">确认</button>
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
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/index.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>