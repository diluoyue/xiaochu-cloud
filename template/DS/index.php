<?php
if (!defined('IN_CRONLITE')) {
    die;
}
$User = login_data::user_data();
?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <title>
        <?php echo $conf['sitename'] ?> -
        <?php echo $conf['title'] ?>
    </title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    <meta name="description" content="<?php echo $conf['description'] ?>">
    <link href="<?php echo $cdnpublic; ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnpublic; ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo $cdnserver; ?>assets/template/DS/assets/css/oneui.css">
    <link href="<?php echo $cdnserver; ?>assets/layui/css/layui.css" rel="stylesheet"/>
    <script src="<?php echo $cdnpublic; ?>modernizr/2.8.3/modernizr.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>jquery/1.12.4/jquery.min.js"></script>
    <link rel="stylesheet" href="<?= ROOT_DIR ?>assets/css/Global.css">
    <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic; ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<style>
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

    .Coupons {
        position: fixed;
        bottom: 20%;
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

<body
        style="<?= background::image() == false ? 'background:linear-gradient(to right, #bdc3c7, #2c3e50);' : background::image() ?>">
<div style="padding-top:6px;" id="app">
    <div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block" style="float: none;">
        <!--????????????-->
        <div class="modal fade" align="left" id="hi-Modal" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">
                            <?php echo $conf['sitename'] ?>
                        </h4>
                    </div>
                    <div class="modal-body" v-html="InformData.PopupNotice"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">?????????</button>
                    </div>
                </div>
            </div>
        </div>
        <!--????????????-->
        <!--??????-->
        <div class="modal fade" align="left" id="anounce" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">??</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">??????</h4>
                    </div>
                    <div class="modal-body" v-html="InformData.NoticeTop"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">??????</button>
                    </div>
                </div>
            </div>
        </div>
        <!--??????-->
        <!--??????????????????-->
        <div class="modal fade" align="left" id="cxsm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">??????????????????????????????????????????</h4>
                    </div>
                    <li class="list-group-item">
                        <font color="red">???????????????????????????????????????????????????????????????????????????????????????</font>
                    </li>
                    <li class="list-group-item">?????????????????????QQ??????????????????????????????QQ????????????????????????</li>
                    <li class="list-group-item">???????????????????????????????????????????????????????????????????????????QQ?????????????????????</li>
                    <li class="list-group-item">??????????????????????????????????????????????????????????????????????????????????????????????????????</li>
                    <li class="list-group-item">
                        <font color="red">??????????????????????????????????????????????????????????????????????????????????????????????????????????????????</font>
                    </li>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">??????</button>
                    </div>
                </div>
            </div>
        </div>
        <!--??????????????????-->

        <!--????????????-->
        <div class="block block-link-hover3" style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.25);">
            <div class="block-content block-content-full text-center bg-image"
                 style="background-image: url('<?php echo $cdnserver; ?>assets/template/DS/assets/head3.jpg');background-size: 100% 100%;">
                <div>
                    <div>
                        <img class="img-avatar img-avatar80 img-avatar-thumb"
                             src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100">
                    </div>
                </div>
            </div>
            <div class="block-content block-content-mini block-content-full">
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a class="btn btn-default" data-toggle="modal" href="#anounce"><i
                                    class="fa fa-bullhorn"></i>&nbsp;<span style="font-weight:bold">??????</span></a>
                    </div>

                    <a v-if="InformData.Appurl!=''" :href="InformData.Appurl" target="_blank"
                       class="btn btn-effect-ripple btn-default"><i class="fa fa-android"></i> <span
                                style="font-weight:bold">?????????</span></a>

                    <a v-else href="#customerservice" target="_blank" data-toggle="modal" class="btn btn-default"><i
                                class="fa fa-qq"></i>&nbsp;<span style="font-weight:bold">??????</span></a>


                    <?php if ($User != false) { ?>
                        <div class="btn-group">
                            <a class="btn btn-default" data-toggle="modal" href="user/"><i
                                        class="fa fa-users fa-1x"></i>&nbsp;????????????</a>
                        </div>
                    <?php } else { ?>
                        <div class="btn-group">
                            <a class="btn btn-default" data-toggle="modal" href="user/login.php"><i
                                        class="fa fa-users fa-1x"></i>&nbsp;??????</a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!--????????????-->

        <div class="block" style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.25);">

            <ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
                <li style="width: 25%;" align="center" <?= (!isset($_QET['query']) ? 'class="active"' : '') ?>><a
                            href="#shop" data-toggle="tab"><span style="font-weight:bold"><i
                                    class="fa fa-shopping-bag fa-fw"></i> ??????</span></a></li>
                <li v-if="ActivitiesGoods.length>=1" style="width: 25%;" align="center"><a
                            href="#shopMS" data-toggle="tab"><span style="font-weight:bold;color: red"><i
                                    class="fa fa-shopping-bag fa-fw"></i> ??????</span></a></li>
                <li style="width: 25%;" align="center" <?= (!isset($_QET['query']) ? '' : 'class="active"') ?>><a
                            href="#search" data-toggle="tab" id="tab-query"><span style="font-weight:bold"><i
                                    class="fa fa-search"></i> ??????</span></a></li>
                <li style="width: 25%;" align="center" <?php if ($conf['ShutDownUserSystem'] != 1) { ?>class="hide"
                    <?php } ?>><a href="#Substation" data-toggle="tab"><span style="font-weight:bold">
								<font color="#ff0000"><i class="fa fa-coffee fa-fw"></i> ??????
							</span></font></a>
                </li>
                <li style="width: 25%;" align="center"><a href="#more" data-toggle="tab"><span
                                style="font-weight:bold"><i class="fa fa-folder-open"></i>
								??????</span></a></li>
            </ul>
            <!--TAB??????-->
            <div class="Coupons" @click="Coupon" style="display: none;"
                 :style="CouponData.length>=1?'display:block;':'display: none;'" title="????????????????????????">
                <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"/>
                <br>
                <span style="font-size: 0.5em;">{{ (type==3?'???????????????':(type==2?'???????????????':'???????????????')) }}</span>
            </div>
            <div class="block-content tab-content">
                <!--????????????-->
                <div class="tab-pane <?= (!isset($_QET['query']) ? 'active' : '') ?>" id="shop">
                    <div class="GoodsImage" style="display: none;"
                         :style="ImagesSrc!=-1?'display: block;':'display: none;'">
                        <img :src="ImagesSrc" :alt="Goods.name"/>
                    </div>
                    <div>
                        <div class="form-group" id="display_searchBar">
                            <div class="input-group">
                                <div class="input-group-addon">????????????</div>
                                <input v-model="GoodsSeek" type="text" class="form-control" placeholder="????????????????????????">
                                <div class="input-group-addon"><span class="glyphicon glyphicon-search onclick"
                                                                     title="??????" @click="SearchGoods"></span></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">????????????</div>
                                <select v-model="cid" @change='GetGoods' class="form-control">
                                    <option value="0">???????????????</option>
                                    <option v-if="cid==-1" value="-1">[{{ GoodsSeek }}]????????????</option>
                                    <option v-for="(item,index) in ClassData" :value="item.cid">{{ item.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div v-if="cid!=0" class="form-group" style="display: none;"
                             :style="cid!=0?'display:block;':'display: none;'">
                            <div class="input-group">
                                <div class="input-group-addon">????????????</div>
                                <select v-model="gid" @change='GetGoodsDetails' class="form-control">
                                    <option value="0">???????????????</option>
                                    <option v-for="(item,index) in GoodsData" :value="item.gid">{{ item.name }}
                                    </option>
                                </select>
                                <span v-if="gid!=0" @click="ShareGoods" class="input-group-btn" title="?????????????????????">
										<button class="btn btn-info glyphicon glyphicon-new-window">
										</button>
									</span>
                            </div>
                        </div>

                        <div style="display: none;"
                             :style="Goods.name != -1?'display:block;':'display: none;'">
                            <div class="form-group" style="color:#4169E1;font-weight:bold">
                                <div class="input-group">
                                    <div class="input-group-addon">????????????</div>
                                    <input v-if="Goods.name != -1&&Goods.Seckill !== -1" type="text"
                                           class="form-control"
                                           :value="(NumRound(Goods.price) - NumRound(Goods.price * Goods.Seckill.depreciate) / 100).toFixed(Goods.accuracy) * num"
                                           style="color:#ff3b31;font-weight:bold" disabled="">
                                    <input v-else-if="Goods.name != -1" type="text" class="form-control"
                                           :value="Goods.price * num"
                                           style="color:#4169E1;font-weight:bold" disabled="">

                                    <span v-if="gid!=0" id="level" class="input-group-btn" title="??????????????????">
											<button class="btn btn-success glyphicon glyphicon glyphicon-usd">
											</button>
										</span>
                                </div>
                            </div>
                            <div class="form-group" v-if="Goods.name != -1&&Goods.Seckill !== -1">
                                <div class="input-group">
                                    <div class="input-group-addon">????????????</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           :value="'?????? ' + Goods.Seckill.depreciate + '%'"
                                           class="form-control" disabled="">
                                </div>
                            </div>
                            <div class="form-group" v-if="Goods.name != -1&&Goods.Seckill !== -1">
                                <div class="input-group"
                                     v-if="Goods.Seckill.state === 1 && Goods.Seckill.attend < Goods.Seckill.astrict">
                                    <div class="input-group-addon">????????????</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           :value="Goods.Seckill.end"
                                           class="form-control" disabled="">
                                </div>
                                <div class="input-group"
                                     v-else-if="Goods.Seckill.state === 1 && Goods.Seckill.attend >= Goods.Seckill.astrict">
                                    <div class="input-group-addon">????????????</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           value="?????????????????????????????????????????????????????????"
                                           class="form-control" disabled="">
                                </div>
                                <div class="input-group" v-else>
                                    <div class="input-group-addon">????????????</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           :value="Goods.Seckill.start"
                                           class="form-control" disabled="">
                                </div>
                            </div>
                            <div class="form-group" v-if="Goods.name != -1&&Goods.Seckill !== -1">
                                <div class="input-group">
                                    <div class="input-group-addon">????????????</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           :value="'??? ' + (Goods.Seckill.astrict - Goods.Seckill.attend) + '???'"
                                           class="form-control" disabled="">
                                </div>
                            </div>
                            <div class="form-group" style="display: none;"
                                 :style="Goods.method==1?'display:block;':'display: none;'">
                                <div class="input-group">
                                    <div class="input-group-addon">????????????</div>
                                    <input type="text" style="color:#4169E1;font-weight:bold"
                                           :value="num * Goods.quantity + '' + Goods.units"
                                           class="form-control" disabled="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon">????????????</div>
                                    <input type="text" :value="Goods.quota + ' ???'" class="form-control"
                                           disabled="">
                                </div>
                            </div>
                            <div class="form-group" style="display: none;"
                                 :style="Goods.repetition==1?'display:block;':'display: none;'">
                                <div class="input-group">
                                    <div class="input-group-addon">????????????</div>
                                    <span class="input-group-btn">
											<input type="button" @click="PriceLs(1)" class="btn btn-info"
                                                   style="border-radius: 0px;" value="???">
										</span>
                                    <input v-on:input="PriceLs" class="form-control" v-model.number="num"
                                           type="number" :min="Goods.min" :max="Goods.max"/>
                                    <span class="input-group-btn">
											<input type="button" @click="PriceLs(2)" class="btn btn-info"
                                                   style="border-radius: 0px;" value="???">
										</span>
                                </div>
                            </div>

                            <div class="form-group" v-for="(item, index) of Goods.input" :key="index">
                                <div class="input-group" v-if="item.state == 1">
                                    <div class="input-group-addon">{{ item.Data }}</div>
                                    <input type="text" v-model="form[index]" class="form-control"
                                           :placeholder="'??????' + item.Data + '????????????!'">
                                </div>

                                <div class="input-group" v-if="item.state == 2">

                                    <div class="input-group-addon">{{ item.Data[0] }}</div>
                                    <input type="text" v-model="form[index]" class="form-control"
                                           @blur="extend(index, item.Data[1].way, item.Data[1].url,2)"
                                           :placeholder="item.Data[1].placeholder"/>
                                    <div class="input-group-addon onclick"
                                         style="z-index: 10"
                                         @click="extend(index, item.Data[1].way, item.Data[1].url)">{{
                                        item.Data[1].name }}
                                    </div>
                                </div>

                                <div class="input-group" v-if="item.state == 3">
                                    <div class="input-group-addon">{{ item.Data }}</div>
                                    <input type="text" v-model="form[index]" class="form-control"
                                           :placeholder="'??????' + item.Data + '????????????!'"
                                    />
                                    <div class="input-group-addon onclick"
                                         style="z-index: 10"
                                         id="Selectaddress" :index="index">
                                        ????????????
                                    </div>
                                </div>

                                <div class="input-group" v-if="item.state == 4">
                                    <div class="input-group-addon">{{ item.Data[0] }}</div>

                                    <select v-model="form[index]" class="form-control">
                                        <option v-for="o in item.Data[1]" :value="o">{{ o }}</option>
                                    </select>
                                </div>
                                <div v-if="item.state == 5">
                                    <div style="color: red;"
                                         v-if="Object.values(Combination).length === 0"
                                    >
                                        ????????????????????????????????????????????????????????????
                                    </div>
                                    <div v-if="Object.values(Combination).length >= 1" class="layui-card-body"
                                         style="padding:0;"
                                    >
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
                                                            style="margin: 0.3em;border-radius: 0.5em;border: none;box-shadow:1px 1px 18px rgb(128, 190, 219);">
                                                        {{ is }}
                                                    </button>
                                                    <button
                                                            type="button"
                                                            class="layui-btn layui-btn-sm layui-btn-primary"
                                                            v-else-if="FormSp[index] != is && ts.type == 1"
                                                            @click="BtnClick(index, is, ts.type)"
                                                            style="margin: 0.3em;border-radius: 0.5em;border: none;box-shadow:1px 1px 18px rgb(128, 190, 219);">
                                                        {{ is }}
                                                    </button>
                                                    <button
                                                            type="button"
                                                            class="layui-btn layui-btn-sm layui-btn-disabled"
                                                            v-else
                                                            @click="BtnClick(index, is, ts.type)"
                                                            style="margin: 0.3em;border-radius: 0.5em;border: none;box-shadow:1px 1px 18px rgb(128, 190, 219);">
                                                        {{ is }}
                                                    </button>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="input-group" v-if="item.state == 6">

                                    <div class="input-group-addon">{{ item.Data[0] }}</div>
                                    <input type="text" v-model="form[index]" class="form-control"
                                           :placeholder="item.Data[1].placeholder"/>
                                    <div class="input-group-addon onclick"
                                         style="z-index: 10"
                                         id="QrId" :url="item.Data[1].url" :index="index">{{
                                        item.Data[1].name }}
                                    </div>
                                </div>

                            </div>
                            <div class="alert alert-success animated rubberBand editor"
                                 style="background: #ff9966;background: -webkit-linear-gradient(to right, rgb(255, 153, 102), rgb(255, 94, 98));background: linear-gradient(to right, rgb(255, 153, 102), rgb(255, 94, 98));font-weight: bold;color:white;"
                                 v-html="Goods.docs"></div>
                            <div class="btn-group btn-group-justified form-group">
                                <a type="submit" @click="submit" class="btn btn-block btn-primary">????????????</a>
                            </div>
                        </div>

                    </div>
                </div>
                <!--????????????-->

                <!--????????????-->
                <div class="tab-pane" id="shopMS" v-if="ActivitiesGoods.length>=1">
                    <div v-if="ActivitiesGoods.length>=1" style="padding-bottom: 1em"
                         class="layui-row layui-col-space1">
                        <div class="layui-col-xs4 layui-col-sm3" style="text-align: center"
                             v-for="(item,index) in ActivitiesGoods">
                            <a class="card" :href="'./?gid='+item.gid" style="color: #000">
                                <div class="card-body">
                                    <div v-if="item.Seckill.state == 1 && item.Seckill.attend < item.Seckill.astrict"
                                         class="layui-badge" style="position: absolute;">
                                        ?????????
                                    </div>
                                    <div v-else-if="item.Seckill.state == 1 && item.Seckill.attend >= item.Seckill.astrict"
                                         class="layui-badge layui-bg-gray" style="position: absolute;">
                                        ?????????
                                    </div>
                                    <div v-else class="layui-badge layui-bg-gray" style="position: absolute;">
                                        ?????????
                                    </div>
                                    <div class="layui-row">
                                        <img
                                                :src="item.image"
                                                style="width: 78px; height: 78px; border-radius: 0.5em; box-shadow: rgb(238, 238, 238) 3px 3px 16px;">
                                        <div class="layui-col-xs12" style="font-size: 80%;margin-top: 0.5em;">
                                            <div class="layui-elip">{{item.name}}</div>
                                            <div class="goodsPrice">
                                                <div v-if="PriceS(item)['state'] === 1">
                                        <span style="font-size: 13px;" :style="'color:' + PriceS(item)['color']">???{{
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
                <!--????????????-->

                <!--????????????-->
                <div class="tab-pane <?= (!isset($_QET['query']) ? '' : 'active') ?>" id="search">
                    <ul class="list-group animated bounceIn">
                        <li class="list-group-item">
                            <div class="media">
									<span class="pull-left thumb-sm"><img
                                                src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100"
                                                class="img-circle img-thumbnail img-avatar"></span>
                                <div class="pull-right push-15-t">
                                    <a href="#customerservice" target="_blank" data-toggle="modal"
                                       class="btn btn-sm btn-info">????????????</a>
                                </div>
                                <div class="pull-left push-10-t">
                                    <div class="font-w600 push-5">????????????QQ??????</div>
                                    <div class="text-muted">
                                        <script>
											var online = [];
                                        </script>
                                        <script
                                                src="https://webpresence.qq.com/getonline?Type=1&<?php echo $conf['kfqq'] ?>:"></script>
                                        <script>
											if (online[0] == 0) document.write('<i class="fa fa-clock-o" aria-hidden="true"></i>&nbsp;' + "9:00 - 21:00");
											else document.write('<i class="fa fa-circle text-success"></i>&nbsp;' + "8:00 - 23:00");
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="col-xs-12 well well-sm animation-pullUp" v-if="InformData.NoticeCheck!=''"
                         v-html="InformData.NoticeCheck"></div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">????????????</div>
                            <input type="text" v-model="seek" class="form-control"
                                   placeholder="????????????????????????????????????????????????????????????" required/>
                            <span class="input-group-btn">
									<a href="#cxsm" data-toggle="modal" class="btn btn-warning">
										<i class="glyphicon glyphicon-exclamation-sign"></i>
									</a>
								</span>
                        </div>
                    </div>
                    <div class="btn-group" style="width: 100%;margin-bottom: 1em;">
                        <button type="button" @click="GetOrderList(-2)" class="btn btn-success btn-sm"
                                style="text-shadow: black 1px 1px 1px;width:50%;">??????????????????
                        </button>
                        <button type="button" @click="GetOrderList(-3)" class="btn btn-info btn-sm"
                                style="text-shadow: black 1px 1px 1px;width:50%;">??????????????????
                        </button>
                    </div>

                    <div class="alert alert-warning" <?= ($User == false ? 'style="margin-top: 1em;"'
                        : 'style="display: none;margin-top: 1em;"') ?>>
                        <a href="#" class="close" data-dismiss="alert">
                            &times;
                        </a>
                        <a href="./?mod=route&p=User"
                           style="color: #efa231;"><strong>?????????</strong>??????????????????????????????????????????????????????????????????????????????????????????????????????????????????</a>
                    </div>

                    <div style="margin-top: 1em;display: none;"
                         :style="OrderData.length>=1||seek!=''?'display:block;':'display: none;'">
                        <div class="alert alert-success">
                            <a href="#" class="close" data-dismiss="alert">
                                &times;
                            </a>
                            ????????? {{ OrderData.length }} ?????????
                        </div>
                    </div>

                    <div class="form-group" style="display: none;"
                         :style="OrderData.length>=1?'display:block;':'display: none;'">
                        <center><small>
                                <font color="#ff0000">??????????????????</font>
                            </small></center>
                        <div class="table-responsive">
                            <table class="table table-vcenter table-condensed table-striped">
                                <thead>
                                <tr>
                                    <th>??????</th>
                                    <th>??????</th>
                                    <th>????????????</th>
                                    <th>????????????</th>
                                    <th>??????</th>
                                    <th>????????????</th>
                                    <th class="hidden-xs">????????????</th>

                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item,index) in OrderData" style="white-space:nowrap;">
                                    <td>
												<span title="??????????????????" @click="GetQuery(item.id,item.order)"
                                                      class="btn btn-warning btn-xs">??????</span>
                                        <a target="_blank" :href="Tracking + item.remark"
                                           v-if="item.RemarkType==2" title="??????????????????" class="btn btn-info btn-xs"
                                           style="margin-left: 0.5em;">??????</a>
                                    </td>
                                    <td>
                                        <span style="color: #00b45a;" v-if="item.state==1">?????????</span>
                                        <span style="color: #2c6bff;" v-else-if="item.state==2">?????????</span>
                                        <span style="color: #FF5252;" v-else-if="item.state==3">?????????</span>
                                        <span style="color: #FFD54F;" v-else-if="item.state==4">?????????</span>
                                        <span style="color: #A6A6A6;" v-else-if="item.state==5">?????????</span>
                                        <span style="color: #ff4254;" v-else-if="item.state==6">?????????</span>
                                        <span style="color: #18b50c;" v-else-if="item.state==7">?????????</span>
                                        <span style="color: #A6A6A6;" v-else>??????</span>
                                    </td>
                                    <td>{{ item.input[0]==undefined?'????????????':item.input[0] }}</td>
                                    <td>{{ item.name==undefined?'????????????':item.name }}</td>
                                    <td v-if="item.coupon!=-1">
                                        <font color="red" size="3">{{ item.price }}???<span class="layui-word-aux"
                                                                                          style="font-size:0.8em;text-decoration:line-through;">{{
														item.originalprice }}???</span>
                                    </td>
                                    <td v-else>{{ item.price + (item.payment != '????????????' ? '???' : item.currency) }}
                                    </td>
                                    <td>{{ item.payment==undefined?'????????????':item.payment }}</td>
                                    <td class="hidden-xs">{{ item.addtime==undefined?'????????????':item.addtime }}</td>

                                </tr>
                                </tbody>
                            </table>
                            <div style="text-align:center;">
                                <button @click="GetOrderList(page+1)" class="btn btn-sm btn-primary">????????????</button>
                            </div>
                        </div>
                    </div>
                    <br/>
                </div>
                <!--????????????-->


                <!--????????????-->
                <div class="tab-pane" id="Substation">
                    <table class="table table-borderless animated bounceIn" style="text-align: center;">
                        <tbody>
                        <tr class="active">
                            <td>
                                <h4>
											<span style="font-weight:bold">
												<font color="#FF8000">???</font>
												<font color="#EC6D13">???</font>
												<font color="#D95A26">???</font>
												<font color="#C64739">???</font>
												<font color="#A0215F">???</font>
												<font color="#8D0E72">???</font>
												<font color="#5400AB">???</font>
												<font color="#4100BE">???</font>
												<font color="#2E00D1">???</font>
												<font color="#1B00E4">???</font>
											</span>
                                </h4>
                            </td>
                        </tr>
                        <tr class="active">
                            <td>??????/?????????/??????/????????????????????????</td>
                        </tr>
                        <tr class="active">
                            <td>
                                <strong>
                                    ????????????????????????????????????????????????</strong>
                            </td>
                        </tr>
                        <tr class="active">
                            <td><span class="glyphicon glyphicon-magnet"></span>&nbsp;?????????????????????????????????????????????
                                <hr>
                                <a href="#userjs" data-toggle="modal"
                                   class="btn btn-effect-ripple  btn-info btn-sm"
                                   style="float:left;overflow: hidden; position: relative;">
                                    <span class="glyphicon glyphicon-eye-open"></span>&nbsp;??????????????????</a>
                                <a href="./?mod=route&p=User" target="_blank"
                                   class="btn btn-effect-ripple  btn-success btn-sm"
                                   style="float:right;overflow: hidden; position: relative;">
                                    <span class="glyphicon glyphicon-share-alt"></span>&nbsp;??????????????????</a>
                            </td>
                        </tr>
                        <tr>
                        </tbody>
                    </table>
                </div>
                <!--????????????-->


                <!--??????-->
                <div class="tab-pane fade fade-right" id="more">

                    <div class="col-xs-6 col-sm-4 col-lg-4" v-if="InformData.Appurl!=''">
                        <a class="block block-link-hover2 text-center" :href="InformData.Appurl" target="_blank">
                            <div class="block-content block-content-full bg-success">
                                <i class="fa fa-cloud-download fa-3x text-white"></i>
                                <div class="font-w600 text-white-op push-15-t">APP??????</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xs-6 col-sm-4 col-lg-4">
                        <a class="block block-link-hover2 text-center" href="./?mod=route&p=User" target="_blank">
                            <div class="block-content block-content-full bg-city">
                                <i class="fa fa-certificate fa-3x text-white"></i>
                                <div class="font-w600 text-white-op push-15-t">????????????</div>
                            </div>
                        </a>
                    </div>
                    <?php if ($conf['FluctuationsPrices'] == 1) { ?>
                        <div class="col-xs-6 col-sm-4 col-lg-4">
                            <a class="block block-link-hover2 text-center" href="./?mod=UpAndDown" target="_blank">
                                <div class="block-content block-content-full bg-primary">
                                    <i class="fa fa-star fa-3x text-white"></i>
                                    <div class="font-w600 text-white-op push-15-t">????????????</div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                </div>
                <!--??????-->


                <!--????????????-->
                <div class="modal fade" align="left" id="userjs" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">????????????</h4>
                            </div>
                            <div class="block">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-vcenter">
                                        <thead>
                                        <tr>
                                            <th style="width: 100px;">??????</th>
                                            <th class="text-center" style="width: 20px;">????????????/????????????</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class="active">
                                            <td>??????????????????</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td>????????????????????????</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="success">
                                            <td>??????????????????</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td>??????????????????</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="info">
                                            <td>????????????????????????</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger"><i
                                                                    class="fa fa-close"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td>??????????????????</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="warning">
                                            <td>??????????????????????????????</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger"><i
                                                                    class="fa fa-close"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td>??????????????????</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger"><i
                                                                    class="fa fa-close"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="danger">
                                            <td>??????????????????APP</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger"><i
                                                                    class="fa fa-close"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <center style="color: #b2b2b2;"><small><em>* ???????????????????????????????????????</em></small></center>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">??????</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--????????????-->
            </div>
        </div>

        <!--??????????????????-->
        <div class="modal fade" align="left" id="customerservice" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">???????????????</h4>
                    </div>
                    <div class="modal-body" id="accordion">
                        <div class="panel panel-default" style="margin-bottom: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion"
                                       href="#collapseOne">??????????????????????????????????????????????????????</a>
                                </h4>
                            </div>
                            <div id="collapseOne" class="panel-collapse in" style="height: auto;">
                                <div class="panel-body">
                                    ?????????????????????????????????????????????????????????????????????????????????????????????<br>
                                    ????????????????????????????????????????????????<br>
                                    ??????????????????????????????????????????????????????
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default" style="margin-bottom: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"
                                       class="collapsed">QQ??????/??????????????????????????????</a>
                                </h4>
                            </div>
                            <div id="collapseTwo" class="panel-collapse collapse" style="height: 0px;">
                                <div class="panel-body">
                                    ????????????48????????????????????????????????????????????????48?????????????????????<br>
                                    ????????????48????????????????????????????????????????????????QQ?????????
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default" style="margin-bottom: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"
                                       class="collapsed">??????/CDK???????????????????????????</a>
                                </h4>
                            </div>
                            <div id="collapseThree" class="panel-collapse collapse" style="height: 0px;">
                                <div class="panel-body">???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????<br>
                                    ????????????????????????????????????????????????????????????????????????/cdk???
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default" style="margin-bottom: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseFourth"
                                       class="collapsed">???????????????????????????????????????</a>
                                </h4>
                            </div>
                            <div id="collapseFourth" class="panel-collapse collapse" style="height: 0px;">
                                <div class="panel-body" style="margin-bottom: 6px;">
                                    ??????????????????????????????????????????????????????????????????????????????????????????????????????<br>???????????????????????????????????????????????????????????????????????????????????????????????????QQ????????????
                                </div>
                            </div>
                        </div>
                        <ul class="list-group" style="margin-bottom: 0px;">
                            <li class="list-group-item">
                                <div class="media">
										<span class="pull-left thumb-sm"><img
                                                    src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100"
                                                    alt="..." class="img-circle img-thumbnail img-avatar"></span>
                                    <div class="pull-right push-15-t">
                                        <a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $conf['kfqq'] ?>&site=qq&menu=yes"
                                           target="_blank" class="btn btn-sm btn-info">??????</a>
                                    </div>
                                    <div class="pull-left push-10-t">
                                        <div class="font-w600 push-5">??????????????????</div>
                                        <div class="text-muted"><b>QQ???
                                                <?php echo $conf['kfqq'] ?>
                                            </b>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                ????????????????????????????????????????????????????????????!<br>
                                ????????????+????????????+???????????????????????????????????????!<br>
                                ???????????????????????????????????????????????????????????????<br>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!--??????????????????-->
        <!--????????????-->
        <div class="block block-themed" style="box-shadow:0 5px 10px 0 rgba(0, 0, 0, 0.25);display: none;"
             :style="ArticleList.data.length >= 1?'display: block;':'display: none;'">
            <div class="block-header bg-amethyst"
                 style="background-color: #6a67c7; border-color: #6a67c7; padding: 10px 10px;">
                <h3 class="block-title"><i class="fa fa-newspaper-o"></i> ???????????? - (???{{ ArticleList.page }}???)</h3>
            </div>

            <span v-for="(item, index) in ArticleList.data" @click="AlertMsg(item.content, item.title)"
                  class="list-group-item" :title="'???' + item.addtime + '??????'"><span class="btn btn- btn-xs"><img
                            :src="item.image" width="15" height="15" class="image_sc"/></span>&nbsp;{{ item.title
					}}</span>

            <div class="form-group" style="padding: 1em;">
                <a v-if="ArticleList.page != 1" @click="GetArticleList(3)" href="#"
                   class="btn btn-primary btn-rounded"><i class="fa fa-home"></i>&nbsp;?????????</a>
                <a v-if="ArticleList.state == true" href="#" @click="GetArticleList(2)"
                   class="btn btn-info btn-rounded" style="float:right;"><i class="fa fa-list"></i>&nbsp;?????????</a>
            </div>
            <br/>
        </div>
        <!--????????????-->

        <div class="block block-content block-content-mini block-content-full"
             style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.25);margin-bottom: 2em;">

            <div class="text-center" v-html="InformData.NoticeBottom"></div>
            <!--????????????-->

            <div class="text-center" style="display: none;margin-bottom: 1em;"
                 :style="InformData.Navigation.length != 0?'display:block;':'display: none;'">???????????????
                <a v-for="(item, index) in InformData.Navigation" :href="item.url" target="_blank" style="">
                    <font color="#FF0000">{{ item.name }}</font> <span
                            v-if="InformData.Navigation.length!=(index+1)">???</span>
                </a>
            </div>

            <div class="block-content text-center border-t">
                <a href="javascript:void(0);" onclick="AddFavorite('<?= $conf['sitename'] ?>',location.href)">
                    <b style="text-shadow: LightSteelBlue 1px 0px 0px;">
                        <i class="fa fa-heart text-danger animation-pulse"></i>
                        <font color=#CB0034>???</font>
                        <font color=#BE0041>???</font>
                        <font color=#B1004E>???</font>
                        <font color=#A4005B>???</font>
                        <font color=#970068>???
                            <?php echo $_SERVER['HTTP_HOST']; ?>
                        </font>
                        <font color=#2F00D0></font>
                        <font color=#CB0034>&nbsp;</font>
                        <font color=#CB0034>???</font>
                        <font color=#BE0041>???</font>
                        <font color=#B1004E>???</font>
                        <font color=#A4005B>???</font>
                    </b>
                </a>
                <br/>
                <?= $conf['statistics'] ?>
            </div>
            <!--????????????-->
        </div>
    </div>

    <!-- ??????????????????-->
    <script>
		function AddFavorite(title, url) {
			try {
				window.external.addFavorite(url, title);
			} catch (e) {
				try {
					window.sidebar.addPanel(title, url, "");
				} catch (e) {
					alert("??????????????????????????? ????????? ????????????/????????????!\n\n???????????????????????? Ctrl+D ?????????????????????! ");
				}
			}
		}
    </script>
    <!-- ??????????????????-->
    <script src="<?php echo $cdnpublic; ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>layer/2.3/layer.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/DS/assets/js/app.js"></script>
    <!-- ??????????????????????????????????????????????????????copy?????? -->
    <script src="<?= ROOT_DIR ?>assets/js/vue3.js"></script>
    <script src="<?= ROOT_DIR ?>assets/js/axios.min.js"></script>
    <script src="./assets/js/distpicker.min.js"></script>
    <script>
		var cid = <?= (empty($_QET['cid']) ? 0 : $_QET['cid']) ?>;
		var gid = <?= (empty($_QET['gid']) ? 0 : $_QET['gid']) ?>;
		var query = <?= ($_QET['query'] == 1 ? 1 : -1) ?>;
    </script>
    <script src="./assets/template/DS/assets/js/index.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>
