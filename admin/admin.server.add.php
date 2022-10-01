<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/20 15:38
// +----------------------------------------------------------------------
// | Filename: admin.server.add.php
// +----------------------------------------------------------------------
// | Explain: 添加服务器
// +----------------------------------------------------------------------

$title = '添加服务器 - 宝塔面板';
include 'header.php';
global $cdnserver;
?>
<div class="row" id="App" sid="<?= (empty($_QET['id']) ? '' : $_QET['id']) ?>">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header mdui-shadow-1">
                <a title="返回" href="admin.server.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                {{id!==''?'修改服务器':'添加服务器'}}
            </div>
            <div class="card-body">
                <div class="mdui-tab mdui-tab-full-width" mdui-tab>
                    <a href="#example4-tab1" @click="Form.system = 1" class="mdui-ripple">Linux系统</a>
                    <a href="#example4-tab2" @click="Form.system = 2" class="mdui-ripple">Windows系统</a>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">对接地址</label>
                    <input class="mdui-textfield-input" v-model="Form.url" type="text"/>
                    <div class="mdui-textfield-helper">
                        宝塔的登陆地址如：http://127.0.0.1:8888 IP+端口号
                    </div>
                </div>
                <div style="color:red;font-size: 0.7rem;">
                    千万不要填写目录保护地址，直接填写IP+端口即可，端口后面的/xxxxx就是目录保护地址
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">域名解析地址【CNAME】</label>
                    <input class="mdui-textfield-input" v-model="Form.domain" type="text"/>
                    <div class="mdui-textfield-helper">可填写已经绑定到此服务器的域名，便于用户使用【CNAME】解析方式绑定用户自己的域名！，如：http://baidu.com
                    </div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">默认建站目录</label>
                    <input class="mdui-textfield-input" v-model="Form.path" type="text"/>
                    <div class="mdui-textfield-helper">填写宝塔站点的文件存储目录，如：/www/wwwroot/，注意Windows和Linux的目录地址不同</div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">宝塔面板安装目录</label>
                    <input class="mdui-textfield-input" v-model="Form.root_directory" type="text"/>
                    <div class="mdui-textfield-helper">
                        宝塔程序的安装目录，Linux一般是/www/，Windows则填写自定义文件存储地址，如：C:/Users/Administrator/Desktop/xxxx/BtSoft/
                    </div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">宝塔接口密钥</label>
                    <input class="mdui-textfield-input" v-model="Form.token" type="text"/>
                    <div class="mdui-textfield-helper">对接密钥前往：面板设置->API接口，生成获取！</div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">存放主机空间的分类编号</label>
                    <input class="mdui-textfield-input" v-model="Form.type" value="0" type="number"/>
                    <div class="mdui-textfield-helper">默认分类编号ID为0，如果创建了新分类，则向上类推，1，2，3~</div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">数据库管理地址</label>
                    <input class="mdui-textfield-input" v-model="Form.sqlurl" type="text"/>
                    <div class="mdui-textfield-helper">在宝塔应用商店安装好 phpMyAdmin 5.0 后，打开应用面板，复制公共访问地址填写即可！</div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">主机大小配额[MB]</label>
                    <input class="mdui-textfield-input" v-model="Form.HostSpace" type="number" value="200"/>
                    <div class="mdui-textfield-helper">新建主机默认可用存储空间大小配额</div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">节点介绍</label>
                    <input class="mdui-textfield-input" v-model="Form.content" type="text"/>
                    <div class="mdui-textfield-helper">服务器节点的相关说明！，注：节点添加后会自动获取节点配置数据，无需填写配置信息，名称等！</div>
                </div>

                <div class="mdui-textfield">
                    <label class="mdui-textfield-label">服务器到期时间</label>
                    <input id="date" class="mdui-textfield-input" v-model="Form.endtime" type="text"/>
                    <div class="mdui-textfield-helper">请填写服务器剩余到期时间，到期后服务器下的主机空间将停止使用！</div>
                </div>
                <br>
                <button @click="CreateServer()"
                        class="mdui-btn mdui-btn-block mdui-text-color-white mdui-ripple mdui-btn-raised mdui-color-deep-purple-accent">
                    {{id!==''?'修改服务器信息':'添加新服务器'}}
                </button>
                <hr>
                <p>如果提示 <font color="red">【IP校验失败,您的访问IP为[xxxxx]】</font> ，可打开宝塔面板，打开面板设置，点击API接口，将提示的IP填入IP白名单内，保存即可！
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header mdui-shadow-2">
                服务器基础应用清单
            </div>
            <div class="card-body mdui-p-a-0">
                <div class="mdui-panel" mdui-panel>
                    <div class="mdui-panel-item">
                        <div class="mdui-panel-item-header">
                            <div class="mdui-panel-item-title">Linux系统</div>
                            <div class="mdui-panel-item-summary">宝塔版本：7.6.0+</div>
                            <i class="mdui-panel-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
                        </div>
                        <div class="mdui-panel-item-body">
                            <div class="mdui-table-fluid">
                                <table class="mdui-table mdui-table-hoverable">
                                    <thead>
                                    <tr>
                                        <th>应用名称</th>
                                        <th>版本范围</th>
                                        <th>是否必须</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Nginx</td>
                                        <td>1.17 ~ 1.18</td>
                                        <td>是</td>
                                    </tr>
                                    <tr>
                                        <td>MySQL</td>
                                        <td>5.6 ~ 5.7</td>
                                        <td>是</td>
                                    </tr>
                                    <tr>
                                        <td>PHP</td>
                                        <td>5.6 ~ 7.4 (7.0必装)</td>
                                        <td>是</td>
                                    </tr>
                                    <tr>
                                        <td>phpMyAdmin</td>
                                        <td>5.0</td>
                                        <td>是</td>
                                    </tr>
                                    <tr>
                                        <td>PHP守护</td>
                                        <td>最新版</td>
                                        <td>否</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="mdui-panel-item">
                        <div class="mdui-panel-item-header">
                            <div class="mdui-panel-item-title">Windows系统</div>
                            <div class="mdui-panel-item-summary">宝塔版本：7.2.0+</div>
                            <i class="mdui-panel-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
                        </div>
                        <div class="mdui-panel-item-body">
                            <div class="mdui-table-fluid">
                                <table class="mdui-table mdui-table-hoverable">
                                    <thead>
                                    <tr>
                                        <th>应用名称</th>
                                        <th>版本范围</th>
                                        <th>是否必须</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Nginx</td>
                                        <td>1.18</td>
                                        <td>是</td>
                                    </tr>
                                    <tr>
                                        <td>MySQL</td>
                                        <td>5.6 ~ 5.7</td>
                                        <td>是</td>
                                    </tr>
                                    <tr>
                                        <td>PHP</td>
                                        <td>5.6 ~ 7.4 (7.0必装)</td>
                                        <td>是</td>
                                    </tr>
                                    <tr>
                                        <td>phpMyAdmin</td>
                                        <td>5.0</td>
                                        <td>是</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="AppBanner" class="row">
    <div class="col-xl-12" v-for="(item,index) in List">
        <div class="card mdui-p-a-0 mdui-m-b-1 mdui-m-t-1">
            <div class="card-body p-0">
                <div class="mdui-panel mdui-panel-popout" :id="item.id + '_card'">
                    <div class="mdui-panel-item" :id="item.id + '_cards'">
                        <div class="mdui-panel-item-header" @click="Open(item.id)">
                            <div class="mdui-panel-item-title" style="width:80%">{{item.name}}
                            </div>
                            <i class="mdui-panel-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
                        </div>
                        <div class="mdui-panel-item-body">
                            <fieldset class="layui-elem-field">
                                <legend><a :href="item.url" target="_blank">{{item.url}}</a></legend>
                                <div class="layui-field-box">
                                    {{item.content}}
                                    <hr>
                                    <div class="mdui-text-center">
                                        <img :src="item.image"
                                             class="mdui-shadow-2"
                                             style="max-width:100%;"/>
                                    </div>
                                    <hr>
                                    广告热度：{{item.hot}}<br>
                                    点赞人数：{{item.top.length}}人<br>
                                    踩的人数：{{item.step_on.length}}人<br>
                                    抵押金额：{{item.deposit-0}}元
                                    <i class="mdui-icon material-icons" @click="Tips()"
                                       style="font-size:1.2em;color:#ff6b1b;cursor:pointer;">error_outline</i><br>
                                    到期时间：{{item.endtime}}<br>
                                    投放时间：{{item.addtime}}
                                    <hr>
                                    <div class="mc-vote mdui-text-center">
                                        <button @click="GiveThumbsUp(1,item,1)"
                                                class="mc-icon-button mdui-btn mdui-btn-icon mdui-btn-outlined mdui-shadow-2"
                                                :mdui-tooltip="'{content: \'顶一下,当前：'+item.top.length+'个人顶过\', delay: 300}'">
                                            <i
                                                    class="mdui-icon material-icons mdui-text-color-theme-icon"
                                                    style="color:rgba(4,180,170,0.72) !important">thumb_up</i>
                                        </button>
                                        <button @click="GiveThumbsUp(2,item,1)"
                                                class="mc-icon-button mdui-btn mdui-btn-icon mdui-btn-outlined mdui-shadow-2 mdui-m-l-5"
                                                :mdui-tooltip="'{content: \'踩一下,当前：'+item.step_on.length+'个人踩过\', delay: 300}'"
                                        ><i
                                                    class="mdui-icon material-icons mdui-text-color-theme-icon"
                                                    style="color:rgba(248,10,10,0.62) !important">thumb_down</i>
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/banner.js?vs=<?= $accredit['versions'] ?>"></script>
<script>
    const App = Vue.createApp({
        data() {
            return {
                Form: {
                    system: 1,
                    root_directory: '/www/',
                    path: '/www/wwwroot/',
                    type: 0,
                },
                id: $("#App").attr('sid'),
            }
        }
        , methods: {
            Get() {
                let is = layer.msg('初始化中，请稍后...', {icon: 16, time: 9999999});
                $.ajax({
                    type: "POST",
                    url: 'main.php?act=ServerGet',
                    data: {
                        id: App.id,
                    },
                    dataType: "json",
                    success: function (res) {
                        layer.close(is);
                        if (res.code == 1) {
                            $("title,.page-title").text('修改信息 - ' + res.data.name);
                            App.Form = res.data;
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
            CreateServer() {
                layer.open({
                    title: '温馨提示',
                    content: '是否要执行此操作？',
                    icon: 1,
                    btn: ['确定', '取消'],
                    btn1: function () {
                        let is = layer.msg('处理中，请稍后...', {icon: 16, time: 9999999});
                        if (App.id !== '') {
                            App.Form.sid = App.id;
                        }
                        $.ajax({
                            type: "POST",
                            url: './main.php?act=CreateServer',
                            data: App.Form,
                            dataType: "json",
                            success: function (res) {
                                layer.close(is);
                                if (res.code == 1) {
                                    layer.alert(res.msg, {
                                        icon: 1
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
                })
            }
        }
    }).mount('#App');

    if (App.id !== '') {
        App.Get();
    }

    AppBanner.ListGet(5);

    layui.use('laydate', function () {
        var laydate = layui.laydate;
        laydate.render({
            elem: '#date'
            , type: 'datetime'
            , done: function (value) {
                App.Form.endtime = value;
            }
        });
    });
</script>
