<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/26 14:51
// +----------------------------------------------------------------------
// | Filename: set.php
// +----------------------------------------------------------------------
// | Explain: 修改主机账号密码
// +----------------------------------------------------------------------
include './header.php';
global $UserData;
?>
<body layadmin-themealias="default">

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">修改账号密码</div>
                <div class="layui-card-body" pad15="">

                    <div class="layui-form">
                        <div class="layui-form-item">
                            <label class="layui-form-label">登陆账号</label>
                            <div class="layui-input-inline">
                                <input type="text" name="username" value="<?= $UserData['username'] ?>"
                                       placeholder="请输入登陆账号" lay-verify="required"
                                       lay-vertype="tips"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">登陆密码</label>
                            <div class="layui-input-inline">
                                <input type="password" name="password" placeholder="留空不修改!"
                                       lay-vertype="tips"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="submit">确认修改</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
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
    }).use(['index', 'set', 'form', 'layer'], function () {
        layui.form.on('submit(submit)', function (data) {
            console.log(data);
            let is = layer.msg('操作中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST",
                url: "../ajax.php?act=SpaceSetAccountModification",
                data: data.field,
                dataType: "json",
                success: function (data) {
                    layer.close(is);
                    if (data.code >= 0) {
                        layer.alert(data.msg, {
                            btn: ['确定'],
                            icon: 1, end: function () {
                                location.reload();
                            }
                        });
                    } else layer.msg(data.msg, {icon: 2});
                },
                error: function () {
                    layer.msg('服务器异常！');
                }
            });
        });
    });
</script>

<style id="LAY_layadmin_theme">.layui-side-menu, .layadmin-pagetabs .layui-tab-title li:after, .layadmin-pagetabs .layui-tab-title li.layui-this:after, .layui-layer-admin .layui-layer-title, .layadmin-side-shrink .layui-side-menu .layui-nav > .layui-nav-item > .layui-nav-child {
        background-color: #20222A !important;
    }

    .layui-nav-tree .layui-this, .layui-nav-tree .layui-this > a, .layui-nav-tree .layui-nav-child dd.layui-this, .layui-nav-tree .layui-nav-child dd.layui-this a {
        background-color: #009688 !important;
    }

    .layui-layout-admin .layui-logo {
        background-color: #20222A !important;
    }</style>
</body>
