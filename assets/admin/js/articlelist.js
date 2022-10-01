const App = Vue.createApp({
    data() {
        return {
            Data: [], page: 1, limit: 10, count: 0, type: -1,
        }
    }, methods: {
        /**
         *
         * @param id
         * @param type
         * @param name
         * @constructor
         * 调整公告状态
         */
        NoticeStateSet(id, type, name) {
            let is = layer.msg('调整中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=NoticeStateSet', data: {
                    id: id, type: type, name: name
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.initialization();
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, Preview(data) {
            mdui.dialog({
                title: data.title, content: data.content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }]
            });
        }, Delete(data) {
            layer.open({
                title: '危险操作', content: '是否要永久删除此公告通知？,删除后不可恢复！', icon: 3, btn: ['确定删除', '取消'], btn1: function () {
                    let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './main.php?act=NoticeDelete', data: {
                            id: data.id, name: data.title
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
        }, NoticeList() {
            let is = layer.msg('公告列表载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=NoticeList', data: {
                    page: App.page, limit: App.limit,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.type = 1;
                    } else {
                        App.Data = [];
                        App.type = 1;
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, initialization(limit = -1) {
            this.page = 1;
            this.limit = (limit === -1 ? this.limit : limit);
            this.type = -1;
            layui.use('laypage', function () {
                var laypage = layui.laypage;
                $.ajax({
                    type: "POST", url: './main.php?act=NoticeCount', data: {
                        name: App.name,
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
                                    App.NoticeList();
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