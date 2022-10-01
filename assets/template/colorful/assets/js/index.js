const AppIds = Vue.createApp({
    data() {
        return {
            cid: cid,
            Type: 1, //模式，1显示分类，2显示商品
            ClassData: [], //分类
            GoodsData: [], //商品
            name: '载入中',
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

        CouponAdd(token) {
            let _this = this;
            if (this.CouponData.length == 0) return false;
            if (this.UserState == -1) {
                mdui.snackbar({
                    message: '领取优惠券需要先登录哦',
                    buttonText: '登录',
                    position: 'right-top',
                    onButtonClick: function () {
                        window.open('./?mod=route&p=User');
                    },
                });
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
                            mdui.snackbar({
                                message: '领取优惠券需要先登录哦',
                                buttonText: '登录',
                                position: 'right-top',
                                onButtonClick: function () {
                                    window.open('./?mod=route&p=User');
                                },
                            });
                        } else layer.msg(data.msg, {icon: 2});
                    }
                });
            }
        },
        Coupon() {
            if (this.CouponData.length == 0) return false;
            var inst = new mdui.Dialog('#CouponMsg');
            inst.open();
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
        CurlGoods(gid) {
            layer.msg("正在载入中...", {
                icon: 16,
                time: 9999999,
            });
            location.href = './?mod=shop&gid=' + gid;
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
            return `<font color=` + color + ` >` + price + `</font>`;
        },
        ShareGoods(gid) {
            if (gid <= 0) return;
            layer.msg("正在生成中...", {
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
                    layer.closeAll();
                    if (data.code == 1) {
                        mdui.alert('<div class="imagesr"><img src="' + data.src + '" width=300 heigth=450 /></div>', '保存图片分享此商品');
                    } else {
                        mdui.alert(data.msg, '警告');
                    }
                },
                error: function () {
                    layer.alert("生成失败！");
                },
            });
        },
        CutGoods(cid, index, type = 1) {
            if (type == 2) {
                this.GetGoods(cid);
            } else {
                if (this.ClassData[index].count == 0) {
                    mdui.alert('该分类下暂无商品', this.ClassData[index].name);
                    return;
                }
                layer.msg("正在载入中...", {
                    icon: 16,
                    time: 9999999,
                });
                this.cid = cid;
                this.name = this.ClassData[index].name;
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
            history.replaceState({}, null, './');
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
                            mdui.alert('该分类下暂无商品', data.CidArr.name);
                            return;
                        }

                        _this.Type = 2;
                        _this.CouponData = [];
                        _this.name = data.CidArr.name;
                        history.replaceState({}, null, '?cid=' + cid);
                        if (data.CidArr.content != '') {
                            mdui.dialog({
                                title: data.CidArr.name,
                                content: data.CidArr.content,
                                modal: true,
                                history: false,
                                buttons: [
                                    {
                                        text: '关闭',
                                    }
                                ]
                            });
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
}).mount('#Appid');