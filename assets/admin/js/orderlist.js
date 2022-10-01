const App = Vue.createApp({
    data() {
        return {
            Data: [],
            page: 1,
            limit: 10,
            name: $("#App").attr('name'),
            type: -1,
            count: 0,
            date: {},
            state: '',
            gid: $("#App").attr('gid'),
            uid: $("#App").attr('uid'),
        }
    }, watch: {
        state(val, ts) {
            if (val === ts) return false;
            this.initialization(this.name, this.limit, this.date, val);
        }, name(val, ts) {
            if (val === ts) return false;
            this.initialization(val, this.limit, this.date, this.state);
        }, date: {
            handler(val) {
                this.initialization(this.name, this.limit, val, this.state);
            }, deep: true, immediate: false,
        }
    }, methods: {
        OrderQueue() {
            layer.open({
                type: 2,
                shade: false,
                title: '订单队列',
                area: ['96vw', '96vh'],
                maxmin: true,
                content: './admin.order.queue.php',
                zIndex: layer.zIndex,
                success: function (layero) {
                    layer.setTop(layero);
                }
            });
        }, BatchOperation(type) {
            const arr = order.pitch_on();
            console.log(arr);
            if (arr.length == 0) {
                layer.alert('请最少选择一条订单！', {icon: 2});
                return;
            }
            switch (type) {
                case 5: //批量退款
                    layer.prompt({
                        formType: 3, value: '', title: '请填写退款百分比:0 - 100',
                    }, function (value, index) {
                        if (value < 0 || value > 100) {
                            layer.alert('退款百分比填写错误，请填写 0(包括0) 到 100(包括100) 之间的值，会根据订单付款金额进行退款！，如果是用户订单，则退款到账户，并且扣除对应的提成，如果是游客，则仅扣除对应的提成！', {
                                icon: 2
                            });
                            return false;
                        }
                        layer.open({
                            title: '温馨提示',
                            content: '是否要为当前选择的这' + arr.length + '条订单进行退款？<br>当前退款百分比为' + value + '%，100元的订单，将会退款' + '约' + (100 * (value / 100)) + '元！<br>如果是游客订单，则仅收回订单提成！',
                            icon: 3,
                            btn: ['确定', '取消'],
                            btn1: function () {
                                order.ajax_state_all(type, arr, value);
                            }
                        })

                        layer.close(index);
                    });
                    break;
                case 8: //批量补单！
                    layer.alert('是否执行此操作？', {
                        title: '批量补单确认', icon: 3, btn: ['确认', '取消'], btn1: function () {
                            layer.msg('即将开始执行批量补单操作！<br>本次共需执行' + arr.length + '条数据！', {
                                icon: 16, time: 9999999
                            });
                            order.order_all(arr);
                        }
                    });
                    break;
                case 9: //批量删除订单！
                    layer.alert('是否执行此操作？', {
                        title: '批量删除订单确认', icon: 3, btn: ['确认', '取消'], btn1: function () {
                            layer.msg('正在批量删除订单，本次共删除' + arr.length + '条订单数据！', {
                                icon: 16, time: 9999999
                            });
                            order.order_delete_all(arr);
                        }
                    });
                    break;
                default:
                    layer.alert('是否执行此操作？', {
                        title: '订单批量操作确认', icon: 3, btn: ['确认', '取消'], btn1: function () {
                            order.ajax_state_all(type, arr);
                        }
                    });
                    break;
            }
        }, OrderSet(id, did, money = 0) {
            if (id == 5) {
                console.log(money);
                layer.prompt({
                    formType: 3, value: money, title: '请输入退款金额/积分', btn: ['确定退款', '取消退款'],
                }, function (value, index) {
                    order.ajax_state(id, did, value);
                    layer.close(index);
                });
            } else {
                layer.alert('是否执行此操作？', {
                    title: '订单状态修改确认', icon: 3, btn: ['确认', '取消'], btn1: function () {
                        order.ajax_state(id, did);
                    }
                })
            }
        }, OrderList() {
            let is = layer.msg('订单载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=OrderList', data: {
                    page: App.page,
                    limit: App.limit,
                    name: App.name,
                    date: App.date,
                    state: App.state,
                    gid: App.gid,
                    uid: App.uid,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.type = 1;
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, logisticsSet(id, content) {
            const area = [$(window).width() > 480 ? '480px' : '100%'];
            layer.prompt({
                formType: 2, value: content, title: '实物商品专用,其他无视！', area: area, id: 're_' + id, success: function () {
                    $("#re_" + id + ' textarea').attr('placeholder', '填写格式：物流单号|快递名称 ，其中名称可留空，只填写物流单号，如：xxxxxxxxxx，会自动识别！');
                }
            }, function (value) {
                var index = layer.msg('物流单号修改中...', {
                    icon: 16, time: 9999999
                });
                $.ajax({
                    type: "post", url: "ajax.php?act=order_logistics", data: {
                        id: id, txt: value
                    }, dataType: "json", success: function (data) {
                        if (data.code > 0) {
                            layer.alert(data.msg, {
                                icon: 1, end: function () {
                                    App.OrderList();
                                }
                            });
                        } else {
                            layer.msg(data.msg, {
                                icon: 2
                            });
                        }
                    }, error: function () {
                        layer.alert('请求成功,无返回数据,可能是列表缓存更新中,已帮您更新页面数据！', {icon: 2});
                        App.OrderList();
                        layer.close(index);
                    }
                });
            });
        }, remarkSet(id, content) {
            const area = [$(window).width() > 480 ? '480px' : '100%'];
            layer.prompt({
                formType: 2, value: content, title: '订单处理结果,备注(用户查询订单的时候可以看到)', area: area,
            }, function (value) {
                var index = layer.msg('备注修改中...', {
                    icon: 16, time: 9999999
                });
                $.ajax({
                    type: "post", url: "ajax.php?act=order_remark", data: {
                        id: id, txt: value
                    }, dataType: "json", success: function (data) {
                        if (data.code > 0) {
                            layer.alert(data.msg, {
                                icon: 1, end: function () {
                                    App.OrderList();
                                }
                            });
                        } else {
                            layer.msg(data.msg, {
                                icon: 2
                            });
                        }
                    }, error: function () {
                        layer.alert('请求成功,无返回数据,可能是列表缓存更新中,已帮您更新页面数据！', {icon: 2});
                        App.OrderList();
                        layer.close(index);
                    }
                });
            });
        }, order_set(id, val, field, name = '', type = 1) {
            var area = [$(window).width() > 480 ? '480px' : '100%'];
            layer.prompt({
                formType: (type === 1 ? 2 : 3),
                value: val,
                title: '订单' + id + '的' + (name === '' ? field + '参数' : name),
                area: area,
            }, function (value) {
                layer.closeAll();
                if (val == value) {
                    layer.msg('无改动');
                    return;
                }
                var index = layer.msg('修改中...', {
                    icon: 16, time: 9999999
                });
                $.ajax({
                    type: "post", url: "ajax.php?act=order_set", data: {
                        id: id, value: value, field: field, name: name,
                    }, dataType: "json", success: function (data) {
                        if (data.code > 0) {
                            layer.alert(data.msg, {
                                icon: 1, end: function () {
                                    App.OrderList();
                                }
                            });
                        } else {
                            layer.msg(data.msg, {
                                icon: 2
                            });
                        }
                    }, error: function () {
                        layer.alert('请求成功,无返回数据,可能是列表缓存更新中,已帮您更新页面数据！', {icon: 2});
                        App.OrderList();
                        layer.close(index);
                    }
                });
            });
        }, set_order(id, content) {
            const area = [$(window).width() > 480 ? '480px' : '100%'];
            layer.prompt({
                formType: 2, value: JSON.stringify(content), title: '修改订单[ ' + id + ' ]下单数据', area: area,
            }, function (value) {
                layer.closeAll();
                const index = layer.msg('修改中...', {
                    icon: 16, time: 9999999
                });
                $.ajax({
                    type: "post", url: "ajax.php?act=set_order", data: {
                        id: id, input: value
                    }, dataType: "json", success: function (data) {
                        if (data.code > 0) {
                            layer.alert(data.msg, {
                                icon: 1, end: function () {
                                    App.OrderList();
                                }
                            });
                        } else {
                            layer.msg(data.msg, {
                                icon: 2
                            });
                        }
                    }, error: function () {
                        layer.alert('请求成功,无返回数据,可能是列表缓存更新中,已帮您更新页面数据！', {icon: 2});
                        App.OrderList();
                        layer.close(index);
                    }
                });
            });
        }, ReplacementOrder(id) {
            layer.open({
                title: '温馨提示',
                content: '由于这是一个旧版本程序订单，无法确定订单对接状态，请主动选择是否需要对接补单？<br>订单ID：' + id,
                icon: 3,
                btn: ['补单', '取消'],
                btn1: function () {
                    App.order_dispose(id);
                }
            })
        }, order_dispose(id, type = 1, arr = null, i) {
            var index = layer.msg('订单' + id + '补单中...', {
                icon: 16, time: 9999999
            });
            if (id === '' || id === undefined) return;
            $.ajax({
                type: "post", url: "ajax.php?act=replenishment", data: {
                    id: id
                }, dataType: "json", success: function (data) {
                    if (type !== 1) {
                        order.order_all(arr, ++i);
                        layer.alert('订单：' + id + '补单状态：<br>' + data.msg, {
                            anim: Math.ceil(Math.random() * 6),
                            icon: (data.code > 0 ? 1 : 2),
                            shade: 0.5,
                            title: '批量补单！'
                        });
                        return true;
                    }
                    if (data.code > 0) {
                        layer.alert(data.msg, {
                            icon: 1, end: function () {
                                App.OrderList();
                            }
                        });
                    } else {
                        layer.msg(data.msg, {
                            icon: 2
                        });
                        return true;
                    }
                }, error: function () {
                    layer.alert('请求成功,无返回数据,可能是列表缓存更新中,已帮您更新页面数据！', {icon: 2});
                    App.OrderList();
                    layer.close(index);
                }
            });
        }, initialization(name = -1, limit = 10, date = {}, state = '') {
            this.page = 1;
            this.limit = (limit === 10 ? this.limit : limit);
            this.name = (name === -1 ? this.name : name);
            this.type = -1;
            this.date = date;
            this.state = state;

            layui.use('laydate', function () {
                var laydate = layui.laydate;
                laydate.render({
                    elem: '#OrderDate1',
                    theme: '#393D49',
                    type: 'datetime',
                    format: 'yyyy-MM-dd HH:mm:ss',
                    done: function (value) {
                        App.date[0] = value;
                    }
                });
                laydate.render({
                    elem: '#OrderDate2',
                    theme: '#393D49',
                    type: 'datetime',
                    format: 'yyyy-MM-dd HH:mm:ss',
                    done: function (value) {
                        App.date[1] = value;
                    }
                });
            });

            layui.use('laypage', function () {
                var laypage = layui.laypage;
                $.ajax({
                    type: "POST", url: './main.php?act=OrderCount', data: {
                        name: App.name, date: App.date, state: App.state, gid: App.gid, uid: App.uid,
                    }, dataType: "json", success: function (res) {
                        if (res.code == 1) {
                            App.count = res.count;
                            laypage.render({
                                elem: 'Page',
                                count: res.count,
                                theme: '#641ec6',
                                limit: App.limit,
                                limits: [1, 10, 20, 30, 50, 100, 200],
                                groups: 3,
                                first: '首页',
                                last: '尾页',
                                prev: '上一页',
                                next: '下一页',
                                skip: true,
                                layout: ['count', 'page', 'prev', 'next', 'limit', 'limits'],
                                jump: function (obj) {
                                    App.page = obj.curr;
                                    App.limit = obj.limit;
                                    App.OrderList();
                                }
                            });
                        } else {
                            layer.alert(res.msg, {
                                icon: 2
                            });
                        }
                    }, error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            });
        },
    }
}).mount('#App');

App.initialization();

const order = {
    select_id: function (id) {
        if ($('.box_' + id).prop('checked') == true) {
            $('.box_' + id).attr("checked", true);
            $('.box_' + id).prop("checked", true);
        } else {
            $('.box_' + id + ',#list_all').attr('checked', false);
            $('.box_' + id + ',#list_all').prop("checked", false);
        }
    }, select_all: function () {
        if ($('#list_all').prop('checked') == true) {
            $(".list_box,#list_all").attr("checked", true);
            $(".list_box,#list_all").prop("checked", true);
        } else {
            $(".list_box,#list_all").attr('checked', false);
            $(".list_box,#list_all").prop("checked", false);
        }
    }, pitch_on: function () {
        var amount = [];
        $("input[type='checkbox']").each(function () {
            if ($(this).is(":checked")) {
                if ($(this).val() != 'true' && $(this).val() != '') amount.push($(this).val());
            }
        });
        return amount;
    }, ajax_state: function (id, did, money = 0) {
        var index = layer.msg('修改中', {
            icon: 16, time: 9999999
        });
        $.ajax({
            type: "post", url: "ajax.php?act=ChangeOrders", data: {
                id: id, did: did, money: money
            }, dataType: "json", success: function (data) {
                if (data.code > 0) {
                    layer.alert(data.msg, {
                        icon: 1, end: function () {
                            App.OrderList();
                        }
                    });
                } else {
                    layer.msg(data.msg, {
                        icon: 2
                    });
                }
            }, error: function () {
                layer.alert('请求成功,无返回数据,可能是列表缓存更新中,已帮您更新页面数据！', {icon: 2});
                App.OrderList();
                layer.close(index);
            }
        });
    }, details: function (id) {
        var index = layer.msg('订单查询中...', {
            icon: 16, time: 9999999
        });
        $.ajax({
            type: "post", url: "ajax.php?act=order_details", data: {
                id: id
            }, dataType: "json", success: function (res) {
                layer.close(index);
                if (res.code == 1) {
                    data = res.data;
                    let content = '';
                    if (data.ApiSn !== -1) {
                        content += '<p style="text-align: center;font-size: 1.3em;color: seagreen;margin-bottom: 0;">订单实时状态</p><table class="layui-table layui-text" lay-size="sm" lay-skin="row"><colgroup>\n' + '    <col width="30%">\n' + '    <col width="70%">' + '    <col>\n' + '  </colgroup><thead>\n' + '    <tr>\n' + '      <th>类型</th>\n' + '      <th>实时状态</th>' + '    </tr> \n' + '  </thead><tbody>';
                        content += '<tr>\n' + '      <td>订单状态</td>\n' + '      <td>' + data.ApiState + '</td>' + '    </tr>';
                        content += '<tr>\n' + '      <td>初始数量</td>\n' + '      <td>' + data.ApiInitial + '</td>' + '    </tr>';
                        content += '<tr>\n' + '      <td>下单数量</td>\n' + '      <td>' + data.ApiNum + '</td>' + '    </tr>';
                        content += '<tr>\n' + '      <td>当前数量</td>\n' + '      <td>' + data.ApiPresent + '</td>' + '    </tr>';
                        content += '<tr>\n' + '      <td>提交时间</td>\n' + '      <td>' + data.ApiTime + '</td>' + '    </tr>';
                        if (data.ApiError !== '无') {
                            content += '<tr>\n' + '      <td>异常信息</td>\n' + '      <td>' + data.ApiError + '</td>' + '    </tr>';
                        }
                        content += '</tbody></table><hr>';
                    }

                    content += '<table class="layui-table layui-text" lay-size="sm" lay-skin="row"><colgroup>\n' + '    <col width="30%">\n' + '    <col width="70%">' + '    <col>\n' + '  </colgroup><thead>\n' + '    <tr>\n' + '      <th>类型</th>\n' + '      <th>状态</th>' + '    </tr> \n' + '  </thead><tbody>';

                    let DataAR = {
                        'name': {
                            'name': '商品名称', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'id': {
                            'name': '订单编号', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'order': {
                            'name': '本地订单', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'order_id': {
                            'name': '货源订单', 'or': '-1', 'yes': '无', 'no': data.order_id, 'units': '',
                        }, 'trade_no': {
                            'name': '支付订单', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'muid': {
                            'name': '站点编号', 'or': '-1', 'yes': '主站', 'no': data.muid, 'units': '',
                        }, 'uid': {
                            'name': '用户编号', 'or': '-1', 'yes': '游客', 'no': data.uid, 'units': '',
                        }, 'docking': {
                            'name': '对接状态',
                            'or': '-1',
                            'yes': '跟随订单',
                            'no': (data.docking == 1 ? '对接成功' : (data.docking == 2 ? '对接失败' : '待提交对接')),
                            'units': '',
                        }, 'state': {
                            'name': '订单状态', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'num': {
                            'name': '购买份数', 'or': '', 'yes': '', 'no': '', 'units': '份',
                        }, 'type': {
                            'name': '付款方式', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'input': {
                            'name': '下单信息', 'or': '-1', 'yes': '', 'no': data.input.join('<br>'), 'units': '',
                        }, 'return': {
                            'name': '对接返回', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'remark': {
                            'name': '订单备注', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'logistics': {
                            'name': '物流单号', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'finishtime': {
                            'name': '完成时间', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'addtiem': {
                            'name': '创建时间', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'ip': {
                            'name': '下单IP', 'or': '', 'yes': '', 'no': '', 'units': '',
                        }, 'take': {
                            'name': '收货状态', 'or': '1', 'yes': '未收货', 'no': '已收货', 'units': '',
                        },
                    };


                    content += '<tr>\n' + '      <td style="color:red">支付金额</td>\n' + '      <td style="color:red">' + data.price + (data.type == '积分兑换' ? '积分' : '元') + '</td>' + '    </tr>';
                    content += '<tr>\n' + '      <td style="color:red">订单成本</td>\n' + '      <td style="color:red">' + data.money + '元</td>' + '    </tr>';
                    content += '<tr>\n' + '      <td style="color:red">对接余额</td>\n' + '      <td style="color:red">' + data.user_rmb + '元</td>' + '    </tr>';

                    if (data.coupon != -1) {
                        content += '<tr>\n' + '      <td style="color:red">优惠金额</td>\n' + '      <td style="color:red">' + data.discounts + '元</td>' + '    </tr>';
                        content += '<tr>\n' + '      <td style="color:red">订单原价</td>\n' + '      <td style="color:red">' + data.originalprice + '元</td>' + '    </tr>';
                        content += '<tr>\n' + '      <td style="color:red">优惠券ID</td>\n' + '      <td style="color:red">' + data.coupon + '</td>' + '    </tr>';
                    }

                    for (const dataARKey in DataAR) {
                        if (data[dataARKey] !== undefined && data[dataARKey] !== null && data[dataARKey] !== '') {
                            content += '<tr>\n' + '      <td>' + DataAR[dataARKey]['name'] + ' </td>\n' + '      <td>' + (DataAR[dataARKey]['or'] !== '' ? (data[dataARKey] === DataAR[dataARKey]['or'] ? DataAR[dataARKey]['yes'] : DataAR[dataARKey]['no']) : data[dataARKey]) + DataAR[dataARKey]['units'] + '</td>' + '    </tr>';
                        }
                    }

                    if (data.token_arr != -1) {
                        var token_list = '';
                        $.each(data.token_arr, function (key, val) {
                            token_list += '卡' + (key + 1) + '：' + val + '<br>';
                        });
                        content += '<tr>\n' + '      <td style="color: red">相关卡密</td>\n' + '      <td>' + token_list + '</td>' + '    </tr>';
                    }

                    content += '</tbody></table><style>#OrderQuery{padding: 0.5em;max-height: 86vh;overflow:hidden;overflow-y: auto}</style><hr>' + data.docs;
                    mdui.dialog({
                        title: '订单[' + data.order + ']详情！', content: content, modal: true, history: false, buttons: [{
                            text: '关闭',
                        }]
                    });
                } else {
                    layer.alert(data.msg, {icon: 2});
                }
            }, error: function () {
                layer.alert('查询失败！');
                layer.close(index);
            }
        });
    }, ajax_state_all: function (type, arr, per = 0) {
        var index = layer.msg('批量操作中...', {
            icon: 16, time: 9999999
        });
        $.ajax({
            type: "post", url: "ajax.php?act=set_order_all", data: {
                type: type, arr: arr, per: per,
            }, dataType: "json", success: function (data) {
                if (data.code > 0) {
                    layer.alert(data.msg, {
                        icon: 1, end: function () {
                            App.OrderList();
                        }
                    });
                } else {
                    layer.msg(data.msg, {
                        icon: 2
                    });
                }
            }, error: function () {
                layer.alert('请求成功,无返回数据,可能是列表缓存更新中,已帮您更新页面数据！', {icon: 2});
                App.OrderList();
                layer.close(index);
            }
        });
    }, order_all: function (arr, i = 0) {
        setTimeout(function () {
            if (arr[i] != undefined) {
                App.order_dispose(arr[i], 2, arr, i);
            } else {
                layer.alert(arr.length + '个订单批量补单执行完成！', {
                    icon: 1, anim: 5, end: function () {
                        App.OrderList();
                    }
                });
            }
        }, 1000);
    }, order_delete_all: function (arr, i = 0) {
        layer.load(2, {
            tiem: 99999
        });
        $.ajax({
            type: "post", url: "ajax.php?act=order_delete_all", data: {
                arr: arr
            }, dataType: "json", success: function (data) {
                layer.closeAll();
                if (data.code == 1) {
                    layer.alert(data.msg, {
                        icon: 1, anim: 5, end: function () {
                            App.initialization();
                        }
                    });
                } else {
                    layer.msg(data.msg, {
                        icon: 2
                    });
                }
            }, error: function () {
                layer.closeAll();
                layer.alert('删除失败！');
            }
        });
    }
};