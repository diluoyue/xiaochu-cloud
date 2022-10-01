<?php
/**
 * Author：晴玖天
 * Creation：2020/4/26 16:08
 * Filename：tickets_new.php
 * 发起新的工单
 */

use Medoo\DB\SQL;

include '../includes/fun.global.php';
global $_QET, $conf, $cdnserver, $cdnpublic;
$UserData = login_data::user_data();
if (!$UserData) show_msg('温馨提示', '请先完成登陆哦<br>请点击右上角关闭当前窗口', 1, false, false);

$DB = SQL::DB();
if (!empty((int)$_QET['id'])) {
    $OrderId = (int)$_QET['id'];
    $Order = $DB->get('order', [
        '[>]tickets' => 'order'
    ], [
        'tickets.id',
    ], ['order.id' => $OrderId, 'order.uid' => $UserData['id']]);
    //此处做了一个中转验证,若已经提交过的,直接显示工单对话界面！
    if ($Order['id'] <> '') header("Location:tickets_code.php?id=" . $Order['id']);
} else $OrderId = '-1';

$TicketsClass = explode(',', $conf['TicketsClass']);
$TicketsOrder = $DB->select('order', [
    '[>]goods' => 'gid',
], [
    'order.id',
    'goods.name',
    'order.addtitm',
    'order.order'
], [
    'uid' => $UserData['id'],
]);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <link rel="stylesheet" type="text/css" href="../assets/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="/assets/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="../../assets/layui/css/layui.css"/>
</head>
<body>
<div class="layui-fluid" style="padding: 0.5em">
    <div class="layui-row layui-col-space8">
        <div class="layui-card">
            <div class="layui-card-body">
                <div style="text-align: left;height: 3em;line-height: 2.5em">
                    <img src="<?= UserImage($UserData) ?>" style="width: 25px;height: 25px;border-radius: 30em"/>
                    您好,<?= $UserData['name'] ?>
                </div>
                <div class="layui-form layui-form-pane">
                    <div class="layui-form-item">
                        <label class="layui-form-label">工单标题</label>
                        <div class="layui-input-block">
                            <input type="text" name="title" lay-verify="required" placeholder="请输入工单标题"
                                   autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">工单类型</label>
                        <div class="layui-input-block">
                            <select name="class" lay-verify="required">
                                <?php foreach ($TicketsClass as $v) { ?>
                                    <option value="<?= $v ?>"><?= $v ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">相关订单</label>
                        <div class="layui-input-block">
                            <select name="order">
                                <option>不选择相关订单</option>
                                <?php foreach ($TicketsOrder as $v) { ?>
                                    <option value="<?= $v['order'] ?>"
                                        <?= ($v['id'] == $OrderId ? 'selected' : '') ?>><?= $v['id'] . ' - ' . $v['name'] . ' - ' . $v['addtitm'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">在线时间</label>
                        <div class="layui-input-block">
                            <select name="time">
                                <option>任何时间</option>
                                <option>每日9:00 - 18:00</option>
                                <option>时间不定</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item layui-form-text">
                        <div class="layui-input-block">
                            <textarea name="content" lay-verify="required" placeholder="请详细描述您所遇到的问题!"
                                      class="layui-textarea editor"></textarea>
                        </div>
                    </div>


                    <button class="layui-btn" lay-submit lay-filter="mark"
                            style="position: fixed;right: 1em;bottom: 1em;background-color: dodgerblue">提交工单内容
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/jquery-3.4.1.min.js"></script>
<script src="/assets/layui/layui.all.js"></script>

<link rel="stylesheet" href="/assets/easyeditor/css/easyeditor.css">
<link rel="stylesheet" type="text/css" href="/assets/easyeditor/css/fonticon.css"/>
<script src="/assets/easyeditor/js/marked.min.js" type="text/javascript" charset="utf-8"></script>

<script src="../../assets/js/jquery-3.4.1.min.js"></script>
<script src="../../assets/layui/layui.all.js"></script>

<link rel="stylesheet" href="../../assets/easyeditor/css/easyeditor.css">
<link rel="stylesheet" type="text/css" href="../../assets/easyeditor/css/fonticon.css"/>
<script src="../../assets/easyeditor/js/marked.min.js" type="text/javascript" charset="utf-8"></script>

<script src="../assets/js/jquery-3.4.1.min.js"></script>
<script src="../assets/layui/layui.all.js"></script>

<link rel="stylesheet" href="../assets/easyeditor/css/easyeditor.css">
<link rel="stylesheet" type="text/css" href="../assets/easyeditor/css/fonticon.css"/>
<script src="../assets/easyeditor/js/marked.min.js" type="text/javascript" charset="utf-8"></script>

<script>
    layui.config({
        base: '/assets/easyeditor/mods/'
    }).extend({
        easyeditor: 'easyeditor'
    }).use(['form', 'easyeditor'], function () {
        var easyeditor = layui.easyeditor;
        var form = layui.form;

        easyeditor.init({
            elem: '.editor'
            , uploadUrl: '/user/ajax.php?act=image_up'
            , videoUploadUrl: '/user/ajax.php?act=image_up'
            , videoUploadSize: 10240
            , uploadSize: 1024
            , style: 'fangge'
            , codeStyle: 'layuiCode'
            , codeSkin: 'notepad'
            , buttonColor: '#292acf'
            , hoverBgColor: 'rgba(220,216,209,0.2)'
            , hoverColor: '#000000'
        });

        form.on('submit(mark)', function (data) {
            data.field['image'] = $(".Picture").attr('data-img');
            layer.open({
                title: '提交确认',
                content: '是否要提交此工单内容,提交后请耐心等待客服回复!',
                btn: ['提交', '取消'],
                icon: 3,
                btn1: function (layero, index) {
                    $.ajax({
                        type: "post",
                        url: "/user/ajax.php?act=Tickets&type=New",
                        data: data.field,
                        dataType: "json",
                        success: function (data) {
                            if (data.code == 1) {
                                layer.alert(data.msg, {
                                    icon: 1, title: '温馨提示', end: function (layero, index) {
                                        location.reload();
                                        var index = parent.layer.getFrameIndex(window.name);
                                        parent.layer.close(index);
                                    }
                                });
                            } else {
                                layer.msg(data.msg, {icon: 2});
                            }
                        },
                        error: function () {
                            layer.alert('加载失败！');
                        }
                    });
                }
            });
        });
    });

    function AddImages(image) {
        var images = $(".Picture").attr('data-img');
        if (images == '') {
            $(".Picture").attr('data-img', image);
        } else {
            $(".Picture").attr('data-img', images + '|' + image);
        }
        RenderProductPictures();
    }

    function RenderProductPictures() {
        var image = $(".Picture").attr('data-img');
        if (image == '') {

        } else {
            arr = image.split('|');
            content = '';
            $.each(arr, function (key, val) {
                content += '<img src="' + val + '" onclick="DeletePicture(' + key + ')" />';
            });
            $(".Picture").html(content)
        }
    }

    function QuicklyEditProductPictures() {
        var images = $(".Picture").attr('data-img');
        var content = images.split('|');
        var content = content.join("\n");
        layer.prompt({
            formType: 2,
            value: content,
            title: '一行一张图片',
            maxlength: 99999999999,
            area: ['350px', '350px'] //自定义文本域宽高
        }, function (value, index, elem) {
            var content = value.split("\n");
            var content = content.join('|');
            $(".Picture").attr('data-img', content);
            RenderProductPictures();
            layer.close(index);
        });
    }

    function RenderProductPictures() { //商品图渲染
        var image = $(".Picture").attr('data-img');
        if (image == '') {
            $(".Picture").text('留不显示图片哦');
        } else {
            arr = image.split('|');
            content = '';
            $.each(arr, function (key, val) {
                content += '<img src="' + val + '" onclick="DeletePicture(' + key + ')" />';
            });
            $(".Picture").html(content)
        }
    }

    function DeletePicture(id) { //删除图片
        var images = $(".Picture").attr('data-img');
        if (images == '') {
            layer.msg('一张图都没有');
            return false;
        } else {
            layer.open({
                title: '操作确认',
                content: '是否要删除第' + ((id - 0) + 1) + '张商品详情图？',
                btn: ['取消', '确认删除'],
                icon: 3,
                btn2: function (layero, index) {
                    arr = images.split('|');
                    arr.splice(id, 1);
                    $(".Picture").attr('data-img', arr.join('|'));
                    RenderProductPictures();
                    layer.close();
                }
            });
        }
    }
</script>
</body>
</html>
