<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城
// +----------------------------------------------------------------------
// | Creation: 2022/1/25 14:20
// +----------------------------------------------------------------------
// | Filename: admin.source.ip.php
// +----------------------------------------------------------------------
// | Explain: 代理IP配置
// +----------------------------------------------------------------------

$title = '代理IP配置[货源对接]';
include 'header.php';
global $cdnserver, $conf;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="layui-form card-body mdui-p-a-0">
                    <div class="form-group mb-3">
                        <label>代理IP开关</label>
                        <select class="custom-select mt-3" lay-search name="PMIPState">
                            <option <?= $conf['PMIPState'] == 1 ? 'selected' : '' ?> value="1">开启代理IP
                            </option>
                            <option <?= $conf['PMIPState'] == -1 ? 'selected' : '' ?> value="-1">关闭代理IP
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>IP地址</label>
                        <input type="text" name="PMIP" class="form-control"
                               value="<?= $conf['PMIP'] ?>" placeholder="请填写获取的IP地址">
                    </div>
                    <div class="form-group mb-3">
                        <label>端口号</label>
                        <input type="text" name="PMIPPort" class="form-control"
                               value="<?= $conf['PMIPPort'] ?>" placeholder="请填写IP对应的端口号">
                    </div>

                    <button type="submit" lay-submit lay-filter="Notification_set"
                            class="btn btn-block btn-xs btn-primary">保存内容
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">注意事项</div>
            <div class="card-body layui-text">
                <ul>
                    <li>代理IP的作用：当您的服务器无法访问对接站点，而他人的服务器却可以访问，并且对接，就很有可能是对接站点屏蔽了您的服务器，或主机，导致你的站点无法访问对接货源的站点</li>
                    <li>获取代理IP的方法：可以去浏览器搜索寻找，或购买均可，可以参考此篇知乎文章获取免费的代理IP：<a
                                href="https://www.zhihu.com/question/359676590" target="_blank">https://www.zhihu.com/question/359676590</a>
                    </li>
                    <li style="color: red">当代理IP失效或无法使用时，会导致您的对接api全部瘫痪，无法对接其他站点，这时可以尝试关闭代理IP功能，或修改当前使用的代理IP</li>
                    <li>代理IP生效范围：货源对接，货源商品列表获取，其他请求均未配置代理IP对接，其中货源对接，包含自定义api对接等！</li>
                </ul>
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
