<?php
if (!defined('IN_CRONLITE')) exit();
$User = login_data::user_data();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>"/>
    <meta name="description" content="<?php echo $conf['description'] ?>"/>
    <meta charset="utf-8"/>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui"/>

    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/bootstrap.min.css"/>
    <link href="<?php echo $cdnpublic; ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/style.css?v=2"/>
    <link rel="stylesheet" type="text/css"
          href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/pcoded-horizontal.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/gmpanel.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layuiadmin/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/FaKa/assets/mobile.css"/>
</head>

<body id="app" class="query">
<div class="theme-loader">
    <div class="loader-track">
        <div class="loader-bar"></div>
    </div>
</div>
<div id="pcoded" class="pcoded">
    <div class="pcoded-container">
        <nav class="navbar header-navbar pcoded-header">
            <div class="navbar-wrapper">
                <div class="navbar-logo" logo-theme="theme1">
                    <a href="./"><img class="img-fluid" src="<?= $conf['logo'] ?>" alt="<?php echo $conf['sitename'] ?>"
                                      style="max-height: 35px;"/></a>
                    <a class="mobile-options">
                        更多
                        <i class="fa fa-ellipsis-v"></i>
                    </a>
                </div>

                <div class="navbar-container container-fluid">
                    <ul class="nav-right">
                        <li></li>
                        <li>
                            <a href="./">
                                <i class="fa fa-opera"></i>
                                首页
                            </a>
                        </li>
                        <li>
                            <a href="#" @click="AlertKefu">
                                <i class="fa fa-commenting"></i>
                                联系客服
                            </a>
                        </li>
                        <li>
                            <a v-if="Service.url != -1" :href="Service.url" target="_blank">
                                <i class="fa fa-commenting"></i>
                                在线咨询
                            </a>
                        </li>
                        <li>
                            <a v-if="InformData.Appurl != ''" :href="InformData.Appurl" target="_blank">
                                <i class="fa fa-android"></i>

                                下载APP
                            </a>
                        </li>
                        <li>
                            <a href="./?mod=query">
                                <i class="fa fa-search"></i>
                                查询订单
                            </a>
                        </li>
                        <?php if ($User != false) { ?>
                            <li><a href="./?mod=route&p=User">我的后台</a></li>
                        <?php } else { ?>
                            <li>
                                <a href="./?mod=route&p=User" style="color:red">
                                    <img src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/image/nav_money.png"
                                         alt="开通分站"/>
                                    开通分站
                                </a>
                            </li>

                            <li><a href="./?mod=route&p=User">后台登录</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="pcoded-main-container">
            <div class="container">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="page-body">
                            <div class="signin-card card-block auth-body mr-auto ml-auto">
                                <div class="card mt-4">
                                    <div class="card-block">
                                        <div class="input-group input-group-sm input-group-button">
                                            <input type="text" class="query_info form-control"
                                                   placeholder="请输入购买时填写的第一行下单信息" v-model="seek"/>
                                            <span @click="GetOrderList(-2)" class="input-group-addon"
                                                  style="display: block; width: 70px; text-align: center; height: 35px; line-height: 35px;">
													查有绑
												</span>
                                            <span @click="GetOrderList(-3)" class="input-group-addon"
                                                  style="display: block; width: 70px; text-align: center; height: 35px; line-height: 35px;">
													查游客
												</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="page-body">
                            <div class="signin-card card-block auth-body mr-auto ml-auto">
                                <div class="card">
                                    <div class="card-block">
                                        <div class="bl_more">
                                            <div class="text-left">
                                                <ul class="pagination">
                                                    <li>
                                                        <span class="rows">共 {{ OrderData.length }} 条订单</span>
                                                    </li>
                                                    <li <?= ($User == false ? '' : 'style="display: none;"') ?>>
                                                        <a style="color: red;" href="./?mod=route&p=User">检测到您未登陆，登陆后可绑定订单，他人无法查询，保护您的购买信息，点我登陆！</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <hr/>
                                        </div>
                                        <div class="layui-row layui-col-space15">
                                            <div class="layui-col-sm12" v-for="(item, index) in OrderData">
                                                <div class="layui-panel" style="padding: 20px;">
                                                    <fieldset class="layui-elem-field">
                                                        <legend><a target="_blank"
                                                                   :href="'./?mod=shop&gid=' + item.gid">{{item.name==null?'商品已删除':item.name}}</a>
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
                                                                <a target="_blank"
                                                                   :href="Tracking + item.logistics.order"
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
                        <div><?= $conf['statistics'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery.slimscroll.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/pcoded.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/menu-hori-fixed.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/jquery.mcustomscrollbar.concat.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/script.js"></script>
<script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
<script src="<?php echo $cdnserver; ?>assets/js/vue3.js"></script>
<script src="<?php echo $cdnserver; ?>assets/template/FaKa/assets/js/query.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>