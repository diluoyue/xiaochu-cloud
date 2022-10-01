<?php

/**
 * 下单界面
 */
if (!defined('IN_CRONLITE')) die;
$UserData = login_data::user_data();
?>
<<!DOCTYPE html>
<html lang="zh"
      style="font-size: 20px;<?= background::image() == false ? 'background:linear-gradient(to right, #bdc3c7, #2c3e50);' : background::image() ?>">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover,user-scalable=no">
    <script>
		document.documentElement.style.fontSize = document.documentElement.clientWidth / 750 * 40 + "px";
    </script>
    <meta name="format-detection" content="telephone=no">
    <title><?= $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <link href="<?php echo $cdnpublic; ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnpublic; ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/foxui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/style.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/foxui.diy.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/iconfont.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/style(1).css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/iconfont.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/detail.css">
    <link rel="shortcut icon" href="<?= ROOT_DIR ?>assets/favicon.ico" type="image/x-icon"/>
    <link href="<?php echo $cdnpublic; ?>limonte-sweetalert2/7.33.1/sweetalert2.min.css" rel="stylesheet">
    <link href="<?php echo $cdnpublic; ?>animate.css/3.7.2/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layui/css/layui.css"/>
    <link href="<?php echo $cdnpublic; ?>Swiper/4.5.1/css/swiper.min.css" rel="stylesheet">
    <link href="<?php echo $cdnserver; ?>assets/layui/css/layui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= ROOT_DIR ?>assets/css/Global.css">
</head>

<style>
    .fix-iphonex-bottom {
        padding-bottom: 34px;
    }
</style>

