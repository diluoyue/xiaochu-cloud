<?php

use Medoo\DB\SQL;

$title = '商品优惠券列表';
include 'header.php';
$DB = SQL::DB();
$GoodsList = $DB->select('goods', ['gid', 'name', 'state'], ['ORDER' => ['gid' => 'DESC']]);
if (isset($_QET['gid'])) {
    $Goods = $DB->get('goods', ['gid', 'name'], ['gid' => (int)$_QET['gid']]);
    if (!$Goods) show_msg('温馨提示', '商品不存在！');
} else $Goods = [];
?>
<div class="row">
    <div class="col-sm-6 text-center">
        <a <?= $Goods['name'] == '' ? '' : 'href="./admin.coupon.list.php"' ?> class="btn btn-outline-danger mb-2">
            <?= $Goods['name'] == '' ? '请选择商品' : $Goods['name'] . '- 查看全部' ?>
        </a>
        <button onclick="search()" class="btn btn-outline-secondary mb-2">
            查询优惠券
        </button>
        <a href="admin.coupon.add.php" target="_blank" class="btn btn-outline-success mb-2">
            添加优惠券
        </a>
        <a href="admin.coupon.set.php" target="_blank" class="btn btn-success mb-2">
            全局配置
        </a>
        <button onclick="vm.DeleteCoupons()" class="btn btn-outline-warning mb-2">
            删除优惠券
        </button>
        <button class="btn btn-warning mb-2" onclick="vm.CouponExport()">
            券码导出
        </button>
    </div>
    <div class="col-sm-6">
        <form class="layui-form" action="">
            <div class="layui-form-item">
                <label class="layui-form-label">查商品</label>
                <div class="layui-input-block">
                    <select name="goods" lay-search lay-filter="goods">
                        <option value="">请选择对应的商品</option>
                        <?php
                        foreach ($GoodsList as $v) :
                            echo '<option value="' . $v['gid'] . '"' . ($_QET['gid'] == $v['gid'] ? 'selected' : '') . ' >' . $v['name'] . ' / ' . ($v['state'] == 1 ? '上架中' : '已下架') . '</option>
                            ';
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
    <div class="col-xs-12 col-sm-12" id="app" data="<?= $Goods['gid'] ?>">
        <div class="card">
            <div class="card-header">
                券码总数 - {{sum}}张
            </div>
            <div class="card-header" style="color: red;">
                Ps:如果发现优惠券达不到预期减免效果，可以查看全局配置内的设置，是否设置了付款金额不会低于成本！
            </div>
            <div class="card-header" v-if="name!=''">
                <button class="btn btn-primary badge mr-1 font-13" @click="search('')">查看全部</button>
                <font color="#0000FF">{{ name }}</font> 的搜索结果如下：
            </div>
            <div class="card-body p-0" style="overflow: hidden;overflow-x: auto;">
                <table class="table  dt-responsive nowrap p-1" style="white-space: nowrap">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>领取用户</th>
                        <th>使用订单</th>
                        <th>券类型</th>
                        <th>使用范围</th>
                        <th>有效期</th>
                        <th>券码(可复制发送给用户去后台<a href="../?mod=route&p=User" target="_blank">兑换</a>)</th>
                        <th>额度/则扣</th>
                        <th>券码名称</th>
                        <th>使用条件</th>
                        <th>发放方式</th>
                        <th>使用时间</th>
                        <th>领取时间</th>
                        <th>使用者IP</th>
                        <th>生成时间</th>
                        <th>领取限制</th>
                        <th>券码说明</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            <button class="layui-btn layui-btn-xs layui-bg-blue" @click="deletes(item.id)">
                                <i style="cursor: pointer;" title="删除" class="layui-icon layui-icon-delete"></i>
                                {{ item.id }}
                            </button>
                        </td>
                        <td>
                            <span v-if="item.uid==-1" class="badge badge-success-lighten">未被领取</span>
                            <a v-else target="_blank" :href="'admin.order.list.php?uid='+item.uid"
                               class="badge badge-dark-lighten">
                                {{ item.uid }}
                            </a>
                        </td>
                        <td>
                            <span v-if="item.oid==-1" class="badge badge-success-lighten">未使用</span>
                            <span v-else-if="item.oid==-2" class="badge badge-warning-lighten">队列中</span>
                            <a v-else target="_blank" :href="'./admin.order.list.php?val='+item.oid">查看({{ item.oid
                                }})</a>
                        </td>
                        <td>
                            <span v-if="item.type==1" class="badge badge-success">满减券</span>
                            <span v-if="item.type==2" class="badge badge-primary">立减券</span>
                            <span v-if="item.type==3" class="badge badge-warning">折扣券</span>
                        </td>
                        <td>
                            <span v-if="item.apply==1" class="badge badge-success-lighten"
                                  @click="layer.msg('仅供编号为['+item.gid+']的商品使用')">单品优惠券<a target="_blank"
                                                                                         :href="'./admin.goods.add.php?gid='+item.gid">({{ item.gid }})</a></span>
                            <span v-if="item.apply==2" @click="layer.msg('分类编号['+item.cid+']下的商品均可使用')"
                                  class="badge badge-primary-lighten">品类券<a target="_blank"
                                                                            :href="'./admin.class.add.php?cid='+item.cid">({{ item.cid }})</a></span>
                            <span v-if="item.apply==3" @click="layer.msg('所有商品均可使用！')"
                                  class="badge badge-warning-lighten">商品通用券</span>
                        </td>

                        <td>{{ item.term_type==1?'领取后'+item.indate+'天失效':item.expirydate }}</td>
                        <td>{{ item.token }}</td>
                        <td>{{ item.type==3? item.money/10 +'折':item.money+'元' }}</td>
                        <td>{{ item.name }}</td>
                        <td>
                            <span v-if="item.type==1">满{{ item.minimum }}元,优惠{{ item.money }}元</span>
                            <span v-if="item.type==2">无门槛,下单立减{{ item.money }}元</span>
                            <span v-if="item.type==3">满{{ item.minimum }}元,享受{{ item.money/10 }}折优惠</span>
                        </td>
                        <td>
                            <span v-if="item.get_way==1">站长主动分享券码,用户后台兑换领取</span>
                            <span v-if="item.get_way==2">显示在对应商品<a target="_blank"
                                                                   :href="'./admin.goods.add.php?gid='+item.gid">({{ item.gid }})</a>详情内,用户可直接领取</span>
                            <span v-if="item.get_way==3">显示在商品分类<a target="_blank"
                                                                   :href="'./admin.class.add.php?cid='+item.cid">({{ item.cid }})</a>界面,用户可直接领取</span>
                            <span v-if="item.get_way==4">显示在商城首页,用户可直接领取</span>
                        </td>
                        <td>
                            <span v-if="item.oid==-1" class="badge badge-success-lighten">未使用</span>
                            <span v-else-if="item.oid==-2" class="badge badge-warning-lighten">队列中</span>
                            <span v-else>{{ item.endtime }}</span>
                        </td>
                        <td>
                            <span v-if="item.uid==-1" class="badge badge-success-lighten">未被领取</span>
                            <span v-else>{{ item.gettime }}</span>
                        </td>
                        <td>
                            <span v-if="item.oid==-1" class="badge badge-success-lighten">未使用</span>
                            <span v-else-if="item.oid==-2" class="badge badge-warning-lighten">队列中</span>
                            <span v-else>{{ item.ip }}</span>
                        </td>
                        <td>{{ item.addtime }}</td>
                        <td>单用户限领{{ item.limit }}个</td>
                        <td>{{ item.content }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="layui-card-body" style="text-align:center;">
                <div id="paging"></div>
            </div>
        </div>
    </div>
</div>

<?php
include 'bottom.php';
?>
<script src="../assets/admin/js/couponlist.js?vs=<?= $accredit['versions'] ?>"></script>