const App = Vue.createApp({
    data() {
        return {
            TemData: false, Banner: [],
        }
    }, watch: {
        "TemData.conf.background": {
            handler(value, tv) {
                if (tv === false) {
                    return false;
                }
                App.TemAjax(value, 'background');
            }
        }, "TemData.conf.cdnpublic": {
            handler(value, tv) {
                if (tv === false) {
                    return false;
                }
                App.TemAjax(value, 'cdnpublic');
            }
        }, "TemData.conf.cdnserver": {
            handler(value, tv) {
                if (tv === false) {
                    return false;
                }
                App.TemAjax(value, 'cdnserver');
            }
        }
    }, methods: {
        TemplateSelection(index, data, type) {
            if (data === false) {
                let msg = '';
                if (index == -1) {
                    msg = '是否要关闭' + (type == 1 ? 'PC端模板' : '移动端模板') + '？';
                } else if (index == -2) {
                    msg = '是否要将PC端模板设置为套娃模式(PC端镶套手机端模板)？';
                } else {
                    msg = '是否要将' + (type == 1 ? 'PC端模板' : '移动端模板') + '设置为' + index + '？';
                }
                layer.open({
                    title: '温馨提示', content: msg, icon: 3, btn: ['确定', '取消'], btn1: function () {
                        App.TemAjax(index, (type == 1 ? 'template' : 'template_m'));
                    }
                })
            } else {
                let content = data.content + '<br><br>';
                console.log(data);
                for (const key in data.extend) {
                    let arr = data.extend[key];
                    if (arr.type === 1) {
                        content += `
<div class="mdui-textfield">
  <input type="text" id="` + key + `" value="` + arr.value + `" class="mdui-textfield-input" placeholder="` + arr.name + `"/>
  <div class="mdui-textfield-helper">` + arr.Tips + `</div>
</div>`;
                    } else {
                        let se = ``;
                        for (const arrKey in arr.data) {
                            se += `<option ` + (arr.value == arrKey ? 'selected' : '') + ` value="` + arrKey + `">` + arr.data[arrKey] + `</option>`;
                        }
                        content += `
<div class="mdui-textfield">
    <div style="height:2em;line-height:2em;">` + arr.name + `</div>
    <select id="` + key + `" class="mdui-select" style="width:100%;font-size:14px;color: rgba(29,29,29,0.77)">
        ` + se + `
    </select>
<div style="margin-top: 0.2em;font-size:12px;color:rgba(0,0,0,.54)">` + arr.Tips + `</div>
</div>
                            `;
                    }
                }

                mdui.dialog({
                    title: '模板配置 - ' + data.name + '(v' + data.version + ')',
                    content: content,
                    modal: true,
                    history: false,
                    buttons: [{
                        text: '关闭',
                    }, {
                        text: '保存数据,并设置为' + (type == 1 ? 'PC端模板' : '移动端模板'), onClick: function () {
                            let Json = data;
                            for (const key in data.extend) {
                                let arr = data.extend[key];
                                let value = $("#" + key).val();
                                if (value == '') {
                                    layer.open({
                                        title: '警告', content: '请填写完整！', icon: 2, btn: ['好的'], btn1: function () {
                                            layer.closeAll();
                                            App.TemplateSelection(index, data, type);
                                        }
                                    });
                                    return false;
                                }
                                Json.extend[key]['value'] = value;
                            }
                            App.TemAjax(index, (type == 1 ? 'template' : 'template_m'), JSON.stringify(Json));
                        }
                    }],
                    onOpen: function () {
                        mdui.mutation();
                    }
                });
            }
        }, TemAjax(value, field, json = false, msg = '保存成功') {
            let is = layer.msg('处理中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=TemConfSet', data: {
                    value: value, field: field, json: json,
                }, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        layer.alert(msg, {
                            icon: 1, btn1: function () {
                                App.DataList();
                            }
                        });
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }, BannerCloseAll() {
            if (App.BannerSet(2) === '') {
                layer.msg('没有可以清空的内容！', {icon: 2});
                return;
            }
            layer.open({
                title: '警告', content: '是否要清空全部横幅广告？', btn: ['确定', '取消'], icon: 3, btn1: function () {
                    App.TemAjax('', 'banner', false, '清空成功');
                }
            });
        }, BannerSetAll() {
            let content = App.BannerSet(2);
            content = content.split('|');
            content = content.join("\n");
            layer.prompt({
                formType: 2,
                value: content,
                title: '一行一条规则,[*]分割跳转链接！',
                maxlength: 99999999999,
                area: ['350px', '350px']
            }, function (value, index, elem) {
                let content = value.split("\n");
                content = content.join('|');
                App.TemAjax(content, 'banner');
                layer.close(index);
            });
        }, BannerAdd() {
            let content = `
<div class="mdui-textfield">
  <label class="mdui-textfield-label">图片地址</label>
  <input class="mdui-textfield-input" id="imagese"  type="text"/>
  <div class="mdui-textfield-helper">
    <span id="ImageUpdate" class="badge badge-success-lighten">上传图片</span>
    <a href="http://cloud.79tian.com/login" target="_blank"  class="badge badge-primary-lighten">免费云盘</a>
  </div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">跳转链接</label>
  <input class="mdui-textfield-input" id="urlse" type="text"/>
</div>
                `;
            mdui.dialog({
                title: '添加一条横幅广告', content: content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }, {
                    text: '保存', onClick: function () {
                        let image = $("#imagese").val();
                        let url = $("#urlse").val();
                        if (image === '' || url === '') {
                            layer.msg('请填写完整！', {icon: 2});
                            return false;
                        }
                        let content = App.BannerSet(2);
                        if (content === '') {
                            content = image + '*' + url;
                        } else {
                            content = image + '*' + url + '|' + content;
                        }
                        App.TemAjax(content, 'banner');
                    }
                }], onOpen: function () {
                    layui.use(['upload'], function () {
                        layui.upload.render({
                            elem: '#ImageUpdate',
                            url: 'ajax.php?act=image_up',
                            acceptMime: 'image/*',
                            accept: 'images',
                            done: function (res, index, upload) {
                                layer.msg(res.msg, {icon: 1});
                                $("#imagese").val(res.src);
                            },
                            error: function () {
                                layer.msg('图片上传失败!')
                            }
                        });
                    });
                    mdui.mutation();
                }
            });
        }, OpenBanner(index, data) {
            let content = `
<div class="mdui-textfield">
  <label class="mdui-textfield-label">图片地址</label>
  <input class="mdui-textfield-input" id="imagese" value="` + data.image + `" type="text"/>
  <div class="mdui-textfield-helper">
    <span id="ImageUpdate" class="badge badge-success-lighten">上传图片</span>
    <a href="http://cloud.79tian.com/login" target="_blank"  class="badge badge-primary-lighten">免费云盘</a>
  </div>
</div>
<div class="mdui-textfield">
  <label class="mdui-textfield-label">跳转链接</label>
  <input class="mdui-textfield-input" id="urlse" value="` + data.url + `" type="text"/>
</div>
                `;

            mdui.dialog({
                title: '编辑此横幅广告 - ' + (index - 0 + 1), content: content, modal: true, history: false, buttons: [{
                    text: '关闭',
                }, {
                    text: '删除', onClick: function () {
                        let arr = {};
                        for (const bannerKey in App.Banner) {
                            if (bannerKey !== index) {
                                arr[bannerKey] = App.Banner[bannerKey];
                            }
                        }
                        App.Banner = arr;
                        let content = App.BannerSet(2);
                        App.TemAjax(content, 'banner', false, '删除成功');
                    }
                }, {
                    text: '保存', onClick: function () {
                        App.Banner[index]['image'] = $("#imagese").val();
                        App.Banner[index]['url'] = $("#urlse").val();
                        let content = App.BannerSet(2);
                        App.TemAjax(content, 'banner');
                    }
                }, {
                    text: '打开', onClick: function () {
                        open(data.url);
                    }
                }], onOpen: function () {
                    layui.use(['upload'], function () {
                        layui.upload.render({
                            elem: '#ImageUpdate',
                            url: 'ajax.php?act=image_up',
                            acceptMime: 'image/*',
                            accept: 'images',
                            done: function (res, index, upload) {
                                layer.msg(res.msg, {icon: 1});
                                $("#imagese").val(res.src);
                            },
                            error: function () {
                                layer.msg('图片上传失败!')
                            }
                        });
                    });
                    mdui.mutation();
                }
            });
        }, BannerSet(type = 1) {
            if (type === 1) {
                if (App.TemData.conf.banner == '') {
                    App.Banner = [];
                    return [];
                }
                let content = App.TemData.conf.banner.split('|');
                let Data = {};
                let i = 0;
                for (const key in content) {
                    if (content[key] === '' || content[key] === undefined) {
                        continue;
                    }
                    let arr = content[key].split('*');
                    Data[i] = {
                        'image': arr[0], 'url': arr[1]
                    };
                    ++i;
                }
                App.Banner = Data;
                return Data;
            } else {
                if (App.Banner.length === 0) {
                    App.TemData.conf.banner = '';
                    return '';
                }
                let content = [];
                let i = 0;
                for (const key in App.Banner) {
                    if (App.Banner[key]['image'] === '' || App.Banner[key]['image'] === undefined || App.Banner[key]['url'] === '' || App.Banner[key]['url'] === undefined) {
                        continue;
                    }
                    content[i] = App.Banner[key]['image'] + '*' + App.Banner[key]['url'];
                    ++i;
                }
                App.TemData.conf.banner = content.join('|');
                return App.TemData.conf.banner;
            }
        }, DataList() {
            let is = layer.msg('数据获取中，请稍后...', {icon: 16, time: 9999999});
            $.ajax({
                type: "POST", url: 'main.php?act=TemData', data: {}, dataType: "json", success: function (res) {
                    layer.close(is);
                    if (res.code == 1) {
                        App.TemData = res;
                        App.BannerSet();
                    } else {
                        layer.alert(res.msg, {
                            icon: 2
                        });
                    }
                }, error: function () {
                    layer.msg('服务器异常！');
                }
            });
        }
    }
}).mount('#App');
App.DataList();