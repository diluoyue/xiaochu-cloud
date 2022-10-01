<?php
$title = '同系统一键对接';
include 'header.php';
global $_QET;
?>
<div id="app" style="margin-top: 1em;">
    <div class="row" style="text-align: center">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%;">
            <div class="card widget-flat">
                <div class="card-body">
                    <h5 class="text-muted font-weight-normal mt-0">
                        商品总数</h5>
                    <h5 class="mt-3 mb-0" style="font-weight: 300">{{ GoodsCount }}个</h5>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%">
            <div class="card widget-flat">
                <div class="card-body">
                    <h5 class="text-muted font-weight-normal mt-0">
                        分类总数</h5>
                    <h5 class="mt-3 mb-0" style="font-weight: 300">{{ ClassCount }}个</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">对接配置</div>
        <div class="card-body">
            <div class="layui-form">
                <div class="layui-form-item">
                    <label>对接站域名</label>
                    <input v-model="form.domain" type="text" name="domain" required lay-verify="required"
                           placeholder="对接站点域名,包含http(s):// 和 /"
                           class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label>用户ID</label>
                    <input v-model="form.uid" type="text" name="domain" required lay-verify="required"
                           placeholder="请填写你在对接站注册的用户ID"
                           class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label>对接密钥</label>
                    <input v-model="form.token" type="text" name="token" required lay-verify="required"
                           placeholder="请填写在对方对接站获取的对接密钥"
                           class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label>数据缓存</label>
                    <select v-model="form.Caching" name="Caching" lay-ignore>
                        <option value="1">使用缓存数据[速度快,可能更新不及时]</option>
                        <option value="2">获取最新数据[速度略慢,但数据准确]</option>
                    </select>
                </div>
                <div class="layui-form-item">
                    <label>对接商品详情数据获取模式</label>
                    <select v-model="form.Forced" name="Forced" lay-ignore>
                        <option value="1">用默认获取方式[自动判断]</option>
                        <option value="2">强制调用系统内置接口[慢但稳]</option>
                    </select>
                </div>
                <div class="layui-form-item">
                    <label>数据筛选模式</label>
                    <select v-model="form.ScreeningMode" name="ScreeningMode" lay-ignore>
                        <option value="1">不进行数据筛选</option>
                        <option value="2">筛选商品名关键词</option>
                        <option value="3">筛选分类名称关键词</option>
                        <option value="4">筛选商品跟分类名称关键词</option>
                    </select>
                </div>
                <div class="layui-form-item" v-if="form.ScreeningMode!=1">
                    <label>筛选内容,多个关键词使用「,」进行分隔</label>
                    <input v-model="form.FilterContent" type="text" name="FilterContent"
                           placeholder="筛选内容,多个关键词使用英文逗号进行分隔"
                           class="layui-input">
                </div>
                <div class="layui-form-item" style="text-align: center;">
                    <button v-on:click="Start" class="btn btn-sm btn-primary">批量对接</button>
                    <button v-on:click="Test" class="btn btn-sm btn-danger">对接测试</button>
                    <button v-on:click="Caching" class="btn btn-sm btn-warning">缓存商品</button>
                </div>
            </div>
            <blockquote style="border-left-color: #526afa;" v-if="TestMsg" class="layui-elem-quote">{{ TestMsg }}
            </blockquote>
        </div>
    </div>
    <div class="card">
        <div class="card-header">注意事项</div>
        <div class="card-body">
            <ul>
                <li>1、为了防止对接站的分类和商品ID和当前站点的冲突，<span style="color: red">会删除当前站点内旧的分类和商品</span>，此插件仅适用于新站点！</li>
                <li>2、如果对方程序版本没有升级到V2.4+，则无法使用快速对接模式，商品详情获取接口将采用系统内置接口，调用速度较慢！</li>
                <li>3、如果对方版本升级到了V2.4+，则可以同时克隆到对方的运费模板数据！</li>
                <li>4、如果对方站点商品数量较多，可能会一键对接失败！除非对方站长做了商品缓存！或者服务器配置很好</li>
                <li>5、如果需要一键对接其他系统，可以前往<a href="./admin.goods.supply.php" target="_blank">供货大厅</a>批量对接，此功能仅限同系统可用！</li>
                <li>6、如果需要找同系统货源，可以前往<a href="./admin.goods.supply.php" target="_blank">供货大厅</a>里面的商城海寻找！</li>
            </ul>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script>
    let data = localStorage.getItem("Form");
    let form = {
        domain: '',
        uid: '',
        token: '',
        Caching: 1,
        Forced: 1, //是否强制调用
        ScreeningMode: 1, //筛选模式：2，筛选商品名关键词，3，筛选分类名称关键词，4，筛选商品跟分类名称关键词
        FilterContent: '', //筛选内容,多个使用逗号分隔
    };
    if (data) {
        form = JSON.parse(data);
    }
    var App = new Vue({
        el: "#app",
        data: {
            form: form,
            GoodsCount: 0,
            ClassCount: 0,
            Type: false, //是否已经开始批量对接
            TestMsg: '',
        },
        watch: {
            form: {
                handler(newValue, oldValue) {
                    localStorage.setItem("Form", JSON.stringify(oldValue));
                },
                deep: true
            }
        },
        mounted() {
            this.QuantityAcquisition();
        },
        methods: {
            QuantityAcquisition(type = 1) {
                let _this = this;
                $.ajax({
                    type: "POST", url: '../api.php?act=OnebuttonDockingSystem&TypeS=Count'
                    , dataType: "json"
                    , success: function (res) {
                        if (res.code === 1) {
                            _this.GoodsCount = res.GoodsCount;
                            _this.ClassCount = res.ClassCount;
                        }
                        if (type === 1) {
                            setTimeout(_this.QuantityAcquisition, 2000);
                        }
                    }, error: function () {
                        if (type === 1) {
                            setTimeout(_this.QuantityAcquisition, 2000);
                        }
                    }
                });
            },
            Caching() {
                if (this.Type) {
                    return;
                }
                let _this = this;
                layer.open({
                    title: '操作确认',
                    content: '确认要开始尝试缓存商品列表数据吗？缓存成功后其他用户对接你的站点时，可以快速获取商品列表数据，缓存有效时间为12小时，当某一等级的商品缓存数据失效后，会自动缓存一次[需要别人对接触发]',
                    btn: ['确认执行', '取消'],
                    icon: 3,
                    timeout: 1000 * 60 * 60, //设置超时的时间60分钟
                    yes: function () {
                        layer.closeAll();
                        layer.load(2, {
                            time: 999999
                        });
                        _this.Type = true;
                        _this.TestMsg = '正在缓存本站商品数据中，请耐心等待，不要退出界面。。。';
                        $.ajax({
                            type: "post",
                            url: '../api.php?act=OnebuttonDockingSystem&TypeS=Caching',
                            data: _this.form,
                            dataType: "json",
                            success: function (data) {
                                layer.closeAll();
                                _this.Type = false;
                                _this.TestMsg = data.msg;
                                if (data.code == 1) {
                                    layer.alert(data.msg, {
                                        icon: 1
                                    });
                                } else layer.alert(data.msg, {icon: 2})
                            }, error: function () {
                                layer.closeAll();
                                _this.Type = false;
                                _this.TestMsg = '缓存操作执行完成，由于商品数据量太大，仅缓存了部分！';
                            }
                        })
                    }
                });
            },
            Start() {
                if (this.Type) {
                    return;
                }
                let _this = this;
                layer.open({
                    title: '操作确认',
                    content: '确认开始执行一键对接操作吗，开始后中途不要退出页面<p style="color: red">注意：当执行开始后，旧的商品分类和商品全部会被删除！，注意备份，可以前往应用商店找到网站维护插件备份数据！</p><hr><p style="color: #e34d4d">温馨提示：如果对方站点未安装此插件，则获取商品详情会非常的慢，如果商品数量过多可能会批量对接商品失败，如果没有提示完成，请不要随意刷新页面，会导致本次批量对接失败！</p>',
                    btn: ['确认执行', '取消'],
                    icon: 3,
                    timeout: 1000 * 60 * 60, //设置超时的时间60分钟
                    yes: function () {
                        layer.closeAll();
                        layer.load(2, {
                            time: 999999
                        });
                        _this.Type = true;
                        _this.TestMsg = '正在批量对接中，请稍后，若对方未安装此插件，对接耗时会长一些，请耐心等待，不要退出页面！';
                        $.ajax({
                            type: "post",
                            url: '../api.php?act=OnebuttonDockingSystem&TypeS=Start',
                            data: _this.form,
                            dataType: "json",
                            success: function (data) {
                                layer.closeAll();
                                _this.Type = false;
                                _this.TestMsg = data.msg;
                                if (data.code == 1) {
                                    layer.alert(data.msg, {
                                        icon: 1
                                    });
                                } else layer.alert(data.msg, {icon: 2})
                            }, error: function () {
                                layer.closeAll();
                                _this.Type = false;
                                _this.TestMsg = '批量添加完成，由于商品数量过多，仅添加成功了部分！';
                            }
                        })
                    }
                });
            },
            Test() {
                if (this.Type) {
                    return;
                }
                var indes3 = layer.msg('正在测试中...', {
                    icon: 16,
                    time: 66666666
                });
                let _this = this;
                this.Type = true;
                this.TestMsg = '正在测试中。。。';
                $.ajax({
                    type: "post",
                    url: "../api.php?act=OnebuttonDockingSystem&TypeS=Test",
                    data: this.form,
                    dataType: "json",
                    success: function (data) {
                        _this.Type = false;
                        _this.TestMsg = data.msg;
                        layer.close(indes3);
                        layer.alert(data.msg);
                    }
                });
            }
        }
    });
    window.onbeforeunload = function (e) {
        var e = window.event || e;
        e.returnValue = ("确定离开当前页面吗？");
    }
</script>
<style>
    #app select {
        height: 38px;
        line-height: 1.3;
        line-height: 38px \9;
        background-color: #fff;
        border-radius: 2px;
        padding-right: 30px;
        cursor: pointer;
        display: block;
        width: 100%;
        padding-left: 10px;
        border: solid 1px #ccc;
    }

    #app ul > li {
        line-height: 2em;
    }
</style>