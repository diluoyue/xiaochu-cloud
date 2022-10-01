var vm = new Vue({
    el: '#app', data: {
        qqpay: [], wxpay: [], alipay: [], PayConQQ: 'epay', PayConWX: -1, PayConZFB: -1, PayRates: 0, type: false
    }, watch: {
        "PayRates": {
            handler(value, tv) {
                if (this.PayRates == tv || this.type === false || tv === false || value === '') {
                    return false;
                }
                $.ajax({
                    type: "POST", url: './ajax.php?act=config_set', data: {
                        PayRates: value - 0
                    }, dataType: "json"
                });
            }
        },
    }, methods: {
        /**
         * @param {Object} Data 配置数据
         * @param {Object} id 应用标识
         * @param {Object} type 类型，0:qq,1:wx,3:zfb
         */
        Save(Data, id, type) {
            console.log(Data);
            let _this = this;
            $.ajax({
                type: 'POST', url: 'ajax.php?act=SavePayData', data: {
                    data: Data, id: id, type: type
                }, async: true, dataType: 'json', success: function (res) {
                    if (res.code >= 0) {
                        _this.Ajax();
                        layer.alert(res.msg, {
                            icon: 1
                        });
                    } else layer.alert(res.msg, {
                        icon: 2
                    });
                }, error: function () {
                    layer.alert('操作失败！');
                }
            });
        }, Cut(type, id) {
            let _this = this;

            let index = layer.confirm('是否要切换?', {
                icon: 3, title: '温馨提示'
            }, function (index) {
                layer.close(index);
                var index2 = layer.load(1, {
                    time: 9999999999
                });
                $.ajax({
                    type: 'POST', url: 'ajax.php?act=PaySet', data: {
                        type: type, id: id
                    }, async: true, dataType: 'json', success: function (res) {
                        layer.close(index2);
                        if (res.code >= 0) {
                            _this.Ajax();
                        } else layer.alert(res.msg);
                    }, error: function () {
                        layer.close(index2);
                        layer.alert('操作失败！');
                    }
                });
            });
        }, Ajax() {
            let _this = this;
            $.ajax({
                type: 'POST', url: 'ajax.php?act=PayData', async: true, dataType: 'json', success: function (res) {
                    if (res.code >= 0) {
                        _this.renderer(res.data);
                        _this.PayConQQ = res.PayConQQ;
                        _this.PayConWX = res.PayConWX;
                        _this.PayConZFB = res.PayConZFB;
                        _this.PayRates = res.PayRates;
                        _this.$forceUpdate();
                        _this.type = true;
                    } else layer.alert(res.msg);
                }, error: function () {
                    layer.alert('用户数据获取失败！');
                }
            });
        }, /**
         * 将数据渲染为视图数据
         */
        renderer(data) {
            let _this = this;
            /**
             * 易支付选择渲染
             */

            _this.qqpay = [];
            _this.wxpay = [];
            _this.alipay = [];

            for (let i = 0; i < data.length; i++) {
                if (data[i].InputData === undefined || data[i].InputData === null) {
                    data[i].InputData = [];
                }
                if (data[i].type[0] == 1) {
                    let Arr = new Array({
                        id: data[i].id, name: data[i].name, input: data[i].input, InputData: data[i].InputData[0] //载入QQ配置
                    });
                    _this.qqpay.push(Arr[0]);
                }

                if (data[i].type[1] == 1) {
                    let Arr = new Array({
                        id: data[i].id, name: data[i].name, input: data[i].input, InputData: data[i].InputData[1] //载入wx配置
                    });
                    _this.wxpay.push(Arr[0]);
                }

                if (data[i].type[2] == 1) {
                    let Arr = new Array({
                        id: data[i].id, name: data[i].name, input: data[i].input, InputData: data[i].InputData[2] //载入zfb配置
                    });
                    _this.alipay.push(Arr[0]);
                }
            }
            /**
             * 编辑器渲染
             */
            _this.$forceUpdate();
        }
    }, mounted() {
        this.Ajax();
    }
});

layui.use('util', function () {
    var util = layui.util;

    //执行
    util.fixbar({
        bar1: false
    });
});