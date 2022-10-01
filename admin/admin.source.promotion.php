<?php
// +----------------------------------------------------------------------
// | Project: xc
// +----------------------------------------------------------------------
// | Creation: 2022/7/22
// +----------------------------------------------------------------------
// | Filename: admin.source.promotion.php
// +----------------------------------------------------------------------
// | Explain: 货源推广[接入官方服务端]
// +----------------------------------------------------------------------

$title = '货源推广';
include 'header.php';
?>
<div class="row" id="App">
    <div class="col-12">
        <div class="card">
            <div class="card-header" v-if="Form.id==-1">
                创建新的推广 - 余额：{{Money}}元
            </div>
            <div class="card-header" v-else>
                编辑推广参数 -「{{Form.name}}」 <span class="badge badge-primary-lighten"
                                              @click="Form.id = -1;Form.class_name='jiuwu';Form.name='';Form.url='';Form.content='';">退出编辑状态</span>
            </div>
            <div class="card-body">
                <div class="layui-form">
                    <div class="layui-form-item">
                        <label style="font-weight: 300">货源类型</label>
                        <select lay-ignore v-model="Form.class_name"
                                style="width: 100%;height: 38px;line-height: 1.3;border-color: #ccc;text-indent: 7px;color: #333">
                            <option v-for="(item,index) in SourceList" :value="index">{{item}}</option>
                        </select>
                    </div>
                    <div class="layui-form-item">
                        <label style="font-weight: 300">货源名称</label>
                        <input type="text" v-model="Form.name" required lay-verify="required" placeholder="请输入推广货源名称"
                               autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-item">
                        <label style="font-weight: 300">货源地址</label>
                        <input type="text" v-model="Form.url" required lay-verify="required"
                               placeholder="请输入货源地址,如：https://baidu.com/"
                               autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-item">
                        <label style="font-weight: 300">货源地址</label>
                        <textarea type="text" v-model="Form.content" required lay-verify="required"
                                  placeholder="请输入货源介绍,纯文字！" class="layui-textarea"></textarea>
                    </div>
                    <div class="layui-form-item">
                        <button v-if="Form.id==-1" @click="Create()" type="submit"
                                class="layui-btn btn-block btn-primary">
                            点击创建 -
                            [价格：{{Price}}元/月,续期同价]
                        </button>
                        <button v-else @click="EditingNew()" type="submit" class="layui-btn btn-block btn-danger">
                            点击保存内容
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                我的推广列表
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0" style="font-size:0.9em;">
                        <thead>
                        <tr style="white-space: nowrap">
                            <th>名称</th>
                            <th>货源地址</th>
                            <th>宣传内容</th>
                            <th>货源类型</th>
                            <th>推广状态</th>
                            <th>创建时间</th>
                            <th>到期时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item,index) in List" :style="'color: '+(item.type==1?'#000':'red')">
                            <td>{{ item.name }}</td>
                            <td>{{ item.url }}</td>
                            <td>{{ item.content }}</td>
                            <td>{{ item.className }}</td>
                            <td>{{ item.state }}</td>
                            <td>{{ item.addtime }}</td>
                            <td>
                                <span>{{ item.endtime }}</span>
                            </td>
                            <td>
                                <button @click="Editing(item)" class="btn btn-sm btn-outline-primary">编辑</button>
                                <button @click="Renew(item)" class="btn btn-sm btn-outline-success mt-1">续期</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
<script src="../assets/js/jquery.nestable.js"></script>
<script src="../assets/js/vue3.js"></script>
<script src="../assets/admin/js/source_promotion.js?vs=<?= $accredit['versions'] ?>"></script>
</body>

</html>

