let ArrayE = {
    'notice_top': 'contentHtml1',
    'notice_check': 'contentHtml2',
    'notice_bottom': 'contentHtml3',
    'notice_user': 'contentHtml4',
    'PopupNotice': 'contentHtml5',
    'ServiceTips': 'contentHtml6',
    'statistics': 'contentHtml7',
    'HostAnnounced': 'contentHtml8',
};
let Name = {};
for (const eKey in ArrayE) {
    Name[eKey] = new window.wangEditor('#' + ArrayE[eKey]);
    Name[eKey].config.zIndex = 100;
    Name[eKey].config.uploadImgAccept = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    Name[eKey].config.uploadVideoAccept = ['mp4'];
    Name[eKey].config.uploadImgMaxSize = 10 * 1024 * 1024;
    Name[eKey].config.uploadVideoMaxSize = 100 * 1024 * 1024;
    Name[eKey].config.uploadImgMaxLength = 30;
    Name[eKey].config.zIndex = 1;
    Name[eKey].config.customUploadImg = function (resultFiles, insertImgFn) {
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

    Name[eKey].config.customUploadVideo = function (resultFiles, insertVideoFn) {
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

    Name[eKey].config.onchange = function (html) {
        $('#' + eKey).val(html);
    }
    Name[eKey].create();
    $('#' + eKey).val(Name[eKey].txt.html());
}


layui.use('form', function () {
    var form = layui.form;
    form.on('submit(Notification_set)', function (data) {
        for (const eKey in ArrayE) {
            data.field[eKey] = Name[eKey].txt.html();
        }

        console.log(data.field);

        layer.alert('是否要执行当前操作', {
            icon: 3, btn: ['确定', '取消'], btn1: function (layero, index) {
                var index = layer.msg('数据保存中,请稍后...', {
                    icon: 16, time: 999999
                });
                $.post('ajax.php?act=config_set', data.field, function (res) {
                    if (res.code == 1) {
                        layer.close(index);
                        layer.alert(res.msg, {
                            btn1: function (layero, index) {
                                location.reload();
                            }
                        });
                    } else {
                        layer.close(index);
                        layer.alert(res.msg, {
                            btn1: function (layero, index) {
                                location.reload();
                            }
                        });
                    }
                });
            }
        });
        return false;
    })
});
$("#query_xc").click(function () {
    var index2 = layer.prompt({
        formType: 3, value: '', title: '请输入晴玖商城站点,需加http://',
    }, function (value, index, elem) {
        var index = layer.msg('公告数据获取中,请稍后...', {
            icon: 16, time: 999999
        });
        $.post('ajax.php?act=Get_notice_xc', {
            url: value
        }, function (res) {
            if (res.code == 1) {
                layer.closeAll();
                $.each(res.data, function (index, value) {
                    Name[index].txt.html(value);
                });
                layer.alert(res.msg, {
                    icon: 1
                });
            } else {
                layer.closeAll();
                layer.alert(res.msg, {
                    icon: 2
                });
            }
        });
    });
});

$("#query_ch").click(function () {
    var index2 = layer.prompt({
        formType: 3, value: '', title: '请输入彩虹代刷站点,需加http://',
    }, function (value, index, elem) {
        var index = layer.msg('公告数据获取中,请稍后...', {
            icon: 16, time: 999999
        });
        $.post('ajax.php?act=Get_notice_ch', {
            url: value
        }, function (res) {
            layer.closeAll();
            if (res.code == 1) {
                $.each(res.data, function (index, value) {
                    Name[index].txt.html(value);
                });
                layer.alert(res.msg, {
                    icon: 1
                });
            } else {
                layer.closeAll();
                layer.alert(res.msg, {
                    icon: 2
                });
            }
        });
    });
})

function EmptyDocs(name) {
    Name[name].txt.clear();
    layer.msg('清空成功！', {icon: 1});
}


function HtmlDocs(name) {
    let Html = Name[name].txt.html();
    let is = layer.prompt({
        formType: 2, value: Html, maxlength: 9999999999999999, title: '编辑原始HTML代码', area: ['80vw', '80vh']
    }, function (value) {
        Name[name].txt.html(value);
        layer.close(is);
        layer.msg('设置成功！', {icon: 1});
    });
}