<?php

/**
 * 我的日志
 */
$title = '我的下级';
include 'header.php';
global $conf;
?>
<div class="row" id="app">
    <div class="col-xs-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                下级总数 - {{sum}} -
                <button class="btn btn-success badge ml-1" @click="Ajax(2)">获取最新</button>
            </div>
            <div class="card-header" v-if="name!=''">
                <button class="btn btn-primary badge mr-1 font-13"
                        @click="search('')">查看全部
                </button>
                类型： <font color="#0000FF">{{ name }}</font> 的搜索结果如下：
            </div>
            <div class="card-body p-0" style="overflow: hidden;overflow-x: auto;">
                <table class="table  dt-responsive nowrap p-1" style="white-space: nowrap">
                    <thead>
                    <tr>
                        <th>UID</th>
                        <th>头像</th>
                        <th>等级</th>
                        <th title="表示您从该用户上获得的收益金额">获利金额</th>
                        <th title="表示您从该用户上获得的收益<?= $conf['currency'] ?>">获利<?= $conf['currency'] ?></th>
                        <th>上级UID</th>
                        <th>分销层级</th>
                        <th>注册时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data" :key="index">
                        <td title="查看Ta为我带来的收益日志"><a :href="'journal.php?uid=' + item.id ">{{ item.id }}</a></td>
                        <td><img :src="item.image" style="width:1rem;height:1rem"/></td>
                        <td>{{ item.gradename }}</td>
                        <td> {{ item.money==-1?'载入中':item.money }}</td>
                        <td>{{ item.currency==-1?'载入中':item.currency }}</td>
                        <td>{{ item.superior }}</td>
                        <td>{{ item.index }}层</td>
                        <td>{{ item.date }}</td>
                        <td>
                            <button class="btn btn-primary badge ml-1" title="刷新获利金额/积分"
                                    @click="AjaxMoney(item.id,index,2)"><i class="layui-icon">&#xe669;</i></button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div style="text-align:center;" id="paging"></div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12">
        <div class="card">
            <div class="card-header bg-city">
                下级用户列表相关说明
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <colgroup>
                        <col width="100">
                        <col>
                    </colgroup>
                    <tr class="">
                        <th>疑问</th>
                        <th>解答</th>
                    </tr>
                    <tbody>
                    <tr>
                        <td>获利是什么?</td>
                        <td>这里的获利分为两种，一种是余额提成获益，另一种是<?= $conf['currency'] ?>获益，代表的是您从此下级用户上获得的收益提成！</td>
                    </tr>
                    <tr>
                        <td>如何获得下级?</td>
                        <td>
                            只需要邀请用户前往您的站点购买商品即可！当用户购买后，如果此用户为登陆状态，Ta就会成为您的直系下级！当然，前提是Ta当前未成为任何人的下级，否则只能够获得商品提成，不会绑定到您的下级列表内！
                            <br>快去<a
                                    href="activity.php" target="_blank">邀请</a>新用户在您的站点购买商品，成为您的直属下级吧！
                        </td>
                    </tr>
                    <tr>
                        <td>分销层级是什么?</td>
                        <td>您的直系下级为一层分销，您直系下级的下级为二层分销，以此类推，只要您的等级足够高，可以无限招收下级帮您赚钱哦！</td>
                    </tr>
                    <tr>
                        <td>如何获得提成呢?</td>
                        <td>
                            无论是几层分销用户，只要在您的小店购买商品，理论上都可以为您提供提成收益，当然，收益分成有一个阈值，如果可分成的收益低于这个阈值就不会继续向上分成到您这了，另外，如果曾经的下级用户的等级高于您，那么他就不会继续为您产生收益，相当于独立出去了，所以您尽量保证自己的等级高于其他人哦，这样子可以发展更多的下级用户！
                        </td>
                    </tr>
                    </tbody>
                </table>
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
            sum: 0,
            page: 1,
            name: '',
            limit: 8
        },
        methods: {
            search(name) {
                if (this.name == name) return;
                this.name = name;
                this.sum = 0;
                this.page = 1;
                this.Ajax();
            },
            AjaxMoney(uid, index, type = 1) {
                if (type == 2) {
                    var is = layer.msg('获利数据获取中...', {
                        icon: 16,
                        time: 9999999
                    });
                }

                let _this = this;
                $.ajax({
                    type: 'post',
                    url: 'ajax.php?act=SubordinateUserMoney',
                    data: {
                        uid: uid,
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (type == 2) {
                            layer.close(is);
                            if (data.code >= 1) {
                                layer.msg(data.msg, {
                                    icon: 1
                                });
                                if (_this.Data[index]['id'] == data.data.uid) {
                                    _this.Data[index]['money'] = data.data.money;
                                    _this.Data[index]['currency'] = data.data.currency;
                                }
                            } else layer.msg(data.msg, {
                                icon: 2
                            });
                        } else {
                            if (_this.Data[index]['id'] == data.data.uid) {
                                _this.Data[index]['money'] = data.data.money;
                                _this.Data[index]['currency'] = data.data.currency;
                            }
                        }
                    },
                    error: function () {
                        layer.close(is);
                        layer.alert('数据更新失败！');
                    }
                });
            },
            Ajax(type = 1) {
                let is = layer.msg('用户列表获取中...', {
                    icon: 16,
                    time: 9999999
                });
                let _this = this;
                $.ajax({
                    type: 'post',
                    url: 'ajax.php?act=SubordinateUser',
                    data: {
                        page: this.page,
                        name: this.name,
                        type: type,
                        limit: this.limit
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(is);
                        if (data.code >= 0) {
                            _this.Data = data.data;
                            for (const key in _this.Data) {
                                if ((_this.Data).hasOwnProperty(key)) {
                                    const value = (_this.Data)[key];
                                    _this.AjaxMoney(value.id, key);
                                }
                            }
                            if (_this.sum == 0) {
                                layui.use('laypage', function () {
                                    var laypage = layui.laypage;
                                    laypage.render({
                                        elem: 'paging',
                                        count: data.count,
                                        theme: '#641ec6'
                                        , limit: vm.limit
                                        , limits: [8, 10, 20, 30, 50, 100, 200]
                                        , groups: 6
                                        , first: '首页'
                                        , last: '尾页'
                                        , prev: '上一页'
                                        , next: '下一页'
                                        , skip: true
                                        , layout: ['count', 'page', 'prev', 'next', 'limit', 'limits'],
                                        jump: function (obj, first) {
                                            _this.page = obj.curr;
                                            _this.limit = obj.limit;
                                            if (!first) {
                                                _this.Ajax();
                                            }
                                        }
                                    });
                                });
                            }
                            _this.sum = data.count;
                        } else {
                            layer.msg(data.msg, {
                                icon: 2
                            });
                        }
                        _this.load == false;
                    },
                    error: function () {
                        layer.close(is);
                        layer.alert('列表获取失败！');
                    }
                });
            }
        },
        mounted() {
            this.Ajax();
        }
    });
</script>