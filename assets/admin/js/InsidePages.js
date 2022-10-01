/**
 * 后台内页公共js
 */

layui.config({
    base: '/assets/layuiadmin/',
}).extend({
    index: 'lib/index' //主入口模块
}).use(['index', 'console', 'admin', 'form']);

function toFixeds(str = 0, num = 2) {
    str = str - 0;
    return str.toFixed(num) + '元';
}

//收益统计
var HomePage = {
    cartogram: function () {
        //访问量
        layui.use(['carousel', 'echarts', 'admin'], function () {
            var $ = layui.$
                , carousel = layui.carousel
                , echarts = layui.echarts;

            var admin = layui.admin;
            var index = layer.load(3);
            admin.req({
                type: "POST",
                url: '/Admin/Ajax/Page/HomeCartogram',
                dataType: "json",
                success: function (data) {
                    layer.close(index);
                    if (data.code == 1) {
                        $.each(data.data, function (key, val) {
                            $("#" + key).text(val);
                        });

                        var echartsApp = [],
                            options = [
                                {
                                    tooltip: {
                                        trigger: 'axis'
                                    },
                                    calculable: true,
                                    legend: {
                                        data: ['订单量', '交易额', '成本']
                                    },

                                    xAxis: [
                                        {
                                            type: 'category',
                                            data: data['category']
                                        }
                                    ],
                                    yAxis: [
                                        {
                                            type: 'value',
                                            name: '订单量',
                                            axisLabel: {
                                                formatter: '{value} 条'
                                            }
                                        },
                                        {
                                            type: 'value',
                                            name: '交易额',
                                            axisLabel: {
                                                formatter: '{value} 元'
                                            }
                                        },
                                        {
                                            type: 'value',
                                            name: '成本',
                                            axisLabel: {
                                                formatter: '{value} 元'
                                            }
                                        }
                                    ],
                                    series: [
                                        {
                                            name: '订单量',
                                            type: 'line',
                                            data: data['order_size']
                                        },
                                        {
                                            name: '交易额',
                                            type: 'line',
                                            yAxisIndex: 1,
                                            data: data['Turnover']
                                        },
                                        {
                                            name: '成本',
                                            type: 'line',
                                            data: data['cost']
                                        }
                                    ]
                                }
                            ]
                            , elemDataView = $('#LAY-index-pagetwos').children('div')
                            , renderDataView = function (index) {
                                echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
                                echartsApp[index].setOption(options[index]);
                                window.onresize = echartsApp[index].resize;
                            };
                        //没找到DOM，终止执行
                        if (!elemDataView[0]) return;
                        renderDataView(0);

                    } else {
                        layer.msg(data.msg);
                    }
                },
                error: function () {
                    layer.alert('获取失败！');
                },
            });
        });
    },
};

//用户统计
var UserStatistics = {
    cartogram: function () {
        //访问量
        layui.use(['carousel', 'echarts', 'admin'], function () {
            var $ = layui.$
                , carousel = layui.carousel
                , echarts = layui.echarts;

            var index = layer.load(3);
            var admin = layui.admin;
            admin.req({
                type: "POST",
                url: '/Admin/Ajax/Page/UserStatistics',
                dataType: "json",
                success: function (data) {
                    layer.close(index);
                    if (data.code == 1) {
                        $.each(data.data, function (key, val) {
                            $("#" + key).text(val);
                        });

                        var echartsApp = [],
                            options = [
                                {
                                    tooltip: {
                                        trigger: 'axis'
                                    },
                                    calculable: true,
                                    legend: {
                                        data: ['新增用户数', '新增分站数', '签到人数']
                                    },

                                    xAxis: [
                                        {
                                            type: 'category',
                                            data: data['category']
                                        }
                                    ],
                                    yAxis: [
                                        {
                                            type: 'value',
                                            name: '新增用户数',
                                            axisLabel: {
                                                formatter: '{value} 人'
                                            }
                                        },
                                        {
                                            type: 'value',
                                            name: '新增分站数',
                                            axisLabel: {
                                                formatter: '{value} 个'
                                            }
                                        },
                                        {
                                            type: 'value',
                                            name: '签到人数',
                                            axisLabel: {
                                                formatter: '{value} 人'
                                            }
                                        }
                                    ],
                                    series: [
                                        {
                                            name: '新增用户数',
                                            type: 'line',
                                            data: data['UserSum']
                                        },
                                        {
                                            name: '新增分站数',
                                            type: 'line',
                                            yAxisIndex: 1,
                                            data: data['UserPlusSum']
                                        },
                                        {
                                            name: '签到人数',
                                            type: 'line',
                                            data: data['SignSum']
                                        }
                                    ]
                                }
                            ]
                            , elemDataView = $('#LAY-index-pagetwos').children('div')
                            , renderDataView = function (index) {
                                echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
                                echartsApp[index].setOption(options[index]);
                                window.onresize = echartsApp[index].resize;
                            };
                        //没找到DOM，终止执行
                        if (!elemDataView[0]) return;
                        renderDataView(0);

                    } else {
                        layer.msg(data.msg);
                    }
                },
                error: function () {
                    layer.alert('获取失败！');
                },
            });
        });
    },
};

