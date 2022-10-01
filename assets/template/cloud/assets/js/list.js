const App = Vue.createApp({
    data() {
        return {
            ClassData: [],
            GoodsData: [],
            CouponData: {
                all: [],
            }, //优惠券列表
            UserState: -1, //登录状态
            type: 'all',
        }
    }
    , mounted() {
        this.ClassList();
    }
    , methods: {
        CouponAdd(token) {
            let _this = this;
            if (this.CouponData[this.type].length == 0) return false;
            if (this.UserState == -1) {
                layer.open({
                    title: '温馨提示',
                    content: '领取优惠券需要先登录哦！',
                    btn: ['登录', '取消'],
                    btn1: function () {
                        window.open('./?mod=route&p=User');
                    }
                })
            } else {
                layer.msg("正在领取中...", {
                    icon: 16,
                    time: 9999999,
                    shade: [0.2, '#393D49'],
                });
                $.ajax({
                    type: "post",
                    url: "./main.php?act=CouponGet",
                    data: {
                        token: token,
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.closeAll();
                        if (data.code >= 1) {
                            _this.CouponList();
                            layer.msg(data.msg, {icon: 1});
                        } else if (data.code == -2) {
                            _this.CouponList();
                            layer.open({
                                title: '温馨提示',
                                content: '领取优惠券需要先登录哦！',
                                btn: ['登录', '取消'],
                                btn1: function () {
                                    window.open('./?mod=route&p=User');
                                }
                            })
                        } else layer.msg(data.msg, {icon: 2});
                    }
                });
            }
        },
        Coupon(type) {
            this.type = type;
            if (App.CouponData[type].length == 0) return false;
            let _this = this;
            let content = '<div class="CouponCss">';
            for (const item of this.CouponData[type]) {
                content += `
				<div class="layui-card" onclick="App.CouponAdd('` + item.limit_token + `')">
                    <div class="layui-card-header" ` + (item.type == 1 ? 'style="color:#fff;background: linear-gradient(to right, #36d1dc, #5b86e5);"' : 'style="display: none;"') + ` >
                        ` + item.name + `
                    </div>
                    <div class="layui-card-header" ` + (item.type == 2 ? 'style="color:#fff;background: linear-gradient(to right, #ff416c, #ff4b2b);"' : 'style="display: none;"') + ` >
                        ` + item.name + `
                    </div>
                    <div class="layui-card-header" ` + (item.type == 3 ? 'style="color:#fff;background: linear-gradient(to right, #f7971e, #ffd200);"' : 'style="display: none;"') + ` >
                        ` + item.name + `
                    </div>
                    <div class="layui-card-body">
                        <img ` + (item.type == 1 ? 'style="border-radius:0"' : 'style="display: none;"') + `  src="assets/img/coupon_1.png"  />
                        <img ` + (item.type == 2 ? 'style="border-radius:0"' : 'style="display: none;"') + `  src="assets/img/coupon_2.png"  />
                        <img ` + (item.type == 3 ? 'style="border-radius:0"' : 'style="display: none;"') + `  src="assets/img/coupon_3.png"  />
                        <ul>
                            <li ` + (item.type == 1 ? 'style="color:red;font-size:1.2em;"' : 'style="display: none;"') + ` >
                                满` + item.minimum + `元可使用此券抵扣` + item.money + `元
                            </li>
                            <li ` + (item.type == 2 ? 'style="color:red;font-size:1.2em;"' : 'style="display: none;"') + ` >
                                无门槛,下单即可抵扣` + item.money + `元
                            </li>
                            <li ` + (item.type == 3 ? 'style="color:red;font-size:1.2em;"' : 'style="display: none;"') + ` >
                                满` + item.minimum + `元可使用此券获得` + (item.money / 10) + `折优惠
							</li>
							<li style="color:#666">` + item.scope + `</li>
                            <li>` + item.content + `</li>
                            <li ` + (item.count >= 1 ? '' : 'style="display: none;"') + ` >持有` + item.count + `张</li>
                        </ul>
                        <button type="button" class="layui-btn layui-btn-sm layui-btn-warm layui-btn-radius"
                            style="margin:auto;display: block;box-shadow: 3px 3px 18px #ccc;">` + (item.count >= 1 ? '再领一张' : '领取') + `</button>
                    </div>
                </div>
			`;
            }

            layer.open({
                title: '优惠券列表',
                content: content + '</div>',
                btn: ['关闭'],
                shade: [0.8, '#000'],
                shadeClose: true,
                area: ['350px', (_this.CouponData[type].length >= 3 ? '90%' : 'auto')]
            });
        },
        CouponList(cid = false) {
            let _this = this;
            $.ajax({
                type: "post",
                url: "./main.php?act=CouponList",
                data: {
                    cid: cid,
                    type: (cid == false ? 3 : 2),
                },
                dataType: "json",
                success: function (data) {
                    if (data.code >= 1) {
                        if (cid === false) {
                            _this.CouponData['all'] = data.data;
                        } else {
                            _this.CouponData[cid] = data.data;
                        }
                        _this.UserState = data.type;
                    }
                }, error: function () {
                    if (cid === false) {
                        _this.CouponData['all'] = [];
                    } else {
                        _this.CouponData[cid] = [];
                    }
                },
            });
        },
        ShareGoods(gid) {
            if (gid <= 0) return;
            layer.msg("正在生成分享链接中...", {
                icon: 16,
                time: 9999999,
            });
            $.ajax({
                type: "post",
                url: "./main.php?act=SharePoster",
                data: {
                    gid: gid,
                },
                dataType: "json",
                success: function (data) {
                    if (data.code == 1) {
                        layer.alert('<img src="' + data.src + '" width=300 heigth=450 />', {
                            area: ["340px", "490px"],
                            title: false,
                            btn: false,
                            shade: [0.8, "#000"],
                            shadeClose: true,
                        });
                    } else {
                        layer.msg(data.msg, {
                            icon: 2,
                        });
                    }
                },
                error: function () {
                    layer.alert("生成失败！");
                },
            });
        },
        ClassList() {
            let _this = this;
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST",
                url: './main.php?act=class',
                data: {
                    num: 9999
                },
                dataType: "json",
                success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.ClassData = res.data;
                        $.each(_this.ClassData, function (key, val) {
                            _this.GoodsData[val.cid] = [];
                            _this.CouponData[val.cid] = [];
                        });
                        App.CouponList();
                        App.GetGoods(res.data[0].cid, 0);
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
        },
        GetGoods(cid, index) {
            let _this = this;
            $.ajax({
                type: 'post',
                url: './main.php?act=GoodsList',
                data: {
                    cid: cid,
                    page: -1
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        App.CouponList(cid);
                        if (data.data.length != 0) {
                            _this.GoodsData[cid].push.apply(_this.GoodsData[cid], data.data);
                        }
                        if (_this.ClassData[index + 1] != undefined) {
                            _this.GetGoods(_this.ClassData[index + 1].cid, index + 1);
                        }
                    } else {
                        layer.msg(data.msg, {
                            icon: 2
                        });
                    }
                },
                error: function () {
                    layer.alert('加载失败！');
                }
            });
        }
    }
}).mount('#App');

layui.use('util', function () {
    var util = layui.util;
    util.fixbar({
        bar1: false
    });
});