<?php
$title = '添加/修改对接货源';
include 'header.php';
global $title, $_QET;
$ID = (empty($_QET['id']) ? -1 : $_QET['id']);
?>
<style>
    .select {
        border: none;
        width: 99%;
        height: 36px;
        line-height: 36px;
        margin-left: 1%;
    }
</style>
<div class="row" id="App" sid="<?= $ID ?>">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <a title="返回" href="./admin.source.list.php" class="badge badge-success mr-1"><i
                            class="layui-icon layui-icon-return"></i></a>
                <a title="初始化" href="javascript:vm.UpdateCache();" class="badge badge-warning ml-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                {{ id==-1?'添加货源站':'更新[ '+ id +' ]货源信息' }}
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <button v-for="(item,index) in Data" @click="Select(index)" class="btn badge ml-1 "
                            :class="index==class_name?'btn-success':'btn-danger'">{{ item.name }}
                    </button>
                </div>
                <blockquote v-if="class_name==-1" class="layui-elem-quote">
                    请在上方选择一个需要添加的货源!
                </blockquote>
                <blockquote v-if="class_name!=-1&&Data[class_name].help!=-1" v-html="Data[class_name].help"
                            class="layui-elem-quote">
                </blockquote>
                <blockquote v-if="ips!=-1" class="layui-elem-quote">
                    当前服务器IP地址为: <span class="badge badge-primary-lighten">{{ip}}</span>
                </blockquote>
                <fieldset v-if="class_name!=-1&&Advertising[class_name]" class="layui-elem-field">
                    <legend>推荐对接 - <a href="admin.source.promotion.php" target="_blank">点击接入</a></legend>
                    <div class="layui-field-box">
                        <button v-for="(item,index) in Advertising[class_name]"
                                @click="SelectAdvertising(index,class_name)"
                                class="btn badge ml-1 badge-primary">{{ item.name }}
                        </button>
                    </div>
                </fieldset>
                <div class="layui-form layui-form-pane" lay-filter="formGets">
                    <div v-for="(item,index) in BoxData">
                        <div class="layui-form-item">
                            <label class="layui-form-label">{{ item.name }}</label>
                            <div v-if="item.type==1" class="layui-input-block">
                                <input type="text" :name="index" v-model="Form[index]" required lay-verify="required"
                                       :placeholder="item.tips" autocomplete="off" class="layui-input">
                            </div>
                            <div v-if="item.type==2" class="layui-input-block" style="border: solid #e6e6e6 1px;">
                                <select class="select" :name="index" v-model="Form[index]" lay-ignore>
                                    <option v-for="(n,i) in item.tips" :value="i">{{n}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div v-if="class_name!=-1" class="layui-form-item layui-form-text">
                        <label class="layui-form-label">货源备注信息 [ 可留空 ] </label>
                        <div class="layui-input-block">
                            <textarea v-model="Form['annotation']" name="annotation" placeholder="请输入备注信息，可在货源列表看到！"
                                      class="layui-textarea"></textarea>
                        </div>
                    </div>
                    <button v-if="class_name!=-1" type="submit" lay-submit lay-filter="Preserve"
                            class="btn btn-block btn-xs btn-success">
                        {{ id==-1?'添加一个对接货源':'保存货源对接信息' }}
                    </button>
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
<script src="../assets/admin/js/sourceadd.js?vs=<?= $accredit['versions'] ?>"></script>
<script src="../assets/admin/js/banner.js?vs=<?= $accredit['versions'] ?>"></script>
<script>
    AppBanner.ListGet(4);
</script>
