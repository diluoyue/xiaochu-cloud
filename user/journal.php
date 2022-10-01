<?php

/**
 * 我的日志
 */
$title = '我的日志';
include 'header.php';
?>
<div class="row" id="app" data="<?= (empty($_QET['uid']) ? -1 : $_QET['uid']) ?>">
    <div class="col-xs-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                日志总数 - {{sum}}
            </div>
            <div class="card-header" v-if="uid!=-1"><a href="./journal.php" class="btn btn-success badge mr-1 font-13">查看全部</a><a
                        href="./subordinate.php" class="btn btn-primary badge mr-1 font-13">返回</a> 下级用户：<font
                        color="#0000FF">{{ uid }}</font> 的收益日志如下：
            </div>
            <div class="card-header" v-if="name!=''">
                <button class="btn btn-primary badge mr-1 font-13" @click="search('')">查看全部类型</button>
                类型： <font color="#0000FF">{{ name }}</font> 的搜索结果如下：
            </div>
            <div class="card-body p-0" style="overflow: hidden;overflow-x: auto;">
                <table class="table  dt-responsive nowrap p-1" style="white-space: nowrap">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>类型</th>
                        <th>数量</th>
                        <th>内容</th>
                        <th>IP</th>
                        <th>时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,index) in Data" :key="index">
                        <td>{{ item.id }}</td>
                        <td @click="search(item.name)" :style="'color:' + colorById(item.name)">
                            {{ item.name }}
                        </td>
                        <td :title="item.count">
                            <font v-if="item.name=='余额提成'||item.name=='升级提成'" style="color: #249e17;">{{
                                item.count==0?'无提成':'+'+item.count+'元' }}</font>
                            <font v-else-if="item.name=='货币提成'||item.name=='积分充值'" style="color: #249e17;">{{
                                item.count==0?'无变化':'+'+item.count+'积分' }}</font>

                            <font v-else-if="item.name=='每日签到'" style="color: #249e17;">{{
                                item.count==0?'无奖励':'+'+item.count }}</font>

                            <font v-else-if="item.name=='邀请奖励'" style="color: #249e17;">{{
                                item.count==0?'无奖励':'+'+item.count+'积分' }}</font>

                            <font v-else-if="item.name=='余额退款'||item.name=='后台加款'||item.name=='在线充值'"
                                  style="color: #249e17;">{{ item.count==0?'无变化':'+'+item.count+'元' }}</font>

                            <font v-else-if="item.name=='商品改价'" style="color: #55aaff;">{{
                                item.count==0?'无变化':item.count+'%' }}</font>


                            <font v-else-if="item.name=='货币提成(无效)'||item.name=='余额提成(无效)'"
                                  style="color: #ff0000;">已退款</font>

                            <font v-else-if="item.name=='积分兑换'" style="color: #ff0000;">{{
                                item.count==0?'无变化':'-'+item.count+'积分' }}</font>

                            <font v-else-if="item.name=='后台扣款'||item.name=='等级提升'" style="color: #ff0000;">{{
                                item.count==0?'无变化':'-'+item.count+'元' }}</font>
                            <font v-else-if="item.name=='域名绑定'||item.name=='余额购买'||item.name=='在线购买'"
                                  style="color: #ff0000;">{{ item.count==0?'未扣款':'-'+item.count+'元' }}</font>

                            <font v-else style="color: #8a8a8a;">{{ item.count==0?'无':item.count }}</font>
                        </td>
                        <td>{{ item.content }}</td>
                        <td>{{ item.ip }}</td>
                        <td>{{ item.date }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div style="text-align:center" id="paging"></div>
        </div>
    </div>
</div>

<?php
include 'bottom.php';
?>
<script>
    uid = $("#app").attr('data');
    var vm = new Vue({
        el: '#app',
        data: {
            Data: [],
            sum: 0,
            page: 1,
            name: '',
            uid: uid,
            ColorArr: []
        },
        methods: {
            search(name) {
                if (this.name == name) return;
                this.name = name;
                this.sum = 0;
                this.page = 1;
                this.Ajax();
            },
            colorById(i) {
                i = i.charCodeAt(0);
                const key = i;
                if (this.ColorArr['co_' + key] !== undefined) {
                    return this.ColorArr['co_' + key];
                }
                if (i < 10) i = i * 92.5;
                if (i < 100) i = i * 35.2;
                for (; i > 255; i *= 0.98) ;
                var temp = i.toString().substring(i.toString().length - 3);
                i += parseInt(temp);
                for (; i > 255; i -= 255) ;
                i = parseInt(i);
                if (i < 10) i += 10;

                var R = i * (i / 100);
                for (; R > 255; R -= 255) ;
                if (R < 50) R += 60;
                R = parseInt(R).toString(16);

                var G = i * (i % 100);
                for (; G > 255; G -= 255) ;
                if (G < 50) G += 60;
                G = parseInt(G).toString(16);

                var B = i * (i % 10);
                for (; B > 255; B -= 255) ;
                if (B < 50) B += 60;
                B = parseInt(B).toString(16);
                this.ColorArr['co_' + key] = "#" + R + G + B;
                return this.ColorArr['co_' + key];
            },
            Ajax() {
                let is = layer.msg('日志列表获取中...', {
                    icon: 16,
                    time: 9999999
                });
                let _this = this;
                $.ajax({
                    type: 'post',
                    url: 'ajax.php?act=LogList',
                    data: {
                        page: this.page,
                        name: this.name,
                        uid: this.uid
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(is);
                        if (data.code >= 0) {
                            _this.Data = data.data;
                            if (_this.sum == 0) {
                                layui.use('laypage', function () {
                                    var laypage = layui.laypage;
                                    laypage.render({
                                        elem: 'paging',
                                        count: data.count,
                                        theme: '#641ec6'
                                        , limit: 8
                                        , groups: 6
                                        , first: '首页'
                                        , last: '尾页'
                                        , prev: '上一页'
                                        , next: '下一页'
                                        , skip: true
                                        , layout: ['count', 'page', 'prev', 'next', 'limits'],
                                        jump: function (obj, first) {
                                            _this.page = obj.curr;
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
                        _this.load = false;
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