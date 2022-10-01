<?php
// +----------------------------------------------------------------------
// | Project: cloud
// +----------------------------------------------------------------------
// | Creation: 2021/6/2 17:50
// +----------------------------------------------------------------------
// | Filename: PseudoStatic.php
// +----------------------------------------------------------------------
// | Explain: 伪静态配置
// +----------------------------------------------------------------------
include './header.php';
global $_QET;
?>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            主目录伪静态规则,子目录伪静态可在域名绑定处设置!
        </div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote">请选择您的应用，若设置伪静态后，网站无法正常访问，请尝试设置回default</blockquote>
            <blockquote class="layui-elem-quote">您可以对伪静态规则进行修改，修改完后保存即可。</blockquote>
            <div class="layui-form layui-form-pane">
                <div class="layui-form-item">
                    <label class="layui-form-label">内置规则</label>
                    <div class="layui-input-block">
                        <select name="RewriteList" lay-filter="RewriteList"></select>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">规则内容</label>
                    <div class="layui-input-block">
                        <textarea placeholder="请填写伪静态规则,若看不懂请不要填写！" rows=8 class="layui-textarea"
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
            url: '../ajax.php?act=SpaceGetRewriteList',
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 0) {
                    //layer.msg('主目录伪静态数据获取成功！', {icon: 1});
                    content = '';
                    $.each(data.list, function (key, val) {
                        content += '<option value="' + val + '">' + val + '</option>';
                    });
                    $("select[name='RewriteList']").html(content);
                    $("textarea[name='RewriteCountent']").html(data.data);
                    form.render();
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.close(load3);
                layer.alert('获取失败！');
            },
        });

        form.on('select(RewriteList)', function (data) {
            var load = layer.load(2, {tiem: 999999});
            admin.req({
                type: "POST",
                url: '../ajax.php?act=SpaceFileBody',
                data: {Rewrite: data.value},
                dataType: "json",
                success: function (data) {
                    layer.close(load);
                    if (data.code == 0) {
                        $("textarea[name='RewriteCountent']").val(data['data']);
                    }
                },
                error: function () {
                    layer.close(load);
                    layer.alert('获取失败！');
                },
            });
        });

        form.on('submit(submit_Rewrite)', function (data) {
            var load = layer.load(3);
            admin.req({
                type: "POST",
                url: "../ajax.php?act=SpaceSaveFileBodys",
                data: {
                    data: $("textarea[name='RewriteCountent']").val(),
                },
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