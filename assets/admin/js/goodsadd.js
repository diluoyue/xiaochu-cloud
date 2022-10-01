const App = Vue.createApp({
	data() {
		return {
			gid: $("#App").attr('gid'), //商品ID
			Form: { //表单数据
				method: [true, true, true, true, true, true, true], //商品扩展参数配置
				specification: false, //商品规格开关
				specification_type: true, profits: 100, //利润比例
				money: 0, deliver: 1, //商品发货方式，1自营,2URL,3卡密,4显示隐藏,其他均是第三方货源
				extend: {}, //扩展参数
				freight: -1, //运费模板
				units: '个', cid: '', sqid: '', accuracy: 2,
			}, editor: false, //编辑器
			upload: false, //文件上传
			InputType: 1, //新建输入框选择
			InputRule: false, //输入框匹配规则
			UserLevel: false, //用户等级
			deliver: false, freightData: false, //运费模板
			profitArr: {}, //利润计算结果
			rate: 0, //支付接口费率
			ClassData: false, //商品分类
			SumInput: 1, //商品输入框数量
			DockingList: false, //获取对接列表
			Docking: false, //可对接第三方货源格式
			DockingData: false, //当前选择的货源数据
			DockIngInput: {}, //对应货源的输入框格式
			LayuiForm: Object, //Layui表单
			GoodsReturnData: {}, //对接返回数据+指针数据

			SellingPriceData: {}, //自定义等级
			SellingPriceDataType: [], TemporaryMoney: -1, //成功同步后的商品价格基数

			ServerData: [], //服务器列表
			ServerDataType: false, //选择的节点
		}
	}, methods: {
		FreeGoods() {
			this.Form.money = 0;
			this.Form.method[4] = false;
			layer.alert('免费商品配置成功，本次操作如下：<br>成本设置为0，关闭价格监控！', {icon: 1});
		}, inputChange() { //快速同步发货数量和商品成本
			if (App.Form.quantity != '' && App.Form.money != '' && App.Form.money > 0 && App.Form.quantity >= 1) {
				if (App.TemporaryMoney == -1) {
					//同步价格基数
					App.TemporaryMoney = App.Form.money / App.Form.quantity;
					mdui.snackbar({
						message: '成本基数已初始化为：' + App.TemporaryMoney + '元 / 个',
					});
				} else {
					let money = App.TemporaryMoney * App.Form.quantity;
					mdui.dialog({
						title: '温馨提示',
						content: '检测到您修改了每份商品的发货数量，是否需要同步当前商品成本？<br>同步后的成本为：<font color="red">' + money + '元</font>',
						modal: true,
						history: false,
						buttons: [{
							text: '关闭', onClick: function () {
								//关闭后再次同步价格基数
								App.TemporaryMoney = App.Form.money / App.Form.quantity;
								mdui.snackbar({
									message: '成本基数已初始化为：' + App.TemporaryMoney + '元 / 个',
								});
							}
						}, {
							text: '同步成本', onClick: function () {
								App.Form.money = money;
								mdui.snackbar({
									message: '商品成本已调整为：' + money + '元',
								});
							}
						}]
					});
				}
			}
		}, SellingPrice(index) { //自定义商品价格

			let content = `
<div class="mdui-textfield">
      <label class="mdui-textfield-label">自定义售价</label>
      <input id="Input1" class="mdui-textfield-input" type="text"/>
      <div class="mdui-textfield-helper">请输入自定义商品价格，可留空，留空后使用默认等级价格规则！</div>
    </div><div class="mdui-textfield">
      <label class="mdui-textfield-label">自定义积分兑换价</label>
      <input id="Input2" class="mdui-textfield-input" type="text"/>
      <div class="mdui-textfield-helper">请输入自定义商品兑换价格，可留空，留空后使用默认等级价格规则！</div>
</div>
`;
			mdui.dialog({
				title: 'V' + (index + 1) + ' - 自定义价格设置',
				content: '<div style="height:15em;">' + content + '</div>',
				modal: true,
				history: false,
				buttons: [{
					text: '关闭',
				}, {
					text: '设置', onClick: function () {
						var price = $("#Input1").val();
						var integral = $("#Input2").val();

						if (price != '' || integral != '') {
							App.SellingPriceDataType[index] = 1;
						} else {
							App.SellingPriceDataType[index] = 2;
						}
						App.SellingPriceData[index] = {
							'a': price, 'b': integral,
						};
					}
				}, {
					text: '清空', onClick: function () {
						App.SellingPriceDataType[index] = 2;
						App.SellingPriceData[index] = {
							'a': '', 'b': '',
						};
					}
				}],
				onOpen: function () {
					if (App.SellingPriceData[index] == undefined) {
						App.SellingPriceDataType[index] = 2;
						App.SellingPriceData[index] = {
							'a': '', 'b': '',
						};
					}
					$("#Input1").val(App.SellingPriceData[index]['a']);
					$("#Input2").val(App.SellingPriceData[index]['b']);
					mdui.mutation();
				}
			});
		}, ProductDetails(index) { //选择列表时的请求方法
			const Reset = {};
			const GoodsData = App.GoodsReturnData.data[index]; //列表数据
			const Data = App.DockIngInput[App.GoodsReturnData.index]; //货源类型解析数据

			let DataOrigin; //替换源
			if (Data.RequestDataSources === '-1') {
				DataOrigin = GoodsData;
			} else {
				DataOrigin = App[Data.RequestDataSources];
			}

			for (const key in Data.request) {
				if (Data.request[key] === false) {
					Reset[key] = DataOrigin[key];
				} else {
					Reset[key] = Data.request[key];
				}
			}

			Reset['extend'] = this.Form.extend; //载入已配置内容
			Reset['Source'] = this.DockingData.class_name; //对接类型
			Reset['index'] = App.GoodsReturnData.index; //指针
			Reset['key'] = index; //商品列表键值
			Reset['sqid'] = this.DockingData.id; //社区ID
			let is = layer.msg('数据获取中，请稍后...', {icon: 16, time: 9999999});
			$.ajax({
				type: "POST",
				url: './main.php?act=DataInterface',
				data: Reset,
				dataType: "json",
				success: function (res) {
					if (res === null) {
						layer.msg('数据获取失败，请检查配置是否有误！', {icon: 2});
						return;
					}
					layer.close(is);
					if (res.code == 1) {
						for (const key in res.data) {
							App.Form[key] = res.data[key];
							if (key === 'docs') {
								App.editor.txt.html(res.data[key]);
							}
						}

						if (res.data.money != undefined && res.data.quantity != undefined) {
							App.TemporaryMoney = res.data.money / res.data.quantity;
							mdui.snackbar({
								message: '成本基数已初始化为：' + App.TemporaryMoney + '元 / 个',
							});
						}
						layer.alert(res.msg, {
							icon: 1
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
		}, ListRequest(data, index, assist = false) { //请求列表数据
			$('select[name="' + index + '"]').html('');
			App.LayuiForm.render('select');
			const Reset = {};
			let origin; //请求数据格式源
			let DataOrigin; //替换源
			if (assist === false) {
				origin = data.GetListData;
				DataOrigin = App[data.GetListDataSources];
			} else {
				origin = assist.request;
				DataOrigin = App[assist.GetListDataSources];
			}
			for (const key in origin) {
				if (origin[key] === false) {
					Reset[key] = DataOrigin[key];
				} else {
					Reset[key] = origin[key];
				}
			}
			Reset['extend'] = this.Form.extend; //载入已配置内容
			Reset['Source'] = this.DockingData.class_name; //对接类型
			Reset['index'] = index; //指针
			let is = layer.msg('数据获取中，请稍后...', {icon: 16, time: 9999999});
			$.ajax({
				type: "POST",
				url: './main.php?act=DataInterface',
				data: Reset,
				dataType: "json",
				success: function (res) {
					if (res === null) {
						layer.msg('数据获取失败，请检查配置是否有误！', {icon: 2});
						return;
					}
					layer.close(is);
					if (res.code == 1) {
						layer.alert(res.msg, {
							icon: 1, end: function () {
								App.GoodsReturnData = {
									'data': res.data, 'extend': Reset['extend'], 'index': Reset['index'],
								};
								$.each(res.data, function (key, value) {
									$('select[name="' + index + '"]').append(new Option(value.name, key));// 下拉菜单里添加元素
								});
								App.LayuiForm.render('select');
							}
						});
					} else if (res.code == 2) {
						layer.alert(res.msg, {
							icon: 1
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
		}, ChooseDocking(index) { //解析货源输入框格式
			let Data = this.DockingList[index];
			this.DockingData = Data;
			this.Form.sqid = Data.id;
			this.DockIngInput = this.Docking[Data['class_name']]['InputField'];
			setTimeout(function () {
				for (const key in App.DockIngInput) {
					if (App.DockIngInput[key].type == 2) {
						//动态参数监控
						App.LayuiForm.on('select(' + key + ')', function (data) {
							if (data.value === '') return false;
							App.Form.extend[key] = data.value;
							App.ProductDetails(data.value);
						});
					}
				}
				App.LayuiForm.render('select');
			}, 1 * 1000);
		}, Data() { //获取表单数据，不会双向绑定
			return JSON.parse(JSON.stringify(App.Form));
		}, StoreData() { //保存商品数据
			let Data = this.DataVerification();
			if (Data === false) return false;
			if (App.gid != '') {
				Data['gid'] = App.gid;
			}
			mdui.dialog({
				title: '操作确认',
				content: '<font color="#43A047">是否要' + (App.gid == '' ? '新增商品[' + Data.name + ']' : '保存商品[' + Data.name + ']数据') + '？</font>',
				modal: true,
				history: false,
				buttons: [{
					text: '取消',
				}, {
					text: '确定', onClick: function () {
						let is = layer.msg('操作中，请稍后...', {icon: 16, time: 9999999});
						$.ajax({
							type: "POST",
							url: './main.php?act=GoodsAdd',
							data: Data,
							dataType: "json",
							success: function (res) {
								layer.close(is);
								if (res.code == 1) {
									mdui.dialog({
										title: '恭喜',
										content: '<span class="mdui-text-color-green">' + res.msg + '</span>',
										modal: true,
										history: false,
										buttons: [{
											text: '继续', onClick: function () {
												location.reload();
											}
										}, {
											text: '返回商品列表', onClick: function () {
												location.href = 'admin.goods.list.php'
											}
										}]
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
				}]
			});
		}, DataVerification() { //添加商品时的数据校验和转换
			let Data = App.Data();

			for (const dataKey in Data) {
				if (Data[dataKey] === "" || Data[dataKey] === undefined || Data[dataKey] === null) {
					Data[dataKey] = "";
				}
			}

			let required = { //必填参数
				'name': '请填写【商品名称】，可在基础配置内填写',
				'image': '请填写【商品图片】，可在基础配置内填写',
				'quantity': '请填写【每份商品的发货数量】，可在基础配置内填写！',
				'quota': '请填写【剩余库存总数】，可在基础配置内填写',
				'money': '请填写【每份商品的成本价格】，可在价格设置内填写',
				'profits': '请设置】商品利润比】，可在价格设置内填写',
				'cid': '请选择【商品分类】，可在分类标签内选择分类！',
			};
			for (const requiredKey in required) {
				if (Data[requiredKey] === "") {
					App.Tips('<font color=red>' + required[requiredKey] + '</font>');
					return false;
				}
			}
			//其他验证
			if (Data.specification === true) {
				Data.specification = 2;
				if (Data.specification_spu === "" || Data.specification_sku === "") {
					App.Tips('<font color=red>请将【商品规格名】和【商品规格值】填写完整！，可在输入框配置里面设置！</font>');
					return false;
				}
			} else {
				Data.specification = 1;
			}

			if (Data.specification_type === true) {
				Data.specification_type = 1;
			} else {
				Data.specification_type = 2;
			}

			if (Data.method[6] === true && (Data.min === "" || Data.max === "")) {
				App.Tips('<font color=red>请将【最低购买份数】【最多购买份数】输入框填写完整！，可在基础配置里面设置！</font>');
				return false;
			}

			switch (Data.deliver) {
				case '2': //CURL
					if (Data.extend.url === "") {
						App.Tips('<font color=red>请将【需要访问的URL地址】填写完整！，可在发货配置里面设置！</font>');
						return false;
					}
					break;
				case '4': //购买后显示隐藏内容
					if (Data.explain === "") {
						App.Tips('<font color=red>请将【需显示的隐藏内容】填写完整！，可在发货配置里面设置！</font>');
						return false;
					}
					break;
				case '5': //服务器空间发货
					if (Data.extend.id <= 0 || App.ServerDataType === false) {
						App.Tips('<font color=red>请将【需要发货主机空间的服务器节点】选择完整！，可在发货配置里面设置！</font>');
						return false;
					}
					Data.extend.id = App.ServerDataType;

					Data.input = '登陆账号|登陆密码';
					Data.units = '月';
					Data.method[2] = false;
					break;
				case '-1': //第三方货源

					break;
			}


			console.log(Data);

			//method 解析
			let method = [];
			for (const key in Data.method) {
				if (Data.method[key] === true) {
					method.push(((key - 0) + 1));
				}
			}
			Data.method = method;

			//下单信息
			if (Data.input === "" && Data.specification === false) {
				Data.input = '下单账号';
			}

			//多行参数解析
			//image
			if (Data.image !== "") {
				Data.image = (Data.image).split('\n');
			}
			//url对接提交
			if (Data.extend.header !== "" && Data.extend.header !== undefined && Data.extend.header !== null) {
				Data.extend.header = (Data.extend.header).split('\n');
			}
			if (Data.extend.post !== "" && Data.extend.post !== undefined && Data.extend.post !== null) {
				Data.extend.post = (Data.extend.post).split('\n');
			}

			//自定义等级售价
			Data.selling = App.SellingPriceData;

			return Data;
		}, GoodsReverse(Data) { //反向解析商品内容
			for (const dataKey in Data) {
				if (Data[dataKey] === "" || Data[dataKey] === undefined || Data[dataKey] === null) {
					Data[dataKey] = "";
				}
			}
			//编辑器
			this.editor.txt.html(Data.docs);
			//多行参数反解析
			//image
			if (Data.image !== "") {
				Data.image = (Data.image).join('\n');
			}
			//url对接提交
			if (Data.extend.header !== "" && Data.extend.header !== undefined && Data.extend.header !== null) {
				Data.extend.header = (Data.extend.header).join('\n');
			}
			if (Data.extend.post !== "" && Data.extend.post !== undefined && Data.extend.post !== null) {
				Data.extend.post = (Data.extend.post).join('\n');
			}

			//规格参数反解析
			if (Data.specification === true) {
				Data.specification_spu = JSON.stringify(Data.specification_spu);
				Data.specification_sku = JSON.stringify(Data.specification_sku);
			}

			//输入框适配
			if (Data.image !== "") {
				$(".ImageUp textarea").val(Data.image);
				mdui.updateTextFields('.ImageUp');
			}

			//服务器节点
			if (Data.deliver == 5) {
				if (Data.extend.id == "false") {
					App.ServerDataType = false;
				} else {
					App.ServerDataType = Data.extend.id;
				}
			}

			//载入第三方对接配置
			if (Data.deliver == -1) {
				for (const key in App.DockingList) {
					if (App.DockingList[key].id == Data.sqid) {
						App.ChooseDocking(key);
					}
				}
			}

			//绑定自定义售价规则
			App.SellingPriceData = Data.selling;

			//同步价格基数
			App.TemporaryMoney = Data.money / Data.quantity;
			mdui.snackbar({
				message: '成本基数已初始化为：' + App.TemporaryMoney + '元 / 个',
			});

			this.Form = Data;
		}, Tips(content = '', title = '温馨提示') {
			mdui.dialog({
				title: title, content: content, modal: true, history: false, buttons: [{
					text: '关闭',
				}]
			});
		}, InputCount() { //计算下单输入框数量
			this.deliver.handleUpdate();
			let sum = 0;
			if (this.Form.input !== '') {
				sum += this.Form.input.split('|').length;
			}
			if (this.Form.specification === true && this.Form.specification_spu !== '') {
				try {
					sum += App.JSONLength(JSON.parse(App.Form.specification_spu));
				} catch (err) {
					console.log('格式有误！');
				}
			}
			this.SumInput = sum;
		}, JSONLength(obj) {
			var size = 0, key;
			for (key in obj) {
				if (obj.hasOwnProperty(key)) size++;
			}
			return size;
		}, copyToClip(content, message) { //复制内容
			var aux = document.createElement("input");
			aux.setAttribute("value", content);
			document.body.appendChild(aux);
			aux.select();
			document.execCommand("copy");
			document.body.removeChild(aux);
			if (message == null) {
				layer.msg("复制成功", {icon: 1});
			} else {
				layer.msg(message, {icon: 1});
			}
		}, DockingListGet(type = 1) { //获取第三方对接列表
			if (type === 1 && this.DockingList) {
				return;
			}
			$.ajax({
				type: "POST", url: './main.php?act=SourceDataList', dataType: "json", success: function (res) {
					if (res.code == 1) {
						App.DockingList = res.data;
						App.Docking = res.Docking;
						if (type === 2) {
							layer.msg('可对接列表更新成功，共' + (res.data.length) + '条数据!', {icon: 1});
						}
					} else {
						App.DockingList = [];
						App.Docking = [];
					}
				}, error: function () {
					layer.msg('服务器异常！');
				}
			});
		}, ServerListGet(type = 1) {
			$.ajax({
				type: "POST", url: 'main.php?act=ServerList', dataType: "json", success: function (res) {
					if (res.code == 1) {
						App.ServerData = res.data;
						if (type === 2) {
							layer.msg('可用服务器节点更新成功，共' + (res.data.length) + '个节点!', {icon: 1});
						}
					} else {
						App.ServerData = [];
					}
				}, error: function () {
					layer.msg('服务器异常！');
				}
			});
		}, ClassDataGet(type = 1) { //获取分类列表
			if (type === 1 && this.ClassData) {
				return;
			}
			$.ajax({
				type: "POST", url: 'main.php?act=ClassList', dataType: "json", success: function (res) {
					if (res.code == 1) {
						App.ClassData = res.data;
						if (App.Form.cid === '' && App.ClassData[0] !== undefined) {
							App.Form.cid = App.ClassData[0].cid;
						}
						if (type === 2) {
							layer.msg('分类列表更新成功，共' + (res.data.length) + '条分类数据!', {icon: 1});
						}
					} else {
						App.ClassData = [];
					}
				}, error: function () {
					layer.msg('服务器异常！');
				}
			});
		}, PriceTips() {
			mdui.dialog({
				title: '注意事项',
				content: '1、积分计算结果均为四舍五入后的整数<br>' + '2、当用户通过QQ,VX,ZFB在线付款购买商品时，商品购买价会自动四舍五入计算,仅保留2位小数！<br>' + '3、余额购买商品时，商品小数位数最多可详细至8位小数！<br>' + '4、计算结果均实时预览，商品可以单独配置规格成本，成本价格计算方式和此界面一样！<br>' + '5、如果在其他界面修改过等级配置，或新增了等级，可以点击更新等级数据按钮更新！<br>' + '6、免费商品配置可关闭价格监控，然后将成本设置为0元，如果成本不为0元，哪怕是成本为0.00001元，也不会算为免费商品！，当然，您也可以直接将该等级价格自定义为0，也算为免费商品<br>' + '7、每个等级的商品售价均支持自定义，可单独自定义售价，或兑换价格！,如果自定义价格为空的话，则按照默认等级价格计算售价！',
				modal: true,
				history: false,
				buttons: [{
					text: '关闭',
				}]
			});
		}, PriceCalculation(profit, type = 1, index) { //计算商品售价!
			profit = parseFloat(profit);
			let money = parseFloat(this.Form.money);
			let profits = parseFloat(this.Form.profits);
			let result;
			let rate;
			this.Form.accuracy -= 0;
			if (type === 1) {
				if (App.SellingPriceDataType[index] === 1 && App.SellingPriceData[index]['a'] != '') {
					result = App.SellingPriceData[index]['a'] - 0;
				} else {
					result = money + ((money * (profit / 100)) * (profits / 100)); //商品售价
				}
				rate = (result * (this.rate / 100));
				let results = result - money; //利润成本
				if (results < 0) {
					this.profitArr[index] = (results + rate).toFixed(this.Form.accuracy);
				} else {
					this.profitArr[index] = (results - rate).toFixed(this.Form.accuracy);
				}
				result = result.toFixed(this.Form.accuracy);
			} else {
				if (App.SellingPriceDataType[index] === 1 && App.SellingPriceData[index]['b'] != '') {
					result = App.SellingPriceData[index]['b'] - 0;
				} else {
					result = money * profit;
				}
				result = result.toFixed(0);
			}

			return (type === 1 ? '卖' : '') + result + (type === 1 ? '元' : '积分');
		}, NumRound(value, type = 1) {
			value -= 0;
			let num = value.toFixed(8) - 0;
			if (type === 1) {
				return num;
			} else {
				if (num === 0) return 0;
				let str = num.toString();
				if (str.indexOf('.') !== -1) {
					return str.split('.')[1].length;
				}
				return 0;
			}
		}, freightDataGet(type = 1) { //运费模板配置
			if (type === 1 && this.freightData) {
				return;
			}
			$.ajax({
				type: "POST", url: 'main.php?act=freightList', data: {
					type: 2,
				}, dataType: "json", success: function (res) {
					if (res.code == 1) {
						App.freightData = res.data;
						if (type === 2) {
							layer.msg('运费模板列表数据更新成功，共' + (res.data.length) + '条模板数据!', {icon: 1});
						}
					} else {
						App.freightData = [];
					}
				}, error: function () {
					layer.msg('服务器异常！');
				}
			});
		}, UserLevelGet(type = 1) { //获取等级列表
			if (type === 1 && this.UserLevel) {
				return;
			}
			$.ajax({
				type: "POST", url: 'main.php?act=UserLevelList', data: {
					type: 2,
				}, dataType: "json", success: function (res) {
					if (res.code == 1) {
						if (res.data.length >= 1) {
							App.UserLevel = res.data.reverse();
						}
						for (const index in App.UserLevel) {
							if (App.SellingPriceData[index] == undefined) {
								App.SellingPriceDataType[index] = 2;
								App.SellingPriceData[index] = {
									'a': '', 'b': '',
								};
							} else if (App.SellingPriceData[index]['a'] != '' || App.SellingPriceData[index]['b'] != '') {
								App.SellingPriceDataType[index] = 1;
							} else {
								App.SellingPriceDataType[index] = 2;
							}
						}
						if (type === 2) {
							layer.msg('用户等级列表数据更新成功，共' + (res.data.length) + '条等级数据!', {icon: 1});
						}
					} else {
						App.UserLevel = [];
					}
				}, error: function () {
					layer.msg('服务器异常！');
				}
			});
		}, SpecificationsGenerated() { //生成规格值和规格名
			layer.open({
				type: 2,
				shade: false,
				title: '商品规格参数生成',
				area: ['96vw', '96vh'],
				maxmin: true,
				content: './admin.goods.specification.php?gid=' + $("#App").attr('gid'),
				zIndex: layer.zIndex,
				success: function (layero) {
					layer.setTop(layero);
				}
			});
		}, RuleGet(type = 1) {
			if (type === 1 && this.InputRule) {
				return;
			}
			$.ajax({
				type: "POST", url: 'ajax.php?act=RuleList', dataType: "json", success: function (res) {
					if (res.code == 1) {
						App.InputRule = res.data;
						if (type === 2) {
							layer.msg('下单输入框规则更新成功!', {icon: 1});
							$("#Rule").html(App.RuleHtml());
						}
					} else {
						App.InputRule = [];
					}
				}, error: function () {
					layer.msg('服务器异常！');
				}
			});
		}, RuleAdd(name) { //插入规则至输入框
			$("#Input1").val(name);
			layer.msg('输入框规则匹配关键词:[ ' + name + ' ]成功写入输入框!', {icon: 1});
		}, RuleHtml() { //将输入框规则解析为HTML代码
			let content = `
<div class="mdui-table-fluid">
  <table class="mdui-table">
    <thead>
      <tr>
        <th>输入框名称</th>
        <th>新增功能按钮</th>
        <th>新增填写帮助</th>
      </tr>
    </thead>
    <tbody>`;
			for (const contentKey in this.InputRule) {
				let Regis = contentKey.split('|');
				let RegisCo = '';
				for (let regisKey in Regis) {
					if (Regis[regisKey] === '说说ID' || Regis[regisKey] === '说说id') {
						Atis = 'QQ账号|';
					} else {
						Atis = '';
					}
					RegisCo += `<p><a href="javascript:App.RuleAdd('` + Atis + Regis[regisKey] + `')">` + Regis[regisKey] + `</a></p>`;
				}
				content += `
    <tr>
        <td>` + RegisCo + `</td>
        <td>` + this.InputRule[contentKey].name + `</td>
        <td>` + this.InputRule[contentKey].placeholder + `</td>
    </tr>`;
			}
			return content + `
    </tbody>
  </table>
</div>`;
		}, LabelAdd() { //添加一个新的标签
			let _this = this;
			let Content = `<div class="mdui-p-b-5"><div class="mdui-textfield">
      <label class="mdui-textfield-label">标签名称</label>
      <input class="mdui-textfield-input" id="Input1" type="text"/>
      <div class="mdui-textfield-helper">标签名称建议1-5字以内</div>
    </div>
    <div class="mdui-textfield">
      <label class="mdui-textfield-label">标签颜色</label>
      <textarea class="mdui-textfield-input" id="Input2"></textarea>
      <div class="mdui-textfield-helper">可留空，颜色默认红色,示例：#FF0000</div>
    </div></div>`;
			mdui.dialog({
				title: '添加商品标签', content: Content, modal: true, history: false, buttons: [{
					text: '关闭',
				}, {
					text: '添加标签', onClick: function () {
						const input1 = $("#Input1").val();
						const input2 = $("#Input2").val();
						if (input1 === "") {
							layer.msg('请将标签名称填写完整!');
							return false;
						}
						let input = input1;
						if (input2 !== "") {
							input += ',' + input2;
						}

						_this.Form.label += ((_this.Form.label === '') ? '' : '|') + input;
						$("#label textarea").val(_this.Form.label);
						mdui.updateTextFields('#label');
					}
				}], onOpened: function () {
					mdui.mutation();
				}
			});
		}, InputAdd() { //添加一个新的输入框
			let _this = this;
			_this.InputType = 1;
			let Rule = this.RuleHtml();
			let content = `
<div class="mdui-tab mdui-tab-full-width" mdui-tab>
  <a href="#tab1" onclick="App.InputType = 1;" class="mdui-ripple">普通输入框</a>
  <a href="#tab2" onclick="App.InputType = 2;" class="mdui-ripple">下拉选择框</a>
</div>
<div id="tab1" class="mdui-p-a-0">
    <div class="mdui-textfield">
      <label class="mdui-textfield-label">输入框名称</label>
      <input id="Input1" class="mdui-textfield-input" type="text"/>
      <div class="mdui-textfield-helper">输入框的名称,建议1-5字以内</div>
    </div>
    <br>
    <div class="mdui-card">
        <div class="mdui-card-primary">
            <div class="mdui-card-primary-title">输入框名称匹配规则</div>
        </div>
        <div id="Rule" style="font-size: 0.8em;">
            ` + Rule + `
        </div>
        <div class="mdui-card-actions">
            <a href="./admin.InputValidation.php" target="_blank" class="mdui-btn mdui-ripple mdui-ripple-white">新增输入框匹配规则</a>
            <button onclick="App.RuleGet(2)" class="mdui-btn mdui-ripple mdui-ripple-white">更新规则信息</button>
        </div>
    </div>
</div>
<div id="tab2" class="mdui-p-a-0">
    <div class="mdui-textfield">
      <label class="mdui-textfield-label">下拉框名称</label>
      <input class="mdui-textfield-input" id="Input2" type="text"/>
      <div class="mdui-textfield-helper">下拉框的名称,建议1-5字以内</div>
    </div>
    <div class="mdui-textfield">
      <label class="mdui-textfield-label">下拉可选项</label>
      <textarea class="mdui-textfield-input" id="Input3"></textarea>
      <div class="mdui-textfield-helper">可以回车换行,一行一个可选项</div>
    </div>
</div>
                `;
			mdui.dialog({
				title: '新建商品输入框', content: content, modal: true, history: false, buttons: [{
					text: '关闭',
				}, {
					text: '插入规则', onClick: function () {
						let input = '';
						switch (_this.InputType) {
							case 1: //普通输入框
								input = $("#Input1").val();
								if (input === '') {
									layer.msg('请将输入框名称填写完整!');
									return false;
								}
								break;
							case 2: //下拉选择框
								let input1 = $("#Input2").val();
								let input2 = $("#Input3").val();
								if (input1 === '' || input2 === '') {
									layer.msg('请将下拉框名称和下拉可选项填写完整!');
									return false;
								}
								input = input1 + '{' + input2.split('\n').join(',') + '}';
								break;
						}
						_this.Form.input += ((_this.Form.input === '') ? '' : '|') + input;
						$("#input textarea").val(_this.Form.input);
						mdui.updateTextFields('#input');
					}
				}], onOpened: function () {
					mdui.mutation();
				}
			});
		}, EmptyDocs() { //清空商品说明
			this.editor.txt.clear();
		}, HtmlDocs() {
			let Html = this.editor.txt.html();
			let is = layer.prompt({
				formType: 2, value: Html, maxlength: 9999999999999999, title: '编辑原始HTML代码', area: ['80vw', '80vh']
			}, function (value) {
				App.editor.txt.html(value);
				layer.close(is);
				layer.msg('设置成功！', {icon: 1});
			});
		}, LabelPreview() {
			if (this.Form.label === '') {
				mdui.dialog({
					title: '温馨提示', content: `当前无可预览标签,请先点击旁边的<button onclick="App.LabelAdd()" class="mdui-btn mdui-btn-icon mdui-text-color-orange" mdui-tooltip="{content: '添加一个新的输入框'}">
                        <i class="mdui-icon material-icons"></i>
                    </button>添加几个标签吧!`, modal: false, history: false, buttons: [{
						text: '关闭',
					}]
				});
				return;
			}

			let content = ``;
			let Array = this.Form.label.split('|');
			for (const key in Array) {
				if (Array[key].indexOf(',') === -1) {
					content += `<span class="badge ml-1 mdui-text-color-white" style="background-color: #FF0000">` + Array[key] + `</span>`;
				} else {
					let arr = Array[key].split(',');
					content += `<span class="badge ml-1 mdui-text-color-white" style="background-color: ` + arr[1] + `">` + arr[0] + `</span>`;
				}
			}
			mdui.dialog({
				title: '商品标签预览', content: content, modal: false, history: false, buttons: [{
					text: '关闭',
				}],
			});
		}, InputPreview() { //商品规则预览
			if (this.Form.input === '') {
				mdui.dialog({
					title: '温馨提示', content: `当前无可预览输入框,请先点击旁边的<button onclick="App.InputAdd()" class="mdui-btn mdui-btn-icon mdui-text-color-orange" mdui-tooltip="{content: '添加一个新的输入框'}">
                        <i class="mdui-icon material-icons"></i>
                    </button>添加几个规则吧!`, modal: false, history: false, buttons: [{
						text: '关闭',
					}]
				});
				return;
			}
			let content = ``;
			let Array = this.Form.input.split('|');
			for (const key in Array) {
				if (Array[key].indexOf('{') === -1 && Array[key].indexOf('}') === -1) {
					content += `
                        <div class="mdui-textfield">
                          <label class="mdui-textfield-label">` + Array[key] + `</label>
                          <input class="mdui-textfield-input" type="text"/>
                        </div>
                        `;
				} else {
					let sparr = Array[key].split('{');
					let searr = (sparr[1].split('}')[0]).split(',');
					let con = ``;
					for (const conKey in searr) {
						con += `
                            <option value="` + searr[conKey] + `">` + searr[conKey] + `</option>`;
					}
					content += `<div class="mdui-textfield">
                          <label class="mdui-textfield-label">` + sparr[0] + `</label>
                          <select class="mdui-select" style="width: 100%;" >
                                ` + con + `
                          </select>
                        </div>`;
				}
			}
			mdui.dialog({
				title: '输入框预览', content: content, modal: false, history: false, buttons: [{
					text: '关闭',
				}], onOpened: function () {
					mdui.mutation();
				}
			});
		}, ImagePreview() { //图片预览
			const ImageData = this.Form.image;
			if (ImageData === '') {
				mdui.dialog({
					title: '温馨提示',
					content: '当前无可预览图片,请上传或直接使用图片外链添加商品图片!,一行一图,如果图片较大,建议使用图片外链,加快网站访问速度!',
					modal: false,
					history: false,
					buttons: [{
						text: '关闭',
					}]
				});
				return false;
			}
			const ImageArray = ImageData.split('\n');
			let content = '<div class="mdui-row-xs-1 mdui-row-sm-2 mdui-row-lg-3 mdui-row-xl-4 mdui-grid-list">';
			for (const imageArrayKey in ImageArray) {
				content += `
<div class="mdui-col" style="min-height: 50vh;">
    <div class="mdui-grid-tile">
      <a href="javascript:;"><img src="` + ImageArray[imageArrayKey] + `"/></a>
      <div class="mdui-grid-tile-actions mdui-grid-tile-actions-top">
        <div class="mdui-grid-tile-text">
          <div class="mdui-grid-tile-title">` + (imageArrayKey - 0 === 0 ? '商品主图' : '详情图' + imageArrayKey) + `</div>
        </div>
      </div>
    </div>
</div>
`;
			}

			mdui.dialog({
				title: '商品主图,详细图预览,图片摆放位置根据模板决定!',
				content: content + '</div>',
				modal: false,
				history: false,
				buttons: [{
					text: '关闭',
				}]
			});
		}, ImageUp() { //图片文件上传
			if (this.upload) {
				return true;
			}
			layui.use(['upload'], function () {
				App.upload = layui.upload;
				this.upload.render({
					elem: '#ImageUpload',
					url: 'ajax.php?act=image_up',
					acceptMime: 'image/*',
					accept: 'images',
					done: function (res) {
						if (App.Form.image === '') {
							App.Form.image += res.src;
						} else {
							App.Form.image += '\n' + res.src;
						}
						$(".ImageUp textarea").val(App.Form.image);
						mdui.updateTextFields('.ImageUp');
					},
					error: function () {
						layer.msg('图片上传失败!')
					}
				});
			});
		}, Editor() { //编辑器
			if (this.editor) {
				this.editor.destroy();
				this.editor = false;
			}
			const E = window.wangEditor;
			this.editor = new E('#content');
			this.editor.config.onchange = function (html) {
				App.Form.docs = html;
			}
			this.editor.config.excludeMenus = ['emoticon'];
			this.editor.config.compatibleMode = function () {
				return !!window.ActiveXObject || "ActiveXObject" in window;
			}
			this.editor.config.zIndex = 100;
			this.editor.config.onchangeTimeout = 500;
			this.editor.config.historyMaxSize = 50;
			this.editor.config.zIndex = 1;
			this.editor.config.uploadImgAccept = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
			this.editor.config.uploadVideoAccept = ['mp4'];
			this.editor.config.uploadImgMaxSize = 10 * 1024 * 1024;
			this.editor.config.uploadVideoMaxSize = 100 * 1024 * 1024;
			this.editor.config.uploadImgMaxLength = 30;
			this.editor.config.customUploadImg = function (resultFiles, insertImgFn) {
				var imageData = new FormData();
				$.each(resultFiles, function (key, val) {
					imageData.append("imageData" + key, val);
				});
				$.ajax({
					data: imageData,
					type: "POST",
					url: "./main.php?act=ImageUp",
					cache: false,
					contentType: false,
					processData: false,
					success: function (imageUrl) {
						if (imageUrl.code == 1) {
							let content = '';
							$.each(imageUrl['SrcArr'], function (key, val) {
								insertImgFn(val['src'])
								content += '图片：<font color=red>' + val['name'] + '</font>大小为：<font color=red>' + val['size'] + '</font><br>';
							});
							layer.alert(content + '<hr>Ps:图片可一次上传多张！', {
								title: imageUrl.msg
							});
						} else layer.msg(imageUrl.msg);
					},
					error: function () {
						layer.msg('图片上传接口异常，上传失败！');
					}
				});
			}
			this.editor.config.customUploadVideo = function (resultFiles, insertVideoFn) {
				var VideoData = new FormData();
				$.each(resultFiles, function (key, val) {
					VideoData.append("VideoData" + key, val);
				});
				$.ajax({
					data: VideoData,
					type: "POST",
					url: "./main.php?act=VideoUp",
					cache: false,
					contentType: false,
					processData: false,
					success: function (videoUrl) {
						if (videoUrl.code == 1) {
							let content = '';
							$.each(videoUrl['SrcArr'], function (key, val) {
								insertVideoFn(val['src']);
								content += '视频：<font color=red>' + val['name'] + '</font>大小为：<font color=red>' + val['size'] + '</font><br>';
							});
							layer.alert(content, {
								title: videoUrl.msg
							});
						} else layer.msg(videoUrl.msg);
					},
					error: function () {
						layer.msg('图片上传接口异常，上传失败！');
					}
				});
			}
			this.editor.create();
		}, GoodsData() { //获取被修改商品的数据
			let is = layer.msg('商品数据载入中，请稍后...', {icon: 16, time: 9999999});
			$.ajax({
				type: "POST", url: './main.php?act=GoodsData', data: {
					gid: App.gid,
				}, dataType: "json", success: function (res) {
					layer.close(is);
					if (res.code == 1) {
						App.GoodsReverse(res.data);
					} else {
						layer.alert(res.msg, {
							icon: 2
						});
					}
				}, error: function () {
					layer.msg('服务器异常！');
				}
			});
		}, initialize() { //初始化
			this.ImageUp(); //初始化图片上传控件
			this.Editor(); //初始化编辑器
			this.freightDataGet(); //初始化运费模板数据
			this.DockingListGet(); //初始化可对接列表
			this.ClassDataGet(); //初始化商品分类
			this.ServerListGet(); //初始化服务器列表
			this.deliver = new mdui.Select('#deliver'); //初始化下拉框
			if (App.gid !== '') {
				this.GoodsData();
			}
		}
	}
}).mount('#App');

App.initialize();

layui.use('form', function () {
	App.LayuiForm = layui.form;
});
