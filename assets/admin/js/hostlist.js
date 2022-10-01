const App = Vue.createApp({
    data() {
        return {
            Data: [], page: 1, limit: 10, name: '', count: 0, type: -1, sid: $("#App").attr('sid'),
        }
    }, methods: {
        Calibration(id, index) {
            let is = layer.msg('正在校准主机空间配额数据，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=SizeCalibration', data: {
                    id: id
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        layer.alert(res.msg, {
                            icon: 1, btn1: function () {
                                App.HostList();
                            }
                        });
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, LogHostBackground(key) {
            layer.open({
                title: '温馨提示', content: '是否要登陆此空间的管理后台？', icon: 3, btn: ['确定登陆', '取消'], btn1: function () {
                    let is = layer.msg('登陆数据写入中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=LogHostBackground',
                        data: {key: key},
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        layer.closeAll();
                                        open(res.url);
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
        }, DeleteHost(id) {
            layer.open({
                title: '温馨提示',
                content: '是否要执行此操作，主机删除后无法恢复，主机数据永久丢失？',
                icon: 3,
                btn: ['确定删除', '取消'],
                btn1: function () {
                    let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=DeleteHost',
                        data: {id: id},
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code >= 0) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.HostList();
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
        }, HostStatusModification(id, state) {
            let is = layer.msg('正在操作中,请勿刷新或关闭页面...', {time: 99999, icon: 16, shade: [0.8, '#393D49']});
            $.ajax({
                type: "POST", url: './main.php?act=HostStatusModification', data: {
                    id: id, state: (state == 1 ? 2 : 1)
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code >= 0) {
                        App.HostList();
                    } else {
                        layer.alert(res.msg, {icon: 2});
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, SpaceStatus(id, state) {
            layer.alert('是否要' + (state == 1 ? '暂停此空间？' : '启动此空间？'), {
                btn: ['确认', '取消'], title: '信息确认', icon: 3, btn1: function () {
                    let is = layer.msg('正在操作中,请勿刷新或关闭页面...', {time: 99999, icon: 16, shade: [0.8, '#393D49']});
                    $.ajax({
                        type: "POST", url: './main.php?act=SpaceStatus', data: {
                            id: id, state: (state == 1 ? 2 : 1)
                        }, dataType: "json", success: function (res) {
                            layer.close(is);
                            if (res.code >= 0) {
                                layer.alert(res.msg, {
                                    icon: 1, end: function () {
                                        App.HostList();
                                    }
                                });
                            } else {
                                layer.alert(res.msg, {icon: 2});
                            }
                        }, error: function () {
                            layer.msg('服务器异常！');
                        }
                    });
                }
            })
        }, HostActivation(id) {
            layer.alert('是否要激活此主机空间？', {
                btn: ['激活', '取消'], title: '信息确认', icon: 3, btn1: function () {
                    let is = layer.msg('正在激活中,请勿刷新或关闭页面...', {time: 99999, icon: 16, shade: [0.8, '#393D49']});
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=HostActivation',
                        data: {id: id},
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code >= 0) {
                                layer.alert(res.msg, {
                                    icon: 1, end: function () {
                                        App.HostList();
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
        }, AdjustingParameters(data) { //调整主机参数
            let content = `
<div class="mdui-textfield">
  <label class="mdui-textfield-label">续费价格(元/月)</label>
  <input class="mdui-textfield-input" id="RenewPrice" value="` + data.RenewPrice + `" type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">用户ID(填写-1,不绑定用户)</label>
  <input class="mdui-textfield-input" id="uid" value="` + data.uid + `" type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">域名绑定数</label>
  <input class="mdui-textfield-input" id="maxdomain" value="` + data.maxdomain + `" type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">并发总数</label>
  <input class="mdui-textfield-input" id="concurrencyall" value="` + data.concurrencyall + `" type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">单IP并发</label>
  <input class="mdui-textfield-input" id="concurrencyip" value="` + data.concurrencyip + `" type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">流量上限(KB)</label>
  <input class="mdui-textfield-input" id="traffic" value="` + data.traffic + `"  type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">最大可上传文件(MB)</label>
  <input class="mdui-textfield-input" id="filesize" value="` + data.filesize + `" type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">可用空间大小(MB)</label>
  <input class="mdui-textfield-input" id="sizespace" value="` + data.sizespace + `" type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">到期时间</label>
  <input class="mdui-textfield-input" id="endtime" value="` + data.endtime + `" type="text"/>
</div>
                `;

            mdui.dialog({
                title: '调整主机【' + data.id + '】参数', content: content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }, {
                    text: '保存数据', onClick: function () {
                        let Form = {
                            'id': data.id,
                            'RenewPrice': $("#RenewPrice").val(),
                            'uid': $("#uid").val(),
                            'maxdomain': $("#maxdomain").val(),
                            'concurrencyall': $("#concurrencyall").val(),
                            'concurrencyip': $("#concurrencyip").val(),
                            'traffic': $("#traffic").val(),
                            'filesize': $("#filesize").val(),
                            'sizespace': $("#sizespace").val(),
                            'endtime': $("#endtime").val(),
                        };

                        for (let formKey in Form) {
                            if (Form[formKey] == '') {
                                mdui.dialog({
                                    title: '警告', content: '请填写完整！', modal: true, history: false, buttons: [{
                                        text: '关闭',
                                    }]
                                });
                                return false;
                            }
                        }

                        let is = layer.msg('正在调整中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=ModifyHostSpace',
                            data: Form,
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.HostList();
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
                }], onOpen: function () {
                    mdui.mutation();
                }
            });
        }, HostList() {
            let is = layer.msg('载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=HostList', data: {
                    page: App.page, limit: App.limit, name: App.name, sid: App.sid,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.type = 1;
                    } else {
                        App.Data = [];
                        App.type = 1;
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, SearchGoods() {
            mdui.prompt('可输入：用户ID，主机ID，主机标识，服务器ID，登陆账号', '搜索主机', function (str) {
                App.initialization(str);
            }, function () {
            }, {
                type: 'textarea', maxlength: 999999999, defaultValue: App.name, confirmText: '确认搜索', cancelText: '取消',
            });
        }, initialization(name = '', limit = -1) {
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
                    type: "POST", url: './main.php?act=HostCount', data: {
                        name: App.name, sid: App.sid,
                    }, dataType: "json", success: function (res) {
                        if (res.code == 1) {
                            App.count = res.count;
                            laypage.render({
                                elem: 'Page',
                                count: res.count,
                                theme: '#641ec6',
                                limit: App.limit,
                                limits: [1, 10, 20, 30, 50, 100, 200],
                                groups: 3,
                                first: '首页',
                                last: '尾页',
                                prev: '上一页',
                                next: '下一页',
                                skip: true,
                                layout: ['count', 'page', 'prev', 'next', 'limit', 'limits'],
                                jump: function (obj) {
                                    App.page = obj.curr;
                                    App.limit = obj.limit;
                                    App.HostList();
                                }
                            });
                        } else {
                            layer.alert(res.msg, {
                                icon: 2
                            });
                        }
                    }, error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            });
        }
    }
}).mount('#App');

App.initialization();