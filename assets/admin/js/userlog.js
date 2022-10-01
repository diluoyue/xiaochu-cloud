const App = Vue.createApp({
    data() {
        return {
            Data: [], page: 1, limit: 10, name: '', count: 0, type: -1, uid: $("#App").attr('uid'), ColorArr: [],
        }
    }, methods: {
        DeleteUserLog(id) {
            mdui.dialog({
                title: '温馨提示', content: '是否要删除ID为[' + id + ']的日志？,删除后不可恢复！', modal: true, history: false, buttons: [{
                    text: '取消',
                }, {
                    text: '确定删除', onClick: function () {
                        let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: 'main.php?act=DeleteUserLog', data: {
                                id: id
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
                }]
            });
        }, colorById(i) {
            i = i.charCodeAt(0);
            const key = i;
            if (this.ColorArr['co_' + key] !== undefined) {
                return this.ColorArr['co_' + key];
            }
            if (i < 10) i = i * 92.5;
            if (i < 100) i = i * 35.2;
            for (; i > 255; i *= 0.98) ;
            var temp = i.toString().substring(i.toString().length - 3);
            i += parseInt(temp);
            for (; i > 255; i -= 255) ;
            i = parseInt(i);
            if (i < 10) i += 10;

            var R = i * (i / 100);
            for (; R > 255; R -= 255) ;
            if (R < 50) R += 60;
            R = parseInt(R).toString(16);

            var G = i * (i % 100);
            for (; G > 255; G -= 255) ;
            if (G < 50) G += 60;
            G = parseInt(G).toString(16);

            var B = i * (i % 10);
            for (; B > 255; B -= 255) ;
            if (B < 50) B += 60;
            B = parseInt(B).toString(16);
            this.ColorArr['co_' + key] = "#" + R + G + B;
            return this.ColorArr['co_' + key];
        }, /**
         * charCodeAt()
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
        }, UserLogList() {
            let is = layer.msg('日志列表载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=UserLogList', data: {
                    page: App.page, limit: App.limit, name: App.name, uid: App.uid,
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
                    type: "POST", url: './main.php?act=UserLogCount', data: {
                        name: App.name, uid: App.uid
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
                                    App.UserLogList();
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