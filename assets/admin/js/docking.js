const App = Vue.createApp({
    data() {
        return {
            Data: [], page: 1, limit: 10, count: 0, type: -1,
        }
    }, methods: {
        Tips(data) {
            console.log(data);

            let content = `
                <pre class="layui-code" lay-title="请求URL地址">` + data.url + `</pre>
                <pre class="layui-code" lay-title="POST请求数据">` + JSON.stringify(data.post, null, "\t") + `</pre>
                <pre class="layui-code" lay-title="对接返回数据">` + JSON.stringify(data.data, null, "\t") + `</pre>
                `;

            mdui.dialog({
                title: '日期：' + data.date, content: content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }], onOpen: function () {
                    layui.use('code', function () { //加载code模块
                        layui.code({
                            about: false, skin: 'notepad',
                        });
                    });
                }
            });
        }, GetList() {
            let is = layer.msg('日志载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=DockingList', data: {
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
                    type: "POST", url: './main.php?act=DockingCount', dataType: "json", success: function (res) {
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
                                    App.GetList();
                                }
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