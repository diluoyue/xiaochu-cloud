const App = Vue.createApp({
    data() {
        return {
            Data: [], page: 1, limit: 20, name: '', cid: $("#AppHis").attr('cid'), type: -1, count: 0,
        }
    }, methods: {
        GoodsCopy(gid) {
            mdui.dialog({
                title: '温馨提示', content: '是否要将商品【' + gid + '】复制一份？此功能用于快速添加商品！', modal: true, history: false, buttons: [{
                    text: '关闭',
                }, {
                    text: '确定复制', onClick: function () {
                        let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: 'main.php?act=GoodsCopy', data: {
                                gid: gid
                            }, dataType: "json", success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.initialization();
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
                    }
                }]
            });
        }, help() {
            const content = `
<div class="mdui-table-fluid">
  <table class="mdui-table mdui-table-hoverable">
    <thead>
      <tr>
        <th>功能</th>
        <th>说明</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="white-space: nowrap;color:red">搜索商品</td>
        <td>点击 <a title="搜索商品" href="javascript:App.SearchGoods();" class="badge badge-danger"><i class="layui-icon layui-icon-search"></i></a> 图标即可搜索商品,可填写商品关键词!</td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">按分类搜索商品</td>
        <td>
        点击所属分类下的 <a href="javascript:void;">( xxx )</a> 即可取出此分类下的全部商品
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">初始化列表</td>
        <td>
        可点击 <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning"><i class="layui-icon layui-icon-refresh-3"></i></a> 图标初始化商品列表,重新载入商品
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">批量设置商品</td>
        <td>
        可点击 <a title="批量设置" href="javascript:App.BatchEditor()" class="badge badge-success"><i class="layui-icon layui-icon-set"></i></a> 图标,根据按钮批量操作商品!
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">每页商品显示数量调整</td>
        <td>
        可滑倒页面最底部,点击下一页后面的页码选择,按需选择即可!最多可同时显示200条商品!
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">查看商品基础信息</td>
        <td>
        可点击商品名称,查看基础信息,可以获取商品的购买链接,商品海报图等!
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">卡密商品补卡</td>
        <td>
        可点击库存旁边的<a href="javascript:void" title="点击补卡" class="badge badge-warning-lighten">自动发卡</a> 补卡或管理卡密!
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">删除商品</td>
        <td>
        除了可以使用批量删除功能外,还可以单独点击商品列表最左边的 设置 图标来删除对应商品
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">商品排序</td>
        <td>
        商品首页展示排序是根据商品的排序参数决定的,可以点击排序列的按钮进行调整,或打开导航栏->商品相关->商品管理->商品排序,进行批量设置!
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">商品状态</td>
        <td>
        商品有两种状态,下架或上架,下架的商品无法被他人购买,点击状态名称,可以快速切换商品上下架状态,或点击上方<a title="批量设置" class="badge badge-success"><i class="layui-icon layui-icon-set"></i></a> 图标,批量设置商品状态!
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">备忘录</td>
        <td>
        备忘录可以用来填写一些商品的对接信息,记录等,仅你可见
        </td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">利润比例</td>
        <td>
        即此商品可赚取利润的百分比比例,基于用户等级,如当一个商品成本为100元时,可以在某个等级用户身上赚取50元的利润,可能50元对于此商品来说太多,用户不会购买此商品,用户等级利润又是固定死的,这时就可以通过调整商品的利润比例来进行价格微调!,如设置为50%,则仅从此用户身上赚25元,如果填写200%,则从此用户身上赚100元,50利润的200%,以此类推,当利润比例设置为0%时,则按照成本价出售商品!
        </td>
      </tr>
    </tbody>
  </table>
</div>
                `;
            mdui.dialog({
                title: '帮助说明', content: content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }]
            });
        }, alert(data) {
            const content = `
                <img src="` + data.image + `" style="width: 140px; height: 140px;display:block;margin: auto"> <br>
                商品编号：` + data.gid + `<br>
                每日库存：` + data.quota + ` 份<br>
                每份数量：` + data.quantity + ` 个<br>
                最少购买：` + data.min + ` 份, <b>` + (data.quantity * data.min) + `</b> 个<br>
                最多购买：` + data.max + ` 份, <b>` + (data.quantity * data.max) + `</b> 个<br>
                商品名称：` + data.name + ` <br>
                商品成本：<font color=red>` + data.money + `</font><br>
                商品售价：<font color=red>` + data.price + `</font><br>
                购买链接：<a href='../?mod=route&p=Goods&gid=` + data.gid + `' target='_blank' >` + document.domain + `/?mod=route&p=Goods&gid=` + data.gid + `</a><br>
                
                `;
            mdui.dialog({
                title: '商品信息', content: content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }]
            });
        }, GoodsDelete(gid, name) {
            layer.open({
                title: '操作确认', content: '是否要永久删除此商品?,删除会影响对应的订单', icon: 3, btn: ['确定', '取消'], btn1: function () {
                    let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './main.php?act=GoodsDelete', data: {
                            gid: gid, name: name
                        }, dataType: "json", success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.initialization();
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
                }
            })
        }, GoodsStateSet(gid, type, name) {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=GoodsStateSet', data: {
                    gid: gid, type: type, name: name
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.GoodsList();
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, fullOpen(n, m) {
            var result = Math.random() * (m - n) + n;
            while (result == n) {
                result = Math.random() * (m - n) + n;
            }
            return result;
        }, GoodsList() {
            let is = layer.msg('载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=GoodsList', data: {
                    page: App.page, limit: App.limit, name: App.name, cid: App.cid
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.type = 1;
                        App.count = res.data.length;
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, BatchEditor(type = 1) {
            console.log(type);
            const arr = goods.pitch_on();
            if (arr.length === 0) {
                layer.alert('请至少选择一个商品!', {icon: 2});
                return;
            }
            switch (type) {
                case 1:
                    layer.alert('是否将这' + arr.length + '个商品设置为上架状态？', {
                        title: '商品批量上架', icon: 3, btn: ['确认', '取消'], btn1: function () {
                            goods.ajax_state(1, arr);
                        }
                    });
                    break;
                case 2:
                    layer.alert('是否将这' + arr.length + '个商品设置为下架状态？', {
                        title: '商品批量下架', icon: 3, btn: ['确认', '取消'], btn1: function () {
                            goods.ajax_state(2, arr);
                        }
                    });
                    break;
                case 3:
                    layer.alert('是否将这' + arr.length + '个商品设置为隐藏状态？', {
                        title: '商品批量隐藏', icon: 3, btn: ['确认', '取消'], btn1: function () {
                            goods.ajax_state(3, arr);
                        }
                    });
                    break;
                case 4:
                    layer.alert('是否将这' + arr.length + '个商品删除？', {
                        title: '商品批量删除', icon: 3, btn: ['确认', '取消'], btn1: function () {
                            goods.ajax_state(4, arr);
                        }
                    });
                    break;
                case 5:
                    mdui.dialog({
                        title: '批量设置商品参数确认',
                        content: '<div id="mmda">' + $("#content_s").html() + '</div>',
                        modal: true,
                        history: false,
                        buttons: [{
                            text: '关闭',
                        }, {
                            text: '确认', onClick: function () {
                                var goods_arr = goods.pitch_ons();
                                layer.alert('是否将这' + arr.length + '个商品批量设置为当前选择参数？', {
                                    title: '批量设置商品参数确认!', icon: 3, btn: ['确认', '取消'], btn1: function () {
                                        goods.ajax_state(5, arr, goods_arr);
                                    }
                                })
                            }
                        }],
                        onOpened: function () {
                            layui.use('form', function () {
                                var form = layui.form;
                                form.render();
                            });
                        }
                    });
                    break;
                case 6:
                    var load = layer.load(3);
                    $.ajax({
                        type: "POST",
                        url: "./ajax.php?act=BatchFreightTem",
                        dataType: "json",
                        success: function (data) {
                            layer.close(load);
                            if (data.code == 1) {
                                var content = '<div style="width: 100%;" class="layui-elip"><a href="javascript:goods.BatchFreightTem(\'' + arr.join('|') + '\',-1)" style="color: tomato">点击设置</a>：不使用运费模板</div>';
                                $.each(data.data, function (key, val) {
                                    content += '<div style="width: 100%;" class="layui-elip"><a href="javascript:goods.BatchFreightTem(\'' + arr.join('|') + '\',' + val.id + ')" style="color: tomato">点击设置</a>：' + val.name + ' | ' + val.id + '</div>';
                                });
                                mdui.dialog({
                                    title: '批量设置运费模板', content: content, modal: true, history: false, buttons: [{
                                        text: '关闭',
                                    },],
                                });
                            } else layer.msg(data.msg, {
                                icon: 2
                            });
                        },
                        error: function () {
                            layer.alert('加载失败！');
                        }
                    });
                    break;
                case 7:
                    layer.prompt({
                        formType: 3, value: 100, title: '默认商品可获得100%的利润',
                    }, function (value, index) {
                        value = value - 0;
                        layer.alert('是否要批量设置以下商品<br>' + arr + '<br>的利润比例为：' + value + '%？', {
                            title: '操作确认', icon: 3, btn: ['确定', '取消'], btn1: function (layero, index) {
                                //操作
                                var load = layer.load(3);
                                $.ajax({
                                    type: "POST", url: "./ajax.php?act=BatchProfitEdit", data: {
                                        gid: arr, profits: value,
                                    }, dataType: "json", success: function (data) {
                                        layer.close(load);
                                        if (data.code == 1) {
                                            layui.use(['table'], function () {
                                                App.GoodsList();
                                            });
                                            layer.alert(data.msg, {
                                                icon: 1, title: '批量操作成功'
                                            });
                                        } else layer.msg(data.msg, {
                                            icon: 2
                                        });
                                    }, error: function () {
                                        layer.alert('加载失败！');
                                    }
                                });
                            }
                        });
                        layer.close(index);
                    });
                    break;
                case 8:
                    var load = layer.load(3);
                    $.ajax({
                        type: "POST", url: "./main.php?act=ClassList", dataType: "json", success: function (data) {
                            layer.close(load);
                            if (data.code == 1) {
                                var content = '';
                                $.each(data.data, function (key, val) {
                                    content += '<div style="width: 100%;" class="layui-elip"><a href="javascript:goods.BatchClass(\'' + arr.join('|') + '\',' + val.cid + ')" style="color: tomato">点击设置</a>：' + val.name + ' | ' + val.cid + '</div>';
                                });
                                mdui.dialog({
                                    title: '批量设置商品分类', content: content, modal: true, history: false, buttons: [{
                                        text: '关闭',
                                    },],
                                });
                            } else layer.msg(data.msg, {
                                icon: 2
                            });
                        }, error: function () {
                            layer.alert('加载失败！');
                        }
                    });
                    break;
                case 9: //售价精度
                    let content = `
<div class="mdui-textfield">
  <label class="mdui-textfield-label">售价精度 [范围0-8]</label>
  <input class="mdui-textfield-input" id="input1" value="2" type="number"/>
</div>
                    `;
                    mdui.dialog({
                        title: '商品售价精度批量设置', content: content, modal: true, history: false, buttons: [{
                            text: '取消',
                        }, {
                            text: '确认修改', onClick: function () {
                                let value = $("#input1").val() - 0;
                                if (value < 0 || value > 8) {
                                    layer.msg('填写的范围不正确，最低0，最高8');
                                    return;
                                }
                                let is = layer.msg('修改中，请稍后...', {icon: 16, time: 9999999});
                                $.ajax({
                                    type: "POST", url: 'main.php?act=PriceAccuracy', data: {
                                        id: arr, value: value
                                    }, dataType: "json", success: function (res) {
                                        layer.close(is);
                                        if (res.code == 1) {
                                            layer.alert(res.msg, {
                                                icon: 1, btn1: function () {
                                                    App.GoodsList();
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
                            }
                        }], onOpen: function () {

                        }
                    });
                    break;
                case 10: //设置最大最小范围
                    content2 = `
<div class="mdui-textfield">
  <label class="mdui-textfield-label">最小下单份数</label>
  <input class="mdui-textfield-input" id="input1" value="1" type="number"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">最大下单份数</label>
  <input class="mdui-textfield-input" id="input2" value="1" type="number"/>
</div>
                    `;
                    mdui.dialog({
                        title: '批量修改商品下单份数', content: content2, modal: true, history: false, buttons: [{
                            text: '取消',
                        }, {
                            text: '确认修改', onClick: function () {
                                let value1 = $("#input1").val() - 0;
                                let value2 = $("#input2").val() - 0;
                                if (value1 < 1 || value2 < value1) {
                                    layer.msg('最低下单份数最少设置1，最多下单份数不能低于最低下单份数！');
                                    return;
                                }
                                let is = layer.msg('修改中，请稍后...', {icon: 16, time: 9999999});
                                $.ajax({
                                    type: "POST", url: 'main.php?act=NumberOrders', data: {
                                        id: arr, min: value1, max: value2
                                    }, dataType: "json", success: function (res) {
                                        layer.close(is);
                                        if (res.code == 1) {
                                            layer.alert(res.msg, {
                                                icon: 1, btn1: function () {
                                                    App.GoodsList();
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
                            }
                        }], onOpen: function () {

                        }
                    });
                    break;
                case 11: //商品库存设置
                    content2 = `
<div class="mdui-textfield">
  <label class="mdui-textfield-label">商品库存</label>
  <input class="mdui-textfield-input" id="input1" value="1" type="number"/>
</div>
                    `;
                    mdui.dialog({
                        title: '批量设置商品库存', content: content2, modal: true, history: false, buttons: [{
                            text: '取消',
                        }, {
                            text: '确认修改', onClick: function () {
                                let value = $("#input1").val() - 0;
                                if (value < 0) {
                                    layer.msg('商品库存不可低于0');
                                    return;
                                }
                                let is = layer.msg('修改中，请稍后...', {icon: 16, time: 9999999});
                                $.ajax({
                                    type: "POST", url: 'main.php?act=InventoryChanges', data: {
                                        id: arr, value: value,
                                    }, dataType: "json", success: function (res) {
                                        layer.close(is);
                                        if (res.code == 1) {
                                            layer.alert(res.msg, {
                                                icon: 1, btn1: function () {
                                                    App.GoodsList();
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
                            }
                        }], onOpen: function () {

                        }
                    });
                    break;
                default:
                    layer.alert('操作错误！');
                    break;
            }

        }, SearchGoods() {
            layer.prompt({
                formType: 2, value: this.name, title: '请输入商品名称关键字',
            }, function (value, index) {
                App.initialization(value);
                layer.close(index);
            });
        }, initialization(name = '') {
            this.page = 1;
            if (name !== '') {
                this.name = name;
            }
            this.type = -1;
            layui.use('laypage', function () {
                var laypage = layui.laypage;
                $.ajax({
                    type: "POST", url: './main.php?act=GoodsCount', data: {
                        cid: App.cid, name: name,
                    }, dataType: "json", success: function (res) {
                        if (res.code == 1) {
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
                                    App.GoodsList();
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
        }
    },
}).mount('#App');

const goods = {
    note_set: function (gid) {
        $.ajax({
            type: "post", url: "ajax.php?act=goods_content", data: {
                gid: gid, field: 'note'
            }, success: function (data) {
                if (data.code == 1) {
                    layer.prompt({
                        formType: 2, value: data.data, title: '商品' + gid + '备忘录内容,仅你可见', maxlength: 99999999999,
                    }, function (value, index) {
                        $.ajax({
                            type: "post", url: "ajax.php?act=note_set", data: {
                                note: value, gid: gid
                            }, dataType: "json", success: function (data) {
                                if (data.code == 1) {
                                    layer.msg(data.msg, {
                                        icon: 1, time: 500, end: function () {
                                            App.GoodsList();
                                        }
                                    });
                                } else {
                                    layer.msg(data.msg, {
                                        icon: 2
                                    });
                                }
                            }, error: function () {
                                layer.alert('加载失败！');
                            }
                        });
                        layer.close(index);
                    });
                } else {
                    layer.msg(data.msg, {
                        icon: 2
                    });
                }
            }, error: function () {
                layer.alert('加载失败！');
            }
        });
    }, FreightTem: function () {
        var arr = goods.pitch_on();
        if (arr.length == 0) {
            layer.msg('请先选中1个商品！');
            return false;
        }

        var load = layer.load(3);
        $.ajax({
            type: "POST", url: "./ajax.php?act=BatchFreightTem", dataType: "json", success: function (data) {
                layer.close(load);
                if (data.code == 1) {
                    var content = '<div style="width: 100%;" class="layui-elip"><a href="javascript:goods.BatchFreightTem(\'' + arr.join('|') + '\',-1)" style="color: tomato">点击设置</a>：不使用运费模板</div>';
                    $.each(data.data, function (key, val) {
                        content += '<div style="width: 100%;" class="layui-elip"><a href="javascript:goods.BatchFreightTem(\'' + arr.join('|') + '\',' + val.id + ')" style="color: tomato">点击设置</a>：' + val.name + ' | ' + val.id + '</div>';
                    });
                    layer.open({
                        title: '批量设置运费模板', content: content, btn: ['取消'],
                    });
                } else layer.msg(data.msg, {
                    icon: 2
                });
            }, error: function () {
                layer.alert('加载失败！');
            }
        });
    }, BatchClass: function (gid, cid) {
        var arr = gid.split('|');
        layer.alert('是否将这' + arr.length + '个商品移动到分类:' + cid + '里面？', {
            title: '商品批量移动确认!', icon: 3, btn: ['确认', '取消'], btn1: function () {
                goods.ajax_state(6, arr, cid);
            }
        });
    }, BatchFreightTem: function (gid, id) {
        var gid_arr = gid.split('|');
        layer.alert('是否要设置商品：<br>' + gid + '<br>的运费模板为：' + id + '？', {
            title: '操作确认', icon: 3, btn: ['确定', '取消'], btn1: function (layero, index) {
                //操作
                var load = layer.load(3);
                $.ajax({
                    type: "POST", url: "./ajax.php?act=BatchFreightTemEdit", data: {
                        gid: gid_arr, id: id,
                    }, dataType: "json", success: function (data) {
                        layer.close(load);
                        if (data.code == 1) {
                            layui.use(['table'], function () {
                                App.GoodsList();
                            });
                            layer.alert(data.msg, {
                                icon: 1, title: '批量操作成功'
                            });
                        } else layer.msg(data.msg, {
                            icon: 2
                        });
                    }, error: function () {
                        layer.alert('加载失败！');
                    }
                });
            }
        });
    }, select_id: function (id) {
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
    }, sort: function (gid, type) {
        $.ajax({
            type: 'POST', url: 'ajax.php?act=setGoodsSort', data: {
                gid: gid, type: type
            }, dataType: 'json', success: function () {
                App.initialization(App.name);
            }, error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    }, pitch_on: function () {
        var amount = [];
        $("#table input[type='checkbox']").each(function () {
            if ($(this).is(":checked")) {
                if ($(this).val() != 'true' && $(this).val() != '') amount.push($(this).val());
            }
        });
        return amount;
    }, pitch_ons: function () {
        var amount = [];
        $("#mmda input[type='checkbox']").each(function () {
            if ($(this).is(":checked")) {
                if ($(this).val() != 'true' && $(this).val() != '') amount.push($(this).val());
            }
        });
        return amount;
    }, ajax_state: function (type, arr, cid = -1) {
        var index = layer.msg('正在调整中...', {
            icon: 16, time: 9999999
        });
        $.ajax({
            type: "post", url: "ajax.php?act=Goods_State_all", data: {
                type: type, arr: arr, cid: cid
            }, dataType: "json", success: function (data) {
                if (data.code > 0) {
                    layer.alert(data.msg, {
                        icon: 1, end: function (layero, index) {
                            App.GoodsList();
                        }
                    });
                } else {
                    layer.msg(data.msg, {
                        icon: 2
                    });
                }
            }, error: function () {
                layer.alert('修改失败！');
                layer.close(index);
            }
        });
    }, SharePoster: function (gid) {
        layer.msg('商品分享海报正在生成中...', {
            icon: 16, time: 9999999
        });
        $.ajax({
            type: "post", url: "../main.php?act=SharePoster", data: {
                gid: gid
            }, dataType: "json", success: function (data) {
                if (data.code == 1) {
                    layer.alert('<img src="' + data.src + '" width=300 heigth=450 />', {
                        area: ['340px', '490px'], title: false, btn: false, shade: [0.8, '#000'], shadeClose: true,
                    })
                } else {
                    layer.msg(data.msg, {
                        icon: 2
                    });
                }
            }, error: function () {
                layer.alert('生成失败！,可能是商品图片地址无法访问');
            }
        });
    }
};

App.initialization();
