const vm = Vue.createApp({
    data() {
        return {
            cid: cid,
            Type: 1, //模式，1显示分类，2显示商品
            ClassData: [], //分类
            GoodsData: [], //商品
            InformData: {
                Navigation: []
            }, //公告
            ArticleList: {
                page: 1,
                data: [],
                state: true
            }, //文章
            Service: [], //客服
            CouponData: [], //优惠券列表
            UserState: -1, //登录状态

            ActivitiesGoods: [], //秒杀活动商品
            Prices: [],
        };
    },
    mounted() {
        if (cid != -1) {
            this.GetGoods(cid)
        } else this.GetClass();

        this.GetInform();

        this.ActivitiesGoodsGet();
    },
    methods: {
        MoneyS(data) {
            var price;
            price = this.PriceS(data)['price'];
            price -= price * (data.Seckill.depreciate / 100);
            return price.toFixed(data.accuracy) - 0;
        },
        PriceS(data) {
            var color;
            var price;
            var state;

            if (this.Prices[data.gid] !== undefined) {
                return this.Prices[data.gid];
            }

            if (data.method == 1) {
                if (data.price == 0 || data.points == 0) {
                    color = '#43A047;font-size: 90%;';
                    price = '免费领取';
                    state = 2;
                } else {
                    color = '#ff0000';
                    price = data.price;
                    state = 1;
                }
            } else if (data.method == 2) {
                if (data.price == 0) {
                    color = '#43A047;font-size: 90%;';
                    price = '免费领取';
                    state = 2;
                } else {
                    color = '#ff0000';
                    price = data.price;
                    state = 1;
                }
            } else if (data.method == 3) {
                if (data.points == 0) {
                    color = '#43A047;font-size: 90%;';
                    price = '免费领取';
                    state = 2;
                } else {
                    color = '#ff0000';
                    price = data.points;
                    state = 3;
                }
            }

            this.Prices[data.gid] = {
                color: color,
                price: price,
                state: state
            };

            return this.Prices[data.gid];
        },
        ActivitiesGoodsGet() {
            $.ajax({
                type: "POST",
                url: './main.php?act=ActivitiesGoods',
                dataType: "json",
                success: function (res) {
                    if (res.code == 1) {
                        vm.ActivitiesGoods = res.data;
                    } else {
                        vm.ActivitiesGoods = [];
                    }
                },
                error: function () {
                    layer.msg('服务器异常！');
                }
            });
        },
        CouponAdd(token) {
            let _this = this;
            if (this.CouponData.length == 0) return false;
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
        Coupon() {
            if (this.CouponData.length == 0) return false;
            let _this = this;
            let content = '<div class="CouponCss">';
            for (const item of this.CouponData) {
                content += `
				<div class="layui-card" onclick="vm.CouponAdd('` + item.limit_token + `')">
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
                area: ['350px', (_this.CouponData.length >= 3 ? '90%' : 'auto')]
            });
        },
        CouponList() {
            let _this = this;
            $.ajax({
                type: "post",
                url: "./main.php?act=CouponList",
                data: {
                    cid: this.cid,
                    type: (this.Type == 1 ? 3 : 2),
                },
                dataType: "json",
                success: function (data) {
                    if (data.code >= 1) {
                        _this.CouponData = data.data;
                        _this.UserState = data.type;
                    }
                }, error: function () {
                    _this.CouponData = [];
                },
            });
        },
        Price(data) { //价格显示计算
            var color;
            var price;

            if (data.method == 1) {
                if (data.price == 0 || data.points == 0) {
                    color = '#43A047';
                    price = '免费商品';
                } else {
                    color = '#E53935';
                    price = '￥' + (data.price) + '元';
                }
            } else if (data.method == 2) {
                if (data.price == 0) {
                    color = '#43A047';
                    price = '免费商品';
                } else {
                    color = '#E53935';
                    price = '￥' + (data.price) + '元';
                }
            } else if (data.method == 3) {
                if (data.points == 0) {
                    color = '#43A047';
                    price = '免费商品';
                } else {
                    color = '#E53935';
                    price = data.points + data.currency;
                }
            }
            return `售价:<font color=` + color + ` >` + price + `</font>`;
        },
        CutGoods(cid, index, type = 1) {

            if (type == 2) {
                this.GetGoods(cid);
            } else {
                if (this.ClassData[index].count == 0) {
                    layer.msg('该分类暂无商品！', {icon: 2});
                    return;
                }
                layer.msg("正在载入中...", {
                    icon: 16,
                    time: 9999999,
                });
                this.cid = cid;
                this.name = this.ClassData[index].name;
                var _this = this;
                layui.use('util', function () {
                    $("ul.layui-fixbar").css("display", "block")
                    var util = layui.util;
                    util.fixbar({
                        bar1: '&#xe603;'
                        , click: function (type) {
                            if (type === 'bar1') {
                                _this.CutClass();
                            }
                        }
                    });
                });
                this.CutGoods(cid, index, 2);
            }

        },
        CutClass() {
            if (this.ClassData.length == 0) this.GetClass();
            this.cid = -1;
            this.Type = 1;
            this.CouponData = [];
            this.CouponList();
            this.name = '载入中';
            this.GoodsData = [];
            $("ul.layui-fixbar").css("display", "none");
            history.replaceState({}, null, './');
        },
        GetInform() {
            let _this = this;
            $.ajax({
                type: 'post',
                url: './main.php?act=inform',
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        _this.InformData = data.data;
                        if (data.data.PopupNotice != '' && _this.cid == -1) {
                            layer.open({
                                offset: '100px',
                                closeBtn: 0,
                                shade: [0.8, '#393D49'],
                                shadeClose: true,
                                title: false,
                                content: data.data.PopupNotice,
                                btn: ['好的'],
                            });
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
        },
        GetClass() {
            let _this = this;
            $.ajax({
                type: 'post',
                url: './main.php?act=class&num=999999',
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        _this.ClassData = data.data;
                        _this.CouponList();
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
        },
        GetGoods(cid) {
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
                        _this.GoodsData = data.data;
                        layer.closeAll();
                        if (data.data.length == 0) {
                            _this.CutClass();
                            layer.alert('该分类下暂无商品', {title: data.CidArr.name});
                            return;
                        }
                        _this.Type = 2;
                        _this.CouponData = [];
                        _this.name = data.CidArr.name;
                        history.replaceState({}, null, '?cid=' + cid);
                        if (data.CidArr.content != '') {
                            layer.alert(data.CidArr.content, {title: data.CidArr.name + '-分类说明'});
                        }

                        _this.CouponList();

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
}).mount('#appid');