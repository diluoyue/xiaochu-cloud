<?php

use Medoo\DB\SQL;

$title = '添加/编辑用户等级';
include 'header.php';
$DB = SQL::DB();
if (!empty($_QET['id'])) {
    $Level = $DB->get('price', '*', ['mid' => (int)$_QET['id']]);
    if (!$Level) {
        show_msg('等级->' . $_QET['id'] . '不存在', '等级->' . $_QET['id'] . '不存在请检查是否访问有误?', 3);
    }
}
$Mid = (empty($_QET['id']) ? -1 : $_QET['id']);

$List = $DB->select('profit_rule', ['id', 'name'], ['state' => 1]);
?>
<div class="row" id="App" mid="<?= $Mid ?>">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="admin.level.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <?= $title ?>
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">
                    <div class="form-group mb-3">
                        <label style="font-weight: 500">加价规则 <a href="admin.increasePrice.add.php"
                                                                target="_blank">「创建」</a>
                        </label>
                        <select name="rule" lay-filter="required">
                            <option <?= $Level['rule'] == -1 ? 'selected' : '' ?> value="-1">不配置加价规则</option>
                            <?php foreach ($List as $value): ?>
                                <option <?= $Level['rule'] == $value['id'] ? 'selected' : '' ?>
                                        value="<?= $value['id'] ?>"><?= $value['name'] ?? '规则(' . $value['id'] . ')' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 500">等级名称</label>
                        <input type="text" name="name" lay-verify="required" class="form-control"
                               value="<?= $Level['name'] ?>" placeholder="等级的名称,建议不要起太长">
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 500">等级售价(元)</label>
                        <input type="text" name="money" lay-verify="required" class="form-control"
                               value="<?= $Level['money'] ?>" placeholder="请输入等级购买价格,用户可补差价升级">
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 500">加价比例(%)</label>
                        <input type="number" name="priceis" lay-verify="required" class="form-control"
                               value="<?= $Level['priceis'] ?>" placeholder="基于商品成本进行加价！">
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 500">兑换倍数</label>
                        <input type="number" name="pointsis" lay-verify="required" class="form-control"
                               value="<?= $Level['pointsis'] ?>" placeholder="基于商品成本进行兑换倍数计算">
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 500">绝对利润(%)</label>
                        <input type="number" name="ActualProfit" lay-verify="required" class="form-control"
                               value="<?= $Level['ActualProfit'] ?>" placeholder="用户下级购买商品时，可分成的绝对利润百分比">
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 500">分成阈值(%)</label>
                        <input type="number" name="ProfitThreshold" lay-verify="required" class="form-control"
                               value="<?= $Level['ProfitThreshold'] ?>" placeholder="当向上分成时,分成金额低于此百分比则不会继续分成！">
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 500">等级公告</label>
                        <textarea id="content" class="form-control" style="display:none"></textarea>
                        <div id="contentHtml"><?= ($Level['content'] === '' ? '' : '<div>' . $Level['content'] . '</div>') ?></div>
                    </div>

                    <button type="submit" lay-submit lay-filter="Preserve" class="btn btn-block btn-xs btn-success">
                        <?= $Mid === -1 ? '添加一个新的等级' : '保存等级信息' ?>
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
            const Mid = $("#App").attr('mid');

            layer.alert('是否要执行当前操作？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    is = layer.msg('数据保存中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    if (Mid != -1) {
                        data.field['mid'] = Mid;
                    }
                    $.ajax({
                        type: "POST",
                        url: './ajax.php?act=level_add',
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
                                        location.href = './admin.level.list.php'
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
</script>
