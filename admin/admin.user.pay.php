<?php

/**
 * Author：晴玖天
 * Creation：2020/3/24 13:30
 * Filename：admin.user.pay.php
 */

use Medoo\DB\SQL;

$title = '提现管理';
include 'header.php';
$DB = SQL::DB();
$P1 = $DB->sum('withdrawal', 'money', [
        'state' => 3
    ]) - 0;
$P2 = $DB->sum('withdrawal', 'money', [
        'state' => 2,
    ]) - 0;
$P3 = $DB->sum('withdrawal', 'money', [
        'state' => 1
    ]) - 0;
?>

<div class="row">
    <div class=" col-md-12 col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                提现管理 -
                <span class="badge badge-primary-lighten">待处理(<?= $P1 ?>元)</span>
                <span class="badge badge-warning-lighten">已退回(<?= $P2 ?>元)</span>
                <span class="badge badge-success-lighten">已打款(<?= $P3 ?>元)</span>
            </div>
            <div class="card-body" style="z-index: 1">
                <table class="layui-hide" id="test-table-totalRow" lay-filter="test-table-totalRow"></table>
            </div>
        </div>
    </div>
</div>
<style>
    .layui-table-page {
        width: 100%;
        overflow: hidden;
        overflow-x: auto;
    }
</style>
<?php include 'bottom.php'; ?>
<script>
    layui.use(['form', 'table', 'upload'], function () {
        var table = layui.table;
        table.render({
            elem: '#test-table-totalRow',
            url: 'ajax.php?act=withdraw_deposit',
            toolbar: '#test-table-totalRow-toolbarDemo',
            title: '提现日志',
            cellMinWidth: 120,
            id: 'idTest',
            size: 'sm',
            cols: [
                [{
                    type: 'checkbox',
                    fixed: 'left'
                }, {
                    field: 'id',
                    title: 'ID',
                    fixed: 'left',
                    unresize: true,
                    sort: true,
                    width: 60
                }, {
                    field: 'operation',
                    templet: '#operation',
                    title: '操作',
                    fixed: 'left',
                    unresize: true,
                    sort: true,
                    width: 120
                }, {
                    field: 'uid',
                    title: '用户ID'
                }, {
                    field: 'type',
                    templet: '#type',
                    title: '收款方式'
                }, {
                    field: 'name',
                    title: '收款姓名',
                    totalRow: true
                }, {
                    field: 'account_number',
                    title: '收款账号',
                    totalRow: true
                }, {
                    field: 'image',
                    templet: '#image',
                    title: '收款图片',
                    totalRow: true
                }, {
                    field: 'money',
                    title: '提现金额',
                    sort: true,
                    totalRow: true
                }, {
                    field: 'arrival_money',
                    title: '实际到账',
                    sort: true,
                    totalRow: true
                }, {
                    field: 'state',
                    templet: '#state',
                    title: '提现状态',
                    sort: true,
                    totalRow: true
                }, {
                    field: 'result_code',
                    title: '处理结果',
                    sort: true,
                    totalRow: true
                }, {
                    field: 'endtime',
                    title: '处理时间',
                    sort: true,
                    totalRow: true
                }, {
                    field: 'addtime',
                    title: '提现时间',
                    sort: true,
                    totalRow: true
                }]
            ],
            page: true
        });
    });

    function dispose(id) {

        layer.open({
            title: '是否要执行此操作?',
            content: '这是一道保护锁,防止点错',
            icon: 3,
            anim: 3,
            btn: ['取消', '是的我要执行'],
            btn2: function () {
                layer.msg('正在加载中...', {
                    time: 999999,
                    icon: 16
                });
                $.ajax({
                    type: "post",
                    url: 'ajax.php?act=withdraw_deposit_data',
                    data: {
                        id: id
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.closeAll();
                        if (data.code == 1) {
                            //根据数据来
                            layer.open({
                                title: '已计算当前用户所有待提现金额 | ' + data.count + '次之和',
                                content: '<center><h4>请转账<font color=red>' + data.money + '元</font>给他完成提现 <a style="color: turquoise" target="_blank" href="admin.user.log.php?uid=' + data.uid + '" lay-text="用户(' + data.uid + ')收益日志">[收益日志]</a></h4><hr><img src="' + data.image + '" width="300" height="300" /><hr>姓名：' + data.name + '<br>收款账号：' + data.account + '<br>提现备注：' + data.remark + '</center>	',
                                btn: ['取消', '已经转账后点我', '审核不通过'],
                                btn2: function (layero, index) { //通过
                                    content = '提现审核通过,已帮您提现至指定账户,有任何问题请联系客服处理';
                                    title = '请填写成功反馈';
                                    layer.prompt({
                                        formType: 2,
                                        value: content,
                                        title: title,
                                        area: ['300px', '300px']
                                    }, function (value, index, elem) {
                                        layer.msg('正在处理中', {
                                            icon: 16,
                                            time: 999999
                                        });
                                        $.ajax({
                                            type: "post",
                                            url: "ajax.php?act=withdraw_deposit_result",
                                            async: true,
                                            typeData: "json",
                                            data: {
                                                type: 1,
                                                uid: data.uid,
                                                id: id,
                                                money: data.money,
                                                money_ar: data.money_ar,
                                                result: value
                                            },
                                            success: function (redss) {
                                                layer.closeAll();
                                                layer.msg('处理完成', {
                                                    icon: 1,
                                                    btnAlign: 'c',
                                                    btn: '好的',
                                                    end: function (layero, index) {
                                                        location.reload();
                                                    }
                                                })
                                            },
                                            error: function () {
                                                layer.msg('数据错误');
                                            }
                                        });
                                    });
                                },
                                btn3: function (layero, index) { //不通过
                                    layer.prompt({
                                        formType: 3,
                                        value: data.money_ar,
                                        title: '请填写退款金额！',
                                    }, function (value2, index, elem) {
                                        content = '账单金额异常,提现失败,已帮您退回' + value2 + '元余额,有任何问题请联系客服！';
                                        title = '请填写失败原因';
                                        layer.prompt({
                                            formType: 2,
                                            value: content,
                                            title: title,
                                            area: ['300px', '300px']
                                        }, function (value, index, elem) {
                                            layer.msg('正在处理中', {
                                                icon: 16,
                                                time: 999999
                                            });
                                            $.ajax({
                                                type: "post",
                                                url: "ajax.php?act=withdraw_deposit_result",
                                                async: true,
                                                typeData: "json",
                                                data: {
                                                    type: 2,
                                                    uid: data.uid,
                                                    id: id,
                                                    money: value2,
                                                    result: value,
                                                    money_ar: data.money_ar
                                                },
                                                success: function (redss) {
                                                    layer.closeAll();
                                                    layer.msg('处理完成', {
                                                        icon: 1,
                                                        btnAlign: 'c',
                                                        btn: '好的',
                                                        end: function (layero, index) {
                                                            location.reload();
                                                        }
                                                    })
                                                },
                                                error: function () {
                                                    layer.msg('数据错误');
                                                }
                                            });
                                        });

                                    });
                                },
                            })
                        } else if (data.code == 2) {
                            layer.msg(data.msg, {
                                icon: 1,
                                btnAlign: 'c',
                                btn: '好的',
                                end: function (layero, index) {
                                    location.reload();
                                }
                            })
                        } else {
                            layer.msg(data.msg, {
                                icon: 2,
                                btnAlign: 'c',
                                btn: '好的',
                                end: function (layero, index) {
                                    location.reload();
                                }
                            })
                        }
                    },
                    error: function () {
                        layer.alert('失败！');
                    }
                });
            }
        })
    }
