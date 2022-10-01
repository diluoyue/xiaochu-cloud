<?php
if (!defined('IN_CRONLITE')) die;
$title = $conf['sitename'];
include 'template/cloud/header.php';
if ((int)$TemState['state']['value'] === 2) {
    include 'template/cloud/indexlist.php';
    die;
}
?>
<!-- 商城 -->
<style>
    .headers {
        border: none;
        border-bottom: solid 1px #eee;
        text-align: left;
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
<div id="goodType">
    <div class="card" style="padding: 1em;">
        <?= $conf['notice_top'] ?>
    </div>
    <div id="appid">
        <div v-if="ActivitiesGoods.length>=1" style="box-shadow:1px 1px 16px #eee;display: block;"
             class="alert layui-row layui-col-space1">
            <div class="layui-col-xs12" style="color: red;font-size:14px">
                限购秒杀
            </div>
            <div class="layui-col-xs6 layui-col-sm2 " style="text-align: center"
                 v-for="(item,index) in ActivitiesGoods">
                <a class="card" :href="'./?mod=shop&gid='+item.gid" style="color: #000">
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
                            <div class="layui-col-xs12" style="font-size: 80%;">
                                <div class="mt-2 layui-elip">{{item.name}}</div>
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

        <div v-if="CouponData.length>=1" @click="Coupon" class="alert mb-3"
             style="cursor: pointer;box-shadow:1px 1px 16px #eee;">
            <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png" style="width: 1.6em; height: 1.6em;">
            您有{{CouponData.length }}个优惠券待领取<font color="red">【领取】</font>
        </div>
        <div v-if="Type==1" class="contacts row" style="display: none" :style="Type==1?'display: ':'display: none'">
            <div class="col-xl-2 col-lg-3 col-sm-4 col-6" v-for="(item, index) in ClassData" :key="index">
                <div @click="CutGoods(item.cid,index)" class="contacts__item shadow-sm"
                     style="box-shadow:1px 1px 16px #eee !important;">
                    <span class="data-tid contacts__img goodTypeChange">
                        <img v-if="item.image!=''" class="lazy" :src="item.image"
                             style="height: 9em;border-radius: 0.5em" :alt="item.name"/>
                    </span>
                    <div class="contacts__info">
                        <strong>{{ item.name }}</strong>
                    </div>
                    <span class="contacts__btn goodTypeChange" style="display: block;">共有{{ item.count }}个商品
                    </span>
                </div>
            </div>
        </div>
        <div v-if="Type==2" class="contacts row" id="gid" style="display: none"
             :style="Type==2?'display: ':'display: none'">
            <div class="col-xl-12">
                <div class="card layui-btn layui-btn-primary layui-btn-fluid headers  layui-elip"
                     style="margin-bottom: 2em;width:100%;"
                     @click="CutClass">
                    <i class="layui-icon">&#xe603; {{ name }}</i>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-sm-4 col-6" v-for="(item, index) in GoodsData" :key="index">
                <div class="contacts__item shadow-sm" style="box-shadow:1px 1px 16px #eee !important;">
                    <a :href="'./?mod=shop&gid='+item.gid" class="data-tid contacts__img goodTypeChange">
                        <img v-if="item.image!=''" class="lazy" :src="item.image"
                             style="height: 9em;border-radius: 0.5em" :alt="item.name"/>
                    </a>
                    <div class="contacts__info">
                        <strong>{{ item.name }}</strong>
                        <small>{{ (item.quota>=1?'剩余'+item.quota+'份':'库存不足') }}</small>
                    </div>
                    <a :href="'./?mod=shop&gid='+item.gid" class="contacts__btn " style="display: block;"
                       v-html="Price(item)">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php include 'template/cloud/bottom.php'; ?>
<!-- 结束 -->
<script>
    var cid = <?= (empty($_QET['cid']) ? -1 : $_QET['cid']) ?>;
</script>
<script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/cloud/assets/js/index.js?vs=<?= $accredit['versions'] ?>"
        type="text/javascript"></script>
</body>

</html>