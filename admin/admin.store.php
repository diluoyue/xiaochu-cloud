<?php
$title = '应用商店';
include 'header.php';
global $accredit;
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header mdui-p-a-0">
                <div class="mdui-appbar mdui-shadow-1 ">
                    <div class="mdui-tab mdui-tab-full-width" mdui-tab>
                        <a v-for="(item,index) in TypeList" @click="Cut(index,1)" class="mdui-ripple">
                            {{item.name}}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-header mdui-p-a-1 mdui-shadow-1">
                <a title="清理数据缓存" href="javascript:App.initialization(-2,-1,2);" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <a title="用户数据" href="javascript:App.Grade();" class="badge badge-success mdui-m-l-1"><i
                            class="layui-icon layui-icon-username"></i></a>
                <a title="搜索用户" href="javascript:App.SearchApp();" class="badge badge-danger mdui-m-l-1"><i
                            class="layui-icon layui-icon-search"></i></a>
                <a title="开发文档" target="_blank" href="http://docs.79tian.com/"
                   class="badge badge-primary mdui-text-color-white mdui-m-l-1"><i
                            class="layui-icon layui-icon-list"></i></a>
                <a title="应用托管" target="_blank" href="https://appstor.79tian.com/"
                   class="badge badge-primary mdui-text-color-white mdui-m-l-1"><i
                            class="layui-icon layui-icon-app"></i></a>
                <a title="上传应用安装包" href="javascript:"
                   id="Uploading"
                   class="badge badge-dark mdui-text-color-white mdui-m-l-1"><i
                            class="layui-icon layui-icon-upload"></i></a>
                <a style="height: 22px;margin-top: -12px;line-height: 18px;"
                   href="javascript:App.UpdateSoftwareList()"
                   class="badge badge-info mdui-text-color-white mdui-m-l-1">更新软件列表</a>
                <span class="mdui-m-l-1">共:{{count}}个应用</span>
            </div>
            <div v-if="SortTypeList.length>=1" class="card-header mdui-p-a-1">
                <div class="mdui-tab mdui-tab-centered mdui-tab-full-width">
                    <a @click="Cut(0,2)" class="mdui-ripple"
                       :class="SortType==0?' mdui-tab-active':''">
                        查看全部
                    </a>
                    <a v-for="(item,index) in SortTypeList" @click="Cut(item.id,2)" class="mdui-ripple"
                       :class="SortType==item.id?' mdui-tab-active':''" :title="item.content">
                        {{item.name}}
                    </a>
                </div>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto;">
                <div v-if="name!==''" class="mb-2">正在查看[ {{name}} ]的搜索结果 <a href="javascript:App.initialization(-2);">查看全部</a>
                </div>
                <table id="table" class="table table-hover table-centered mb-0"
                       style="font-size:0.9em;white-space:nowrap;">
                    <colgroup>
                        <col width="100">
                        <col width="200">
                        <col width="100">
                        <col>
                    </colgroup>
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>应用名称</th>
                        <th>开发者</th>
                        <th>应用售价</th>
                        <th>应用类型</th>
                        <th>到期时间</th>
                        <th>版本限制</th>
                        <th>等级限制</th>
                        <th>应用开关</th>
                        <th>全局悬窗</th>
                        <th>应用标识</th>
                        <th>应用描述</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            <button :mdui-menu="'{target:\'#html_operation_'+item.id+'\'}'"
                                    class="mdui-btn mdui-ripple mdui-color-white mdui-text-color-blue-grey mdui-shadow-0 mdui-btn-icon">
                                <i class="mdui-icon material-icons">&#xe8b8;</i>
                            </button>
                            <ul class="mdui-menu" :id="'html_operation_'+item.id">
                                <li class="mdui-menu-item">
                                    <a href="javascript:" class="mdui-ripple">
                                        编号：{{item.id}}
                                    </a>
                                </li>
                                <li v-if="item.PayType !== 3&&item.PayType !== 4" class="mdui-menu-item">
                                    <a href="javascript:" @click="prolong(item.id,item.price,item.name,item.discounts)"
                                       class="mdui-ripple">应用续期</a>
                                </li>
                                <li v-if="item.DeployState===2&&(item.PayType===1||item.PayType===4)"
                                    class="mdui-menu-item">
                                    <a href="javascript:" @click="install(item.identification,item.name)"
                                       class="mdui-ripple">安装应用</a>
                                </li>
                                <li v-if="item.DeployState===1&&item.UpdateState===2&&item.PayType===1"
                                    class="mdui-menu-item">
                                    <a href="javascript:"
                                       @click="Update(item.identification, item.versions, item.update_instructions)"
                                       class="mdui-ripple">应用升级</a>
                                </li>
                                <li v-if="item.PayType===3" class="mdui-menu-item">
                                    <a href="javascript:" @click="pay(item.id,item.price,item.name,item.discounts)"
                                       class="mdui-ripple">购买应用</a>
                                </li>
                                <li v-if="item.DeployState!==2&&item.state==1"
                                    class="mdui-menu-item">
                                    <a href="javascript:"
                                       :onclick="'Hover.Open(\''+item.identification+'\',\''+item.name+'\')'"
                                       class="mdui-ripple">打开界面</a>
                                </li>
                                <li v-if="item.DeployState!==2&&item.state==1"
                                    class="mdui-menu-item">
                                    <a href="javascript:" @click="iframeOn(item.identification,item.name)"
                                       class="mdui-ripple">使用说明</a>
                                </li>
                                <li v-if="item.DeployState!==2" class="mdui-menu-item">
                                    <a href="javascript:" @click="unload(item.identification,item.name)"
                                       class="mdui-ripple">卸载应用</a>
                                </li>
                            </ul>
                        </td>
                        <td style="cursor:pointer;">
                            <span v-if="item.DeployState!==2&&item.state==1"
                                  :onclick="'Hover.Open(\''+item.identification+'\',\''+item.name+'\')'">
                                    <img :src="item.image" class="mdui-m-r-1" style="width:20px;height:20px;"/>
                                    {{ item.name }}</span>
                            <span v-else-if="item.DeployState===2&&(item.PayType===1||item.PayType===4)"
                                  @click="install(item.identification,item.name)">
                                    <img :src="item.image" class="mdui-m-r-1" style="width:20px;height:20px;"/>
                                    {{ item.name }}</span>
                            <span v-else-if="item.PayType !== 3&&item.PayType !== 4"
                                  @click="prolong(item.id,item.price,item.name,item.discounts)">
                                    <img :src="item.image" class="mdui-m-r-1" style="width:20px;height:20px;"/>
                                    {{ item.name }}</span>
                            <span v-else-if="item.PayType===3"
                                  @click="pay(item.id,item.price,item.name,item.discounts)">
                                    <img :src="item.image" class="mdui-m-r-1" style="width:20px;height:20px;"/>
                                    {{ item.name }}</span>
                            <span v-else
                                  :onclick="'Hover.Open(\''+item.identification+'\',\''+item.name+'\')'">
                                    <img :src="item.image" class="mdui-m-r-1" style="width:20px;height:20px;"/>
                                    {{ item.name }}</span>

                            <span style="color:red"
                                  v-if="item.DeployState===1&&item.UpdateState===2&&item.PayType===1"
                                  @click="Update(item.identification, item.versions, item.update_instructions)">[可升级]</span>
                        </td>
                        <td>
                            <a :href="item.url" target="_blank">{{ item.source }}</a>
                        </td>
                        <td>
                            <div style="color: #18B566;" v-if="item.price == 0">免费</div>
                            <div class="layui-elip" v-if="item.discounts == 100 && item.price != 0">
                                <span style="font-size:1em;color:#fc6d26">￥{{ item.price }}/月</span>
                            </div>
                            <div class="layui-elip" v-if="item.discounts != 100 && item.price != 0">
                                <span style="font-size:1em;color:#fc6d26">￥{{ ((item.price - 0) * ((item.discounts - 0) / 100)).toFixed(2) }}/月</span>
                                <span style="color: #acacac;font-size: 0.7rem;" class="ml-1">￥{{ item.price }}/月</span>
                            </div>
                        </td>
                        <td>
                            <span v-if="item.type == 1" class="badge badge-warning-lighten"
                                  style="border-radius: 0.2em;">插件</span>
                            <span v-else class="badge badge-danger-lighten" style="border-radius: 0.2em;">模板</span>
                        </td>
                        <td>
                            <span v-if="item.DeployState===3||item.PayType===4" style="color:#ccc">
                                永久可用
                            </span>
                            <span v-else-if="item.PayType===3"
                                  class="badge badge-warning-lighten"
                                  style="cursor:pointer"
                                  @click="pay(item.id,item.price,item.name,item.discounts)">
                                点击购买
                            </span>
                            <span v-else>
                                    <div v-if="item.PayType === 1"
                                         style="color:#11c27b">{{item.endtime.split(' ')[0]}} <span
                                                style="color:#2eac3f;font-size: 90%;cursor:pointer"
                                                @click="prolong(item.id,item.price,item.name,item.discounts)">[续期]</span></div>
                                    <div v-else-if="item.PayType == 2" style="color:#fc8635" title="剩余使用时间不足">{{item.endtime.split(' ')[0]}} <span
                                                style="color:#2eac3f;font-size: 90%;cursor:pointer"
                                                @click="prolong(item.id,item.price,item.name,item.discounts)">[续期]</span></div>
                                </span>
                        </td>
                        <td>
                            <span class="badge badge-dark-lighten">{{item.versions_astrict}}</span>
                        </td>
                        <td>
                            <span class="badge badge-dark-lighten" v-if="item.grade==-1">无限制</span>
                            <span class="badge badge-dark-lighten" v-else>≧ {{item.grade}} 级</span>
                        </td>
                        <td>
                            <div v-if="item.DeployState!=2">
                                <div @click="state(item.identification,2)" style="cursor:pointer;"
                                     v-if="item.state==1"
                                     class="badge badge-success-lighten">开启中
                                </div>
                                <div @click="state(item.identification,1)" style="cursor:pointer;" v-else
                                     class="badge badge-danger-lighten">未开启
                                </div>
                            </div>
                            <div v-else class="badge badge-danger-lighten">
                                未安装
                            </div>
                        </td>
                        <td>
                            <div v-if="item.DeployState==2" class="badge badge-danger-lighten">
                                未安装
                            </div>
                            <div v-else>
                                <div @click="Hovers(item.identification,'HoverOff')" style="cursor:pointer;"
                                     v-if="item.Hover==1" class="badge badge-success-lighten">开启中
                                </div>
                                <div @click="Hovers(item.identification,'HoverOn')" style="cursor:pointer;" v-else
                                     class="badge badge-danger-lighten">未开启
                                </div>
                            </div>
                        </td>
                        <td>
                            {{item.identification}}
                        </td>
                        <td>
                            <span v-if="item.content==''||item.content==null">
                                无描述
                            </span>
                            <span v-else>
                                {{item.content}}
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个应用也没有~' }}
                </div>
            </div>
            <div class="layui-card-body" style="text-align:center;">
                <div id="Page"></div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/store.js?vs=<?= $accredit['versions'] ?>"></script>