//热卖商品
var HotCommodity = {
    ToHotAll: function () {
        layui.use('table', function () {
            var table = layui.table;
            //热卖商品统计总榜
            table.render({
                elem: '#ToHotAll'
                , url: '/Admin/Ajax/Page/ToHotAll/' //数据接口
                , page: true //开启分页
                , cols: [[ //表头
                    {field: 'gid', title: 'GID', width: 100, sort: true, fixed: 'left'}
                    , {field: 'name', templet: '#name', title: '商品名称', width: 200}
                    , {field: 'count', templet: '#count', title: '售出数量', width: 120, sort: true}
                    , {field: 'money', templet: '#money', title: '消耗余额'}
                    , {field: 'cost', templet: '#cost', title: '订单成本'}
                ]]
            });
        });
    },
    ToDayHot: function () {
        layui.use('table', function () {
            var table = layui.table;
            //今日热卖商品统计
            table.render({
                elem: '#ToDayHot'
                , url: '/Admin/Ajax/Page/ToDayHot/' //数据接口
                , page: true //开启分页
                , cols: [[ //表头
                    {field: 'gid', title: 'GID', width: 100, sort: true, fixed: 'left'}
                    , {field: 'name', templet: '#name', title: '商品名称', width: 200}
                    , {field: 'count', templet: '#count', title: '售出数量', width: 120, sort: true}
                    , {field: 'money', templet: '#money', title: '消耗余额'}
                    , {field: 'cost', templet: '#cost', title: '订单成本'}
                ]]
            });
        });
    },
    HotYesterday: function () {
        if ($("#HotYesterday").text() != '') {
            layer.msg('拦截');
            return false;
        }
        layui.use('table', function () {
            var table = layui.table;
            //今日热卖商品统计
            table.render({
                elem: '#HotYesterday'
                , url: '/Admin/Ajax/Page/HotYesterday/' //数据接口
                , page: true //开启分页
                , cols: [[ //表头
                    {field: 'gid', title: 'GID', width: 100, sort: true, fixed: 'left'}
                    , {field: 'name', templet: '#name', title: '商品名称', width: 200}
                    , {field: 'count', templet: '#count', title: '售出数量', width: 120, sort: true}
                    , {field: 'money', templet: '#money', title: '消耗余额'}
                    , {field: 'cost', templet: '#cost', title: '订单成本'}
                ]]
            });
        });
    }
};

