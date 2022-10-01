<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/7/7 13:00
// +----------------------------------------------------------------------
// | Filename: indexs.php
// +----------------------------------------------------------------------
// | Explain: 程序安装模块
// +----------------------------------------------------------------------
error_reporting(0);
header('Content-Type: text/html; charset=UTF-8');
include '../includes/fun.core.php';
if (PHP_VERSION_ID < 70000 || PHP_VERSION_ID >= 80000) {
    die('为了更好的使用程序,当前PHP版本最低设置为7.0,最高为7.4，请调整PHP版本，当前PHP版本：' . PHP_VERSION);
}
?>
<!doctype html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>晴玖商城系统安装引导程序</title>
    <link rel="shortcut icon" href="../assets/favicon.ico">
    <link rel="stylesheet" type="text/css" href="../assets/layuiadmin/layui/css/layui.css">
    <link href="../assets/mdui/css/mdui.min.css" rel="stylesheet" type="text/css">
</head>
<!--[if lt IE 9]>
<script src="//cdn.staticfile.org/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="//cdn.staticfile.org/lib.baomitu.com/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<body style="background-image: url('<?= background::Bing_random() ?>');-webkit-background-size: cover和-o-background-size: cover;opacity: 0.95">
<div style="height:0.5em;" class="mdui-hidden-xs-down"></div>
<div id="App" class="layui-fluid mdui-p-a-0">
    <div class="layui-row">
        <div class="layui-col-xs12 layui-col-sm8 layui-col-sm-offset2">
            <div class="mdui-card">
                <div v-if="data!==false" class="mdui-card-primary mdui-shadow-1 mdui-m-b-1">
                    <div class="mdui-card-primary-title">晴玖商城安装引导程序</div>
                    <div class="mdui-card-primary-subtitle">程序版本：{{data.accredit.versions}}</div>
                </div>

                <div v-if="type==-1" class="mdui-card-content">
                    <fieldset class="layui-elem-field">
                        <legend>程序已安装提醒</legend>
                        <div class="layui-field-box">
                            为防止程序重复安装，已经创建安装锁
                            <hr>
                            可手动删除：<font color="red">/install/install.lock</font> 文件，重新安装程序
                        </div>
                    </fieldset>
                </div>

                <div v-if="type==0" class="mdui-card-content mdui-text-center">
                    <div v-if="type==0" style="height:3em;line-height:3em;font-size: 1.2em;" class="mdui-m-t-2">
                        程序安装数据载入中，请稍后...
                    </div>
                    <button v-if="type==-2" class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-color-red-500"
                            @click="Get()">数据载入失败，点击重新载入
                    </button>
                </div>

                <div v-else-if="type==1" class="mdui-card-content mdui-p-a-0">
                    <iframe src="disclaimer.php" style="width:100%;height:70vh;border: none;"></iframe>
                </div>

                <div v-else-if="type==2" class="mdui-card-content">
                    <div class="mdui-table-fluid">
                        <table class="mdui-table mdui-table-hoverable">
                            <thead>
                            <tr>
                                <th>参数名</th>
                                <th>状态</th>
                                <th>说明</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item,index) in data.Verification">
                                <td>{{item.name}}</td>
                                <td>
                                    <span class="layui-badge layui-bg-green" v-if="item.state===1">
                                        √
                                    </span>
                                    <span class="layui-badge" v-else>
                                        ×
                                    </span>
                                </td>
                                <td>{{item.content}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else-if="type==3" class="mdui-card-content">

                    <fieldset class="layui-elem-field">
                        <legend>安装模式 - {{state}}</legend>
                        <div class="layui-field-box">
                            <label class="mdui-radio" @click="state=1">
                                <input type="radio" name="state" checked/>
                                <i class="mdui-radio-icon"></i>
                                全新安装 [ 数据清空 ]
                            </label>

                            <label class="mdui-radio mdui-m-l-2" @click="state=2">
                                <input type="radio" name="state"/>
                                <i class="mdui-radio-icon"></i>
                                仅校验数据
                            </label>
                        </div>
                    </fieldset>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库连接地址</label>
                        <input class="mdui-textfield-input" v-model="data.dbconfig.host" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库端口</label>
                        <input class="mdui-textfield-input" v-model="data.dbconfig.port" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库名称</label>
                        <input class="mdui-textfield-input" v-model="data.dbconfig.dbname" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库用户名</label>
                        <input class="mdui-textfield-input" v-model="data.dbconfig.user" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库密码</label>
                        <input class="mdui-textfield-input" v-model="data.dbconfig.pwd" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">安装Token</label>
                        <input class="mdui-textfield-input" v-model="data.accredit.token" type="text"/>
                    </div>
                    <blockquote class="layui-elem-quote layui-quote-nm mdui-m-t-1 layui-text">
                        1、安装Token，可打开<a href="https://cdn.79tian.com/api/wxapi/view" target="_blank">服务端</a>，主页获取！<br>
                        2、程序安装成功后，当前访问的域名需要授权，才可进入程序管理后台！
                    </blockquote>
                </div>

                <div v-else-if="type==4" class="mdui-card-content">
                    <h4>请手动选择延迟最小的节点，并配置，此节点用于对接服务端！</h4>
                    <hr>
                    <div class="mdui-table-fluid">
                        <table class="mdui-table">
                            <thead>
                            <tr>
                                <th>节点ID</th>
                                <th>节点名称</th>
                                <th>对接延迟</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item,index) in data.ApiList">
                                <td>{{index+1}}</td>
                                <td>{{item.name}}</td>
                                <td @click="Ping(index)">
                                    <span v-if="item.ping===false" class="layui-badge layui-bg-black">点击检测</span>
                                    <span v-else-if="item.ping==-1" style="color: red;">无法访问</span>
                                    <span v-else style="color: #0AAB89;">{{item.ping}}</span>
                                </td>
                                <td>
                                    <button v-if="ids==index"
                                            title="当前选择"
                                            class="mdui-btn mdui-btn-icon mdui-text-color-green-600 mdui-shadow-1 mdui-ripple">
                                        <i
                                                class="mdui-icon material-icons">star</i></button>
                                    <button v-else
                                            @click="ApiSelect(index)"
                                            title="点击切换到此节点"
                                            class="mdui-btn mdui-btn-icon mdui-text-color-grey-600 mdui-shadow-1 mdui-ripple">
                                        <i class="mdui-icon material-icons">star_border</i></button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else-if="type==5" class="mdui-card-content">
                    <fieldset class="layui-elem-field">
                        <legend>安装成功提醒</legend>
                        <div class="layui-field-box">
                            本程序已经安装成功，可以点击下方按钮快捷跳转界面<br>
                            打开后台后，可以输入<font color="red">服务端的账号密码</font> 登录后台，若碰到不懂的地方，可以在论坛发帖求助，或进入官方交流群求助
                        </div>
                    </fieldset>
                </div>
                <hr>
                <div class="mdui-card-actions" style="text-align:center;">
                    <div v-if="type===-1">
                        <a href="../"
                           class="mdui-btn mdui-color-red-700 mdui-ripple">
                            返回首页
                        </a>
                    </div>
                    <div v-if="type===1">
                        <button v-if="time==0" @click="Open(2)"
                                class="mdui-btn mdui-color-blue-grey-700 mdui-ripple">
                            我已阅读并同意【附加协议】内容
                        </button>
                        <button v-else
                                disabled
                                class="mdui-btn mdui-color-grey-800 mdui-ripple">
                            请先阅读【附加协议】内容({{time}}秒)
                        </button>
                    </div>

                    <div v-if="type===2">
                        <button v-if="VsType===true" @click="Open(3)"
                                class="mdui-btn mdui-color-blue-grey-700 mdui-ripple">
                            验证通过,点击进行下一步
                        </button>
                        <button v-else
                                disabled
                                class="mdui-btn mdui-color-grey-800 mdui-ripple">
                            验证未通过，无法进行下一步,请检查
                        </button>
                    </div>

                    <div v-if="type===3">
                        <button @click="Open(4)"
                                class="mdui-btn mdui-color-blue-grey-700 mdui-ripple">
                            确认数据无误后,点击按钮开始尝试安装
                        </button>
                    </div>

                    <div v-if="type===4">
                        <button @click="Open(5)"
                                class="mdui-btn mdui-color-blue-grey-700 mdui-ripple">
                            选择好最优节点后，点击进入最后一页
                        </button>
                    </div>
                    <div v-if="type===5">
                        <a href="../admin" target="_blank" class="mdui-btn mdui-color-red-a200 mdui-ripple">
                            站长后台
                        </a>
                        <a href="http://bbs.79tian.com/" target="_blank"
                           class="mdui-btn mdui-color-red-a200 mdui-ripple">
                            官方论坛
                        </a>
                        <a href="http://cdn.79tian.com/api/wxapi/view/index.php" target="_blank"
                           class="mdui-btn mdui-color-red-a200 mdui-ripple">
                            服务端后台
                        </a>
                    </div>
                </div>
                <hr>
                <div class="mdui-card-content mdui-text-center" style="color:rgba(0,0,0,0.63)">
                    <div>
                        Copyright © 2021-2031 武夷山晴玖天网络科技有限公司
                    </div>
                    <div>
                        <a href="https://cdn.79tian.com/api/wxapi/view/flock.php" target="_blank">官方交流群</a> -
                        <a href="https://shangbiao.tianyancha.com/54470783t42" target="_blank">晴玖商城</a> -
                        <a href="http://bbs.79tian.com" target="_blank">官方论坛</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="height:0.5em;" class="mdui-hidden-xs-down"></div>

