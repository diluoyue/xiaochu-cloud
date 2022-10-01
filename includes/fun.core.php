<?php

use lib\Guard\Guard;
use lib\Hook\Hook;
use lib\Pay\Pay;
use Medoo\DB\SQL;

/**
 * 将秒数转换为剩余详细时间！
 */
function Sec2Time($time)
{
    if (is_numeric($time)) {
        $value = array(
            'years' => 0, 'days' => 0, 'hours' => 0,
            'minutes' => 0, 'seconds' => 0,
        );
        if ($time >= 31556926) {
            $value['years'] = floor($time / 31556926);
            $time = ($time % 31556926);
        }
        if ($time >= 86400) {
            $value['days'] = floor($time / 86400);
            $time = ($time % 86400);
        }
        if ($time >= 3600) {
            $value['hours'] = floor($time / 3600);
            $time = ($time % 3600);
        }
        if ($time >= 60) {
            $value['minutes'] = floor($time / 60);
            $time = ($time % 60);
        }
        $value['seconds'] = floor($time);

        $t = ($value['years'] >= 1 ? $value['years'] . '年' : '') . ($value['days'] >= 1 ? $value['days'] . '天' : '') . ($value['hours'] >= 1 ? $value['hours'] . '时' : '') . ($value['minutes'] >= 1 ? $value['minutes'] . '分' : '') . ($value['seconds'] >= 1 ? $value['seconds'] . '秒' : '');
        return (!empty($t) ? $t : '0秒');
    }

    return false;
}

/**
 * @param $startdate 开始时间
 * @param int $enddate 结束时间
 * 返回两者之间相差多少秒
 */
function TimeLag($startdate, $enddate = -1)
{
    global $date;
    $enddate = ($enddate == -1 ? $date : $enddate);
    $time = strtotime($enddate) - strtotime($startdate); //和结束时间的时间戳
    $text = '';
    $dater = floor(($time) / 86400);
    if ($dater > 0) {
        $time = $time - ($dater * 86400);
        $text .= $dater . '天';
    }

    $hour = floor(($time) / 3600);
    if ($hour > 0) {
        $time = $time - ($hour * 3600);
        $text .= $hour . '小时';
    }

    $minute = floor(($time) / 60);
    $time = $time - ($minute * 60);
    if ($minute > 0) {
        $text .= $minute . '分钟';
    }

    $second = floor(($time) % 60);
    if ($second > 0) {
        $text .= $second . '秒';
    }

    return $text;
}

/**
 * @param $vals 混淆函数
 * 生成唯一token参数！
 */
function TokenCreate($vals = '1')
{
    $key = mt_rand();
    $hash = hash_hmac('sha1', $vals . mt_rand() . time(), $key, true);
    return str_replace(['=', '_', '-'], '', strtr(base64_encode($hash), '+/', '-_'));
}

/**
 * @param string
 * @param string skey
 * @return string
 * 数据加密函数
 */
function xiaochu_en($string, $skey = 'xiaochuyun')
{
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value) {
        $key < $strCount && $strArr[$key] .= $value;
    }
    return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
}

/**
 * @param $string
 * @param string $skey
 * @return false|string
 * 数据解密函数
 */
function xiaochu_de($string, $skey = 'xiaochuyun')
{
    $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value) {
        $key <= $strCount && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    }
    return base64_decode(join('', $strArr));
}

function saddslashes($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = saddslashes($val);
        }
    } else {
        $string = addslashes($string);
    }
    return $string;
}

/**
 * @param $code
 * @param $msg
 * @param $type
 * @return never
 */
function dies($code = -1, $msg = '', $type = 1)
{
    if (empty($_SESSION['ADMIN_TOKEN'])) {
        $msg = Guard::Filtrate($msg);
    }
    session_write_close();


    if (empty($msg)) {
        $msg = '没有返回任何信息,请检查接口是否出现异常！';
    }

    $array = [
        'code' => $code,
        'status' => $code,
        'state' => $code,
        'msg' => $msg,
        'info' => $msg,
        'message' => $msg
    ];

    if ($type === 1) {
        die(json_encode($array));
    }
    die(json_encode($array, JSON_UNESCAPED_UNICODE));
}

/**
 * @param $arr
 * @param $type
 * @return never
 */
function dier($arr, $type = 1)
{
    if (empty($_SESSION['ADMIN_TOKEN'])) {
        $arr = Guard::Filtrate($arr);
    }
    session_write_close();

    if (isset($arr['code'])) {
        $arr['status'] = ($arr['status'] ?? $arr['code']);
        $arr['state'] = ($arr['state'] ?? $arr['code']);
    }

    if (isset($arr['msg'])) {
        if (empty($arr['msg'])) {
            $arr['msg'] = '没有返回任何信息,请检查接口是否出现异常！';
        }
        $arr['info'] = ($arr['info'] ?? $arr['msg']);
        $arr['message'] = ($arr['message'] ?? $arr['msg']);
    }

    if ($type === 1) {
        die(json_encode($arr));
    }
    die(json_encode($arr, JSON_UNESCAPED_UNICODE));
}

/**
 * @param $str_string
 * @return mixed
 * 安全过滤插件
 */
