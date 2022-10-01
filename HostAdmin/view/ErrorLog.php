<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/2 22:56
// +----------------------------------------------------------------------
// | Filename: ErrorLog.php
// +----------------------------------------------------------------------
// | Explain: 错误日志
// +----------------------------------------------------------------------
include './header.php';
?>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-body" style="width: 100%;line-height: 22px;overflow: auto;">
            <pre class="layui-code" id="logs" lay-skin="notepad">主机错误日志载入中...</pre>
        </div>
    </div>
</div>
<style>
    .layui-code {
        font-family: "幼圆";
        width: 100%:
        white-space: pre;
        margin: 0px;
        background-color: #333;
        color: #fff;
        padding: 0 5px;
    }
</style>

<script src="../../assets/js/jquery-3.4.1.min.js"></script>
<script src="../../assets/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '../../assets/layuiadmin/'
    }).extend({
        index: 'lib/index'
    }).use(['index', 'table', 'form'], function () {
        var admin = layui.admin
            , form = layui.form;
        var load = layer.load(3);
        admin.req({
            type: "POST",
            url: '../ajax.php?act=SpaceGetSiteErrorLogs',
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 0) {
                    $("#logs").html((data.msg == '' ? '暂时没有日志' : data.msg));
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.close(load3);
                layer.alert('获取失败！');
            },
        });

        layui.code({
            elem: 'logs'
        });
    });
</script>
