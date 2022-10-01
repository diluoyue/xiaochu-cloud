<?php
// +----------------------------------------------------------------------
// | Project: cloud
// +----------------------------------------------------------------------
// | Creation: 2021/6/2 15:05
// +----------------------------------------------------------------------
// | Filename: PHP.php
// +----------------------------------------------------------------------
// | Explain: php版本设置
// +----------------------------------------------------------------------
include './header.php';
global $_QET;
?>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            PHP版本配置
        </div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote">
                请根据程序设置对应的PHP版本
            </blockquote>
            <div class="layui-form layui-form-pane">
                <div class="layui-form-item">
                    <label class="layui-form-label">PHP版本</label>
                    <div class="layui-input-block">
                        <select name="php" lay-filter="php"></select>
                    </div>
                </div>
                <button class="layui-btn layui-btn-fluid layui-btn-sm" style="background-color: #ce2e15"
                        lay-submit lay-filter="submit_php">保存
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
            url: '../ajax.php?act=SpacePhpVersions',
            data: {
                id: $("#id").val(),
            },
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 0) {
                    //layer.msg('php版本数据获取成功！', {icon: 1});
                    content = '';
                    $.each(data.list, function (key, val) {
                        content += '<option ' + (data.Version == val.version ? 'selected' : '') + ' value="' + val.version + '">' + val.name + '</option>';
                    });
                    $("select[name='php']").html(content);
                    form.render();
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.close(load1);
                layer.alert('获取失败！');
            },
        });
        form.on('submit(submit_php)', function (data) {
            var load = layer.load(3);
            admin.req({
                type: "POST",
                url: '../ajax.php?act=SpacePhpSave',
                data: data.field,
                dataType: "json",
                success: function (data) {
                    layer.close(load);
                    if (data.code == 0) {
                        layer.msg(data.msg, {
                            icon: 1
                        });
                    } else layer.msg(data.msg, {icon: 2});
                },
                error: function () {
                    layer.close(load);
                    layer.alert('获取失败！');
                },
            });
        });
    });
</script>
