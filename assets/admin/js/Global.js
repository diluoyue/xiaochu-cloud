const Hover = new Vue({
	el: '#Hover', data: {
		List: [], state: false
	}, mounted() {
		if (window.top !== window.self) {
			return;
		}
		var Data = layui.data('Hover');
		if (Data.state !== undefined) {
			this.state = Data.state;
			this.List = Data.List;
			$("#Hover").show();
		}
		this.AjaxList();
		this.Update();
	}, methods: {
		AjaxList() {
			let _this = this;
			$.ajax({
				type: 'post', url: 'ajax.php?act=HoverList', dataType: 'json', success: function (res) {
					if (res.code >= 0) {
						layui.data('Hover', {
							key: 'List', value: res.data
						});
						_this.List = res.data;
						$("#Hover").show();
					} else {
						layui.data('Hover', {
							key: 'List', value: []
						});
						_this.List = [];
					}
				}
			});
		}, switchs() {
			if (this.state === false) {
				layui.data('Hover', {
					key: 'state', value: true
				});
				this.state = true;
			} else {
				layui.data('Hover', {
					key: 'state', value: false
				});
				this.state = false;
			}
		}, Open(id, name) {
			let index = layer.msg('应用[' + name + ']模板载入中...', {icon: 16, time: 9999999999999})
			if (document.body.clientWidth > 750) {
				area = ['90%', '90%'];
			} else area = ['96%', '96%'];
			layer.open({
				type: 2,
				shade: false,
				title: name,
				area: area,
				maxmin: true,
				content: './ajax.php?act=app_view&id=' + id + '&path=index',
				zIndex: layer.zIndex,
				success: function (layero) {
					layer.setTop(layero);
					layer.close(index);
				}
			});
		}, UpdateTips(data, type = 1) {
			$("#head_image").attr('src', data.image);
			$("#header_name").text(data.name);
			$("#VersionNumber").text(data.data[0]['versions']);
			const UpdateRis = layui.data('UpdateRis');
			if (type === 1 && UpdateRis.type === '1') {
				return;
			}
			if (data.versions !== null) {
				var index = -1;
				(data.data).forEach(function (item, key) {
					if (item.versions === data.versions) {
						index = key;
					}
				});
				let content;
				if (index === -1) {
					content = '您有新的版本可供升级，版本号：<font color="red">' + data.versions + '</font>';
				} else {
					content = '您有新的版本可供升级，版本号：<font color="red">' + data.versions + '</font>' + '<hr>' + data.data[index]['content'] + '<hr>更新时间：' + data.data[index]['date'];
				}
				mdui.dialog({
					title: '版本升级提醒', content: content, modal: true, history: false, buttons: [{
						text: '关闭',
					}, {
						text: '不再提醒', onClick: function () {
							layui.data('UpdateRis', {
								key: 'type', value: '1'
							});
						}
					}, {
						text: '前往更新', onClick: function () {
							layui.data('UpdateRis', {
								key: 'type', value: '1'
							});
							window.location.href = './admin.update.php'
						}
					}]
				});
			}
		}, Update(type = 1) {
			let _this = this;
			const Dispute = layui.data('index_data');
			if (Dispute.data !== undefined && type === 1) {
				const Peacetime = layui.data('indextype');
				const mis = Peacetime.time - 1;
				if (mis > 0) {
					_this.UpdateTips(JSON.parse(Dispute.data), type);
					layui.data('indextype', {
						key: 'time', value: mis
					});
					return false;
				}
			}
			if (type === 2) {
				vis = layer.msg('正在获取最新数据中...', {icon: 16, time: 999999});
			}
			$.ajax({
				type: 'POST', url: './ajax.php?act=UpdateInspection', data: {
					type: type
				}, dataType: 'json', success: function (res) {
					if (type === 2) {
						layer.close(vis);
					}
					if (res.code >= 1) {
						var data = JSON.stringify(res);
						layui.data('index_data', {
							key: 'data', value: data
						});
						layui.data('indextype', {
							key: 'time', value: 18
						});
						_this.UpdateTips(res, type);
					} else {
						if (type === 2) {
							layer.msg(res.msg, {icon: 2});
						}
					}
				}
			});
		}
	}
});
