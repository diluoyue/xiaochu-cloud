<?php
// +----------------------------------------------------------------------
// | Project: cloud
// +----------------------------------------------------------------------
// | Creation: 2021/6/2 18:02
// +----------------------------------------------------------------------
// | Filename: RunDirectory.php
// +----------------------------------------------------------------------
// | Explain: 网站运行目录配置
// +----------------------------------------------------------------------
include './header.php';
global $_QET;
?>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            网站运行目录[子域名可单独设置目录]
        </div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote">
                部分程序需要指定二级目录作为运行目录，如ThinkPHP5，Laravel等
            </blockquote>
            <div class="layui-form layui-form-pane">
                <div class="layui-form-item">
                    <label class="layui-form-label">运行目录</label>
                    <div class="layui-input-block">
                        <select name="dirs"></select>
                    </div>
                </div>
                <button class="layui-btn layui-btn-fluid layui-btn-sm" style="background-color: #cc6dcf"
                        lay-submit lay-filter="submit_dirs">保存
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
            url: '../ajax.php?act=SpaceGetDir',
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 0) {
                    content = '';
                    $.each(data.list, function (key, val) {
                        content += '<option ' + (data.runPath == val ? 'selected' : '') + ' value="' + val + '">' + val + '</option>';
                    });
                    $("select[name='dirs']").html(content);
                    form.render();
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.close(load2);
                layer.alert('获取失败！');
            },
        });

        form.on('submit(submit_dirs)', function (data) {
            var load = layer.load(3);
            admin.req({
                type: "POST",
                url: '../ajax.php?act=SpaceSetSiteRunPath',
                data: data.field,
                dataType: "json",
                success: function (data) {
                    layer.close(load);
                    if (data.code == 0) {
                        layer.msg(data.msg, {
                            icon: 1, end: function () {
                            }
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