//商品管理
var CommodityManagement = {
    //调整商品排序
    GoodsSorting: function (gid, sort, type) {
        var load = layer.load(3);
        var admin = layui.admin;
        admin.req({
            type: "POST",
            url: '/Admin/Ajax/Page/GoodsSorting',
            data: {
                gid: gid,
                type: type,
                sort: sort
            },
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 1) {
                    layui.use(['table'], function () {
                        var table = layui.table;
                        table.reload('idTest', {});
                    });
                    layer.msg(data.msg, {icon: 1});
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.alert('获取失败！');
            },
        });
    },
    //删除商品
    DeleteGoods: function (gid, Batch = false) {
        layer.alert('此操作无法撤销，是否执行？', {
            title: '是否删除商品' + gid + '?', icon: 3, btn: ['取消', '删除'], btn2: function (layero, index) {
                var index = layer.load(2);
                var admin = layui.admin;
                admin.req({
                    type: "POST",
                    url: '/Admin/Ajax/Page/DeleteGoods',
                    data: {
                        gid: gid,
                        Batch: Batch
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(index);
                        if (data.code == 1) {
                            layui.use(['table'], function () {
                                var table = layui.table;
                                table.reload('idTest', {});
                            });
                            layer.msg(data.msg, {icon: 1});
                        } else layer.msg(data.msg, {icon: 2});
                    },
                    error: function () {
                        layer.alert('加载失败！');
                    },
                });
            }
        });
    },
    //商品上下架状态调整
    GoodsState: function (gid, type, Batch = false) {
        var index = layer.load(3);
        var admin = layui.admin;
        admin.req({
            type: "POST",
            url: '/Admin/Ajax/Page/GoodsState',
            data: {
                state: (type == 1 ? 2 : 1),
                gid: gid,
                Batch: Batch,
            },
            dataType: "json",
            success: function (data) {
                layer.close(index);
                if (data.code == 1) {
                    layui.use(['table'], function () {
                        var table = layui.table;
                        table.reload('idTest', {});
                    });
                    layer.msg(data.msg, {icon: 1});
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.alert('加载失败！');
            },
        });
    },
    //获取商品列表
    ProductList: function () {
        layui.use('table', function () {
            var table = layui.table;
            table.render({
                elem: '#ProductList'
                , url: '/Admin/Ajax/Page/ProductList/'
                , id: 'idTest'
                , toolbar: '#toolbarDemo'
                , cellMinWidth: 100
                , page: {
                    layout: ['limit', 'prev', 'page', 'next', 'skip', 'count']
                    , groups: 3
                }
                , cols: [[ //表头
                    {type: 'checkbox', fixed: 'left'}
                    , {field: 'gid', width: 60, title: 'ID', sort: true, fixed: 'left'}
                    , {field: 'sort', templet: '#sort', title: '商品排序', width: 88}
                    , {field: 'name', title: '商品名称', edit: 'text', width: 150}
                    , {field: 'cid', title: '分类ID', edit: 'text', width: 78}
                    , {field: 'money', templet: '#money', title: '商品成本', width: 120, edit: 'text'}
                    , {field: 'profits', templet: '#profits', title: '利润比例'}
                    , {
                        field: 'method',
                        templet: '#method',
                        title: '<a lay-tips="点击右侧查看开启状态，点击其他位置快速编辑参数！">商品配置</a>',
                        width: 100
                    }
                    , {field: 'image', templet: '#image', title: '商品图片', edit: 'text'}
                    , {field: 'picture', templet: '#picture', title: '详情图', edit: 'text'}
                    , {field: 'sqid', templet: '#sqid', title: '对接类型'}
                    , {field: 'date', title: '上架时间'}
                    , {field: 'set', templet: '#set', title: '操作', width: 125, fixed: 'right'}
                ]]
            });

            table.on('edit(ProductList)', function (obj) {
                var load = layer.load(2);
                var admin = layui.admin;
                admin.req({
                    type: "POST",
                    url: '/Admin/Ajax/Page/GeneralMerchandiseEditor',
                    data: {
                        field: obj.field,
                        text: obj.value,
                        gid: obj.data.gid
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(load);
                        if (data.code == 1) {
                            layer.msg(data.msg, {icon: 1});
                        } else layer.msg(data.msg, {icon: 2});
                    },
                    error: function () {
                        layer.alert('获取失败！');
                    },
                });
            });

            //头工具栏事件
            table.on('toolbar(ProductList)', function (obj) {
                var checkStatus = table.checkStatus(obj.config.id);
                var data = checkStatus.data;
                var data_arr = []; //初始化
                $.each(data, function (key, val) {
                    data_arr.push(val.gid);
                });
                if (data_arr.length == 0) {
                    layer.msg('请先选择一个商品！');
                    return false;
                }
                switch (obj.event) {
                    case 'BatchMethod': //批量设置商品参数
                        $("#content_s input[type='checkbox']").each(function () {
                            $(this).attr('checked', true);
                        });
                        layer.open({
                            title: '批量编辑商品数据',
                            content: $("#content_s").html() + '<hr>批量编辑：<br>' + data_arr.join('|') + '<hr>共' + data_arr.length + '个商品！',
                            btn: ['保存', '关闭'],
                            id: 'BatchMethod',
                            btn1: function (layero, index) {
                                var BatchMethod = CommodityManagement.pitch_on('BatchMethod');
                                layer.alert('是否确认修改？', {
                                    title: '批量调整参数',
                                    icon: 3,
                                    btn: ['确定', '取消'], yes: function (layero, index) {
                                        var load = layer.load(3);
                                        var admin = layui.admin;
                                        admin.req({
                                            type: "POST",
                                            url: '/Admin/Ajax/Page/MethodEdit',
                                            data: {
                                                gid: data_arr,
                                                method: BatchMethod,
                                                Batch: 1,
                                            },
                                            dataType: "json",
                                            success: function (data) {
                                                layer.close(load);
                                                if (data.code == 1) {
                                                    layui.use(['table'], function () {
                                                        var table = layui.table;
                                                        table.reload('idTest', {});
                                                    });
                                                    layer.alert(data.msg, {icon: 1, title: '批量操作成功'});
                                                } else layer.msg(data.msg, {icon: 2});
                                            },
                                            error: function () {
                                                layer.alert('获取失败！');
                                            },
                                        });
                                    }
                                });
                            }, success: function (layero, index) {
                                var form = layui.form;
                                form.render('checkbox');
                            }
                        });
                        break;
                    case 'BatchClass': //批量调整商品分类
                        var load = layer.load(3);
                        var admin = layui.admin;
                        admin.req({
                            type: "POST",
                            url: '/Admin/Ajax/Page/BatchClassList', //取出分类列表
                            dataType: "json",
                            success: function (data) {
                                layer.close(load);
                                if (data.code == 1) {
                                    var content = '';
                                    $.each(data.data, function (key, val) {
                                        console.log(val);
                                        content += '<div style="width: 100%;" class="layui-elip"><a href="javascript:CommodityManagement.BatchClass(\'' + data_arr.join('|') + '\',' + val.cid + ')" style="color: tomato">点击移动</a>：' + val.name + ' | ' + val.cid + '</div>';
                                    });
                                    layer.open({
                                        title: '批量移动商品分类',
                                        content: content,
                                        btn: ['取消移动'],
                                    });
                                } else layer.msg(data.msg, {icon: 2});
                            },
                            error: function () {
                                layer.alert('获取失败！');
                            },
                        });
                        break;
                    case 'BatchDelete': //批量删除商品
                        CommodityManagement.DeleteGoods(data_arr, 1);
                        break;
                    case 'BatchState': //批量设置商品上下架
                        layer.alert('是否要批量设置商品上下架状态？', {
                            title: '操作确认', icon: 3, btn: ['批量上架', '批量下架', '取消'], btn1: function (layero, index) {
                                //上架
                                CommodityManagement.GoodsState(data_arr, 2, 1);
                            }, btn2: function (layero, index) {
                                //下架
                                CommodityManagement.GoodsState(data_arr, 1, 1);
                            }
                        });
                        break;
                }
            })
        });
    },
    BatchClass: function (gid, cid) {
        var gid_arr = gid.split('|');
        layer.alert('是否要将商品：<br>' + gid + '批量移动到ID：' + cid + '的分类中？', {
            title: '操作确认', icon: 3, btn: ['确定', '取消'], btn1: function (layero, index) {
                //操作
                var load = layer.load(3);
                var admin = layui.admin;
                admin.req({
                    type: "POST",
                    url: '/Admin/Ajax/Page/BatchClass',
                    data: {
                        gid: gid_arr,
                        cid: cid,
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(load);
                        if (data.code == 1) {
                            layui.use(['table'], function () {
                                var table = layui.table;
                                table.reload('idTest', {});
                            });
                            layer.alert(data.msg, {icon: 1, title: '批量操作成功'});
                        } else layer.msg(data.msg, {icon: 2});
                    },
                    error: function () {
                        layer.alert('获取失败！');
                    },
                });
            }
        });
    },
    method: function (method, gid) {
        var method_arr = JSON.parse(method);
        var content = '<a href="javascript:CommodityManagement.method_edit(\'' + method + '\',' + gid + ')" title="编辑商品参数">' + (method == null ? '点击设置' : method) + '</a><hr>';
        $.each(method_arr, function (key, val) {
            switch (val) {
                case 1: //在线付款
                    content += '在线付款<br>';
                    break;
                case 2: //支持余额付款
                    content += '余额付款<br>';
                    break;
                case 3: //支持积分付款
                    content += '积分付款<br>';
                    break;
                case 4: //支持同系统对接
                    content += '支持被对接<br>';
                    break;
                case 5: //开启商品价格监控
                    content += '开启价格监控<br>';
                    break;
                case 6: //允许被克隆
                    content += '允许此商品被克隆<br>';
                    break;
                case 7: //开启下单份数
                    content += '允许下单多份<br>';
                    break;
                case 8: //启用等级密价
                    content += '启用等级密价<br>';
                    break;
                case 9: //启用加价模板
                    content += '启用加价模板<br>';
                    break;
            }
        });
        return content;
    },
    method_edit: function (method, gid) {
        var method_arr = JSON.parse(method);
        $("#content_s input[type='checkbox']").each(function () {
            $(this).attr('checked', false);
            if (method.indexOf($(this).val()) > -1) {
                $(this).attr('checked', true);
            }
        });
        layer.open({
            title: '商品' + gid + '参数编辑',
            content: $("#content_s").html(),
            btn: ['保存', '关闭'],
            id: 'method_' + gid,
            btn1: function (layero, index) {
                var method_data = CommodityManagement.pitch_on('method_' + gid);
                var admin = layui.admin;
                var index = layer.load(2);
                admin.req({
                    type: "POST",
                    url: '/Admin/Ajax/Page/MethodEdit',
                    data: {
                        method: method_data,
                        gid: gid
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(index);
                        if (data.code == 1) {
                            layui.use(['table'], function () {
                                var table = layui.table;
                                table.reload('idTest', {});
                            });
                            layer.msg(data.msg, {icon: 1});
                        } else layer.msg(data.msg, {icon: 2});
                    },
                    error: function () {
                        layer.alert('加载失败！');
                    },
                });
            },
            success: function (layero, index) {
                var form = layui.form;
                form.render('checkbox');
            }
        });
    },
    pitch_on: function (id) { //批量获取 input checkbox  CommodityManagement.pitch_on('id') 调用
        var amount = [];
        $("#" + id + " input[type='checkbox']").each(function () {
            if ($(this).is(":checked")) {
                if ($(this).val() != 'true' && $(this).val() != '') amount.push(($(this).val()));
            }
        });
        return amount;
    },
};

