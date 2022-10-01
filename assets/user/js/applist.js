const App = Vue.createApp({
    data() {
        return {
            Data: [],
            page: 1,
            limit: 10,
            name: '',
            count: 0,
            type: -1,
            upload: Object,
            colorpicker: Object,
        }
    }
    , methods: {
        AppDownload(id) {
            mdui.dialog({
                title: '温馨提示',
                content: '请选择需要执行的操作：<br>可直接获取下载地址，然后手动前往我的店铺内填写App下载地址！' +
                    '<br>也可选择直接将此App下载地址设置为网站App下载地址',
                modal: true,
                history: false,
                buttons: [
                    {
                        text: '关闭',
                    },
                    {
                        text: '获取下载地址',
                        onClick: function () {
                            App.Ajax(id, 1);
                        }
                    },
                    {
                        text: '将下载地址绑定到网站',
                        onClick: function () {
                            App.Ajax(id, 2);
                        }
                    }
                ], onOpen: function () {
                    layui.use(['form'], function () {
                        var form = layui.form;
                        form.render();
                    });
                }
            });
        },
        Ajax(id, type) {
            let is = layer.msg('操作中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST",
                url: 'ajax.php?act=AppDownload',
                data: {
                    id: id,
                    type: type,
                },
                dataType: "json",
                success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        layer.alert(res.msg + '<br>' + res.url, {
                            btn: ['打开下载地址', '取消'],
                            icon: 1, btn1: function () {
                                open(res.url);
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
        },
        AppCalibration(id) {
            mdui.dialog({
                title: '温馨提示',
                content: '是否需要同步App打包任务进度？',
                modal: true,
                history: false,
                buttons: [
                    {
                        text: '关闭',
                    },
                    {
                        text: '确认同步',
                        onClick: function () {
                            let is = layer.msg('同步中，请稍后...', {icon: 16, time: 9999999});
                            $.ajax({
                                type: "POST",
                                url: 'ajax.php?act=AppCalibration',
                                data: {
                                    id: id
                                },
                                dataType: "json",
                                success: function (res) {
                                    layer.close(is);
                                    if (res.code == 1) {
                                        layer.alert(res.msg, {
                                            icon: 1, btn1: function () {
                                                App.initialization();
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
                    }
                ]
            });
        },
        AppDelete(id) {
            mdui.dialog({
                title: '警告',
                content: '删除后，无法恢复，此App生成记录将完全消失，下载地址也将不可用！',
                modal: true,
                history: false,
                buttons: [
                    {
                        text: '关闭',
                    },
                    {
                        text: '确认删除',
                        onClick: function () {
                            let is = layer.msg('删除中，请稍后...', {icon: 16, time: 9999999});
                            $.ajax({
                                type: "POST",
                                url: 'ajax.php?act=AppDelete',
                                data: {
                                    id: id
                                },
                                dataType: "json",
                                success: function (res) {
                                    layer.close(is);
                                    if (res.code == 1) {
                                        layer.alert(res.msg, {
                                            icon: 1, btn1: function () {
                                                App.initialization();
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
                    }
                ]
            });
        },
        AppSubmit(id) {
            mdui.dialog({
                title: '温馨提示',
                content: '是否要提交App打包任务至服务端？提交后不可修改，如果生成失败，可前往服务端修改App打包数据，本地不可修改！',
                modal: true,
                history: false,
                buttons: [
                    {
                        text: '关闭',
                    },
                    {
                        text: '确认提交',
                        onClick: function () {
                            let is = layer.msg('加载中，请稍后...', {icon: 16, time: 9999999});
                            $.ajax({
                                type: "POST",
                                url: 'ajax.php?act=AppSubmit',
                                data: {
                                    id: id
                                },
                                dataType: "json",
                                success: function (res) {
                                    layer.close(is);
                                    if (res.code == 1) {
                                        layer.alert(res.msg, {
                                            icon: 1, btn1: function () {
                                                App.initialization();
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
                    }
                ]
            });
        },
        adjustmentValue(id, name, field, value = '', TaskID = -1) {
            if (value === undefined || value === null || value === '') {
                value = '';
            }
            if (TaskID != -1) {
                layer.msg('已经提交生成，无法再改变！');
                return false;
            }
            mdui.prompt('要改成什么？,请在下方填写', '当前' + name + '为：' + (value === '' ? '空' : value),
                function (str) {
                    let is = layer.msg('修改中，请稍后...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: 'ajax.php?act=AppSet',
                        data: {
                            id: id,
                            field: field,
                            value: str,
                        },
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        App.initialization();
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
                },
                function () {

                },
                {
                    type: 'textarea',
                    maxlength: 999999999,
                    defaultValue: value,
                    confirmText: '确认修改',
                    cancelText: '取消',
                }
            )
        },
        AppAdd() {
            let content = `
<div class="mdui-textfield">
  <label class="mdui-textfield-label">App名称</label>
  <input class="mdui-textfield-input" id="name" type="text"/>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">网站域名(填写-1,自动获取自己店铺域名)</label>
  <input class="mdui-textfield-input" id="url" type="text"/>
</div>
                `;
            mdui.dialog({
                title: '创建App客户端生成任务',
                content: content + '<hr>Ps：创建成功后，可以点击对应内容修改数据！',
                modal: true,
                history: false,
                buttons: [
                    {
                        text: '关闭',
                    },
                    {
                        text: '确认生成',
                        onClick: function () {
                            let is = layer.msg('生成中，请稍后...', {icon: 16, time: 9999999});
                            $.ajax({
                                type: "POST",
                                url: 'ajax.php?act=AppAdd',
                                data: {
                                    'name': $("#name").val(),
                                    'url': $("#url").val(),
                                },
                                dataType: "json",
                                success: function (res) {
                                    layer.close(is);
                                    if (res.code == 1) {
                                        layer.alert(res.msg, {
                                            icon: 1, btn1: function () {
                                                App.initialization();
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
                    }
                ]
            });
        },
        PackageSubmitted(id) {
            layer.open({
                title: '提交打包任务[' + id + ']',
                content: '温馨提示,请确认APP信息正确，提交打包成功后将无法修改数据，若想调整，只能够重新生成APP！',
                btn: ['提交任务', '取消'],
                icon: 3,
                btn1: function () {
                    let is = layer.msg('正在提交中...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: '../ajax/ajax.php?act=CreateAppSubmit',
                        data: {
                            id: id,
                        },
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code >= 0) {
                                layer.alert(res.msg, {
                                    icon: 1, end: function () {
                                        location.reload();
                                    }
                                });
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }
                    });
                }
            })
        },
        FilesUpload(type, id, image, TaskID) {
            if (TaskID != -1) {
                layer.msg('已经提交生成，无法再改变图片！');
                return false;
            }
            let _this = this;
            let content = `
                <div style="text-align: center"><img src="../ajax.php?act=AppImage&id=` + image + `" style="width:100px;min-height:100px;max-height:200px;" /></div><hr>
                <button id="Image" class="layui-btn layui-btn-fluid" style="background-color: #f07178">点击上传更新图片</button>
                `;
            layer.open({
                title: '设置[' + id + ']' + (type == 1 ? 'APP图标' : 'APP启动图'),
                content: content,
                btn: false,
                offset: '100px',
                success: function () {
                    _this.upload.render({
                        elem: '#Image'
                        , url: './ajax.php?act=AppUploading&id=' + id + '&type=' + type
                        , size: 1024 * 2
                        , accept: 'images'
                        , acceptMime: 'image/*'
                        , exts: 'jpg|jpeg|png'
                        , before: function () {
                            tipId1 = layer.msg('正在上传中...', {
                                icon: 16,
                                shade: 0.01,
                                time: 9999999
                            });
                        }
                        , done: function (res) {
                            layer.alert(res.msg, {
                                icon: 1, end: function () {
                                    App.initialization();
                                }
                            });
                        }
                        , error: function () {
                            layer.msg('图片上传失败');
                        }
                    });
                }
            });
        },
        ColorSettings(type, id, color, TaskID) {
            if (TaskID != -1) {
                layer.msg('已经提交，无法再改变颜色！');
                return false;
            }
            let _this = this;
            layer.open({
                title: '设置[' + id + ']' + (type == 1 ? '主题颜色' : '加载条颜色'),
                content: '<div id="color"></div><span id="ColorPreview" style="padding: 1em;">' + color + '</span>',
                btn: ['保存', '取消'],
                btn1: function () {
                    colorset = $("#ColorPreview").text();
                    if (colorset == '' || colorset == color) {
                        layer.msg('颜色不可为空，或颜色无改动', {icon: 2});
                        return false;
                    }
                    let is = layer.msg('正在调整中...', {icon: 16, time: 9999999});
                    $.ajax({
                        type: "POST",
                        url: './ajax.php?act=AppColorSet',
                        data: {
                            id: id,
                            color: colorset,
                            type: type,
                        },
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code >= 0) {
                                layer.alert(res.msg, {
                                    icon: 1, end: function () {
                                        App.initialization();
                                    }
                                });
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }
                    });
                }, success: function () {
                    _this.colorpicker.render({
                        elem: '#color',
                        format: 'hex',
                        predefine: true,
                        colors: color,
                        color: color,
                        change: function (colorS) {
                            $("#ColorPreview").text(colorS);
                        }, done: function (colorS) {
                            $("#ColorPreview").text(colorS);
                        }
                    });
                }
            })
        },
        AppList() {
            let is = layer.msg('任务载入中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST",
                url: './ajax.php?act=AppList',
                data: {
                    page: App.page,
                    limit: App.limit,
                    name: App.name,
                },
                dataType: "json",
                success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.Data = res.data;
                        App.type = 1;
                    } else {
                        App.Data = [];
                        App.type = 1;
                    }
                },
                error: function () {
                    layer.msg('服务器异常！');
                }
            });
        },
        SearchGoods() {
            mdui.prompt('可输入：App名称,ID等', '搜索任务',
                function (str) {
                    App.initialization(str);
                },
                function () {

                },
                {
                    type: 'textarea',
                    maxlength: 999999999,
                    defaultValue: App.name,
                    confirmText: '确认搜索',
                    cancelText: '取消',
                }
            );
        },
        initialization(name = '', limit = -1) {
            this.page = 1;
            this.limit = (limit === -1 ? this.limit : limit);
            if (name == -2) {
                this.name = '';
            } else {
                this.name = (name === '' ? this.name : name);
            }
            this.type = -1;
            layui.use('laypage', function () {
                var laypage = layui.laypage;
                $.ajax({
                    type: "POST",
                    url: './ajax.php?act=AppCount',
                    data: {
                        name: App.name,
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.code == 1) {
                            App.count = res.count;
                            laypage.render({
                                elem: 'Page'
                                , count: res.count
                                , theme: '#641ec6'
                                , limit: App.limit
                                , limits: [1, 10, 20, 30, 50, 100, 200]
                                , groups: 3
                                , first: '首页'
                                , last: '尾页'
                                , prev: '上一页'
                                , next: '下一页'
                                , skip: true
                                , layout: ['count', 'page', 'prev', 'next', 'limit', 'limits']
                                , jump: function (obj) {
                                    App.page = obj.curr;
                                    App.limit = obj.limit;
                                    App.AppList();
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
            });
        }
    }
}).mount('#App');

App.initialization();

layui.use(['upload', 'colorpicker'], function () {
    App.upload = layui.upload;
    App.colorpicker = layui.colorpicker;
});