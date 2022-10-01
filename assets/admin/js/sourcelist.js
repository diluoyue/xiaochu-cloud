const App = Vue.createApp({
	data() {
		return {
			Data: [], type: 1,
		}
	}, methods: {
		SourceDelete(id, count) {
			layer.open({
				title: '温馨提示',
				icon: 3,
				content: '是否要删除此对接货源？删除后无法恢复 ! ' + (count === 0 ? '' : ',且里面的' + count + '个商品全部无法对接至货源，商品不会删除!'),
				btn: ['确定', '取消'],
				btn1: function () {
					let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
					$.ajax({
						type: "POST", url: './main.php?act=SourceDelete', data: {
							id: id,
						}, dataType: "json", success: function (res) {
							layer.close(is);
							if (res.code == 1) {
								layer.alert(res.msg, {
									icon: 1, btn1: function () {
										App.ListGet();
									}
								});
							} else {
								layer.alert(res.msg, {
									icon: 2
								});
							}
						}, error: function () {
							layer.msg('服务器异常！');
						}
					});
				}
			})
		}, Ping(index) {
			const Data = this.Data[index];
			if (this.type === 1) {
				this.type = Data.url;
			} else {
				mdui.snackbar({
					message: '同一时间只能够测速一个<br>当前正在测速域名:<br>' + this.type, position: 'right-top'
				});
				return;
			}
			let is = layer.msg('测速中，请稍后...', {icon: 16, time: 9999999});
			$.ajax({
				type: "POST", url: './main.php?act=Ping', data: {
					url: Data.url,
				}, dataType: "json", success: function (res) {
					App.type = 1;
					layer.close(is);
					if (res.code == 1) {
						layer.msg(res.msg + '<br>测速结果:' + res.ping, {icon: 1});
						Data.ping = res.ping;
					} else {
						layer.alert(res.msg, {
							icon: 2
						});
					}
				}, error: function () {
					App.type = 1;
					layer.msg('服务器异常！');
				}
			});
		}, ListGet() {
			let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
			$.ajax({
				type: "POST", url: './main.php?act=SourceDataList', dataType: "json", success: function (res) {
					layer.close(is);
					if (res.code == 1) {
						App.Data = res.data;
					} else {
						layer.alert(res.msg, {
							icon: 2
						});
					}
				}, error: function () {
					layer.msg('服务器异常！');
				}
			});
		}
	}
}).mount('#App');
App.ListGet();
