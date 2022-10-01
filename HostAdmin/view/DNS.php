<?php
/**
 * Author：晴天 QQ：1186258278
 * Creation：2020/4/17 21:28
 * Filename：DNS.php
 * 域名解析管理
 */
include './header.php';
?>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header">
                    相关提示
                </div>
                <div class="layui-card-body">
                    <blockquote class="layui-elem-quote">当前托管服务器可绑定域名数量：<font id="domain_sum" color="red"
                                                                              size="3"></font>个
                    </blockquote>
                    <blockquote class="layui-elem-quote">增加域名绑定，域名在添加绑定之前请先<font color="red"
                                                                                 size="3">CNAME</font>解析指向<font
                                color="red" id="domain" size="3"></font></blockquote>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header">
                    域名绑定
                </div>
                <div class="layui-card-body">
                    <div class="layui-form layui-form-pane">
                        <div class="layui-form-item layui-form-text">
                            <label class="layui-form-label">域名</label>
                            <div class="layui-input-block">
                                <textarea placeholder="每行填写一个域名，默认为80端口
泛解析添加方法 *.domain.com
如另加端口格式为 www.domain.com:88" class="layui-textarea" name="domain"></textarea>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">绑定目录</label>
                            <div class="layui-input-block">
                                <select name="dirs" id="dirslist"></select>
                            </div>
                        </div>
                        <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="submit_add">添加</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header">域名列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="test-table-totalRow" lay-filter="test-table-totalRow"></table>
                </div>
            </div>
        </div>
        <div id="Rewrite" style="display: none">
            <div class="layui-form layui-form-pane">
                <div class="layui-form-item">
                    <label class="layui-form-label">快速编辑</label>
                    <div class="layui-input-block">
                        <select name="RewriteList"></select>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">规则内容</label>
                    <div class="layui-input-block">
                        <textarea placeholder="请填写伪静态规则,若看不懂请不要填写！" class="layui-textarea"
                                  name="RewriteCountent"></textarea>
                    </div>
                </div>
            </div>
            <blockquote class="layui-elem-quote">请选择您的应用，若设置伪静态后，网站无法正常访问，请尝试设置回default</blockquote>
            <blockquote class="layui-elem-quote">您可以对伪静态规则进行修改，修改完后保存即可。</blockquote>
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
        table.render({
            elem: '#test-table-totalRow'
            , url: '../ajax.php?act=SpaceDNSList'
            , toolbar: '#test-table-totalRow-toolbarDemo'
            , title: '托管服务器域名列表'
            , cellMinWidth: 80
            , cols: [[
                {field: 'name', templet: "#domain", title: '域名'}
                , {field: 'port', title: '端口'}
                , {field: 'path', templet: "#path", title: '绑定目录'}
                , {field: 'addtime', title: '添加日期'}
                , {field: 'operation', templet: "#operation", title: '操作'}
            ]]
            , page: true,
            done: function (res, curr, count) {
                content = '';
                $.each(res.dirs, function (key, val) {
                    content += '<option value="' + val + '">' + val + '</option>';
                });
                $("#dirslist").html(content);
                $("#domain").text(res.domain);
                $("#domain_sum").text(res.domain_sum);
                form.render();
            }
        });

        $.ajax({
            type: "post",
            url: "../ajax.php?act=SpaceVi",
            dataType: "json",
        });

        form.on('select(RewriteList)', function (data) {
            var load = layer.load(2, {tiem: 999999});
            var admin = layui.admin;
            admin.req({
                type: "POST",
                url: '../ajax.php?act=SpaceFileBody',
                data: {Rewrite: data.value},
                dataType: "json",
                success: function (data) {
                    layer.close(load);
                    if (data.code == 0) {
                        $("#RewriteCountent").val(data['data']);
                    } else layer.msg(data.msg, {icon: 2});
                },
                error: function () {
                    layer.close(load);
                    layer.alert('获取失败！');
                },
            });
        });

        form.on('submit(submit_add)', function (data) {
            var load = layer.load(2);
            data.field['domain'] = data.field['domain'].split('\n');
            var admin = layui.admin;
            admin.req({
                type: "POST",
                url: '../ajax.php?act=SpaceDNSAdd',
                data: data.field,
                dataType: "json",
                success: function (data) {
                    layer.close(load);
                    if (data.code == 0) {
                        layer.alert(data.msg, {
                            icon: 1, btn1: function () {
                                window.location.reload();
                            }
                        });
                    }
                },
                error: function () {
                    layer.close(load);
                    layer.alert('获取失败！');
                },
            });
        });
    });

    function SpaceDeleteDomain(name) {
        layer.alert('是否要删除域名：' + name, {
            btn: ['确认删除', '取消'],
            title: '警告',
            icon: 3, btn1: function () {
                let is = layer.msg('正在删除中,请勿刷新或关闭页面...', {time: 99999, icon: 16, shade: [0.8, '#393D49']});
                var admin = layui.admin;
                admin.req({
                    url: '../ajax.php?act=SpaceDeleteDomain',
                    data: {name: name},
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 0) {
                            layer.alert(res.msg, {
                                icon: 1, btn1: function () {
                                    window.location.reload();
                                }
                            })
                        }
                    }
                })
            }
        })
    }

    function GetDirRewrite(name, path) {
        layer.load(2, {tiem: 999999});
        $.ajax({
            type: "post",
            url: "../ajax.php?act=SpaceDirRewrite",
            data: {name: name, path: path},
            dataType: "json",
            success: function (data) {
                layer.closeAll();
                if (data.code == 1) {
                    layer.open({
                        title: '配置伪静态规则',
                        id: 'Rewrites',
                        content: $("#Rewrite").html(),
                        btn: ['保存', '取消'],
                        success: function () {
                            form = layui.form;
                            $("#Rewrites textarea[name='RewriteCountent']").attr('id', 'RewriteCountent');
                            $("#RewriteCountent").val(data['data']);
                            content = '';
                            $.each(data.rlist, function (key, val) {
                                content += '<option value="' + val + '">' + val + '</option>';
                            });
                            $("#Rewrites select[name='RewriteList']").attr('lay-filter', 'RewriteList');
                            $("#Rewrites select[name='RewriteList']").html(content);
                            form.render();
                        },
                        btn1: function () {
                            layer.load(2, {tiem: 999999});
                            var admin = layui.admin;
                            admin.req({
                                type: "POST",
                                url: "../ajax.php?act=SpaceSaveFileBody",
                                data: {
                                    path: data['filename'],
                                    data: $("#RewriteCountent").val(),
                                    name: name
                                },
                                dataType: "json",
                                success: function (data) {
                                    layer.closeAll();
                                    if (data.code == 0) {
                                        layer.msg(data.msg, {icon: 1});
                                    }
                                },
                                error: function () {
                                    layer.closeAll();
                                    layer.alert('获取失败！');
                                },
                            });
                        }
                    })
                } else if (data.code == 2) {
                    layer.alert(data.msg, {
                        icon: 3, btn: ['确定', '取消'], btn1: function () {
                            layer.load(2, {tiem: 999999});
                            $.ajax({
                                type: "post",
                                url: "../ajax.php?act=SpaceDirRewrite",
                                data: {name: name, add: 1},
                                dataType: "json",
                                success: function (data) {
                                    layer.closeAll();
                                    if (data.code == 1) {
                                        layer.open({
                                            title: '配置伪静态规则',
                                            id: 'Rewrites',
                                            content: $("#Rewrite").html(),
                                            btn: ['保存', '取消'],
                                            success: function () {
                                                form = layui.form;
                                                $("#Rewrites textarea[name='RewriteCountent']").attr('id', 'RewriteCountent');
                                                $("#RewriteCountent").val(data['data']);
                                                content = '';
                                                $.each(data.rlist, function (key, val) {
                                                    content += '<option value="' + val + '">' + val + '</option>';
                                                });
                                                $("#Rewrites select[name='RewriteList']").attr('lay-filter', 'RewriteList');
                                                $("#Rewrites select[name='RewriteList']").html(content);
                                                form.render();
                                            },
                                            btn1: function () {
                                                layer.load(2, {tiem: 999999});
                                                var admin = layui.admin;
                                                admin.req({
                                                    type: "POST",
                                                    url: "../ajax.php?act=SpaceSaveFileBody",
                                                    data: {
                                                        path: data['filename'],
                                                        data: $("#RewriteCountent").val(),
                                                        name: name
                                                    },
                                                    dataType: "json",
                                                    success: function (data) {
                                                        layer.closeAll();
                                                        if (data.code == 0) {
                                                            layer.msg(data.msg, {icon: 1});
                                                        }
                                                    },
                                                    error: function () {
                                                        layer.closeAll();
                                                        layer.alert('获取失败！');
                                                    },
                                                });
                                            }
                                        })
                                    } else {
                                        layer.closeAll();
                                        layer.msg(data.msg, {icon: 2});
                                    }
                                },
                                error: function () {
                                    layer.closeAll();
                                    layer.alert('加载失败！');
                                }
                            });
                        }
                    })
                } else {
                    layer.closeAll();
                    layer.msg(data.msg, {icon: 2});
                }
            },
            error: function () {
                layer.closeAll();
                layer.alert('加载失败！');
            }
        });
    }

</script>
<script id="path" type="text/html">
    {{# if(d.path=='/'){ }}
    {{ d.path }}
    {{# }else{ }}
    {{ d.path }}  <a href="javascript:GetDirRewrite('{{ d.name }}','{{d.path}}')"
                     style="color: #20a53a;font-size: 0.8em">伪静态</a>
    {{# } }}
</script>
<script id="operation" type="text/html">
    <a href="javascript:SpaceDeleteDomain('{{ d.name }}')"
       class="layui-btn layui-btn-sm layui-btn-danger layui-btn-xs">删除域名</a>
</script>