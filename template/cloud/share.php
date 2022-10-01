<?php
/**
 * 网站分享
 */

use Medoo\DB\SQL;

if (!defined('IN_CRONLITE')) die;
global $conf;
$DB = SQL::DB();
$rand = rand(1, 13);
$UserData = login_data::user_data();
$title = '分享有礼 - ' . $conf['sitename'];
if ($UserData <> false) {
    $shareLink = reward::shareLink($UserData); #邀请链接生成
    if ($conf['prevent_switch'] == 1) {
        $shareLin = reward::prevent($shareLink, 2);
    }
    $count_1 = $DB->count('invite', [
        'uid' => $UserData['id']
    ]);
    $count_2 = $DB->count('invite', [
        'uid' => $UserData['id'],
        'award' => 0
    ]);
    $data_arr = reward::Invite_statistics($UserData);
    $shareLinks = 'user/image/api.php?url=' . base64_encode($shareLink) . '&ids=' . $rand . '&images=uid' . $UserData['id'] . '_' . $rand . '.jpg';
} else {
    $shareLinks = 'user/image/api.php?url=' . base64_encode(href()) . '&ids=' . $rand . '&images=推广图片_' . $rand . '.jpg';
}
include 'template/cloud/header.php';
?>
<div class="content__inner content__inner--sm">
    <div class="card new-contact">
        <div class="new-contact__header p-0">
            <img class="card-img-top" src="<?= $shareLinks ?>"
                 alt="推广图片">
        </div>
        <center>
            <h5 class="header-title mt-0 mb-3">专属邀请福利图片 <br>
                <span class="badge badge-primary p-1" onclick="location.reload()"
                      style="cursor: pointer">换种样式 [图片<?php $rand = rand(1, 13);
                    echo $rand; ?>]</span>
                <a href="<?= $shareLinks ?>" target="_blank"
                   class="badge badge-success p-1">保存图片</a>
            </h5>
        </center>
    </div>
    <?php if ($UserData <> false) { ?>
        <div class="card">
            <div class="card-body">
                <h5 class="header-title mb-3">我的邀请 - 前28</h5>
                <div class="slimscroll" style="min-height: 435px;">
                    <?php foreach ($data_arr as $v) { ?>
                        <div class="media p-2 shadow-sm">
                            <img class="mr-3 rounded-circle" lay-src="<?= $v['image'] ?>" width="40"
                                 alt="Generic placeholder image">
                            <div class="media-body">
                                <h5 class="mt-0 mb-1 text-justify"><?= $v['name'] ?></h5>
                                <span class="font-13"><?= $v['creation_time'] ?>
                                </span>
                                <?php
                                if ($v['award'] == 0) {
                                    echo '<a href="javascript:layer.alert(\'您已经在<font color=#03de01>' . $v['draw_time'] . '</font>领取了奖励点!\')" class="badge badge-primary-lighten">您已经领取奖励</a>';
                                } else {
                                    echo '<a href="user/activity.php?act=get&id=' . $v['id'] . '" class="badge badge-success-lighten" >点我领取邀请奖励</a>';
                                }
                                ?>
                            </div>
                            <a class="badge badge-warning ml-1 text-white mt-3">待领取：<?= $v['award'] ?></a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php include 'template/cloud/bottom.php'; ?>
<script>
    layui.use('flow', function () {
        var flow = layui.flow;
        flow.lazyimg();
    });
</script>