<script src="../assets/js/jquery-3.4.1.min.js"></script>
<script src="../assets/layuiadmin/layui/layui.js"></script>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/mdui/js/mdui.min.js"></script>

<script>
    const App = Vue.createApp({
        data() {
            return {
                type: 0,
                data: false,
                time: 3,
                VsType: true,
                state: 1,
                ids: 0,
                tase: false,
                Pattern: 1, //安装模式
            }
        }
        , methods: {
            CountDown() {
                layui.use('util', function () {
                    var util = layui.util;
                    var endTime = new Date().getTime() + 8000;
                    var serverTime = new Date().getTime();
                    util.countdown(endTime, serverTime, function (date, serverTime, timer) {
                        App.time = date[3];
                    });
                });
            },
            Open(type) {
                if (type === 2) {
                    //协议
                    mdui.dialog({
                        title: '温馨提示',
                        content: '您若点击确认，则代表已经完全同意<font color=red>【附加协议】</font>内容，可点击确认进行下一步',
                        modal: true,
                        history: false,
                        buttons: [
                            {
                                text: '关闭',
                            },
                            {
                                text: '确认',
                                onClick: function () {
                                    App.type = type;
                                }
                            }
                        ]
                    });
                } else if (type === 3) {
                    App.type = type;
                    mdui.mutation();
                } else if (type === 4) {
                    //安装
                    App.install()
                } else {
                    App.tase = true;
                    App.type = type;
                }
            },
            ApiSelect(id) {
                let is = layer.msg('节点切换中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: 'main.php?act=ApiSet',
                    data: {
                        id: id,
                    },
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            layer.msg(res.msg, {
                                icon: 1, success: function () {
                                    App.Ping(id);
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
            },
            Ping(index, type = 1) {
                if (App.tase === true) {
                    return false;
                }
                if (type === 1) {
                    is = layer.msg('测速中，请稍后...', {icon: 16, time: 9999999});
                }
                $.ajax({
                    type: "POST",
                    url: 'main.php?act=Ping',
                    data: {
                        id: index,
                    },
                    dataType: "json",
                    success: function (res) {
                        if (type === 1) {
                            layer.close(is);
                        }
                        App.ids = res.at - 1;
                        if (res.code == 1) {
                            App.data.ApiList[index].ping = res.ms;
                        } else {
                            App.data.ApiList[index].ping = -1;
                        }
                        if (type !== 1) {
                            if (App.data.ApiList[index + 1] !== undefined) {
                                if (App.data.ApiList[index + 1].ping === false) {
                                    App.Ping(index + 1, 2);
                                } else {
                                    setTimeout(function () {
                                        App.Ping(index + 1, 2);
                                    }, 3000);
                                }
                            } else {
                                setTimeout(function () {
                                    App.Ping(0, 2);
                                }, 3000);
                            }
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            install() {
                let Form = {
                    'host': App.data.dbconfig.host,
                    'port': App.data.dbconfig.port,
                    'user': App.data.dbconfig.user,
                    'pwd': App.data.dbconfig.pwd,
                    'dbname': App.data.dbconfig.dbname,
                    'token': App.data.accredit.token,
                    'state': App.state,
                    'versions': App.data.accredit.versions,
                };
                for (const formKey in Form) {
                    if (Form[formKey] === '') {
                        layer.msg('请将安装数据填写完整！', {icon: 2});
                        return false;
                    }
                }

                let is = layer.msg('安装中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: 'main.php?act=Install',
                    data: Form,
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            if (res.type == 2) {
                                const jsonString = JSON.stringify(res.data, null, '\t');
                                layer.alert(`部分老版本MySQL数据库会提示一些错误信息,无视即可,或者去将数据库版本升级到5.7！<hr><pre>${jsonString}</pre>`, {
                                    title: '安装成功',
                                    icon: 1, btn1: function () {
                                        layer.closeAll();
                                        App.type = 4;
                                        App.Ping(0, 2);
                                    }
                                });
                            } else {
                                layer.alert(res.msg, {
                                    icon: 1, btn1: function () {
                                        layer.closeAll();
                                        App.type = 4;
                                        App.Ping(0, 2);
                                    }
                                });
                            }
                        } else {
                            layer.alert((res.msg === undefined ? '请将PHP版本切换为：7.0-7.4' : res.msg), {
                                icon: 2,
                                title: '错误编号：' + (res.code === undefined ? '无' : res.code)
                            });
                        }
                    },
                    error: function () {
                        layer.msg('服务器异常！');
                    }
                });
            },
            Get() {
                let is = layer.msg('数据载入中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: 'main.php?act=InstallData',
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            App.data = res.data;
                            for (const key in res.data.Verification) {
                                if (res.data.Verification[key].state !== 1) {
                                    App.VsType = false;
                                }
                            }
                            if (res.data.state === -1) {
                                App.type = 1;
                                App.CountDown();
                            } else {
                                App.type = -1;
                            }
                        } else {
                            App.type = -2;
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
        }
    }).mount('#App');

    App.Get();
</script>

</body>
</html>
