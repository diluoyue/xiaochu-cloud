<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/8 15:33
// +----------------------------------------------------------------------
// | Filename: admin.api.set.php
// +----------------------------------------------------------------------
// | Explain: 节点配置
// +----------------------------------------------------------------------

$title = '服务端节点配置';
include 'header.php';
?>
<div class="card" id="App">
    <div class="card-body">
        <h4>请手动选择延迟最小的节点，并配置，此节点用于对接服务端！</h4>
        <hr>
        <div class="mdui-table-fluid">
            <table class="mdui-table">
                <thead>
                <tr>
                    <th>节点ID</th>
                    <th>节点名称</th>
                    <th>对接延迟</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item,index) in DataList">
                    <td>{{index+1}}</td>
                    <td>{{item.name}}</td>
                    <td @click="Ping(index)">
                        <span v-if="item.ping===false" class="layui-badge layui-bg-black">点击检测</span>
                        <span v-else-if="item.ping==-1" style="color: red;">无法访问</span>
                        <span v-else style="color: #0AAB89;">{{item.ping}}</span>
                    </td>
                    <td>
                        <button v-if="ids==index"
                                title="当前选择"
                                class="mdui-btn mdui-btn-icon mdui-text-color-green-600 mdui-shadow-1 mdui-ripple">
                            <i
                                    class="mdui-icon material-icons">star</i></button>
                        <button v-else
                                @click="ApiSelect(index)"
                                title="点击切换到此节点"
                                class="mdui-btn mdui-btn-icon mdui-text-color-grey-600 mdui-shadow-1 mdui-ripple">
                            <i class="mdui-icon material-icons">star_border</i></button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/cdns.js?vs=<?= $accredit['versions'] ?>"></script>
