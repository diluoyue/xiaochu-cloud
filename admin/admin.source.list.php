<?php

/**
 * 货源管理
 */

$title = '货源管理';
include 'header.php';
?>
<div class="card" id="App">
    <div class="card-header">
        <a title="添加货源" href="./admin.source.add.php" class="badge badge-primary"><i
                    class="layui-icon layui-icon-addition"></i></a>
        <a title="查看商品" href="./admin.goods.list.php" class="badge badge-success ml-1"><i
                    class="layui-icon layui-icon-cart-simple"></i></a>
        <a title="初始化" href="javascript:App.ListGet();" class="badge badge-warning ml-1"><i
                    class="layui-icon layui-icon-refresh-3"></i></a>
    </div>
    <div class="card-body">
        <div class="layui-row layui-col-space8">
            <div v-for="(item,index) in Data" class="layui-col-xs6 layui-col-sm4 layui-col-lg3">
                <div class="card mdui-ripple">
                    <div class="card-header layui-elip font-13">
                        <button class="btn btn-outline-primary badge mr-1">
                            {{ item.id }}
                        </button>
                        {{item.name}}
                    </div>
                    <div class="card-body">
                        <div class="layui-row">
                            <div class="layui-col-xs12 layui-col-sm4 text-center">
                                <div class="layui-hide-xs"><img :src="item.image"
                                                                style="width: 48px; height: 48px; border-radius: 0.5em; box-shadow: rgb(238, 238, 238) 3px 3px 16px;">
                                </div>
                                <div class="layui-hide-sm"><img :src="item.image"
                                                                style="width: 78px; height: 78px; border-radius: 0.5em; box-shadow: rgb(238, 238, 238) 3px 3px 16px;">
                                </div>
                            </div>
                            <div class="layui-col-xs12 layui-col-sm8" style="font-size: 80%;">
                                <div class="layui-hide-sm mt-2"></div>
                                <div class="layui-elip w-100">
                                    {{ item.username }}
                                </div>
                                <div class="layui-elip w-100">关联商品：{{ item.count }}个</div>
                                <div class="layui-elip w-100">访问延迟: {{ item.ping }}</div>
                                <div class="layui-elip w-100"><a target="_blank" :href="item.url">{{ item.url }}</a>
                                </div>
                            </div>
                            <div class="layui-col-xs12 layui-col-sm12 mt-1 mdui-text-color-red"
                                 v-if="item.annotation!=''&&item.annotation!=null">
                                {{item.annotation}}
                            </div>
                            <div class="layui-col-xs12 layui-col-sm12 mt-1">
                                <button class="btn btn-success badge" @click="Ping(index)">
                                    测
                                </button>
                                <a class="btn btn-success badge ml-1" :href="'./admin.source.add.php?id='+item.id">
                                    <i class="layui-icon layui-icon-set" style="font-size:0.5em;"></i>
                                </a>
                                <button class="btn btn-danger badge ml-1" @click="SourceDelete(item.id,item.count)">
                                    <i class="layui-icon layui-icon-delete" style="font-size:0.5em;"></i>
                                </button>
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
                    <div class="mdui-panel-item mdui-shadow-0" :id="item.id + '_cards'">
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
<script src="../assets/admin/js/sourcelist.js?vs=<?= $accredit['versions'] ?>"></script>
<script src="../assets/admin/js/banner.js?vs=<?= $accredit['versions'] ?>"></script>
<script>
	AppBanner.ListGet(4);
</script>
</body>

</html>
