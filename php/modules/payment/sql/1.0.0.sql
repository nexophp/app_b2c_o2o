CREATE TABLE IF NOT EXISTS `payment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_num` varchar(255) NOT NULL COMMENT '订单号',
  `transaction_id` varchar(255) NOT NULL COMMENT '交易号',
  `order_type` varchar(255)  DEFAULT 'order' COMMENT '订单类型', 
  `amount` decimal(10,2) NOT NULL COMMENT '订单金额',
  `real_amount` decimal(10,2) NOT NULL COMMENT '实际支付金额',
  `can_refund_amount` decimal(10,2) NOT NULL COMMENT '可退款金额', 
  `payment_method` varchar(255)  NOT NULL COMMENT '支付方式',
  `data` json DEFAULT NULL COMMENT '数据',
  `seller_id` int DEFAULT '0' COMMENT '商家ID',
  `user_id` int DEFAULT '0' COMMENT '用户ID',
  `app_id` varchar(255)  DEFAULT NULL COMMENT '应用ID',
  `openid` varchar(255)  DEFAULT NULL COMMENT '用户OpenID',
  `paid_at` int DEFAULT NULL COMMENT '支付时间',
  `created_at` int NOT NULL COMMENT '创建时间', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT '支付';


CREATE TABLE IF NOT EXISTS `payment_refund` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_id` varchar(255)  NOT NULL COMMENT '支付ID',
  `order_num` varchar(255)  NOT NULL COMMENT '订单号',
  `order_type` varchar(255)  DEFAULT 'order' COMMENT '订单类型', 
  `refund_order_num` varchar(255)  NOT NULL COMMENT '退款订单号',
  `amount` decimal(10,2) NOT NULL COMMENT '退款金额',
  `total_amount` decimal(10,2) NOT NULL COMMENT '退款总金额',
  `currency` varchar(255)  NOT NULL COMMENT '退款货币',
  `status` varchar(255)  DEFAULT 'wait' COMMENT '退款状态',
  `payment_method` varchar(255)  NOT NULL COMMENT '退款支付方式',
  `seller_id` int DEFAULT '0' COMMENT '商家ID',
  `user_id` int DEFAULT '0' COMMENT '用户ID',
  `created_at` int NOT NULL COMMENT '创建时间', 
  `paid_at` int DEFAULT NULL COMMENT '支付时间', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT '退款';



CREATE TABLE IF NOT EXISTS `payment_transfer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_num` varchar(255)  NOT NULL COMMENT '订单号',
  `account` varchar(255)  NOT NULL COMMENT '账号',
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `desc` varchar(255)  DEFAULT NULL COMMENT '描述',
  `user_id` int DEFAULT '0' COMMENT '用户ID',
  `store_id` int DEFAULT '0' COMMENT '店铺ID',
  `seller_id` int DEFAULT '0' COMMENT '商家ID',
  `type` varchar(255)  DEFAULT 'weixin' COMMENT '类型',
  `status` varchar(255)  DEFAULT 'success' COMMENT '状态',
  `api_data` json DEFAULT NULL COMMENT '数据',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NOT NULL COMMENT '更新时间', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1  DEFAULT CHARSET=utf8mb4 COMMENT '转账';