function SecurityFiltering($str_string)
{
    $_arr_dangerRegs = [
        "/on(afterprint|beforeprint|beforeunload|error|haschange|load|message|offline|online|pagehide|pageshow|popstate|redo|resize|storage|undo|unload|blur|change|contextmenu|focus|formchange|forminput|input|invalid|reset|select|submit|keydown|keypress|keyup|click|dblclick|drag|dragend|dragenter|dragleave|dragover|dragstart|drop|mousedown|mousemove|mouseout|mouseover|mouseup|mousewheel|scroll|abort|canplay|canplaythrough|durationchange|emptied|ended|error|loadeddata|loadedmetadata|loadstart|pause|play|playing|progress|ratechange|readystatechange|seeked|seeking|stalled|suspend|timeupdate|volumechange|waiting)\s*=\s*(\"|')?\S*(\"|')?/i",
        "/\w+\s*=\s*(\"|')?(java|vb)script:\S*(\"|')?/i",
        '/(document|location)\s*\.\s*\S*/i',
        '/(eval|alert|prompt|msgbox)\s*\(.*\)/i',
        '/expression\s*:\s*\S*/i',
        '/show\s+(databases|tables|index|columns)/i',
        '/create\s+(database|table|(unique\s+)?index|view|procedure|proc)/i',
        '/alter\s+(database|table)/i',
        '/drop\s+(database|table|index|view|column)/i',
        '/backup\s+(database|log)/i',
        '/truncate\s+table/i',
        '/replace\s+view/i',
        '/(add|change)\s+column/i',
        '/(select|insert|update|delete)\s+\S*\s+from/i',
        '/insert\s+into/i',
        '/load_file\s*\(.*\)/i',
        "/(outfile|infile)\s+(\"|')?\S*(\"|')/i",
        "/<(\\/?)(script|i?frame|bgsound|applet|embed|blink|i?layer|style|base|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU",
        '/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU',
        "/(select|insert|delete\'|\/\*|\.\.\/|\.\/|union|into|load_file|outfile|dump)/is"
    ];

    $_str_return = $str_string;

    foreach ($_arr_dangerRegs as $_value) {
        $_str_return = preg_replace($_value, '', $_str_return);
    }

    return $_str_return;
}

/**
 * @param $string
 * @return array|mixed|string
 * 数据安全过滤
 */
function daddslashes($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $val = SecurityFiltering($val);
            $string[$key] = daddslashes($val);
            $string[$key] = saddslashes($string[$key]);
            if (($key == 'file' || strstr($key, 'imageData') || strstr($key, 'VideoData')) && !empty($val['tmp_name'])) {
                $string[$key]['tmp_name'] = SecurityFiltering($val['tmp_name']);
            }
        }
    } else {
        $string = htmlspecialchars($string);
        $string = saddslashes($string);
    }
    return $string;
}

/**
 * @param string $source //源文件,填写绝对路径
 * @param string $dest //目标路径,填写绝对路径
 * @param bool $force //开启会每次强制覆盖原文件,false不进行覆盖,存在文件不做处理
 * 实现文件目录移动，拷贝等,返回被拷贝的文件数,
 */
function DirCopy($source, $dest, $force = true)
{
    static $counter = 0;
    $paths = array_filter(scandir($source), function ($file) {
        return !in_array($file, ['.', '..']);
    });
    foreach ($paths as $path) {
        $sourceFullPath = $source . DIRECTORY_SEPARATOR . $path;
        $destFullPath = $dest . DIRECTORY_SEPARATOR . $path;
        if (is_dir($sourceFullPath)) {
            if (!is_dir($destFullPath)) {
                mkdirs($destFullPath);
                chmod($destFullPath, 0755);
            }
            DirCopy($sourceFullPath, $destFullPath, $force);
            continue;
        }
        if (!$force && file_exists($destFullPath)) {
            continue;
        }
        if (copy($sourceFullPath, $destFullPath)) {
            $counter++;
        }
    }
    return $counter;
}

/**
 * @param $dir
 *
 * @return array
 * 获取文件夹下的文件夹列表，1层
 * 获取目录列表
 * 获取文件列表
 */
function for_dir($dir, $exclude = [])
{
    $files = array();
    if (@$handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if ($file != '..' && $file != '.' && !in_array($file, $exclude)) {
                $files[] = $file;
            }
        }
    }
    closedir($handle);
    return $files;
}

/**
 * @param $url 接口地址
 * @param array $data 表单数据
 * @param array $files 表单文件 $_FILES
 * 文件上传中转【暂时废弃】
 */
function FileUploadTransfer($url, array $data = array(), array $files = array())
{
    $curlfiles = array();
    foreach ($files as $key => $value) {
        $tmpname = $files[$key]['name'];
        $tmpfile = $files[$key]['tmp_name'];
        $tmpType = $files[$key]['type'];
        $curlfiles[$key] = new CURLFile(realpath($tmpfile), $tmpType, $tmpname);
    }
    $dataTotal = array_merge($data, $curlfiles);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, 'joe:secret');
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataTotal);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $return_data = curl_exec($ch);
    curl_close($ch);
    return json_decode($return_data, TRUE);
}

/**
 * @param $min
 * @param $max
 * 取出随机小数
 */
function randomFloat($min = 0, $max = 1)
{
    $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
    return sprintf("%.2f", $num);  //控制小数后几位
}

/**
 * @param $url
 * @return mixed
 * 请求转发【暂时废弃】
 */
function get_url_content($url, $post = 0)
{
    $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);//设置要访问的IP
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);//模拟用户使用的浏览器
    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置超时时间
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($ch, CURLOPT_HEADER, 0); //显示返回的HEAD区域的内容
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * @param $url
 * @param $post
 * @param $referer
 * @param $cookie
 * @param $header
 * @param $ua
 * @param $nobaody
 * @return bool|string
 * 发起curl请求
 */
