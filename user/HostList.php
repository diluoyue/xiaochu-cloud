<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/26 16:06
// +----------------------------------------------------------------------
// | Filename: HostList.php
// +----------------------------------------------------------------------
// | Explain: 主机列表
// +----------------------------------------------------------------------

$title = '我的主机';
include 'header.php';
global $UserData, $conf, $_QET;
if (($conf['hostSwitch'] != 1)) {
    show_msg('警告', '主机模块已经关闭！');
}
?>
<div class="row" id="App" sid="<?= (empty($_QET['sid']) ? '' : $_QET['sid']) ?>">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>

                <span class="mdui-m-l-1">共:{{count}}个主机空间</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto">
                <div v-if="sid!==''" class="mb-2">正在查看服务器ID为[ {{sid}} ]的相关主机 <a href="./admin.host.list.php">查看全部</a>
                </div>
                <div v-if="name!=''" class="mb-2">正在查看和[ {{name}} ]关键词相关的主机 <a href="./admin.host.list.php">查看全部</a>
                </div>

                <table id="table" class="table table-hover table-centered mb-0"
                       style="font-size:0.9em;white-space: nowrap">
                    <thead>
                    <tr>
                        <th>操作</th>
                        <th>ID</th>
                        <th>激活状态</th>
                        <th>续费价格</th>
                        <th>域名绑定数</th>
                        <th>并发总数</th>
                        <th>单IP并发</th>
                        <th>流量上限</th>
                        <th>最大可上传文件</th>
                        <th>启停状态</th>
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
                                    <a href="javascript:" class="mdui-ripple">
                                        主机：{{ item.id }}
                                    </a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:;" @click="LogHostBackground(item.identification)"
                                       class="mdui-ripple">登陆后台</a>
                                </li>
                            </ul>
                        </td>
                        <td>{{item.id}}</td>
                        <td>
                            <span v-if="item.type==1" class="badge badge-primary-lighten">
                                已激活
                            </span>
                            <span v-else class="badge badge-danger-lighten">
                                未激活
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-warning-lighten">
                                {{item.RenewPrice - 0}}元 / 30天
                            </span>
                        </td>
                        <td>{{item.maxdomain}}个</td>
                        <td>{{item.concurrencyall}}</td>
                        <td>{{item.concurrencyip}}</td>
                        <td>{{item.traffic}}KB</td>
                        <td>{{item.filesize}}MB</td>
                        <td>
                            <span v-if="item.status==1" class="badge badge-success-lighten">
                                启动中
                            </span>
                            <span v-else class="badge badge-danger-lighten">
                                已停止
                            </span>
                        </td>
                        <td>
                            <span v-if="item.state==1" class="badge badge-success-lighten">
                                正常
                            </span>
                            <span v-else class="badge badge-danger-lighten">
                                冻结
                            </span>
                        </td>
                        <td>{{item.endtime}}</td>
                        <td>{{item.addtime}}</td>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个主机也没有' }}
                </div>
            </div>
            <div class="layui-card-body" style="text-align:center;">
                <div id="Page"></div>
            </div>
        </div>
    </div>
</div>
<?php
include 'bottom.php';
?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/user/js/hostlist.js"></script>
