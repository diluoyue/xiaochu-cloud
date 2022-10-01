var vm = new Vue({
	el: '#app', data: {
		Data: []
	}, mounted() {
		this.AjaxList();
	}, methods: {
		unset(index) {
			let _this = this;
			layer.open({
				title: '操作确认',
				content: '是否要删除规则[' + index + ']，删除后不可恢复？',
				btn: ['删除', '取消'],
				icon: 3,
				btn1: function () {
					layer.msg('正在删除[' + index + ']的规则数据', {icon: 16, time: 99999999});
					$.ajax({
						type: 'POST', url: './ajax.php?act=RuleUnset', data: {
							id: index,
						}, async: true, dataType: 'json', success: function (res) {
							if (res.code >= 0) {
								_this.AjaxList();
								layer.msg(res.msg);
							} else layer.msg(res.msg);
						}
					});
				}
			});
		}, add() {
			let _this = this;
			let content = `
				<div class="layui-form layui-form-pane">
					<div class="layui-form-item">
						<label class="layui-form-label">匹配字段</label>
						<div class="layui-input-block">
						  <input type="text" name="key" value="" placeholder="多字段请用|分割" autocomplete="off" class="layui-input">
						</div>
					</div>
					
					<div class="layui-form-item">
						<label class="layui-form-label">按钮名称</label>
						<div class="layui-input-block">
						  <input type="text" name="name" value="" placeholder="侧边按钮的名称" autocomplete="off" class="layui-input">
						</div>
					</div>
					
					<div class="layui-form-item">
					    <label class="layui-form-label">动作类型</label>
					    <div class="layui-input-block">
					      <select name="type">
					        <option></option>
					        <option value="-1">调用自定义api接口</option>
					        <option value="1">调用内置地址选择控件</option>
					        <option value="2">调用图片二维码内容解析控件</option>
					      </select>
					    </div>
					</div>
					
					<div class="layui-form-item">
					    <label class="layui-form-label">传值内容</label>
					    <div class="layui-input-block">
					      <select name="way">
					        <option></option>
					        <option value="1">当前成功匹配的输入框的内容</option>
					        <option value="2">当前商品内所有输入框的内容</option>
					      </select>
					    </div>
					</div>
					
					<div class="layui-form-item layui-form-text">
					    <label class="layui-form-label">接口地址</label>
					    <div class="layui-input-block">
					      <textarea name="url" placeholder="接口地址会暴露在外，请自行做中转处理" class="layui-textarea"></textarea>
						  可用参数：<font color=red>[url] = 当前请求域名,如 http://baidu.com</font>
					    </div>
					</div>
					
					<div class="layui-form-item layui-form-text">
					    <label class="layui-form-label">输入提示</label>
					    <div class="layui-input-block">
					      <textarea name="placeholder" placeholder="匹配成功输入内的提示" class="layui-textarea"></textarea>
					    </div>
					</div>
				</div>
			`;

			mdui.dialog({
				title: '新增规则模板', content: content, modal: true, history: false, buttons: [{
					text: '关闭',
				}, {
					text: '保存规则', onClick: function () {
						let Data = {};
						let key = $("input[name='key']").val();
						Data.way = $("select[name='way']").val();
						Data.type = $("select[name='type']").val();
						Data.name = $("input[name='name']").val();
						Data.url = $("textarea[name='url']").val();
						Data.placeholder = $("textarea[name='placeholder']").val();

						layer.msg('正在保存[' + key + ']的匹配字段', {icon: 16, time: 99999999});
						$.ajax({
							type: 'POST', url: './ajax.php?act=RuleAdd', data: {
								id: key, data: Data
							}, async: true, dataType: 'json', success: function (res) {
								layer.closeAll();
								if (res.code >= 0) {
									_this.AjaxList();
									layer.msg(res.msg);
								} else layer.msg(res.msg);
							}
						});
					}
				}], onOpened: function () {
					layui.use(['form'], function () {
						var form = layui.form;
						form.render();
					});
				}
			});
		}, introduce() {
			let content = `
			<div style="max-height:72vh">
			<h4>此功能是为技术人员准备,不懂勿动哦~</h4>
			<h5><font color=#ff5500>问：匹配字段如何填写,有什么作用？</font></h4>
			<font color=#00aa7f>答</font>：匹配字段可填写多个，中间用 | 符号分割，作用是匹配下单输入框的名称，如果匹配字段和输入框的名称对应，则会显示对应功能，按钮，提示等！
			
			<h5><font color=#ff5500>问：匹配规则文件存储在哪里？</font></h4>
			<font color=#00aa7f>答</font>：存储路径：<font color=#0000ff>includes/lib/rule/rule.php</font> 如有需要可手动进行编写！
			
			<h5><font color=#ff5500>问：传值内容怎么选择，有什么区别？</font></h4>
			<font color=#00aa7f>答</font>：传值内容分为两种，一种是单输入框提交，只将当前匹配成功的输入框内容提交至指定接口！，另外一种是多输入框提交，会一次提交全部输入框内容，便于开发者调整！

<h5>Ps：如果想要调用本站内置接口，自定义接口必须在前面加上[url],[url] = 当前请求域名,如http://baidu.com，否则会访问失败哦</h5>
			<pre class="layui-code" lay-title="规则参数注解">
'name' => '点我一下', //按钮名称
'type' => -1|1, //-1自定义远程数据接口，1收货地址,2二维码解析
'url' => 'http://baidu.com', //自定义接口调用地址
'way' => 1|2, //1:提交单输入框的值，2:提交全部输入框的值
'placeholder' => '请将下单信息填写完整！', //匹配成功的输入框提示信息
			</pre>   
			</div>
			`;
			mdui.dialog({
				title: '规则介绍', content: content, modal: true, history: false, buttons: [{
					text: '关闭',
				}]
			});
		}, introduce2() {
			let content = `
<h5>下方是自定义接口可以接收到的参数！</h5>
					<div style="max-height:62vh">
					<pre class="layui-code" lay-title="单输入框提交参数说明">
value：当前按钮输入框的值</pre>   
					
					
					<pre class="layui-code" lay-title="多输入框提交参数说明">
value0：对应输入框1
value1：对应输入框2
value2：对应输入框3
value3：对应输入框4
...</pre>   
<h5>Ps：请务必让自定义接口内的返回参数和下方保持一致,否则会提示错误！</h5>
					<pre class="layui-code" lay-title="单输入框提交返回参数说明">
'code' => '1', //返回判断参数>=0为成功
'msg' => 'xxxx', //提示信息,用户可看到，支持html代码
'value' => 'xxxx',  //此参数内容会替换掉当前输入框的值
'js'=>'js代码'; //可有可无，用于补缺，提示后的确认按钮触发</pre>
					<pre class="layui-code" lay-title="多输入框提交返回参数说明">
'code' => '1', //返回判断参数>=0为成功
'msg' => 'xxxx', //提示信息,用户可看到，支持html代码
'data' => ['输入框1','输入框2','输入框3'],  //此参数内容会替换掉全部输入框的值,按需修改即可！
'js'=>'js代码'; //可有可无，用于补缺，提示后的确认按钮触发</pre>
					</div>
					`;

			mdui.dialog({
				title: '提交返回参数注解', content: content, modal: true, history: false, buttons: [{
					text: '关闭',
				}]
			});
		}, matching(index) {
			let _this = this;
			layer.prompt({
				formType: 2, value: index, title: '请填写新的值'
			}, function (value) {
				layer.msg('正在保存[' + index + ']的匹配字段', {icon: 16, time: 99999999});
				$.ajax({
					type: 'POST', url: './ajax.php?act=RuleMatching', data: {
						id: index, value: value
					}, async: false, dataType: 'json', success: function (res) {
						layer.closeAll();
						if (res.code >= 0) {
							_this.AjaxList();
							layer.msg(res.msg);
						} else layer.msg(res.msg);
					}
				});
			});
		}, preserve(index) {
			layer.msg('正在保存[' + index + ']的规则数据', {icon: 16, time: 99999999});
			let _this = this;
			$.ajax({
				type: 'POST', url: './ajax.php?act=RulePreserve', data: {
					id: index, data: this.Data[index]
				}, async: false, dataType: 'json', success: function (res) {
					if (res.code >= 0) {
						_this.AjaxList();
						layer.msg(res.msg);
					} else layer.msg(res.msg);
				}
			});
		}, AjaxList() {
			let _this = this;
			let is = layer.msg('正在获取中...', {icon: 16, time: 6666666});
			$.ajax({
				type: 'POST', url: './ajax.php?act=RuleList', async: true, dataType: 'json', success: function (res) {
					layer.close(is);
					if (res.code >= 0) {
						_this.Data = res.data;
						$.each(res.data, function (key) {
							_this.Data[key].state = 1;
						});
						_this.$forceUpdate();
					} else layer.msg(res.msg);
				}
			});
		}, edit(index) {
			this.Data[index].state = 2;
			this.$forceUpdate();
		}, cancel(index) {
			this.Data[index].state = 1;
			this.$forceUpdate();
		}
	}
});
