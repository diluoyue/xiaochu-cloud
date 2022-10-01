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
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="btn btn-sm btn-success" onclick="tickets.new()"
                         style="position: fixed;top: 70vh;right: 2em">
                        发起新的工单
                    </div>
                    <div class="layui-tab layui-tab-brief" lay-filter="Tab">
                        <ul class="layui-tab-title">
                            <li class="layui-this" onclick="tickets.list('all')">全部</li>
                            <li onclick="tickets.list(2)">处理中</li>
                            <li onclick="tickets.list(1)">已解决</li>
                            <li onclick="tickets.list(3)">已关闭</li>
                        </ul>
                        <div class="layui-tab-content" id="content"
                             style="padding: 0.2em;white-space:nowrap;overflow:hidden;overflow-x: auto">
                        </div>
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
            layer.load(2, {time: 99999});
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
                            '      <th>标题</th>' +
                            '      <th>类型</th>' +
                            '      <th>状态</th>' +
                            '      <th>实时状态</th>' +
                            '      <th>创建时间</th>' +
                            '      <th>评分</th>\n' +
                            '    </tr> \n' +
                            '  </thead><tbody>';
                        $.each(data.data, function (key, val) {
                            content += '' +
                                '   <tr>\n' +
                                '      <td>' + val.id + '</td>\n' +
                                '      <td><a href="javascript:tickets.details(' + val.id + ',\'' + val.addtime + '\')" title="查看工单详情">' + val.name + '</a></td>' +
                                '      <td>' + val.class + '</td>' +
                                '      <td>' + (val.state == 1 ? '<font color=#2e8b57>已解决</font>' : (val.state == 2 ? '<font color=#1e90ff>处理中</font>' : '<font color=#5f9ea0>已关闭</font>')) + '</td>' +
                                '      <td>' + tickets.type(val.type) + '</td>' +
                                '      <td>' + val.addtime + '</td>' +
                                '      <td>' + (val.grade == undefined ? '未打分' : val.grade + '分') + '</td> \n' +
                                '    </tr>';
                        });
                        content += '</tbody></table>';
                        $("#content").html(content);
                    } else layer.msg(data.msg, {icon: 2});
                },
                error: function () {
                    layer.alert('加载失败！');
                }
            });
        }
        , details: function (id, time) {
            layer.open({
                type: 2,
                area: ['98%', '98%'],
                title: '工单详情[' + id + ']',
                content: 'tickets_code.php?id=' + id,
                skin: 'layui-layer-rim',
                id: 'OrderMark',
                btn: false,
                end: function (layero, index) {
                    tickets.list();
                }
            });
        }

        , new: function () {
            layer.open({
                type: 2,
                area: ['98%', '98%'],
                title: '发起工单',
                content: 'tickets_new.php',
                skin: 'layui-layer-rim',
                id: 'OrderMark',
                btn: false,
                end: function (layero, index) {
                    tickets.list();
                }
            });
        }, type(id) {
            switch ((id - 0)) {
                case 1:
                    return '<font color=#1e90ff>待客服回复</font>';
                    break;
                case 2:
                    return '<font color=red>客服已回复</font>';
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