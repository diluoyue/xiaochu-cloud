<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/15 11:56
// +----------------------------------------------------------------------
// | Filename: index.php
// +----------------------------------------------------------------------
// | Explain: 主机数据中心
// +----------------------------------------------------------------------
include './header.php';
?>
<link rel="stylesheet" href="../../assets/layuiadmin/style/template.css" media="all">
<link rel="stylesheet" href="../../assets/css/Global.css" media="all">
<style>
    .layui-btn-radius {
        margin: 0.5em !important;
    }
</style>
<div class="layui-fluid" id="App">
    <div class="layui-row layui-col-space15 layui-anim layui-anim-upbit">
        <div class="layui-col-xs12">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-sm6 layui-col-md3">
                    <div class="layui-card">
                        <div class="layui-card-header">
                            总并发数
                            <span class="layuiadmin-badge layui-icon layui-icon-chart"></span>
                        </div>
                        <div class="layui-card-body layuiadmin-card-list">
                            <p class="layuiadmin-big-font">{{Data.concurrencyall}}</p>
                            <p>
                                单IP并发数
                                <span class="layuiadmin-span-color">{{Data.concurrencyip}}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="layui-col-sm6 layui-col-md3">
                    <div class="layui-card">
                        <div class="layui-card-header">
                            上行流量
                            <span class="layuiadmin-badge layui-icon layui-icon-chart"></span>
                        </div>
                        <div class="layui-card-body layuiadmin-card-list">
                            <p class="layuiadmin-big-font">{{Data.traffic}} KB</p>
                            <p>
                                当前主机最大上行流量为：
                                <span class="layuiadmin-span-color">{{Data.traffic}}KB</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="layui-col-sm6 layui-col-md3">
                    <div class="layui-card">
                        <div class="layui-card-header">
                            空间配额 <font color="red" v-if="Data.currentsize > Data.sizespace">超出，已限制文件上传功能</font>
                            <span class="layuiadmin-badge layui-icon layui-icon-chart"></span>
                        </div>
                        <div class="layui-card-body layuiadmin-card-list">
                            <p style="font-size:30px;" class="layuiadmin-big-font">{{Data.sizespace}}MB /
                                {{Data.currentsize}}MB</p>
                            <p>
                                当前主机最大可上传文件大小为：
                                <span class="layuiadmin-span-color">{{Data.filesize}}MB</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="layui-col-sm6 layui-col-md3">
                    <div class="layui-card">
                        <div class="layui-card-header">
                            域名绑定
                            <span class="layuiadmin-badge layui-icon layui-icon-chart"></span>
                        </div>
                        <div class="layui-card-body layuiadmin-card-list">
                            <p class="layuiadmin-big-font">{{Data.maxdomain}} 个</p>
                            <p>
                                当前主机最多可绑定域名数量为：
                                <span class="layuiadmin-span-color">{{Data.maxdomain}}个</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="Data.HostAnnounced!=''&&Data.HostAnnounced!=undefined&&Data.HostAnnounced!=null"
             class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header">
                    公告通知
                </div>
                <div class="layui-card-body editor" v-html="Data.HostAnnounced"></div>
            </div>
        </div>

        <div class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header">
                    功能面板
                </div>
                <div class="layui-card-body">
                    <a v-if="Data.type==1" lay-text="数据库管理" lay-href="view/SQL.php" target="_blank"
                       style="background-color: #ff1a7d"
                       class="layui-btn layui-btn-radius layui-btn-sm">
                        打开数据库
                    </a>
                    <button v-if="Data.type==2" style="background-color: #21c445"
                            @click="ActivateGet()"
                            class="layui-btn layui-btn-radius layui-btn-sm">
                        激活主机空间
                    </button>
                    <button v-if="Data.type==1" style="background-color: #FF9800"
                            @click="RenewalTypeGet((Data.RenewalType==1?2:1))"
                            class="layui-btn layui-btn-radius layui-btn-sm">
                        {{Data.RenewalType == 1 ? '关闭' : '开启'}}自动续期
                    </button>
                    <button v-if="Data.type==1" :style="'background-color:'+(Data.status==2?'#52e0cf':'red')"
                            @click="StatusGet((Data.status==1?2:1))"
                            class="layui-btn layui-btn-radius layui-btn-sm">
                        {{Data.status == 1 ? '关闭' : '开启'}}主机空间
                    </button>
                    <button v-if="Data.type==1" @click="Calibration()" style="background-color: #7b64e0"
                            class="layui-btn layui-btn-radius layui-btn-sm">
                        校准主机数据
                    </button>
                    <button v-if="Data.type==1" style="background-color: #e600ee"
                            @click="ResDatabasePasswordGet()"
                            class="layui-btn layui-btn-radius layui-btn-sm">
                        重置数据库密码
                    </button>
                    <button @click="SpaceDeleteSite()" style="background-color: #ef0a0a"
                            class="layui-btn layui-btn-radius layui-btn-sm">
                        永久删除主机
                    </button>
                </div>
            </div>
        </div>

        <div class="layui-col-xs12" v-show="Data.type==1">
            <div class="layui-card">
                <div class="layui-card-header">
                    主机续期
                </div>
                <div class="layui-card-body">
                    <div class="layui-panel" style="padding: 10px 10px 0 10px;">
                        <fieldset class="layui-elem-field">
                            <legend>续期时长 {{Data.RenewPrice * num }}元</legend>
                            <div class="layui-field-box">
                                <div class="layui-form">
                                    <div class="layui-form-item">
                                        <input type="radio" name="num" lay-filter="Duration" value="1"
                                               title="30天(1月)" checked>
                                        <input type="radio" name="num" lay-filter="Duration" value="2"
                                               title="60天(2月)">
                                        <input type="radio" name="num" lay-filter="Duration" value="3"
                                               title="90天(1季)">
                                        <input type="radio" name="num" lay-filter="Duration" value="6"
                                               title="180天(半年)">
                                        <input type="radio" name="num" lay-filter="Duration"
                                               value="12"
                                               title="360天(一年)">
                                        <input lay-ignore v-show="1!=1" type="radio" :key="num" name="num"
                                               lay-filter="Duration"
                                               value="24"
                                               title="720天(二年)">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="layui-elem-field">
                            <legend>付款方式</legend>
                            <div class="layui-field-box">
                                <div class="layui-btn-container">
                                    <button @click="Expenditure(1)" type="button"
                                            class="layui-btn layui-btn-primary layui-border-red">支付宝付款
                                    </button>
                                    <button @click="Expenditure(2)" type="button"
                                            class="layui-btn layui-btn-primary layui-border-green">微信付款
                                    </button>
                                    <button @click="Expenditure(3)" type="button"
                                            class="layui-btn layui-btn-primary layui-border-blue">QQ付款
                                    </button>
                                    <button @click="Expenditure(4)" v-if="Data.uid!=-1" type="button"
                                            class="layui-btn layui-btn-primary layui-border-black">余额付款
                                        剩：[ {{Data.money}}元 ]
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>

        <div class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header">
                    数据看板
                </div>
                <div class="layui-card-body layui-text" style="white-space:nowrap;overflow-x: auto;">
                    <table class="layui-table">
                        <thead>
                        <tr>
                            <th>名称</th>
                            <th>参数</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>主机编号</td>
                            <td>{{Data.id}}</td>
                        </tr>
                        <tr>
                            <td>绑定订单</td>
                            <td>{{(Data.oid == -1 ? '未绑定' : Data.oid)}}</td>
                        </tr>
                        <tr>
                            <td>续期价格</td>
                            <td>{{ Data.RenewPrice - 0 }}元 / 月
                            </td>
                        </tr>
                        <tr>
                            <td>数据库名称</td>
                            <td>{{Data.sql_name}}</td>
                        </tr>
                        <tr>
                            <td>数据库账号</td>
                            <td>{{Data.sql_user}}</td>
                        </tr>
                        <tr>
                            <td>数据库密码</td>
                            <td>{{Data.sql_pass}}
                                <a @click="ResDatabasePasswordGet()">重置</a>
                            </td>
                        </tr>
                        <tr>
                            <td>主机标识码</td>
                            <td>{{Data.identification}}</td>
                        </tr>
                        <tr>
                            <td>主机到期时间</td>
                            <td>{{Data.endtime}}</td>
                        </tr>
                        <tr>
                            <td>主机创建时间</td>
                            <td>{{Data.addtime}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header">
                    主机说明
                </div>
                <div class="layui-card-body">
                    <blockquote v-else class="layui-elem-quote layui-text" v-html="Data.content">
                    </blockquote>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/jquery-3.4.1.min.js"></script>
<script src="../../assets/layuiadmin/layui/layui.js"></script>
<script src="../../assets/js/vue3.js"></script>
<script>
    const App = Vue.createApp({
        data() {
            return {
                Data: [],
                num: 1,
            }
        }
        , methods: {
            SpaceDeleteSite() {
                layer.alert('是否要删除此主机空间?<br>一旦删除若里面有数据或文件,将永久性丢失！，且永远无法恢复！', {
                    btn: ['我已知晓后果,确认删除', '取消'],
                    title: '危险操作',
                    icon: 2, btn1: function () {
                        let is = layer.msg('正在删除中,请勿刷新或关闭页面...', {time: 99999, icon: 16, shade: [0.8, '#393D49']});
                        var admin = layui.admin;
                        admin.req({
                            url: '../ajax.php?act=SpaceDeleteSite',
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 0) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            top.location.reload();
                                        }
                                    });
                                }
                            }
                        })
                    }
                })
            },
            ResDatabasePasswordGet() {
                layer.open({
                    title: '温馨提示',
                    icon: 3,
                    content: '是否需要重置数据库密码？，确定后会随机生成一个新的数据库密码，旧的密码将失效！',
                    btn: ['确定', '取消'],
                    btn1: function () {
                        let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: '../ajax.php?act=ResDatabasePassword',
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.GetData();
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
                });
            },
            SizeCalibrationGet(type = 1) {
                if (type === 1) {
                    is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                }
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=SizeCalibration',
                    dataType: "json",
                    success: function (res) {
                        if (type === 1) {
                            layer.close(is);
                        }
                        if (res.code == 1) {
                            if (type === 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.GetData();
                                    }
                                });
                            } else {
                                App.GetData();
                            }
                        } else {
                            if (type === 1) {
                                layer.alert(res.msg, {
                                    icon: 2
                                });
                            }
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            Calibration() {
                layer.open({
                    title: '温馨提示',
                    icon: 3,
                    content: '是否需要校准此主机的参数？<br>可校准参数有：并发，上行流量，数据库信息，到期时间等，如果校准后还是存在问题，可以重启一下主机，点击开启|关闭主机按钮重启！，或联系客服处理！',
                    btn: ['确定', '取消'],
                    btn1: function () {
                        let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: '../ajax.php?act=Calibration',
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.GetData();
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
                });
            },
            Expenditure(type) {
                layer.open({
                    title: '温馨提示',
                    content: '是否要为此主机空间续期？<br>续期时长：' + App.num + '月<br>续期金额：' + (App.num * App.Data.RenewPrice) + '元',
                    icon: 3,
                    btn: ['确定续期', '取消'],
                    btn1: function () {
                        let is = layer.msg('正在操作中,请勿刷新或关闭页面...', {time: 99999, icon: 16, shade: [0.8, '#393D49']});
                        $.ajax({
                            type: "POST",
                            url: '../ajax.php?act=Expenditure',
                            data: {type: type, num: App.num},
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.GetData();
                                        }
                                    });
                                } else if (res.code == 2) {
                                    layer.open({
                                        title: '订单创建成功',
                                        content: '请点击下方按钮付款！',
                                        btn: ['付款', '取消'],
                                        btn1: function () {
                                            window.open(res.url);
                                            layer.msg('付款成功了吗？,点击确定刷新数据', {
                                                icon: 16,
                                                btn: '确定',
                                                btn1: function () {
                                                    App.GetData();
                                                },
                                                time: 666666,
                                                btnAlign: 'c',
                                                anim: 3
                                            })
                                        },
                                    });
                                } else {
                                    layer.alert(res.msg, {icon: 2})
                                }
                            },
                            error: function () {
                                layer.msg('服务器异常！');
                            }
                        });
                    }
                })
            },
            ActivateGet() {
                layer.alert('是否要激活此主机空间？,激活后开放管理权限', {
                    btn: ['激活', '取消'],
                    title: '信息确认',
                    icon: 1, btn1: function () {
                        let is = layer.msg('正在激活中,请勿刷新或关闭页面...', {time: 99999, icon: 16, shade: [0.8, '#393D49']});
                        $.ajax({
                            type: "POST",
                            url: '../ajax.php?act=server_activate',
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 0) {
                                    layer.alert(res.msg, {
                                        icon: 1, end: function () {
                                            App.GetData();
                                        }
                                    });
                                } else {
                                    layer.alert(res.msg, {icon: 2});
                                }
                            },
                            error: function () {
                                layer.msg('服务器异常！');
                            }
                        });
                    }
                })
            },
            StatusGet(type) {
                let is = layer.msg('切换中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=SpaceStatus',
                    data: {
                        state: type,
                    },
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 0) {
                            layer.alert(res.msg, {
                                icon: 1, end: function () {
                                    App.GetData();
                                }
                            });
                        } else {
                            layer.alert(res.msg, {icon: 2});
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            RenewalTypeGet(type) {
                let is = layer.msg('切换中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=SpaceRenewalSet',
                    data: {
                        state: type,
                    },
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 0) {
                            layer.alert(res.msg, {
                                icon: 1, end: function () {
                                    App.GetData();
                                }
                            });
                        } else {
                            layer.alert(res.msg, {icon: 2});
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            GetData() {
                let is = layer.msg('数据载入中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=HostDetails',
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            App.Data = res.data;
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

    layui.config({
        base: '../../assets/layuiadmin/'
    }).extend({
        index: 'lib/index'
    }).use(['index', 'table', 'form'], function () {
        App.GetData();
        App.SizeCalibrationGet(2);
        $.ajax({
            type: "post",
            url: "../ajax.php?act=SpaceVi",
            dataType: "json",
        });
        layui.form.on('radio(Duration)', function (data) {
            App.num = data.value;
            App.key++;
            layui.form.render(); //更新全部
        });
    });
</script>
