<?php

use lib\App\App;
use Medoo\DB\SQL;

if (!defined('IN_CRONLITE')) die;
global $_QET, $conf;
$DB = SQL::DB();
if (!isset($_QET['id'])) {
    show_msg('温馨提示', 'App任务ID获取失败，无法获取下载地址！');
}
$App = $DB->get('app', [
    'name', 'url', 'icon', 'download', 'content', 'id', 'endtime'
], [
    'id' => (int)$_QET['id'],
    'state' => 1,
]);
if (!$App) {
    show_msg('温馨提示', 'App数据获取失败，无法获取下载地址！');
}
$DownloadUrl = App::DownloadUrl($App['download'], $App['id']);
$size = filesize(($DownloadUrl['state'] === 1 ? ROOT : '') . $DownloadUrl['android_url']);
if (!$size || $size <= 0) {
    $size = '4.3 MB';
} else {
    $size = round($size / (1024 * 1024), 2) . '. MB';
}
if (empty($App['content'])) {
    $App['content'] = $conf['appcontent'];
}
?>
<!DOCTYPE html>
<html lang="zh-cmn">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no"/>
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta content="no-siteapp" http-equiv="Cache-Control"/>
    <title><?= $App['name'] ?> - 安卓和IOS苹果APP里致力于下单便捷化一站式程序</title>
    <meta name="description" content="<?= $App['name'] ?>APP是致力于下单便捷化一站式程序"/>
    <meta name="keywords" content="<?= $App['name'] ?>APP,<?= $App['name'] ?>自助下单APP,<?= $App['name'] ?>在线商城APP"/>
    <link rel="stylesheet" href="<?= ROOT_DIR ?>assets/app/static/css/default.min.css"/>
    <link rel="stylesheet" href="<?= ROOT_DIR ?>assets/app/static/css/main.css"/>
</head>
<body class="frame_wrap">
<div class="intro_banner">
    <div class="intro_banner_info">
        <div class="intro_banner_info_inner">
            <div class="qw_icon qw_icon_BannerLogo"></div>
            <div class="qw_icon qw_icon_BannerMobileLogo"></div>
            <div class="intro_banner_info_title"></div>
            <div class="intro_banner_info_downloadBtnWrap">
                <a id="downloadInMobile"
                   class="intro_banner_info_downloadBtn intro_banner_info_downloadBtn_ForMobile"
                   href="javascript:openAppUrl();">
                    <span class="qw_icon qw_icon_AppStore"></span>IOS下载
                </a>
                <a id="downIos" class="intro_banner_info_downloadBtn intro_banner_info_downloadBtn_ForDesktop"
                   href="javascript:openAppUrl();">
							<span class="intro_banner_info_downloadBtn_origin">
								<span class="qw_icon qw_icon_AppStore"></span>IOS下载
							</span>
                    <div class="intro_banner_info_downloadBtn_qrCodeWrap">
                        <div
                                class="intro_banner_info_downloadBtn_qrCode intro_banner_info_downloadBtn_qrCode_IOS">
                        </div>
                        <div class="intro_banner_info_downloadBtn_qrCodeText">扫描二维码下载</div>
                    </div>
                </a>
            </div>
            <a id="btn_bug" class="intro_banner_info_downloadBtn intro_banner_info_githubBtn"
               href="javascript:android_download();">
						<span class="intro_banner_info_downloadBtn_origin">
							<span class="qw_icon qw_icon_Andr"></span>安卓下载
						</span>
            </a>

            <div class="intro_banner_info_version" id="app_version_info"></div>
            <div class="intro_banner_info_version">最后更新: <span id="date"></span></div>
            <div style="color:#fff;margin-top:0.5em;">
                <?= $App['content'] ?>
            </div>
        </div>
    </div>
</div>

<div class="intro_cnt">

    <div class="intro_cnt_inside">

        <div class="intro_cnt_ability intro_column">

            <div class="intro_cnt_ability_list">

                <div class="intro_ability_item">
                    <div class="qw_icon qw_icon_GlobalConfig"></div>
                    <div class="intro_ability_item_title">更多丰富UI</div>
                    <div class="intro_ability_item_cnt">安卓和苹果IOS双更新，精心打磨界面每一处细节，从后台的演算到用户手中的绽放，每一幕都是精彩的演绎。</div>
                </div>

                <div class="intro_ability_item">
                    <div class="qw_icon qw_icon_Extension"></div>
                    <div class="intro_ability_item_title">数据多层加密</div>
                    <div class="intro_ability_item_cnt">在全新版本上，我们更加注重数据的安全性，对涉及到的隐私数据只加密存至本地不上传云端，让盗窃者无处可盗。
                    </div>
                </div>

                <div class="intro_ability_item">
                    <div class="qw_icon qw_icon_Component"></div>
                    <div class="intro_ability_item_title">多元模块化</div>
                    <div class="intro_ability_item_cnt">对每个功能全新的从0设计，从安全，美观，快捷三个因素综合出发，让每个模块以最完美的形态展现。</div>

                </div>

                <div class="intro_ability_item">
                    <div class="qw_icon qw_icon_Tool"></div>
                    <div class="intro_ability_item_title">高效的工具</div>
                    <div class="intro_ability_item_cnt">更多独家工具类的功能出现，对用户数据进行大量纵向计算给用户呈现出完美的预先分析。</div>
                </div>

            </div>

        </div>


    </div>
