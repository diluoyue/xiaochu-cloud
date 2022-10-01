<?php

use Medoo\DB\SQL;

$title = '商品优惠券列表';
include 'header.php';
$DB = SQL::DB();
$GoodsList = $DB->select('goods', ['gid', 'name', 'state'], ['ORDER' => ['gid' => 'DESC']]);
if (isset($_QET['gid'])) {
    $Goods = $DB->get('goods', ['gid', 'name'], ['gid' => (int)$_QET['gid']]);
    if (!$Goods) show_msg('温馨提示', '商品不存在！');
} else $Goods = [
    'name' => '',
];

/**
 * type=1取出未使用，2取出已使用
 */
$Type = (empty($_QET['type']) ? 1 : ($_QET['type'] == 1 ? 1 : 2));
?>
<div class="row">
    <div class="col-sm-6 text-center">
        <a <?= $Goods['name'] == '' ? '' : 'href="./coupon.php"' ?> class="btn btn-outline-danger mb-2">
            <?= $Goods['name'] == '' ? '请选择商品' : $Goods['name'] . '- 查看全部' ?>
        </a>
        <a <?= $Type == 1 ? 'href="./coupon.php?type=2' . (!empty($_QET['gid']) ? '&gid=' . $_QET['gid'] : '') . '"' : 'href="./coupon.php?type=1' . (!empty($_QET['gid']) ? '&gid=' . $_QET['gid'] : '') . '"' ?>
                class="btn btn-outline-success mb-2">
            <?= $Type == 1 ? '查看全部已使用券码' : '查看全部未使用券码' ?>
        </a>
        <button onclick="search()" class="btn btn-outline-secondary mb-2">
            查询优惠券
        </button>
        <button onclick="conversion()" class="btn btn-outline-secondary mb-2">
            兑换优惠券
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
                        foreach ($GoodsList as $v) {
                            echo '<option value="' . $v['gid'] . '"' . ($_QET['gid'] == $v['gid'] ? 'selected' : '') . ' >' . $v['name'] . ' / ' . ($v['state'] == 1 ? '上架中' : '已下架') . '</option>
                            ';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
    <div class="col-xs-12 col-sm-12" id="app" data="<?= $Goods['gid'] ?>">
        <div class="card">
            <div class="card-header">
                券码总数 - {{sum}}
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
                        <th>使用订单</th>
                        <th>券类型</th>
                        <th>使用范围</th>
                        <th>卡券失效倒计时</th>
                        <th>券码</th>
                        <th>额度/则扣</th>
                        <th>券码名称</th>
                        <th>使用条件</th>
                        <th>使用时间</th>
                        <th>领取时间</th>
                        <th>券码说明</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            {{ item.id }}
                        </td>
                        <td>
                            <span v-if="item.oid==-1" class="badge badge-success-lighten">未使用</span>
                            <span v-else-if="item.oid==-2" class="badge badge-warning-lighten">队列中</span>
                            <a v-else target="_blank" :href="'./tickets_new.php?id='+item.oid">售后({{ item.oid }})</a>
                        </td>
                        <td>
                            <span v-if="item.type==1" class="badge badge-success">满减券</span>
                            <span v-if="item.type==2" class="badge badge-primary">立减券</span>
                            <span v-if="item.type==3" class="badge badge-warning">折扣券</span>
                        </td>
                        <td>
                            <span v-if="item.apply==1" class="badge badge-success-lighten"
                                  @click="layer.msg('仅供编号为['+item.gid+']的商品使用')">单品优惠券<a target="_blank"
                                                                                         :href="'../?mod=route&p=Goods&gid='+item.gid">({{ item.gid }})</a></span>
                            <span v-if="item.apply==2" @click="layer.msg('分类编号['+item.cid+']下的商品均可使用')"
                                  class="badge badge-primary-lighten">品类券<a target="_blank"
                                                                            href="../">({{ item.cid }})</a></span>
                            <span v-if="item.apply==3" @click="layer.msg('所有商品均可使用！')"
                                  class="badge badge-warning-lighten">商品通用券</span>
                        </td>

                        <td style="color:red">{{ item.expirydate }}</td>
                        <td>{{ item.token }}</td>
                        <td>{{ item.type==3? item.money/10 +'折':item.money+'元' }}</td>
                        <td>{{ item.name }}</td>
                        <td>
                            <span v-if="item.type==1">满{{ item.minimum }}元,优惠{{ item.money }}元</span>
                            <span v-if="item.type==2">无门槛,下单立减{{ item.money }}元</span>
                            <span v-if="item.type==3">满{{ item.minimum }}元,享受{{ item.money/10 }}折优惠</span>
                        </td>
                        <td>
                            <span v-if="item.oid==-1" class="badge badge-success-lighten">未使用</span>
                            <span v-else-if="item.oid==-2" class="badge badge-warning-lighten">队列中</span>
                            <span v-else>{{ item.endtime }}</span>
                        </td>
                        <td>
                            {{ item.gettime }}
                        </td>
                        <td>{{ item.content }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div style="text-align:center;" id="paging"></div>
        </div>
    </div>
</div>

<?php
include 'bottom.php';
?>
<script>
    type = <?= $Type ?>;
    gid = $("#app").attr('data');
    var vm = new Vue({
        el: '#app',
        data: {
            Data: [],
            sum: 0,
            page: 1,
            name: '',
            gid: gid,
            type: type,
        },
        methods: {
            conversion(token) {
                let ist = layer.msg('正在兑换中...', {
                    icon: 16,
                    time: 9999999
                });
                let _this = this;
                $.ajax({
                    type: 'post',
                    url: 'ajax.php?act=CouponConversion',
                    data: {
                        token: token,
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(ist);
                        if (data.code >= 1) {
                            _this.search('');
                            layer.alert(data.msg, {
                                icon: 1
                            });
                        } else layer.alert(data.msg, {
                            icon: 2
                        });
                    }
                })
            },
            search(name) {
                this.name = name;
                this.sum = 0;
                this.page = 1;
                layer.closeAll();
                this.Ajax();
            },
            Ajax() {
                let is = layer.msg('优惠券列表获取中...', {
                    icon: 16,
                    time: 9999999
                });
                let _this = this;
                $.ajax({
                    type: 'post',
                    url: 'ajax.php?act=CouponList',
                    data: {
                        page: this.page,
                        name: this.name,
                        gid: this.gid,
                        type: this.type,
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(is);
                        if (data.code >= 0) {
                            _this.Data = data.data;
                            if (_this.sum == 0) {
                                layui.use('laypage', function () {
                                    var laypage = layui.laypage;
                                    laypage.render({
                                        elem: 'paging',
                                        count: data.count,
                                        theme: '#641ec6'
                                        , limit: 16
                                        , groups: 6
                                        , first: '首页'
                                        , last: '尾页'
                                        , prev: '上一页'
                                        , next: '下一页'
                                        , skip: true
                                        , layout: ['count', 'page', 'prev', 'next', 'limits'],
                                        jump: function (obj, first) {
                                            _this.page = obj.curr;
                                            if (!first) {
                                                _this.Ajax();
                                            }
                                        }
                                    });
                                });
                            }
                            _this.sum = data.count;
                        } else {
                            if (_this.name != '') {
                                _this.Data = [];
                                layer.msg('什么都没搜索到~', {
                                    icon: 2
                                });
                            } else {
                                layer.msg(data.msg, {
                                    icon: 2
                                });
                            }

                        }
                        _this.load == false;
                    },
                    error: function () {
                        layer.close(is);
                        layer.alert('列表获取失败！');
                    }
                });
            }
        },
        mounted() {
            this.Ajax();
        }
    });

    layui.use('form', function () {
        var form = layui.form;
        form.on('select(goods)', function (data) {
            if (data.value != '') {
                location.href = 'coupon.php?type=' + type + '&gid=' + data.value;
            } else {
                location.href = 'coupon.php?type=' + type;
            }
        });
    });

    function search() {
        layer.prompt({
            formType: 3,
            value: '',
            title: '可输入券码/订单id/ID/名称',
        }, function (value, index, elem) {
            vm.search(value);
        });
    }

    function conversion() {
        layer.prompt({
            formType: 3,
            value: '',
            title: '请输入您获得的券码',
        }, function (value, index, elem) {
            vm.conversion(value);
        });
    }
</script>