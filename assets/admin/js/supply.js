const App = Vue.createApp({
    data() {
        return {
            ServerData: {}, SupplyDataList: {}, SuppId: -1, Supply: {}, ClassData: false, //分类列表数据
            cid: -1, DockingListData: false, //对接列表
            Docking: false, DockingList: {}, //可对接商品列表
            DockingId: -1, //选择的对接货源键值
            DockingSearch: '', //商品关键词
            DockingClassSearch: '', //货源列表关键词
            SupplierSearch: '', //供货商关键词
            MyIslandData: false, //我的商城数据
            MyIslandList: false, //商城海数据列表
            MyIslandSearch: '', //搜索内容
            score: 3, //评分
            AuCn: false, //自动同意跳过失败商品
            SuccessCount: 0, ErrorCount: 0,

            MyPage: 1, MyLimit: 38, Mypattern: 1,//表格，列表
            MyCount: 0, //数量
            MyMoney: 1, //充值金额
            MyPayType: '', //付款方式
            MyPayTypeZ: '', //付款参数
        }
    }, methods: {
        SelectMyMoney(money) {
            App.MyMoney = money;
            console.log(App.MyMoney);
        }, SelectMyPayType(type, ts) {
            App.MyPayType = type;
            App.MyPayTypeZ = ts;
            console.log(App.MyPayType, App.MyPayTypeZ);
        }, Pay() {
            let is = layer.msg('数据获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=MyPayList', dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {

                        let PayHtml = '';

                        for (const payHtmlKey in res.data) {
                            let data = res.data[payHtmlKey];
                            for (const dataKey in data.data) {
                                let paydata = data.data[dataKey];
                                if (paydata.state == false) {
                                    continue;
                                }
                                console.log(paydata, dataKey);
                                PayHtml += `
<label class="mdui-radio" style="margin-right:0.5rem;">
    <input onclick="App.SelectMyPayType('` + data.key + `','` + dataKey + `')" ` + (App.MyPayType == data.key && App.MyPayTypeZ == dataKey ? 'checked="checked" ' : '') + ` type="radio" name="type"/>
    <i class="mdui-radio-icon"></i>
    [` + data.name + `] ` + paydata.name + `
</label>
                            `;
                            }
                        }

                        if (PayHtml === '') {
                            PayHtml = '当前无可用付款方式，请联系管理员充值！';
                        }

                        let content = `
<fieldset class="layui-elem-field">
  <legend>充值金额</legend>
  <div class="layui-field-box">
  <label class="mdui-radio" style="margin-right:0.5rem">
    <input onclick="App.SelectMyMoney(10)" ` + (App.MyMoney == 10 ? 'checked="checked" ' : '') + ` type="radio"name="moeny"/>
    <i class="mdui-radio-icon"></i>
    充值10元
  </label>
  <label class="mdui-radio" style="margin-right:0.5rem">
    <input onclick="App.SelectMyMoney(50)" ` + (App.MyMoney == 50 ? 'checked="checked" ' : '') + ` type="radio" name="moeny"/>
    <i class="mdui-radio-icon"></i>
    充值50元
  </label>
  <label class="mdui-radio" style="margin-right:0.5rem">
    <input type="radio" onclick="App.SelectMyMoney(100)" ` + (App.MyMoney == 100 ? 'checked="checked" ' : '') + ` name="moeny"/>
    <i class="mdui-radio-icon"></i>
    充值100元
  </label>
  <label class="mdui-radio" style="margin-right:0.5rem">
    <input type="radio" onclick="App.SelectMyMoney(500)" ` + (App.MyMoney == 500 ? 'checked="checked" ' : '') + ` name="moeny"/>
    <i class="mdui-radio-icon"></i>
    充值500元
  </label>
  <label class="mdui-radio" style="margin-right:0.5rem">
    <input type="radio" onclick="App.SelectMyMoney(1000)" ` + (App.MyMoney == 1000 ? 'checked="checked" ' : '') + ` name="moeny"/>
    <i class="mdui-radio-icon"></i>
    充值1000元
  </label>
  <label class="mdui-radio">
    <input type="radio" onclick="App.SelectMyMoney(2000)" ` + (App.MyMoney == 2000 ? 'checked="checked" ' : '') + ` name="moeny"/>
    <i class="mdui-radio-icon"></i>
    充值2000元
  </label>
  </div>
</fieldset>
<fieldset class="layui-elem-field">
  <legend>付款方式</legend>
  <div class="layui-field-box">
   ` + PayHtml + `
  </div>
</fieldset>
            `;
                        mdui.dialog({
                            title: '在线充值', content: content, modal: true, history: false, buttons: [{
                                text: '关闭',
                            }, {
                                text: '创建充值订单', onClick: function () {
                                    if (App.MyPayType == '' || App.MyPayTypeZ == '') {
                                        layer.msg('请选择付款方式！', {
                                            icon: 2
                                        });
                                        return;
                                    }
                                    App.MyPayOrder();
                                }
                            }]
                        });
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, MyPayOrder() {
            let is = layer.msg('正在创建充值订单中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=MyPayOrder', data: {
                    money: App.MyMoney,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code >= 0) {
                        App.MyPay(res.order);
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, MyPay(order) {
            //开始付款
            let Data = App.MyIslandData.Pay;
            let is = layer.msg('正在生成付款界面,请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=MyPay', data: {
                    order: order,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code >= 0) {
                        let is = layer.msg('数据获取中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: 'main.php?act=MyPayTs', data: {
                                order: order, key: App.MyPayTypeZ, type: App.MyPayType
                            }, dataType: "json", success: function (res3) {
                                layer.close(is);
                                if (res3.code == 1) {
                                    console.log(res3, res);

                                    if (App.MyPayTypeZ === 'wxpayH5') {
                                        res3.type = 1;
                                        App.MyPayType = '<font color="red">非手机微信客户端</font>,如QQ,浏览器';
                                    }

                                    if (res3.type == 1) {
                                        //生成付款二维码
                                        let content = `
<fieldset class="layui-elem-field" style="text-align:center">
  <h4 style="margin-top:1rem">请使用【` + App.MyPayType + `】扫码付款` + res.data.payment + `元</h4>
  <div class="layui-field-box">
    <img src="` + Data.QrUrl + encodeURIComponent(res3.url) + `" style="width:258px;height:258px;box-shadow: 1px 1px 12px #eee" />
    <hr>
    订单号：` + res.data.order + `
    <hr>
    <a href="` + res3.url + `" target="_blank">点击打开付款地址</a>
  </div>
</fieldset>
                            `;
                                        mdui.dialog({
                                            title: res.data.name, content: content, history: false, buttons: [{
                                                text: '取消付款',
                                            }, {
                                                text: '我已经完成付款', onClick: function () {
                                                    App.MyIslandGet(3);
                                                }
                                            }]
                                        });
                                    } else {
                                        //打开新页面
                                        window.open(res3.url);
                                        layer.open({
                                            title: '温馨提示',
                                            content: '付款后点击下方按钮刷新余额！',
                                            icon: 16,
                                            btn: ['我已完成付款', '取消'],
                                            btn1: function () {
                                                App.MyIslandGet(3);
                                            }
                                        })
                                    }
                                } else {
                                    layer.alert(res3.msg, {
                                        icon: 2
                                    });
                                }
                            }, error: function () {
                                layer.msg('服务器异常！');
                            }
                        });
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, SiteDetails(data) { //站点详情
            /**
             * 站点名称，地址，克隆密钥，级别，评论列表，新增评论
             */
            let is = layer.msg('数据获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=MyIslandCommentList', data: {
                    id: data.id,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        let comment = '<ul class="mdui-list">';
                        for (const key in res.data) {
                            let val = res.data[key];
                            comment += `<li class="mdui-list-item mdui-ripple">
    <div class="mdui-list-item-avatar"><img mdui-tooltip="{content: '用户ID：` + val.uid + `<br>评论时间：` + val.addtime + `', position: 'top'}" src="` + val.image + `"/></div>
    <div class="mdui-list-item-content">
      <div class="mdui-list-item-title">` + val.name + ` - ` + val.score + `分 </div>
      <div class="mdui-list-item-text mdui-text-color-grey-900">` + val.content + `</div>
    </div>
  </li>
  <li class="mdui-subheader-inset">用户ID：` + val.uid + ' | ' + val.addtime + `</li>`;
                        }

                        let content = `
                <div class="mdui-card">
                    <div class="mdui-card-header">
                        <img class="mdui-card-header-avatar" src="` + data.Icon + `" />
                        <div class="mdui-card-header-title">` + data.name + `</div>
                        <div class="mdui-card-header-subtitle">` + data.domain + `</div>
                    </div>
                    <div class="mdui-card-content mdui-p-b-0" >` + data.introduce + `<hr>
                        <span class="badge badge-primary-lighten"> UID:` + data.uid + `</span>
                        <span class="badge badge-primary-lighten"> Lv ` + data.grade[0] + ` 站长</span>
                        <span ` + (data.agent == 1 ? '' : 'style="display:none;"') + ` class="badge badge-info-lighten">系统代理商</span>
                        <span ` + (data.agent == 2 ? '' : 'style="display:none;"') + ` class="badge badge-warning-lighten">系统授权商</span>
                        <span ` + (data.supplier == 1 ? '' : 'style="display:none;"') + ` class="badge badge-success-lighten">系统供货商</span>
                        <span ` + (data.clone_key != -1 ? '' : 'style="display:none;"') + ` class="badge badge-danger-lighten">克隆密钥：` + data.clone_key + `</span>
                    </div>
                    <div class="mdui-card-content mdui-m-t-0 mdui-p-t-0">
` + (comment != '<ul class="mdui-list">' ? '<hr>' + comment + '</ul>' : '') + `
                    </div>
                </div>
`;
                        mdui.dialog({
                            title: '站点详情 - ' + data.name, content: content, modal: true, history: false, buttons: [{
                                text: '关闭',
                            }, {
                                text: '评论', onClick: function () {
                                    App.InitiateComments(data.id, data.name);
                                }
                            }], onOpen: function () {

                            }
                        });

                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, InitiateComments(id, name) { //评论
            let content = `
<div class="mdui-textfield mdui-textfield-floating-label">
  <label class="mdui-textfield-label">评论内容 - 所有人可见</label>
  <textarea id="com" class="mdui-textfield-input"></textarea>
  <div id="score"></div>
</div>
                `;

            mdui.dialog({
                title: '发起新评论 - ' + name, content: content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }, {
                    text: '提交评论', onClick: function () {
                        if (App.score < 1 || App.score > 6) {
                            App.score = 3;
                        }
                        let text = $("#com").val();
                        if (text == '') {
                            layer.open({
                                title: '警告', content: '请将评论内容填写完整！', icon: 2, btn: ['好的'], btn1: function () {
                                    layer.closeAll();
                                    App.InitiateComments(id, name);
                                }
                            });
                            return false;
                        }
                        let is = layer.msg('评论中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: 'main.php?act=MyIslandInitiateComments', data: {
                                id: id, msg: text, score: App.score,
                            }, dataType: "json", success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert((!res.msg ? '异常' : res.msg), {
                                        icon: 1, btn1: function () {
                                            App.MyIslandListGet(2);
                                        }
                                    });
                                } else {
                                    layer.alert((!res.msg ? '异常' : res.msg), {
                                        icon: 2
                                    });
                                }
                            }, error: function () {
                                layer.msg('服务器异常！');
                            }
                        });
                    }
                }], onOpen: function () {
                    layui.use('rate', function () {
                        layui.rate.render({
                            elem: '#score', length: 6, value: App.score, theme: 'red', choose: function (value) {
                                App.score = value;
                            }
                        });
                    });
                    mdui.mutation();
                }
            });
        }, GiveThumbsUp(type, data, state = 1) { //点赞，踩
            let content = '';
            if (state === 1) {
                content = (type === 1 ? '是否要为站点 [ ' + data.name + ' ] 点赞？点赞后这个站点可以获得1-5点热度！' : '是否要踩一下站点 [ ' + data.name + ' ] ？，踩后这个站点会扣除1-5点热度！') + '<hr>站点的热度值越高，排名越靠前！';
            } else {
                content = (type === 1 ? '是否要取消对站点[ ' + data.name + ' ]的点赞？取消后该站点获得的热度会扣除！' : '是否要取消对站点[ ' + data.name + ' ]的踩？，取消后这个站点会恢复扣除的热度！') + '<hr>站点的热度值越高，排名越靠前！';
            }
            mdui.dialog({
                title: '温馨提示', content: content, modal: true, history: false, buttons: [{
                    text: '取消',
                }, {
                    text: '确定', onClick: function () {
                        let is = layer.msg('执行中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST", url: 'main.php?act=MyIslandGiveThumbs', data: {
                                id: data.id, type: type, state: state,
                            }, dataType: "json", success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert((!res.msg ? '异常' : res.msg), {
                                        icon: 1, end: function () {
                                            App.MyIslandListGet(2);
                                        }
                                    });
                                } else if (res.code == -2) {
                                    //取消赞或者踩
                                    App.GiveThumbsUp(res.type, data, 2);
                                } else {
                                    layer.alert((!res.msg ? '异常' : res.msg), {
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
        }, ContactCustomerServiceMy(data) { //商城海联系客服
            let content = ``;
            if (data.qq != -1) {
                content += '联系QQ：' + data.qq + '<br><br>';
            }

            if (data.wx != -1) {
                content += '联系微信：' + data.wx + '<br><br>';
            }

            if (data.group != -1) {
                content += '加群链接：<a href="' + data.group + '" target="_blank">' + data.group + '</a>';
            }

            if (data.yzf == -1) {
                mdui.dialog({
                    title: '联系站长', content: content, modal: true, history: false, buttons: [{
                        text: '关闭',
                    }]
                });
            } else {
                mdui.dialog({
                    title: '联系站长', content: content, modal: true, history: false, buttons: [{
                        text: '关闭',
                    }, {
                        text: '在线咨询', onClick: function () {
                            open('https://yzf.qq.com/xv/web/static/chat/index.html?sign=' + data.yzf);
                        }
                    }]
                });
            }
        }, SignDaily(t) { //每日签到
            if (t != -1) {
                mdui.dialog({
                    title: '通知',
                    content: '您今日已经签到过了，签到时间：' + t + '<hr>若想要获得更多热度值，可通过购买，或其他站点点赞获得！',
                    modal: true,
                    history: false,
                    buttons: [{
                        text: '关闭',
                    }]
                });
                return false;
            }

            mdui.dialog({
                title: '签到提醒',
                content: '您今日还未签到过哦，此签到和服务端签到不同，是专门为热度值开设的<br>每日签到可随机获得1~10点热度值！，热度值越高，站点排名越靠前！',
                modal: true,
                history: false,
                buttons: [{
                    text: '关闭',
                }, {
                    text: '确认签到', onClick: function () {
                        let is = layer.msg('签到中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=MyIslandSignDaily',
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert((!res.msg ? '异常' : res.msg), {
                                        icon: 1, btn1: function () {
                                            App.MyIslandGet(2);
                                        }
                                    });
                                } else {
                                    layer.alert((!res.msg ? '异常' : res.msg), {
                                        icon: 2
                                    });
                                }
                            },
                            error: function () {
                                layer.msg('服务器异常！');
                            }
                        });
                    }
                },],
                onOpen: function () {

                }
            });
        }, ViewComments(data) { //查看评论
            let content = '<ul class="mdui-list">';
            for (const key in data) {
                let val = data[key];
                content += `<li class="mdui-list-item mdui-ripple">
    <div class="mdui-list-item-avatar"><img mdui-tooltip="{content: '用户ID：` + val.uid + `<br>评论时间：` + val.addtime + `', position: 'top'}" src="` + val.image + `"/></div>
    <div class="mdui-list-item-content">
      <div class="mdui-list-item-title">` + val.name + ` - ` + val.score + `分 </div>
      <div class="mdui-list-item-text mdui-text-color-grey-900">` + val.content + `</div>
    </div>
  </li>
  <li class="mdui-subheader-inset">用户ID：` + val.uid + ' | ' + val.addtime + `</li>`;
            }
            mdui.dialog({
                title: '评论内容', content: content + '</ul>', modal: true, history: false, buttons: [{
                    text: '关闭',
                }]
            });
        }, BuyingEnthusiasm() {
            mdui.dialog({
                title: '热度购买提醒',
                content: '热度购买价格：<font color=red size=5>1元=10点</font><br>热度可通过其他用户点赞获得，热度越高，则在商城海的排名越靠前，获得更多的展示机会！<hr>Ps：热度会缓慢增加，每天约增加1-10点左右，仅对公开展示状态的站点有效',
                modal: true,
                history: false,
                buttons: [{
                    text: '关闭',
                }, {
                    text: '购买热度', onClick: function () {
                        layer.prompt({
                            formType: 3, value: 10, title: '请输入购买金额，10元=100点热度值',
                        }, function (value, index, elem) {
                            let count = parseInt(value - 0);
                            if (count < 1) {
                                layer.msg('最低购买1元热度值(10点)', {icon: 2});
                                return false;
                            }

                            if (App.MyIslandData.money < count) {
                                layer.open({
                                    title: '警告',
                                    content: '当前服务端余额不足' + count + '元！<br>当前余额为：' + App.MyIslandData.money + '元<br>请先充值！',
                                    icon: 2,
                                    btn: ['充值', '更新数据', '取消'],
                                    btn1: function () {
                                        open('https://cdn.79tian.com/api/wxapi/view/index.php');
                                    },
                                    btn2: function () {
                                        App.MyIslandGet(2);
                                    }
                                })
                                return false;
                            }
                            layer.open({
                                title: '温馨提示',
                                icon: 3,
                                content: '是否要购买<font color=red>' + count * 10 + '点</font>热度值?<br>消耗余额：<font color=red>' + count + '元</font><br>当前余额为：<font color=red>' + App.MyIslandData.money + '元</font>！',
                                btn: ['确定购买', '取消'],
                                btn1: function () {
                                    let is = layer.msg('购买中，请稍后...', {icon: 16, time: 9999999});
                                    $.ajax({
                                        type: "POST", url: 'main.php?act=MyIslandPurchase', data: {
                                            money: count
                                        }, dataType: "json", success: function (res) {
                                            layer.close(is);
                                            if (res.code == 1) {
                                                layer.alert((!res.msg ? '异常' : res.msg), {
                                                    icon: 1, btn1: function () {
                                                        App.MyIslandGet(2);
                                                    }
                                                });
                                            } else {
                                                layer.alert((!res.msg ? '异常' : res.msg), {
                                                    icon: 2
                                                });
                                            }
                                        }, error: function () {
                                            layer.msg('服务器异常！');
                                        }
                                    });
                                }
                            })
                            layer.close(index);
                        });
                    }
                }],
                onOpen: function () {

                }
            });
        }, StateSet(state) {
            let is = layer.msg('正在切换中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=MyIslandState', data: {
                    state: state
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.MyIslandGet(2);
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, DataSynchronization() {
            layer.open({
                title: '温馨提示', content: '是否要同步数据？', icon: 3, btn: ['确定', '取消'], btn1: function () {
                    let is = layer.msg('正在同步中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: 'main.php?act=MyIslandSynchronization',
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert((!res.msg ? '异常' : res.msg), {
                                    icon: 1, btn1: function () {
                                        App.MyIslandGet(2);
                                    }
                                });
                            } else {
                                layer.alert((!res.msg ? '异常' : res.msg), {
                                    icon: 2
                                });
                            }
                        },
                        error: function () {
                            layer.msg('服务器异常！');
                        }
                    });
                }
            })
        }, UpdateData() {
            layer.open({
                title: '温馨提示', content: '是否要更新数据？', icon: 3, btn: ['确定', '取消'], btn1: function () {
                    let is = layer.msg('正在更新中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: 'main.php?act=MyIslandSet',
                        data: App.MyIslandData,
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert((!res.msg ? '异常' : res.msg), {
                                    icon: 1, btn1: function () {
                                        App.MyIslandGet(2);
                                    }
                                });
                            } else {
                                layer.alert((!res.msg ? '异常' : res.msg), {
                                    icon: 2
                                });
                            }
                        },
                        error: function () {
                            layer.msg('服务器异常！');
                        }
                    });
                }
            })
        }, ViewData(data, type = 1) {
            if (data.length === 0) {
                layer.msg(type === 1 ? '没有任何人为你点赞~' : '没有任何人踩你~');
                return false;
            }

            let content = '';

            for (const key in data) {
                let str = data[key].split(',');
                content += `<tr><td>` + (key - 0 + 1) + `</td><td>` + str[0].substr(1, str[0].length - 2) + `</td><td>` + str[1] + `点</td><td>` + str[2] + `</td></tr>`;
            }
            mdui.dialog({
                title: '数据看板 - ' + (type === 1 ? '赞' : '踩'), content: `<div class="mdui-table-fluid">
  <table class="mdui-table">
    <thead>
      <tr>
        <th>#</th>
        <th>用户ID</th>
        <th>` + (type === 1 ? '奖励' : '扣除') + `热度</th>
        <th>发生时间</th>
      </tr>
    </thead>
    <tbody>
    ` + content + `
    </tbody>
  </table>
</div>`, modal: true, history: false, buttons: [{
                    text: '关闭',
                }]
            });
        }, MyPageGet(type = 1) {
            if (type === 1) {
                ++App.MyPage;
            } else {
                --App.MyPage;
            }
            if (App.MyPage <= 1) {
                App.MyPage = 1;
            }
            App.MyIslandListGet(2);
        }, MyIslandListGet() {
            let is = layer.msg('数据获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=MyIslandList', dataType: "json", data: {
                    page: App.MyPage, limit: App.MyLimit,
                }, success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.MyIslandList = res.data;
                        App.MyCount = res.count;
                    } else {
                        App.MyIslandList = false;
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, MyIslandGet(type = 1) {
            if (type === 1 && App.MyIslandData !== false) {
                return;
            }
            let is = layer.msg('数据获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=MyIsland', dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        if (type === 3 && App.MyIslandData.money != res.money) {
                            mdui.dialog({
                                title: '充值成功',
                                content: '您本次成功充值：' + (res.money - App.MyIslandData.money) + '元！',
                                modal: true,
                                history: false,
                                buttons: [{
                                    text: '关闭',
                                }]
                            });
                            App.MyIslandData = res;
                        } else {
                            App.MyIslandData = res;
                        }
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, SupplierSearchState(data) { //供货商显示状态
            if (this.SupplierSearch === '') {
                return true;
            }
            return !!((data.name).includes(this.SupplierSearch) || (data.id).includes(this.SupplierSearch) || (data.qq).includes(this.SupplierSearch) || (data.name).includes(this.SupplierSearch));
        }, MyIslandStatus(data) { //商城显示状态
            if (this.MyIslandSearch === '') {
                return true;
            }
            return !!((data.uid).includes(this.MyIslandSearch) || (data.name).includes(this.MyIslandSearch) || (data.domain).includes(this.MyIslandSearch) || (data.qq).includes(this.MyIslandSearch) || (data.wx).includes(this.MyIslandSearch));
        }, DockingCommodityClassStatus(data) { //货源显示状态
            if (this.DockingClassSearch === '') {
                return true;
            }
            return (data.url).includes(this.DockingClassSearch);
        }, DockingCommodityStatus(data) { //商品显示状态(根据搜索内容判断)
            if (this.DockingSearch === '') {
                return true;
            }
            return (data.name).includes(this.DockingSearch);
        }, BatchLaunch() { //商品批量上架
            let Data = $("#SupplyT2 .mdui-table-row-selected .GoodsID").find().prevObject;
            if (Data.length === 0) {
                mdui.dialog({
                    title: '警告', content: '最少选择一个需要上架的商品！', modal: true, history: false, buttons: [{
                        text: '关闭',
                    }]
                });
                return false;
            }

            App.SuccessCount = 0;
            App.ErrorCount = 0;

            let GoodsList = [];
            let s = 0
            for (let i = 0; i < Data.length; i++) {
                if (Data[i].attributes.show.value == 2) {
                    continue;
                }
                let id = (Data[i].id).split('_');
                GoodsList[s] = {
                    'data': this.DockingList[id[1]], 'key': id[1],
                };
                GoodsList[s + 1] = false;
                ++s;
            }

            if (this.ClassData === false || this.ClassData.length === 0) {
                mdui.dialog({
                    title: '警告', content: '当前站点无任何商品分类，请先去添加商品分类！', modal: true, history: false, buttons: [{
                        text: '关闭',
                    }]
                });
                return;
            }

            let html = ``;
            for (const index in this.ClassData) {
                let item = this.ClassData[index];
                html += `<label class="mdui-list-item mdui-ripple">
                            <div class="mdui-list-item-avatar mdui-color-white"><img src="` + item.image + `"/></div>
                            <div onclick="App.SelectCategories(` + item.cid + `)" class="mdui-list-item-content">` + item.name + `</div>
                            <label class="mdui-radio">
                                <input type="radio" value="` + item.cid + `" name="cid"
                                ` + (App.cid == item.cid ? 'checked="checked" ' : '') + `
                                />
                                <i class="mdui-radio-icon"></i>
                                CID:` + item.cid + `
                            </label>
                        </label>`;
            }

            let content = `<div class="mdui-list">
` + html + `
                    </div>`;
            mdui.dialog({
                title: '商品批量串货，当前已选择' + (GoodsList.length - 1) + '个商品',
                content: '请选择需要存放这些商品的分类：<br>' + content,
                modal: true,
                history: false,
                buttons: [{
                    text: '关闭',
                }, {
                    text: '开始一键上架商品', onClick: function () {
                        if (App.cid <= 0) {
                            mdui.dialog({
                                title: '警告', content: '请先选择需要添加商品的分类！', modal: true, history: false, buttons: [{
                                    text: '关闭',
                                }]
                            });
                            return;
                        }
                        //开始上架商品
                        App.ProductDetails(GoodsList[0]['data'], GoodsList[0]['key'], 2, GoodsList, 0);
                    }
                }],
                onOpen: function () {
                    mdui.mutation();
                }
            });
        }, ProductDetails(item, key, type = 1, GoodsList = false, index = false) {
            let is = layer.msg((type === 1 ? '商品数据获取中，请稍后...' : '正在上架商品【' + item.name + '】<br>当前是第：' + (index + 1) + '个，共：' + (GoodsList.length - 1) + '个商品...'), {
                icon: 16, time: 9999999
            });
            $.ajax({
                type: "POST", url: './main.php?act=DataInterface', data: {
                    gid: item.gid,
                    cid: item.cid,
                    key: key,
                    controller: App.Docking[App.DockingListData[App.DockingId].class_name]['InputField']['ProductList']['request']['controller'],
                    index: 'ProductList',
                    sqid: App.DockingListData[App.DockingId]['id'],
                    Source: App.DockingListData[App.DockingId].class_name,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        //数据获取成功
                        let Data = {};
                        Data.data = JSON.stringify(res.data); //商品数据
                        Data.class_name = App.DockingListData[App.DockingId].class_name; //货源类型
                        Data.name = res.data.name; //商品名字
                        Data.sqid = App.DockingListData[App.DockingId]['id']; //社区ID
                        Data.content = '商品成本：' + res.data.money + '元 ' + res.data.quantity + (res.data.units === null || res.data.units === undefined ? '个' : res.data.units) + '<br>剩余库存：' + res.data.quota + '<hr>';
                        if (type === 1) {
                            App.AddProductsQuickly(Data);
                        } else {
                            Data.cid = App.cid;
                            App.AddProductsQuicklyBatch(Data, GoodsList, index + 1);
                        }
                    } else {
                        if (type === 1) {
                            layer.alert((!res.msg ? '异常' : res.msg), {
                                icon: 2
                            });
                        } else {
                            ++App.ErrorCount;
                            if (App.AuCn === true) {
                                if (GoodsList[(index + 1)] === false) {
                                    App.AuCn = false;
                                    layer.open({
                                        title: '温馨提示',
                                        icon: 1,
                                        content: '商品批量上架指令执行完成，本次成功上架了' + App.SuccessCount + '个商品，失败了' + App.ErrorCount + '个商品！',
                                        btn: ['打开商品列表', '关闭'],
                                        btn1: function () {
                                            open('admin.goods.list.php');
                                        }
                                    });
                                } else {
                                    App.ProductDetails(GoodsList[(index + 1)]['data'], GoodsList[(index + 1)]['key'], 2, GoodsList, (index + 1));
                                }
                                return false;
                            }

                            if (GoodsList[(index + 1)] === false) {
                                App.AuCn = false;
                                layer.open({
                                    title: '温馨提示',
                                    icon: 1,
                                    content: '商品批量上架指令执行完成，本次成功上架了' + index + '个商品！',
                                    btn: ['打开商品列表', '关闭'],
                                    btn1: function () {
                                        open('admin.goods.list.php');
                                    }
                                });
                                return false;
                            }

                            layer.open({
                                title: '温馨提示',
                                icon: 2,
                                content: (!res.msg ? '异常' : res.msg) + '<br>问题商品：' + item.name,
                                btn: ['跳过此商品，继续执行', '本次上架结束前,默认继续执行', '关闭'],
                                btn1: function () {
                                    App.ProductDetails(GoodsList[(index + 1)]['data'], GoodsList[(index + 1)]['key'], 2, GoodsList, (index + 1));
                                },
                                btn2: function () {
                                    App.AuCn = true;
                                    App.ProductDetails(GoodsList[(index + 1)]['data'], GoodsList[(index + 1)]['key'], 2, GoodsList, (index + 1));
                                }
                            });
                        }
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, AddProductsQuicklyBatch(data, GoodsList, index) { //批量上架商品
            let is = layer.msg('正在尝试添加商品【' + data.name + '】。。。<br>当前是第：' + index + '个，共：' + (GoodsList.length - 1) + '个商品...', {
                icon: 16, time: 9999999
            });
            $.ajax({
                type: "POST",
                url: './main.php?act=AddProductsQuickly',
                data: data,
                dataType: "json",
                success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        ++App.SuccessCount;
                        console.log('商品[' + data.name + ']上架成功，进度：[' + (GoodsList.length - 1) + '/' + index + ']');
                        if (GoodsList[index] !== false) {
                            App.ProductDetails(GoodsList[index]['data'], GoodsList[index]['key'], 2, GoodsList, index);
                        } else {
                            App.AuCn = false;
                            layer.open({
                                title: '温馨提示',
                                icon: 1,
                                content: '商品批量上架指令执行完成，本次成功上架了' + App.SuccessCount + '个商品，失败了' + App.ErrorCount + '个商品！',
                                btn: ['打开商品列表', '关闭'],
                                btn1: function () {
                                    open('admin.goods.list.php');
                                }
                            });
                        }
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg) + '<hr>问题商品：' + data.name, {
                            icon: 2
                        });
                    }
                },
                error: function () {
                    layer.msg('服务器异常,可能是本批上架的商品太多，卡死了，请减少本批商品上架数量！');
                }
            });

        }, DockingProductList(index, type = 1) { //获取对接商品列表
            let data = this.DockingListData[index];
            let is = layer.msg('可对接商品列表获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=DataInterface', data: {
                    id: data.id,
                    Source: data.class_name,
                    controller: App.Docking[data.class_name]['InputField']['ProductList']['GetListData']['controller'],
                    index: 'ProductList',
                    type: type,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        if (res.data.length === 0 || res.data === undefined) {
                            layer.msg('可对接商品数量为0！', {
                                icon: 2,
                            });
                            return false;
                        }
                        App.DockingId = index;
                        App.DockingList = res.data;
                        App.$nextTick(function () {
                            mdui.updateTables();
                        });
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, DockingListDataGet(type = 1) {
            if (App.DockingListData !== false && type === 1) {
                return;
            }
            let is = layer.msg('可对接货源列表获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=SourceDataList', dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.DockingListData = res.data;
                        App.Docking = res.Docking;
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                        App.DockingListData = [];
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, SelectCategories(cid = -1) {
            this.cid = cid;
            console.log(this.cid);
        }, BatchLaunchSupply() {
            let Data = $("#SupplyT1 .mdui-table-row-selected .GoodsIDT1").find().prevObject;
            if (Data.length === 0) {
                mdui.dialog({
                    title: '警告', content: '最少选择一个需要上架的商品！', modal: true, history: false, buttons: [{
                        text: '关闭',
                    }]
                });
                return false;
            }

            App.SuccessCount = 0;
            App.ErrorCount = 0;

            let GoodsList = [];
            for (let i = 0; i < Data.length; i++) {
                let id = (Data[i].id).split('_');
                GoodsList[i] = {
                    'data': this.SupplyDataList.data[id[1]], 'key': id[1],
                };
                GoodsList[i + 1] = false;
            }
            if (this.ClassData === false || this.ClassData.length === 0) {
                mdui.dialog({
                    title: '警告', content: '当前站点无任何商品分类，请先去添加商品分类！', modal: true, history: false, buttons: [{
                        text: '关闭',
                    }]
                });
                return;
            }

            let html = ``;
            for (const index in this.ClassData) {
                let item = this.ClassData[index];
                html += `<label class="mdui-list-item mdui-ripple">
                            <div class="mdui-list-item-avatar mdui-color-white"><img src="` + item.image + `"/></div>
                            <div onclick="App.SelectCategories(` + item.cid + `)" class="mdui-list-item-content">` + item.name + `</div>
                            <label class="mdui-radio">
                                <input type="radio" value="` + item.cid + `" name="cid"
                                ` + (App.cid == item.cid ? 'checked="checked" ' : '') + `
                                />
                                <i class="mdui-radio-icon"></i>
                                CID:` + item.cid + `
                            </label>
                        </label>`;
            }

            let content = `<div class="mdui-list">
` + html + `
                    </div>`;
            mdui.dialog({
                title: '商品批量串货，当前已选择' + (GoodsList.length - 1) + '个商品',
                content: '请选择需要存放这些商品的分类：<br>' + content,
                modal: true,
                history: false,
                buttons: [{
                    text: '关闭',
                }, {
                    text: '开始一键上架商品', onClick: function () {
                        if (App.cid <= 0) {
                            mdui.dialog({
                                title: '警告', content: '请先选择需要添加商品的分类！', modal: true, history: false, buttons: [{
                                    text: '关闭',
                                }]
                            });
                            return;
                        }
                        //开始上架商品
                        App.ProductsSale(GoodsList[0]['data'], GoodsList[0]['key'], 2, GoodsList, 0);
                    }
                }],
                onOpen: function () {
                    mdui.mutation();
                }
            });
        }, ProductsSale(data, key = false, type = 1, GoodsList = false, index = false) { //服务端商品上架
            let Data = {};
            Data.data = JSON.stringify(data); //商品数据
            Data.class_name = 'official'; //货源类型
            Data.name = data.name; //商品名字
            this.AddProductsQuickly(Data, key, type, GoodsList, index);
        }, AddProductsQuickly(data, key = false, type = 1, GoodsList = false, index = false) {
            if (this.ClassData === false || this.ClassData.length === 0 && type === 1) {
                mdui.dialog({
                    title: '警告', content: '当前站点无任何商品分类，请先去添加商品分类！', modal: true, history: false, buttons: [{
                        text: '关闭',
                    }]
                });
                return;
            }

            if (type === 2) {
                data.cid = App.cid;
                let is = layer.msg('正在上架商品【' + data.name + '】<br>当前是第：' + (index + 1) + '个，共：' + (GoodsList.length - 1) + '个商品...', {
                    icon: 16, time: 9999999
                });
                $.ajax({
                    type: "POST",
                    url: './main.php?act=AddProductsQuickly',
                    data: data,
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            ++App.SuccessCount;
                            console.log('商品[' + data.name + ']上架成功，进度：[' + (GoodsList.length - 1) + '/' + (index + 1) + ']');
                            if (GoodsList[index + 1] !== false) {
                                App.ProductsSale(GoodsList[(index + 1)]['data'], GoodsList[(index + 1)]['key'], 2, GoodsList, (index + 1));
                            } else {
                                App.AuCn = false;
                                layer.open({
                                    title: '温馨提示',
                                    icon: 1,
                                    content: '商品批量上架指令执行完成，本次成功上架了' + App.SuccessCount + '个商品，失败了' + App.ErrorCount + '个商品！',
                                    btn: ['打开商品列表', '关闭'],
                                    btn1: function () {
                                        open('admin.goods.list.php');
                                    }
                                });
                            }
                        } else {
                            ++App.ErrorCount;
                            if (App.AuCn === true) {
                                if (GoodsList[(index + 1)] === false) {
                                    App.AuCn = false;
                                    layer.open({
                                        title: '温馨提示',
                                        icon: 1,
                                        content: '商品批量上架指令执行完成，本次成功上架了' + App.SuccessCount + '个商品，失败了' + App.ErrorCount + '个商品！',
                                        btn: ['打开商品列表', '关闭'],
                                        btn1: function () {
                                            open('admin.goods.list.php');
                                        }
                                    });
                                } else {
                                    App.ProductsSale(GoodsList[(index + 1)]['data'], GoodsList[(index + 1)]['key'], 2, GoodsList, (index + 1));
                                }
                                return false;
                            }

                            if (GoodsList[(index + 1)] === false) {
                                App.AuCn = false;
                                layer.open({
                                    title: '温馨提示',
                                    icon: 1,
                                    content: '商品批量上架指令执行完成，本次成功上架了' + index + '个商品！',
                                    btn: ['打开商品列表', '关闭'],
                                    btn1: function () {
                                        open('admin.goods.list.php');
                                    }
                                });
                                return false;
                            }

                            layer.open({
                                title: '温馨提示',
                                icon: 2,
                                content: (!res.msg ? '异常' : res.msg) + '<br>问题商品：' + item.name,
                                btn: ['跳过此商品，继续执行', '本次上架结束前,默认继续执行', '关闭'],
                                btn1: function () {
                                    App.ProductDetails(GoodsList[(index + 1)]['data'], GoodsList[(index + 1)]['key'], 2, GoodsList, (index + 1));
                                },
                                btn2: function () {
                                    App.AuCn = true;
                                    App.ProductDetails(GoodsList[(index + 1)]['data'], GoodsList[(index + 1)]['key'], 2, GoodsList, (index + 1));
                                }
                            });
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            } else {
                let html = ``;
                for (const index in this.ClassData) {
                    let item = this.ClassData[index];
                    html += `<label class="mdui-list-item mdui-ripple">
                            <div class="mdui-list-item-avatar mdui-color-white"><img src="` + item.image + `"/></div>
                            <div onclick="App.SelectCategories(` + item.cid + `)" class="mdui-list-item-content">` + item.name + `</div>
                            <label class="mdui-radio">
                                <input type="radio" value="` + item.cid + `" name="cid"
                                ` + (App.cid == item.cid ? 'checked="checked" ' : '') + `
                                />
                                <i class="mdui-radio-icon"></i>
                                CID:` + item.cid + `
                            </label>
                        </label>`;
                }

                let content = `<div class="mdui-list">
` + html + `
                    </div>`;

                if (data.content === undefined) {
                    data.content = '';
                }

                mdui.dialog({
                    title: '添加商品：' + data.name,
                    content: data.content + '请选择要添加商品的分类：<br>' + content,
                    modal: true,
                    history: false,
                    buttons: [{
                        text: '关闭',
                    }, {
                        text: '添加商品', onClick: function () {

                            if (App.cid <= 0) {
                                mdui.dialog({
                                    title: '警告', content: '请先选择需要添加商品的分类！', modal: true, history: false, buttons: [{
                                        text: '关闭',
                                    }]
                                });
                                return;
                            }

                            data.cid = App.cid;

                            let is = layer.msg('添加中，请稍后...', {icon: 16, time: 9999999});
                            $.ajax({
                                type: "POST",
                                url: './main.php?act=AddProductsQuickly',
                                data: data,
                                dataType: "json",
                                success: function (res) {
                                    layer.close(is);
                                    if (res.code == 1) {
                                        layer.alert((!res.msg ? '异常' : res.msg), {
                                            icon: 1
                                        });
                                    } else {
                                        layer.alert((!res.msg ? '异常' : res.msg), {
                                            icon: 2
                                        });
                                    }
                                },
                                error: function () {
                                    layer.msg('服务器异常！');
                                }
                            });
                        }
                    }],
                    onOpen: function () {
                        mdui.mutation();
                    }
                });
            }
        }, SupplyList(id, page = 1, Supply = []) {
            //获取供货商的商品列表
            let is = layer.msg('正在获取可对接商品列表，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=SupplyList', data: {
                    id: id, page: page,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.SupplyDataList = res.data;
                        App.SuppId = id;
                        App.Supply = Supply;
                    } else {
                        App.SuppId = -1;
                        App.Supply = [];
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                    App.$nextTick(function () {
                        mdui.updateTables();
                    });
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, ContactCustomerService(data) {
            let content = ``;
            if (data.qq != -1) {
                content += '联系QQ：' + data.qq + '<br>';
            }
            if (data.email != -1) {
                content += '通知邮箱：' + data.email;
            }
            if (data.CustomerServiceKey == -1) {
                mdui.dialog({
                    title: '联系供货商',
                    content: (content == '' ? '当前无可用联系方式！' : content),
                    modal: true,
                    history: false,
                    buttons: [{
                        text: '关闭',
                    }]
                });
            } else {
                mdui.dialog({
                    title: '联系供货商',
                    content: (content == '' ? '点击下方在线咨询！' : content),
                    modal: true,
                    history: false,
                    buttons: [{
                        text: '关闭',
                    }, {
                        text: '在线咨询', onClick: function () {
                            open('https://yzf.qq.com/xv/web/static/chat/index.html?sign=' + data.CustomerServiceKey);
                        }
                    }]
                });
            }
        }, ServerDataGet() {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST",
                url: './main.php?act=DockingDataServer',
                data: {},
                dataType: "json",
                success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.ServerData = res.data;
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                },
                error: function () {
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
                        if (App.cid === -1 && App.ClassData[0] !== undefined) {
                            App.cid = App.ClassData[0].cid;
                        }
                        if (type === 2) {
                            layer.msg('分类列表更新成功，共' + (res.data.length) + '条分类数据!', {icon: 1});
                        }
                    } else {
                        layer.alert((!res.msg ? '异常' : res.msg), {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        },
    }
}).mount('#App');

App.ClassDataGet();
App.ServerDataGet();
