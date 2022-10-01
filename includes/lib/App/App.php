<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城系统
// +----------------------------------------------------------------------
// | Creation: 2021/5/20 18:03
// +----------------------------------------------------------------------
// | Filename: App.php
// +----------------------------------------------------------------------
// | Explain: 第三方App生成操作类
// +----------------------------------------------------------------------

namespace lib\App;

use config;
use Curl\Curl;
use Medoo\DB\SQL;

class App
{
    /**
     * @var string
     * error image
     */
    public static $ImageError = 'http://assets.79tian.com/data/20210312/13/1615530357_esNSJyK1ZHcgUDrO_404.png';

    /**
     * @param $id
     * 获取地址或部署
     */
    public static function AppDownload($id, $type = 1, $uid = -1)
    {
        $DB = SQL::DB();
        $SQL = [
            'id' => (int)$id,
            'TaskID[!]' => -1,
            'state' => 1,
        ];
        if ($uid != -1) {
            $SQL['uid'] = (int)$uid;
        }
        $App = $DB->get('app', ['id', 'download', 'uid'], $SQL);
        if (!$App) {
            dies(-1, 'App打包任务不存在或未打包成功！');
        }
        self::DownloadUrl($App['download'], $App['id']);
        if ((int)$type === 2) {
            //部署
            if ($App['uid'] >= 1) {
                $UserData = $DB->get('user', ['configuration', 'id'], [
                    'id' => (int)$App['uid']
                ]);
                if (!$UserData) {
                    dies(-1, '用户不存在！');
                }

                if ($UserData['configuration'] == '') {
                    $configuration_arr = [];
                } else $configuration_arr = config::common_unserialize($UserData['configuration']);

                $configuration_arr = array_merge($configuration_arr, [
                    'appurl' => ROOT_DIR_S . '/?mod=AppDownload&id=' . $id,
                ]);
                $Res = $DB->update('user', [
                    'configuration' => serialize($configuration_arr),
                ], [
                    'id' => $UserData['id']
                ]);
                if ($Res) {
                    $msg = '此App的下载地址已经成功绑定至用户：' . $App['uid'] . '的站点！';
                } else {
                    $msg = '为用户：' . $App['uid'] . '绑定App下载地址失败！，数据无法写入！';
                }
            } else {
                $Res = $DB->update('config', [
                    'V' => ROOT_DIR_S . '/?mod=AppDownload&id=' . $id,
                ], [
                    'K' => 'appurl'
                ]);
                if ($Res) {
                    config::unset_cache();
                    $msg = '默认App下载地址设置成功,可前往网站配置查看！';
                } else {
                    $msg = '默认App下载地址设置失败！';
                }
            }
        } else {
            $msg = 'App下载地址获取成功';
        }
        dier([
            'code' => 1,
            'msg' => $msg,
            'url' => href(2) . ROOT_DIR_S . '/?mod=AppDownload&id=' . $id,
        ]);
    }

    /**
     * @param $download
     * 返回本地下载地址
     */
    public static function DownloadUrl($download, $id)
    {
        $path = ROOT . 'includes/extend/log/App/' . $id . '/';
        mkdirs($path);
        if (file_exists($path . 'android.apk') && file_exists($path . 'ios.mobileconfig')) {
            return [
                'android_url' => '/includes/extend/log/App/' . $id . '/android.apk',
                'ios_url' => '/includes/extend/log/App/' . $id . '/ios.mobileconfig',
                'state' => 1,
            ];
        }
        $download = json_decode($download, true);
        if (empty($download['android_url']) || empty($download['ios_url'])) {
            dies(-1, 'App下载地址无效，请联系客服处理！');
        }
        if (copy($download['android_url'], $path . 'android.apk') && copy($download['ios_url'], $path . 'ios.mobileconfig')) {
            return [
                'android_url' => '/includes/extend/log/App/' . $id . '/android.apk',
                'ios_url' => '/includes/extend/log/App/' . $id . '/ios.mobileconfig',
                'state' => 1,
            ];
        }
        $download['state'] = 2;
        return $download;
    }

