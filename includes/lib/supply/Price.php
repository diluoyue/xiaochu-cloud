<?php

namespace lib\supply;


use extend\UserConf;
use Medoo\DB\SQL;

class Price
{
    /**
     * @var array
     * 用户等级列表
     */
    public static $RankList = [];
    private static $PriceCache = [];

    /**
     * @param $Money
     * 根据成本和用户等级计算商品售价
     * 取出全部等级售价列表
     * @param $Profits
     * 商品利润比例
     * @param $Gid
     * 商品ID
     */
    public static function List($Money, $Profits, $Gid = false, $selling = '{}')
    {
        self::load();
        $Data = [];
        foreach (self::$RankList as $key => $value) {
            $Data[] = self::Get($Money, $Profits, ($key + 1), $Gid, $selling);
        }
        return $Data;
    }

    /**
     * @return bool
     * 等级配置载入
     */
    public static function load()
    {
        if (count(self::$RankList) !== 0) {
            return false;
        }
        $DB = SQL::DB();
        $Res = $DB->select('price', '*', ['state' => 1, 'ORDER' => ['sort' => 'ASC']]);
        if (!$Res) {
            self::$RankList = [];
        } else {
            self::$RankList = $Res;
        }
        return true;
    }

    /**
     * @param $Money
     * @param $Profits
     * @param int $Level
     * 根据用户等级和成本计算出商品售价！
     * 取出单个
     * @param array $selling
     * 自定义等级规则
     */
    public static function Get($Money, $Profits, $Level = 1, $Gid = false, $selling = '{}')
    {
        $NameMd5 = md5($Money . $Profits . $Level . $selling);
        if (!empty(self::$PriceCache[$NameMd5])) {
            return self::$PriceCache[$NameMd5];
        }

        self::load();

        $Money = (float)$Money;
        if ($Level === -1 || $Level === false) {
            $Level = 1;
        }


        if (count(self::$RankList) === 0) {
            $UserRank = [
                'name' => '站点用户',
                'priceis' => 0,
                'pointsis' => 1000,
                'rule' => -1,
            ];
        } else {
            if ($Level >= count(self::$RankList)) {
                //超出
                $Level = count(self::$RankList);
            }
            $UserRank = self::$RankList[($Level - 1)];
        }

        //计算收益
        if ($UserRank['rule'] != -1 && $Profits == 100) {
            $DB = SQL::DB();
            $rule_get = $DB->get('profit_rule', ['rules'], [
                'state' => 1,
            ]);
            if ($rule_get) {
                //替换利润比例
                $Profits = self::MatchProfitRatio(json_decode($rule_get['rules'] ?? '{}', true), $Money);
            }
        }

        if (!empty($selling)) {
            $selling = json_decode($selling, true);
        }

        if (!is_array($selling)) {
            $selling = [];
        }


        /**
         * 价格计算
         */

        if ((float)isset($selling[($Level - 1)]['a']) && $selling[($Level - 1)]['a'] != '') {
            $Price = (float)$selling[($Level - 1)]['a'];
        } else {
            $Price = round(($Money + ($Money * ($UserRank['priceis'] / 100)) * ((float)$Profits / 100)), 8);
        }

        if ((float)isset($selling[($Level - 1)]['b']) && $selling[($Level - 1)]['b'] != '') {
            $Profits = (float)$selling[($Level - 1)]['b'];
        } else {
            $Profits = round($Money * $UserRank['pointsis']);
        }

        /**
         * 分店加价
         */
        if ($Gid !== false) {
            $Rise = UserConf::GoodsPrice((int)$Gid);
            $Price += ($Price * ($Rise / 100));
            $Profits += ($Profits * ($Rise / 100));
        }

        self::$PriceCache[$NameMd5] = [
            'price' => $Price,
            'points' => $Profits,
            'name' => $UserRank['name'],
        ];

        return self::$PriceCache[$NameMd5];
    }

    /**
     * @param $freight
     * @param $input
     * @param $price
     * @param $num
     * @return float|int
     * 运费模板,返回的任何数据均已计算过份数售价
     */
    public static function Freight($freight, $input, $price, $num)
    {
        if (($price * $num) >= (float)$freight['threshold']) {
            //减免运费
            return $price * $num;
        }

        $freight_arr = explode('|', $freight['region']);

        $retos = '';
        foreach ($freight_arr as $v) {
            $rets = explode(',', $v);
            if (strpos(json_encode($input, JSON_UNESCAPED_UNICODE), $rets[0]) !== false) {
                $retos = $rets;
            }
        }

        if ($retos !== '') { //运费已自定义地区
            $_SESSION['retos'] = $retos[0] . '运费'; //地区
            $freight['money'] = (float)$retos[1]; //运费
            $freight['exceed'] = (float)$retos[2]; //加价金额！
            $_SESSION['exceed'] = $retos[0] . '地区，份数超出' . $freight['nums'] . '份，则每份需额外加' . round($freight['exceed'], 8) . '元运费';
        } else {
            $_SESSION['retos'] = '通用运费 ';
            $_SESSION['exceed'] = '若购买份数超出' . $freight['nums'] . '份，则每份需额外加' . round($freight['exceed'], 8) . '元运费';
        }

        if ($num > $freight['nums']) { //购买数量超出，加运费
            return round((((float)$price * $num) + (float)$freight['money']) + ((float)$freight['exceed'] * ((float)$num - (float)$freight['nums'])), 8);
        }

        //加正常运费
        return round((((float)$price * $num) + (float)$freight['money']), 8);
    }

    /**
     * @param $array //规则
     * @param $moeny //成本
     * 解析商品利润比例
     */
    public static function MatchProfitRatio($array, $moeny)
    {
        foreach ($array as $value) {
            if ($moeny >= $value['min'] && $moeny <= $value['max']) {
                return $value['profit'] ?? 100;
            }
        }
        //如果没有匹配成功，则使用默认
        return 100;
        //下方为匹配最终利润规则
        /*if (count($array) == 0) {
            return 100;
        }
        return $array[count($array) - 1]['profit'] ?? 100;*/
    }
}
