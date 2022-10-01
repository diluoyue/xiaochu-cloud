(function () {
    var defaults = {baseUrl: window.baseUrl, siteUrl: window.siteUrl};
    var core = {options: {}};
    core.csrf_param = document.getElementsByName('csrf-param')[0].content;
    core.init = function (options) {
        this.options = $.extend({}, defaults, options || {})
    };
    core.toQueryPair = function (key, value) {
        if (typeof value == 'undefined') {
            return key
        }
        return key + '=' + encodeURIComponent(value === null ? '' : String(value))
    };
    core.number_format = function (number, fix) {
        var fix = arguments[1] ? arguments[1] : 2;
        var fh = ',';
        var jg = 3;
        var str = '';
        number = number.toFixed(fix);
        zsw = number.split('.')[0];
        xsw = number.split('.')[1];
        zswarr = zsw.split('');
        for (var i = 1; i <= zswarr.length; i++) {
            str = zswarr[zswarr.length - i] + str;
            if (i % jg == 0) {
                str = fh + str;
            }
        }
        str = (zsw.length % jg == 0) ? str.substr(1) : str;
        zsw = str + '.' + xsw;
        return zsw
    };
    core.toQueryString = function (obj) {
        var ret = [];
        for (var key in obj) {
            key = encodeURIComponent(key);
            var values = obj[key];
            if (values && values.constructor == Array) {
                var queryValues = [];
                for (var i = 0, len = values.length, value; i < len; i++) {
                    value = values[i];
                    queryValues.push(this.toQueryPair(key, value))
                }
                ret = ret.concat(queryValues)
            } else {
                ret.push(this.toQueryPair(key, values))
            }
        }
        return ret.join('&')
    };
    core.getUrl = function (routes, params, full) {
        var url = this.options.baseUrl.replace('ROUTES', routes);
        if (params) {
            if (typeof (params) == 'object') {
                url += "&" + this.toQueryString(params)
            } else if (typeof (params) == 'string') {
                url += "&" + params
            }
        }
        return full ? this.options.siteUrl + 'app/' + url : url
    };
    core.json = function (routes, args, callback, hasloading, ispost, type) {
        var url;
        if (typeof args === "function") {
            callback = args;
            args = {};
        }
        url = ispost ? this.getUrl(routes) : this.getUrl(routes, args);
        var op = {
            url: url, type: ispost ? 'post' : 'get', dataType: type || 'json', cache: false, beforeSend: function () {
                if (hasloading) {
                    FoxUI.loader.show('mini')
                }
            }, error: function (a) {
                /*alert(JSON.stringify(a));*/
                if (hasloading) {
                    FoxUI.loader.hide()
                }
            }
        };
        if (args && ispost) {
            args[this.csrf_param] = this.csrf_token;
            op.data = args
        }
        if (callback) {
            op.success = function (data) {
                if (hasloading) {
                    FoxUI.loader.hide()
                }
                callback(data)
            }
        }
        $.ajax(op)
    };
    core.post = function (routes, args, callback, hasloading) {
        this.json(routes, args, callback, hasloading, true)
    };
    core.post_html = function (routes, args, callback, hasloading) {
        this.json(routes, args, callback, hasloading, true, 'html')
    };
    core.tpl = function (containerid, templateid, data, append) {
        if (typeof append === undefined) {
            append = false
        }
        var html = tpl(templateid, data);
        if (append) {
            $(containerid).append(html)
        } else {
            $(containerid).html(html)
        }
        setTimeout(function () {
            $(containerid).closest('.fui-content').lazyload('render')
        }, 10)
    };
    core.getNumber = function (str) {
        str = $.trim(str);
        if (str == '') {
            return 0
        }
        return parseFloat(str.replace(',', ''))
    };

    core.wechatPay = function (url, date, callback) {
        FoxUI.loader.show('mini');
        this.post(url, date, function (res) {
            FoxUI.loader.hide();
            if (res.error === -2) {
                return initQuick();
            }
            if (res.error !== 0) {
                return FoxUI.toast.show(res.message, null, 2000);
            }

            function onBridgeReady() {
                WeixinJSBridge.invoke(
                    'getBrandWCPayRequest', {
                        "appId": res.pay_info.appId,     //公众号名称，由商户传入
                        "timeStamp": res.pay_info.timeStamp,         //时间戳，自1970年以来的秒数
                        "nonceStr": res.pay_info.nonceStr, //随机串
                        "package": res.pay_info.package,
                        "signType": res.pay_info.signType,         //微信签名方式：
                        "paySign": res.pay_info.paySign //微信签名
                    },
                    function (res1) {
                        callback(res1, res);
                    });
            }

            if (typeof WeixinJSBridge == "undefined") {
                if (document.addEventListener) {
                    document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                } else if (document.attachEvent) {
                    document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                    document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                }
            } else {
                onBridgeReady();
            }
        });
    };

    //支付宝支付
    core.alipay = function (url, date, callback) {
        FoxUI.loader.show('mini');
        this.post_html(url, date, function (res) {
            FoxUI.loader.hide();
            if (typeof res === 'string' && res.indexOf('{"error":') !== -1) {
                res = JSON.parse(res);
            }

            var is_object = typeof res === 'object';

            if (is_object) {
                if (res.error === -2) {
                    return initQuick();
                }
                if (res.error !== 0) {
                    return FoxUI.toast.show(res.message, null, 2000);
                }
            }
            callback(res, is_object);
        });
    };


    core.showIframe = function (url) {
        var if_w = "100%";
        var if_h = $(document.body).height();
        $("<iframe width='" + if_w + "' height='" + if_h + "' id='mainFrame' name='mainFrame' style='position:absolute;z-index:4;'  frameborder='no' marginheight='0' marginwidth='0' ></iframe>").prependTo('body');
        var st = document.documentElement.scrollTop || document.body.scrollTop;
        var sl = document.documentElement.scrollLeft || document.body.scrollLeft;
        var ch = document.documentElement.clientHeight;
        var cw = document.documentElement.clientWidth;
        var objH = $("#mainFrame").height();
        var objW = $("#mainFrame").width();
        var objT = Number(st) + (Number(ch) - Number(objH)) / 2;
        var objL = Number(sl) + (Number(cw) - Number(objW)) / 2;
        $("#mainFrame").css('left', objL);
        $("#mainFrame").css('top', objT);
        $("#mainFrame").attr("src", url)
    };
    core.getDistanceByLnglat = function (lng1, lat1, lng2, lat2) {
        function rad(d) {
            return d * Math.PI / 180.0
        }

        var rad1 = rad(lat1), rad2 = rad(lat2);
        var a = rad1 - rad2, b = rad(lng1) - rad(lng2);
        var s = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin(a / 2), 2) + Math.cos(rad1) * Math.cos(rad2) * Math.pow(Math.sin(b / 2), 2)));
        s = s * 6378137.0;
        s = Math.round(s * 10000) / 10000000;
        return s
    };
    core.showImages = function (imgClass) {
        var ua = navigator.userAgent.toLowerCase();
        var isWX = ua.match(/MicroMessenger/i) == "micromessenger";
        var z = [];
        $(imgClass).each(function () {
            var img = $(this).attr("data-lazy");
            z.push(img ? img : $(this).attr("src"));
        });
        var current;
        if (isWX) {
            $(imgClass).unbind('click').click(function (e) {
                e.preventDefault();
                var startingIndex = $(imgClass).index($(e.currentTarget));
                var current = null;
                $(imgClass).each(function (B, A) {
                    if (B === startingIndex) {
                        current = $(A).attr("data-lazy") ? $(A).attr("data-lazy") : $(A).attr("src");
                    }
                });
                WeixinJSBridge.invoke("imagePreview", {
                    current: current,
                    urls: z
                });
            });
        }
    };
    core.ish5app = function () {
        var userAgent = navigator.userAgent;
        if (userAgent.indexOf('CK 2.0') > -1) {
            return true;
        }
        return false;
    };
    core.isWeixin = function () {
        var ua = navigator.userAgent.toLowerCase();
        var isWX = ua.match(/MicroMessenger/i) == "micromessenger";
        return isWX;
    };

    core.isUrl = function (str) {
        return /^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/.test($.trim(str))
    };
    core.isInt = function (str) {
        return /^[-\+]?\d+$/.test($.trim(str))
    };
    core.isUserID = function (str) {
        return /^\s*[A-Za-z0-9_-]{6,20}\s*$/.test($.trim(str))
    };
    core.isTel = function (str) {
        return /^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,14}$/.test($.trim(str))
    };
    core.isMobile = function (str) {
        return $.trim(str) !== '' && /^1[3|4|5|6|7|8|9][0-9]\d{8}$/.test($.trim(str))
    };
    core.isEmpty = function (str) {
        return $.trim(str) == '' || str == undefined
    };

    core.init();

    window.core = core;

    /*商品组*/
    if ($('.fui-goods-group').length > 0) {
        var resizeImages = function () {
            $('.fui-goods-group img').not(".exclude").each(function () {
                $(this).height($(this).width());
            })
        };
        window.onload = resizeImages;
        window.resize = resizeImages;
    }

    /*页面空白处理*/
    if ($('.fui-page').length) {
        $('.fui-page').closest('.fui-page').css('height', $('.fui-page').closest('.fui-page').height() + 'px');
    }
    // FoxUI.init();
})();
