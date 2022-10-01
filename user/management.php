<?php
/**
 * Author：晴玖天
 * Creation：2020/3/22 23:01
 * Filename：management.php
 * 店铺管理
 */

use Medoo\DB\SQL;
use voku\helper\AntiXSS;

$title = '我的店铺';
include 'header.php';
global $conf, $UserData, $times;
if ($conf['userleague'] <> 1) show_msg('警告', '当前商城未开启用户加盟权限！', 2);
if ($UserData['grade'] < $conf['userleaguegrade']) show_msg('警告', '您当前的等级无法开通店铺,请先去提升等级！', 2, 'grade.php');
$template_arr = for_dir("../template/");
$data_arr = config::common_unserialize($UserData['configuration']);
if ($data_arr == false) {
    $data_arr = $conf;
} else {
    $antiXss = new AntiXSS();
    $data_arr = $antiXss->xss_clean($data_arr);
}

$DB = SQL::DB();

$count_1 = $DB->count('journal', [
    'name' => ['货币提成', '余额提成'],
    'uid' => $UserData['id'],
]);

$count_2 = $DB->count('journal', [
    'name' => ['货币提成', '余额提成'],
    'uid' => $UserData['id'],
    'date[>=]' => $times
]);

$count_3 = $DB->sum('journal', 'count', [
    'uid' => $UserData['id'],
    'name' => ['货币提成', '积分充值', '每日签到', '邀请奖励'],
]);

$count_4 = $DB->sum('journal', 'count', [
    'uid' => $UserData['id'],
    'name' => ['货币提成', '积分充值', '每日签到', '邀请奖励'],
    'date[>=]' => $times
]);

$count_5 = $DB->sum('journal', 'count', [
    'uid' => $UserData['id'],
    'name' => ['余额提成', '升级提成', '余额退款', '后台加款', '在线充值']
]);

$count_6 = $DB->sum('journal', 'count', [
    'uid' => $UserData['id'],
    'name' => ['余额提成', '升级提成', '余额退款', '后台加款', '在线充值'],
    'date[>=]' => $times
]);

$count_7 = $DB->count('user', [
    'superior' => $UserData['id']
]);

$count_7 = ($count_7 == 0 ? 0 : $count_7);

