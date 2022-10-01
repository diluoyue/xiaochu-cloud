<?php

/**
 * Author：晴玖天
 * Creation：2020/8/9 12:38
 * Filename：admin.FilePower.php
 * 目录权限管理！
 */
$title = '网站目录权限管理';
include 'header.php';
?>
<div id="app">
    <div class="layui-fluid" style="padding: 0;">
        <div class="p-2">
            为了防止网站缓存无法写入，请直接将下列文件或目录权限全部设置为777，部分主机可能无法修改文件权限，请自行寻找解决办法
        </div>
        <div class="card">
            <div class="card-header">
                {{ Data.length==0?'文件列表载入中...':'文件权限管理('+Data.length+')' }}
                <button class="layui-btn btn-success layui-btn-xs" @click="FilePowerSet(1)" style="float: right;">
                    一键777权限
                </button>
            </div>
            <div class="card-body">
                <div class="card" v-for="(item,index) in Data" :key="index">
                    <div class="card-header text-primary">
                        权限：{{ item.Power }}
                        <button @click="FilePowerSet(2,index)" class="layui-btn btn-primary layui-btn-xs"
                                style="float: right;">修改为777权限
                        </button>
                    </div>
                    <div class="card-body">
                        位置：{{ item.name }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>

<script>
    var vm = new Vue({
        el: '#app',
        data: {
            Data: []
        },
        mounted() {
            this.AjaxList();
        },
        methods: {
            AjaxList() {
                let _this = this;
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php?act=FilePowerList',
                    async: true,
                    dataType: 'json',
                    success: function (res) {
                        if (res.code >= 0) {
                            for (let i = 0; i < res.data.length; i++) {
                                setTimeout(() => {
                                    _this.Data.push(res.data[i]);
                                }, 18 * i);
                            }
                        } else layer.msg(res.msg);
                    }
                });
            },
            FilePowerSet(type, id = -1) {
                let _this = this;
                let index = layer.confirm(
                    type == 1 ? '是否要将这些文件/目录权限设置为777?' : '是否要将此文件/目录权限设置为777?', {
                        icon: 3,
                        title: '温馨提示'
                    },
                    function (indexr) {
                        layer.close(index);
                        var index2 = layer.load(1, {
                            time: 9999999999
                        });
                        $.ajax({
                            type: 'POST',
                            url: 'ajax.php?act=FilePowerSet',
                            data: {
                                type: type,
                                id: id
                            },
                            async: true,
                            dataType: 'json',
                            success: function (res) {
                                layer.close(index2);
                                if (res.code >= 0) {
                                    layer.alert(res.msg, {
                                        icon: 1,
                                        end: function (layero, index) {
                                            location.reload();
                                        }
                                    });
                                } else layer.alert(res.msg);
                            },
                            error: function () {
                                layer.close(index2);
                                layer.alert('操作失败！');
                            }
                        });
                    }
                );
            }
        }
    });
</script>
