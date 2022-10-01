const App = Vue.createApp({
    data() {
        return {
            Data: [], type: -1, money: 100, //成本
            profit: 100, //利润比例
            ColorArr: [],
        }
    }, methods: {
        help() {
            const content = `
<div class="mdui-table-fluid">
  <table class="mdui-table mdui-table-hoverable">
    <thead>
      <tr>
        <th>功能</th>
        <th>说明</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="white-space: nowrap;color:red">重置等级</td>
        <td>点击 <a title="重置等级" href="javascript:App.Reset()" class="badge badge-success ml-1"><i class="layui-icon layui-icon-component"></i></a> 图标即可，内置等级有8个</td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">加价比例</td>
        <td>简单来说，就是此等级的用户的商品购买价是根据加价比例和商品成本来进行计算的，具体可在上方的两个商品参数配置项处调整实时查看价格！</td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">兑换倍数</td>
        <td>同上，积分兑换点数不受利润比例参数影响，填写多少，就会将商品成本×多少倍，得出最终兑换价格！</td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">绝对利润</td>
        <td>本程序用户系统拥有无限级分销体系，可以一层层的向上分成，绝对利润比例指的是，自己可以获得下级用户提成的比例，当比例为100%时，不会向上级分成，如果填60%，则会向上级分成40%的利润，以此类推！，当然，没上级的话，利润全部由该用户获得！</td>
      </tr>
      <tr>
        <td style="white-space: nowrap;color:red">分成阈值</td>
        <td>既然存在了无限级分销体系，那么当用户等级足够多时，可能会出现非常非常少的利润都会向上分成的情况，这种无意义分成是不需要的，这时可以配置此参数，如配置20%，则当向上分成的利润低于总利润的百分之20时，会停止分成！</td>
      </tr>
    </tbody>
  </table>
</div>
                `;
            mdui.dialog({
                title: '帮助说明', content: content, modal: true, history: false, buttons: [{
                    text: '我已了解',
                }]
            });
        }, Reset() {
            mdui.dialog({
                title: '等级重置', content: '是否需要重置等级，确认后现有的等级清空，重新生成8个内置的用户等级!', modal: true, history: false, buttons: [{
                    text: '取消',
                }, {
                    text: '确认重置', onClick: function () {
                        let is = layer.msg('重置中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: 'main.php?act=UserLevelReset',
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            App.UserLevelList();
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
                }]
            });
        }, colorById(i) {
            i = i.charCodeAt(0);
            const key = i;
            if (this.ColorArr['co_' + key] !== undefined) {
                return this.ColorArr['co_' + key];
            }
            if (i < 10) i = i * 92.5;
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
        }, Price(a, type) {
            let money = 0;
            this.money -= 0;
            this.profit -= 0;
            a -= 0;
            if (type === 1) {
                //售价
                money = this.money + ((this.money * (a / 100)) * (this.profit / 100));
            } else if (type === 2) {
                //积分
                money = this.money * a;
                money = money.toFixed(0);
            } else {
                money = this.money + ((this.money * (a / 100)) * (this.profit / 100));
                money -= this.money;
            }
            return this.NumRound(money);
        }, LevelDelete(mid, name) {
            layer.open({
                title: '危险操作', content: '是否要永久删除此等级?', icon: 3, btn: ['确定', '取消'], btn1: function () {
                    let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST", url: './main.php?act=LevelDelete', data: {
                            mid: mid, name: name
                        }, dataType: "json", success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.UserLevelList();
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
            });
        }, LevelStateSet(mid, type, name) {
            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=LevelStateSet', data: {
                    mid: mid, type: type, name: name
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.UserLevelList();
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, sort(mid, type) {
            $.ajax({
                type: 'POST', url: 'ajax.php?act=setlevelSort', data: {
                    mid: mid, type: type
                }, dataType: 'json', success: function () {
                    App.UserLevelList();
                }, error: function () {
                    layer.msg('服务器错误');
                }
            });
        }, UserLevelList() {
            let is = layer.msg('等级列表载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: './main.php?act=UserLevelList', dataType: "json", success: function (res) {
                    layer.close(is);
                    App.type = 1;
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.type = 1;
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
App.UserLevelList();