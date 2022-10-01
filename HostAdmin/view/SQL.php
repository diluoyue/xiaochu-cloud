<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/16 0:35
// +----------------------------------------------------------------------
// | Filename: SQL.php
// +----------------------------------------------------------------------
// | Explain: 数据库跳转?
// +----------------------------------------------------------------------

include './header.php';
?>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            数据库管理
        </div>
        <div id="App" class="layui-card-body">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md12">
                    <div class="layui-panel">
                        <div style="padding: 10px;">
                            数据库名称：{{data.name}}<br>
                            数据库账号：{{data.user}}<br>
                            数据库密码：{{data.pass}}<br>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md12">
                    <div class="layui-panel">
                        <div style="padding: 10px;">
                            <div class="layui-btn-container">
                                <button type="button" @click="GetData(2)"
                                        class="layui-btn layui-btn-primary layui-border-red">重载
                                </button>
                                <button type="button" @click="ResDatabasePasswordGet()"
                                        class="layui-btn layui-btn-primary layui-border-red">重置密码
                                </button>
                                <button type="button" @click="Calibration()"
                                        class="layui-btn layui-btn-primary layui-border-orange">校准数据
                                </button>
                                <a :lay-href="data.url" lay-text="数据库管理"
                                   class="layui-btn layui-btn-primary layui-border-green">打开数据库 [ 模式一
                                    ]
                                </a>
                                <a :href="data.url" target="_blank"
                                   class="layui-btn layui-btn-primary layui-border-blue">打开数据库 [ 模式二
                                    ]
                                </a>
                            </div>
                        </div>
                    </div>
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
                data: false,
            }
        }
        , mounted() {
            this.GetData();
        }
        , methods: {
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
            GetData(type = 1) {
                let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=DatabaseInformation',
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            if (type === 2) {
                                layer.msg('数据重载成功！', {icon: 1});
                            }
                            App.data = res.data;
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
        base: '../../assets/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use('index');

</script>