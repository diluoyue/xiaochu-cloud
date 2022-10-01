<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/8 12:00
// +----------------------------------------------------------------------
// | Filename: admin.goods.monitoring.php
// +----------------------------------------------------------------------
// | Explain: 商品监控
// +----------------------------------------------------------------------

$title = '商品监控';
include 'header.php';
global $conf;
?>
<div class="card">
    <div class="card-body">
        <form class="form-horizontal layui-form">
            <div class="form-group mb-2">
                <label for="example-input-normal" style="font-weight: 500">监控总开关 <font
                            color="red">(先验证此配置，再验证商品监控配置！)</font></label>
                <select class="custom-select mt-3" name="SupervisorySwitch">
                    <option <?= $conf['SupervisorySwitch'] == 1 ? 'selected' : '' ?> value="1">开启商品监控(推荐)
                    </option>
                    <option <?= $conf['SupervisorySwitch'] == 2 ? 'selected' : '' ?> value="2">关闭商品监控
                    </option>
                </select>
            </div>
            <div class="form-group mb-2">
                <label for="example-input-normal" style="font-weight: 500">对接货源下架或关闭下单后的操作</label>
                <select class="custom-select mt-3" name="SupervisoryError">
                    <option <?= $conf['SupervisoryError'] == 1 ? 'selected' : '' ?> value="1">不做任何操作
                    </option>
                    <option <?= $conf['SupervisoryError'] == 2 ? 'selected' : '' ?> value="2">清空商品库存
                    </option>
                    <option <?= $conf['SupervisoryError'] == 3 ? 'selected' : '' ?> value="3">下架商品
                    </option>
                </select>
            </div>

            <div class="form-group mb-2">
                <label for="example-input-normal" style="font-weight: 500">对接货源商品上架后操作 <font color="red">(若监控的商品已经下架，则此功能只在使用api监控时才会触发,因为普通用户看不到已下架商品，无法触发监控)</font></label>
                <select class="custom-select mt-3" name="SupervisorySuccess">
                    <option <?= $conf['SupervisorySuccess'] == 1 ? 'selected' : '' ?> value="1">不做任何操作
                    </option>
                    <option <?= $conf['SupervisorySuccess'] == 2 ? 'selected' : '' ?> value="2">上架商品或同步商品库存,同步价格等
                    </option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal" style="font-weight: 500">自动静默监控间隔周期 <font color="red">(单位/秒,推荐1-10分钟,此监控间隔面向整个网站!，静默监控在用户打开商品时触发！)</font></label>
                <input type="text" name="SupervisoryCycle"
                       class="form-control" value="<?= $conf['SupervisoryCycle'] ?>"
                       placeholder="自动静默监控间隔周期">
            </div>

            <div class="form-group mb-3">
                <label for="example-input-normal" style="font-weight: 500">商品监控api <font color="red">
                        推荐监控周期1分钟1次，每次监控1-60个商品，全天轮询监控，推荐开启商品监控后，再单独监控此链接！，后面的num=监控数量[1-60]</font></label>
                <input type="text"
                       class="form-control"
                       value="<?= href(2) . ROOT_DIR_S ?>/api.php?act=Supervisory&token=<?= $conf['secret'] ?>&num=10"
                       placeholder="全天监控api">
            </div>

            <div class="form-group mb-3" style="">
                <label for="example-input-normal"
                       style="font-weight: 500">商品价格有调整后的提示语 <font
                            color="red">(只在用户主动触发监控时才会提示,推荐填写纯文字)</font></label>
                <textarea class="form-control" name="SupervisoryMsg" rows="6"
                          placeholder="商品价格有变化时的提示语"><?= $conf['SupervisoryMsg'] ?></textarea>
            </div>

            <button type="submit" lay-submit lay-filter="Web_editor"
                    class="btn btn-block btn-xs btn-success">保存内容
            </button>
        </form>
    </div>
    <div class="card-body">
        <blockquote class="layui-elem-quote">
            <h4>监控规则说明：</h4>
            1、价格监控仅适用于发货方式为：第三方串货对接类商品！<br>
            2、监控分为两种，一种为通过上方的 商品监控api 轮询监控，另一种为静默监控<br>
            3、静默监控会在打开商品详情界面时触发，触发后会对商品进行同步，同步远程商品状态，价格，库存等！<br>
            4、可通过访问API链接进行轮询监控，轮询监控会全天24h不间断的监控商品状态，价格，库存等<br>
            5、静默监控仅会在用户打开商品详情界面时触发，就造成了已经自动下架的商品无法被同步，并且用户如果不打开商品详情界面就无法同步数据，这是一个很大的缺陷！，强烈建议通过宝塔计划任务或其他监控程序，全天监控上方api链接！
            <hr>
            <h4>支持监控串货列表如下：</h4>
            <div id="data" style="color:red;font-size: 1.2em;">
                数据载入中...
            </div>
        </blockquote>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script>
    layui.use('form', function () {
        var form = layui.form;
        form.on('submit(Web_editor)', function (data) {
            layer.alert('是否要执行当前操作？', {
                icon: 3, btn: ['确定', '取消'], btn1: function (layero, index) {
                    var index = layer.msg('数据保存中,请稍后...', {icon: 16, time: 999999});
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
        });


        let is = layer.msg('初始化中，请稍后...', {icon: 16, time: 9999999});
        $.ajax({
            type: "POST",
            url: './main.php?act=MonitoredType',
            dataType: "json",
            success: function (res) {
                layer.close(is);
                if (res.code == 1) {
                    $("#data").html(res.data);
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
</script>
