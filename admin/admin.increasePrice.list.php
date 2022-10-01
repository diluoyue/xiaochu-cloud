<?php
// +----------------------------------------------------------------------
// | Project: xc
// +----------------------------------------------------------------------
// | Creation: 2022/8/23
// +----------------------------------------------------------------------
// | Filename: admin.increasePrice.list.php
// +----------------------------------------------------------------------
// | Explain: 加价规则列表
// +----------------------------------------------------------------------


$title = '加价规则列表';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="添加" href="admin.increasePrice.add.php" class="badge badge-primary"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <a title="搜索用户" href="javascript:App.SearchGoods();" class="badge badge-danger mdui-m-l-1"><i
                            class="layui-icon layui-icon-search"></i></a>
                <span class="mdui-m-l-1">共:{{count}}个加价规则</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto">
                <div v-if="name!==''" class="mb-2">正在查看搜索内容为[ {{name}} ]的相关规则 <a
                            href="javascript:App.initialization(-2);">查看全部</a>
                </div>
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>ID</th>
                        <th>规则名称</th>
                        <th>规则详情</th>
                        <th>状态</th>
                        <th>是否被调用</th>
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
                                    <a :href="'admin.increasePrice.add.php?id='+item.id" class="mdui-ripple">编辑规则</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:" @click="Delete(item.id)" class="mdui-ripple">删除规则</a>
                                </li>
                            </ul>
                        </td>
                        <td>{{item.id}}</td>
                        <td>{{item.name}}</td>
                        <td style="white-space:nowrap;" v-html="item.rules"></td>
                        <td>
                            <span v-if="item.state==1" @click="StateSet(item.id,2)" style="cursor: pointer"
                                  class="badge badge-success-lighten"> 已启用 </span>
                            <span v-else class="badge badge-danger-lighten" style="cursor: pointer"
                                  @click="StateSet(item.id,1)"> 已停用 </span>
                        </td>
                        <td>
                            <span v-if="item.call" style="color: #00A7AA" v-html="item.call"></span>
                            <span v-else class="badge badge-danger-lighten"> 未被调用 </span>
                        </td>
                        <td>{{item.addtime}}</td>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'空空如也，快去添加一个吧' }}
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
            StateSet(id, state) {
                layer.load(1, {time: 999999});
                $.ajax({
                    type: "POST", url: './main.php?act=StateFareIncreaseRule',
                    data: {
                        id: id,
                        state: state,
                    }
                    , dataType: "json"
                    , success: function (res) {
                        layer.closeAll();
                        if (res.code === 1) {
                            App.initialization();
                        } else {
                            layer.alert(res.msg, {
                                icon: 2
                            });
                        }
                    }, error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            ListFareIncreaseRule() {
                let is = layer.msg('列表载入中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: './main.php?act=ListFareIncreaseRule',
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
                mdui.prompt('可输入：ID或名称', '搜索内容！',
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
                            url: './main.php?act=DeleteFareIncreaseRule',
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
                        url: './main.php?act=CountFareIncreaseRule',
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
                                        App.ListFareIncreaseRule();
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
