<?php
// +----------------------------------------------------------------------
// | Project: cloud
// +----------------------------------------------------------------------
// | Creation: 2021/6/2 21:21
// +----------------------------------------------------------------------
// | Filename: SSL.php
// +----------------------------------------------------------------------
// | Explain: SSL配置
// +----------------------------------------------------------------------

include './header.php';
global $_QET;
?>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            配置SSL请自行准备好证书
        </div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote" id="cert_data" style="display: none">
                <span id="typessl"></span>
                认证域名：<span id="subject"></span><br>
                证书品牌：<span id="issuer"></span><br>
                到期时间：<span id="notAfter"></span><br>
                <button class="layui-btn layui-btn-xs layui-btn-danger" id="unssl" style="display: none">关闭证书
                </button>

                <button class="layui-btn layui-btn-xs" id="CoerceSSL" style="display: none">强制HTTPS
                </button>
            </blockquote>

            <div class="layui-form layui-form-pane">
                <div class="layui-row layui-col-space8">
                    <div class="layui-col-xs12 layui-col-sm6">
                        <div class="layui-form-item layui-form-text">
                            <label class="layui-form-label">密钥(KEY)</label>
                            <div class="layui-input-block">
                        <textarea placeholder="" rows=8 class="layui-textarea"
                                  name="key"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="layui-col-xs12 layui-col-sm6">
                        <div class="layui-form-item layui-form-text">
                            <label class="layui-form-label">证书(PEM格式)</label>
                            <div class="layui-input-block">
                        <textarea placeholder="" rows=8 class="layui-textarea"
                                  name="csr"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="layui-btn layui-btn-fluid layui-btn-sm" style="background-color: #4b2b9b"
                        lay-submit lay-filter="submit_SSL">保存并开启证书
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/jquery-3.4.1.min.js"></script>
<script src="../../assets/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '../../assets/layuiadmin/'
    }).extend({
        index: 'lib/index'
    }).use(['index', 'table', 'form'], function () {
        var admin = layui.admin
            , table = layui.table
            , form = layui.form;

        var load = layer.load(3);
        admin.req({
            type: "POST",
            url: '../ajax.php?act=SpaceGetSSL',
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 0) {
                    $("textarea[name='key']").html(data.key);
                    $("textarea[name='csr']").html(data.csr);

                    if (data.cert_data.subject != undefined) {
                        $("#cert_data").show(100);
                        $("#subject").text(data.cert_data.subject);
                        $("#notAfter").text(data.cert_data.notAfter);
                        $("#issuer").text(data.cert_data.issuer);
                        if (data.type >= 0) {
                            $("#typessl").html('<font color=#2e8b57>当前已部署：请在证书到期之前更换新的证书</font><br>')
                            $("#unssl").show(100);
                            $("#CoerceSSL").show(100);
                            if (data.httpTohttps) {
                                $("#CoerceSSL").text('关闭强制HTTPS');
                                $("#CoerceSSL").attr("typedata", "1")
                                $("#CoerceSSL").attr('class', 'layui-btn layui-btn-xs layui-btn-danger');
                            } else {
                                $("#CoerceSSL").text('开启强制HTTPS');
                                $("#CoerceSSL").attr('class', 'layui-btn layui-btn-xs');
                                $("#CoerceSSL").attr("typedata", "-1")
                            }
                        } else {
                            $("#typessl").html('<font color=red>当前未部署：请点击【保存】按钮完成此证书的部署</font><br>')
                        }
                    }
                    form.render();
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.close(load);
                layer.alert('获取失败！');
            },
        });


        form.on('submit(submit_SSL)', function (data) {
            var load = layer.load(3);
            admin.req({
                type: "POST",
                url: "../ajax.php?act=SpaceSetSSL",
                data: data.field,
                dataType: "json",
                success: function (data) {
                    layer.closeAll();
                    if (data.code >= 0) {
                        layer.alert(data.msg, {
                            icon: 1, end: function () {
                                location.reload();
                            }
                        });
                    } else layer.msg(data.msg, {icon: 2});
                },
                error: function () {
                    layer.closeAll();
                    layer.alert('获取失败！');
                },
            });
        });
    });

    $("#CoerceSSL").click(function () {
        var load = layer.load(3);
        $.ajax({
            type: "post",
            url: "../ajax.php?act=SpaceCoerceSSL",
            data: {
                type: $("#CoerceSSL").attr("typedata")
            },
            dataType: "json",
            success: function (data) {
                layer.closeAll();
                if (data.code >= 0) {
                    layer.alert(data.msg, {
                        icon: 1, end: function () {
                            location.reload();
                        }
                    });
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            },
            error: function () {
                layer.closeAll();
                layer.alert('加载失败！');
            }
        });
    })

    $("#unssl").click(function () {
        var load = layer.load(3);
        $.ajax({
            type: "post",
            url: "../ajax.php?act=SpaceCloseSSLConf",
            dataType: "json",
            success: function (data) {
                layer.closeAll();
                if (data.code >= 0) {
                    layer.alert(data.msg, {
                        icon: 1, end: function () {
                            location.reload();
                        }
                    });
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            },
            error: function () {
                layer.closeAll();
                layer.alert('加载失败！');
            }
        });
    })
</script>