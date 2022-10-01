<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/11 15:45
// +----------------------------------------------------------------------
// | Filename: admin.server.list.php
// +----------------------------------------------------------------------
// | Explain: 服务器列表
// +----------------------------------------------------------------------

$title = '可用服务器列表';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <select v-model="time" class="custom-select" name="OrderState" style="width: 10em;">
                    <option v-for="index in 20" :value="index">同步速率：{{index}}秒</option>
                </select>
                <a title="添加商品" href="admin.server.add.php" class="badge badge-primary mdui-m-l-1"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="初始化" href="admin.server.list.php" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <span class="mdui-m-l-1">共:{{count}}台服务器</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto;white-space:nowrap;">
                <div v-if="name!==''" class="mb-2">正在查看ID为[ {{uid}} ]的用户日志 <a
                            href="javascript:App.initialization(-2);">查看全部</a>
                </div>
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>名称</th>
                        <th>服务器负载</th>
                        <th>CPU</th>
                        <th>内存</th>
                        <th>硬盘</th>
                        <th>系统</th>
                        <th>宝塔版本</th>
                        <th>主机数</th>
                        <th>主机大小配额</th>
                        <th>运行时间</th>
                        <th>网站/FTP/数据库</th>
                        <th>文件目录</th>
                        <th>状态</th>
                        <th>到期时间</th>
                        <th>创建时间</th>
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
                                    <a href="javascript:" @click="alert(item)" class="mdui-ripple">
                                        服务器：{{ item.id }}
                                    </a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a :href="'admin.server.add.php?id='+item.id" class="mdui-ripple">编辑服务器</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:" @click="CreateHost(index)" class="mdui-ripple">创建新主机</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:" @click="DeleteServer(item)" class="mdui-ripple">删除服务器</a>
                                </li>
                            </ul>
                        </td>
                        <td>
                            <a class="layui-elip mdui-text-color-deep-purple-500" :title="item.data.name"
                               :href="item.url"
                               target="_blank"
                               style="max-width: 10em; cursor: pointer;">{{item.data.name}}
                            </a>
                            <div style="color: red;font-size:0.9em;" v-if="ErrorData[index]!==false">
                                {{ErrorData[index]}}
                            </div>
                        </td>
                        <td>
                            <div v-if="RealTimeData[index]!=false">
                                {{RealTimeData[index].Load.Total }} / {{RealTimeData[index].Load.Occupy }}
                                <div class="mdui-progress" style="height:1em;"
                                     :title="RealTimeData[index]['Load']['Percentage'] + '%'"
                                >
                                    <div class="mdui-progress-determinate"
                                         :class="ColorConfiguration(RealTimeData[index]['Load']['Percentage'])"
                                         :style="'width:'+RealTimeData[index]['Load']['Percentage'] + '%'"></div>
                                </div>
                            </div>
                            <div v-else>
                                数据载入中
                            </div>
                        </td>
                        <td>
                            <div>
                                {{item.data.cpuNum}}核
                                <span v-if="RealTimeData[index]!=false"> / {{RealTimeData[index].CPU.Occupy}}%</span>
                            </div>
                            <div class="mdui-progress" style="height:1em;" v-if="RealTimeData[index]!=false"
                                 :title="RealTimeData[index].CPU.Occupy + '%'"
                            >
                                <div class="mdui-progress-determinate"
                                     :class="ColorConfiguration(RealTimeData[index].CPU.Occupy)"
                                     :style="'width:'+RealTimeData[index].CPU.Occupy + '%'"></div>
                            </div>
                        </td>
                        <td>
                            <div>
                                {{item.data.memTotal}}MB
                                <span v-if="RealTimeData[index]!=false"> / {{RealTimeData[index].Memory.Occupy }}MB</span>
                            </div>
                            <div @click="ReMemoryGet(index)" class="mdui-progress" style="height:1em;cursor:pointer;"
                                 :title="RealTimeData[index]['Memory']['Percentage'] + '%'"
                                 v-if="RealTimeData[index]!=false">
                                <div class="mdui-progress-determinate"
                                     :class="ColorConfiguration(RealTimeData[index]['Memory']['Percentage'])"
                                     :style="'width:'+RealTimeData[index]['Memory']['Percentage'] + '%'"></div>
                            </div>
                        </td>
                        <td>
                            <div>
                                {{item.data.DiskSize}}G
                                <span v-if="RealTimeData[index]!=false"> / {{RealTimeData[index].Disk.Occupy }}G</span>
                            </div>
                            <div class="mdui-progress" style="height:1em;" v-if="RealTimeData[index]!=false"
                                 :title="RealTimeData[index]['Disk']['Percentage'] + '%'">
                                >
                                <div class="mdui-progress-determinate"
                                     :class="ColorConfiguration(RealTimeData[index]['Disk']['Percentage'])"
                                     :style="'width:'+RealTimeData[index]['Disk']['Percentage'] + '%'"></div>
                            </div>
                        </td>
                        <td>
                            <div class="layui-elip" :title="item.data.system"
                                 style="max-width:6em; cursor: pointer;">{{item.data.system}}
                            </div>
                        </td>
                        <td>
                            {{item.data.version}}
                        </td>
                        <td>
                            <a :href="'./admin.host.list.php?sid='+item.id">{{item.count}}个</a>
                        </td>
                        <td>
                            {{item.HostSpace - 0}}MB
                        </td>
                        <td>
                            {{item.data.time}}
                        </td>
                        <td>
                            <span class="badge badge-success-lighten">
                                {{item.data.site_total}}
                            </span> /
                            <span class="badge badge-primary-lighten">
                                {{item.data.database_total}}
                            </span> /
                            <span class="badge badge-danger-lighten">
                                {{item.data.ftp_total}}
                            </span>
                        </td>
                        <td>
                            {{item.path}}
                        </td>
                        <td>
                            <span v-if="item.state==1" class="badge badge-success-lighten">
                                开启中
                            </span>
                            <span v-else class="badge badge-danger-lighten">
                                已关闭
                            </span>
                        </td>
                        <td>
                            {{item.endtime}}
                        </td>
                        <td>
                            {{item.addtime}}
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个可用服务器也没有，快去添加一个吧' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="color: red">
                主机安全相关
            </div>
            <div class="card-body" style="color: red;font-size:1.2em">
                注意：部分宝塔面板服务器会出现防跨站失效的问题！<br>这时宝塔主机用户可以通过程序获取到整个服务器的文件详情！<br>可通过开启宝塔内所有的PHP：bt_safe 安全扩展解决！<br>
                开启方法：打开软件商店->选择PHP->选择安装扩展->安装bt_safe扩展
            </div>
        </div>
    </div>
</div>
<div id="AppBanner" class="row">
    <div class="col-xl-12" v-for="(item,index) in List">
        <div class="card mdui-p-a-0 mdui-m-b-1 mdui-m-t-1">
            <div class="card-body p-0">
                <div class="mdui-panel mdui-panel-popout" :id="item.id + '_card'">
                    <div class="mdui-panel-item mdui-shadow-0" :id="item.id + '_cards'">
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
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/serverlist.js?vs=<?= $accredit['versions'] ?>"></script>
<script src="../assets/admin/js/banner.js?vs=<?= $accredit['versions'] ?>"></script>
<script>
    AppBanner.ListGet(5);
</script>
