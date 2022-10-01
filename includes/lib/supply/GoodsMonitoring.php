<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/6/8 12:40
// +----------------------------------------------------------------------
// | Filename: GoodsMonitoring.php
// +----------------------------------------------------------------------
// | Explain: 商品状态监控处理模块
// +----------------------------------------------------------------------

namespace lib\supply;


use Exception;
use Medoo\DB\SQL;

class GoodsMonitoring
{
    /**
     * @param $Num ,监控数量
     * 商品监控
     */
    public static function BatchMonitoring($Num)
    {
        $start = microtime(true);
        global $conf, $_QET;
        if ($conf['secret'] != $_QET['token']) {
            dies(-1, 'API对接密钥有误！');
        }
        $DB = SQL::DB();
        $Array = file_get_contents(ROOT . '/assets/log/phpshop.log');
        $Array = explode('|', $Array);

        $Count = $DB->count('goods', [
            'method[~]' => '5',
            'deliver' => -1
        ]);
        if ($Count === 0) {
            dies(-1, '当前无可监控商品！,商品价格监控仅支持串货类商品！', 2);
        }
        $GoodsList = $DB->select('goods', '*', [
            'gid[!]' => $Array,
            'method[~]' => '5',
            'deliver' => -1,
            'ORDER' => 'gid',
            'LIMIT' => $Num
        ]);
        if (!$GoodsList) {
            unlink(ROOT . "/assets/log/phpshop.log");
            dies(-1, '无可监控商品,即将开始下一轮监控！', 2);
        }
        $A1 = [];
        $A2 = [];
        $Error = [];
        foreach ($GoodsList as $Goods) {
            try {
                $DM = self::DirectionalMonitoring($Goods);
                if ($DM['code'] === 1) {
                    file_put_contents(ROOT . '/assets/log/phpshop.log', $Goods['gid'] . '|', FILE_APPEND);
                    $A1[] = "「{$Goods['name']}」({$Goods['gid']})已成功和对接站点同步！";
                } else if ($DM['code'] === 2) {
                    file_put_contents(ROOT . '/assets/log/phpshop.log', $Goods['gid'] . '|', FILE_APPEND);
                    $A2[] = "「{$Goods['name']}」无需同步：{$DM['msg']}";
                } else {
                    $Error[] = '商品' . $Goods['name'] . '监控失败,请重新尝试！';
                }
            } catch (Exception $e) {
                dies(-1, '程序执行异常，请重新尝试！');
            }
        }
        $end = microtime(true);
        $time = $end - $start;
        $seconds = number_format($time, 3, '.', '');
        dier([
            '已重新同步商品：' => count($A1) == 0 ? '无' : $A1,
            '无需同步的商品：' => count($A2) == 0 ? '无' : $A2,
            '监控失败的商品：' => count($Error) == 0 ? '无' : $Error,
            '本次监控耗时为：' => $seconds . '秒'
        ], 2);
    }

    /**
     * @param $Goods
     * 定向监控
     */
    public static function DirectionalMonitoring($Goods, $type = 1)
    {
        global $conf, $date;

        if ((int)$conf['SupervisoryError'] === 1 && (int)$conf['SupervisorySuccess'] === 1) {
            return [
                'code' => 2,
                'msg' => '后台已设置不做任何操作，已自动跳过！',
            ];
        }

        if ($type !== 1) {
            $filename = ROOT . "/assets/log/phpshop_" . $Goods['gid'] . '.log';
            $lock_date = filemtime($filename);
            $date_1s = time() - (int)$conf['SupervisoryCycle'];
            if ($date_1s < $lock_date && $lock_date !== false) {
                return [
                    'code' => 2,
                    'msg' => '未到可监控时间',
                ];
            }
            $fse = fopen($filename, "w");
            fwrite($fse, userip() . "\n" . $date);
            fclose($fse);
        }

        /**
         * 验证模块
         */
        $Data = StringCargo::Docking();
        $DB = SQL::DB();
        $SourceGoods = $DB->get('shequ', '*', [
            'id' => (int)$Goods['sqid'],
        ]);
        if (!$SourceGoods) {
            return [
                'code' => 2,
                'msg' => '此商品串货对接信息已丢失，无法监控！'
            ];
        }

        if ((string)$SourceGoods['class_name'] === '-1' || $SourceGoods['class_name'] === '') {
            $SourceGoods['class_name'] = StringCargo::DataConversion($SourceGoods['type']);
        }
        if ((string)$SourceGoods['class_name'] === '0') {
            $SourceGoods['class_name'] = 'jiuwu';
        }

        if (empty($Data[$SourceGoods['class_name']])) {
            return [
                'code' => 2,
                'msg' => '串货商品对接信息已丢失，无法完成监控！',
            ];
        }

        if ($Data[$SourceGoods['class_name']]['PriceMonitoring'] !== 1) {
            return [
                'code' => 2,
                'msg' => '货源：' . $Data[$SourceGoods['class_name']]['name'] . '不支持商品价格监控！',
            ];
        }

        /**
         * 通用商品数据详情获取
         * 返回内容
         * state (上下架状态) 1 正常 其他下架
         * money 进货成本（基数 = 售价 / 发货数量）
         * inventory 商品库存数量
         */

        $Data = self::ProductDetails($Goods, $SourceGoods);

        if ($Data['code'] < 0) {
            return [
                'code' => 2,
                'msg' => $Data['msg'],
            ];
        }
        /**
         * 数据调整
         */
        if ($Data['data']['state'] === 1) {
            if ((int)$conf['SupervisorySuccess'] === 1) {
                return [
                    'code' => 2,
                    'msg' => '对接商品状态正常，后台已设置不做任何操作！',
                ];
            }
            return self::DataSynchronization($Goods, $Data['data'], $SourceGoods['class_name']);
        }

        if ((int)$conf['SupervisoryError'] === 1) {
            return [
                'code' => 2,
                'msg' => '对接商品状态异常，后台已设置不做任何操作！',
            ];
        }
        return self::DataSynchronization($Goods, $Data['data'], $SourceGoods['class_name'], 2);
    }

