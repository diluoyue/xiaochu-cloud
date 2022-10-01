const App = Vue.createApp({
    data() {
        return {
            Data: [], type: -1,
        }
    }, methods: {
        ClassPaySet(cid, index) {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=ClassPaySet', data: {
                    cid: cid, key: index,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.ClassList();
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, ClassDelete(cid, name) {
            layer.open({
                title: '危险操作',
                content: '是否要永久删除此分类?,删除后,分类里面的商品将不会在首页显示，除非将商品移动到新的分类!',
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './main.php?act=ClassDelete', data: {
                            cid: cid, name: name
                        }, dataType: "json", success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.ClassList();
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
        }, ClassStateSet(cid, type, name) {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=ClassStateSet', data: {
                    cid: cid, type: type, name: name
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.ClassList();
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, sort(cid, type) {
            $.ajax({
                type: 'POST', url: 'ajax.php?act=setClassSort', data: {
                    cid: cid, type: type
                }, dataType: 'json', success: function () {
                    App.ClassList();
                }, error: function () {
                    layer.msg('服务器错误');
                }
            });
        }, ClassList() {
            let is = layer.msg('分类载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=ClassList', data: {
                    type: 2
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    App.type = 1;
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.type = 1;
                    } else {
                        App.Data = [];
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }
    }
}).mount('#App');
App.ClassList();