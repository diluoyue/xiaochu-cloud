<?php
$title = '分类管理';
include 'header.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="添加分类" href="./admin.class.add.php" class="badge badge-primary"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="查看商品" href="./admin.goods.list.php" class="badge badge-success"><i
                            class="layui-icon layui-icon-cart-simple"></i></a>
                <a title="初始化" href="javascript:App.ClassList();" class="badge badge-warning"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
            </div>
            <div class="card-body" id="App">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                        <thead>
                        <tr style="white-space: nowrap">
                            <th>CID</th>
                            <th>分类排序</th>
                            <th>分类名称</th>
                            <th>商品数量</th>
                            <th>分类图片</th>
                            <th>分类状态</th>
                            <th>支持的付款方式(<a
                                        href="javascript:layer.alert('可点击对应的付款通道名称来开启或关闭!<hr><font color=red>商品列表批量操作内可批量设置商品参数，如开启关闭支持的支付通道等！</font><hr>优先级说明：<br>1、当商品关闭了在线支付方式后，此处的QQ,微信,支付宝通道配置均无效,全部算为关闭状态<br>2、当商品开启了余额或积分付款方式后，此处却关闭了余额积分付款方式，用户付款时也会无法使用余额，积分付款，必须两处同时开启！',{title:'帮助说明'})">帮助说明</a>)
                            </th>
                            <th>可见等级限制(<a
                                        href="javascript:layer.alert('当设置为1时，对所有等级的用户，包括游客开放，当设置为2时，对大于等于2级的用户开放，以此类推<hr>注：此功能仅带有显示隐藏效果，不会影响用户购买，如其他站点用户通过对接API购买商品，不会有任何限制！',{title:'帮助说明'})">帮助说明</a>)
                            </th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item,index) in Data">
                            <td>
                                {{item.cid}}
                            </td>
                            <td>
                                <a :href="'javascript:App.sort('+item.cid+',1)'" title="最顶部">↑</a>
                                <a :href="'javascript:App.sort('+item.cid+',2)'" title="上移一格">▲</a>
                                <a :href="'javascript:App.sort('+item.cid+',3)'" title="下移一格">▼</a>
                                <a :href="'javascript:App.sort('+item.cid+',4)'" title="最低部">↓</a>
                            </td>
                            <td>
                                <a :href="'admin.goods.list.php?cid='+item.cid" target="_blank" style="color: red"
                                   title="打开分类">{{item.name}}</a>
                            </td>
                            <td>
                                <span class="badge badge-primary-lighten">{{item.count}}个</span>
                            </td>
                            <td>
                                <img :src="item.image" style="width:3em"/>
                            </td>
                            <td>
                                <a v-if="item.state==1" @click="ClassStateSet(item.cid,2,item.name)"
                                   href="javascript:void" class="badge badge-success-lighten">显示中</a>
                                <a v-if="item.state==2" href="javascript:void"
                                   @click="ClassStateSet(item.cid,1,item.name)"
                                   class="badge badge-danger-lighten">已隐藏</a>
                            </td>
                            <td style="cursor:pointer;">
                                <span class="badge" @click="ClassPaySet(item.cid,0)"
                                      :class="item.support[0]==1?'badge-success-lighten':'badge-danger-lighten'">QQ</span>
                                <span class="badge  ml-1 " @click="ClassPaySet(item.cid,1)"
                                      :class="item.support[1]==1?'badge-success-lighten':'badge-danger-lighten'">微信</span>
                                <span class="badge  ml-1 " @click="ClassPaySet(item.cid,2)"
                                      :class="item.support[2]==1?'badge-success-lighten':'badge-danger-lighten'">支付宝</span>
                                <span class="badge  ml-1 " @click="ClassPaySet(item.cid,3)"
                                      :class="item.support[3]==1?'badge-success-lighten':'badge-danger-lighten'">余额</span>
                                <span class="badge  ml-1 " @click="ClassPaySet(item.cid,4)"
                                      :class="item.support[4]==1?'badge-success-lighten':'badge-danger-lighten'">积分</span>
                            </td>
                            <td>
                                用户等级大于等于{{item.grade}}级
                            </td>
                            <td>
                                {{item.date}}
                            </td>
                            <td>
                                <a :href="'admin.class.add.php?cid='+item.cid" class="action-icon"> <i
                                            class="layui-icon layui-icon-set-fill"></i></a>
                                <span title="删除商品" style="cursor:pointer;color: red"
                                      @click="ClassDelete(item.cid,item.name)" class="action-icon"> <i
                                            class="layui-icon layui-icon-delete"></i></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个分类也没有' }}
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/jquery.nestable.js"></script>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/classlist.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>
