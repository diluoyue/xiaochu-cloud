<?php
/**
 * 用户等级管理
 */
$title = '等级管理';
include 'header.php';
global $UserData, $conf, $_QET;

use extend\UserConf;
use lib\Hook\Hook;
use Medoo\DB\SQL;


$mid_arr = array_reverse(RatingParameters($UserData, 2));
$data_v = array_reverse($mid_arr);
$Umid = RatingParameters($UserData);
$us_v = ((int)$Umid['mid']) - 1; //用户等级
$us_d = $us_v;
$us_c = '当前为(' . ($us_v + 1) . '级密价),级别越高商品价格越便宜！';
$us_m = (($us_v + 1) * 10);
if (!empty($data_v[$us_v]['name'])) {
    $us_c = $data_v[$us_v]['content'];
    $us_m = $data_v[$us_v]['money'];
    $us_v = $data_v[$us_v]['name'] . ' - (V' . ($us_v + 1) . '级)';
}
if (isset($_QET['update']) && (int)$_QET['update'] > 0 && (int)$_QET['update'] > $UserData['grade']) { //0为默认等级
    $money = ($data_v[((int)$_QET['update']) - 1]['money']) - $us_m; //升级所需
    if ($money < 0 || (int)$_QET['update'] > count($mid_arr)) {
        show_msg('警告', '数据异常', '4');
    }
    if ($UserData['money'] < $money) {
        show_msg('余额不足提醒', '所需余额不足，当前余额为：<font color="red">' . round($UserData['money'], 3) . '元</font><br>还差<font color="red">' . round(($money - $UserData['money']), 3) . '元</font>即可升级！<br>快捷导航：<a href="pay.php">余额充值</a>', '3', 'grade.php');
    }
    /**
     * 余额充足
     */
    $DB = SQL::DB();
    $a = $DB->update('user', [
        'money[-]' => $money,
        'grade' => (int)$_QET['update']
    ], [
        'id' => $UserData['id']
    ]);
    if ($a) {
        UserConf::Commission($UserData['id'], $money, $UserData['superior'], $_QET['update']);
        userlog('等级提升', '您在后台消耗余额将用户等级提升为' . $data_v[((int)$_QET['update']) - 1]['name'] . ',消耗金额为：' . round($money, 3) . '元！', $UserData['id'], $money);
        Hook::execute('UserLevelUp', [
            'uid' => $UserData['id'],
            'money' => $money,
            'grade' => (int)$_QET['update']
        ]);
        show_msg('成功提示', '成功帮您升级为：' . $data_v[((int)$_QET['update']) - 1]['name'] . '！<br>消耗余额为：' . round($money, 3) . '元！<br>当前余额为：' . round(($UserData['money'] - $money), 3) . '元！', '1', 'grade.php');
    } else {
        show_msg('温馨提示', '升级失败！,请联系管理员处理,未进行扣款！', '2');
    }
}
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                等级等级介绍
            </div>
            <div class="card-body">
                <?php $i = 1;
                foreach ($data_v as $value) { ?>
                    <div id="Docs<?= $i ?>" class="Docs" <?= ($us_d + 1 == $i ? '' : 'style="display: none"') ?>>
                        <?= htmlspecialchars_decode($value['content']) ?>
                    </div>
                    <?php ++$i;
                }
                if (($us_d + 1) >= $i) {
                    echo htmlspecialchars_decode($value['content']);
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                用户等级管理
            </div>
            <div class="card-body">
                <form class="form-horizontal layui-form">
                    <div class="form-group mb-2">
                        <label for="example-input-normal">当前用户等级：</label>
                        <input type="text" class="form-control" value="<?= $us_v ?>" lay-verType="tips" disabled/>
                    </div>
                    <div class="form-group mb-2">
                        <label for="example-input-normal">当前等级价值：</label>
                        <input type="text" class="form-control" value="<?= round($us_m, 2) ?>元" lay-verType="tips"
                               disabled/>
                    </div>
                    <div class="form-group mb-2">
                        <label for="example-input-normal">用户等级提升</label>
                        <select class="custom-select mt-3" name="update" lay-search lay-filter="shequ">
                            <?php
                            $sr = 0;
                            foreach ($data_v as $v) {
                                echo '<option ' . ($us_d >= $sr ? 'disabled' : '') . ' value="' . ($sr + 1) . '">升级为' . $v['name'] . ' | ' . $v['money'] . '元 ' . ($us_d >= $sr ? ' | 已达成' : ' | 可升级(需' . ($v['money'] - $us_m) . '元)') . '</option>';
                                $sr = $sr + 1;
                            }
                            if (($us_d + 1) >= $sr) {
                                echo '<option value="' . $sr . '" >恭喜！当前已达到最高等级！</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <?php if (($us_d + 1) >= $sr) {
                    } else { ?>
                        <button type="submit" lay-submit
                                class="btn btn-block btn-xs btn-outline-success">花费余额提升等级
                        </button>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-12" style="<?= ($conf['userleague'] == 1 ? '' : 'display: none') ?>">
        <div class="card">
            <div class="card-header bg-city">
                等级权限说明
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <tr class="">
                        <th>权限说明</th>
                        <th>等级限制</th>
                    </tr>
                    <tbody>
                    <tr>
                        <td>加盟商城，开自己的店铺，商品全由主站打理，无需担心售后问题</td>
                        <td>大于等于 V<?= $conf['userleaguegrade'] ?>级</td>
                    </tr>
                    <tr>
                        <td>可以自定义店铺的信息，公告，客服联系方式等，便于店铺管理哦！</td>
                        <td>大于等于 V<?= $conf['usergradenotice'] ?>级</td>
                    </tr>
                    <tr>
                        <td>自定义自己加盟店内的商品利润，可以赚更多的提成收益！</td>
                        <td>大于等于 V<?= $conf['usergradeprofit'] ?>级</td>
                    </tr>
                    <tr>
                        <td>可以自定义自己加盟店内的商品上架状态！,便于打理店铺！</td>
                        <td>大于等于 V<?= $conf['usergradegoodsstate'] ?>级</td>
                    </tr>
                    <tr>
                        <td>可以设置自己店铺的界面，模板，让你的店铺与众不同！</td>
                        <td>大于等于 V<?= $conf['usergradetem'] ?>级</td>
                    </tr>

                    <tr style="<?= ($conf['userdeposit'] == 1 ? '' : 'display: none') ?>">
                        <td>开启余额提现权限，赚到的余额不光可以自己用，还可以提取出来哦！</td>
                        <td>大于等于 V<?= $conf['userdepositgrade'] ?>级</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include 'bottom.php';
?>
<script>
    layui.use('form', function () {
        var form = layui.form;

        form.on('select(shequ)', function (data) {
            console.log(data.value);
            $(".Docs").hide(0);
            $("#Docs" + data.value).show(0);
        });
    })
</script>
