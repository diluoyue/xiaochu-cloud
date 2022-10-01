<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/15 12:06
// +----------------------------------------------------------------------
// | Filename: header.php
// +----------------------------------------------------------------------
// | Explain: 全局通用文件
// +----------------------------------------------------------------------
use Server\Server;

include '../../includes/fun.global.php';
$UserData = Server::LoginStatus();
if (!$UserData) {
    header("Location: ./login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>晴玖系统</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="../../assets/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="../../assets/layuiadmin/style/admin.css" media="all">
</head>
