<?php
$title = '公告通知';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="添加商品" href="admin.article.add.php" class="badge badge-primary"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <span class="mdui-m-l-1">共:{{count}}条公告</span>
            </div>
            <div class="card-body m-t-0" style="overflow-y: auto">
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>ID</th>
                        <th>标题</th>
                        <th>封面</th>
                        <th>浏览量</th>
                        <th>状态</th>
                        <th>可见范围</th>
                        <th>发布时间</th>
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
                                        公告：{{ item.id }}
                                    </a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a @click="Preview(item)" class="mdui-ripple">公告预览</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a :href="'admin.article.add.php?id='+item.id" class="mdui-ripple">编辑公告</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a @click="Delete(item)" class="mdui-ripple">删除公告</a>
                                </li>
                            </ul>
                        </td>
                        <td>{{item.id}}</td>
                        <td>{{item.title}}</td>
                        <td>
                            <img :src="item.image" style="width: 3em;">
                        </td>
                        <td>{{item.PV}}</td>
                        <td>
                            <a v-if="item.state==1" @click="NoticeStateSet(item.id,1,item.title)" href="javascript:void"
                               class="badge badge-success-lighten">显示中</a>
                            <a v-if="item.state==2" href="javascript:void" @click="NoticeStateSet(item.id,2,item.title)"
                               class="badge badge-danger-lighten">已隐藏</a>
                        </td>
                        <td>
                            <a v-if="item.type==1" @click="NoticeStateSet(item.id,3,item.title)" href="javascript:void"
                               class="badge badge-success-lighten">全部可见</a>
                            <a v-if="item.type==2" href="javascript:void" @click="NoticeStateSet(item.id,4,item.title)"
                               class="badge badge-danger-lighten">登陆可见</a>
                        </td>
                        <td>
                            {{item.date}}
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一条公告也没有' }}
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
<script src="../assets/admin/js/articlelist.js?vs=<?= $accredit['versions'] ?>"></script>
