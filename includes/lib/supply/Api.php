<?php

namespace lib\supply;


use extend\GoodsImage;
use Medoo\DB\SQL;

/**
 * Api下单模块
 * Author：晴玖天
 * Creation：2020/4/24 0:19
 * Filename：Api.php
 */
class Api
{
    /**
     * @param $OrData 订单数据
     * @param $Goods GoodsImage;
     */
    public static function Submit($OrData, $Goods)
    {
        global $date, $conf;
        $DB = SQL::DB();
        $GoodsData = json_decode($Goods['extend'], true); //对接数据
        if (empty($GoodsData['url'])) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => '对接配置有误，请确保提交地址正确！',
                'money' => 0,
                'order' => 0,
            ];
        }
        $Order = $DB->get('order', '*', ['id' => (int)$OrData['id']]);
        $InputData = json_decode($Order['input'], TRUE);
        if ((int)$Goods['specification'] === 2 && (int)$Goods['specification_type'] === 2) {
            $InputData = RuleSubmitParameters(json_decode($Goods['specification_spu'], TRUE), $InputData);
        }
        $Url = self::replace($GoodsData['url'], $Order, $Goods, $InputData, 1);
        $Header = self::replace($GoodsData['header'], $Order, $Goods, $InputData, 2);
        $Post = self::replace($GoodsData['post'], $Order, $Goods, $InputData, 2);
        $CurlData = self::SuppluCurl($Url, $Post, $Header);
        if (empty($CurlData)) {
            return [
                'code' => $conf['SubmitState'],
                'docking' => 2,
                'msg' => '对接返回信息有误，请根据对接日志调试！',
                'money' => 0,
                'order' => 0,
            ];
        }
        $CurlData = json_decode($CurlData, TRUE);
        /**
         * 遍历匹配自定义状态参数
         */
        if (isset($CurlData['msg'])) {
            $msg = $CurlData['msg'];
        }
        if (isset($CurlData['message'])) {
            $msg = $CurlData['message'];
        }
        if (isset($CurlData['result'])) {
            $msg = $CurlData['result'];
        }
        if (isset($CurlData['code'])) {
            $code = $CurlData['code'];
        }
        if (isset($CurlData['state'])) {
            $code = $CurlData['state'];
        }
        if (isset($CurlData['status'])) {
            $code = $CurlData['status'];
        }
        if (isset($CurlData['money'])) {
            $money = $CurlData['money'];
        }
        if (isset($CurlData['order'])) {
            $orders = $CurlData['order'];
        }

        if (isset($CurlData['price'])) {
            //写入成本
            $DB->update('order', [
                'money' => $CurlData['price'],
            ], [
                'id' => $Order['id']
            ]);
        }

        if (!isset($msg)) {
            $msg = '未知返回信息：' . json_encode($CurlData, JSON_UNESCAPED_UNICODE);
        }

        if (!isset($code)) {
            $code = -1;
        }

        if (!isset($money)) {
            $money = 0;
        }

        if (!isset($orders)) {
            $orders = -1;
        }
        if (isset($CurlData['kami']) && count($CurlData['kami']) >= 1) {
            $SQL = [];
            foreach ($CurlData['kami'] as $v) {
                if (empty($v)) {
                    continue;
                }
                $SQL[] = [
                    'uid' => $Order['uid'],
                    'gid' => $Goods['gid'],
                    'code' => $InputData[0],
                    'token' => $v,
                    'ip' => $Order['ip'],
                    'order' => $Order['order'],
                    'endtime' => $date,
                    'addtime' => $date,
                ];
            }
            $DB->insert('token', $SQL);
        }
        $docking = ($code >= 0 ? 1 : 2);

        return [
            'code' => $code,
            'docking' => $docking,
            'msg' => $msg,
            'money' => $money,
            'order' => $orders,
        ];
    }

    /**
     * @param $Str
     * @param $Order
     * @param $Goods
     * @param $Input
     * @param int $Type
     * 内容替换
     * @return array|string|string[]
     */
    public static function replace($Str, $Order, $Goods, $Input, int $Type = 1)
    {
        if ($Type === 1) {
            $Str = str_replace(['[name]', '[share]', '[number]', '[total]', '[order]', '[price]', '[url]', '[gid]', '[time]', '[input]'], [$Goods['name'], $Order['num'], $Goods['quantity'], ($Order['num'] * $Goods['quantity']), $Order['order'], $Order['price'], href(), $Goods['gid'], time(), json_encode($Input)], $Str);
            foreach ($Input as $key => $value) {
                $Str = str_replace('[input' . ($key + 1) . ']', $value, $Str);
            }
        } else {
            if (empty($Str)) {
                return [];
            }
            $Array = [];
            foreach ($Str as $value) {
                $Ex = explode('|', $value);
                $Array[$Ex[0]] = self::replace($Ex[1], $Order, $Goods, $Input, 1);
            }
            $Str = $Array;
        }
        return $Str;
    }

    /**
     * @param $url //URL链接
     * @param int $post Post数据
     * @param int $referer 伪造来路域名
     * @param int $cookie CK数据
     * @param int $header 开启请求头
     * @param int $ua 环境模拟
     * @param int $nobaody 输出请求头信息
     * @return bool|string
     * 对接提交专用,此处提交的数据都会被记录！
     */
    public static function SuppluCurl($url, $post = 0, $Header = [], $headerType = 0, $referer = 0, $cookie = 0, $ua = 0, $nobaody = 0)
    {
        $start = microtime(true);
        global $accredit, $date;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = 'Accept: */*';
        $httpheader[] = 'Accept-Encoding: gzip,deflate,sdch';
        $httpheader[] = 'Accept-Language: zh-CN,zh;q=0.8';
        $httpheader[] = 'Connection: close';
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($post) ? http_build_query($post) : $post));
        }
        if ($headerType) {
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
        }
        if (count($Header) >= 1) {
            foreach ($Header as $key => $val) {
                $httpheader[] = $key . ': ' . $val;
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($ua) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
        }
        global $conf;
        if ($conf['PMIPState'] == 1) {
            curl_setopt($ch, CURLOPT_PROXY, $conf['PMIP']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $conf['PMIPPort']);
        }
        if ($nobaody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        $end = microtime(true);
        mkdirs(SYSTEM_ROOT . 'extend/log/Supply');
        file_put_contents(SYSTEM_ROOT . 'extend/log/Supply/Supply_' . $accredit['token'] . '.log', json_encode([$date => ['url' => $url, 'post' => $post, 'data' => $ret, 'ping' => (round(number_format($end - $start, 10, '.', ''), 4) . '秒')]], JSON_UNESCAPED_UNICODE) . 'SEGMENTATION', FILE_APPEND);
        curl_close($ch);
        return $ret;
    }

    /**
     * @param $url //URL链接
     * @param int $post Post数据
     * @param int $referer 伪造来路域名
     * @param int $cookie CK数据
     * @param int $header 开启请求头
     * @param int $ua 环境模拟
     * @param int $nobaody 输出请求头信息
     * @return bool|string
     * 商品对接专用,此处不会被记录
     */
    public static function Curl($url, $post = 0, $Header = [], $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = 'Accept: */*';
        $httpheader[] = 'Accept-Encoding: gzip,deflate,sdch';
        $httpheader[] = 'Accept-Language: zh-CN,zh;q=0.8';
        $httpheader[] = 'Connection: close';
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($post) ? http_build_query($post) : $post));
        }
        if (count($Header) >= 1) {
            foreach ($Header as $key => $val) {
                $httpheader[] = $key . ': ' . $val;
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
        }
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($ua) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
        }
        global $conf;
        if ($conf['PMIPState'] == 1) {
            curl_setopt($ch, CURLOPT_PROXY, $conf['PMIP']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $conf['PMIPPort']);
        }
        if ($nobaody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}
