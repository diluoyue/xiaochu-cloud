<?php

/**
 * 站长后台操作类
 */

namespace Admin;


use BT\Config as BTC;
use BT\Construct as BTCO;
use CookieCache;
use Curl\Curl;
use extend\ImgThumbnail;
use lib\supply\GoodsMonitoring;
use lib\supply\jiuwu;
use lib\supply\Price;
use lib\supply\StringCargo;
use login_data;
use Medoo\DB\SQL;
use Server\Server;

class Admin
{
    private static $SpPriceArr = [];

    /**
     * @param int $type
     * @param $date
     * @return array
     * 主站后台数据统计
     */
    public static function HomeData($type = 1, $date)
    {
        global $times, $conf;
        $Time = strtotime($date . ' 00:00:00');
        if (!$Time) {
            dies(-1, '读取时间提交有误！');
        }

        if ($Time > time()) {
            dies(-1, '读取时间范围不能大于！' . date("Y-m-d"));
        }

        $Redis = Redis(8);
        $RedisName = ('AdminHomeData_' . $date);
        if ($type === 1 && $Redis !== false && !empty($Redis->get($RedisName))) {
            return json_decode($Redis->get($RedisName), TRUE);
        }

        $DB = SQL::DB();
        $Data = [];

        $Data['OrderSum'] = $DB->count('order', [
            'addtitm[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
        ]); //订单总数

        $Data['NewOrders'] = $DB->count('order', [
            'addtitm[>=]' => date("Y-m-d H:i:s", $Time),
            'addtitm[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
        ]); //新增订单

        $Data['FailOrders'] = $DB->count('order', [
            'state[!]' => [1, 7, 5],
            'addtitm[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
        ]); //失败订单

        $Data['TurnoverSum'] = $DB->sum('order', 'price', [
                'payment[!]' => ['免费商品', '积分兑换'],
                'addtitm[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
            ]) - 0; //总交易额

        $Data['DayTurnover'] = $DB->sum('order', 'price', [
                'payment[!]' => ['免费商品', '积分兑换'],
                'addtitm[>=]' => date("Y-m-d H:i:s", $Time),
                'addtitm[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
            ]) - 0; //今日交易额

        $Data['DayCost'] = $DB->sum('order', 'money', [
                'state[!]' => 5,
                'addtitm[>=]' => date("Y-m-d H:i:s", $Time),
                'addtitm[<]' => date("Y-m-d H:i:s", ($Time + 86400))
            ]) - 0; //今日成本

        $Data['UserSum'] = $DB->count('user', [
            'found_date[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
        ]); //用户总数

        $Data['NewUser'] = $DB->count('user', [
            'found_date[>=]' => date("Y-m-d H:i:s", $Time),
            'found_date[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
        ]); //今日新增用户

        $Data['DaySign'] = $DB->count('user', [
            '[>]journal' => ['id' => 'uid']
        ], [
            'user.id'
        ], [
            'journal.date[>=]' => date("Y-m-d H:i:s", $Time),
            'journal.date[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
            'journal.name' => '每日签到'
        ]); //今日签到用户

        $Data['CheckPending'] = $DB->count('mark', ['state' => 2]); //待审核评论
        $Data['CheckWithdraw'] = $DB->count('withdrawal', ['state' => 3]); //待处理提现
        $Data['CheckWorkOrder'] = $DB->count('tickets', [
            'state[!]' => 3,
            'type[!]' => 3,
        ]); //待处理工单

        $Data['GoodsSum'] = $DB->count('goods', [
            'date[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
        ]); //商品总数
        $Data['DayPay'] = $DB->sum('pay', 'money', [
                'gid' => -1, 'trade_no[!]' => NULL,
                'addtime[>=]' => date("Y-m-d H:i:s", $Time),
                'addtime[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
            ]) - 0; //今日充值
        $Data['DayConsumption'] = $DB->sum('order', 'price', [
                'payment' => '积分兑换',
                'addtitm[>=]' => date("Y-m-d H:i:s", $Time),
                'addtitm[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
            ]) - 0; //今日消耗积分

        $Data['SuccessfulWithdrawal'] = $DB->sum('withdrawal', 'money', ['state' => 1]) - 0; //成功提现
        $Data['AgentsSum'] = $DB->count('user', [
            'grade[>]' => $conf['userdefaultgrade'],
            'found_date[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
        ]); //代理总数
        $Data['DayInvite'] = $DB->count('invite', [
            'creation_time[>=]' => date("Y-m-d H:i:s", $Time),
            'creation_time[<]' => date("Y-m-d H:i:s", ($Time + 86400)),
        ]); //今日邀请

        if ($type === 1 && $Redis !== false && !empty($Redis->get('AdminHomeDataTable'))) {
            $Data['Table'] = json_decode($Redis->get('AdminHomeDataTable'), TRUE);
        } else {
            $TodayTime = (strtotime($times) - 1); //今日时间戳
            $Data['Table'] = [];
            $weekarray = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];

            for ($i = 1; $i < 31; $i++) {
                $Day = ($TodayTime - (86400 * $i)); //今日时间戳
                $Data['Table'][] = [
                    'date' => '[' . ($weekarray[date("w", $Day)]) . '] ' . date("m/d", $Day),
                    'OrderSum' => $DB->count('order', [
                        'addtitm[<]' => date("Y-m-d H:i:s", ($Day + 86400)),
                        'addtitm[>]' => date("Y-m-d H:i:s", $Day),
                    ]),
                    'TurnoverSum' => $DB->sum('order', 'price', [
                            'payment[!]' => ['免费商品', '积分兑换'],
                            'addtitm[<]' => date("Y-m-d H:i:s", ($Day + 86400)),
                            'addtitm[>]' => date("Y-m-d H:i:s", $Day),
                        ]) - 0,
                    'NewUser' => $DB->count('user', [
                            'found_date[<]' => date("Y-m-d H:i:s", ($Day + 86400)),
                            'found_date[>]' => date("Y-m-d H:i:s", $Day),
                        ]) - 0,
                ];
            }

            $Data['Table'] = array_reverse($Data['Table']);

            if ($Redis !== false) {
                $Times = strtotime(date("Y-m-d") . ' 23:59:59') + 5; //容错级别4秒
                $Redis->setex('AdminHomeDataTable', ($Times - time()), json_encode($Data['Table']));
            }
        }

        if ($Redis !== false) {
            $Redis->setex($RedisName, 60, json_encode($Data));
        }

