<?php

/**
 * Author：晴玖天
 * Creation：2020/4/26 21:05
 * Filename：tickets.php
 * 工单管理
 */
$title = '售后工单管理';
include 'header.php';

?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="layui-tab layui-tab-brief" lay-filter="Tab">
                    <div class="mdui-tab mdui-tab-full-width" mdui-tab>
                        <a onclick="tickets.list('all')" class="mdui-ripple">全部</a>
                        <a onclick="tickets.list(2)" class="mdui-ripple">未结单</a>
                        <a onclick="tickets.list(1)" class="mdui-ripple">已解决</a>
                        <a onclick="tickets.list(3)" class="mdui-ripple">已关闭</a>
                    </div>
                    <div class="layui-tab-content" id="content"
                         style="padding: 0.2em;white-space:nowrap;overflow:hidden;overflow-x: auto">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include 'bottom.php';
?>

<script>
    layui.use(['form', 'table', 'upload', 'element'], function () {
        var form = layui.form;
        var table = layui.table;
        var upload = layui.upload;
        var element = layui.element;

        element.on('tab(Tab)', function (data) {
            console.log(data.index);
        });
    });

    var tickets = {
        list: function (type = 'all') {
            layer.load(2, {
                time: 99999
            });
            $("#content").html('');
            $.ajax({
                type: "post",
                url: "ajax.php?act=Tickets&type=list",
                data: {
                    state: type,
                },
                dataType: "json",
                success: function (data) {
                    layer.closeAll();
                    if (data.code == 1) {
                        if (data.data.length == 0) {
                            $("#content").html('<span class="font-13">没有查询到该状态的工单记录</span>');
                            return false;
                        }
                        var content = '<table class="layui-table layui-text" lay-size="" lay-skin="row"><colgroup>\n' +
                            '    <col width="10%">\n' +
                            '    <col width="20%">' +
                            '    <col width="20%">' +
                            '    <col width="10%">' +
                            '    <col width="20%">' +
                            '    <col width="10%">\n' +
                            '    <col>\n' +
                            '  </colgroup><thead>\n' +
                            '    <tr>\n' +
                            '      <th>编号</th>\n' +
                            '      <th>标题(点击处理)</th>' +
                            '      <th>类型</th>' +
                            '      <th>状态</th>' +
                            '      <th>实时状态</th>' +
                            '      <th>创建时间</th>' +
                            '      <th>用户评分</th>\n' +
                            '    </tr> \n' +
                            '  </thead><tbody>';
                        $.each(data.data, function (key, val) {
                            content += '' +
                                '   <tr>\n' +
                                '      <td>' + val.id + '</td>\n' +
                                '      <td><a href="javascript:tickets.details(' + val.id + ',\'' + val.addtime + '\')" title="查看工单详情">' + val.name + '</a></td>' +
                                '      <td>' + val.class + '</td>' +
                                '      <td>' + (val.state == 1 ? '<font color=#2e8b57>已解决</font>' : (val.state == 2 ? '<font color=#5f9ea0>已处理</font>' : '<font color=#5f9ea0>已关闭</font>')) + '</td>' +
                                '      <td>' + tickets.type(val.type) + '</td>' +
                                '      <td>' + val.addtime + '</td>' +
                                '      <td>' + (val.grade == undefined ? '未打分' : val.grade + '分') + '</td> \n' +
                                '    </tr>';
                        });
                        content += '</tbody></table>';
                        $("#content").html(content);
                    } else layer.msg(data.msg, {
                        icon: 2
                    });
                },
                error: function () {
                    layer.alert('加载失败！');
                }
            });
        },
        details: function (id, time) {
            layer.open({
                type: 2,
                area: ['98%', '98%'],
                title: '工单详情[' + id + ']',
                content: 'tickets_code.php?id=' + id,
                skin: 'layui-layer-rim',
                id: 'OrderMark',
                btn: false,
                end: function () {
                    tickets.list();
                }
            });
        },
        type(id) {
            switch ((id - 0)) {
                case 1:
                    return '<font color=red>需你回复</font>';
                    break;
                case 2:
                    return '<font color=#1e90ff>待用户回复</font>';
                    break;
                case 3:
                    return '<font color=#2e8b57>已解决</font>';
                    break;
                case 4:
                    return '<font color=#708090>已关闭</font>';
                    break;
                case 5:
                    return '<font color=#9acd32>已评价</font>';
                    break;
                default:
                    return '<font color=#ff69b4>未知状态</font>';
                    break;
            }
        }
    };

    tickets.list();
</script>