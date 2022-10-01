<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/27 11:36
// +----------------------------------------------------------------------
// | Filename: admin.goods.supply.php
// +----------------------------------------------------------------------
// | Explain: 供货大厅
// +----------------------------------------------------------------------

$title = '供货大厅';
include 'header.php';
global $cdnserver, $conf;
?>
<div class="card" id="App">
    <div class="card-body">
        <div class="mdui-tab mdui-tab-full-width" mdui-tab>
            <a href="#tab1" class="mdui-ripple">官方供货区</a>
            <a href="#tab2" class="mdui-ripple" @click="DockingListDataGet(1)">串货区</a>
            <a href="#tab3" class="mdui-ripple" @click="MyIslandListGet(1)">商城海</a>
            <a href="#tab4" class="mdui-ripple" @click="MyIslandGet(1)">我的商城</a>
        </div>
        <div id="tab1" class="mdui-p-a-0 mdui-m-t-2">

            <div v-show="ServerData.length>=1&&SuppId==-1" class="mdui-textfield">
                <i @click="ServerDataGet()"
                   mdui-tooltip="{content: '点击重新载入供货商数据！',position: 'right'}"
                   style="cursor:pointer;color:#ff0000" class="mdui-icon material-icons">autorenew</i>
                <label class="mdui-textfield-label">搜索供货商</label>
                <input class="mdui-textfield-input" v-model="SupplierSearch" placeholder="请输入关键词进行搜索"
                       type="text"/>
                <div class="mdui-textfield-helper" style="height:auto;">
                    可输入：供货商ID，名称，联系QQ来进行搜索
                </div>
            </div>
            <hr v-if="ServerData.length>=1&&SuppId==-1">
            <div v-if="SuppId==-1">
                <div class="mdui-panel" mdui-panel>
                    <div class="mdui-panel-item">
                        <div class="mdui-panel-item-header">如何入驻官方供货区？<font color="red">[点击查看]</font></div>
                        <div class="mdui-panel-item-body">
                            <p>1、登录PC端服务端后台：<a target="_blank" href="https://cdn.79tian.com/api/wxapi/view/index.php">https://cdn.79tian.com/api/wxapi/view/index.php</a>
                            </p>
                            <p>2、点击左上角导航栏，展开，选择供货入驻栏目！</p>
                            <p>3、按照界面提示开通即可成为服务端供货商，为全系统供货！</p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="SuppId==-1"
                 class="mdui-row-xs-2 mdui-row-sm-3 mdui-row-md-4 mdui-row-lg-5 mdui-row-xl-6 mdui-grid-list">
                <div class="mdui-col" v-for="(item,index) in ServerData"
                     v-show="item.count>=1&&SupplierSearchState(item)==true"
                >
                    <div class="mdui-grid-tile">
                        <img @click="SupplyList(item.id,1,item)" style="cursor:pointer;" title="查看详情"
                             :src="item.image"/>
                        <div class="mdui-grid-tile-actions mdui-grid-tile-actions-top">
                            <div class="mdui-grid-tile-text">
                                <div class="mdui-grid-tile-title">押金：{{item.deposit}}元</div>
                            </div>
                        </div>
                        <div class="mdui-grid-tile-actions">
                            <div class="mdui-grid-tile-text">
                                <div class="mdui-grid-tile-title">{{item.name}}</div>
                                <div class="mdui-grid-tile-subtitle">
                                    <span mdui-tooltip="{content: '商品数量', position: 'top'}" class="badge badge-primary">{{item.count}}个</span>
                                    <span v-if="item.agent===1" class="badge badge-info mdui-m-l-1">代理商</span>
                                    <span v-if="item.agent===2" class="badge badge-danger mdui-m-l-1">授权商</span>
                                    <span :mdui-tooltip="'{content: \'供货商等级：V'+item.Grade+'，总成长值：'+item.GrowthValue+'\', position: \'top\'}'"
                                          class="badge badge-success mdui-m-l-1">V{{item.Grade}}</span>
                                </div>
                            </div>
                            <div class="mdui-grid-tile-buttons">
                                <button @click="ContactCustomerService(item)"
                                        mdui-tooltip="{content: '联系供货商', position: 'top'}"
                                        class="mdui-btn mdui-btn-icon"><i
                                            class="mdui-icon material-icons">person_pin</i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="mdui-card">
                    <div class="mdui-card-header">
                        <img class="mdui-card-header-avatar" :src="Supply.image"/>
                        <div class="mdui-card-header-title">{{Supply.name}}</div>
                        <div class="mdui-card-header-subtitle">押金：{{Supply.deposit}}元</div>
                        <div class="mdui-card-header-subtitle">
                            <span mdui-tooltip="{content: '商品数量', position: 'top'}" class="badge badge-primary">商品：{{Supply.count}}个</span>
                            <span v-if="Supply.agent===1" class="badge badge-info mdui-m-l-1">代理商</span>
                            <span v-if="Supply.agent===2" class="badge badge-danger mdui-m-l-1">授权商</span>
                            <span :mdui-tooltip="'{content: \'供货商等级：V'+Supply.Grade+'，总成长值：'+Supply.GrowthValue+'\', position: \'top\'}'"
                                  class="badge badge-success mdui-m-l-1">V{{Supply.Grade}}</span>
                        </div>
                    </div>
                    <div class="mdui-card-actions">
                        <button class="mdui-btn mdui-ripple" @click="SuppId = -1">返回上一页</button>
                        <button class="mdui-btn mdui-ripple" @click="ContactCustomerService(Supply)">联系供货商</button>
                        <button class="mdui-btn mdui-ripple" @click="BatchLaunchSupply()">批量上架</button>
                    </div>
                </div>

                <div id="SupplyT1">
                    <div class="mdui-table-fluid mdui-shadow-1 mdui-m-t-1" style="white-space:nowrap;">
                        <table class="mdui-table mdui-table-hoverable mdui-table-selectable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>商品名称</th>
                                <th>进货成本</th>
                                <th>商品库存</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item,index) in SupplyDataList.data"
                            >
                                <td class="GoodsIDT1" :id="'Goods_'+index">{{item.gid}}</td>
                                <td>
                                    <img onerror="this.src='../assets/img/404.png'" :src="item.image"
                                         style="width:2rem;height:2rem;margin-right:0.5rem;"
                                    />
                                    <a href="javascript:" @click="ProductsSale(item)">{{item.name}}</a>
                                </td>
                                <td>
                                    {{item.price}}元 × {{item.count + item.units}}
                                </td>
                                <td>
                                    {{item.inventory==-1?'无限':item.inventory + '份'}}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="SupplyDataList.lastPage>1" class="mdui-text-center mdui-m-t-1 mdui-btn-group">
                    <button v-for="index in SupplyDataList.lastPage"
                            @click="SupplyList(Supply.id,index,Supply)"
                            class="mdui-btn mdui-color-theme-accent mdui-ripple">
                        第{{index}}页
                    </button>
                </div>
            </div>
        </div>
        <div id="tab2" class="mdui-p-a-0 mdui-m-t-2">

            <div v-show="DockingListData!==false&&DockingListData.length>=1&&DockingId==-1" class="mdui-textfield">
                <i @click="DockingListDataGet(2)"
                   mdui-tooltip="{content: '点击重新载入串货区数据！',position: 'right'}"
                   style="cursor:pointer;color:#ff0000" class="mdui-icon material-icons">autorenew</i>
                <label class="mdui-textfield-label">搜索货源</label>
                <input class="mdui-textfield-input" v-model="DockingClassSearch" placeholder="请输入对接域名关键词"
                       type="text"/>
                <div class="mdui-textfield-helper" style="height:auto;">
                    可输入自定义对接货源域名来进行搜索,点击旁边的红色按钮可刷新数据!
                </div>
            </div>
            <hr v-if="DockingListData!==false&&DockingListData.length>=1&&DockingId==-1">

            <div v-if="DockingListData===false">
                对接货源列表数据正在获取中...
            </div>
            <div v-else-if="DockingListData.length===0"
                 class="mdui-text-center"
            >
                <br>
                当前无可对接货源！请点击 <a href="admin.source.add.php" target="_blank">添加货源</a> 按钮快速添加，
                添加后，可点击对应货源方块，查看商品列表，快速串货！<br>
                也可点击商城海，寻找心仪的货源站，打开他们的站点，注册用户，获取对接密钥，将对方站点添加到您的货源列表内！
                <hr>
                <a href="admin.source.add.php" target="_blank"
                   class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-color-blue-grey-400">添加货源</a>
                <button class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-color-yellow-700 mdui-m-l-1"
                        @click="DockingListDataGet(2)">重新载入
                </button>
            </div>
            <div v-else>
                <div v-if="DockingId==-1"
                     class="mdui-row-xs-1 mdui-row-sm-3 mdui-row-md-4 mdui-row-lg-5 mdui-row-xl-6 mdui-grid-list mdui-shadow-1 mdui-m-t-1">
                    <div class="mdui-col mdui-shadow-1" v-for="(item,index) in DockingListData" id
                         v-show="(item.type!=6&&item.class_name!='official') && DockingCommodityClassStatus(item) === true"
                    >
                        <div class="mdui-grid-tile">
                            <img @click="DockingProductList(index)" onerror="this.src='../assets/img/404.png'"
                                 :src="item.image"
                                 style="height:14em;cursor:pointer"/>
                            <div class="mdui-grid-tile-actions mdui-grid-tile-actions-top">
                                <div class="mdui-grid-tile-text">
                                    <div class="mdui-grid-tile-title">{{item.name}}</div>
                                </div>
                            </div>
                            <div class="mdui-grid-tile-actions mdui-grid-tile-actions-gradient"
                                 style="background-color: rgba(0,0,0,0.44)">
                                <div class="mdui-grid-tile-text">
                                    <a :href="item.url" target="_blank"
                                       class="mdui-grid-tile-title mdui-text-color-white">
                                        {{item.url}}
                                    </a>
                                    <div class="mdui-grid-tile-title">
                                        <span class="badge badge-success"
                                        >
                                            ID：{{item.id}}
                                        </span>
                                        <span class="badge badge-primary mdui-m-l-1"
                                        >
                                            {{item.username}}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div class="mdui-card">
                        <div class="mdui-card-header">
                            <img class="mdui-card-header-avatar" :src="DockingListData[DockingId].image"/>
                            <div class="mdui-card-header-title">{{DockingListData[DockingId].name}}</div>
                            <div class="mdui-card-header-subtitle">
                                <span class="badge badge-success"
                                >
                                            ID：{{DockingListData[DockingId].id}}
                                        </span>
                                <span class="badge badge-primary mdui-m-l-1"
                                >
                                            {{DockingListData[DockingId].username}}
                                        </span>
                            </div>
                            <div class="mdui-card-header-subtitle mdui-text-color-red mdui-m-t-1"
                                 v-if="DockingListData[DockingId].annotation!=''&&DockingListData[DockingId].annotation!=null">
                                {{DockingListData[DockingId].annotation}}
                            </div>
                        </div>
                        <div class="mdui-card-actions">
                            <button class="mdui-btn mdui-ripple" @click="DockingId = -1;DockingSearch = ''">上一页
                            </button>
                            <a :href="DockingListData[DockingId].url" target="_blank"
                               class="mdui-btn mdui-ripple">打开站点</a>
                            <button class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-m-l-1"
                                    @click="DockingProductList(DockingId,2)">数据重载
                            </button>
                            <button class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-m-l-1"
                                    @click="BatchLaunch()">批量上架
                            </button>
                        </div>

                        <div class="mdui-textfield mdui-p-a-1">
                            <label class="mdui-textfield-label">商品名称关键词</label>
                            <input class="mdui-textfield-input" v-model="DockingSearch" type="text"
                                   placeholder="可输入商品名称关键词进行搜索！"/>
                        </div>
                    </div>

                    <div id="SupplyT2" class="mdui-table-fluid mdui-shadow-1 mdui-m-t-1" style="white-space:nowrap;">
                        <table class="mdui-table mdui-table-hoverable mdui-table-selectable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>商品名称(点击添加商品)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item,index) in DockingList"
                                v-show="DockingCommodityStatus(item) === true"
                            >
                                <td class="GoodsID" :show="(DockingCommodityStatus(item) === true?1:2)"
                                    :id="'Goods_'+index">{{item.gid}}
                                </td>
                                <td>
                                    <a href="javascript:" @click="ProductDetails(item,index)">{{item.name}}</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div id="tab3" class="mdui-p-a-0 mdui-m-t-2">
            <div v-if="MyIslandList===false">
                商城海列表数据正在获取中...
            </div>
            <div v-else-if="MyIslandList.length===0"
                 class="mdui-text-center"
            >
                <br>
                商城海内空空如也
                <hr>
                <button class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-color-yellow-700 mdui-m-l-1"
                        @click="MyIslandListGet(2)">重新载入
                </button>
            </div>

            <div v-show="MyIslandList.length>0">
                <div class="mdui-textfield">
                    <i @click="MyIslandListGet(2)"
                       mdui-tooltip="{content: '点击重新载入商城海数据<br>商城海数据每18秒更新一次！',position: 'right'}"
                       style="cursor:pointer;color:#ff0000" class="mdui-icon material-icons">autorenew</i>
                    <label class="mdui-textfield-label">搜索商城(仅本页数据)</label>
                    <input class="mdui-textfield-input" v-model="MyIslandSearch" placeholder="请输入商城关键词" type="text"/>
                    <div class="mdui-textfield-helper" style="height:auto;">
                        可输入用户ID，站点名称，站点域名，联系QQ，微信等进行搜索，注：商城海数据每18秒更新一次！
                    </div>
                </div>
                <div v-if="MyIslandList!==false" class="mdui-card-actions">