        return $Data;
    }

    /**
     * @param int $type
     * @return array
     * 销售排行
     */
    public static function SalesChart($type = 1)
    {
        $Redis = Redis(6);
        if ($Redis !== false && $Redis->get('SalesChart_' . $type)) {
            return json_decode($Redis->get('SalesChart_' . $type), TRUE);
        }
        $DateDay = date("Y-m-d") . ' 00:00:01';
        $YesterdayDate = date("Y-m-d", strtotime("-" . 1 . " day")) . ' 00:00:01';
        $DB = SQL::DB();
        switch ($type) {
            case 1: //全部
                $Res = $DB->query("SELECT a.gid,a.name,image,(select count(*) from `sky_order` as b where b.gid = a.gid ) as count ,(select sum(money) from `sky_order` as b where b.gid = a.gid ) as cost,(select sum(price) from `sky_order` as b where b.gid = a.gid and b.payment != '积分兑换' ) as money FROM  `sky_goods` as a   ORDER BY `count` DESC LIMIT 10 ")->fetchAll();
                break;
            case 2: //今日
                $Res = $DB->query("SELECT a.gid,a.name,image,(select count(*) from `sky_order` as b where b.gid = a.gid and  b.addtitm > '$DateDay' ) as count ,(select sum(money) from `sky_order` as b where b.gid = a.gid and  b.addtitm > '$DateDay' ) as cost,(select sum(price) from `sky_order` as b where b.gid = a.gid and b.payment != '积分兑换' and  b.addtitm > '$DateDay' ) as money FROM  `sky_goods` as a  ORDER BY `count` DESC LIMIT 10 ")->fetchAll();
                break;
            default: //昨日
                $Res = $DB->query("SELECT a.gid,a.name,image,(select count(*) from `sky_order` as b where b.gid = a.gid and  b.addtitm < '$DateDay' and b.addtitm > '$YesterdayDate' ) as count ,(select sum(money) from `sky_order` as b where b.gid = a.gid and  b.addtitm < '$DateDay' and b.addtitm > '$YesterdayDate' ) as cost,(select sum(price) from `sky_order` as b where b.gid = a.gid and b.payment != '积分兑换' and  b.addtitm < '$DateDay' and b.addtitm > '$YesterdayDate' ) as money FROM  `sky_goods` as a  ORDER BY `count` DESC LIMIT 10 ")->fetchAll();
                break;
        }

        $Data = [];
        foreach ($Res as $v) {
            if ($v['cost'] === null && $v['money'] === null) {
                continue;
            }
            $Data[] = [
                'image' => ImageUrl(json_decode($v['image'], true)[0]),
                'gid' => $v['gid'],
                'name' => $v['name'],
                'count' => $v['count'] - 0,
                'cost' => round($v['cost'], 3),
                'money' => round($v['money'], 3),
            ];
        }

        if (count($Data) === 0) {
            dies(-1, '没有查询到相关数据！');
        }

        if ($Redis !== false) {
            $Redis->setex('SalesChart_' . $type, 120, json_encode($Data));
        }

        return $Data;
    }

    /**
     * @param $type
     * 用户批量操作
     */
    public static function UserBatchEditor($type)
    {
        $DB = SQL::DB();
        switch ((int)$type) {
            case 1: //清空用户配置
                $Res = $DB->update('user', [
                    'configuration' => '',
                ]);
                break;
            case 2: //清空商品加价
                $Res = $DB->update('user', [
                    'pricehike' => '',
                ]);
                break;
            case 3: //校准用户等级
                $Count = $DB->count('price', [
                    'state' => 1
                ]);

                if (empty($Count)) {
                    $Count = 1;
                }

                $Res = $DB->update('user', [
                    'grade' => $Count,
                ], [
                    'grade[>]' => $Count
                ]);
                break;
            default:
                dies(-1, '请求有误！');
                break;
        }

        if ($Res) {
            dies(1, '操作成功！');
        } else {
            dies(-1, '操作失败！');
        }
    }

    /**
     * @param $id
     * 登录用户后台
     */
    public static function UserLogin($id)
    {
        $DB = SQL::DB();
        $User = $DB->get('user', ['user_idu'], [
            'id' => (int)$id,
        ]);
        if (!$User) dies(-1, '用户不存在!');
        if (setcookie("THEKEY", $User['user_idu'], time() + 3600 * 12 * 15, '/')) {
            dies(1, '登录数据写入成功，点击进入后台！');
        } else dies(-1, '登录失败！');
    }

    public static function AppSet($_QET)
    {

        if ($_QET['field'] === 'url') {
            $_QET['value'] = StringCargo::UrlVerify($_QET['value'], 3);
        }

        $SQL = [
            $_QET['field'] => $_QET['value']
        ];
        $DB = SQL::DB();
        $Res = $DB->update('app', $SQL, [
            'id' => $_QET['id']
        ]);
        if ($Res) {
            dies(1, '调整成功！');
        } else dies(-1, '调整失败！');
    }

    /**
     * @param $_QET
     * 修改用户信息
     */
    public static function UserRedact($_QET)
    {
        global $conf;
        $User = login_data::UserMoney($_QET['id'], 2);
        if (!$User) {
            dies(-1, '用户不存在！');
        }
        $MSG = false;
        $Count = 0;
        switch ($_QET['field']) {
            case 'money[+]':
                $_QET['value'] = (float)$_QET['value'];
                $User['money'] = (float)$User['money'] + $_QET['value'];
                $MSG = '官方客服在后台为您增加了' . $_QET['value'] . '元余额！,当前账户余额为：' . $User['money'] . '元';
                break;
            case 'money[-]':
                $_QET['value'] = (float)$_QET['value'];
                $User['money'] = (float)$User['money'] - $_QET['value'];
                $MSG = '官方客服在后台为您扣除了' . $_QET['value'] . '元余额！,当前账户余额为：' . $User['money'] . '元';
                break;
            case 'currency[+]':
                $_QET['value'] = (float)$_QET['value'];
                $User['currency'] = (float)$User['currency'] + $_QET['value'];
                $MSG = '官方客服在后台为您增加了' . $_QET['value'] . $conf['currency'] . '！,当前账户剩余：' . $User['currency'] . $conf['currency'];
                break;
            case 'currency[-]':
                $_QET['value'] = (float)$_QET['value'];
                $User['currency'] = (float)$User['currency'] - $_QET['value'];
                $MSG = '官方客服在后台为您扣除了' . $_QET['value'] . $conf['currency'] . '！,当前账户剩余：' . $User['currency'] . $conf['currency'];
                break;
            case 'grade':

                break;
            case 'domain':

                break;
            case 'state':

                break;
            case 'qq':

                break;
            case 'mobile':

                break;
            case 'superior':
                if ((int)$_QET['id'] === (int)$_QET['value']) {
                    dies(-1, '上级编号不能修改为被调整用户！');
                }
                if ($_QET['value'] >= 1) {
                    $User = login_data::UserMoney($_QET['value']);
                    if (!$User) dies(-1, '绑定上级不存在！');
                }
                break;
            case 'username':
                preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $_QET['value'], $arr_pr);
                if (empty($arr_pr[0])) {
                    dies(-1, '登陆账号只可为英文或数字,并且长度要大于6小于18位！');
                }
                if (empty($_QET['value'])) {
                    dies(-1, '登录账号不可为空！');
                }
                break;
            case 'password':
                preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $_QET['value'], $arr_pr);
                if (empty($arr_pr[0]) && !empty($_QET['value'])) {
                    dies(-1, '用户登陆密码只可为英文或数字,并且长度要大于6小于18位！');
                }
                if (empty($_QET['value'])) {
                    dies(-1, '登录密码不可为空！');
                }
                $_QET['value'] = md5($_QET['value']);
                break;
            case 'name':

                break;
            default:
                dies(-1, '无效请求');
                break;
        }
        $SQL = [
            $_QET['field'] => $_QET['value']
        ];
        $DB = SQL::DB();
        $Res = $DB->update('user', $SQL, [
            'id' => $_QET['id']
        ]);

        if ($Res) {
            if ($MSG !== false) {
                userlog('账户调整', $MSG, $_QET['id'], $Count);
            }
            dies(1, '调整成功！');
        } else dies(-1, '调整失败！');
    }

    /**
     * @param $Data
     * 日志列表
     */
    public static function UserLogList($Data)
    {
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
            'uid[>=]' => 1
        ];

        $DB = SQL::DB();

        if (!empty($Data['name'])) {
            $SQL['name'] = $Data['name'];
        }

        if (!empty($Data['uid'])) {
            $SQL['uid'] = $Data['uid'];
        }

        $Res = $DB->select('journal', '*', $SQL);
        if (!$Res) {
            dies(-1, ' 没有更多了');
        }

        $DataList = [];
        foreach ($Res as $v) {
            $v['count'] = round($v['count'], 8);
            $DataList[] = $v;
        }

        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $DataList,
        ];
    }

    /**
     * @param $Data
     * @return array
     * 用户列表
     */
    public static function UserList($Data)
    {
        global $conf, $accredit;
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        $DB = SQL::DB();

        if ((int)$Data['GradeIndex'] !== -1) {
            //获取指定等级的用户
            $Count = $DB->count('price', ['state' => 1]);
            if ((int)$Data['GradeIndex'] === $Count) {
                //最大
                $SQL['grade[>=]'] = (int)$Data['GradeIndex'];
            } else {
                //范围之内
                $SQL['grade'] = (int)$Data['GradeIndex'];
            }
        }

        if (!empty($Data['name'])) {
            $SQL['OR'] = [
                'name[~]' => $Data['name'],
                'id' => $Data['name'],
                'qq' => $Data['name'],
                'superior' => $Data['name'],
                'domain' => $Data['name'],
                'mobile' => $Data['name'],
                'username' => $Data['name'],
                'ip' => $Data['name'],
            ];
        }

        if ((int)$Data['UserSort'] !== -1) {
            //获取指定等级的用户
            switch ((int)$Data['UserSort']) {
                case 1:
                    $SQL['ORDER'] = [
                        'money' => 'ASC'
                    ];
                    break;
                case 2:
                    $SQL['ORDER'] = [
                        'money' => 'DESC'
                    ];
                    break;
                case 3:
                    $SQL['ORDER'] = [
                        'currency' => 'ASC'
                    ];
                    break;
                case 4:
                    $SQL['ORDER'] = [
                        'currency' => 'DESC'
                    ];
                    break;
            }
        }

        $Res = $DB->select('user', [
            'id', 'user_idu',
            'superior', 'currency',
            'ip', 'money', 'grade',
            'domain', 'name',
            'state', 'recent_time',
            'found_date', 'qq', 'mobile',
            'username'
        ], $SQL);

        if (!$Res) {
            dies(-1, ' 没有更多了');
        }

        $GradeList = $DB->select('price', [
            'name',
        ], ['state' => 1, 'ORDER' => ['sort' => 'ASC']]);

        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Res,
            'type' => (int)$conf['userdomaintype'],
            'GradeList' => (!$GradeList ? [] : $GradeList),
            'domain' => ((int)$conf['userdomaintype'] === 1 ? '.' . $accredit['url'] : href() . '/'),
        ];
    }

    /**
     * 取出订单列表
     */
    public static function OrderList($Data)
    {
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        if (!empty($Data['name'])) {
            $SQL['OR'] = [
                'order.input[~]' => $Data['name'],
                'order.order' => $Data['name'],
                'order.id' => $Data['name'],
            ];
        }
        if (!empty($Data['date'])) {
            if (!empty($Data['date'][0])) {
                $SQL['order.addtitm[>]'] = $Data['date'][0];
            }
            if (!empty($Data['date'][1])) {
                $SQL['order.addtitm[<]'] = $Data['date'][1];
            }
        }
        if (!empty($Data['state'])) {
            $SQL['order.state'] = $Data['state'];
        }

        if (!empty($Data['gid'])) {
            $SQL['order.gid'] = $Data['gid'];
        }
        if (!empty($Data['uid'])) {
            $SQL['order.uid'] = $Data['uid'];
        }

        $DB = SQL::DB();
        $Res = $DB->select('order', [
            '[>]goods' => ['gid' => 'gid']
        ], [
            'goods.name',
            'order.id',
            'order.uid',
            'order.muid',
            'order.price',
            'order.money',
            'order.payment',
            'order.num',
            'order.input',
            'order.docking',
            'order.return',
            'order.logistics',
            'order.state',
            'order.user_rmb',
            'order.remark',
            'order.addtitm',
            'goods.input(value)',
            'goods.specification',
            'goods.specification_spu',
        ], $SQL);

        $Order = [];
        foreach ($Res as $v) {
            $v['input'] = json_decode($v['input'], true);
            if (!is_array($v['input'])) {
                $v['input'] = [(!$v['input'] ? '无下单信息' : $v['input'])];
            }
            $v['InputName'] = CommodityInputBoxName($v['value'], $v['specification'], $v['specification_spu']);
            unset($v['specification_spu'], $v['value'], $v['specification']);
            $v['user_rmb'] -= 0;
            $Order[] = $v;
        }
        return [
            'code' => 1,
            'msg' => '订单列表获取成功',
            'data' => $Order
        ];
    }

    /**
     * @param $gid
     * 复制商品
     */
    public static function GoodsCopy($gid)
    {
        global $date;
        $DB = SQL::DB();
        $Goods = $DB->get('goods', '*', [
            'gid' => (int)$gid,
        ]);
        if (!$Goods) dies(-1, '商品不存在！');

        $sort = $DB->get('goods', ['sort'], [
            'ORDER' => [
                'sort' => 'DESC'
            ],
            'LIMIT' => 1
        ]);
        $sort = $sort['sort'] + 1;
        $Goods['sort'] = $sort;
        unset($Goods['gid']);
        $Goods['date'] = $date;
        $Goods['name'] .= ' [复制品]';
        $Res = $DB->insert('goods', $Goods);
        if ($Res) {
            return [
                'code' => 1,
                'msg' => '商品复制成功',
            ];
        }

        return [
            'code' => -1,
            'msg' => '商品复制失败！',
        ];
    }

    /**
     * @param $Data
     * @return array
     * 商品列表
     */
    public static function GoodsList($Data)
    {
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'goods.sort' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        if ($Data['cid'] >= 1) {
            $SQL['goods.cid'] = $Data['cid'];
        }
        if (!empty($Data['name'])) {
            $SQL['goods.name[~]'] = $Data['name'];
        }
        $DB = SQL::DB();
        $Goods = $DB->select('goods', [
            '[>]class' => ['cid' => 'cid'],
            '[>]shequ' => ['sqid' => 'id']
        ], [
            'gid', 'goods.name', 'goods.cid',
            'class.name(Cname)', 'money', 'goods.image',
            'sqid', 'goods.state', 'explain', 'deliver',
            'note', 'profits', 'goods.sort',
            'min', 'max', 'quota', 'quantity',
            'specification', 'specification_type',
            'specification_spu', 'specification_sku',
            'shequ.class_name', 'shequ.type(hyid)', 'selling',
        ], $SQL);
        $GoodsData = [];
        foreach ($Goods as $k => $v) {
            $Pir = self::GoodsPrice($v);
            if ((int)$v['deliver'] === 3) {
                $v['quota'] = $DB->count('token', ['uid' => 1, 'gid' => $v['gid']]);
            }
            $v['money'] = $Pir['money'];
            $v['price'] = $Pir['price'];
            unset($Pir);

            if ($v['deliver'] == -1) {
                $SQ = self::CommunityParameter(($v['hyid'] == -1 ? $v['class_name'] : $v['hyid']));
                $v['sqid'] = $SQ['name'];
                unset($SQ);
            }

            if (!empty($v['image'])) {
                $image = json_decode($v['image'], true);
                $v['image'] = (!$image ? [$v['image']] : $image);
                unset($image);
            }

            if ($v['quota'] <= 0) {
                $v['quota'] = 0;
            }

            $GoodsData[$k] = $v;
            unset($v);
        }
        return $GoodsData;
    }

    /**
     * @param $Goods
     * @return array
     * 计算商品成本 + 售价(价格区间)
     */
    public static function GoodsPrice($Goods)
    {
        //未开启
        if ((int)$Goods['specification'] === 1) {
            $Name = 'F_' . md5((float)$Goods['profits'] . $Goods['money'] . $Goods['selling'] ?? '');
            $PrEx = self::SpPrExists($Name);
            if ($PrEx) {
                return $PrEx;
            }
            unset($PrEx);
            $Pricer = Price::List($Goods['money'], $Goods['profits'], false, $Goods['selling']);
            if (count($Pricer) === 0) {
                $Min = $Goods['money'];
                $Max = $Goods['money'];
            } else {
                $Min = 999999999999;
                $Max = 0;
            }
            foreach ($Pricer as $val) {
                if ($val['price'] > $Max) {
                    $Max = $val['price'];
                }
                if ($val['price'] < $Min) {
                    $Min = $val['price'];
                }
            }
            unset($Pricer);
            if ($Min == $Max) {
                $Price = $Min . '元';
            } else {
                $Price = $Min . ' ~ ' . $Max . '元';
            }
            self::$SpPriceArr[$Name] = [
                'money' => $Goods['money'] - 0 . '元',
                'price' => $Price,
            ];
            return self::$SpPriceArr[$Name];
        }

        $SpRule = self::SpPrice($Goods);
        if ($SpRule == -1) {
            $Goods['specification'] = 1;
            return self::GoodsPrice($Goods);
        }
        $PrMin = 999999999999;
        $PrMax = 0;
        $Min = 999999999999;
        $Max = 0;
        foreach ($SpRule as $val) {
            if ($val['money'] > $Max) {
                $Max = $val['money'];
            }
            if ($val['money'] < $Min) {
                $Min = $val['money'];
            }
            if ($val['price'] > $PrMax) {
                $PrMax = $val['price'];
            }
            if ($val['price'] < $PrMin) {
                $PrMin = $val['price'];
            }
        }
        unset($SpRule);

        if ($Min == $Max) {
            $Money = $Min . '元';
        } else {
            $Money = $Min . '~' . $Max . '元';
        }

        if ($PrMin == $PrMax) {
            $Price = $PrMax . '元';
        } else {
            $Price = $PrMin . '~' . $PrMax . '元';
        }

        unset($Min, $Max);

        return [
            'money' => $Money,
            'price' => $Price
        ];
    }

    /**
     * @param false $name
     * @param false $cid
     * @return false|mixed
     * 检测价格是否计算过,防止重复读取,导致卡慢
     */
    public static function SpPrExists($name = false)
    {
        $Data = self::$SpPriceArr;
        foreach ($Data as $key => $val) {
            if ($key == $name) {
                return $val;
            }
        }
        return false;
    }

    /**
     * 规格商品价格区间计算
     */
    public static function SpPrice($Goods)
    {
        $SkuJson = json_decode($Goods['specification_sku'], TRUE);
        if (empty($SkuJson)) {
            return -1;
        }

        $parAdd = [];
        foreach ($SkuJson as $value) {
            $Money = ($value['money'] == '' ? $Goods['money'] : $value['money']);
            $Name = md5($Goods['profits'] . $Money);
            $PrEx = self::SpPrExists($Name);
            if ($PrEx) {
                $Price = $PrEx;
            } else {
                $Price = Price::Get($Money, $Goods['profits'], 1, false, $Goods['selling']);
                self::$SpPriceArr[$Name] = $Price;
            }
            $parAdd[] = [
                'money' => $Money,
                'price' => $Price['price'],
                'points' => $Price['points'],
            ];
        }
        return $parAdd;
    }

    /**
     * @param $type
     * 匹配可对接社区的参数
     * @return array
     */
    public static function CommunityParameter($type)
    {
        $Data = StringCargo::Docking($type);
        if (empty($Data['name'])) return [
            'name' => '未知货源',
            'class_name' => $type,
        ];
        $Data['class_name'] = $type;
        return $Data;
    }

    /**
     * @param $Url
     * @param int $length
     * @return string
     * 域名测速
     */
    public static function Ping($Url, $length = 4)
    {
        $start = microtime(true);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $Url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_NOBODY, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        $end = microtime(true);
        if (!empty($result)) {
            return round(number_format($end - $start, 10, '.', ''), $length) . '秒';
        }
        return '无法访问域名[' . $Url . ']';
    }

    /**
     * @param $Data
     * 编辑器图片上传
     */
    public static function ImageUp($Data)
    {
        global $conf;
        $ImageArr = [];
        $timestamp = date('Ymd');
        mkdirs("../assets/img/image/" . $timestamp . '/');
        foreach ($Data as $key => $value) {
            $ImageName = md5_file($value["tmp_name"]);
            switch ($value['type']) {
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
            move_uploaded_file($value["tmp_name"], '../assets/img/image/' . $timestamp . '/' . $ImageName);
            $images = '/assets/img/image/' . $timestamp . '/' . $ImageName;
            new ImgThumbnail(ROOT . $images, $conf['compression'], ROOT . $images, 2);
            $ImageArr[] = ['src' => ImageUrl($images), 'size' => $value['size'] / 1000 . 'kb', 'name' => $value["name"]];
        }
        return [
            'code' => 1,
            'msg' => '图片上传成功,本次共成功上传' . count($ImageArr) . '张图片',
            'SrcArr' => $ImageArr,
        ];
    }

    public static function VideoUp($Data)
    {
        $ViewArr = [];
        $timestamp = date('Ymd');
        $File = '../assets/video/' . $timestamp . '/';
        mkdirs($File);
        foreach ($Data as $key => $value) {
            $VideoName = md5_file($value["tmp_name"]) . '.mp4';
            if ($value['type'] !== 'video/mp4') {
                dies(-1, '视频格式不正确,仅支持MP4格式视频!');
            }
            move_uploaded_file($value["tmp_name"], $File . $VideoName);
            $Video = $File . $VideoName;
            $ViewArr[] = ['src' => $Video, 'size' => $value['size'] / 1000 . 'kb', 'name' => $value["name"]];
        }
        return [
            'code' => 1,
            'msg' => '视频上传成功,本次共成功上传' . count($ViewArr) . '个视频',
            'SrcArr' => $ViewArr,
        ];
    }

    /**
     * @param $Data
     * 添加商品
     */
    public static function GoodsAdd($Data)
    {
        global $date;
        $DB = SQL::DB();

        $method = [];
        foreach ($Data['method'] as $v) {
            $method[] = (float)$v;
        }
        $method = json_encode($method);
        $SQL = [
            'cid' => $Data['cid'],
            'name' => $Data['name'],
            'image[JSON]' => (empty($Data['image']) ? [] : $Data['image']),
            'money' => $Data['money'],
            'profits' => $Data['profits'],
            'min' => $Data['min'],
            'max' => $Data['max'],
            'quota' => $Data['quota'],
            'freight' => $Data['freight'],
            'method' => $method,
            'input' => $Data['input'],
            'quantity' => $Data['quantity'],
            'docs' => $Data['docs'],
            'alert' => $Data['alert'],
            'units' => $Data['units'],
            'deliver' => $Data['deliver'],
            'sqid' => $Data['sqid'],
            'note' => (empty($Data['note']) ? '' : $Data['note']),
            'explain' => $Data['explain'],
            'specification' => $Data['specification'],
            'specification_type' => $Data['specification_type'],
            'specification_spu[JSON]' => (empty($Data['specification_spu']) ? [] : json_decode($Data['specification_spu'], true)),
            'specification_sku[JSON]' => (empty($Data['specification_sku']) ? [] : json_decode($Data['specification_sku'], true)),
            'extend[JSON]' => $Data['extend'],
            'label' => $Data['label'],
            'sales' => $Data['sales'], //自定义销量
            'accuracy' => $Data['accuracy'], //小数点精度
            'selling[JSON]' => $Data['selling'], //自定义售价
            'update_dat' => $date, //商品更新时间
        ];

        if (!empty($Data['gid'])) {
            /**
             * 成本变动数据写入
             */
            $Goods = $DB->get('goods', '*', ['gid' => (int)$Data['gid']]);
            if (!$Goods) {
                dies(-1, '商品不存在！');
            }
            $PriceChange = [];
            if ($Data['quantity'] == $Goods['quantity'] && (float)$Goods['money'] != (float)$SQL['money']) {
                global $date;
                $PriceChange[] = [
                    'type' => ($Goods['money'] < $SQL['money'] ? 1 : 2),
                    'money' => $SQL['money'],
                    'OriginalPrice' => $Goods['money'],
                    'name' => $Goods['name'],
                    'key' => false,
                    'date' => $date,
                    'time' => time(),
                    'gid' => $Goods['gid'],
                ];
            }

            $New = $SQL['specification_sku[JSON]'];
            $Used = json_decode($Goods['specification_sku'], true);
            if ($SQL['specification'] == 2 && $Goods['specification'] == 2 && count($New) >= 1 && count($Used) >= 1) {
                foreach ($Used as $key => $val) {
                    $Ne = $New[$key];
                    if ($val['money'] == '' || $Ne['money'] == '' || $val['quantity'] != $Ne['quantity']) {
                        continue;
                    }
                    if ($val['money'] != $Ne['money']) {
                        $PriceChange[] = [
                            'type' => ($val['money'] < $Ne['money'] ? 1 : 2),
                            'money' => $Ne['money'],
                            'OriginalPrice' => $val['money'],
                            'name' => $Goods['name'],
                            'key' => $key,
                            'date' => $date,
                            'time' => time(),
                            'gid' => $Goods['gid'],
                        ];
                    }
                }
            }
            if (count($PriceChange) >= 1) {
                GoodsMonitoring::ChangesCommodityPrices($PriceChange);
            }
            $Res = $DB->update('goods', $SQL, ['gid' => $Data['gid']]);
        } else {
            $sort = $DB->get('goods', ['sort'], [
                'ORDER' => [
                    'sort' => 'DESC'
                ],
                'LIMIT' => 1
            ]);
            $sort = $sort['sort'] + 1;
            $SQL['sort'] = $sort;
            $SQL['date'] = $date;
            $Res = $DB->insert('goods', $SQL);
        }

        if ($Res) {
            return [
                'code' => 1,
                'msg' => '操作成功！',
            ];
        }
        return [
            'code' => -1,
            'msg' => '操作失败！',
        ];
    }

    /**
     * @param $Gid
     * 编辑器取出商品数据
     */
    public static function GoodsData($Gid)
    {
        $DB = SQL::DB();
        $Goods = $DB->get('goods', '*', ['gid' => (int)$Gid]);

        if (!$Goods) {
            return [
                'code' => -1,
                'msg' => '商品不存在！'
            ];
        }

        $Uns = [
            'image',
            'extend',
            'specification_sku',
            'specification_spu',
        ];

        foreach ($Uns as $v) {
            if (empty($Goods[$v])) {
                $Goods[$v] = [];
                continue;
            }
            $Arrays = json_decode($Goods[$v], true);
            if (!$Arrays) {
                $Goods[$v] = [$Goods[$v]];
            } else {
                $Goods[$v] = $Arrays;
            }
        }

        $method = [];
        $methodArr = json_decode($Goods['method'], TRUE);
        for ($i = 0; $i < 7; $i++) {
            $method[$i] = (in_array(($i + 1), $methodArr, true) ? true : false);
        }
        $Goods['method'] = $method;

        if ((int)$Goods['specification'] == 2) {
            $Goods['specification'] = true;
        } else {
            $Goods['specification'] = false;
        }

        if ((int)$Goods['specification_type'] == 1) {
            $Goods['specification_type'] = true;
        } else {
            $Goods['specification_type'] = false;
        }

        if (!empty($Goods['selling'])) {
            $Goods['selling'] = json_decode($Goods['selling'], true);
        } else {
            $Goods['selling'] = [];
        }

        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Goods
        ];
    }

    /**
     * @param $Data
     * 对接请求分发
     */
    public static function DockingRequestDistribution($Data)
    {
        $Source = StringCargo::Docking($Data['Source']); //数据源
        $DataSo = $Source['InputField'][$Data['index']]; //参数源
        if (empty($Source)) {
            dies(-1, '参数缺失，无法操作！');
        }
        $path = SYSTEM_ROOT . 'lib/supply/' . $Source['ClassName'] . '.php';
        if (is_file($path)) {
            include_once $path;
        } else {
            dies(-1, '指定对接操作类不存在！,文件路径：' . $path);
        }
        $new = '\\lib\\supply\\' . $Source['ClassName'];
        if (!class_exists($new)) {
            dies(-1, '指定对接操作类不存在！，请检查：' . $new);
        }
        if (!method_exists($new, $Data['controller'])) {
            dies(-1, '指定对接操作类【' . $new . '】里面不存在【' . $Data['controller'] . '】方法，请检查！');
        }
        return $new::AdminOrigin($Data, $Source, $DataSo);
    }

    /**
     * @param $Data
     * 添加新用户
     */
    public static function UserAdd($Data)
    {
        preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $Data['username'], $arr_pr);
        if (empty($arr_pr[0])) {
            dies(-1, '登陆账号只可为英文或数字,并且长度要大于6小于18位！');
        }
        preg_match('/^[a-zA-Z0-9]{6,18}+$/u', $Data['password'], $arr_pr);
        if (empty($arr_pr[0]) && !empty($Data['value'])) {
            dies(-1, '用户登陆密码只可为英文或数字,并且长度要大于6小于18位！');
        }
        global $date, $conf;
        $DB = SQL::DB();

        $Vs = $DB->get('user', ['id'], [
            'OR' => [
                'qq' => (string)$Data['qq'],
                'username' => (string)$Data['username'],
            ]
        ]);
        if ($Vs) {
            dies(-1, 'QQ或登陆账号已经被用户[ ' . $Vs['id'] . ' ]绑定了！');
        }

        if (!empty($Data['mobile'])) {
            $Vs = $DB->get('user', ['id'], [
                'mobile' => $Data['mobile'],
            ]);
            if ($Vs) {
                dies(-1, '手机号已被用户[ ' . $Vs['id'] . ' ]绑定了！');
            }
        }

        $Res = $DB->insert('user', [
            'user_idu' => md5(json_encode($Data)),
            'username' => $Data['username'],
            'password' => md5($Data['password']),
            'superior' => 0,
            'ip' => '127.0.0.1',
            'image' => 'https://q4.qlogo.cn/headimg_dl?dst_uin=' . $Data['qq'] . '&spec=100',
            'qq' => $Data['qq'],
            'name' => $Data['name'],
            'state' => 1,
            'grade' => ((int)$Data['grade'] <= 0 ? $conf['userdefaultgrade'] : (int)$Data['grade']),
            'money' => ((float)$Data['money'] <= 0 ? 0 : (float)$Data['money']),
            'currency' => ((int)$Data['currency'] <= 0 ?: (int)$Data['currency']),
            'found_date' => $date,
            'recent_time' => $date,
            'mobile' => $Data['mobile'],
        ]);

        if ($Res) {
            $ID = $DB->id();
            userlog('账号创建', '客服在后台创建了新账户：' . $ID, $ID);
            return [
                'code' => 1,
                'msg' => '用户创建成功，新用户ID为：' . $ID . '！',
            ];
        }

        return [
            'code' => -1,
            'msg' => '用户创建失败！',
        ];
    }

    /**
     * @param $uid
     * 收益，消费，充值，计算！
     * 暂不开放
     */
    public static function UserDetail($uid)
    {
        global $times;

        $DB = SQL::DB();
        $Pay = []; //充值

        $Pay[] = $DB->sum('pay', 'money', [
                'uid' => $uid,
                'state' => 1,
                'gid' => -1,
                'addtime[>=]' => $times
            ]) - 0; //今日充值

        $Pay[] = $DB->sum('pay', 'money', [
                'uid' => $uid,
                'state' => 1,
                'gid' => -1,
            ]) - 0; //累计充值

        return [
            'Pay' => $Pay,
        ];
    }

    /**
     * 获取列表
     */
    public static function DockingList($Data)
    {
        if (empty($_SESSION['DockingList'])) {
            dies(-1, '获取失败,没有任何日志!');
        }

        $List = array_reverse($_SESSION['DockingList']);

        $Array = [];

        $Page = ($Data['page'] - 1) * $Data['limit'];
        $Limit = ($Page + $Data['limit']);
        foreach ($List as $key => $value) {
            if ($key < $Page || $key >= $Limit) {
                continue;
            }
            foreach ($value as $k => $v) {
                if (strstr($v['data'], '<p class="jump"><b id="wait">3</b> 秒后页面将自动跳转</p>')) {
                    $v['data'] = [jiuwu::error($v['data'])];
                } else if (empty($v['data'])) {
                    $v['data'] = ['对接失败,无返回信息！'];
                } else {
                    $v['data'] = self::unicodeDecode($v['data']);
                }

                /*$v['data'] = json_encode($v['data']);
                $v['post'] = json_encode($v['post']);*/

                $v['date'] = $k;
                $Array[($key + 1)] = $v;
            }
        }

        if (count($Array) == 0) {
            dies(-1, '没有更多了');
        }

        return [
            'page' => $Page,
            'limit' => $Limit,
            'code' => 1,
            'msg' => '对接日志获取成功！',
            'data' => $Array,
        ];
    }

    /**
     * @param $unicode_str
     * @return mixed|string[]
     * 对接返回数据解析
     */
    public static function unicodeDecode($unicode_str)
    {
        if (empty($unicode_str)) return ['对接失败,无返回信息！'];
        $a = json_decode($unicode_str, TRUE);
        if (empty($a)) {
            $b = xiaochu_de($unicode_str);
            return (!is_null(json_decode($b)) ? self::unicodeDecode($b) : $unicode_str);
        }
        return $a;
    }

    /**
     * @param $Data
     * @return array
     * App列表
     */
    public static function AppList($Data)
    {
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        $DB = SQL::DB();

        if (!empty($Data['name'])) {
            $SQL['name[~]'] = $Data['name'];
        }

        if (!empty($Data['uid'])) {
            $SQL['uid'] = $Data['uid'];
        }

        $Res = $DB->select('app', '*', $SQL);
        if (!$Res) {
            dies(-1, ' 没有更多了');
        }

        $DataList = [];
        foreach ($Res as $v) {
            $DataList[] = $v;
        }

        return [
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $DataList,
        ];
    }

    /**
     * @param $Data
     * @return array
     * 主机列表
     */
    public static function HostList($Data)
    {
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        if (!empty($Data['sid'])) {
            $SQL['server'] = $Data['sid'];
        }

        $DB = SQL::DB();

        if (!empty($Data['name'])) {
            $SQL['OR'] = [
                'identification' => $Data['name'],
                'id' => $Data['name'],
                'username[~]' => $Data['name'],
                'server' => $Data['name'],
                'uid' => $Data['name'],
            ];
        }

        $Res = $DB->select('mainframe', '*', $SQL);
        if (!$Res) {
            dies(-1, ' 没有更多了');
        }

        $DataList = [];
        foreach ($Res as $v) {
            $v['currentsize'] -= 0;
            $v['sizespace'] -= 0;
            $DataList[] = $v;
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $DataList,
        ]);
    }

    /**
     * @param $Data
     * 修改主机空间
     */
    public static function ModifyHostSpace($Data)
    {
        test(['RenewPrice|e', 'uid|e', 'id|e', 'maxdomain|e', 'concurrencyall|e', 'endtime|e', 'traffic|e', 'filesize|e', 'sizespace|e'], '请提交完整！');

        $DB = SQL::DB();
        unset($_POST['act'], $_POST['id']);


        $Res = $DB->update('mainframe', $_POST, [
            'id' => $Data['id'],
        ]);

        if ($Res) {
            $Dv = BTCO::DataV($Data['id']);
            $MainframeData = $Dv['MainframeData'];
            if ($MainframeData['type'] == 2) {
                dies(1, '修改成功！');
            }

            BTCO::Getendtime($MainframeData['siteId'], $MainframeData['endtime']);
            BTCO::GetSetLimitNet($MainframeData['siteId'], $MainframeData);

            dies(1, '修改成功！');
        }

        dies(-1, '修改失败！');
    }

    /**
     * @param $Data
     * 创建主机空间
     */
    public static function CreateHostSpace($Data)
    {
        test(['RenewPrice|e', 'day|e', 'server|e'], '请提交完整！');

        global $date;
        $DB = SQL::DB();
        $identification = md5(random_int(99999, 9999999) . '晴玖天商城系统' . time());

        $SQL = [
            'oid' => -1,
            'identification' => $identification,
            'uid' => ($Data['uid'] <= 0 ? -1 : (int)$Data['uid']),
            'RenewPrice' => (float)$Data['RenewPrice'],
            'server' => $Data['server'],
            'sql_user' => '待生成',
            'sql_name' => '待生成',
            'sql_pass' => '待生成',
            'maxdomain' => $Data['maxdomain'],
            'concurrencyall' => $Data['concurrencyall'],
            'concurrencyip' => $Data['concurrencyip'],
            'traffic' => $Data['traffic'],
            'filesize' => $Data['filesize'],
            'username' => $Data['username'],
            'sizespace' => $Data['sizespace'],
            'password' => (empty($_POST['password']) ? '' : md5($_POST['password'])),
            'endtime' => date("Y-m-d H:i:s", strtotime(" + " . $Data['day'] . " day")),
            'addtime' => $date,
        ];


        if (!empty($Data['username'])) {
            $Vs1 = $DB->get('mainframe', ['id'], [
                'username' => (string)$Data['username'],
            ]);

            if ($Vs1) {
                dies(-1, '此用户名已被其他主机占用！');
            }
        }

        $Vs = $DB->get('server', ['id'], [
            'id' => (int)$Data['server'],
        ]);

        if (!$Vs) {
            dies(-1, '服务器不存在！');
        }
        if ($SQL['uid'] > 0) {
            $Vs = $DB->get('user', ['id'], [
                'id' => (int)$SQL['uid'],
            ]);

            if (!$Vs) {
                dies(-1, '用户不存在！');
            }
        }

        $Res = $DB->insert('mainframe', $SQL);

        if ($Res) {
            if ($SQL['uid'] > 0) {
                userlog('主机创建', '官方客服在后台为您创建了一个主机空间，主机ID为：' . $DB->id(), $SQL['uid']);
            }
            dies(1, '主机创建成功，请前往主机管理列表查看，注：主机需要手动激活才可使用！');
        }

        dies(-1, '创建失败！，请重新尝试！');
    }

    /**
     * @param $id
     * 清理服务器内存碎片
     */
    public static function ReMemory($id)
    {
        global $date;
        $DB = SQL::DB();

        $Server = $DB->get('server', '*', [
            'id' => (int)$id,
            'endtime[>]' => $date,
        ]);
        if (!$Server) {
            dies(-1, '服务器不存在或已到期');
        }

        //初始化
        BTC::Conf($Server);


        $Data = BTCO::Get('/system?action=ReMemory', []);

        if (isset($Data['status']) && $Data['status'] == false) {
            BTCO::WriteException($id, $Data['msg']);
            dies(-1, $Data['msg']);
        }

        if (!isset($Data['memRealUsed'])) {
            dies(-1, '数据获取失败');
        }
        dier([
            'code' => 1,
            'msg' => '内存碎片释放成功',
            'data' => $Data,
        ]);
    }

    /**
     * @param $id
     * 监听服务器状态
     */
    public static function ServerStatusMonitoring($id)
    {
        global $date;
        $DB = SQL::DB();

        $Server = $DB->get('server', '*', [
            'id' => (int)$id,
            'endtime[>]' => $date,
        ]);
        if (!$Server) {
            dies(-1, '服务器不存在或已到期');
        }

        //初始化
        BTC::Conf($Server);

        $Data = BTCO::Get('/system?action=GetNetWork', []);

        if (isset($Data['status']) && $Data['status'] == false) {
            BTCO::WriteException($id, $Data['msg']);
            dies(-1, $Data['msg']);
        }

        if (!isset($Data['cpu'])) {
            dies(-1, '数据获取失败');
        }

        /**
         * 磁盘大小计算
         */
        $DiskSize = 0;
        $DiskOccupy = 0;
        foreach ($Data['disk'] as $val) {
            $DiskSize += str_replace('G', '', $val['size'][0]);
            $DiskOccupy += str_replace('G', '', $val['size'][1]);
        }

        $SQL = [
            'cpuNum' => $Data['cpu'][1],
            'memTotal' => $Data['mem']['memTotal'],
            'system' => $Data['system'],
            'version' => $Data['version'],
            'time' => $Data['time'],
            'DiskSize' => $DiskSize,
            'site_total' => $Data['site_total'],
            'database_total' => $Data['database_total'],
            'ftp_total' => $Data['ftp_total'],
            'name' => $Data['title'],
        ];

        $DB->update('server', [
            'data[JSON]' => $SQL,
            'name' => (empty($Data['title']) ? '无名称' : $Data['title']),
            'error' => null,
        ], [
            'id' => $id,
        ]);

        $Base = [
            'CPU' => [
                'Total' => $Data['cpu'][1],
                'Occupy' => $Data['cpu'][0],
            ],
            'Disk' => [
                'Total' => $DiskSize,
                'Occupy' => $DiskOccupy,
            ],
            'Memory' => [
                'Total' => $Data['mem']['memTotal'],
                'Occupy' => $Data['mem']['memRealUsed'],
            ],
            'Load' => [
                'Total' => $Data['load']['limit'],
                'Occupy' => $Data['load']['one'],
            ]
        ];


        dier([
            'code' => 1,
            'msg' => '数据获取成功！',
            'data' => $SQL,
            'RealTime' => $Base,
        ]);
    }

    /**
     * @param string $Name
     * 取出可用服务器列表
     */
    public static function ServerList($Name = '')
    {
        $DB = SQL::DB();
        $SQL = [];
        if (!empty($Name)) {
            $SQL['name[~]'] = $Name;
            $SQL['url[~]'] = $Name;
        }
        $Res = $DB->select('server', '*', $SQL);

        if (!$Res) {
            dies(-1, ' 没有更多了');
        }

        $DataList = [];
        foreach ($Res as $v) {
            if (empty($v['data'])) {
                $v['data'] = [
                    'cpuNum' => '1',
                    'memTotal' => '1024',
                    'system' => 'CentOS',
                    'version' => '7.6.0',
                    'time' => '1',
                    'DiskSize' => '10',
                    'site_total' => 0,
                    'database_total' => 0,
                    'ftp_total' => 0,
                    'name' => '名称载入中...',
                ];
            } else {
                $v['data'] = json_decode($v['data'], true);
            }

            $v['count'] = $DB->count('mainframe', [
                    'server' => $v['id'],
                ]) - 0;

            $DataList[] = $v;
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $DataList,
        ]);
    }

    /**
     * @param $Data
     * 订单导出
     */
    public static function ExportOrders($_QET)
    {
        test(['AdjustState|e', 'date|e', 'gid|e', 'state|e'], '请提交完整参数！');
        $DB = SQL::DB();
        $SQL = [
            'gid' => $_QET['gid'],
            'ORDER' => [
                'id' => 'DESC',
            ]
        ];

        if ((int)$_QET['state'] != -1) {
            $SQL['state'] = (int)$_QET['state'];
        }

        if (!strstr($_QET['date'], ' Split ')) {
            dies(-1, '时间格式异常！');
        }
        $Arr = explode(' Split ', $_QET['date']);
        $SQL['addtitm[>]'] = $Arr[0];
        $SQL['addtitm[<]'] = $Arr[1];

        $OrderList = $DB->select('order', [
            'id', 'num', 'input', 'state'
        ], $SQL);

        if (count($OrderList) == 0) {
            dies(-1, '无可导出订单！');
        }

        $Goods = $DB->get('goods', ['name', 'quantity'], [
            'gid' => (int)$_QET['gid'],
        ]);

        $Data = [];
        $Data[] = '商品编号：' . $_QET['gid'] . '
导出订单数量：' . count($OrderList) . '条';
        $Data[] = '本次订单导出时间段：
' . implode('=>', $Arr);
        $Data[] = '------------------------------------------';
        foreach ($OrderList as $value) {
            $Input = json_decode($value['input'], true);

            if ($Goods) {
                $Data[] = implode('---', $Input) . ' | ' . $value['num'] * $Goods['quantity'];
            } else {
                $Data[] = implode('---', $Input) . ' | ' . $value['num'];
            }

            if ($_QET['AdjustState'] != 1 && $value['state'] != 5 && $value['state'] != 6 && $value['state'] != 7) {
                switch ($_QET['AdjustState']) {
                    case 2: //待处理
                        $DB->update('order', [
                            'state' => 2,
                        ], [
                            'id' => $value['id']
                        ]);
                        break;
                    case 3: //处理中
                        $DB->update('order', [
                            'state' => 4,
                        ], [
                            'id' => $value['id']
                        ]);
                        break;
                    case 4: //已发货
                        $DB->update('order', [
                            'state' => 1,
                        ], [
                            'id' => $value['id']
                        ]);
                        break;
                }
            }
        }

        dier([
            'code' => 1,
            'msg' => '本次成功导出：' . count($OrderList) . '条订单！',
            'name' => $_QET['gid'] . '-' . date("Y-m-d-H:i:s"),
            'data' => $Data
        ]);
    }

    /**
     * 商品价格监控支持类型
     */
    public static function MonitoredType()
    {
        $Data = StringCargo::Docking();
        $Arr = [];
        foreach ($Data as $k => $v) {
            if ($v['PriceMonitoring'] != 1) {
                continue;
            }
            $Arr[] = $v['name'];
        }
        dier([
            'code' => 1,
            'msg' => '可监控货源类型获取成功',
            'data' => implode(' , ', $Arr),
        ]);
    }

    /**
     * @param $Data
     * 创建 / 修改服务器
     */
    public static function CreateServer($Data)
    {
        global $date, $_QET;
        $DB = SQL::DB();
        test(['content|e', 'endtime|e', 'path|e', 'root_directory|e', 'sqlurl|e', 'token|e', 'url|e', 'domain|e']);

        $SQL = [
            'content' => trim($Data['content']),
            'path' => trim($Data['path']),
            'root_directory' => trim($Data['root_directory']),
            'sqlurl' => trim($Data['sqlurl']),
            'system' => trim($Data['system']),
            'token' => trim($Data['token']),
            'type' => trim($Data['type']),
            'domain' => trim($Data['domain']),
            'HostSpace' => trim($Data['HostSpace']),
            'url' => trim($Data['url']),
            'addtime' => $date,
            'endtime' => $Data['endtime'],
        ];

        if (!empty($Data['sid'])) {
            $Vs = $DB->get('server', ['id'], [
                'OR' => [
                    'token' => (string)$SQL['token'],
                    'url' => (string)$SQL['url'],
                ],
                'id[!]' => (int)$Data['sid'],
            ]);

            if ($Vs) {
                dies(-1, '对应的服务器已经绑定过了！');
            }
        } else {
            $Vs = $DB->get('server', ['id'], [
                'OR' => [
                    'token' => (string)$SQL['token'],
                    'url' => (string)$SQL['url'],
                ]
            ]);

            if ($Vs) {
                dies(-1, '服务器不可以重复添加！');
            }
        }

        /**
         * 测试
         */
        $Data = Server::Test($SQL);
        $SQL['name'] = $Data['title'];

        if (empty($_QET['sid'])) {
            $Res = $DB->insert('server', $SQL);
            if ($Res) {
                $id = $DB->id();
                $DiskSize = 0;
                foreach ($Data['disk'] as $val) {
                    $DiskSize += str_replace('G', '', $val['size'][0]);
                }
                $DB->update('server', [
                    'data[JSON]' => [
                        'cpuNum' => $Data['cpu'][1],
                        'memTotal' => $Data['mem']['memTotal'],
                        'system' => $Data['system'],
                        'version' => $Data['version'],
                        'time' => $Data['time'],
                        'DiskSize' => $DiskSize,
                        'site_total' => $Data['site_total'],
                        'database_total' => $Data['database_total'],
                        'ftp_total' => $Data['ftp_total'],
                        'name' => $Data['title'],
                    ],
                    'name' => (empty($Data['title']) ? '无名称' : $Data['title']),
                    'error' => null,
                ], [
                    'id' => $id,
                ]);

                dies(1, '服务器添加成功！');
            } else {
                dies(-1, '添加失败，请重新尝试！');
            }
        } else {
            $Res = $DB->update('server', $SQL, [
                'id' => $_QET['sid'],
            ]);
            if ($Res) {
                $DiskSize = 0;
                foreach ($Data['disk'] as $val) {
                    $DiskSize += str_replace('G', '', $val['size'][0]);
                }
                $DB->update('server', [
                    'data[JSON]' => [
                        'cpuNum' => $Data['cpu'][1],
                        'memTotal' => $Data['mem']['memTotal'],
                        'system' => $Data['system'],
                        'version' => $Data['version'],
                        'time' => $Data['time'],
                        'DiskSize' => $DiskSize,
                        'site_total' => $Data['site_total'],
                        'database_total' => $Data['database_total'],
                        'ftp_total' => $Data['ftp_total'],
                        'name' => $Data['title'],
                    ],
                    'name' => (empty($Data['title']) ? '无名称' : $Data['title']),
                    'error' => null,
                ], [
                    'id' => $_QET['sid'],
                ]);

                dies(1, '服务器修改成功！');
            } else {
                dies(-1, '修改失败，请重新尝试！');
            }
        }
    }

    /**
     * 返回服务端对接配置数据
     * 返回供货商列表
     */
    public static function DockingDataServer()
    {
        CookieCache::read();
        global $date;
        /**
         * 检测是否添加了服务端对接通道
         */
        $DB = SQL::DB();
        $Get = $DB->get('shequ', ['id'], [
            'OR' => [
                'type' => 6,
                'class_name' => 'official',
            ]
        ]);
        if (!$Get) {
            //获取服务端货源站对接配置
            $SetGet = Curl::Get('/api/Supply/api', [
                'act' => 'SupplierData',
            ]);
            $SetGet = json_decode(xiaochu_de($SetGet), true);
            if (empty($SetGet) || $SetGet['code'] < 0) {
                dies(-1, '服务端对接配置数据获取失败，请手动在货源管理->添加货源 内添加服务端对接！' . $SetGet['msg']);
            }
            //写入对接配置
            $Res = $DB->insert('shequ', [
                'type' => -1,
                'url' => $SetGet['url'],
                'username' => $SetGet['token'],
                'secret' => 1,
                'pattern' => 1,
                'date' => $date,
                'class_name' => 'official'
            ]);
            if (!$Res) {
                dies(-1, '请手动在货源管理->添加货源 内添加服务端对接！');
            }
        }

        $Data = Curl::Get('/api/Supply/api', [
            'act' => 'SupplierList',
        ]);
        $Data = json_decode(xiaochu_de($Data), true);
        if (empty($Data) || $Data['code'] < 0) {
            dies(-1, '数据获取失败，请检查是否可以正常和服务端对接！' . $Data['msg']);
        }
        CookieCache::add($Data, 240);
        dier($Data);
    }

    /**
     * @param $Data
     * 快速添加商品！
     */
    public static function AddProductsQuickly($Data)
    {
        $StrCar = StringCargo::Docking($Data['class_name']);
        $path = SYSTEM_ROOT . 'lib/supply/' . $StrCar['ClassName'] . '.php';
        if (is_file($path)) {
            include_once $path;
        } else {
            dies(-1, '指定对接操作类不存在！,文件路径：' . $path);
        }

        $new = '\\lib\\supply\\' . $StrCar['ClassName'];
        if (!class_exists($new)) {
            dies(-1, '指定对接操作类不存在！，请检查：' . $new);
        }
        if (!method_exists($new, 'CommodityAnalysis')) {
            dies(-1, '指定对接操作类【' . $new . '】里面不存在【CommodityAnalysis】方法，请检查！');
        }
        $DB = SQL::DB();
        $sort = $DB->get('goods', ['sort'], [
            'ORDER' => [
                'sort' => 'DESC'
            ],
            'LIMIT' => 1
        ]);
        $Data['sort'] = $sort['sort'] + 1; //排序
        $Data['controller'] = 'CommodityAnalysis';
        $SQL = $new::AdminOrigin($Data);
        $Res = $DB->insert('goods', $SQL);
        if ($Res) {
            dies(1, '商品添加成功！');
        }
        dies(-1, '商品添加失败！');
    }

    /**
     * @param string $name
     * 商城海列表
     */
    public static function MyIslandList()
    {
        global $_QET;
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandList',
            'page' => $_QET['page'],
            'limit' => $_QET['limit']
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '数据获取失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $data
     * 生成充值卡
     */
    public static function RechargeAdd($data)
    {
        global $date;
        $DB = SQL::DB();
        $SQL = [];
        $text = '';
        for ($i = 0; $i < $data['count']; $i++) {
            $token = md5(time() . random_int(1000, 999999));
            $text .= $token . '<br>';
            $SQL[] = [
                'name' => $data['name'],
                'type' => $data['type'],
                'token' => $token,
                'money' => $data['money'],
                'addtime' => $date,
            ];
        }
        $Res = $DB->insert('recharge', $SQL);
        if ($Res) {
            dies(1, '本次成功生成了' . count($SQL) . '张充值卡！<hr>' . $text);
        }
        dies(-1, '卡密生成失败！');
    }

    /**
     * @param $Data
     * 发起评论
     */
    public static function MyIslandInitiateComments($Data)
    {
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandInitiateComments',
            'id' => $Data['id'],
            'msg' => $Data['msg'],
            'score' => $Data['score'],
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '获取失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $id
     * 评论列表
     */
    public static function MyIslandCommentList($id)
    {
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandCommentList',
            'id' => $id,
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '获取失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    public static function MyPayList()
    {
        global $accredit;
        $SetGet = Curl::Get('/api/Pay/index', [
            'act' => 'PayData',
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '获取失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $money
     * 创建充值订单
     */
    public static function MyPayOrder($money)
    {
        $SetGet = Curl::Get('/api/Pay/index', [
            'act' => 'OrderAdd',
            'money' => $money,
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '创建失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    public static function MyPayTs($Data)
    {
        $SetGet = Curl::Get('/api/Pay/index', [
            'act' => 'Pay',
            'order' => $Data['order'],
            'key' => $Data['key'],
            'type' => $Data['type']
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '获取失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $order
     * 获取付款地址
     */
    public static function MyPay($order)
    {
        $SetGet = Curl::Get('/api/Pay/index', [
            'act' => 'OrderLog',
            'order' => $order,
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '获取失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $id
     * @param $type
     * @param $state
     * 点赞或踩其他站点
     */
    public static function MyIslandGiveThumbs($id, $type, $state)
    {
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandGiveThumbs',
            'id' => $id,
            'type' => $type,
            'state' => $state,
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            if ($SetGet['code'] == -2) {
                dier([
                    'code' => -2,
                    'msg' => $SetGet['msg'],
                    'type' => $SetGet['type'],
                ]);
            }
            dies(-1, '操作失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * 每日签到
     */
    public static function MyIslandSignDaily()
    {
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandSignDaily',
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '签到失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $money
     * 购买热度
     */
    public static function MyIslandPurchase($money)
    {
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandPurchase',
            'money' => $money,
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '切换失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $state
     * 调整商城海状态
     */
    public static function MyIslandState($state)
    {
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandState',
            'state' => $state,
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '切换失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * 同步数据
     */
    public static function MyIslandSynchronization()
    {
        $DB = SQL::DB();

        $GoodsCount = $DB->count('goods');
        $OrderCount = $DB->count('order');
        $UserCount = $DB->count('user');
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandSynchronization',
            'data' => [
                $GoodsCount, $OrderCount, $UserCount
            ],
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '同步失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $data
     * 修改商城海数据
     */
    public static function MyIslandSet($data)
    {
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIslandSet',
            'data' => $data['data'],
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '修改失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param int $type
     * 获取的的商城海数据
     */
    public static function MyIsland()
    {
        $SetGet = Curl::Get('/api/Supply/api', [
            'act' => 'MyIsland',
        ]);

        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '数据获取失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $path
     * @param $Json
     * 保存指定模板的配置文件
     */
    public static function TemJsonSet($path, $Json)
    {
        return file_put_contents($path, json_encode(json_decode($Json, true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * @param $date
     * 保存数据
     */
    public static function TemConfSet($date)
    {
        $DB = SQL::DB();
        $Res = $DB->update('config', [
            'V' => $date['value'],
        ], [
            'K' => $date['field']
        ]);
        if ($Res) {
            (new \config)->unset_cache();
            if ($date['json'] !== false) {
                self::TemJsonSet(ROOT . "template/" . $date['value'] . "/conf.json", $_POST['json']);
            }
            dies(1, '保存成功');
        } else {
            dies(-1, '保存失败！');
        }
    }

    /**
     * 获取网站模板数据
     */
    public static function TemData()
    {
        global $conf;
        $template_arr = for_dir(ROOT . "template/");
        $Data = [
            'PC' => [],
            'M' => [],
        ];
        $Data['PC'][-1] = false;
        $Data['PC'][-2] = false;
        $Data['M'][-1] = false;
        foreach ($template_arr as $value) {
            $file_path = ROOT . "template/" . $value . "/conf.json";
            if (!file_exists($file_path)) {
                $Data['PC'][$value] = false;
                $Data['M'][$value] = false;
            } else {
                $Json = json_decode(file_get_contents($file_path), true);
                if (!empty($Json)) {
                    if ((int)$Json['type'] == 1) {
                        $Data['PC'][$value] = $Json;
                    } else if ((int)$Json['type'] == 2) {
                        $Data['M'][$value] = $Json;
                    } else {
                        $Data['PC'][$value] = $Json;
                        $Data['M'][$value] = $Json;
                    }
                } else {
                    $Data['PC'][$value] = false;
                    $Data['M'][$value] = false;
                }
            }
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $Data,
            'conf' => [
                'template' => $conf['template'],
                'template_m' => $conf['template_m'],
                'cdnpublic' => $conf['cdnpublic'],
                'cdnserver' => $conf['cdnserver'],
                'background' => $conf['background'],
                'banner' => $conf['banner'],
            ]
        ]);
    }

    /**
     * @param $type
     * 广告列表
     */
    public static function BannerList($type)
    {
        $SetGet = Curl::Get('/api/Banner/index', [
            'act' => 'List',
            'type' => $type,
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            dies(-1, '数据获取失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    public static function BannerGiveThumbs($id, $type, $state)
    {
        $SetGet = Curl::Get('/api/Banner/index', [
            'act' => 'Admire',
            'id' => $id,
            'type' => $type,
            'state' => $state,
        ]);
        $SetGet = json_decode(xiaochu_de($SetGet), true);
        if (empty($SetGet) || $SetGet['code'] < 0) {
            if ($SetGet['code'] == -2) {
                dier([
                    'code' => -2,
                    'msg' => $SetGet['msg'],
                    'type' => $SetGet['type'],
                ]);
            }
            dies(-1, '操作失败，' . $SetGet['msg']);
        }
        dier($SetGet);
    }

    /**
     * @param $Data
     * 创建限价秒杀活动
     */
    public static function AddSeckill($Data)
    {
        global $date;
        $DB = SQL::DB();
        $Goods = $DB->get('goods', ['gid'], [
            'gid' => (int)$Data['gid']
        ]);
        if (!$Goods) {
            dies(-1, '需要参加活动的商品不存在,请前往商品列表获取商品GID');
        }
        if (strtotime($Data['start_time']) >= strtotime($Data['end_time'])) {
            dies(-1, '活动开启时间，不能大于或等于活动结束时间');
        }
        unset($Data['act']);
        if ($Data['sid'] >= 1) {
            $Sid = $Data['sid'];
            unset($Data['sid']);
            $Res = $DB->update('seckill', $Data, [
                'id' => $Sid
            ]);
        } else {
            $Vs = $DB->get('seckill', ['gid'], [
                'gid' => (int)$Data['gid']
            ]);
            if ($Vs) {
                dies(-1, '此商品【' . $Data['gid'] . '】已经创建过活动了，无法重复创建，可前往活动列表找到此商品，修改活动内容！');
            }
            $Data['addtime'] = $date;
            $Res = $DB->insert('seckill', $Data);
        }
        if ($Res) {
            dies(1, '操作成功');
        } else {
            dies(-1, '操作失败');
        }
    }

    /**
     * @param $Data
     * 商品限购秒杀活动列表
     */
    public static function SeckillList($Data)
    {
        global $date;
        $Limit = (int)$Data['limit'];
        $Page = ($Data['page'] - 1) * $Limit;

        $SQL = [
            'ORDER' => [
                'id' => 'DESC',
            ],
            'LIMIT' => [$Page, $Limit],
        ];

        $DB = SQL::DB();

        if (!empty($Data['name'])) {
            $SQL['OR'] = [
                'id' => $Data['name'],
                'gid' => $Data['name'],
            ];
        }

        $Res = $DB->select('seckill', '*', $SQL);
        if (!$Res) {
            dies(-1, ' 没有更多了');
        }

        $DataList = [];
        foreach ($Res as $v) {

            $v['attend'] = $DB->count('order', [
                    'gid' => $v['gid'],
                    'addtitm[>]' => $v['start_time'],
                    'addtitm[<]' => $v['end_time']
                ]) - 0;

            if ($v['attend'] >= $v['astrict']) {
                $v['attend'] = $v['astrict'];
            }

            $T1 = strtotime($v['start_time']);
            $T2 = strtotime($v['end_time']);
            if ($T1 >= time()) {
                $v['start_time'] = Sec2Time($T1 - time()) . '后开始';
                $v['end_time'] = '活动未开始';
                $v['state'] = 2;
            } else if ($T2 - time() <= 0) {
                $v['start_time'] = '活动已结束';
                $v['end_time'] = '活动已结束';
                $v['state'] = -1;
            } else {
                $v['start_time'] = '活动已开始';
                $v['end_time'] = Sec2Time($T2 - time()) . '后结束';
                $v['state'] = 1;
            }

            $DataList[] = $v;
        }

        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'data' => $DataList,
        ]);
    }
}
