<?php
/**
 * 网站模板配置
 */
$title = '网站模板配置';
include 'header.php';
global $cdnserver;
?>
<div class="card" id="App">
    <div class="card-body">
        <div class="mdui-tab mdui-tab-full-width" mdui-tab>
            <a href="#tab1" class="mdui-ripple">模板选择</a>
            <a href="#tab3" class="mdui-ripple">全局配置</a>
        </div>
        <div id="tab1" class="mdui-p-a-0 mdui-p-t-1">
            <div v-if="TemData!==false">
                <h3>
                    PC端模板：
                    <span class="badge badge-danger-lighten" v-if="TemData.conf.template==-1">已关闭</span>
                    <span class="badge badge-warning-lighten" v-else-if="TemData.conf.template==-2">套娃模式</span>
                    <span class="badge badge-primary-lighten" v-else>{{TemData.conf.template}}</span>
                </h3>

                <div class="mdui-row-xs-2 mdui-row-sm-3 mdui-row-md-4 mdui-row-lg-5 mdui-row-xl-6 mdui-grid-list">
                    <div class="mdui-col" @click="TemplateSelection(index,item,1)" style="cursor: pointer;"
                         title="当前选择的模板"
                         v-for="(item,index) in TemData.data.PC">
                        <div class="mdui-grid-tile">
                            <div v-if="TemData.conf.template==index" class="mdui-card-menu">
                                <button class="mdui-btn mdui-btn-icon mdui-text-color-white mdui-p-l-1 mdui-shadow-1 mdui-ripple"
                                        style="background-color: rgba(255,0,0,0.85);"><i
                                            class="mdui-icon material-icons mdui-m-r-1">beenhere</i>
                                </button>
                            </div>
                            <img onerror="this.src='../assets/img/tebj.jpg'" style="height:8em;"
                                 :src="'../template/'+index+'/index.png'"/>
                            <div class="mdui-grid-tile-actions mdui-grid-tile-actions-gradien">
                                <div class="mdui-grid-tile-text">
                                    <div class="mdui-grid-tile-title">
                                        {{(item==false?(index==-1?'关闭模板':(index==-2?'套娃模式':index)):item.name)}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <h3>
                    移动端模板：
                    <span class="badge badge-danger-lighten" v-if="TemData.conf.template_m==-1">已关闭</span>
                    <span class="badge badge-primary-lighten" v-else>{{TemData.conf.template_m}}</span>
                </h3>
                <div class="mdui-row-xs-2 mdui-row-sm-3 mdui-row-md-4 mdui-row-lg-5 mdui-row-xl-6 mdui-grid-list">
                    <div class="mdui-col" @click="TemplateSelection(index,item,2)" style="cursor: pointer;"
                         title="当前选择的模板"
                         v-for="(item,index) in TemData.data.M">
                        <div class="mdui-grid-tile">
                            <div v-if="TemData.conf.template_m==index" class="mdui-card-menu">
                                <button class="mdui-btn mdui-btn-icon mdui-text-color-white mdui-p-l-1 mdui-shadow-1 mdui-ripple"
                                        style="background-color: rgba(255,0,0,0.85);"><i
                                            class="mdui-icon material-icons mdui-m-r-1">beenhere</i>
                                </button>
                            </div>
                            <img onerror="this.src='../assets/img/tebj.jpg'" style="height:8em;"
                                 :src="'../template/'+index+'/index.png'"/>
                            <div class="mdui-grid-tile-actions mdui-grid-tile-actions-gradien">
                                <div class="mdui-grid-tile-text">
                                    <div class="mdui-grid-tile-title">
                                        {{(item==false?(index==-1?'关闭模板':index):item.name)}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                数据载入中...
            </div>
        </div>
        <div id="tab3" class="mdui-p-a-0 mdui-p-t-1">
            <div v-if="TemData!==false">
                <div class="mdui-textfield">
                    <div style="height:2em;line-height:2em;">背景图片</div>
                    <select :value="TemData.conf.background" v-model="TemData.conf.background" class="mdui-select"
                            style="width:100%;font-size:14px;color: rgba(29,29,29,0.77)">
                        <option value="1">随机二次元一(快)</option>
                        <option value="2">随机高清壁纸(快)</option>
                        <option value="3">随机二次元二(快)</option>
                        <option value="4">模板默认背景(快)</option>
                        <option value="5">自定义背景图(快)</option>
                    </select>
                    <div style="margin-top: 0.2em;font-size:12px;color:rgba(0,0,0,.54)">
                        自定义背景图存放到：assets/img/bj.png,若无则会显示空白！
                    </div>
                </div>

                <div class="mdui-textfield">
                    <div style="height:2em;line-height:2em;">静态资源加速节点</div>
                    <select :value="TemData.conf.cdnpublic" v-model="TemData.conf.cdnpublic" class="mdui-select"
                            style="width:100%;font-size:14px;color: rgba(29,29,29,0.77)">
                        <option value="1">七牛云CDN</option>
                        <option value="2">BootCDN</option>
                    </select>
                </div>

                <div class="mdui-textfield">
                    <div style="height:2em;line-height:2em;">晴玖静态资源加速</div>
                    <select :value="TemData.conf.cdnserver" v-model="TemData.conf.cdnserver" class="mdui-select"
                            style="width:100%;font-size:14px;color: rgba(29,29,29,0.77)">
                        <option value="1">开启静态资源加速</option>
                        <option value="2">关闭静态资源加速</option>
                    </select>
                    <div style="margin-top: 0.2em;font-size:12px;color:rgba(0,0,0,.54)">
                        若开启了SSL，可能会导致站点资源文件加载异常，导致无法正常使用！
                    </div>
                </div>

                <div class="mdui-textfield">
                    <div style="height:2em;line-height:2em;">首页横幅广告图</div>
                    <div>
                        <span class="badge badge-success-lighten" @click="BannerAdd()">添加一条</span>
                        <span class="badge badge-primary-lighten mdui-m-l-1" @click="BannerSetAll()">快速编辑</span>
                        <span class="badge badge-danger-lighten mdui-m-l-1" @click="BannerCloseAll()">清空全部</span>
                    </div>
                    <div class="mdui-m-t-1">
                        <div class="mdui-row-xs-2 mdui-row-sm-3 mdui-row-md-4 mdui-row-lg-5 mdui-row-xl-6 mdui-grid-list">
                            <div class="mdui-col" style="cursor: pointer;"
                                 title="点击编辑横幅广告"
                                 v-for="(item,index) in Banner"
                                 @click="OpenBanner(index,item)"
                            >
                                <div class="mdui-grid-tile">
                                    <img onerror="this.src='../assets/img/404.png'" style="height:8em;"
                                         :src="item.image"/>
                                    <div class="mdui-grid-tile-actions mdui-grid-tile-actions-gradien">
                                        <div class="mdui-grid-tile-text">
                                            <div class="mdui-grid-tile-title">
                                                {{item.url}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div v-else>
                数据载入中...
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/temset.js?vs=<?= $accredit['versions'] ?>"></script>
