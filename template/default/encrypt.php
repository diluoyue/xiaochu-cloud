<?php if (!defined('IN_CRONLITE')) die;
global $cdnpublic, $conf;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>请输入密码访问本站</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="max-age=30">
    <meta name="renderer" content="origin">
    <link rel="stylesheet" href="<?= ROOT_DIR ?>assets/css/encrypt.css"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no,user-scalable=0"/>
    <link rel="shortcut icon" href="<?= ROOT_DIR ?>assets/favicon.ico" type="image/x-icon"/>
    <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic; ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic; ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<div class="docs init-docs" id="doc">
    <div class="acss-header">
        <div class="verify-form">
            <div class="clearfix">
                <div class="verify-input ac-close clearfix">
                    <dl class="pickpw clearfix">
                        <dt>请输入访问密码：</dt>
                        <dd class="clearfix input-area">
                            <input class="QKKaIE LxgeIt" type="text" id="accessCode"/>
                            <div id="submitBtn">
                                <a class="submit-a g-button-blue-large" href="javascript:;" title="访问密码">
                                <span class="g-button-right">
                                    <span class="text submit-btn-text" style="width: auto;">验证密码</span>
                                </span>
                                </a>
                            </div>
                        </dd>
                    </dl>
                    <div style="margin-top:2em">
                        <?= $conf['PasswordAccessTips'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= ROOT_DIR ?>assets/layui/layui.all.js"></script>
<script src="<?= ROOT_DIR ?>assets/js/jquery-3.4.1.min.js"></script>
<script type="text/javascript">
    $("#submitBtn").click(function () {
        let is = layer.msg('验证中，请稍后...', {icon: 16, offset: "180px", time: 9999999});
        $.ajax({
            type: "POST",
            url: './main.php?act=PasswordAccessVerify',
            data: {
                pass: $("#accessCode").val(),
                token: "<?=$token?>"
            },
            dataType: "json",
            success: function (res) {
                layer.close(is);
                if (res.code == 1) {
                    layer.alert(res.msg, {
                        offset: "150px",
                        icon: 1, btn1: function () {
                            location.reload();
                        }
                    });
                } else {
                    layer.alert(res.msg, {
                        offset: "150px",
                        icon: 2
                    });
                }
            },
            error: function () {
                layer.msg('服务器异常！');
            }
        });
    })
</script>
</body>
</html>