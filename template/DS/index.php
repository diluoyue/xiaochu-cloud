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
        <!--弹出公告-->
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
                        <button type="button" class="btn btn-default" data-dismiss="modal">知道啦</button>
                    </div>
                </div>
            </div>
        </div>
        <!--弹出公告-->
        <!--公告-->
        <div class="modal fade" align="left" id="anounce" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">公告</h4>
                    </div>
                    <div class="modal-body" v-html="InformData.NoticeTop"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
        <!--公告-->
        <!--查单说明开始-->
        <div class="modal fade" align="left" id="cxsm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">查询内容是什么？该输入什么？</h4>
                    </div>
                    <li class="list-group-item">
                        <font color="red">请在右侧的输入框内输入您下单时，在第一个输入框内填写的信息</font>
                    </li>
                    <li class="list-group-item">例如您购买的是QQ赞类商品，输入下单的QQ账号即可查询订单</li>
                    <li class="list-group-item">例如您购买的是邮箱类商品，需要输入您的邮箱号，输入QQ号是查询不到的</li>
                    <li class="list-group-item">例如您购买的是短视频类商品，输入视频链接即可查询，不要带其他中文字符</li>
                    <li class="list-group-item">
                        <font color="red">如果您不知道下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询</font>
                    </li>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
        <!--查单说明结束-->

        <!--顶部导航-->
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
                                    class="fa fa-bullhorn"></i>&nbsp;<span style="font-weight:bold">公告</span></a>
                    </div>

                    <a v-if="InformData.Appurl!=''" :href="InformData.Appurl" target="_blank"
                       class="btn btn-effect-ripple btn-default"><i class="fa fa-android"></i> <span
                                style="font-weight:bold">客户端</span></a>

                    <a v-else href="#customerservice" target="_blank" data-toggle="modal" class="btn btn-default"><i
                                class="fa fa-qq"></i>&nbsp;<span style="font-weight:bold">客服</span></a>


                    <?php if ($User != false) { ?>
                        <div class="btn-group">
                            <a class="btn btn-default" data-toggle="modal" href="user/"><i
                                        class="fa fa-users fa-1x"></i>&nbsp;管理后台</a>
                        </div>
                    <?php } else { ?>
                        <div class="btn-group">
                            <a class="btn btn-default" data-toggle="modal" href="user/login.php"><i
                                        class="fa fa-users fa-1x"></i>&nbsp;登录</a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!--顶部导航-->

        <div class="block" style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.25);">

            <ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs">
                <li style="width: 25%;" align="center" <?= (!isset($_QET['query']) ? 'class="active"' : '') ?>><a
                            href="#shop" data-toggle="tab"><span style="font-weight:bold"><i
                                    class="fa fa-shopping-bag fa-fw"></i> 下单</span></a></li>
                <li v-if="ActivitiesGoods.length>=1" style="width: 25%;" align="center"><a
                            href="#shopMS" data-toggle="tab"><span style="font-weight:bold;color: red"><i
                                    class="fa fa-shopping-bag fa-fw"></i> 秒杀</span></a></li>
                <li style="width: 25%;" align="center" <?= (!isset($_QET['query']) ? '' : 'class="active"') ?>><a
                            href="#search" data-toggle="tab" id="tab-query"><span style="font-weight:bold"><i
                                    class="fa fa-search"></i> 查询</span></a></li>
                <li style="width: 25%;" align="center" <?php if ($conf['ShutDownUserSystem'] != 1) { ?>class="hide"
                    <?php } ?>><a href="#Substation" data-toggle="tab"><span style="font-weight:bold">
								<font color="#ff0000"><i class="fa fa-coffee fa-fw"></i> 分站
							</span></font></a>
                </li>
                <li style="width: 25%;" align="center"><a href="#more" data-toggle="tab"><span
                                style="font-weight:bold"><i class="fa fa-folder-open"></i>
								更多</span></a></li>
            </ul>
            <!--TAB标签-->
            <div class="Coupons" @click="Coupon" style="display: none;"
                 :style="CouponData.length>=1?'display:block;':'display: none;'" title="领取惊喜优惠券！">
                <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"/>
                <br>
                <span style="font-size: 0.5em;">{{ (type==3?'惊喜优惠券':(type==2?'分类优惠券':'商品优惠券')) }}</span>
            </div>
            <div class="block-content tab-content">
                <!--在线下单-->
                <div class="tab-pane <?= (!isset($_QET['query']) ? 'active' : '') ?>" id="shop">
                    <div class="GoodsImage" style="display: none;"
                         :style="ImagesSrc!=-1?'display: block;':'display: none;'">
                        <img :src="ImagesSrc" :alt="Goods.name"/>
                    </div>
                    <div>
                        <div class="form-group" id="display_searchBar">
                            <div class="input-group">
                                <div class="input-group-addon">搜索商品</div>
                                <input v-model="GoodsSeek" type="text" class="form-control" placeholder="请填写商品关键词">
                                <div class="input-group-addon"><span class="glyphicon glyphicon-search onclick"
                                                                     title="搜索" @click="SearchGoods"></span></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">选择分类</div>
                                <select v-model="cid" @change='GetGoods' class="form-control">
                                    <option value="0">请选择分类</option>
                                    <option v-if="cid==-1" value="-1">[{{ GoodsSeek }}]搜索结果</option>
                                    <option v-for="(item,index) in ClassData" :value="item.cid">{{ item.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div v-if="cid!=0" class="form-group" style="display: none;"
                             :style="cid!=0?'display:block;':'display: none;'">
                            <div class="input-group">
                                <div class="input-group-addon">选择商品</div>
                                <select v-model="gid" @change='GetGoodsDetails' class="form-control">
                                    <option value="0">请选择商品</option>
                                    <option v-for="(item,index) in GoodsData" :value="item.gid">{{ item.name }}
                                    </option>
                                </select>
                                <span v-if="gid!=0" @click="ShareGoods" class="input-group-btn" title="分享商品给朋友">
										<button class="btn btn-info glyphicon glyphicon-new-window">
										</button>
									</span>
                            </div>
                        </div>

                        <div style="display: none;"
                             :style="Goods.name != -1?'display:block;':'display: none;'">
                            <div class="form-group" style="color:#4169E1;font-weight:bold">
                                <div class="input-group">
                                    <div class="input-group-addon">商品价格</div>
                                    <input v-if="Goods.name != -1&&Goods.Seckill !== -1" type="text"
                                           class="form-control"
                                           :value="(NumRound(Goods.price) - NumRound(Goods.price * Goods.Seckill.depreciate) / 100).toFixed(Goods.accuracy) * num"
                                           style="color:#ff3b31;font-weight:bold" disabled="">
                                    <input v-else-if="Goods.name != -1" type="text" class="form-control"
                                           :value="Goods.price * num"
                                           style="color:#4169E1;font-weight:bold" disabled="">

                                    <span v-if="gid!=0" id="level" class="input-group-btn" title="查看商品价格">
											<button class="btn btn-success glyphicon glyphicon glyphicon-usd">
											</button>
										</span>
                                </div>
                            </div>
                            <div class="form-group" v-if="Goods.name != -1&&Goods.Seckill !== -1">
                                <div class="input-group">
                                    <div class="input-group-addon">活动内容</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           :value="'降价 ' + Goods.Seckill.depreciate + '%'"
                                           class="form-control" disabled="">
                                </div>
                            </div>
                            <div class="form-group" v-if="Goods.name != -1&&Goods.Seckill !== -1">
                                <div class="input-group"
                                     v-if="Goods.Seckill.state === 1 && Goods.Seckill.attend < Goods.Seckill.astrict">
                                    <div class="input-group-addon">结束时间</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           :value="Goods.Seckill.end"
                                           class="form-control" disabled="">
                                </div>
                                <div class="input-group"
                                     v-else-if="Goods.Seckill.state === 1 && Goods.Seckill.attend >= Goods.Seckill.astrict">
                                    <div class="input-group-addon">结束时间</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           value="已结束，活动人数已达上限，商品恢复原价"
                                           class="form-control" disabled="">
                                </div>
                                <div class="input-group" v-else>
                                    <div class="input-group-addon">开始时间</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           :value="Goods.Seckill.start"
                                           class="form-control" disabled="">
                                </div>
                            </div>
                            <div class="form-group" v-if="Goods.name != -1&&Goods.Seckill !== -1">
                                <div class="input-group">
                                    <div class="input-group-addon">活动名额</div>
                                    <input type="text" style="color:#ff3b31;font-weight:bold"
                                           :value="'剩 ' + (Goods.Seckill.astrict - Goods.Seckill.attend) + '个'"
                                           class="form-control" disabled="">
                                </div>
                            </div>
                            <div class="form-group" style="display: none;"
                                 :style="Goods.method==1?'display:block;':'display: none;'">
                                <div class="input-group">
                                    <div class="input-group-addon">购买数量</div>
                                    <input type="text" style="color:#4169E1;font-weight:bold"
                                           :value="num * Goods.quantity + '' + Goods.units"
                                           class="form-control" disabled="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon">剩余库存</div>
                                    <input type="text" :value="Goods.quota + ' 份'" class="form-control"
                                           disabled="">
                                </div>
                            </div>
                            <div class="form-group" style="display: none;"
                                 :style="Goods.repetition==1?'display:block;':'display: none;'">
                                <div class="input-group">
                                    <div class="input-group-addon">下单份数</div>
                                    <span class="input-group-btn">
											<input type="button" @click="PriceLs(1)" class="btn btn-info"
                                                   style="border-radius: 0px;" value="━">
										</span>
                                    <input v-on:input="PriceLs" class="form-control" v-model.number="num"
                                           type="number" :min="Goods.min" :max="Goods.max"/>
                                    <span class="input-group-btn">
											<input type="button" @click="PriceLs(2)" class="btn btn-info"
                                                   style="border-radius: 0px;" value="✚">
										</span>
                                </div>
                            </div>

                            <div class="form-group" v-for="(item, index) of Goods.input" :key="index">
                                <div class="input-group" v-if="item.state == 1">
                                    <div class="input-group-addon">{{ item.Data }}</div>
                                    <input type="text" v-model="form[index]" class="form-control"
                                           :placeholder="'请将' + item.Data + '填写完整!'">
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
                                           :placeholder="'请将' + item.Data + '填写完整!'"
                                    />
                                    <div class="input-group-addon onclick"
                                         style="z-index: 10"
                                         id="Selectaddress" :index="index">
                                        选择地址
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
                                        商品下单参数未配置完善，请联系客服处理！
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
                                <a type="submit" @click="submit" class="btn btn-block btn-primary">立即购买</a>
                            </div>
                        </div>

                    </div>
                </div>
                <!--在线下单-->

                <!--限购秒杀-->
                <div class="tab-pane" id="shopMS" v-if="ActivitiesGoods.length>=1">
                    <div v-if="ActivitiesGoods.length>=1" style="padding-bottom: 1em"
                         class="layui-row layui-col-space1">
                        <div class="layui-col-xs4 layui-col-sm3" style="text-align: center"
                             v-for="(item,index) in ActivitiesGoods">
                            <a class="card" :href="'./?gid='+item.gid" style="color: #000">
                                <div class="card-body">
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
                                    <div class="layui-row">
                                        <img
                                                :src="item.image"
                                                style="width: 78px; height: 78px; border-radius: 0.5em; box-shadow: rgb(238, 238, 238) 3px 3px 16px;">
                                        <div class="layui-col-xs12" style="font-size: 80%;margin-top: 0.5em;">
                                            <div class="layui-elip">{{item.name}}</div>
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
                <!--限购秒杀-->

                <!--查询订单-->
                <div class="tab-pane <?= (!isset($_QET['query']) ? '' : 'active') ?>" id="search">
                    <ul class="list-group animated bounceIn">
                        <li class="list-group-item">
                            <div class="media">
									<span class="pull-left thumb-sm"><img
                                                src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100"
                                                class="img-circle img-thumbnail img-avatar"></span>
                                <div class="pull-right push-15-t">
                                    <a href="#customerservice" target="_blank" data-toggle="modal"
                                       class="btn btn-sm btn-info">联系客服</a>
                                </div>
                                <div class="pull-left push-10-t">
                                    <div class="font-w600 push-5">订单售后QQ客服</div>
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
                            <div class="input-group-addon">下单信息</div>
                            <input type="text" v-model="seek" class="form-control"
                                   placeholder="请输入要查询的内容（留空则显示最新订单）" required/>
                            <span class="input-group-btn">
									<a href="#cxsm" data-toggle="modal" class="btn btn-warning">
										<i class="glyphicon glyphicon-exclamation-sign"></i>
									</a>
								</span>
                        </div>
                    </div>
                    <div class="btn-group" style="width: 100%;margin-bottom: 1em;">
                        <button type="button" @click="GetOrderList(-2)" class="btn btn-success btn-sm"
                                style="text-shadow: black 1px 1px 1px;width:50%;">查询有绑订单
                        </button>
                        <button type="button" @click="GetOrderList(-3)" class="btn btn-info btn-sm"
                                style="text-shadow: black 1px 1px 1px;width:50%;">查询游客订单
                        </button>
                    </div>

                    <div class="alert alert-warning" <?= ($User == false ? 'style="margin-top: 1em;"'
                        : 'style="display: none;margin-top: 1em;"') ?>>
                        <a href="#" class="close" data-dismiss="alert">
                            &times;
                        </a>
                        <a href="./?mod=route&p=User"
                           style="color: #efa231;"><strong>警告！</strong>检测到您未登陆，登陆后可绑定订单，他人无法查询，保护您的购买信息，点我登陆！</a>
                    </div>

                    <div style="margin-top: 1em;display: none;"
                         :style="OrderData.length>=1||seek!=''?'display:block;':'display: none;'">
                        <div class="alert alert-success">
                            <a href="#" class="close" data-dismiss="alert">
                                &times;
                            </a>
                            共取出 {{ OrderData.length }} 条订单
                        </div>
                    </div>

                    <div class="form-group" style="display: none;"
                         :style="OrderData.length>=1?'display:block;':'display: none;'">
                        <center><small>
                                <font color="#ff0000">可以左右滑动</font>
                            </small></center>
                        <div class="table-responsive">
                            <table class="table table-vcenter table-condensed table-striped">
                                <thead>
                                <tr>
                                    <th>操作</th>
                                    <th>状态</th>
                                    <th>下单账号</th>
                                    <th>商品名称</th>
                                    <th>金额</th>
                                    <th>付款方式</th>
                                    <th class="hidden-xs">购买时间</th>

                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item,index) in OrderData" style="white-space:nowrap;">
                                    <td>
												<span title="查看订单进度" @click="GetQuery(item.id,item.order)"
                                                      class="btn btn-warning btn-xs">详情</span>
                                        <a target="_blank" :href="Tracking + item.remark"
                                           v-if="item.RemarkType==2" title="查看物流信息" class="btn btn-info btn-xs"
                                           style="margin-left: 0.5em;">物流</a>
                                    </td>
                                    <td>
                                        <span style="color: #00b45a;" v-if="item.state==1">已完成</span>
                                        <span style="color: #2c6bff;" v-else-if="item.state==2">待处理</span>
                                        <span style="color: #FF5252;" v-else-if="item.state==3">异常中</span>
                                        <span style="color: #FFD54F;" v-else-if="item.state==4">处理中</span>
                                        <span style="color: #A6A6A6;" v-else-if="item.state==5">已退款</span>
                                        <span style="color: #ff4254;" v-else-if="item.state==6">售后中</span>
                                        <span style="color: #18b50c;" v-else-if="item.state==7">已评价</span>
                                        <span style="color: #A6A6A6;" v-else>未知</span>
                                    </td>
                                    <td>{{ item.input[0]==undefined?'无效信息':item.input[0] }}</td>
                                    <td>{{ item.name==undefined?'无效商品':item.name }}</td>
                                    <td v-if="item.coupon!=-1">
                                        <font color="red" size="3">{{ item.price }}元<span class="layui-word-aux"
                                                                                          style="font-size:0.8em;text-decoration:line-through;">{{
														item.originalprice }}元</span>
                                    </td>
                                    <td v-else>{{ item.price + (item.payment != '积分兑换' ? '元' : item.currency) }}
                                    </td>
                                    <td>{{ item.payment==undefined?'无效方式':item.payment }}</td>
                                    <td class="hidden-xs">{{ item.addtime==undefined?'无效日期':item.addtime }}</td>

                                </tr>
                                </tbody>
                            </table>
                            <div style="text-align:center;">
                                <button @click="GetOrderList(page+1)" class="btn btn-sm btn-primary">查看更多</button>
                            </div>
                        </div>
                    </div>
                    <br/>
                </div>
                <!--查询订单-->


                <!--开通分站-->
                <div class="tab-pane" id="Substation">
                    <table class="table table-borderless animated bounceIn" style="text-align: center;">
                        <tbody>
                        <tr class="active">
                            <td>
                                <h4>
											<span style="font-weight:bold">
												<font color="#FF8000">搭</font>
												<font color="#EC6D13">建</font>
												<font color="#D95A26">属</font>
												<font color="#C64739">于</font>
												<font color="#A0215F">自</font>
												<font color="#8D0E72">己</font>
												<font color="#5400AB">的</font>
												<font color="#4100BE">代</font>
												<font color="#2E00D1">刷</font>
												<font color="#1B00E4">网</font>
											</span>
                                </h4>
                            </td>
                        </tr>
                        <tr class="active">
                            <td>学生/上班族/创业/休闲赚钱必备工具</td>
                        </tr>
                        <tr class="active">
                            <td>
                                <strong>
                                    网站轻轻松松推广日赚上千元不是梦</strong>
                            </td>
                        </tr>
                        <tr class="active">
                            <td><span class="glyphicon glyphicon-magnet"></span>&nbsp;快加入我们成为大家庭中的一员吧
                                <hr>
                                <a href="#userjs" data-toggle="modal"
                                   class="btn btn-effect-ripple  btn-info btn-sm"
                                   style="float:left;overflow: hidden; position: relative;">
                                    <span class="glyphicon glyphicon-eye-open"></span>&nbsp;网站详情介绍</a>
                                <a href="./?mod=route&p=User" target="_blank"
                                   class="btn btn-effect-ripple  btn-success btn-sm"
                                   style="float:right;overflow: hidden; position: relative;">
                                    <span class="glyphicon glyphicon-share-alt"></span>&nbsp;免费开通网站</a>
                            </td>
                        </tr>
                        <tr>
                        </tbody>
                    </table>
                </div>
                <!--开通分站-->


                <!--更多-->
                <div class="tab-pane fade fade-right" id="more">

                    <div class="col-xs-6 col-sm-4 col-lg-4" v-if="InformData.Appurl!=''">
                        <a class="block block-link-hover2 text-center" :href="InformData.Appurl" target="_blank">
                            <div class="block-content block-content-full bg-success">
                                <i class="fa fa-cloud-download fa-3x text-white"></i>
                                <div class="font-w600 text-white-op push-15-t">APP下载</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xs-6 col-sm-4 col-lg-4">
                        <a class="block block-link-hover2 text-center" href="./?mod=route&p=User" target="_blank">
                            <div class="block-content block-content-full bg-city">
                                <i class="fa fa-certificate fa-3x text-white"></i>
                                <div class="font-w600 text-white-op push-15-t">用户中心</div>
                            </div>
                        </a>
                    </div>
                    <?php if ($conf['FluctuationsPrices'] == 1) { ?>
                        <div class="col-xs-6 col-sm-4 col-lg-4">
                            <a class="block block-link-hover2 text-center" href="./?mod=UpAndDown" target="_blank">
                                <div class="block-content block-content-full bg-primary">
                                    <i class="fa fa-star fa-3x text-white"></i>
                                    <div class="font-w600 text-white-op push-15-t">价格波动</div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                </div>
                <!--更多-->


                <!--版本介绍-->
                <div class="modal fade" align="left" id="userjs" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">版本介绍</h4>
                            </div>
                            <div class="block">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-vcenter">
                                        <thead>
                                        <tr>
                                            <th style="width: 100px;">功能</th>
                                            <th class="text-center" style="width: 20px;">初级站点/高级站点</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class="active">
                                            <td>专属代刷平台</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td>三种在线支付接口</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="success">
                                            <td>专属网站域名</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td>赚取用户提成</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="info">
                                            <td>赚取下级分站提成</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger"><i
                                                                    class="fa fa-close"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td>设置商品价格</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                                    class="fa fa-check"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="warning">
                                            <td>设置下级分站商品价格</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger"><i
                                                                    class="fa fa-close"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="">
                                            <td>搭建下级分站</td>
                                            <td class="text-center">
														<span class="btn btn-effect-ripple btn-xs btn-danger"><i
                                                                    class="fa fa-close"></i></span>
                                                <span class="btn btn-effect-ripple btn-xs btn-success"><i
                                                            class="fa fa-check"></i></span>
                                            </td>
                                        </tr>
                                        <tr class="danger">
                                            <td>赠送专属精致APP</td>
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
                                <center style="color: #b2b2b2;"><small><em>* 自己的能力决定着你的收入！</em></small></center>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--版本介绍-->
            </div>
        </div>

        <!--关于我们弹窗-->
        <div class="modal fade" align="left" id="customerservice" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">客服与帮助</h4>
                    </div>
                    <div class="modal-body" id="accordion">
                        <div class="panel panel-default" style="margin-bottom: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion"
                                       href="#collapseOne">为什么订单显示已完成了却一直没到账？</a>
                                </h4>
                            </div>
                            <div id="collapseOne" class="panel-collapse in" style="height: auto;">
                                <div class="panel-body">
                                    订单显示（已完成）就证明已经提交到服务器内！并不是订单已刷完。<br>
                                    如果长时间没到账请联系客服处理！<br>
                                    订单长时间显示（待处理）请联系客服！
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default" style="margin-bottom: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"
                                       class="collapsed">QQ会员/钻类等什么时候到账？</a>
                                </h4>
                            </div>
                            <div id="collapseTwo" class="panel-collapse collapse" style="height: 0px;">
                                <div class="panel-body">
                                    下单后的48小时内到账（会员或钻全部都是一样48小时内到账）！<br>
                                    如果超过48小时，请联系客服退款或补单，提供QQ号码！
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default" style="margin-bottom: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"
                                       class="collapsed">卡密/CDK没有发送我的邮箱？</a>
                                </h4>
                            </div>
                            <div id="collapseThree" class="panel-collapse collapse" style="height: 0px;">
                                <div class="panel-body">没有收到请检查自己邮箱的垃圾箱！也可以去查单区：输入自己下单时填写的邮箱进行查单。<br>
                                    查询到订单后点击（详细）就可以看到自己购买的卡密/cdk！
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default" style="margin-bottom: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseFourth"
                                       class="collapsed">已付款了没有查询到我订单？</a>
                                </h4>
                            </div>
                            <div id="collapseFourth" class="panel-collapse collapse" style="height: 0px;">
                                <div class="panel-body" style="margin-bottom: 6px;">
                                    联系客服处理，请提供（付款详细记录截图）（下单商品名称）（下单账号）<br>直接把三个信息发给客服，然后等待客服回复处理（请不要发抖动窗口或者QQ电话）！
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
                                           target="_blank" class="btn btn-sm btn-info">联系</a>
                                    </div>
                                    <div class="pull-left push-10-t">
                                        <div class="font-w600 push-5">订单售后客服</div>
                                        <div class="text-muted"><b>QQ：
                                                <?php echo $conf['kfqq'] ?>
                                            </b>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                想要快速回答你的问题就请把问题描述讲清楚!<br>
                                下单账号+业务名称+问题，直奔主题，按顺序回复!<br>
                                有问题直接留言，请勿抖动语音否则直接无视。<br>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!--关于我们弹窗-->
        <!--文章列表-->
        <div class="block block-themed" style="box-shadow:0 5px 10px 0 rgba(0, 0, 0, 0.25);display: none;"
             :style="ArticleList.data.length >= 1?'display: block;':'display: none;'">
            <div class="block-header bg-amethyst"
                 style="background-color: #6a67c7; border-color: #6a67c7; padding: 10px 10px;">
                <h3 class="block-title"><i class="fa fa-newspaper-o"></i> 文章列表 - (第{{ ArticleList.page }}页)</h3>
            </div>

            <span v-for="(item, index) in ArticleList.data" @click="AlertMsg(item.content, item.title)"
                  class="list-group-item" :title="'于' + item.addtime + '发布'"><span class="btn btn- btn-xs"><img
                            :src="item.image" width="15" height="15" class="image_sc"/></span>&nbsp;{{ item.title
					}}</span>

            <div class="form-group" style="padding: 1em;">
                <a v-if="ArticleList.page != 1" @click="GetArticleList(3)" href="#"
                   class="btn btn-primary btn-rounded"><i class="fa fa-home"></i>&nbsp;上一页</a>
                <a v-if="ArticleList.state == true" href="#" @click="GetArticleList(2)"
                   class="btn btn-info btn-rounded" style="float:right;"><i class="fa fa-list"></i>&nbsp;下一页</a>
            </div>
            <br/>
        </div>
        <!--文章列表-->

        <div class="block block-content block-content-mini block-content-full"
             style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.25);margin-bottom: 2em;">

            <div class="text-center" v-html="InformData.NoticeBottom"></div>
            <!--底部导航-->

            <div class="text-center" style="display: none;margin-bottom: 1em;"
                 :style="InformData.Navigation.length != 0?'display:block;':'display: none;'">友情链接：
                <a v-for="(item, index) in InformData.Navigation" :href="item.url" target="_blank" style="">
                    <font color="#FF0000">{{ item.name }}</font> <span
                            v-if="InformData.Navigation.length!=(index+1)">丨</span>
                </a>
            </div>

            <div class="block-content text-center border-t">
                <a href="javascript:void(0);" onclick="AddFavorite('<?= $conf['sitename'] ?>',location.href)">
                    <b style="text-shadow: LightSteelBlue 1px 0px 0px;">
                        <i class="fa fa-heart text-danger animation-pulse"></i>
                        <font color=#CB0034>本</font>
                        <font color=#BE0041>站</font>
                        <font color=#B1004E>网</font>
                        <font color=#A4005B>址</font>
                        <font color=#970068>：
                            <?php echo $_SERVER['HTTP_HOST']; ?>
                        </font>
                        <font color=#2F00D0></font>
                        <font color=#CB0034>&nbsp;</font>
                        <font color=#CB0034>建</font>
                        <font color=#BE0041>议</font>
                        <font color=#B1004E>收</font>
                        <font color=#A4005B>藏</font>
                    </b>
                </a>
                <br/>
                <?= $conf['statistics'] ?>
            </div>
            <!--底部导航-->
        </div>
    </div>

    <!-- 收藏代码开始-->
    <script>
		function AddFavorite(title, url) {
			try {
				window.external.addFavorite(url, title);
			} catch (e) {
				try {
					window.sidebar.addPanel(title, url, "");
				} catch (e) {
					alert("手机用户：点击底部 “≡” 添加书签/收藏网址!\n\n电脑用户：请您按 Ctrl+D 手动收藏本网址! ");
				}
			}
		}
    </script>
    <!-- 收藏代码结束-->
    <script src="<?php echo $cdnpublic; ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>layer/2.3/layer.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/DS/assets/js/app.js"></script>
    <!-- 若要根据此模板进行开发，以下部分必须copy过去 -->
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
