<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/13 20:54
// +----------------------------------------------------------------------
// | Filename: login.php
// +----------------------------------------------------------------------
// | Explain: 登陆
// +----------------------------------------------------------------------

use Server\Server;

include '../includes/fun.global.php';
global $conf;
if ((int)$conf['hostSwitch'] !== 1) {
    show_msg('温馨提示', '当前站点的主机管理系统未开启！');
}
$UserData = Server::LoginStatus();
if ($UserData !== false) {
    header('Location: ./index.php');
}
$image = background::image();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>主机管理面板登陆</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="../assets/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="../assets/layuiadmin/style/admin.css" media="all">
    <link rel="stylesheet" href="../assets/layuiadmin/style/login.css" media="all">
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login"
     style="display: none;background-image: url(<?= background::Bing_random() ?>)">
    <div class="layadmin-user-login-main" style="background-color: #fff;">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2>主机管理中心</h2>
            <p></p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-username"
                       for="LAY-user-login-username"></label>
                <input type="text" name="username" id="LAY-user-login-username" lay-verify="required" placeholder="用户名"
                       class="layui-input">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password"></label>
                <input type="password" name="password" lay-verify="required"
                       placeholder="密码" class="layui-input">
            </div>
            <div class="layui-form-item">
                <div class="layui-row">
                    <div class="layui-col-xs7">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-vercode"></label>
                        <input type="text" name="vercode" lay-verify="required"
                               placeholder="图形验证码" class="layui-input">
                    </div>
                    <div class="layui-col-xs5">
                        <div style="margin-left: 10px;">
                            <img id="vc" src="../user/ajax.php?act=VerificationCode&n=LoginHost"
                                 onclick="this.src='../user/ajax.php?act=VerificationCode&n=LoginHost';"
                                 class="layadmin-user-login-codeimg">
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-form-item" style="margin-bottom: 20px;">
                <input type="checkbox" name="remember" lay-skin="primary" title="记住密码">
                <a href="../?mod=route&p=User" class="layadmin-user-jump-change layadmin-link"
                   style="margin-top: 7px;">用户后台</a>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit">登 入</button>
            </div>
        </div>
    </div>

    <div class="layui-trans layadmin-user-login-footer">
        <p>© All Rights Reserved</p>
    </div>

</div>
<script src="../assets/js/jquery-3.4.1.min.js"></script>
<script src="../assets/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '../assets/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'user'], function () {
        var $ = layui.$
            , form = layui.form;
        form.render();
        form.on('submit(LAY-user-login-submit)', function (obj) {
            let is = layer.msg('登陆中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST",
                url: 'ajax.php?act=LoginVerification',
                data: obj.field,
                dataType: "json",
                success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        layer.msg(res.msg, {
                            offset: '15px'
                            , icon: 1
                            , time: 1000
                        }, function () {
                            location.href = './index.php';
                        });
                    } else {
                        $("#vc").attr('src', '../user/ajax.php?act=VerificationCode&n=LoginHost');
                        $("input[name='vercode']").val('');
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                },
                error: function () {
                    layer.msg('服务器异常！');
                }
            });
        });
    });
</script>
</body>
</html>