//商品分类管理
var CommodityClassificationManagement = {
    //调整分类排序
    ClassSorting: function (cid, sort, type) {
        var load = layer.load(3);
        var admin = layui.admin;
        admin.req({
            type: "POST",
            url: '/Admin/Ajax/Page/ClassSorting',
            data: {
                cid: cid,
                type: type,
                sort: sort
            },
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 1) {
                    layui.use(['table'], function () {
                        var table = layui.table;
                        table.reload('idTest', {});
                    });
                    layer.msg(data.msg, {icon: 1});
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.alert('获取失败！');
            },
        });
    },
    //获取分类列表
    CategoryListings: function () {
        layui.use('table', function () {
            var table = layui.table;
            table.render({
                elem: '#CategoryListings'
                , url: '/Admin/Ajax/Page/CategoryListings/' //数据接口
                , page: true //开启分页
                , id: 'idTest'
                , toolbar: '#toolbarDemo'
                , cellMinWidth: 100
                , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                    layout: ['limit', 'prev', 'page', 'next', 'skip', 'count'] //自定义分页布局
                    //,curr: 5 //设定初始在第 5 页
                    , groups: 3 //只显示 1 个连续页码
                }
                , cols: [[ //表头
                    {type: 'checkbox', fixed: 'left'}
                    , {field: 'cid', width: 60, title: 'ID', sort: true, fixed: 'left'}
                    , {field: 'sort', templet: '#sort', title: '分类排序', width: 88}
                    , {field: 'name', title: '分类名称', edit: 'text'}
                    , {field: 'image', templet: '#image', title: '分类图片', edit: 'text'}
                    , {field: 'date', title: '创建时间'}
                    , {field: 'set', templet: '#set', title: '操作', width: 125, fixed: 'right'}
                ]]
            });

            table.on('toolbar(CategoryListings)', function (obj) {
                var checkStatus = table.checkStatus(obj.config.id);
                var data = checkStatus.data;
                var data_arr = []; //初始化
                $.each(data, function (key, val) {
                    data_arr.push(val.cid);
                });
                if (data_arr.length == 0) {
                    layer.msg('请先选择一个分类！');
                    return false;
                }
                switch (obj.event) {
                    case 'BatchDelete': //批量删除分类
                        CommodityClassificationManagement.DeleteClass(data_arr, 1);
                        break;
                    case 'BatchState': //批量设置商品上下架
                        layer.alert('是否要批量设置分类显示状态？', {
                            title: '操作确认', icon: 3, btn: ['批量显示', '批量掩藏', '取消'], btn1: function (layero, index) {
                                //上架
                                CommodityClassificationManagement.ClassState(data_arr, 2, 1);
                            }, btn2: function (layero, index) {
                                //下架
                                CommodityClassificationManagement.ClassState(data_arr, 1, 1);
                            }
                        });
                        break;
                }
            });

            table.on('edit(CategoryListings)', function (obj) {
                var load = layer.load(2);
                var admin = layui.admin;
                admin.req({
                    type: "POST",
                    url: '/Admin/Ajax/Page/GeneralClassEditor',
                    data: {
                        field: obj.field,
                        text: obj.value,
                        cid: obj.data.cid
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(load);
                        if (data.code == 1) {
                            layer.msg(data.msg, {icon: 1});
                        } else layer.msg(data.msg, {icon: 2});
                    },
                    error: function () {
                        layer.alert('获取失败！');
                    },
                });
            });

        });
    },
    DeleteClass: function (cid, Batch = false) {
        layer.alert('此操作无法撤销，是否执行？', {
            title: '是否删除分类' + cid + '?', icon: 3, btn: ['取消', '删除'], btn2: function (layero, index) {
                var index = layer.load(2);
                var admin = layui.admin;
                admin.req({
                    type: "POST",
                    url: '/Admin/Ajax/Page/DeleteGoodsClass',
                    data: {
                        cid: cid,
                        Batch: Batch,
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(index);
                        if (data.code == 1) {
                            layui.use(['table'], function () {
                                var table = layui.table;
                                table.reload('idTest', {});
                            });
                            layer.msg(data.msg, {icon: 1});
                        } else layer.msg(data.msg, {icon: 2});
                    },
                    error: function () {
                        layer.alert('加载失败！');
                    },
                });
            }
        });
    },
    ClassState: function (cid, type, Batch = false) {
        var index = layer.load(3);
        var admin = layui.admin;
        admin.req({
            type: "POST",
            url: '/Admin/Ajax/Page/ClassState',
            data: {
                state: (type == 1 ? 2 : 1),
                cid: cid,
                Batch: Batch,
            },
            dataType: "json",
            success: function (data) {
                layer.close(index);
                if (data.code == 1) {
                    layui.use(['table'], function () {
                        var table = layui.table;
                        table.reload('idTest', {});
                    });
                    layer.msg(data.msg, {icon: 1});
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.alert('加载失败！');
            },
        });
    }
};

