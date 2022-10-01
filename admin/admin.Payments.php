<?php

/**
 * Author：晴玖天
 * Creation：2020/7/14 11:14
 * Filename：admin.Payments.php
 * 支付接口配置
 */
$title = '支付接口配置';
include 'header.php';
?>
<div class="mdui-container-fluid p-0" id="app">
    <div class="mdui-row">
        <div class="mdui-col-xs-12">
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">奸商模式</div>
                </div>
                <div class="mdui-card-content">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">通道费率(%)</label>
                        <input type="number" v-model="PayRates" class="mdui-textfield-input"
                               placeholder="请填写通道费率，如0.6"/>
                    </div>
                    <div class="mdui-title">
                        如果填写0，则在线支付手续费全部由自己承担，如果填写0.6，则用户付款时加价0.6%，以此类推
                    </div>
                </div>
            </div>
        </div>
        <div class="mdui-col-xs-12 mdui-col-sm-6 mdui-m-t-1">
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">QQ支付通道配置</div>
                </div>
                <div class="mdui-card-content">
                    <div v-for="(item , index) of qqpay" v-if="PayConQQ==item.id"
                         class="layui-form layui-form-pane p-1">
                        <div class="layui-form-item" v-for="(input,index2) of item.input" :key="index2"
                             class="mdui-textfield">
                            <label class="mdui-textfield-label">{{ input }}</label>
                            <textarea class="mdui-textfield-input" v-model="qqpay[index].InputData[index2]"
                                      :placeholder="'请将'+input+'填写完整！'"></textarea>
                        </div>
                        <button @click="Save(qqpay[index].InputData,item.id,0)"
                                class="mdui-btn mdui-btn-block mdui-btn-raised mdui-ripple mdui-color-deep-purple-a200">
                            保存[{{item.name}}]QQ配置
                        </button>
                    </div>
                    <div v-if="PayConQQ==-1">
                        支付通道已关闭，可前往应用商店下载安装更多支付插件！
                    </div>
                </div>
                <div class="mdui-card-actions">
                    <button v-for="(item , index) of qqpay" @click="Cut(1,item.id)" class="mdui-btn mdui-ripple"
                            :class="(PayConQQ==item.id?' mdui-color-blue-grey-400':'')">
                        {{item.name}}
                    </button>
                    <button :class="(PayConQQ==-1?' mdui-color-blue-grey-400':'')" @click="Cut(1,-1)" title="关闭QQ支付通道"
                            class="mdui-btn mdui-ripple">
                        {{(PayConQQ==-1?'接口已关闭':'关闭此接口') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="mdui-col-xs-12 mdui-col-sm-6 mdui-m-t-1">
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">微信支付通道配置</div>
                </div>
                <div class="mdui-card-content">
                    <div v-for="(item , index) of wxpay" v-if="PayConWX==item.id"
                         class="layui-form layui-form-pane p-1">
                        <div class="layui-form-item" v-for="(input,index2) of item.input" :key="index2"
                             class="mdui-textfield">
                            <label class="mdui-textfield-label">{{ input }}</label>
                            <textarea class="mdui-textfield-input" v-model="wxpay[index].InputData[index2]"
                                      :placeholder="'请将'+input+'填写完整！'"></textarea>
                        </div>
                        <button @click="Save(wxpay[index].InputData,item.id,1)"
                                class="mdui-btn mdui-btn-block mdui-btn-raised mdui-ripple mdui-color-green-600">
                            保存[{{item.name}}]微信配置
                        </button>
                    </div>
                    <div v-if="PayConWX==-1">
                        支付通道已关闭，可前往应用商店下载安装更多支付插件！
                    </div>
                </div>
                <div class="mdui-card-actions">
                    <button v-for="(item , index) of wxpay" :key="index" @click="Cut(2,item.id)"
                            class="mdui-btn mdui-ripple" :class="(PayConWX==item.id?' mdui-color-blue-grey-400':'')">
                        {{item.name}}
                    </button>
                    <button :class="(PayConWX==-1?' mdui-color-blue-grey-400':'')" @click="Cut(2,-1)" title="关闭QQ支付通道"
                            class="mdui-btn mdui-ripple">
                        {{(PayConWX==-1?'接口已关闭':'关闭此接口') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="mdui-col-xs-12 mdui-col-sm-12 mdui-m-t-1">
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">支付宝支付通道配置</div>
                    <div class="mdui-card-primary-subtitle">支付宝当面付的自定义商品名称设置为：-1时 会显示商品原名称</div>
                </div>
                <div class="mdui-card-content">
                    <div v-for="(item , index) of alipay" v-if="PayConZFB==item.id"
                         class="layui-form layui-form-pane p-1">
                        <div class="layui-form-item" v-for="(input,index2) of item.input" :key="index2"
                             class="mdui-textfield">
                            <label class="mdui-textfield-label">{{ input }}</label>
                            <textarea class="mdui-textfield-input" v-model="alipay[index].InputData[index2]"
                                      :placeholder="'请将'+input+'填写完整！'"></textarea>
                        </div>
                        <button @click="Save(alipay[index].InputData,item.id,2)"
                                class="mdui-btn mdui-btn-block mdui-btn-raised mdui-ripple mdui-color-indigo-a400">
                            保存[{{item.name}}]支付宝配置
                        </button>
                    </div>
                    <div v-if="PayConZFB==-1">
                        支付通道已关闭，可前往应用商店下载安装更多支付插件！
                    </div>
                </div>
                <div class="mdui-card-actions">
                    <button v-for="(item , index) of alipay" @click="Cut(3,item.id)" class="mdui-btn mdui-ripple"
                            :class="(PayConZFB==item.id?' mdui-color-blue-grey-400':'')">
                        {{item.name}}
                    </button>
                    <button :class="(PayConZFB==-1?' mdui-color-blue-grey-400':'')" @click="Cut(3,-1)" title="关闭QQ支付通道"
                            class="mdui-btn mdui-ripple">
                        {{(PayConZFB==-1?'接口已关闭':'关闭此接口') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="AppBanner" class="row">
    <div class="col-xl-12" v-for="(item,index) in List">
        <div class="card mdui-p-a-0 mdui-m-b-1 mdui-m-t-1">
            <div class="card-body p-0">
                <div class="mdui-panel mdui-panel-popout" :id="item.id + '_card'">
                    <div class="mdui-panel-item" :id="item.id + '_cards'">
                        <div class="mdui-panel-item-header" @click="Open(item.id)">
                            <div class="mdui-panel-item-title" style="width:80%">{{item.name}}
                            </div>
                            <i class="mdui-panel-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
                        </div>
                        <div class="mdui-panel-item-body">
                            <fieldset class="layui-elem-field">
                                <legend><a :href="item.url" target="_blank">{{item.url}}</a></legend>
                                <div class="layui-field-box">
                                    {{item.content}}
                                    <hr>
                                    <div class="mdui-text-center">
                                        <img :src="item.image"
                                             class="mdui-shadow-2"
                                             style="max-width:100%;"/>
                                    </div>
                                    <hr>
                                    广告热度：{{item.hot}}<br>
                                    点赞人数：{{item.top.length}}人<br>
                                    踩的人数：{{item.step_on.length}}人<br>
                                    抵押金额：{{item.deposit-0}}元
                                    <i class="mdui-icon material-icons" @click="Tips()"
                                       style="font-size:1.2em;color:#ff6b1b;cursor:pointer;">error_outline</i><br>
                                    到期时间：{{item.endtime}}<br>
                                    投放时间：{{item.addtime}}
                                    <hr>
                                    <div class="mc-vote mdui-text-center">
                                        <button @click="GiveThumbsUp(1,item,1)"
                                                class="mc-icon-button mdui-btn mdui-btn-icon mdui-btn-outlined mdui-shadow-2"
                                                :mdui-tooltip="'{content: \'顶一下,当前：'+item.top.length+'个人顶过\', delay: 300}'">
                                            <i
                                                    class="mdui-icon material-icons mdui-text-color-theme-icon"
                                                    style="color:rgba(4,180,170,0.72) !important">thumb_up</i>
                                        </button>
                                        <button @click="GiveThumbsUp(2,item,1)"
                                                class="mc-icon-button mdui-btn mdui-btn-icon mdui-btn-outlined mdui-shadow-2 mdui-m-l-5"
                                                :mdui-tooltip="'{content: \'踩一下,当前：'+item.step_on.length+'个人踩过\', delay: 300}'"
                                        ><i
                                                    class="mdui-icon material-icons mdui-text-color-theme-icon"
                                                    style="color:rgba(248,10,10,0.62) !important">thumb_down</i>
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/admin/js/pay.js?vs=<?= $accredit['versions'] ?>"></script>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/banner.js?vs=<?= $accredit['versions'] ?>"></script>
<script>
    AppBanner.ListGet(6);
</script>
