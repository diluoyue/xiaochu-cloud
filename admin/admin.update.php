<?php

/**
 * Filename：admin.update.php
 * 系统版本管理
 */
$title = '程序版本管理';
include 'header.php';
global $cdnserver;
?>
<div class="card" id="App">
    <div v-if="type===2" style="width:100%;text-align:center;line-height:2em;padding:2em 0 2em 0">
        数据载入中... <a href="javascript:" @click="Ajax()">尝试重新载入</a>
    </div>
    <div v-else class="mdui-card">
        <div class="mdui-card-header">
            <img class="mdui-card-header-avatar" :src="Data.image"/>
            <div class="mdui-card-header-title">你好：{{Data.name}}</div>
            <div v-if="Data.state==1" class="mdui-card-header-subtitle mdui-text-color-red">当前无可升级版本</div>
            <div v-else class="mdui-card-header-subtitle">可升级版本：{{Data.versions}}</div>
        </div>
        <div class="mdui-card-actions">
            <button v-else class="mdui-btn mdui-ripple" @click="UpdateGet()">系统升级</button>
            <button v-else class="mdui-btn mdui-ripple" @click="CalibrationDatabase()">校准数据库</button>
            <button @click="Ajax(2)" class="mdui-btn mdui-ripple">获取最新数据</button>
        </div>

        <div class="mdui-card-content">
            <ul class="layui-timeline">
                <li v-for="(item,index) in Data.data" class="layui-timeline-item">
                    <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                    <div class="layui-timeline-content layui-text">
                        <h3 class="layui-timeline-title">{{ item.versions }}</h3>
                        <p v-html="item.content">
                        </p>
                        <div>
                            {{item.date}}
                        </div>
                    </div>
                </li>
                <li class="layui-timeline-item">
                    <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                    <div class="layui-timeline-content layui-text">
                        <div class="layui-timeline-title">~</div>
                    </div>
                </li>
            </ul>
            <hr>
            Ps：如果需要手动调整程序版本号，或数据库配置，可打开：/includes/deploy.php 文件，手动调整！
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script>
    const App = Vue.createApp({
        data() {
            return {
                Data: [],
                type: 2,
            }
        },
        methods: {
            CalibrationDatabase() {
                mdui.dialog({
                    title: '数据库校准',
                    content: '确认要进行数据库结构校准吗，校准可以验证数据库表+字段的完整性，以及数据库编码是否统一，校准后下单信息支持小表情输入！，不过部分老数据库可能会出现异常，如 MySQL5.6版本，校准成功后会将数据库编码统一配置为：utf8mb4，数据库引擎会统一配置为：InnoDB！<hr>强烈建议校准前先备份数据库！<hr><span style="color:red;font-size: 16px;">需要校准多次，直到数值稳定即可！一般点两次校准就可以了！</span>',
                    modal: true,
                    history: false,
                    buttons: [{
                        text: '关闭',
                    },
                        {
                            text: '确认校准',
                            onClick: function () {
                                let is = layer.msg('正在校准中，请稍后...', {
                                    icon: 16,
                                    time: 9999999
                                });
                                $.ajax({
                                    type: "POST",
                                    url: 'main.php?act=CalibrationDatabase',
                                    dataType: "json",
                                    success: function (res) {
                                        layer.close(is);
                                        if (res.code == 1) {
                                            const jsonString = JSON.stringify(res.data, null, '\t');
                                            mdui.dialog({
                                                title: '恭喜',
                                                content: `<pre>${jsonString}</pre>`,
                                                modal: true,
                                                history: false,
                                                buttons: [{
                                                    text: '好的',
                                                }],
                                            });
                                        } else {
                                            mdui.dialog({
                                                title: '抱歉',
                                                content: res.msg,
                                                modal: true,
                                                history: false,
                                                buttons: [{
                                                    text: '好的',
                                                }]
                                            });
                                        }
                                    },
                                    error: function () {
                                        layer.msg('服务器异常，校准失败！');
                                    }
                                });
                            }
                        }
                    ]
                });
            },
            UpdateGet() {
                let is = layer.msg('数据获取中，请稍后...', {
                    icon: 16,
                    time: 9999999
                });
                $.ajax({
                    type: "POST",
                    url: 'main.php?act=UpdateData',
                    data: {},
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            mdui.dialog({
                                title: res.data.versions + '更新内容如下：',
                                content: '<h4>更新内容如下：</h4><br>' + res.data.content + '<br><br>版本发布时间：' + res.data.date + '<hr><font color=red>Ps；升级有风险，注意备份数据哦，以免数据丢失！</font>',
                                modal: true,
                                history: false,
                                buttons: [{
                                    text: '关闭',
                                },
                                    {
                                        text: '确认升级',
                                        onClick: function () {
                                            let is = layer.msg('正在升级中，请稍后...', {
                                                icon: 16,
                                                time: 9999999
                                            });
                                            $.ajax({
                                                type: "POST",
                                                url: 'main.php?act=Update',
                                                dataType: "json",
                                                success: function (res) {
                                                    layer.close(is);
                                                    if (res.code == 1) {
                                                        mdui.dialog({
                                                            title: '恭喜',
                                                            content: res.msg,
                                                            modal: true,
                                                            history: false,
                                                            buttons: [{
                                                                text: '好的',
                                                            }]
                                                        });
                                                        App.Ajax(2);
                                                    } else {
                                                        mdui.dialog({
                                                            title: '抱歉',
                                                            content: res.msg,
                                                            modal: true,
                                                            history: false,
                                                            buttons: [{
                                                                text: '好的',
                                                            }]
                                                        });
                                                    }
                                                },
                                                error: function () {
                                                    layer.msg('服务器异常！');
                                                }
                                            });
                                        }
                                    }
                                ]
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
            },
            Ajax(type = 1) {
                let is = layer.msg('数据载入中，请稍后...', {
                    icon: 16,
                    time: 9999999
                });
                $.ajax({
                    type: "POST",
                    url: 'ajax.php?act=UpdateInspection',
                    data: {
                        type: type,
                    },
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        App.type = 1;
                        if (res.code == 1) {
                            if (type === 2) {
                                layer.msg('最新数据获取成功！', {
                                    icon: 1
                                });
                            }
                            if (res.state === 2) {
                                App.UpdateGet();
                            }
                            App.Data = res;
                        } else {
                            layer.alert(res.msg, {
                                icon: 2
                            });
                        }
                    },
                    error: function () {
                        layer.alert('点击确定刷新界面', {
                            icon: 1,
                            zIndex: 1,
                            btn1: function () {
                                location.reload();
                            }
                        });
                    }
                });
            }
        }
    }).mount('#App');
    App.Ajax();
</script>
