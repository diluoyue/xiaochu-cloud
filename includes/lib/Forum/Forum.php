<?php
// +----------------------------------------------------------------------
// | Project: 晴玖商城
// +----------------------------------------------------------------------
// | Creation: 2022/1/18 9:38
// +----------------------------------------------------------------------
// | Filename: Forum.php
// +----------------------------------------------------------------------
// | Explain: 获取官方论坛的部分参数，论坛程序：Discuz! Q
// +----------------------------------------------------------------------

namespace lib\Forum;

use CookieCache;

/**
 *
 */
class Forum
{

    /**
     * @var string
     * 论坛地址
     */
    public static $Url = 'https://bbs.79tian.com';

    /**
     * @var int
     * 获取的分类ID
     */
    private static $Type = 8;

    /**
     * @var float|int
     * 数据缓存时间：秒数
     */
    private static $Cache = 60 * 10;

    /**
     * @var int
     * 获取的数据数量
     */
    private static $perPage = 10;

    /**
     * 开始获取数据
     */
    public static function getUrl($type = 1)
    {
        if ($type === 1) {
            CookieCache::read();
        }
        $GetData = [
            'perPage' => self::$perPage,
            'filter' => [
                'categoryids' => [self::$Type]
            ]
        ];
        $Data = get_curl(self::$Url . '/api/v3/thread.list?' . http_build_query($GetData));
        $Data = json_decode($Data, TRUE);

        if (empty($Data) || $Data['Code'] !== 0 || count($Data['Data']['pageData']) === 0) {
            dies(-1, '公告数据获取失败!');
        }
        $Content = [];
        foreach ($Data['Data']['pageData'] as $key) {
            $Content[] = [
                'title' => $key['title'],
                'content' => $key['content']['text'],
                'viewCount' => $key['viewCount'],
                'addtime' => $key['createdAt'],
                'user' => [
                    'userId' => $key['user']['userId'],
                    'nickname' => $key['user']['nickname'],
                    'avatar' => $key['user']['avatar'],
                    'url' => self::$Url . '/user/' . $key['user']['userId'],
                ],
                'url' => self::$Url . '/thread/' . $key['threadId'],
            ];
        }

        CookieCache::add([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Content
        ], self::$Cache);

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Content
        ]);
    }
}
