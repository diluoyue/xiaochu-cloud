const vm = Vue.createApp({
    data() {
        return {
            OrderData: [], //订单
            seek: '',
            state: 1, //1全部，2未完成，3待收货，4已完成，5已取消
            Tracking: '',
            page: 1,
        };
    },
    mounted() {
        this.GetOrderList();
    },
    methods: {
        OrderModification(oid, order, input) {
            let arr = input.split('|||');
            if (arr.length === 0) {
                return false;
            }
            let content = ``;
            for (const arrKey in arr) {
                let str = arr[arrKey].split('：');
                /*content += `
                <div class="mdui-textfield">
                  <label class="mdui-textfield-label">` + str[0] + `</label>
                  <input class="mdui-textfield-input" id="inputs_` + arrKey + `" value="` + str[1] + `" type="text"/>
                </div>
                `;*/
                content += `<div class="input-group"><div class="input-group-addon">` + str[0] + `</div><input type="text" id="inputs_` + arrKey + `" value="` + str[1] + `" class="form-control" placeholder="请填写完整！"></div>`;
            }
            layer.open({
                title: false,
                area: ['350px', '400px'],
                content: content,
                btn: ['确认修改', '取消'],
                btn1: function () {
                    let form = {};
                    for (const arrKey in arr) {
                        form[arrKey] = $("#inputs_" + arrKey).val();
                        if (form[arrKey] == '') {
                            layer.open({
                                title: '警告',
                                content: '请将第' + ((arrKey - 0) + 1) + '行输入框填写完整！',
                                btn: ['确定'],
                                icon: 2,
                                end: function () {
                                    vm.OrderModification(oid, order, input);
                                }
                            });
                            return false;
                        }
                    }
                    let is = layer.msg('正在修改中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: "./main.php?act=OrderList&type=OrderModification",
                        data: {
                            oid: oid,
                            order: order,
                            data: form,
                        },
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1
                                });
                            } else {
                                layer.alert(res.msg, {
                                    icon: 2
                                });
                            }
                        },
                        error: function () {
                            layer.msg('服务器异常！');
                        }
                    });
                }
            });
        },
        GetQuery(id, order) {
            var index = layer.msg('订单查询中...', {
                icon: 16,
                time: 9999999
            });
            $.ajax({
                type: "post",
                url: "./main.php?act=OrderList&type=QueryDetails",
                data: {
                    id: id,
                    order: order
                },
                dataType: "json",
                success: function (res) {
                    layer.close(index);
                    if (res.code == 1) {
                        data = res.data;
                        let content = '';
                        if (data.ApiSn !== -1) {
                            content += '<p style="text-align: center;font-size: 1.3em;color: seagreen;margin-bottom: 0;">订单实时状态</p><table class="layui-table layui-text" lay-size="sm" lay-skin="row"><colgroup>\n' +
                                '    <col width="30%">\n' +
                                '    <col width="70%">' +
                                '    <col>\n' +
                                '  </colgroup><thead>\n' +
                                '    <tr>\n' +
                                '      <th>类型</th>\n' +
                                '      <th>实时状态</th>' +
                                '    </tr> \n' +
                                '  </thead><tbody>';
                            content += '<tr>\n' +
                                '      <td>订单状态</td>\n' +
                                '      <td>' + data.ApiState + '</td>' +
                                '    </tr>';
                            content += '<tr>\n' +
                                '      <td>初始数量</td>\n' +
                                '      <td>' + data.ApiInitial + '</td>' +
                                '    </tr>';
                            content += '<tr>\n' +
                                '      <td>下单数量</td>\n' +
                                '      <td>' + data.ApiNum + '</td>' +
                                '    </tr>';
                            content += '<tr>\n' +
                                '      <td>当前数量</td>\n' +
                                '      <td>' + data.ApiPresent + '</td>' +
                                '    </tr>';
                            content += '<tr>\n' +
                                '      <td>提交时间</td>\n' +
                                '      <td>' + data.ApiTime + '</td>' +
                                '    </tr>';
                            if (data.ApiError !== '无') {
                                content += '<tr>\n' +
                                    '      <td>异常信息</td>\n' +
                                    '      <td>' + data.ApiError + '</td>' +
                                    '    </tr>';
                            }
                            content += '</tbody></table><hr>';
                        }

                        content += '<table class="layui-table layui-text" lay-size="sm" lay-skin="row"><colgroup>\n' +
                            '    <col width="30%">\n' +
                            '    <col width="70%">' +
                            '    <col>\n' +
                            '  </colgroup><thead>\n' +
                            '    <tr>\n' +
                            '      <th>类型</th>\n' +
                            '      <th>状态</th>' +
                            '    </tr> \n' +
                            '  </thead><tbody>';

                        let DataAR = {
                            'name': {
                                'name': '商品名称',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'id': {
                                'name': '订单编号',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'order': {
                                'name': '本地订单',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'order_id': {
                                'name': '货源订单',
                                'or': '-1',
                                'yes': '无',
                                'no': data.order_id,
                                'units': '',
                            },
                            'trade_no': {
                                'name': '支付订单',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'muid': {
                                'name': '站点编号',
                                'or': '-1',
                                'yes': '主站',
                                'no': data.muid,
                                'units': '',
                            },
                            'uid': {
                                'name': '用户编号',
                                'or': '-1',
                                'yes': '游客',
                                'no': data.uid,
                                'units': '',
                            },
                            'docking': {
                                'name': '对接状态',
                                'or': '-1',
                                'yes': '跟随订单',
                                'no': (data.docking == 1 ? '对接成功' : (data.docking == 2 ? '对接失败' : '待提交对接')),
                                'units': '',
                            },
                            'state': {
                                'name': '订单状态',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'num': {
                                'name': '购买份数',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '份',
                            },
                            'type': {
                                'name': '付款方式',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'input': {
                                'name': '下单信息',
                                'or': '-1',
                                'yes': '',
                                'no': data.input.join('<br>'),
                                'units': ((res.OrderModification == 1 && (data.stateid == 2 || data.stateid == 3)) ? ' <a href="javascript:vm.OrderModification(' + id + ',\'' + data.order + '\',\'' + data.input.join('|||') + '\')">[ 修改 ]</a>' : ''),
                            },
                            'return': {
                                'name': '对接返回',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'remark': {
                                'name': '订单备注',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'logistics': {
                                'name': '物流单号',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'finishtime': {
                                'name': '完成时间',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'addtiem': {
                                'name': '创建时间',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'ip': {
                                'name': '下单IP',
                                'or': '',
                                'yes': '',
                                'no': '',
                                'units': '',
                            },
                            'take': {
                                'name': '收货状态',
                                'or': '1',
                                'yes': '未收货',
                                'no': '已收货',
                                'units': '',
                            },
                        };

                        if (data.HostType == 1) {
                            content += '<tr>\n' +
                                '      <td>主机管理</td>\n' +
                                '      <td><a href="./HostAdmin" target="_blank" class="layui-btn layui-btn-xs layui-btn-danger">登陆主机后台</a></td>' +
                                '    </tr>';
                        }


                        content += '<tr>\n' +
                            '      <td style="color:red">支付金额</td>\n' +
                            '      <td style="color:red">' + data.price + (data.type == '积分兑换' ? '积分' : '元') + '</td>' +
                            '    </tr>';

                        if (data.coupon != -1) {
                            content += '<tr>\n' +
                                '      <td style="color:red">优惠金额</td>\n' +
                                '      <td style="color:red">' + data.discounts + '元</td>' +
                                '    </tr>';
                            content += '<tr>\n' +
                                '      <td style="color:red">订单原价</td>\n' +
                                '      <td style="color:red">' + data.originalprice + '元</td>' +
                                '    </tr>';
                            content += '<tr>\n' +
                                '      <td style="color:red">优惠券ID</td>\n' +
                                '      <td style="color:red">' + data.coupon + '</td>' +
                                '    </tr>';
                        }

                        for (const dataARKey in DataAR) {
                            if (data[dataARKey] !== undefined && data[dataARKey] !== null && data[dataARKey] !== '') {
                                content += '<tr>\n' +
                                    '      <td>' + DataAR[dataARKey]['name'] + ' </td>\n' +
                                    '      <td>' + (DataAR[dataARKey]['or'] !== '' ? (data[dataARKey] === DataAR[dataARKey]['or'] ? DataAR[dataARKey]['yes'] : DataAR[dataARKey]['no']) : data[dataARKey]) + DataAR[dataARKey]['units'] + '</td>' +
                                    '    </tr>';
                            }
                        }
                        if (data.token_arr != -1) {
                            var token_list = '';
                            $.each(data.token_arr, function (key, val) {
                                token_list += '卡' + (key + 1) + '：' + val + '<br>';
                            });
                            content += '<tr>\n' +
                                '      <td style="color: red">相关卡密</td>\n' +
                                '      <td>' + token_list + '</td>' +
                                '    </tr>';
                        }
                        if (data.Explain != -1) {
                            content += '<tr>\n' +
                                '      <td style="color:red">隐藏内容</td>\n' +
                                '      <td>' + data.Explain + '</td>' +
                                '    </tr>';
                        }

                        content += '</tbody></table><style>#OrderQuery{padding: 0.5em;max-height: 86vh;overflow:hidden;overflow-y: auto}</style><hr>' + data.docs;
                        var area = [$(window).width() > 480 ? '480px' : '98%'];
                        layer.open({
                            type: 0,
                            area: area,
                            title: '订单[' + data.order + ']详情！',
                            content: content,
                            skin: 'layui-layer-rim',
                            id: 'OrderQuery',
                            btn: false,
                        });
                    } else {
                        layer.msg(data.msg);
                    }
                },
                error: function () {
                    layer.alert('查询失败！');
                    layer.close(index);
                }
            });
        },
        GetOrderList(page = 1) {
            let _this = this;
            types = 1;
            query = 1;
            if (page == -1) {
                _this.page = 1;
                this.OrderData = [];
                var index = layer.load(1);
            } else if (page == -2) {
                _this.page = 1;
                this.OrderData = [];
                var index = layer.load(1);
                query = 2;
            } else if (page == -3) {
                _this.page = 1;
                this.OrderData = [];
                var index = layer.load(1);
                types = 2;
            } else {
                _this.page = page;
                var index;
            }

            if (types == 1) {
                $.ajax({
                    type: 'post',
                    url: './main.php?act=OrderList&type=OrderAll',
                    data: {
                        seek: _this.seek,
                        state: _this.state,
                        page: _this.page
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(index);
                        if (data.code == 1) {
                            _this.Tracking = data.Tracking;
                            if (data.data.length != 0) {
                                _this.OrderData.push.apply(_this.OrderData, data.data);
                            } else if (query == 2) {
                                layer.alert('没有查询到任何订单<br>可能此订单是游客订单，未绑定用户,请使用游客查单模式！', {
                                    icon: 2
                                });
                            }
                            if (data.data.length === 0) {
                                layer.msg('没有更多了', {icon: 2});
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
            } else {
                $.ajax({
                    type: 'post',
                    url: './main.php?act=OrderList&type=QueryList',
                    data: {
                        msg: _this.seek,
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(index);
                        if (data.code == 1) {
                            _this.Tracking = data.Tracking;
                            if (data.data.length != 0) {
                                _this.OrderData.push.apply(_this.OrderData, data.data);
                            } else layer.alert('没有查询到相关订单<br>请输入下单时填写的第一行信息！<br>或者和搜索内容有关的订单并非游客订单!', {
                                icon: 2
                            });
                        } else {
                            layer.alert(data.msg, {
                                icon: 2
                            });
                        }
                    },
                    error: function () {
                        layer.alert('加载失败！');
                    }
                });
            }
        },
    }
}).mount('#appquery');