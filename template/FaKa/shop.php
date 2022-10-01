<?php
if (!defined('IN_CRONLITE')) die;
$User = login_data::user_data();
?>
<!DOCTYPE html>
<html lang="ch">
<head>
    <title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>"/>
    <meta name="description" content="<?php echo $conf['description'] ?>"/>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/bootstrap.min.css">
    <link href="<?php echo $cdnpublic; ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/style.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/pcoded-horizontal.min.css">
    <link href="<?php echo $cdnserver; ?>assets/layui/css/layui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= ROOT_DIR ?>assets/css/Global.css">

    <style>
        img.logo {
            width: 22px;
            margin: -2px 5px 0 5px;
        }

        .onclick {
            cursor: pointer;
            touch-action: manipulation;
        }

        #product-detail img {
            max-width: 100%;
            max-height: 100%;
        }

        .from_in_2 {
            min-width: 4em;
            height: 5em;
            background-color: #eee;
            color: #656565;
            padding: 0.5em;
            line-height: 4.2em;
            font-size: 0.8em;
            border: solid 1px #ccc;
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
</head>

<body id="app" class="share">
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
                        <li>

                        </li>
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
        <div class="pcoded-main-container mt-5">
            <div class="container">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="page-body">
                            <div class="row">
                                <div class="col-md-12" v-if="Goods.name!=-1 && Goods.Seckill !== -1">
                                    <div class="card product-detail-page" style="margin-bottom: 10px;padding:0;">
                                        <div class="card-header" style="color: red;height:auto;font-size:1.2em;">
                                            限购秒杀
                                        </div>
                                        <div class="card-block">
                                            剩余名额：{{(Goods.Seckill.astrict - Goods.Seckill.attend)}}个<br>
                                            活动内容：{{'降价 ' + Goods.Seckill.depreciate + '%'}}<br>
                                            <span v-if="Goods.Seckill.state === 1 && Goods.Seckill.attend < Goods.Seckill.astrict">
                                                结束时间：{{Goods.Seckill.end}}
                                            </span>
                                            <span v-else-if="Goods.Seckill.state === 1 && Goods.Seckill.attend >= Goods.Seckill.astrict">
                                                结束时间：已结束，活动人数已达上限，商品恢复原价
                                            </span>
                                            <span v-else>
                                                开始时间：{{Goods.Seckill.start}}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <!-- Product detail page start -->
                                    <div class="card product-detail-page">
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="col-lg-12 col-xs-12 product-detail" id="product-detail">
                                                    <form action="?" method="post" id="myForm">
                                                        <input type="hidden" name="card_ware_id" value="13">
                                                        <div class="row">
                                                            <div class="col-lg-12" style="display: none;"
                                                                 :style="Goods.name!=-1?'display:block;':'display: none;'">
                                                                <img :src="Goods.image[0]"
                                                                     onerror="this.src='<?= $conf['logo'] ?>';this.onerror='null' "
                                                                     width="100" align=right border=0
                                                                     :alt="Goods.name"/>
                                                                <strong class="pro-desc" style="font-size: 1.5em;">{{
                                                                    Goods.name }}</strong><br>
                                                                <span id="id" class="txt-muted d-inline-block">商品编号: {{ Goods.gid }}</span>

                                                                <div class="p-l-0 m-b-10">
                                                                    <input type="hidden" id="leftcount" value="">
                                                                    <span class="txt-muted d-inline-block">商品库存: {{ Goods.quota }}份</span><br>
                                                                    <span class="txt-muted d-inline-block">购买数量: {{ Goods.quantity * num}} {{ Goods.units }}</span><br>
                                                                    <span class="text-primary product-price"
                                                                          style="font-size: 1rem;margin-top:0.5rem;">售价:
                                                                        <span
                                                                                v-if="Goods.Seckill == -1"
                                                                                style="font-size: 1rem;margin-top:0.5rem;margin-left:0.5rem">
                                                                                <font v-if="Goods.price == 0 && Goods.points == 0"
                                                                                      color="#18B566">免费</font>
                                                                                <font v-else>
                                                                                    <font color="red"
                                                                                          v-if="Goods.method == 1 || Goods.method == 2">
                                                                                        {{ NumRound(Goods.price * num) }} 元
                                                                                    </font>

                                                                                    <font v-else color="#ffaa00">
                                                                                        {{ Goods.points * num }}
                                                                                        <font style="font-size: 0.72rem;margin-left: 0.2rem;">{{ Goods.currency }}</font>
                                                                                    </font>
                                                                                </font>
                                                                            </span>
                                                                        <span
                                                                                v-else
                                                                                style="font-size: 1rem;margin-top:0.5rem;margin-left:0.5rem">
                                                                                <font v-if="Goods.price == 0 && Goods.points == 0"
                                                                                      color="#18B566">免费</font>
                                                                                <font v-else>
                                                                                    <font color="red"
                                                                                          v-if="Goods.method == 1 || Goods.method == 2">
                                                                                       {{ (NumRound(Goods.price) - NumRound(Goods.price * Goods.Seckill.depreciate) /
                                                        100).toFixed(Goods.accuracy) * num }}元
                                                        <span style="color: #9e9e9e;text-decoration:line-through;font-size:12px;margin-left: 4px;">
                                                            {{ NumRound(Goods.price * num) }}
                                                        </span>
                                                                                    </font>

                                                                                    <font v-else color="#ffaa00">
                                                                                         {{ (Goods.points - (Goods.points * Goods.Seckill.depreciate) / 100).toFixed(0) * num }}
                                                        {{ Goods.currency }}
                                                        <span style="color: #9e9e9e;text-decoration:line-through;font-size:12px;margin-left: 4px;">{{ Goods.points * num }}{{ Goods.currency }}</span>
                                                                                    </font>
                                                                                </font>
                                                                            </span>
                                                                </div>
                                                                <div class="col-lg-12">
                                                                    <h4 class="sub-title"
                                                                        style="margin-bottom: 10px;padding-bottom: 5px;">
                                                                        商品介绍</h4>
                                                                </div>
                                                                <div v-html="Goods.docs"
                                                                     class="col-xl-12 col-sm-12 editor"
                                                                     style="margin-bottom: 1em;"></div>
                                                                <div class="col-xl-12 col-sm-12">
                                                                    <h4 class="sub-title"
                                                                        style="margin-bottom: 10px;padding-bottom: 5px;padding-top: 5px;">
                                                                        购买信息</h4>

                                                                    <div class="layui-form-item"
                                                                         v-for="(item, index) of Goods.input"
                                                                         :key="index">
                                                                        <div class="input-group" v-if="item.state == 1">

                                                                            <button type="button"
                                                                                    class="btn btn-default btn-number shadow-none"
                                                                                    disabled="disabled">{{ item.Data
                                                                                }}
                                                                            </button>
                                                                            <input type="text" v-model="form[index]"
                                                                                   class="form-control input-number text-center"
                                                                                   :placeholder="'请将' + item.Data + '填写完整!'"/>
                                                                        </div>

                                                                        <div class="input-group" v-if="item.state == 2">

                                                                            <button type="button"
                                                                                    class="btn btn-default btn-number shadow-none"
                                                                                    disabled="disabled">{{
                                                                                item.Data[0] }}
                                                                            </button>

                                                                            <input type="text" v-model="form[index]"
                                                                                   class="form-control input-number text-center"
                                                                                   @blur="extend(index, item.Data[1].way, item.Data[1].url,2)"
                                                                                   :placeholder="item.Data[1].placeholder"/>

                                                                            <div class="from_in_2 onclick"
                                                                                 @click="extend(index, item.Data[1].way, item.Data[1].url)">
                                                                                {{ item.Data[1].name }}
                                                                            </div>
                                                                        </div>

                                                                        <div class="input-group" v-if="item.state == 3">
                                                                            <button type="button"
                                                                                    class="btn btn-default btn-number shadow-none"
                                                                                    disabled="disabled">{{
                                                                                item.Data }}
                                                                            </button>
                                                                            <input type="text" v-model="form[index]"
                                                                                   class="form-control input-number text-center"
                                                                                   :placeholder="'请将' + item.Data + '填写完整!'"
                                                                            />
                                                                            <div class="from_in_2 onclick"
                                                                                 id="Selectaddress" :index="index">
                                                                                选择地址
                                                                            </div>
                                                                        </div>

                                                                        <div class="input-group" v-if="item.state == 4">
                                                                            <button type="button"
                                                                                    class="btn btn-default btn-number shadow-none"
                                                                                    disabled="disabled">{{
                                                                                item.Data[0] }}
                                                                            </button>

                                                                            <select style="height: 3.4em"
                                                                                    v-model="form[index]"
                                                                                    class="form-control">
                                                                                <option v-for="o in item.Data[1]"
                                                                                        :value="o">{{ o }}
                                                                                </option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="input-group" v-if="item.state == 5">
                                                                            <div v-if="Object.values(Combination).length === 0"
                                                                                 style="color:red">
                                                                                商品下单参数未配置完善，请联系客服处理！
                                                                            </div>
                                                                            <div v-if="Object.values(Combination).length >= 1"
                                                                                 class="layui-card-body"
                                                                                 style="padding:0;">
                                                                                <fieldset
                                                                                        v-for="(item, index) in SkuBtn"
                                                                                        class="layui-elem-field layui-field-title site-title">
                                                                                    <legend>{{ index }}</legend>
                                                                                    <div style="margin-top:0.5em">
                                                                                        <div style="display:inline-block;"
                                                                                             v-for="(ts, is) in item"
                                                                                             :key="is">
                                                                                            <button
                                                                                                    type="button"
                                                                                                    class="layui-btn layui-btn-sm layui-btn-normal"
                                                                                                    v-if="FormSp[index] == is && ts.type == 1"
                                                                                                    @click="BtnClick(index, is, ts.type)"
                                                                                                    style="margin: 0.3em;border-radius: 0.5em;border: none;box-shadow:1px 1px 18px #ccc;">
                                                                                                {{ is }}
                                                                                            </button>
                                                                                            <button
                                                                                                    type="button"
                                                                                                    class="layui-btn layui-btn-sm layui-btn-primary"
                                                                                                    v-else-if="FormSp[index] != is && ts.type == 1"
                                                                                                    @click="BtnClick(index, is, ts.type)"
                                                                                                    style="margin: 0.3em;border-radius: 0.5em;border: none;box-shadow:1px 1px 8px #ccc;">
                                                                                                {{ is }}
                                                                                            </button>
                                                                                            <button
                                                                                                    type="button"
                                                                                                    class="layui-btn layui-btn-sm  layui-btn-disabled"
                                                                                                    v-else
                                                                                                    @click="BtnClick(index, is, ts.type)"
                                                                                                    style="margin: 0.3em;border-radius: 0.5em;border: none">
                                                                                                {{ is }}
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </fieldset>
                                                                            </div>
                                                                        </div>

                                                                        <div class="input-group" v-if="item.state == 6">
                                                                            <button type="button"
                                                                                    class="btn btn-default btn-number shadow-none"
                                                                                    disabled="disabled">{{
                                                                                item.Data[0] }}
                                                                            </button>

                                                                            <input type="text" v-model="form[index]"
                                                                                   class="form-control input-number text-center"
                                                                                   :placeholder="item.Data[1].placeholder"/>

                                                                            <button class="from_in_2 onclick"
                                                                                    type="button"
                                                                                    id="QrId" :url="item.Data[1].url"
                                                                                    style="z-index: 10;"
                                                                                    :index="index">
                                                                                {{ item.Data[1].name }}
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group" style="display: none;"
                                                                         :style="Goods.repetition==1?'display:block;':'display: none;'">
                                                                        <div class="input-group">
                                                                                <span class="input-group-btn">
                                                                                    <input type="button"
                                                                                           @click="PriceLs(1)"
                                                                                           class="btn btn-info"
                                                                                           style="border-radius: 0px;"
                                                                                           value="━">
                                                                                </span>
                                                                            <input v-on:input="PriceLs"
                                                                                   class="form-control"
                                                                                   v-model.number="num" type="number"
                                                                                   :min="Goods.min"
                                                                                   :max="Goods.max"/>
                                                                            <span class="input-group-btn">
                                                                                    <input type="button"
                                                                                           @click="PriceLs(2)"
                                                                                           class="btn btn-info"
                                                                                           style="border-radius: 0px;"
                                                                                           value="✚">
                                                                                </span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-radio m-b-10 form-inline">
                                                                        <button type="button" @click="submit"
                                                                                class="btn btn-info btn-block waves-effect waves-light"
                                                                                style="background-color: #6495ED">
                                                                            <i class="fa fa-shopping-cart f-16"></i><span
                                                                                    class="m-l-10">购买</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="card-block p-2 col-md-12"
                                                                     style="display: none;cursor: pointer;"
                                                                     :style="CouponData.length>=1?'display:block;':'display: none;'">
                                                                    <div class="alert alert-danger m-1"
                                                                         @click="Coupon()">
                                                                        <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"
                                                                             style="width:1.6em;height:1.6em"/>
                                                                        您有{{ CouponData.length }}个优惠券待领取
                                                                    </div>
                                                                </div>
                                                    </form>
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
    </div>
</div>

<script src="<?php echo $cdnserver; ?>assets/js/jquery-3.4.1.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery.slimscroll.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/custom-prism.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/pcoded.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery.mcustomscrollbar.concat.min.js"></script>
<script src="/assets/template/FaKa/assets/js/script.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/slick.min.js"></script>
<script src="./assets/js/distpicker.min.js"></script>

<script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
<script src="<?php echo $cdnserver; ?>assets/js/axios.min.js"></script>
<script>
	var gid = <?= $_QET['gid'] ?>;
</script>
<script src="./assets/template/FaKa/assets/js/shop.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>