<!--                    <button v-if="MyPage>1" class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-m-l-1"-->
<!--                            @click="MyPageGet(2)"-->
<!--                    >上一页-->
<!--                    </button>-->
<!--                    <button v-if="MyPage * MyLimit < MyCount"-->
<!--                            @click="MyPageGet(1)"-->
<!--                            class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-m-l-1"-->
<!--                    >下一页-->
<!--                    </button>-->
                    <button @click="Mypattern=(Mypattern==1?2:1)"
                            class="mdui-btn mdui-color-theme-accent mdui-ripple mdui-m-l-1"
                    >{{Mypattern===2?'卡片模式':'列表模式'}}
                    </button>
                </div>
                <hr>
                <div v-if="Mypattern===1"
                     class="mdui-row-xs-1 mdui-row-sm-2 mdui-row-md-3 mdui-row-lg-4 mdui-row-xl-5 mdui-grid-list mdui-m-t-1 mdui-p-b-1">
                    <div class="mdui-col" v-for="(item,index) in MyIslandList"
                         v-show="MyIslandStatus(item) === true"
                         style="margin-bottom:1rem"
                    >
                        <div class="mdui-card" style="border-radius: 0.2em;">
                            <div class="mdui-card-media">
                                <img style="height:15rem" :src="item.Icon" onerror="this.src='../assets/img/404.png'"/>
                                <div class="mdui-card-media-covered" style="background-color: rgba(0,0,0,0.37)">
                                    <div :mdui-tooltip="'{content: \'Lv ' + item.grade[0] + ' 站长<br>总成长值：' + item.grade[1] + '\', position: \'top\'}'"
                                         class="mdui-card-primary">
                                        <div class="mdui-card-primary-title">{{item.name}}</div>
                                        <div class="mdui-card-primary-subtitle">{{item.domain}}</div>
                                    </div>
                                </div>
                                <div class="mdui-card-menu">
                                    <button style="background-color: rgba(10,154,81,0.66)"
                                            @click="GiveThumbsUp(1,item)"
                                            class="mdui-btn mdui-text-color-white mdui-p-l-1 mdui-shadow-1 mdui-ripple">
                                        <i
                                                mdui-tooltip="{content: '赞一下', position: 'top'}"
                                                class="mdui-icon material-icons mdui-m-r-1">thumb_up</i>{{item.top.length}}
                                    </button>
                                    <button style="background-color: rgba(250,38,38,0.66)"
                                            @click="GiveThumbsUp(2,item)"
                                            mdui-tooltip="{content: '踩一下', position: 'top'}"
                                            class="mdui-btn mdui-text-color-white  mdui-shadow-1 mdui-ripple"><i
                                                class="mdui-icon material-icons mdui-m-r-1">thumb_down</i>{{item.step_on.length}}
                                    </button>
                                </div>
                            </div>
                            <div :title="item.introduce" class="mdui-card-content  mdui-text-truncate">
                                {{item.introduce}}
                                <hr>
                                <div class="mdui-grid-tile-title">
                                    <span class="badge badge-danger-lighten"
                                    >
                                            热度:{{item.Popular}}
                                        </span>
                                    <span class="badge badge-success-lighten mdui-m-l-1"
                                    >
                                            商品:{{item.data[0]}}
                                        </span>
                                    <span class="badge badge-warning-lighten mdui-m-l-1"
                                    >
                                            订单:{{item.data[1]}}
                                        </span>
                                    <span class="badge badge-primary-lighten mdui-m-l-1"
                                    >
                                            用户:{{item.data[2]}}
                                        </span>
                                </div>
                            </div>
                            <div class="mdui-card-actions">
                                <button @click="SiteDetails(item)" class="mdui-btn mdui-ripple mdui-shadow-1">查看详情
                                </button>
                                <a :href="'http://'+item.domain" target="_blank"
                                   class="mdui-btn mdui-ripple mdui-shadow-1">打开站点</a>
                                <button @click="ContactCustomerServiceMy(item)"
                                        class="mdui-btn mdui-btn-icon mdui-float-right mdui-shadow-2"><i
                                            class="mdui-icon material-icons">person_pin</i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="Mypattern!==1">
                    <div class="mdui-table-fluid">
                        <table class="mdui-table">
                            <thead>
                            <tr>
                                <th>操作</th>
                                <th>热度</th>
                                <th>商城名称</th>
                                <th>基础信息</th>
                                <th>站点等级</th>
                                <th>站点介绍</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item,index) in MyIslandList"
                                v-show="MyIslandStatus(item) === true"
                            >
                                <td>
                                    <button :mdui-menu="'{target:\'#html_operation_'+item.id+'\',position:\'bottom\'}'"
                                            class="mdui-btn mdui-ripple mdui-color-white mdui-text-color-blue-grey mdui-shadow-0 mdui-btn-icon">
                                        <i class="mdui-icon material-icons">&#xe8b8;</i>
                                    </button>
                                    <ul class="mdui-menu" :id="'html_operation_'+item.id">
                                        <li class="mdui-menu-item">
                                            <a href="javascript:" class="mdui-ripple">
                                                ID：{{item.id}}
                                            </a>
                                        </li>
                                        <li class="mdui-menu-item">
                                            <a href="javascript:" @click="SiteDetails(item)"
                                               class="mdui-ripple">站点详情</a>
                                        </li>
                                        <li class="mdui-menu-item">
                                            <a href="javascript:" @click="ContactCustomerServiceMy(item)"
                                               class="mdui-ripple">联系站长</a>
                                        </li>
                                        <li class="mdui-menu-item">
                                            <a :href="'http://'+item.domain" target="_blank"
                                               class="mdui-ripple">打开站点</a>
                                        </li>
                                        <li class="mdui-menu-item">
                                            <a href="javascript:" @click="GiveThumbsUp(1,item)"
                                               class="mdui-ripple">赞一下({{item.top.length}})</a>
                                        </li>
                                        <li class="mdui-menu-item">
                                            <a href="javascript:" @click="GiveThumbsUp(2,item)"
                                               class="mdui-ripple">踩一下({{item.step_on.length}})</a>
                                        </li>
                                    </ul>
                                </td>
                                <td>
                                    <span class="badge badge-danger-lighten">{{item.Popular}}</span>
                                </td>
                                <td><a :href="'http://'+item.domain" target="_blank">{{item.name}}</a></td>
                                <td>
                                    <div class="mdui-grid-tile-title">
                                        <span class="badge badge-success-lighten mdui-m-l-1"
                                        >
                                            商品数:{{item.data[0]}}
                                        </span>
                                        <span class="badge badge-warning-lighten mdui-m-l-1"
                                        >
                                            订单数:{{item.data[1]}}
                                        </span>
                                        <span class="badge badge-primary-lighten mdui-m-l-1"
                                        >
                                            用户数:{{item.data[2]}}
                                        </span>
                                    </div>
                                </td>
                                <td>Lv{{item.grade[0]}}</td>
                                <td>{{item.introduce}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="MyIslandData!==false" id="tab4" class="mdui-p-a-0 mdui-m-t-2">
            <fieldset class="layui-elem-field layui-anim layui-anim-up">
                <legend>热度排名：第{{MyIslandData.rank}}名
                    <i class="mdui-icon material-icons" @click="MyIslandGet(2)" style="font-size:0.9em;cursor:pointer;">cached</i>
                </legend>
                <div class="layui-field-box">
                    <div class="mdui-table-fluid">
                        <table class="mdui-table mdui-table-hoverable" style="white-space: nowrap;">
                            <thead>
                            <tr>
                                <th>名称</th>
                                <th>值</th>
                                <th>说明</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>被赞次数</td>
                                <td>{{MyIslandData.data.top.length}}次
                                    <a href="javascript:" @click="ViewData(MyIslandData.data.top,1)">详情</a>
                                </td>
                                <td>被赞的次数
                                </td>
                            </tr>
                            <tr>
                                <td>被踩次数</td>
                                <td>{{MyIslandData.data.step_on.length}}次
                                    <a href="javascript:" @click="ViewData(MyIslandData.data.step_on,2)">详情</a>
                                </td>
                                <td>被踩的次数
                                </td>
                            </tr>
                            <tr mdui-tooltip="{content: '热度指数可通过被他人点赞，或购买获得！，他人每次点赞可获得1-5点热度值！，每日签到可获得1-10点热度值！', position: 'top'}">
                                <td>
                                    热度指数
                                </td>
                                <td>
                                    <span class="badge badge-info-lighten">{{MyIslandData.data.Popular}}点</span>
                                    <span style="cursor:pointer;"
                                          @click="BuyingEnthusiasm()"
                                          class="badge badge-danger-lighten mdui-m-l-1">购买</span>
                                    <span v-if="MyIslandData.data.CheckTime==-1" style="cursor:pointer;"
                                          @click="SignDaily(MyIslandData.data.CheckTime)"
                                          class="badge badge-danger-lighten mdui-m-l-1">
                                        签到
                                    </span>
                                    <span v-else style="cursor:pointer;"
                                          @click="SignDaily(MyIslandData.data.CheckTime)"
                                          class="badge badge-warning-lighten mdui-m-l-1">
                                        已签
                                    </span>
                                </td>
                                <td>热度值会缓慢增长,每天约可增加1-10点左右</td>
                            </tr>
                            <tr>
                                <td>公开状态</td>
                                <td>
                                    <span v-if="MyIslandData.data.state==1"
                                          class="badge badge-success-lighten"
                                          style="cursor:pointer;"
                                          @click="StateSet(3)"
                                    >正常状态</span>
                                    <span v-else-if="MyIslandData.data.state==2"
                                          class="badge badge-danger-lighten"
                                          style="cursor:pointer;"
                                          :mdui-tooltip="'{content: \'此站点已被禁止公开，原因：' + MyIslandData.data.error + ' \', position: \'top\'}'"
                                    >禁封状态</span>
                                    <span v-else-if="MyIslandData.data.state==3"
                                          @click="StateSet(1)"
                                          style="cursor:pointer;"
                                          class="badge badge-warning-lighten">暂停公开</span>
                                    <span v-else
                                          class="badge badge-dark-lighten">未知状态</span>
                                </td>
                                <td>若状态异常，则无法在商城海展示,可点击切换状态</td>
                            </tr>
                            <tr>
                                <td>站点等级</td>
                                <td>
                                    <span class="badge badge-primary-lighten">
                                        Lv {{MyIslandData.data.grade[0]}}
                                    </span>
                                    <span class="badge">
                                        [ {{MyIslandData.data.grade[1]}} / {{MyIslandData.data.grade[2]}} ]
                                    </span>
                                </td>
                                <td>等级[成长值,升级所需成长值]，成长值越高,可信度越高，可通过他人点赞,每日签到获得！</td>
                            </tr>
                            <tr>
                                <td>商城数据</td>
                                <td>
                                    {{MyIslandData.data.data[0]}}个 / {{MyIslandData.data.data[1]}}条 /
                                    {{MyIslandData.data.data[2]}}个
                                    <span @click="DataSynchronization()" class="badge badge-danger-lighten"
                                          style="cursor:pointer;">
                                        同步
                                    </span>
                                </td>
                                <td>商品总数 | 订单总数 | 用户总数 ，用于展示，需手动同步</td>
                            </tr>
                            <tr>
                                <td>被评次数</td>
                                <td>
                                    {{MyIslandData.comment.length}}次
                                    <span v-if="MyIslandData.comment.length>=1"
                                          @click="ViewComments(MyIslandData.comment)"
                                          class="badge badge-danger-lighten"
                                          style="cursor:pointer;">
                                        查看
                                    </span>
                                </td>
                                <td>被评论的次数</td>
                            </tr>
                            <tr>
                                <td>我的余额</td>
                                <td style="color:rgba(240,58,58,0.76)">{{MyIslandData.money}}元
                                    <span v-else style="cursor:pointer;"
                                          @click="Pay()"
                                          class="badge badge-success-lighten mdui-m-l-1">
                                        在线充值
                                    </span>
                                </td>
                                <td>服务端可用余额</td>
                            </tr>
                            <tr>
                                <td>加入时间</td>
                                <td>{{MyIslandData.data.addtime}}</td>
                                <td>加入商城海的时间</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </fieldset>
            <fieldset class="layui-elem-field">
                <legend>我的商城信息 - 公开信息</legend>
                <div class="layui-field-box">

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">站点名称</label>
                        <input v-model="MyIslandData.data.name" placeholder="请输入站点名称" class="mdui-textfield-input"
                               type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">站点图标</label>
                        <input v-model="MyIslandData.data.Icon" placeholder="可填写完整图片地址链接"
                               mdui-tooltip="{content: '用于对外展示，如果图片地址失效，则会显示默认图片！', position: 'top'}"
                               class="mdui-textfield-input" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">站点域名</label>
                        <input v-model="MyIslandData.data.domain" placeholder="一般是自动获取，可手动修改，便于他人对接！"
                               mdui-tooltip="{content: '一般是自动获取，可手动修改，便于他人对接，若系统检测到此链接失效或非本系统链接，会自动关闭公开展示！',position: 'top'}"
                               class="mdui-textfield-input" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">克隆密钥</label>
                        <input v-model="MyIslandData.data.clone_key" placeholder="填写-1，不显示！"
                               mdui-tooltip="{content: '填写-1，隐藏此参数！，若填写了，则其他站点可通过此克隆密钥克隆您的商城商品！', position: 'top'}"
                               class="mdui-textfield-input" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">客服QQ</label>
                        <input v-model="MyIslandData.data.qq" placeholder="填写-1，不显示！"
                               mdui-tooltip="{content: '填写-1，隐藏此参数！', position: 'top'}"
                               class="mdui-textfield-input" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">客服微信</label>
                        <input v-model="MyIslandData.data.wx" placeholder="填写-1，不显示！"
                               mdui-tooltip="{content: '填写-1，隐藏此参数！', position: 'top'}"
                               class="mdui-textfield-input" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">云智服key</label>
                        <input v-model="MyIslandData.data.yzf" placeholder="填写-1，不显示！"
                               mdui-tooltip="{content: '填写-1，隐藏此参数！', position: 'top'}"
                               class="mdui-textfield-input" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">加群链接</label>
                        <input v-model="MyIslandData.data.group" placeholder="填写-1，不显示！"
                               mdui-tooltip="{content: '填写-1，隐藏此参数！', position: 'top'}"
                               class="mdui-textfield-input" type="text"/>
                    </div>

                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">站点介绍信息 [仅支持纯文字]</label>
                        <textarea v-model="MyIslandData.data.introduce" class="mdui-textfield-input"
                                  placeholder="请输入介绍内容！"></textarea>
                    </div>

                    <button @click="UpdateData()"
                            class="mdui-btn mdui-m-t-2 mdui-btn-block mdui-text-color-white mdui-ripple mdui-btn-raised mdui-color-deep-purple-accent">
                        更新数据
                    </button>
                </div>
            </fieldset>
        </div>
    </div>
    <div class="mdui-text-center mdui-m-b-2">当前选择分类：{{cid}}</div>
</div>

<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/supply.js?vs=<?= $accredit['versions'] ?>"></script>