function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    if ($header) {
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
    }
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    if ($referer) {
        if ($referer == 1) {
            curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
        } else {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
    }
    if ($ua) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
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

/**
 * @param $title
 * @param $content
 * @param $type
 * @param $url
 * @param $true
 * @return never
 * type=1 成功，2警告，3正常，4失败
 * 信息提示模块
 */
function show_msg($title = '温馨提示', $content = '这是内容', $type = 3, $url = false, $true = true, $w = 42, $h = 42)
{
    $Data = file_get_contents(ROOT . 'includes/lib/AppStore/hint.TP');
    switch ((int)$type) {
        case 2: //警告Warning
            $Type = 'warning';
            $Image = '<svg t="1641434429779" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="24548" width="' . $w . '" height="' . $h . '"><path d="M512.454257 0a511.999147 511.999147 0 1 0 472.745879 316.07414A511.487148 511.487148 0 0 0 512.397368 0z m0 950.896193A439.352157 439.352157 0 1 1 951.351303 511.999147a439.807267 439.807267 0 0 1-438.897046 438.897046z" fill="#FAB34A" p-id="24549"></path><path d="M555.348408 314.424365a54.954575 54.954575 0 0 0-29.639062-22.584851h-13.311978a49.151918 49.151918 0 0 0-35.83994 14.222198 44.316371 44.316371 0 0 0-14.62042 34.531498l14.16531 215.267197a46.933255 46.933255 0 0 0 7.50932 26.567067 31.004393 31.004393 0 0 0 18.204415 13.255089c6.826655 1.365331 13.880866 1.365331 20.764409 0a31.004393 31.004393 0 0 0 18.204414-13.255089 46.478145 46.478145 0 0 0 7.054211-26.567067l14.62042-215.267197a41.187487 41.187487 0 0 0 0-12.856867 48.241697 48.241697 0 0 0-7.111099-13.311978zM511.999147 647.508254a44.259482 44.259482 0 0 0-30.094172 12.401757 41.52882 41.52882 0 0 0-12.401758 30.549283 40.334155 40.334155 0 0 0 12.401758 30.151061 44.316371 44.316371 0 0 0 60.700343 0 40.391044 40.391044 0 0 0 12.401757-30.151061 41.642597 41.642597 0 0 0-12.401757-30.549283 44.316371 44.316371 0 0 0-30.606171-12.401757z" fill="#FAB34A" p-id="24550"></path></svg>';
            break;
        case 3: //正常Info
            $Type = 'info';
            $Image = '<svg t="1641434989840" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="37107" width="' . $w . '" height="' . $h . '"><path d="M512 1024a512 512 0 1 1 512-512 512 512 0 0 1-512 512z m0-960a448 448 0 1 0 448 448A448 448 0 0 0 512 64z" fill="#0070E1" p-id="37108"></path><path d="M476.16 454.72h71.68v311.36h-71.68z" fill="#0070E1" p-id="37109"></path><path d="M512 339.84m-54.72 0a54.72 54.72 0 1 0 109.44 0 54.72 54.72 0 1 0-109.44 0Z" fill="#0070E1" p-id="37110"></path></svg>';
            break;
        case 4: //失败Error
            $Type = 'error';
            $Image = '<svg t="1641433806987" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="14726" width="' . $w . '" height="' . $h . '"><path d="M512 0c144.896 3.547429 265.545143 53.540571 361.984 150.016C970.459429 246.454857 1020.452571 367.104 1024 512c-3.547429 144.896-53.540571 265.545143-150.016 361.984C777.545143 970.459429 656.896 1020.452571 512 1024c-144.896-3.547429-265.545143-53.540571-361.984-150.016C53.540571 777.545143 3.547429 656.896 0 512c3.547429-144.896 53.540571-265.545143 150.016-361.984C246.454857 53.540571 367.104 3.547429 512 0z" fill="#FF5C59" p-id="14727"></path><path d="M675.218286 312.795429l36.205714 36.205714a17.078857 17.078857 0 0 1 0 24.137143l-138.788571 138.788571 138.788571 138.788572a17.078857 17.078857 0 0 1 0 24.137142l-36.205714 36.205715a17.078857 17.078857 0 0 1-24.137143 0l-138.788572-138.788572-138.788571 138.788572a17.078857 17.078857 0 0 1-24.137143 0l-36.205714-36.205715a17.078857 17.078857 0 0 1 0-24.137142l138.788571-138.788572-138.788571-138.788571a17.078857 17.078857 0 0 1 0-24.137143l36.205714-36.205714a17.078857 17.078857 0 0 1 24.137143 0l138.788571 138.788571 138.788572-138.788571a17.078857 17.078857 0 0 1 24.137143 0z" fill="#FFFFFF" p-id="14728"></path></svg>';
            break;
        default: //成功Success
            $Type = 'success';
            $Image = '<svg t="1641434359072" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="20554" width="' . $w . '" height="' . $h . '"><path d="M512 1024C229.2 1024 0 794.8 0 512S229.2 0 512 0s512 229.2 512 512-229.2 512-512 512z m0-60.2c249.5 0 451.8-202.3 451.8-451.8S761.5 60.2 512 60.2 60.2 262.5 60.2 512 262.5 963.8 512 963.8z" fill="#13BD93" p-id="20555"></path><path d="M473.8 632.8L324.7 483.7a30.057 30.057 0 0 0-42.6 0 30.057 30.057 0 0 0 0 42.6l170.4 170.4c5.9 5.9 13.6 8.8 21.3 8.8 7.7 0 15.4-2.9 21.3-8.8L772 419.8c11.8-11.8 11.8-30.8 0-42.6s-30.8-11.8-42.6 0L473.8 632.8z" fill="#13BD93" p-id="20556"></path></svg>';
            break;
    }
    if ($true) {
        $true = 'inline-block';
        if (!$url) {
            $true = 'none';
            $url = '-1';
        }
    } else {
        $true = 'none';
        $url = '-1';
    }
    $bgclass = background::image() == false ? 'background:#edf1f4;' : background::image();
    $Data = str_replace('[Second]', 10, $Data);
    $Data = str_replace('[Background]', $bgclass, $Data);
    $Data = str_replace('[UrlBtn]', $true, $Data);
    $Data = str_replace('[Url]', $url, $Data);
    $Data = str_replace('[Image]', $Image, $Data);
    $Data = str_replace('[Type]', $Type, $Data);
    $Data = str_replace('[Title]', $title, $Data);
    header('Content-Type: text/html; charset=UTF-8');
    die(str_replace('[Content]', $content, $Data));
}

/**
 * @param $name //日志名称
 * @param $content //日志内容
 * @param $uid //用户ID
 * @param $count //相关数量
 * @return bool
 * 用户日志写入
 */
function userlog($name, $content, $uid, $count = 0)
{
    global $date;
    $DBS = SQL::DB();
    if (!$uid) {
        $UserData = login_data::user_data();
        $uid = $UserData['id'];
    }
    if (!empty($_SESSION['ADMIN_TOKEN'])) {
        $IP = '127.0.0.1';
    } else {
        $IP = userip();
    }
    $Res = $DBS->insert('journal', [
        'ip' => $IP,
        'uid' => $uid,
        'count' => $count,
        'name' => $name,
        'content' => $content,
        'date' => $date
    ]);
    if ($Res) {
        return true;
    }

    return false;
}

/**
 * @param $type //1 仅返回域名，其他返回http(s)://
 * @return mixed|string
 * 获取当前站点域名信息
 */
function href($type = 1)
{
    if ($type === 1) {
        return $_SERVER['HTTP_HOST'];
    }
    return is_https(false) . $_SERVER['HTTP_HOST'];
}

/**
 * config 操作类
 */
class config
{
    /**
     * @return array|mixed
     * 从缓存区数据
     */
    public static function data_cache()
    {
        $DBS = SQL::DB();
        $br = $DBS->get('cache', ['V'], ['K' => 'config']);
        if (!$br || !empty($br['V'])) {
            self::serialize_array();
        }
        $data = [];
        foreach (self::common_unserialize($br['V']) as $v) {
            $data += $v;
        }
        $Arr = [];
        if (self::func_is_base64($data['currency']) === false) {
            $Type = 1;
        } else {
            $Type = 2;
        }
        foreach ($data as $key => $value) {
            $Arr += [$key => ($Type === 1 ? $value : base64_decode($value))];
        }
        $Arr = array_merge($Arr, self::Notice());
        return extend\UserConf::Conf($Arr);
    }

    /**
     * @return bool
     * 序列化数据
     */
    public static function serialize_array()
    {
        $DBS = SQL::DB();
        $br = $DBS->get('cache', ['V'], ['K' => 'config']);
        if (!$br) {
            $DBS->insert('cache', [
                'K' => 'config'
            ]);
        }
        if ($br['V'] == '') {
            $Data = $DBS->select('config', '*');
            $Array = [];
            foreach ($Data as $v) {
                $Array[] = [
                    $v['K'] => base64_encode($v['V'])
                ];
            }
            $ar = serialize($Array);
            $DBS->update('cache', [
                'V' => $ar
            ], [
                'K' => 'config'
            ]);
        }

        return true;
    }

    /**
     * @param $serial_str
     * @return mixed
     * 序列化修复
     */
    public static function common_unserialize($serial_str)
    {
        $serial_str = preg_replace_callback('/s:(\d+):"([\s\S]*?)";/', function ($matches) {
            return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
        }, $serial_str);
        return unserialize($serial_str);
    }

    public static function func_is_base64($str)
    {
        return $str == base64_encode(base64_decode($str)) ? true : false;
    }

    /**
     * 取出公告内容.
     * 分站不需要改动，仅优化读取即可
     */
    public static function Notice()
    {
        mkdirs(ROOT . 'includes/extend/log/Notice/');
        return [
            'notice_top' => self::NoticeRead('notice_top'),
            'notice_check' => self::NoticeRead('notice_check'),
            'notice_bottom' => self::NoticeRead('notice_bottom'),
            'notice_user' => self::NoticeRead('notice_user'),
            'PopupNotice' => self::NoticeRead('PopupNotice'),
            'statistics' => self::NoticeRead('statistics'),
            'ServiceTips' => self::NoticeRead('ServiceTips'),
            'HostAnnounced' => self::NoticeRead('HostAnnounced'),
        ];
    }

    /**
     * @param $Nc
     * @return false|string
     * 取出指定公告数据
     */
    public static function NoticeRead($Nc)
    {
        if (!file_exists(ROOT . 'includes/extend/log/Notice/' . $Nc . '.nc')) return '';
        return file_get_contents(ROOT . 'includes/extend/log/Notice/' . $Nc . '.nc');
    }

    /**
     * @return bool
     * 删除缓存
     */
    public function unset_cache()
    {
        global $DB;
        $DB = SQL::DB();
        $Res = $DB->update('cache', [
            'V' => ''
        ], [
            'K' => 'config'
        ]);
        if ($Res) {
            return true;
        }

        return false;
    }
}

/**
 * 域名防红
 */
class reward
{
    /**
     * @param $url
     * @param $type
     * @return false|mixed|string
     * 参数替换，并解析出指定接口内容
     */
    public static function prevent($url, $type = 2)
    {
        global $conf;
        $url2 = $url;
        $url = urlencode($url);
        if ($type == 2 && isset($_COOKIE[$conf['prevent_return'] . '_' . md5($url)])) {
            return base64_decode($_COOKIE[$conf['prevent_return'] . '_' . md5($url)]);
        }
        if ($conf['prevent_switch'] == 1) {
            if ($conf['prevent_url'] == '') return $url2;
            $UrlField = html_entity_decode($conf['prevent_field']);
            $UrlField = str_replace('[url]', $url, $UrlField);
            $UrlField = str_replace('[ip]', userip(), $UrlField);
            $UrlField = str_replace('[time]', time(), $UrlField);
            $UrlField = str_replace('[sitename]', $conf['sitename'], $UrlField);
            $UrlField = str_replace('[http]', is_https(false), $UrlField);
            $data = get_curl($conf['prevent_url'] . $UrlField);
            $data = json_decode($data, TRUE);
            if (isset($data[$conf['prevent_return']])) {
                setcookie($conf['prevent_return'] . '_' . md5($url), base64_encode($data[$conf['prevent_return']]), time() + 3600 * 12, '/');
                return $data[$conf['prevent_return']];
            } else  return $url2;
        } else return $url2;
    }

    /**
     * @param $money
     * @param $type
     * @param $UserData
     * @return void
     * 用户在线充值
     */
    public static function user_payet($money, $type, $UserData)
    {
        $Res = Pay::PrepaidPhoneOrders([
            'type' => $type,
            'uid' => $UserData['id'],
            'gid' => -1,
            'input' => -1,
            'num' => 1
        ], [
            'name' => '用户:[' . $UserData['id'] . '] 在线充值',
            'price' => $money
        ]);
        dier($Res);
    }

    /**
     * @return bool
     * 用户登陆状态判断
     */
    public static function user_landing()
    {
        $DB = SQL::DB();
        if (!empty($_COOKIE['THEKEY'])) {
            $idu = SecurityFiltering($_COOKIE['THEKEY']);
            if (strlen($_COOKIE['THEKEY']) === 32) {
                $user_row = $DB->get('user', ['id'], ['user_idu' => (string)$idu, 'state' => 1, 'LIMIT' => 1]);
            } else {
                $user_row = $DB->get('user', ['id'], ['wx_idu' => (string)$idu, 'state' => 1, 'LIMIT' => 1]);
            }
            if (!$user_row) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * @param $s
     * @param $i
     * @return void
     * 生成邀请奖励缓存
     */
    public static function Invited_status($s, $i)
    {
        $DB = SQL::DB();
        $yoid = $DB->get('user', '*', ['id' => (int)$i, 'LIMIT' => 1]);
        if ($yoid && md5($yoid['user_idu']) === $s) {
            setcookie('INVITED_STATUS', $yoid['id'], time() + 3600 * 12 * 15, '/');
            $URLS = href(2) . $_SERVER['SCRIPT_NAME'];
            header("Location:$URLS");
        }
    }

    /**
     * @param $UserData
     * @return int
     * 签到状态
     */
    public static function welfare_judge($UserData)
    {
        $DB = SQL::DB();
        $thtime = date('Y-m-d') . ' 00:00:00';
        $Vs = $DB->get('journal', ['id'], [
            'name' => '每日签到',
            'uid' => (int)$UserData['id'],
            'date[>=]' => $thtime
        ]);
        if (!$Vs) {
            return true;
        }
        return false;
    }

    /**
     * @param $UserData //用户信息
     * @return string
     * 获取邀请链接
     */
    public static function shareLink($UserData)
    {
        return href(2) . ROOT_DIR_S . '/?s=' . md5($UserData['user_idu']) . '&id=' . $UserData['id'];
    }

    /**
     * @param $UserData
     * @return array
     * 用户收益计算
     */
    public static function user_pays($UserData)
    {
        global $times, $timea;
        $DB = SQL::DB();

        $Count1 = $DB->sum('journal', 'count', [
            'uid' => $UserData['id'],
            'name' => ['后台扣款', '等级提升', '域名绑定', '余额购买', '在线购买']
        ]);

        $Count2 = $DB->sum('journal', 'count', [
            'uid' => $UserData['id'],
            'name' => ['每日签到', '邀请奖励', '货币提成', '积分充值'],
            'date[>=]' => $times
        ]);

        $Count3 = $DB->sum('journal', 'count', [
            'uid' => $UserData['id'],
            'name' => ['每日签到', '邀请奖励', '货币提成', '积分充值'],
            'date[<]' => $times,
            'date[>=]' => $timea
        ]);

        return [
            'Count1' => round($Count1, 2), //累计消费
            'Count2' => round($Count2, 0), //今日获得积分
            'Count3' => round($Count3, 0), //昨日获得积分
        ];
    }

    /**
     * @param $ID
     * @return array
     * 累计消费计算
     */
    public static function statistics($ID)
    {
        $DB = SQL::DB();
        return [
            'count_1' => $DB->count('order', ['uid' => $ID]), //订单总数
            'count_2' => $DB->sum('journal', 'count', ['name' => ['在线购买', '余额购买'], 'uid' => $ID]), //累计消费
            'count_3' => $DB->count('journal', ['name' => '邀请奖励', 'uid' => $ID]), //累计邀请
            'count_4' => $DB->sum('journal', 'count', ['name' => '邀请奖励', 'uid' => $ID]), //累计奖励
        ];
    }

    /**
     * @param $UserData
     * @param $id
     * @param int $type
     * 奖励领取！
     */
    public static function issue_reward($UserData, $id, $type = 1)
    {
        global $DB, $date, $conf;
        $DB = SQL::DB();
        $rs = $DB->get('invite', '*', [
            'uid' => (int)$UserData['id'],
            'id' => (int)$id
        ]);
        if ($rs) {
            $rs2 = $DB->get('user', '*', [
                'id' => (int)$rs['invitee']
            ]);
            if ($rs['award'] == 0) {
                if ($type !== 1) {
                    dies(-1, '您已经领取过一次奖励了,无法再次领取哦!,可以去多邀请人，奖励就会越来越多的');
                }
                show_msg('领取失败提醒', '受邀用户：' . $rs2['name'] . ' <img src="' . $rs2['image'] . '" width="20" style="border-radius: 3em"><br>邀请时间：' . $rs['creation_time'] . '<br>领取时间：' . $rs['draw_time'] . '<br>奖励状态：您已经领取过一次奖励了,无法再次领取哦!,可以去多邀请人，奖励就会越来越多的！', 4);
            } else {
                if ($conf['InviteValve'] != 0) {
                    /**
                     * 验证消费是否达标
                     */
                    $Sum = $DB->sum('order', 'money', [
                        'uid' => $rs['invitee'],
                        'payment[!]' => ['免费领取', '积分兑换']
                    ]);
                    if ($Sum < (float)$conf['InviteValve']) {
                        if ($type !== 1) {
                            dies(-1, '您的邀请对象：' . $rs2['name'] . '未满足，累计消费' . $conf['InviteValve'] . '元的条件，您无法领取邀请奖励！');
                        }
                        show_msg('领取失败提醒', '您的邀请对象：' . $rs2['name'] . '未满足，累计消费' . $conf['InviteValve'] . '元的条件，您无法领取邀请奖励！', 4);
                    }
                }
                userlog('邀请奖励', '您在' . $date . '成功领取了' . $rs['award'] . '邀请奖励点！再接再厉哦！', $UserData['id'], $rs['award']);
                $ac = $DB->update('user', [
                    'currency[+]' => $rs['award'],
                ], [
                    'id' => $UserData['id']
                ]);

                $ad = $DB->update('invite', [
                    'award' => 0,
                    'draw_time' => $date,
                ], [
                    'uid' => $UserData['id'],
                    'id' => $id
                ]);

                if ($ac && $ad) {
                    if ($type !== 1) {
                        dies(1, '恭喜您，您成功领取了' . $rs['award'] . '个奖励点');
                    }
                    show_msg('奖励发放成功提醒', '受邀用户：' . $rs2['name'] . ' <img src="' . $rs2['image'] . '" width="20" style="border-radius: 3em"><br>邀请时间：' . $rs['creation_time'] . '<br>领取时间：' . $date . '<br>奖励状态：恭喜您，您成功领取了' . $rs['award'] . '个奖励点,快去兑换商品吧,再接再厉哦！', 1);
                } else {
                    if ($type !== 1) {
                        dies(-1, '领取失败了，请联系客服处理！');
                    }
                    show_msg('领取失败提醒', '抱歉，领取失败了，请联系管理员处理！', 4);
                }
            }
        } else {
            if ($type !== 1) {
                dies(-1, '奖励日志不存在，领取失败！');
            }
            show_msg('奖励订单不存在', '奖励订单不存在', 4);
        }
    }

    /**
     * @param $UserData
     * @param int $Page
     * @param int $Limit
     * @return array
     * 查询邀请列表
     */
    public static function Invite_statistics($UserData, $Page = -1, $Limit = 12)
    {
        $DB = SQL::DB();
        $SQL = [
            'invite.uid' => $UserData['id'],
            'ORDER' => [
                'invite.id' => 'DESC'
            ],
        ];
        if ($Page === -1) {
            $SQL['LIMIT'] = 28;
        } else {
            $Limit = (int)$Limit;
            $Page = ($Page - 1) * $Limit;
            $SQL['LIMIT'] = [$Page, $Limit];
        }
        $Res = $DB->select('invite', [
            '[>]user' => ['invitee' => 'id']
        ], [
            'user.image',
            'user.name',
            'invite.id',
            'invite.award',
            'invite.draw_time',
            'invite.creation_time'
        ], $SQL);
        if (!$Res) {
            $Res = [];
        }
        return $Res;
    }

    /**
     * @param $UserData
     * @param $type
     * @return void
     * 签到
     */
    public static function welfare($UserData, $type = 1)
    {
        global $conf;
        $DB = SQL::DB();
        $thtime = date('Y-m-d') . ' 00:00:00';
        $Vs = $DB->get('journal', ['id', 'date'], [
            'name' => '每日签到',
            'uid' => (int)$UserData['id'],
            'date[>=]' => $thtime
        ]);
        if ($Vs) {
            if ($type === 2) {
                dies(-1, '签到失败，您今天已经签到过了，签到时间：' . $Vs['date']);
            }
            show_msg('签到失败提醒', '您已经签到过了,请明天再来哦~', '4');
        } else {

            $State = ($conf['SignAway'] == 1 ? 'currency' : 'money');
            $Ex = explode('-', $conf['GiftContent'] ?? ($State == 'currency' ? '1-100' : '0.01-0.1'));
            if ($State == 'currency') {
                if ($Ex[0] < 1) {
                    $Ex[0] = 1;
                }

                if ($Ex[1] < 1) {
                    $Ex[1] = 1;
                }

                $GiftContent = rand($Ex[0], $Ex[1]);
            } else {
                if ($Ex[0] <= 0) {
                    $Ex[0] = 0;
                }
                if ($Ex[1] <= 0.01) {
                    $Ex[1] = 0.01;
                }
                $GiftContent = randomFloat($Ex[0], $Ex[1]);
            }
            $Res = $DB->update('user', [
                $State . '[+]' => $GiftContent,
            ], [
                'id' => $UserData['id']
            ]);
            if ($Res) {
                userlog('每日签到', '恭喜您签到成功,赠送您' . $GiftContent . ($State == 'currency' ? $conf['currency'] : '余额'), $UserData['id'], $GiftContent);
                Hook::execute('UserSignIn', [
                    'id' => $UserData['id'],
                    'num' => $GiftContent,
                    'type' => $State
                ]);
                if ($type === 2) {
                    dies(1, '恭喜您签到成功,赠送您' . $GiftContent . ($State == 'currency' ? $conf['currency'] : '余额') . ',记得明天再来签到哦~');
                }
                show_msg('签到成功提醒', '恭喜您签到成功,赠送您' . $GiftContent . ($State == 'currency' ? $conf['currency'] : '余额') . ',记得明天再来签到哦~', '1');
            } else {
                if ($type === 2) {
                    dies(1, '签到失败，请重新尝试！');
                }
                show_msg('签到失败提醒', '因程序原因，签到失败了', '4');
            }
        }
    }
}

/**
 * Class login_data
 * 用户登陆模块
 */
class login_data
{
    public static $UserDataNew = false;

    /**
     * @param $uid
     * 根据用户名ID取出等级！
     */
    public static function user_grade($uid)
    {
        if (!empty(CookieCache::$Cache['user_grade'][md5($uid)])) {
            return CookieCache::$Cache['user_grade'][md5($uid)];
        }
        global $conf;
        if ((int)$conf['ShutDownUserSystem'] === -1) {
            CookieCache::$Cache['user_grade'][md5($uid)] = -1;
            return -1;
        }
        if ($uid === -1) {
            CookieCache::$Cache['user_grade'][md5($uid)] = -1;
            return -1; //游客
        }
        $DB = SQL::DB();
        $G = $DB->get('user', [
            'grade'
        ], [
            'id' => (int)$uid
        ]);
        CookieCache::$Cache['user_grade'][md5($uid)] = $G['grade'];
        return $G['grade'];
    }

    /**
     * @param $Uid
     * 获取用户实时+积分+等级
     */
    public static function UserMoney($Uid, $type = 1)
    {
        $DB = SQL::DB();
        $User = $DB->get('user', ['money', 'grade', 'currency'], [
            'id' => (int)$Uid,
            'state' => ($type === 1 ? 1 : [1, 2])
        ]);
        if ($User) {
            return $User;
        }
        return false;
    }

    /**
     * @return array|bool|mixed|string[]|null
     * 分站用户登陆验证
     */
    public static function login_verify()
    {
        $UserData = self::user_data();
        if (!$UserData) {
            setcookie('THEKEY', null, time() - 1, '/');
            header('Location:' . href(2) . ROOT_DIR_S . '/user/login.php');
        }
        return $UserData;
    }

    /**
     * @return bool|mixed
     * 获取用户数据，一次请求只载入一次！
     */
    public static function user_data()
    {
        if (self::$UserDataNew !== false) {
            return self::$UserDataNew;
        }
        global $conf;
        if ((int)$conf['ShutDownUserSystem'] === -1) {
            return false;
        }
        $DB = SQL::DB();
        if (reward::user_landing() === true) {
            $idu = SecurityFiltering(@$_COOKIE['THEKEY']);
            if (strlen(@$_COOKIE['THEKEY']) === 32) {
                $user_row = $DB->get('user', '*', ['user_idu' => (string)$idu, 'LIMIT' => 1]);
            } else {
                $user_row = $DB->get('user', '*', ['wx_idu' => (string)$idu, 'LIMIT' => 1]);
            }
            self::$UserDataNew = $user_row;
            return $user_row;
        }
        return false;
    }
}

/**
 * @param string $ip
 * @return bool|mixed|string
 * 获取用户IP详细地址！【暂未用到，功能正常】
 */
function getCity($ip = '')
{
    $url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=';
    $city = get_curl($url . $ip);
    $city = mb_convert_encoding($city, 'UTF-8', 'GB2312');
    $city = json_decode($city, true);
    if ($city['city']) {
        $location = $city['pro'] . $city['city'];
    } else {
        $location = $city['pro'];
    }
    if ($location) {
        return $location;
    } else {
        return false;
    }
}

/**
 * @return bool|string
 * 获取服务器IP地址【暂未用到，功能正常】
 */
function getServerIp()
{
    $url = 'http://members.3322.org/dyndns/getip';
    $url2 = 'https://www.bt.cn/Api/getIpAddress';
    if ($data = get_curl($url2)) {
        return $data;
    } else {
        $data = get_curl($url);
        if ($data == '') {
            return gethostbyname(href());
        }
        return $data;
    }
}

/**
 * @param int $type
 * @return mixed 获取当前来访域名ip 和用户ip
 */
function userip($type = 0)
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if ($type <= 0 && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] as $xip) {
            if (filter_var($xip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $ip = $xip;
                break;
            }
        }
    } elseif ($type <= 0 && isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ($type <= 1 && isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif ($type <= 1 && isset($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    return $ip;
}

/**
 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
 * @param  [string] $user_name 字符串
 * @param  [int] $head      左侧保留位数
 * @param  [int] $foot      右侧保留位数
 * @return string 格式化后的姓名
 */
function substr_cut($user_name, $head, $foot)
{
    $strlen = mb_strlen($user_name, 'utf-8');
    $firstStr = mb_substr($user_name, 0, $head, 'utf-8');
    $lastStr = mb_substr($user_name, -$foot, $foot, 'utf-8');
    return $strlen === 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat('*', $strlen - ($head + $foot)) . $lastStr;
}

/**
 * @param $act 字段名称
 * @param $cs  判断参数
 * @param $Data 数据
 * 数据存储,只可存储json数据
 */
function HomecachingAdd($act = '', $cs = [], $Data = [])
{
    $Flie = ROOT . 'includes/extend/log/Home/Cache_' . md5($act . json_encode($cs)) . '.cache';
    @file_put_contents($Flie, json_encode($Data, JSON_UNESCAPED_UNICODE));
    return true;
}

/**
 * @param $act //字段名称
 * @param $cs  //判断参数
 * 数据验证
 */
function HomecachingVerify($act, $cs = [])
{
    global $conf;
    $Flie = ROOT . 'includes/extend/log/Home/Cache_' . md5($act . json_encode($cs)) . '.cache';
    if (file_exists($Flie)) {
        $tiem = (float)filemtime($Flie);
        if ((time() - $tiem) < $conf['Homecaching']) {
            return json_decode(file_get_contents($Flie), TRUE);
        }
        return false;
    }

    return false;
}

/**
 * @param $act
 * @param $cs
 * @return bool
 * 删除数据缓存
 */
function HomecachingUnlink($act, $cs = [])
{
    $Flie = ROOT . 'includes/extend/log/Home/Cache_' . md5($act . json_encode($cs)) . '.cache';
    if (unlink($Flie)) {
        return true;
    }
    return false;
}

/**
 * @param string $str // 本地图片地址
 * URL本地连接转换为外链
 * /assets/img/ path
 * @param bool $Url
 * @return string
 */
function ImageUrl($str = '', $Url = false)
{
    if (is_array($str)) {
        print_r($str);
        die;
    }
    $Name = md5($str . $Url);
    if (!empty(CookieCache::$Cache['ImageUrl'][$Name])) {
        return CookieCache::$Cache['ImageUrl'][$Name];
    }

    if ($Url === false) {
        $Url = href(2);
    }

    if (empty($str)) {
        CookieCache::$Cache['ImageUrl'][$Name] = $Url . ROOT_DIR . 'assets/img/404.png';
        return $Url . ROOT_DIR . 'assets/img/404.png';
    }
    $arr = explode('/', $str);
    if (ROOT_DIR !== '/') {
        if ($arr[0] === '' && $arr[1] === 'assets' && $arr[2] === 'img') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . ROOT_DIR_S . $str;
            return $Url . ROOT_DIR_S . $str;
        }

        if ($arr[0] === 'assets' && $arr[1] === 'img') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . ROOT_DIR . $str;
            return $Url . ROOT_DIR . $str;
        }

        if ($arr[0] === '' && $arr[1] === 'template') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . ROOT_DIR_S . $str;
            return $Url . ROOT_DIR_S . $str;
        }

        if ($arr[1] === 'includes' && $arr[2] === 'extend' && $arr[3] === 'log') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . ROOT_DIR_S . $str;
            return $Url . ROOT_DIR_S . $str;
        }

        if ($arr[0] === '' && $arr[1] === 'assets') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . ROOT_DIR . $str;
            return $Url . ROOT_DIR . $str;
        }
    } else {
        if ($arr[0] === '' && $arr[1] === 'assets' && $arr[2] === 'img') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . $str;
            return $Url . $str;
        }

        if ($arr[0] === 'assets' && $arr[1] === 'img') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . '/' . $str;
            return $Url . '/' . $str;
        }

        if ($arr[0] === '' && $arr[1] === 'template') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . $str;
            return $Url . $str;
        }

        if ($arr[1] === 'includes' && $arr[2] === 'extend' && $arr[3] === 'log') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . $str;
            return $Url . $str;
        }

        if ($arr[0] === '' && $arr[1] === 'assets') {
            CookieCache::$Cache['ImageUrl'][$Name] = $Url . '/' . $str;
            return $Url . '/' . $str;
        }
    }
    CookieCache::$Cache['ImageUrl'][$Name] = $str;
    return $str;
}

