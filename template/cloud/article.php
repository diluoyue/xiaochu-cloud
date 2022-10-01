<?php
/**
 * 文章列表
 */

use Medoo\DB\SQL;

if (!defined('IN_CRONLITE')) die;
global $conf, $_QET;
$title = '公告通知 - ' . $conf['sitename'];
$DB = SQL::DB();
$SQL = [
    'state' => 1,
    'ORDER' => [
        'id' => 'DESC'
    ]
];
$User = login_data::user_data();
if (!$User) {
    $SQL['type[!]'] = 2;
}
$notice_user = $DB->select('notice', '*', $SQL);
include 'template/cloud/header.php';
if (!empty($_QET['id'])) {
    $state = 1;
    $SQL = [
        'state' => 1,
        'ORDER' => [
            'id' => 'DESC'
        ],
        'id' => (int)$_QET['id'],
    ];
    if (!$User) {
        $SQL['type[!]'] = 2;
    }
    $data = $DB->get('notice', '*', $SQL);
    if (!$data) {
        show_msg('温馨提示', '当前搜索的公告通知不存在！', 4);
    }
}
?>
<style>
    .layui-shadow {
        margin: 1em auto;
        box-shadow: 0px 0px 4px #eee;
        transition: all 0.3;
        cursor: pointer;
        text-decoration: none;
        display: block;
    }

    .layui-shadow:hover {
        box-shadow: 0px 0px 16px #eee;
        background-color: #eee;
    }

    .layui-over {
        height: 38em;
        overflow: hidden;
        overflow-y: auto
    }

    .imagtes img {
        max-width: 100%;
        display: block;
        margin: auto;
    }
</style>
<div class="layui-container" style="opacity: 0.9">
    <div class="layui-row">
        <?php if (!empty($state)) { ?>
            <div class="layui-col-md8" style="padding: 0.2em;color: #111;">
                <div class="layui-card">
                    <div class="layui-card-header" style="height: auto;font-weight: 800;font-size: 1.2em">
                        <?= $data['title'] ?>
                    </div>
                    <div class="layui-card-body imagtes">
                        <?= $data['content'] ?>
                        <center style="margin:6em auto 1em;color: #666">发布时间：<?= $data['date'] ?><br>
                            <a href="?mod=article" class="layui-icon layui-icon-home"
                               style="margin-top: 1em;display: block;width: 3em;height: 3em;"></a>
                        </center>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="<?= $state == 1 ? 'layui-col-md4 layui-hide-xs' : 'layui-col-md12' ?>"
             style="padding: 0.2em;color: #111">
            <div class="layui-card">
                <div class="layui-card-header">文章列表 - 共<?= count($notice_user) ?>篇文章</div>
                <div class="layui-card-body">
                    <div class="layui-card">
                        <div class="layui-card-body" style="padding: 0">
                            <div class="layui-card layui-text <? echo $state == 1 ? 'layui-over' : '' ?>">
                                <?php foreach ($notice_user as $v) { ?>
                                    <a href="?mod=article&id=<?= $v['id'] ?>"
                                       class="layui-card-header layui-elip layui-shadow"
                                       style="height: auto;color: #000000">
                                        <?= $v['title'] ?>
                                        <div class="layui-word-aux" style="float: right"><?= $v['date'] ?></div>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'template/cloud/bottom.php'; ?>
