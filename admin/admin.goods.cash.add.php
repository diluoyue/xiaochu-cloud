<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城
// +----------------------------------------------------------------------
// | Creation: 2022/1/13 9:13
// +----------------------------------------------------------------------
// | Filename: admin.goods.cash.add.php
// +----------------------------------------------------------------------
// | Explain: 添加卡密
// +----------------------------------------------------------------------
$title = '添加商品兑换卡';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="./admin.goods.cash.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <?= $title ?>
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">商品ID
                        </label>
                        <input type="number" name="gid" lay-verify="required" class="form-control" value=""
                               placeholder="商品ID">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">生成数量
                        </label>
                        <input type="number" name="count" lay-verify="required" class="form-control" value=""
                               placeholder="生成的数量">
                    </div>

                    <button type="submit" lay-submit lay-filter="Preserve" class="btn btn-block btn-xs btn-success">
                        添加商品兑换卡
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
            layer.alert('是否要添加商品兑换卡？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    const is = layer.msg('添加中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=AddCardSecret',
                        data: data.field,
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg + '<hr>' + res.data.join('<br>') + '<hr>请记得复制保存，卡密只会显示一次！', {
                                    btn: ['继续添加', '返回列表'],
                                    icon: 1,
                                    btn1: function () {
                                        location.reload();
                                    },
                                    btn2: function () {
                                        location.href = './admin.goods.cash.list.php'
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