/**
 * @param $value
 * @param $array
 * @return bool
 * 检测多维数组内是否包含某个键值
 */
function deep_in_array($value, $array, $key = 'id')
{
    $Data = array_column($array, $key);
    if (in_array($value, $Data)) {
        unset($Data);
        return true;
    }
    unset($Data);
    return false;
}

/**
 * @param array $arr
 * @param string $msg
 * @param bool $Data
 * 参数验证
 */
function test(array $arr, string $msg = '', $Data = false)
{
    if ($Data == false) {
        global $_QET;
    } else {
        $_QET = $Data;
    }
    foreach ($arr as $value) {
        $r = explode('|', $value);
        if (!isset($r[1])) {
            $r[1] = 'i';
        }
        if (!isset($_QET[$r[0]]) && $r[1] == 'i') {
            if (!empty($r[2])) {
                dies(-1, $r[2]);
            } else {
                if ($msg == '') dies(-1, '请将参数：' . $r[0] . ' 提交完整！');
                dies(-1, $msg);
            }
        }
        if (empty($_QET[$r[0]]) && $r[1] == 'e') {
            if (!empty($r[2])) {
                dies(-1, $r[2]);
            } else {
                if ($msg == '') dies(-1, '请将参数：' . $r[0] . ' 提交完整！');
                dies(-1, $msg);
            }
        }
    }
}


