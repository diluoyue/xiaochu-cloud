<?php
/**
 * Author：晴玖天
 * Creation：2020/7/18 19:33
 * Filename：Hook.php
 * 全局钩子类
 *
 * 调用方法
 * use lib\Hook\Hook;
 *
 * Hook::delete(钩子名称,待删除应用); 删除
 * Hook::add(钩子名称,待注册应用); 注册
 * Hook::execute(钩子名称,传值内容); 执行
 * Hook::arr(钩子名称); 查询，钩子名称留空查询全部
 * 待添加...
 */

namespace lib\Hook;

use lib\AppStore\AppList;

class Hook
{
    private static $HookFlie = ROOT . 'includes/lib/Hook/Hook.json'; //改可以改，但是要保证路径可用，文件格式无所谓

    /**
     * @param $Name 钩子名称
     * @param $id 应用标识
     * 为应用注册钩子
     */
    public static function add($Name, $id)
    {
        $Data = self::arr();
        $HookFs = fopen(self::$HookFlie, 'w');
        if (!$HookFs) return [
            'code' => -1,
            'msg' => '无法读取钩子内容'
        ];
        if (in_array($id, $Data[$Name])) {
            fwrite($HookFs, json_encode($Data, JSON_UNESCAPED_UNICODE));
            fclose($HookFs);
            return [
                'code' => -1,
                'msg' => '应用[' . $id . ']已注册',
            ];
        }

        if (count($Data[$Name]) === 0) {
            $Data[$Name] = [$id];
        } else {
            $Data[$Name] = array_merge($Data[$Name], [$id]);
        }

        if (fwrite($HookFs, json_encode($Data, JSON_UNESCAPED_UNICODE))) {
            fclose($HookFs);
            return [
                'code' => 1,
                'msg' => '应用[' . $id . ']已经成功在钩子[' . $Name . ']内注册',
            ];
        }
        fclose($HookFs);
        return [
            'code' => -1,
            'msg' => '应用[' . $id . ']在钩子[' . $Name . ']内注册失败',
        ];
    }

    public static $ArrayName = [];

    /**
     * @param $Name //钩子名称
     * 取出已注册的钩子列表
     */
    public static function arr($Name = false)
    {
        //校验
        if ($Name && isset(self::$ArrayName[$Name])) {
            return self::$ArrayName[$Name];
        }
        mkdirs(ROOT . 'includes/lib/Hook');
        $Data = json_decode(file_get_contents(self::$HookFlie), TRUE);
        if (!is_file(self::$HookFlie) || !$Data) {
            @file_put_contents(self::$HookFlie, '[]');
            $Data = [];
        }
        if ($Name) {
            if (!array_key_exists($Name, $Data)) {
                $HookFs = fopen(self::$HookFlie, 'w');
                if (!$HookFs) return [
                    'code' => -1,
                    'msg' => '无法读取钩子内容'
                ];
                $Data = $Data + [$Name => []];
                if (fwrite($HookFs, json_encode($Data, JSON_UNESCAPED_UNICODE))) {
                    fclose($HookFs);
                    return [];
                }
                return false;
            }
            self::$ArrayName[$Name] = $Data[$Name];
            return $Data[$Name];
        }
        return $Data;
    }

    /**
     * @param $Name //钩子名称
     * @param $id //应用标识
     * 删除指定钩子内的指定应用！
     */
    public static function delete($Name, $id)
    {
        $Data = self::arr();
        $HookFs = fopen(self::$HookFlie, 'w');
        if (!$HookFs) {
            return [
                'code' => -1,
                'msg' => '无法读取钩子内容'
            ];
        }

        if (!in_array($id, $Data[$Name])) {
            fwrite($HookFs, json_encode($Data, JSON_UNESCAPED_UNICODE));
            fclose($HookFs);
            return [
                'code' => -1,
                'msg' => '应用[' . $id . ']未注册,无法删除！',
            ];
        }

        $Data[$Name] = array_diff($Data[$Name], [$id]);
        if (fwrite($HookFs, json_encode($Data, JSON_UNESCAPED_UNICODE))) {
            fclose($HookFs);
            return [
                'code' => 1,
                'msg' => '已经为应用[' . $id . ']删除了钩子[' . $Name . ']',
            ];
        }

        fclose($HookFs);
        return [
            'code' => -1,
            'msg' => '删除失败',
        ];
    }

    /**
     * @param $Name //待执行的钩子名称
     * @param array $Data 传递到应用内的名称
     * 执行钩子！
     * 此处的Api为方法名称
     */
    public static function execute($Name, array $Data = [])
    {
        $Hook = self::arr($Name);
        $Count = count($Hook);
        if ($Count === 0) {
            return [
                'code' => -1,
                'msg' => '钩子[' . $Name . ']内无待执行应用！',
            ];
        }
        $Data = array_merge($Data, [
            'HookName' => $Name
        ]);
        $i = 0;
        foreach ($Hook as $value) {
            try {
                $Api = AppList::Api($value, $Data, false);
            } catch (\Exception $e) {
                $Api = false;
            }
            if ($Api) {
                ++$i;
            }
            unset($Api);
        }
        unset($Hook);
        return [
            'code' => 1,
            'msg' => '本次钩子[' . $Name . ']成功调用了' . $i . '个应用，共' . $Count . '个应用！'
        ];
    }
}
