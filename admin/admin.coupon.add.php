<?php

use Medoo\DB\SQL;

$title = '添加优惠券';
include 'header.php';
$DB = SQL::DB();
$GoodsList = $DB->select('goods', ['gid', 'name', 'state'], ['ORDER' => ['gid' => 'DESC']]);
$ClassList = $DB->select('class', ['cid', 'name', 'state'], ['ORDER' => ['cid' => 'DESC']]);
?>
<div class="row">
    <div class="col-xs-12 col-sm-8">
        <div class="layui-card">
            <div class="layui-card-header bg-primary text-white">
                填写参数
            </div>
            <div class="layui-card-body" id="app">
                <div class="layui-form layui-form-pane">
                    <div class="layui-form-item">
                        <label class="layui-form-label">生成类型</label>
                        <div class="layui-input-block">
                            <select name="type" lay-filter="type">
                                <option value="1">满减券 - 满多少减多少</option>
                                <option value="2">立减券 - 无门槛优惠券</option>
                                <option value="3">折扣券 - 满多少享折扣</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">使用范围</label>
                        <div class="layui-input-block">
                            <select name="apply" lay-filter="apply">
                                <option value="1">单品优惠券 - 指定商品可用</option>
                                <option value="2">品类券 - 指定分类下全部商品可用</option>
                                <option value="3">商品通用券 - 全部商品均可使用</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">失效类型</label>
                        <div class="layui-input-block">
                            <select name="term_type" lay-filter="term_type">
                                <option value="1">相对类型 - 领取后多少天失效</option>
                                <option value="2">固定类型 - 到固定日期后失效</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">领取方式</label>
                        <div class="layui-input-block">
                            <select name="get_way" lay-filter="get_way">
                                <option value="1">隐藏券 - 需站长主动分享</option>
                                <option value="2">显示在指定商品 - 限单品券或通用券</option>
                                <option value="3">显示在指定分类 - 限品类券或通用券</option>
                                <option value="4">显示在商城首页 - 不限制任何类型券</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item" id="gid">
                        <label class="layui-form-label">商品ID</label>
                        <div class="layui-input-block">
                            <select name="gid" lay-search>
                                <option value="-1">请搜索需投放优惠券的商品</option>
                                <?php
                                foreach ($GoodsList as $v) :
                                    echo '<option value="' . $v['gid'] . '">' . $v['name'] . ' / ' . ($v['state'] == 1 ? '上架中' : '已下架') . '</option>
                                        ';
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item" id="cid" style="display: none;">
                        <label class="layui-form-label">分类ID</label>
                        <div class="layui-input-block">
                            <select name="cid" lay-search>
                                <option value="-1">请搜索需投放优惠券的分类</option>
                                <?php
                                foreach ($ClassList as $v) :
                                    echo '<option value="' . $v['cid'] . '">' . $v['name'] . ' / ' . ($v['state'] == 1 ? '显示中' : '已隐藏') . '</option>
                                        ';
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">券码名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="name" required lay-verify="required" placeholder="请填写优惠券的名称"
                                   class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">券码说明</label>
                        <div class="layui-input-block">
                            <input type="text" name="content" required lay-verify="required"
                                   placeholder="请填写相关说明,简单文字即可" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label" id="money">优惠金额</label>
                        <div class="layui-input-block">
                            <input type="number" name="money" required lay-verify="required"
                                   placeholder="请输入优惠金额，如0.5=0.5元" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item" id="minimum">
                        <label class="layui-form-label">使用限制</label>
                        <div class="layui-input-block">
                            <input type="text" name="minimum" placeholder="优惠券最低使用金额限制,以实际付款金额为准" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item" id="indate">
                        <label class="layui-form-label">有效天数</label>
                        <div class="layui-input-block">
                            <input type="text" name="indate" value="30" placeholder="请输入领取后的有效天数" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item" id="expirydate" style="display: none;">
                        <label class="layui-form-label">到期时间</label>
                        <div class="layui-input-block">
                            <input type="text" id="date" name="expirydate" placeholder="请点击选择或输入固定到期时间"
                                   class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">领取数限制</label>
                        <div class="layui-input-block">
                            <input type="number" value="1" name="limit" required lay-verify="required"
                                   placeholder="同批生成的优惠券领取数量限制" class="layui-input">
                        </div>
                    </div>
                    <button type="submit" lay-submit lay-filter="Submit" class="btn btn-block btn-xs btn-success">开始生成
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="layui-card">
            <div class="layui-card-header">
                相关说明
            </div>
            <div class="layui-card-body">
                <ul class="layui-timeline">
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">优惠券的类型</h3>
                            <p>1、<font color=#F44336>满减券</font>：满足最低可用金额即可使用，是比较常用的一种优惠券</p>
                            <p>2、<font color=#2196F3>立减券</font>：无任何条件限制，可直接使用，此无门槛优惠券需严格把控，不然容易亏本！</p>
                            <p>3、<font color=#FF9800>折扣券</font>
                                ：满足最低可用金额即可使用，最低可用金额最低可设置为0，满足金额后可对实际付款金额打折，如填写75，则订单可优惠百分之25，填写10，则会优惠90%，以此类推！</p>
                            <p>可通过<font color=red>生成类型</font>参数配置</p>
                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">用户获取优惠券的途径</h3>
                            <p>1、由你主动分享，此类券将隐藏</p>
                            <p>2、显示在指定商品购买页内，用户可直接领取，此方式仅适用于，单品优惠券或通用优惠券！</p>
                            <p>3、显示在指定商品分类界面，用户可直接领取，此方式仅适用于，品类券或通用优惠券！</p>
                            <p>可通过<font color=red>领取方式</font>参数配置</p>
                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">如何控制用户领取数量</h3>
                            <p>可设置<font color=red>领取数限制</font>参数，此参数限制每次同批生成优惠券 单个用户最多可领取数量，如本次一共生成了800张，<font
                                        color=red>领取数限制</font>设置为2，那么这800张同批优惠券内每个独立用户最多可领取2张</p>
                            <p>另外，还可以前往<a href="admin.app.set.php">网站编辑</a>内进行优惠券全局配置,限制单用户每日可领取优惠券数量和每天可使用优惠券的数量等！</p>
                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">如何快速查找需要的商品/分类?</h3>
                            <p>点击下拉框的时候可用删除里面的文字，直接搜索商品名称或分类名称！</p>
                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <div class="layui-timeline-title">如有疑问可咨询官方，另外优惠券不可在<font color=red>积分付款</font>方式下使用！</div>
                        </div>
                    </li>
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <div class="layui-timeline-title">待补充</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
include 'bottom.php';
?>

