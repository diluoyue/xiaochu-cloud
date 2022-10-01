import {
    b as o,
    d as a,
    e as r,
    F as s,
    g as m,
    k as t,
    m as d,
    n,
    o as i,
    p as c,
    q as p,
    r as e,
    s as f,
    t as y,
    w as l
} from "./vendor.caf54732.js";

const u = {
        data: () => ({
            Banner: [],
            CommodityVolatility: {Data: [], List: []},
            CommodityVolatilityName: -1,
            CommodityVolatilityState: !0,
            Class: [],
            CouponData: [],
            GoodsList: [],
            cid: -1,
            GoodsState: !1,
            Page: 1,
            GoodsType: !0,
            Content: "",
            State: !1,
            ActivitiesGoods: 0,
            Prices: [],
            svg: '\n\t\t\t          <path class="path" d="\n\t\t\t            M 30 15\n\t\t\t            L 28 17\n\t\t\t            M 25.61 25.61\n\t\t\t            A 15 15, 0, 0, 1, 15 30\n\t\t\t            A 15 15, 0, 1, 1, 27.99 7.5\n\t\t\t            L 15 15\n\t\t\t          " style="stroke-width: 4px; fill: rgba(0, 0, 0, 0)"/>\n\t\t\t        '
        }), created() {
            this.BannerGet(), this.CommodityVolatilityGet(), this.ClassList(), this.GoodsListGet(), this.ActivitiesGoodsGet(), "" !== this.$ConfData.Conf.PopupNotice && void 0 !== this.$ConfData.Conf.PopupNotice && null !== this.$ConfData.Conf.PopupNotice && (this.Content = this.$ConfData.Conf.PopupNotice, this.State = !0)
        }, methods: {
            Open(e) {
                open('#/goods?gid=' + e);
                //this.$router.push({path: "goods", query: {gid: e}})
            }, SelectCategories(e = -1) {
                e !== this.cid && (this.cid = e, this.Page = 1, this.GoodsType = !0, this.GoodsList = [], -2 == e ? this.ActivitiesGoodsGet() : this.GoodsListGet())
            }, GoodsLoad(e) {
                !0 === this.GoodsType && (++this.Page, this.GoodsListGet())
            }, Money(e) {
                var t;
                return t = this.Price(e).price, (t -= t * (e.Seckill.depreciate / 100)).toFixed(e.accuracy) - 0
            }, Price(e) {
                var t, i, o;
                return void 0 !== this.Prices[e.gid] || (1 == e.method ? 0 == e.price || 0 == e.points ? (t = "#43A047;font-size: 90%;", i = "免费领取", o = 2) : (t = "#ff0000", i = e.price, o = 1) : 2 == e.method ? 0 == e.price ? (t = "#43A047;font-size: 90%;", i = "免费领取", o = 2) : (t = "#ff0000", i = e.price, o = 1) : 3 == e.method && (0 == e.points ? (t = "#43A047;font-size: 90%;", i = "免费领取", o = 2) : (t = "#ff0000", i = e.points, o = 3)), this.Prices[e.gid] = {
                    color: t,
                    price: i,
                    state: o
                }), this.Prices[e.gid]
            }, ClassList() {
                let e = this;
                this.$ajax.post("main.php?act=class", {num: 9999}).then((function (t) {
                    t.code >= 0 && (e.Class = t.data)
                }))
            }, ActivitiesGoodsGet() {
                var e = this;
                this.$ajax.post("main.php?act=ActivitiesGoods").then((t => {
                    if (t.code >= 0) {
                        if (e.GoodsState = !1, t.code >= 0) {
                            for (let i = 0; i < t.data.length; i++) e.GoodsList.push(t.data[i]);
                            0 === e.ActivitiesGoods && (e.ActivitiesGoods = t.Seckill), 0 === t.data.length && (e.GoodsType = !1)
                        }
                    } else e.ActivitiesGoods = []
                }))
            }, GoodsListGet() {
                this.GoodsState = !0;
                let e = this;
                this.$ajax.post("main.php?act=GoodsList", {
                    page: this.Page,
                    SortingType: 0,
                    Sorted: 1,
                    cid: this.cid
                }).then((function (t) {
                    if (e.GoodsState = !1, t.code >= 0) {
                        for (let i = 0; i < t.data.length; i++) e.GoodsList.push(t.data[i]);
                        0 === t.data.length && (e.GoodsType = !1)
                    }
                }))
            }, BannerGet() {
                let e = this;
                this.$ajax.post("main.php?act=banner").then((function (t) {
                    t.code >= 0 && (e.Banner = t.data, document.title = t.title)
                }))
            }, CommodityVolatilityGet(e = "") {
                let t = this;
                "" != e && (t.CommodityVolatilityName = e), this.CommodityVolatilityState = !0, this.$ajax.post("main.php?act=ChangesCommodityPrices", {name: t.CommodityVolatilityName}).then((function (e) {
                    t.CommodityVolatilityState = !1, e.code >= 0 && (t.CommodityVolatility = e.data, t.CommodityVolatilityName = e.ListName)
                }))
            }
        }
    }, h = ["href"], g = {key: 1, style: {width: "100%", height: "300px"}}, k = {class: "card-header"},
    x = r("img", {src: "./assets/template/PcStore/assets/bodong-1.04004567.svg", class: "imagebodong"}, null, -1),
    v = r("span", null, "价格波动", -1), C = r("i", {class: "el-icon-arrow-down el-icon--right"}, null, -1), _ = {key: 0},
    G = {style: {width: "100%"}}, P = {style: {width: "100%", "text-align": "center"}}, b = m("上架中"), w = m("已下架"),
    L = {key: 1, style: {height: "254px"}}, z = m(" 全部商品 "), S = m(" 限购秒杀 "), F = {style: {padding: "14px 0 0 0"}},
    V = {key: 0}, N = {key: 0},
    A = {style: {color: "#9e9e9e", "text-decoration": "line-through", "font-size": "8px", "margin-left": "4px"}},
    $ = {key: 1}, B = {key: 2}, D = {style: {"font-size": "13px", color: "#f4a300"}},
    T = {style: {color: "#9e9e9e", "text-decoration": "line-through", "font-size": "8px", "margin-left": "4px"}},
    q = {style: {"font-size": "10px", color: "#f4a300", "margin-left": "4px"}}, M = {key: 1}, j = {key: 0},
    U = {key: 1}, O = {key: 2}, H = {style: {"font-size": "13px", color: "#f4a300"}},
    E = {style: {"font-size": "10px", color: "#f4a300", "margin-left": "4px"}}, I = {key: 2, class: "PriceSales"},
    J = {style: {"margin-top": "0.5em"}}, K = m("库存充足"), Q = m("暂无库存"), R = {key: 1, style: {width: "76%"}},
    W = {key: 2, style: {"text-align": "center", "margin-top": "1em", color: "#999"}}, X = m(" 载入中 "),
    Y = {key: 1, style: {"background-color": "#FFFFFF"}}, Z = ["innerHTML"], ee = {class: "dialog-footer"},
    te = m("我已知晓");
