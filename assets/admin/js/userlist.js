const App = Vue.createApp({
    data() {
        return {
            Data: [], page: 1, limit: 10, name: '', count: 0, type: -1, GradeList: [], GradeIndex: -1, //级别
            UserSort: -1, //排序
            domain: window.location.host, pattern: 1, //1泛解析,ck绑定
            ColorArr: {},
        }
    }, watch: {
        GradeIndex(val, ts) {
            if (val === ts) return false;
            this.initialization(this.name, this.limit, val);
        }, UserSort(val, ts) {
            if (val === ts) return false;
            this.initialization(this.name, this.limit, this.GradeIndex, val);
        },
    }, methods: {
        BatchEditor(type) {
            mdui.dialog({
                title: '温馨提示', content: '是否要执行此操作，此操作不可撤销!', modal: true, history: false, buttons: [{
                    text: '取消',
                }, {
                    text: '确认操作', onClick: function () {
                        let is = layer.msg('操作中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: 'main.php?act=UserBatchEditor', data: {
                                type: type
                            }, dataType: "json", success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.UserList();
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
                },]
            });
        }, DeleteUser(id) {
            mdui.dialog({
                title: '温馨提示', content: '是否要删除用户[' + id + ']？，删除后不可恢复！', modal: true, history: false, buttons: [{
                    text: '取消',
                }, {
                    text: '确定删除', onClick: function () {
                        let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: 'main.php?act=DeleteUser', data: {
                                id: id,
                            }, dataType: "json", success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.initialization();
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
                }]
            });
        }, Login(id) {
            mdui.dialog({
                title: '温馨提示', content: '是否要登陆用户[' + id + ']的后台？', modal: true, history: false, buttons: [{
                    text: '取消',
                }, {
                    text: '确定', onClick: function () {
                        let is = layer.msg('登录中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: './main.php?act=UserLogin', data: {
                                id: id,
                            }, dataType: "json", success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            layer.closeAll();
                                            open('../user');
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
                }]
            });
        }, colorById(i) {
            const key = i - 0;
            if (this.ColorArr['co_' + key] !== undefined) {
                return this.ColorArr['co_' + key];
            }

            if (i < 10) i = i * 36.5;
            if (i < 100) i = i * 35.2;

            for (; i > 255; i *= 0.98) ;
            var temp = i.toString().substring(i.toString().length - 3);
            i += parseInt(temp);
            for (; i > 255; i -= 255) ;
            i = parseInt(i);
            if (i < 10) i += 10;

            var R = i * (i / 100);
            for (; R > 255; R -= 255) ;
            if (R < 50) R += 60;
            R = parseInt(R).toString(16);

            var G = i * (i % 100);
            for (; G > 255; G -= 255) ;
            if (G < 50) G += 60;
            G = parseInt(G).toString(16);

            var B = i * (i % 10);
            for (; B > 255; B -= 255) ;
            if (B < 50) B += 60;
            B = parseInt(B).toString(16);
            this.ColorArr['co_' + key] = "#" + R + G + B;
            return this.ColorArr['co_' + key];
        }, Grade(key) {
            if (key <= 0) key = 1;
            if (this.GradeList.length === 0) {
                return 'v' + key + '_' + '未设置';
            }
            let count = this.GradeList.length;
            if (key >= count) key = count;
            return 'v' + key + '_' + this.GradeList[(key - 1)].name;
        }, adjustmentValue(id, name, field, value = '') {
            if (value === undefined || value === null || value === '') {
                value = '';
            }
            if (field === 'password') {
                msg = '密码无法直接查看,请直接设置新密码!';
            } else {
                msg = '当前' + name + '为：' + (value === '' ? '空' : value);
            }
            mdui.prompt('要改成什么？,请在下方填写', msg, function (str) {
                App.Ajax(id, field, str);
            }, function () {

            }, {
                type: 'textarea', maxlength: 999999999, defaultValue: value, confirmText: '确认修改', cancelText: '取消',
            })
        }, adjustment(id, num, type) {
            num = App.NumRound(num);
            let content = `
				<div class="layui-form layui-form-pane">
                    <div class="layui-form-item">
					    <label class="layui-form-label">操作类型</label>
					    <div class="layui-input-block">
					      <select name="type">
					        <option value="1">增加` + (type === 1 ? '余额' : '积分') + `</option>
					        <option value="2">扣除` + (type === 1 ? '余额' : '积分') + `</option>
					      </select>
					    </div>
					</div>
					<div class="layui-form-item">
						<label class="layui-form-label">变动` + (type === 1 ? '余额' : '积分') + `</label>
						<div class="layui-input-block">
						  <input type="text" name="money" value="" placeholder="请填写增加或扣除的` + (type === 1 ? '余额' : '积分') + `" class="layui-input">
						</div>
					</div>
				</div>
			`;

            mdui.dialog({
                title: '用户[ ' + id + ' ]' + (type === 1 ? '余额' : '积分') + '调整 - 当前剩余：' + (this.NumRound(num)) + (type === 1 ? '元' : '积分'),
                content: content,
                modal: true,
                history: false,
                buttons: [{
                    text: '取消',
                }, {
                    text: '确定', onClick: function () {
                        let value = App.NumRound($("input[name='money']").val());
                        let types = $("select[name='type']").val() - 0;
                        App.Ajax(id, (type === 1 ? 'money' : 'currency') + (types === 1 ? '[+]' : '[-]'), value);
                    }
                },],
                onOpen: function () {
                    layui.use(['form'], function () {
                        var form = layui.form;
                        form.render();
                    });
                }
            });
        }, Ajax(id, field, value) {
            let is = layer.msg('调整中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=UserRedact', data: {
                    id: id, field: field, value: value
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.UserList();
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, /**
         * @param {Object} value
         * 四舍五入
         * @param type
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
        }, UserList() {
            let is = layer.msg('用户列表载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=UserList', data: {
                    page: App.page,
                    limit: App.limit,
                    name: App.name,
                    GradeIndex: App.GradeIndex,
                    UserSort: App.UserSort,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.GradeList = res.GradeList;
                        App.type = 1;
                        App.pattern = res.type;
                        App.domain = res.domain;
                    } else {
                        App.Data = [];
                        App.type = 1;
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, SearchGoods() {
            mdui.prompt('可输入：用户名,ID,QQ,手机号,名称,域名前缀/后缀,上级ID,IP', '搜索用户', function (str) {
                App.initialization(str);
            }, function () {
            }, {
                type: 'textarea', maxlength: 999999999, defaultValue: App.name, confirmText: '确认搜索', cancelText: '取消',
            });
        }, initialization(name = '', limit = -1, GradeIndex = -1, UserSort = -1) {
            this.page = 1;
            this.limit = (limit === -1 ? this.limit : limit);

            if (name == -2) {
                this.name = '';
            } else {
                this.name = (name === '' ? this.name : name);
            }
            this.GradeIndex = (GradeIndex === -1 ? this.GradeIndex : GradeIndex);
            this.UserSort = (UserSort === -1 ? this.UserSort : UserSort);
            this.type = -1;
            layui.use('laypage', function () {
                var laypage = layui.laypage;
                $.ajax({
                    type: "POST", url: './main.php?act=UserCount', data: {
                        name: App.name, GradeIndex: App.GradeIndex,
                    }, dataType: "json", success: function (res) {
                        if (res.code == 1) {
                            App.count = res.count;
                            laypage.render({
                                elem: 'Page',
                                count: res.count,
                                theme: '#641ec6',
                                limit: App.limit,
                                limits: [1, 10, 20, 30, 50, 100, 200],
                                groups: 3,
                                first: '首页',
                                last: '尾页',
                                prev: '上一页',
                                next: '下一页',
                                skip: true,
                                layout: ['count', 'page', 'prev', 'next', 'limit', 'limits'],
                                jump: function (obj) {
                                    App.page = obj.curr;
                                    App.limit = obj.limit;
                                    App.UserList();
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
            });
        }
    }
}).mount('#App');

App.initialization();