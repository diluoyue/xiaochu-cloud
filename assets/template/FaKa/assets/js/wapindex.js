const vm = Vue.createApp({
    data() {
        return {
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
            CouponDatas: [],
            CouponData: [],
            UserState: -1, //登录状态

            ActivitiesGoods: [], //秒杀活动商品
            Prices: [],
        };
    },
    mounted() {
        this.GetClass();
        this.GetInform();
        //this.GetArticleList();
        this.GetService();

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

        CouponAdd(token, cid) {

            if (cid == -1) {
                //全站优惠券
                CouponData = this.CouponData;
            } else {
                //分类优惠券
                CouponData = this.CouponDatas[cid];
            }
            if (CouponData.length == 0) return false;

            let _this = this;
            if (this.UserState == -1) {
                layer.open({
                    title: '温馨提示',
                    content: '领取优惠券需要先登录哦！',
                    offset: '50px',
                    btn: ['登录', '取消'],
                    btn1: function () {
                        window.open('./?mod=route&p=User');
                    }
                })
            } else {
                layer.msg("正在领取中...", {
                    icon: 16,
                    time: 9999999,
                    offset: '50px',
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
                            _this.CouponList((cid == -1 ? 3 : 2), cid);
                            layer.msg(data.msg, {icon: 1, offset: '50px'});
                        } else if (data.code == -2) {
                            layer.open({
                                title: '温馨提示',
                                content: '领取优惠券需要先登录哦！',
                                offset: '50px',
                                btn: ['登录', '取消'],
                                btn1: function () {
                                    window.open('./?mod=route&p=User');
                                }
                            })
                        } else layer.msg(data.msg, {icon: 2, offset: '50px'});
                    }
                });
            }
        },
        Coupon(cid = -1) {
            if (cid == -1) {
                //全站优惠券
                CouponData = this.CouponData;
            } else {
                //分类优惠券
                CouponData = this.CouponDatas[cid];
            }
            if (CouponData.length == 0) return false;
            let _this = this;
            let content = '<div class="CouponCss">';
            for (const item of CouponData) {
                content += `
				<div class="layui-card" onclick="vm.CouponAdd('` + item.limit_token + `','` + cid + `')">
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
                title: (cid == -1 ? '惊喜优惠券列表' : '分类(' + cid + ')优惠券列表'),
                content: content + '</div>',
                btn: ['关闭'],
                shade: [0.8, '#000'],
                offset: '50px',
                shadeClose: true,
                area: ['auto', (CouponData.length >= 3 ? '90%' : 'auto')]
            });
        },
        CouponList(type = 3, cid = -1) {
            let _this = this;
            $.ajax({
                type: "post",
                url: "./main.php?act=CouponList",
                data: {
                    cid: cid,
                    type: type,
                },
                dataType: "json",
                success: function (data) {
                    if (data.code >= 1) {
                        if (type == 3) {
                            _this.CouponData = data.data;
                        } else {
                            _this.CouponDatas[cid] = data.data;
                        }
                        _this.UserState = data.type;
                    } else {
                        if (type == 3) {
                            _this.CouponData = [];
                        } else {
                            _this.CouponDatas[cid] = [];
                        }
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
            return `<font color=` + color + ` >` + price + `</font>`;
        },
        AlertKefu() {
            let _this = this;

            let BtnArr = ['好的'];

            if (_this.Service.GroupUrl != '') {
                BtnArr = ['好的', '加入官方群'];
            }

            con = '<center><h3>客服QQ：' + this.Service.qq + '</h3><br><img src="' + this.Service.image + '" style="width:80%" /><br><hr>' + this.Service.tips + '</center>';

            layer.open({
                content: con,
                title: '联系客服',
                btn: BtnArr,
                area: ['90%', '70%'],
                shade: [0.8, '#393D49'],
                shadeClose: true,
                offset: '50px',
                btn2: function (layero, index) {
                    open(_this.Service.GroupUrl);
                }
            });
        },
        GetService() {
            let _this = this;
            $.ajax({
                type: 'post',
                url: './main.php?act=Service',
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        _this.Service = data;
                    } else {
                        layer.msg(data.msg, {icon: 2, offset: '50px'});
                    }
                },
                error: function () {
                    layer.alert('加载失败！', {offset: '50px'});
                }
            });
        },
        AlertMsg(content, name) {
            layer.alert(content, {
                title: name,
                btn: ['好的'],
                area: ['80%', '80%'],
                shade: [0.8, '#393D49'],
                offset: '50px',
                shadeClose: true
            });
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
                        if (data.data.PopupNotice != '') {
                            $('#hi-Modal').modal({
                                keyboard: true
                            });
                        }
                    } else {
                        layer.msg(data.msg, {icon: 2, offset: '50px'});
                    }
                },
                error: function () {
                    layer.alert('加载失败！', {offset: '50px'});
                }
            });
        },
        GetArticleList(state = 1) {
            let _this = this;

            if (state == 2) {
                ++_this.ArticleList.page;
            } else if (state == 3) {
                --_this.ArticleList.page;
            }

            $.ajax({
                type: 'post',
                url: './main.php?act=ArticleList',
                data: {
                    page: _this.ArticleList.page
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        _this.ArticleList.data = data.data;
                        if (data.data < 6) {
                            _this.ArticleList.state = false;
                        } else if (state == 3) {
                            _this.ArticleList.state = true;
                        }
                    } else {
                        _this.ArticleList.state = false;
                        if (state == 2) {
                            --_this.ArticleList.page;
                        }
                    }
                },
                error: function () {
                    layer.alert('加载失败！', {offset: '50px'});
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
                        $.each(_this.ClassData, function (key, val) {
                            _this.GoodsData[val.cid] = [];
                            _this.CouponDatas[val.cid] = [];
                        });
                        _this.GetGoods(data.data[0].cid, 0);
                    } else {
                        layer.msg(data.msg, {
                            icon: 2,
                            offset: '50px'
                        });
                    }
                },
                error: function () {
                    layer.alert('加载失败！', {offset: '50px'});
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
                        if (data.data.length != 0) {
                            _this.GoodsData[cid].push.apply(_this.GoodsData[cid], data.data);
                            _this.CouponList(2, cid);
                        }
                        if (_this.ClassData[index + 1] != undefined) {
                            _this.GetGoods(_this.ClassData[index + 1].cid, index + 1);
                        }
                    } else {
                        layer.msg(data.msg, {
                            icon: 2,
                            offset: '50px'
                        });
                    }
                },
                error: function () {
                    layer.alert('加载失败！', {offset: '50px'});
                }
            });
        }
    }
}).mount('#app');
if ($('#modals').text() != '') {
    $('#hi-Modal').modal({
        keyboard: true
    });
}