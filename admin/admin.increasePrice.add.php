<?php
// +----------------------------------------------------------------------
// | Project: xc
// +----------------------------------------------------------------------
// | Creation: 2022/8/23
// +----------------------------------------------------------------------
// | Filename: admin.increasePrice.add.php
// +----------------------------------------------------------------------
// | Explain: 新增加价规则
// +----------------------------------------------------------------------

use Medoo\DB\SQL;

$title = '商品加价规则';
include 'header.php';
if (!empty($_QET['id'])) {
    $DB = SQL::DB();
    $Price = $DB->get('profit_rule', '*', ['id' => (int)$_QET['id']]);
    if (!$Price) {
        show_msg('加价规则->' . $_QET['id'] . '不存在', '加价规则->' . $_QET['id'] . '不存在请检查是否访问有误?', 3);
    }
} else {
    $Price = [
        'rules' => -1,
    ];
}
$id = (empty($_QET['id']) ? -1 : $_QET['id']);

$Count = $DB->count('price', ['state' => 1]);

if ($Count < 2) {
    show_msg('温馨提示', '此功能需要绑定用户等级，请先去添加用户等级！最低2个', 2, './admin.level.add.php');
}
?>
<div class="row" id="App" aid="<?= $id ?>">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="admin.increasePrice.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <?= $title ?>
            </div>
            <div class="card-header" v-if="Rules.length>=1">
                <div class="layui-row">
                    <div class="layui-col-xs4">
                        商品成本
                        <input type="number" v-model="money" class="layui-input" placeholder="商品成本">
                    </div>
                    <div class="layui-col-xs4">
                        等级利润(%)
                        <input type="number" v-model="profit" class="layui-input" placeholder="等级利润%">
                    </div>
                    <div class="layui-col-xs4">
                        最终售价
                        <input type="text" style="color: red" disabled v-model="price" class="layui-input"
                               placeholder="最终商品售价">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">加价规则名称</label>
                        <input type="text" name="title" lay-verify="required" class="form-control"
                               v-model="name" placeholder="加价规则名称">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">加价规则内容 <span
                                    @click="addRun()"
                                    style="cursor: pointer"
                                    class="badge badge-success mr-1"><i class="layui-icon layui-icon-add-1"></i></span></label>
                        <div>
                            <blockquote v-for="(item,index) in Rules" @click="deleteRule(index)"
                                        style="box-shadow:1px 3px 3px #ccc;cursor: pointer"
                                        class="layui-elem-quote layui-quote-nm">「{{index+1}}」-
                                当商品成本大于等于<span style="color:red">{{ item.min
                                }}元</span>，并且小于等于<span style="color:red">{{item.max}}元</span>时「商品利润比降为<span
                                        style="color:red">{{item.profit}}%</span>」
                            </blockquote>
                        </div>
                    </div>
                    <button
                            @click="saveRun()"
                            class="btn btn-primary btn-block">
                        {{id==-1?'创建新规则':'保存规则['+id+']内容'}}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                功能说明 - 商品最终售价解释说明
            </div>
            <div class="card-body" style="white-space: nowrap;overflow-x: auto">
                <ul style="line-height: 2.2em;">
                    <li>此功能可以用来<span style="color: red">调整商品各个价位的最终售价</span>，如实现10元成本，卖12元，100元成本，卖110元，1000元成本，卖1050元！
                    </li>
                    <li>此功能的优先级<span style="color: red">小于</span>商品独立配置，<span style="color:red;font-size: 120%">如果商品独立配置内调整了售价的利润比，让商品独立配置内的利润比不为100，则此加价规则失效！</span>
                    </li>
                    <li style="color: #c823b1">当商品成本没有成功匹配到对应的价格区间时，会按照默认的100%利润比例来进行计算，建议最后一个价格区间的最大值设置大一点，如99999等！
                    </li>
                    <li>规则创建成功后，可以前往<span style="color: red">等级编辑</span>处进行规则绑定，每个等级都可以配置独立的加价规则，实现丰富多彩的密价功能！</li>
                    <li>商品售价公式[利润比作用]：<span
                                style="color: red">商品成本 + ( ( 商品成本 x 等级利润百分比) x 利润比 ) = 商品最终售价</span>「实现等级之间的售价差异」
                    </li>
                    <li>白话解释：利润比例越低，则在用户身上可以获得的利润越低，如果利润比为0，则售价=商品成本</li>
                    <li>等级利润和利润比的关系：用户等级控制商品利润，利润比控制商品利润的比例，如原来的利润为10元，利润比配置为50后，最终获得的利润只剩下5元，以此类推！</li>
                </ul>
            </div>
        </div>
    </div>
    <?php include 'bottom.php'; ?>
    <script src="../assets/js/vue3.js"></script>
    <script>
        const Rules = '<?=$Price['rules'];?>';
        const App = Vue.createApp({
            data() {
                return {
                    name: '<?= $Price['name'] ?>', //规则名称
                    Rules: (Rules === '-1' ? [] : JSON.parse(Rules)),
                    id: $("#App").attr('aid'),
                    profit: '',
                    money: 100,
                    price: '',
                }
            }, watch: {
                profit() {
                    this.PriceCalculation();
                },
                money() {
                    this.PriceCalculation();
                },
                Rules: {
                    handler() {
                        this.PriceCalculation();
                    },
                    immediate: true
                }
            }
            , methods: {
                //计算模拟售价
                PriceCalculation() {
                    if (Rules.length === 0 || this.profit <= 0 || this.money <= 0) {
                        this.price = '不满足计算条件';
                        return;
                    }
                    this.money -= 0;
                    this.profit -= 0;
                    let profit = 100; //默认加价
                    for (const key in this.Rules) {
                        let data = this.Rules[key];
                        if (this.money >= (data.min - 0) && this.money <= (data.max - 0)) {
                            profit = data.profit;
                        }
                    }
                    profit -= 0;
                    layer.msg('通过加价规则计算后<br>等级利润保留了' + profit + '%');
                    this.price = this.money + (this.money * (this.profit / 100) * (profit / 100)) + '元';
                },
                saveRun() {
                    if (this.name == '' || this.Rules.length == 0) {
                        layer.msg('请将内容填写完整，并且最少创建一个规则参数', {
                            icon: 2,
                        });
                        return false;
                    }
                    let _this = this;
                    layer.open({
                        title: '操作确认',
                        content: '确认保存内容吗？',
                        btn: ['确认', '取消'],
                        icon: 3,
                        yes: function () {
                            layer.load(2, {
                                time: 999999
                            });
                            $.ajax({
                                type: "post",
                                url: './main.php?act=SaveFareIncreaseRule',
                                data: {
                                    id: _this.id,
                                    name: _this.name,
                                    rules: _this.Rules,
                                },
                                dataType: "json",
                                success: function (data) {
                                    layer.closeAll();
                                    if (data.code == 1) {
                                        layer.alert(data.msg, {
                                            icon: 1,
                                            btn: ['返回列表', '继续'],
                                            btn1: function () {
                                                window.location.href = 'admin.increasePrice.list.php';
                                            }, btn2: function () {
                                                window.location.reload();
                                            }
                                        });
                                    } else layer.msg(data.msg)
                                }
                            })
                        }
                    });
                },
                deleteRule(index) {
                    let _this = this;
                    layer.alert('确定要删除此规则吗，删除会连带清空后续规则，需要重新创建！', {
                        icon: 3,
                        btn: ['确认删除', '取消'],
                        btn1: function () {
                            let arr = [];
                            for (let rKey in _this.Rules) {
                                rKey -= 0;
                                if (rKey >= index) {
                                    continue;
                                }
                                arr.push(_this.Rules[rKey]);
                            }
                            _this.Rules = arr;
                            layer.closeAll();
                        }
                    });
                },
                addRun() {
                    let data = false; //上一级别的参数
                    let _this = this;

                    let input = `<div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">成本大于等于多少时调整利润比</label>
                        <input type="text" name="min" lay-verify="required" class="form-control"
                                placeholder="请填写成本金额">
                    </div>`;
                    if (this.Rules.length !== 0) {
                        data = this.Rules[this.Rules.length - 1];
                        input = `<div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">成本大于等于多少时调整利润比</label>
                        <input id="Min" type="number" value="` + (data.max - 0 + 0.0001) + `" disabled name="min" lay-verify="required" class="form-control disabled"
                                placeholder="请填写成本金额">
                    </div>`;
                    } else if (this.Rules.length === 0) {
                        input = `<div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">成本大于等于多少时调整利润比</label>
                        <input id="Min" type="number" value="0.00" disabled name="min" lay-verify="required" class="form-control disabled"
                                placeholder="请填写成本金额">
                    </div>`;
                    }
                    let content = `
                <div class="form-horizontal layui-form">
                    ` + input + `
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">成本小于等于多少时调整利润比</label>
                        <input id="Max" type="number" name="max" lay-verify="required" class="form-control"
                                placeholder="请填写成本金额">
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">利润比[0-100]</label>
                        <input id="profit" type="number" name="profit" lay-verify="required" class="form-control"
                                placeholder="请填写利润比">
                    </div>
                </div>
                    `;
                    layer.open({
                        title: '新增成本区间利润规则',
                        content: content,
                        area: ['300px', '400px'],
                        btn: ['确认添加', '取消'],
                        btn1: function () {
                            let max = $("#Max").val() - 0; //最大值
                            let min = $("#Min").val() - 0; //最小值
                            let profit = $("#profit").val() - 0;
                            console.log(max, min);

                            if (max <= min) {
                                alert('成本最大值(' + max + ')不能小于或等于最小值(' + min + ')！');
                                return false;
                            }

                            if (profit < 0 || profit > 100 || profit === '') {
                                $("#profit").val(100);
                                alert('利润比不能小于0或者大于100！');
                                return false;
                            }
                            if (data && profit > data.profit) {
                                alert('利润比必须小于' + data.profit);
                                if (data.profit <= 10) {
                                    $("#profit").val(data.profit - 0.1);
                                } else {
                                    $("#profit").val(data.profit - 1);
                                }
                                return false;
                            }
                            //开始写入数据
                            _this.Rules.push({
                                max: max,
                                min: min,
                                profit: profit
                            });
                            layer.msg('添加成功！', {icon: 1, time: 500});
                        }
                    });
                }
            }
        }).mount('#App');

    </script>