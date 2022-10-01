<?php
/**
 * 后台首页
 */

use Medoo\DB\SQL;

$title = '用户首页';
include 'header.php';
global $UserData, $_QET, $conf;
if (isset($_QET['act']) && $_QET['act'] == 'get') { #领取发放的奖励
    reward::issue_reward($UserData, $_QET['id']);
}
if (isset($_QET['act']) && $_QET['act'] == 'welfare') {
    reward::welfare($UserData);
}
if (isset($_QET['act']) && $_QET['act'] === 'notification') {
    $DB = SQL::DB();
    $Get = $DB->get('notice', ['content', 'title', 'date'], ['id' => (int)$_QET['id']]);
    if (!$Get) {
        show_msg('温馨提示', '公告通知不存在！', 4);
    }
    show_msg($Get['title'], $Get['content'] . '<hr>发布时间：' . $Get['date'], 3);
}

$count_data = reward::statistics($UserData['id']); #信息统计
$data_arr = reward::Invite_statistics($UserData); #邀请列表
$DB = SQL::DB();
$notice_user = $DB->select('notice', [
    'id', 'title', 'content', 'PV', 'date'
], [
    'state' => 1
]);

/**
 * 小提示
 */
$tpis_arr = [
    '小提示：每日可用' . $conf['currency'] . '兑换一次商品哦！',
    '小提示：积分可以通过每日签到,邀请他人注册获得！',
    '小提示：可兑换的商品每天0点会刷新商品库存！',
    '小提示：若是看上什么需要的东西,您可以直接购买！',
];
$tpis = $tpis_arr[array_rand($tpis_arr, 1)];
?>
<div class="row">
    <div class="col-xl-4">
        <div class="card d-block pt-2 pb-1 text-center">
            <img class="card-img-top m-auto"
                 style="height: 68px;width: 68px;margin:auto;display: block;border-radius: 0.3em;box-shadow: 0px 0px 30px #ccc"
                 src="<?= UserImage($UserData) ?>" alt="Card image cap">
            <div class="card-body pb-2">
                <h5 class="card-title">平台官方认证用户</h5>
                <p class="card-text text-success"><?= $UserData['name'] ?> [ UID:<?= $UserData['id'] ?> /
                    V<?= $UserData['grade'] ?>用户 ]</p>
            </div>
            <ul class="list-group list-group-flush mb-2">
                <li class="list-group-item"><?= $tpis ?></li>
            </ul>
            <a class="btn btn-outline-danger mb-2" href="?act=welfare"><i
                        class="layui-icon <?= reward::welfare_judge($UserData) === true ? 'layui-icon-rate' : 'layui-icon-rate-solid' ?>"></i>
                每日签到
            </a>
            <a class="btn btn-outline-warning mb-2" href="../"><i
                        class="layui-icon layui-icon-cart"></i> 在线下单
            </a>
            <a href="activity.php" class="btn btn-outline-primary mb-2"><i
                        class="layui-icon layui-icon-gift"></i> 推广有奖
            </a>
            <a class="btn btn-outline-info mb-2" href="set.php"><i
                        class="layui-icon layui-icon-username"></i> 个人信息
            </a>
            <a class="btn btn-outline-success mb-2" href="pay.php"><i
                        class="layui-icon layui-icon-rmb"></i> 在线充值
            </a>
            <a href="?act=close" class="btn btn-outline-secondary mb-2"><i
                        class="layui-icon layui-icon-logout"></i> 退出登陆
            </a>
        </div> <!-- end card-->
    </div>
    <div class="col-xl-8">
        <div class="row">
            <div class="col-xl-12">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%;"
                         onclick="layer.alert('QQ互联绑定可用作账号登陆！',{icon:3,title:'QQ互联绑定',btn:['确定','重新绑定'],btn2:function(layero,index) { window.open('ajax.php?act=connected') }})">
                        <div class="card widget-flat" id="data1">
                            <div class="card-body">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-login-qq widget-icon"
                                       style="background-color: #494951;color: white;"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    QQ互联</h5>
                                <h5 class="mt-2 mb-0"
                                    style="font-weight: 300"><?= ($UserData['user_idu'] == '' ? '点击绑定' : $UserData['name']) ?></h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                    <?php if ($conf['sms_switch_user'] == -1 && $conf['sms_switch_order'] == -1) { ?>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%">
                            <div class="card widget-flat" id="data2">
                                <div class="card-body">
                                    <div class="float-right" style="width: 30px;height: 30px">
                                        <i class="layui-icon layui-icon-cellphone widget-icon"
                                           style="background-color: #02cbe4;color: white"></i>
                                    </div>
                                    <h5 class="text-muted font-weight-normal mt-0">
                                        注册时间</h5>
                                    <h5 class="mt-2 mb-0" style="font-weight: 300"><?= $UserData['found_date'] ?></h5>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                    <?php } else { ?>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%"
                             onclick="Mobile_phone_binding(<?= $UserData['mobile'] ?>)">
                            <div class="card widget-flat" id="data2">
                                <div class="card-body">
                                    <div class="float-right" style="width: 30px;height: 30px">
                                        <i class="layui-icon layui-icon-cellphone widget-icon"
                                           style="background-color: #02cbe4;color: white"></i>
                                    </div>
                                    <h5 class="text-muted font-weight-normal mt-0">
                                        手机号码</h5>
                                    <h5 class="mt-2 mb-0"
                                        style="font-weight: 300"><?= ($UserData['mobile'] == '' ? '点击绑定' : $UserData['mobile']) ?></h5>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                    <?php } ?>
                </div>
            </div>
        </div> <!-- end row -->
        <div class="row">
            <div class="col-xl-6">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%;">
                        <div class="card widget-flat" id="data1">
                            <div class="card-body">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-rmb widget-icon"
                                       style="background-color: #ff0a24;color: white;"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    余额 <a href="pay.php" class="badge badge-danger-lighten">充值</a></h5>
                                <h5 class="mt-2 mb-0" style="font-weight: 300"><?= round($UserData['money'], 2) ?>元</h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%">
                        <div class="card widget-flat" id="data2">
                            <div class="card-body">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-rate-solid widget-icon"
                                       style="background-color: #ff824d;color: white"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    可用<font color="blue"><?= $conf['currency'] ?></font></h5>
                                <h5 class="mt-2 mb-0" style="font-weight: 300"><?= round($UserData['currency'], 0) ?>
                                    个</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%;">
                        <div class="card widget-flat" id="data1">
                            <div class="card-body">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-list widget-icon"
                                       style="background-color: #ff6ba8;color: white;"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    订单总数</h5>
                                <h5 class="mt-2 mb-0"
                                    style="font-weight: 300"><?= ($count_data['count_1'] == 0 ? 0 : $count_data['count_1']) ?>
                                    条</h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%">
                        <div class="card widget-flat" id="data2">
                            <div class="card-body">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-dollar widget-icon"
                                       style="background-color: #98ff3b;color: white"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    累计消费</h5>
                                <h5 class="mt-2 mb-0"
                                    style="font-weight: 300"><?= round(($count_data['count_2'] == 0 ? 0 : $count_data['count_2']), 2) ?>
                                    元</h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div>
            </div>

        </div> <!-- end row -->

        <div class="row">

            <div class="col-xl-6">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%;">
                        <div class="card widget-flat" id="data1">
                            <div class="card-body">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-component widget-icon"
                                       style="background-color: #8ed9ff;color: white;"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    累计邀请</h5>
                                <h5 class="mt-2 mb-0"
                                    style="font-weight: 300"><?= ($count_data['count_3'] == 0 ? 0 : $count_data['count_3']) ?>
                                    人</h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%">
                        <div class="card widget-flat" id="data2">
                            <div class="card-body">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-star widget-icon"
                                       style="background-color: #a1ccff;color: white"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    累计奖励</h5>
                                <h5 class="mt-2 mb-0"
                                    style="font-weight: 300"><?= ($count_data['count_4'] == 0 ? 0 : $count_data['count_4']) ?><?= $conf['currency'] ?></h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div>
            </div>

            <div class="col-xl-6">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%;">
                        <div class="card widget-flat" id="data1">
                            <div class="card-body">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-user widget-icon"
                                       style="background-color: #9655d0;color: white;"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    我的编号</h5>
                                <h5 class="mt-2 mb-0" style="font-weight: 300"><?= $UserData['id'] ?></h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%">
                        <div class="card widget-flat" id="data2">
                            <div class="card-body"
                                 onclick="layer.alert('等级越高商品价格越便宜哦,当前你的等级为<?= $UserData['grade'] ?>级！',{title:'等级提示',btn:['确定','提升等级'],btn2:function(layero,index) { location.href='grade.php' }})">
                                <div class="float-right" style="width: 30px;height: 30px">
                                    <i class="layui-icon layui-icon-cart-simple widget-icon"
                                       style="background-color: #43A047;color: white"></i>
                                </div>
                                <h5 class="text-muted font-weight-normal mt-0">
                                    当前等级</h5>
                                <h5 class="mt-2 mb-0" style="font-weight: 300">V<?= $UserData['grade'] ?>用户</h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div>
            </div>
        </div> <!-- end row -->


    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-4">公告通知</h4>
                <div class="slimscroll" style="max-height: 230px;">
                    <?php foreach ($notice_user as $v) { ?>
                        <a href="?act=notification&id=<?= $v['id'] ?>" class="dropdown-item notify-item pb-0">
                            <p class="notify-details"><span class="text-truncate mr-3"><?= $v['title'] ?></span>
                                <span class="badge badge-success-lighten"><?= $v['PV'] ?>人已读</span><br>
                                <span class="text-muted"><?= $v['date'] ?></span>
                            </p>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-4">我的邀请</h4>
                <div class="slimscroll" style="max-height: 230px;">
                    <?php foreach ($data_arr as $v) { ?>
                        <div class="media p-2 shadow-sm">
                            <img class="mr-3 rounded-circle" src="<?= $v['image'] ?>" width="40"
                                 alt="Generic placeholder image">
                            <div class="media-body">
                                <h5 class="mt-0 mb-1 text-justify"><?= $v['name'] ?></h5>
                                <span class="font-13"><?= $v['creation_time'] ?>
                            </span>
                                <?php
                                if ($v['award'] == 0) {
                                    echo '<a href="javascript:layer.msg(\'您已经在<font color=#03de01>' . $v['draw_time'] . '</font>领取了奖励点!\')" class="badge badge-primary-lighten">您已经领取奖励</a>';
                                } else {
                                    echo '<a href="?act=get&id=' . $v['id'] . '" class="badge badge-success-lighten" >点我领取邀请奖励</a>';
                                }
                                ?>
                            </div>
                            <a class="badge badge-warning ml-1 text-white mt-3">待领取：<?= $v['award'] ?></a>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <!-- end card-body -->
        </div>
        <!-- end card-->
    </div>
</div>
<!-- end row -->
<?php if (isset($conf['notice_user'])) { ?>
    <div class="col-xl-12  p-0">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-4">后台公告</h4>
                <div class="card-body p-0">
                    <?= $conf['notice_user'] ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php
include 'bottom.php';
?>
<script>
    <?php if($UserData['mobile'] == '' && ($conf['sms_switch_user'] == 1 || $conf['sms_switch_order'] == 1)){ ?>
    !function (t) {
        t.NotificationApp.send("你还没绑定手机号哦", "点击手机号码板块,绑定登陆手机号吧~", "top-right", "rgba(0,0,0,0.2)", !1)
    }(window.jQuery);
    <?php } if(reward::welfare_judge($UserData) === true){ ?>
    !function (t) {
        t.NotificationApp.send("今天还没签到哦?", "点击签到按钮领取今日的<?=$conf['currency']?>,每天都可以领取哦~", "top-right", "rgba(0,0,0,0.2)", "success", !1)
    }(window.jQuery);
    <?php  } ?>
    <?php if($conf['sms_switch_user'] == 1 || $conf['sms_switch_order'] == 1){  ?>

    function Mobile_phone_binding(mobile = '') {
        layer.alert('手机号绑定可用做登陆<br>商品购买提醒,方便售后！',
            {
                icon: 3, title: '手机号绑定', btn: ['取消', '立即绑定'],
                btn2: function (layero, index) {
                    var index = layer.prompt({
                        formType: 3,
                        value: mobile,
                        title: '请输入手机号',
                        btn: ['<span id=codemobile>开始验证</span>', '取消']
                    }, function (value, index, elem) {
                        if ($("#codemobile").text() != '开始验证') {
                            layer.alert('请打开手机短信查看验证码,耐心等待哦..');
                            return false;
                        }
                        /**
                         * 发送验证码，每日最多绑定2次
                         */
                        $("#codemobile").text('验证码发送中...');
                        $.ajax({
                            type: "post",
                            url: "ajax.php?act=Send_verification_code",
                            data: {mobile: value},
                            dataType: "json",
                            success: function (data) {
                                if (data.code >= 1) {
                                    alert(data.msg);
                                    $("#codemobile").text('发送成功');
                                    var index2 = layer.prompt({
                                        formType: 3,
                                        value: '',
                                        title: '请输入收到的短信验证码',
                                    }, function (valuer, index, elem) {
                                        $.ajax({
                                            type: "post",
                                            url: "ajax.php?act=Send_verification",
                                            data: {code: valuer},
                                            dataType: "json",
                                            success: function (res) {
                                                if (res.code >= 1) {
                                                    layer.close();
                                                    layer.alert(res.msg, {
                                                        title: '恭喜',
                                                        icon: 1,
                                                        yes: function (layero, index) {
                                                            location.reload()
                                                        }
                                                    })
                                                } else if (res.code == -1) {
                                                    layer.alert(res.msg, {title: '温馨提示', icon: 2})
                                                } else {
                                                    layer.close();
                                                    layer.alert(res.msg, {title: '温馨提示', icon: 2})
                                                }
                                            }
                                        });
                                        return false;
                                    });
                                } else {
                                    $("#codemobile").text('发送失败');
                                    layer.close(index);
                                    layer.alert(data.msg, {title: '温馨提示', icon: 2})
                                }
                            }
                        });
                        return false;
                    });
                }
            })
    }

    <?php } ?>

</script>