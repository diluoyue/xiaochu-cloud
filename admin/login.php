<?php
include '../includes/fun.global.php';
DirectoryProtection();
if (!empty($_SESSION['ADMIN_TOKEN'])) header("Location:./index.php");
$image = background::image();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>登入 - 站长后台</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" type="text/css" href="../assets/layui/css/layui.css"/>
    <link rel="stylesheet" href="../assets/layuiadmin/style/admin.css" media="all">
    <link rel="stylesheet" href="../assets/layuiadmin/style/login.css" media="all">
    <link rel="stylesheet" href="../assets/admin/css/login.css" media="all">
</head>
<!--[if lt IE 9]>
<script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
<script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<body style="<?= ($image == false ? 'background-image: linear-gradient(130deg, #fc5c7d, #6a82fb);' : $image) ?>">
<div id="loading" style="opacity: 1;display:block;">
    <div id="loading-center">
        <div id="loading-center-absolute">
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
            <div class="object"></div>
        </div>
    </div>
</div>
<style>
    @media screen and (max-width: 788px) {
        .nbi {
            width: 94% !important;
            margin-top: -3.5em;
        }
    }

    .AdminImageLogin {
        margin: auto;
        display: block;
        box-shadow: 3px 3px 16px #eee;
        border-radius: 0.5em;
    }
</style>
<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">
    <div class="layadmin-user-login-main nbi" style="background-color: white;border-radius: 0.3em;opacity: 0.9;">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2 class="FontColor"><?= $conf['sitename'] ?></h2>
            <p>站长登陆，<?= $conf['sitename'] ?>管理后台</p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form" id="Content">
            <?php if (empty($_QET['login'])) { ?>
                <div id="form" style="display: block">
                    <div class="layui-form-item">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-username"></label>
                        <input type="text" name="user" lay-verify="required" placeholder="请输入服务端登陆账号或域名独立账户"
                               class="layui-input">
                    </div>

                    <div class="layui-form-item">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-about"></label>
                        <input type="password" name="pass" lay-verify="required" placeholder="请输入服务端密码账号或域名独立密码"
                               class="layui-input">
                    </div>

                    <div class="layui-row">
                        <div class="layui-col-xs7">
                            <label class="layadmin-user-login-icon layui-icon layui-icon-vercode"></label>
                            <input type="text" name="vercode" lay-verify="required" style="padding-left: 38px"
                                   placeholder="输入验证码" class="layui-input">
                        </div>
                        <div class="layui-col-xs5">
                            <div style="margin-left: 10px;">
                                <img id="vc" src="ajax.php?act=VerificationCode&n=Login_uvc"
                                     onclick="this.src='ajax.php?act=VerificationCode&n=Login_uvc';"
                                     class="layadmin-user-login-codeimg">
                            </div>
                        </div>
                    </div>

                    <button class="layui-btn layui-btn-fluid" lay-submit
                            style="background-color: #ff7235;margin-top: 1em" onclick="AdminLogin.do_user_login()">登 入
                    </button>
                </div>
            <?php } else { ?>
                <div id="form" style="display: block">
                    <div class="layui-form-item">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-cellphone"></label>
                        <input type="text" name="phone" lay-verify="required" placeholder="请输入已绑定的手机号"
                               class="layui-input">
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-row">
                            <div class="layui-col-xs8">
                                <label class="layadmin-user-login-icon layui-icon layui-icon-vercode"></label>
                                <input type="text" name="vercode" lay-verify="required" placeholder="请输入短信验证码"
                                       class="layui-input">
                            </div>
                            <div class="layui-col-xs4">
                                <a class="layui-btn layui-btn-fluid LgoinBtn" id="PhoneCode"
                                   href="javascript:AdminLogin.do_admin_get_mobile_code()">
                                    获取验证码
                                </a>
                            </div>
                        </div>
                    </div>

                    <button class="layui-btn layui-btn-fluid" lay-submit style="background-color: #ff7235"
                            onclick="AdminLogin.do_admin_login_phone_verify()">登 入
                    </button>
                </div>
            <?php } ?>

            <div id="Weix" style="display:none ">
                <div class="layui-form-item">
                    <div class="layui-row">
                        <div class="layui-col-xs8">
                            <label class="layadmin-user-login-icon layui-icon layui-icon-vercode"></label>
                            <input type="text" name="token" placeholder="也可输入token值完成验证！" class="layui-input">
                        </div>
                        <div class="layui-col-xs4">
                            <a class="layui-btn layui-btn-fluid LgoinBtn Token"
                               onclick="AdminLogin.do_admin_login_token()">
                                开始验证
                            </a>
                        </div>
                    </div>
                </div>
                <div class="layuiadmin-card-text WeixText">
                    请尽快输入验证码哦
                </div>
            </div>
            <div class="layui-trans layui-form-item layadmin-user-login-other">
                <label>其他登陆方式</label>
                <?php if (empty($_QET['login'])) { ?>
                    <a href="?login=1" class="layui-icon layui-icon-cellphone LoginIcon" title="手机号登陆"></a>
                <?php } else { ?>
                    <a href="login.php" class="layui-icon layui-icon-survey LoginIcon" title="账号密码登陆"></a>
                <?php } ?>
                <i title="APP验证登陆" onclick="AdminLogin.do_login_app()"><img src="../assets/img/app.png" width="28"
                                                                            height="28"/> </i>
                <i title="APP扫码登陆" onclick="AdminLogin.do_login_scan()"><img src="../assets/img/scan.png" width="28"
                                                                             height="28"/> </i>
                <a href="https://cdn.79tian.com/api/wxapi/view/login.php" target="_blank"
                   class="layadmin-user-jump-change layadmin-link">登陆云端</a>
            </div>
        </div>
    </div>

    <div class="layui-trans layadmin-user-login-footer">
        <p style="color: white">© 2020 - 2025 <a href="../" style="color: white"
                                                 target="_blank"><?= $conf['sitename'] ?></a></p>
    </div>
</div>

<script src="../assets/layui/layui.all.js"></script>
<script src="../assets/js/jquery-3.4.1.min.js"></script>
<script src="../assets/admin/js/login.js?vs=<?= $accredit['versions'] ?>"></script>
<script>
    window.onload = function () {
        let loading = document.getElementById('loading');
        let jis = 100;
        for (let i = jis; i >= 0; i--) {
            setTimeout(function () {
                let sum = loading.style.opacity - 0;
                if (sum > 0.5) {
                    loading.style.opacity = '' + i / 100 + '';
                } else {
                    loading.style.display = 'none';
                }
            }, 5 * (jis - i));
        }
    }
</script>
</body>

</html>
