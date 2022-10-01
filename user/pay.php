<?php

/**
 * 资金管理
 */

use Medoo\DB\SQL;

$title = '资金管理';
include 'header.php';
global $UserData, $conf;
$data_arr = reward::user_pays($UserData);
$DB = SQL::DB();
$withdrawal = $DB->get('withdrawal', [
    'uid' => $UserData['id'],
    'ORDER' => [
        'addtime' => 'DESC'
    ]
]);
$timehtm = md5($UserData['id'] . '晴玖');
$images = ROOT . 'assets/img/withdraw/' . $timehtm . '/' . $UserData['id'] . '.png';
if ($conf['PayConZFB'] == -1 && $conf['PayConWX'] == -1 && $conf['PayConQQ'] == -1) {
    $PayState = -1;
} else {
    $PayState = 1;
}
?>
<style>
    .input::-webkit-input-placeholder {
        color: white;
    }

    .input:-moz-placeholder {
        color: white;
    }

    .input:-ms-input-placeholder {
        color: white;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-<?= ($PayState == -1 ? '12' : '6') ?>">
            <div class="card widget-flat  shadow-lg bg-danger text-white"
                 style="background:  linear-gradient(to right, #f12711, #f5af19);">
                <div class="card-body" style="position: relative;">
                    <div class="float-right">
                        <h6 class="text-white text-uppercase mt-0" title="Revenue">今日增加</h6>
                        <h3 class="mb-2 mt-2"><?= round($data_arr['Count2'], 0) ?></h3>
                    </div>
                    <h6 class="text-white text-uppercase mt-0" title="Revenue">我的<?= $conf['currency'] ?></h6>
                    <h3 class="mb-2 mt-2"><?= $UserData['currency'] ?></h3>
                    <div class="row text-center">
                        <input class="layui-input input" type="text"
                               style="border-radius: 0.5em;width: 96%;margin-left: 2%;background-color: rgba(0,0,0,0);color:white"
                               placeholder="请填写充值卡的卡密" id="token" value=""/>
                        <div class="mt-2 text-center" style="text-align: center;width: 100%;">
                            <button onclick="get_token()" class="btn btn-danger btn-xs"
                                    style="background-color:rgb(25,226,179);">
                                使用充值卡
                            </button>
                            <?php if (!empty($conf['rechargepurchaseurl'])) { ?>
                                <a class="btn btn-danger btn-xs"
                                   href="<?= $conf['rechargepurchaseurl'] ?>"
                                   target="_blank""
                                                  style="background-color:rgb(119,125,196);">
                                                  购买充值卡
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6" <?= ($PayState == -1 ? 'style="display: none"' : '') ?>>
            <div class="card widget-flat  shadow-lg bg-danger text-white"
                 style="background: linear-gradient(to right,  #00d2ff, #3a7bd5)">
                <div class="card-body" style="position: relative;">
                    <div class="float-right">
                        <h6 class="text-white text-uppercase mt-0" title="Revenue">累计消费金额</h6>
                        <h3 class="mb-2 mt-2"><?= round($data_arr['Count1'], 8) ?>元</h3>
                    </div>
                    <h6 class="text-white text-uppercase mt-0" title="Revenue">我的钱包余额</h6>
                    <h3 class="mb-2 mt-2"><?= round($UserData['money'], 8) ?>元</h3>
                    <div class="row text-center">
                        <input class="layui-input input" type="number"
                               style="border-radius: 0.5em;width: 96%;margin-left: 2%;background-color: rgba(0,0,0,0);color:white"
                               placeholder="请填写充值金额" id="money" value=""/>
                        <div class="mt-2 text-center" style="text-align: center;width: 100%">
                            <?= ($conf['PayConWX'] == -1 ? '' : '<span class="btn btn-success btn-xs" onclick=get_data(\'wxpay\') >微信付款</span>') ?> <?= ($conf['PayConZFB'] == -1 ? '' : '<span class="btn btn-danger btn-xs"  onclick=get_data(\'alipay\') >支付宝付款</span>') ?> <?= ($conf['PayConQQ'] == -1 ? '' : '<span class="btn btn-primary btn-xs" onclick=get_data(\'qqpay\') >QQ付款</span>') ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    提现申请 - <a
                            href="javascript:layer.alert('<img src='+$('#GatheringFigure').attr('src')+' width=300 heigth=300 /> ')"
                            id="GatheringFigure"
                            src="<?= (file_exists($images) ? ROOT_DIR . 'assets/img/withdraw/' . $timehtm . '/' . $UserData['id'] . '.png?time=' . time() : ROOT_DIR . 'assets/img/pays.png') ?>"
                            class="badge badge-success-lighten">查看收款二维码</a> <a href="javascript:"
                                                                               class="badge badge-danger-lighten"
                                                                               id="GatheringFigures">上传新的收款二维码</a>
                </div>
                <div class="card-body">
                    <form class="form-horizontal layui-form">

                        <div class="form-group mb-2">
                            <label for="example-input-normal" style="font-weight: 500">提现收款方式</label>
                            <select class="custom-select mt-3" name="type">
                                <?php $type = explode(',', $conf['userdeposittype']); ?>
                                <option value="qqpay"
                                    <?= ($withdrawal['type'] == 'qqpay' ? ' selected ' : '') . ($type[0] <> 1 ? 'disabled' : '') ?>
                                >QQ收款 <?= ($type[0] <> 1 ? ' - 暂不支持' : '') ?></option>
                                <option value="wxpay"
                                    <?= ($withdrawal['type'] == 'wxpay' || $withdrawal['type'] == 'wxpai' ? ' selected ' : '') . ($type[1] <> 1 ? 'disabled' : '') ?>
                                >微信收款 <?= ($type[1] <> 1 ? ' - 暂不支持' : '') ?></option>
                                <option value="alipay"
                                    <?= ($withdrawal['type'] == 'alipay' ? ' selected ' : '') . ($type[2] <> 1 ? 'disabled' : '') ?>
                                >支付宝收款 <?= ($type[2] <> 1 ? ' - 暂不支持' : '') ?></option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">收款人姓名</label>
                            <input type="text" name="name" lay-verify="required" class="form-control"
                                   value="<?= $withdrawal['name'] ?>" placeholder="请填写收款人姓名">
                        </div>
                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">提现账号</label>
                            <input type="text" name="account_number" lay-verify="required" class="form-control"
                                   value="<?= $withdrawal['account_number'] ?>" placeholder="请填写提现账号">
                        </div>
                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">提现备注</label>
                            <input type="text" name="remarks" class="form-control" value="<?= $withdrawal['remarks'] ?>"
                                   placeholder="管理员会看到,可填可不填">
                        </div>
                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">提现金额 <span
                                        class="badge badge-danger-lighten">费率：<?= $conf['userdepositservice'] ?>%</span>
                                <span class="badge badge-primary-lighten"><?= $conf['userdepositmin'] ?>元起提</span></label>
                            <input type="number" name="money" class="form-control"
                                   value="<?= round($UserData['money'], 2) ?>" placeholder="提现金额">
                        </div>
                        <button type="submit" lay-submit lay-filter="withdrawal"
                                class="btn btn-block btn-xs btn-outline-success">发起提现请求
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    提现日志
                    <span class="badge badge-primary-lighten">待处理(<?= $DB->sum('withdrawal', 'money', ['uid' => $UserData['id'], 'state' => 3]) - 0 ?>元)</span>
                    <span class="badge badge-warning-lighten">累计提现(<?= $DB->sum('withdrawal', 'money', ['uid' => $UserData['id']]) - 0 ?>元) </span>
                    <span class="badge badge-success-lighten">成功提现(<?= $DB->sum('withdrawal', 'money', ['uid' => $UserData['id'], 'state' => 1]) - 0 ?>元)</span>
                </div>
                <div class="card-body">
                    <table class="layui-hide" id="test-table-totalRow" lay-filter="test-table-totalRow"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'bottom.php';
?>

<script>
    layui.use(['form', 'table', 'upload'], function () {
        var form = layui.form;
        var table = layui.table;
        var upload = layui.upload;

        var uploadInst = upload.render({
            elem: '#GatheringFigures',
            accept: 'images',
            acceptMime: 'image/*',
            exts: 'jpg|png|gif|bmp|jpeg',
            url: 'ajax.php?act=DoGatheringFigure',
            size: 1024,
            done: function (res, index, upload) {
                layer.msg(res.msg);
                $("#GatheringFigure").attr('src', res.src);
            },
            error: function () {
                layer.msg('收款码上传失败');
            }
        });

        form.on('submit(withdrawal)', function (data) {
            var img = $("#GatheringFigure").attr('src');
            if (img == '../assets/img/pays.png') {
                layer.msg('请先上传收款图！');
                return false;
            }
            layer.msg('正在处理中...', {
                time: 999999,
                icon: 16
            });
            $.ajax({
                type: "POST",
                url: 'ajax.php?act=WithdrawDeposit',
                data: data.field,
                dataType: "json",
                success: function (data) {
                    if (data.code == 1) {
                        layer.alert(data.msg, {
                            icon: 1,
                            end: function (layero, index) {
                                location.reload();
                            }
                        })
                    } else {
                        layer.msg(data.msg)
                    }
                },
                error: function () {
                    layer.alert('失败！');
                }
            });
            return false;
        });
        table.render({
            elem: '#test-table-totalRow',
            url: 'ajax.php?act=withdraw_deposit',
            toolbar: '#test-table-totalRow-toolbarDemo',
            title: '提现日志',
            id: 'idTest',
            cellMinWidth: 120,
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
                    field: 'money',
                    title: '提现金额',
                    sort: true,
                    totalRow: true
                }, {
                    field: 'arrival_amount',
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
                    field: 'remarks',
                    title: '提现备注',
                    sort: true,
                    totalRow: true
                }, {
                    field: 'type',
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

    function get_token() {
        let token = $("#token").val();
        if (token == '') {
            layer.msg('请将卡密填写完整！');
            return false;
        }
        layer.open({
            title: '温馨提示',
            content: '是否要使用此卡密？',
            btn: ['确定', '取消'],
            icon: 3,
            btn1: function () {
                let is = layer.msg('处理中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: "ajax.php?act=CarmiActivation",
                    data: {
                        token: token
                    },
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            layer.alert(res.msg, {
                                icon: 1, btn1: function () {
                                    location.reload();
                                }
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
        })
    }

    function get_data(type) {
        money = ($("#money").val() - 0);
        if (money <= 0) {
            layer.msg('请将金额填写完整！');
            return false;
        }
        var index = layer.msg('加载中...', {
            icon: 16,
            time: 9999999
        });
        $.ajax({
            type: "post",
            url: "ajax.php?act=user_pay",
            data: {
                money: money,
                type: type
            },
            dataType: "json",
            success: function (data) {
                layer.close(index);
                if (data.code == 1) {
                    layer.alert(data.msg, {
                        icon: 1
                    });
                } else if (data.code == 2) {
                    layer.open({
                        title: '订单创建成功',
                        content: '请点击下方按钮付款！',
                        btn: ['付款', '取消'],
                        btn1: function (layero, index) {
                            window.open(data.url);
                            layer.msg('付款成功了吗？,点击确定刷新界面', {
                                icon: 16,
                                btn: '确定',
                                btn1: function (layero, index) {
                                    location.reload();
                                },
                                time: 666666,
                                btnAlign: 'c',
                                anim: 3
                            })
                        },
                    });
                } else {
                    layer.alert(data.msg, {
                        icon: 2
                    });
                }
            },
            error: function () {
                layer.msg('充值失败！');
                layer.close(index);
            }
        });
    }
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