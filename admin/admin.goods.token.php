<?php

/**
 * 发卡商品
 */

use Medoo\DB\SQL;

$title = '卡密管理 -  数据每10秒更新一次';
include 'header.php';
global $_QET;
$DB = SQL::DB();
$GoodsRe = $DB->select('goods', ['gid', 'name', 'state'], [
    'ORDER' => [
        'sort' => 'DESC'
    ],
    'deliver' => 3,
]);
$GoodsArray = [];
foreach ($GoodsRe as $val) {
    $val['count'] = $DB->count('token', ['gid' => $val['gid'], 'order' => null]);
    $GoodsArray[] = $val;
}
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a href="javascript:kami.add()" class="btn btn-success badge ml-1">
                    添加库存
                </a>
                <a href="javascript:kami.empty_use()" class="btn btn-danger badge ml-1">
                    清空已使用
                </a>
                <a href="javascript:kami.empty()" class="btn btn-danger badge ml-1">
                    清空全部
                </a>
                <a href="javascript:kami.query()" class="btn btn-warning badge ml-1 mdui-text-color-white">
                    搜索
                </a>
                <a href="javascript:kami.derive()" class="btn btn-info badge ml-1">
                    导出
                </a>
            </div>
            <div class="card-body">
                <div class="layui-form">
                    <div class="layui-form-item">
                        <label class="layui-form-label">查商品</label>
                        <div class="layui-input-block">
                            <select name="kmgid" lay-search lay-filter="kmgid">
                                <option value="">请选择对应的卡密商品</option>
                                <?php
                                foreach ($GoodsArray as $v) :
                                    $state = $v['state'] == 1 ? '上架中' : '已下架';
                                    echo '<option value="' . $v['gid'] . '" ';
                                    echo $_QET['gid'] == $v['gid'] ? 'selected' : null;
                                    echo ' >' . $v['name'] . ' - [' . $v['count'] . '库存/' . $state . ']</option>';
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" id="App">
                    <div v-if="name!=''" class="mb-2">正在查看和关键词[ {{name}} ]相关的卡密
                        <a href="javascript:App.initialization('')" class="btn btn-primary badge ml-1">
                            查看全部
                        </a>
                    </div>
                    <table class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                        <thead>
                        <tr style="white-space: nowrap">
                            <th>KID</th>
                            <th>UID</th>
                            <th>商品名称</th>
                            <th>卡密内容</th>
                            <th>提卡密码</th>
                            <th>使用者IP</th>
                            <th>对应订单</th>
                            <th>使用时间</th>
                            <th>添加时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item,index) in Data">
                            <td>{{ item.kid }}</td>
                            <td>
                                <span v-if="item.order==null||item.order==''" class="badge  ml-1 badge-success-lighten">未使用</span>
                                <span v-else class="badge  ml-1 badge-primary-lighten">{{ item.uid }}</span>
                            </td>
                            <td>
                                <div class="layui-elip mdui-text-color-deep-orange"
                                     style="max-width:10em;cursor:pointer" :title="item.name">
                                    {{ (item.name==null||item.name==''?'商品已删除':item.name) }}
                                </div>
                            </td>
                            <td>{{ item.token }}</td>
                            <td>{{ (item.order==null||item.order==''?'未使用':item.code) }}</td>
                            <td>
                                <span v-if="item.order==null||item.order==''" class="badge  ml-1 badge-success-lighten">未使用</span>
                                <span v-else class="badge  ml-1 badge-danger-lighten">{{ item.ip }}</span>
                            </td>
                            <td>
                                <span v-if="item.order==null||item.order==''" class="badge  ml-1 badge-success-lighten">未使用</span>
                                <span v-else class="badge  ml-1 badge-warning-lighten">{{ item.order }}</span>
                            </td>
                            <td>
                                <span v-if="item.order==null||item.order==''" class="badge  ml-1 badge-success-lighten">未使用</span>
                                <span v-else class="badge  ml-1 badge-primary-lighten">{{ item.endtime }}</span>
                            </td>
                            <td>{{ item.addtime }}</td>
                            <td>
                                    <span @click="TokenDe(item.kid)" title="删除商品" class="action-icon"
                                          style="cursor: pointer; color: red;">
                                        <i class="layui-icon layui-icon-delete"></i>
                                    </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div v-if="Data.length===0" class="text-center w-100 mt-3 font-w300">
                        {{ type==-1?'正在载入中,请稍后...':'一个卡密也没有' }}
                    </div>
                </div>
                <div class="layui-card-body" style="text-align:center;">
                    <div id="Page"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/vue3.js"></script>
<script>
    let gid = '<?= $_QET['gid'] ?>';
</script>
<script src="../assets/admin/js/tokenlist.js?vs=<?= $accredit['versions'] ?>"></script>
