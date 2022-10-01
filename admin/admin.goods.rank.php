<?php

$title = '商品热销排行';
include 'header.php';

?>
<div class="card">
    <div class="card-body" style="padding:0.3em !important;">

        <div class="mdui-tab mdui-tab-full-width" mdui-tab>
            <a href="#Ranking1" onclick="HotCommodity.ToHotAll()" class="mdui-ripple">销售总榜</a>
            <a href="#Ranking2" onclick="HotCommodity.ToDayHot()" class="mdui-ripple">今日热卖</a>
            <a href="#Ranking3" onclick="HotCommodity.HotYesterday()" class="mdui-ripple">昨日热卖</a>
        </div>
        <div id="Ranking1" class="mdui-p-a-2">
            <table id="ToHotAll" lay-filter="ToHotAll"></table>
        </div>
        <div id="Ranking2" class="mdui-p-a-2">
            <table id="ToDayHot" lay-filter="ToDayHot"></table>
        </div>
        <div id="Ranking3" class="mdui-p-a-2">
            <table id="HotYesterday" lay-filter="HotYesterday"></table>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script>
    var HotCommodity = {
        ToHotAll: function () {
            layui.use('table', function () {
                var table = layui.table;
                table.render({
                    elem: '#ToHotAll',
                    url: './ajax.php?act=ToHot&type=All',
                    page: true,
                    loading: true,
                    skin: 'nob',
                    size: 'lg',
                    cols: [
                        [{
                            field: 'gid',
                            title: 'GID',
                            width: 70,
                            sort: true,
                            fixed: 'left'
                        },
                            {
                                field: 'name',
                                templet: '#name',
                                title: '商品名称',
                                width: 200
                            },
                            {
                                field: 'count',
                                templet: '#count',
                                title: '售出份数',
                                width: 150,
                                sort: true
                            },
                            {
                                field: 'money',
                                templet: '#money',
                                title: '销售总额',
                                width: 150,
                            },
                            {
                                field: 'cost',
                                templet: '#cost',
                                title: '消费成本',
                                width: 150,
                            },
                            {
                                field: 'content',
                                templet: '#content',
                                title: '说明'
                            }
                        ]
                    ]
                });
            });
        },
        ToDayHot: function () {
            layui.use('table', function () {
                var table = layui.table;
                table.render({
                    elem: '#ToDayHot',
                    url: './ajax.php?act=ToHot&type=Day',
                    page: true,
                    loading: true,
                    skin: 'nob',
                    size: 'lg',
                    cols: [
                        [{
                            field: 'gid',
                            title: 'GID',
                            width: 70,
                            sort: true,
                            fixed: 'left'
                        },
                            {
                                field: 'name',
                                templet: '#name',
                                title: '商品名称',
                                width: 200
                            },
                            {
                                field: 'count',
                                templet: '#count',
                                title: '售出份数',
                                width: 150,
                                sort: true
                            },
                            {
                                field: 'money',
                                templet: '#money',
                                title: '销售总额',
                                width: 150,
                            },
                            {
                                field: 'cost',
                                templet: '#cost',
                                title: '消费成本',
                                width: 150,
                            },
                            {
                                field: 'content',
                                templet: '#content',
                                title: '说明'
                            }
                        ]
                    ]
                });
            });
        },
        HotYesterday: function () {
            layui.use('table', function () {
                var table = layui.table;
                table.render({
                    elem: '#HotYesterday',
                    url: './ajax.php?act=ToHot&type=Yesterday',
                    page: true,
                    loading: true,
                    skin: 'nob',
                    size: 'lg',
                    cols: [
                        [{
                            field: 'gid',
                            title: 'GID',
                            width: 70,
                            sort: true,
                            fixed: 'left'
                        },
                            {
                                field: 'name',
                                templet: '#name',
                                title: '商品名称',
                                width: 200
                            },
                            {
                                field: 'count',
                                templet: '#count',
                                title: '售出份数',
                                width: 150,
                                sort: true
                            },
                            {
                                field: 'money',
                                templet: '#money',
                                title: '销售总额',
                                width: 150,
                            },
                            {
                                field: 'cost',
                                templet: '#cost',
                                title: '消费成本',
                                width: 150,
                            },
                            {
                                field: 'content',
                                templet: '#content',
                                title: '说明'
                            }
                        ]
                    ]
                });
            });
        }
    };

    HotCommodity.ToHotAll();
</script>
<script type="text/html" id="content">
    共售出了{{ d.count-0 }}份，其中消耗用户余额{{ d.money-0 }}元，耗费成本{{ d.cost-0 }}元！
</script>
<script type="text/html" id="name">
    <a class="badge badge-primary-lighten" href="admin.goods.add.php?gid={{d.gid}}" title='编辑商品' target="_blank">
        {{ d.name }}
    </a>
</script>
<script type="text/html" id="count">
    <a class="badge badge-warning-lighten" href="admin.order.list.php?gid={{d.gid}}" title="查看订单" target="_blank">{{
        d.count }}份</a>
</script>
<script type="text/html" id="money">
    <span class="badge badge-info-lighten">{{ (d.money==null?'0.00':d.money-0) }}元</span>
</script>
<script type="text/html" id="cost">
    <span class="badge badge-danger-lighten">{{ (d.cost==null?'0.00':d.cost-0) }}元</span>
</script>
