(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["pages-user-landing-register-register"], {
    1122: function (t, e, i) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = void 0;
        var o = {
            data: function () {
                return {Form: {}, num: 1, state: !1, content: "注册成功！", code_image: ""}
            }, onLoad: function () {
                this.code_image = getApp().globalData.domain + "/user/ajax.php?act=VerificationCode&n=Login_res"
            }, methods: {
                Open: function () {
                    this.shows = !0
                }, OpenPage: function () {
                    this.$u.route({type: "reLaunch", url: "pages/user/user"})
                }, Submit: function () {
                    var t = this;
                    this.state = !0, this.$u.post("?act=UserAjax&uac=login_register", this.Form).then((function (e) {
                        t.state = !1, e.code >= 0 ? (t.$refs.uToast.show({
                            title: e.msg,
                            type: "success"
                        }), t.OpenPage()) : -2 == e.code ? (t.$refs.uToast.show({
                            title: e.msg,
                            type: "error"
                        }), t.adds()) : t.adds()
                    }))
                }, adds: function () {
                    this.num += 1
                }, go_register: function () {
                    this.$u.route({type: "navigateTo", url: "pages/user/landing/login/login"})
                }
            }
        };
        e.default = o
    }, "2cf5": function (t, e, i) {
        var o = i("ba84");
        "string" === typeof o && (o = [[t.i, o, ""]]), o.locals && (t.exports = o.locals);
        var a = i("4f06").default;
        a("6a02b18a", o, !0, {sourceMap: !1, shadowMode: !1})
    }, 3074: function (t, e, i) {
        "use strict";
        i("a9e3"), Object.defineProperty(e, "__esModule", {value: !0}), e.default = void 0;
        var o = {
            name: "u-loading",
            props: {
                mode: {type: String, default: "circle"},
                color: {type: String, default: "#c7c7c7"},
                size: {type: [String, Number], default: "34"},
                show: {type: Boolean, default: !0}
            },
            computed: {
                cricleStyle: function () {
                    var t = {};
                    return t.width = this.size + "rpx", t.height = this.size + "rpx", "circle" == this.mode && (t.borderColor = "#e4e4e4 #e4e4e4 #e4e4e4 ".concat(this.color ? this.color : "#c7c7c7")), t
                }
            }
        };
        e.default = o
    }, "317f": function (t, e, i) {
        "use strict";
        i.d(e, "b", (function () {
            return a
        })), i.d(e, "c", (function () {
            return n
        })), i.d(e, "a", (function () {
            return o
        }));
        var o = {
            uInput: i("71b2").default,
            uRow: i("311c").default,
            uCol: i("fb7b").default,
            uImage: i("c4e9").default,
            uLoading: i("cb09").default,
            uButton: i("1ae1").default,
            uToast: i("a680").default
        }, a = function () {
            var t = this, e = t.$createElement, i = t._self._c || e;
            return i("v-uni-view", {staticClass: "content"}, [i("v-uni-view", {staticClass: "login-bg"}, [i("v-uni-view", {staticClass: "login-card"}, [i("v-uni-view", {staticClass: "login-head"}, [t._v("欢迎加入")])
                , i("v-uni-view", {staticClass: "login-input login-margin-b"}, [i("u-input", {
                    attrs: {
                        height: 90,
                        "border-color": "#eee",
                        focus: !0,
                        placeholder: "请输入绑定QQ",
                        type: "text",
                        border: !0
                    }, model: {
                        value: t.Form.qq, callback: function (e) {
                            t.$set(t.Form, "qq", e)
                        }, expression: "Form.qq"
                    }
                })], 1), i("v-uni-view", {staticClass: "login-input login-margin-b"}, [i("u-input", {
                    attrs: {
                        height: 90,
                        "border-color": "#eee",
                        placeholder: "请输入登陆账号",
                        type: "text",
                        border: !0
                    }, model: {
                        value: t.Form.username, callback: function (e) {
                            t.$set(t.Form, "username", e)
                        }, expression: "Form.username"
                    }
                })], 1), i("v-uni-view", {staticClass: "login-input login-margin-b"}, [i("u-input", {
                    attrs: {
                        height: 90,
                        "border-color": "#eee",
                        placeholder: "请输入登陆密码",
                        type: "password",
                        border: !0
                    }, model: {
                        value: t.Form.password, callback: function (e) {
                            t.$set(t.Form, "password", e)
                        }, expression: "Form.password"
                    }
                })], 1), i("v-uni-view", {staticClass: "login-input"}, [i("u-row", {attrs: {gutter: "1"}}, [i("u-col", {attrs: {span: "6"}}, [i("u-input", {
                    attrs: {
                        height: 90,
                        placeholder: "请输入验证码",
                        "border-color": "#eee",
                        type: "text",
                        border: !0
                    }, model: {
                        value: t.Form.vercode, callback: function (e) {
                            t.$set(t.Form, "vercode", e)
                        }, expression: "Form.vercode"
                    }
                })], 1), i("u-col", {attrs: {span: "6"}}, [i("u-image", {
                    key: t.num,
                    attrs: {
                        "lazy-load": !1,
                        width: "100%",
                        height: 90,
                        src: t.code_image + "&t=" + t.num,
                        fade: !0,
                        duration: "450"
                    },
                    on: {
                        click: function (e) {
                            arguments[0] = e = t.$handleEvent(e), t.adds()
                        }
                    }
                }, [i("v-uni-view", {
                    staticStyle: {"font-size": "24rpx"},
                    attrs: {slot: "error"},
                    slot: "error"
                }, [t._v("加载失败,点击重试")]), i("u-loading", {
                    attrs: {slot: "loading"},
                    slot: "loading"
                })], 1)], 1)], 1)], 1), i("v-uni-view", {staticClass: "login-function"}, [i("v-uni-view", {
                    staticClass: "login-register",
                    on: {
                        click: function (e) {
                            arguments[0] = e = t.$handleEvent(e), t.go_register.apply(void 0, arguments)
                        }
                    }
                }, [t._v("快速登录")])], 1)], 1)], 1), i("v-uni-view", {staticClass: "login-btn"}, [i("u-button", {
                attrs: {
                    type: "success",
                    loading: t.state,
                    "hair-line": !1,
                    ripple: !0
                }, on: {
                    click: function (e) {
                        arguments[0] = e = t.$handleEvent(e), t.$u.throttle(t.Submit(), 1e3)
                    }
                }
            }, [t._v("注册")])], 1), i("u-toast", {ref: "uToast"})], 1)
        }, n = []
    }, "36dd": function (t, e, i) {
        "use strict";
        var o = i("2cf5"), a = i.n(o);
        a.a
    }, 3848: function (t, e, i) {
        "use strict";
        var o = i("c7ed"), a = i.n(o);
        a.a
    }, 6203: function (t, e, i) {
        var o = i("24fb");
        e = o(!1), e.push([t.i, ".login-btn[data-v-2711fa7a]{padding:%?10?% %?70?%;margin-top:%?500?%;text-align:center}.login-function[data-v-2711fa7a]{overflow:auto;padding:%?20?% %?20?% %?30?% %?20?%}.login-forget[data-v-2711fa7a]{float:left;font-size:%?26?%;color:#999}.login-register[data-v-2711fa7a]{color:#666;float:right;font-size:%?26?%}.login-input uni-input[data-v-2711fa7a]{background:#f2f5f6;font-size:%?28?%;padding:%?10?% %?25?%;height:%?62?%;line-height:%?62?%;border-radius:%?8?%}.login-margin-b[data-v-2711fa7a]{margin-bottom:%?25?%}.login-input[data-v-2711fa7a]{padding:%?10?% %?20?%}.login-head[data-v-2711fa7a]{font-size:%?34?%;text-align:center;padding:%?0?% %?10?% %?55?% %?10?%}.login-card[data-v-2711fa7a]{background:#fff;border-radius:%?12?%;padding:%?10?% %?25?%;position:relative;margin-top:%?30?%}.login-bg[data-v-2711fa7a]{height:%?260?%;padding:%?25?%}.content[data-v-2711fa7a]{margin-bottom:%?100?%}", ""]), t.exports = e
    }, "67f1": function (t, e, i) {
        "use strict";
        var o = i("ddd9"), a = i.n(o);
        a.a
    }, "773f": function (t, e, i) {
        "use strict";
        i("a9e3"), Object.defineProperty(e, "__esModule", {value: !0}), e.default = void 0;
        var o = {
            name: "u-image",
            props: {
                src: {type: String, default: ""},
                mode: {type: String, default: "aspectFill"},
                width: {type: [String, Number], default: "100%"},
                height: {type: [String, Number], default: "auto"},
                shape: {type: String, default: "square"},
                borderRadius: {type: [String, Number], default: 0},
                lazyLoad: {type: Boolean, default: !0},
                showMenuByLongpress: {type: Boolean, default: !0},
                loadingIcon: {type: String, default: "photo"},
                errorIcon: {type: String, default: "error-circle"},
                showLoading: {type: Boolean, default: !0},
                showError: {type: Boolean, default: !0},
                fade: {type: Boolean, default: !0},
                webp: {type: Boolean, default: !1},
                duration: {type: [String, Number], default: 500},
                bgColor: {type: String, default: "#f3f4f6"}
            },
            data: function () {
                return {isError: !1, loading: !0, opacity: 1, durationTime: this.duration, backgroundStyle: {}}
            },
            watch: {
                src: {
                    immediate: !0, handler: function (t) {
                        t ? this.isError = !1 : (this.isError = !0, this.loading = !1)
                    }
                }
            },
            computed: {
                wrapStyle: function () {
                    var t = {};
                    return t.width = this.$u.addUnit(this.width), t.height = this.$u.addUnit(this.height), t.borderRadius = "circle" == this.shape ? "50%" : this.$u.addUnit(this.borderRadius), t.overflow = this.borderRadius > 0 ? "hidden" : "visible", this.fade && (t.opacity = this.opacity, t.transition = "opacity ".concat(Number(this.durationTime) / 1e3, "s ease-in-out")), t
                }
            },
            methods: {
                onClick: function () {
                    this.$emit("click")
                }, onErrorHandler: function (t) {
                    this.loading = !1, this.isError = !0, this.$emit("error", t)
                }, onLoadHandler: function () {
                    var t = this;
                    if (this.loading = !1, this.isError = !1, this.$emit("load"), !this.fade) return this.removeBgColor();
                    this.opacity = 0, this.durationTime = 0, setTimeout((function () {
                        t.durationTime = t.duration, t.opacity = 1, setTimeout((function () {
                            t.removeBgColor()
                        }), t.durationTime)
                    }), 50)
                }, removeBgColor: function () {
                    this.backgroundStyle = {backgroundColor: "transparent"}
                }
            }
        };
        e.default = o
    }, "8e0f": function (t, e, i) {
        "use strict";
        i.d(e, "b", (function () {
            return a
        })), i.d(e, "c", (function () {
            return n
        })), i.d(e, "a", (function () {
            return o
        }));
        var o = {uIcon: i("1143").default}, a = function () {
            var t = this, e = t.$createElement, i = t._self._c || e;
            return i("v-uni-view", {
                staticClass: "u-image",
                style: [t.wrapStyle, t.backgroundStyle],
                on: {
                    click: function (e) {
                        arguments[0] = e = t.$handleEvent(e), t.onClick.apply(void 0, arguments)
                    }
                }
            }, [t.isError ? t._e() : i("v-uni-image", {
                staticClass: "u-image__image",
                style: {borderRadius: "circle" == t.shape ? "50%" : t.$u.addUnit(t.borderRadius)},
                attrs: {
                    src: t.src,
                    mode: t.mode,
                    "lazy-load": t.lazyLoad,
                    "show-menu-by-longpress": t.showMenuByLongpress
                },
                on: {
                    error: function (e) {
                        arguments[0] = e = t.$handleEvent(e), t.onErrorHandler.apply(void 0, arguments)
                    }, load: function (e) {
                        arguments[0] = e = t.$handleEvent(e), t.onLoadHandler.apply(void 0, arguments)
                    }
                }
            }), t.showLoading && t.loading ? i("v-uni-view", {
                staticClass: "u-image__loading",
                style: {
                    borderRadius: "circle" == t.shape ? "50%" : t.$u.addUnit(t.borderRadius),
                    backgroundColor: this.bgColor
                }
            }, [t.$slots.loading ? t._t("loading") : i("u-icon", {
                attrs: {
                    name: t.loadingIcon,
                    width: t.width,
                    height: t.height
                }
            })], 2) : t._e(), t.showError && t.isError && !t.loading ? i("v-uni-view", {
                staticClass: "u-image__error",
                style: {borderRadius: "circle" == t.shape ? "50%" : t.$u.addUnit(t.borderRadius)}
            }, [t.$slots.error ? t._t("error") : i("u-icon", {
                attrs: {
                    name: t.errorIcon,
                    width: t.width,
                    height: t.height
                }
            })], 2) : t._e()], 1)
        }, n = []
    }, a247: function (t, e, i) {
        "use strict";
        i.r(e);
        var o = i("3074"), a = i.n(o);
        for (var n in o) "default" !== n && function (t) {
            i.d(e, t, (function () {
                return o[t]
            }))
        }(n);
        e["default"] = a.a
    }, a42b: function (t, e, i) {
        var o = i("24fb");
        e = o(!1), e.push([t.i, "/* uni.scss */.TemCut[data-v-1b741bef]{bottom:%?300?%;right:%?40?%;border-radius:5493px;background-color:#e1e1e1;z-index:999999;opacity:.8;width:%?80?%;height:%?80?%;position:fixed;z-index:9;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;flex-direction:column;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;background-color:#e1e1e1;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-transition:opacity .4s}.grid-text[data-v-1b741bef]{font-size:%?20?%;margin-top:%?8?%;color:#909399}.demo-warter[data-v-1b741bef]{border-radius:8px;margin:5px;background-color:#fff;padding:8px;position:relative;max-width:47vw}.u-close[data-v-1b741bef]{position:absolute;top:%?32?%;right:%?32?%}.demo-image[data-v-1b741bef]{width:100%;border-radius:4px}.demo-title[data-v-1b741bef]{font-size:%?30?%;margin-top:5px;color:#303133}.demo-tag[data-v-1b741bef]{display:flex;margin-top:5px}.demo-tag-owner[data-v-1b741bef]{background-color:#fa3534;color:#fff;display:flex;align-items:center;padding:%?4?% %?14?%;border-radius:%?50?%;font-size:%?20?%;line-height:1}.demo-tag-text[data-v-1b741bef]{border:1px solid #2979ff;color:#2979ff;margin-left:10px;border-radius:%?50?%;line-height:1;padding:%?4?% %?14?%;display:flex;align-items:center;border-radius:%?50?%;font-size:%?20?%}.HistoryBtn[data-v-1b741bef]{background-color:#f1f1f1;border:none!important;font-size:.5rem;margin:.2rem}.demo-price[data-v-1b741bef]{font-size:%?30?%;color:#fa3534;margin-top:5px}.demo-shop[data-v-1b741bef]{font-size:%?22?%;color:#909399;margin-top:5px}.uni-scroll-view-content[data-v-1b741bef]{height:auto!important}.jingdong[data-v-1b741bef]{width:96%;margin-left:2%;height:auto;background-color:#fff;display:flex;box-shadow:3px 3px 16px #eee;margin-bottom:1rem}.jingdong .left[data-v-1b741bef]{padding:0 %?30?%;background-color:#5f94e0;text-align:center;font-size:%?28?%;color:#fff}.jingdong .left .sum[data-v-1b741bef]{margin-top:%?50?%;font-weight:700;font-size:%?32?%}.jingdong .left .sum .num[data-v-1b741bef]{font-size:%?80?%}.jingdong .left .type[data-v-1b741bef]{margin-bottom:%?50?%;font-size:%?24?%}.jingdong .right[data-v-1b741bef]{padding:%?20?% %?20?% 0;font-size:%?28?%}.jingdong .right .top[data-v-1b741bef]{border-bottom:%?2?% dashed #e4e7ed}.jingdong .right .top .title[data-v-1b741bef]{margin-right:%?60?%;line-height:%?40?%}.jingdong .right .top .title .tag[data-v-1b741bef]{padding:%?4?% %?20?%;background-color:#499ac9;border-radius:%?20?%;color:#fff;font-weight:700;font-size:%?24?%;margin-right:%?10?%}.jingdong .right .top .bottom[data-v-1b741bef]{display:flex;margin-top:%?20?%;align-items:center;justify-content:space-between;margin-bottom:%?10?%}.jingdong .right .top .bottom .date[data-v-1b741bef]{font-size:%?20?%;flex:1}.jingdong .right .tips[data-v-1b741bef]{width:100%;line-height:%?50?%;display:flex;align-items:center;justify-content:space-between;font-size:%?24?%}.jingdong .right .tips .transpond[data-v-1b741bef]{margin-right:%?10?%}.jingdong .right .tips .explain[data-v-1b741bef]{display:flex;align-items:center}.jingdong .right .tips .particulars[data-v-1b741bef]{width:%?30?%;height:%?30?%;box-sizing:border-box;padding-top:%?8?%;border-radius:50%;background-color:#c8c9cc;text-align:center}.Countitle[data-v-1b741bef]{width:100vw;height:3rem;text-align:left;line-height:3rem;box-shadow:3px 3px 16px #ccc;font-weight:700;font-size:16px;text-indent:.5rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.u-loading-circle[data-v-1b741bef]{display:inline-flex;vertical-align:middle;width:%?28?%;height:%?28?%;background:0 0;border-radius:50%;border:2px solid;border-color:#e5e5e5 #e5e5e5 #e5e5e5 #8f8d8e;-webkit-animation:u-circle-data-v-1b741bef 1s linear infinite;animation:u-circle-data-v-1b741bef 1s linear infinite}.u-loading-flower[data-v-1b741bef]{width:20px;height:20px;display:inline-block;vertical-align:middle;-webkit-animation:a 1s steps(12) infinite;animation:u-flower-data-v-1b741bef 1s steps(12) infinite;background:transparent url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCI+PHBhdGggZmlsbD0ibm9uZSIgZD0iTTAgMGgxMDB2MTAwSDB6Ii8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjRTlFOUU5IiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgLTMwKSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iIzk4OTY5NyIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgzMCAxMDUuOTggNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjOUI5OTlBIiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKDYwIDc1Ljk4IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0EzQTFBMiIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSg5MCA2NSA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNBQkE5QUEiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoMTIwIDU4LjY2IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0IyQjJCMiIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgxNTAgNTQuMDIgNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjQkFCOEI5IiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKDE4MCA1MCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNDMkMwQzEiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTE1MCA0NS45OCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNDQkNCQ0IiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTEyMCA0MS4zNCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNEMkQyRDIiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTkwIDM1IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0RBREFEQSIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgtNjAgMjQuMDIgNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjRTJFMkUyIiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKC0zMCAtNS45OCA2NSkiLz48L3N2Zz4=) no-repeat;background-size:100%}@-webkit-keyframes u-flower-data-v-1b741bef{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes u-flower-data-v-1b741bef{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@-webkit-keyframes u-circle-data-v-1b741bef{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}", ""]), t.exports = e
    }, ba84: function (t, e, i) {
        var o = i("24fb");
        e = o(!1), e.push([t.i, "/* uni.scss */.TemCut[data-v-00140dbc]{bottom:%?300?%;right:%?40?%;border-radius:5493px;background-color:#e1e1e1;z-index:999999;opacity:.8;width:%?80?%;height:%?80?%;position:fixed;z-index:9;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;flex-direction:column;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;background-color:#e1e1e1;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-transition:opacity .4s}.grid-text[data-v-00140dbc]{font-size:%?20?%;margin-top:%?8?%;color:#909399}.demo-warter[data-v-00140dbc]{border-radius:8px;margin:5px;background-color:#fff;padding:8px;position:relative;max-width:47vw}.u-close[data-v-00140dbc]{position:absolute;top:%?32?%;right:%?32?%}.demo-image[data-v-00140dbc]{width:100%;border-radius:4px}.demo-title[data-v-00140dbc]{font-size:%?30?%;margin-top:5px;color:#303133}.demo-tag[data-v-00140dbc]{display:flex;margin-top:5px}.demo-tag-owner[data-v-00140dbc]{background-color:#fa3534;color:#fff;display:flex;align-items:center;padding:%?4?% %?14?%;border-radius:%?50?%;font-size:%?20?%;line-height:1}.demo-tag-text[data-v-00140dbc]{border:1px solid #2979ff;color:#2979ff;margin-left:10px;border-radius:%?50?%;line-height:1;padding:%?4?% %?14?%;display:flex;align-items:center;border-radius:%?50?%;font-size:%?20?%}.HistoryBtn[data-v-00140dbc]{background-color:#f1f1f1;border:none!important;font-size:.5rem;margin:.2rem}.demo-price[data-v-00140dbc]{font-size:%?30?%;color:#fa3534;margin-top:5px}.demo-shop[data-v-00140dbc]{font-size:%?22?%;color:#909399;margin-top:5px}.uni-scroll-view-content[data-v-00140dbc]{height:auto!important}.jingdong[data-v-00140dbc]{width:96%;margin-left:2%;height:auto;background-color:#fff;display:flex;box-shadow:3px 3px 16px #eee;margin-bottom:1rem}.jingdong .left[data-v-00140dbc]{padding:0 %?30?%;background-color:#5f94e0;text-align:center;font-size:%?28?%;color:#fff}.jingdong .left .sum[data-v-00140dbc]{margin-top:%?50?%;font-weight:700;font-size:%?32?%}.jingdong .left .sum .num[data-v-00140dbc]{font-size:%?80?%}.jingdong .left .type[data-v-00140dbc]{margin-bottom:%?50?%;font-size:%?24?%}.jingdong .right[data-v-00140dbc]{padding:%?20?% %?20?% 0;font-size:%?28?%}.jingdong .right .top[data-v-00140dbc]{border-bottom:%?2?% dashed #e4e7ed}.jingdong .right .top .title[data-v-00140dbc]{margin-right:%?60?%;line-height:%?40?%}.jingdong .right .top .title .tag[data-v-00140dbc]{padding:%?4?% %?20?%;background-color:#499ac9;border-radius:%?20?%;color:#fff;font-weight:700;font-size:%?24?%;margin-right:%?10?%}.jingdong .right .top .bottom[data-v-00140dbc]{display:flex;margin-top:%?20?%;align-items:center;justify-content:space-between;margin-bottom:%?10?%}.jingdong .right .top .bottom .date[data-v-00140dbc]{font-size:%?20?%;flex:1}.jingdong .right .tips[data-v-00140dbc]{width:100%;line-height:%?50?%;display:flex;align-items:center;justify-content:space-between;font-size:%?24?%}.jingdong .right .tips .transpond[data-v-00140dbc]{margin-right:%?10?%}.jingdong .right .tips .explain[data-v-00140dbc]{display:flex;align-items:center}.jingdong .right .tips .particulars[data-v-00140dbc]{width:%?30?%;height:%?30?%;box-sizing:border-box;padding-top:%?8?%;border-radius:50%;background-color:#c8c9cc;text-align:center}.Countitle[data-v-00140dbc]{width:100vw;height:3rem;text-align:left;line-height:3rem;box-shadow:3px 3px 16px #ccc;font-weight:700;font-size:16px;text-indent:.5rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.u-image[data-v-00140dbc]{position:relative;transition:opacity .5s ease-in-out}.u-image__image[data-v-00140dbc]{width:100%;height:100%}.u-image__loading[data-v-00140dbc], .u-image__error[data-v-00140dbc]{position:absolute;top:0;left:0;width:100%;height:100%;display:flex;flex-direction:row;align-items:center;justify-content:center;background-color:#f3f4f6;color:#909399;font-size:%?46?%}", ""]), t.exports = e
    }, c4e9: function (t, e, i) {
        "use strict";
        i.r(e);
        var o = i("8e0f"), a = i("c84d");
        for (var n in a) "default" !== n && function (t) {
            i.d(e, t, (function () {
                return a[t]
            }))
        }(n);
        i("36dd");
        var r, d = i("f0c5"),
            s = Object(d["a"])(a["default"], o["b"], o["c"], !1, null, "00140dbc", null, !1, o["a"], r);
        e["default"] = s.exports
    }, c7ed: function (t, e, i) {
        var o = i("a42b");
        "string" === typeof o && (o = [[t.i, o, ""]]), o.locals && (t.exports = o.locals);
        var a = i("4f06").default;
        a("4e1cf204", o, !0, {sourceMap: !1, shadowMode: !1})
    }, c84d: function (t, e, i) {
        "use strict";
        i.r(e);
        var o = i("773f"), a = i.n(o);
        for (var n in o) "default" !== n && function (t) {
            i.d(e, t, (function () {
                return o[t]
            }))
        }(n);
        e["default"] = a.a
    }, cb09: function (t, e, i) {
        "use strict";
        i.r(e);
        var o = i("cc33"), a = i("a247");
        for (var n in a) "default" !== n && function (t) {
            i.d(e, t, (function () {
                return a[t]
            }))
        }(n);
        i("3848");
        var r, d = i("f0c5"),
            s = Object(d["a"])(a["default"], o["b"], o["c"], !1, null, "1b741bef", null, !1, o["a"], r);
        e["default"] = s.exports
    }, cc33: function (t, e, i) {
        "use strict";
        var o;
        i.d(e, "b", (function () {
            return a
        })), i.d(e, "c", (function () {
            return n
        })), i.d(e, "a", (function () {
            return o
        }));
        var a = function () {
            var t = this, e = t.$createElement, i = t._self._c || e;
            return t.show ? i("v-uni-view", {
                staticClass: "u-loading",
                class: "circle" == t.mode ? "u-loading-circle" : "u-loading-flower",
                style: [t.cricleStyle]
            }) : t._e()
        }, n = []
    }, d411: function (t, e, i) {
        "use strict";
        i.r(e);
        var o = i("317f"), a = i("d71a");
        for (var n in a) "default" !== n && function (t) {
            i.d(e, t, (function () {
                return a[t]
            }))
        }(n);
        i("67f1");
        var r, d = i("f0c5"),
            s = Object(d["a"])(a["default"], o["b"], o["c"], !1, null, "2711fa7a", null, !1, o["a"], r);
        e["default"] = s.exports
    }, d71a: function (t, e, i) {
        "use strict";
        i.r(e);
        var o = i("1122"), a = i.n(o);
        for (var n in o) "default" !== n && function (t) {
            i.d(e, t, (function () {
                return o[t]
            }))
        }(n);
        e["default"] = a.a
    }, ddd9: function (t, e, i) {
        var o = i("6203");
        "string" === typeof o && (o = [[t.i, o, ""]]), o.locals && (t.exports = o.locals);
        var a = i("4f06").default;
        a("f4583ada", o, !0, {sourceMap: !1, shadowMode: !1})
    }
}]);