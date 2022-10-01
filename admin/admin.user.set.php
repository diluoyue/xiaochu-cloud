<?php

/**
 * 加盟配置
 */
$title = '用户配置';
include 'header.php';
global $conf, $accredit;
$paytype = explode(',', $conf['userdeposittype']);
?>
<style>
    label {
        font-weight: 400;
    }
</style>
<div class="row">
    <div class=" col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="mdui-tab mdui-tab-scrollable mdui-tab-centered mdui-p-l-0" mdui-tab>
                    <a href="#Se1" class="mdui-ripple mdui-tab-active">分店相关配置</a>
                    <a href="#Se2" class="mdui-ripple">域名相关配置</a>
                    <a href="#Se3" class="mdui-ripple">提现相关配置</a>
                    <a href="#Se4" class="mdui-ripple">其他基础配置</a>
                    <a href="#Se5" class="mdui-ripple">每日签到配置</a>
                </div>
            </div>
            <div class="layui-form card-body mdui-p-a-0">
                <div id="Se1" class="mdui-p-a-2">
                    <div class="form-group mb-3">
                        <label>分店功能总开关</label>
                        <select class="custom-select mt-3" lay-search name="userleague">
                            <option <?= $conf['userleague'] == 1 ? 'selected' : '' ?> value="1">开启分店功能，允许用户对站内商品进行分销
                            </option>
                            <option <?= $conf['userleague'] == -1 ? 'selected' : '' ?> value="-1">关闭分店功能，用户无法对站内商品进行分销
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>无限级分成开关[无视用户等级进行分成,只要是上下级关系都可以进行分成]</label>
                        <select class="custom-select mt-3" lay-search name="InfiniteDivision">
                            <option <?= $conf['InfiniteDivision'] == 1 ? 'selected' : '' ?>
                                    value="1">开启无限级分店分成
                            </option>
                            <option <?= $conf['InfiniteDivision'] == -1 ? 'selected' : '' ?>
                                    value="-1">关闭无限级分店分成
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>是否强制将用户提成分配给上级</label>
                        <select class="custom-select mt-3" lay-search name="CommitsDistribute">
                            <option <?= $conf['CommitsDistribute'] == 1 ? 'selected' : '' ?>
                                    value="1">开启强制提成分配模式[无论用户去其他分站,或主站下单,上级都可以获得提成]
                            </option>
                            <option <?= $conf['CommitsDistribute'] == -1 ? 'selected' : '' ?>
                                    value="-1">关闭强制提成分配模式[仅分配给当前下单站点]
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>用户升级金额归属</label>
                        <select class="custom-select mt-3" lay-search name="usergradecost">
                            <option <?= $conf['usergradecost'] == 1 ? 'selected' : '' ?> value="1">升级提成奖励给上级</option>
                            <option <?= $conf['usergradecost'] == -1 ? 'selected' : '' ?> value="-1">不奖励给上级(下方分成配置无效)
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>升级奖励分成百分比</label>
                        <input type="number" name="userupgradeprofit" class="form-control"
                               value="<?= $conf['userupgradeprofit'] ?>" placeholder="请填写完整">
                        <span>和主站分成的比例,可填写0到100,代表0%-100%,如填写90，则用户获得90%，主站获得10%</span>
                    </div>
                    <div class="form-group mb-3">
                        <label>多少级的用户可以开通分店?</label>
                        <input type="number" name="userleaguegrade" class="form-control"
                               value="<?= $conf['userleaguegrade'] ?>"
                               placeholder="当用户等级大于等于此参数时，可以开通并管理自己的分店,协助卖货！">
                    </div>
                    <div class="form-group mb-3">
                        <label>多少级的用户可以配置分店基础参数?</label>
                        <input type="number" name="usergradenotice" class="form-control"
                               value="<?= $conf['usergradenotice'] ?>"
                               placeholder="当用户等级大于或等于此参数时，可自己配置分店基础的信息,如公告,客服等">
                    </div>
                    <div class="form-group mb-3">
                        <label>多少级的用户可以配置分店商品利润?</label>
                        <input type="number" name="usergradeprofit" class="form-control"
                               value="<?= $conf['usergradeprofit'] ?>"
                               placeholder="请填写完整">
                    </div>

                    <div class="form-group mb-3">
                        <label>多少级可以自定义分店商品状态(上下架)？</label>
                        <input type="number" name="usergradegoodsstate" class="form-control"
                               value="<?= $conf['usergradegoodsstate'] ?>" placeholder="请填写完整">
                    </div>
                    <div class="form-group mb-3">
                        <label>多少级的用户可以自定义店铺首页模板？</label>
                        <input type="number" name="usergradetem" class="form-control"
                               value="<?= $conf['usergradetem'] ?>"
                               placeholder="请填写完整">
                    </div>

                    <div class="form-group mb-3">
                        <label>新用户注册后的默认等级是多少?</label>
                        <input type="number" name="userdefaultgrade" class="form-control"
                               value="<?= $conf['userdefaultgrade'] ?>" placeholder="请填写完整">
                    </div>
                </div>
                <div id="Se2" class="mdui-p-a-2">
                    <div class="form-group mb-3">
                        <label>分店域名绑定模式</label>
                        <select class="custom-select mt-3" lay-search name="userdomaintype">
                            <option <?= $conf['userdomaintype'] == 1 ? 'selected' : '' ?>
                                    value="1">泛解析模式,用户可自配置域名前缀,通过域名前缀来区分店铺
                            </option>
                            <option <?= $conf['userdomaintype'] == 2 ? 'selected' : '' ?>
                                    value="2">Cookie缓存模式，通过域名后缀判断店铺,无需泛解析
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>可供用户分店绑定的域名主体</label>
                        <input type="text" name="userdomain" class="form-control" value="<?= $conf['userdomain'] ?>"
                               placeholder="请填写完整"/>
                        <br>
                        <span color="#40e0d0">多个域名主体可用英文【,】逗号分割！,下方为域名拆分示例<br>
                        <span style="color:red">完整域名</span>：www.baidu.com<br><span style="color:red">域名前缀</span>：www （泛解析模式时用户可自定义）<br><span
                                    style="color:red">域名主体</span>：baidu.com （可以不授权）
                    </span>
                    </div>
                    <div class="form-group mb-3">
                        <label>主站保留域名前缀/后缀,用户不可选，多个则英文逗号分割【,】</label>
                        <input type="text" name="userdomainretain" class="form-control"
                               value="<?= $conf['userdomainretain'] ?>"
                               placeholder="请填写完整">
                        <br>
                        <span>
                        当配置参数为【www,m】时，用户设置分店域名时就无法设置www和m两个参数作为域名前缀或域名小尾巴后缀
                    </span>
                    </div>
                    <div class="form-group mb-3">
                        <label>用户修改分店域名的价格是多少，单位/元</label>
                        <input type="text" name="userdomainsetmoney" class="form-control"
                               value="<?= $conf['userdomainsetmoney'] ?>" placeholder="请填写完整">
                    </div>

                    <div class="form-group mb-3">
                        <label>是否允许用户访问其他未绑定的分店域名?</label>
                        <select class="custom-select mt-3" lay-search name="userbinding">
                            <option <?= $conf['userbinding'] == 1 ? 'selected' : '' ?> value="1">允许访问
                            </option>
                            <option <?= $conf['userbinding'] == -1 ? 'selected' : '' ?> value="-1">禁止访问
                            </option>
                        </select>
                        <br>
                        <span>
                        当前默认放行域名：<a target="_blank"
                                    href="<?= is_https(false) . $accredit['url'] ?>"><?= $accredit['url'] ?></a>，<a
                                    target="_blank"
                                    href="<?= is_https(false) . 'www.' . $accredit['url'] ?>">www.<?= $accredit['url'] ?></a><br>
                        域名修改文件地址：<font color="#40e0d0">includes/deploy.php</font><br>
                        Ps：当禁止用户访问未绑定域名时，用户仅可访问上方默认放行域名和已经绑定过分店的域名,其他一律拦截！，无法进入本站！
                    </span>
                    </div>
                </div>
                <div id="Se3" class="mdui-p-a-2">
                    <div class="form-group mb-3">
                        <label>用户提现总开关</label>
                        <select class="custom-select mt-3" lay-search name="userdeposit">
                            <option <?= $conf['userdeposit'] == 1 ? 'selected' : '' ?> value="1">允许用户提现
                            </option>
                            <option <?= $conf['userdeposit'] == -1 ? 'selected' : '' ?> value="-1">禁止用户提现
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>用户多少级才可以进行余额提现?</label>
                        <input type="number" name="userdepositgrade" class="form-control"
                               value="<?= $conf['userdepositgrade'] ?>" placeholder="请填写完整">
                    </div>

                    <div class="form-group mb-3">
                        <label>用户提现的手续费是多少( 5 = 5% )？</label>
                        <input type="number" name="userdepositservice" class="form-control"
                               value="<?= $conf['userdepositservice'] ?>" placeholder="请填写完整">
                    </div>

                    <div class="form-group mb-3">
                        <label>用户最低提现金额是多少(元)？</label>
                        <input type="number" name="userdepositmin" class="form-control"
                               value="<?= $conf['userdepositmin'] ?>"
                               placeholder="请填写完整">
                    </div>

                    <div class="form-group mb-3">
                        <label>充值卡购买地址，留空后不显示</label>
                        <input type="text" name="rechargepurchaseurl" class="form-control"
                               value="<?= $conf['rechargepurchaseurl'] ?>" placeholder="请填写充值卡购买地址，可留空">
                    </div>

                    <div class="form-group mb-3">
                        <label>支持的提现收款方式：</label>
                        <div class="layui-input-inline">
                            <input type="checkbox" name="userdeposittype[]" title="QQ"
                                   lay-skin="primary" <?= ($paytype[0] == 1 ? 'checked' : '') ?>>
                            <input type="checkbox" name="userdeposittype[]" title="微信"
                                   lay-skin="primary" <?= ($paytype[1] == 1 ? 'checked' : '') ?>>
                            <input type="checkbox" name="userdeposittype[]" title="支付宝"
                                   lay-skin="primary" <?= ($paytype[2] == 1 ? 'checked' : '') ?>>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal">最大最小充值金额范围，用-分割[左最小，右最大]</label>
                        <input type="text" name="RechargeLimit" class="form-control"
                               value="<?= $conf['RechargeLimit'] ?>" placeholder="用户后台充值金额范围,中间使用 - 分隔">
                    </div>
                </div>
                <div id="Se4" class="mdui-p-a-2">
                    <div class="form-group mb-3">
                        <label for="example-input-normal">用户系统总开关</label>
                        <select class="custom-select mt-3" lay-search name="ShutDownUserSystem">
                            <option <?= $conf['ShutDownUserSystem'] == 1 ? 'selected' : '' ?> value="1">开启用户系统
                            </option>
                            <option <?= $conf['ShutDownUserSystem'] == -1 ? 'selected' : '' ?> value="-1">关闭用户系统
                            </option>
                        </select>
                        <div class="mt-1" style="color: red">关闭后用户将无法进入用户后台,并且他人无法对接您的站点,用户只能够用游客状态购买您站点的商品!
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal">是否开启邀请注册功能</label>
                        <select class="custom-select mt-3" lay-search name="inItRegister">
                            <option <?= $conf['inItRegister'] == 1 ? 'selected' : '' ?> value="1">开启强制邀请注册
                            </option>
                            <option <?= $conf['inItRegister'] == 2 ? 'selected' : '' ?> value="2">关闭强制邀请注册
                            </option>
                        </select>
                        <div class="mt-1" style="color: red">开启后，其他用户只能够通过访问已注册用户后台的邀请链接才可以注册成为新用户
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal">关闭用户系统的原因说明,开启时此项无效，填写纯文字！</label>
                        <input type="text" name="ShutDownUserSystemCause" class="form-control"
                               value="<?= $conf['ShutDownUserSystemCause'] ?>" placeholder="关闭用户系统的原因">
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal">用户账户密码登陆/注册</label>
                        <select class="custom-select mt-3" name="AccountPasswordLogin">
                            <option <?= $conf['AccountPasswordLogin'] == 1 ? 'selected' : '' ?> value="1">开启账号密码登陆/注册
                            </option>
                            <option <?= $conf['AccountPasswordLogin'] == 2 ? 'selected' : '' ?> value="2">关闭账号密码登陆/注册
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal">用户后台手机号登陆/注册</label>
                        <select class="custom-select mt-3" lay-search name="sms_switch_user">
                            <option <?= $conf['sms_switch_user'] == -1 ? 'selected' : '' ?> value="-1">关闭手机号登陆/注册
                            </option>
                            <option <?= $conf['sms_switch_user'] == 1 ? 'selected' : '' ?> value="1">开启手机号登陆/注册
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>用户注册总开关</label>
                        <select class="custom-select mt-3" lay-search name="userregister">
                            <option <?= $conf['userregister'] == 1 ? 'selected' : '' ?> value="1">开启新用户注册
                            </option>
                            <option <?= $conf['userregister'] == -1 ? 'selected' : '' ?> value="-1">关闭新用户注册
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal">强制用户登陆</label>
                        <select class="custom-select mt-3" name="ForcedLanding">
                            <option <?= $conf['ForcedLanding'] == 1 ? 'selected' : '' ?> value="1">不开启用户强制登陆
                            </option>
                            <option <?= $conf['ForcedLanding'] == 2 ? 'selected' : '' ?>
                                    value="2">开启用户强制登陆(打开首页就跳转到登陆界面)
                            </option>
                            <option <?= $conf['ForcedLanding'] == 3 ? 'selected' : '' ?>
                                    value="3">开启用户强制登陆(只在下单的时候才提示需要登陆)
                            </option>
                        </select>
                        <div class="mt-1" style="color: red">开启后用户必须登陆才可以购买商品！
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal">用户邀请其他用户奖励多少积分</label>
                        <input type="text" name="award" lay-verify="required" class="form-control"
                               value="<?= $conf['award'] ?>"
                               placeholder="每邀请1人奖励的<?= $conf['currency'] ?>数量">
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal">用户系统虚拟货币名称</label>
                        <input type="text" name="currency" lay-verify="required" class="form-control"
                               value="<?= $conf['currency'] ?>" placeholder="网站货币的名称">
                    </div>
                    <div class="form-group mb-3">
                        <label>用户每日绑定手机号短信发送次数限制</label>
                        <input type="number" name="usersmsbinding" class="form-control"
                               value="<?= $conf['usersmsbinding'] ?>"
                               placeholder="请填写完整">
                    </div>

                    <div class="form-group mb-3">
                        <label>用户每日登陆用户后台短信发送次数限制</label>
                        <input type="number" name="usersmslogin" class="form-control"
                               value="<?= $conf['usersmslogin'] ?>"
                               placeholder="请填写完整">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal">用户工单类型，多个可用英文逗号分割</label>
                        <input type="text" name="TicketsClass" class="form-control" value="<?= $conf['TicketsClass'] ?>"
                               placeholder="工单类型，第一个必须是【订单问题】！">
                    </div>


                    <div class="form-group mb-3">
                        <label for="example-input-normal">用户被邀请后，必须消费多少，邀请者才可以领取奖励?，单位，元</label>
                        <input type="text" name="InviteValve" class="form-control" value="<?= $conf['InviteValve'] ?>"
                               placeholder="邀请阈值">
                    </div>

                </div>

                <div id="Se5" class="mdui-p-a-2">
                    <div class="form-group mb-3">
                        <label for="example-input-normal">每日签到赠送内容</label>
                        <select class="custom-select mt-3" lay-search name="SignAway">
                            <option <?= $conf['SignAway'] == 1 ? 'selected' : '' ?> value="1">签到赠送积分
                            </option>
                            <option <?= $conf['SignAway'] == 2 ? 'selected' : '' ?> value="2">签到赠送余额
                            </option>
                        </select>
                        <div class="mt-1" style="color: red">每天用户签到赠送积分还是余额呢？
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal">每次签到赠送的内容范围，用-分割[积分或余额范围]</label>
                        <input type="text" name="GiftContent" class="form-control"
                               value="<?= $conf['GiftContent'] ?>" placeholder="签到赠送范围,中间使用 - 分隔">
                    </div>
                </div>
                <button type="submit" lay-submit lay-filter="Notification_set" class="btn btn-block btn-xs btn-success">
                    保存内容
                </button>
            </div>
        </div>
    </div>
    <div class=" col-md-4">
        <div class="card">
            <div class="card-header">
                分店分销配置以及相关说明
            </div>
            <div class="card-body">
                <fieldset class="layui-elem-field">
                    <legend>上下级绑定模式</legend>
                    <div class="layui-field-box">
                        1、新用户在分店域名内注册账号<br>
                        2、老用户(未绑定上级),在分店内购买商品，自动成为分店下级
                    </div>
                </fieldset>
                <fieldset class="layui-elem-field">
                    <legend>提成分配模式</legend>
                    <div class="layui-field-box">
                        1、用户在分店内购买商品，分店店长可获得<font color="red">差价</font>提成（具体以配置为准）<br>
                        2、当一个高级分店的下级用户(直系下级分销)的下级(二级分销用户)购买商品，产生了差价，并且该等级的绝对利润配置未设置100%，比如设置了50%，则在向上级分成时，该高级站点的直系分销用户也会向上继续分成，直到触发分成阈值，理论上可以实现无限级分润
                        <hr>
                        3、如果觉得以上解释较为复杂，可以直接点击<a target="_blank" href="./admin.level.list.php">等级列表</a>内的<a title="重置等级"
                                                                                                           href="javascript:"
                                                                                                           class="badge badge-success ml-1"><i
                                    class="layui-icon layui-icon-component"></i></a> 按钮,内置了8个等级，
                        全部配置了绝对利润和分成阀值,支持多级分销！
                    </div>
                </fieldset>
                <fieldset class="layui-elem-field">
                    <legend>邀请奖励体系</legend>
                    <div class="layui-field-box">
                        1、邀请仅创建邀请奖励数据，不会变为邀请者下级！<br>
                        2、邀请功能会验证IP，同IP新用户不会获得邀请奖励！<br>
                        3、可以设置被邀请者在网站消费了多少元，才可以领取邀请奖励！
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>

<script>
    layui.use('form', function () {
        var form = layui.form;
        form.on('submit(Notification_set)', function (data) {
            let userdeposittype = '';
            if (data.field['userdeposittype[0]'] !== undefined) {
                userdeposittype += '1,';
            } else {
                userdeposittype += '-1,';
            }
            if (data.field['userdeposittype[1]'] !== undefined) {
                userdeposittype += '1,';
            } else {
                userdeposittype += '-1,';
            }
            if (data.field['userdeposittype[2]'] !== undefined) {
                userdeposittype += '1';
            } else {
                userdeposittype += '-1';
            }
            data.field['userdeposittype'] = userdeposittype;

            console.log(data.field);

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
            return false;
        })
    });
</script>
