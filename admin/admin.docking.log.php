<?php
$title = '对接日志';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <span class="mdui-m-l-1">共:{{count}}条日志</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto">
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <colgroup>
                        <col width="50">
                        <col width="200">
                        <col width="100">
                        <col>
                    </colgroup>
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>对接日期</th>
                        <th>请求耗时</th>
                        <th>请求url</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            <button @click="Tips(item)"
                                    class="mdui-btn mdui-ripple mdui-color-white mdui-shadow-0 mdui-btn-icon">
                                <i class="mdui-icon material-icons mdui-text-color-blue">&#xe8b6;</i>
                            </button>
                        </td>
                        <td>{{item.date}}</td>
                        <td>
                                <span class="badge badge-dark-lighten"
                                      v-if="item.ping===''||item.ping===null||item.ping===undefined">
                                    无数据
                                </span>
                            <span class="badge badge-warning-lighten" v-else>
                                    {{ item.ping }}
                                </span>
                        </td>
                        <td>{{item.url}}</td>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一条日志也没有' }}
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
<script src="../assets/admin/js/docking.js?vs=<?= $accredit['versions'] ?>"></script>
