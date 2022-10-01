<?php
// +----------------------------------------------------------------------
// | Project: 晴天，QQ：1186258278
// +----------------------------------------------------------------------
// | Creation: 2021/12/22 12:08
// +----------------------------------------------------------------------
// | Filename: ProductsExchange.php
// +----------------------------------------------------------------------
// | Explain: 商品卡密兑换类
// +----------------------------------------------------------------------
namespace lib\supply;

use login_data;
use Medoo\DB\SQL;
use query;

class ProductsExchange
{
    /**
     * @var bool
     * 功能开关，设置为false关闭卡密兑换功能
     */
    public static $State = true;

    /**
     * @param $DataBuy //下单信息
     * @param $Token
     * 下单信息提交
     */
    public static function Ordersubmit($DataBuy, $Token)
    {
        if (!self::$State) {
            dies(-1, '卡密商品兑换功能已关闭，请联系站长处理！');
        }
        global $date;
        $DB = SQL::DB();
        $Token = $DB->get('cash_card', '*', [
            'token' => $Token,
            'state' => 1
        ]);

        if (!$Token) {
            dies(-1, '卡密不存在，或已经被使用了！');
        }

        $User = login_data::user_data();
        if (!$User) {
            $User = -1;
        } else {
            $User = $User['id'];
        }

        $Goods = $DB->get('goods', '*', [
            'gid' => $Token['gid'],
        ]);

        if (!$Goods) {
            dies(-1, '商品不存在！');
        }
        $Goods = Order::VerifyBuy($DataBuy['gid'], $DataBuy['num'], $DataBuy['data'], $User);
        $DataBuy['data'] = $Goods['InputData'];
        $Money = $Goods['money'] * $Goods['num'];

        /**
         * 卡密消费
         */
        $Res = $DB->update('cash_card', [
            'uid' => $User,
            'state' => 2,
            'endtime' => $date,
        ], [
            'id' => $Token['id'],
        ]);
        if (!$Res) {
            dies(-1, '订单状态调整失败，请重新提交订单信息！');
        }
        userlog('卡密兑换', ($User === -1 ? '游客' : '用户：' . $User) . '于' . $date . '使用了商品兑换卡兑换了商品,卡号为：' . $Token . '，对应的商品名称为：' . $Goods['gid'], $User);
        $OrData = Order::OrderAwait(-1, -1, '卡密兑换[免费]', -1, $User, $DataBuy, 0, $Money);
        if (!$OrData) dies(-1, '订单创建失败,无法提交！');
        mkdirs(ROOT . 'includes/extend/log/Order/');
        query::OrderCookie($OrData['order']);
        $DB->update('cash_card', [
            'oid' => $OrData['id'],
        ], [
            'id' => $Token['id'],
        ]);
        $Order = Order::OrderSubmit($OrData, $Goods);
        if ($Order) {
            OrderStatus($Order);
        } else {
            dies(-1, '订单创建失败,请联系管理员处理！');
        }
    }