    /**
     * @param $Goods
     * @param $Data
     * @param $type //货源类型
     * 货源类型
     * @param int $state
     * 处理模式 1|2
     * 商品数据同步
     */
    public static function DataSynchronization($Goods, $Data, $type = 1, $state = 1)
    {
        global $conf, $date;
        /**
         * 由于服务端和同系统可能存在商品规格参数，需额外配置，其他随意
         */
        if ($state === 1 && ($type === 'xiaochu' || $type === 'official') && $Data['data']['specification'] !== false) {
            $Types = self::ProductSpecificationSynchronization($Goods, $Data['specification']);
        } else {
            $Types = false;
        }
        if ($state === 1) {
            //上架状态
            $GoodsSet = [
                'quota' => $Data['inventory'],
                'money' => $Data['money'] * $Goods['quantity'],
                'state' => 1,
                'update_dat' => $date,
            ];
        } else {
            //对接商品下架或无法购买状态！
            $GoodsSet = [
                'quota' => ($conf['SupervisoryError'] == 2 ? 0 : $Goods['quota']),
                'state' => ($conf['SupervisoryError'] == 3 ? 2 : $Goods['state']),
                'money' => $Goods['money'],
                'update_dat' => $date,
            ];
        }

        $Res = self::update($Goods['gid'], $GoodsSet);
        if (!$Res) {
            return [
                'code' => 2,
                'msg' => '商品数据更新失败！，商品[' . $Goods['gid'] . ']无法进行监控',
            ];
        }

        if ($Types !== true && $GoodsSet['quota'] == $Goods['quota'] && $GoodsSet['money'] == $Goods['money'] && $GoodsSet['state'] == $Goods['state']) {
            return [
                'code' => 2,
                'msg' => '商品[' . $Goods['gid'] . ']监控成功，数据无变化！',
            ];
        }

        if ((float)$GoodsSet['money'] !== (float)$Goods['money']) {
            global $date;
            $PriceChange[] = [
                'type' => ($Goods['money'] < $GoodsSet['money'] ? 1 : 2), //1涨，2降
                'money' => $GoodsSet['money'], //最新成本
                'OriginalPrice' => $Goods['money'], //原价
                'name' => $Goods['name'], //商品名称
                'key' => false,
                'date' => $date,
                'time' => time(),
                'gid' => $Goods['gid'],
            ];
            self::ChangesCommodityPrices($PriceChange);
        }
        return [
            'code' => 1,
            'msg' => '商品[' . $Goods['gid'] . ']监控成功，数据已更新！',
        ];
    }