</script>

<script type="text/html" id="operation">
    {{# if(d.state==3 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #00afff" onclick="dispose('{{d.id}}')">处理</buttom>
    <a class="layui-btn layui-btn-xs" style="background-color: rgba(255,160,8,0.6)"
       href="admin.user.log.php?uid={{ d.uid }}">日志</a>
    {{# }else{ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: rgba(255,0,63,0.6)" onclick="dispose('{{d.id}}')">
        删除
    </buttom>
    <a class="layui-btn layui-btn-xs" style="background-color: rgba(255,160,8,0.6)"
       href="admin.user.log.php?uid={{ d.uid }}">日志</a>
    {{# } }}
</script>

<script type="text/html" id="type">
    {{# if(d.type=='alipay' ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #ff6c86">支付宝</buttom>
    {{# }else if(d.type=='wxpay'||d.type=='wxpai'){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #37c532">微信收款</buttom>
    {{# }else{ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #ff6f58">QQ收款</buttom>
    {{# } }}
</script>

<script type="text/html" id="image">
    <a href="javascript:layer.alert('<img src={{ d.image }} width=350 height=350 />')">查看图片</a>
</script>

<script type="text/html" id="state">
    {{# if(d.state==3 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #5c3dff">待处理</buttom>
    {{# }else if(d.state==2 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: rgba(14,0,101,0.6)">已退款</buttom>
    {{# }else if(d.state==1 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #00cb5b">已完成</buttom>
    {{# } }}
</script>
