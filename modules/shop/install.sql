-- MySQL 5.7～8.4：新建表使用统一字符集和排序规则。
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- BadouCMS Shop schema (重构版 · CMS 管展示 / shop 管交易 · aucode 桥接)
-- 表前缀 __PREFIX__ -> bd_ ; InnoDB / utf8mb4 ; 时间戳 created_at/updated_at
-- ----------------------------

-- 品牌主表（品牌不参与 CMS 多语言）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '品牌名称',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '品牌Logo',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '品牌简介',
  `content` text COMMENT '品牌介绍',
  `sorting` int(11) NOT NULL DEFAULT 255,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_brand_status` (`status`),
  KEY `idx_brand_sorting` (`sorting`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品品牌';

-- 商品主表（单实体：一个逻辑商品仅一条共享交易主记录；通过 aucode 桥接 cms_content）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aucode` int(11) NOT NULL COMMENT 'CMS 内容桥接键(唯一,商品身份锚点,同 cms_content.aucode 整型池)',
  `brand_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '品牌ID',
  `sn` varchar(100) NOT NULL DEFAULT '' COMMENT '商品货号',
  `default_sku_id` int(11) DEFAULT NULL COMMENT '默认 SKU ID',
  `min_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '最低价(SKU 聚合)',
  `max_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '最高价(SKU 聚合)',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT '市场价',
  `cost_price` decimal(10,2) DEFAULT NULL COMMENT '成本价',
  `total_stock` int(11) NOT NULL DEFAULT 0 COMMENT '总库存(SKU 聚合)',
  `sales` int(11) NOT NULL DEFAULT 0 COMMENT '销量',
  `weight` decimal(10,2) DEFAULT NULL COMMENT '重量',
  `freight_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '运费模板ID',
  `images` text COMMENT '商品图片(交易/列表用图;详情长图走 cms_content.pics)',
  `thumb` varchar(255) DEFAULT '' COMMENT '缩略图',
  `unit` varchar(50) DEFAULT '' COMMENT '单位',
  `is_recommend` tinyint(1) NOT NULL DEFAULT 0 COMMENT '推荐',
  `is_hot` tinyint(1) NOT NULL DEFAULT 0 COMMENT '热销',
  `is_new` tinyint(1) NOT NULL DEFAULT 0 COMMENT '新品',
  `is_on_sale` tinyint(1) NOT NULL DEFAULT 1 COMMENT '上架',
  `is_free_shipping` tinyint(1) NOT NULL DEFAULT 0 COMMENT '包邮',
  `view_count` int(11) NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_aucode` (`aucode`),
  UNIQUE KEY `uniq_sn` (`sn`),
  KEY `idx_brand_id` (`brand_id`),
  KEY `idx_is_on_sale` (`is_on_sale`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品共享交易主记录(语言无关)';

-- 商品交易侧语言内容（单位等展示字段按语言保存）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `acode` varchar(20) NOT NULL DEFAULT '' COMMENT 'CMS语言区域编码',
  `unit` varchar(50) NOT NULL DEFAULT '' COMMENT '商品单位',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_goods_acode` (`goods_id`,`acode`),
  KEY `idx_goods_lang_acode` (`acode`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品语言交易展示内容';

-- 商品 SKU 表（语言无关；同一 SKU 被所有语言共享）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL COMMENT '商品 ID',
  `sku_key` varchar(100) NOT NULL DEFAULT '' COMMENT '规格值组合键(排序后的 spec_value_id 用 _ 连接;无规格留空)',
  `sn` varchar(100) NOT NULL DEFAULT '' COMMENT 'SKU 货号',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '售价',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT '市场价',
  `cost_price` decimal(10,2) DEFAULT NULL COMMENT '成本价',
  `stock` int(11) NOT NULL DEFAULT 0 COMMENT '库存',
  `frozen_stock` int(11) NOT NULL DEFAULT 0 COMMENT '冻结库存(预占;第一版未用)',
  `weight` decimal(10,2) DEFAULT NULL COMMENT '重量',
  `image` varchar(255) DEFAULT '' COMMENT 'SKU 图片',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_goods_skukey` (`goods_id`,`sku_key`),
  KEY `idx_sku_sn` (`sn`),
  KEY `idx_goods_id` (`goods_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品 SKU';

-- 规格定义（交易结构,语言无关实体;名称走 _lang）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_spec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL DEFAULT 0 COMMENT '规格模板ID',
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_template_sort` (`template_id`,`sort`,`id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='规格定义';

-- 规格值实体（语言无关）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_spec_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spec_id` int(11) NOT NULL COMMENT '所属规格',
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_spec_id` (`spec_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='规格值实体';

-- 规格名称翻译（lang 存 CMS acode）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_spec_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spec_id` int(11) NOT NULL,
  `acode` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_spec_acode` (`spec_id`,`acode`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='规格名称翻译';

-- 规格值名称翻译
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_spec_value_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spec_value_id` int(11) NOT NULL,
  `acode` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_specvalue_acode` (`spec_value_id`,`acode`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='规格值名称翻译';

-- SKU 规格模板（参考 zhuzi shop_sku_template；spec_data 保存一整套规格结构）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_sku_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '模板名称',
  `spec_data` longtext NOT NULL COMMENT '规格结构 JSON',
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间(Unix时间戳)',
  `updated_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间(Unix时间戳)',
  PRIMARY KEY (`id`),
  KEY `idx_sort_status` (`sort`,`status`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SKU 规格模板';

-- 商品启用的规格值集合（决定可组合的 SKU 骨架）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_goods_sku_spec` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品ID',
  `spec_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格ID',
  `spec_value_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格值ID',
  `createtime` bigint(16) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
  `updatetime` bigint(16) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_goods_id` (`goods_id`),
  KEY `idx_spec_id` (`spec_id`),
  KEY `idx_spec_value_id` (`spec_value_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品SKU规格关联表';

-- 库存变更日志
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_stock_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `sku_id` int(11) NOT NULL,
  `change_type` varchar(30) NOT NULL DEFAULT '' COMMENT 'order/cancel/restore/adjust',
  `before_stock` int(11) NOT NULL DEFAULT 0,
  `num` int(11) NOT NULL DEFAULT 0 COMMENT '变动数(正为增,负为减)',
  `after_stock` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) DEFAULT NULL,
  `source` varchar(50) DEFAULT '' COMMENT '来源',
  `remark` varchar(255) DEFAULT '',
  `admin_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_goods_id` (`goods_id`),
  KEY `idx_sku_id` (`sku_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='库存变更日志';

-- 商城内容关联(商品页挂载购物指南/相关资讯等附属 CMS 文章)
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_content_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_type` varchar(20) NOT NULL DEFAULT '' COMMENT 'goods/category',
  `target_id` int(11) NOT NULL COMMENT 'shop_goods.id 或分类 aucode',
  `content_id` int(11) NOT NULL COMMENT 'cms_content.id',
  `relation_type` varchar(20) NOT NULL DEFAULT 'related' COMMENT 'guide/news/help/related',
  `sort` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_relation` (`target_type`,`target_id`,`content_id`,`relation_type`),
  KEY `idx_target` (`target_type`,`target_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商城附属内容关联';

-- 购物车（仅引用 sku_id;展示标题/图片实时从 cms_content 取当前语言）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL COMMENT '冗余便于展示',
  `sku_id` int(11) NOT NULL,
  `num` int(11) NOT NULL DEFAULT 1,
  `selected` tinyint(1) NOT NULL DEFAULT 1,
  `scene` varchar(50) NOT NULL DEFAULT 'pc',
  `price_cache` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '展示缓存;结算时重读 SKU 价',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_sku_scene` (`user_id`,`sku_id`,`scene`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_sku_id` (`sku_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='购物车';

-- 订单主表
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) NOT NULL COMMENT '订单号',
  `user_id` int(11) NOT NULL,
  `acode` varchar(20) NOT NULL DEFAULT '' COMMENT '下单时语言标识',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `goods_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `freight_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pay_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '应付金额',
  `currency_code` varchar(16) NOT NULL DEFAULT 'CNY' COMMENT '下单时展示币种',
  `currency_value` decimal(15,8) NOT NULL DEFAULT 1.00000000 COMMENT '下单时汇率快照',
  `pay_type` varchar(50) DEFAULT '' COMMENT '支付方式',
  `pay_status` varchar(20) NOT NULL DEFAULT 'unpaid' COMMENT 'unpaid/paid/refunding/refunded',
  `pay_time` datetime DEFAULT NULL,
  `order_status` varchar(20) NOT NULL DEFAULT 'pending_payment' COMMENT 'pending_payment/paid/shipped/completed/cancelled/refund_processing/refunded',
  `shipping_status` varchar(20) NOT NULL DEFAULT 'unshipped' COMMENT 'unshipped/shipped/received',
  `shipping_type` varchar(50) DEFAULT '' COMMENT '配送方式',
  `receiver_name` varchar(100) NOT NULL DEFAULT '',
  `receiver_phone` varchar(20) NOT NULL DEFAULT '',
  `receiver_address` varchar(500) NOT NULL DEFAULT '',
  `remark` varchar(500) DEFAULT '' COMMENT '买家备注',
  `admin_remark` varchar(500) DEFAULT '' COMMENT '卖家备注',
  `cancelled_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_order_sn` (`order_sn`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_status` (`order_status`),
  KEY `idx_pay_status` (`pay_status`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单主表';

-- 订单项（不可变快照;商品改名/删除后历史订单仍完整）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_order_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `sku_id` int(11) DEFAULT NULL,
  `goods_aucode` int(11) DEFAULT NULL COMMENT '便于历史内容回溯',
  `goods_sn` varchar(100) DEFAULT '' COMMENT '商品货号快照',
  `sku_sn` varchar(100) DEFAULT '' COMMENT 'SKU 货号快照',
  `goods_title` varchar(255) NOT NULL DEFAULT '' COMMENT '下单时当前语言快照',
  `goods_thumb` varchar(255) DEFAULT '',
  `spec_text` varchar(500) DEFAULT '' COMMENT '规格文本快照',
  `num` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_goods_id` (`goods_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单项快照';

-- 订单状态日志
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_order_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `from_status` varchar(20) DEFAULT '',
  `to_status` varchar(20) DEFAULT '',
  `remark` varchar(255) DEFAULT '',
  `admin_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单状态日志';

-- 商品收藏（沿用 zhuzi/shop 的用户-商品状态切换模型）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=取消收藏,1=已收藏',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_goods` (`user_id`,`goods_id`),
  KEY `idx_user_status` (`user_id`,`status`),
  KEY `idx_goods_id` (`goods_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品收藏';

-- 商品评价（订单商品评价，默认待审核）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `star` tinyint(1) NOT NULL DEFAULT 5,
  `content` text NOT NULL,
  `images` text,
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/approved/hidden',
  `admin_reply` text,
  `admin_id` int(11) DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_order_item_user` (`user_id`,`order_item_id`),
  KEY `idx_goods_status` (`goods_id`,`status`),
  KEY `idx_user_status` (`user_id`,`status`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品评价';

-- 支付单
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `pay_sn` varchar(50) NOT NULL COMMENT '本系统支付单号',
  `trade_no` varchar(100) DEFAULT NULL COMMENT '支付平台交易号(空=未支付;唯一防重复回调)',
  `pay_type` varchar(50) DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'CNY',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/paid/failed',
  `paid_at` datetime DEFAULT NULL,
  `raw` text COMMENT '回调原始数据',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pay_sn` (`pay_sn`),
  UNIQUE KEY `uniq_trade_no` (`trade_no`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付单';

-- 退款单（资金记录；售后单完成后生成）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `aftersale_id` int(11) DEFAULT NULL COMMENT '商品售后单ID;旧整单退款为空',
  `order_item_id` int(11) DEFAULT NULL COMMENT '退款商品项ID',
  `refund_sn` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reason` varchar(500) DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/success/failed',
  `trade_no` varchar(100) DEFAULT '',
  `refunded_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_refund_sn` (`refund_sn`),
  UNIQUE KEY `uniq_aftersale_id` (`aftersale_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_order_item_id` (`order_item_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='退款单';

-- 商品级售后单（仅退款 / 退货退款）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_aftersale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aftersale_sn` varchar(50) NOT NULL COMMENT '售后单号',
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL COMMENT 'refund_only/return_refund',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '系统按实付比例计算的退款额',
  `reason` varchar(500) NOT NULL DEFAULT '',
  `description` text,
  `images` text COMMENT 'JSON凭证图片',
  `status` varchar(30) NOT NULL DEFAULT 'pending' COMMENT 'pending/awaiting_return/returning/refunding/completed/rejected/cancelled',
  `reject_reason` varchar(500) NOT NULL DEFAULT '',
  `return_shipping_name` varchar(100) NOT NULL DEFAULT '',
  `return_shipping_no` varchar(100) NOT NULL DEFAULT '',
  `admin_id` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `refunded_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_aftersale_sn` (`aftersale_sn`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_order_item_id` (`order_item_id`),
  KEY `idx_user_status` (`user_id`,`status`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品售后单';

-- 发货记录（为拆单发货预留;第一版单物流）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_shipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `shipping_code` varchar(50) DEFAULT '' COMMENT '物流编码',
  `shipping_name` varchar(100) DEFAULT '' COMMENT '物流公司',
  `shipping_no` varchar(100) DEFAULT '' COMMENT '物流单号',
  `shipped_at` datetime DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='发货/物流记录';

-- 收货地址
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `country_id` int(11) NOT NULL DEFAULT 0 COMMENT '国家地区ID',
  `country` varchar(100) NOT NULL DEFAULT '' COMMENT '国家地区名称',
  `province_id` int(11) DEFAULT 0,
  `province` varchar(100) DEFAULT '',
  `city` varchar(100) DEFAULT '',
  `address` varchar(500) NOT NULL DEFAULT '',
  `zip_code` varchar(20) DEFAULT '',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收货地址';

-- 配送地区表，国家及省州共用名称，城市由用户填写。
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) DEFAULT 0 COMMENT '父ID',
  `level` tinyint(1) DEFAULT 0 COMMENT '等级:0国家1省州',
  `name` varchar(100) DEFAULT '' COMMENT '名称',
  `adcode` varchar(50) DEFAULT '' COMMENT '区划编码',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态:1正常0隐藏',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `level` (`level`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配送地区表';

-- 快递公司
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_shipper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `code` varchar(100) NOT NULL DEFAULT '' COMMENT '快递编码',
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1启用0停用',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='快递公司';

INSERT IGNORE INTO `__PREFIX__shop_shipper` (`id`, `name`, `code`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(1, '顺丰速运', 'SF', 10, 1, NOW(), NOW()),
(2, '中通快递', 'ZTO', 20, 1, NOW(), NOW()),
(3, '圆通速递', 'YTO', 30, 1, NOW(), NOW()),
(4, '申通快递', 'STO', 40, 1, NOW(), NOW()),
(5, '韵达速递', 'YD', 50, 1, NOW(), NOW()),
(6, '极兔速递', 'JTSD', 60, 1, NOW(), NOW()),
(7, '京东快递', 'JD', 70, 1, NOW(), NOW()),
(8, 'EMS', 'EMS', 80, 1, NOW(), NOW()),
(9, '邮政快递包裹', 'YZPY', 90, 1, NOW(), NOW()),
(10, '德邦快递', 'DBL', 100, 1, NOW(), NOW()),
(11, '百世快递', 'HTKY', 110, 1, NOW(), NOW()),
(12, '百世快运', 'BTWL', 120, 1, NOW(), NOW()),
(13, '天天快递', 'HHTT', 130, 1, NOW(), NOW()),
(14, '优速快递', 'UC', 140, 1, NOW(), NOW());

-- 运费模板
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_freight` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '模板名称',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '计费方式:1按件2按重',
  `is_free` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否全部配送地区包邮',
  `first_unit` decimal(10,2) NOT NULL DEFAULT 1.00 COMMENT '默认首件/首重',
  `first_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '默认首费',
  `continue_unit` decimal(10,2) NOT NULL DEFAULT 1.00 COMMENT '默认续件/续重',
  `continue_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '默认续费',
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='运费模板';

CREATE TABLE IF NOT EXISTS `__PREFIX__shop_freight_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `freight_id` int(11) NOT NULL COMMENT '模板ID',
  `region_ids` text COMMENT '计费地区ID(逗号分隔)',
  `postage_region_ids` text COMMENT '包邮地区ID(逗号分隔,空=全部地区)',
  `first_unit` decimal(10,2) NOT NULL DEFAULT 1.00 COMMENT '首件/首重',
  `first_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '首费',
  `continue_unit` decimal(10,2) NOT NULL DEFAULT 1.00 COMMENT '续件/续重',
  `continue_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '续费',
  `free_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '包邮条件:0无1满件数2满金额',
  `free_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '包邮满额/满件',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `freight_id` (`freight_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='运费模板分区规则';

-- 优惠券定义
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1满减 2折扣 3免邮',
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `condition` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '使用门槛',
  `total` int(11) NOT NULL DEFAULT 0 COMMENT '发放量',
  `used` int(11) NOT NULL DEFAULT 0 COMMENT '已用量',
  `condition_ids` varchar(500) NOT NULL DEFAULT '' COMMENT '优惠券条件ID(逗号分隔)',
  `result` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0折扣 1满减 2免邮',
  `result_data` text COMMENT '优惠结果JSON:money/number',
  `mode` varchar(20) NOT NULL DEFAULT 'dates' COMMENT 'dates日期范围/fixation领取后天数',
  `is_private` varchar(10) NOT NULL DEFAULT 'no' COMMENT 'yes私有券/no公开券',
  `allow_num` int(11) NOT NULL DEFAULT 1 COMMENT '每人最多领取数量',
  `use_days` int(11) NOT NULL DEFAULT 0 COMMENT '领取后有效天数(mode=fixation)',
  `use_start_time` bigint(16) DEFAULT NULL COMMENT '固定使用开始时间(Unix时间戳)',
  `use_end_time` bigint(16) DEFAULT NULL COMMENT '固定使用结束时间(Unix时间戳)',
  `start_time` bigint(16) DEFAULT NULL COMMENT '领取开始时间(Unix时间戳)',
  `end_time` bigint(16) DEFAULT NULL COMMENT '领取结束时间(Unix时间戳)',
  `is_valid` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_is_valid` (`is_valid`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='优惠券';

-- 优惠券标题翻译（优惠券规则本身不按语言复制）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_coupon_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `acode` varchar(20) NOT NULL DEFAULT '' COMMENT 'CMS语言区域编码',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '优惠券标题',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_coupon_acode` (`coupon_id`,`acode`),
  KEY `idx_coupon_lang_acode` (`acode`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='优惠券标题翻译';

-- 优惠券条件(指定商品/新用户/老用户/指定分类/指定品牌)
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_coupon_condition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(2) NOT NULL DEFAULT 1 COMMENT '1商品 2新用户 3老用户 4分类 5品牌',
  `content` varchar(1000) NOT NULL DEFAULT '' COMMENT '条件内容ID(逗号分隔)',
  `created_at` bigint(16) NOT NULL COMMENT '创建时间(Unix时间戳)',
  `updated_at` bigint(16) NOT NULL COMMENT '更新时间(Unix时间戳)',
  PRIMARY KEY (`id`),
  KEY `idx_coupon_condition_type` (`type`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='优惠券条件';

-- 用户优惠券（领券/核销）
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_user_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'unused' COMMENT 'unused/used/expired',
  `begin_time` bigint(16) DEFAULT NULL COMMENT '用户券开始使用时间(Unix时间戳)',
  `expire_time` bigint(16) DEFAULT NULL COMMENT '用户券失效时间(Unix时间戳)',
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_coupon_id` (`coupon_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户优惠券';

-- 积分变动流水
CREATE TABLE IF NOT EXISTS `__PREFIX__shop_integral_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `value` int(11) NOT NULL DEFAULT 0 COMMENT '变动值',
  `balance` int(11) NOT NULL DEFAULT 0 COMMENT '余额',
  `source` varchar(100) DEFAULT '',
  `source_id` int(11) DEFAULT NULL,
  `remark` varchar(255) DEFAULT '',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分流水';

-- 1.1.1 --
-- 安装和升级共用；先检查字段，再执行历史结构迁移。
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_address' AND COLUMN_NAME = 'country_id'),
  'DO 0',
  'ALTER TABLE `__PREFIX__shop_address` ADD COLUMN `country_id` int(11) NOT NULL DEFAULT 0 COMMENT ''国家地区ID'' AFTER `phone`'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_address' AND COLUMN_NAME = 'country'),
  'DO 0',
  'ALTER TABLE `__PREFIX__shop_address` ADD COLUMN `country` varchar(100) NOT NULL DEFAULT '''' COMMENT ''国家地区名称'' AFTER `country_id`'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
ALTER TABLE `__PREFIX__shop_address` MODIFY COLUMN `province` varchar(100) DEFAULT '';
ALTER TABLE `__PREFIX__shop_address` MODIFY COLUMN `city` varchar(100) DEFAULT '';
-- 原国内地址一次性补上国家，并将区县文字合并到城市，不保留旧市区联动。
UPDATE `__PREFIX__shop_address` SET `country_id`=3758, `country`='中国' WHERE `country_id`=0;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_address' AND COLUMN_NAME = 'district'),
  'UPDATE `__PREFIX__shop_address` SET `city`=TRIM(CONCAT(COALESCE(`city`,''''),`district`)) WHERE `district`<>''''',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_address' AND COLUMN_NAME = 'city_id'),
  'ALTER TABLE `__PREFIX__shop_address` DROP COLUMN `city_id`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_address' AND COLUMN_NAME = 'district_id'),
  'ALTER TABLE `__PREFIX__shop_address` DROP COLUMN `district_id`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_address' AND COLUMN_NAME = 'district'),
  'ALTER TABLE `__PREFIX__shop_address` DROP COLUMN `district`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;

SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_region' AND COLUMN_NAME = 'weigh'),
  'DO 0',
  'ALTER TABLE `__PREFIX__shop_region` ADD COLUMN `weigh` int NOT NULL DEFAULT 0 COMMENT ''权重'''
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
-- 原排序越小越靠前，转为权重越大越靠前；转换后移除旧字段，避免重复升级改变权重。
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_region' AND COLUMN_NAME = 'sort'),
  'UPDATE `__PREFIX__shop_region` SET `weigh`=-`sort`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_region' AND COLUMN_NAME = 'sort'),
  'ALTER TABLE `__PREFIX__shop_region` DROP COLUMN `sort`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
ALTER TABLE `__PREFIX__shop_region` MODIFY COLUMN `level` tinyint(1) DEFAULT 0 COMMENT '等级:0国家1省州';
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_region' AND COLUMN_NAME = 'pinyin'),
  'ALTER TABLE `__PREFIX__shop_region` DROP COLUMN `pinyin`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_region' AND COLUMN_NAME = 'py'),
  'ALTER TABLE `__PREFIX__shop_region` DROP COLUMN `py`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_region' AND COLUMN_NAME = 'zipcode'),
  'ALTER TABLE `__PREFIX__shop_region` DROP COLUMN `zipcode`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_region' AND COLUMN_NAME = 'lng'),
  'ALTER TABLE `__PREFIX__shop_region` DROP COLUMN `lng`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
SET @badou_upgrade_sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '__PREFIX__shop_region' AND COLUMN_NAME = 'lat'),
  'ALTER TABLE `__PREFIX__shop_region` DROP COLUMN `lat`',
  'DO 0'
);
PREPARE badou_upgrade_stmt FROM @badou_upgrade_sql;
EXECUTE badou_upgrade_stmt;
DEALLOCATE PREPARE badou_upgrade_stmt;
ALTER TABLE `__PREFIX__shop_region` COMMENT='配送地区表';
-- 中国国家ID由region.sql定义，原省份直接归入中国，城市和区县不再作为地区字典。
UPDATE `__PREFIX__shop_region` SET `pid`=3758 WHERE `level`=1 AND `pid`=0;
DELETE FROM `__PREFIX__shop_region` WHERE `level` IN (2,3);