u.render = function (u, ie, oe, ae, le, se) {
    const de = e("el-image"), ne = e("el-carousel-item"), re = e("el-carousel"), ce = e("el-col"), pe = e("el-tooltip"),
        ye = e("el-dropdown-item"), me = e("el-dropdown-menu"), fe = e("el-dropdown"), ue = e("n-ellipsis"),
        he = e("el-tag"), ge = e("el-row"), ke = e("el-descriptions-item"), xe = e("el-descriptions"),
        ve = e("el-popover"), Ce = e("el-scrollbar"), _e = e("el-empty"), Ge = e("el-card"), Pe = e("el-menu-item"),
        be = e("el-menu"), we = e("el-aside"), Le = e("el-icon"), ze = e("el-main"), Se = e("el-container"),
        Fe = e("el-button"), Ve = e("el-dialog"), Ne = t("loading"), Ae = t("infinite-scroll");
    return i(), o(s, null, [a(ge, {style: {padding: "0", margin: "0"}, gutter: 20}, {
        default: l((() => [a(ce, {
            span: 17,
            class: "Banner",
            style: {padding: "0"}
        }, {
            default: l((() => [le.Banner.length >= 1 ? (i(), d(re, {
                key: 0,
                height: "300px",
                style: {width: "100%", padding: "0"}
            }, {
                default: l((() => [(i(!0), o(s, null, n(le.Banner, ((e, t) => (i(), d(ne, {key: t}, {
                    default: l((() => [r("a", {
                        href: e.url,
                        target: "_blank"
                    }, [a(de, {style: {height: "300px", width: "100%"}, src: e.image}, null, 8, ["src"])], 8, h)])),
                    _: 2
                }, 1024)))), 128))])), _: 1
            })) : c((i(), o("div", g, null, 512)), [[Ne, !0]])])), _: 1
        }), a(ce, {span: 7, style: {padding: "0 0 0 1em"}}, {
            default: l((() => [c(a(Ge, {class: "box-card Price-Box"}, {
                header: l((() => [r("div", k, [x, v, -1 != le.CommodityVolatilityName && le.CommodityVolatility.List.length >= 1 ? (i(), d(fe, {
                    key: 0,
                    trigger: "click",
                    style: {color: "#FFFFFF", position: "absolute", right: "1em"}
                }, {
                    dropdown: l((() => [a(me, null, {
                        default: l((() => [(i(!0), o(s, null, n(le.CommodityVolatility.List, ((e, t) => (i(), d(ye, {
                            onClick: t => se.CommodityVolatilityGet(e),
                            key: t
                        }, {
                            default: l((() => [r("span", {style: p(e === le.CommodityVolatilityName ? "color:red" : "")}, y(e), 5)])),
                            _: 2
                        }, 1032, ["onClick"])))), 128))])), _: 1
                    })])),
                    default: l((() => [a(pe, {
                        effect: "light",
                        content: "点击选择日期",
                        placement: "left"
                    }, {
                        default: l((() => [r("span", null, [m(y(le.CommodityVolatilityName) + " ", 1), C])])),
                        _: 1
                    })])),
                    _: 1
                })) : f("", !0)])])),
                default: l((() => [le.CommodityVolatility.Data.length >= 1 ? (i(), o("div", _, [a(Ce, {height: "254px;"}, {
                    default: l((() => [(i(!0), o(s, null, n(le.CommodityVolatility.Data, ((e, t) => (i(), o("div", {
                        class: "Price-Box-Div",
                        key: t
                    }, [a(ve, {placement: "left", width: "350px", trigger: "hover"}, {
                        reference: l((() => [a(ge, {
                            gutter: 20,
                            onClick: t => se.Open(e.Gid),
                            style: {width: "100%", margin: "auto", cursor: "pointer"},
                            title: "查看详情"
                        }, {
                            default: l((() => [a(ce, {span: 15}, {
                                default: l((() => [a(ue, {style: {"max-width": "100%"}}, {
                                    default: l((() => [m(y(e.Name), 1)])),
                                    _: 2
                                }, 1024)])), _: 2
                            }, 1024), a(ce, {
                                span: 9,
                                style: {"text-align": "right", "padding-right": "0.5em"}
                            }, {
                                default: l((() => [1 === e.type ? (i(), d(he, {
                                    key: 0,
                                    size: "mini",
                                    effect: "plain",
                                    "disable-transitions": "",
                                    type: "danger"
                                }, {
                                    default: l((() => [m(" 涨价 " + y((e.NewPrice - e.UsedPrice).toFixed(3)) + "元 ", 1)])),
                                    _: 2
                                }, 1024)) : (i(), d(he, {
                                    key: 1,
                                    size: "mini",
                                    effect: "plain",
                                    "disable-transitions": "",
                                    type: "success"
                                }, {
                                    default: l((() => [m(" 降价 " + y((e.UsedPrice - e.NewPrice).toFixed(3)) + "元 ", 1)])),
                                    _: 2
                                }, 1024))])), _: 2
                            }, 1024)])), _: 2
                        }, 1032, ["onClick"])])),
                        default: l((() => [r("div", G, [r("div", P, [a(de, {
                            style: {width: "100px", height: "100px"},
                            src: e.image
                        }, null, 8, ["src"])]), r("div", null, [a(xe, {
                            title: e.Name,
                            size: "mini",
                            direction: "vertical",
                            column: 4,
                            border: ""
                        }, {
                            default: l((() => [a(ke, {label: "商品编号"}, {
                                default: l((() => [m(y(e.Gid), 1)])),
                                _: 2
                            }, 1024), a(ke, {label: "商品状态"}, {
                                default: l((() => [1 == e.state ? (i(), d(he, {
                                    key: 0,
                                    type: "success",
                                    size: "mini",
                                    effect: "dark"
                                }, {default: l((() => [b])), _: 1})) : (i(), d(he, {
                                    key: 1,
                                    type: "danger",
                                    size: "mini",
                                    effect: "dark"
                                }, {default: l((() => [w])), _: 1}))])), _: 2
                            }, 1024), a(ke, {label: "当前价格"}, {
                                default: l((() => [a(he, {
                                    size: "mini",
                                    effect: "dark"
                                }, {default: l((() => [m(y(e.NewPrice) + "元", 1)])), _: 2}, 1024)])), _: 2
                            }, 1024), a(ke, {label: "历史价格"}, {
                                default: l((() => [a(he, {
                                    type: "info",
                                    size: "mini",
                                    effect: "dark"
                                }, {default: l((() => [m(y(e.UsedPrice) + "元", 1)])), _: 2}, 1024)])), _: 2
                            }, 1024), a(ke, {label: "波动状态"}, {
                                default: l((() => [1 === e.type ? (i(), d(he, {
                                    key: 0,
                                    size: "mini",
                                    "disable-transitions": "",
                                    type: "danger"
                                }, {
                                    default: l((() => [m(" 涨价 " + y((e.NewPrice - e.UsedPrice).toFixed(3)) + "元 ", 1)])),
                                    _: 2
                                }, 1024)) : (i(), d(he, {
                                    key: 1,
                                    size: "mini",
                                    "disable-transitions": "",
                                    type: "success"
                                }, {
                                    default: l((() => [m("降价 " + y((e.UsedPrice - e.NewPrice).toFixed(3)) + "元", 1)])),
                                    _: 2
                                }, 1024))])), _: 2
                            }, 1024), 0 != e.key ? (i(), d(ke, {
                                key: 0,
                                label: "规格组合"
                            }, {
                                default: l((() => [m(y(e.key), 1)])),
                                _: 2
                            }, 1024)) : f("", !0), a(ke, {label: "波动时间"}, {
                                default: l((() => [m(y(e.date), 1)])),
                                _: 2
                            }, 1024)])), _: 2
                        }, 1032, ["title"])])])])),
                        _: 2
                    }, 1024)])))), 128))])), _: 1
                })])) : (i(), o("div", L, [a(_e, {description: "当日的商品价格很平稳"})]))])),
                _: 1
            }, 512), [[Ne, le.CommodityVolatilityState]])])), _: 1
        }), a(ce, {span: 24, style: {padding: "0", "margin-top": "1em", "border-radius": "1em", overflow: "hidden"}}, {
            default: l((() => [le.Class.length >= 1 ? (i(), d(Se, {
                key: 0,
                style: {border: "1px solid #eee", "background-color": "#FFFFFF"}
            }, {
                default: l((() => [a(we, {width: "220px"}, {
                    default: l((() => [a(be, {
                        "default-active": le.cid,
                        "active-text-color": "#ff5500"
                    }, {
                        default: l((() => [a(Pe, {
                            onClick: ie[0] || (ie[0] = e => se.SelectCategories(-1)),
                            index: "-1"
                        }, {title: l((() => [z])), _: 1}), le.ActivitiesGoods >= 1 ? (i(), d(Pe, {
                            key: 0,
                            onClick: ie[1] || (ie[1] = e => se.SelectCategories(-2)),
                            index: "-2"
                        }, {
                            title: l((() => [S])),
                            _: 1
                        })) : f("", !0), (i(!0), o(s, null, n(le.Class, ((e, t) => (i(), d(Pe, {
                            key: t,
                            index: e.cid,
                            onClick: t => se.SelectCategories(e.cid)
                        }, {title: l((() => [m(y(e.name), 1)])), _: 2}, 1032, ["index", "onClick"])))), 128))])), _: 1
                    }, 8, ["default-active"])])), _: 1
                }), c(a(Se, {style: {"padding-bottom": "1em"}}, {
                    default: l((() => [c(a(ze, {
                        "element-loading-svg": le.svg,
                        "element-loading-svg-view-box": "-10, -10, 50, 50"
                    }, {
                        default: l((() => [le.GoodsList.length >= 1 ? (i(), d(ge, {key: 0, gutter: 12}, {
                            default: l((() => [(i(!0), o(s, null, n(le.GoodsList, ((e, t) => (i(), d(ce, {
                                span: 6,
                                key: t,
                                style: {"margin-bottom": "10px"}
                            }, {
                                default: l((() => [a(Ge, {
                                    onClick: t => se.Open(e.gid),
                                    shadow: "hover",
                                    "body-style": {padding: "10px", cursor: "pointer"}
                                }, {
                                    default: l((() => [a(de, {
                                        lazy: "",
                                        style: {width: "100%", height: "200px"},
                                        title: e.name,
                                        src: e.image,
                                        fit: "cover"
                                    }, null, 8, ["title", "src"]), r("div", F, [a(ue, {
                                        style: {
                                            "max-width": "100%",
                                            "line-height": "22px",
                                            "font-size": "16px",
                                            "font-weight": "500",
                                            color: "#333",
                                            "margin-bottom": "0.5em",
                                            "text-indent": "0.2em"
                                        }
                                    }, {
                                        default: l((() => [m(y(e.name), 1)])),
                                        _: 2
                                    }, 1024), -2 == le.cid && le.GoodsList.length >= 1 ? (i(), o("div", V, [1 === se.Price(e).state ? (i(), o("div", N, [r("span", {
                                        class: "Price",
                                        style: p("color:" + se.Price(e).color)
                                    }, "￥" + y(se.Money(e)), 5), r("span", A, y(se.Price(e).price), 1)])) : 2 === se.Price(e).state ? (i(), o("div", $, [r("span", {style: p("color:" + se.Price(e).color)}, y(se.Price(e).price), 5)])) : (i(), o("div", B, [r("span", D, y(se.Money(e)), 1), r("span", T, y(se.Price(e).price), 1), r("span", q, y(e.currency), 1)]))])) : (i(), o("div", M, [1 === se.Price(e).state ? (i(), o("div", j, [r("span", {
                                        class: "Price",
                                        style: p("color:" + se.Price(e).color)
                                    }, "￥" + y(se.Price(e).price), 5)])) : 2 === se.Price(e).state ? (i(), o("div", U, [r("span", {style: p("color:" + se.Price(e).color)}, y(se.Price(e).price), 5)])) : (i(), o("div", O, [r("span", H, y(se.Price(e).price), 1), r("span", E, y(e.currency), 1)]))])), e.sales >= 1 ? (i(), o("div", I, "销量" + y(e.sales >= 1e3 ? "1万+" : e.sales), 1)) : f("", !0), r("div", J, [a(he, {
                                        size: "mini",
                                        style: {"margin-right": "0.5em"}
                                    }, {
                                        default: l((() => [m("每份" + y(e.quantity) + y(e.units), 1)])),
                                        _: 2
                                    }, 1024), e.quota >= 100 ? (i(), d(he, {
                                        key: 0,
                                        size: "mini",
                                        type: "success"
                                    }, {
                                        default: l((() => [K])),
                                        _: 1
                                    })) : e.quota > 0 && e.quota < 100 ? (i(), d(he, {
                                        key: 1,
                                        size: "mini",
                                        type: "danger"
                                    }, {
                                        default: l((() => [m("库存较少(" + y(e.quota) + ")", 1)])),
                                        _: 2
                                    }, 1024)) : (i(), d(he, {
                                        key: 2,
                                        size: "mini",
                                        type: "info"
                                    }, {
                                        default: l((() => [Q])),
                                        _: 1
                                    })), -2 == le.cid && le.GoodsList.length >= 1 ? (i(), d(he, {
                                        key: 3,
                                        size: "mini",
                                        type: "danger",
                                        style: {"margin-left": "0.5em"}
                                    }, {
                                        default: l((() => [m(" 优惠" + y(e.Seckill.depreciate) + "% ", 1)])),
                                        _: 2
                                    }, 1024)) : f("", !0)])])])), _: 2
                                }, 1032, ["onClick"])])), _: 2
                            }, 1024)))), 128))])), _: 1
                        })) : (i(), o("div", R, [a(_e, {description: "此分类一个商品也没有"})])), le.GoodsState && le.GoodsList.length >= 1 ? (i(), o("div", W, [X, a(Le, {
                            color: "#999",
                            class: "el-icon-loading"
                        })])) : f("", !0)])), _: 1
                    }, 8, ["element-loading-svg"]), [[Ne, le.GoodsState && 0 === le.GoodsList.length]])])), _: 1
                }, 512), [[Ae, se.GoodsLoad]])])), _: 1
            })) : (i(), o("div", Y, [a(_e, {description: "当前站点一个商品也没有"})]))])), _: 1
        })])), _: 1
    }), a(Ve, {
        title: "系统公告",
        "append-to-body": "",
        center: "",
        modal: "",
        modelValue: le.State,
        "onUpdate:modelValue": ie[3] || (ie[3] = e => le.State = e),
        width: "560px"
    }, {
        footer: l((() => [r("span", ee, [a(Fe, {
            class: "ant-btn-primary",
            type: "danger",
            size: "medium",
            onClick: ie[2] || (ie[2] = e => le.State = !1)
        }, {default: l((() => [te])), _: 1})])])),
        default: l((() => [r("div", {innerHTML: le.Content}, null, 8, Z)])),
        _: 1
    }, 8, ["modelValue"])], 64)
};
export {u as default};