<style>
    select {
        border: solid 1px #000;
        appearance: none;
        -moz-appearance: none;
        -webkit-appearance: none;
        background: none;
        padding-right: 14px;
    }

    select::-ms-expand {
        display: none;
    }

    .fui-page,
    .fui-page-group {
        -webkit-overflow-scrolling: auto;
    }

    .fui-cell-group .fui-cell .fui-input {
        display: inline-block;
        width: 70%;
        height: 32px;
        line-height: 1.5;
        margin: 0 auto;
        padding: 2px 7px;
        font-size: 12px;
        border: 1px solid #dcdee2;
        border-radius: 4px;
        color: #515a6e;
        background-color: #fff;
        background-image: none;
        cursor: text;
        transition: border .2s ease-in-out, background .2s ease-in-out, box-shadow .2s ease-in-out;
    }

    .btnee {
        width: 20%;
        float: right;
        margin-top: -2.8em;
    }

    .fui-cell-group .fui-cell .fui-cell-label1 {
        padding: 0 0.4rem;
        line-height: 0.7rem;
    }

    .fui-cell-group .fui-cell.must .fui-cell-label:after {
        top: 40%;
    }

    /*支付方式*/
    .payment-method {
        position: fixed;
        bottom: 0;
        background: white;
        width: 100%;
        padding: 0.75rem 0.7rem;
        z-index: 1000 !important;
    }

    .payment-method .title {
        font-size: 0.75rem;
        text-align: center;
        color: #333333;
        line-height: 0.75rem;
        margin-bottom: 1rem;
    }

    .payment-method .title span {
        height: 0.75rem;
        position: absolute;
        right: 0.3rem;
        width: 2rem;
    }

    .payment-method .title .close:before {
        font-family: 'iconfont';
        content: '\e654';
        display: inline-block;
        transform: scale(1.5);
        color: #ccc;

    }

    .payment-method .payment {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        padding: 0.7rem 0;
    }

    .payment-method .payment .icon-weixin1 {
        color: #5ee467;
        font-size: 1.3rem;
        margin-right: 0.4rem;
    }

    .payment-method .payment .icon-zhifubao1 {
        color: #0b9ff5;
        font-size: 1.5rem;
        margin-right: 0.4rem;
    }

    .icon-zhifubao1::before {
        margin-left: 1px;
    }

    .payment-method .payment .paychoose {
        font-size: 1.2rem;
    }

    .payment-method .payment .icon-xuanzhong4 {
        color: #2e8cf0;
    }

    .payment-method .payment .icon-option_off {
        color: #ddd;
    }

    .payment-method .payment .paytext {
        flex: 1;
        font-size: 0.8rem;
        color: #333;
    }

    .payment-method .layui-form-item button {
        margin-top: 0.8rem;
        background: #2e8cf0;
        color: white;
        letter-spacing: 1px;
        font-size: 0.7rem;
        border: none;
        outline: none;
        width: 17.25rem;
        height: 1.75rem;
        border-radius: 1.75rem;
    }

    .input_select {
        flex: 1;
        height: 1.5rem;
        border-radius: 2px;
        border: none;
        border-bottom: 1px solid #eee;
        outline: none;
        margin-left: 0.4rem;
    }

    img.logo {
        width: 22px;
        margin: -2px 5px 0 5px;
    }

    html {
        background-color: #E3E3E3;
        font-size: 14px;
        color: #000;
        font-family: '微软雅黑'
    }

    a,
    a:hover {
        text-decoration: none;
    }

    pre {
        font-family: '微软雅黑'
    }

    .box {
        padding: 20px;
        background-color: #fff;
        margin: 50px 100px;
        border-radius: 5px;
    }

    .box a {
        padding-right: 15px;
    }

    #about_hide {
        display: none
    }

    .layer_text {
        background-color: #fff;
        padding: 20px;
    }

    .layer_text p {
        margin-bottom: 10px;
        text-indent: 2em;
        line-height: 23px;
    }

    .button {
        display: inline-block;
        *display: inline;
        *zoom: 1;
        line-height: 30px;
        padding: 0 20px;
        background-color: #56B4DC;
        color: #fff;
        font-size: 14px;
        border-radius: 3px;
        cursor: pointer;
        font-weight: normal;
    }

    .photos-demo img {
        width: 200px;
    }

    .layui-layer-content {
        margin: auto;
    }

    * {
        -webkit-overflow-scrolling: touch;
    }

    .pro_content {
        background-image: linear-gradient(130deg, #00F5B2, #1FC3FF, #00dbde);
        height: 120px;
        position: relative;
        margin-bottom: 4rem;
        background-size: 300%;
        animation: bganimation 10s infinite;
    }

    @keyframes bganimation {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    #picture {
        padding-top: 1em;
    }

    #picture div {
        text-align: center;
    }

    #picture img {
        width: auto;
        max-height: 38vh;
        margin: auto;
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

    .btn {
        margin: 0.6rem 0 0.6rem 0 !important;
    }
</style>