<script>
    var vm = new Vue({
        el: '#app',
        data: {
            Data: {
                type: 1,
                apply: 1,
                term_type: 1,
                get_way: 1
            },
        },
        methods: {
            Submit(num) {
                _this = this;
                layer.open({
                    title: '操作确认',
                    content: '是否确认生成[' + num + ']张，商品优惠券?',
                    icon: 3,
                    btn1: function (layero, index) {
                        let ist = layer.msg('正在生成中...', {
                            icon: 16,
                            time: 9999999
                        });

                        $.ajax({
                            type: 'post',
                            url: 'ajax.php?act=CouponAdd&num=' + num,
                            data: _this.Data,
                            dataType: 'json',
                            success: function (data) {
                                layer.close(ist);
                                if (data.code >= 1) {
                                    layer.alert(data.msg + '<hr>' + data.data.join('<br>'), {
                                        icon: 1,
                                        title: '生成结果'
                                    });
                                } else layer.msg(data.msg, {
                                    icon: 2
                                });
                            }
                        })
                    }
                })
            }
        }
    });

    layui.use(['laydate', 'form'], function () {
        var laydate = layui.laydate;
        var form = layui.form;
        var laytpl = layui.laytpl;
        laydate.render({
            elem: '#date',
            type: 'datetime'
        });

        for (const v of ['type', 'apply', 'term_type', 'get_way']) {
            form.on('select(' + v + ')', function (data) {
                vm.Data[v] = data.value;
                if (v == 'type') {
                    if (vm.Data[v] == 3) {
                        $('#money').text('折扣比例');
                        $("#minimum").show(100);
                        $("input[name='money']").attr('placeholder', '请输入折扣百分比，如75=75折');
                    } else {
                        $('#money').text('优惠金额');
                        if (vm.Data[v] == 2) {
                            $("#minimum").hide(100);
                        } else $("#minimum").show(100);
                        $("input[name='money']").attr('placeholder', '请输入优惠金额，如0.5=0.5元');
                    }
                } else if (v == 'apply') {
                    if (vm.Data[v] == 1 || vm.Data['get_way'] == 2) {
                        $("#gid").show(100);
                    } else $("#gid").hide(100);
                    if (vm.Data[v] == 2 || vm.Data['get_way'] == 3) {
                        $("#cid").show(100);
                    } else $("#cid").hide(100);
                } else if (v == 'term_type') {
                    if (vm.Data[v] == 1) {
                        $("#indate").show(100);
                        $("#expirydate").hide(100);
                    } else {
                        $("#expirydate").show(100);
                        $("#indate").hide(100);
                    }
                } else if (v == 'get_way') {
                    if (vm.Data[v] == 2 || vm.Data['apply'] == 1) {
                        $("#gid").show(100);
                    } else $("#gid").hide(100);

                    if (vm.Data[v] == 3 || vm.Data['apply'] == 2) {
                        $("#cid").show(100);
                    } else $("#cid").hide(100);
                }
                form.render();
            });
        }

        form.on('submit(Submit)', function (data) {
            vm.Data = data.field;
            console.log(vm.Data);
            layer.prompt({
                formType: 3,
                value: '1',
                title: '请输入本批优惠券生成数量',
            }, function (value, index, elem) {
                vm.Submit(value);
                layer.close(index);
            });

        });

        form.render();
    });
</script>