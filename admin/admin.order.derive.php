<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城
// +----------------------------------------------------------------------
// | Creation: 2021/6/7 16:39
// +----------------------------------------------------------------------
// | Filename: admin.order.derive.php
// +----------------------------------------------------------------------
// | Explain: 订单导出
// +----------------------------------------------------------------------

use Medoo\DB\SQL;

$title = '订单导出';
include 'header.php';

$DB = SQL::DB();
$List = $DB->select('order', [
    '[>]goods' => ['gid' => 'gid'],
], [
    'goods.name',
    'order.gid',
], [
    'GROUP' => [
        'order.gid',
    ],
]);
if (!$List) show_msg('注意', '当前无可导出订单！', false, false, 1);

?>
<div class="row">
    <div class="col-sm-12">
        <blockquote class="layui-elem-quote">
            注意事项：<br>
            <ul>
                <li>1、生成的txt文件格式：<font color="red">下单信息(每个内容中间用---分割) | 下单数量</font></li>
                <li>2、导出的同时可以调整状态，当然，仅待发货，处理中，异常中，已完成等状态可调整，其他状态无法改变</li>
                <li>3、需要导出的商品名称，可以点击选择框搜索，无需滑动选择</li>
                <li style="color: red">4、若商品已经下架，则仅能够导出输入框内容+下单份数，而不是下单数量！</li>
            </ul>
        </blockquote>
    </div>
    <div class="col-sm-12">
        <div class="card">
            <blockquote class="card-body">
                <div class="layui-form layui-form-pane">
                    <div class="layui-form-item">
                        <label class="layui-form-label">商品名称</label>
                        <div class="layui-input-block">
                            <select name="gid" lay-verify="required" lay-search>
                                <option value="">请选择需导出的商品</option>
                                <?php
                                foreach ($List as $v) :
                                    echo '<option value="' . $v['gid'] . '">' . (empty($v['name']) ? '商品不存在' : $v['name']) . ' - 商品ID：' . $v['gid'] . '</option>';
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">导出状态</label>
                        <div class="layui-input-block">
                            <select name="state" lay-verify="required">
                                <option value="">请选择需要导出的订单状态</option>
                                <option value="-1">全部状态</option>
                                <option value="1">已完成</option>
                                <option value="2">待处理</option>
                                <option value="3">异常中</option>
                                <option value="4">处理中</option>
                                <option value="5">已退单(导出不会改变状态)</option>
                                <option value="6">投诉中(导出不会改变状态)</option>
                                <option value="7">已评价(导出不会改变状态)</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">状态调整</label>
                        <div class="layui-input-block">
                            <select name="AdjustState" lay-verify="required">
                                <option value="">请选择导出后的订单状态</option>
                                <option value="1">不做调整</option>
                                <option value="2">改为待处理</option>
                                <option value="3">改为处理中</option>
                                <option value="4">改为已完成</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">时间范围</label>
                        <div class="layui-input-block">
                            <input type="text" name="date" placeholder="点击选择时间范围" lay-verify="required"
                                   class="layui-input" id="TimeHorizon">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <button class="mdui-btn mdui-btn-block mdui-text-color-white mdui-ripple mdui-btn-raised mdui-color-deep-purple-accent"
                                lay-submit lay-filter="formDemo">
                            导出为txt文件
                        </button>
                    </div>
                </div>
        </div>
    </div>
</div>
</div>

<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/js/FileSaver.js"></script>
<script>
    layui.use(['form', 'laydate'], function () {
        var laydate = layui.laydate;
        laydate.render({
            elem: '#TimeHorizon'
            , type: 'datetime'
            , range: 'Split'
            , theme: '#393D49'
        });

        var form = layui.form;
        form.on('submit(formDemo)', function (data) {
            layer.open({
                title: '温馨提示',
                content: '是否需要导出指定条件的订单？',
                icon: 3,
                btn: ['导出', '取消'],
                btn1: function () {
                    let is = layer.msg('订单数据载入中...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: './main.php?act=ExportOrders',
                        data: data.field,
                        dataType: "json",
                        success: function (data) {
                            layer.close(is);
                            if (data.code == 1) {
                                layer.alert(data.msg, {icon: 1});
                                var blob = new Blob([(data.data).join('\n')], {type: "text/plain;charset=utf-8"});
                                saveAs(blob, data.name);
                            } else {
                                layer.alert(data.msg, {icon: 2});
                            }
                        }
                    });
                }
            });
        });
    });
</script>
