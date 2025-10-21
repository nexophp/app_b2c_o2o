CREATE TABLE IF NOT EXISTS `invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `type` varchar(255) NOT NULL COMMENT '发票类型',
  `title` varchar(255) NOT NULL COMMENT '发票抬头',
  `content` text NOT NULL COMMENT '发票内容',
  `amount` decimal(10,2) NOT NULL COMMENT '发票金额',
   `created_at` int(11) NOT NULL,
  `updated_at` int(11) NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发票';

CREATE TABLE IF NOT EXISTS `invoice_title` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `type` varchar(255) NOT NULL COMMENT '发票类型',
  `title` varchar(255) NOT NULL COMMENT '发票抬头',
  `tax_no` varchar(255)  NULL COMMENT '纳税人识别号',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发票抬头';

CREATE TABLE IF NOT EXISTS `invoice_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `invoice_id` int(11)  NULL COMMENT '发票ID', 
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `type` varchar(255) NOT NULL COMMENT '发票类型',
  `title` varchar(255) NOT NULL COMMENT '发票抬头',
  `tax_no` varchar(255)  NULL COMMENT '纳税人识别号',

  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发票订单';
