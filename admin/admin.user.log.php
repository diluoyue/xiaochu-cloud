<?php
$title = '日志管理';
include 'header.php';
global $_QET;
?>
<div class="row" id="App" uid="<?= $_QET['uid'] ?>">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <span class="mdui-m-l-1">共:{{count}}条日志</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto">
                <div v-if="uid!==''" class="mb-2">正在查看编号为[ {{uid}} ]的用户日志 <a href="admin.user.log.php">查看全部</a>
                </div>
                <div v-if="name!==''" class="mb-2">正在查看类型为[ <span :style="'color:'+colorById(name)"> {{name}} </span>
                    ]的日志
                    <a href="javascript:App.initialization(-2);">查看全部</a>
                </div>
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>ID</th>
                        <th>用户编号</th>
                        <th>操作类型</th>
                        <th>数量</th>
                        <th>内容</th>
                        <th>IP</th>
                        <th>时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>{{ item.id }}</td>
                        <td>
                            <a :href="'?uid='+item.uid" class="badge badge-primary-lighten"> {{ item.uid }}</a>
                        </td>
                        <td @click="initialization(item.name)">
                            <span class="badge badge-dark-lighten mdui-text-color-white"
                                  :style="'background-color:' + colorById(item.name)">{{ item.name }}</span>
                        </td>
                        <td :title="item.count">
                            <font v-if="item.name=='余额提成'||item.name=='升级提成'" style="color: #249e17;">{{
                                item.count==0?'无提成':'+'+item.count+'元' }}</font>
                            <font v-else-if="item.name=='货币提成'||item.name=='积分充值'" style="color: #249e17;">{{
                                item.count==0?'无变化':'+'+item.count+'积分' }}</font>

                            <font v-else-if="item.name=='每日签到'||item.name=='邀请奖励'" style="color: #249e17;">{{
                                item.count==0?'无奖励':'+'+item.count+'积分' }}</font>

                            <font v-else-if="item.name=='余额退款'||item.name=='后台加款'||item.name=='在线充值'"
                                  style="color: #249e17;">{{ item.count==0?'无变化':'+'+item.count+'元' }}</font>

                            <font v-else-if="item.name=='商品改价'" style="color: #55aaff;">{{
                                item.count==0?'无变化':item.count+'%' }}</font>
                            <font v-else-if="item.name=='货币提成(无效)'||item.name=='余额提成(无效)'"
                                  style="color: #ff0000;">已退款</font>
                            <font v-else-if="item.name=='积分兑换'" style="color: #ff0000;">{{
                                item.count==0?'无变化':'-'+item.count+'积分' }}</font>
                            <font v-else-if="item.name=='后台扣款'||item.name=='等级提升'" style="color: #ff0000;">{{
                                item.count==0?'无变化':'-'+item.count+'元' }}</font>
                            <font v-else-if="item.name=='域名绑定'||item.name=='余额购买'||item.name=='在线购买'"
                                  style="color: #ff0000;">{{ item.count==0?'未扣款':'-'+item.count+'元' }}</font>
                            <font v-else style="color: #8a8a8a;">{{ item.count==0?'无':item.count }}</font>
                        </td>
                        <td>{{ item.content }}</td>
                        <td>{{ item.ip }}</td>
                        <td>{{ item.date }}</td>
                        <td>
                            <button @click="DeleteUserLog(item.id)"
                                    class="mdui-btn mdui-ripple mdui-color-white mdui-text-color-blue-grey mdui-shadow-0 mdui-btn-icon">
                                <i class="mdui-icon material-icons mdui-text-color-red" title="删除此日志">&#xe92b;</i>
                            </button>
                        </td>
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
<script src="../assets/admin/js/userlog.js?vs=<?= $accredit['versions'] ?>"></script>
