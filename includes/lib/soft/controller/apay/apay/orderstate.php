<?php
/**
 * 订单付款状态查询
 */

use Medoo\DB\SQL;

require_once("/includes/fun.global.php");
if (empty((int)$_QET['order'])) dies(-1, '403');
$DB = SQL::DB();
$Res = $DB->get('pay', [
    'state'
], [
    'order' => $_QET['order'],
]);
if ((int)$Res['state'] === 1) {
    query::OrderCookie($_QET['order']);
    dier(['code' => 1, 'msg' => '订单已付款', 'url' => is_https(false) . href() . '/?mod=query']);
} else {
    dies(-1, '订单未付款');
}