<?php
/**
 * 联系客服
 */
if (!defined('IN_CRONLITE')) die;
QR_Code(2, 'http://wpa.qq.com/msgrd?v=3&uin=' . $conf['kfqq'] . '&site=qq&menu=yes', 5, 0, SYSTEM_ROOT . 'extend/log/ShopImage/' . md5($conf['kfqq']) . '.png');
$Image = href(2) . ROOT_DIR_S . '/includes/extend/log/ShopImage/' . md5($conf['kfqq']) . '.png';
$KF = [
    'qq' => $conf['kfqq'],
    'image' => ($conf['ServiceImage'] == '' ? $Image : ImageUrl($conf['ServiceImage'])),
    'url' => ($conf['YzfSign'] == '' ? -1 : 'https://yzf.qq.com/xv/web/static/chat/index.html?sign=' . $conf['YzfSign']),
    'tips' => htmlspecialchars_decode($conf['ServiceTips']),
    'GroupUrl' => ($conf['Communication'] == '' ? -1 : htmlspecialchars_decode($conf['Communication'])),
];
?>
<!DOCTYPE html>
<html lang="zh"
      style="font-size: 20px;<?= background::image() == false ? 'background:linear-gradient(to right, #bdc3c7, #2c3e50);' : background::image() ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover,user-scalable=no">
    <script> document.documentElement.style.fontSize = document.documentElement.clientWidth / 750 * 40 + "px";</script>
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-param" content="_csrf">
    <title><?= $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <!-- Vendor styles -->
    <link rel="icon" href="<?php echo $cdnserver; ?>assets/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/foxui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/style.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/foxui.diy.css">
    <link rel="shortcut icon" href="<?php echo $cdnserver; ?>assets/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/iconfont.css">
</head>

<style>
    .fix-iphonex-bottom {
        padding-bottom: 34px;
    }
</style>
<script>
    window.alert = function (name) {
        const iframe = document.createElement('IFRAME');
        iframe.style.display = 'none';
        iframe.setAttribute('src', 'data:text/plain,');
        document.documentElement.appendChild(iframe);
        window.frames[0].window.alert(name);
        iframe.parentNode.removeChild(iframe);
    };
</script>

<style>
    .custormer-page {
        background: #f3f3f3;
    }

    .custormer-page .fixed {
        position: fixed;
        width: 15rem;
        height: 20rem;
        top: 5%;
        /*margin-top: -11rem;*/
        left: 50%;
        margin-left: -7.5rem;
        /*background: #000;*/
    }

    .custormer-page .box {
        width: 15rem;
        height: 17rem;
        background: #fff;
        border-radius: 0.4rem;
        text-align: center;
        overflow: hidden;
    }

    .custormer-page .box p {
        line-height: 2rem;
        margin-top: 1rem;
        font-weight: bold;
        font-size: 0.8rem;
    }

    .custormer-page .box img {
        width: 13rem;
        height: 13rem;
    }

    .custormer-text {
        color: #969696;
        line-height: 2rem;
        font-size: 0.65rem;
        font-weight: bold;
    }

    .complaint {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        color: #2d8cf0;
        width: 100%;
        height: 2.5rem;
        line-height: 2.5rem;
        justify-content: center;
        background: white;
        border-radius: 6px;
        margin-top: 0.5rem;
    }

    .complaint img {
        width: 1.5rem;
        margin-right: 0.2rem;
    }
</style>
<body style="margin: auto;    max-width: 600px;">
<div id="body">
    <div class="custormer-page">
        <div class="fixed" style="position: absolute">
            <div class="box">
                <p>QQ号:<?= $KF['qq'] ?></p>
                <img style="box-shadow: 3px 3px 16px #eee"
                     src="<?= $KF['image'] ?>">
            </div>
            <hr>
            <div style="text-align: center;">
                <a href="http://wpa.qq.com/msgrd?v=3&uin=<?= $conf['kfqq'] ?>&site=qq&menu=yes"
                   target="_blank">[添加客服]</a> -
                <a href="<?= $KF['GroupUrl'] ?>" target="_blank">[添加QQ群]</a> -
                <a href="<?= $KF['url'] ?>" target="_blank">[在线咨询]</a>
            </div>
            <br>
            <div>
                <div class="complaint">
                    <?= $KF['tips'] ?>
                </div>
            </div>
        </div>

    </div>

</div>
<div class="fui-navbar" style="bottom:-34px;background-color: white;max-width: 600px">
</div>
<div class="fui-navbar" style="max-width: 600px;padding-top: 0.1em">
    <a href="index.php" class="nav-item  "> <span class="icon icon-home"></span> <span class="label">首页</span>
    </a>
    <a href="./?mod=query" class="nav-item "> <span class="icon icon-dingdan1"></span> <span
                class="label">订单</span> </a>
    <a href="./?mod=kf" class="nav-item "> <span class="icon icon-qq"></span> <span
                class="label">客服</span> </a>
    <a href="./?mod=route&p=User" class="nav-item "> <span class="icon icon-person2"></span> <span
                class="label">会员中心</span> </a>
</div>
</body>
</html>