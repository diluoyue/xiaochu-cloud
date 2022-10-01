<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/10 16:26
// +----------------------------------------------------------------------
// | Filename: admin.login.set.php
// +----------------------------------------------------------------------
// | Explain: 快捷登录配置
// +----------------------------------------------------------------------

$title = '用户后台快捷登录配置';
include 'header.php';
global $cdnserver, $conf;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="mdui-tab mdui-tab-scrollable mdui-tab-centered mdui-p-l-0" mdui-tab>
                    <a href="#Se1" class="mdui-ripple">QQ互联登录配置</a>
                    <a href="#Se2" class="mdui-ripple">微信快捷登录配置</a>
                </div>
                <div class="layui-form card-body mdui-p-a-0">
                    <div id="Se1" class="mdui-p-a-2">
                        <div class="form-group mb-3">
                            <label>登录通道配置</label>
                            <select class="custom-select mt-3" lay-search name="QQInternetChoice">
                                <option <?= $conf['QQInternetChoice'] == 1 ? 'selected' : '' ?> value="1">使用官方内置API登录接口
                                </option>
                                <option <?= $conf['QQInternetChoice'] == 2 ? 'selected' : '' ?> value="2">使用自定义QQ互联登录接口
                                </option>
                                <option <?= $conf['QQInternetChoice'] == -1 ? 'selected' : '' ?> value="-1">关闭QQ互联快捷登录方式
                                </option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>APP ID (仅在使用自定义接口时生效)</label>
                            <input type="text" name="QQInternetID" class="form-control"
                                   value="<?= $conf['QQInternetID'] ?>" placeholder="请填写从QQ互联官网获取的应用ID">
                        </div>
                        <div class="form-group mb-3">
                            <label>APP Key (仅在使用自定义接口时生效)</label>
                            <input type="text" name="QQInternetKey" class="form-control"
                                   value="<?= $conf['QQInternetKey'] ?>" placeholder="请填写从QQ互联官网获取的应用Key">
                        </div>
                        <div class="form-group mb-3">
                            <label>token url (仅在使用自定义接口时生效)</label>
                            <input type="text" name="QQInternetCallback" class="form-control"
                                   value="<?= $conf['QQInternetCallback'] ?>" placeholder="QQ快捷登录回调地址！">
                        </div>

                        <button type="submit" lay-submit lay-filter="Notification_set"
                                class="btn btn-block btn-xs btn-primary">保存内容
                        </button>
                    </div>

                    <div id="Se2" class="mdui-p-a-2">
                        暂未开放！
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card">
            <div class="card-header">注意事项</div>
            <div class="card-body layui-text">
                <ul>
                    <li>1、此处的QQ快捷登录内需要填写的参数均在QQ互联官方接口内生成，生成地址：<a href="https://connect.qq.com/" target="_blank">https://connect.qq.com/</a>
                    </li>
                    <li>2、当站点有用户使用QQ快捷登录后，<font color="red">请不要切换登录通道</font>，如官方通道切换为自定义等，会导致原来的用户无法登录,直接创建一个新账户！</li>
                    <li>3、token url 参数会自动生成，如有需要可修改域名，不可修改后面的回调地址 <font color="red">/includes/LoginCallback.php</font>参数
                    </li>
                    <li>4、用户唯一密钥是根据QQ互联官方接口内的 <font color="red">unionid</font> 参数生成，待应用审核通过后，可点击：<font color="red">查看->接口名称->申请</font>
                    </li>
                    <li>
                        5、当应用失效或被删除后，可以重新创建应用，需在同一个QQ互联开发者账户下创建应用，用户可以通过新的应用进行登录，不会重新创建新用户！
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card">
            <div class="card-header">申请教程</div>
            <div class="card-body layui-text">
                <ul>
                    <li>
                        1、打开QQ互联官网：<a href="https://connect.qq.com/" target="_blank">https://connect.qq.com/</a>，点击应用管理，进行开发者认证
                    </li>
                    <li>
                        2、认证完成后，点击网站应用，点击创建应用按钮，点击创建网站应用，按照提示填写相关资料
                    </li>
                    <li>3、其中，网站地址填写：<?= href(2) ?>/</li>
                    <li>
                        4、网站回调地址填写：<?= href(2) ?>/includes/LoginCallback.php
                    </li>
                    <li>
                        5、全部提交完成后，点击创建应用，等待审核完成！，审核完成后，点击查看->应用接口->点击申请按钮 申请unionid(平台统一ID信息)
                    </li>
                    <li>
                        6、以上操作全部成功后，点击查看，复制应用名称下方的：APP ID 和 APP Key 的内容，复制后填写到上方即可！，token url填写应用内的网站回调域！
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script>
    layui.use('form', function () {
        var form = layui.form;

        form.on('submit(Notification_set)', function (data) {
            layer.alert('是否要保存当前页面全部配置数据？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function () {
                    let is = layer.msg('加载中，请稍后...', {
                        icon: 16,
                        time: 9999999
                    });
                    $.ajax({
                        type: "POST",
                        url: 'ajax.php?act=config_set',
                        data: data.field,
                        dataType: "json",
                        success: function (res) {
                            layer.close(is);
                            if (res.code == 1) {
                                layer.alert(res.msg, {
                                    icon: 1,
                                    btn1: function () {
                                        location.reload();
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
            });
        });
    });
</script>
