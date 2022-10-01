<?php
/**
 * Author：晴玖天
 * Creation：2020/4/26 16:08
 * Filename：OrderMark.php
 * 商品评价
 */
if (!defined('IN_CRONLITE')) die;

if (empty($_QET['id'])) show_msg('温馨提示', '请填写完整!<br>请点击右上角关闭当前窗口', 1, false, false);
$UserData = login_data::user_data();
if (!$UserData) show_msg('温馨提示', '请先完成登陆哦<br>请点击右上角关闭当前窗口', 1, false, false);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= '订单评价 - ' . $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/layui/css/layui.css"/>
</head>
<body>
<style>
    #grade span {
        height: 2em;
        line-height: 2em;
        display: inline-block
    }

    .Picture {
        width: 100%;
        border-radius: 0.3em;
        margin-bottom: 0.5em;
        padding: 0.5em;
    }

    .Picture img {
        margin: 0.3em;
        box-shadow: 3px 3px 16px #eee;
        border-radius: 0.5em;
        width: 50px;
        height: 50px;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space8">
        <div class="layui-card">
            <div class="layui-card-body">
                <div id="grade"></div>
                <br>
                您对本次购物满意吗,请为本次购物打分?
                <hr>
                <div style="text-align: left;height: 3em;line-height: 2.5em">
                    <img src="<?= UserImage($UserData) ?>" style="width: 25px;height: 25px;border-radius: 30em"/>
                    您好,<?= $UserData['name'] ?>
                </div>
                <div class="layui-form layui-form-pane">
                    <input type="hidden" name="id" value="<?= $_QET['id'] ?>">
                    <input type="hidden" name="grade" value="6">
                    <div class="layui-form-item layui-form-text">
                        <label class="layui-form-label">请在下方填写您对此商品的评价</label>
                        <div class="layui-input-block">
                            <textarea name="content" lay-verify="required" placeholder=" 可从多个角度评价商品,可以帮助更多想买的人"
                                      class="layui-textarea"></textarea>
                        </div>
                    </div>

                    <div class="layui-form-item layui-form-text">
                        <label class="layui-form-label">描述图片
                            <button id="upload_Picture" class="layui-btn layui-btn-xs layui-btn-danger">上传</button>
                            <button onclick="QuicklyEditProductPictures()"
                                    class="layui-btn layui-btn-xs layui-btn-primary">编辑
                            </button>
                        </label>
                        <div class="layui-input-block">
                            <div class="Picture"
                                 data-img="">
                            </div>
                        </div>
                    </div>

                    <button class="layui-btn" lay-submit lay-filter="mark"
                            style="position: fixed;right: 1em;bottom: 1em;background-color: dodgerblue">提交商品评价
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $cdnpublic; ?>jquery/3.4.1/jquery.min.js"></script>
<script src="<?php echo $cdnserver; ?>assets/layui/layui.all.js"></script>
<script>
    layui.use(['rate', 'form', 'upload'], function () {
        var rate = layui.rate;
        var form = layui.form;
        rate.render({
            elem: '#grade'
            , value: 5
            , text: true
            , length: 5
            , setText: function (value) {
                var arrs = {
                    '1': '极差'
                    , '2': '差评'
                    , '3': '中评'
                    , '4': '好评'
                    , '5': '极好'
                };
                this.span.text(arrs[value] || (value + "星"));
                $("input[name='grade']").val(value);
            }
        });

        var upload = layui.upload;
        var uploadInst = upload.render({
            elem: '#upload_Picture'
            , url: '/user/ajax.php?act=image_up'
            , acceptMime: 'image/*'
            , accept: 'images'
            , done: function (res, index, upload) {
                layer.msg(res.msg);
                AddImages(res.src);
            }
            , error: function () {
                layer.msg('图片上传失败!');
            }
        });

        form.on('submit(mark)', function (data) {
            image = $(".Picture").attr('data-img');
            a = image.split('|');
            if (a.length > 9) {
                layer.msg('最多一次提交9张图！', {icon: 2});
                return false;
            }
            data.field['image'] = image;
            layer.open({
                title: '提交确认',
                content: '是否要提交评价内容,提交后内容不可修改!',
                btn: ['提交', '取消'],
                icon: 3,
                btn1: function (layero, index) {
                    $.ajax({
                        type: "post",
                        url: "/main.php?act=OrderList&type=QueryMark",
                        data: data.field,
                        dataType: "json",
                        success: function (data) {
                            if (data.code == 1) {
                                layer.alert(data.msg, {
                                    icon: 1, title: '温馨提示', end: function (layero, index) {
                                        window.history.go(-1);
                                    }
                                });
                            } else {
                                layer.alert(data.msg, {
                                    icon: 2, title: '温馨提示', end: function (layero, index) {
                                        window.history.go(-1);
                                    }
                                });
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
            $(".Picture").html(content);
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
            area: ['350px', '350px']
        }, function (value, index, elem) {
            var content = value.split("\n");
            var content = content.join('|');
            $(".Picture").attr('data-img', content);
            RenderProductPictures();
            layer.close(index);
        });
    }

    function RenderProductPictures() {
        var image = $(".Picture").attr('data-img');
        if (image == '') {
            $(".Picture").text('留不显示图片哦');
        } else {
            arr = image.split('|');
            content = '';
            $.each(arr, function (key, val) {
                content += '<img src="' + val + '" onclick="DeletePicture(' + key + ')" />';
            });
            $(".Picture").html(content);
        }
    }

    function DeletePicture(id) {
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
