<?php
require_once("model/builder/AlipayTradePrecreateContentBuilder.php");
require_once("AlipayTradeService.php");
// 创建请求builder，设置请求参数
$qrPayRequestBuilder = new AlipayTradePrecreateContentBuilder();
$qrPayRequestBuilder->setOutTradeNo($Pay['order']);
$qrPayRequestBuilder->setTotalAmount($Pay['money']);
$qrPayRequestBuilder->setSubject($Pay['name']);
// 调用扫码方法
$qrPay = new \AlipayTradeService($config);
$qrPayResult = $qrPay->qrPay($qrPayRequestBuilder);
// 返回状态值
$status = $qrPayResult->getTradeStatus();
$response = $qrPayResult->getResponse();
if ($status == 'SUCCESS') {
    $code_url = $response->qr_code;
} else if ($status == 'FAILED') {
    show_msg('温馨提示', '支付宝创建订单二维码失败！[' . $response->sub_code . ']' . $response->sub_msg, '4');
} else {
    show_msg('系统状态异常！' . print_r($response), '4');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="Content-Language" content="zh-cn"/>
    <meta name="renderer" content="webkit"/>
    <title>支付宝扫码支付 - <?= $conf['sitename'] ?></title>
    <link href="/includes/lib/soft/controller/apay/assets/css/alipay_pay.css?i=1" rel="stylesheet" media="screen"/>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
</head>
<body>
<div class="body">
    <h1 class="mod-title"><span class="ico-wechat"></span><span class="text">支付宝扫码支付</span></h1>
    <div class="mod-ct">
        <div class="order">
        </div>
        <div class="amount">
            ￥<?= round($Pay['money'], 2) ?>
        </div>
        <div class="qr-image" id="qrcode" data-url="<?= $code_url ?>"></div>
        <div class="detail" id="orderDetail">
            <dl class="detail-ct" style="display: none;">
                <dt>
                    购买物品
                </dt>
                <dd id="productName">
                    <?= $Pay['name'] ?>
                </dd>
                <dt>
                    商户订单号
                </dt>
                <dd id="billId">
                    <?= $Pay['order'] ?>
                </dd>
                <dt>
                    创建时间
                </dt>
                <dd id="createTime">
                    <?= $Pay['addtime'] ?>
                </dd>
            </dl>
            <a href="javascript:qralipay.OrderLog()" class="arrow"><i class="ico-arrow"></i></a>
        </div>
        <div class="tip">
            <span class="dec dec-left"></span>
            <span class="dec dec-right"></span>
            <div class="ico-scan"></div>
            <div class="tip-text">
                <p>请使用支付宝扫一扫</p>
                <p>扫描二维码完成支付</p>
            </div>
        </div>
        <div class="tip-text">
        </div>
    </div>
    <div class="foot">
        <div class="inner">
            <div id="J_downloadInteraction" class="download-interaction download-interaction-opening">
                <div class="inner-interaction">
                    <p class="download-opening">正在打开支付宝<span class="download-opening-1">.</span><span
                                class="download-opening-2">.</span><span class="download-opening-3">.</span></p>
                    <p class="download-asking">如果没有打开支付宝，<a id="J_downloadBtn" href="javascript:"
                                                            onclick="qralipay.rouse();">请点此重新唤起</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
<script src="//cdn.staticfile.org/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script>
    // 当面付调用类
    var qralipay = {
        qrcode: function () { //生成二维码
            $('#qrcode').qrcode({
                text: $("#qrcode").attr('data-url'),
                width: 230,
                height: 230,
                foreground: "#000000",
                background: "#ffffff",
                typeNumber: -1
            });
        },
        OrderLog: function () { //查看订单详情
            if ($('#orderDetail').hasClass('detail-open')) {
                $('#orderDetail .detail-ct').slideUp(500, function () {
                    $('#orderDetail').removeClass('detail-open');
                });
            } else {
                $('#orderDetail .detail-ct').slideDown(500, function () {
                    $('#orderDetail').addClass('detail-open');
                });
            }
        },
        OrderMonitor: function (time = 3000) { //订单监控 3s 1轮询
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/ajax.php?act=OrderState",
                timeout: 10000, //ajax请求超时时间10s
                data: {
                    order: "<?php echo $Pay['order']?>",
                    type: 2
                },
                success: function (data, textStatus) {
                    //从服务器得到数据，显示数据并继续查询
                    if (data.code == 1) {
                        if (confirm("您已支付完成，需要跳转到订单页面吗？")) {
                            window.location.href = '/?mod=route&p=Order';
                        } else {
                            // 用户取消
                        }
                    } else {
                        setTimeout(function () {
                            qralipay.OrderMonitor();
                        }, time);
                    }
                },
                //Ajax请求超时，继续查询
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (textStatus == "timeout") {
                        setTimeout(function () {
                            qralipay.OrderMonitor();
                        }, 1000);
                    } else { //异常
                        setTimeout(function () {
                            qralipay.OrderMonitor();
                        }, time);
                    }
                }
            });
        },
        rouse: function () { //若是手机则唤醒支付宝支付
            var scheme = 'alipays://platformapi/startapp?saId=10000007&qrcode=';
            scheme += encodeURIComponent($("#qrcode").attr('data-url'));
            if (navigator.userAgent.indexOf("Safari") > -1) {
                window.location.href = scheme;
            } else {
                var iframe = document.createElement("iframe");
                iframe.style.display = "none";
                iframe.src = scheme;
                document.body.appendChild(iframe);
            }
        }
    };
    qralipay.qrcode();
    qralipay.rouse();
    setTimeout(function () {
        qralipay.OrderMonitor();
    }, 3000);
</script>
</body>
</html>