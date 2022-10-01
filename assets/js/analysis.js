/**
 * 二维码图片解析
 */

layui.use(["upload"], function () {
    layui.upload.render({
        elem: "#QrId",
        accept: "images",
        acceptMime: "image/*",
        exts: "jpg|png|gif|jpeg",
        url: "./QrReader.php?act=Decode",
        size: 2048,
        done: function (res, index, upload) {
            layer.open({
                title: "恭喜",
                content: res.msg,
                btn: ["插入解析结果", "取消"],
                btn1: function () {
                    console.log(vm.form.length);
                    if (vm.form.length === 1) {
                        vm.form[0] = res.content;
                    } else {
                        let i = 0;
                        for (const argument of vm.form) {
                            if (argument === '请点击按钮上传需要解析的图片！') {
                                vm.form[i] = res.content;
                            }
                            ++i;
                        }
                    }
                    layer.closeAll();
                }
            });
        },
        error: function () {
            layer.msg("需解析的二维码上传失败!");
        }
    });
});



