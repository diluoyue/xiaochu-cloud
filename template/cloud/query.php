<?php
if (!defined('IN_CRONLITE')) die;
$title = '订单查询 - ' . $conf['sitename'];
include 'template/cloud/header.php';
$User = login_data::user_data();
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layuiadmin/layui/css/layui.css"/>
<style>
    input,
    select {
        border: none !important;
        border-bottom: solid #f4f4f4 1px !important;
        outline: none !important;
    }

    .qt-header {
        height: 4em;
        line-height: 4em;
    }

    .qt-header > input {
        height: 2.5em;
        width: 100%;
        border: none;
        text-indent: 2.5em;
        border-radius: 0.5em;
        font-size: 0.9em;
    }

    .qt-header > span {
        position: absolute;
        margin-left: 0.8em;
        font-size: 0.9em;
    }

    .qt-card {
        box-shadow: 0px 0px 6px #eee;
        border-radius: 0.5em;
    }

    .qt-card img {
        width: 6em;
        max-width: 100%;
        height: 6em;
        border-radius: 0.5em;
        box-shadow: 3px 3px 16px #eee;
    }

    .qt-btn {
        border-radius: 0.5em;
        border: solid 1px #eee;
    }

    .query_info {
        height: 3em;
    }
</style>
<div class="content__inner">
    <!-- 订单查询 -->
    <div class="card">
        <?php if (!isset($conf['notice_check'])) { ?>
            <div class="card-body">
                <span class="label label-primary">待处理</span> 说明正在努力提交到服务器！<p></p>
                <p></p><span class="label label-success">已完成</span> 并不是刷完了只是开始刷了！<p></p>
                <p></p><span class="label label-warning">处理中</span> 已经开始为您开单 请耐心等！<p></p>
                <p></p><span class="label label-danger">有异常</span> 下单信息有误 联系客服处理！
            </div>
        <?php } elseif (isset($conf['notice_check'])) { ?>
            <div class="card-body">
                <?= $conf['notice_check'] ?>
            </div>
        <?php } ?>

    </div>
    <div class="card" style="opacity: 0.9" id="appquery">
        <div class="card-body" style="padding: 0">
            <div class="layui-card">
                <div class="layui-card-body" style="padding: 0;color: black">
                    <div class="page-body">
                        <div class="signin-card card-block auth-body mr-auto ml-auto">
                            <div class="card" style="padding: 1em;">
                                <div class="card-block">
                                    <div class="input-group">
                                        <label class="col-form-label">下单信息</label>
                                        <input type="text" v-model="seek" class="form-control"
                                               placeholder="请输入购买时填写的第一行下单信息">
                                    </div>
                                    <div class="layui-btn-group" style="width: 100%;">
                                        <button @click="GetOrderList(-2)" type="button"
                                                class="layui-btn layui-btn-primary layui-border-blue layui-btn-sm"
                                                style="width: 50%;">
                                            查询有绑订单
                                        </button>
                                        <button @click="GetOrderList(-3)" type="button"
                                                class="layui-btn layui-btn-primary layui-border-green layui-btn-sm"
                                                style="width: 50%;">查询游客订单
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="page-body">
                        <div class="signin-card card-block auth-body mr-auto ml-auto">
                            <div class="card" style="padding: 1em;">
                                <div class="card-block">
                                    <div class="bl_more">
                                        <div class="text-left">
                                            <ul class="pagination">
                                                <li>
                                                    <span class="rows">已取出 {{ OrderData.length }} 条订单</span>
                                                </li>
                                                <li <?= ($User === false ? '' : 'style="display: none;"') ?>>
                                                    <a style="color: red;margin-left:1em;" href="./?mod=route&p=User">检测到您未登陆，登陆后可绑定订单，他人无法查询，保护您的购买信息，点我登陆！</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="layui-row layui-col-space15">
                                        <div class="layui-col-md4 layui-col-sm6" v-for="(item, index) in OrderData">
                                            <div class="layui-panel" style="padding: 20px;">
                                                <fieldset class="layui-elem-field">
                                                    <legend><a target="_blank" :href="'./?mod=shop&gid=' + item.gid">{{item.name==null?'商品已删除':item.name}}</a>
                                                    </legend>
                                                    <div class="layui-field-box">
                                                        订单状态：<span
                                                                style="display: none;background-color: rgba(0,182,95,0.89)"
                                                                :style="item.state==1?'display:inline-block;':'display: none;'"
                                                                class="layui-badge">已完成</span>
                                                        <span style="display: none;background-color: rgba(36,62,198,0.77)"
                                                              :style="item.state==2?'display:inline-block;':'display: none;'"
                                                              class="layui-badge">待处理</span>
                                                        <span style="display: none;background-color: rgba(255,0,0,0.68)"
                                                              :style="item.state==3?'display:inline-block;':'display: none;'"
                                                              class="layui-badge">异常中</span>
                                                        <span style="display: none;background-color: rgba(255,132,30,0.78)"
                                                              :style="item.state==4?'display:inline-block;':'display: none;'"
                                                              class="layui-badge">处理中</span>
                                                        <span style="display: none;background-color: #A6A6A6"
                                                              :style="item.state==5?'display:inline-block;':'display: none;'"
                                                              class="layui-badge">已退款</span>
                                                        <span style="display: none;background-color: #ff4254"
                                                              :style="item.state==6?'display:inline-block;':'display: none;'"
                                                              class="layui-badge">售后中</span>
                                                        <span style="display: none;background-color: #18b50c"
                                                              :style="item.state==7?'display:inline-block;':'display: none;'"
                                                              class="layui-badge">已评价</span>
                                                        <div>
                                                            <span v-if="item.coupon!=-1">付款金额：
                                                                <font color="red" size="3">{{ item.price }}元
                                                                    <span class="layui-word-aux"
                                                                          style="font-size:0.8em;text-decoration:line-through;">{{item.originalprice }}元</span></font>
                                                                    </span>
                                                            <span v-else>付款金额：<font color="#ff7f50" size="3">{{ item.price + (item.payment != '积分兑换' ? '元' :item.currency) }}</font></span>
                                                            × {{item.num}}份
                                                        </div>
                                                        付款方式：{{ item.payment }}<br>
                                                        付款时间：{{ item.addtime }}<br>
                                                        订单单号：{{ item.order }}
                                                        <hr>
                                                        <span v-for="(items, ins) in item.input">{{ (item.value[ins]==unll?'输入框'+(ins+1):item.value[ins]) }} ：{{ items }}
                                                        <br/>
                                                        </span>
                                                        <div v-if="item.Token.length>=1" style="color:red">
                                                            <hr>
                                                            <span v-for="(items, ins) in item.Token">
                                                                卡{{ (ins+1) +'：' + items.token }}
                                                                <br/>
                                                            </span>
                                                            <hr>
                                                        </div>
                                                        <div v-if="item.logistics!=-1">
                                                            {{item.logistics.name}}：{{ item.logistics.order }}
                                                            <a target="_blank" :href="Tracking + item.logistics.order"
                                                               class="layui-btn layui-btn-primary layui-border-orange layui-btn-xs"
                                                               style="margin-left:5px;">
                                                                物流查询
                                                            </a>
                                                            <br>
                                                        </div>
                                                        <button class="layui-btn layui-btn-primary layui-btn-sm layui-border-red layui-btn-fluid"
                                                                style="margin-top: 1em;"
                                                                @click="GetQuery(item.id,item.order)">查看订单详情
                                                        </button>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>

                                    <div @click="GetOrderList(page+1)"
                                         style="text-align: center; width: 100%; margin: 1.6rem 0px;">
                                        <button class="layui-btn layui-btn-radius layui-btn-primary layui-border-black">
                                            加载更多
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'template/cloud/bottom.php'; ?>
    <script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
    <script src="<?php echo $cdnserver; ?>assets/template/cloud/assets/js/query.js?vs=<?= $accredit['versions'] ?>"
            type="text/javascript"></script>