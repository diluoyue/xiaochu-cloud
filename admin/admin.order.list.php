<?php

/**
 * 商品管理
 */
$title = '订单列表 - 数据每60秒更新一次';
include 'header.php';
global $_QET;
?>
<style>
    .mdui-menu-cascade {
        width: auto;
    }
</style>
<div class="row" id="App" gid="<?= ($_QET['gid'] ?? '') ?>" name="<?= ($_QET['name'] ?? '') ?>"
     uid="<?= ($_QET['uid'] ?? '') ?>">
    <div class="card" style="width:100%">
        <div class="card-header">
            <select v-model="state" class="custom-select" name="OrderState" style="width:10em;">
                <option value="" selected="">查看全部</option>
                <option value="1">已完成</option>
                <option value="2">待处理</option>
                <option value="3">异常</option>
                <option value="4">处理中</option>
                <option value="5">已退款</option>
                <option value="6">投诉中</option>
                <option value="7">已评价</option>
            </select>
            <a title="批量设置" mdui-menu="{target: '#BatchOperation'}"
               class="badge badge-success mdui-text-color-white mdui-m-l-1"><i
                        class="layui-icon layui-icon-set"></i></a>

            <a title="订单队列" href="javascript:App.OrderQueue();"
               class="badge badge-primary mdui-text-color-white mdui-m-l-1"><i
                        class="layui-icon layui-icon-list"></i></a>
            <a title="初始化" href="javascript:App.initialization();" class="badge badge-warning mdui-m-l-1"><i
                        class="layui-icon layui-icon-refresh-3"></i></a>
            <span class="mdui-m-l-1">共:{{count}}条</span>

            <ul class="mdui-menu" id="BatchOperation">
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(1)" class="mdui-ripple">
                        批量设置为已完成
                    </a>
                </li>
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(2)">
                        批量设置为待处理
                    </a>
                </li>
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(3)" class="mdui-ripple">
                        批量设置为异常
                    </a>
                </li>
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(4)" class="mdui-ripple">
                        批量设置为处理中
                    </a>
                </li>
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(5)" class="mdui-ripple">
                        批量为订单退款
                    </a>
                </li>
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(6)" class="mdui-ripple">
                        批量设置为售后中
                    </a>
                </li>
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(7)" class="mdui-ripple">
                        批量设置为已评价
                    </a>
                </li>
                <li class="mdui-divider"></li>
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(8)" class="mdui-ripple">
                        批量对接补单
                    </a>
                </li>
                <li class="mdui-menu-item">
                    <a href="javascript:" @click="BatchOperation(9)" class="mdui-ripple">
                        批量删除订单
                    </a>
                </li>
            </ul>

            <div class="mdui-textfield mdui-textfield-expandable mdui-float-right">
                <button class="mdui-textfield-icon mdui-btn mdui-btn-icon"><i
                            class="mdui-icon material-icons">search</i></button>
                <input class="mdui-textfield-input" v-model="name" type="text" placeholder="可输入下单信息,订单号,订单ID"/>
                <button class="mdui-textfield-close mdui-btn mdui-btn-icon"><i
                            class="mdui-icon material-icons">close</i></button>
            </div>
        </div>
        <div class="card-header">
            <div class="layui-row">
                <div class="layui-col-xs6">
                    <input type="text" v-model="date[0]" readonly class="layui-input" placeholder="开始日期,可留空"
                           id="OrderDate1">
                </div>
                <div class="layui-col-xs6">
                    <input type="text" v-model="date[1]" readonly class="layui-input" placeholder="结束日期,可留空"
                           id="OrderDate2">
                </div>
            </div>
        </div>
        <div class="card-body m-t-0" id="App" style="overflow-y: auto">
            <div v-if="gid!=''" class="mb-2">正在查看商品编号为[ {{gid}} ]的全部订单 <a href="./admin.order.list.php">查看全部</a></div>

            <div v-if="uid!=''" class="mb-2">正在查看用户[ {{uid}} ]的相关订单 <a href="./admin.order.list.php">查看全部</a></div>

            <div v-if="name!=''" class="mb-2">正在查看和关键词[ {{name}} ]有关的订单
                <a href="javascript:" @click="initialization('')">查看全部</a>
            </div>

            <table id="table" class="table table-hover table-centered mb-0">
                <thead style="white-space: nowrap;font-size:0.9em;">
                <tr>
                    <th>操作</th>
                    <th>
                        <input name="checkbox_all" id="list_all" type="checkbox" onclick="order.select_all()"
                               value="true">
                        订单ID
                    </th>
                    <th>商品名称</th>
                    <th>下单信息</th>
                    <th>份数</th>
                    <th>支付金额</th>
                    <th>付款方式</th>
                    <th>利润</th>
                    <th>站点</th>
                    <th>购买者</th>
                    <th>对接返回信息</th>
                    <th>对接站余额</th>
                    <th>对接状态</th>
                    <th>订单状态</th>
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
                        <ul class="mdui-menu mdui-menu-cascade" style="font-size: 1em;" :id="'html_operation_'+item.id">
                            <li class="mdui-menu-item">
                                <a href="javascript:" class="mdui-ripple">
                                    订单：{{ item.id }}
                                </a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="OrderSet(1,item.id)" class="mdui-ripple">标记已完成</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="OrderSet(2,item.id)" class="mdui-ripple">标记待处理</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="OrderSet(3,item.id)" class="mdui-ripple">标记异常中</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="OrderSet(4,item.id)" class="mdui-ripple">标记处理中</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="OrderSet(5,item.id,item.price)"
                                   class="mdui-ripple">标记已退款</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="OrderSet(6,item.id)" class="mdui-ripple">标记售后中</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="OrderSet(7,item.id)" class="mdui-ripple">标记已评价</a>
                            </li>
                            <li class="mdui-divider"></li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="order_dispose(item.id)" class="mdui-ripple">对接补单</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="remarkSet(item.id,item.remark)"
                                   class="mdui-ripple">订单备注</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="logisticsSet(item.id,item.logistics)" class="mdui-ripple">物流单号</a>
                            </li>
                            <li class="mdui-menu-item">
                                <a href="javascript:" @click="OrderSet(8,item.id)" class="mdui-ripple">删除订单</a>
                            </li>
                        </ul>
                    </td>
                    <td>
                        <input type="checkbox" name="checkbox[]" :class="'list_box box_'+item.id"
                               :onclick="'order.select_id('+item.id+')'" :value="item.id"/>
                        {{item.id}}
                        </a>
                    </td>
                    <td>
                        <div :onclick="'order.details('+item.id+')'" class="layui-elip"
                             style="max-width: 5em; cursor: pointer;">
                            {{ item.name==null?'商品不存在':item.name }}
                        </div>
                    </td>
                    <td @click="set_order(item.id,item.input)">
                        <div v-for="(vs,is) in item.input" style="font-size:14px;color:#4c415f">
                            {{
                            (item.InputName[is]==''||item.InputName[is]==null?'输入框'+(is+1):item.InputName[is])
                            }} : {{vs}}
                        </div>
                    </td>
                    <td @click="order_set(item.id,item.num,'num','下单份数',2)">
                        {{item.num}} 份
                    </td>
                    <td @click="order_set(item.id,item.price,'price','支付金额',2)">
                            <span class="badge badge-success-lighten" v-if="item.price==0">
                                免费领取
                            </span>
                        <span class="badge badge-warning-lighten" v-else-if="item.payment=='积分兑换'">
                                {{ item.price-0 }}积分
                            </span>
                        <span class="badge badge-primary-lighten" v-else>
                                {{ item.price-0 }}元
                            </span>
                    </td>
                    <td style="font-size:0.8em;">
                        {{item.payment}}
                    </td>
                    <td>
                            <span class="badge badge-warning-lighten" v-if="item.payment=='积分兑换'||item.payment=='免费领取'">
                                {{ item.money==0?'不亏不赚':'亏'+(item.money-0).toFixed(3) + '元' }}
                            </span>
                        <span class="badge badge-danger-lighten" v-else-if="(item.price - item.money)<0">
                                亏{{ (item.money - item.price).toFixed(3) }}元
                            </span>
                        <span class="badge badge-success-lighten" v-else>
                                赚{{ (item.price - item.money).toFixed(3) }}元
                            </span>
                    </td>
                    <td>
                            <span v-if="item.muid==-1" class="badge badge-dark" style="background-color:#6386f6;">
                                主站
                            </span>
                        <a :href="'?uid='+item.muid" v-else class="badge badge-primary-lighten">
                            {{item.muid}}
                        </a>
                    </td>
                    <td>
                            <span class="badge badge-info-lighten" v-if="item.uid==-1">
                                游客
                            </span>
                        <a :href="'?uid='+item.uid" class="badge badge-primary-lighten" v-else>
                            {{item.uid}}
                        </a>
                    </td>
                    <td>
                        <textarea @click="order_set(item.id,item.return,'return','对接返回信息')" readonly
                                  style="min-height:1em;" class="layui-textarea">{{ (item.return===''||item.return===null?'无':item.return) }}</textarea>
                    </td>
                    <td>
                        {{ item.user_rmb <=0 ?'获取失败':item.user_rmb + '元' }}
                    </td>
                    <td>
                            <span @click="ReplacementOrder(item.id)" class="badge"
                                  style="background-color:#8a8d8c;color:#fff;cursor: pointer" v-if="item.docking==-1">
                                跟随订单
                            </span>
                        <span :onclick="'order.details('+item.id+')'" class="badge badge-success-lighten"
                              style="cursor:pointer;" title="查看订单详情" v-else-if="item.docking==1">
                                对接成功
                            </span>
                        <span @click="order_dispose(item.id)" style="cursor:pointer" class="badge badge-danger-lighten"
                              v-else-if="item.docking==2">
                                对接失败
                            </span>
                        <span @click="order_dispose(item.id)" style="cursor:pointer" class="badge badge-primary-lighten"
                              v-else-if="item.docking==3">
                                待提交
                            </span>
                        <span style="cursor:pointer" class="badge badge-dark-lighten" v-else-if="item.docking==4">
                                无需对接
                            </span>
                        <span class="badge badge-info-lighten" v-else>
                                未知状态
                            </span>
                    </td>
                    <td @click="remarkSet(item.id,item.remark)" style="cursor:pointer;">
                            <span class="badge badge-success-lighten" v-if="item.state==1">
                                已完成
                            </span>
                        <span class="badge badge-primary-lighten" v-else-if="item.state==2">
                                待处理
                            </span>
                        <span class="badge badge-danger-lighten" v-else-if="item.state==3">
                                异常中
                            </span>
                        <span class="badge badge-warning-lighten" v-else-if="item.state==4">
                                处理中
                            </span>
                        <span class="badge badge-dark-lighten" v-else-if="item.state==5">
                                已退单
                            </span>
                        <span class="badge badge-warning-lighten" v-else-if="item.state==6">
                                投诉中
                            </span>
                        <span class="badge badge-success-lighten" v-else-if="item.state==7">
                                已评价
                            </span>
                        <span class="badge badge-danger-lighten" v-else>
                                未知
                            </span>
                    </td>
                    <td>
                        {{item.addtitm}}
                    </td>
                </tr>
                </tbody>
            </table>
            <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                {{ type==-1?'正在载入中,请稍后...':'一个订单也没有' }}
            </div>
        </div>
        <div class="layui-card-body" style="text-align:center;">
            <div id="Page"></div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/orderlist.js?vs=<?= $accredit['versions'] ?>"></script>
