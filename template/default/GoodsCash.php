<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <title>卡密兑换</title>
    <link href="./assets/layui/css/layui.css" rel="stylesheet"/>
</head>
<style>
    .xiaoxuan-tip img {
        max-width: 100%;
    }
</style>
<body style="background-image: url(<?= background::Bing_random() ?>)">
<div class="layui-fluid" id="appshop">
    <div class="layui-row">
        <?php if (!empty($conf['notice_top'])) { ?>
            <div style="margin-top: 1em" class="layui-col-sm10 layui-col-sm-offset1 layui-col-md8 layui-col-md-offset2">
                <div class="layui-card">
                    <div class="layui-card-header">
                        公告信息
                    </div>
                    <div class="layui-card-body">
                        <?= $conf['notice_top'] ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div style="margin-top:1em" class="layui-col-sm10 layui-col-sm-offset1 layui-col-md8 layui-col-md-offset2">
            <div class="layui-card">
                <div class="layui-card-header">
                    卡密激活
                </div>
                <div class="layui-card-body">
                    <div class="layui-form layui-form-pane">
                        <div class="layui-form-item">
                            <label class="layui-form-label">卡密内容</label>
                            <div class="layui-input-block">
                                <input type="text" v-model="Token" required lay-verify="required"
                                       placeholder="请输入卡密，自动获取商品！"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div v-if="Type">
                            <div style="padding: 1em;width: 100%;text-align: center">
                                <img :src="Goods.image[0]"
                                     style="width: 6em;height: 6em;border-radius: 0.5em;box-shadow: 3px 3px 16px #ccc">
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">商品名称</label>
                                <div class="layui-input-block">
                                    <input type="text" disabled
                                           :value="Goods.name"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">剩余库存</label>
                                <div class="layui-input-block">
                                    <input type="text" disabled
                                           :value="Goods.quota + ' 份'"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div v-for="(item, index) of Goods.input" :key="index">
                                <div class="layui-form-item" v-if="item.state == 1">
                                    <label class="layui-form-label">{{ item.Data }}</label>
                                    <div class="layui-input-block">
                                        <input type="text" v-model="form[index]"
                                               class="layui-input"
                                               :placeholder="'请将' + item.Data + '填写完整!'">
                                    </div>
                                </div>

                                <div class="layui-form-item" v-if="item.state == 2">
                                    <label class="layui-form-label">{{ item.Data[0] }}</label>
                                    <div class="layui-input-block">
                                        <input type="text" v-model="form[index]" class="layui-input"
                                               @blur="extend(index, item.Data[1].way, item.Data[1].url,2)"
                                               :placeholder="item.Data[1].placeholder"/>
                                        <div class="layui-btn layui-btn-xs" style="background-color: #71bcff;"
                                             @click="extend(index, item.Data[1].way, item.Data[1].url)">{{
                                            item.Data[1].name}}
                                        </div>
                                    </div>
                                </div>

                                <div class="layui-form-item" v-if="item.state == 3">
                                    <label class="layui-form-label">{{ item.Data }}</label>
                                    <div class="layui-input-block">
                                        <input type="text" v-model="form[index]" class="layui-input"
                                               :placeholder="'请将' + item.Data + '填写完整!'">
                                    </div>
                                </div>

                                <div class="layui-form-item" v-if="item.state == 4">
                                    <label class="layui-form-label">{{ item.Data[0] }}</label>
                                    <div class="layui-input-block">
                                        <select style="width: 100%;height: 38px;border: 1px solid #ccc;color: #333"
                                                lay-ignore v-model="form[index]">
                                            <option v-for="o in item.Data[1]" :value="o">{{ o }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input-group" v-if="item.state == 5">
                                    <div v-if="Object.values(Combination).length === 0"
                                         style="color:red">
                                        商品下单参数未配置完善，请联系客服处理！
                                    </div>
                                    <div v-if="Object.values(Combination).length >= 1" class="layui-card-body"
                                         style="padding:0;">
                                        <fieldset v-for="(item, index) in SkuBtn"
                                                  class="layui-elem-field layui-field-title site-title">
                                            <legend>{{ index }}</legend>
                                            <div style="margin-top:0.5em">
                                                <div style="display:inline-block;" v-for="(ts, is) in item" :key="is">
                                                    <button
                                                            type="button"
                                                            class="layui-btn layui-btn-sm layui-btn-normal"
                                                            v-if="FormSp[index] == is && ts.type == 1"
                                                            @click="BtnClick(index, is, ts.type)"
                                                            style="margin: 0.3em;border-radius: 0.5em;border: none;box-shadow:1px 1px 18px #ccc;">
                                                        {{ is }}
                                                    </button>
                                                    <button
                                                            type="button"
                                                            class="layui-btn layui-btn-sm layui-btn-primary"
                                                            v-else-if="FormSp[index] != is && ts.type == 1"
                                                            @click="BtnClick(index, is, ts.type)"
                                                            style="margin: 0.3em;border-radius: 0.5em;border: none;box-shadow:1px 1px 8px #ccc;">
                                                        {{ is }}
                                                    </button>
                                                    <button
                                                            type="button"
                                                            class="layui-btn layui-btn-sm  layui-btn-disabled"
                                                            v-else
                                                            @click="BtnClick(index, is, ts.type)"
                                                            style="margin: 0.3em;border-radius: 0.5em;border: none">
                                                        {{ is }}
                                                    </button>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                            <button @click="Submi" class="layui-btn layui-btn-danger layui-btn-fluid">兑换商品</button>
                            <div v-if="Goods.docs!=''" v-html="Goods.docs" class="xiaoxuan-tip editor"
                                 style="background: linear-gradient(to right, rgb(255, 153, 102), rgb(255, 94, 98)); font-weight: bold; color: white;margin: 1em 0 1em;padding:1em">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($conf['notice_bottom'])) { ?>
            <div style="margin-top: 1em;margin-bottom: 2em"
                 class="layui-col-sm10 layui-col-sm-offset1 layui-col-md8 layui-col-md-offset2">
                <div class="layui-card">
                    <div class="layui-card-body">
                        <?= $conf['notice_bottom'] ?>
                        <div><?= $conf['statistics'] ?></div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<script src="./assets/js/jquery-3.4.1.min.js"></script>
<script src="./assets/layui/layui.all.js"></script>
<script src="./assets/js/vue3.js"></script>
<script>
    layui.use(['layer']);
    const vm = Vue.createApp({
        data() {
            return {
                Token: '',
                pass: '',
                Goods: {
                    image: [],
                    input: [[]],
                    name: -1,
                    Seckill: -1,
                },

                Type: false,

                SkuType: -1,
                num: 1,
                Price: 0,
                form: [],
                js: ``,

                FormSp: {}, //商品规格下单参数
                SkuBtn: {}, //商品规格按钮,用于渲染按钮
                Combination: {}, //商品规格数据，用于匹配数据
                GoodsBack: -1, //备份参数,用于未选恢复
                freight: '', ///运费
                exceed: '',
                formApi: false,
            }
        },
        watch: {
            Token: function (value) {
                if (value.length == 36) {
                    this.Type = false;
                    this.TokenAjax();
                } else {
                    this.Type = false;
                }
            },
        },
        methods: {
            extend(index, type, url, state = 1) {
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
                $.ajax({
                    type: "POST",
                    url: url,
                    data: Data,
                    dataType: "json",
                    success: function (res) {
                        layer.closeAll();
                        if (res.code >= 0) {
                            _this.formApi = true;
                            if (type == 1) {
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
                                _this.js = res.js;
                            } else _this.js = ``;
                            if (res.msg != '' && res.msg != undefined) {
                                layer.alert(res.msg, {
                                    title: '提示信息',
                                    btn: ['确定'],
                                    yes: function () {
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
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            Submi() {
                let _this = this;
                layer.open({
                    title: '温馨提示',
                    icon: 3,
                    content: '是否要使用此兑换卡？',
                    btn: ['确定', '取消'],
                    btn1: function () {
                        let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=Ordersubmit&Token=' + _this.Token,
                            data: _this.SubmitData(),
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1, btn1: function () {
                                            layer.closeAll();
                                            open('<?=ROOT_DIR?>?mod=route&p=Order');
                                        }
                                    });
                                } else if (res.code == -3) {
                                    layer.alert(res.msg, {
                                        icon: 2, btn1: function () {
                                            layer.closeAll();
                                            open('<?=ROOT_DIR?>?mod=route&p=User');
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
                })
            },
            SubmitData() {
                let Input = this.Goods.input;
                let _this = this;
                let Data = {};
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
            },
            BtnClick(index, name, type) {
                let _this = this;
                if (type == 0) {
                    layer.msg('库存暂时不足,可联系客服补货~', {icon: 2});
                    return;
                }
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
                this.Goods = Object.assign(this.Goods, Data);
                this.Image = this.Goods.image[0];
                this.num = JSON.parse(JSON.stringify(this.Goods)).min;
            },
            Btn() {
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
            },
            BtnType(index, name) {
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
            },
            AjaxGoodsMonitoring(gid) {
                $.ajax({
                    type: "POST",
                    url: "./main.php?act=GoodsMonitoring",
                    data: {
                        gid: gid
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.code !== 1) {
                            layer.alert(data.msg, {
                                icon: 3,
                                end: function () {
                                    location.reload();
                                },
                            });
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            TokenAjax() {
                let _this = this;
                let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: './main.php?act=GoodsData',
                    data: {
                        Token: _this.Token,
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(is);
                        if (data.code == 1) {
                            _this.Type = true;
                            _this.Goods = data.data;
                            _this.AjaxGoodsMonitoring(_this.Goods.gid);
                            _this.num = 1;
                            _this.FormSp = {};
                            _this.GoodsBack = JSON.parse(JSON.stringify(data.data)); //深层绑定

                            if (Object.values(data.data.input).length >= 1) {
                                for (const key in data.data.input) {
                                    if (data.data.input.hasOwnProperty.call(data.data.input, key)) {
                                        const value = data.data.input[key];
                                        _this.form[key] = '';
                                        if (value['state'] == 4) {
                                            _this.form[key] = value.Data[1][0]
                                        }
                                    }
                                }
                            } else _this.form = [];

                            if (data.data.input[0].state == 5) {
                                _this.Combination = data.data.input[0]['data']['Parameter'];
                                let SPU = data.data.input[0]['SPU'];
                                _this.SkuType = 1;
                                _this.Btn();
                            } else _this.SkuType = -1;

                            if (data.data.alert != "") {
                                layer.alert(data.data.alert, {
                                    title: ["商品须知", "font-size:18px;"],
                                    btn: "知道了",
                                    closeBtn: 0,
                                });
                            }

                        } else {
                            layer.alert(data.msg, {
                                icon: 2
                            });
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            }
        },
    }).mount("#appshop");
</script>
</body>
</html>
