<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城
// +----------------------------------------------------------------------
// | Creation: 2022/1/25 15:16
// +----------------------------------------------------------------------
// | Filename: admin.order.pay.php
// +----------------------------------------------------------------------
// | Explain: 支付订单列表，列表+删除+补单
// +----------------------------------------------------------------------
$title = '支付订单管理';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <a title="搜索订单" href="javascript:App.SearchGoods();" class="badge badge-danger mdui-m-l-1"><i
                            class="layui-icon layui-icon-search"></i></a>
                <span class="mdui-m-l-1">共:{{count}}条支付订单</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto;">
                <div v-if="name!==''" class="mb-2">正在搜索[ {{name}} ]相关的支付订单 <a
                            href="javascript:App.initialization(-2);">查看全部</a>
                </div>
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap;">
                    <tr>
                        <th>操作</th>
                        <th>ID</th>
                        <th>用户</th>
                        <th>商品订单</th>
                        <th>类型</th>
                        <th>名称</th>
                        <th>金额</th>
                        <th>数量</th>
                        <th>状态</th>
                        <th>支付订单</th>
                        <th>对接订单</th>
                        <th>付款方式</th>
                        <th>订单信息</th>
                        <th>完成时间</th>
                        <th>创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            <button :mdui-menu="'{target:\'#html_operation_'+item.id+'\'}'"
                                    class="mdui-btn mdui-ripple mdui-color-white mdui-text-color-blue-grey mdui-shadow-0 mdui-btn-icon">
                                <i class="mdui-icon material-icons">&#xe8b8;</i>
                            </button>
                            <ul class="mdui-menu" :id="'html_operation_'+item.id">
                                <li class="mdui-menu-item">
                                    <a href="javascript:" class="mdui-ripple">
                                        订单ID：{{ item.id }}
                                    </a>
                                </li>
                                <li class="mdui-menu-item" v-if="item.state==2">
                                    <a href="javascript:" @click="OrderPaySubmit(item.id)" class="mdui-ripple">补单</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:" @click="OrderPayDelete(item.id)" class="mdui-ripple">删除</a>
                                </li>
                            </ul>
                        </td>
                        <td>{{item.id}}</td>
                        <td>
                            <span class="badge badge-info-lighten" v-if="item.uid==-1">
                                游客
                            </span>
                            <a :href="'./admin.user.log.php?uid='+item.uid" class="badge badge-primary-lighten" v-else>
                                {{item.uid}}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-danger-lighten" v-if="item.oid==-1">
                                未绑定
                            </span>
                            <a v-else :href="'./admin.order.list.php?name='+item.oid"
                               class="badge badge-warning-lighten"
                               target="_blank">{{item.oid}}</a>
                        </td>
                        <td>
                            <span class="badge badge-primary-lighten" v-if="item.gid==-1">在线充值</span>
                            <span class="badge badge-info-lighten" v-else-if="item.gid==-2">队列支付</span>
                            <span class="badge badge-warning-lighten" v-else-if="item.gid==-3">主机续期</span>
                            <span class="badge badge-success-lighten" v-else>在线购买</span>
                        </td>
                        <td>
                            <div class="layui-elip"
                                 :title="item.name"
                                 :onclick="'layer.msg(\''+item.name+'\')'"
                                 style="max-width: 5em; cursor: pointer;">
                                {{ item.name==null?'商品不存在':item.name }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-primary-lighten">
                                {{ item.money-0 }}元
                            </span>
                        </td>
                        <td>
                            {{item.num}}
                        </td>
                        <td>
                            <span class="badge badge-success-lighten" v-if="item.state==1">已完成</span>
                            <span class="badge badge-danger-lighten" v-else>未支付</span>
                        </td>
                        <td>
                            <span class="badge badge-primary-lighten">
                                {{item.order}}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-danger-lighten" v-if="item.trade_no==null">
                                无对接支付订单
                            </span>
                            <span class="badge badge-warning-lighten" v-else>
                                {{item.trade_no}}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-primary-lighten" v-if="item.type=='qqpay'">QQ支付</span>
                            <span class="badge badge-success-lighten" v-else-if="item.type=='wxpay'">微信支付</span>
                            <span class="badge badge-info-lighten" v-else>支付宝</span>
                        </td>
                        <td>
                            <span class="badge badge-primary-lighten" v-if="item.input=='-1'">在线充值</span>
                            <span v-else>{{item.input}}</span>
                        </td>
                        <td>
                            <span class="badge badge-danger-lighten" v-if="item.endtime==null">
                                此订单未完成
                            </span>
                            <span class="badge badge-warning-lighten" v-else>
                                {{item.endtime}}
                            </span>
                        </td>
                        <td>
                            {{item.addtime}}
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一条支付订单也没有' }}
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
            /**
             * @param {Object} value
             * 四舍五入
             * @param type
             */
            NumRound(value, type = 1) {
                value -= 0;
                let num = value.toFixed(8) - 0;
                if (type === 1) {
                    return num;
                } else {
                    return num.toString().split('.')[1].length;
                }
            },
            OrderPayList() {
                let is = layer.msg('商品载入中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: './main.php?act=OrderPayList',
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
            OrderPaySubmit(id) {
                layer.open({
                    title: '温馨提示',
                    content: '是否要为此支付订单补单，补单成功后，会执行对应的操作，如充值，续期，购买商品等？',
                    icon: 3,
                    btn: ['确定', '取消'],
                    btn1: function () {
                        let is = layer.msg('补单中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=OrderPaySubmit',
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
            OrderPayDelete(id) {
                layer.open({
                    title: '温馨提示',
                    content: '是否要删除此支付订单？',
                    icon: 3,
                    btn: ['确定', '取消'],
                    btn1: function () {
                        let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=OrderPayDelete',
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
            SearchGoods() {
                mdui.prompt('可输入：用户ID，订单号，对接信息等', '搜索支付订单',
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
                        url: './main.php?act=OrderPayCount',
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
                                        App.OrderPayList();
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