//加价模板
var PriceTemplate = {
    PriceList: function () {
        layui.use('table', function () {
            var table = layui.table;
            table.render({
                elem: '#PriceList'
                , height: 388
                , url: '/Admin/Ajax/Page/PriceList/' //数据接口
                , page: true //开启分页
                , cols: [[ //表头
                    {field: 'pid', title: 'PID', width: 88, sort: true, fixed: 'left'}
                    , {field: 'name', title: '模板名称'}
                    , {field: 'image', title: '模板规则'}
                    , {field: 'date', title: '创建时间'}
                    , {field: 'cost', title: '操作'}
                ]]
            });
        });
    }
};

//商品货源
var CommoditySupplyGoods = {
    SupplyList: function () {
        layui.use('table', function () {
            var table = layui.table;
            table.render({
                elem: '#SupplyList'
                , height: 388
                , url: '/Admin/Ajax/Page/SupplyList/' //数据接口
                , page: true //开启分页
                , cols: [[ //表头
                    {field: 'id', title: 'ID', width: 88, sort: true, fixed: 'left'}
                    , {field: 'domain', title: '对接域名'}
                    , {field: 'state', title: '对接类型'}
                    , {field: 'date', title: '创建时间'}
                    , {field: 'cost', title: '操作'}
                ]]
            });
        });
    }
};