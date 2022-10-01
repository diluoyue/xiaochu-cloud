<?php
$title = '等级列表';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a title="添加等级" href="./admin.level.add.php" class="badge badge-primary"><i
                            class="layui-icon layui-icon-addition"></i></a>
                <a title="重置等级" href="javascript:App.Reset()" class="badge badge-success ml-1"><i
                            class="layui-icon layui-icon-component"></i></a>
                <a title="初始化" href="javascript:App.UserLevelList();" class="badge badge-warning ml-1"><i
                            class="layui-icon layui-icon-refresh-3"></i></a>
                <a href="javascript:App.help();" class="btn btn-info badge ml-1 pb-0" style="">帮助</a>
            </div>
            <div class="card-header">
                <div class="layui-row">
                    <div class="layui-col-xs6">
                        商品成本(元)
                        <input type="number" v-model="money" class="layui-input" placeholder="商品成本">
                    </div>
                    <div class="layui-col-xs6">
                        利润比例(%)
                        <input type="number" v-model="profit" class="layui-input" placeholder="利润比例">
                    </div>
                </div>
            </div>
            <div class="card-body layui-row layui-col-space8">
                <div v-for="(item,index) in Data" class="layui-col-xs6 layui-col-sm4 layui-col-lg3">
                    <div class="card mdui-ripple">
                        <div class="card-header layui-elip font-13">
                            <button @click="LevelStateSet(item.mid,(item.state==1?2:1),item.name)"
                                    class="btn badge mr-1"
                                    :class="(item.state==1?'btn-outline-success':'btn-outline-danger')">
                                V{{ (Data.length)-index }}-{{(item.state==1?'开':'关')}}
                            </button>
                            <span :style="'color:'+colorById(item.name)">{{item.name}}</span>
                        </div>
                        <div class="card-body  mdui-valign">
                            <div class="layui-row" style="font-size:90%;">
                                <div class="layui-col-xs12 layui-col-sm12">
                                    <div style="height:2em;line-height:2em;">
                                        商品售价：<span style="color: salmon">{{ Price(item.priceis,1) }}元</span>
                                    </div>
                                    <div style="height:2em;line-height:2em;">
                                        每单利润：<span style="color: #4ca514">{{ Price(item.priceis,3) }}元</span>
                                    </div>
                                    <div style="height:2em;line-height:2em;">
                                        积分兑换：<span style="color: #2c86c4">{{ Price(item.pointsis,2) }}点</span>
                                    </div>
                                </div>
                                <div class="layui-col-xs12 layui-col-sm12 mt-1" style="line-height:2em;">
                                    <div class="layui-elip w-100">
                                        等级售价：<span class="badge badge-warning-lighten">{{ item.money }}元</span>
                                    </div>
                                    <div class="layui-elip w-100">加价比例：<span class="badge badge-danger-lighten">{{ item.priceis }}%</span>
                                    </div>
                                    <div class="layui-elip w-100">兑换倍数：<span class="badge badge-info-lighten">{{ item.pointsis }}倍</span>
                                    </div>
                                    <div class="layui-elip w-100">绝对利润：<span class="badge badge-success-lighten">{{ item.ActualProfit }}%</span>
                                    </div>
                                    <div class="layui-elip w-100">分成阈值：<span class="badge badge-primary-lighten">{{ item.ProfitThreshold }}%</span>
                                    </div>
                                </div>
                                <div class="layui-col-xs12 layui-col-sm12 mt-1">
                                    <button class="btn btn-success badge" title="至顶部"
                                            style="border-radius: 0.5rem 0 0 0.5rem;" @click="sort(item.mid,1)">
                                        ↑
                                    </button>
                                    <button class="btn btn-primary badge" title="上移1格" style="border-radius: 0;"
                                            @click="sort(item.mid,2)">
                                        ▲
                                    </button>
                                    <button class="btn btn-success badge" style="border-radius: 0;" title="下移1格"
                                            @click="sort(item.mid,3)">
                                        ▼
                                    </button>
                                    <button class="btn btn-primary badge" title="至底部" style="border-radius: 0;"
                                            @click="sort(item.mid,4)">
                                        ↓
                                    </button>
                                    <a class="btn btn-warning badge" style="border-radius: 0;"
                                       :href="'admin.level.add.php?id='+item.mid">
                                        设
                                    </a>
                                    <button class="btn btn-danger badge" style="border-radius: 0 0.5rem 0.5rem 0;"
                                            @click="LevelDelete(item.mid,item.name)">
                                        删
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                    {{ type==-1?'正在载入中,请稍后...':'一个等级也没有' }}
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/levellist.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>
