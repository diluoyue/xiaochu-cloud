<?php
/**
 * 提交参数构建
 */

class cpay
{
    public function cpay_sgin($pay_row, $cpay_array, $siteurl)
    {
        $codepay_id = $siteurl['cpay_id'];//这里改成码支付ID
        $codepay_key = $siteurl['cpay_key']; //这是您的通讯密钥
        $data = array(
            "id" => $codepay_id,//码支付ID
            "pay_id" => $pay_row['order'], //订单号
            "type" => $cpay_array['type'],//1支付宝支付 3微信支付 2QQ钱包
            "price" => $pay_row['money'],//金额100元
            "notify_url" => $siteurl['notify_url'],//通知地址
            "return_url" => $siteurl['return_url'],//跳转地址
            "chartchart" => $cpay_array['chartchart'], //编码
            "page" => $cpay_array['page'],
            "style" => $cpay_array['style'],
            "outTime" => $cpay_array['outTime'], #超时限制,默认300s
            "min" => $cpay_array['min'], //最小付款金额
            "pay_type" => $cpay_array['pay_type'],
        ); //构造需要

        ksort($data); //重新排序$data数组
        reset($data); //内部指针指向数组中的第一个元素

        $sign = ''; //初始化需要签名的字符为空
        $urls = ''; //初始化URL参数为空

        foreach ($data as $key => $val) { //遍历需要传递的参数
            if ($val == '' || $key == 'sign') continue; //跳过这些不参数签名
            if ($sign != '') { //后面追加&拼接URL
                $sign .= "&";
                $urls .= "&";
            }
            $sign .= "$key=$val"; //拼接为url参数形式
            $urls .= "$key=" . urlencode($val); //拼接为url参数形式并URL编码参数值
        }
        $query = $urls . '&sign=' . md5($sign . $codepay_key); //创建订单所需的参数
        //$url = "http://api2.fateqq.com:52888/creat_order/?{$query}"; //支付页面
        $url = is_https(false) . href() . "/?AppApies&identification=cpay&typeS=5&{$query}"; //支付页面
        return $url;
    }
}