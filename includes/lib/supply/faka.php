<?php

/**
 * Author：晴玖天
 * Creation：2020/4/24 12:13
 * Filename：faka.php
 * 发卡商品
 */

namespace lib\supply;


use lib\Hook\Hook;
use Medoo\DB\SQL;

class faka
{
    /**
     * @param $OrData 订单信息
     * @param $Goods 商品数据
     */
    public static function Submit($OrData, $Goods)
    {
        global $date;
        $DB = SQL::DB();

        $count = $Goods['num'] * $Goods['quantity'];
        if ($count <= 1) {
            $count = 1;
        }

        $Res = $DB->select('token', ['kid', 'token'], [
            "AND" => [
                "OR" => [
                    "order[=]" => "",
                    "order" => null
                ],
                "gid" => $Goods['gid']
            ],
            'ORDER' => 'kid',
            'LIMIT' => $count
        ]);

        if (count($Res) < $count) {
            $DB->update('order', [
                'return' => '卡密库存不足' . $count,
            ], [
                'id' => $OrData['id']
            ]);
            return [
                'code' => -1,
                'msg' => '卡密库存不足' . $count,
            ];
        }

        $HookArr = [
            'Order' => $OrData,
            'Token' => $Res
        ];

        Hook::execute('FakaLater', $HookArr);

        /**
         * 修改订单状态！
         */
        $Darr = [];
        $TokenArr = [];
        foreach ($Res as $v) {
            $Darr = array_merge($Darr, [$v['kid']]);
            $TokenArr = array_merge($TokenArr, [$v['token']]);
        }

        $Order = $DB->get('order', '*', ['id' => (int)$OrData['id']]);

        $InputData = json_decode($Order['input'], TRUE);
        if ($Goods['specification'] == 2 && $Goods['specification_type'] == 2) {
            $InputData = RuleSubmitParameters(json_decode($Goods['specification_sku'], TRUE)[0], $InputData);
        }

        $Re = $DB->update('token', [
            'uid' => $Order['uid'],
            'order' => $Order['order'],
            'code' => $InputData[0],
            'ip' => $Order['ip'],
            'endtime' => $date,
        ], [
            'kid' => $Darr
        ]);

        return [
            'code' => ($Re ? 1 : -1),
            'msg' => ($Re ? '发卡成功,本次共发卡' . count($Darr) . '张！,卡密内容：<br>' . implode('<br>', $TokenArr) : '发卡失败,请联系管理员发卡！'),
            'order' => $Order['order'],
        ];
    }
}
