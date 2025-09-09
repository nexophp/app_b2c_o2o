CREATE TABLE IF NOT EXISTS `wallet` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
  `in_amount` decimal(10,2) DEFAULT '0.00' COMMENT '入金金额',
  `wait_in_amount` decimal(10,2) DEFAULT '0.00' COMMENT '待入金金额',
  `can_out_amount` decimal(10,2) DEFAULT '0.00' COMMENT '可出金金额',
  `out_amount` decimal(10,2) DEFAULT '0.00' COMMENT '出金金额',
  `wait_out_amount` decimal(10,2) DEFAULT '0.00' COMMENT '待出金金额',
  `user_id` int DEFAULT '0' COMMENT '用户ID',
  `seller_id` int DEFAULT '0' COMMENT '商户ID',
  `store_id` int DEFAULT '0' COMMENT '店铺ID', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ;

CREATE TABLE IF NOT EXISTS `wallet_in_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(20)  NOT NULL COMMENT ' ',
  `type` varchar(20)  NOT NULL COMMENT 'in',  
  `user_id` int DEFAULT '0' COMMENT '用户ID',
  `seller_id` int DEFAULT '0' COMMENT '商户ID',
  `store_id` int DEFAULT '0' COMMENT '店铺ID', 

  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ;


CREATE TABLE IF NOT EXISTS `wallet_cash_in` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_num` varchar(30)  DEFAULT NULL COMMENT '订单号',
  `type` varchar(20)  NOT NULL COMMENT '类型,如shop',
  `desc` varchar(255)  DEFAULT NULL COMMENT '描述',
  `status` varchar(10)  DEFAULT 'wait' COMMENT '状态', 
  `order_amount` decimal(10,2) NOT NULL COMMENT '订单金额',
  `rate` decimal(11,3) NOT NULL COMMENT '比例',
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NOT NULL COMMENT '更新时间',
  `user_id` int DEFAULT '0' COMMENT '用户ID',
  `seller_id` int DEFAULT '0' COMMENT '商户ID',
  `store_id` int DEFAULT '0' COMMENT '店铺ID', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ;

CREATE TABLE IF NOT EXISTS `wallet_cash_out` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_num` varchar(30)  NOT NULL COMMENT '订单号',
  `desc` varchar(255)  DEFAULT NULL COMMENT '描述',
  `status` varchar(10)  DEFAULT 'pending' COMMENT '状态',
  `type` varchar(20)  DEFAULT 'weixin' COMMENT '类型', 
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `rate` decimal(11,3) NOT NULL COMMENT '比例',
  `rate_amount` decimal(10,2) NOT NULL COMMENT '费率金额',
  `real_amount` decimal(10,2) NOT NULL COMMENT '实际金额', 
  `account` json DEFAULT NULL COMMENT '账号',
  `user_id` int DEFAULT '0' COMMENT '用户ID',
  `seller_id` int DEFAULT '0' COMMENT '商户ID',
  `store_id` int DEFAULT '0' COMMENT '店铺ID', 
  `op_user_id` int DEFAULT '0' COMMENT '操作用户ID',
  `op_images` json DEFAULT NULL COMMENT '操作图片',
  `paid_at` int DEFAULT '0' COMMENT '支付时间',
  `paid_method` varchar(50)  DEFAULT '' COMMENT '支付方式',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ;


CREATE TABLE IF NOT EXISTS `wallet_transfer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_num` varchar(30)  DEFAULT NULL COMMENT '订单号',
  `nid` int DEFAULT '0' COMMENT '提现订单ID',
  `type` varchar(20)  DEFAULT 'weixin' COMMENT '类型', 
  `account` varchar(255)  DEFAULT '' COMMENT '账号',
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `status` varchar(10)  DEFAULT 'wait' COMMENT '状态',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ;
