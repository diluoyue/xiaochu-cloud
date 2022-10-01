<?php

/**
 * 商品管理
 */
$title = '商品列表';
include 'header.php';
global $_QET;
?>
<div class="row" id="AppHis" cid="<?= ($_QET['cid'] ?? -1) ?>">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="添加商品" href="admin.goods.add.php" class="badge badge-primary"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="搜索商品" href="javascript:App.SearchGoods();" class="badge badge-danger"><i
                            class="layui-icon layui-icon-search"></i></a>
                <a title="批量设置" href="javascript:" mdui-menu="{target: '#BatchOperation'}"
                   class="badge badge-success"><i class="layui-icon layui-icon-set"></i></a>
                <ul class="mdui-menu" id="BatchOperation">
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(1);" class="mdui-ripple">
                            批量上架
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(2);">
                            批量下架
                        </a>
                    </li>
                    <!--<li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(3);">
                            批量隐藏
                        </a>
                    </li>-->
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(4);">
                            批量删除
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(5);">
                            设置参数
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(6);">
                            运费模板
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(7);">
                            利润比例
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(8);">
                            设置分类
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(9);">
                            售价精度
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(10);">
                            购买限制
                        </a>
                    </li>
                    <li class="mdui-menu-item">
                        <a href="javascript:App.BatchEditor(11);">
                            商品库存
                        </a>
                    </li>
                </ul>
                <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <a href="javascript:App.help();" class="btn btn-info badge ml-1 pb-0" style="">帮助</a>
            </div>
            <div class="card-body m-t-0" id="App" style="overflow-y: auto">
                <div v-if="cid!=-1" class="mb-2">正在查看分类[ {{cid}} ]下的全部商品 <a href="admin.goods.list.php">查看全部</a></div>
                <div v-if="name!=''" class="mb-2">正在查看商品名称带[ {{name}} ]关键词的全部商品</div>
                <table id="table" class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                    <thead style="white-space: nowrap">
                    <tr>
                        <th>操作</th>
                        <th>
                            <input name="checkbox_all" id="list_all" type="checkbox" onclick="goods.select_all()"
                                   value="true">
                            Gid
                        </th>
                        <th>排序</th>
                        <th>名称</th>
                        <th>所属分类</th>
                        <th>商品图</th>
                        <th>成本</th>
                        <th>售价(购买价区间)</th>
                        <th>利润比</th>
                        <th>剩余库存</th>
                        <th>发货方式</th>
                        <th>商品状态</th>
                        <th>备忘录</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data">
                        <td>
                            <button :mdui-menu="'{target:\'#html_operation_'+item.gid+'\'}'"
                                    class="mdui-btn mdui-ripple mdui-color-white mdui-text-color-blue-grey mdui-shadow-0 mdui-btn-icon">
                                <i class="mdui-icon material-icons">&#xe8b8;</i>
                            </button>
                            <ul class="mdui-menu" :id="'html_operation_'+item.gid">
                                <li class="mdui-menu-item">
                                    <a href="javascript:" @click="alert(item)" class="mdui-ripple">
                                        商品：{{ item.gid }}
                                    </a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a :href="'admin.goods.add.php?gid='+item.gid" class="mdui-ripple">编辑商品</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a @click="GoodsCopy(item.gid)" class="mdui-ripple">复制商品</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:" :onclick="'goods.note_set('+item.gid+')'" class="mdui-ripple"
                                       style="font-size:0.8em;">商品备忘录</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:" :onclick="'goods.SharePoster('+item.gid+')'"
                                       class="mdui-ripple">分享商品</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a :href="'admin.order.list.php?gid='+item.gid" class="mdui-ripple">商品订单</a>
                                </li>
                                <li class="mdui-menu-item">
                                    <a href="javascript:" @click="GoodsDelete(item.gid,item.name)" class="mdui-ripple">删除商品</a>
                                </li>
                            </ul>
                        </td>
                        <td>
                            <input type="checkbox" name="checkbox[]" :class="'list_box box_'+item.gid"
                                   :onclick="'goods.select_id('+item.gid+')'" :value="item.gid"/>
                            {{item.gid}}
                            </a>
                        </td>
                        <td>
                            <a :href="'javascript:goods.sort('+item.gid+',1)'" title="最顶部">↑</a>
                            <a :href="'javascript:goods.sort('+item.gid+',2)'" title="上移一格">▲</a>
                            <a :href="'javascript:goods.sort('+item.gid+',3)'" title="下移一格">▼</a>
                            <a :href="'javascript:goods.sort('+item.gid+',4)'" title="最低部">↓</a>
                        </td>
                        <td>
                            <div class="layui-elip mdui-text-color-deep-orange" @click="alert(item)" :title="item.name"
                                 style="max-width:10em;cursor:pointer">{{item.name}}
                            </div>
                        </td>
                        <td>
                            <div class="layui-elip" :title="item.Cname" style="max-width:5em;">
                                <a :href="'admin.goods.list.php?cid='+item.cid" title="点击查看此分类下的全部商品!"
                                >( {{item.cid}} )</a> {{item.Cname}}
                            </div>
                        </td>
                        <td>
                            <img :src="item.image[0]" style="width:30px;height:30px;"/>
                        </td>
                        <td><span class="badge badge-primary-lighten">{{item.money }}</span></td>
                        <td><span class="badge badge-danger-lighten">{{item.price }}</span></td>
                        <td><span class="badge badge-success-lighten">{{item.profits }}%</span></td>
                        <td>{{ item.sqid==-3?'剩 '+item.quota+' 张卡':'剩'+item.quota+'份' }}</td>
                        <td>
                            <span v-if="item.deliver==1" class="badge badge-success-lighten">自营</span>
                            <span v-else-if="item.deliver==2" class="badge badge-primary-lighten">访问URL</span>
                            <a :href="'admin.goods.token.php?gid='+item.gid" title="点击补卡" v-else-if="item.deliver==3"
                               class="badge badge-warning-lighten">自动发卡</a>
                            <span v-else-if="item.deliver==4" class="badge badge-primary-lighten">显示内容</span>
                            <span v-else-if="item.deliver==5" class="badge badge-warning-lighten">宝塔对接</span>
                            <span v-else class="badge badge-primary-lighten">{{ item.sqid }}</span>
                        </td>
                        <td>
                            <a v-if="item.state==1" @click="GoodsStateSet(item.gid,2,item.name)"
                               href="javascript:void(0)" class="badge badge-success-lighten">上架中</a>
                            <a v-if="item.state==2" href="javascript:void(0)"
                               @click="GoodsStateSet(item.gid,1,item.name)" class="badge badge-danger-lighten">已下架</a>
                            <a v-if="item.state==3" href="javascript:void(0)"
                               @click="GoodsStateSet(item.gid,1,item.name)" class="badge badge-warning-lighten">已隐藏</a>
                        </td>
                        <td>
                            <span v-if="item.note==null||item.note==''" class="badge badge-warning-lighten"
                                  :onclick="'goods.note_set('+item.gid+')'">设置</span>
                            <span v-else class="badge badge-info-lighten"
                                  :onclick="'goods.note_set('+item.gid+')'">查看</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个商品也没有' }}
                </div>
            </div>
            <div class="layui-card-body" style="text-align:center;">
                <div id="Page"></div>
            </div>
        </div>
    </div>
</div>
<div id="content_s" style="display: none">
    <div class="form-horizontal layui-form">
        <input type="checkbox" name="method[]" lay-skin="primary" value="1" title="[1] 允许在线购买" checked="">
        <input type="checkbox" name="method[]" lay-skin="primary" value="2" title="[2] 允许余额购买" checked="">
        <input type="checkbox" name="method[]" lay-skin="primary" value="3" title="[3] 允许被兑换" checked="">
        <input type="checkbox" name="method[]" lay-skin="primary" value="4" title="[4] 允许被对接" checked="">
        <input type="checkbox" name="method[]" lay-skin="primary" value="5" title="[5] 启用商品价格监控" checked="">
        <input type="checkbox" name="method[]" lay-skin="primary" value="6" title="[6] 允许被克隆" checked="">
        <input type="checkbox" name="method[]" lay-skin="primary" value="7" title="[7] 允许购买多份" checked="">
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/goodslist.js?vs=<?= $accredit['versions'] ?>"></script>