</div>

<div class="intro_foot">
    <div> Developed by <a target="_blank" class="qui_txtBold"></a>
    </div>
</div>

<div class="intro_weChatTip" id="browserTip">
    <div class="qui_mask qw_mask" id="browserTipMask"></div>
    <span class="qw_icon qw_icon_ShareArrow"></span>
    <div class="intro_weChatTip_cnt">
        点击右上角<br/>
        选择“在 Safari 中打开”
    </div>
</div>

<div class="intro_weChatTip" id="androidBrowserTip">
    <div class="qui_mask qw_mask"></div>
    <span class="qw_icon qw_icon_ShareArrow"></span>
    <div class="intro_weChatTip_cnt">
        点击右上角<br/>
        选择“在 浏览器 中打开” 或 “浏览器”
    </div>
</div>

<script src="<?= ROOT_DIR ?>assets/app/static/js/jquery.min.js"></script>
<script src="<?= ROOT_DIR ?>assets/app/static/js/layer/layer.js"></script>
<script src="<?= ROOT_DIR ?>assets/app/static/js/jr-qrcode.js"></script>
<script>
    var app_name = "<?= $App['name'] ?>";
    var app_version = 1,
        app_version_name = "1.0";
    var app_date = "<?=$App['endtime']?>";
    var app_android_url = "<?=$DownloadUrl['android_url']?>";
    var app_ios_url = "<?=$DownloadUrl['ios_url']?>";

    var app_icon = "/ajax.php?act=AppImage&id=<?=$App['icon']?>";
    $(".qw_icon_BannerLogo").css("background-size", "cover");
    $(".qw_icon_BannerMobileLogo").css("background-size", "cover");
    $(".qw_icon_BannerLogo").css("background-repeat", "no-repeat");
    $(".qw_icon_BannerMobileLogo").css("background-repeat", "no-repeat");
    $(".qw_icon_BannerLogo").css("background-image", "url(" + app_icon + ")");
    $(".qw_icon_BannerMobileLogo").css("background-image", "url(" + app_icon + ")");
    $(".intro_banner_info_title").html(app_name);
    $("#app_version_info").html("V" + app_version_name + " (Build " + app_version + ") - <?=$size?>");
    $("#date").html(app_date);
    $(".qui_txtBold").html(app_name + "APP");
    $(".qui_txtBold").attr('href', '<?=$App['url']?>');
    var base64_img = jrQrcode.getQrBase64(window.location.href);
    $(".intro_banner_info_downloadBtn_qrCode_IOS").css("background-image", "url(" + base64_img + ")");

    var browser = {
        versions: function () {
            var u = navigator.userAgent,
                app = navigator.appVersion;
            return { //移动终端浏览器版本信息
                trident: u.indexOf('Trident') > -1, //IE内核
                presto: u.indexOf('Presto') > -1, //opera内核
                webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
                gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
                mobile: !!u.match(/AppleWebKit.*Mobile/i) || !!u.match(
                    /MIDP|SymbianOS|NOKIA|SAMSUNG|LG|NEC|TCL|Alcatel|BIRD|DBTEL|Dopod|PHILIPS|HAIER|LENOVO|MOT-|Nokia|SonyEricsson|SIE-|Amoi|ZTE/
                ), //是否为移动终端
                ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
                android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
                iPhone: u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, //是否为iPhone或者QQHD浏览器
                iPad: u.indexOf('iPad') > -1, //是否iPad
                webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
            };
        }(),
        language: (navigator.browserLanguage || navigator.language).toLowerCase()
    }

    function isSafari() {
        return /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent) && !/MQQBrowser/.test(navigator
            .userAgent);
    }

    function IsPC() {
        var userAgentInfo = navigator.userAgent;
        var Agents = ["Android", "iPhone",
            "SymbianOS", "Windows Phone",
            "iPad", "iPod"
        ];
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) {
                flag = false;
                break;
            }
        }
        return flag;
    }

    function is_weixn_qq() {
        var ua = navigator.userAgent.toLowerCase();
        if (ua.match(/MicroMessenger\/[0-9]/i)) {
            return "weixin";
        }
        if (ua.match(/QQ\/[0-9]/i)) {
            return "QQ";
        }
        return false;
    }

    function openAppUrl() {
        if (browser.versions.iPhone || browser.versions.iPad || browser.versions.ios) {
            if (isSafari()) {
                window.location.href = app_ios_url;
                setTimeout(function () {
                    layer.open({
                        title: '提示',
                        content: '是否前往安装？<br>如无法跳转且提示"描述文件损坏"，请手动前往"设置"->"通用"->"描述文件"->"安装"',
                        btn: ['前往', '取消'],
                        btnAlign: 'c',
                        skin: 'layui-layer-molv',
                        yes: function (index) {
                            layer.close(index);
                            location.href = app_ios_url;
                        }
                    });
                }, 1500)
            } else {
                if (is_weixn_qq()) {
                    document.getElementById("browserTip").style.display = "block";
                }
                layer.msg('请用IOS系统自带浏览器[Safari]打开本页面');
            }
        }
    }

    function android_download() {
        if (!IsPC()) {
            if (is_weixn_qq()) {
                document.getElementById("androidBrowserTip").style.display = "block";
            } else {
                window.location.href = app_android_url;
            }
        } else {
            window.location.href = app_android_url;
        }
    }
</script>
</body>
</html>

