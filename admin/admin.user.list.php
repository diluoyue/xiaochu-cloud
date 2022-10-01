<?php
$title = '用户管理列表';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <select v-model="GradeIndex" class="custom-select" name="OrderState" style="width:10em;">
                    <option value="-1" selected="">查看全部</option>
                    <option v-for="(item,index) in GradeList" :value="(index+1)">{{ item.name }}</option>
                </select>
                <select v-model="UserSort" class="custom-select" name="OrderState" style="width:10em;">
                    <option value="-1">默认排序</option>
                    <option value="1">余额正序(小到大)</option>
                    <option value="2">余额倒序(大到小)</option>
                    <option value="3">积分正序(小到大)</option>
                    <option value="4">积分倒序(大到小)</option>
                </select>
                <a title="添加用户" href="admin.user.add.php" class="badge badge-primary mdui-m-l-1"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="批量设置" mdui-menu="{target: '#BatchOperation'}"
                   class="badge badge-success mdui-text-color-white mdui-m-l-1"><i
                            class="layui-icon layui-icon-set"></i></a>
                <ul class="mdui-menu" id="BatchOperation">
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(1);" class="mdui-ripple">
                            清空用户站点配置
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(2);">
                            清空用户商品加价
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(3);">
                            校准用户等级
                        </a>
                    </li>
                </ul>
                <a title="搜索用户" href="javascript:App.SearchGoods();" class="badge badge-danger mdui-m-l-1"><i
                            class="layui-icon layui-icon-search"></i></a>
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <span class="mdui-m-l-1">共:{{count}}个用户</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto;min-height: 50vh;">
                <div v-if="name!==''" class="mb-2">正在查看搜索内容为[ {{name}} ]的相关用户 <a
                            href="javascript:App.initialization(-2);">查看全部</a>
                </div>
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>编号</th>
                        <th>用户名</th>
                        <th>级别</th>
                        <th>余额</th>
                        <th>积分</th>
                        <th>联系QQ</th>
                        <th>手机号</th>
                        <th>域名</th>
                        <th>上级ID</th>
                        <th>状态</th>
                        <th>最近登录(变动)</th>
                        <th>注册时间</th>
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
                                    <a :href="'./admin.user.log.php?uid='+item.id" target="_blank" class="mdui-ripple">
                                        用户：{{ item.id }}
                                    </a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a @click="adjustmentValue(item.id,'登录账号','username',item.username)"
                                       class="mdui-ripple">编辑账号</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a @click="adjustmentValue(item.id,'新的登录密码','password','')"
                                       class="mdui-ripple">设置密码</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a :href="'./admin.user.log.php?uid='+item.id" target="_blank" class="mdui-ripple">收支明细</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a :href="'admin.order.list.php?uid='+item.id" target="_blank" class="mdui-ripple">相关订单</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a @click="Login(item.id)" class="mdui-ripple">登录后台</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a @click="DeleteUser(item.id)" class="mdui-ripple">删除用户</a>
                                </li>
                            </ul>
                        </td>
                        <td>
                            <a :href="'./admin.user.log.php?uid='+item.id" target="_blank"
                               class="badge badge-primary-lighten">
                                {{ item.id }}
                            </a>
                        </td>
                        <td @click="adjustmentValue(item.id,'用户名称','name',item.name)">
                                <span class="badge badge-dark-lighten" v-if="item.name===''||item.name===null">
                                    未设置
                                </span>
                            <span v-else>
                                    {{ item.name }}
                                </span>
                        </td>
                        <td @click="adjustmentValue(item.id,'用户级别 [ 1-'+GradeList.length+' ] ,当前','grade',item.grade)">
                            <span class="badge badge-dark" :style="'background-color:' + colorById(item.grade)">{{ Grade(item.grade) }}</span>
                        </td>
                        <td><a title="点击修改" href="javascript:" @click="adjustment(item.id,item.money,1)"
                               style="font-weight:bold">
                                {{ NumRound(item.money) }}元</a></td>
                        <td>
                            <a title="点击修改" href="javascript:" @click="adjustment(item.id,item.currency,2)"
                               style="font-weight:bold;color:#ff730d">
                                {{ NumRound(item.currency) }}点</a>
                        </td>
                        <td @click="adjustmentValue(item.id,'联系QQ','qq',item.qq)">
                                <span class="badge badge-dark-lighten" v-if="item.qq===''||item.qq===null">
                                    未设置
                                </span>
                            <span v-else>
                                    {{ item.qq }}
                                </span>
                        </td>
                        <td @click="adjustmentValue(item.id,'手机号','mobile',item.mobile)">
                                <span class="badge badge-dark-lighten" v-if="item.mobile===''||item.mobile===null">
                                    未设置
                                </span>
                            <span v-else>
                                    {{ item.mobile }}
                                </span>
                        </td>
                        <td @click="adjustmentValue(item.id,'域名'+(pattern==1?'前缀':'小尾巴'),'domain',item.domain)">
                                <span class="badge badge-dark-lighten" v-if="item.domain===''||item.domain===null">
                                    未设置
                                </span>
                            <span class="badge badge-info-lighten" v-else>
                                    <span v-if="pattern==1">
                                        {{item.domain}}{{ domain }}
                                    </span>
                                    <span v-else>
                                        {{ domain }}?t={{item.domain}}
                                    </span>
                                </span>
                        </td>
                        <td @click="adjustmentValue(item.id,'上级用户编号','superior',item.superior)">
                                <span class="badge badge-dark-lighten"
                                      v-if="item.superior==0||item.superior==undefined||item.superior==null">
                                    没有上级
                                </span>
                            <span class="badge badge-primary-lighten" v-else>
                                    用户:{{ item.superior }}
                                </span>
                        </td>
                        <td>
                            <a v-if="item.state==1" @click="Ajax(item.id,'state',2)" href="javascript:"
                               class="badge badge-success-lighten">正常</a>
                            <a v-else href="javascript:" @click="Ajax(item.id,'state',1)"
                               class="badge badge-danger-lighten">禁封</a>
                        </td>
                        <td>{{ item.recent_time }}</td>
                        <td>{{ item.found_date }}</td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个用户也没有' }}
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
<script src="../assets/admin/js/userlist.js?vs=<?= $accredit['versions'] ?>"></script>
