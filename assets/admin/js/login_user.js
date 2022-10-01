/**
 * 用户/站长后台登陆操作类
 * @type {{do_admin_codes(*): void, do_admin_login_phone_verify(): void, do_login_wx: AdminLogin.do_login_wx, do_user_login: AdminLogin.do_user_login, do_admin_get_mobile_code(): (boolean|undefined)}}
 */
var AdminLogin = {
    IsPC() {
        var userAgentInfo = navigator.userAgent;
        var Agents = ["Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod"];
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) {
                flag = false;
                break;
            }
        }
        return flag;
    }, do_login_wx: function () {
        layer.open({
            title: '是否要使用QQ互联登陆？',
            content: '如果您是第一次使用QQ互联登陆会自动帮您生成新账号哦！',
            btn: ['确认登陆', '取消'],
            icon: 3,
            anim: 6,
            btn1: function () {
                location.href = '../user/ajax.php?act=QQ_QuickLogin';
                layer.msg('正在跳转登陆中...', {icon: 16, time: 8999999})
            },
        });
    }, do_sms_verification() {
        /**
         * 登陆短信验证，验证通过后可发送验证码！
         */
        layer.open({
            type: 1,
            title: '请先完成验证',
            btn: ['验证', '取消'],
            id: 'smsvis',
            content: '<div class="layui-row" style="padding: 1em">\n' + '                        <div class="layui-col-xs7">\n' + '                            <label class="layadmin-user-login-icon layui-icon layui-icon-vercode"></label>\n' + '                            <input type="text" name="vercode" style="padding-left: 38px"\n' + '                                   placeholder="输入验证码"\n' + '                                   class="layui-input">\n' + '                        </div>\n' + '                        <div class="layui-col-xs5">\n' + '                            <div style="margin-left: 10px;">\n' + '                                <img id="vc" src="../user/ajax.php?act=VerificationCode&n=Login_sms_vis"\n' + '                                     onclick="this.src=\'../user/ajax.php?act=VerificationCode&n=Login_sms_vis\';"\n' + '                                     class="layadmin-user-login-codeimg">\n' + '                            </div>\n' + '                        </div>\n' + '                    </div>',
            btn1: function () {
                let code = $("#smsvis input[name='vercode']").val();
                layer.msg('正在验证中,请稍后！', {icon: 16, time: 9999999});
                AdminLogin.do_admin_get_mobile_code(code, type = 2);
            },
            tipsMore: true,
            zIndex: layer.zIndex
        })
    }, do_admin_get_mobile_code(type = 2, state = 1) {
        var phone = $("input[name='phone']").val();
        if (phone == '') {
            layer.msg('请先填写手机号', {icon: 2});
            return false;
        }

        if (type == 2 && state == 1) {
            AdminLogin.do_sms_verification();
            return false;
        }

        $.ajax({
            type: "POST",
            url: '../user/ajax.php?act=Send_verification_code_login',
            data: {mobile: phone, code: type},
            dataType: "json",
            success: function (data) {
                layer.closeAll();
                $("#smsvis #vc").attr('src', '../user/ajax.php?act=VerificationCode&n=Login_sms_vis');
                $("#smsvis input[name='vercode']").val('');
                if (data.code > 0) {
                    layer.closeAll();
                    AdminLogin.do_admin_codes(60);
                    layer.alert(data.msg, {icon: 1});
                } else {
                    layer.alert(data.msg, {icon: 2});
                }
            },
            error: function () {
                layer.alert('服务器异常！');
            },
        });
    }, do_admin_codes(m) {
        $("#PhoneCode").attr('class', 'layui-btn layui-btn-fluid LgoinBtnDe');
        $("#PhoneCode").html('<span id="codes">' + m + '</span>秒后重发');
        $("#PhoneCode").attr('href', 'javascript:layer.msg(\'请填写短信！' + m + '秒后可重新发送！\')');
        var ms = $("#codes").text() - 0;
        if (ms <= 0) {
            $("#PhoneCode").attr('class', 'layui-btn layui-btn-fluid LgoinBtn');
            $("#PhoneCode").html('获取验证码');
            $("#PhoneCode").attr('href', 'javascript:AdminLogin.do_admin_get_mobile_code()');
        } else {
            setTimeout(function () {
                AdminLogin.do_admin_codes(ms - 1);
            }, 1000);
        }
    }, do_admin_login_phone_verify() {
        var code = $("input[name='vercode']").val();
        layer.open({
            title: '是否确认登陆？',
            content: '若确认验证码填写正确，可点击确定登陆后台！',
            btn: ['确认', '取消'],
            icon: 3,
            anim: 6,
            btn1: function (layero, index) {
                layer.msg('正在验证,请稍后', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: '../user/ajax.php?act=Send_verification_login',
                    data: {code: code},
                    dataType: "json",
                    success: function (data) {
                        if (data.code > 0) {
                            layer.alert(data.msg, {
                                icon: 1, title: '恭喜', end: function (lyaero, index) {
                                    location.reload();
                                }
                            });
                        } else {
                            layer.alert(data.msg, {icon: 2});
                        }
                    },
                    error: function () {
                        layer.alert('服务器异常！');
                    },
                });
            },
        });
    }, do_user_login: function () {
        var user = $("input[name='user']").val();
        var pass = $("input[name='pass']").val();
        var vercode = $("input[name='vercode']").val();
        if (user == '' || pass == '' || vercode == '') return;

        layer.msg('正在登陆中,请稍后...', {icon: 16, time: 9999999});

        $.ajax({
            type: "post", url: "../user/ajax.php?act=login_account", data: {
                user: user, pass: pass, vercode: vercode,
            }, dataType: "json", success: function (data) {
                layer.closeAll();
                $("#vc").attr('src', '../user/ajax.php?act=VerificationCode&n=Login_vc');
                $("input[name='vercode']").val('');
                if (data.code == 1) {
                    layer.msg(data.msg, {icon: 1});
                    layer.alert(data.msg, {
                        icon: 1, yes: function (layero, index) {
                            location.href = './index.php';
                        }
                    })
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            }, error: function () {
                layer.alert('加载失败！');
            }
        });
    }
};


layui.use('form', function () {
    var form = layui.form;

    form.on('submit(user_register)', function (data) {
        layer.open({
            title: '注册确认',
            content: '您的注册信息如下：<br>登陆账号：' + data.field['username'] + '<br>' + '登陆密码：' + data.field['password'] + '<br>' + '绑定QQ：' + data.field['qq'] + '<br>' + '请确认信息,若有勿,点取消,重新填写！',
            btn: ['注册', '取消'],
            icon: 3,
            btn1: function () {
                layer.msg('正在注册中,请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "post",
                    url: "../user/ajax.php?act=login_register",
                    data: data.field,
                    dataType: "json",
                    success: function (data) {
                        $("#vc").attr('src', '../user/ajax.php?act=VerificationCode&n=Login_res');
                        $("input[name='vercode']").val('');
                        layer.closeAll();
                        if (data.code == 1) {
                            layer.msg(data.msg, {icon: 1});
                            layer.alert(data.msg, {
                                icon: 1, yes: function () {
                                    location.href = './index.php';
                                }
                            })
                        } else {
                            layer.msg(data.msg, {icon: 2});
                        }
                    },
                    error: function () {
                        layer.alert('加载失败！');
                    }
                });
            }
        });

    });

});