    /**
     * @param $id
     * 同步任务
     */
    public static function AppCalibration($id, $uid = -1)
    {
        global $date;
        $DB = SQL::DB();
        $SQL = [
            'id' => (int)$id,
            'TaskID[!]' => -1,
        ];
        if ($uid != -1) {
            $SQL['uid'] = (int)$uid;
        }
        $App = $DB->get('app', '*', $SQL);
        if (!$App) {
            dies(-1, '此任务不存在或未提交！');
        }

        $Data = Curl::Get('api/App/index', [
            'act' => 'AppCalibration',
            'TaskID' => $App['TaskID'],
        ]);
        $Data = json_decode(xiaochu_de($Data), true);
        if (empty($Data)) {
            dies(-1, '校准失败，请重新尝试！');
        }
        if ($Data['code'] < 1) {
            $SQL = [
                'TaskMsg' => $Data['msg'],
                'state' => 3,
            ];
        } else {
            //数据获取成功
            if ((int)$Data['data']['state'] === 1) {
                //成功
                $SQL = [
                    'TaskMsg' => $Data['data']['TaskMsg'],
                    'endtime' => $Data['data']['endtime'],
                    'download[JSON]' => $Data['data']['download[JSON]'],
                    'state' => 1,
                ];
            } else {
                //失败
                $SQL = [
                    'TaskMsg' => $Data['msg'],
                    'endtime' => $date,
                    'state' => ($Data['data']['state'] === 0 ? 2 : 3),
                ];
            }
        }
        /**
         * 写入进度
         */
        $DB->update('app', $SQL, [
            'id' => $App['id'],
        ]);
        dier($Data);
    }

    /**
     * @param $id
     * 提交打包任务
     */
    public static function AppSubmit($id, $uid = -1)
    {
        global $date;
        $DB = SQL::DB();
        $SQL = [
            'id' => (int)$id,
            'TaskID' => -1,
        ];
        if ($uid != -1) {
            $SQL['uid'] = (int)$uid;
        }
        $App = $DB->get('app', '*', $SQL);
        if (!$App) {
            dies(-1, '此任务不存在或已经提交,可点击校准任务来进行同步！');
        }
        $Data = Curl::Get('api/App/index', [
            'act' => 'AppSubmit',
            'data' => [
                'name' => $App['name'],
                'url' => $App['url'],
                'content' => $App['content'],
                'theme' => $App['theme'],
                'load_theme' => $App['load_theme'],
                'icon' => $App['icon'],
                'background' => $App['background'],
            ],
        ]);
        $Data = json_decode(xiaochu_de($Data), true);
        if (empty($Data)) {
            dies(-1, '提交失败，请重新尝试！');
        }
        if ($Data['code'] < 1) {
            $SQL = [
                'TaskMsg' => $Data['msg'],
                'state' => 3,
            ];
        } else {
            $SQL = [
                'TaskID' => $Data['id'],
                'TaskMsg' => $Data['msg'],
                'endtime' => $date,
                'state' => 2,
            ];
        }

        /**
         * 写入进度
         */
        $Res = $DB->update('app', $SQL, [
            'id' => $App['id'],
        ]);
        if ($Res) {
            if ($App['uid'] >= 1) {
                userlog('App生成', '您于' . $date . '成功提交了App网站客户端打包任务,任务ID:' . $App['id'], $App['uid']);
            }
        }
        dier($Data);
    }

    /**
     * 图片上传
     */
    public static function AppUploading()
    {
        $Data = Curl::GetFile('api/App/index', [
            'act' => 'AppUploading',
        ]);
        if (empty($Data)) {
            dies(-1, '文件上传失败！');
        }
        return $Data;
    }

    /**
     * @param $ID
     * App图片预览
     * @return never
     */
    public static function ImagePreview($ID)
    {
        $path = ROOT . 'includes/extend/log/App/image/';
        mkdirs($path);
        if (file_exists($path . $ID . '.jpg')) {
            Header('HTTP/1.1 303 See Other');
            Header('Location: ' . ROOT_DIR . '/includes/extend/log/App/image/' . $ID . '.jpg');
            die;
        }
        if (!empty($_SESSION['ImageAPP_' . $ID])) {
            if ($_SESSION['ImageAPP_' . $ID] == -1) {
                Header('HTTP/1.1 303 See Other');
                Header('Location: ' . self::$ImageError);
                die;
            }
            header('Content-type:image/png');
            die($_SESSION['ImageAPP_' . $ID]);
        }
        RVS(1000);
        test(['id|e|请填写图片ID']);
        $url = Curl::Get('api/Distribute/ajax', [
            'act' => 'Image',
            'id' => $ID
        ]);
        $Data = json_decode($url, TRUE);
        if (empty($url) || $Data['code'] == -1) {

            $_SESSION['ImageAPP_' . $ID] = -1;

            Header("HTTP/1.1 303 See Other");
            Header("Location: " . self::$ImageError);
        } else {

            $_SESSION['ImageAPP_' . $ID] = $url;

            $Files = fopen($path . $ID . ".jpg", "w");
            if ($Files) {
                fwrite($Files, $url);
                fclose($Files);
            }
            header('Content-type:image/jpeg');
            die($url);
        }
    }
}
