<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/5 16:03
// +----------------------------------------------------------------------
// | Filename: admin.recharge.add.php
// +----------------------------------------------------------------------
// | Explain: 充值卡生成
// +----------------------------------------------------------------------

$title = '充值卡生成';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="admin.recharge.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <?= $title ?>
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">卡密类型
                        </label>
                        <select name="type" lay-filter="type">
                            <option value="1">余额充值卡</option>
                            <option value="2">积分充值卡</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">充值卡名称
                        </label>
                        <input type="text" name="name" lay-verify="required" class="form-control" value=""
                               placeholder="充值卡名称">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">充值卡面额
                        </label>
                        <input type="number" name="money" lay-verify="required" class="form-control" value=""
                               placeholder="充值卡面额">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">生成数量
                        </label>
                        <input type="number" name="count" lay-verify="required" class="form-control" value="10"
                               placeholder="生成的数量"/>
                    </div>

                    <button type="submit" lay-submit lay-filter="Preserve" class="btn btn-block btn-xs btn-success">
                        开始生成
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script>
    layui.use(['upload', 'form'], function () {
        var form = layui.form;
        form.on('submit(Preserve)', function (data) {
            layer.alert('是否要生成充值卡？', {
                icon: 3,
                btn: ['生成', '取消'],
                btn1: function () {
                    const is = layer.msg('正在生成中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=RechargeAdd',
                        data: data.field,
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    btn: ['继续生成', '返回列表'],
                                    icon: 1,
                                    btn1: function () {
                                        location.reload();
                                    },
                                    btn2: function () {
                                        location.href = './admin.recharge.list.php'
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
            });
        });
    });
</script>
