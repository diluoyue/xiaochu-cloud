<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城
// +----------------------------------------------------------------------
// | Creation: 2022/1/13 9:13
// +----------------------------------------------------------------------
// | Filename: admin.goods.cash.list.php
// +----------------------------------------------------------------------
// | Explain: 商品卡密列表
// +----------------------------------------------------------------------
$title = '商品兑换卡列表';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="添加" href="./admin.goods.cash.add.php" class="badge badge-primary"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <a title="下载独立部署文件" href="javascript:App.Download();" class="badge badge-dark mdui-m-l-1"><i
                            class="layui-icon layui-icon-download-circle"></i></a>
                <a title="添加" href="../?mod=GoodsCash" target="_blank" class="badge badge-primary-lighten mdui-m-l-1"><i
                            class="layui-icon layui-icon-link"></i></a>
                <a title="已使用" href="javascript:App.TokenDelete('',2);"
                   style="height: 22px;margin-top: -12px;line-height: 18px;"
                   class="badge badge-danger-lighten mdui-m-l-1">删除已使用</a>
                <a title="未使用" href="javascript:App.TokenDelete('',3);"
                   style="height: 22px;margin-top: -12px;line-height: 18px;"
                   class="badge badge-danger-lighten mdui-m-l-1">删除未使用</a>
                <a title="全部" href="javascript:App.TokenDelete('',4);"
                   style="height: 22px;margin-top: -12px;line-height: 18px;"
                   class="badge badge-danger-lighten mdui-m-l-1">删除全部</a>
                <span class="mdui-m-l-1">共:{{count}}张商品兑换卡</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto">
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>ID</th>
                        <th>商品ID</th>
                        <th>商品名称</th>
                        <th>商品成本</th>
                        <th>订单ID</th>
                        <th>使用者ID</th>
                        <th>兑换卡</th>
                        <th>卡密状态</th>
                        <th>使用时间</th>
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
                                    <a href="javascript:" @click="TokenDelete(item.id,1)" class="mdui-ripple">删除卡密</a>
                                </li>
                            </ul>
                        </td>
                        <td>{{item.id}}</td>
                        <td>
                            <a :href="'./admin.order.list.php?gid='+item.gid" target="_blank">{{item.gid}}</a>
                        </td>
                        <td>
                            <a :href="'./admin.goods.add.php?gid='+item.gid" target="_blank"
                               class="layui-elip mdui-text-color-deep-orange" :title="item.name"
                               style="max-width:5em;cursor:pointer;display: block">{{
                                (item.name==null||item.name==''?'商品已删除':item.name) }}
                            </a>
                        </td>
                        <td><span class="badge badge-warning-lighten">{{item.money - 0}}元</span></td>
                        <td>
                            <span v-if="item.state==1" class="badge  ml-1 badge-success-lighten">未使用</span>
                            <a :href="'./admin.order.list.php?name='+item.oid" target="_blank" v-else
                               class="badge  ml-1 badge-primary-lighten">{{ item.oid }}</a>
                        </td>
                        <td>
                            <span v-if="item.state==1" class="badge  ml-1 badge-success-lighten">未使用</span>
                            <a :href="'./admin.user.log.php?uid='+item.uid" target="_blank" v-else
                               class="badge  ml-1 badge-primary-lighten">{{ (item.uid==-1?'游客':item.uid) }}</a>
                        </td>
                        <td><span @click="copyToClip(item.token)">{{item.token}}</span></td>
                        <td>
                            <span v-if="item.state==1" class="badge  ml-1 badge-success-lighten">未使用</span>
                            <span v-else class="badge  ml-1 badge-danger-lighten">已使用</span>
                        </td>
                        <td>
                            <span v-if="item.state==1" class="badge  ml-1 badge-success-lighten">未使用</span>
                            <span v-else class="badge  ml-1 badge-danger-lighten">{{ item.endtime }}</span>
                        </td>
                        <td>{{item.addtime}}</td>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一张卡也没有' }}
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
                type: 1,
                gid: '',
            }
        }
        , methods: {
            CardListGoodsList() {
                let is = layer.msg('卡密载入中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: './main.php?act=CardListGoods',
                    data: {
                        page: App.page,
                        limit: App.limit,
                        gid: App.gid,
                        type: App.type
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
            Download() {
                layer.open({
                    title: '温馨提示',
                    content: '是否要下载独立商品卡兑换页面部署文件？<br>文件名称：index.html<br>文件用途：可将此文件放置到其他服务器或主机内,当作独立站点使用,仅需将下载的文件上传到对应的站点内即可！<hr>' +
                        '注意：生成的文件完全是静态html文件，公告信息，底部全局配置等各种参数，若在当前站点修改后，需要在此处重新生成最新文件，重新部署！',
                    icon: 3,
                    btn: ['确定下载', '取消'],
                    btn1: function () {
                        let is = layer.msg('生成文件中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=HtmlGoodsCard',
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn: ['下载文件'], btn1: function () {
                                            layer.closeAll();
                                            App.downloadUrlFile(res.url, 'index.html');
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
            saveAs(data, name) {
                const urlObject = window.URL || window.webkitURL || window;
                const export_blob = new Blob([data]);
                const save_link = document.createElementNS('http://www.w3.org/1999/xhtml', 'a');
                save_link.href = urlObject.createObjectURL(export_blob);
                save_link.download = name;
                save_link.click();
                let formData3 = new FormData();
                formData3.append('taskid', id);
                postAction(_that.downloadOver, formData3)
            },
            // 下载含有url的文件
            downloadUrlFile(url, fileName) {
                const url2 = url.replace(/\\/g, '/');
                const xhr = new XMLHttpRequest();
                xhr.open('GET', url2, true);
                xhr.responseType = 'blob';
                xhr.onload = () => {
                    if (xhr.status === 200) {
                        App.saveAs(xhr.response, fileName);
                    }
                };
                xhr.send();
            },
            TokenDelete(id = '', type = 1) {
                layer.open({
                    title: '温馨提示',
                    content: '确认删除吗？删除后不可恢复！',
                    btn: ['确定', '取消'],
                    icon: 3,
                    btn1: function () {
                        let is = layer.msg('正在删除中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=DeleteGoodsCard',
                            data: {
                                state: type,
                                id: id,
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
            }, copyToClip(content, message = null) {
                var aux = document.createElement("input");
                aux.setAttribute("value", content);
                document.body.appendChild(aux);
                aux.select();
                document.execCommand("copy");
                document.body.removeChild(aux);
                if (message == null) {
                    layer.msg("复制成功", {icon: 1});
                } else {
                    layer.msg(message, {icon: 1});
                }
            },
            initialization(gid = '', limit = -1) {
                this.page = 1;
                this.limit = (limit === -1 ? this.limit : limit);
                if (name == -2) {
                    this.gid = '';
                } else {
                    this.gid = (gid === '' ? this.gid : gid);
                }
                this.type = 1;
                layui.use('laypage', function () {
                    const laypage = layui.laypage;
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=CardCountGoods',
                        data: {
                            gid: App.gid,
                            type: App.type
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
                                        App.CardListGoodsList();
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