<body ontouchstart="" style="overflow: auto;height: auto !important;max-width: 600px;margin: auto;">
<div id="appshop" class="fui-page-group statusbar" style="max-width: 600px;left: auto;">

    <div class="Coupons" @click="Coupon" style="display: none;"
         :style="CouponData.length>=1?'display:block;':'display: none;'" title="领取惊喜优惠券！">
        <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"/>
        <br>
        <span style="font-size: 0.5em;">商品优惠券</span>
    </div>
    <div class="fui-page  fui-page-current " style="overflow: inherit">
        <div id="container" class="fui-content " style="background-color: rgb(255, 255, 255); padding-bottom: 60px;">
            <div class="pro_content" style="margin-bottom: 3.5rem;">
                <div class="list_item_box" style="top: 53px;">
                    <div class="bor_detail" style="display: none;"
                         :style="Goods.name!=-1?'display:block;':'display: none;'">
                        <div class="thumb">
                            <img :src="Goods.image[0]">
                        </div>
                        <div class="pro_right fl">
                            <span v-html="Goods.name"></span>
                            <span id="level" class="list_item_title" style="color:#f00f00"
                                  v-html="Goods.level"></span>
                            <div class="list_tag">
                                <div class="price">
                                    <span class="t_price pay_prices" v-if="Goods.Seckill == -1">
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
                                    </span>

                                    <span class="t_price pay_prices" v-else>
                                        <font v-if="Goods.price == 0 && Goods.points == 0" color="#18B566">免费</font>
                                            <font v-else>
                                                <font color="red" v-if="Goods.method == 1 || Goods.method == 2">
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

                                    <span class="stock" style="">剩余:{{Goods.quota}}份</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content_friends" v-if="Goods.Seckill !== -1">
                <div class="top_tit" style="color: red">
                    限购秒杀
                </div>
                <div class="hd_intro editor">
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
            <marquee style="margin:1em;">
                <?php if ($UserData == false) { ?>
                    <a href="./?mod=route&p=User" style="color: salmon">您当前未登录，点击此段文字进行登陆后再下单，售后更加快捷方便哦~</a>
                <?php } ?>
            </marquee>
            <div class="content_friends">
                <div class="top_tit">
                    商品说明
                </div>
                <div class="hd_intro editor" v-html="Goods.docs"></div>
            </div>

            <div class="swiper-container" id="swiper"
                 style="width: 94%;max-height: 42vh;box-shadow: 1px 1px 8px #eee;border-radius: 0.3em">
                <div class="swiper-wrapper" id="picture"></div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>

            <div class="assemble-footer footer" style="max-width:600px;">
                <a href="index.php" class="left" style="width: 25% !important;">
                    <div class="wid all">
                        <span class="icon icon-left top"></span>
                        <p>返回</p>
                    </div>
                </a>
                <a @click="ShareGoods" class="left" style="width: 25% !important;border-left: solid 1px #eee">
                    <div class="wid all">
                        <span class="icon icon-share top"></span>
                        <p>分享</p>
                    </div>
                </a>
                <a class="middle" href="javascript:$('#paymentmethod').show()"
                   style="background-color: #f00000d7 !important">
                    <div class="wid y_buy " style="background-color: #f00000d7">
                        <span class="pay_price" style="line-height: 1.5rem !important;">购买此商品</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div id="paymentmethod" class="common-mask" style="display:none;max-width: 600px">
        <div class="payment-method" style="position: absolute;max-height:70vh;">
            <div class="title" style="color: salmon;font-size: 1.3em;">
                下单信息确认
                <span class="close" onclick="$('#paymentmethod').hide()"></span>
            </div>
            <hr>
            <div style="height: 52vh;overflow:hidden;overflow-y: auto">
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width: 100%;text-align: left;padding:0">商品价格</label>
                    <div class="layui-input-">
                        <input type="text" disabled :value="NumRound(Goods.price * num) + '元'" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width: 100%;text-align: left;padding:0">剩余份数</label>
                    <div class="layui-input-">
                        <input type="text" :value="Goods.quota + '份'" disabled lay-verify="required"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item" style="display: none;"
                     :style="Goods.repetition==1?'display:block;':'display: none;'">
                    <label class="layui-form-label" style="width: 100%;text-align: left;padding:0">下单份数</label>
                    <div class="input-group">
                                <span class="input-group-btn">
                                    <button @click="PriceLs(1)" class="layui-btn layui-btn-sm"
                                            style="width: 2em;margin-top: -0.1em;border-radius: 0;">━</button>
                                </span>
                        <input @input="PriceLs" class="layui-input" v-model.number="num" type="number"
                               :min="min" :max="max"/>
                        <span class="input-group-btn">
                                    <button @click="PriceLs(2)" class="layui-btn layui-btn-sm"
                                            style="width: 2em;margin-top: -0.1em;border-radius: 0;">✚</button>
                                </span>
                    </div>
                </div>

                <div v-for="(item, index) of Goods.input" :key="index">
                    <div class="layui-form-item" v-if="item.state == 1">
                        <label class="layui-form-label" style="width: 100%;text-align: left;padding:0">{{ item.Data
                            }}</label>
                        <input type="text" v-model="form[index]" class="layui-input"
                               :placeholder="'请将' + item.Data + '填写完整!'">
                    </div>

                    <div class="layui-form-item" v-if="item.state == 2">
                        <label class="layui-form-label" style="width: 100%;text-align: left;padding:0">{{ item.Data[0]
                            }}</label>
                        <input type="text" v-model="form[index]" class="layui-input"
                               @blur="extend(index, item.Data[1].way, item.Data[1].url,2)"
                               :placeholder="item.Data[1].placeholder"/>
                        <div class="layui-btn layui-btn-sm layui-btn-normal btnee"
                             style="z-index: 10;"
                             @click="extend(index, item.Data[1].way, item.Data[1].url)">{{ item.Data[1].name}}
                        </div>
                    </div>

                    <div class="layui-form-item" v-if="item.state == 3">
                        <label class="layui-form-label" style="width: 100%;text-align: left;padding:0">{{ item.Data
                            }}</label>
                        <input type="text" v-model="form[index]" class="layui-input"
                               :placeholder="'请将' + item.Data + '填写完整!'"
                        />
                        <div class="layui-btn layui-btn-sm layui-btn-normal btnee"
                             style="z-index: 10;"
                             id="Selectaddress" :index="index">选择地址
                        </div>
                    </div>

                    <div class="layui-form-item" v-if="item.state == 4">
                        <label class="layui-form-label" style="width: 100%;text-align: left;padding:0">{{ item.Data[0]
                            }}</label>
                        <div class="layui-input-">
                            <select v-model="form[index]">
                                <option v-for="o in item.Data[1]" :value="o">{{ o }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group" v-if="item.state == 5">
                        <div v-if="Object.values(Combination).length === 0"
                             style="color:red">
                            商品下单参数未配置完善，请联系客服处理！
                        </div>
                        <div v-if="Object.values(Combination).length >= 1" class="layui-card-body" style="padding:0;">
                            <fieldset v-for="(item, index) in SkuBtn"
                                      class="layui-elem-field layui-field-title site-title">
                                <legend>{{ index }}</legend>
                                <div style="margin-top:0.5em">
                                    <div style="display:inline-block;" v-for="(ts, is) in item" :key="is">
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

                    <div class="layui-form-item" v-if="item.state == 6">
                        <label class="layui-form-label" style="width: 100%;text-align: left;padding:0">{{ item.Data[0]
                            }}</label>
                        <input type="text" v-model="form[index]" class="layui-input"
                               :placeholder="item.Data[1].placeholder"/>
                        <div class="layui-btn layui-btn-sm layui-btn-normal btnee"
                             style="z-index: 10;"
                             id="QrId" :url="item.Data[1].url" :index="index">{{ item.Data[1].name}}
                        </div>
                    </div>

                </div>
                <div class="form-group" style="text-align: center">
                    <button type="button" @click="submit"
                            style="margin:auto;text-align: center;    line-height: 1.75rem;margin-top: 0.4rem;    background: #ff0404;    color: white;    letter-spacing: 1px;    font-size: 0.7rem;    border: none;    outline: none; height: 1.75rem;;"
                            id="submit" class="btn btn-primary btn-block">
                        提交订单
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script src="<?php echo $cdnpublic; ?>jquery/1.12.4/jquery.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>layer/2.3/layer.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
    <script src="<?php echo $cdnpublic; ?>jquery/3.4.1/jquery.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>Swiper/4.5.1/js/swiper.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>limonte-sweetalert2/7.33.1/sweetalert2.min.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/js/axios.min.js"></script>
    <script>
		var gid = <?= $_QET['gid'] ?>;
    </script>
    <script src="./assets/js/distpicker.min.js"></script>
    <script src="./assets/template/cool/assets/js/shop.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>
