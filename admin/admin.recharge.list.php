<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/5 16:45
// +----------------------------------------------------------------------
// | Filename: admin.recharge.list.php
// +----------------------------------------------------------------------
// | Explain: 充值卡列表
// +----------------------------------------------------------------------

$title = '充值卡列表';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <a title="添加商品" href="./admin.recharge.add.php" class="badge badge-primary mdui-m-l-1"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a href="javascript:" @click="exports()" class="badge badge-success mdui-m-l-1"><i
                            class="layui-icon layui-icon-export"></i></a>
                <a href="javascript:" @click="BatchRemove()" class="badge badge-danger mdui-m-l-1"><i
                            class="layui-icon layui-icon-delete"></i></a>
                <a title="搜索用户" href="javascript:" @click="SearchGoods()" class="badge badge-info mdui-m-l-1"><i
                            class="layui-icon layui-icon-search"></i></a>
                <span class="mdui-m-l-1">共:{{count}}张充值卡</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto">
                <div v-if="name!==''" class="mb-2">正在查看搜索内容为[ {{name}} ]的相关充值卡 <a
                            href="javascript:App.initialization(-2);">查看全部</a>
                </div>
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>ID</th>
                        <th>类型</th>
                        <th>使用者</th>
                        <th>卡密名称</th>
                        <th>面额</th>
                        <th>卡号</th>
                        <th>IP</th>
                        <th>使用时间</th>
                        <th>生成时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            <button @click="Delete(item.id)"
                                    class="mdui-btn mdui-ripple mdui-color-white mdui-text-color-blue-grey mdui-shadow-0 mdui-btn-icon">
                                <i class="mdui-icon material-icons mdui-text-color-red" title="删除此卡密">&#xe92b;</i>
                            </button>
                        </td>
                        <td>
                            {{item.id}}
                        </td>
                        <td>
                            <span class="badge badge-primary-lighten" v-if="item.type==1">
                            余额充值卡
                            </span>
                            <span class="badge badge-warning-lighten" v-else>
                            积分充值卡
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-success-lighten" v-if="item.uid==-1">
                                未使用
                            </span>
                            <a :href="'admin.user.log.php?uid='+item.uid" target="_blank"
                               class="badge badge-danger-lighten" v-else>
                                {{item.uid}}
                            </a>
                        </td>
                        <td>
                            {{item.name}}
                        </td>
                        <td>
                            {{item.money}}
                        </td>
                        <td>
                            {{item.token}}
                        </td>
                        <td>
                            <span class="badge badge-success-lighten" v-if="item.uid==-1">
                                未使用
                            </span>
                            <span class="badge badge-danger-lighten" v-else>
                                {{item.ip}}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-success-lighten" v-if="item.uid==-1">
                                未使用
                            </span>
                            <span v-else>
                                {{item.endtime}}
                            </span>
                        </td>
                        <td>
                            {{item.addtime}}
                        </td>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个卡密也没有' }}
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
<script src="../assets/js/FileSaver.js"></script>
<script>
    const App = Vue.createApp({
        data() {
            return {
                Data: [],
                page: 1,
                limit: 10,
                count: 0,
                type: -1,
                name: '',
            }
        }
        , methods: {
            SearchGoods() {
                mdui.prompt('可输入：使用者ID，卡号，名称', '搜索充值卡',
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
                mdui.dialog({
                    title: '温馨提示',
                    content: '是否要删除ID为[' + id + ']的卡密？,删除后不可恢复！',
                    modal: true,
                    history: false,
                    buttons: [
                        {
                            text: '取消',
                        },
                        {
                            text: '确定删除',
                            onClick: function () {
                                let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                                $.ajax({
                                    type: "POST",
                                    url: 'main.php?act=DeleteRecharge',
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
                        }
                    ]
                });
            },
            RechargeList() {
                let is = layer.msg('列表载入中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: './main.php?act=RechargeList',
                    data: {
                        page: App.page,
                        limit: App.limit,
                        name: App.name
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
            BatchRemove() {
                let content = `
<div class="mdui-textfield">
                    <div style="height:2em;line-height:2em;">删除卡密类型</div>
                    <select class="mdui-select"
                            id="type"
                            style="width:100%;font-size:14px;color: rgba(29,29,29,0.77)">
                        <option value="1">全部类型</option>
                        <option value="2">余额充值卡</option>
                        <option value="3">积分充值卡</option>
                    </select>
                </div>
<div class="mdui-textfield">
                    <div style="height:2em;line-height:2em;">充值卡状态</div>
                    <select class="mdui-select"
                            id="state"
                            style="width:100%;font-size:14px;color: rgba(29,29,29,0.77)">
                        <option value="1">未使用</option>
                        <option value="2">已使用</option>
                    </select>
                </div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">请选择需要删除的卡密面额（留空删除全部）</label>
  <input class="mdui-textfield-input" id="money" type="number"/>
</div>
                `;

                mdui.dialog({
                    title: '请选择需要执行的操作',
                    content: content,
                    modal: true,
                    history: false,
                    buttons: [
                        {
                            text: '关闭',
                        },
                        {
                            text: '开始删除',
                            onClick: function () {
                                let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                                $.ajax({
                                    type: "POST",
                                    url: './main.php?act=RechargeBatchRemove',
                                    data: {
                                        type: $("#type").val(),
                                        state: $("#state").val(),
                                        money: $("#money").val(),
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
                        }
                    ]
                });
            },

            exports() {
                let content = `
<div class="mdui-textfield">
                    <div style="height:2em;line-height:2em;">导出卡密类型</div>
                    <select class="mdui-select"
                            id="type"
                            style="width:100%;font-size:14px;color: rgba(29,29,29,0.77)">
                        <option value="1">余额充值卡</option>
                        <option value="2">积分充值卡</option>
                    </select>
                </div>
<div class="mdui-textfield">
                    <div style="height:2em;line-height:2em;">充值卡状态</div>
                    <select class="mdui-select"
                            id="state"
                            style="width:100%;font-size:14px;color: rgba(29,29,29,0.77)">
                        <option value="1">未使用</option>
                        <option value="2">已使用</option>
                    </select>
                </div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">需导出的充值卡面额</label>
  <input class="mdui-textfield-input" id="money" type="number"/>
</div>
                `;

                mdui.dialog({
                    title: '请选择导出内容',
                    content: content,
                    modal: true,
                    history: false,
                    buttons: [
                        {
                            text: '关闭',
                        },
                        {
                            text: '确认导出',
                            onClick: function () {
                                if ($("#money").val() === '') {
                                    layer.msg('请填写完整！');
                                    return;
                                }
                                let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                                $.ajax({
                                    type: "POST",
                                    url: './main.php?act=PrepaidCardExported',
                                    data: {
                                        type: $("#type").val(),
                                        state: $("#state").val(),
                                        money: $("#money").val(),
                                    },
                                    dataType: "json",
                                    success: function (res) {
                                        layer.close(is);
                                        if (res.code == 1) {
                                            layer.alert(res.msg, {icon: 1});
                                            var blob = new Blob([(res.data).join('\n')], {type: "text/plain;charset=utf-8"});
                                            saveAs(blob, res.name);
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
                    ]
                });
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
                        url: './main.php?act=RechargeCount',
                        data: {
                            name: name
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
                                        App.RechargeList();
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