/**
 * @param $dir
 * @param int $mode
 * @return bool
 * 检测目录是否存在，不存在则创建
 */
function mkdirs($dir, int $mode = 0777)
{
    if (is_dir($dir) || mkdir($dir, $mode) || is_dir($dir)) {
        return true;
    }
    if (!mkdirs(dirname($dir), $mode)) {
        return false;
    }
    return @mkdir($dir, $mode);
}

/**
 * 是否为https
 * @param bool $type //默认验证
 * @param int $ts //1 返回:// 其他不返回://
 */
function is_https($type = true, $ts = 1)
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        if ($type === true) {
            return true;
        }

        if ($ts === 2) {
            return 'https';
        }
        return 'https://';
    }

    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        if ($type === true) {
            return true;
        }

        if ($ts === 2) {
            return 'https';
        }

        return 'https://';
    }

    if (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        if ($type === true) {
            return true;
        }

        if ($ts === 2) {
            return 'https';
        }

        return 'https://';
    }
    if ($type === true) {
        return false;
    }

    if ($ts === 2) {
        return 'http';
    }

    return 'http://';
}

/**
 * @param $str
 * @param $leftStr
 * @param $rightStr
 * @return false|string
 * 取中间值
 */
function getSubstr($str, $leftStr, $rightStr)
{
    if ($rightStr == '') {
        return explode($leftStr, $str)[1];
    } else {
        $left = strpos($str, $leftStr);
        $right = strpos($str, $rightStr, $left);
        if ($left < 0 or $right < $left) {
            return '';
        }
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }
}


