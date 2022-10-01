<?php

/**
 * 短信配置
 */
$title = '短信通知';
include 'header.php';
global $conf;
?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="mdui-tab mdui-tab-scrollable mdui-tab-centered mdui-p-l-0" mdui-tab>
                    <a href="#Se1" class="mdui-ripple">短信基础配置</a>
                    <a href="#Se2" class="mdui-ripple">自定义短信配置</a>
                </div>
                <div id="Se1" class="mdui-p-a-2">
                    <form class="form-horizontal layui-form">
                        <div class="form-group mb-3">
                            <label for="example-input-normal">短信开关(购买提醒/用户)</label>
                            <select class="custom-select mt-3" lay-search name="sms_switch_order">
                                <option <?= $conf['sms_switch_order'] == -1 ? 'selected' : '' ?> value="-1">关闭短信验证
                                </option>
                                <option <?= $conf['sms_switch_order'] == 1 ? 'selected' : '' ?> value="1">开启短信验证
                                </option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal"
                                   style="font-weight: 500">手机号绑定(可用来登陆站长后台，需消耗你的短信额度)</label>
                            <input type="text" name="Mobile" lay-verify="required" class="form-control" value=""
                                   placeholder="请输入你的手机号！">
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">我的短信额度</label>
                            <input type="text" name="sms" class="form-control" value="0条" disabled
                                   placeholder="剩余短信额度！">
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">今日发送短信</label>
                            <input type="text" name="sms_today" class="form-control" value="0条" disabled
                                   placeholder="今日发送短信！">
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">累计发送短信</label>
                            <input type="text" name="sms_total" class="form-control" value="0条" disabled
                                   placeholder="累计发送短信！">
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">服务端余额</label>
                            <input type="text" name="money" class="form-control" value="0元" disabled
                                   placeholder="服务端余额！">
                        </div>

                        <button type="submit" lay-submit lay-filter="Notification_set"
                                class="btn btn-block btn-xs btn-success">保存内容
                        </button>
                    </form>
                </div>
                <div id="Se2" class="mdui-p-a-2">
                    <form class="form-horizontal layui-form">
                        <div class="form-group mb-3">
                            <label for="example-input-normal">短信对接通道</label>
                            <select class="custom-select mt-3" lay-search name="SMSChannelConfiguration">
                                <option <?= $conf['SMSChannelConfiguration'] == 1 ? 'selected' : '' ?> value="1">官方内置[切换此接口时，下方无需配置]
                                </option>
                                <option <?= $conf['SMSChannelConfiguration'] == 2 ? 'selected' : '' ?> value="2">阿里云
                                </option>
                                <option disabled <?= $conf['SMSChannelConfiguration'] == 3 ? 'selected' : '' ?>
                                        value="3">腾讯云【未开放】
                                </option>
                                <option disabled <?= $conf['SMSChannelConfiguration'] == 4 ? 'selected' : '' ?>
                                        value="4">七牛云【未开放】
                                </option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal"
                                   style="font-weight: 500">短信签名【签名名称】</label>
                            <input type="text" name="SMSSignName" lay-verify="required" class="form-control"
                                   value="<?= $conf['SMSSignName'] ?>"
                                   placeholder="请输入短信签名名称！">
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal"
                                   style="font-weight: 500">短信KeyID</label>
                            <input type="text" name="SMSAccessKeyId" lay-verify="required" class="form-control"
                                   value="<?= $conf['SMSAccessKeyId'] ?>"
                                   placeholder="请输入短信KeyID">
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal"
                                   style="font-weight: 500">短信KeySecret</label>
                            <input type="text" name="SMSAccessKeySecret" lay-verify="required" class="form-control"
                                   value="<?= $conf['SMSAccessKeySecret'] ?>"
                                   placeholder="请输入短信KeySec">
                        </div>

                        <div class="form-group mb-3">
                            <label for="example-input-normal"
                                   style="font-weight: 500">模板CODE[验证码]</label>
                            <input type="text" name="SMSTemplateCode" lay-verify="required" class="form-control"
                                   value="<?= $conf['SMSTemplateCode'] ?>"
                                   placeholder="请输入短信模板CODE[验证码]">
                        </div>

                        <button type="submit" lay-submit lay-filter="Notification_set"
                                class="btn btn-block btn-xs btn-success">保存内容
                        </button>

                        <div style="margin-top: 1em;text-align: center">
                            <a href="https://ram.console.aliyun.com/manage/ak" target="_blank ">https://ram.console.aliyun.com/manage/ak【阿里云】</a>取得您的AK信息<br>
                            <a href="https://blog.csdn.net/u010385623/article/details/113773339" target="_blank ">https://blog.csdn.net/u010385623/article/details/113773339【阿里云】</a>查看短信信息获取教程
                        </div>
                    </form>
                </div>
                <div style="text-align: center">
                    <span class="mt-3" style="
                        display: block">短信额度充值请前往授权服务端<a href="https://m3w.cn/xcy888" target="_blank"
                                                         style="color: #000"><span class="badge badge-primary-lighten">晴玖服务端管理后台</span>( 进入）</a></span>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'bottom.php'; ?>

<script>
    layui.use('form', function () {
        var form = layui.form;
        form.on('submit(Notification_set)', function (data) {
            layer.alert('是否要执行当前操作？', {
                icon: 3,
                btn: ['确定', '取消'],
                btn1: function (layero, index) {
                    var index = layer.msg('数据保存中,请稍后...', {
                        icon: 16,
                        time: 999999
                    });
                    $.post('ajax.php?act=config_set_sms', data.field, function (res) {
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
    $.post('ajax.php?act=sms_data', function (res) {
        if (res.code < 0) {
            layer.msg(res.msg);
        } else {
            $("input[name='money']").val(res.data.money + '元');
            $("input[name='sms']").val(res.data.sms + '条');
            $("input[name='Mobile']").val((res.data.Mobile == 0 ? '' : res.data.Mobile));
            $("input[name='sms_today']").val(res.data.sms_today + '条');
            $("input[name='sms_total']").val(res.data.sms_total + '条');
        }
    })
</script>
