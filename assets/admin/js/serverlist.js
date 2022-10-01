const App = Vue.createApp({
    data() {
        return {
            Data: [], name: '', count: 0, type: -1, RealTimeData: [], ErrorData: [], time: 5,
        }
    }, methods: {
        DeleteServer(Data) {
            layer.open({
                title: '警告',
                content: '是否要删除此服务器节点？<br>删除后，此节点下的服务器均无法登录！',
                icon: 2,
                btn: ['确定删除', '取消'],
                btn1: function () {
                    let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './main.php?act=DeleteServer', data: {
                            id: Data.id,
                        }, dataType: "json", success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.ServerList();
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
                }
            })
        }, CreateHost(index) {
            let Data = App.Data[index];
            let content = `<div class="mdui-textfield">
  <label class="mdui-textfield-label">主机有效期(天)</label>
  <input class="mdui-textfield-input" value="30" id="day" type="number"/>
<div class="mdui-textfield-helper">到期时间从主机激活后开始计算</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">绑定用户ID</label>
  <input class="mdui-textfield-input" value="-1" id="uid" type="number"/>
<div class="mdui-textfield-helper">绑定后，主机将直接在用户后台显示，可填写-1，不绑定用户，只可使用账号密码登录！</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">续费金额(月)</label>
  <input class="mdui-textfield-input" value="1" id="money" type="number"/>
<div class="mdui-textfield-helper">每月的续费金额是多少？</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">最多可绑定域名数量(个)</label>
  <input class="mdui-textfield-input" id="maxdomain" value="3" type="number"/>
<div class="mdui-textfield-helper">这个主机最多可以绑定多少个域名？</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">并发总数</label>
  <input class="mdui-textfield-input" id="concurrencyall" value="200" type="number"/>
<div class="mdui-textfield-helper">此主机的并发总数是多少</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">单IP并发</label>
  <input class="mdui-textfield-input" id="concurrencyip" value="30" type="number"/>
<div class="mdui-textfield-helper">此主机的单IP并发是多少</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">上行流量上限(KB)</label>
  <input class="mdui-textfield-input" id="traffic" value="512" type="number"/>
<div class="mdui-textfield-helper">此主机的最大上行流量是多少？</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">上传文件大小(MB)</label>
  <input class="mdui-textfield-input" id="filesize" value="12" type="number"/>
<div class="mdui-textfield-helper">此主机最大可以上传多少MB的文件？</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">可用空间大小(MB)</label>
  <input class="mdui-textfield-input" id="filesize" value="200" type="number"/>
<div class="mdui-textfield-helper">此主机最多可以存储多大空间的文件?</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">主机登录账号</label>
  <input class="mdui-textfield-input" id="username" value="" type="text"/>
<div class="mdui-textfield-helper">独立主机管理面板的登录账号(留空随机生成)</div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">主机登录密码</label>
  <input class="mdui-textfield-input" id="password" value="" type="text"/>
<div class="mdui-textfield-helper">独立主机管理面板的登录密码(留空随机生成)</div>
</div>
                `;

            mdui.dialog({
                title: '创建新主机[' + Data.data.name + ']', content: content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }, {
                    text: '确认创建', onClick: function () {
                        let Form = {
                            'server': Data.id,
                            'day': $("#day").val(),
                            'uid': $("#uid").val(),
                            'RenewPrice': $("#money").val(),
                            'maxdomain': $("#maxdomain").val(),
                            'concurrencyall': $("#concurrencyall").val(),
                            'concurrencyip': $("#concurrencyip").val(),
                            'traffic': $("#traffic").val(),
                            'filesize': $("#filesize").val(),
                            'sizespace': $("#sizespace").val(),
                            'username': $("#username").val(),
                            'password': $("#password").val(),
                        };

                        if (Form.RenewPrice === "" || Form.day === "") {
                            mdui.dialog({
                                title: '警告', content: '续费金额和主机有效天数必须填写！', modal: true, history: false, buttons: [{
                                    text: '关闭',
                                }]
                            });
                            return false;
                        }

                        let is = layer.msg('正在创建中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=CreateHostSpace',
                            data: Form,
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.Data[index].count += 1;
                                            layer.closeAll();
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
        }, /**
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
                if (num === 0) return 0;
                let str = num.toString();
                if (str.indexOf('.') !== -1) {
                    return str.split('.')[1].length;
                }
                return 0;
            }
        }, ReMemoryGet(index) {
            let Data = App.Data[index];
            layer.open({
                title: '内存释放',
                icon: 2,
                content: '<font color=red>若您的站点处于有大量访问的状态，释放内存可能带来无法预测的后果，您确定现在就释放服务器【' + Data.name + '】的内存吗？</font>',
                btn: ['确定', '取消'],
                btn1: function () {
                    let is = layer.msg('释放中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './main.php?act=ReMemory', data: {
                            id: Data.id,
                        }, dataType: "json", success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                let count = App.RealTimeData[index]['Memory']['Occupy'] - res.data.memRealUsed + 'MB';
                                layer.alert(res.msg + '<br>本次成功释放了' + count + '内存！', {
                                    icon: 1, btn1: function () {
                                        App.RealTimeData[index]['Memory']['Occupy'] = res.data.memRealUsed;
                                        layer.closeAll();
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
                }
            })
        }, StateMonitoring(index) {
            let Data = App.Data[index];
            $.ajax({
                type: "POST", url: './main.php?act=ServerStatusMonitoring', data: {
                    id: Data.id,
                }, dataType: "json", success: function (res) {
                    if (res.code == 1) {
                        App.ErrorData[index] = false;
                        App.Data[index]['data'] = res.data;
                        App.RealTimeData[index] = res.RealTime;
                        App.DynamicRendering(index);
                    } else {
                        App.ErrorData[index] = res.msg;
                    }

                    if (App.Data[index + 1] !== undefined) {
                        if (App.RealTimeData[index + 1] === false) {
                            App.StateMonitoring(index + 1);
                        } else {
                            setTimeout(function () {
                                App.StateMonitoring(index + 1);
                            }, 1000 * App.time);
                        }
                    } else {
                        setTimeout(function () {
                            App.StateMonitoring(0);
                        }, 1000 * App.time);
                    }
                },
            });
        }, DynamicRendering(index) {
            //内存进度条百分比配置;
            App.RealTimeData[index]['Memory']['Percentage'] = ((App.RealTimeData[index]['Memory']['Occupy'] / App.RealTimeData[index]['Memory']['Total']) * 100).toFixed(2);
            //硬盘空间百分比配置
            App.RealTimeData[index]['Disk']['Percentage'] = ((App.RealTimeData[index]['Disk']['Occupy'] / App.RealTimeData[index]['Disk']['Total']) * 100).toFixed(2);
            //服务器负载状态
            App.RealTimeData[index]['Load']['Percentage'] = ((App.RealTimeData[index]['Load']['Occupy'] / App.RealTimeData[index]['Load']['Total']) * 100).toFixed(2);
        }, ColorConfiguration(p = 0) {
            //动态效果增强
            if (p > 0 && p <= 10) {
                return ' mdui-color-green-600';
            } else if (p > 10 && p <= 20) {
                return ' mdui-color-green-700';
            } else if (p > 20 && p <= 30) {
                return ' mdui-color-yellow-a700';
            } else if (p > 30 && p <= 40) {
                return ' mdui-color-yellow-600';
            } else if (p > 40 && p <= 50) {
                return ' mdui-color-yellow-700';
            } else if (p > 50 && p <= 60) {
                return ' mdui-color-yellow-800';
            } else if (p > 70 && p <= 70) {
                return ' mdui-color-deep-orange-600';
            } else if (p > 70 && p <= 80) {
                return ' mdui-color-deep-orange-700';
            } else if (p > 80 && p <= 90) {
                return ' mdui-color-deep-orange-800';
            } else if (p > 90 && p <= 100) {
                return ' mdui-color-deep-orange-900';
            }
            return ' mdui-color-red-900';
        }, ServerList() {
            let is = layer.msg('可用服务器列表载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=ServerList', data: {
                    name: App.name,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.count = res.data.length;
                        App.type = 1;
                        if (App.count >= 1) {
                            for (var i = 0; i < App.count; i++) {
                                App.ErrorData[i] = false;
                                App.RealTimeData[i] = false;
                            }
                            App.StateMonitoring(0);
                        }
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
            mdui.prompt('可输入：服务器名称，IP', '搜索服务器', function (str) {
                App.initialization(str);
            }, function () {

            }, {
                type: 'textarea', maxlength: 999999999, defaultValue: App.name, confirmText: '确认搜索', cancelText: '取消',
            });
        }, initialization(name = '') {
            if (name == -2) {
                this.name = '';
            } else {
                this.name = (name === '' ? this.name : name);
            }
            App.ServerList();
        }
    }
}).mount('#App');

App.initialization();