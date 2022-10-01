<?php
$title = '添加用户';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="admin.user.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <?= $title ?>
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">用户名称
                        </label>
                        <input type="text" name="name" lay-verify="required" class="form-control" value=""
                               placeholder="用户名">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">绑定QQ
                        </label>
                        <input type="text" name="qq" lay-verify="required" class="form-control" value=""
                               placeholder="用户绑定QQ,用于获取头像,邮箱等">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">账户余额
                        </label>
                        <input type="number" name="money" lay-verify="required" class="form-control" value="0"
                               placeholder="用户账号初始余额">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">账户积分
                        </label>
                        <input type="number" name="currency" lay-verify="required" class="form-control" value="0"
                               placeholder="用户账号初始积分">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">账户等级
                        </label>
                        <input type="number" name="grade" lay-verify="required" class="form-control" value="1"
                               placeholder="用户账号初始等级">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">绑定手机
                        </label>
                        <input type="text" name="mobile" class="form-control" value="" placeholder="用户绑定手机,可留空">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">登陆账号
                        </label>
                        <input type="text" name="username" lay-verify="required" class="form-control" value=""
                               placeholder="用户登陆账号">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">登陆密码
                        </label>
                        <input type="text" name="password" lay-verify="required" class="form-control" value=""
                               placeholder="用户登陆密码">
                    </div>


                    <button type="submit" lay-submit lay-filter="Preserve" class="btn btn-block btn-xs btn-success">
                        添加用户
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
            layer.alert('是否要添加新用户？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    const is = layer.msg('添加用户中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=UserAdd',
                        data: data.field,
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    btn: ['继续添加', '返回列表'],
                                    icon: 1,
                                    btn1: function () {
                                        location.reload();
                                    },
                                    btn2: function () {
                                        location.href = './admin.user.list.php'
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
