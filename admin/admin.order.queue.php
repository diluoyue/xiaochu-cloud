<?php

use Medoo\DB\SQL;

$protect_admin = true;
include '../includes/fun.global.php';
$DB = SQL::DB();
$Res = $DB->select('queue', [
    '[>]goods' => ['gid' => 'gid'],
], [
    'goods.name',
    'goods.image',
    'queue.id',
    'queue.order',
    'queue.trade_no',
    'queue.uid',
    'queue.ip',
    'queue.input',
    'queue.num',
    'queue.gid',
    'queue.payment',
    'queue.price',
    'queue.remark',
    'queue.addtime',
], [
    'queue.type' => 2,
]);
if (!$Res) {
    show_msg('温馨提示', '当前订单队列内没有需要提交的订单！', 3, false, false);
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="../assets/layui/css/layui.css"/>
</head>

<body>
<div class="layui-fluid" style="padding: 0">
    <div class="layui-card">
        <div class="layui-card-body" style="padding:0;width: 100%;overflow:hidden;white-space: nowrap;overflow-x: auto">
            <table class="layui-table layui-text" lay-size="sm" lay-skin="row">
                <colgroup>
                    <col width="120">
                    <col width="200">
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th>操作</th>
                    <th>付款方式</th>
                    <th>付款金额</th>
                    <th>商品名称</th>
                    <th>下单用户</th>
                    <th>下单信息</th>
                    <th>创建时间</th>
                    <th>订单编号</th>
                    <th>订单备注</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($Res as $v) : ?>
                    <tr>
                        <td>
                            <button class="layui-btn layui-btn-xs layui-btn-normal" onclick="tips(<?= $v['id'] ?>)">提交
                            </button>
                            <button class="layui-btn layui-btn-xs layui-btn-danger" onclick="de(<?= $v['id'] ?>)">删除
                            </button>
                        </td>
                        <td><?= $v['payment'] ?></td>
                        <td><?= round($v['price'], 2) ?></td>
                        <td><img src="<?= json_decode($v['image'], true)[0] ?>" width="30" height="30"
                                 style="border-radius: 30px;margin: 0.5em;"/><?= $v['name'] ?></td>
                        <td><?= $v['uid'] ?></td>
                        <td><?= $v['input'] ?></td>
                        <td><?= $v['addtime'] ?></td>
                        <td><?= $v['order'] ?></td>
                        <td><?= $v['remark'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="../assets/js/jquery-3.4.1.min.js"></script>
<script src="../assets/layui/layui.all.js"></script>
<script>
	function tips(id) {
		layer.open({
			title: '操作确认',
			content: '是否要执行此操作？',
			btn: ['确认执行', '取消'],
			icon: 3,
			btn1: function (layero, index) {
				layer.load(2, {
					time: 9999999
				});
				$.ajax({
					type: "post",
					url: "../main.php?act=SubmitOrder",
					data: {
						id: id
					},
					dataType: "json",
					success: function (data) {
						layer.closeAll();
						if (data.code == 1) {
							layer.msg(data.msg, {
								icon: 1
							});
							location.reload();
						} else layer.msg(data.msg, {
							icon: 2
						});
					},
					error: function () {
						layer.closeAll();
						layer.alert('提交失败！');
					}
				});
			}
		});

	}

	function de(id) {
		layer.open({
			title: '操作确认',
			content: '是否要执行此操作？',
			btn: ['确认执行', '取消'],
			icon: 3,
			btn1: function (layero, index) {
				layer.load(2, {
					time: 9999999
				});
				$.ajax({
					type: "post",
					url: "ajax.php?act=OrderDel",
					data: {
						id: id
					},
					dataType: "json",
					success: function (data) {
						if (data.code == 1) {
							layer.msg(data.msg, {
								icon: 1
							});
							location.reload();
						} else layer.msg(data.msg, {
							icon: 2
						});
					},
					error: function () {
						layer.closeAll();
						layer.alert('删除失败！');
					}
				});
			}
		});

	}
</script>
</body>

</html>
