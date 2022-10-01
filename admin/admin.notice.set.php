<?php

/**
 * 添加文章
 */
$title = '公告设置';
include 'header.php';
global $conf;
?>
<div class="row">
    <div class=" col-md-12 col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="mb-3 header-title text-success"><?= $title ?></h3>
                        <form class="form-horizontal layui-form">

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">首页顶部公告</label>
                                <textarea id="notice_top" class="form-control" style="display:none"></textarea>
                                <div id="contentHtml1"><?= ($conf['notice_top'] === '' ? '' : '<div>' . $conf['notice_top'] . '</div>') ?></div>
                                <span style="cursor:pointer;color: #0AAB89;font-size: 15px;"
                                      onclick="EmptyDocs('notice_top');">清空全部内容</span>
                                <span style="cursor:pointer;color: red;font-size: 15px;"
                                      onclick="HtmlDocs('notice_top');">编辑原始代码</span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">首页查单公告</label>
                                <textarea id="notice_check" class="form-control" style="display:none"></textarea>
                                <div id="contentHtml2"><?= ($conf['notice_check'] === '' ? '' : '<div>' . $conf['notice_check'] . '</div>') ?></div>
                                <span style="cursor:pointer;color: #0AAB89;font-size: 0.5em;"
                                      onclick="EmptyDocs('notice_check');">清空全部内容</span>
                                <span style="cursor:pointer;color: red;font-size: 0.5em;"
                                      onclick="HtmlDocs('notice_check');">编辑原始代码</span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">全局底部公告</label>
                                <textarea id="notice_bottom" class="form-control" style="display:none"></textarea>
                                <div id="contentHtml3"><?= ($conf['notice_bottom'] === '' ? '' : '<div>' . $conf['notice_bottom'] . '</div>') ?></div>
                                <span style="cursor:pointer;color: #0AAB89;font-size: 0.5em;"
                                      onclick="EmptyDocs('notice_bottom');">清空全部内容</span>
                                <span style="cursor:pointer;color: red;font-size: 0.5em;"
                                      onclick="HtmlDocs('notice_bottom');">编辑原始代码</span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">用户后台公告</label>
                                <textarea id="notice_user" class="form-control" style="display:none"></textarea>
                                <div id="contentHtml4"><?= ($conf['notice_user'] === '' ? '' : '<div>' . $conf['notice_user'] . '</div>') ?></div>
                                <span style="cursor:pointer;color: #0AAB89;font-size: 0.5em;"
                                      onclick="EmptyDocs('notice_user');">清空全部内容</span>
                                <span style="cursor:pointer;color: red;font-size: 0.5em;"
                                      onclick="HtmlDocs('notice_user');">编辑原始代码</span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">首页公告弹窗</label>
                                <textarea id="PopupNotice" class="form-control" style="display:none"></textarea>
                                <div id="contentHtml5"><?= ($conf['PopupNotice'] === '' ? '' : '<div>' . $conf['PopupNotice'] . '</div>') ?></div>
                                <span style="cursor:pointer;color: #0AAB89;font-size: 0.5em;"
                                      onclick="EmptyDocs('PopupNotice');">清空全部内容</span>
                                <span style="cursor:pointer;color: red;font-size: 0.5em;"
                                      onclick="HtmlDocs('PopupNotice');">编辑原始代码</span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">客服界面提示公告</label>
                                <textarea id="ServiceTips" class="form-control" style="display:none"></textarea>
                                <div id="contentHtml6"><?= ($conf['ServiceTips'] === '' ? '' : '<div>' . $conf['ServiceTips'] . '</div>') ?></div>
                                <span style="cursor:pointer;color: #0AAB89;font-size: 0.5em;"
                                      onclick="EmptyDocs('ServiceTips');">清空全部内容</span>
                                <span style="cursor:pointer;color: red;font-size: 0.5em;"
                                      onclick="HtmlDocs('ServiceTips');">编辑原始代码</span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">IP统计代码[全局js代码也可以放到里面，如音乐播放器等！]</label>
                                <textarea id="statistics" class="form-control" style="display:none"></textarea>
                                <div id="contentHtml7"><?= ($conf['statistics'] === '' ? '' : '<div>' . $conf['statistics'] . '</div>') ?></div>
                                <span style="cursor:pointer;color: #0AAB89;font-size: 0.5em;"
                                      onclick="EmptyDocs('statistics');">清空全部内容</span>
                                <span style="cursor:pointer;color: red;font-size: 0.5em;"
                                      onclick="HtmlDocs('statistics');">编辑原始代码</span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="example-input-normal" style="font-weight: 500">主机后台公告通知</label>
                                <textarea id="HostAnnounced" class="form-control" style="display:none"></textarea>
                                <div id="contentHtml8"><?= ($conf['HostAnnounced'] === '' ? '' : '<div>' . $conf['HostAnnounced'] . '</div>') ?></div>
                                <span style="cursor:pointer;color: #0AAB89;font-size: 0.5em;"
                                      onclick="EmptyDocs('HostAnnounced');">清空全部内容</span>
                                <span style="cursor:pointer;color: red;font-size: 0.5em;"
                                      onclick="HtmlDocs('HostAnnounced');">编辑原始代码</span>
                            </div>

                            <button type="submit" lay-submit lay-filter="Notification_set"
                                    class="btn btn-block btn-xs btn-success">保存内容
                            </button>
                        </form>

                        <span class="mt-3" style="display: block">若不懂代码,可点击：<span class="badge badge-primary-lighten"
                                                                                  id="query_xc" style="cursor: pointer">获取小储系统的公告信息</span><span
                                    class="badge badge-info-lighten" id="query_ch"
                                    style="cursor: pointer">获取彩虹代刷的公告信息</span></span>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>
<script src="../assets/js/wangEditor.min.js"></script>
<script src="../assets/admin/js/notice.js?vs=<?= $accredit['versions'] ?>"></script>