/**
 * @param $dir //待删除的文件夹名称
 * @return bool
 * 删除指定文件夹！
 */
function DelDir($dir)
{
    if (!is_dir($dir)) {
        return false;
    }
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != '.' && $file != '..') {
            $fullpath = $dir . '/' . $file;
            if (!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                DelDir($fullpath);
            }
        }
    }
    closedir($dh);
    if (rmdir($dir)) {
        return true;
    }

    return false;
}


/**
 * 背景图片操作类
 */
class background
{
    /**
     * @return false|string
     * 取出背景图片
     */
    public static function image()
    {
        global $conf;
        switch ($conf['background']) {
            case 1: //随机二次元
                return 'background-image: url(' . self::anime() . ');-webkit-background-size: cover和-o-background-size: cover';
            case 2: //随机高清
                return 'background-image: url(' . self::Bing_random() . ');-webkit-background-size: cover和-o-background-size: cover';
            case 3: //随机二次元
                return 'background-image: url(' . self::anime2() . ');-webkit-background-size: cover和-o-background-size: cover';
            case 4:
                return false;
            case 5:
                return 'background:#ecedf0 url(/assets/img/bj.png) fixed;background-repeat:repeat;';
            default:
                return 'background-image: url(' . self::Bing_random() . ')';
        }
    }

