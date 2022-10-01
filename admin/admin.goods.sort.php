<?php

/**
 * Filename：admin.goods.sort.php
 * 1、显示商品数量，
 * 2、功能：根据商品ID，根据商品名称，根据商品成本，根据商品库存，根据商品上架状态
 */
$title = '商品排序管理';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                商品列表（根据sort值升序显示前100个）
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-muted font-14">当前商品总数：{{ count }}个(包含已下架商品)</p>
                        <div class="button-list">
                            <button type="button" onclick="operation.execute(this.innerHTML,1)"
                                    class="btn btn-outline-primary btn-rounded">根据商品ID排序
                            </button>
                            <button type="button" onclick="operation.execute(this.innerHTML,2)"
                                    class="btn btn-outline-secondary btn-rounded">根据商品名称排序
                            </button>
                            <button type="button" onclick="operation.execute(this.innerHTML,3)"
                                    class="btn btn-outline-success btn-rounded">根据商品成本排序
                            </button>
                            <button type="button" onclick="operation.execute(this.innerHTML,4)"
                                    class="btn btn-outline-warning btn-rounded">根据商品库存排序
                            </button>
                            <button type="button" onclick="operation.execute(this.innerHTML,5)"
                                    class="btn btn-outline-info btn-rounded">根据商品上架状态排序
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body mdui-table-fluid" style="border:none !important;padding:0;">
                <table class="mdui-table mdui-table-hoverable">
                    <colgroup>
                        <col width="88">
                        <col width="88">
                        <col width="100">
                        <col width="100">
                        <col width="88">
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>商品ID</th>
                        <th>排序ID</th>
                        <th>成本</th>
                        <th title="库存仅判断自己设置的库存，不判断卡密库存">库存总数</th>
                        <th>状态</th>
                        <th>名称</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in GoodsList">
                        <td title="编辑商品">
                            <a style="color: slateblue" :href="'admin.goods.add.php?gid=' + item.gid" target="_blank">{{
                                item.gid }}</a>
                        </td>
                        <td>{{ item.sort }}</td>
                        <td>{{ item.money-0 }}元</td>
                        <td>{{ item.quota }}份</td>
                        <td>
                            <font v-if="item.state==1" color="#40e0d0">上架中</font>
                            <font v-else color="#ff6347">已下架</font>
                        </td>
                        <td>
                            <div class="layui-elip" :title="item.name" style="max-width:20em;cursor:pointer">
                                {{item.name}}
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script>
    const App = Vue.createApp({
        data() {
            return {
                GoodsList: [],
                count: '0',
            }
        },
        methods: {
            Get() {
                let is = layer.msg('加载中，请稍后...', {
                    icon: 16,
                    time: 9999999
                });
                let _this = this;
                $.ajax({
                    type: "POST",
                    url: 'ajax.php?act=BatchSort',
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            _this.GoodsList = res.data;
                            _this.count = res.count;
                        } else {
                            layer.alert(res.msg, {
                                icon: 2
                            });
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            }
        }
    }).mount('#App');

    App.Get();

    var operation = {
        execute(t, i) {
            layer.alert('是否要执行：<font color=red>' + t + '</font>？<br>请点击下方按钮选择排序规则！', {
                icon: 3,
                btn: ['降序排序', '升序排序', '取消'],
                btn1: function (layero, index) {
                    operation.ajax(i, 1);
                },
                btn2: function (layero, index) {
                    operation.ajax(i, 2);
                }
            });
        },
        ajax(id, type) {
            layer.msg('执行中,请稍后...', {
                icon: 16,
                time: 999999
            });
            $.ajax({
                type: "POST",
                url: "ajax.php?act=BatchSortSettings",
                data: {
                    id: id,
                    type: type
                },
                dataType: "json",
                success: function (data) {
                    layer.closeAll();
                    if (data.code == 1) {
                        layer.alert('<h4>' + data.msg + '</h4>调整商品总数：' + data.count + '个<br>调整成功数量：' + data.data.success + '个<br>调整失败数量：' +
                            data.data.error.length + '个', {
                            title: '执行结果如下',
                            btn1: function (layero, index) {
                                App.Get();
                            }
                        });
                    } else {
                        layer.alert(data.msg, {
                            btn1: function (layero, index) {
                                App.Get();
                            }
                        });
                    }
                },
                error: function () {
                    layer.alert('加载失败！');
                }
            });
        }
    }
</script>
</div>
