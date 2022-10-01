<?php

/**
 * 网站编辑
 */
$title = '优惠券全局配置';
include 'header.php';
?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="mb-3 header-title text-success"><?= $title ?></h3>
                        <h4>注意，为了防止计算异常，优惠券功能暂时无法在购物车结算模式下使用！</h4>
                        <form class="form-horizontal layui-form">
                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">使用优惠券后最低付款金额限制，当优惠券不合理时的保护措施，<font
                                            color=red>此功能会造成优惠券可能无法达到预期效果</font></label>
                                <select class="custom-select mt-3" name="CouponMinimumType">
                                    <option <?= $conf['CouponMinimumType'] == 1 ? 'selected' : '' ?> value="1">不会低于成本
                                    </option>
                                    <option <?= $conf['CouponMinimumType'] == 2 ? 'selected' : '' ?> value="2">不会低于0元
                                    </option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">优惠券使用/领取
                                    IP验证,防止多账户重复使用/领取优惠券,仅可预防常规手段</label>
                                <select class="custom-select mt-3" name="CouponUseIpType">
                                    <option <?= $conf['CouponUseIpType'] == 1 ? 'selected' : '' ?> value="1">开启
                                    </option>
                                    <option <?= $conf['CouponUseIpType'] == 2 ? 'selected' : '' ?> value="2">关闭
                                    </option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">同系统对接他人网站自动使用最佳优惠券,仅限更新了优惠券功能的小储系统</label>
                                <select class="custom-select mt-3" name="CouponApiDockingOthers">
                                    <option <?= $conf['CouponApiDockingOthers'] == 1 ? 'selected' : '' ?> value="1">开启
                                    </option>
                                    <option <?= $conf['CouponApiDockingOthers'] == 2 ? 'selected' : '' ?> value="2">关闭
                                    </option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal"
                                       style="font-weight: 500">是否允许通过对接API对接你站点时使用优惠券</label>
                                <select class="custom-select mt-3" name="CouponApiBeDocking">
                                    <option <?= $conf['CouponApiBeDocking'] == 1 ? 'selected' : '' ?> value="1">允许
                                    </option>
                                    <option <?= $conf['CouponApiBeDocking'] == 2 ? 'selected' : '' ?> value="2">不允许
                                    </option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">每个用户每天最多可使用的券数量限制</label>
                                <input type="text" name="CouponUsableMax" lay-verify="required" class="form-control"
                                       value="<?= $conf['CouponUsableMax'] ?>" placeholder="每个用户每天可使用的券数量限制">
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">每个用户每天最多可领取的券码数量限制</label>
                                <input type="text" name="CouponGetMax" lay-verify="required" class="form-control"
                                       value="<?= $conf['CouponGetMax'] ?>" placeholder="每个用户每天可领取的券码数量限制">
                            </div>

                            <button type="submit" lay-submit lay-filter="Web_editor"
                                    class="btn btn-block btn-xs btn-success">保存内容
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'bottom.php'; ?>

<script>
    layui.use('form', function () {
        var form = layui.form;
        form.on('submit(Web_editor)', function (data) {
            layer.alert('是否要执行当前操作？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function (layero, index) {
                    var index = layer.msg('数据保存中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    $.post('ajax.php?act=config_set', data.field, function (res) {
                        if (res.code == 1) {
                            layer.close(index);
                            layer.alert(res.msg, {
                                btn1: function (layero, index) {
                                    location.reload();
                                }
                            });
                        } else {
                            layer.close(index);
                            layer.alert(res.msg, {
                                btn1: function (layero, index) {
                                    location.reload();
                                }
                            });
                        }
                    });
                }
            });
            return false;
        })
    });
</script>