    public static function Bing_random()
    {
        return 'https://api.ixiaowai.cn/gqapi/gqapi.php';
    }

    public static function anime()
    {
        return 'https://api.ghser.com/random/api.php';
    }

    public static function anime2()
    {
        return 'https://api.mtyqx.cn/api/random.php';
    }
}

/**
 * @param $url
 * @return bool
 * 验证远程文件地址是否有效
 */
function file_exists_image($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    $result = curl_exec($curl);
    if ($result !== false) {
        return true;
    } else return false;
}

/**
 * @param int $Type 1=返回二维码图片地址，2=输出图片或生成图片
 * @param string $url //内容
 * @param int $w //宽高
 * @param int $m //间距
 * @param bool $outfile
 * @param bool $outfiles
 * 二维码生成方法
 */
function QR_Code($Type = 1, $url = '请将text参数填写完整，可传递域名，文字等信息！', $w = 6, $m = 1, $outfile = false, $outfiles = false)
{
    if ($Type == 1) {
        QRcode::png($url, $outfile, QR_ECLEVEL_H, $w, $m);
        return ($outfiles == false ? $outfile : $outfiles);
    }
    return QRcode::png($url, $outfile, QR_ECLEVEL_H, $w, $m);
}


/**
 * @param $name
 * 模板配置读取
 */
function TemConf($name, $type = 1)
{
    $file_path = ROOT . 'template/' . $name . '/conf.json';
    $Json = json_decode(file_get_contents($file_path), true);
    if (empty($Json)) {
        return false;
    }
    if ($type === 1) {
        return $Json['extend'];
    }
    return $Json;
}


/**
 * @param $html
 * @return string
 * 提取html 文本内容，正文内容
 */
function DeleteHtml($str)
{
    $str = trim($str);
    $str = strip_tags($str, ''); //利用php自带的函数清除html格式
    $str = preg_replace("/\t/", "", $str);
    $str = preg_replace("/\r\n/", "", $str);
    $str = preg_replace("/\r/", "", $str);
    $str = preg_replace("/\n/", "", $str);
    $str = preg_replace("/ /", "", $str);
    $str = preg_replace("/  /", "", $str);
    $str = preg_replace("/\'/", "", $str);
    return trim($str);
}
