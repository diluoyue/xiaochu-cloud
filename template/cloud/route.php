<?php
/**
 * Author：晴玖天
 * Creation：2020/7/3 12:46
 * Filename：route.php
 * 路由导航，解决每个模板之间的路径差异问题！
 * 这只是一个简单的例子，开发新模板时可以对照开发，系统内置的访问方式已经注释好
 */

switch ($_GET['p']) {
    case 'Goods': //商品 /?mod=route&p=Goods&gid=xxx
        header("Location: " . ROOT_DIR . "?mod=shop&gid=" . $_GET['gid']);
        break;
    case 'Article': //文章 /?mod=route&p=Article
        header("Location: " . ROOT_DIR . "?mod=article");
        break;
    case 'Class': //分类 /?mod=route&p=Class
        header("Location: " . ROOT_DIR);
        break;
    case 'Order': //订单 /?mod=route&p=Order
        header("Location: " . ROOT_DIR . "?mod=query");
        break;
    case 'Cart': //购物车 /?mod=route&p=Cart
        header("Location: " . ROOT_DIR);
        break;
    case 'User': //用户中心 /?mod=route&p=User
        header("Location: " . ROOT_DIR . "user/");
        break;
    case 'Comment': //商品评论  /?mod=route&p=Comment&gid=xxx
        header("Location: " . ROOT_DIR);
        break;
    default:
        header("Location: " . ROOT_DIR);
        break;
}