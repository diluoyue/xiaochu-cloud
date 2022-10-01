<?php
// +----------------------------------------------------------------------
// | Project: fun.global.php
// +----------------------------------------------------------------------
// | Creation: 2021/8/11 15:03
// +----------------------------------------------------------------------
// | Filename: admin.seckill.add.php
// +----------------------------------------------------------------------
// | Explain: 创建限购秒杀活动
// +----------------------------------------------------------------------
use Medoo\DB\SQL;

$title = '创建/修改秒杀活动';
include 'header.php';
if (!empty($_QET['id'])) {
    $DB = SQL::DB();
    $Seckill = $DB->get('seckill', '*', ['id' => (int)$_QET['id']]);
    if (!$Seckill) {
        show_msg('商品秒杀活动' . $_QET['id'] . '不存在', '商品秒杀活动' . $_QET['id'] . '不存在请检查是否访问有误?', 3);
    }
}
$sid = ($_QET['id'] ?? -1);
?>
<div class="row" id="App" sid="<?= $sid ?>">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="./admin.seckill.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <?= $title ?>
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">商品编号 (前往<a
                                    href="admin.goods.list.php" target="_blank">商品列表</a>查看GID)</label>
                        <input type="text" name="gid" lay-verify="required" class="form-control"
                               value="<?= $Seckill['gid'] ?>" placeholder="请填写活动商品ID，如：100">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">降价幅度(%)</label>
                        <div class="input-group">
                            <input type="text" name="depreciate" lay-verify="required" lay-verType="tips"
                                   class="form-control"
                                   value="<?= $Seckill['depreciate'] ?>"
                                   placeholder="请将降价百分比填写完整！"/>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">限购人数</label>
                        <div class="input-group">
                            <input type="number" name="astrict" lay-verify="required" lay-verType="tips"
                                   class="form-control"
                                   value="<?= $Seckill['astrict'] ?>"
                                   placeholder="本次活动商品限购人数"/>
                        </div>
                        <span style="color: red;font-size: 12px;">
                            通过计算活动期间创建的订单总数来实现限购！如在活动期间创建了3个订单，则购买人数为3，哪怕是这3个订单都购买了100份，购买人数也算为3！
                        </span>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">活动开始时间</label>
                        <div class="input-group">
                            <input type="text" name="start_time" id="start_time" lay-verify="required"
                                   lay-verType="tips"
                                   class="form-control"
                                   value="<?= $Seckill['start_time'] ?>"
                                   placeholder="活动开始时间"/>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">活动结束时间</label>
                        <div class="input-group">
                            <input type="text" name="end_time" id="end_time" lay-verify="required" lay-verType="tips"
                                   class="form-control"
                                   value="<?= $Seckill['end_time'] ?>"
                                   placeholder="活动结束时间"/>
                        </div>
                    </div>


                    <button type="submit" lay-submit lay-filter="Preserve" class="btn btn-block btn-xs btn-success">
                        修改 / 创建
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script>
    layui.use(['laydate', 'form'], function () {
        var laydate = layui.laydate;
        laydate.render({
            elem: '#start_time',
            type: 'datetime',
        });
        laydate.render({
            elem: '#end_time',
            type: 'datetime',
        });
        var form = layui.form;
        form.on('submit(Preserve)', function (data) {
            const sid = $("#App").attr('sid');


            layer.alert('是否要执行当前操作？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    is = layer.msg('数据保存中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    if (sid != -1) {
                        data.field['sid'] = sid;
                    }
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=AddSeckill',
                        data: data.field,
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    btn: ['继续', '返回列表'],
                                    icon: 1,
                                    btn1: function () {
                                        location.reload();
                                    },
                                    btn2: function () {
                                        location.href = './admin.seckill.list.php'
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
