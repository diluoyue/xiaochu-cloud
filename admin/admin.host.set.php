<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/30 16:47
// +----------------------------------------------------------------------
// | Filename: admin.host.set.php
// +----------------------------------------------------------------------
// | Explain: 主机相关配置
// +----------------------------------------------------------------------

$title = '用户后台快捷登录配置';
include 'header.php';
global $cdnserver, $conf;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="layui-form card-body mdui-p-a-0">
                    <div class="mdui-p-a-2">
                        <div class="form-group mb-3">
                            <label>主机系统开关</label>
                            <select class="custom-select mt-3" lay-search name="hostSwitch">
                                <option <?= $conf['hostSwitch'] == 1 ? 'selected' : '' ?> value="1">开启主机系统
                                </option>
                                <option <?= $conf['hostSwitch'] == 2 ? 'selected' : '' ?> value="-1">关闭主机系统
                                </option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>空间配额满了后的操作</label>
                            <select class="custom-select mt-3" lay-search name="QuotaRestrictionOperation">
                                <option
                                    <?= $conf['QuotaRestrictionOperation'] == 1 ? 'selected' : '' ?> value="1">直接关闭主机空间
                                </option>
                                <option
                                    <?= $conf['QuotaRestrictionOperation'] == -1 ? 'selected' : '' ?>
                                        value="2">不做任何操作,仅后台提示
                                </option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>免费主机是否允许续费</label>
                            <select class="custom-select mt-3" lay-search name="FreeHostRenewal">
                                <option <?= $conf['FreeHostRenewal'] == 1 ? 'selected' : '' ?> value="1">允许续费(相当于无限制使用)
                                </option>
                                <option <?= $conf['FreeHostRenewal'] == -1 ? 'selected' : '' ?> value="-1">不允许续费(到期后销毁)
                                </option>
                            </select>
                        </div>

                        <button type="submit" lay-submit lay-filter="Notification_set"
                                class="btn btn-block btn-xs btn-primary">保存内容
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script>
    layui.use('form', function () {
        var form = layui.form;

        form.on('submit(Notification_set)', function (data) {
            layer.alert('是否要保存当前页面全部配置数据？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    let is = layer.msg('加载中，请稍后...', {
                        icon: 16,
                        time: 9999999
                    });
                    $.ajax({
                        type: "POST",
                        url: 'ajax.php?act=config_set',
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
                }
            });
        });
    });
</script>
