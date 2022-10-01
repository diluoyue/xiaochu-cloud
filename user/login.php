<?php
/**
 * Author：晴玖天
 * Creation：2020/3/29 12:36
 */
include '../includes/fun.global.php';
global $conf, $_QET, $cdnserver, $accredit, $cdnpublic;
if ((int)$conf['ShutDownUserSystem'] === -1) {
    show_msg('温馨提示', $conf['ShutDownUserSystemCause'], 1, '/');
}
if (!empty($_COOKIE['THEKEY'])) {
    header("Location:./index.php");
}
$image = background::image();
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title><?= (empty($_QET['register']) ? '登入' : '注册') ?> - 店长后台 - <?= $conf['sitename'] ?></title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" type="text/css" href="../assets/layui/css/layui.css"/>
    <link rel="stylesheet" href="../assets/layuiadmin/style/admin.css" media="all">
    <link rel="stylesheet" href="../assets/layuiadmin/style/login.css" media="all">
    <link rel="stylesheet" href="../assets/admin/css/login.css?t=<?= $accredit['versions'] ?>"
          media="all">
</head>
<!--[if lt IE 9]>
<script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
<script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<body>
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
</style>
<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login"
     style="display: none;width: 100%;height: auto;<?= ($image == false ? 'background: linear-gradient(130deg, #a770ef, #cf8bf3, #fdb99b);' : $image) ?>;">
    <div class="layadmin-user-login-main nbi" style="background-color: white;border-radius: 0.3em;opacity: 0.9;">
        <a href="<?= (empty($_QET['mode']) && empty($_QET['register']) ? '../' : './') ?>"
           class="layui-icon layui-icon-left"
           style="position: absolute;margin: 0.6em;background-image: -webkit-linear-gradient(125deg, #29b9ff, #ff5b40);-webkit-background-clip: text;-webkit-text-fill-color: transparent;"></a>
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2 class="FontColor"><?= $conf['sitename'] ?></h2>
            <p><img src="<?= $conf['logo'] ?>" width="18" height="18"/> <?= $conf['sitename'] ?>用户管理后台</p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <?php if ($conf['AccountPasswordLogin'] == 1 && empty($_QET['mode'])) {
                if ($_QET['register'] <> 1) {
                    ?>
                    <div class="layui-form-item">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-username"></label>
                        <input type="text" name="user" lay-verify="required" placeholder="请输入登陆账号" class="layui-input">
                    </div>

                    <div class="layui-form-item">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-about"></label>
                        <input type="password" name="pass" lay-verify="required" placeholder="请输入登陆密码"
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
                                <img id="vc" src="ajax.php?act=VerificationCode&n=Login_vc"
                                     onclick="this.src='ajax.php?act=VerificationCode&n=Login_vc';"
                                     class="layadmin-user-login-codeimg">
                            </div>
                        </div>
                    </div>

                    <button class="layui-btn layui-btn-fluid" lay-submit
                            style="background-color: #ff7235;margin-top: 1em" onclick="AdminLogin.do_user_login()">登 入
                    </button>

                    <div class="layui-trans layui-form-item layadmin-user-login-other">
                        <label>快捷登陆</label>
                        <i class="layui-icon layui-icon-login-qq LoginIcon" onclick="AdminLogin.do_login_wx()"
                           title="QQ登陆"></i>
                        <?php if ($conf['sms_switch_user'] == 1) { ?>
                            <a href="?mode=1" class="layui-icon layui-icon-cellphone LoginIcon" title="手机号登陆"></a>
                        <?php } ?>

                        <a href="?register=1" class="layadmin-user-jump-change layadmin-link">注册帐号</a>
                    </div>
                    <?php
                } else {
                    ?>

                    <div class="layui-form-item">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-username"></label>
                        <input type="text" name="qq" lay-verify="required" placeholder="请输入绑定QQ"
                               class="layui-input">
                    </div>

                    <div class="layui-form-item">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-username"></label>
                        <input type="text" name="username" lay-verify="required" placeholder="请输入登陆账号"
                               class="layui-input">
                    </div>

                    <div class="layui-form-item">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-about"></label>
                        <input type="password" name="password" lay-verify="required" placeholder="请输入登陆密码"
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
                                <img id="vc" src="ajax.php?act=VerificationCode&n=Login_res"
                                     onclick="this.src='ajax.php?act=VerificationCode&n=Login_res';"
                                     class="layadmin-user-login-codeimg">
                            </div>
                        </div>
                    </div>
                    <button class="layui-btn layui-btn-fluid" lay-filter="user_register" lay-submit
                            style="background-color: #ff7235;margin:1.5em 0;">注 册
                    </button>
                    <?php
                }
            } else {
                //开启快捷登陆
                if ($conf['sms_switch_user'] == 1) { ?>
                    <div id="form" style="display: block">
                        <div class="layui-form-item">
                            <label class="layadmin-user-login-icon layui-icon layui-icon-cellphone"></label>
                            <input type="text" name="phone" lay-verify="required" placeholder="请输入手机号，若是第一次登陆会帮您注册！"
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
                    <div class="layui-trans layui-form-item layadmin-user-login-other">
                        <label>其他登陆方式</label>
                        <i class="layui-icon layui-icon-login-qq LoginIcon" onclick="AdminLogin.do_login_wx()"
                           title="登陆站长后台"></i>
                        <?php if ($conf['AccountPasswordLogin'] == 1) { ?>
                            <a href="./login.php" class="layui-icon layui-icon-survey LoginIcon" title="账号密码登陆"></a>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <center style="margin-bottom: 3em;">
                        <span class="layui-icon layui-icon-login-qq LoginIcon"
                              style="font-size: 4em;margin: auto;display: block" onclick="AdminLogin.do_login_wx()"
                              title="登陆站长后台"></span>
                        <h4 style="margin-top: 1em">登陆/注册</h4>
                    </center>
                    <?php if ($conf['AccountPasswordLogin'] == 1) { ?>
                        <div class="layui-trans layui-form-item layadmin-user-login-other">
                            <label>其他登陆方式</label>
                            <a href="./login.php" class="layui-icon layui-icon-survey LoginIcon" title="账号密码登陆"></a>
                        </div>
                    <?php } ?>
                <?php }
            } ?>
        </div>
    </div>

    <div class="layui-trans layadmin-user-login-footer">
        <p style="color: white">© 2020 - 2025 <a href="../" style="color: white"
                                                 target="_blank"><?= $conf['sitename'] ?></a></p>
    </div>
</div>
<script src="../assets/layui/layui.all.js"></script>
<script src="../assets/js/jquery-3.4.1.min.js"></script>
<script src="../assets/admin/js/login_user.js?r=<?= $accredit['versions'] ?>"></script>
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