    /**
     * @param $Goods
     * @param $specification
     * 同步商品规格数据
     * @param $data
     * @return bool
     */
    public static function ProductSpecificationSynchronization($Goods, $specification)
    {
        global $date;
        if ((int)$Goods['specification'] === 1) {
            /**
             * 不知出于何种原因，这位站长未开启商品规格，那就跳过吧
             */
            return false;
        }
        $Sku = json_decode($Goods['specification_sku'], true);
        /**
         * 仅需同步库存，售价即可
         */
        $JsonSku = [];
        $Type = false;

        $PriceChange = []; //价格变动数据

        foreach ($Sku as $key => $val) {
            $ApiData = $specification[$key];
            if (empty($ApiData)) {
                $JsonSku[$key] = $val;
                continue;
            }
            if ($val['money'] === '' && $val['quota'] === '' && $ApiData['money'] === '' && $ApiData['inventory'] === '') {
                $JsonSku[$key] = $val;
                continue;
            }
            if ($ApiData['inventory'] !== '') {
                $inventory = $ApiData['inventory'];
            } else {
                $inventory = $val['quota'];
            }

            $quantity = (!empty($val['quantity']) ? $val['quantity'] : $Goods['quantity']);

            if ($ApiData['money'] !== '') {
                $Money = $quantity * $ApiData['money'];
            } else {
                $Money = $val['money'];
            }

            if ($Type !== true && ($inventory != $val['quota'] || $val['money'] != $Money)) {
                $Type = true;
            }

            if ($val['money'] != $Money) {
                $PriceChange[] = [
                    'type' => ($val['money'] < $Money ? 1 : 2), //1涨，2降
                    'money' => $Money, //最新成本
                    'OriginalPrice' => $val['money'], //原价
                    'name' => $Goods['name'], //商品名称
                    'key' => $key,
                    'date' => $date,
                    'time' => time(),
                    'gid' => $Goods['gid'],
                ];
            }

            $val['quota'] = $inventory;
            $val['money'] = $Money;
            $JsonSku[$key] = $val;
            unset($quantity, $Money, $inventory);
        }
        if (empty($JsonSku)) {
            $JsonSku = $Sku;
        }
        if (count($PriceChange) >= 1) {
            self::ChangesCommodityPrices($PriceChange);
        }
        $State = self::update($Goods['gid'], [
            'specification_sku[JSON]' => $JsonSku,
            'update_dat' => $date,
        ]);
        return ($State === true ? $Type : false);
    }

    /**
     * @param $PriceChange
     * 保存商品价格变动数据！
     */
    public static function ChangesCommodityPrices($PriceChange)
    {
        global $accredit;
        mkdirs(ROOT . 'includes/extend/log/PriceChange/');
        mkdirs(ROOT . 'includes/extend/log/PriceChangeGoods/');
        $file = ROOT . 'includes/extend/log/PriceChange/' . md5($accredit['token']) . '_' . date('Ymd') . '.log';
        foreach ($PriceChange as $val) {
            self::ChangesCommodityPricesGid([$val], $val['gid']);
        }
        if (is_file($file)) {
            $PriceChangeData = json_decode(file_get_contents($file), true);
            if (empty($PriceChangeData)) {
                $PriceChangeData = [];
            }
        } else {
            $PriceChangeData = [];
        }
        $Data = array_merge($PriceChangeData, $PriceChange);
        return file_put_contents($file, json_encode($Data));
    }

    /**
     * @param $Data
     * 单商品波动日志记录
     */
    public static function ChangesCommodityPricesGid($Data, $Gid)
    {
        global $accredit;
        $file = ROOT . 'includes/extend/log/PriceChangeGoods/' . md5($accredit['token']) . '_' . $Gid . '.log';
        if (is_file($file)) {
            $PriceChangeData = json_decode(file_get_contents($file), true);
            if (empty($PriceChangeData)) {
                $PriceChangeData = [];
            }
        } else {
            $PriceChangeData = [];
        }
        $Data = array_merge($PriceChangeData, $Data);
        return file_put_contents($file, json_encode($Data));
    }

    /**
     * @param $gid
     * @param $data
     * 更新商品数据
     * @return bool
     */
    public static function update($gid, $data): bool
    {
        $DB = SQL::DB();
        $Res = $DB->update('goods', $data, [
            'gid' => $gid,
        ]);
        if ($Res) {
            return true;
        }
        return false;
    }

    /**
     * @param $Goods
     * @param $TypeSupply
     * 获取商品状态信息
     */
    public static function ProductDetails($Goods, $TypeSupply)
    {
        $ApiData = StringCargo::Distribute([
            'Source' => $TypeSupply['class_name'],
            'Supply' => $TypeSupply,
            'Goods' => $Goods,
            'controller' => 'CommodityStatus', //绑定商品状态查询
        ]);

        if (!$ApiData) {
            return [
                'code' => -1,
                'msg' => '数据获取失败，对接类内无指定方法，或对接类已移除！',
            ];
        }

        return $ApiData;
    }
}
