<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/23 17:48
// +----------------------------------------------------------------------
// | Filename: admin.apps.set.php
// +----------------------------------------------------------------------
// | Explain: App生成配置
// +----------------------------------------------------------------------

$title = 'App基础配置';
include 'header.php';
global $conf;
?>
<div class="layui-form layui-form-pane">
    <div class="form-group mb-3">
        <div class="form-group mb-3">
            <label>分店后台App生成开关</label>
            <select class="custom-select mt-3" lay-search name="userappstate">
                <option <?= $conf['userappstate'] == 1 ? 'selected' : '' ?> value="1">开启App自助生成功能</option>
                <option <?= $conf['userappstate'] == 2 ? 'selected' : '' ?> value="2">关闭App自助生成功能</option>
            </select>
        </div>
        <label for="example-input-normal">默认图标ID</label>
        <div class="input-group">
            <input type="text" name="appiconid" lay-verify="required" lay-verType="tips" class="form-control"
                   id="appiconid" value="<?= $conf['appiconid'] ?>" placeholder="默认APP图标ID"/>
            <div class="input-group-append">
                <span class="input-group-text" id="appiconidUp" style="cursor: pointer">上传</span>
            </div>
            <div class="input-group-append">
                <span class="input-group-text"
                      onclick="layer.alert('<img src=\'main.php?act=AppImage&id='+$('#appiconid').val()+'\' style=width:300px;height:300px;  />')"
                      style="cursor: pointer;background-color: slateblue;color: white">预览</span>
            </div>
        </div>
    </div>
    <div class="form-group mb-3">
        <label for="example-input-normal">默认启动图ID</label>
        <div class="input-group">
            <input type="text" name="appbackgroundid" lay-verify="required" lay-verType="tips" class="form-control"
                   id="appbackgroundid" value="<?= $conf['appbackgroundid'] ?>" placeholder="默认APP启动图ID"/>
            <div class="input-group-append">
                <span class="input-group-text" id="appbackgroundidUp" style="cursor: pointer">上传</span>
            </div>
            <div class="input-group-append">
                <span class="input-group-text"
                      onclick="layer.alert('<img src=\'main.php?act=AppImage&id='+$('#appbackgroundid').val()+'\' style=width:300px;height:300px;  />')"
                      style="cursor: pointer;background-color: slateblue;color: white">预览</span>
            </div>
        </div>
    </div>
    <div class="form-group mb-3">
        <label for="example-input-normal">生成成本</label>
        <input type="text" name="appthemecolor" lay-verify="required" class="form-control" value="1元" disabled>
    </div>
    <div class="form-group mb-3">
        <label for="example-input-normal">生成价格</label>
        <input type="text" name="appprice" lay-verify="required" class="form-control" value="<?= $conf['appprice'] ?>"
               placeholder="App生成价格">
        <font color="red">默认2元，生成成本1元，可为多个等级配置价格<br>
            配置方法：2|1.8|1.5 ，效果：1级用户2元生成，2级1.8元生成，3级1.5元生成，以此类推！</font>
    </div>
    <div class="form-group mb-3">
        <label for="example-input-normal">默认导航栏颜色</label>
        <input type="text" name="appthemecolor" lay-verify="required" class="form-control"
               value="<?= $conf['appthemecolor'] ?>" placeholder="默认导航栏的颜色，如：#FF0000">
    </div>
    <div class="form-group mb-3">
        <label for="example-input-normal">默认加载条颜色</label>
        <input type="text" name="apploadthemecolor" lay-verify="required" class="form-control"
               value="<?= $conf['apploadthemecolor'] ?>" placeholder="默认加载条的颜色，如：#FF0000">
    </div>
    <div class="form-group mb-3">
        <label for="example-input-normal">App简单默认介绍[纯文字]</label>
        <input type="text" name="appcontent" lay-verify="required" class="form-control"
               value="<?= $conf['appcontent'] ?>" placeholder="App简单默认介绍">
    </div>
    <button class="btn btn-block btn-xs btn-success" lay-submit lay-filter="formDemo">保存配置</button>
</div>
<?php include 'bottom.php'; ?>
<script>
    layui.use(['form', 'upload'], function () {
        var form = layui.form;
        var upload = layui.upload;

        upload.render({
            elem: '#appiconidUp',
            url: 'main.php?act=AppUploading',
            size: 1024 * 2,
            accept: 'images',
            acceptMime: 'image/*',
            exts: 'jpg|jpeg|png',
            before: function () {
                tipId1 = layer.msg('正在上传中...', {
                    icon: 16,
                    shade: 0.01,
                    time: 9999999
                });
            },
            done: function (res) {
                layer.alert(res.msg, {
                    icon: 1,
                    end: function () {
                        $("#appiconid").val(res.id);
                    }
                });
            },
            error: function () {
                layer.msg('图片上传失败');
            }
        });

        upload.render({
            elem: '#appbackgroundidUp',
            url: 'main.php?act=AppUploading',
            size: 1024 * 2,
            accept: 'images',
            acceptMime: 'image/*',
            exts: 'jpg|jpeg|png',
            before: function () {
                tipId1 = layer.msg('正在上传中...', {
                    icon: 16,
                    shade: 0.01,
                    time: 9999999
                });
            },
            done: function (res) {
                layer.alert(res.msg, {
                    icon: 1,
                    end: function () {
                        $("#appbackgroundid").val(res.id);
                    }
                });
            },
            error: function () {
                layer.msg('图片上传失败');
            }
        });

        //监听提交
        form.on('submit(formDemo)', function (data) {
            let is = layer.msg('保存中，请稍后...', {
                icon: 16,
                time: 9999999
            });
            $.ajax({
                type: "POST",
                url: './ajax.php?act=config_set',
                data: data.field,
                dataType: "json",
                success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        layer.alert(res.msg, {
                            icon: 1,
                            btn1: function () {
                                location.reload();
                            }
                        });
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                },
                error: function () {
                    layer.msg('服务器异常！');
                }
            });
        });
    });
</script>
