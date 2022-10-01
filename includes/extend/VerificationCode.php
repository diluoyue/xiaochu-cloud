<?php
/**
 * Author：晴玖天
 * Creation：2020/5/30 14:07
 * Filename：VerificationCode.php
 * 验证码操作类
 */

namespace extend;


use FC\Captcha;

class VerificationCode
{

    /**
     * @param string $Name
     * @param int $num
     * @param int $w
     * @param int $h
     * @param int $size
     * 创建随机验证码
     */
    public static function RandomVerificationCode($Name = 'logins', $w = 240, $h = 80, $size = 28)
    {
        global $conf;
        if ((int)$conf['CaptchaType'] === 6) {
            $conf['CaptchaType'] = random_int(1, 5);
        }
        $ver = new Captcha();
        $ver->nums = 5;
        $ver->font_size = $size;
        $ver->width = $w;
        $ver->height = $h;
        $ver->font_path = ROOT . 'assets/fonts/title.otf';

        switch ((int)$conf['CaptchaType']) {
            case 2: //普通字母
                $ver->random = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
                break;
            case 3: //字母+数字
                break;
            case 4: //汉字
                $ver->random = self::getChar(10);
                $ver->font_path = ROOT . 'assets/fonts/names.otf';
                $ver->nums = 4;
                break;
            case 5: //运算符验证码
                $ver->font_path = ROOT . 'assets/fonts/names.otf';
                //$ver->is_gif = false;
                $type = random_int(1, 4);
                switch ($type) {
                    case 2: //减
                        $arr = self::RandomEvenNumber(1, 10);
                        $code = $arr[0] . '减' . $arr[1];
                        $count = $arr[0] - $arr[1];
                        break;
                    case 3: //乘
                        $arr = self::RandomEvenNumber(1, 10, 2);
                        $code = $arr[0] . '乘' . $arr[1];
                        $count = $arr[0] * $arr[1];
                        break;
                    case 4: //除
                        $arr = self::RandomEvenNumber(1, 10);
                        $code = ($arr[0] * $arr[1]) . '除' . $arr[0];
                        $count = ($arr[0] * $arr[1]) / $arr[0];
                        break;
                    default: //加
                        $arr = self::RandomEvenNumber(1, 10, 2);
                        $code = $arr[0] . '加' . $arr[1];
                        $count = $arr[0] + $arr[1];
                        break;
                }

                $_SESSION[$Name] = md5((string)$count . href());

                break;
            default: //数字
                $ver->random = '123456789';
                break;
        }

        if ((int)$conf['CaptchaType'] !== 5) {
            $code = $ver->getCode();

            $_SESSION[$Name] = md5($code . href());

        }
        $ver->doImg($code);
    }

    /**
     * @param $min
     * @param $max
     * @param int $type
     * 返回随机双数
     */
    public static function RandomEvenNumber($min, $max, $type = 1)
    {
        $arr = [random_int($min, $max), random_int($min, $max)];
        if ($type === 1) {
            rsort($arr);
        }
        return $arr;
    }

    /**
     * @param $num
     * @return string
     * 随机汉字生成器
     */
    public static function getChar($num)
    {
        $b = '';
        for ($i = 0; $i < $num; $i++) {
            $a = chr(mt_rand(0xB0, 0xD0)) . chr(mt_rand(0xA1, 0xF0));
            $b .= iconv('GB2312', 'UTF-8', $a);
        }
        return $b;
    }
}