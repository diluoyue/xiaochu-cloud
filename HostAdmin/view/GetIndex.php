<?php
// +----------------------------------------------------------------------
// | Project: cloud
// +----------------------------------------------------------------------
// | Creation: 2021/6/2 21:53
// +----------------------------------------------------------------------
// | Filename: GetIndex.php
// +----------------------------------------------------------------------
// | Explain: 默认文件配置
// +----------------------------------------------------------------------

include './header.php';
global $_QET;
?>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            主机默认文件配置
        </div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote">默认文档，每行一个，优先级由上至下。</blockquote>
            <div class="layui-form layui-form-pane">
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">规则内容</label>
                    <div class="layui-input-block">
                        <textarea placeholder="一行一个文件名称" rows=8 class="layui-textarea"
                                  name="RewriteCountent"></textarea>
                    </div>
                </div>
                <button class="layui-btn layui-btn-fluid layui-btn-sm" style="background-color: #1acaff"
                        lay-submit lay-filter="submit_Rewrite">保存
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
            , form = layui.form;

        var load = layer.load(3);
        admin.req({
            type: "POST",
            url: '../ajax.php?act=SpaceGetIndex',
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 0) {
                    content = '';
                    $.each(data.msg, function (key, val) {
                        if (key == 0) {
                            content += val;
                        } else content += '\n' + val;
                    });
                    $("textarea[name='RewriteCountent']").html(content);
                    form.render();
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.close(load3);
                layer.alert('获取失败！');
            },
        });

        form.on('submit(submit_Rewrite)', function (data) {
            layer.load(3);
            data.field.RewriteCountent = data.field.RewriteCountent.split('\n');
            admin.req({
                type: "POST",
                url: "../ajax.php?act=SpaceSetIndex",
                data: data.field,
                dataType: "json",
                success: function (data) {
                    layer.closeAll();
                    if (data.code == 0) {
                        layer.msg(data.msg, {
                            icon: 1
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
</script>
