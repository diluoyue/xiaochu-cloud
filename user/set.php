<?php
/**
 * Author：晴玖天
 * Creation：2020/5/30 19:25
 * Filename：set.php
 * 账号密码绑定
 */

$title = '信息管理';
include 'header.php';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <!-- Profile -->
            <div class="card bg-white">
                <div class="card-body profile-user-box">
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="media">
                            <span class="float-left m-2 mr-4 hide"><img src="<?= UserImage($UserData) ?>"
                                                                        style="height: 100px;" alt=""
                                                                        class="rounded-circle img-thumbnail"></span>
                                <div class="media-body">
                                    <h5 class="mt-1 mb-1">ID：<?= $UserData['id'] ?></h5>
                                    <h5 class="mt-1 mb-1">
                                        KEY：<span><?= $UserData['token'] == null ? '未生成' : '<a id="token" href="javascript:layer.alert($(\'#token\').attr(\'data-token\'),{title:\'请手动复制密钥\'})" data-token="' . $UserData['token'] . '">查看密钥</a>' ?></span>
                                    </h5>
                                    <h5 class="mt-1 mb-1">账户余额：<?= $UserData['money'] ?>元</h5>
                                    <p class="mt-3 font-16 text-success">
                                        我的订单 <a href="../?mod=route&p=Order" target="_blank">查询</a></p>
                                </div> <!-- end media-body-->
                            </div>
                        </div> <!-- end col-->
                        <div class="col-sm-4">
                            <div class="text-center mt-sm-0 mt-3 text-sm-right">
                                <button type="button" class="btn btn-primary btn-rounded mt-1"
                                        onclick="Docking.add_token()">
                                    <i class="mdi mdi-account-edit mr-1"></i> 生成对接密钥
                                </button>
                                <button type="button" class="btn btn-warning btn-rounded mt-1"
                                        onclick="Docking.set_ip()">
                                    <i class="mdi mdi-account-edit mr-1"></i> 设置IP白名单
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    以下为您添加的白名单IP，| 为分隔符
                </div>
                <div class="card-body text-success"
                     id="ip_data"><?= $UserData['ip_white_list'] == null ? '一个白名单IP都没有,您必须添加一个才可以进行商品对接！' : $UserData['ip_white_list'] ?></div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    信息管理
                </div>
                <div class="card-body">
                    <div class="card-body">
                        <form class="form-horizontal layui-form">
                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">用户名，可自由修改</label>
                                <input type="text" name="name" lay-verify="required"
                                       class="form-control"
                                       value="<?= $UserData['name'] ?>"
                                       placeholder="请填写新的手机号">
                            </div>
                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">绑定QQ，用于登陆或绑定账号</label>
                                <input type="number" name="qq" lay-verify="required"
                                       class="form-control"
                                       value="<?= $UserData['qq'] ?>"
                                       placeholder="请填写新的QQ">
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">绑定手机号，用于登陆或绑定账号</label>
                                <input type="number" name="mobile" lay-verify="required"
                                       class="form-control"
                                       value="<?= $UserData['mobile'] ?>"
                                       placeholder="请填写新的手机号">
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">登陆账号，主站开启了账号密码登陆时可使用</label>
                                <input type="text" name="username" lay-verify="required"
                                       class="form-control"
                                       value="<?= $UserData['username'] ?>"
                                       placeholder="请填写新的登陆账号">
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">登陆密码，主站开启了账号密码登陆时可使用</label>
                                <input type="text" name="password"
                                       class="form-control"
                                       value=""
                                    <?= ($UserData['password'] == '' ? 'lay-verify="required"' : '') ?>
                                       placeholder="留空不修改,请填写新的密码">
                            </div>

                            <button type="submit" lay-submit lay-filter="withdrawal"
                                    class="btn btn-block btn-xs btn-outline-success">保存信息
                            </button>
                        </form>
                    </div>
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
        form.on('submit(withdrawal)', function (data) {
            layer.msg('正在保存中...', {
                time: 999999,
                icon: 16
            });

            layer.open({
                title: '操作确认',
                content: '是否要执行此操作？',
                btn: ['确认执行', '取消'],
                icon: 3,
                btn1: function (layero, index) {
                    $.ajax({
                        type: "POST",
                        url: 'ajax.php?act=user_set',
                        data: data.field,
                        dataType: "json",
                        success: function (data) {
                            if (data.code == 1) {
                                layer.alert(data.msg, {
                                    icon: 1, end: function (layero, index) {
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
                }
            });


            return false;
        });
    });

    var Docking = { //商品对接设置类
        add_token: function () {
            $.ajax({
                type: "post",
                url: "ajax.php?act=add_token",
                dataType: "json",
                success: function (data) {
                    if (data.code == 1) {
                        $("#token").attr('data-token', data.token);
                        layer.alert('您新的密钥为：<br>' + data.token, {
                            btn1: function (layero, index) {
                                location.reload();
                            }
                        });
                    } else {
                        layer.msg('生成失败');
                    }
                },
                error: function () {
                    layer.alert('生成失败！');
                }
            });
        },
        set_ip: function () {
            var ips = $("#ip_data").text();
            var ips = ips.split("|");
            console.log(ips);
            var content = ips.join('\n');
            layer.prompt({
                formType: 2,
                value: content,
                title: '一行一个IP！',
                area: ['350px', '350px'] //自定义文本域宽高
            }, function (value, index, elem) {
                layer.close(index);
                var value = value.split('\n');
                console.log(value);
                $.ajax({
                    type: "post",
                    url: "ajax.php?act=ip_data",
                    data: {
                        ip_data: value
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.code == 1) {
                            $("#ip_data").text(data.ip_data);
                            layer.alert(data.msg);
                        } else {
                            layer.msg(data.msg);
                        }
                    },
                    error: function () {
                        layer.alert('设置失败！');
                    }
                });
            });
        }
    }

</script>
