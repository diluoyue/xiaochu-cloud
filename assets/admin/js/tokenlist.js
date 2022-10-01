const App = Vue.createApp({
    data() {
        return {
            Data: [], gid: gid, page: 1, limit: 20, type: -1, name: '',
        }
    }, methods: {
        TokenDe(kid) {
            layer.alert('删除后不可撤销，是否确认删除？', {
                icon: 3, title: '温馨提示', btn: ['取消', '确定删除'], btn2: function () {
                    let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './main.php?act=TokenDe', data: {
                            kid: kid
                        }, dataType: "json", success: function (res) {
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
                        }, error: function () {
                            layer.msg('服务器异常！');
                        }
                    });
                }
            });
        }, GetList() {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=TokenList', data: {
                    page: App.page, limit: App.limit, gid: App.gid, name: App.name,
                }, dataType: "json", success: function (res) {
                    App.type = 1;
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, initialization(name = '', limit = 10) {
            this.page = 1;
            this.limit = limit;
            this.name = name;
            this.type = -1;
            layui.use('laypage', function () {
                var laypage = layui.laypage;
                $.ajax({
                    type: "POST", url: './main.php?act=TokenSum', data: {
                        gid: App.gid, name: name,
                    }, dataType: "json", success: function (res) {
                        if (res.code == 1) {
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
                                    App.GetList();
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

layui.use('form', function () {
    var form = layui.form;
    form.on('select(kmgid)', function (data) {
        if (data.value != '') {
            App.gid = data.value;
            history.replaceState({}, null, '?gid=' + data.value);
        } else {
            App.gid = '';
            history.replaceState({}, null, '?=1');
        }
        App.initialization();
    });
});
const kami = {
    query: function () {
        layer.prompt({
            formType: 3, value: '', title: '可输入卡密/订单号/提卡密码/KID/IP等',
        }, function (value) {
            layer.closeAll();
            App.initialization(value);
        });
    }, empty_use: function () {
        layer.open({
            title: '温馨提示',
            content: '是否要执行此操作,' + (App.gid === '' ? '一次清空整站已使用卡密库存' : '清空此商品已使用卡密库存') + '？',
            btn: ['确定清空', '取消'],
            icon: 3,
            btn1: function () {
                let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST", url: './ajax.php?act=kami_empty_use', data: {
                        gid: App.gid
                    }, dataType: "json", success: function (res) {
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
                    }, error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            }
        });
    }, derive() {
        layer.open({
            title: '请选择导出模式',
            content: '请选择导出模式，当前已经选择的是' + (App.gid === '' ? '全站商品卡密' : '指定商品卡密') + '？',
            btn: ['全部', '未使用', '已使用', '取消'],
            btn1: function () {
                location.href = './ajax.php?act=kami_derive&type=1&gid=' + App.gid;
                layer.closeAll();
            },
            btn2: function () {
                location.href = './ajax.php?act=kami_derive&type=2&gid=' + App.gid;
                layer.closeAll();
            },
            btn3: function () {
                location.href = './ajax.php?act=kami_derive&type=3&gid=' + App.gid;
                layer.closeAll();
            }
        });
    }, empty: function () {
        layer.open({
            title: '温馨提示',
            content: '是否要执行此操作,' + (App.gid === '' ? '一次清空整站全部卡密库存' : '清空此商品全部卡密库存') + '？',
            btn: ['确定清空', '取消'],
            icon: 3,
            btn1: function () {
                let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST", url: './ajax.php?act=kami_empty', data: {
                        gid: App.gid
                    }, dataType: "json", success: function (res) {
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
                    }, error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            }
        });
    }, add: function () { //添加
        if (App.gid === '') {
            layer.alert('请在下方选择需要添加库存的卡密商品!', {icon: 3});
            return false;
        }
        layer.prompt({
            formType: 2,
            value: '',
            maxlength: 99999999999,
            title: '为此商品添加卡密库存,<font color=red>一卡一行</font>！',
            area: ['800px', '350px']
        }, function (value) {
            var content = value.split('\n');
            layer.alert('是否添加这' + content.length + '个卡密？', {
                title: '添加确认', icon: 3, btn: ['确定添加', '取消添加！'], btn1: function () {
                    $.post('ajax.php?act=kami_add', {
                        gid: App.gid, kam_arr: content
                    }, function (res) {
                        layer.closeAll();
                        if (res.code == 1) {
                            layer.alert(res.msg, {
                                icon: 1, btn1: function () {
                                    App.initialization();
                                }
                            });
                        } else layer.alert(res.msg)
                    })
                }
            });
        });
    }
};