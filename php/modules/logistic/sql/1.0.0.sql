CREATE TABLE IF NOT EXISTS `logistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `status` int(3) DEFAULT 1,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 运费模板表
CREATE TABLE IF NOT EXISTS `logistic_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '模板名称',
  `seller_id` int(11) NOT NULL COMMENT '卖家ID',
  `is_free_shipping` tinyint(1) DEFAULT 0 COMMENT '是否包邮',
  `free_shipping_amount` decimal(10,2) DEFAULT 0 COMMENT '包邮金额',
  `delivery_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '计费方式：1按件数，2按重量，3按体积',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0禁用，1启用',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='运费模板表';

-- 运费模板区域表
CREATE TABLE IF NOT EXISTS `logistic_template_region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL COMMENT '模板ID',
  `region_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '区域类型：1默认区域，2指定区域，3偏远地区',
  `regions` text COMMENT '区域列表，JSON格式', 
  `first_fee` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '首件积费用', 
  `additional_fee` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '续件积费用',
  `is_free_shipping` tinyint(1) DEFAULT 0 COMMENT '是否包邮',
  `free_shipping_amount` decimal(10,2) DEFAULT 0 COMMENT '包邮金额',
  `first_weight` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '首重',
  `additional_weight` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '续重',


  PRIMARY KEY (`id`),
  KEY `idx_template` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='运费模板区域表';

