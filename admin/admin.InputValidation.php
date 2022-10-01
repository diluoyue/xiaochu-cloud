<?php
$title = '下单输入规则管理';
include 'header.php';
?>
<div id="app">
    <div class="layui-fluid" style="padding: 0;">
        <div class="card">
            <div class="card-header">相关：
                <button class="btn btn-warning badge ml-1" @click="introduce">介绍</button>
                <button class="btn btn-success badge ml-1" @click="introduce2">注解</button>
                <button class="btn btn-primary badge ml-1" @click="add">新增规则</button>
            </div>
        </div>
        <div class="layui-row layui-col-space10 bg-white mb-3">
            <div v-for="(item,index) in Data" class="layui-col-xs12 layui-col-sm6 layui-col-lg4" :key="index">
                <div class="mdui-card">
                    <div :title="index" class="mdui-card-header mdui-ripple layui-elip">
                        <div class="mdui-card-primary-title">{{ index }}</div>
                    </div>
                    <div class="mdui-card-content mdui-table-fluid">
                        <table class="mdui-table mdui-table-hoverable mb-0">
                            <thead>
                            <tr style="white-space: nowrap">
                                <th>名称</th>
                                <th>内容</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr style="white-space: nowrap;font-size: 0.9em">
                                <td>按钮名称</td>
                                <td v-if="item.state==1">
                                    <button class="layui-btn layui-btn-xs btn-success">{{ item.name }}</button>
                                </td>
                                <td v-else>
                                    <input class="input layui-input-block" placeholder="请将按钮名称填写完整"
                                           v-model="Data[index].name"/>
                                </td>
                            </tr>
                            <tr style="white-space: nowrap;font-size: 0.9em">
                                <td>动作类型</td>
                                <td v-if="item.state==1" :style="item.type==-1?'color:#0000ff':'color:#ff0000'">
                                    <span v-if="item.type==-1">自定义api接口</span>
                                    <span v-else-if="item.type==1">调用内置地址选择控件</span>
                                    <span v-else-if="item.type==2">调用二维码内容解析控件</span>
                                </td>
                                <td v-else>
                                    <select class="layui-select" v-model="Data[index].type">
                                        <option value="-1">调用自定义api接口</option>
                                        <option value="1">调用内置地址选择控件</option>
                                        <option value="2">调用图片二维码内容解析控件</option>
                                    </select>
                                    请选择类型
                                </td>
                            </tr>
                            <tr style="white-space: nowrap;font-size: 0.9em">
                                <td>传值内容</td>
                                <td :style="item.way==1?'color:#00aaff':'color:#55aa00'" v-if="item.state==1">{{
                                    item.way==1?'当前输入框内容':'全部输入框内容'}}
                                </td>
                                <td v-else>

                                    <select class="layui-select" v-model="Data[index].way">
                                        <option value="1">当前成功匹配的输入框内容</option>
                                        <option value="2">全部输入框内容，完整的</option>
                                    </select>
                                    请选择类型
                                </td>
                            </tr>
                            <tr style="white-space: nowrap;font-size: 0.9em">
                                <td>输入提示</td>
                                <td v-if="item.state==1">{{ item.placeholder}}</td>
                                <td v-else>
                                    <input class="input layui-input-block" placeholder="匹配成功后此输入框内的提示信息"
                                           v-model="Data[index].placeholder"/>
                                </td>
                            </tr>
                            <tr style="white-space: nowrap;font-size: 0.9em">
                                <td>接口地址</td>
                                <td :style="item.url==null||item.type==1?'color:#868686':'color:#5500ff'"
                                    v-if="item.state==1">{{ item.url==null||item.type==1?'内置':item.url + ' [暴露在外]'
                                    }}
                                </td>
                                <td v-else>
                                    <input class="input layui-input-block" placeholder="此接口地址会保留在前台,请自行做中转调整"
                                           v-model="Data[index].url"/><br>可用参数：[url]
                                    = <?= href(2) ?><br>
                                    <font color="red">Ps：如果是调用当前站点接口，务必添加此参数！</font>
                                </td>
                            </tr>
                            <tr style="white-space: nowrap;font-size: 0.9em">
                                <td>操作规则</td>
                                <td v-if="item.state==1">
                                    <button class="btn btn-sm btn-primary" @click="edit(index)">编辑</button>
                                    <button class="btn btn-sm btn-success" @click="matching(index)">修改匹配字段</button>
                                    <button class="btn btn-sm btn-danger" @click="unset(index)">删除</button>
                                </td>
                                <td v-else>
                                    <button class="btn btn-sm btn-warning mdui-text-color-white" @click="cancel(index)">
                                        预览
                                    </button>
                                    <button class="btn btn-sm btn-success" @click="preserve(index)">保存</button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>
<script src="../assets/admin/js/InputVal.js?vs=<?= $accredit['versions'] ?>"></script>
<style scoped lang="scss">
    .input {
        background-color: rgba(0, 0, 0, 0);
        border: none;
        border-bottom: solid 1px #ccc;
        font-size: 0.8rem;
        width: 100%;
        margin-left: 0;
    }
</style>
