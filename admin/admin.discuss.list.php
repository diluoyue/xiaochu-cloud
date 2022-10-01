<?php

/**
 * Author：晴玖天
 * Creation：2020/4/27 21:56
 * Filename：admin.discuss.list.php
 * 评论管理
 */

use Medoo\DB\SQL;

$title = '评论管理';
include 'header.php';
$DB = SQL::DB();
$C1 = $DB->count('mark', [
    'state' => 1
]);
$C2 = $DB->count('mark', [
    'state' => 2
]);
$C3 = $DB->count('mark', [
    'state' => 3
]);
?>
<style>
    .image_sc {
        margin: 0.3em;
        box-shadow: 3px 3px 16px #eee;
        border-radius: 0.5em;
        width: 60px;
        height: 60px;
    }
</style>
<div class="row">
    <div class=" col-md-12 col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                评论管理 -
                <span class="badge badge-success-lighten">显示(<?= $C1 ?>)</span>
                <span class="badge badge-warning-lighten">待审核(<?= $C2 ?>)</span>
                <span class="badge badge-danger-lighten">驳回(<?= $C3 ?>)</span>
            </div>
            <div class="card-body" style="z-index: 1">
                <table class="layui-hide" id="totalRow" lay-filter="totalRow"></table>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>
<script type="text/html" id="toolbar">
    <div class="layui-btn-container">
        <button type="button" class="btn btn-primary btn-sm" lay-event="getCheckData"><i
                    class="mdi mdi-rocket mr-1"></i>
            <span>操作</span></button>
    </div>
</script>
<script>
    layui.use(['form', 'table', 'upload'], function () {
        var table = layui.table;
        table.render({
            elem: '#totalRow',
            url: 'ajax.php?act=Mark&type=List',
            toolbar: '#toolbar',
            cellMinWidth: 120,
            id: 'idTest',
            cols: [
                [{
                    type: 'checkbox',
                    fixed: 'left'
                },
                    {
                        field: 'operation',
                        templet: '#operation',
                        title: '操作',
                        fixed: 'left',
                        unresize: true,
                        sort: true,
                        width: 180
                    }, {
                    field: 'content',
                    title: '评论内容',
                    totalRow: true,
                    width: 180
                }, {
                    field: 'image',
                    templet: '#image',
                    title: '配图',
                    totalRow: true
                }, {
                    field: 'state',
                    templet: '#state',
                    title: '状态',
                    width: 80,
                    sort: true,
                    totalRow: true
                }, {
                    field: 'score',
                    title: '评分',
                    sort: true,
                    totalRow: true,
                    width: 80
                }, {
                    field: 'uid',
                    templet: '#uid',
                    title: '用户ID'
                }, {
                    field: 'gid',
                    templet: '#gid',
                    title: '商品ID'
                }, {
                    field: 'order',
                    templet: '#order',
                    title: '相关订单',
                    totalRow: true
                }, {
                    field: 'name',
                    title: '参数',
                    sort: true,
                    totalRow: true
                }, {
                    field: 'addtime',
                    title: '评论时间',
                    sort: true,
                    totalRow: true
                }
                ]
            ],
            page: true
        });
        table.on('toolbar(totalRow)', function (obj) {
            var checkStatus = table.checkStatus(obj.config.id);
            switch (obj.event) {
                case 'getCheckData':
                    var data = checkStatus.data;
                    let arr = [];
                    data.forEach(function (item, index) {
                        arr[index] = item.id;
                    });

                    layer.open({
                        title: '温馨提示',
                        content: '请选择需要执行的操作',
                        btn: ['批量通过', '批量驳回', '批量删除', '取消'],
                        icon: 3,
                        btn1: function () {
                            ajax(1, arr);
                        },
                        btn2: function () {
                            ajax(2, arr);
                        },
                        btn3: function () {
                            ajax(3, arr);
                        }
                    });
                    break;
            }
        });
    });

    function ajax(type, arr) {
        layer.load(2, {
            time: 666666
        });
        $.ajax({
            type: "post",
            url: 'ajax.php?act=Mark&type=stateAll',
            data: {
                state: type,
                arr: arr
            },
            dataType: "json",
            success: function (data) {
                layer.closeAll();
                if (data.code >= 0) {
                    layer.msg(data.msg, {
                        icon: 1
                    });
                    layui.use('table', function () {
                        table = layui.table;
                        table.reload('idTest', {});
                    });
                } else layer.msg(data.msg);
            }
        })
    }

    function image(img) {
        const arr = img.split('|');
        let con = '';
        let i = 1;
        $.each(arr, function (key, val) {
            if (i == 3) {
                s = '<br>';
                i = 1;
            } else {
                s = '';
                ++i;
            }
            con += '<img lay-src="' + val + '" class="image_sc" />' + s;

        });
        layer.open({
            title: '评论图片预览',
            content: con,
            skin: 'layui-layer-rim',
            btn: false,
            shade: [0.8, '#393D49'],
            shadeClose: true,
        });
        layui.use('flow', function () {
            var flow = layui.flow;
            flow.lazyimg();
        });
    }

    function dispose(id, state) {
        layer.open({
            title: '操作确认',
            content: '是否要执行此操作？',
            btn: ['确认执行', '取消'],
            icon: 3,
            yes: function () {
                layer.load(2, {
                    time: 666666
                });
                $.ajax({
                    type: "post",
                    url: 'ajax.php?act=Mark&type=state',
                    data: {
                        id: id,
                        state: state
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.closeAll();
                        if (data.code == 1) {
                            layer.msg(data.msg, {
                                icon: 1
                            });
                            layui.use('table', function () {
                                table = layui.table;
                                table.reload('idTest', {});
                            });
                        } else layer.msg(data.msg)
                    }
                })
            }
        });
    }
</script>
<script type="text/html" id="uid">
    <a href="admin.user.list.php?id={{ d.uid }}" target="_blank">{{ d.uid }}</a>
</script>
<script type="text/html" id="gid">
    <a href="admin.order.list.php?gid={{ d.gid }}" target="_blank">{{ d.gid }}</a>
</script>
<script type="text/html" id="order">
    <a href="admin.order.list.php?val={{ d.order }}" target="_blank">{{ d.order }}</a>
</script>
<script type="text/html" id="image">
    {{# if(d.image==''){ }}
    无图评论
    {{# }else{ }}
    <a href="javascript:image('{{ d.image }}')">查看图片</a>
    {{# } }}
</script>
<script type="text/html" id="operation">
    <buttom class="layui-btn layui-btn-xs" style="background-color: #00afff" onclick="dispose('{{d.id}}',1)">通过</buttom>
    <buttom class="layui-btn layui-btn-xs" style="background-color: rgba(255,160,8,0.6)"
            onclick="dispose('{{d.id}}',3)">驳回
    </buttom>
    <buttom class="layui-btn layui-btn-xs" style="background-color: #fb3418" onclick="dispose('{{d.id}}',4)">删除</buttom>
</script>

<script type="text/html" id="state">
    {{# if(d.state==3 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #fb3418">已驳回</buttom>
    {{# }else if(d.state==2 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: rgba(14,0,101,0.6)">审核中</buttom>
    {{# }else if(d.state==1 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #00cb5b">已通过</buttom>
    {{# } }}
</script>
