function DataPush(data) {
	inst = new mdui.Select('#Pushs', {
		position: 'bottom'
	});
	$("#Pushs").append(`<option value="` + vm.gid + `" >` + vm.Goods.name + `</option>`);
	if (data.length >= 1) {
		for (const v of data) {
			if (vm.gid != v.gid) $("#Pushs").append(`<option value="` + v.gid + `" >` + v.name + `</option>`);
		}
	}
	inst.handleUpdate();
}

const vm = Vue.createApp({
	data() {
		return {
			gid: gid, Goods: {
				image: [], input: [[]], name: -1, Push: [], Seckill: -1,
			}, CouponData: [], //优惠券列表
			UserState: -1, //登录状态
			CouponOn: [], //有效优惠券
			CouponOff: [], //未满足要求的优惠券
			CouponSelect: -1, //优惠券选择
			SkuType: -1, num: 1, Price: 0, form: [], js: ``,

			FormSp: {}, //商品规格下单参数
			SkuBtn: {}, //商品规格按钮,用于渲染按钮
			Combination: {}, //商品规格数据，用于匹配数据
			GoodsBack: -1, //备份参数,用于未选恢复
			freight: '', ///运费
			exceed: '', formApi: false,
		};
	}, mounted() {
		this.AjaxData();
	}, methods: {
		/**
		 * @param {Object} value
		 * 四舍五入
		 */
		NumRound(value, type = 1) {
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
		}, CouponAdd(token) {
			let _this = this;
			if (this.CouponData.length == 0) return false;
			if (this.UserState == -1) {
				mdui.snackbar({
					message: '领取优惠券需要先登录哦', buttonText: '登录', position: 'right-top', onButtonClick: function () {
						window.open('./?mod=route&p=User');
					},
				});
			} else {
				layer.msg("正在领取中...", {
					icon: 16, time: 9999999, shade: [0.2, '#393D49'],
				});
				$.ajax({
					type: "post", url: "./main.php?act=CouponGet", data: {
						token: token,
					}, dataType: "json", success: function (data) {
						layer.closeAll();
						if (data.code >= 1) {
							_this.CouponList();
							layer.msg(data.msg, {icon: 1});
						} else if (data.code == -2) {
							_this.CouponList();
							mdui.snackbar({
								message: '领取优惠券需要先登录哦',
								buttonText: '登录',
								position: 'right-top',
								onButtonClick: function () {
									window.open('./?mod=route&p=User');
								},
							});
						} else layer.msg(data.msg, {icon: 2});
					}
				});
			}
		}, Coupon() {
			if (this.CouponData.length == 0) return false;
			var inst = new mdui.Dialog('#CouponMsg');
			inst.open();
		}, CouponList() {
			let _this = this;
			$.ajax({
				type: "post", url: "./main.php?act=CouponList", data: {
					gid: this.gid, type: 1,
				}, dataType: "json", success: function (data) {
					if (data.code >= 1) {
						_this.CouponData = data.data;
						_this.UserState = data.type;
					} else _this.CouponData = [];
				}, error: function () {
					_this.CouponData = [];
				},
			});
		}, onClickImg(gid) {
			if (gid == this.gid) return;
			this.gid = gid;
			history.replaceState({}, null, './?mod=shop&gid=' + gid);
			this.SkuType = -1;
			$("#Pushs").empty();
			this.num = 1;
			Sliding();
			this.AjaxData();
		}, ShareGoods() {
			layer.msg("正在生成中...", {
				icon: 16, time: 9999999, shade: [0.2, '#393D49'],
			});
			$.ajax({
				type: "post", url: "./main.php?act=SharePoster", data: {
					gid: this.gid,
				}, dataType: "json", success: function (data) {
					layer.closeAll();
					if (data.code == 1) {
						mdui.alert('<div class="imagesr"><img src="' + data.src + '" width=300 heigth=450 /></div>', '保存图片分享此商品');
					} else {
						mdui.alert(data.msg, '警告');
					}
				}, error: function () {
					layer.closeAll();
					mdui.alert("生成失败！");
				},
			});
		}, SubmitData() {
			let Input = this.Goods.input;
			let _this = this;
			let Data = {
				gid: this.gid, num: this.num
			}; //传值拼接
			let SpIn = 0;
			if (this.SkuType === 1) {
				for (let s in _this.FormSp) {
					Data[SpIn] = _this.FormSp[s];
					++SpIn;
				}
			}
			let sum = this.SkuType === 1 ? 1 : 0;
			for (let i = sum; i < Input.length; i++) {
				if (this.form[i] == undefined || this.form[i] == '') {
					Data[SpIn] = '';
				} else {
					Data[SpIn] = this.form[i];
				}
				++SpIn;
			}
			return Data;
		}, CouponSelectR(index) {
			let _this = this;
			if (_this.CouponOn.length == 0 && _this.CouponOff.length == 0) return false;
			_this.CouponSelect = index;
			layer.closeAll();
			_this.PayAisle();
		}, CouponSelectS(type = 1) {
			let _this = this;
			if (_this.CouponOn.length == 0 && _this.CouponOff.length == 0) return false;

			if (type == 1) {

				if (_this.CouponOff.length == 0) {
					btn = ['关闭窗口'];
				} else btn = ['查看不可用优惠券', '关闭窗口'];

				layer.closeAll();

				let Vlas = '';
				for (const key in _this.CouponOn) {
					if (_this.CouponOn.hasOwnProperty.call(_this.CouponOn, key)) {
						const value = _this.CouponOn[key];

						if (value.type == 1) {
							color = 'style="color:#fff;background: linear-gradient(to right, #36d1dc, #5b86e5);"';
							image = 'assets/img/coupon_1.png';
						} else if (value.type == 2) {
							color = 'style="color:#fff;background: linear-gradient(to right, #ff416c, #ff4b2b);"';
							image = 'assets/img/coupon_2.png';
						} else {
							color = 'style="color:#fff;background: linear-gradient(to right, #f7971e, #ffd200);"';
							image = 'assets/img/coupon_3.png';
						}

						Vlas += `
                        <div class="layui-col-xs12 CouponCss">
                            <div class="layui-card" ` + (_this.CouponSelect == key ? 'style="box-shadow: -2px -2px 16px #f7797d;"' : '') + ` onclick="vm.CouponSelectR(` + key + `)">
                                <div class="layui-card-header layui-elip" ` + color + `>
                                    ` + value.name + `
                                </div>
                                <div class="layui-card-body">
                                    <img src="` + image + `" />
                                    <ul>
                                        <li style="color:red;font-size:1em">[ ID:` + value.id + ` ] ` + value.Money + `</li>
                                        <li style="color:#000">` + value.explain + `</li>
                                        <li>此优惠券将于` + value.ExpirationDate + `后失效</li>
                                        <li>` + value.content + `</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        `;
					}
				}

				content = `
                <div class="layui-row layui-col-space15">
                    <div class="layui-col-xs12 CouponCss" onclick="vm.CouponSelectR(-1)">
                        <button class="btn btn-default btn-block" ` + (_this.CouponSelect == -1 ? 'style="box-shadow: -2px -2px 16px #f7797d;"' : '') + ` onclick="vm.submitpay(1)" style="margin-top:10px;">
                        <img src="assets/img/pays.png" class="logo">不使用任何优惠券</button>
                    </div>
                    ` + Vlas + `
                </div>
                `;
				layer.open({
					title: '点击选择需要使用的优惠券', content: content, btn: btn, area: ['350px', '90%'], btn1: function () {
						if (_this.CouponOff.length >= 1) {
							layer.closeAll();
							_this.CouponSelectS(2);
						} else {
							layer.closeAll();
							_this.PayAisle();
						}
					}, btn2: function () {
						layer.closeAll();
						_this.PayAisle();
					}
				});
			} else {

				if (_this.CouponOn.length == 0) {
					btn = ['关闭窗口'];
				} else btn = ['查看可用优惠券', '关闭窗口'];

				layer.closeAll();

				let Vlas = '';
				for (const key in _this.CouponOff) {
					if (_this.CouponOff.hasOwnProperty.call(_this.CouponOff, key)) {
						const value = _this.CouponOff[key];
						if (value.type == 1) {
							color = 'style="color:#fff;background: linear-gradient(to right, #36d1dc, #5b86e5);"';
							image = 'assets/img/coupon_1.png';
						} else if (value.type == 2) {
							color = 'style="color:#fff;background: linear-gradient(to right, #ff416c, #ff4b2b);"';
							image = 'assets/img/coupon_2.png';
						} else {
							color = 'style="color:#fff;background: linear-gradient(to right, #f7971e, #ffd200);"';
							image = 'assets/img/coupon_3.png';
						}
						Vlas += `
                        <div class="layui-col-xs12 CouponCss">
                            <div class="layui-card" onclick="alert('` + value.explain + `')">
                                <div class="layui-card-header layui-elip" ` + color + `>
                                    ` + value.name + `
                                </div>
                                <div class="layui-card-body">
                                    <img src="` + image + `" />
                                    <ul>
                                        <li style="color:red;font-size:1em">[ ID:` + value.id + ` ] ` + value.Money + `</li>
                                        <li style="color:#000">` + value.explain + `</li>
                                        <li>此优惠券将于` + value.ExpirationDate + `后失效</li>
                                        <li>` + value.content + `</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        `;
					}
				}

				content = `
                <div class="layui-row layui-col-space15">
                    ` + Vlas + `
                </div>
                `;
				layer.open({
					title: '不可用优惠券列表', content: content, btn: btn, area: ['350px', '90%'], btn1: function () {
						if (_this.CouponOff.length >= 1 && _this.CouponOn.length >= 1) {
							_this.CouponSelectS(1);
						} else {
							layer.closeAll();
							_this.PayAisle();
						}
					}, btn2: function () {
						layer.closeAll();
						_this.PayAisle();
					}
				});
			}
		}, submit() {
			let thie = this;
			layer.load(1);
			axios
				.get("./main.php?act=CreateOrder", {
					params: this.SubmitData(),
				})
				.then((res) => {
					var data = res.data;
					layer.closeAll();
					if (data.code >= 0) {
						data = data.data;

						// let inputs = "";

						// for (let i = 0; i < data.input.length; i++) {
						//     inputs += data.input[i] + "：" + data.data[i] + "<br>";
						// }

						if (res.data.Coupon.length >= 1 || res.data.CouponOff.length >= 1) {
							thie.CouponOn = res.data.Coupon;
							thie.CouponOff = res.data.CouponOff;
							if (res.data.Coupon.length >= 1) {
								thie.CouponSelect = 0;
							} else thie.CouponSelect = -1;
						} else {
							thie.CouponOn = [];
							thie.CouponOff = [];
							thie.CouponSelect = -1;
						}

						thie.SubData = data;
						thie.PayAisle();
					} else if (data.code == '-3') {
						location.href = './?mod=route&p=User';
					} else mdui.alert(data.msg, '警告!');
				});
		}, PayAisle() {
			let thie = this;
			layer.load(1);
			axios
				.get("./main.php?act=PaymentWay", {
					params: {
						type: 1, gid: thie.gid,
					},
				})
				.then((res) => {
					var data = res.data;
					layer.closeAll();
					if (data.code >= 0) {
						if (thie.CouponOn.length >= 1 || thie.CouponOff.length >= 1) {

							if (thie.CouponSelect != -1) {
								Price = '<font color=red>￥' + thie.CouponOn[thie.CouponSelect].Price + '元</font><span class="layui-word-aux" style="font-size:0.7em;text-decoration:line-through;">' + thie.SubData.price + '元' + '</span>';
								CousBtn = `<p>` + thie.CouponOn[thie.CouponSelect].explain + `</p>
                                <button  class="btn btn-default btn-block" onclick="vm.CouponSelectS()" style="margin-top:10px;font-size:0.6em">
                                    <img src="assets/img/coupon_1.png" class="logo">已选择：` + thie.CouponOn[thie.CouponSelect].name + `</button>`;
							} else {
								Price = thie.SubData.price + '元';
								CousBtn = `<button  class="btn btn-default btn-block" onclick="vm.CouponSelectS(` + (thie.CouponOn.length >= 1 ? 1 : 2) + `)" style="margin-top:10px;font-size:0.6em">
                                    <img src="assets/img/coupon_1.png" class="logo">当前未使用优惠券,点击查看详情!</button>`;
							}

							content = `
                            <center>
                            <h2 style="color:#00C853" ` + (data.data[0].type != 1 && data.data[1].type != 1 && data.data[2].type != 1 && data.data[3].type != 1 ? 'style="display: none;"' : "") + ` >` + Price + `</h2>
                                <span ` + (data.data[4].type != 1 ? 'style="display: none;"' : "") + `> 或 <font color=#F57C00>` + thie.SubData.points + thie.SubData.currency + `</font></span>
                            <hr>
                            <button ` + (data.data[0].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(1)" style="margin-top:10px;">
                                <img src="assets/img/qqpay.png" class="logo">QQ钱包</button>
                            <button ` + (data.data[1].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(2)" style="margin-top:10px;">
                                <img src="assets/img/wxpay.png" class="logo">微信支付</button>
                            <button ` + (data.data[2].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(3)" style="margin-top:10px;">
                                <img src="assets/img/alipay.png" class="logo">支付宝</span>
                            </button>
                            <button ` + (data.data[3].surplus == "未登陆，点击登陆" || data.data[3].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(4)" style="margin-top:10px;">
                                <img src="assets/img/moneypay.png" class="logo">余额付款</button>
                            <button ` + (data.data[4].surplus == "未登陆，点击登陆" || data.data[4].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(5)" style="margin-top:10px;">
                                <img src="assets/img/total.png" class="logo">积分兑换</button>
                            <hr>
                            ` + CousBtn + `
                            <hr>
                            <button class="btn btn-block" style="background: linear-gradient(to right, rgb(113, 215, 162), rgb(34, 215, 185)); font-weight: bold; color: white;" onclick="layer.closeAll();">取消付款</button>
                            </cemter>
                        `;
						} else {
							content = `
                                <center>
                                <h2 style="color:#00C853" ` + (data.data[0].type != 1 && data.data[1].type != 1 && data.data[2].type != 1 && data.data[3].type != 1 ? 'style="display: none;"' : "") + ` >￥` + thie.SubData.price + `元</h2>  <span ` + (data.data[4].type != 1 ? 'style="display: none;"' : "") + `> 或 <font color=#F57C00>` + thie.SubData.points + thie.SubData.currency + `</font></span>
                                <hr>
                                <button ` + (data.data[0].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(1)" style="margin-top:10px;">
                                    <img src="assets/img/qqpay.png" class="logo">QQ钱包</button>
                                <button ` + (data.data[1].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(2)" style="margin-top:10px;">
                                    <img src="assets/img/wxpay.png" class="logo">微信支付</button>
                                <button ` + (data.data[2].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(3)" style="margin-top:10px;">
                                    <img src="assets/img/alipay.png" class="logo">支付宝</span>
                                </button>
                                <button ` + (data.data[3].surplus == "未登陆，点击登陆" || data.data[3].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(4)" style="margin-top:10px;">
                                    <img src="assets/img/moneypay.png" class="logo">余额付款</button>
                                <button ` + (data.data[4].surplus == "未登陆，点击登陆" || data.data[4].type != 1 ? 'style="display: none;"' : "") + ` class="btn btn-default btn-block" onclick="vm.submitpay(5)" style="margin-top:10px;">
                                    <img src="assets/img/total.png" class="logo">积分兑换</button>
                                <hr ` + (data.data[3].surplus == "未登陆，点击登陆" || data.data[3].type != 1 ? 'style="display: none;"' : "") + `>
                                <div ` + (data.data[3].surplus == "未登陆，点击登陆" || data.data[3].type != 1 ? 'style="display: none;"' : "") + `>
                                    ` + data.data[3].surplus + `
                                </div>
                                <hr ` + (data.data[3].surplus == "未登陆，点击登陆" || data.data[3].type != 1 ? 'style="display: none;"' : "") + `>
                                <div ` + (data.data[4].surplus == "未登陆，点击登陆" || data.data[4].type != 1 ? 'style="display: none;"' : "") + `>
                                    ` + data.data[4].surplus + `
                                </div>
                                <hr>
                                <button class="btn btn-block" style="background: linear-gradient(to right, rgb(113, 215, 162), rgb(34, 215, 185)); font-weight: bold; color: white;" onclick="layer.closeAll();">取消付款</button>
                                </cemter>
                            `;
						}

						layer.open({
							title: "选择付款方式", btn: false, content: content, area: ["350px"],
						});
					} else layer.msg(data.msg);
				});
		}, submitpay(paytype) {
			let thie = this;
			let _this = this;
			layer.load(1);

			/**
			 * 构建数据
			 */
			let mode = "wxpay";
			let type = 1;

			switch (paytype) {
				case 1: //QQ
					mode = "qqpay";
					type = 1;
					break;
				case 2: //微信
					mode = "wxpay";
					type = 1;
					break;
				case 3: //支付宝
					mode = "alipay";
					type = 1;
					break;
				case 4: //余额
					type = 2;
					break;
				case 5: //积分
					type = 3;
					break;
			}
			let Data = {};
			for (let i in this.SubData.data) {
				Data[i] = this.SubData.data[i];
			}
			Data.gid = this.SubData.gid;
			Data.num = this.SubData.num;
			Data.type = type;
			Data.mode = mode;

			CouponSelect = -1;
			if (this.CouponSelect != -1) {
				CouponSelect = this.CouponOn[this.CouponSelect].id;
			}

			Data.CouponSelect = CouponSelect;

			layer.msg("操作中,请稍后...", {
				icon: 16, time: 9999999, shade: [0.2, '#393D49'],
			});

			axios
				.get("./main.php?act=Pay", {
					params: Data,
				})
				.then((res) => {
					var data = res.data;
					layer.closeAll();
					if (data.code == 1) {
						_this.queue();
						layer.alert(data.msg, {
							title: "通知", icon: 1, btn: ["确定"], yes: function (layero, index) {
								location.href = "./?mod=query";
							},
						});
					} else if (data.code == 2) {
						layer.alert(data.msg, {
							btn: ["购买", "取消"], btn1: function (layero, index) {
								open(data.url);
								layer.alert("订单付款状态监控中", {
									btn: ["我已付款,查看订单", "关闭"], btn1: function (layero, index) {
										location.href = "./?mod=query";
									}, btn2: function (layero, index) {
										location.reload();
									}, icon: 16, time: 99999999, btnAlign: "c",
								});
								_this.Monitoring(data.pid, 1);
							},
						});
					} else if (data.code == -3) {
						_this.queue();
						_this.Monitoring(data.pid, 2);
					} else if (data.code == -1) {
						layer.alert(data.msg, {
							icon: 2, title: "警告",
						});
					} else {
						_this.queue();
						layer.alert("点击确定查看订单", {
							title: "通知", icon: 1, btn: ["确定"], yes: function (layero, index) {
								location.href = "./?mod=query";
							},
						});
					}
				});
		}, /**
		 * @param {Object} pid 支付订单ID
		 * @param {type} type 监控类型 1支付订单，2商品订单
		 * 监控订单！
		 */
		Monitoring(pid, type) {
			var _this = this;
			axios
				.get("./main.php?act=OrderState", {
					params: {
						pid: pid, type: type,
					},
				})
				.then((res) => {
					res = res.data;
					if (res.code == 1) {
						layer.closeAll();
						layer.alert(res.msg, {
							title: "通知", icon: 1, btn: ["确定"], yes: function (layero, index) {
								location.href = "./?mod=query";
							},
						});
					} else {
						setTimeout(() => {
							_this.Monitoring(pid, type);
							_this.queue();
						}, 1000);
					}
				});
		}, /**
		 * @param {Object} pid 队列订单提交
		 */
		queue() {
			axios.get("./main.php?act=SubmitOrder");
		}, AjaxGoodsMonitoring(gid) {
			axios.get("./main.php?act=GoodsMonitoring&gid=" + gid).then((res) => {
				var data = res.data;
				if (data.code != 1 && data != '') {
					layer.alert(data.msg, {
						icon: 3, end: function (layero, indexs) {
							location.reload();
						},
					});
				}
			});
		}, BtnClick(index, name, type) {
			//规格按钮点击
			let _this = this;
			if (type == 0) {
				layer.msg('库存暂时不足,可联系客服补货~', {icon: 2});
				return;
			}//已选择或无库存！
			if (_this.FormSp[index] == name) {
				_this.Goods = JSON.parse(JSON.stringify(_this.GoodsBack));
				_this.num = _this.Goods.min;
				_this.FormSp[index] = '';
			} else {
				_this.FormSp[index] = name;
			}
			_this.$forceUpdate();

			let SKP = [];
			let i = 0;
			for (let s in this.SkuBtn) {
				if (_this.FormSp[s] == '') return false;
				SKP[i] = _this.FormSp[s];
				++i;
			}

			let Data = this.Combination[SKP.join('`')];
			if (Data == undefined) {
				return false;
			}

			for (let index in Data) {
				if (Data[index] === '' || Data[index] === undefined || Data[index] === null) {
					Data[index] = _this.GoodsBack[index];
				}
			}

			/**
			 * 商品赋值
			 */
			this.Goods = Object.assign(this.Goods, Data);
			/**
			 * 初始化
			 */
			this.Image = this.Goods.image[0];
			this.num = JSON.parse(JSON.stringify(this.Goods)).min;
		},

		Btn() {
			//规格按钮渲染
			let SkuBtn = {};
			let i = 0;
			let _this = this;
			for (const key in _this.GoodsBack['input'][0].SPU) {
				let Arr = _this.GoodsBack['input'][0].SPU[key];
				let Btn = {};
				Arr.forEach(function (keys) {
					let type = _this.BtnType(i, keys);
					Btn[keys] = {type: type};
					if (type == 0 && keys == _this.form[key]) _this.form[key] = '';
				});
				SkuBtn[key] = Btn;
				++i;
			}
			this.SkuBtn = SkuBtn;
		}, BtnType(index, name) {
			//规格库存匹配
			let _this = this;
			for (const key in _this.Combination) {
				if (_this.Combination.hasOwnProperty.call(_this.Combination, key)) {
					const value = _this.Combination[key];

					for (var is in value) {
						if (value[is] === '' || value[is] === undefined || value[is] === null) {
							value[is] = _this.GoodsBack[is];
						}
					}

					let Arr = {};
					if (key.indexOf('`') != -1) {
						Arr = key.split('`');
					} else Arr = [key];

					if (Arr[index] === name && value.quota >= 1) {
						return 1;
					}
				}
			}
			return 0;
		}, AjaxData() {
			let thie = this;
			let _this = this;
			axios.get("./main.php?act=Goods&gid=" + this.gid).then((res) => {
				var data = res.data;
				if (data.code >= 0) {
					thie.AjaxGoodsMonitoring(thie.gid);
					thie.Goods = data.data;
					_this.num = 1;
					_this.FormSp = {};
					_this.GoodsBack = JSON.parse(JSON.stringify(data.data)); //深层绑定

					if (Object.values(data.data.input).length >= 1) {
						for (const key in data.data.input) {
							if (data.data.input.hasOwnProperty.call(data.data.input, key)) {
								const value = data.data.input[key];
								_this.form[key] = '';
							}
						}
					} else _this.form = [];

					thie.CouponList();
					DataPush(data.data.Push);

					if (data.data.input[0].state == 5) {
						_this.Combination = data.data.input[0]['data']['Parameter'];
						let SPU = data.data.input[0]['SPU'];
						_this.SkuType = 1;
						_this.Btn();
					} else _this.SkuType = -1;

					var coutent_img = "";
					if (data.data.image.length >= 1) {
						$.each(data.data.image, function (key, val) {
							coutent_img += '<div class="swiper-slide"><img src="' + val + '"  /></div>';
						});

						$("#picture").html(coutent_img);
					}

					var swiper = new Swiper(".swiper-container", {
						slidesPerView: 1, spaceBetween: 30, loop: true, pagination: {
							el: ".swiper-pagination", dynamicBullets: true,
						},
					});

					if (data.data.alert != "") {
						mdui.dialog({
							title: '购买须知', content: data.data.alert, modal: true, history: false, buttons: [{
								text: '我已了解',
							}]
						});
					}

					if (data.data.level_arr.length == 1) {
						$("#level").attr("onclick", "mdui.alert('您当前为最高等级密价！','恭喜！')");
					} else {
						var contents = "";
						count = 1;
						$.each(data.data.level_arr, function (key, val) {
							tips = '<span style="float:right;margin-right: 1em;color:seagreen;" >' + val.price + "元</span>";
							contents += "<p>等级：" + val.name + tips + "</p>";
							++count;
						});
						$("#level").attr("onclick", "layer.alert('" + unescape(contents) + "',{title:'密价等级表',btn:['确定','升级密价'],shade: [0.8, '#000'],shadeClose:true,btn2:function(layero,index){window.open('./?mod=route&p=User')}})");
					}
					thie.PriceLs();

					_this.$nextTick(function () {
						_this.AnaLYsis();
					})
				} else {
					layer.msg(data.msg);
				}
			});
		}, AnaLYsis() {
			layui.use(["upload"], function () {
				layui.upload.render({
					elem: "#QrId",
					accept: "images",
					acceptMime: "image/*",
					exts: "jpg|png|gif|jpeg",
					url: $("#QrId").attr("url"),
					size: 8048,
					before: function () {
						layer.load();
					},
					done: function (res) {
						layer.closeAll();
						if (res.code >= 1) {
							layer.open({
								icon: 1, title: "恭喜", content: res.msg, btn: ["插入解析结果", "取消"], btn1: function () {
									vm.form[$("#QrId").attr("index")] = res.content;
									layer.closeAll();
								}
							});
						} else {
							layer.alert(res.msg, {icon: 2});
						}
					},
					error: function () {
						layer.msg("需解析的二维码上传失败,请求接口异常!");
					}
				});
			});

			$("#Selectaddress").click(function () {
				let content = `
		<div id="distpicker" style="width:300px;">
		  <select id="site_1"></select>
		  <select id="site_2"></select>
		  <select id="site_3"></select>
		</div>
		<div style="width:300px;margin-top:10px">
			<input type="text" style="width="100%;height:50px;" id="site_4" placeholder="请填写详细地址" />
		</div>
		`;
				layer.open({
					title: '温馨提示', content: content, btn: ['确定', '取消'], btn1: function () {
						//需执行的操作
						let province = $("#site_1").val();
						let city = $("#site_2").val();
						let district = $("#site_3").val();
						let detail = $("#site_4").val();
						vm.form[$("#Selectaddress").attr("index")] = province + city + district + detail;
						layer.closeAll();
					}, success: function () {
						$('#distpicker').distpicker({
							province: '-- 所在省 --', city: '-- 所在市 --', district: '-- 所在区 --'
						});
					}
				})
			})
		}, PriceLs(type = 0) {
			if (type == 1) {
				this.num--;
			} else if (type == 2) {
				this.num++;
			}
			if (this.num <= 0) this.num = 1;

			if (this.num >= this.Goods.max) {
				this.num = this.Goods.max;
			}

			if (this.num <= this.Goods.min) {
				this.num = this.Goods.min;
			}

			let _this = this;
			if (this.Goods.freight == -1) {
				_this.exceed = ''; //文字说明
				_this.freight = ''; //额外付款金额
				return;
			}

			const Data = JSON.parse(JSON.stringify(this.Goods.freight));
			if (this.Goods.price * this.num >= Data.threshold - 0) {
				_this.exceed = '总金额满' + (Data.threshold - 0) + '元，已减免运费，享受包邮福利！'; //文字说明
				_this.freight = '';
			} else {
				let retos = '';
				let input = JSON.stringify(_this.SubmitData());
				if (Data.region !== '') {
					let retarr = Data.region.split('|');
					for (let key in retarr) {
						let arr = retarr[key].split(',');
						if (input.indexOf(arr[0]) !== -1) {
							retos = arr;
						}
					}
				}

				if (retos !== '') {
					Data.money = retos[1] - 0;
					Data.exceed = retos[2] - 0;
				}

				Data.money -= 0;
				Data.exceed -= 0;
				Data.nums -= 0;
				Data.threshold -= 0;

				if (this.num > Data.nums) {
					_this.freight = ' +' + (Data.money + Data.exceed * (_this.num - Data.nums)) + '元运费';
					_this.exceed = '份数超出' + Data.nums + '份，每份需额外加' + Data.exceed + '元运费，满' + Data.threshold + '元可享受包邮福利！';
				} else {
					_this.freight = ' +' + Data.money + '元运费';
					_this.exceed = '此商品需要额外支付' + Data.money + '元运费，满' + Data.threshold + '元可享受包邮福利！';
				}
			}
		}, extend(index, type, url, state = 1) {

			let _this = this;
			let Input = this.Goods.input;
			let Data = {};

			if (state === 2 && this.form[index] != '' && this.formApi === true) {
				return;
			}

			layer.load(3, {
				time: 9999999
			});

			if (type == 1) {
				if (this.form[index] == '' || this.form[index] == undefined) {
					let content = '请将[' + Input[index].Data[0] + ']填写完整！';
					layer.closeAll();
					if (state !== 1) {
						return;
					}
					layer.alert(content);
					return;
				} else {
					Data['value'] = this.form[index];
				}
			} else {
				for (let i = 0; i < Input.length; i++) {
					if (this.form[i] == undefined || this.form[i] == '') {
						Data['value' + i] = '';
					} else {
						Data['value' + i] = this.form[i];
					}
				}
			}

			this.js = ``;

			axios.get(url, {
				params: Data
			}).then(res => {
				res = res.data;
				layer.closeAll();
				if (res.code >= 0) {
					_this.formApi = true;
					if (type == 1) {
						//替换当前接口输入框
						if (res.value == '' || res.value == undefined) {
							if (state !== 1) {
								return;
							}
							layer.alert('此接口返回的参数不完整，请联系管理员处理！');
							return;
						} else {
							_this.form[index] = res.value;
						}
					} else {
						//替换全部输入框
						if (res.data.length < 1 || res.data == undefined) {
							if (state !== 1) {
								return;
							}
							layer.alert('此接口返回的参数数量 和 当前需填写的输入框的数量不匹配，请联系管理员处理！');
							return;
						} else {
							_this.form = res.data;
						}
					}

					if (res.js != '' && res.js != undefined) {
						_this.js = res.js; //赋值
					} else _this.js = ``;

					if (res.msg != '' && res.msg != undefined) {
						layer.alert(res.msg, {
							title: '提示信息', btn: ['确定'], yes: function (layero, index) {
								layer.closeAll();
								if (_this.js == ``) return;
								eval(_this.js);
							}
						});
					}
				} else {
					if (state !== 1) {
						return;
					}
					content = res.msg == '' || res.msg == undefined ? '未知回调,当前接口存在异常,请联系管理员处理！' : res.msg;
					layer.alert(content);
				}
				_this.$forceUpdate();
			});
		}, LeveLs() {
		},
	},
}).mount("#Appid");

function Sliding() {
	mdui.updateSliders();
}

$('#Pushs').change(function () {
	var data = $(this).val();
	if (data == vm.gid) return;
	vm.onClickImg(data);
});
