<?php
/**
 * Author：晴玖天
 * Creation：2020/4/26 22:07
 * Filename：tickets_code.php
 * 工单详情界面
 */

use Medoo\DB\SQL;

$protect_user = true;
include '../includes/fun.global.php';
global $sex, $cdnserver;
if (empty($_QET['id'])) show_msg('温馨提示', '请填写完整!<br>请点击右上角关闭当前窗口', 1, false, false);
$DB = SQL::DB();
$UserData = login_data::login_verify();
$Tickets = $DB->get('tickets', '*', ['id' => (int)$_QET['id'], 'uid' => $UserData['id']]);
if (!$Tickets) show_msg('温馨提示', '工单不存在！', 1, false, false);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="../assets/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="/assets/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="../../assets/layui/css/layui.css"/>
</head>
<body>
<div class="layui-fluid" style="padding: 0.5em">
    <div class="layui-row layui-col-space8">
        <div class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header">
                    沟通记录<span id="count"></span> <a href="javascript:location.reload();"
                                                    class="layui-badge layui-bg-orange layui-icon layui-icon-refresh"></a>
                    <div class="layui-layout-right" style="margin-right: 0.5em;">工单状态：<span id="state"></span></div>
                </div>
                <div class="layui-card-body">
                    <div class="layui-card">
                        <div class="layui-card-header">
                            <img src="<?= UserImage($UserData) ?>" style="width: 20px;height: 20px;"/> <span
                                    style="color:#030707"><?= $UserData['name'] ?></span>
                            <span class="layui-layout-right"
                                  style="color: #ccc;font-size: 0.9em"><?= $Tickets['addtime'] ?></span>
                        </div>
                        <div class="layui-card-body" style="font-size: 1em;padding: 0.2em">
                            <div class="easyeditor-content"><?= $Tickets['content'] ?></div>
                            <hr>
                            <div style="color:#666;font-size: 0.8em;">
                                类型：<span id="class"></span><br>
                                相关订单：<span id="order"></span><br>
                                在线时间段：<span id="time"></span>
                            </div>
                        </div>
                    </div>
                    <div id="message"></div>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12" id="Submit" style="display: none">
            <div class="layui-card">
                <input type="hidden" name="grade" value="5">
                <div class="layui-form layui-form-pane">
                    <input type="hidden" name="id" value="<?= $_QET['id'] ?>">
                    <textarea id="L_content" name="content" style="height: 300px;margin-bottom: 4em;" required
                              lay-verify="required"
                              placeholder="请输入需要补充的地方"
                              class="layui-textarea editor"></textarea>
                    <button class="layui-btn" lay-submit lay-filter="tickets"
                            style="position: fixed;right: 1em;bottom: 1em;background-color: dodgerblue;width: 6em">提交信息
                    </button>
                    <button class="layui-btn" onclick="finish()"
                            style="position: fixed;right: 1em;bottom: 1em;background-color: coral;right: 8em">完结工单
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
    }).use(['easyeditor', 'form'], function () {
        var easyeditor = layui.easyeditor;
        var form = layui.form;

        easyeditor.init({
            elem: '.editor'
            , uploadUrl: '/user/ajax.php?act=image_up'
            , videoUploadSize: 10240
            , uploadSize: 1024
            , style: 'fangge'
            , codeStyle: 'layuiCode'
            , codeSkin: 'notepad'
            , buttonColor: '#292acf'
            , hoverBgColor: 'rgba(220,216,209,0.2)'
            , hoverColor: '#000000'
        });

        form.on('submit(tickets)', function (data) {
            layer.open({
                title: '提交确认',
                content: '您若提交补充信息后,平台客服会第一时间收到您的反馈!',
                btn: ['提交', '取消'],
                icon: 3,
                btn1: function (layero, index) {
                    $.ajax({
                        type: "post",
                        url: "/user/ajax.php?act=Tickets&type=Supplementary",
                        data: data.field,
                        dataType: "json",
                        success: function (data) {
                            if (data.code == 1) {
                                layer.alert(data.msg, {
                                    icon: 1, title: '温馨提示', end: function (layero, index) {
                                        $("#L_content").val('');
                                        location.reload();
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

    function details() {
        layer.load(2, {time: 99999});
        $.ajax({
            type: "post",
            url: "/user/ajax.php?act=Tickets&type=details&id=" + $("input[name='id']").val(),
            dataType: "json",
            success: function (data) {
                layer.closeAll();
                if (data.code == 1) {
                    var content = '';
                    $.each(data.data, function (key, val) {
                        content += '<div class="layui-card">\n' +
                            '           <div class="layui-card-header">\n' +
                            '              <img src="' + (val.type == 1 ? '<?=UserImage($UserData)?>' : '<?=$conf['logo']?>') + '" style="width: 20px;height: 20px;"> <span style="color:#030707">' + (val.type == 1 ? '<?=$UserData['name']?>' : '<?=$conf['sitename']?>客服') + '</span>\n' +
                            '              <span class="layui-layout-right" style="color: #ccc;font-size: 0.9em">' + key + '</span>\n' +
                            '            </div>\n' +
                            '            <div class="layui-card-body easyeditor-content" style="font-size: 0.8em;padding: 0.2em">' + val.content + '</div>\n' +
                            '       </div>';
                    });
                    $("#message").html(content);
                    $("#state").html(state(data.type));
                    $("#class").html(data.class);
                    $("#order").html(data.order);
                    $("#time").html(data.time);
                    $("#count").html(' - ' + data.count)
                    if ((data.state - 0) == 1 || (data.state - 0) == 3 || (data.type - 0) >= 3) {
                        $("#Submit").hide(100);
                        if (data.grade == undefined) {
                            layer.msg('请评分哦');
                            grade()
                        }
                    } else $("#Submit").show(100);

                    layui.use(['easyeditor'], function () {
                        var easyeditor = layui.easyeditor;
                        easyeditor.render({
                            elem: ".easyeditor-content"
                        });
                    });
                } else layer.msg(data.msg, {icon: 2});
            },
            error: function () {
                layer.closeAll();
                layer.alert('加载失败！');
            }
        });
    }

    function grade() {
        layer.open({
            title: '评分面板',
            content: '<div id="grade"></div>\n' +
                '                <br>\n' +
                '                您对本次售后工单处理还满意吗?\n' +
                '                ',
            btn: ['提交', '取消'],
            success: function (layero, index) {
                layui.use(['rate'], function () {
                    var rate = layui.rate;
                    rate.render({
                        elem: '#grade'
                        , value: 5
                        , text: true
                        , length: 5
                        , setText: function (value) {
                            var arrs = {
                                '1': '很不满意'
                                , '2': '不满意'
                                , '3': '满意'
                                , '4': '很满意'
                                , '5': '非常满意'
                            };
                            this.span.text(arrs[value] || (value + "星"));
                            $("input[name='grade']").val(value);
                        }
                    });
                })
            }, yes: function (layero, index) {
                $.ajax({
                    type: "post",
                    url: "/user/ajax.php?act=Tickets&type=Grade&id=" + $("input[name='id']").val() + '&n=' + $("input[name='grade']").val(),
                    dataType: "json",
                    success: function (data) {
                        if (data.code == 1) {
                            layer.msg(data.msg, {icon: 1});
                        } else {
                            layer.msg(data.msg, {icon: 2});
                        }
                    },
                    error: function () {
                        layer.alert('加载失败！');
                    }
                });
            }
        })
    }

    function state(id = 1) {
        switch ((id - 0)) {
            case 1:
                return '<font color=#1e90ff>已受理</font>';
                break;
            case 2:
                return '<font color=#fa8072>已处理</font>';
                break;
            case 3:
                return '<font color=#2e8b57>已解决</font>';
                break;
            case 4:
                return '<font color=#708090>关闭工单</font>';
                break;
            case 5:
                return '<font color=#9acd32>已评价</font>';
                break;
            default:
                return '<font color=#ff69b4>未知状态</font>';
                break;
        }
    }

    function finish() {
        layer.open({
            title: '温馨提示',
            content: '若您的问题已经得到解决,可点击确认完结此工单,若完结后还有其他问题,可再创建新的工单哦!',
            btn: ['完结工单', '取消'],
            icon: 3,
            btn1: function (layero, index) {
                $.ajax({
                    type: "post",
                    url: "/user/ajax.php?act=Tickets&type=Finish&id=" + $("input[name='id']").val(),
                    dataType: "json",
                    success: function (data) {
                        if (data.code == 1) {
                            layer.alert(data.msg, {
                                icon: 1, title: '温馨提示', end: function (layero, index) {
                                    $("#L_content").val('');
                                    location.reload();
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
    }

    details();
</script>
</body>
</html>
