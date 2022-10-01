<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/23 17:48
// +----------------------------------------------------------------------
// | Filename: admin.apps.list.php
// +----------------------------------------------------------------------
// | Explain: App生成列表
// +----------------------------------------------------------------------

$title = 'App生成列表';
include 'header.php';
global $_QET;
?>
<div class="row" id="App" uid="<?= (isset($_QET['uid']) ?? $_QET['uid']) ?>">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="添加" href="javascript:;" @click="AppAdd()" class="badge badge-primary"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <a title="搜索商品" href="javascript:App.SearchGoods();" class="badge badge-danger mdui-m-l-1"><i
                            class="layui-icon layui-icon-search"></i></a>
                <span class="mdui-m-l-1">共:{{count}}条App打包任务</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto;">
                <div v-if="name!==''" class="mb-2">正在查看和[ {{name}} ]的相关任务 <a href="javascript:App.initialization(-2);">查看全部</a>
                </div>
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>ID</th>
                        <th>生成金额</th>
                        <th>App名称</th>
                        <th>封装域名</th>
                        <th>当前状态</th>
                        <th>站点编号</th>
                        <th>任务ID</th>
                        <th>状态说明</th>
                        <th>导航栏颜色</th>
                        <th>加载条颜色</th>
                        <th>App图标</th>
                        <th>App启动图</th>
                        <th>App介绍语</th>
                        <th>提交时间</th>
                        <th>创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            <button :mdui-menu="'{target:\'#html_operation_'+item.id+'\'}'"
                                    class="mdui-btn mdui-ripple mdui-color-white mdui-text-color-blue-grey mdui-shadow-0 mdui-btn-icon">
                                <i class="mdui-icon material-icons"></i></button>
                            <ul class="mdui-menu" :id="'html_operation_'+item.id">
                                <li class="mdui-menu-item"><a href="javascript:" class="mdui-ripple">
                                        任务ID：{{item.id}}</a></li>
                                <li @click="AppSubmit(item.id)" class="mdui-menu-item"
                                    v-if="item.state!=2&&item.state!=1"><a class="mdui-ripple">提交任务</a>
                                </li>
                                <li v-if="item.state==2 || item.state==3" @click="AppCalibration(item.id)"
                                    class="mdui-menu-item"><a class="mdui-ripple">同步进度</a>
                                </li>
                                <li @click="AppDownload(item.id)" class="mdui-menu-item" v-if="item.state==1"><a
                                            class="mdui-ripple">获取地址</a></li>
                                <li @click="AppDelete(item.id)" class="mdui-menu-item"><a href="javascript:"
                                                                                          class="mdui-ripple">删除任务</a>
                                </li>
                            </ul>
                        </td>
                        <td>{{item.id}}</td>
                        <td><span class="badge badge-primary-lighten">{{item.money}}元</span></td>
                        <td @click="adjustmentValue(item.id,'App名称','name',item.name,item.TaskID)">{{item.name}}</td>
                        <td @click="adjustmentValue(item.id,'封装站点域名','url',item.url,item.TaskID)">{{item.url}}</td>
                        <td>
                            <span @click="AppDownload(item.id)" class="badge badge-success-lighten"
                                  v-if="item.state==1">打包成功</span>
                            <span @click="AppCalibration(item.id)" class="badge badge-warning-lighten"
                                  v-if="item.state==2">正在打包</span>
                            <span @click="AppCalibration(item.id)" class="badge badge-default-lighten"
                                  v-if="item.state==3">打包失败</span>
                            <span @click="AppSubmit(item.id)" class="badge badge-primary-lighten" v-if="item.state==4">待提交</span>
                        </td>
                        <td @click="adjustmentValue(item.id,'绑定用户','uid',item.uid)">
                                <span class="badge badge-primary-lighten" v-if="item.uid>=1">
                                    {{item.uid}}
                                </span>
                            <span class="badge badge-dark-lighten" v-else>未绑定</span>
                        </td>
                        <td>
                                <span class="badge badge-primary-lighten" v-if="item.TaskID>=1">
                                    {{item.TaskID}}
                                </span>
                            <span @click="AppSubmit(item.id)" class="badge badge-dark-lighten" v-else>
                                    未提交
                                </span>
                        </td>
                        <td>{{item.TaskMsg}}</td>
                        <td @click="ColorSettings(1,item.id,item.theme,item.TaskID)" :style="'color:'+item.theme">
                            {{item.theme}}
                        </td>
                        <td @click="ColorSettings(2,item.id,item.load_theme,item.TaskID)"
                            :style="'color:'+item.load_theme">{{item.load_theme}}
                        </td>
                        <td @click="FilesUpload(1,item.id,item.icon,item.TaskID)">
                            <img :src="'./main.php?act=AppImage&id='+item.icon" style="width:30px;height:30px;"/>
                        </td>
                        <td @click="FilesUpload(2,item.id,item.background,item.TaskID)">
                            <img :src="'./main.php?act=AppImage&id='+item.background" style="width:30px;height:30px;"/>
                        </td>
                        <td @click="adjustmentValue(item.id,'App介绍内容','content',item.content)">
                            <span class="badge badge-dark-lighten" v-if="item.content==null">默认介绍</span>
                            <span v-else>{{item.content}}</span>
                        </td>
                        <td>
                            <span class="badge badge-dark-lighten" v-if="item.endtime==null">未完成</span>
                            <span v-else>{{item.endtime}}</span>
                        </td>
                        <td>{{item.addtime}}</td>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一条任务也没有' }}
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
<script src="../assets/admin/js/applist.js?vs=<?= $accredit['versions'] ?>"></script>
