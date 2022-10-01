const App = Vue.createApp({
    data() {
        return {
            Data: [],
            page: 1,
            limit: 10,
            name: '',
            count: 0,
            type: -1,
            sid: $("#App").attr('sid'),
        }
    }
    , methods: {
        LogHostBackground(key) {
            layer.open({
                title: '温馨提示',
                content: '是否要登陆此空间的管理后台？',
                icon: 3,
                btn: ['确定登陆', '取消'],
                btn1: function () {
                    let is = layer.msg('登陆数据写入中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: './ajax.php?act=LogHostBackground',
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
        },
        HostList() {
            let is = layer.msg('载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST",
                url: './ajax.php?act=HostList',
                data: {
                    page: App.page,
                    limit: App.limit,
                },
                dataType: "json",
                success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.type = 1;
                    } else {
                        App.Data = [];
                        App.type = 1;
                        layer.msg(res.msg, {
                            icon: 2
                        });
                    }
                },
                error: function () {
                    layer.msg('服务器异常！');
                }
            });
        },
        initialization(name = '', limit = -1) {
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
                    type: "POST",
                    url: './ajax.php?act=HostCount',
                    dataType: "json",
                    success: function (res) {
                        if (res.code == 1) {
                            App.count = res.count;
                            laypage.render({
                                elem: 'Page'
                                , count: res.count
                                , theme: '#641ec6'
                                , limit: App.limit
                                , limits: [1, 10, 20, 30, 50, 100, 200]
                                , groups: 3
                                , first: '首页'
                                , last: '尾页'
                                , prev: '上一页'
                                , next: '下一页'
                                , skip: true
                                , layout: ['count', 'page', 'prev', 'next', 'limit', 'limits']
                                , jump: function (obj) {
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
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            });
        }
    }
}).mount('#App');

App.initialization();