    /**
     * @return
     * 安装指定字段
     */
    public static function InstallSql()
    {
        $DB = SQL::DBS();
        $SQL = "CREATE TABLE `sky_cash_card`
(
    `id`      int(255) NOT NULL AUTO_INCREMENT,
    `gid`     int(255) NOT NULL COMMENT '商品ID',
    `oid`     int(255) NOT NULL COMMENT '订单id',
    `token`   varchar(255) NOT NULL,
    `uid`     int(255) NOT NULL COMMENT '用户ID',
    `state`   int(255) NOT NULL DEFAULT '1' COMMENT '1未使用，2已使用',
    `addtime` datetime     NOT NULL,
    `endtime` datetime     NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        if ($DB->query($SQL)) {
            dies(1, '数据安装成功，请刷新页面', 2);
        } else {
            dies(-1, '请勿重复安装数据哦！', 2);
        }
    }

    /**
     * @param $Goods
     * @param $Count
     * 添加商品卡密
     */
    public static function AddCardSecret($Goods = 1, $Count = 10)
    {
        global $date;
        $SQL = [];
        $Token = [];

        if (!SQL::DB()->get('goods', ['gid'], [
            'gid' => $Goods,
        ])) {
            dies(-1, '商品不存在，请提交正确的商品ID！');
        }

        for ($i = 0; $i < $Count; $i++) {
            $uuid = self::uuid();
            $SQL[] = [
                'gid' => $Goods,
                'oid' => -1,
                'token' => $uuid,
                'uid' => -1,
                'state' => 1,
                'addtime' => $date,
                'endtime' => '',
            ];
            $Token[] = $uuid;
        }
        $Res = SQL::DB()->insert('cash_card', $SQL);
        if ($Res) {
            dier([
                'code' => 1,
                'msg' => '卡密添加成功,本次成功添加了' . $Count . '张商品兑换卡！',
                'data' => $Token
            ]);
        } else {
            dies(-1, '卡密添加失败，请重新尝试！');
        }
    }

    /**
     * @param $Gid //商品ID
     * @param $Type //1全部，2已使用，3未使用
     * 兑换卡数量
     */
    public static function CardCountGoods($Gid = '', $Type = 1)
    {
        $DB = SQL::DB();
        $SQL = [];
        if (!empty($Gid)) {
            $SQL['gid'] = $Gid;
        }
        switch ($Type) {
            case 2:
                $SQL['state'] = 2;
                break;
            case 3:
                $SQL['state'] = 1;
                break;
        }
        $Count = $DB->count('cash_card', $SQL);
        dier([
            'code' => 1,
            'msg' => '数据获取成功',
            'count' => $Count,
        ]);
    }

    /**
     * @param $Page
     * @param $Limit
     * @param $Gid //商品ID
     * @param $Type //1全部，2已使用，3未使用
     * 商品卡密列表
     */
    public static function CardListGoods($Page = 1, $Limit = 10, $Gid = '', $Type = 1)
    {
        $DB = SQL::DB();

        $Page = ($Page - 1) * $Limit;

        $SQL = [
            'LIMIT' => [$Page, $Limit],
        ];

        if (!empty($Gid)) {
            $SQL['cash_card.gid'] = $Gid;
        }

        switch ($Type) {
            case 2:
                $SQL['cash_card.state'] = 2;
                break;
            case 3:
                $SQL['cash_card.state'] = 1;
                break;
        }

        $Res = $DB->select('cash_card', [
            '[>]goods' => ['gid' => 'gid'],
        ], [
            'cash_card.id',
            'cash_card.gid',
            'cash_card.oid',
            'cash_card.uid',
            'cash_card.token',
            'goods.name',
            'goods.money',
            'cash_card.state',
            'cash_card.addtime',
            'cash_card.endtime',
        ], $SQL);

        if ($Res) {
            dier([
                'code' => 1,
                'msg' => '数据获取成功',
                'data' => $Res
            ]);
        } else {
            dies(-1, '数据获取失败！');
        }
    }

    /**
     * 生成独立的html部署文件
     */
    public static function HtmlGoodsCard()
    {
        global $date;
        ob_start();
        include SYSTEM_ROOT . 'lib/supply/ProductsExchange.TP';
        $data = ob_get_contents();
        ob_clean();
        file_put_contents(ROOT . 'includes/extend/log/Home/index.html', $data);
        ob_end_clean();
        unset($date);
        if (is_file(ROOT . 'includes/extend/log/Home/index.html')) {
            dier([
                'code' => 1,
                'msg' => '独立部署文件生成成功，将此文件上传至任意站点，或本地访问均可对接当前站点！用于兑换商品卡！',
                'url' => href(2) . ROOT_DIR . 'includes/extend/log/Home/index.html',
            ]);
        } else dies(-1, '文件创建失败！');
    }

    /**
     * @param $Array
     * @param $Type //1根据前一个参数删除，2删除全部已使用，3删除全部未使用，4删除全部！
     * 删除商品卡密
     */
    public static function DeleteGoodsCard($Array, $Type = 1)
    {
        $SQL = [];
        switch ($Type) {
            case 2:
                $SQL['state'] = 2;
                break;
            case 3:
                $SQL['state'] = 1;
                break;
            case 4:

                break;
            default:
                $SQL['id'] = $Array;
                break;
        }
        $DB = SQL::DB();
        $Res = $DB->delete('cash_card', $SQL);
        if ($Res) {
            dies(1, '删除成功！');
        } else {
            dies(-1, '删除失败！');
        }
    }

    /**
     * @param $Id
     * @param $Data
     * 编辑商品卡密
     */
    public static function EditGoodsCard($Id, $Data)
    {
        $DB = SQL::DB();
        $Res = $DB->update('cash_card', $Data, [
            'id' => $Id,
        ]);
        if ($Res) {
            dies(1, '修改成功！');
        } else {
            dies(-1, '修改失败！');
        }
    }

    /**
     * @return string
     * 生成Guid唯一码
     */
    public static function uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
        return $uuid;
    }
}
