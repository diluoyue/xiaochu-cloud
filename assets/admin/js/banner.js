const AppBanner = Vue.createApp({
    data() {
        return {
            List: [], type: 1, Inst: false,
        }
    }, methods: {
        Tips() {
            mdui.dialog({
                title: '相关说明',
                content: '为保障用户权益，本系统所有投放的广告全部都有押金抵押在平台，若广告投放方跑路，可向官方客服举报，核实后，会使用该广告投放方的押金进行赔付！并且下架广告！<hr>另：从押金数还可看出广告投放方的实力，进一步保障用户权益！',
                modal: true,
                history: false,
                buttons: [{
                    text: '关闭',
                }]
            });
        }, Open(index) {
            AppBanner.Inst[index].toggle('#' + index + '_cards');
        }, ListGet(type = 1) {
            this.type = type;
            $.ajax({
                type: "POST", url: 'main.php?act=BannerList', data: {
                    type: type,
                }, dataType: "json", success: function (res) {
                    if (res.code == 1) {
                        AppBanner.List = res.data;
                        if (AppBanner.Inst !== false) {
                            mdui.mutation()
                            return;
                        }
                        AppBanner.Inst = {};
                        for (const key in res.data) {
                            let arr = res.data[key];
                            AppBanner.Inst[arr.id] = new mdui.Panel('#' + arr.id + '_card');
                        }
                        mdui.mutation()
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, GiveThumbsUp(type, data, state = 1) { //点赞，踩
            let content = '';
            if (state === 1) {
                content = (type === 1 ? '是否要为广告 [ ' + data.name + ' ] 点赞？点赞后这个广告可以获得1-10点热度！' : '是否要踩一下广告 [ ' + data.name + ' ] ？，踩后这个广告会扣除1-10点热度！') + '<hr>广告的热度值越高，排名越靠前！';
            } else {
                content = (type === 1 ? '是否要取消对广告[ ' + data.name + ' ]的点赞？取消后该广告获得的热度会扣除！' : '是否要取消对广告[ ' + data.name + ' ]的踩？，取消后这个广告会恢复扣除的热度！') + '<hr>广告的热度值越高，排名越靠前！';
            }
            mdui.dialog({
                title: '温馨提示', content: content, modal: true, history: false, buttons: [{
                    text: '取消',
                }, {
                    text: '确定', onClick: function () {
                        let is = layer.msg('执行中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: 'main.php?act=BannerGiveThumbs', data: {
                                id: data.id, type: type, state: state,
                            }, dataType: "json", success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, end: function () {
                                            AppBanner.ListGet(AppBanner.type);
                                        }
                                    });
                                } else if (res.code == -2) {
                                    //取消赞或者踩
                                    AppBanner.GiveThumbsUp(res.type, data, 2);
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
        },
    }
}).mount('#AppBanner');