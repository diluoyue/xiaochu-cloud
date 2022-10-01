<?php

/**
 * 查询订单
 */
if (!defined('IN_CRONLITE')) die;
$User = login_data::user_data();
?>
<!DOCTYPE html>
<html lang="zh"
      style="font-size: 102.4px;<?= background::image() == false ? 'background:linear-gradient(to right, #bdc3c7, #2c3e50);' : background::image() ?>">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover,user-scalable=no">
    <script>
        document.documentElement.style.fontSize = document.documentElement.clientWidth / 750 * 40 + "px";
    </script>
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-param" content="_csrf">
    <meta name="csrf-token"
          content="201LTF3q2tij2vn-BETmAv-IWRi93YNTLfQ2YubgZ1WzfQIfAoGDrpWJvLVlEZBgl_JrQtix6RJnnmAsiNczHQ==">
    <meta name="viewport" content="width=device-width, viewport-fit=cover">
    <title><?= $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">

    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/foxui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/style.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/foxui.diy.css">
    <link rel="shortcut icon" href="<?= ROOT_DIR ?>assets/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/cool/assets/css/iconfont.css">

    <link href="<?php echo $cdnpublic; ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <script src="<?php echo $cdnpublic; ?>modernizr/2.8.3/modernizr.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layuiadmin/layui/css/layui.css"/>
</head>
<script>
    window.alert = function (name) {
        const iframe = document.createElement('IFRAME');
        iframe.style.display = 'none';
        iframe.setAttribute('src', 'data:text/plain,');
        document.documentElement.appendChild(iframe);
        window.frames[0].window.alert(name);
        iframe.parentNode.removeChild(iframe);
    };
</script>
<style>
    .fix-iphonex-bottom {
        padding-bottom: 34px;
    }

    body {
        width: 100%;
        max-width: 600px;
        margin: auto;
    }

    .fui-tab.fui-tab-primary a.active {
        color: #1492fb;
        border-color: #1492fb;
    }

    .qt-header {
        display: none;
        height: 10vh;
        line-height: 10vh;
        background-color: #FFF;
        margin-bottom: 6em;
        box-shadow: 1px 1px 16px #eee;
    }

    .qt-header > input {
        height: 5vh;
        width: 100%;
        border: none;
        text-indent: 2.5em;
        line-height: 5vh;
        border-radius: 0.5em;
        font-size: 0.7rem;
    }

    .qt-header > span {
        position: absolute;
        margin-left: 0.6rem;
        font-size: 0.7rem;
    }

    .qt-card {
        box-shadow: 0 0 6px #eee;
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
</style>

<body>
<div id="appquery" style="width: 100%;max-width: 600px">
    <div class="fui-page-group statusbar" style="max-width: 600px;left: auto;overflow: auto;">
        <div class="layui-row layui-col-space6">
            <div class="layui-card">
                <div class="layui-card-header"
                     style="text-align: center;font-size: 0.8rem;box-shadow: 3px 3px 16px #eee;color: #333;margin-bottom: 0.5rem;">
                    订单管理
                    <a href="javascript:hiset()" class="layui-icon layui-icon-search" id="sicon"
                       style="position:absolute;left: 1rem"></a>
                </div>
                <div class="layui-card-header qt-header">
                    <span class="layui-icon layui-icon-search" @click="GetOrderList(-2)"></span>
                    <input type="text" id="query" v-model="seek" placeholder="搜索我的订单" autocomplete="off">
                    <div class="layui-btn-group" style="width: 100%;background-color: #fff;">
                        <button @click="GetOrderList(-2)" type="button"
                                class="layui-btn layui-btn-primary layui-border-blue layui-btn-sm"
                                style="width: 50%;">查询有绑订单
                        </button>
                        <button @click="GetOrderList(-3)" type="button"
                                class="layui-btn layui-btn-primary layui-border-green layui-btn-sm" style="width: 50%;">
                            查询游客订单
                        </button>
                    </div>
                </div>
                <div class="page-body">
                    <div class="signin-card card-block auth-body mr-auto ml-auto">
                        <div class="card" style="padding: 1em;padding-top:0;">
                            <div class="card-block" style="margin-bottom: 3em;">
                                <div class="bl_more">
                                    <div class="text-left">
                                        <ul class="pagination">
                                            <li>
                                                <span style="display:block;height:2em;line-height:2em;text-align:center;">已取出 {{ OrderData.length }} 条订单</span>
                                            </li>
                                            <li <?= ($User === false ? '' : 'style="display: none;"') ?>>
                                                <a style="color: red;margin-left:1em;" href="./?mod=route&p=User">检测到您未登陆，登陆后可绑定订单，他人无法查询，保护您的购买信息，点我登陆！</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="layui-row layui-col-space15">
                                    <div class="layui-col-sm12" v-for="(item, index) in OrderData">
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
                                     style="text-align: center; width: 100%; margin: 1em auto 1em;">
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
        <style>
            .fui-navbar .label {
                margin-top: 0.3em;
            }
        </style>
        <div class="fui-navbar" style="max-width: 600px;padding-top: 0.1em">
            <a href="index.php" class="nav-item  "> <span class="icon icon-home"></span> <span class="label">首页</span>
            </a>
            <a href="./?mod=query" class="nav-item "> <span class="icon icon-dingdan1"></span> <span
                        class="label">订单</span> </a>
            <a href="./?mod=kf" class="nav-item "> <span class="icon icon-qq"></span> <span class="label">客服</span> </a>
            <a href="./?mod=route&p=User" class="nav-item "> <span class="icon icon-person2"></span> <span
                        class="label">会员中心</span>
            </a>
        </div>

    </div>
</div>
<script src="<?php echo $cdnpublic; ?>jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo $cdnpublic; ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic; ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic; ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic; ?>layer/2.3/layer.js"></script>
<script src="<?php echo $cdnpublic; ?>jquery/3.4.1/jquery.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
<script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/cool/assets/js/query.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>