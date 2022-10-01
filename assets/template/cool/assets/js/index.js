const vm = Vue.createApp({
    data() {
        return {
            cid: cid,
            GoodsData: [], //商品
            CouponData: [], //优惠券列表
            UserState: -1, //登录状态
            GoodsDataClass: [], //分类参数
            SortingType: 1,
            Sorted: 1,
            state: 2, //显示状态
            name: '',

            ActivitiesGoods: [], //秒杀活动商品
            Prices: [],
        };
    }
    , watch:
        {
            "state":
                {
                    handler(value, tv) {
                        layui.data('data', {
                            key: 'state'
                            , value: value
                        });
                    }
                }
        },
    mounted() {
        var localTest = layui.data('data');
        if (localTest.state !== undefined) {
            this.state = localTest.state;
        }
        this.GetGoods(this.cid);

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
            let _this = this;
            $.ajax({
                type: "POST",
                url: './main.php?act=ActivitiesGoods',
                dataType: "json",
                success: function (res) {
                    if (res.code == 1) {
                        _this.ActivitiesGoods = res.data;
                    } else {
                        _this.ActivitiesGoods = [];
                    }
                },
                error: function () {
                    layer.msg('服务器异常！');
                }
            });
        },

        Search(msg) {
            this.name = msg;
            layer.msg('商品搜索中...', {icon: 16, time: 999999});
            this.GetGoods(this.cid);
        },
        SortSet(SortingType) {
            this.SortingType = SortingType;
            if (this.SortingType == SortingType) {
                if (this.Sorted == 1) {
                    this.Sorted = 2;
                } else {
                    this.Sorted = 1;
                }
            }

            layer.msg('加载中...', {icon: 16, time: 999999});
            this.GetGoods(this.cid);
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
                type: 0,
                title: (_this.type == 1 ? '商品(' + _this.gid + ')优惠券列表' : (_this.type == 2 ? '分类(' + _this.cid + ')优惠券列表' : '惊喜优惠券列表')),
                content: content + '</div>',
                btn: ['关闭'],
                shade: [0.8, '#000'],
                skin: 'layui-layer-rim',
                shadeClose: true,
                area: ['350px', (_this.CouponData.length >= 3 ? '90%' : 'auto')]
            });
        },
        CouponList(type = 0) {
            if (type == 0) {
                type = this.type;
            } else this.type = type;
            let _this = this;
            $.ajax({
                type: "post",
                url: "./main.php?act=CouponList",
                data: {
                    cid: _this.cid,
                    type: type,
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
        GetGoods(cid) {
            let _this = this;
            if (cid == -1) {
                _this.CouponData = [];
                _this.CouponList(3);
            }
            $.ajax({
                type: 'post',
                url: './main.php?act=GoodsList',
                data: {
                    cid: cid,
                    page: -1,
                    SortingType: _this.SortingType,
                    Sorted: _this.Sorted,
                    name: _this.name,
                },
                dataType: 'json',
                success: function (data) {
                    layer.closeAll();
                    if (data.code == 1) {
                        if (cid != -1 && data.data.length == 0) {
                            layer.alert('当前分类无任何商品', {
                                icon: 2, btn: '返回', end: function (layero, index) {
                                    location.href = './';
                                }
                            });
                            return;
                        } else if (cid != -1) {
                            _this.CouponData = [];
                            _this.CouponList(2);
                        }
                        _this.GoodsData = data.data;
                        _this.GoodsDataClass = data.CidArr;
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
            return price + `/` + data.quantity + data.units;
        },
    }
}).mount('#GoodsApp');