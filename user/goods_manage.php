<?php
/**
 * Author：晴玖天
 * Creation：2020/8/19 10:23
 * Filename：goods_manage.php
 */

$title = '商品管理';
include 'header.php';
if ($UserData['grade'] < $conf['usergradeprofit'] && $UserData['grade'] < $conf['usergradegoodsstate']) show_msg('温馨提示', '您当前等级未达到标准，无法管理商品！');
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                注意事项
            </div>
            <div class="card-body">
                <ul style="line-height: 2em;">
                    <li style="color: #ff4254">1、涨价后的效果您仅可在未登录的情况下，访问自己的店铺域名查看哦</li>
                    <li style="color: #18b50c">2、价格涨幅百分比只可填写数字，填1就相当于将此商品涨价百分之1，以此类推！</li>
                    <li style="color: #18b50c">3、若您在登陆状态下访问您的店铺域名，商品售价还是会显示为您的成本价，如果其他已登陆用户的用户等级和你一样，甚至比你高，也是不会有加价效果的！
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card" id="app">
            <div class="card-header">
                {{ name==''?'我的商品':'共搜索到' }} - {{ Sum }}个
                <button class="btn btn-success badge ml-1" @click="AlterPrice(-1,0)">一键改价</button>
                <button class="btn btn-primary badge ml-1" @click="State(-1)">一键上下架</button>
                <button class="btn btn-danger badge ml-1" @click="search()">搜索</button>
            </div>
            <div class="card-header" v-if="name!=''"><a href="./goods_manage.php">[ 查看全部 ]</a> 关键词： <font
                        color="#0000FF">{{ name }}</font> 的搜索结果如下：
            </div>
            <div class="card-body">
                <div class="layui-row layui-col-space8">
                    <div v-for="(item,index) in Data" :key="index" class="layui-col-xs6 layui-col-sm4 layui-col-lg3">
                        <div class="card">
                            <div class="card-header layui-elip font-13" :title="item.name">
                                <button @click="OpenGoods(item.gid)" class="btn btn-outline-primary badge mr-1"
                                        title="查看商品详情">{{ item.gid }}
                                </button>
                                {{ item.name }}
                            </div>
                            <div class="card-body">
                                <div class="layui-row">
                                    <div class="layui-col-xs12 layui-col-sm4 text-center">

                                        <div class="layui-hide-xs"><img @click="DocsGoods(index)" :src="item.image"
                                                                        style="width: 48px;height: 48px;border-radius: 0.5em;box-shadow: 3px 3px 16px #eee;"/>
                                        </div>
                                        <div class="layui-hide-sm"><img @click="DocsGoods(index)" :src="item.image"
                                                                        style="width: 78px;height: 78px;border-radius: 0.5em;box-shadow: 3px 3px 16px #eee;"/>
                                        </div>
                                    </div>
                                    <div class="layui-col-xs12 layui-col-sm8" style="font-size: 80%;">
                                        <div class="layui-hide-sm mt-2"></div>
                                        <div class="layui-elip w-100" :title="'成本：'+item.price+'元'">成本：{{ item.price
                                            }}元
                                        </div>
                                        <div class="layui-elip w-100">数量：{{ item.quantity }}{{item.units}}</div>
                                        <div class="layui-elip w-100">售出：{{ item.sales }}份</div>
                                        <div class="layui-elip w-100">库存：{{ item.quota }}份</div>
                                        <div class="layui-elip w-100" v-if="item.method!=2">支持兑换，需{{ item.points
                                            }}<?= $conf['currency'] ?></div>
                                        <div class="layui-elip w-100" v-if="item.method==2">
                                            此商品不支持<?= $conf['currency'] ?>兑换
                                        </div>
                                    </div>
                                    <div class="layui-col-xs12 layui-col-sm12 mt-1">
                                        <button class="btn btn-success badge" @click="AlterPrice(item.gid,item.rise)">涨价
                                            {{ item.rise }} %
                                        </button>
                                        <button v-if="item.state!=1" class="btn btn-danger badge ml-1"
                                                @click="State(item.gid,1)">已下架
                                        </button>
                                        <button v-if="item.state==1" class="btn btn-success badge ml-1"
                                                @click="State(item.gid,-1)">上架中
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="text-align: center;width: 100%;margin: 1.6rem 0;" v-if="statr==true">
                    <button @click="LoadMore" class="layui-btn layui-btn-primary layui-btn-radius">查看更多</button>
                </div>

                <div style="text-align: center;width: 100%;margin: 1.6rem 0;" v-if="statr==false">
                    <button class="layui-btn layui-btn-disabled layui-btn-radius">{{
                        name==''?'到底了':(Data.length==0?'没有搜索到包含此关键词的商品':'没有更多了') }}
                    </button>
                </div>
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
            Data: [],
            page: 1,
            name: '',
            statr: true,
            Sum: 0,
            scrollTop: 0,
            load: false
        },
        destroyed() {
            window.removeEventListener('scroll', this.handleScroll);
        },
        methods: {
            State(gid = -1, state = -2) {
                if (state == -2) {
                    btn = ['全部上架', '全部下架', '取消'];
                    msg = '是否要为' + (this.name == '' ? '全部' : '商品名称包含[' + this.name + ']的') + '商品调整上下架状态，下架的商品用户访问时是无法看到的！';
                } else {
                    btn = ['确认', '取消'];
                    msg = '是否要将此商品设置为' + (state == 1 ? '上架' : '下架') + '状态？';
                }

                let _this = this;
                let indexs = layer.open({
                    title: '温馨提示',
                    content: msg,
                    btn: btn,
                    icon: 3,
                    btn1: function (layero, index) {
                        layer.close(indexs);
                        if (state == -2) {
                            _this.StateAjax(-1, 1);
                        } else {
                            _this.StateAjax(gid, state);
                        }
                    },
                    btn2: function (layero, index) {
                        if (state == -2) {
                            _this.StateAjax(-1, -1);
                        }
                    }
                });
            },
            StateAjax(gid, state) {
                let _this = this;
                let is = layer.msg('处理中...', {icon: 16, time: 9999999});
                $.ajax({
                    type: 'post',
                    url: 'ajax.php?act=GoodsState',
                    data: {
                        gid: gid,
                        state: state,
                        name: _this.name
                    },
                    dataType: 'json',
                    success: function (res) {
                        layer.close(is);
                        if (res.code >= 0) {
                            layer.msg(res.msg, {icon: 1});
                            _this.initialize();
                            _this.GoodsList();
                        } else layer.msg(res.msg, {icon: 2});
                    }
                });
            },
            /**
             * 批量调整商品价格！
             */
            AlterPrice(gid = -1, rise = 0) {
                if (gid == -1) {
                    msg = '是否要为' + (this.name == '' ? '全部' : '商品名称包含[' + this.name + ']的') + '商品设置价格涨幅百分比？,价格越高您可以获得的收益越高！';
                } else msg = '是否要调整此商品的价格涨幅百分比？';
                let _this = this;
                layer.confirm(msg, {icon: 3, title: '提示'}, function (index) {
                    layer.close(index);
                    let index2 = layer.prompt(
                        {
                            formType: 0,
                            value: rise,
                            title: '请填写涨幅百分比1~100+,纯数字',
                            btn: ['确定', '取消']
                        },
                        function (value, index, elem) {
                            layer.close(index2);
                            let is = layer.msg('处理中...', {icon: 16, time: 9999999});
                            $.ajax({
                                type: 'post',
                                url: 'ajax.php?act=AlterPrice',
                                data: {
                                    gid: gid,
                                    rise: value,
                                    name: _this.name
                                },
                                dataType: 'json',
                                success: function (res) {
                                    layer.close(is);
                                    if (res.code >= 0) {
                                        layer.msg(res.msg, {icon: 1});
                                        _this.initialize();
                                        _this.GoodsList();
                                    } else layer.msg(res.msg, {icon: 2});
                                }
                            });
                        }
                    );
                });
            },
            /**
             * 初始化部分参数！
             */
            initialize() {
                this.Data = [];
                this.page = 1;
                this.statr = true;
                this.Sum = 0;
                this.scrollTop = 0;
                this.load = false;
            },
            search() {
                let _this = this;
                let index = layer.prompt(
                    {
                        formType: 0,
                        value: this.name,
                        title: '请输入商品名称关键词',
                        btn: ['搜索', '取消']
                    },
                    function (value, index, elem) {
                        layer.close(index);
                        _this.name = value;
                        _this.initialize();
                        _this.GoodsList();
                    }
                );
            },
            /**
             * 监听页面滚动
             */
            handleScroll() {
                this.scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
                if (this.statr == false) return;
                var viewH = $(window).height();
                var contentH = $(document).height();
                if (contentH - (this.scrollTop + viewH) <= viewH / 6) {
                    this.LoadMore();
                }
            },
            /**
             * 加载更多
             */
            LoadMore() {
                if (this.statr == false || this.load == true) return;
                this.load == true;
                ++this.page;
                this.GoodsList();
            },
            /**
             * @param {Object} index
             * 查看商品说明
             */
            DocsGoods(index) {
                let Data = this.Data[index];
                layer.open({
                    title: Data.name,
                    content: Data.docs,
                    btn: ['确定']
                });
            },
            OpenGoods(gid = '') {
                window.open('../?mod=route&p=Goods&gid=' + gid);
            },
            GoodsList() {
                let is = layer.msg('商品列表获取中...', {icon: 16, time: 9999999});
                let _this = this;
                $.ajax({
                    type: 'post',
                    url: 'ajax.php?act=GoodsList',
                    data: {name: this.name, page: this.page},
                    dataType: 'json',
                    success: function (data) {
                        layer.close(is);
                        if (data.code >= 0) {
                            if (data.data.length < 8) {
                                _this.statr = false;
                            }

                            for (let i = 0; i < data.data.length; i++) {
                                _this.Data.push(data.data[i]);
                            }

                            _this.Sum = data.count;
                        } else {
                            layer.msg(data.msg, {icon: 2});
                        }
                        _this.load == false;
                    },
                    error: function () {
                        layer.close(is);
                        layer.alert('商品列表获取失败！');
                    }
                });
            }
        },
        mounted() {
            this.GoodsList();
            window.addEventListener('scroll', this.handleScroll, true);
        }
    });
</script>
