<?php

/**
 * ajax接收端
 * 自定义接口会提交的数据为：
 * value[0~6],代表输入框内容
 */

use Curl\Curl;
use extend\ImgThumbnail;
use lib\App\App;
use Medoo\DB\SQL;
use Zxing\QrReader;

include './includes/fun.global.php';
header('Content-Type: application/json; charset=UTF-8');
global $_QET, $conf;

switch ($_QET['act']) {
    case 'LinkExtraction': //提取链接
        test(['value|e'], '内容不能为空！');
        $Url = URLExtraction($_QET['value']);
        dier([
            'code' => 1,
            'msg' => '内容获取成功！',
            'value' => $Url,
        ]);
        break;
    case 'SSID': //说说ID获取
        test(['value0|e'], 'QQ号不能为空');
        $Data = Curl::curl(false, ['act' => 'ssid', 'qq' => $_QET['value0'], 'page' => 1], true, '/qtapi/index', 2);
        if ($Data['code'] >= 0) {
            if (count($Data['data']) == 0) dies(-1, '没有获取到任何说说,请先在空间内发布一条说说试试！');
            $content = '';
            foreach ($Data['data'] as $value) {
                $content .= '<hr>说说ID：<font color="#66cdaa">' . $value['ssid'] . '</font><br>说说内容：' . $value['content'] . '<br>发布时间：' . $value['addtime'];
            }
            dier([
                'code' => 1,
                'msg' => '<div style="font-size: 0.7rem"><font>共获取到' . count($Data['data']) . '条说说，已经自动帮您填写好了第一条，可在下方列表长按复制其他的ID</font><br>' . $content . '</div>',
                'data' => [$_QET['value0'], $Data['data'][0]['ssid']],
            ]);
        } else dies(-1, 'QQ[' . $_QET['value0'] . ']的说说ID获取失败！');
        break;
    case 'DYID': //DYid
        test(['value|e'], 'DY链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        $Data = Curl::curl(false, ['act' => 'getshareid', 'url' => $Url], true, '/qtapi/index', 2);
        if ($Data['code'] >= 0) {
            dier([
                'code' => 1,
                'msg' => '恭喜，ID获取成功',
                'value' => $Data['songid'],
            ]);
        } else dies(-1, 'DYID获取失败！');
        break;
    case 'HSID':
        test(['value|e'], '火山链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        $Data = GetHSID($Url);
        if ($Data['code'] >= 0) {
            dier([
                'code' => 1,
                'msg' => '恭喜，ID获取成功',
                'value' => $Data['hsid'],
            ]);
        } else dies(-1, '火山ID获取失败！');
        break;
    case 'WSID':
        test(['value|e'], '微视链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        $Data = GetWSID($Url);
        if ($Data['code'] >= 0) {
            dier([
                'code' => 1,
                'msg' => '恭喜，ID获取成功',
                'value' => $Data['wsid'],
            ]);
        } else dies(-1, '微视ID获取失败！');
        break;
    case 'JRTTID':
        test(['value|e'], '今日头条链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        $Data = GetJRTTID($Url);
        if ($Data['code'] >= 0) {
            dier([
                'code' => 1,
                'msg' => '恭喜，ID获取成功',
                'value' => $Data['jrttid'],
            ]);
        } else dies(-1, '今日头条ID获取失败！');
        break;
    case 'PPXID':
        test(['value|e'], '链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        $Data = PPXID($Url);
        if ($Data['code'] >= 0) {
            dier([
                'code' => 1,
                'msg' => '恭喜，ID获取成功',
                'value' => $Data['ppxid'],
            ]);
        } else dies(-1, 'ID获取失败！');
        break;
    case 'XHSID':
        test(['value|e'], '链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        $Data = XHSID($Url);
        if ($Data['code'] >= 0) {
            dier([
                'code' => 1,
                'msg' => '恭喜，ID获取成功',
                'value' => $Data['xhsid'],
            ]);
        } else dies(-1, 'ID获取失败！');
        break;
    case 'MPID':
        test(['value|e'], '链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        $Data = MPID($Url);
        if ($Data['code'] >= 0) {
            dier([
                'code' => 1,
                'msg' => '恭喜，ID获取成功',
                'value' => $Data['mpid'],
            ]);
        } else dies(-1, 'ID获取失败！');
        break;
    case 'BLIID':
        test(['value|e'], '链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        $Data = BLIID($Url);
        if ($Data['code'] >= 0) {
            dier([
                'code' => 1,
                'msg' => '恭喜，ID获取成功',
                'value' => $Data['bliid'],
            ]);
        } else dies(-1, 'ID获取失败！');
        break;
    case 'ZYOUID':
        test(['value|e'], '链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        if (!strstr($Url, 'izuiyou.com')) dies(-1, '分享链接有误！');
        $ID = getSubstr($Url, '?pid=', '&rid=');
        if (!$ID) dies(-1, '分享链接有误！');
        dier([
            'code' => 1,
            'msg' => '恭喜，ID获取成功',
            'value' => $ID,
        ]);
        break;
    case 'QMKGID':
        test(['value|e'], '链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        if (strstr($Url, 'kg.qq.com')) {
            $ID = getSubstr($Url, 'play?s=', '&appsource=');
        } else if (strstr($Url, 'kg2.qq.com')) {
            $ID = getSubstr($Url, '/play?s=', '&shareuid');
        } else dies(-1, '分享链接有误！');
        if (!$ID) dies(-1, '分享链接有误！');

        dier([
            'code' => 1,
            'msg' => '恭喜，ID获取成功',
            'value' => $ID,
        ]);
        break;
    case 'MTXXID':
        test(['value|e'], '链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        if (!strstr($Url, 'meitu.com')) dies(-1, '分享链接有误！');
        $ID = getSubstr($Url, 'feed_id=', '&lang=');
        if (!$ID) dies(-1, '分享链接有误！');
        dier([
            'code' => 1,
            'msg' => '恭喜，ID获取成功',
            'value' => $ID,
        ]);
        break;
    case 'LVZID':
        test(['value|e'], '链接不可为空！');
        $Url = URLExtraction($_QET['value']);
        if (!strstr($Url, 'oasis.weibo.cn')) dies(-1, '分享链接有误！');
        $ID = getSubstr($Url, '?sid=', '');
        if (!$ID) dies(-1, '分享链接有误！');
        dier([
            'code' => 1,
            'msg' => '恭喜，ID获取成功',
            'value' => $ID,
        ]);
        break;
    case 'OrderState': //查询支付状态，公共查询模块
        $DB = SQL::DB();
        if (empty($_QET['order'])) dies(-1, '请将数据提交完整！');
        $Pay = $DB->get('pay', '*', [
            'order' => (string)$_QET['order']
        ]);
        if ($Pay['state'] == 1) {
            if ($_QET['type'] == 2) {
                dies(1, '支付订单已完成');
            } else {
                switch ($Pay['gid']) {
                    case -1: //在线充值
                        dies(1, '恭喜您成功充值' . round($Pay['money'], 2) . '元！');
                        break;
                    case -2: //订单队列
                        dies(1, '恭喜您成功购买' . $Pay['name'] . '！');
                        break;
                    default: //普通付款订单
                        dies(1, '恭喜您成功购买' . $Pay['name'] . '！');
                        break;
                }
            }
        } else dies(-1, '支付订单未完成！');
        break;
    case 'AppImage': //AppImage
        if (!isset($_QET['id'])) {
            Header('HTTP/1.1 303 See Other');
            Header('Location: ' . App::$ImageError);
        }
        App::ImagePreview($_QET['id']);
        break;
    case 'Decode': //二维码解码
        mkdirs(SYSTEM_ROOT . 'extend/log/Qr');
        $ImageName = md5_file($_QET['file']['tmp_name']);
        switch ($_QET['file']['type']) {
            case 'image/gif':
                $ImageName .= '.gif';
                break;
            case 'image/jpeg':
                $ImageName .= '.jpeg';
                break;
            case 'image/png':
            default:
                $ImageName .= '.png';
                break;
        }
        if (!in_array($_QET['file']['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
            dies(-1, '请上传png或jpg格式的图片');
        }
        move_uploaded_file($_QET['file']['tmp_name'], ROOT . 'includes/extend/log/Qr/' . $ImageName);
        $images = ROOT . 'includes/extend/log/Qr/' . $ImageName;
        if (!file_exists($images)) {
            dies(-1, '待解析的二维码上传失败！');
        }
        new ImgThumbnail($images, $conf['compression'], $images, 2);
        $qrcode = new QrReader($images);
        $Content = $qrcode->text();
        if (!$Content) {
            dies(-1, '二维码内容解析失败，请确保上传的图片内包含二维码！');
        }
        unlink($images); //解析成功后销毁图片
        dier([
            'code' => 1,
            'msg' => '解析结果为：' . $Content,
            'content' => $Content,
        ]);
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        dies(-2, '访问路径不存在！');
        break;
}
