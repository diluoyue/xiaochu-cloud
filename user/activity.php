<?php
/**
 * 活动邀请界面
 */
$title = '邀请福利';
include 'header.php';
global $conf, $UserData;
if (isset($_QET['act']) && $_QET['act'] == 'get') { #领取发放的奖励
    reward::issue_reward($UserData, $_QET['id']);
}
$shareLink = reward::shareLink($UserData); #邀请链接生成

if ($conf['prevent_switch'] == 1) {
    $shareLink = reward::prevent($shareLink, 2); //一直生成新的 2则启用缓存
}
$DB = \Medoo\DB\SQL::DB();
$count_1 = $DB->count('invite', [
    'uid' => $UserData['id'],
]);
$count_2 = $DB->count('invite', [
    'uid' => $UserData['id'],
    'award' => 0,
]);
$data_arr = reward::Invite_statistics($UserData);
?>
<div class="row">
    <div class="col-sm-12">
        <!-- Profile -->
        <div class="card bg-white">
            <div class="card-body profile-user-box">
                <div class="row">
                    <div class="col-sm-8">
                        <div class="media">
                            <span class="float-left m-2 mr-4"><img src="<?= UserImage($UserData) ?>"
                                                                   style="height: 100px;" alt=""
                                                                   class="rounded-circle img-thumbnail"></span>
                            <div class="media-body">
                                <h4 class="mt-1 mb-1"><?= $UserData['name'] ?></h4>
                                <p class="font-13"> <?= $conf['sitename'] ?> <span
                                            class="badge badge-success-lighten ml-2">已认证</span></p>
                                <ul class="mb-0 list-inline">
                                    <li class="list-inline-item mr-3">
                                        <h5 class="mb-1"><?= $count_1 ?>人</h5>
                                        <p class="mb-0 font-13">累计邀请人数</p>
                                        <h5 class="mb-1"><?= $count_2 ?>次</h5>
                                        <p class="mb-0 font-13">累计领取奖励</p>
                                    </li>
                                </ul>
                            </div> <!-- end media-body-->
                        </div>
                    </div> <!-- end col-->

                    <div class="col-sm-4">
                        <div class="text-center mt-sm-0 mt-3 text-sm-right">
                            <button type="button" class="btn btn-danger btn-rounded btn-code"
                                    data-clipboard-target="#btn_code">
                                <i class="mdi mdi-account-edit mr-1"></i> 复制邀请链接
                            </button>
                            <p class="mt-3 font-16 text-success">
                                每邀请1个真实用户奖励<?= $conf['award'] . $conf['currency'] ?></p>
                        </div>
                    </div> <!-- end col-->
                </div> <!-- end row -->
            </div> <!-- end card-body/ profile-user-box-->
        </div><!--end profile/ card -->
    </div> <!-- end col-->
    <div class="col-xl-6">
        <div class="card text-white bg-danger overflow-hidden">
            <div class="card-body">
                <div class="toll-free-box text-center font-18 ">
                    <i class="layui-icon layui-icon-gift"></i> <span class="mr-1">专属推广链接</span><span
                            id="btn_code"><?= urldecode($shareLink) ?></span>
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
        <!-- Personal-Information -->
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="header-title mt-0 mb-3">专属邀请福利图片
                    <span class="badge badge-primary p-1" onclick="location.reload()"
                          style="cursor: pointer">换种样式 [图片<?php $rand = rand(1, 13);
                        echo $rand; ?>]</span>
                    <a href="image/api.php?url=<?= base64_encode($shareLink) ?>&ids=<?= $rand ?>&images=uid<?= $UserData['id'] ?>_<?= $rand ?>.jpg"
                       target="_blank"
                       class="badge badge-success p-1">保存图片</a>
                </h4>
                <img class="card-img-top"
                     src="image/api.php?url=<?= base64_encode($shareLink) ?>&ids=<?= $rand ?>&images=uid<?= $UserData['id'] ?>_<?= $rand ?>.jpg"
                     alt="推广图片">
            </div>
        </div>
        <!-- Personal-Information -->
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3">我的邀请</h4>
                <div class="slimscroll" style="min-height: 435px;">
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
<script src="https://cdn.bootcss.com/clipboard.js/2.0.4/clipboard.js"></script>
<?php
include 'bottom.php';
?>
<script>
    var clipboard = new ClipboardJS('.btn-code');
    clipboard.on('success', function (e) {
        layer.msg("复制成功<br>快去分享给朋友一起来领免费名片赞吧！", {icon: 1});
        e.clearSelection();
    });

    clipboard.on('error', function (e) {
        layer.msg('专属链接复制失败,请手动复制链接~', {icon: 3});
    });
</script>
