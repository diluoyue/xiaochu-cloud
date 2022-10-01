<?php

/**
 * 云商城样式模板[商品兑换]
 */

if (!defined('IN_CRONLITE')) die;
$title = '商品购买 - ' . $conf['sitename'];
include 'template/cloud/header.php';
?>
<link href="<?php echo $cdnpublic; ?>animate.css/3.7.2/animate.min.css" rel="stylesheet">
<link href="<?php echo $cdnpublic; ?>limonte-sweetalert2/7.33.1/sweetalert2.min.css" rel="stylesheet">
<link href="<?php echo $cdnpublic; ?>Swiper/4.5.1/css/swiper.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT_DIR ?>assets/css/Global.css">
<style>
    input,
    select {
        border: none !important;
        border-bottom: solid #f4f4f4 1px !important;
        outline: none !important;
    }

    .swiper-slide {
        text-align: center;
    }

    .swiper-slide img {
        width: auto;
        max-height: 18em;
    }

    .GoodsImage {
        width: 8.8em;
        height: 8.8em;
        margin: 0em auto 1em;
        box-shadow: 3px 3px 16px #ccc;
        border-radius: 0.3em;
        overflow: hidden;
    }

    .GoodsImage img {
        width: 100% !important;
        height: 100% !important;
    }

    img.logo {
        width: 22px;
        margin: -2px 5px 0 5px;
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
<!-- 兑换界面 -->
<div class="content__inner content__inner--sm" id="appshop">
    <div class="card new-contact">

        <div class="card-block" v-if="Goods.Seckill !== -1">

            <div style="width: 100%;height: 40px;background-color: #ff007f;padding-left: 10px;color: #fff"
                 v-if="Goods.Seckill.state === 1 && Goods.Seckill.attend < Goods.Seckill.astrict"
                 class="alert alert-warning">
                限购秒杀活动将于{{Goods.Seckill.end}}
            </div>
            <div v-else-if="Goods.Seckill.state === 1 && Goods.Seckill.attend >= Goods.Seckill.astrict"
                 style="width: 100%;height: 40px;background-color: #949494;padding-left: 10px;color: #fff"
                 class="alert alert-warning">
                限购秒杀活动参与人数已达上限
            </div>
            <div v-else class="alert alert-warning"
                 style="width: 100%;height: 40px;background-color: #949494;padding-left: 10px;color: #fff"
            >
                限购秒杀活动将于{{Goods.Seckill.start}}
            </div>

        </div>

        <div class="swiper-container" style="width: 100%;max-height: 18em;padding-top: 1em">
            <div class="swiper-wrapper" id="picture"></div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
        <div class="card-body" style="display: none;" :style="Goods.name!=-1?'display:block;':'display: none;'">
            <div class="form-group">
                <div class="input-group">
                    <label class="col-form-label">当前商品</label>
                    <input type="text" class="form-control" :value="Goods.name" readonly="readonly"
                           style="border-radius: 0.3em;background-color: #fff;center;color:#32c787;font-weight:bold"/>
                    <i class="form-group__bar"></i>
                </div>
            </div>
            <div class="form-group" v-if="Goods.Seckill === -1">
                <div class="input-group">
                    <label class="col-form-label" id="level" @click="LeveLs">{{ Goods.level }}</label>
                    <div style="height:2em;width:60%;display:inline-block;line-height:2.35em;margin-left:1em;">
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
                        → {{Goods.quantity * num}} {{Goods.units}}
                    </div>
                </div>
            </div>
            <div class="form-group" v-else>
                <div class="input-group">
                    <label class="col-form-label" id="level" @click="LeveLs">{{ Goods.level }}</label>
                    <div style="height:2em;width:60%;display:inline-block;line-height:2.35em;margin-left:1em;">
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
                        → {{Goods.quantity * num}} {{Goods.units}}
                    </div>
                </div>
            </div>
            <div class="form-group" v-if="Goods.Seckill !== -1">
                <div class="input-group">
                    <label class="col-form-label">活动内容</label>
                    <input type="text" class="form-control" :value="'降价 ' + Goods.Seckill.depreciate + '%'"
                           readonly="readonly"
                           style="border-radius: 0.3em;background-color: #fff;color: red"/>
                    <i class="form-group__bar"></i>
                </div>
            </div>
            <div class="form-group" v-if="Goods.Seckill !== -1">
                <div class="input-group">
                    <label class="col-form-label">活动名额</label>
                    <input type="text" class="form-control"
                           :value="'还剩 ' + (Goods.Seckill.astrict - Goods.Seckill.attend) + '个名额'" readonly="readonly"
                           style="border-radius: 0.3em;background-color: #fff;color: red"/>
                    <i class="form-group__bar"></i>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <label class="col-form-label">剩余库存</label>
                    <input type="text" class="form-control" :value="Goods.quota + ' 份'" readonly="readonly"
                           style="border-radius: 0.3em;background-color: #fff"/>
                    <i class="form-group__bar"></i>
                </div>
            </div>

            <div class="form-group" style="display: none;"
                 :style="Goods.repetition==1?'display:block;':'display: none;'">
                <div class="input-group">
                    <span class="input-group-btn">
                        <input type="button" @click="PriceLs(1)" class="btn btn-info" style="border-radius: 0px;"
                               value="━">
                    </span>
                    <input @input="PriceLs" class="form-control" v-model.number="num" type="number"
                           :min="min" :max="max"/>
                    <span class="input-group-btn">
                        <input type="button" @click="PriceLs(2)" class="btn btn-info" style="border-radius: 0px;"
                               value="✚">
                    </span>
                </div>
            </div>

            <div class="form-group" v-for="(item, index) of Goods.input" :key="index">
                <div class="input-group" v-if="item.state == 1">
                    <label class="col-form-label">{{ item.Data }}</label>
                    <input type="text" v-model="form[index]" class="form-control"
                           :placeholder="'请将' + item.Data + '填写完整!'">
                </div>

                <div class="input-group" v-if="item.state == 2">
                    <label class="col-form-label">{{ item.Data[0] }}</label>
                    <input type="text" v-model="form[index]" class="form-control"
                           @blur="extend(index, item.Data[1].way, item.Data[1].url,2)"
                           :placeholder="item.Data[1].placeholder"/>
                    <div class="btn btn-danger input-group-addon" style="background-color: #71bcff;z-index: 10;"
                         @click="extend(index, item.Data[1].way, item.Data[1].url)">{{ item.Data[1].name}}
                    </div>
                </div>

                <div class="input-group" v-if="item.state == 3">
                    <label class="col-form-label">{{ item.Data }}</label>
                    <input type="text" v-model="form[index]" class="form-control"
                           :placeholder="'请将' + item.Data + '填写完整!'"
                    />
                    <div class="btn btn-danger input-group-addon" style="background-color: #71bcff;z-index: 10;"
                         id="Selectaddress" :index="index">选择地址
                    </div>
                </div>

                <div class="input-group" v-if="item.state == 4">
                    <label class="col-form-label">{{ item.Data[0] }}</label>
                    <select v-model="form[index]" class="form-control">
                        <option v-for="o in item.Data[1]" :value="o">{{ o }}</option>
                    </select>
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

                <div class="input-group" v-if="item.state == 6">
                    <label class="col-form-label">{{ item.Data[0] }}</label>
                    <input type="text" v-model="form[index]" class="form-control"
                           :placeholder="item.Data[1].placeholder"/>
                    <div class="btn btn-danger input-group-addon" style="background-color: #71bcff;z-index: 10;"
                         id="QrId" :url="item.Data[1].url" :index="index">{{ item.Data[1].name}}
                    </div>
                </div>
            </div>

            <div v-if="Goods.docs!=''" v-html="Goods.docs" class="alert alert-info xiaoxuan-tip editor"
                 style="background: linear-gradient(to right, rgb(255, 153, 102), rgb(255, 94, 98)); font-weight: bold; color: white;">
            </div>
            <div class="btn-group btn-block">
                <button type="btn" @click="submit" class="btn btn-success"
                        style="width: 60%;background-color: #ff6a80">提交订单
                </button>
                <button type="text" @click="ShareGoods" class="btn btn-danger"
                        style="width: 20%;background-color: #71bcff">分享 <i
                            class="layui-icon layui-icon-share  layui-hide-xs"></i>
                </button>
            </div>
        </div>
        <div class="card-block col-md-12" style="display: none;cursor: pointer;"
             :style="CouponData.length>=1?'display:block;':'display: none;'">
            <div class="alert alert-warning " @click="Coupon()">
                <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png" style="width:1.6em;height:1.6em"/>
                您有{{ CouponData.length }}个优惠券待领取使用
            </div>
        </div>
    </div>
</div>

<?php include 'template/cloud/bottom.php'; ?>
<script src="<?php echo $cdnpublic; ?>limonte-sweetalert2/7.33.1/sweetalert2.min.js"></script>
<script src="<?php echo $cdnpublic; ?>Swiper/4.5.1/js/swiper.min.js"></script>
<!-- 结束 -->
<script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
<script src="<?php echo $cdnserver; ?>assets/js/axios.min.js"></script>
<script src="./assets/js/distpicker.min.js"></script>
<script>
	var gid = <?= $_QET['gid'] ?>;
</script>
<script src="./assets/template/cloud/assets/js/shop.js?vs=<?= $accredit['versions'] ?>"
        type="text/javascript"></script>
</body>

</html>
