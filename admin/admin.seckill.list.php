<?php
// +----------------------------------------------------------------------
// | Project: fun.global.php
// +----------------------------------------------------------------------
// | Creation: 2021/8/11 15:03
// +----------------------------------------------------------------------
// | Filename: admin.seckill.list.php
// +----------------------------------------------------------------------
// | Explain: 秒杀活动列表
// +----------------------------------------------------------------------

$title = '商品限购秒杀活动列表';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="添加" href="admin.seckill.add.php" class="badge badge-primary"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <span class="mdui-m-l-1">共:{{count}}个秒杀活动</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto">
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>ID</th>
                        <th>商品ID</th>
                        <th>降价百分比</th>
                        <th>限购人数</th>
                        <th>已购人数</th>
                        <th>活动开始时间</th>
                        <th>活动结束时间</th>
                        <th>创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            <button :mdui-menu="'{target:\'#html_operation_'+item.gid+'\'}'"
                                    class="mdui-btn mdui-ripple mdui-color-white mdui-text-color-blue-grey mdui-shadow-0 mdui-btn-icon">
                                <i class="mdui-icon material-icons">&#xe8b8;</i>
                            </button>
                            <ul class="mdui-menu" :id="'html_operation_'+item.gid">
                                <li class="mdui-menu-item">
                                    <a :href="'admin.seckill.add.php?id='+item.id" class="mdui-ripple">编辑活动</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:" @click="Delete(item.id)" class="mdui-ripple">删除活动</a>
                                </li>
                            </ul>
                        </td>
                        <td>{{item.id}}</td>
                        <td>
                            <a :href="'../?mod=route&p=Goods&gid='+item.gid" target="_blank">{{item.gid}}</a>
                        </td>
                        <td>
                            <span class="badge badge-success-lighten">{{item.depreciate}}%</span>
                        </td>
                        <td>{{item.astrict}}</td>
                        <td>{{item.attend}}</td>
                        <td>
                            <span v-if="item.state==1" style="color: #0AAB89">
                                <span v-if="item.astrict>item.attend">
                                    {{item.start_time}}
                                </span>
                                <span v-else style="color: #e17c77">
                                    人数已上限
                                </span>
                            </span>
                            <span v-else-if="item.state==-1" style="color:red">
                                {{item.start_time}}
                            </span>
                            <span v-else>
                               {{item.start_time}}
                            </span>
                        </td>
                        <td>{{item.end_time}}</td>
                        <td>{{item.addtime}}</td>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个限价秒杀活动也没有' }}
                </div>
            </div>
            <div class="layui-card-body" style="text-align:center;">
                <div id="Page"></div>
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
                Data: [],
                page: 1,
                limit: 10,
                name: '',
                count: 0,
                type: -1,
            }
        }
        , methods: {
            SeckillList() {
                let is = layer.msg('商品载入中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: './main.php?act=SeckillList',
                    data: {
                        page: App.page,
                        limit: App.limit,
                        name: App.name,
                    },
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            App.Data = res.data;
                            App.type = 1;
                        } else {
                            App.Data = [];
                            App.type = 1;
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            SearchGoods() {
                mdui.prompt('可输入：商品ID，活动ID', '搜索秒杀活动',
                    function (str) {
                        App.initialization(str);
                    },
                    function () {
                    },
                    {
                        type: 'textarea',
                        maxlength: 999999999,
                        defaultValue: App.name,
                        confirmText: '确认搜索',
                        cancelText: '取消',
                    }
                );
            },
            Delete(id) {
                layer.open({
                    title: '温馨提示',
                    icon: 3,
                    content: '确定要删除吗？',
                    btn: ['确定', '取消'],
                    btn1: function () {
                        let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=SeckillDelete',
                            data: {
                                id: id
                            },
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.initialization();
                                        }
                                    });
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
                })
            },
            initialization(name = '', limit = -1) {
                this.page = 1;
                this.limit = (limit === -1 ? this.limit : limit);
                if (name == -2) {
                    this.name = '';
                } else {
                    this.name = (name === '' ? this.name : name);
                }
                this.type = -1;
                layui.use('laypage', function () {
                    var laypage = layui.laypage;
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=SeckillCount',
                        data: {
                            name: App.name,
                        },
                        dataType: "json",
                        success: function (res) {
                            if (res.code == 1) {
                                App.count = res.count;
                                laypage.render({
                                    elem: 'Page'
                                    , count: res.count
                                    , theme: '#641ec6'
                                    , limit: App.limit
                                    , limits: [1, 10, 20, 30, 50, 100, 200]
                                    , groups: 3
                                    , first: '首页'
                                    , last: '尾页'
                                    , prev: '上一页'
                                    , next: '下一页'
                                    , skip: true
                                    , layout: ['count', 'page', 'prev', 'next', 'limit', 'limits']
                                    , jump: function (obj) {
                                        App.page = obj.curr;
                                        App.limit = obj.limit;
                                        App.SeckillList();
                                    }
                                });
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
                });
            }
        }
    }).mount('#App');

    App.initialization();
</script>
