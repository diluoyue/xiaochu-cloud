const App = Vue.createApp({
    data() {
        return {
            Data: [],
            page: 1,
            limit: 10,
            name: '',
            count: 0,
            AppType: 0,
            SortType: 0,
            SortTypeList: [],
            type: -1,
            TypeList: [{
                name: '全部'
            }, {
                name: '插件'
            }, {
                name: '模板'
            }, {
                name: '已安装'
            }],
        }
    }, methods: {
        UpdateSoftwareList(type = 1) {
            if (type == 1) {
                layer.msg('正在更新中，请稍后...', {icon: 16, time: 9999999});
            }
            $.ajax({
                type: "POST", url: './main.php?act=ApplyingUpdate', dataType: "json", success: function (res) {
                    layer.closeAll();
                    if (res.code == 1) {
                        if (type == 1) {
                            layer.alert(res.msg, {
                                icon: 1, btn1: function () {
                                    App.initialization();
                                }
                            });
                        } else {
                            App.initialization();
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
        }, SearchApp() {
            mdui.prompt('可数量应用名称关键字进行搜索！', '搜索应用', function (str) {
                App.name = str;
                App.initialization();
            }, function () {

            }, {
                type: 'textarea', maxlength: 999999999, defaultValue: App.name, confirmText: '确认搜索', cancelText: '取消',
            });
        }, Cut(index, type) {
            if (type === 1) {
                App.AppType = index;
                App.SortType = 0;
            } else {
                App.SortType = index;
            }
            App.initialization();
        }, Tips(data) {
            mdui.dialog({
                title: data.title, content: data.content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }, {
                    text: '查看详细使用说明书', onClick: function () {
                        App.iframeOn(data.identification, data.title);
                    }
                }]
            });
        }, iframeOn(identification, title) {
            if (document.body.clientWidth > 750) {
                area = ['90%', '90%'];
            } else area = ['96%', '96%'];
            layer.open({
                type: 2,
                shade: false,
                title: '[ ' + title + ' ] 使用说明',
                area: area,
                maxmin: true,
                content: './ajax.php?act=app_help&id=' + identification,
                zIndex: layer.zIndex,
                success: function (layero) {
                    layer.setTop(layero);
                }
            });
        }, install(identification, name) {
            layer.open({
                title: '操作确认', content: '是否要安装' + name + '？', btn: ['安装', '取消'], icon: 3, btn1: function () {
                    let is = layer.msg('安装中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './ajax.php?act=app_install', data: {
                            type: 1, identification: identification
                        }, dataType: "json", success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.ApplyList();
                                    }
                                });
                            } else {
                                layer.alert(res.msg, {
                                    icon: 2
                                });
                            }
                        }, error: function () {
                            layer.alert('应用安装失败，返回异常，请检查您的服务器是否支持：ZipArchive操作类！<br>或尝试切换PHP版本来解决此问题！', {icon: 2});
                        }
                    });
                }
            });
        }, unload(identification, name) {
            layer.open({
                title: '操作确认',
                content: '是否要卸载' + name + '？<br>卸载后将无法使用此应用,若要使用需要再次安装!',
                btn: ['确认', '取消'],
                icon: 3,
                btn1: function () {
                    let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './ajax.php?act=app_unload', data: {
                            identification: identification,
                        }, dataType: "json", success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.initialization('', -1, 2);
                                    }
                                });
                            } else {
                                layer.alert(res.msg, {
                                    icon: 2
                                });
                            }
                        }, error: function () {
                            layer.msg('卸载失败，请重新尝试！');
                        }
                    });
                }
            });
        }, Update(identification, versions, update_instructions) {
            mdui.dialog({
                title: 'v' + versions + ' 更新日志', content: update_instructions, modal: true, history: false, buttons: [{
                    text: '取消',
                }, {
                    text: '确定升级', onClick: function () {
                        let is = layer.msg('正在更新中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: './ajax.php?act=app_install', data: {
                                identification: identification, type: 2
                            }, dataType: "json", success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.ApplyList();
                                        }
                                    });
                                } else {
                                    layer.alert(res.msg, {
                                        icon: 2
                                    });
                                }
                            }, error: function () {
                                layer.msg('更新失败，请重新尝试！');
                            }
                        });
                    }
                }]
            });
        }, prolong(id, price, name, discounts = 100) {
            if (discounts != 100) {
                price = ((price - 0) * (discounts / 100)).toFixed(2);
            }
            mdui.dialog({
                title: '应用[' + name + ']续期提醒',
                content: '是否要为应用：' + name + '，续期？此应用价格为每月<font color=red>' + price + '元</font>,最多可一次续期24个月！<br>续期成功后会扣除对应的服务端余额，请确保服务端余额充足！',
                modal: true,
                history: false,
                buttons: [{
                    text: '取消',
                }, {
                    text: '确认续期', onClick: function () {
                        layer.prompt({
                            formType: 3, value: 1, title: '请填写续期月数，最多24个月！',
                        }, function (value, index) {
                            if (value < 1 || value > 24) {
                                layer.alert('每次最低续期1个月，最多续期24个月！', {
                                    icon: 2
                                });
                                return false;
                            }
                            layer.close(index);
                            let is = layer.msg('正在续期中，请稍后...', {icon: 16, time: 9999999});
                            $.ajax({
                                type: "POST", url: './ajax.php?act=app_prolong', data: {
                                    id: id, value: value,
                                }, dataType: "json", success: function (res) {
                                    layer.close(is);
                                    if (res.code == 1) {
                                        layer.alert(res.msg, {
                                            icon: 1, btn1: function () {
                                                App.ApplyList();
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
                }]
            });
        }, Grade() {
            let is = layer.msg('用户数据获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './ajax.php?act=app_users', data: {}, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code >= 0) {
                        let content = `
<div class="mdui-table-fluid">
  <table class="mdui-table">
    <thead>
      <tr>
        <th>名称</th>
        <th>内容</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>账户余额</td>
        <td>` + App.NumRound(res.money) + `元</td>
      </tr>
      <tr>
        <td>账户等级</td>
        <td>` + res.grade + `级</td>
      </tr>
       <tr>
        <td>升级还需</td>
        <td>` + res.demand + `成长值</td>
      </tr>
      <tr>
        <td>总成长值</td>
        <td>` + res.present + `点</td>
      </tr>
    </tbody>
   </table>
   <hr>
   <center>
    <a href="./admin.goods.supply.php">点击充值余额【我的商城->在线充值】</a>
   </center>
   <br>
</div>
                            `;
                        mdui.dialog({
                            title: '我的账户详情', content: content, modal: true, history: false, buttons: [{
                                text: '关闭窗口',
                            }]
                        });
                    } else layer.msg(res.msg);
                }, error: function () {
                    layer.msg('获取失败，请重新尝试！');
                }
            });
        }, pay(id, price, name, discounts = 100) {
            if (discounts != 100) {
                price = ((price - 0) * (discounts / 100)).toFixed(2);
            }
            mdui.dialog({
                title: '应用[' + name + ']购买提醒',
                content: '是否要购买应用：' + name + '，此应用价格为每月<font color=red>' + price + '元</font>,最多可一次购买24个月！<br>购买后会扣除对应的服务端余额，请确保服务端余额充足！',
                modal: true,
                history: false,
                buttons: [{
                    text: '取消',
                }, {
                    text: '确认购买', onClick: function () {
                        layer.prompt({
                            formType: 3, value: 1, title: '请填写购买月数，最多24个月！',
                        }, function (value, index) {
                            if (value < 1 || value > 24) {
                                layer.alert('每次最低购买1个月，最多购买24个月！', {
                                    icon: 2
                                });
                                return false;
                            }
                            layer.close(index);
                            let is = layer.msg('正在购买中，请稍后...', {icon: 16, time: 9999999});
                            $.ajax({
                                type: "POST", url: './ajax.php?act=app_pay', data: {
                                    id: id, value: value,
                                }, dataType: "json", success: function (res) {
                                    layer.close(is);
                                    if (res.code == 1) {
                                        layer.alert(res.msg, {
                                            icon: 1, btn1: function () {
                                                App.ApplyList();
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
                }]
            });
        }, state(id, type) {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './ajax.php?act=app_state_set', data: {
                    id: id, type: type,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.ApplyList();
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, Hovers(id, type) {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: 'post', url: './ajax.php', data: {
                    act: type, id: id
                }, dataType: 'json', success: function (data) {
                    layer.close(is);
                    if (data.code >= 0) {
                        App.ApplyList();
                        Hover.AjaxList();
                        Hover.state = true;
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                }, error: function () {
                    layer.alert('操作失败！');
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
        }, ApplyList(type = 1) {
            let is = layer.msg('应用列表载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './ajax.php?act=ApplyList', data: {
                    page: App.page,
                    limit: App.limit,
                    Search: App.name,
                    type: App.AppType,
                    SortType: App.SortType,
                    cache: 1,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        if (App.SortTypeList.length === 0) {
                            App.SortTypeList = res.Tags;
                        }
                        App.Data = res.data;
                        App.type = 1;
                    } else {
                        App.Data = [];
                        App.type = 1;
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                    App.$forceUpdate();
                }, error: function () {
                    layer.open({
                        title: '温馨提示', content: '服务器返回异常，是否需要重新获取列表数据？', btn: ['确定', '取消'], btn1: function () {
                            App.initialization('', -1, 2);
                        }
                    });
                }
            });
        }, initialization(name = '', limit = -1, type = 1) {
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
                    type: "POST", url: './ajax.php?act=ApplyCount', data: {
                        Search: App.name, type: App.AppType, SortType: App.SortType, cache: type,
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
                                    App.ApplyList(type);
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

layui.use('upload', function () {
    layui.upload.render({
        elem: '#Uploading', url: './ajax.php?act=AppFlieInstAll', exts: 'zip', accept: 'file', done: function (res) {
            layer.alert(res.msg, {
                title: '温馨提示', icon: 1, yes: function () {
                    App.initialization();
                }
            });
        }, error: function (res) {
            layer.alert(res.msg, {
                title: '安装失败', icon: 2,
            });
        }
    });
});
