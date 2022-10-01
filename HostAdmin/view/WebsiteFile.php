<?php
/**
 * Author：晴天 QQ：1186258278
 * Creation：2020/4/18 10:21
 * Filename：WebsiteFile.php
 * 网站文件管理
 */
include './header.php';
global $date;
if (isset($_QET['flie'])) {
    $flie_arr = explode('.', $_QET['flie']);
}
$Md5 = md5($date);
?>
<style>
    .operation a {
        color: #20a53a;
        cursor: pointer;
        size: 0.7em;
    }

    #ContentEdit {
        padding: 0 !important;
    }
</style>
<link rel="stylesheet" href="../../assets/CodeMirror/lib/codemirror.css"/>
<link href="../../assets/CodeMirror/lib/codemirror.css" rel="stylesheet">
<link href="../../assets/CodeMirror/addon/display/fullscreen.css" rel="stylesheet">
<link href="../../assets/CodeMirror/theme/monokai.css" rel="stylesheet">
<div class="layui-fluid" id="App" flie="<?= $_QET['flie'] ?? '' ?>">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header">
                    主机文件管理 <font color="#ff8c00" size="2">同节点下的文件可复制粘贴</font>
                </div>
                <div class="layui-card-body">
                    当前目录：
                    <span class="layui-breadcrumb">
                        <a href="WebsiteFile.php" style="color: #33cabb !important;">根目录</a>
                        <?php $flie = '';
                        foreach ($flie_arr as $v) {
                            $flie .= $v . '.';
                            ?>
                            <a href="WebsiteFile.php?flie=<?= substr_replace($flie, "", -1) ?>"><?= $v ?></a>
                        <?php } ?>
                    </span>
                    <table class="layui-hide" id="test-table-totalRow" lay-filter="test-table-totalRow"></table>
                    <script type="text/html" id="toolbarDemo">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="getUpdate">
                                上传
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="getDownload">
                                远程下载
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="CreateFile">
                                新建文件
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="CreateDir">
                                新建目录
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="SetBatchData">
                                删除
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="SetBatchZrp">
                                压缩
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="SetBatchCopy">
                                复制
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="SetBatchShear">
                                剪切
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="SetBatchPaste">
                                粘贴
                            </button>
                            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="Refresh">
                                刷新
                            </button>
                        </div>
                    </script>
                </div>
            </div>
        </div>
    </div>
    <div id="Update" style="display: none">
        <div class="layui-upload">
            <button type="button" class="layui-btn layui-btn-normal layui-btn-sm testList">选择文件</button>
            <button type="button" class="layui-btn layui-btn-sm testListAction">开始上传</button>
            <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" style="float: right"
                    onclick="layer.closeAll()">关闭窗口
            </button>
            <div class="layui-upload-list">
                <table class="layui-table" lay-size="sm">
                    <thead>
                    <tr>
                        <th>文件名</th>
                        <th>大小</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody class="demoList"></tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="Download" style="display: none">
        <div class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label class="layui-form-label">URL地址</label>
                <div class="layui-input-block">
                    <input type="text" name="domain" onblur="Curlvalue()" required lay-verify="required"
                           placeholder="URL地址"
                           autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">下载到</label>
                <div class="layui-input-block">
                    <input type="text" name="flieS" required
                           value="/<?= (empty($_QET['flie']) ? '' : implode('/', $flie_arr) . '/') ?>"
                           lay-verify="required" placeholder="下载到" autocomplete="off"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">文件名</label>
                <div class="layui-input-block">
                    <input type="text" name="fliename" required lay-verify="required" placeholder="保存文件名"
                           autocomplete="off"
                           class="layui-input">
                </div>
            </div>
        </div>
    </div>
    <div id="SetZrp" style="display: none">
        <div class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label class="layui-form-label">压缩类型</label>
                <div class="layui-input-block">
                    <select name="z_type">
                        <option value="tar.gz">tar.gz (推荐)</option>
                        <option value="zip">zip (通用格式)</option>
                        <option value="rar">rar (WinRAR对中文兼容较好)</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">压缩到</label>
                <div class="layui-input-block">
                    <input type="text" name="flieS" required
                           value="/<?= (empty($_QET['flie']) ? '' : implode('/', $flie_arr) . '/') . $Md5 ?>"
                           lay-verify="required" placeholder="压缩到" autocomplete="off"
                           class="layui-input">
                </div>
            </div>
        </div>
    </div>

    <div id="UnZip" style="display: none">
        <div class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label class="layui-form-label">编码</label>
                <div class="layui-input-block">
                    <select name="coding">
                        <option value="UTF-8">UTF-8</option>
                        <option value="gb18030">GBK</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">文件名</label>
                <div class="layui-input-block">
                    <input type="text" name="sfile" required disabled
                           value="/<?= (empty($_QET['flie']) ? '' : implode('/', $flie_arr) . '/') ?>"
                           lay-verify="required" placeholder="文件名" autocomplete="off"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">解压到</label>
                <div class="layui-input-block">
                    <input type="text" name="dfile" required
                           value="/<?= (empty($_QET['flie']) ? '' : implode('/', $flie_arr)) ?>"
                           lay-verify="required" placeholder="解压到" autocomplete="off"
                           class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">解压密码</label>
                <div class="layui-input-block">
                    <input type="text" name="password" required
                           value="" placeholder="没有的话留空" autocomplete="off"
                           class="layui-input">
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../assets/CodeMirror/lib/codemirror.js"></script>
<script src="../../assets/CodeMirror/mode/javascript/javascript.js"></script>
<script src="../../assets/CodeMirror/mode/xml/xml.js"></script>
<script src="../../assets/CodeMirror/mode/css/css.js"></script>
<script src="../../assets/CodeMirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="../../assets/CodeMirror/addon/selection/active-line.js"></script>
<script src="../../assets/CodeMirror/addon/edit/matchbrackets.js"></script>
<script src="../../assets/CodeMirror/addon/display/fullscreen.js"></script>
<script src="../../assets/js/jquery-3.4.1.min.js"></script>
<script src="../../assets/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '../../assets/layuiadmin/'
    }).extend({
        index: 'lib/index'
    }).use(['index', 'table', 'form', 'admin', 'upload'], function () {

        var admin = layui.admin
            , table = layui.table
            , form = layui.form
            , upload = layui.upload;
        table.render({
            elem: '#test-table-totalRow'
            , url: '../ajax.php?act=SpaceDirList&flie=' + $("#App").attr('flie')
            , toolbar: '#toolbarDemo'
            , title: '文件列表'
            , cellMinWidth: 80
            , id: 'idTest'
            , cols: [[
                {type: 'checkbox', fixed: 'left'}
                , {field: 'filename', templet: "#filename", title: '文件名', width: 200}
                , {field: 'size', templet: "#size", title: '大小', width: 100}
                , {field: 'ModificationTime', templet: "#path", title: '修改时间', width: 160}
                , {field: 'authority', title: '权限', width: 70}
                , {field: 'possessor', title: '所有者', width: 80}
                , {field: 'operation', templet: "#operation", title: '操作', minWidth: 250}
            ]]
            , page: true, limit: 20, limits: [10, 20, 50, 100, 200, 500, 1000, 2000],
            done: function (res, curr, count) {

            }
        });

        $.ajax({
            type: "post",
            url: "../ajax.php?act=SpaceVi",
            dataType: "json",
        });

        form.on('select(z_type)', function (data) {
            var zip = $("#GetSetZrp input[name='flieS']").val();
            zip = zip.split('.')[0];
            $("#GetSetZrp input[name='flieS']").val(zip + '.' + data.value);
        });

        table.on('toolbar(test-table-totalRow)', function (obj) {
            var checkStatus = table.checkStatus(obj.config.id).data;
            var array = [];
            $.each(checkStatus, function (key, val) {
                array.push(val['filename']);
            });
            switch (obj.event) {
                case 'SetBatchData': //批量删除
                    if (checkStatus.length == 0) {
                        layer.msg('最少要选择一个文件/文件夹');
                        return false;
                    }
                    SetBatch(array, 4);
                    break;
                case 'Refresh':
                    table.reload('idTest', {});
                    break;
                case 'getDownload': //远程下载
                    layer.open({
                        title: '远程下载',
                        id: 'GetDownload',
                        content: $("#Download").html(),
                        btn: ['确认', '取消'],
                        btn1: function () {
                            var load = layer.load(2, {tiem: 99999});
                            admin.req({
                                type: "POST",
                                url: '../ajax.php?act=SpaceDownloadFile',
                                data: {
                                    flie: $("#App").attr('flie'),
                                    flieS: $("#GetDownload input[name='flieS']").val(),
                                    curl: $("#GetDownload input[name='domain']").val(),
                                    fliename: $("#GetDownload input[name='fliename']").val(),
                                },
                                dataType: "json",
                                success: function (data) {
                                    layer.close(load);
                                    if (data.code == 0) {
                                        layer.alert(data.msg, {
                                            icon: 1, btn1: function () {
                                                layer.closeAll();
                                                table.reload('idTest', {});
                                            }
                                        });
                                    } else layer.msg(data.msg, {icon: 2});
                                },
                                error: function () {
                                    layer.close(load);
                                    layer.alert('获取失败！');
                                },
                            });
                        }
                    });
                    break;
                case 'getUpdate': //上传文件
                    layer.open({
                        title: '文件上传[支持批量]',
                        id: 'GetUpdate',
                        content: $("#Update").html(),
                        btn: false,
                        area: ['96%', '90%'],
                        closeBtn: 0,
                        anim: 4,
                        success: function () {
                            $("#GetUpdate .demoList").attr('id', 'GetUpdate_demoList');
                            $("#GetUpdate .testListAction").attr('id', 'GetUpdate_testListAction');
                            $("#GetUpdate .testList").attr('id', 'GetUpdate_testList');
                            demoListView = $('#GetUpdate_demoList');
                            uploadListIns = upload.render({
                                elem: '#GetUpdate_testList',
                                url: '../ajax.php?act=SpaceUpdate&flie=' + $("#App").attr('flie'),
                                accept: 'file',
                                multiple: true,
                                auto: false,
                                size: 1024 * <?=$UserData['filesize'] ?? 9999999 ?>,
                                bindAction: '#GetUpdate_testListAction',
                                choose: function (obj) {
                                    var files = this.files = obj.pushFile();
                                    obj.preview(function (index, file) {
                                        var tr = $(['<tr id="upload-' + index + '">'
                                            , '<td>' + file.name + '</td>'
                                            , '<td>' + (file.size / 1024).toFixed(2) + 'kb</td>'
                                            , '<td>等待上传</td>'
                                            , '<td>'
                                            , '<button class="layui-btn layui-btn-xs demo-reload layui-hide">重传</button>'
                                            , '<button class="layui-btn layui-btn-xs layui-btn-danger demo-delete">删除</button>'
                                            , '</td>'
                                            , '</tr>'].join(''));
                                        tr.find('.demo-reload').on('click', function () {
                                            obj.upload(index, file);
                                        });
                                        tr.find('.demo-delete').on('click', function () {
                                            delete files[index];
                                            tr.remove();
                                            uploadListIns.config.elem.next()[0].value = '';
                                        });
                                        demoListView.append(tr);
                                    });
                                },
                                done: function (res, index, upload) {
                                    if (res.code == 0) {
                                        var tr = demoListView.find('tr#upload-' + index)
                                            , tds = tr.children();
                                        tds.eq(2).html('<span style="color: #5FB878;">' + res.msg + '</span>');
                                        tds.eq(3).html('');
                                        table.reload('idTest', {});
                                        SizeCalibrationGet();
                                        return delete this.files[index];
                                    } else {
                                        this.error(index, upload, res.msg);
                                    }
                                },
                                error: function (index, upload, msg) {
                                    var tr = demoListView.find('tr#upload-' + index)
                                        , tds = tr.children();
                                    tds.eq(2).html('<span style="color: #FF5722;">' + msg + '</span>');
                                    tds.eq(3).find('.demo-reload').removeClass('layui-hide');
                                }
                            });
                        }
                    });
                    break;
                case 'CreateFile': //新建空白文件
                    layer.prompt({
                        formType: 3,
                        value: '',
                        title: '请输入文件名(新建空白文件)',
                        btn: ['新建', '取消'],
                    }, function (value, index) {
                        var load = layer.load(2, {tiem: 99999});
                        admin.req({
                            type: "POST",
                            url: '../ajax.php?act=SpaceCreateFile',
                            data: {
                                flie: $("#App").attr('flie'),
                                name: value
                            },
                            dataType: "json",
                            success: function (data) {
                                layer.close(load);
                                if (data.code == 0) {
                                    layer.alert(data.msg, {
                                        icon: 1, btn1: function () {
                                            layer.closeAll();
                                            table.reload('idTest', {});
                                            edit(value);
                                        }
                                    });
                                } else layer.msg(data.msg, {icon: 2});
                            },
                            error: function () {
                                layer.close(load);
                                layer.alert('获取失败！');
                            },
                        });
                        layer.close(index);
                    });
                    break;
                case 'CreateDir': //新建目录
                    layer.prompt({
                        formType: 3,
                        value: '',
                        title: '请输入目录名称(新建目录)',
                        btn: ['新建', '取消'],
                    }, function (value, index, elem) {
                        var load = layer.load(2, {tiem: 99999});
                        admin.req({
                            type: "POST",
                            url: '../ajax.php?act=SpaceCreateDir',
                            data: {
                                flie: $("#App").attr('flie'),
                                name: value
                            },
                            dataType: "json",
                            success: function (data) {
                                layer.close(load);
                                if (data.code == 0) {
                                    layer.alert(data.msg, {
                                        icon: 1, btn1: function () {
                                            layer.closeAll();
                                            table.reload('idTest', {});
                                        }
                                    });
                                } else layer.msg(data.msg, {icon: 2});
                            },
                            error: function () {
                                layer.close(load);
                                layer.alert('获取失败！');
                            },
                        });
                        layer.close(index);
                    });
                    break;
                case 'SetBatchZrp': //压缩文件
                    if (checkStatus.length == 0) {
                        layer.msg('最少要选择一个文件/文件夹');
                        return false;
                    }
                    CompressAjax(array, $("#SetZrp input[name='flieS']").val());
                    break;
                case 'SetBatchCopy': //复制
                    if (checkStatus.length == 0) {
                        layer.msg('最少要选择一个文件/文件夹');
                        return false;
                    }
                    SetBatch(array, 1);
                    break;
                case 'SetBatchShear': //剪切
                    if (checkStatus.length == 0) {
                        layer.msg('最少要选择一个文件/文件夹');
                        return false;
                    }
                    SetBatch(array, 2);
                    break;
                case 'SetBatchPaste': //粘贴全部

                    var load = layer.load(2, {tiem: 99999});
                    $.ajax({
                        type: "POST",
                        url: '../ajax.php?act=SpaceSetBatchPasteV',
                        data: {
                            flie: $("#App").attr('flie')
                        },
                        dataType: "json",
                        success: function (data) {
                            layer.close(load);
                            if (data.code == 0) {
                                var load = layer.load(2, {tiem: 99999});
                                $.ajax({
                                    type: "POST",
                                    url: '../ajax.php?act=SpaceSetBatchPaste',
                                    data: {
                                        flie: $("#App").attr('flie')
                                    },
                                    dataType: "json",
                                    success: function (data) {
                                        layer.close(load);
                                        if (data.code == 0) {
                                            layer.msg(data.msg, {icon: 1});
                                            table.reload('idTest', {});
                                        } else layer.msg(data.msg, {icon: 2})
                                    },
                                    error: function () {
                                        layer.close(load);
                                        layer.alert('获取失败！');
                                    },
                                });
                            } else if (data.code == -2) {
                                content = '<table class="layui-table" lay-skin="nob" lay-size="sm"><thead><tr><th>文件名</th><th>大小</th><th>最后修改时间</th></tr></thead><tbody>';
                                $.each(data.data, function (key, val) {
                                    content += '<tr><td>' + val.filename + '</td><td>' + val.size + '</td><td>' + val.mtime + '</td></tr>'
                                });
                                content += '</tbody></table>';
                                layer.open({
                                    title: '当前有' + data.data.length + '个冲突文件！',
                                    content: content,
                                    id: 'Paste',
                                    btn: ['确认覆盖', '取消'],
                                    btn1: function () {
                                        var load = layer.load(2, {tiem: 99999});
                                        $.ajax({
                                            type: "POST",
                                            url: '../ajax.php?act=SpaceSetBatchPaste',
                                            data: {
                                                flie: $("#App").attr('flie')
                                            },
                                            dataType: "json",
                                            success: function (data) {
                                                layer.close(load);
                                                if (data.code == 0) {
                                                    layer.msg(data.msg, {icon: 1});
                                                    table.reload('idTest', {});
                                                } else layer.msg(data.msg, {icon: 2})
                                            },
                                            error: function () {
                                                layer.close(load);
                                                layer.alert('获取失败！');
                                            },
                                        });
                                    }, end: function () {
                                        layer.closeAll();
                                    }
                                })
                            } else layer.msg(data.msg, {icon: 2});
                        },
                        error: function () {
                            layer.close(load);
                            layer.alert('获取失败！');
                        },
                    });
                    break;
            }
        });
    });

    function Compress(name) {
        var array = new Array(name);
        name = name.split('.')[0];
        var flies = '/<?= (empty($_QET['flie']) ? '' : implode('/', $flie_arr) . '/') ?>';
        CompressAjax(array, flies + name);
    }

    function CompressAjax(array, name) {
        form = layui.form;
        table = layui.table;
        layer.open({
            title: '压缩文件',
            id: 'GetSetZrp',
            content: $("#SetZrp").html(),
            btn: ['压缩', '取消'],
            success: function () {
                $("#GetSetZrp select[name='z_type']").attr('lay-filter', 'z_type');
                $("#GetSetZrp input[name='flieS']").val(name + '.tar.gz')
                form.render();
            },
            btn1: function () {
                var load = layer.load(2, {tiem: 99999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=SpaceSetBatchZip',
                    data: {
                        flie: $("#App").attr('flie'),
                        data: array,
                        z_type: $("#GetSetZrp select[name='z_type']").val(),
                        dfile: $("#GetSetZrp input[name='flieS']").val(),
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(load);
                        if (data.code == 0) {
                            layer.msg(data.msg, {icon: 1});
                            table.reload('idTest', {});
                        } else layer.msg(data.msg, {icon: 2})
                    },
                    error: function () {
                        layer.close(load);
                        layer.alert('获取失败！');
                    },
                });
            }
        });
    }

    function SetBatch(array, type) {
        layer.open({
            title: '温馨提示',
            content: '是否要执行此操作？删除后无法恢复！',
            icon: 3,
            btn: ['确定删除', '取消'],
            btn1: function () {
                var load = layer.load(2, {tiem: 99999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=SpaceSetBatchData',
                    data: {
                        flie: $("#App").attr('flie'),
                        data: array,
                        type: type
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(load);
                        if (data.code == 0) {
                            layer.msg(data.msg, {icon: 1});
                            table = layui.table;
                            table.reload('idTest', {});
                        } else layer.msg(data.msg, {icon: 2})
                    },
                    error: function () {
                        layer.close(load);
                        layer.alert('获取失败！');
                    },
                });
            }
        })
    }

    function Curlvalue() {
        var value = $("#GetDownload input[name='domain']").val();
        var value_arr = value.split('/');
        var do_val = value_arr[(value_arr.length) - 1];
        $("#GetDownload input[name='fliename']").val(do_val);
    }

    function get_path_size(flie = '', id) {
        var load = layer.load(2, {tiem: 99999});
        var admin = layui.admin;
        admin.req({
            type: "POST",
            url: '../ajax.php?act=SpacePathSize',
            data: {flie: flie},
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 0) {
                    $("#" + id).text(data.size);
                } else layer.alert(data.msg, {icon: 3});
            },
            error: function () {
                layer.close(load);
                layer.alert('获取失败！');
            },
        });
    }

    function Rechristen(name) {
        form = layui.form;
        table = layui.table;
        layer.prompt({
            formType: 3,
            value: name,
            title: '重命名',
            btn: ['保存', '取消'],
        }, function (value, index, elem) {
            var load = layer.load(2, {tiem: 99999});
            $.ajax({
                type: "POST",
                url: '../ajax.php?act=SpaceMvFile',
                data: {
                    flie: $("#App").attr('flie'),
                    sfile: name,
                    dfile: value,
                },
                dataType: "json",
                success: function (data) {
                    layer.close(load);
                    if (data.code == 0) {
                        layer.alert(data.msg, {
                            icon: 1, btn1: function () {
                                layer.closeAll();
                                table.reload('idTest', {});
                            }
                        });
                    } else layer.msg(data.msg, {icon: 2});
                },
                error: function () {
                    layer.close(load);
                    layer.alert('获取失败！');
                },
            });
            layer.close(index);
        });
    }

    function DeleteFile(name) {
        layer.open({
            title: '不可逆操作',
            content: '是否要删除文件<br>' + name + '？',
            btn: ['确认删除', '取消'],
            icon: 3,
            btn1: function () {
                var load = layer.load(2, {tiem: 99999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=SpaceDeleteFile',
                    data: {
                        flie: $("#App").attr('flie'),
                        name: name,
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(load);
                        if (data.code == 0) {
                            layer.msg(data.msg, {
                                icon: 1, btn1: function () {
                                    layer.closeAll();
                                }
                            });
                            table = layui.table;
                            table.reload('idTest', {});
                        } else layer.msg(data.msg, {icon: 2});
                    },
                    error: function () {
                        layer.close(load);
                        layer.alert('获取失败！');
                    },
                });
            }
        });
    }

    function ShareDownload(name) {
        layer.open({
            title: '操作确认',
            content: '是否要为文件：<br>' + name + '<br>创建分享外链？',
            btn: ['确认', '取消'],
            icon: 3,
            btn1: function () {
                var load = layer.load(2, {tiem: 99999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=SpaceShareDownload',
                    data: {
                        flie: $("#App").attr('flie'),
                        name: name,
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(load);
                        if (data.code == 0) {
                            layer.alert(data.msg, {
                                icon: 1, btn1: function () {
                                    layer.closeAll();
                                    layer.open({
                                        title: data.name,
                                        content: '文件下载地址：<br>' + data.url + '<hr>文件提取密码：<br>' + data.password + '<hr>外链创建时间：' + data.addtime + '<br>外链失效时间：' + data.expire + '<hr>Ps:此文件外链有效期内只可生成一次,请牢记密码哦！',
                                        btn: false,
                                    })
                                }
                            });
                        } else layer.msg(data.msg, {icon: 2});
                    },
                    error: function () {
                        layer.close(load);
                        layer.alert('获取失败！');
                    },
                });
            }
        });
    }

    //打开编辑器
    function edit(name) {
        var load = layer.load(2, {tiem: 99999});
        $.ajax({
            type: "POST",
            url: '../ajax.php?act=SpaceEditContent',
            data: {
                flie: $("#App").attr('flie'),
                name: name,
            },
            dataType: "json",
            success: function (data) {
                layer.close(load);
                if (data.code == 0) {
                    //调用编辑器模块
                    content = '<textarea name="code" id="code" >' + data.data + '</textarea>';
                    layer.open({
                        title: '在线编辑 - <font color=red>' + name + '</font> - [' + data.encoding + ']'
                        , area: ["95%", "95%"]
                        , offset: 'auto'
                        , maxmin: true
                        , scrollbar: false
                        , id: 'ContentEdit'
                        , content: content
                        , btn: ['保存', '关闭']
                        , btn1: function () {
                            var load = layer.load(2, {tiem: 99999});
                            scriptDesc = editor.getValue();
                            $.ajax({
                                type: "POST",
                                url: '../ajax.php?act=SpaceEditSave',
                                data: {
                                    flie: $("#App").attr('flie'),
                                    name: name,
                                    content: scriptDesc,
                                    encoding: data.encoding,
                                },
                                dataType: "json",
                                success: function (res) {
                                    layer.close(load);
                                    if (res.code == 0) {
                                        layer.msg(res.msg, {icon: 1});
                                    } else {
                                        layer.msg(res.msg, {icon: 2});
                                    }
                                },
                                error: function () {
                                    layer.close(load);
                                    layer.alert('加载失败！');
                                }
                            });
                        }
                        , success: function () {
                            editor = CodeMirror.fromTextArea(document.getElementById("code"), {
                                lineNumbers: true,
                                indentUnit: 4,
                                styleActiveLine: true,
                                matchBrackets: true,
                                mode: 'htmlmixed',
                                lineWrapping: true,
                                theme: 'monokai',
                            });
                            editor.setSize('auto', '100%');
                            editor.setOption("extraKeys", {
                                Tab: function (cm) {
                                    var spaces = Array(cm.getOption("indentUnit") + 1).join(" ");
                                    cm.replaceSelection(spaces);
                                }, "F11": function (cm) {
                                    cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                                }, "Esc": function (cm) {
                                    if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                                }
                            });
                        }
                    });
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            },
            error: function () {
                layer.alert('获取内容失败！');
            }
        });
    }

    function SizeCalibrationGet() {
        $.ajax({
            type: "POST",
            url: '../ajax.php?act=SizeCalibration',
            dataType: "json"
        });
    }

    function UnZip(name) {
        form = layui.form;
        table = layui.table;
        layer.open({
            title: '解压目录',
            id: 'GetUnZip',
            content: $("#UnZip").html(),
            btn: ['解压', '取消'],
            success: function () {
                $("#GetUnZip input[name='sfile']").val($("#UnZip input[name='sfile']").val() + name);
                form.render();
            },
            btn1: function () {
                var load = layer.load(2, {tiem: 99999});
                $.ajax({
                    type: "POST",
                    url: '../ajax.php?act=SpaceSetBatchUnZip',
                    data: {
                        flie: $("#App").attr('flie'),
                        name: name,
                        dfile: $("#GetUnZip input[name='dfile']").val(),
                        coding: $("#GetUnZip select[name='coding']").val(),
                        password: $("#GetUnZip input[name='password']").val(),
                    },
                    dataType: "json",
                    success: function (data) {
                        layer.close(load);
                        if (data.code == 0) {
                            layer.msg(data.msg, {icon: 1});
                            table.reload('idTest', {});
                        } else layer.msg(data.msg, {icon: 2})
                    },
                    error: function () {
                        layer.close(load);
                        layer.alert('获取失败！');
                    },
                });
            }
        });
    }

</script>

<script id="operation" type="text/html">
    <div class="operation">
        {{# if(d.type==1){ }}
        <!--文件夹-->
        {{# }else if(d.type==2){ }}
        <!--php-->
        <a href="javascript:edit('{{ d.filename }}')">编辑</a> <a
                href="javascript:ShareDownload('{{ d.filename }}')">外链</a>
        {{# }else if(d.type==3){ }}
        <!--htmk-->
        <a href="javascript:edit('{{ d.filename }}')">编辑</a> <a
                href="javascript:ShareDownload('{{ d.filename }}')">外链</a>
        {{# }else if(d.type==4){ }}
        <!--压缩包-->
        <a href="javascript:UnZip('{{ d.filename }}')">解压</a> <a
                href="javascript:ShareDownload('{{ d.filename }}')">外链</a>
        {{# }else if(d.type==5){ }}
        <!--图片-->
        <a href="javascript:ShareDownload('{{ d.filename }}')">外链</a>
        {{# }else if(d.type==6){ }}
        <!--js-->
        <a href="javascript:edit('{{ d.filename }}')">编辑</a> <a
                href="javascript:ShareDownload('{{ d.filename }}')">外链</a>
        {{# }else if(d.type==7){ }}
        <!--css-->
        <a href="javascript:edit('{{ d.filename }}')">编辑</a> <a
                href="javascript:ShareDownload('{{ d.filename }}')">外链</a>
        {{# }else{ }}
        <!--未知-->
        <a href="javascript:ShareDownload('{{ d.filename }}')">外链</a>
        {{# } }}
        <!--通用-->
        <a href="javascript:Compress('{{ d.filename }}')">压缩</a>
        <a href="javascript:Rechristen('{{ d.filename }}')">重命名</a>
        <a href="javascript:DeleteFile('{{ d.filename }}')">删除</a>
    </div>
</script>

<script id="filename" type="text/html">
    {{# if(d.type==1){ }}
    <a href="WebsiteFile.php?flie=<?= (isset($_QET['flie']) ? $_QET['flie'] . '.{{ d.filename }}' : '{{ d.filename }}') ?>"
       style="color: #33cabb"
       title="打开{{ d.filename }}文件夹">{{ d.filename }}</a>
    {{# }else{ }}
    {{# if(d.type==2||d.type==3||d.type==6||d.type==7){ }}
    <a href="javascript:edit('{{ d.filename }}')" title="编辑文件 {{d.filename}}">{{ d.filename }}</a>
    {{# }else{ }}
    {{ d.filename }}
    {{# } }}
    {{# } }}
</script>

<script id="size" type="text/html">
    {{# if(d.type==1){ }}
    <a href="javascript:get_path_size('<?= (isset($_QET['flie']) ? $_QET['flie'] . '.{{ d.filename }}' : '{{ d.filename }}') ?>','Size_{{ d.id }}')"
       style="color: #20a53a"
       title="计算文件夹{{ d.filename }}大小" id="Size_{{ d.id }}">计算大小</a>
    {{# }else{ }}
    {{ d.size }}
    {{# } }}
</script>
