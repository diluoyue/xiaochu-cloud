<?php

use Medoo\DB\SQL;

$title = '添加/编辑公告通知';
include 'header.php';
if (!empty($_QET['id'])) {
    $DB = SQL::DB();
    $Article = $DB->get('notice', '*', ['id' => (int)$_QET['id']]);
    if (!$Article) {
        show_msg('公告->' . $_QET['id'] . '不存在', '公告->' . $_QET['id'] . '不存在请检查是否访问有误?', 3);
    }
}
$id = (empty($_QET['id']) ? -1 : $_QET['id']);
?>
<div class="row" id="App" aid="<?= $id ?>">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="admin.article.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <?= $title ?>
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">公告封面</label>
                        <div class="input-group">
                            <input type="text" lay-verify="required" lay-verType="tips" class="form-control"
                                   name="image" id="image" value="<?= ImageUrl($Article['image']) ?>"
                                   placeholder="文章图片地址"/>
                            <div class="input-group-append">
                                <span class="input-group-text" id="upload" style="cursor: pointer">上传</span>
                            </div>
                            <div class="input-group-append">
                                <span class="input-group-text"
                                      onclick="layer.alert('<img src=\''+$('#image').val()+'\' style=width:100%  />')"
                                      style="cursor: pointer;background-color: slateblue;color: white">预览</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">公告标题</label>
                        <input type="text" name="title" lay-verify="required" class="form-control"
                               value="<?= $Article['title'] ?>" placeholder="公告标题">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">浏览人数</label>
                        <input type="number" name="PV" lay-verify="required" class="form-control"
                               value="<?= $Article['PV'] ?>" placeholder="公告浏览人数">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">公告内容</label>
                        <textarea id="content" class="form-control" style="display:none"></textarea>
                        <div id="contentHtml"><?= ($Article['content'] === '' ? '' : '<div>' . $Article['content'] . '</div>') ?></div>
                        <span style="cursor:pointer;color: #0AAB89;font-size: 15px;"
                              onclick="EmptyDocs();">清空全部内容</span>
                        <span style="cursor:pointer;color: red;font-size: 15px;" onclick="HtmlDocs();">编辑原始代码</span>
                    </div>

                    <button type="submit" lay-submit lay-filter="Preserve" class="btn btn-block btn-xs btn-success">
                        <?= $id === -1 ? '发布新公告' : '保存给公告数据' ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/wangEditor.min.js"></script>
<script>
    const E = window.wangEditor;
    const editor = new E('#contentHtml');
    const $Content = $('#content');
    editor.config.uploadImgAccept = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    editor.config.uploadVideoAccept = ['mp4'];
    editor.config.uploadImgMaxSize = 10 * 1024 * 1024;
    editor.config.uploadVideoMaxSize = 100 * 1024 * 1024;
    editor.config.uploadImgMaxLength = 30;
    editor.config.zIndex = 1;
    editor.config.customUploadImg = function (resultFiles, insertImgFn) {
        var imageData = new FormData();
        $.each(resultFiles, function (key, val) {
            imageData.append("imageData" + key, val);
        });
        $.ajax({
            data: imageData,
            type: "POST",
            url: "./main.php?act=ImageUp",
            cache: false,
            contentType: false,
            processData: false,
            success: function (imageUrl) {
                if (imageUrl.code == 1) {
                    let content = '';
                    $.each(imageUrl['SrcArr'], function (key, val) {
                        insertImgFn(val['src'])
                        content += '图片：<font color=red>' + val['name'] + '</font>大小为：<font color=red>' + val['size'] + '</font><br>';
                    });
                    layer.alert(content + '<hr>Ps:图片可一次上传多张！', {
                        title: imageUrl.msg
                    });
                } else layer.msg(imageUrl.msg);
            },
            error: function () {
                layer.msg('图片上传接口异常，上传失败！');
            }
        });
    }

    editor.config.customUploadVideo = function (resultFiles, insertVideoFn) {
        var VideoData = new FormData();
        $.each(resultFiles, function (key, val) {
            VideoData.append("VideoData" + key, val);
        });
        $.ajax({
            data: VideoData,
            type: "POST",
            url: "./main.php?act=VideoUp",
            cache: false,
            contentType: false,
            processData: false,
            success: function (videoUrl) {
                if (videoUrl.code == 1) {
                    let content = '';
                    $.each(videoUrl['SrcArr'], function (key, val) {
                        insertVideoFn(val['src']);
                        content += '视频：<font color=red>' + val['name'] + '</font>大小为：<font color=red>' + val['size'] + '</font><br>';
                    });
                    layer.alert(content, {
                        title: videoUrl.msg
                    });
                } else layer.msg(videoUrl.msg);
            },
            error: function () {
                layer.msg('图片上传接口异常，上传失败！');
            }
        });
    }

    editor.config.onchange = function (html) {
        $Content.val(html);
    }
    editor.create();
    $Content.val(editor.txt.html());

    layui.use(['upload', 'form'], function () {
        var form = layui.form;
        form.on('submit(Preserve)', function (data) {
            data.field['content'] = editor.txt.html();
            const aid = $("#App").attr('aid');

            layer.alert('是否要执行当前操作？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    is = layer.msg('数据保存中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    if (aid != -1) {
                        data.field['aid'] = aid;
                    }
                    $.ajax({
                        type: "POST",
                        url: './ajax.php?act=article_msg',
                        data: data.field,
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    btn: ['继续', '返回列表'],
                                    icon: 1,
                                    btn1: function () {
                                        location.reload();
                                    },
                                    btn2: function () {
                                        location.href = './admin.article.list.php'
                                    }
                                });
                            } else {
                                layer.alert(res.msg, {
                                    icon: 2
                                });
                            }
                        },
                        error: function () {
                            layer.msg('服务器异常！');
                        }
                    });
                }
            });
        });
    });

    layui.use(['upload', 'form'], function () {
        var upload = layui.upload;
        var uploadInst = upload.render({
            elem: '#upload' //绑定元素
            ,
            url: 'ajax.php?act=image_up' //上传接口
            ,
            done: function (res, index, upload) {
                layer.msg('图片上传成功');
                $("#image").val(res.src);
            },
            error: function () {
                layer.msg('图片上传失败!')
            }
        });
    });

    function EmptyDocs() {
        editor.txt.clear();
        layer.msg('清空成功！', {
            icon: 1
        });
    }

    function HtmlDocs() {
        let Html = editor.txt.html();
        let is = layer.prompt({
            formType: 2,
            value: Html,
            maxlength: 9999999999999999,
            title: '编辑原始HTML代码',
            area: ['80vw', '80vh']
        }, function (value) {
            editor.txt.html(value);
            layer.close(is);
            layer.msg('设置成功！', {
                icon: 1
            });
        });
    }
</script>
