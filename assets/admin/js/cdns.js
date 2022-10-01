/**
 * 节点配置
 */

const App = Vue.createApp({
    data() {
        return {
            DataList: [], id: 1,
        }
    }, methods: {
        ApiList() {
            let is = layer.msg('节点列表获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=ApiList', dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.DataList = res.data;
                        App.Ping(0, 2);
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, ApiSelect(id) {
            let is = layer.msg('节点切换中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=ApiSet', data: {
                    id: id,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        layer.msg(res.msg, {
                            icon: 1, success: function () {
                                App.Ping(id);
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
        }, Ping(index, type = 1) {
            if (type === 1) {
                is = layer.msg('测速中，请稍后...', {icon: 16, time: 9999999});
            }
            $.ajax({
                type: "POST", url: 'main.php?act=ApiPing', data: {
                    id: index,
                }, dataType: "json", success: function (res) {
                    if (type === 1) {
                        layer.close(is);
                    }
                    App.ids = res.at - 1;
                    if (res.code == 1) {
                        App.DataList[index].ping = res.ms;
                    } else {
                        App.DataList[index].ping = -1;
                    }
                    if (type !== 1) {
                        if (App.DataList[index + 1] !== undefined) {
                            if (App.DataList[index + 1].ping === false) {
                                App.Ping(index + 1, 2);
                            } else {
                                setTimeout(function () {
                                    App.Ping(index + 1, 2);
                                }, 3000);
                            }
                        } else {
                            setTimeout(function () {
                                App.Ping(0, 2);
                            }, 3000);
                        }
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }
    }
}).mount('#App');

App.ApiList();