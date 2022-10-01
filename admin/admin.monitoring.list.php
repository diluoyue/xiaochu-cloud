<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/11 10:07
// +----------------------------------------------------------------------
// | Filename: admin.monitoring.list.php
// +----------------------------------------------------------------------
// | Explain: 监控中心，站点监控汇总
// +----------------------------------------------------------------------

$title = '监控中心 - 站点监控汇总';
include 'header.php';
global $conf;
?>
<div class="row">
    <div class="col-sm-8">
        <div class="card">
            <div class="card-header">
                API对接密钥可前往：网站配置->其他杂项 <a href="admin.app.set.php">修改</a>
            </div>
            <div class="card-body">
                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">商品状态，价格同步监控</label>
                    <input class="mdui-textfield-input"
                           onclick="copyToClip(this.value)"
                           value="<?= href(2) . ROOT_DIR_S ?>/api.php?act=Supervisory&token=<?= $conf['secret'] ?>&num=10"
                           type="text"/>
                    <div class="mdui-textfield">
                        监控频率：1分钟1次 <a href="admin.goods.monitoring.php"
                                      target="_blank">管理</a><br>
                        监控范围：本站的所有串货商品(对接其他站点的商品)<br>
                        监控效果：商品状态同步，库存同步，价格同步等<br>
                        注意事项：仅支持第三方对接的商品，不支持监控自定义api对接等，每次访问此接口将监控1-60个商品的状态，需要全天监控！
                    </div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">对接订单状态监控</label>
                    <input class="mdui-textfield-input"
                           onclick="copyToClip(this.value)"
                           value="<?= href(2) . ROOT_DIR_S ?>/api.php?act=OrderStatusMonitoring&token=<?= $conf['secret'] ?>"
                           type="text"/>
                    <div class="mdui-textfield">
                        监控频率：1分钟1次 <a href="admin.order.list.php"
                                      target="_blank">管理</a><br>
                        监控范围：3天内的串货类订单，对接成功的订单，非退款状态的订单<br>
                        监控效果：实时将对接订单的状态和本地同步<br>
                        注意事项：为防止出错，退款订单无法同步退款，需手动操作，仅调整订单状态！
                    </div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">宝塔主机资源监控</label>
                    <input class="mdui-textfield-input"
                           onclick="copyToClip(this.value)"
                           value="<?= href(2) . ROOT_DIR_S ?>/api.php?act=HostMonitoring&token=<?= $conf['secret'] ?>"
                           type="text"/>
                    <div class="mdui-textfield">
                        监控频率：30分钟1次 <a href="admin.server.list.php"
                                       target="_blank">管理</a><br>
                        监控范围：本站的所有主机<br>
                        监控效果：主机到期提醒，自动续期，到期删除等<br>
                        注意事项：若服务器节点已经到期或关闭，将不会监控该服务器下面的订单！
                    </div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">宝塔主机已用磁盘空间监控</label>
                    <input class="mdui-textfield-input"
                           onclick="copyToClip(this.value)"
                           value="<?= href(2) . ROOT_DIR_S ?>/api.php?act=HostSpaceMonitoring&token=<?= $conf['secret'] ?>"
                           type="text"/>
                    <div class="mdui-textfield">
                        监控频率：1分钟1次 <a href="admin.host.set.php"
                                      target="_blank">管理</a><br>
                        监控范围：本站的所有未到期，未关闭的主机<br>
                        监控效果：同步主机占用空间，可超出使用空间后关闭主机等<br>
                        注意事项：可以点击管理按钮，来设置主机空间大小超额后的操作
                    </div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">订单队列监控</label>
                    <input class="mdui-textfield-input"
                           onclick="copyToClip(this.value)"
                           value="<?= href(2) . ROOT_DIR_S ?>/api.php?act=SubmitOrder&token=<?= $conf['secret'] ?>&num=10"
                           type="text"/>
                    <div class="mdui-textfield">
                        监控频率：1分钟1次 <a href="admin.order.list.php"
                                      target="_blank">管理</a><br>
                        监控范围：订单队列内的全部未提交订单<br>
                        监控效果：将订单队列内的订单提交至服务器，创建真实订单！<br>
                        注意事项：仅适用于开启了订单队列和购物车的站点，后面的10，可以调整：1-10，代表每次提交的订单数量
                    </div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">易支付漏单监控</label>
                    <input class="mdui-textfield-input"
                           onclick="copyToClip(this.value)"
                           value="<?= href(2) . ROOT_DIR_S ?>/api.php?act=OrdersTesting&token=<?= $conf['secret'] ?>"
                           type="text"/>
                    <div class="mdui-textfield">
                        监控频率：1分钟1次 <a href="admin.Payments.php"
                                      target="_blank">管理</a><br>
                        监控范围：通过易支付接口付款的商品订单，前50条<br>
                        监控效果：易支付漏单自动补单<br>
                        注意事项：仅适用于易支付支付接口，使用其他接口无需监控
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card">
            <div class="card-header">
                节点监控教程
            </div>
            <div class="card-body">
                推荐使用 宝塔(<a href="http://bt.cn" target="_blank">bt.cn</a>)面板内自带的 计划任务监控！<br>
                <h4>使用步骤：</h4>
                1、打开宝塔面板，选择计划任务<br>
                2、任务类型 选择：访问URL<br>
                3、任务名称设置为对应监控接口的名称<br>
                4、执行周期按照各接口的监控频率填写！<br>
                5、URL地址填写 本站的监控地址<br>
                6、点击添加任务，保存即可！
                <hr>
                <img onclick="Tips()" src="../assets/img/monitoring.png" style="width:100%"/>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>

<script>
    function Tips() {
        mdui.dialog({
            title: '图片预览',
            content: '<img src="../assets/img/monitoring.png" />',
            modal: true,
            history: false,
            buttons: [
                {
                    text: '关闭',
                }
            ]
        });
    }

    function copyToClip(content, message) {
        var aux = document.createElement("input");
        aux.setAttribute("value", content);
        document.body.appendChild(aux);
        aux.select();
        document.execCommand("copy");
        document.body.removeChild(aux);
        if (message == null) {
            layer.msg("复制成功", {
                icon: 1
            });
        } else {
            layer.msg(message, {
                icon: 1
            });
        }
    }
</script>
