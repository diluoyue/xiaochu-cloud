<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/9 21:19
// +----------------------------------------------------------------------
// | Filename: UpAndDown.php
// +----------------------------------------------------------------------
// | Explain: 商品涨跌浮动展示
// +----------------------------------------------------------------------
if (!defined('IN_CRONLITE')) die;
global $conf;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>商品价格变动日志 <?= ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
    <link rel="icon" href="<?= ROOT_DIR ?>assets/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="<?= ROOT_DIR ?>assets/mdui/css/mdui.min.css">
</head>
<body id="App">
<div class="mdui-drawer mdui-drawer-full-height mdui-drawer-close mdui-shadow-4" id="drawer">
    <div class="mdui-card">
        <!-- 卡片头部，包含头像、标题、副标题 -->
        <div class="mdui-card-header">
            <img class="mdui-card-header-avatar" src="<?= $conf['logo'] ?>"/>
            <div class="mdui-card-header-title">您好：{{GetData.GradeName}}</div>
            <div class="mdui-card-header-subtitle">当前：{{GetData.ListName}}</div>
        </div>
    </div>
    <ul class="mdui-list">
        <li v-for="(item,index) in List" @click="GetList(item)" class="mdui-list-item mdui-ripple">
            <i class="mdui-list-item-icon mdui-icon material-icons">date_range</i>
            <div class="mdui-list-item-content">{{item}}</div>
            <i class="mdui-list-item-icon mdui-icon material-icons">chevron_right</i>
        </li>
    </ul>
</div>

<div class="mdui-toolbar mdui-color-blue-accent mdui-shadow-6">
    <a v-if="List.length>=1" href="javascript:;" @click="ListSwitch()" class="mdui-btn mdui-btn-icon"><i
                class="mdui-icon material-icons">menu</i></a>
    <span class="mdui-typo-title">价格波动表</span>
    <div class="mdui-toolbar-spacer">{{Data.length}}次波动</div>
    <a href="javascript:;" @click="GetList()" class="mdui-btn mdui-btn-icon"><i
                class="mdui-icon material-icons">refresh</i></a>
</div>

<div class="mdui-container-fluid mdui-m-t-1" style="overflow:hidden;width:100%;overflow-x: auto;overflow-y:auto;">
    <table v-if="Data.length>=1" class="mdui-table mdui-table-hoverable">
        <thead>
        <tr>
            <th>商品信息</th>
            <th>最新价格</th>
            <th>原来价格</th>
            <th>涨跌百分比</th>
            <th>规格值</th>
            <th>波动时间</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(item,index) in Data">
            <td style="max-width:200px;">
                <label class="mdui-list-item mdui-ripple" @click="open(item)">
                    <div class="mdui-list-item-avatar" style="background-color: #fff"><img :src="item.image"/></div>
                    <div class="mdui-list-item-content mdui-text-truncate"
                         :class="item.state!=1?' mdui-text-color-grey-600':''">{{item.Name}}
                    </div>
                </label>
            </td>
            <td :style="'color:'+(item.type==2?'#43a047':'#f64747')">{{item.NewPrice}}元</td>
            <td style="color: #9c9999;">{{item.UsedPrice}}元</td>
            <td>
                <button v-if="item.type==2" class="mdui-btn mdui-btn-raised mdui-color-green-600">{{item.Percentage}}
                </button>
                <button v-else class="mdui-btn mdui-btn-raised mdui-color-red-a200">{{item.Percentage}}</button>
            </td>
            <td>{{item.key===false?'无':item.key}}</td>
            <td>{{item.date}}</td>
        </tr>
        </tbody>
    </table>

    <div v-else>
        <div class="mdui-progress">
            <div class="mdui-progress-indeterminate"></div>
        </div>
        <div class="madui-p-a-3 mdui-text-center mdui-m-t-3" style="font-size: 1.3em;">
            一条波动日志都没有
        </div>
    </div>

</div>

<script src="<?= ROOT_DIR ?>assets/js/jquery-3.4.1.min.js"></script>
<script src="<?= ROOT_DIR ?>assets/mdui/js/mdui.min.js"></script>
<script src="<?= ROOT_DIR ?>assets/js/vue3.js"></script>
<script>
    const App = Vue.createApp({
        data() {
            return {
                Data: [],
                List: [],
                name: '',
                GetData: [],
            }
        }
        , methods: {
            open(data) {
                if (data.state != 1) {
                    mdui.snackbar({
                        message: '此商品已下架，无法购买！',
                        position: 'right-top',
                    });
                    return false;
                }
                mdui.dialog({
                    title: '温馨提示',
                    content: '是否需要跳转商品购买界面？',
                    modal: true,
                    history: false,
                    buttons: [
                        {
                            text: '关闭',
                        },
                        {
                            text: '购买商品',
                            onClick: function () {
                                open('./?mod=route&p=Goods&gid=' + data.Gid);
                            }
                        }
                    ]
                });
            },
            ListSwitch() {
                const inst = new mdui.Drawer('#drawer');
                inst.toggle();
            },
            GetList(name = false) {
                if (name === false) {
                    name = this.name;
                }
                let is = mdui.snackbar({
                    message: '数据载入中,请稍后...',
                    timeout: 0,
                    position: 'right-top',
                    closeOnButtonClick: false,
                    closeOnOutsideClick: false,
                });
                $.ajax({
                    type: "POST",
                    url: '/main.php?act=ChangesCommodityPrices',
                    data: {
                        name: name,
                    },
                    dataType: "json",
                    success: function (res) {
                        is.close();
                        if (res.code === 1) {
                            App.List = res.data.List;
                            App.Data = res.data.Data;
                            App.name = res.ListName;
                            App.GetData = res;
                        } else {
                            App.Data = [];
                            App.List = [];
                            App.name = '';
                        }
                    },
                    error: function () {
                        App.Data = [];
                        App.List = [];
                        App.name = [];
                    }
                });
            }
        }
    }).mount('#App');

    App.GetList();
</script>
</body>
</html>

