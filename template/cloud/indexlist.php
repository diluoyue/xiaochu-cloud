<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/8 16:33
// +----------------------------------------------------------------------
// | Filename: indexlist.php
// +----------------------------------------------------------------------
// | Explain: 列表版
// +----------------------------------------------------------------------
?>

<div class="layui-fluid">
    <?= $conf['notice_top'] ?>
</div>
<style>
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

<div id="App">
    <div class="card p-1 pb-0 mb-0"
         v-for="(item,index) in ClassData"
         v-show="item.count > 0"
    >
        <div class="card-header">
            {{item.name}} - {{item.count}}个商品
            <span v-if="CouponData[item.cid].length>=1"
                  @click="Coupon(item.cid)"
                  style="color:red;cursor:pointer;"
            >【领券×{{CouponData[item.cid].length}}】</span>
        </div>
        <div class="card-body p-1 pb-0" style="overflow-y: auto;white-space: nowrap;">
            <table class="table table-hover table-centered table-condensed">
                <thead>
                <tr>
                    <th>商品名称</th>
                    <th>售价</th>
                    <th>销量</th>
                    <th>库存</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody v-for="(it,ins) in GoodsData[item.cid]">
                <tr>
                    <td class="layui-elip"
                        :title="it.name"
                    >
                        <img onerror="this.src='<?= ROOT_DIR ?>assets/img/404.png'" :src="it.image" class="img-rounded"
                             style="width:1.5em;height:1.5em;margin-right:0.5em;"/>
                        <a style="color: #0a0e14"
                           :href="it.quota>=1?'./?mod=shop&gid='+it.gid:'javascript:alert(\'库存不足，可联系客服补货！\')'">{{it.name}}
                            [{{it.gid}}]</a>
                    </td>
                    <td>
                        <span style="color: red;font-size:1.2rem">{{it.price}}元/{{it.quantity + it.units}}</span>
                    </td>
                    <td>
                        <span class="label label-info">
                            {{(it.sales<10000?it.sales:'1万+')}}
                        </span>
                    </td>
                    <td>
                        <span v-if="it.quota==0" style="color: rgb(142,142,142)">
                            暂无库存
                        </span>
                        <span v-else :style="it.quota>10?'color:#000':'color:red'">
                            {{it.quota>10?'库存充足':'较少('+it.quota+')'}}
                        </span>
                    </td>
                    <td>
                        <a v-if="it.quota>=1" :href="'./?mod=shop&gid='+it.gid"
                           class="layui-btn layui-btn-xs"
                           style="background-color:rgba(255,97,74,0.86);box-shadow:0 2px 8px rgba(255,0,0,0.34);">购买</a>
                        <button v-else class="layui-btn layui-btn-xs"
                                style="background-color:#979595;box-shadow:0 2px 8px rgba(0,0,0,0.18);"
                                onclick="alert('库存不足，可联系客服补货！')">
                            库存不足
                        </button>
                        <button class="layui-btn layui-btn-xs"
                                style="background-color:#979595;box-shadow:0 2px 8px rgba(0,0,0,0.18);"
                                @click="ShareGoods(it.gid)">
                            分享
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div v-if="CouponData.all.length>=1">
        <div class="Coupons" @click="Coupon('all')" title="领取惊喜优惠券！">
            <img src="<?= ROOT_DIR ?>assets/img/coupon_5.png"/>
            <br>
            <span style="font-size: 0.5em;">惊喜优惠券</span>
        </div>
    </div>
</div>
<?php include 'template/cloud/bottom.php'; ?>
<script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
<script src="<?= ROOT_DIR ?>assets/template/cloud/assets/js/list.js?vs=<?= $accredit['versions'] ?>"></script>
</body>
