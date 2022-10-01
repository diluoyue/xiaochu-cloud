gid = $("#app").attr('data');
var vm = new Vue({
    el: '#app', data: {
        Data: [], sum: 0, page: 1, name: '', gid: gid, limit: 10,
    }, methods: {
        DeleteCoupons() {
            gid = this.gid;
            if (gid == '') {
                var gid = '';
                var tpis = '请选择删除模式，当前已经选择全站优惠券?';
            } else {
                var gid = gid;
                var tpis = '请选择删除模式，当前已选择商品：[ ' + gid + ' ] ？';
            }
            let _this = this;
            layer.alert(tpis + '<hr><font color=red>用户领取/使用优惠券的限制是根据优惠券列表计算，如果删除了优惠券，可能会造成已经达到上限的用户可以再次领取/使用优惠券！</font>', {
                title: '温馨提示', icon: 3, btn: ['删除全部', '删除未兑换', '已兑换未使用', '已兑换已使用', '取消'], btn1: function () {
                    _this.DeleteCouponsAjax(1, gid);
                }, btn2: function () {
                    _this.DeleteCouponsAjax(2, gid);
                }, btn3: function () {
                    _this.DeleteCouponsAjax(3, gid);
                }, btn4: function () {
                    _this.DeleteCouponsAjax(4, gid);
                }
            });
        }, DeleteCouponsAjax(type, gid) {
            let _this = this;
            layer.open({
                title: '操作确认',
                content: '是否要删除这些优惠券?,删除后将无法恢复,请谨慎操作！',
                icon: 2,
                btn: ['确认删除', '取消'],
                btn1: function (layero, index) {
                    let ist = layer.msg('优惠券删除中...', {
                        icon: 16, time: 9999999
                    });
                    $.ajax({
                        type: 'post', url: 'ajax.php?act=CouponDeletet', data: {
                            type: type, gid: gid,
                        }, dataType: 'json', success: function (data) {
                            layer.close(ist);
                            if (data.code >= 1) {
                                _this.search('');
                                layer.msg(data.msg, {
                                    icon: 1
                                });
                            } else layer.msg(data.msg, {
                                icon: 2
                            });
                        }
                    })
                }
            });
        }, CouponExport() {
            gid = this.gid;
            if (gid == '') {
                var gid = '';
                var tpis = '请选择导出模式，当前已经选择全站优惠券?';
            } else {
                var gid = gid;
                var tpis = '请选择导出模式，当前已选择商品：[ ' + gid + ' ] ？';
            }
            layer.alert(tpis, {
                title: '温馨提示', btn: ['全部', '未兑换', '已兑换', '取消'], btn1: function (layero, index) {
                    location.href = 'ajax.php?act=CouponExport&gid=' + gid + '&type=1';
                    layer.closeAll();
                }, btn2: function (layero, index) {
                    location.href = 'ajax.php?act=CouponExport&gid=' + gid + '&type=2';
                    layer.closeAll();
                }, btn3: function (layero, index) {
                    location.href = 'ajax.php?act=CouponExport&gid=' + gid + '&type=3';
                    layer.closeAll();
                }
            });
        }, deletes(id) {
            let _this = this;
            layer.open({
                title: '操作确认',
                content: '是否要删除ID为[' + id + ']的优惠券?,删除后将无法恢复！',
                icon: 3,
                btn: ['确认删除', '取消'],
                btn1: function (layero, index) {
                    let ist = layer.msg('优惠券[' + id + ']删除中', {
                        icon: 16, time: 9999999
                    });
                    $.ajax({
                        type: 'post', url: 'ajax.php?act=CouponDeletet', data: {
                            id: id,
                        }, dataType: 'json', success: function (data) {
                            layer.close(ist);
                            if (data.code >= 1) {
                                _this.search('');
                                layer.msg(data.msg, {
                                    icon: 1
                                });
                            } else layer.msg(data.msg, {
                                icon: 2
                            });
                        }
                    })
                }
            });
        }, search(name) {
            this.name = name;
            this.sum = 0;
            this.page = 1;
            layer.closeAll();
            this.Ajax();
        }, Ajax() {
            let is = layer.msg('优惠券列表获取中...', {
                icon: 16, time: 9999999
            });
            let _this = this;
            $.ajax({
                type: 'post', url: 'ajax.php?act=CouponList', data: {
                    page: this.page, name: this.name, gid: this.gid, limit: this.limit,
                }, dataType: 'json', success: function (data) {
                    layer.close(is);
                    if (data.code >= 0) {
                        _this.Data = data.data;
                        if (_this.sum == 0) {
                            layui.use('laypage', function () {
                                var laypage = layui.laypage;
                                laypage.render({
                                    elem: 'paging',
                                    count: data.count,
                                    theme: '#641ec6',
                                    limit: _this.limit,
                                    limits: [1, 10, 20, 30, 50, 100, 200],
                                    groups: 3,
                                    first: '首页',
                                    last: '尾页',
                                    prev: '上一页',
                                    next: '下一页',
                                    skip: true,
                                    layout: ['count', 'page', 'prev', 'next', 'limit', 'limits'],
                                    jump: function (obj, first) {
                                        _this.page = obj.curr;
                                        _this.limit = obj.limit;
                                        if (!first) {
                                            _this.Ajax();
                                        }
                                    }
                                });
                            });
                        }
                        _this.sum = data.count;
                    } else {
                        if (_this.name != '') {
                            _this.Data = [];
                            layer.msg('什么都没搜索到~', {
                                icon: 2
                            });
                        } else {
                            layer.msg(data.msg, {
                                icon: 2
                            });
                        }

                    }
                    _this.load == false;
                }, error: function () {
                    layer.close(is);
                    layer.alert('列表获取失败！');
                }
            });
        }
    }, mounted() {
        this.Ajax();
    }
});

layui.use('form', function () {
    var form = layui.form;
    form.on('select(goods)', function (data) {
        if (data.value != '') {
            location.href = 'admin.coupon.list.php?gid=' + data.value;
        } else {
            location.href = 'admin.coupon.list.php';
        }
    });
});

function search() {
    layer.prompt({
        formType: 3, value: '', title: '可输入券码/订单号/ID/IP/名称',
    }, function (value, index, elem) {
        vm.search(value);
    });
}