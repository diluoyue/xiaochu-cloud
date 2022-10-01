<?php

/**
 * Author：晴玖天
 * Creation：2020/3/12 14:26
 * Filename：admin.goods.express.php
 * 快递模板
 */

use Medoo\DB\SQL;

$title = '运费模板';
include 'header.php';
global $_QET;
$DataList = shop::freight();
if (!empty($_QET['unset'])) {
    $DB = SQL::DB();
    $Res = $DB->delete('freight', [
        'id' => $_QET['unset'],
    ]);
    if ($Res) {
        show_msg('成功提示', '编号:' . $_QET['unset'] . '删除成功,请点击下方按钮返回', '1');
    } else {
        show_msg('温馨提示', '编号:' . $_QET['unset'] . '删除失败', '2');
    }
}

if (isset($_QET['id'])) {
    $DB = SQL::DB();
    $Data = $DB->get('freight', '*', ['id' => (int)$_QET['id']]);
    if (!$Data) show_msg('警告', '快递模板不存在！');
}

?>
<div class="layui-card" style="box-shadow: 3px 3px 16px #eee">
    <div class="layui-card-header">
        <?= (isset($_QET['id']) ? '编辑模板：' . $Data['name'] . ' | <a class="text-white" href="admin.goods.express.php">返回</a>' : '新增运费模板') ?>
    </div>
    <div class="layui-card-body">
        <div class="layui-form layui-form-pane">
            <input type="hidden" name="state" value="<?= (isset($_QET['id']) ? $Data['id'] : '-1') ?>">
            <div class="layui-form-item">
                <label class="layui-form-label">模板名称</label>
                <div class="layui-input-block" pane>
                    <input type="text" name="name" value="<?= (isset($_QET['id']) ? $Data['name'] : '') ?>"
                           lay-verify="required" placeholder="请输入模板名称" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">默认运费</label>
                <div class="layui-input-block" pane>
                    <input type="text" name="money" value="<?= (isset($_QET['id']) ? $Data['money'] : '') ?>"
                           placeholder="未知地区运费" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">默认加费</label>
                <div class="layui-input-block" pane>
                    <input type="text" name="exceed" value="<?= (isset($_QET['id']) ? $Data['exceed'] : '') ?>"
                           placeholder="未知地区超出下单数量每件加费" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">下单份数</label>
                <div class="layui-input-block" pane>
                    <input type="text" name="nums" value="<?= (isset($_QET['id']) ? $Data['nums'] : '') ?>"
                           placeholder="若超出下单份数,则每份额外加钱！" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">运费阈值</label>
                <div class="layui-input-block" pane>
                    <input type="text" name="threshold" value="<?= (isset($_QET['id']) ? $Data['threshold'] : '') ?>"
                           placeholder="若购买价格超出,则免运费！" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">地区|<a href="javascript:CarriageTemplate.region_add();"
                                                      class="text-success">添加</a></label>
                <div class="layui-input-block" pane>
                    <textarea name="region" placeholder="点击添加快速编辑"
                              class="layui-textarea"><?= (isset($_QET['id']) ? $Data['region'] : '') ?></textarea>
                </div>
            </div>

            <button class="layui-btn  layui-btn-fluid  layui-btn-normal <?= (isset($_QET['id']) && ($Data['type'] == 1) ? '' : 'bg-primary') ?>"
                    lay-submit lay-filter="add"><?= (isset($_QET['id']) ? '保存编辑内容' : '新增一个运费模板') ?>
            </button>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-centered table-striped dt-responsive nowrap w-100" id="products-datatable">
                <thead>
                <tr style="white-space: nowrap">
                    <th>ID</th>
                    <th>运费名称</th>
                    <th>地区</th>
                    <th>默认运费(未识别出地区使用)</th>
                    <th>下单份数</th>
                    <th>超出1份加价</th>
                    <th>免运费阈值</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($DataList['data'] as $v) :
                    ?>
                    <tr style="white-space: nowrap;font-size: 0.9em">
                        <td>
                            <?= $v['id'] ?>
                        </td>
                        <td>
                            <?= $v['name'] ?>
                        </td>
                        <td>
                            <?= implode('|', explode('|', $v['region'])) ?>
                        </td>
                        <td>
                            未知地区，运费为<?= $v['money'] ?>元
                        </td>
                        <td>
                            购买份数<=<?= $v['nums'] ?>,默认运费为<?= $v['money'] ?>元<br>
                            若超出,则每份默认加<?= $v['exceed'] ?>元运费
                        </td>
                        <td>
                            <?= $v['exceed'] ?>元
                        </td>
                        <td>
                            <?= $v['threshold'] ?>元
                        </td>
                        <td>
                            <a href="?id=<?= $v['id'] ?>" class="action-icon"> <i
                                        class="layui-icon layui-icon-set-fill"></i></a>
                            <a href="javascript:CarriageTemplate.unset(<?= $v['id'] ?>)" class="action-icon"> <i
                                        class="layui-icon layui-icon-delete"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script>
    layui.use(['form'], function () {
        var form = layui.form;
        form.on('submit(add)', function (data) {
            var load = layer.load(3);
            $.ajax({
                type: "POST",
                url: './ajax.php?act=NewCarriageTemplate',
                data: data.field,
                dataType: "json",
                success: function (data) {
                    layer.close(load);
                    if (data.code == 1) {
                        layer.alert(data.msg, {
                            icon: 1,
                            yes: function (layero, index) {
                                location.reload();
                            }
                        });
                    } else layer.alert(data.msg, {
                        icon: 3
                    });
                },
                error: function () {
                    layer.alert('获取失败！');
                },
            });
            return false;
        });
    });

    var CarriageTemplate = {
        region_add: function () { //快速加地区？
            var content = '若用户下单信息包含地区名称,则使用此地区运费配置！，若无，则默认<hr>' +
                '<div class="layui-form layui-form-pane">\n' +
                '                    <div class="layui-form-item">\n' +
                '                        <label class="layui-form-label">地区名称</label>\n' +
                '                        <div class="layui-input-block" pane>\n' +
                '                            <select name="regions" lay-search><select>\n' +
                '                        </div>\n' +
                '                    </div>' +
                '                    <div class="layui-form-item">\n' +
                '                        <label class="layui-form-label">运费金额</label>\n' +
                '                        <div class="layui-input-block" pane>\n' +
                '                            <input type="text" name="moneys" value="" placeholder="请填写运费金额" autocomplete="off"\n' +
                '                                   class="layui-input">\n' +
                '                        </div>\n' +
                '                    </div>' +
                '                    <div class="layui-form-item">\n' +
                '                        <label class="layui-form-label">加价金额</label>\n' +
                '                        <div class="layui-input-block" pane>\n' +
                '                            <input type="text" name="exceeds" value="" placeholder="超出份数每份加价金额" autocomplete="off"\n' +
                '                                   class="layui-input">\n' +
                '                        </div>\n' +
                '                    </div>' +
                '</div>';
            var ix = layer.open({
                title: '添加地区运费配置',
                content: content,
                btn: ['插入', '清空', '取消'],
                btn1: function (layero, index) {
                    var regions = $("select[name='regions']").val();
                    var moneys = $("input[name='moneys']").val();
                    var exceeds = $("input[name='exceeds']").val();
                    if (regions == '' || moneys == '' || exceeds == '') {
                        alert('请填写完整！');
                        return false;
                    } else {
                        var region = $("textarea[name='region']").text();
                        if (region == '') {
                            $("textarea[name='region']").text(regions + ',' + moneys + ',' + exceeds);
                        } else {
                            $("textarea[name='region']").text(region + '|' + regions + ',' + moneys + ',' + exceeds);
                        }
                        layer.close(ix);
                    }

                },
                btn2: function (layero, index) {
                    $("textarea[name='region']").text('');
                    layer.close(ix);
                },
                success: function (layero, index) {
                    options = '<option></option>';
                    $.each("\u5317\u4eac|1|72|1,\u4e0a\u6d77|2|78|1,\u5929\u6d25|3|51035|1,\u91cd\u5e86|4|113|1,\u6cb3\u5317|5|142,\u5c71\u897f|6|303,\u6cb3\u5357|7|412,\u8fbd\u5b81|8|560,\u5409\u6797|9|639,\u9ed1\u9f99\u6c5f|10|698,\u5185\u8499\u53e4|11|799,\u6c5f\u82cf|12|904,\u5c71\u4e1c|13|1000,\u5b89\u5fbd|14|1116,\u6d59\u6c5f|15|1158,\u798f\u5efa|16|1303,\u6e56\u5317|17|1381,\u6e56\u5357|18|1482,\u5e7f\u4e1c|19|1601,\u5e7f\u897f|20|1715,\u6c5f\u897f|21|1827,\u56db\u5ddd|22|1930,\u6d77\u5357|23|2121,\u8d35\u5dde|24|2144,\u4e91\u5357|25|2235,\u897f\u85cf|26|2951,\u9655\u897f|27|2376,\u7518\u8083|28|2487,\u9752\u6d77|29|2580,\u5b81\u590f|30|2628,\u65b0\u7586|31|2652,\u6e2f\u6fb3|52993|52994,\u53f0\u6e7e|32|2768,\u9493\u9c7c\u5c9b|84|84".split(","), function (a, c) {
                        c = c.split("|"),
                            options += '<option value="' + c[0] + '">' + c[0] + '</option>'
                    });
                    $("select[name='regions']").html(options);
                    layui.use(['form'], function () {
                        var form = layui.form;
                        form.render('select');
                    });
                }
            });
        },
        unset: function (a) {
            layer.alert('删除后不可撤销，是否确认删除？', {
                icon: 3,
                title: '温馨提示',
                btn: ['取消', '确定删除'],
                btn2: function (layero, index) {
                    window.location.href = '?unset=' + a;
                }
            })
        }
    }
</script>