$award = $DB->get('config', ['V'], ['k' => 'award'])['V'];
?>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%;">
        <div class="card widget-flat" id="data1">
            <div class="card-body">
                <div class="float-right" style="width: 30px;height: 30px">
                    <i class="layui-icon layui-icon-rmb widget-icon" style="background-color: #FF7043;color: white"></i>
                </div>
                <h5 class="text-muted font-weight-normal mt-0">
                    累计增加余额</h5>
                <h5 class="mt-3 mb-0" style="font-weight: 300"><?= round(($count_5 == 0 ? 0 : $count_5), 2) ?>元</h5>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-6" style="width: 50%;">
        <div class="card widget-flat" id="data1">
            <div class="card-body">
                <div class="float-right" style="width: 30px;height: 30px">
                    <i class="layui-icon layui-icon-rmb widget-icon" style="background-color: #9575CD;color: white"></i>
                </div>
                <h5 class="text-muted font-weight-normal mt-0">
                    累计获得<?= $conf['currency'] ?></h5>
                <h5 class="mt-3 mb-0"
                    style="font-weight: 300"><?= round(($count_3 == 0 ? 0 : $count_3), 0) . $conf['currency'] ?></h5>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-3" style="width: 50%;">
        <div class="card widget-flat" id="data1">
            <div class="card-body">
                <div class="float-right" style="width: 30px;height: 30px">
                    <i class="layui-icon layui-icon-rmb widget-icon" style="background-color: #FF7043;color: white"></i>
                </div>
                <h5 class="text-muted font-weight-normal mt-0">
                    今日增加余额</h5>
                <h5 class="mt-3 mb-0" style="font-weight: 300"><?= round(($count_6 == 0 ? 0 : $count_6), 2) ?>元</h5>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-3" style="width: 50%;">
        <div class="card widget-flat" id="data1">
            <div class="card-body">
                <div class="float-right" style="width: 30px;height: 30px">
                    <i class="layui-icon layui-icon-rmb widget-icon" style="background-color: #FFC107;color: white"></i>
                </div>
                <h5 class="text-muted font-weight-normal mt-0">
                    今日获得<?= $conf['currency'] ?></h5>
                <h5 class="mt-3 mb-0"
                    style="font-weight: 300"><?= round(($count_4 == 0 ? 0 : $count_4), 0) . $conf['currency'] ?></h5>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-3" style="width: 50%;">
        <div class="card widget-flat" id="data1">
            <div class="card-body">
                <div class="float-right" style="width: 30px;height: 30px">
                    <i class="layui-icon layui-icon-cart-simple widget-icon"
                       style="background-color: #7C4DFF;color: white;"></i>
                </div>
                <h5 class="text-muted font-weight-normal mt-0">
                    累计订单</h5>
                <h5 class="mt-3 mb-0" style="font-weight: 300"><?= ($count_1 == 0 ? 0 : $count_1) ?>条</h5>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6 col-xl-3" style="width: 50%;">
        <div class="card widget-flat" id="data1">
            <div class="card-body">
                <div class="float-right" style="width: 30px;height: 30px">
                    <i class="layui-icon layui-icon-cart-simple widget-icon"
                       style="background-color: #43A047;color: white"></i>
                </div>
                <h5 class="text-muted font-weight-normal mt-0">
                    今日订单</h5>
                <h5 class="mt-3 mb-0" style="font-weight: 300"><?= ($count_2 == 0 ? 0 : $count_2) ?>条</h5>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                店铺域名 - 域名绑定后修改价格为<?= $conf['userdomainsetmoney'] ?>元
            </div>
            <div class="card-body">
                <form class="form-horizontal layui-form">
                    <input type="hidden" name="type" value="domain">
                    <?php if ($conf['userdomaintype'] == 1) { ?>
                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">域名主体（如www.<font
                                        color="red">baidu.com</font>，<font
                                        color="red">baidu.com</font> 则是域名主体）</label>
                            <select class="custom-select mt-3" lay-search name="domain">
                                <?php
                                $arr_domain = explode(',', $conf['userdomain']);
                                $user_domain = explode('.', $UserData['domain']);
                                $pr = $user_domain[0];
                                unset($user_domain[0]);
                                $hz = implode('.', $user_domain);
                                if (empty($arr_domain)) {
                                    echo ' <option>站长未设置域名主体,无法完成绑定！</option>';
                                } else {
                                    echo ' <option></option>';
                                }
                                foreach ($arr_domain as $value) {
                                    echo ' <option value="' . $value . '" ' . ($hz == $value ? 'selected' : '') . '>' . $value . '</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">域名前缀（如<font
                                        color="red">www</font>.baidu.com，<font
                                        color="red">www</font> 则是域名前缀）</label>
                            <input type="text" name="prefix" class="form-control" value="<?= $pr ?>"
                                   placeholder="不可将<?= implode('和', explode(',', $conf['userdomainretain'])) ?>作为域名前缀">
                        </div>
                        <blockquote class="site-text layui-elem-quote mb-3">
                            <?= $UserData['domain'] == '' ? '当前未绑定域名,初次绑定免费！' : '当前已绑定域名：' . $UserData['domain'] . ',修改收取' . $conf['userdomainsetmoney'] . '元服务费<hr>你的店铺域名：<span id="domain"><a href="' . is_https(false) . $UserData['domain'] . '" target="_blank">' . $UserData['domain'] . '</a></span><hr>
成功绑定店铺域名后怎么赚钱呢 <a href="javascript:rule()">查看</a><hr>当前您共有' . $count_7 . '个直系下级用户(在你的店铺内注册/或被你邀请的用户)！只要下级等级比你低，通过你邀请进来的用户也会成为你下级哦！，快去<a href="activity.php" target="_blank">邀请</a>新用户成为你的下级吧！' ?>
                        </blockquote>
                    <?php } else { ?>
                        <div class="form-group mb-3">
                            <label for="example-input-normal" style="font-weight: 500">域名小尾巴（如www.baidu.com?t=<font
                                        color="red">xxx</font>，<font
                                        color="red">xxx</font> 就是你的域名小尾巴）</label>
                            <input type="text" name="prefix" class="form-control" value="<?= $UserData['domain'] ?>"
                                   placeholder="不可将<?= implode('和', explode(',', $conf['userdomainretain'])) ?>作为域名小尾巴">
                        </div>
                        <blockquote class="site-text layui-elem-quote mb-3">
                            <?= $UserData['domain'] == '' ? '当前未绑定域名小尾巴,初次绑定免费！' : '当前已绑定域名小尾巴：' . $UserData['domain'] . ',修改收取' . $conf['userdomainsetmoney'] . '元服务费<hr>你的店铺域名：<span id="domain"><a href="' . href(2) . '?t=' . $UserData['domain'] . '" target="_blank">' . href(2) . '?t=' . $UserData['domain'] . '</a></span><hr>
成功绑定店铺域名后怎么赚钱呢 <a href="javascript:rule()">查看</a><hr>当前您共有' . $count_7 . '个下级用户(在你的店铺内注册/或被你邀请的用户)！只要下级等级比你低，通过你邀请进来的用户也会成为你下级哦！，快去<a href="activity.php" target="_blank">邀请</a>新用户成为你的下级吧！' ?>
                        </blockquote>
                    <?php } ?>
                    <button type="submit" lay-submit lay-filter="configuration_save"
                            class="btn btn-block btn-xs btn-outline-info">绑定域名
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                店铺装修
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">
                    <input type="hidden" name="type" value="template">
                    <div class="form-group mb-2">
                        <label for="example-input-normal" style="font-weight: 500">首页模板（PC端）</label>
                        <select class="custom-select mt-3" lay-search name="template">
                            <option
                                <?= ($data_arr['template'] == '' ? $conf['template'] : $data_arr['template']) == -1 ? 'selected' : null; ?>
                                    value="-1">关闭PC端首页
                            </option>
                            <option
                                <?= ($data_arr['template'] == '' ? $conf['template'] : $data_arr['template']) == -2 ? 'selected' : null; ?>
                                    value="-2">套娃模式(PC端镶套移动端模板)
                            </option>
                            <?php
                            foreach ($template_arr as $v) {
                                echo '<option ';
                                echo ($data_arr['template'] == '' ? $conf['template'] : $data_arr['template']) == $v ? 'selected' : null;
                                echo ' value="' . $v . '">' . $v . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label for="example-input-normal" style="font-weight: 500">首页模板（手机端）</label>
                        <select class="custom-select mt-3" lay-search name="template_m">
                            <option
                                <?= ($data_arr['template_m'] == '' ? $conf['template_m'] : $data_arr['template_m']) == -1 ? 'selected' : null; ?>
                                    value="-1">关闭移动端首页
                            </option>
                            <?php
                            foreach ($template_arr as $v) {
                                echo '<option ';
                                echo ($data_arr['template_m'] == '' ? $conf['template_m'] : $data_arr['template_m']) == $v ? 'selected' : null;
                                echo ' value="' . $v . '">' . $v . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">背景图片</label>
                        <select class="custom-select mt-3" lay-search name="background">
                            <option
                                <?= ($data_arr['background'] == '' ? $conf['background'] : $data_arr['background']) == 1 ? 'selected' : '' ?>
                                    value="1">随机二次元一(快)
                            </option>
                            <option
                                <?= ($data_arr['background'] == '' ? $conf['background'] : $data_arr['background']) == 2 ? 'selected' : '' ?>
                                    value="2">随机高清壁纸(块)
                            </option>
                            <option
                                <?= ($data_arr['background'] == '' ? $conf['background'] : $data_arr['background']) == 3 ? 'selected' : '' ?>
                                    value="3">随机二次元二(快)
                            </option>
                            <option
                                <?= ($data_arr['background'] == '' ? $conf['background'] : $data_arr['background']) == 4 ? 'selected' : '' ?>
                                    value="4">模板默认背景(快)
                            </option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label for="example-input-normal">首页banner(轮播图)</label>
                        <div class="input-group">
                            <button class="btn btn-xs btn-outline-primary mr-1"
                                    style="cursor: pointer" onclick="template.add()">添加
                            </button>
                            <button class="btn btn-xs btn-outline-success"
                                    onclick="template.QuickEdit()"
                                    style="cursor: pointer;margin-left: 1em">快速编辑
                            </button>
                            <div class="banner"
                                 data-img="<?= ($data_arr['banner'] == '' ? $conf['banner'] : $data_arr['banner']) ?>">
                            </div>
                        </div>
                    </div>
                    <style>
                        .banner {
                            width: 100%;
                            min-height: 12em
                        }

                        .banner img {
                            width: 10em;
                            height: 10em;
                            margin: 1em;
                            box-shadow: 3px 3px 16px #eee;
                            border-radius: 0.5em;
                        }
                    </style>
                    <button type="submit" lay-submit lay-filter="configuration_save"
                            class="btn btn-block btn-xs btn-outline-success">保存设置
                    </button>
                </
                >
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                基础设置
            </div>
            <div class="card-body">
                <form class="form-horizontal layui-form">
                    <input type="hidden" name="type" value="webset">
                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">网站名称</label>
                        <input type="text" name="sitename" lay-verify="required"
                               class="form-control"
                               value="<?= ($data_arr['sitename'] == '' ? $conf['sitename'] : $data_arr['sitename']) ?>"
                               placeholder="请输入站点名称">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">网站关键词</label>
                        <input type="text" name="keywords" lay-verify="required"
                               class="form-control"
                               value="<?= ($data_arr['keywords'] == '' ? $conf['keywords'] : $data_arr['keywords']) ?>"
                               placeholder="请输入关键词">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">网站描述语</label>
                        <input type="text" name="description" lay-verify="required"
                               class="form-control"
                               value="<?= ($data_arr['description'] == '' ? $conf['description'] : $data_arr['description']) ?>"
                               placeholder="请输入站点名称">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">客服QQ</label>
                        <input type="text" name="kfqq" lay-verify="required"
                               class="form-control"
                               value="<?= ($data_arr['kfqq'] == '' ? $conf['kfqq'] : $data_arr['kfqq']) ?>"
                               placeholder="请输入客服QQ">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">添加客服界面的提示语（支持html代码，不支持js）</label>
                        <input type="text" name="ServiceTips"
                               class="form-control"
                               value="<?= ($data_arr['ServiceTips'] == '' ? $conf['ServiceTips'] : $data_arr['ServiceTips']) ?>"
                               placeholder="提示语">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">购物车开关 <font color="red">
                                关闭后用户无法向购物车添加商品 </font></label>
                        <select class="custom-select mt-3" name="CartState">
                            <option
                                <?= ($data_arr['CartState'] == '' ? $conf['CartState'] : $data_arr['CartState']) == 1 ? 'selected' : '' ?>
                                    value="1">开启购物车
                            </option>
                            <option
                                <?= ($data_arr['CartState'] == '' ? $conf['CartState'] : $data_arr['CartState']) == 2 ? 'selected' : '' ?>
                                    value="2">关闭购物车
                            </option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">客服二维码，用于联系客服，可上传微信或QQ的二维码（留空显示添加QQ好友的二维码哦）</label>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control" name="ServiceImage" id="image2"
                                   value="<?= ($data_arr['ServiceImage'] == '' ? $conf['ServiceImage'] : $data_arr['ServiceImage']) ?>"
                                   placeholder="客服二维码"/>
                            <div class="input-group-append">
                                                <span class="input-group-text" id="upload2"
                                                      style="cursor: pointer">上传</span>
                            </div>
                            <div class="input-group-append">
                                                <span class="input-group-text"
                                                      onclick="layer.alert('<img src=\''+$('#image2').val()+'\' style=width:100%  />')"
                                                      style="cursor: pointer;background-color: slateblue;color: white">预览</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">官方群链接</label>
                        <input type="text" name="Communication" class="form-control"
                               value="<?= ($data_arr['Communication'] == '' ? $conf['Communication'] : $data_arr['Communication']) ?>"
                               placeholder="请输入官方群链接">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">邀请奖励(最高可设置为:<font
                                    color="#1e90ff"><?= $award ?></font>)</label>
                        <input type="text" name="award" lay-verify="required"
                               class="form-control"
                               value="<?= ($data_arr['award'] == '' ? $conf['award'] : $data_arr['award']) ?>"
                               placeholder="每邀请1人奖励的<?= ($data_arr['currency'] == '' ? $conf['currency'] : $data_arr['currency']) ?>数量">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">货币名称</label>
                        <input type="text" name="currency" lay-verify="required"
                               class="form-control"
                               value="<?= ($data_arr['currency'] == '' ? $conf['currency'] : $data_arr['currency']) ?>"
                               placeholder="网站货币的名称">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">App下载地址</label>
                        <input type="text" name="appurl"
                               class="form-control"
                               value="<?= (empty($data_arr['appurl']) ? $conf['appurl'] : $data_arr['appurl']) ?>"
                               placeholder="App下载地址">
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">强制用户登陆 <font color="red">
                                开启后用户必须登陆用户后台才可以购买商品！ </font></label>
                        <select class="custom-select mt-3" lay-search name="ForcedLanding">
                            <option
                                <?= ($data_arr['ForcedLanding'] == '' ? $conf['ForcedLanding'] : $data_arr['ForcedLanding']) == 1 ? 'selected' : '' ?>
                                    value="1">不开启用户强制登陆
                            </option>
                            <option
                                <?= ($data_arr['ForcedLanding'] == '' ? $conf['ForcedLanding'] : $data_arr['ForcedLanding']) == 2 ? 'selected' : '' ?>
                                    value="2">开启用户强制登陆(打开首页就跳转到登陆界面)
                            </option>
                            <option
                                <?= ($data_arr['ForcedLanding'] == '' ? $conf['ForcedLanding'] : $data_arr['ForcedLanding']) == 3 ? 'selected' : '' ?>
                                    value="3">开启用户强制登陆(只在下单的时候才提示需要登陆)
                            </option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal">站点动态消息通知提醒开关,如用户xxx于xxx购买了商品</label>
                        <select class="custom-select mt-3" lay-search name="DynamicMessage">
                            <option
                                <?= ($data_arr['DynamicMessage'] == '' ? $conf['DynamicMessage'] : $data_arr['DynamicMessage']) == -1 ? 'selected' : '' ?>
                                    value="-1">关闭动态消息通知
                            </option>
                            <option
                                <?= ($data_arr['DynamicMessage'] == '' ? $conf['DynamicMessage'] : $data_arr['DynamicMessage']) == 1 ? 'selected' : '' ?>
                                    value="1">开启动态消息通知
                            </option>
                        </select>
                    </div>


                    <div class="form-group mb-3">
                        <label for="example-input-normal"
                               style="font-weight: 500">商品推荐开关（仅在购物车,商品详情,订单详情等界面下方出现哦）</label>
                        <select class="custom-select mt-3" name="GoodsRecommendation">
                            <option
                                <?= ($data_arr['GoodsRecommendation'] == '' ? $conf['GoodsRecommendation'] : $data_arr['GoodsRecommendation']) == 1 ? 'selected' : '' ?>
                                    value="1">开启
                            </option>
                            <option
                                <?= ($data_arr['GoodsRecommendation'] == '' ? $conf['GoodsRecommendation'] : $data_arr['GoodsRecommendation']) == 2 ? 'selected' : '' ?>
                                    value="2">关闭
                            </option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal">同分类商品推荐</label>
                        <select class="custom-select mt-3" lay-search name="SimilarRecommend">
                            <option
                                <?= ($data_arr['SimilarRecommend'] == '' ? $conf['SimilarRecommend'] : $data_arr['SimilarRecommend']) == -1 ? 'selected' : '' ?>
                                    value="-1">关闭推荐
                            </option>
                            <option
                                <?= ($data_arr['SimilarRecommend'] == '' ? $conf['SimilarRecommend'] : $data_arr['SimilarRecommend']) == 1 ? 'selected' : '' ?>
                                    value="1">开启推荐
                            </option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="example-input-normal" style="font-weight: 500">腾讯云智服sign <font color="red">
                                留空不显示,你必须到你的站点内才可看到效果哦</font>
                            <a href="https://yzf.qq.com/" target="_blank">获取地址</a>
                            <a href="javascript:layer.alert('第一步：点击旁边的获取地址进入云智服官网<br>第二步：注册,注册需要微信<br>第三步：登陆后台,登陆后可看提示操作<br>第四步：点击顶部设置按钮,渠道按钮选择网站渠道<br>第五步：点击新增网站按钮填写信息,填写完毕后可看到部署界面！<br>第六步：在链接地址里面找到你自己的sign！<br>如：https://yzf.qq.com/xv/web/static/chat/index.html?sign=长长的混合字符串<br>把sign后面的字符串复制下来,填在这里就行了！',{title:'云智服sign获取教程'})"
                               style="color: #0bedcf">获取教程</a>
                            <a href="https://yzf.qq.com/xv/html/admin/chat/home" target="_blank">会话管理地址</a>
                        </label>
                        <input type="text" name="YzfSign"
                               class="form-control"
                               value="<?= ($data_arr['YzfSign'] == '' ? $conf['YzfSign'] : (($data_arr['YzfSign']) == -1 ? '' : $data_arr['YzfSign'])) ?>"
                               placeholder="留空不显示！">
                    </div>

                    <button type="submit" lay-submit lay-filter="configuration_save"
                            class="btn btn-block btn-xs btn-outline-success">保存设置
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                公告配置
            </div>
            <div class="card-body">
                <div class="form-horizontal layui-form">
                    <input type="hidden" name="type" value="notice">
                    <div class="form-group mb-3" style="">
                        <label for="example-input-normal"
                               style="position: relative;bottom: 0;left: 0">首页顶部公告</label>
                        <textarea class="form-control" name="notice_top" id="notice_top" rows="4"
                                  placeholder="请输入首页顶部公告内容"><?= (base64_decode($data_arr['notice_top'], TRUE) == '' ? $conf['notice_top'] : base64_decode($data_arr['notice_top'], TRUE)) ?></textarea>
                    </div>

                    <div class="form-group mb-3" style="">
                        <label for="example-input-normal"
                               style="position: relative;bottom: 0;left: 0">首页查单公告</label>
                        <textarea class="form-control" name="notice_check" id="notice_check" rows="4"
                                  placeholder="请输入首页顶部公告内容"><?= (base64_decode($data_arr['notice_check'], TRUE) == '' ? $conf['notice_check'] : base64_decode($data_arr['notice_check'], TRUE)) ?></textarea>
                    </div>
                    <div class="form-group mb-3" style="">
                        <label for="example-input-normal"
                               style="position: relative;bottom: 0;left: 0">全局底部公告</label>
                        <textarea class="form-control" name="notice_bottom" id="notice_bottom" rows="4"
                                  placeholder="请输入全局底部公告内容"><?= (base64_decode($data_arr['notice_bottom'], TRUE) == '' ? $conf['notice_bottom'] : base64_decode($data_arr['notice_bottom'], TRUE)) ?></textarea>
                    </div>

                    <div class="form-group mb-3" style="">
                        <label for="example-input-normal"
                               style="position: relative;bottom: 0;left: 0">首页公告弹窗</label>
                        <textarea class="form-control" name="PopupNotice" id="PopupNotice" rows="6"
                                  placeholder="请输入用户后台公告内容"><?= (base64_decode($data_arr['PopupNotice'], TRUE) == '' ? $conf['PopupNotice'] : base64_decode($data_arr['PopupNotice'], TRUE)) ?></textarea>
                    </div>

                    <button type="submit" lay-submit lay-filter="configuration_save"
                            class="btn btn-block btn-xs btn-outline-success">保存设置
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include 'bottom.php';
?>
<link href="<?= $cdnpublic ?>summernote/0.8.12/summernote.css" rel="stylesheet">
<script src="<?= $cdnpublic ?>summernote/0.8.12/summernote.min.js"></script>
<script src="<?= $cdnpublic ?>summernote/0.8.12/lang/summernote-zh-CN.min.js"></script>
<script>
	$(document).ready(function () {
		$('#notice_top').summernote({
			lang: 'zh-CN',
			placeholder: '请输入首页顶部公告信息',
			minHeight: 200,
			callbacks: {
				onImageUpload: function (files, editor, $editable) {
					UploadFiles(files, 'notice_top');
				}
			},
		});
		$('#notice_check').summernote({
			lang: 'zh-CN',
			placeholder: '请输入查单界面公告信息',
			minHeight: 200,
			callbacks: {
				onImageUpload: function (files, editor, $editable) {
					UploadFiles(files, 'notice_check');
				}
			},
		});
		$('#notice_bottom').summernote({
			lang: 'zh-CN',
			placeholder: '请输入全局底部公告信息',
			minHeight: 200,
			callbacks: {
				onImageUpload: function (files, editor, $editable) {
					UploadFiles(files, 'notice_bottom');
				}
			},
		});
		$('#PopupNotice').summernote({
			lang: 'zh-CN',
			placeholder: '请输入首页公告弹窗！',
			minHeight: 200,
			callbacks: {
				onImageUpload: function (files, editor, $editable) {
					UploadFiles(files, 'PopupNotice');
				}
			},
		});

		function UploadFiles(files, id) {
			var imageData = new FormData();
			$.each(files, function (key, val) {
				imageData.append("imageData" + key, val);
			});
			$.ajax({
				data: imageData,
				type: "POST",
				url: "ajax.php?act=image_content",
				cache: false,
				contentType: false,
				processData: false,
				success: function (imageUrl) {
					if (imageUrl.code == 1) {
						var content = '';
						$.each(imageUrl['SrcArr'], function (key, val) {
							$('#' + id).summernote('editor.insertImage', val['src']);
							content += '图片：<font color=red>' + val['name'] + '</font>大小为：<font color=red>' + val['size'] + '</font><br>';
						});
						layer.alert(content + '<hr>Ps:图片可一次上传多张！', {title: imageUrl.msg});
					} else layer.msg(imageUrl.msg);
				},
				error: function () {
					layer.msg('图片上传接口异常，上传失败！');
				}
			})
		}
	});
	layui.use('form', function () {
		var form = layui.form;

		form.on('submit(configuration_save)', function (data) {
			if (data.field['type'] == 'notice') {
				data.field['notice_top'] = $('#notice_top').summernote('code');
				data.field['notice_check'] = $('#notice_check').summernote('code');
				data.field['notice_bottom'] = $('#notice_bottom').summernote('code');
				data.field['PopupNotice'] = $('#PopupNotice').summernote('code');
			}
			layer.alert('是否要执行当前操作？', {
				icon: 3, btn: ['确定', '取消'], btn1: function (layero, index) {
					var index = layer.msg('数据保存中,请稍后...', {icon: 16, time: 999999});
					if (data.field['type'] == 'template') {
						var banner = $(".banner").attr('data-img');
						data.field['banner'] = banner;
					}
					let is = layer.msg('保存中，请稍后...', {icon: 16, time: 9999999});
					$.ajax({
						type: "POST",
						url: 'ajax.php?act=configuration_save',
						data: data.field,
						dataType: "json",
						success: function (res) {
							layer.close(is);
							if (res.code == 1) {
								layer.alert(res.msg, {
									icon: 1, btn1: function () {
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
	var template = {
		ls: function () {
			var images = $(".banner").attr('data-img');
			var content = images.split('|');
			contents = '';
			$.each(content, function (key, val) {
				img_arr = val.split('*');
				contents += '<a href="' + img_arr[1] + '" target="_blank" ><img src="' + img_arr[0] + '"  /></a>';
			});
			$(".banner").html(contents);
		},
		add: function () {
			var content = '<a  class="btn btn-xs btn-outline-success mr-2" id="upload" >上传图片到本地服务器</a><hr>' +
				'<div class="layui-form layui-form-pane">\n' +
				'                    <div class="layui-form-item">\n' +
				'                        <label class="layui-form-label">图片地址</label>\n' +
				'                        <div class="layui-input-block" pane>\n' +
				'                            <input type="text" name="image" value="" placeholder="请填写图片地址！" autocomplete="off"\n' +
				'                                   class="layui-input">\n' +
				'                        </div>\n' +
				'                    </div>' +
				'                    <div class="layui-form-item">\n' +
				'                        <label class="layui-form-label">跳转链接</label>\n' +
				'                        <div class="layui-input-block" pane>\n' +
				'                            <input type="text" name="url" value="" placeholder="请填写的跳转链接" autocomplete="off"\n' +
				'                                   class="layui-input">\n' +
				'                        </div>\n' +
				'                    </div>' +
				'</div>';
			var ix = layer.open({
				title: '快速添加首页轮播图',
				content: content,
				btn: ['插入', '取消'],
				btn1: function (layero, index) {
					var image = $("input[name='image']").val();
					var url = $("input[name='url']").val();
					if (image == '' || url == '') {
						alert('请填写完整！');
						return false;
					} else {
						var images = $(".banner").attr('data-img');
						var imas = image + '*' + url;
						if (images == '') {
							$(".banner").attr('data-img', imas);
						} else {
							$(".banner").attr('data-img', images + '|' + imas);
						}
						template.ls();
						layer.close(ix);
					}

				}, success: function (layero, index) {
					layui.use(['upload', 'form'], function () {
						var upload = layui.upload;
						var uploadInst = upload.render({
							elem: '#upload' //绑定元素
							, url: 'ajax.php?act=image_up' //上传接口
							, accept: 'images'
							, acceptMime: 'image/*'
							, exts: 'jpg|png|gif|bmp|jpeg'
							, done: function (res, index, upload) {
								$("input[name='image']").val(res.src);
							}
							, error: function () {
								layer.msg('图片上传失败!')
							}
						});
					});
				}
			});
		},
		QuickEdit: function () {
			var images = $(".banner").attr('data-img');
			var content = images.split('|');
			var content = content.join("\n");
			layer.prompt({
				formType: 2,
				value: content,
				title: '一行一条规则,[*]分割跳转链接！',
				maxlength: 99999999999,
				area: ['350px', '350px'] //自定义文本域宽高
			}, function (value, index, elem) {
				var content = value.split("\n");
				var content = content.join('|');
				$(".banner").attr('data-img', content);
				template.ls();
				layer.close(index);
			});
		}
	};
	template.ls();

	function rule() {
		layer.alert('' +
			'<span class="font-15 text-success">1、商品提成说明：</span><br>1、必须让用户在你店铺域名内购买商品,你才可以得到商品提成奖励！<br>如果在你的店铺内购买商品的用户等级比你还要高，您就无法获得任何提成！<br>如果你店铺内的商品购买价格和用户购买商品的价格一样，中间无差价，也无法获得任何提成<br>所以只要你等级够高，提成就越高<br><font color=#f0f>计算公式：用户购买价格 - 你的购买价 = 提成收益 </font>！' +
            <?php if($conf['usergradecost'] == 1){ ?>'<br><span class="font-15 text-success">2、下级升级提成说明：</span><br><font color=red>规则一</font>：用户在你店铺后台提升等级的时候，没有绑定其他上级，上级就是你，他升级花费的金额全部归你！<br><font color=red>规则二</font>：提升等级的用户上级已经绑定了，并且不是你，但是他又跑到你的网站来提升等级了，这种情况下，你还是可以获得提成奖励！只不过是和他的上级一人一半对分！' +
			'<br><font color=red>规则三</font>：这个提升等级的用户是从主站注册的，并且没有任何上级，直接跑到你加盟站提升等级，这种情况下，他的升级提成也全部是属于你的！' +<?php } ?>
			'', {btn: false, title: false})
	}

	$.ajax({
		type: "POST",
		url: "ajax.php?act=prevent",
		dataType: "json",
		success: function (data) {
			$("#domain").html('<a href="' + data.msg + '" target="_blank">' + data.msg + '</a>');
		},
		error: function () {
			layer.alert('加载失败！');
		}
	});

	layui.use(['upload', 'form'], function () {
		var upload = layui.upload;

		var uploadInst = upload.render({
			elem: '#upload2' //绑定元素
			, url: 'ajax.php?act=image_up' //上传接口
			, done: function (res, index, upload) {
				layer.msg('图片上传成功');
				$("#image2").val(res.src);
			}
			, error: function () {
				layer.msg('图片上传失败!')
			}
		});
	});
</script>
