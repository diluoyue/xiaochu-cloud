<?php

/**
 * 新增分类
 */

use Medoo\DB\SQL;

$title = '添加/编辑分类';
include 'header.php';
if (!empty($_QET['cid'])) {
    $DB = SQL::DB();
    $Class = $DB->get('class', '*', ['cid' => (int)$_QET['cid']]);
    if (!$Class) {
        show_msg('分类->' . $_QET['cid'] . '不存在', '分类->' . $_QET['cid'] . '不存在请检查是否访问有误?', 3);
    }
}
$Cid = ($_QET['cid'] ?? -1);
?>
<div class="row" id="App" cid="<?= $Cid ?>">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="./admin.class.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <?php if ($Cid >= 1) { ?>
                    <a title="查看商品" href="./admin.goods.list.php?cid=<?= $Cid ?>" class="badge badge-success mr-1">
                        <i class="layui-icon layui-icon-cart-simple"></i></a>
                <?php } ?>
                <?= $title ?>
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">分类状态</label>
                        <select class="custom-select mt-3" name="state">
                            <option value="1" <?= $Class['state'] == 1 ? 'selected' : null ?>>显示分类</option>
                            <option value="2" <?= $Class['state'] == 2 ? 'selected' : null ?>>隐藏分类</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">分类名称</label>
                        <input type="text" name="name" lay-verify="required" class="form-control"
                               value="<?= $Class['name'] ?>" placeholder="分类的名称,建议不要起太长">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">分类图片</label>
                        <div class="input-group">
                            <input type="text" name="image" lay-verify="required" lay-verType="tips"
                                   class="form-control" id="image"
                                   value="<?= $Cid === -1 ? ROOT_DIR . 'assets/img/sc.jpg' : $Class['image'] ?>"
                                   placeholder="分类的图片,推荐放好看的图片"/>
                            <div class="input-group-append">
                                <span class="input-group-text" id="upload" style="cursor: pointer">上传</span>
                            </div>
                            <div class="input-group-append">
                                <span class="input-group-text"
                                      onclick="layer.alert('<img src=\''+$('#image').val()+'\' style=width:100%  />')"
                                      style="cursor: pointer;background-color: slateblue;color: white">预览</span>
                            </div>
                            <div class="input-group-append">
                                <a href="http://cloud.79tian.com/s/e4KSo" target="_blank" class="input-group-text"
                                   style="cursor: pointer;background-color: #a8f46b;color: white">图库</a>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">可见等级</label>
                        <input type="number" name="grade" lay-verify="required" class="form-control"
                               value="<?= $Class['grade'] ?>" placeholder="多少级的用户才可以看到此分类？">
                        <div style="font-size:14px;color: #444444;margin-top: 5px;">
                            设置1，则代表未登录用户和所有1级用户均可看见此分类，如果填写2，则代表2级及以上的用户才可看到此分类，以及分类下的商品！
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">分类公告</label>
                        <textarea id="content" class="form-control" style="display:none"></textarea>
                        <div id="contentHtml"><?= $Class['content'] ?></div>
                        <span style="cursor:pointer;color: #0AAB89;font-size: 15px;"
                              onclick="EmptyDocs();">清空全部内容</span>
                        <span style="cursor:pointer;color: red;font-size: 15px;" onclick="HtmlDocs();">编辑原始代码</span>
                    </div>

                    <button type="submit" lay-submit lay-filter="Preserve" class="btn btn-block btn-xs btn-success">
                        <?= $Cid === -1 ? '添加一个新的分类' : '保存分类信息' ?>
                    </button>
                </div>
                <span class="mt-3" style="display: block">
                    官方免费商品图库
                    <a href="http://cloud.79tian.com/s/e4KSo" target="_blank">进入</a>
                    存储海量商品图片
                </span>
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
            const Cid = $("#App").attr('cid');

            layer.alert('是否要执行当前操作？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    is = layer.msg('数据保存中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    if (Cid !== -1) {
                        data.field['cid'] = Cid;
                    }
                    $.ajax({
                        type: "POST",
                        url: './ajax.php?act=add_category',
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
                                        location.href = './admin.class.list.php'
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

        var upload = layui.upload;
        upload.render({
            elem: '#upload',
            url: 'ajax.php?act=image_up',
            done: function (res) {
                layer.msg('图片上传成功');
                $("input[name='image']").val(res.src);
                $("#image_base").attr('src', res.src);
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
