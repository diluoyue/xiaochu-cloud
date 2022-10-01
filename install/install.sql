SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `sky_app`;
CREATE TABLE `sky_app`
(
    `id`         int(255) NOT NULL AUTO_INCREMENT,
    `uid`        int(255)       DEFAULT NULL COMMENT 'uid',
    `TaskID`     int(255)       DEFAULT '-1' COMMENT '任务ID',
    `TaskMsg`    text COMMENT '任务状态',
    `name`       varchar(255)   DEFAULT NULL,
    `url`        varchar(255)   DEFAULT NULL,
    `content`    text COMMENT 'App介绍说明',
    `state`      int(2)         DEFAULT '4' COMMENT '1、成功，2、正在打包，3、打包失败，4、待打包',
    `theme`      varchar(255)   DEFAULT NULL,
    `load_theme` varchar(255)   DEFAULT NULL,
    `money`      decimal(12, 2) DEFAULT '2.00' COMMENT '生成消耗金额',
    `icon`       varchar(255)   DEFAULT NULL,
    `background` varchar(255)   DEFAULT NULL,
    `download`   text,
    `endtime`    datetime       DEFAULT NULL,
    `addtime`    datetime       DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_cache`;
CREATE TABLE `sky_cache`
(
    `K` varchar(255) NOT NULL COMMENT 'cache',
    `V` longtext,
    PRIMARY KEY (`K`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_cart`;
CREATE TABLE `sky_cart`
(
    `id`      int(22) NOT NULL AUTO_INCREMENT,
    `uid`     int(22)      DEFAULT '-1',
    `cookie`  text COMMENT '缓存标识',
    `ip`      varchar(255) DEFAULT NULL,
    `content` text,
    `addtime` datetime     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_cash_card`;
CREATE TABLE `sky_cash_card`
(
    `id`      int(255)     NOT NULL AUTO_INCREMENT,
    `gid`     int(255)     NOT NULL COMMENT '商品ID',
    `oid`     int(255)     NOT NULL COMMENT '订单id',
    `token`   varchar(255) NOT NULL,
    `uid`     int(255)     NOT NULL COMMENT '用户ID',
    `state`   int(255)     NOT NULL DEFAULT '1' COMMENT '1未使用，2已使用',
    `addtime` datetime     NOT NULL,
    `endtime` datetime     NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_class`;
CREATE TABLE `sky_class`
(
    `cid`     int(11) NOT NULL AUTO_INCREMENT,
    `sort`    int(11)      DEFAULT NULL COMMENT '排序',
    `name`    varchar(255) DEFAULT NULL,
    `image`   text,
    `grade`   int(255)     DEFAULT '1' COMMENT '等级限制',
    `content` text,
    `state`   int(11)      DEFAULT '1' COMMENT '1,显示，2隐藏',
    `support` varchar(255) DEFAULT '1,1,1,1,1' COMMENT 'QQ,微信,支付宝,余额,积分',
    `date`    datetime     DEFAULT NULL,
    PRIMARY KEY (`cid`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8mb4;
BEGIN;
INSERT INTO `sky_class` (`cid`, `sort`, `name`, `image`, `grade`, `content`, `state`, `support`, `date`)
VALUES (1, NULL, '默认分类', '/assets/img/logo.png', 1, '', 1, '1,1,1,1,1', '2022-08-04 17:17:58');
COMMIT;
DROP TABLE IF EXISTS `sky_config`;
CREATE TABLE `sky_config`
(
    `K` varchar(255) NOT NULL,
    `V` text,
    `C` text,
    PRIMARY KEY (`K`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_coupon`;
CREATE TABLE `sky_coupon`
(
    `id`          int(255) NOT NULL AUTO_INCREMENT,
    `oid`         text COMMENT '订单id',
    `uid`         int(255)       DEFAULT NULL,
    `name`        text,
    `content`     text,
    `gid`         int(255)       DEFAULT NULL,
    `cid`         int(255)       DEFAULT NULL,
    `token`       text,
    `money`       decimal(65, 2) DEFAULT NULL COMMENT '优惠券则扣/抵扣金额',
    `ip`          varchar(255)   DEFAULT NULL,
    `minimum`     decimal(65, 2) DEFAULT NULL COMMENT '满减时最低可用金额，立减时填0，折扣券时填0',
    `type`        int(255)       DEFAULT NULL COMMENT '1、满减券，2、立减券，3、折扣券',
    `apply`       int(255)       DEFAULT NULL COMMENT '1、单品优惠券，2、品类券，3、商品通用券',
    `term_type`   int(255)       DEFAULT NULL COMMENT '1、相对类型，领取后计算时间，2、固定类型，固定到期时间',
    `indate`      int(255)       DEFAULT NULL COMMENT '优惠券有效天数，相对',
    `expirydate`  datetime       DEFAULT NULL COMMENT '优惠券失效日期，固定',
    `get_way`     int(255)       DEFAULT NULL COMMENT '1、不显示，隐藏券，2、显示在对应商品详情内，必须是单品或通用券，3、显示在商品分类界面，必须是品类券，或通用券',
    `limit`       int(255)       DEFAULT NULL COMMENT '每个用户最多可领取多少个此次生成优惠券的数量',
    `limit_token` text COMMENT '优惠券数量限制token，同一时间生成的优惠券均会生成相同的ltoken',
    `gettime`     datetime       DEFAULT NULL COMMENT '领取时间',
    `endtime`     datetime       DEFAULT NULL COMMENT '使用时间',
    `addtime`     datetime       DEFAULT NULL COMMENT '生成时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_freight`;
CREATE TABLE `sky_freight`
(
    `id`        int(11) NOT NULL AUTO_INCREMENT,
    `name`      varchar(255)   DEFAULT NULL COMMENT '运费模板名称',
    `region`    text COMMENT '地区',
    `money`     decimal(11, 2) DEFAULT NULL COMMENT '运费,和地区挂钩',
    `nums`      int(11)        DEFAULT NULL COMMENT '购买数量>=nums则运费为money',
    `exceed`    decimal(11, 2) DEFAULT NULL COMMENT '高于下单数量额外每件商品的价格',
    `threshold` decimal(11, 2) DEFAULT NULL COMMENT '临界点,高于免运费',
    `date`      datetime       DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_goods`;
CREATE TABLE `sky_goods`
(
    `gid`                int(11) NOT NULL AUTO_INCREMENT,
    `cid`                int(11)        DEFAULT '1' COMMENT '分类ID',
    `sort`               int(11)        DEFAULT NULL COMMENT '排序',
    `name`               varchar(255)   DEFAULT NULL,
    `image`              text,
    `money`              decimal(24, 8) DEFAULT NULL COMMENT '成本价格',
    `profits`            decimal(24, 0) DEFAULT '100' COMMENT '商品利润比例',
    `min`                int(255)       DEFAULT '1' COMMENT '最低购买份数',
    `max`                int(255)       DEFAULT '1' COMMENT '最大购买份数',
    `quota`              int(255)       DEFAULT '9999' COMMENT '商品总库存',
    `freight`            varchar(255)   DEFAULT '-1' COMMENT '运费模板',
    `method`             varchar(255)   DEFAULT '[1,2,3,4,5,6,7]' COMMENT '商品扩展参数',
    `input`              text COMMENT '提交字段名称 | 分割',
    `quantity`           int(255)       DEFAULT '1' COMMENT '每份数量',
    `docs`               mediumtext COMMENT '商品说明',
    `alert`              text COMMENT '弹窗',
    `units`              varchar(255)   DEFAULT '个' COMMENT '数量单位',
    `accuracy`           int(255)       DEFAULT '2' COMMENT '小数点精度',
    `deliver`            int(255)       DEFAULT '1' COMMENT '1自营,2URL,3卡密,4购买成功后显示隐藏内容,其他均是第三方货源',
    `state`              int(11)        DEFAULT '1' COMMENT '1上架,2下架,3隐藏',
    `note`               text COMMENT '商品便签，仅站长可见',
    `sqid`               int(11)        DEFAULT NULL COMMENT '社区ID',
    `sales`              int(255)       DEFAULT '0' COMMENT '商品销量',
    `selling`            text COMMENT '商品自定义售价',
    `explain`            text,
    `specification`      int(1)         DEFAULT '1' COMMENT '1关闭，2开启',
    `specification_type` int(1)         DEFAULT '1' COMMENT '是否将商品规格参数提交至对接货源，1提交，2不提交',
    `specification_spu`  mediumtext COMMENT '商品规格组合数组',
    `specification_sku`  mediumtext COMMENT '商品规格组合参数',
    `extend`             mediumtext COMMENT '对接扩展参数，根据商品发货类型配置',
    `label`              mediumtext COMMENT '商品标签,逗号分割',
    `date`               datetime       DEFAULT NULL,
    `update_dat`         datetime       DEFAULT NULL,
    PRIMARY KEY (`gid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_invite`;
CREATE TABLE `sky_invite`
(
    `id`            int(11) NOT NULL AUTO_INCREMENT,
    `uid`           int(11)        DEFAULT NULL COMMENT '邀请者ID',
    `invitee`       int(11)        DEFAULT NULL COMMENT '被邀请者编号',
    `award`         decimal(11, 0) DEFAULT NULL COMMENT '邀请奖励金额池',
    `ip`            varchar(255)   DEFAULT NULL COMMENT '可根据IP配置防刷',
    `draw_time`     datetime       DEFAULT NULL COMMENT '领取时间',
    `creation_time` datetime       DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_journal`;
CREATE TABLE `sky_journal`
(
    `id`      int(11) NOT NULL AUTO_INCREMENT,
    `ip`      varchar(255)   DEFAULT NULL,
    `uid`     int(11)        DEFAULT NULL COMMENT '用户ID',
    `count`   decimal(24, 8) DEFAULT NULL COMMENT '相关数量',
    `name`    varchar(255)   DEFAULT NULL COMMENT '日志名称',
    `content` text COMMENT '日志内容',
    `date`    datetime       DEFAULT NULL COMMENT '发生时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_login`;
CREATE TABLE `sky_login`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `token`        varchar(255) DEFAULT NULL,
    `ip`           varchar(255) DEFAULT NULL,
    `state`        int(1)       DEFAULT '3' COMMENT '1:用户确认，2用户取消，3，待确认',
    `finish_time`  datetime     DEFAULT NULL,
    `date_created` datetime     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_mainframe`;
CREATE TABLE `sky_mainframe`
(
    `id`             int(255) NOT NULL AUTO_INCREMENT,
    `oid`            int(255) NOT NULL DEFAULT '-1' COMMENT '订单ID',
    `identification` varchar(255)      DEFAULT NULL COMMENT '唯一标识符',
    `siteId`         int(255)          DEFAULT '-1' COMMENT '主机ID',
    `uid`            int(255)          DEFAULT '-1' COMMENT '用户ID',
    `server`         int(255)          DEFAULT NULL COMMENT '服务器ID',
    `type`           int(10)           DEFAULT '2' COMMENT '激活状态,1|2',
    `sql_user`       varchar(255)      DEFAULT NULL,
    `sql_name`       varchar(255)      DEFAULT NULL,
    `sql_pass`       varchar(255)      DEFAULT NULL,
    `domain`         text COMMENT '绑定域名',
    `RenewalType`    int(255)          DEFAULT '1' COMMENT '自动续期1|2',
    `RenewPrice`     decimal(16, 8)    DEFAULT NULL COMMENT '续费金额',
    `maxdomain`      int(255)          DEFAULT '3' COMMENT '最大域名绑定数量',
    `concurrencyall` int(255)          DEFAULT NULL COMMENT '并发总数',
    `concurrencyip`  int(255)          DEFAULT NULL COMMENT '单IP并发',
    `traffic`        int(255)          DEFAULT NULL COMMENT '上行流量上限',
    `filesize`       int(255)          DEFAULT NULL COMMENT '文件上传大小限制',
    `sizespace`      decimal(65, 2)    DEFAULT '200.00' COMMENT '可用空间大小(MB)',
    `currentsize`    decimal(65, 2)    DEFAULT '0.00' COMMENT '当前空间大小(MB)',
    `status`         int(2)            DEFAULT '1' COMMENT '用户可配置的主机开启状态',
    `state`          int(2)            DEFAULT '1' COMMENT '管理员可配置的主机开启状态',
    `username`       text COMMENT '面板登录账号',
    `password`       text COMMENT '面板登录密码',
    `return`         text COMMENT '对接错误信息,方便主站调试',
    `endtime`        datetime          DEFAULT NULL COMMENT '到期时间',
    `addtime`        datetime          DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_mark`;
CREATE TABLE `sky_mark`
(
    `id`      int(24) NOT NULL AUTO_INCREMENT,
    `gid`     int(24)      DEFAULT NULL COMMENT '商品ID',
    `order`   varchar(255) DEFAULT NULL COMMENT '订单号',
    `content` text COMMENT '评论内容',
    `image`   text COMMENT '配图',
    `name`    text COMMENT '商品参数说明,名称,购买数量',
    `uid`     int(24)      DEFAULT NULL COMMENT '评论用户UID,未登录用户无法评论',
    `seller`  varchar(255) DEFAULT NULL COMMENT '卖家UID,供货商模式启用',
    `score`   varchar(255) DEFAULT NULL COMMENT '商品评分!',
    `state`   int(255)     DEFAULT '3' COMMENT '1=显示,2=审核,3=驳回',
    `addtime` datetime     DEFAULT NULL COMMENT '评论时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_notice`;
CREATE TABLE `sky_notice`
(
    `id`      int(11) NOT NULL AUTO_INCREMENT,
    `title`   varchar(255) DEFAULT NULL,
    `content` mediumtext,
    `image`   text,
    `browse`  text COMMENT '浏览人数,|分割',
    `state`   int(2)       DEFAULT '1' COMMENT '1显示，2关闭',
    `type`    int(2)       DEFAULT '1' COMMENT '1,全部可见,2登陆用户可见',
    `PV`      int(255)     DEFAULT '0' COMMENT '阅读数',
    `date`    datetime     DEFAULT NULL COMMENT '发布时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_order`;
CREATE TABLE `sky_order`
(
    `id`            int(11)      NOT NULL AUTO_INCREMENT,
    `order`         varchar(255) NOT NULL COMMENT '订单号',
    `trade_no`      varchar(255)      DEFAULT NULL COMMENT '支付订单',
    `uid`           int(11)           DEFAULT NULL COMMENT '用户ID',
    `muid`          int(11)           DEFAULT '-1' COMMENT '店铺ID',
    `ip`            varchar(255)      DEFAULT NULL,
    `input`         text COMMENT '下单信息',
    `state`         int(11)           DEFAULT NULL COMMENT '领取状态，1成功，2待处理，3异常，4正在处理，5退款,6售后维权,7已评价',
    `docking`       int(11)           DEFAULT '-1' COMMENT '1,对接成功,2,对接失败,3待提交对接,-1,其他状态',
    `num`           int(11)           DEFAULT '1' COMMENT '下单份数',
    `return`        text COMMENT '对接返回数据',
    `gid`           int(11)           DEFAULT NULL COMMENT '商品ID',
    `order_id`      text COMMENT '社区返回的订单号',
    `money`         decimal(24, 8)    DEFAULT NULL COMMENT '下单成本',
    `originalprice` decimal(24, 8)    DEFAULT '-1.00000000' COMMENT '原始订单付款金额，未使用优惠券则=-1',
    `coupon`        varchar(255)      DEFAULT '-1' COMMENT '折扣券id',
    `payment`       varchar(255)      DEFAULT NULL COMMENT '付款方式',
    `take`          int(1)            DEFAULT '1' COMMENT '1=未收货,2=确认收货',
    `price`         decimal(24, 8)    DEFAULT NULL COMMENT '订单金额',
    `logistics`     text COMMENT '物流单号',
    `user_rmb`      decimal(24, 8)    DEFAULT NULL COMMENT '玖伍返回的余额,亿乐为空',
    `remark`        text,
    `finishtime`    timestamp    NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '订单完成时间',
    `addtitm`       datetime          DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 10
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_pay`;
CREATE TABLE `sky_pay`
(
    `id`       int(11)      NOT NULL AUTO_INCREMENT,
    `order`    varchar(255) NOT NULL COMMENT '本地生成的订单号',
    `trade_no` varchar(255)   DEFAULT NULL COMMENT '对接易支付生成的订单号',
    `type`     varchar(11)    DEFAULT NULL COMMENT '付款方式，qqpayQQ,wxpay微信,alipay支付宝',
    `uid`      int(11)        DEFAULT NULL COMMENT '操作用户,-1 游客，>0用户',
    `gid`      int(11)        DEFAULT NULL COMMENT '-1,在线充值, >0购买的商品ID',
    `oid`      int(22)        DEFAULT '-1' COMMENT '对应的订单ID',
    `name`     varchar(255)   DEFAULT NULL COMMENT '充值标识,如 后台充值,购买商品等',
    `money`    decimal(24, 8) DEFAULT NULL COMMENT '操作金额',
    `price`    decimal(24, 8) DEFAULT '-1.00000000' COMMENT '原始订单付款金额，未使用优惠券则=-1',
    `coupon`   varchar(255)   DEFAULT '-1' COMMENT '折扣券id',
    `ip`       varchar(255)   DEFAULT NULL,
    `input`    text COMMENT '用户输入的下单内容',
    `num`      int(11)        DEFAULT '1' COMMENT '下单数量',
    `state`    int(11)        DEFAULT '2' COMMENT '订单状态,1:已完成,2未完成',
    `verify`   varchar(255)   DEFAULT '-1' COMMENT '价格监控验证，-1未验证状态！',
    `endtime`  datetime       DEFAULT NULL COMMENT '结束时间',
    `addtime`  datetime       DEFAULT NULL COMMENT '发布时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 18
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_price`;
CREATE TABLE `sky_price`
(
    `mid`             int(12) NOT NULL AUTO_INCREMENT,
    `sort`            int(12)        DEFAULT NULL COMMENT '排序',
    `name`            varchar(255)   DEFAULT NULL COMMENT '等级名称',
    `content`         text COMMENT '等级说明',
    `priceis`         text COMMENT '售价增长比',
    `pointsis`        text COMMENT '积分增长比',
    `rule`            int(25)        DEFAULT '-1' COMMENT '加价规则ID',
    `ActualProfit`    varchar(255)   DEFAULT '100' COMMENT '实际获得利润百分比',
    `ProfitThreshold` varchar(255)   DEFAULT '100' COMMENT '利润分成阈值百分比',
    `money`           decimal(12, 2) DEFAULT '0.00' COMMENT '等级价格,0则默认',
    `state`           int(255)       DEFAULT '1' COMMENT '是否使用',
    `addtime`         datetime       DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`mid`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 9
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_profit_rule`;
CREATE TABLE `sky_profit_rule`
(
    `id`      int(255) NOT NULL AUTO_INCREMENT,
    `name`    varchar(255) DEFAULT NULL COMMENT '规则名称',
    `rules`   longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '规则内容',
    `state`   int(2)       DEFAULT '1' COMMENT '加价规则开关',
    `addtime` datetime     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4;
BEGIN;

INSERT INTO `sky_profit_rule` (`id`, `name`, `rules`, `state`, `addtime`)
VALUES (1, '基础规则',
        '[{\"max\":\"30\",\"min\":\"0.00\",\"profit\":\"100\"},{\"max\":\"50\",\"min\":\"30.01\",\"profit\":\"90\"},{\"max\":\"70\",\"min\":\"50.01\",\"profit\":\"80\"},{\"max\":\"90\",\"min\":\"70.01\",\"profit\":\"70\"},{\"max\":\"110\",\"min\":\"90.01\",\"profit\":\"60\"},{\"max\":\"130\",\"min\":\"110.01\",\"profit\":\"50\"},{\"max\":\"150\",\"min\":\"130.01\",\"profit\":\"40\"},{\"max\":\"170\",\"min\":\"150.01\",\"profit\":\"30\"},{\"max\":\"190\",\"min\":\"170.01\",\"profit\":\"20\"},{\"max\":\"999999\",\"min\":\"190.01\",\"profit\":\"10\"}]',
        1, '2022-08-23 22:35:27');

INSERT INTO `sky_price` (`mid`, `sort`, `name`, `content`, `priceis`, `pointsis`, `rule`, `ActualProfit`,
                         `ProfitThreshold`,
                         `money`, `state`, `addtime`)
VALUES (1, 1, '普通用户', '普通用户，和游客没什么区别!', '30', '3000', 1, '50', '20', 0.00, 1, '2022-06-10 16:35:36');
INSERT INTO `sky_price` (`mid`, `sort`, `name`, `content`, `priceis`, `pointsis`, `rule`, `ActualProfit`,
                         `ProfitThreshold`,
                         `money`, `state`, `addtime`)
VALUES (2, 2, '铜牌代理', '铜牌代理，可以加盟分店了,其他用户在你加盟分店下单你可以获得提成!', '28', '2800', 1, '50', '20', 10.00, 1,
        '2022-06-10 16:35:36');
INSERT INTO `sky_price` (`mid`, `sort`, `name`, `content`, `priceis`, `pointsis`, `rule`, `ActualProfit`,
                         `ProfitThreshold`,
                         `money`, `state`, `addtime`)
VALUES (3, 3, '银牌代理', '银牌代理，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '26', '2600', 1, '50', '20', 20.00, 1,
        '2022-06-10 16:35:36');
INSERT INTO `sky_price` (`mid`, `sort`, `name`, `content`, `priceis`, `pointsis`, `rule`, `ActualProfit`,
                         `ProfitThreshold`,
                         `money`, `state`, `addtime`)
VALUES (4, 4, '金牌代理', '金牌代理，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '24', '2400', 1, '50', '20', 30.00, 1,
        '2022-06-10 16:35:36');
INSERT INTO `sky_price` (`mid`, `sort`, `name`, `content`, `priceis`, `pointsis`, `rule`, `ActualProfit`,
                         `ProfitThreshold`,
                         `money`, `state`, `addtime`)
VALUES (5, 5, '小站长', '小站长，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '22', '2200', 1, '50', '20', 40.00, 1,
        '2022-06-10 16:35:36');
INSERT INTO `sky_price` (`mid`, `sort`, `name`, `content`, `priceis`, `pointsis`, `rule`, `ActualProfit`,
                         `ProfitThreshold`,
                         `money`, `state`, `addtime`)
VALUES (6, 6, '平台站长', '平台站长，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '20', '2000', 1, '50', '20', 50.00, 1,
        '2022-06-10 16:35:36');
INSERT INTO `sky_price` (`mid`, `sort`, `name`, `content`, `priceis`, `pointsis`, `rule`, `ActualProfit`,
                         `ProfitThreshold`,
                         `money`, `state`, `addtime`)
VALUES (7, 7, '高级站长', '高级站长，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '18', '1800', 1, '50', '20', 60.00, 1,
        '2022-06-10 16:35:36');
INSERT INTO `sky_price` (`mid`, `sort`, `name`, `content`, `priceis`, `pointsis`, `rule`, `ActualProfit`,
                         `ProfitThreshold`,
                         `money`, `state`, `addtime`)
VALUES (8, 8, '领袖站长', '领袖站长，除了可以加盟分店外，还可以赚取下级提成，并且收益比上一级别更高！，推荐购买!', '16', '1600', 1, '50', '20', 70.00, 1,
        '2022-06-10 16:35:36');
COMMIT;
DROP TABLE IF EXISTS `sky_queue`;
CREATE TABLE `sky_queue`
(
    `id`       int(22) NOT NULL AUTO_INCREMENT,
    `type`     int(22)        DEFAULT '2' COMMENT '1=有效，2=待执行，3=待付款',
    `order`    varchar(255)   DEFAULT NULL COMMENT '订单号',
    `trade_no` varchar(255)   DEFAULT NULL COMMENT '支付订单',
    `uid`      int(25)        DEFAULT NULL,
    `ip`       varchar(255)   DEFAULT NULL,
    `input`    text COMMENT '下单信息',
    `num`      int(25)        DEFAULT NULL COMMENT '下单份数',
    `gid`      int(25)        DEFAULT NULL COMMENT '商品ID',
    `payment`  varchar(255)   DEFAULT NULL COMMENT '付款方式',
    `price`    decimal(25, 8) DEFAULT NULL COMMENT '订单金额',
    `money`    decimal(24, 8) DEFAULT '-1.00000000' COMMENT '原始订单付款金额，未使用优惠券则=-1',
    `coupon`   varchar(255)   DEFAULT '-1' COMMENT '折扣券id',
    `remark`   text COMMENT '订单备注',
    `endtime`  datetime       DEFAULT NULL COMMENT '处理时间',
    `addtime`  datetime       DEFAULT NULL COMMENT '队列创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_recharge`;
CREATE TABLE `sky_recharge`
(
    `id`      int(255) NOT NULL AUTO_INCREMENT,
    `name`    varchar(255)   DEFAULT NULL COMMENT '充值卡名称',
    `uid`     int(255)       DEFAULT '-1' COMMENT '使用者',
    `money`   decimal(16, 2) DEFAULT NULL COMMENT '面额',
    `type`    int(2)         DEFAULT '1' COMMENT '1余额充值，2积分充值',
    `token`   varchar(255)   DEFAULT NULL,
    `ip`      varchar(255)   DEFAULT NULL,
    `endtime` datetime       DEFAULT NULL,
    `addtime` datetime       DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_seckill`;
CREATE TABLE `sky_seckill`
(
    `id`         int(255) NOT NULL AUTO_INCREMENT,
    `gid`        int(255) DEFAULT NULL COMMENT '商品ID',
    `depreciate` int(255) DEFAULT '0' COMMENT '降价百分比',
    `start_time` datetime DEFAULT NULL COMMENT '活动开启时间',
    `end_time`   datetime DEFAULT NULL COMMENT '结束时间',
    `astrict`    int(255) DEFAULT '1' COMMENT '限购人数',
    `addtime`    datetime DEFAULT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_server`;
CREATE TABLE `sky_server`
(
    `id`             int(255) NOT NULL AUTO_INCREMENT,
    `name`           varchar(255)   DEFAULT NULL COMMENT '主机名称',
    `url`            varchar(255)   DEFAULT NULL COMMENT '对接链接',
    `domain`         text COMMENT '可供绑定的域名或ID',
    `data`           text COMMENT '服务器基础参数',
    `path`           varchar(255)   DEFAULT '/www/wwwroot/' COMMENT '默认建站目录',
    `root_directory` varchar(255)   DEFAULT '/www/' COMMENT '宝塔安装目录,仅windows需要配置',
    `token`          varchar(255)   DEFAULT NULL COMMENT '对接密钥',
    `HostSpace`      decimal(65, 2) DEFAULT '200.00' COMMENT '主机空间大小配额',
    `system`         int(255)       DEFAULT '1' COMMENT '系统类型，1|2 linux，Windows',
    `type`           int(255)       DEFAULT '0' COMMENT '存放的分类ID',
    `state`          int(10)        DEFAULT '1' COMMENT '服务器状态',
    `sqlurl`         text COMMENT '数据库管理地址',
    `error`          text COMMENT '错误数据',
    `content`        text COMMENT '服务器节点介绍',
    `endtime`        datetime       DEFAULT NULL,
    `addtime`        datetime       DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_shequ`;
CREATE TABLE `sky_shequ`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `url`        varchar(255)     DEFAULT NULL,
    `type`       int(11) NOT NULL DEFAULT '1' COMMENT '1,玖伍,2亿乐',
    `class_name` varchar(255)     DEFAULT '-1' COMMENT '类名称',
    `username`   varchar(222)     DEFAULT NULL,
    `password`   varchar(255)     DEFAULT NULL,
    `secret`     varchar(255)     DEFAULT NULL,
    `pattern`    varchar(255)     DEFAULT '1' COMMENT '对接模式',
    `annotation` varchar(255)     DEFAULT NULL COMMENT '注释信息',
    `date`       datetime         DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_tickets`;
CREATE TABLE `sky_tickets`
(
    `id`       int(25) NOT NULL AUTO_INCREMENT,
    `uid`      int(25) NOT NULL,
    `order`    varchar(255) DEFAULT NULL,
    `name`     text COMMENT '工单标题',
    `content`  text COMMENT '用户问题',
    `message`  text COMMENT '互动内容',
    `state`    int(1)       DEFAULT '2' COMMENT '1已解决,2处理中,3已关闭',
    `type`     int(255)     DEFAULT '1' COMMENT '1已受理,2已处理,3已解决,4,关闭工单,5已评价',
    `grade`    int(25)      DEFAULT NULL COMMENT '完成评分',
    `class`    text COMMENT '工单类型',
    `timetips` varchar(250) DEFAULT NULL COMMENT '在线时间段说明',
    `endtime`  datetime     DEFAULT NULL,
    `addtime`  datetime     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_token`;
CREATE TABLE `sky_token`
(
    `kid`     int(11) NOT NULL AUTO_INCREMENT,
    `uid`     int(11)      DEFAULT '1' COMMENT '购买者UID',
    `gid`     int(11)      DEFAULT NULL COMMENT '商品ID',
    `code`    varchar(255) DEFAULT NULL COMMENT '提卡密码',
    `token`   varchar(255) DEFAULT NULL COMMENT '卡密内容',
    `ip`      varchar(255) DEFAULT NULL,
    `order`   varchar(255) DEFAULT NULL,
    `endtime` datetime     DEFAULT NULL,
    `addtime` datetime     DEFAULT NULL,
    PRIMARY KEY (`kid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_user`;
CREATE TABLE `sky_user`
(
    `id`            int(11)      NOT NULL AUTO_INCREMENT,
    `user_idu`      varchar(255) NOT NULL COMMENT '唯一识别码',
    `username`      text COMMENT '账号',
    `password`      text COMMENT '密码',
    `token`         varchar(255)      DEFAULT NULL COMMENT '同系统对接密钥',
    `ip_white_list` text COMMENT 'IP白名单',
    `superior`      int(11)           DEFAULT NULL COMMENT '上级编号',
    `currency`      decimal(24, 0)    DEFAULT '0' COMMENT '用户积分',
    `ip`            varchar(255)      DEFAULT NULL,
    `money`         decimal(24, 8)    DEFAULT '0.00000000' COMMENT '余额',
    `grade`         int(255)          DEFAULT '1' COMMENT '用户等级！',
    `domain`        varchar(255)      DEFAULT NULL COMMENT '店铺域名',
    `configuration` longtext COMMENT '其他配置，以序列格式存在',
    `image`         varchar(255)      DEFAULT NULL COMMENT '用户头像',
    `name`          varchar(255)      DEFAULT NULL,
    `pricehike`     longtext COMMENT '商品涨价百分比规则',
    `qq`            varchar(255)      DEFAULT NULL,
    `mobile`        varchar(255)      DEFAULT NULL COMMENT '电话号码',
    `state`         int(11)           DEFAULT NULL COMMENT '用户状态，1正常，2禁止登陆',
    `recent_time`   timestamp    NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `found_date`    datetime          DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1000
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
DROP TABLE IF EXISTS `sky_withdrawal`;
CREATE TABLE `sky_withdrawal`
(
    `id`             int(12) NOT NULL AUTO_INCREMENT,
    `type`           varchar(255)   DEFAULT NULL,
    `name`           varchar(255)   DEFAULT NULL COMMENT '提现者姓名',
    `account_number` varchar(255)   DEFAULT NULL COMMENT '提现账号',
    `uid`            int(11)        DEFAULT NULL,
    `remarks`        text COMMENT '提现者备注',
    `state`          int(1)         DEFAULT '3' COMMENT '1，已完成,2已退回,3待处理',
    `result_code`    text COMMENT '处理结果',
    `money`          decimal(12, 2) DEFAULT NULL COMMENT '提现金额',
    `endtime`        datetime       DEFAULT NULL COMMENT '处理时间',
    `addtime`        datetime       DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
BEGIN;
COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
