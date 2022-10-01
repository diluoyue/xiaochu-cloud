<?php
/**
 * Author：晴玖天
 * Creation：2020/4/27 21:56
 * Filename：admin.discuss.list.php
 * 评论管理
 */

use Medoo\DB\SQL;

$title = '评论管理';
include 'header.php';
$DB = SQL::DB();
global $UserData;
?>
<style>
    .image_sc {
        margin: 0.3em;
        box-shadow: 3px 3px 16px #eee;
        border-radius: 0.5em;
        width: 60px;
        height: 60px;
    }
</style>
<div class="row">
    <div class=" col-md-12 col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                评论管理 -
                <span class="badge badge-primary-lighten">显示(<?= $DB->count('mark', ['state' => 1, 'uid' => $UserData['id']]) ?>)</span>
                <span class="badge badge-warning-lighten">待审核(<?= $DB->count('mark', ['state' => 2, 'uid' => $UserData['id']]) ?>)</span>
                <span class="badge badge-success-lighten">驳回(<?= $DB->count('mark', ['state' => 3, 'uid' => $UserData['id']]) ?>)</span>
            </div>
            <div class="card-body" style="z-index: 1">
                <table class="layui-hide" id="test-table-totalRow" lay-filter="test-table-totalRow"></table>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>

<script>
    layui.use(['form', 'table', 'upload'], function () {
        var table = layui.table;
        table.render({
            elem: '#test-table-totalRow'
            , url: 'ajax.php?act=Mark&type=List'
            , toolbar: '#test-table-totalRow-toolbarDemo'
            , title: '评论管理'
            , cellMinWidth: 120
            , id: 'idTest'
            , cols: [[
                {field: 'content', title: '评论内容', totalRow: true}
                , {field: 'image', templet: '#image', title: '配图', totalRow: true}
                , {field: 'state', templet: '#state', title: '状态', sort: true, totalRow: true}
                , {field: 'score', title: '评分', sort: true, totalRow: true}
                , {field: 'uid', templet: '#uid', title: '用户ID'}
                , {field: 'gid', templet: '#gid', title: '商品ID'}
                , {field: 'order', templet: '#order', title: '相关订单', totalRow: true}
                , {field: 'name', title: '参数', sort: true, totalRow: true}
                , {field: 'seller', title: '卖家ID', sort: true, totalRow: true}
                , {field: 'addtime', title: '评论时间', sort: true, totalRow: true}
            ]]
            , page: true
        });
    });

    function image(img) {
        arr = img.split('|');
        con = '';
        i = 1;
        $.each(arr, function (key, val) {
            if (i == 3) {
                s = '<br>';
                i = 1;
            } else {
                s = '';
                ++i;
            }
            con += '<img lay-src="' + val + '" class="image_sc" />' + s;

        });
        layer.open({
            title: '评论图片预览',
            content: con,
            skin: 'layui-layer-rim',
            btn: false,
            shade: [0.8, '#393D49'],
            shadeClose: true,
        });
        layui.use('flow', function () {
            var flow = layui.flow;
            flow.lazyimg();
        });
    }

</script>
<script type="text/html" id="uid">
    <a href="javascript:void(0)">{{ d.uid }}</a>
</script>
<script type="text/html" id="gid">
    <a href="javascript:void(0)">{{ d.gid }}</a>
</script>
<script type="text/html" id="order">
    <a href="javascript:void(0)">{{ d.order }}</a>
</script>
<script type="text/html" id="image">
    {{# if(d.image==''){ }}
    无图评论
    {{# }else{ }}
    <a href="javascript:image('{{ d.image }}')">查看图片</a>
    {{# } }}
</script>
<script type="text/html" id="state">
    {{# if(d.state==3 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #fb3418">已驳回</buttom>
    {{# }else if(d.state==2 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: rgba(14,0,101,0.6)">审核中</buttom>
    {{# }else if(d.state==1 ){ }}
    <buttom class="layui-btn layui-btn-xs" style="background-color: #00cb5b">已通过</buttom>
    {{# } }}
</script>
