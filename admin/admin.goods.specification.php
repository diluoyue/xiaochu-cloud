<?php
$protect_admin = true;
include '../includes/fun.global.php';
if (empty($_QET['gid'])) {
    $Gid = -1;
} else {
    $Gid = (int)$_QET['gid'];
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>规格生成</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="../assets/layui/css/layui.css" media="all">
    <link href="../assets/mdui/css/mdui.min.css" rel="stylesheet" type="text/css"/>
</head>

<body style="background-color:#f8f8f8">
<div class="layui-fluid mdui-p-a-1" id="App" data="<?= $Gid ?>">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-xs6">
            <div class="layui-form layui-form-pane">
                <div class="layui-form-item layui-form-text mdui-m-b-0">
                    <label class="layui-form-label">商品规格名参数[点击内容复制]</label>
                    <div class="layui-input-block">
                        <textarea @click="copyToClip(SPUJSON,'商品规格名参数复制成功,快去粘贴到对应参数内吧')" placeholder="请在下方生成"
                                  class="layui-textarea">{{ SPUJSON }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-xs6">
            <div class="layui-form layui-form-pane">
                <div class="layui-form-item layui-form-text mdui-m-b-0">
                    <label class="layui-form-label">商品规格值参数[点击内容复制]</label>
                    <div class="layui-input-block">
                        <textarea @click="copyToClip(SKUJSON,'商品规格值参数复制成功,快去粘贴到对应参数内吧')" placeholder="请在下方生成"
                                  class="layui-textarea">{{ SKUJSON }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <span @click="SPUADD" class="layui-badge"
                          style="background-color:#29cfa8;margin-left: 0.5em;">添加规格名</span>

                    <span @click="Tips" class="layui-badge"
                          style="background-color:#ff8a00;margin-left: 0.5em;">说明</span>

                    <span @click="SPUImport" class="layui-badge layui-bg-blue layuiadmin-badge mdui-m-l-1"
                          title="规格名称参数">
                            导入
                        </span>
                    <span @click="HostSpecification()" class="layui-badge layuiadmin-badge mdui-m-l-1"
                          style="background-color:#ab80ff;"
                          title="导入主机规格配置">
                            主机规格
                        </span>
                </div>
                <div class="layui-card-body">
                    <ul class="layui-timeline">
                        <li class="layui-timeline-item" v-for="(item,index) in SPU">
                            <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                            <div class="layui-timeline-content layui-text">
                                <h3 class="layui-timeline-title">{{ item }}</h3>
                                <fieldset class="layui-elem-field layui-field-title">
                                    <legend @click="Add(item)">点击添加规格值</legend>
                                    <div class="layui-field-box" v-if="SKU[item]!=undefined">
                                            <span style="margin:0.25em" class="layui-badge layui-bg-black"
                                                  v-for="(items,indexs) in SKU[item]"
                                                  @click="Modification(item,items,indexs,index,SKUCOST[index][items])">
                                                {{ items }} / {{ SKUCOST[index][items] }}元
                                            </span>
                                    </div>
                                </fieldset>
                            </div>
                        </li>
                    </ul>
                    <div class="layui-form layui-form-pane mdui-m-t-1">
                        <div class="layui-form-item">
                            <label class="layui-form-label">成本底价</label>
                            <div class="layui-input-inline">
                                <input type="number" v-model="money" placeholder="默认为0" autocomplete="off"
                                       class="layui-input">
                            </div>
                            <div class="layui-form-mid layui-word-aux">
                                每种规格组合的成本会根据成本底价生成,而商品售价是根据成本计算而来,所以规格参数只需要配置成本即可,最终售价会自动计算!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12" v-if="Object.keys(Combination).length>=1">
            <div class="layui-card">
                <div class="layui-card-header">
                    商品规格生成结果 - {{ Object.keys(Combination).length }}种
                    <span @click="VolumeSet(false)" class="layui-badge"
                          style="background-color:#ec4949;margin-left: 0.5em;">批量设置</span>

                    <span @click="Btn" class="layui-badge layuiadmin-badge mdui-m-l-1"
                          style="background-color:forestgreen;color: #fff !important;">
                            按钮模拟
                        </span>
                </div>
                <div class="layui-card-body" style="width:100%;overflow-x:auto;padding:0em;max-height:54vh;">
                    <table class="layui-table" lay-size="sm">
                        <thead>
                        <tr>
                            <th>操作</th>
                            <th>SKU</th>
                            <th>商品成本</th>
                            <th>每份数量</th>
                            <th>剩余库存</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item,index) in Combination" @click="VolumeSet(index)">
                            <td><a style="color:red;cursor:pointer;">设置</a></td>
                            <td>{{ index }}</td>
                            <td>{{ (item.money == '' ? '默认' : item.money)}}</td>
                            <td>{{ (item.quantity == '' ? '默认' : item.quantity) }}</td>
                            <td>{{ (item.quota == '' ? '默认' : item.quota) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12" v-if="Object.keys(SkuBtn).length>=1">
            <div class="layui-card">
                <div class="layui-card-header">
                    按钮点击模拟
                </div>
                <div class="layui-card-body">
                    <fieldset class="layui-elem-field layui-field-title" v-for="(item,index) in SkuBtn">
                        <legend>{{ index }}</legend>
                        <div class="layui-field-box">
                            <div class="layui-btn-container">
                                    <span v-for="(ts,is) in item">
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-warm"
                                                v-if="SpuBtn[index]==is&&ts.type==1"
                                                @click="BtnClick(index,is,ts.type)">{{ is }}</button>

                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary"
                                                v-else-if="SpuBtn[index]!=is&&ts.type==1"
                                                @click="BtnClick(index,is,ts.type)">{{ is }}</button>

                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-disabled" v-else>{{ is }}</button>
                                    </span>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/jquery-3.4.1.min.js"></script>
<script src="../assets/layui/layui.all.js"></script>
<script src="../assets/mdui/js/mdui.min.js"></script>
<script src="../assets/js/vue3.js"></script>
<script>
    const vm = Vue.createApp({
        data() {
            return {
                Separator: "`",
                SPUJSON: {},
                SKUJSON: {},
                SPU: [],
                SKU: {},
                Combination: {},
                SkuBtn: {},
                SpuBtn: [],
                Max: 4,
                money: 0,
                SKUCOST: [],
            }
        },
        watch: {
            money: {
                handler(newVal) {
                    if (newVal < 0 || this.isNumber(newVal) === false) this.money = 0;
                    if (Object.keys(this.Combination).length >= 1) {
                        this.SPUVRAY();
                    }
                },
                deep: false,
                immediate: false,
            }
        },
        methods: {
            HostSpecification() {
                let _this = this;
                layer.open({
                    title: '温馨提示',
                    content: '是否需要导入主机空间类商品规格配置？<br>仅适用发货方式为：宝塔主机空间发货<br>可根据服务器节点配置调整参数，价格等！',
                    icon: 3,
                    btn: ['确定导入', '取消'],
                    btn1: function () {
                        value = JSON.parse('{"并发总数":["200","300","400","500"],"上行流量":["512KB","1024KB","2048KB"],"上传限制":["9MB","12MB","18MB","24MB"],"域名绑定":["1个","3个","6个","9个"]}');
                        value2 = JSON.parse('{"200`512KB`9MB`1个":{"image":"","alert":"","money":"","quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`9MB`3个":{"image":"","alert":"","money":1,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`9MB`6个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`9MB`9个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`12MB`1个":{"image":"","alert":"","money":1,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`12MB`3个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`12MB`6个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`12MB`9个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`18MB`1个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`18MB`3个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`18MB`6个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`18MB`9个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`24MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`24MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`24MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`512KB`24MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`9MB`1个":{"image":"","alert":"","money":1,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`9MB`3个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`9MB`6个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`9MB`9个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`12MB`1个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`12MB`3个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`12MB`6个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`12MB`9个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`18MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`18MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`18MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`18MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`24MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`24MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`24MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"200`1024KB`24MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`9MB`1个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`9MB`3个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`9MB`6个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`9MB`9个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`12MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`12MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`12MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`12MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`18MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`18MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`18MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`18MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`24MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`24MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`24MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"200`2048KB`24MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`9MB`1个":{"image":"","alert":"","money":1,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`9MB`3个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`9MB`6个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`9MB`9个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`12MB`1个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`12MB`3个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`12MB`6个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`12MB`9个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`18MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`18MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`18MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`18MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`24MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`24MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`24MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`512KB`24MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`9MB`1个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`9MB`3个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`9MB`6个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`9MB`9个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`12MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`12MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`12MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`12MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`18MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`18MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`18MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`18MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`24MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`24MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`24MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"300`1024KB`24MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`9MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`9MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`9MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`9MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`12MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`12MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`12MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`12MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`18MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`18MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`18MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`18MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`24MB`1个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`24MB`3个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`24MB`6个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"300`2048KB`24MB`9个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`9MB`1个":{"image":"","alert":"","money":2,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`9MB`3个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`9MB`6个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`9MB`9个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`12MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`12MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`12MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`12MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`18MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`18MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`18MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`18MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`24MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`24MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`24MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`512KB`24MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`9MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`9MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`9MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`9MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`12MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`12MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`12MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`12MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`18MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`18MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`18MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`18MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`24MB`1个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`24MB`3个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`24MB`6个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"400`1024KB`24MB`9个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`9MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`9MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`9MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`9MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`12MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`12MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`12MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`12MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`18MB`1个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`18MB`3个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`18MB`6个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`18MB`9个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`24MB`1个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`24MB`3个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`24MB`6个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"400`2048KB`24MB`9个":{"image":"","alert":"","money":10,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`9MB`1个":{"image":"","alert":"","money":3,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`9MB`3个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`9MB`6个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`9MB`9个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`12MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`12MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`12MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`12MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`18MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`18MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`18MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`18MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`24MB`1个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`24MB`3个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`24MB`6个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`512KB`24MB`9个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`9MB`1个":{"image":"","alert":"","money":4,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`9MB`3个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`9MB`6个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`9MB`9个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`12MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`12MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`12MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`12MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`18MB`1个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`18MB`3个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`18MB`6个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`18MB`9个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`24MB`1个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`24MB`3个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`24MB`6个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"500`1024KB`24MB`9个":{"image":"","alert":"","money":10,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`9MB`1个":{"image":"","alert":"","money":5,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`9MB`3个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`9MB`6个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`9MB`9个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`12MB`1个":{"image":"","alert":"","money":6,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`12MB`3个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`12MB`6个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`12MB`9个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`18MB`1个":{"image":"","alert":"","money":7,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`18MB`3个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`18MB`6个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`18MB`9个":{"image":"","alert":"","money":10,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`24MB`1个":{"image":"","alert":"","money":8,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`24MB`3个":{"image":"","alert":"","money":9,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`24MB`6个":{"image":"","alert":"","money":10,"quantity":"","quota":"","min":"","max":"","units":""},"500`2048KB`24MB`9个":{"image":"","alert":"","money":11,"quantity":"","quota":"","min":"","max":"","units":""}}');
                        let SPU = [];
                        let i = 0;
                        for (const spuKey in value) {
                            SPU[i] = spuKey;
                            ++i;
                        }
                        _this.SPU = SPU;
                        _this.SKU = value;
                        _this.SKUCOSTSET();
                        _this.Combination = value2;
                        _this.SKUJSON = JSON.stringify(_this.Combination);
                        _this.SPUJSON = JSON.stringify(_this.SKU);
                        layer.alert('导入成功，可根据需要自己调整规格参数，价格等，请务必按照当前导入的参数名称来进行调整！', {
                            icon: 1
                        });
                    }
                });
            },
            SKUCOSTSET() {
                let data = this.SKU;
                let i = 0;
                let _this = this;
                for (const key in data) {
                    if (_this.SKUCOST[i] == undefined) {
                        _this.SKUCOST[i] = {};
                    }
                    for (const kis in data[key]) {
                        let Name = data[key][kis];
                        _this.SKUCOST[i][Name] = (_this.isNumber(_this.SKUCOST[i][Name]) === true ? _this.SKUCOST[i][Name] : 0);
                    }
                    ++i;
                }
            },
            price(data) {
                let money = 0;
                let SKUCOST = this.SKUCOST;
                for (const key in data) {
                    if (SKUCOST[key][data[key]] > 0) {
                        money += (SKUCOST[key][data[key]] - 0);
                    }
                }
                let moneys = ((this.money - 0) + money);
                return (moneys === 0 ? '' : moneys);
            },
            Tips() {
                mdui.dialog({
                    title: '提示',
                    content: '1、您可以点击旁边的添加规格名称按钮生成自己需要的参数<br>' +
                        '2、生成规格名称后可以点击添加规格值文字添加对应的参数<br>' +
                        '3、若需要修改或删除规格参数可以点击规格参数名称调整<br>' +
                        '4、可以点击最右边的导入参数按钮根据提示导入以前生成过的参数！<br>' +
                        '5、生成后可以点击下方的生成结果配置参数，也可以点击批量设置一次设置全部<br>' +
                        '6、若想要看到你配置的规格生成的选择按钮可以点击按钮模拟<br>' +
                        '7、若生成的参数太多，如几千个生成组合，可以点击子参数绑定部件价格，系统会根据部件价格自动组合计算最终成本！<br>' +
                        '8、当然，部件成本价格配置时会获取一次成本底价，若未配置底价那么按照0来计算，如规格1下的参数1成本为1元，规格2下的参数1成本为2元，底价默认为0，则此组合生成的最终成本价为3元，以此类推！<br>',
                    modal: true,
                    history: false,
                    buttons: [{
                        text: '关闭',
                    }]
                });
            },
            initialize() {
                this.SKUJSON = {};
                this.SPUJSON = {};
                this.Combination = {};
            },
            SPUImport() {
                let _this = this;
                layer.prompt({
                    formType: 2,
                    value: '',
                    title: '请输入[商品规格名参数]',
                    btn: ['下一步', '取消'],
                    maxlength: 999999999999999,
                }, function (value, index) {
                    layer.prompt({
                        formType: 2,
                        value: '',
                        title: '请输入[商品规格值参数]',
                        btn: ['生成', '取消'],
                        maxlength: 999999999999999,
                    }, function (value2, index2) {
                        value = JSON.parse(value);
                        value2 = JSON.parse(value2);
                        let SPU = [];
                        let i = 0;
                        for (const spuKey in value) {
                            SPU[i] = spuKey;
                            ++i;
                        }
                        _this.SPU = SPU;
                        _this.SKU = value;
                        _this.SKUCOSTSET();
                        _this.Combination = value2;
                        _this.SKUJSON = JSON.stringify(_this.Combination);
                        _this.SPUJSON = JSON.stringify(_this.SKU);
                        layer.alert('导入成功,如果导入格式有误,界面功能将无法正常使用!', {
                            icon: 1
                        });
                        layer.close(index2);
                    });
                    layer.close(index);
                });
            },
            Rendering(gid) {
                let is = layer.msg('数据载入中...', {
                    icon: 16,
                    time: 9999999
                });
                let _this = this;
                $.ajax({
                    type: "POST",
                    url: './main.php?act=GoodsSpu',
                    data: {
                        gid: gid,
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(is);
                        if (data.code == 1 && data.SPU != null) {
                            layer.open({
                                title: '温馨提示',
                                content: '是否需要载入商品[' + data.name + ']的规格配置？',
                                btn: ['确定', '取消'],
                                icon: 3,
                                btn1: function () {
                                    layer.closeAll();
                                    let SPU = [];
                                    let i = 0;
                                    for (const spuKey in data.SPU) {
                                        SPU[i] = spuKey;
                                        ++i;
                                    }
                                    if (data.SKU == null) {
                                        data.SKU = {};
                                        layer.alert('当前商品的SKU参数异常，请随意改动规则内容即可重新渲染！', {
                                            icon: 2
                                        });
                                    }
                                    _this.SPU = SPU;
                                    _this.SKU = data.SPU;
                                    _this.SKUCOSTSET();
                                    _this.Combination = data.SKU;
                                    _this.SKUJSON = JSON.stringify(_this.Combination);
                                    _this.SPUJSON = JSON.stringify(_this.SKU);
                                }
                            })
                        } else return false;
                    }
                });
            },
            BtnClick(index, name, type) {
                let _this = this;
                if (type == 0) return;
                if (_this.SpuBtn[index] == name) {
                    _this.SpuBtn[index] = '';
                } else _this.SpuBtn[index] = name;
            },
            Btn() {
                SkuBtn = {};
                let i = 0;
                let _this = this;
                for (const key in this.SKU) {
                    let Arr = this.SKU[key];
                    let Btn = {};
                    Arr.forEach(function (keys) {
                        let type = _this.BtnType(i, keys);
                        Btn[keys] = {
                            type: type
                        };
                        if (type == 0 && keys == _this.SpuBtn[key]) _this.SpuBtn[key] = '';
                    });
                    SkuBtn[key] = Btn;
                    ++i;
                }
                this.SkuBtn = SkuBtn;
            },
            BtnType(index, name) {
                let _this = this;
                let inventory = 0;
                for (const key in _this.Combination) {
                    if ((_this.Combination).hasOwnProperty.call(_this.Combination, key)) {
                        const value = _this.Combination[key];
                        let Arr = {};
                        if (key.indexOf(_this.Separator) != -1) {
                            Arr = key.split(_this.Separator);
                        } else Arr = [key];
                        if (Arr[index] == name && (value.inventory >= 1 || value.inventory == -1)) {
                            inventory = 1;
                        }
                    }
                }
                return inventory;
            },
            copyToClip(content, message) {
                var aux = document.createElement("input");
                aux.setAttribute("value", content);
                document.body.appendChild(aux);
                aux.select();
                document.execCommand("copy");
                document.body.removeChild(aux);
                if (message == null) {
                    layer.msg("复制成功", {
                        icon: 1
                    });
                } else {
                    layer.msg(message, {
                        icon: 1
                    });
                }
            },
            VolumeSet(index = false) {
                let _this = this;
                let Data = {
                    image: '',
                    alert: '',
                    money: '',
                    quantity: '',
                    quota: '',
                    min: '',
                    max: '',
                    units: '',
                };
                if (index != false) {
                    Data = _this.Combination[index];
                }
                let content = `
<div class="layui-form layui-form-pane" id="Form" style="padding: 1em;">
   <div class="layui-form-item">
    <label class="layui-form-label">商品主图</label>
    <div class="layui-input-inline">
     <input name="image" placeholder="请输入商品主图链接" value="` + Data.image + `" class="layui-input" />
    </div>
   </div>
   <div class="layui-form-item">
    <label class="layui-form-label">下单提示</label>
    <div class="layui-input-block">
     <input type="text" name="alert" value="` + Data.alert + `" placeholder="请输入下单提示信息,纯文字即可" autocomplete="off" class="layui-input" />
    </div>
   </div>
   <div class="layui-form-item">
    <label class="layui-form-label">商品成本</label>
    <div class="layui-input-block">
     <input type="number" name="money" placeholder="每份商品的进货价是多少？" value="` + Data.money + `" class="layui-input" />
    </div>
   </div>
   <div class="layui-form-item">
    <label class="layui-form-label">每份数量</label>
    <div class="layui-input-block">
     <input type="number" name="quantity" placeholder="每份商品的发货数量" value="` + Data.quantity + `" class="layui-input" />
    </div>
   </div>
   <div class="layui-form-item">
    <label class="layui-form-label">剩余库存</label>
    <div class="layui-input-inline">
     <input type="number" name="quota" placeholder="请输入剩余库存" value="` + Data.quota + `" class="layui-input" />
    </div>
    <div class="layui-form-mid layui-word-aux">
      商品总库存数量
    </div>
   </div>
   <div class="layui-form-item">
    <label class="layui-form-label">最低份数</label>
    <div class="layui-input-inline">
     <input type="number" name="min" placeholder="请输入最低购买份数" value="` + Data.min + `" class="layui-input" />
    </div>
    <div class="layui-form-mid layui-word-aux">
      商品最低购买份数！
    </div>
   </div>
   <div class="layui-form-item">
    <label class="layui-form-label">最多份数</label>
    <div class="layui-input-inline">
     <input type="number" name="max" placeholder="请输入商品最多购买份数" value="` + Data.max + `" class="layui-input" />
    </div>
    <div class="layui-form-mid layui-word-aux">
      商品最多购买份数！
    </div>
   </div>
   <div class="layui-form-item">
    <label class="layui-form-label">数量单位</label>
    <div class="layui-input-inline">
     <input type="text" name="units" placeholder="请填写数量单位" value="` + Data.units + `" class="layui-input" />
    </div>
    <div class="layui-form-mid layui-word-aux">
      可填写如：个，条，张等数量单位
    </div>
   </div>
  </div>
                `;
                let set = layer.open({
                    type: 1,
                    title: (index == false ? '批量修改' : '修改[' + index + ']'),
                    content: content,
                    btn: ['修改', '取消'],
                    area: ['98%', '98%'],
                    maxmin: true,
                    btn1: function () {
                        layer.close(set);
                        let Data = {
                            image: _this.FormLog('image'),
                            alert: _this.FormLog('alert'),
                            money: _this.FormLog('money'),
                            quantity: _this.FormLog('quantity'),
                            quota: _this.FormLog('quota'),
                            min: _this.FormLog('min'),
                            max: _this.FormLog('max'),
                            units: _this.FormLog('units'),
                        };
                        if (index == false) {
                            Object.keys(_this.Combination).forEach(function (key) {
                                _this.Combination[key] = Data;
                            });
                        } else {
                            _this.Combination[index] = Data;
                        }
                        _this.SKUJSON = JSON.stringify(_this.Combination);

                        if (Object.keys(_this.SkuBtn).length >= 1) {
                            _this.Btn();
                        }
                        layer.msg('保存成功', {
                            icon: 1
                        });
                    }
                });
            },
            FormLog(name) {
                return $("#Form input[name=" + name + "] ").val();
            },
            SPUVRAY(type = 1) {
                let _this = this;
                if (Object.values(_this.SKU).length == 0) {
                    if (type == 1) layer.msg('请先设置商品规格！', {
                        icon: 2
                    });
                    return false;
                }
                for (const string of this.SPU) {
                    if (_this.SKU[string] == undefined || _this.SKU[string] == null || _this.SKU[string] == '') {
                        if (type == 1) layer.msg('请将规格名称为：【' + string + '】的规格参数参数填写完整！');
                        _this.SKU[string] = [];
                        _this.initialize();
                        return false;
                    }
                }
                var obj = this.SKU;
                var arr = Object.values(obj);
                this.Create(arr);
            },
            Create(arr) {
                let allArr = this.cartesianProductOf(...arr);
                let Arrar = {};
                let _this = this;
                allArr.forEach(function (a) {
                    Arrar[(a.join(_this.Separator))] = {
                        image: '',
                        alert: '',
                        money: _this.price(a),
                        quantity: '',
                        quota: '',
                        min: '',
                        max: '',
                        units: '',
                    };
                });
                this.Combination = Arrar;
                this.SKUJSON = JSON.stringify(this.Combination);
                this.SPUJSON = JSON.stringify(this.SKU);
            },
            cartesianProductOf() {
                return Array.prototype.reduce.call(arguments, function (a, b) {
                    var ret = [];
                    a.forEach(function (a) {
                        b.forEach(function (b) {
                            ret.push(a.concat([b]));
                        });
                    });
                    return ret;
                }, [
                    []
                ]);
            },
            isRepeat(arr) {
                var hash = {};
                for (var i in arr) {
                    if (hash[arr[i]])
                        return true;
                    hash[arr[i]] = true;
                }
                return false;
            },
            SPUADD() {
                let _this = this;

                layer.prompt({
                    formType: 3,
                    value: '',
                    title: '添加规格名称,最多' + _this.Max + '个',
                }, function (value, index) {
                    if (value.indexOf('"') != -1) {
                        layer.alert('不可包含符号：" ', {
                            icon: 2
                        });
                        return;
                    }
                    if (_this.SPU.length == 0) {
                        let SPU = [];
                        SPU[0] = value;
                        _this.SPU = SPU;
                        layer.msg('规格名称:[' + value + ']添加成功！', {
                            icon: 1
                        });
                        layer.close(index);
                    } else {
                        if (_this.SPU.length >= _this.Max) {
                            layer.alert('最多添加' + _this.Max + '个规格名称', {
                                icon: 2
                            });
                            layer.close(index);
                            return false;
                        }
                        let SPU = JSON.parse(JSON.stringify(_this.SPU));
                        SPU[SPU.length] = value;
                        if (_this.isRepeat(SPU)) {
                            layer.alert('参数[' + value + ']重复！', {
                                icon: 2
                            });
                            layer.close(index);
                            return false;
                        }
                        _this.SPU = SPU;
                        layer.msg('规格名称:[' + value + ']添加成功！', {
                            icon: 1
                        });
                        layer.close(index);
                    }
                    _this.SPUVRAY(2);
                });
            },
            Add(SKU) {
                let _this = this;
                layer.prompt({
                    formType: 3,
                    value: '',
                    title: '为[' + SKU + ']添加子参数，最多' + (_this.Max * 3) + '个',
                }, function (value) {
                    layer.closeAll();
                    if (value.indexOf(_this.Separator) != -1) {
                        layer.alert('不可包含符号：' + _this.Separator + ' ', {
                            icon: 2
                        });
                        return;
                    }
                    if (value.indexOf('"') != -1) {
                        layer.alert('不可包含符号：" ', {
                            icon: 2
                        });
                        return;
                    }
                    if (_this.SKU[SKU] == undefined) {
                        _this.SKU[SKU] = [];
                        _this.SKU[SKU][0] = value;
                        _this.SKUCOSTSET();
                    } else {
                        let SKUARR = JSON.parse(JSON.stringify(_this.SKU[SKU]));
                        if (SKUARR.length >= (_this.Max * 3)) {
                            layer.alert('最多只可设置' + (_this.Max * 3) + '个子规则参数！', {
                                icon: 2
                            });
                            return;
                        }
                        SKUARR[SKUARR.length] = value;
                        if (_this.isRepeat(SKUARR)) {
                            layer.alert('参数[' + value + ']重复！', {
                                icon: 2
                            });
                        } else {
                            (_this.SKU[SKU]).push(value);
                            _this.SKUCOSTSET();
                        }
                    }
                    _this.SPUVRAY();
                });
            },
            Modification(Key = 0, Value = '', Index = 0, SpuIndex = 0, Money = 0) {
                let _this = this;
                layer.open({
                    title: '温馨提示',
                    content: '删除还是修改【' + Key + '】规则下的参数【' + Value + '】？',
                    icon: 3,
                    btn: ['改名', '删除', '改价', '取消'],
                    btn1: function () {
                        layer.prompt({
                            formType: 3,
                            value: Value,
                            title: '修改成什么？',
                        }, function (value, index) {
                            if (value.indexOf(_this.Separator) != -1) {
                                layer.alert('不可包含符号：' + _this.Separator + ' ', {
                                    icon: 2
                                });
                                return;
                            }
                            let Data = JSON.parse(JSON.stringify(_this.SKU[Key]));
                            Data[Index] = value;
                            if (_this.isRepeat(Data)) {
                                layer.alert('参数[' + value + ']重复！', {
                                    icon: 2
                                });
                                layer.close(index);
                                return false;
                            }
                            _this.SKU[Key][Index] = value;
                            _this.SKUCOSTSET();
                            layer.alert('已经成功将[' + Value + ']修改为：[' + value + ']', {
                                icon: 1
                            });
                            layer.close(index);
                            _this.SPUVRAY();
                        });
                    },
                    btn2: function () {
                        _this.SKU[Key] = (_this.SKU[Key]).filter(function (item) {
                            return item != Value;
                        });
                        layer.alert('规格参数[' + Value + ']删除成功', {
                            icon: 1
                        });
                        _this.SKUCOSTSET();
                        layer.closeAll();
                        _this.SPUVRAY();
                    },
                    btn3: function () {
                        layer.prompt({
                            formType: 3,
                            value: Money,
                            title: '此部件价改为多少？',
                        }, function (value, index) {
                            if (_this.isNumber(value) !== true) {
                                layer.msg('请输入纯数字！', {
                                    icon: 2
                                });
                                return false;
                            }
                            _this.SKUCOST[SpuIndex][Value] = (value - 0);
                            layer.msg('规格参数[' + Value + ']改价成功,已重新渲染商品价格！', {
                                icon: 1
                            });
                            layer.close(index);
                            _this.SPUVRAY();
                        });
                    }
                })
            },
            isNumber(val) {
                var regPos = /^[0-9]+.?[0-9]*/;
                return regPos.test(val);
            }
        }
    }).mount('#App');

    layui.config({
        base: '../../layuiadmin/'
    }).extend({
        index: 'lib/index',
    }).use(['form'], function () {
        if ($("#App").attr("data") != -1) {
            vm.Rendering($("#App").attr("data"));
        }
    });
</script>
</body>

</html>
