const vm = Vue.createApp({
    data() {
        return {
            id: $("#App").attr('sid'),
            Data: [],
            class_name: -1,
            BoxData: [],
            ip: -1,
            ips: -1,
            Form: [],
            Advertising: {}
        }
    }, methods: {
        AdvertisingGet() {
            $.ajax({
                type: "POST",
                url: './main.php?act=DockingAdvertising',
                data: {},
                dataType: "json",
                success: function (res) {
                    if (res.code == 1) {
                        vm.Advertising = res.data;
                    }
                }
            });
        },
        UpdateCache() {
            layer.open({
                title: '温馨提示',
                content: '是否要更新货源列表?,若安装过其他货源对接插件,可以点击此按钮更新对接列表',
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=SourceCache',
                        data: {},
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        vm.RuleGet();
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
        SelectAdvertising(index, name) {
            let _this = this;
            let data = {};
            try {
                data = this.Advertising[name][index];
                layer.open({
                    title: data.name,
                    offset: '60px',
                    content: `
<div style="overflow: hidden;max-height: 60vh;overflow-y: auto;width: 100%">
<fieldset class="layui-elem-field">
  <legend>货源介绍 - 对接教程</legend>
  <div class="layui-field-box">
    <div style="font-size: 18px;margin-bottom: 3px;">货源介绍：</div>
    <div style="border: solid 1px #ccc;padding: 1em;margin: 1em auto;">` + data.content + `</div>
    <div style="font-size: 18px;margin-top: 3px;">对接教程：</div>
    <p>1、打开域名：<a href="` + data.url + `" target="_blank">` + data.url + `</a></p>
    <p>2、注册账号，获取对接信息，返回当前页面填写对接信息！</p>
    <p>3、打开<a href="admin.goods.supply.php" target="_blank">供货大厅</a>，一键串货，批量上架商品！</p>
  </div>
</fieldset>
<fieldset class="layui-elem-field">
  <legend>遇到虚假货源站怎么办？</legend>
  <div class="layui-field-box">
    <p>1、加入<a href="https://cdn.79tian.com/api/wxapi/view/flock.php" target="_blank">官方交流群</a></p>
    <p>2、准备举报证据，联系群管理进行举报！</p>
    <p>3、还可以直接前往论坛进行举报：<a href="https://bbs.79tian.com/" target="_blank">进入</a></p>
  </div>
</fieldset>
<fieldset class="layui-elem-field">
  <legend>关于推荐货源的可信度问题？</legend>
  <div class="layui-field-box">
    <p>为了提高推荐可信度，已经采用了根据到期时间来进行排序的规则，推荐的货源越靠前，到期时间越长，相对来说越稳定！</p>
    <p>创建时间：` + data.addtime + `</p>
    <p>到期时间：` + data.endtime + `</p>
  </div>
</fieldset>
</div>`,
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    btn: ['接入', '打开货源站', '关闭'],
                    btn1: function () {
                        vm.Form = {};
                        vm.Form['url'] = data.url;
                        layer.alert('接入成功，是否需要打开此货源站点，获取对接配置信息？获取后前往当前页面填写即可完成对接！', {
                            icon: 3,
                            offset: '60px',
                            shade: [0.8, '#393D49'],
                            shadeClose: true,
                            btn: ['打开此货源站', '取消'],
                            btn1: function () {
                                open(data.url);
                                layer.closeAll();
                            }
                        })
                    }, btn2: function () {
                        open(data.url);
                        layer.closeAll();
                    }
                });
            } catch (e) {
                layer.msg('数据获取失败，请刷新页面重新尝试！');
                return false
            }
        },
        Select(class_name) {
            //选择对接类型
            this.class_name = class_name;
            for (const boxDataKey in this.Data[class_name]['field']) {
                if (this.Data[class_name]['field'][boxDataKey].type === 1) {
                    this.Form[boxDataKey] = (this.Form[boxDataKey] === undefined ? '' : this.Form[boxDataKey]);
                } else {
                    this.Form[boxDataKey] = (this.Form[boxDataKey] === undefined ? 1 : this.Form[boxDataKey]);
                }
            }
            this.BoxData = this.Data[class_name]['field'];
            if (this.Data[class_name].ip !== -1) {
                vm.ips = 1;
                if (this.ip === -1) {
                    vm.ipGet();
                }
            } else {
                vm.ips = -1;
            }
        }, ipGet() {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=ip', dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        vm.ip = res.ip;
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, Get() {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=SourceDataList', data: {
                    id: vm.id
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    let Data;
                    if (res.code == 1) {
                        Data = res.data[0];
                        vm.Form = {};
                        vm.Form['url'] = Data.url;
                        vm.Form['pattern'] = Data.pattern;
                        vm.Form['username'] = '已隐藏,不改动就不修改';
                        vm.Form['password'] = '已隐藏,不改动就不修改';
                        vm.Form['secret'] = Data.secret;
                        vm.Form['annotation'] = Data.annotation;
                        vm.Select(Data.class_name);
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, RuleGet() {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=SourceList', dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        vm.Data = res.data;
                        if (vm.id != -1) {
                            vm.Get();
                        }
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }
    }
}).mount('#App');

layui.use('form', function () {
    var form = layui.form;
    form.on('submit(Preserve)', function (data) {
        data.field = vm.Form;
        data.field['class_name'] = vm.class_name;
        if (vm.id != -1) {
            data.field['SQID'] = vm.id;
        }
        let Data = {};
        for (const dataKey in data.field) {
            Data[dataKey] = data.field[dataKey];
        }
        let is = layer.msg('添加中，请稍后...', {icon: 16, time: 9999999});
        layer.open({
            title: '温馨提示', icon: 3, content: '是否要执行此操作？', btn: ['确定', '取消'], btn1: function () {
                $.ajax({
                    type: "POST",
                    url: './main.php?act=SourceAdd',
                    data: Data,
                    dataType: "JSON",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            if (vm.id != -1) {
                                layer.alert(res.msg, {
                                    btn: ['刷新', '返回列表'], icon: 1, btn1: function () {
                                        vm.RuleGet();
                                    }, btn2: function () {
                                        location.href = 'admin.source.list.php';
                                    }
                                });
                            } else {
                                layer.alert(res.msg, {
                                    btn: ['再添加一个', '返回列表'], icon: 1, btn1: function () {
                                        location.reload();
                                    }, btn2: function () {
                                        location.href = 'admin.source.list.php';
                                    }
                                });
                            }
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
        return false;
    });
});

vm.RuleGet();
vm.AdvertisingGet();