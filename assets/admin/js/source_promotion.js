const App = Vue.createApp({
    data() {
        return {
            List: [],
            Money: 0,
            Price: 600,
            SourceList: [],
            Form: {
                id: -1,
                name: '',
                url: '',
                content: '',
                class_name: 'jiuwu',
            },
        }
    }, methods: {
        ListGet() {
            layer.load(1, {time: 999999});
            $.ajax({
                type: "POST", url: './main.php?act=promotionList'
                , dataType: "json"
                , success: function (res) {
                    layer.closeAll();
                    if (res.code === 1) {
                        App.SourceList = res.SourceList;
                        App.List = res.data;
                        App.Money = res.money;
                        App.Price = res.Price;
                    } else {
                        App.List = [];
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        },
        Create() {
            if (this.Price > this.Money) {
                layer.open({
                    title: '警告',
                    icon: 2,
                    content: '当前站点绑定的账户余额不足，请先充值！',
                    btn: ['前往充值', '取消'],
                    yes: function () {
                        alert('即将打开供货大厅，进入后请点击「我的商城」，在下方找到在线充值按钮进行充值！')
                        location.href = './admin.goods.supply.php';
                    }
                });
                return;
            }
            layer.open({
                title: '操作确认',
                content: '确认创建此定向货源推广位吗，创建后如果需要修改内容，可以直接点击编辑按钮，无需额外扣费！，当前创建价格：<span style="color:red">' + this.Price + '元/月</span><hr>默认创建1月，如果需要更加靠前的排名，可以在创建成功后进行续期，到期时间越长，排名越靠前！',
                btn: ['确认创建', '取消'],
                icon: 3,
                yes: function () {
                    layer.load(2, {
                        time: 999999
                    });
                    $.ajax({
                        type: "post",
                        url: './main.php?act=promotionCreate',
                        data: App.Form,
                        dataType: "json",
                        success: function (data) {
                            layer.closeAll();
                            if (data.code == 1) {
                                layer.alert(data.msg, {
                                    icon: 1,
                                    end: function () {
                                        App.ListGet();
                                    }
                                });
                            } else layer.msg(data.msg)
                        }
                    })
                }
            });
        },
        Editing(data) {
            console.log(data)
            this.Form = {
                id: data.id,
                name: data.name,
                class_name: data.class_name,
                url: data.url,
                content: data.content
            }

            $('html, body').animate({scrollTop: 0}, 300);
        },
        EditingNew() {
            layer.open({
                title: '操作确认',
                content: '是否要保存编辑内容？',
                btn: ['确认保存', '取消'],
                icon: 3,
                yes: function () {
                    layer.load(2, {
                        time: 999999
                    });
                    $.ajax({
                        type: "post",
                        url: './main.php?act=promotionEditing',
                        data: App.Form,
                        dataType: "json",
                        success: function (data) {
                            layer.closeAll();
                            if (data.code == 1) {
                                layer.alert(data.msg, {
                                    icon: 1,
                                    end: function () {
                                        App.ListGet();
                                    }
                                });
                            } else layer.msg(data.msg)
                        }
                    })
                }
            });
        },
        Renew(data) {
            layer.prompt({
                formType: 3,
                value: 1,
                title: '想续期多少个月，' + this.Price + '元/月',
            }, function (value, index) {
                layer.load(1, {time: 999999});
                $.ajax({
                    type: "POST", url: './main.php?act=promotionRenew',
                    data: {
                        id: data.id,
                        count: value
                    }
                    , dataType: "json"
                    , success: function (res) {
                        layer.closeAll();
                        if (res.code === 1) {
                            layer.alert(res.msg, {
                                icon: 1,
                                end: function () {
                                    App.ListGet();
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
                layer.close(index);
            });
        }
    }
}).mount('#App');

App.ListGet();