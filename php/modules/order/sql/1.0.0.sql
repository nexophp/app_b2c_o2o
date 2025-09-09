CREATE TABLE IF NOT EXISTS `order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_num` varchar(255)  DEFAULT NULL COMMENT '订单号',
  `type` varchar(255)  DEFAULT 'product' COMMENT '类型',  
  
  `num` int DEFAULT '1' COMMENT '商品数量',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '商品总价',
  `desc` varchar(255)  DEFAULT NULL COMMENT '备注',
  `real_amount` decimal(10,2) DEFAULT NULL COMMENT '实际支付金额',
  `status` varchar(50)  DEFAULT 'wait' COMMENT '订单状态',

  `payment_method` varchar(50)  DEFAULT NULL COMMENT '支付方式', 

  `address` varchar(255)  DEFAULT NULL COMMENT '地址',
  `phone` varchar(255)  DEFAULT NULL COMMENT '电话',
  `name` varchar(255)  DEFAULT NULL COMMENT '姓名',
  `ship_status` varchar(50)  DEFAULT 'none' COMMENT '发货状态,默认不需要发货',
  `in_wallet` int DEFAULT '0' COMMENT '是否加入收益',
  `can_refund_amount` decimal(10,2) DEFAULT '0.00' COMMENT '可退款金额',
  `has_refund_amount` decimal(10,2) DEFAULT '0.00' COMMENT '已退款金额',
  `real_get_amount` decimal(10,2) DEFAULT '0.00' COMMENT '实际获得金额',

  `is_lock` int DEFAULT '0' COMMENT '是否锁定',
  `lock_at` int DEFAULT NULL COMMENT '锁定时间',


  `user_tag` varchar(20)  DEFAULT 'admin' COMMENT '商品属于谁',
  `admin_id` int DEFAULT '0' COMMENT '管理员ID',
  `store_id` int DEFAULT '0' COMMENT '门店ID',
  `seller_id` int DEFAULT '0' COMMENT '商家ID',
  `user_id` int DEFAULT NULL COMMENT '用户ID',
  `g_id` varchar(255) DEFAULT NULL COMMENT '游客ID',

  `ship_type` varchar(50) DEFAULT 'none' COMMENT '发货类型', 

  `sys_tag` varchar(100) DEFAULT 'product' COMMENT '系统标签',

  `point` int DEFAULT '0' COMMENT '积分',
  
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '订单';



CREATE TABLE IF NOT EXISTS `order_item` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL COMMENT '购物车ID',
  `order_num` varchar(255)  DEFAULT NULL COMMENT '订单号',
  `type` varchar(255)  DEFAULT 'product' COMMENT '类型',
  `type_1` varchar(255)  DEFAULT NULL COMMENT '类型',
  `seller_id` int DEFAULT NULL COMMENT '商家ID',
  `store_id` int DEFAULT NULL COMMENT '门店ID',
  `user_id` int DEFAULT NULL COMMENT '用户ID',
  `product_id` varchar(255)  DEFAULT NULL COMMENT '商品ID',
  `spec` varchar(255)  DEFAULT NULL COMMENT '商品规格',
  `attr` varchar(255)  DEFAULT NULL COMMENT '商品属性',
  `title` varchar(255)  DEFAULT NULL COMMENT '商品名称',
  `ori_price` decimal(10,2) DEFAULT NULL COMMENT '商品单价',
  `price` decimal(10,2) DEFAULT NULL COMMENT '商品单价',
  `image` varchar(255)  DEFAULT NULL COMMENT '商品图片',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '商品总价',
  `num` int DEFAULT '1' COMMENT '商品数量',
  `real_price` decimal(10,2) DEFAULT NULL COMMENT '实际单价',
  `real_amount` decimal(10,2) DEFAULT NULL COMMENT '实际总价',
  `status` varchar(50)  DEFAULT 'wait' COMMENT '订单状态',
  `str_1` varchar(255)  DEFAULT NULL COMMENT '商品参数1',
  `str_2` varchar(255)  DEFAULT NULL COMMENT '商品参数2',
  `param_1` json DEFAULT NULL COMMENT '商品参数1',
  `param_2` json DEFAULT NULL COMMENT '商品参数2',
  `can_refund_num` int DEFAULT '0' COMMENT '可退款数量',
  `refund_num` int DEFAULT '0' COMMENT '已退款数量',
  `can_refund_amount` decimal(10,2) DEFAULT '0.00' COMMENT '可退款金额',
  `has_refund_amount` decimal(10,2) DEFAULT '0.00' COMMENT '已退款金额',
  `real_get_amount` decimal(10,2) DEFAULT '0.00' COMMENT '实际获得金额',
  `discount_amount` decimal(10,2) DEFAULT '0.00' COMMENT '优惠金额',
  `point` int DEFAULT '0' COMMENT '积分',
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '订单明细';

CREATE TABLE IF NOT EXISTS `order_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL COMMENT '订单ID',
  `nid` int(11) DEFAULT NULL COMMENT '优惠券ID',
  `type` varchar(255)  DEFAULT 'coupon' COMMENT '类型',
  `value` decimal(10,2) DEFAULT '0.00' COMMENT '优惠金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '订单信息';


CREATE TABLE IF NOT EXISTS `order_paid_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL COMMENT '订单ID',
  `title` varchar(255)  DEFAULT NULL COMMENT '名称',
  `nid` int(11) DEFAULT NULL COMMENT '优惠券ID',
  `type` varchar(255)  DEFAULT 'coupon' COMMENT '类型',
  `num` int(11) DEFAULT '1' COMMENT '数量', 
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '抵扣金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '订单支付信息';


 

CREATE TABLE IF NOT EXISTS `order_logistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_num` varchar(255) NOT NULL comment '订单编号', 
  `no` varchar(255) NOT NULL comment '物流单号',
  `type` varchar(255) NOT NULL comment '物流类型',
  `data` json DEFAULT NULL comment '物流数据',
  `status` varchar(50) DEFAULT 'wait' comment '物流状态',
  `logic_type` varchar(100) DEFAULT 'order' comment '类型',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `order_refund` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_num` varchar(255)  DEFAULT NULL COMMENT '售后单号',
  `order_id` int(11) DEFAULT NULL COMMENT '订单ID',
  `type` varchar(255)  DEFAULT 'refund' COMMENT '类型',  
  `reason` varchar(255)  DEFAULT NULL COMMENT '退款原因',
  
  `num` int DEFAULT '1' COMMENT '商品数量',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '商品总价',

  `desc` varchar(255)  DEFAULT NULL COMMENT '备注',
  `admin_desc` varchar(255)  DEFAULT NULL COMMENT '备注',
  `images` json  DEFAULT NULL COMMENT '图片', 
  `status` varchar(50)  DEFAULT 'wait' COMMENT '订单状态',
  

  `user_tag` varchar(20)  DEFAULT 'user' COMMENT '商品属于谁',

  `admin_id` int DEFAULT '0' COMMENT '管理员ID',
  `store_id` int DEFAULT '0' COMMENT '门店ID',
  `seller_id` int DEFAULT '0' COMMENT '商家ID',
  `user_id` int DEFAULT NULL COMMENT '用户ID', 
 
  
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '售后订单'; 


CREATE TABLE IF NOT EXISTS `order_refund_item` (
  `id` int NOT NULL AUTO_INCREMENT,
  `refund_id` int DEFAULT NULL COMMENT '售后订单ID',
  `order_id` int DEFAULT NULL COMMENT '原订单ID',
  `order_item_id` int DEFAULT NULL COMMENT '原订单明细ID',
   
  `title` varchar(255)  DEFAULT NULL COMMENT '商品名称', 
  `price` decimal(10,2) DEFAULT NULL COMMENT '商品单价',
  `image` varchar(255)  DEFAULT NULL COMMENT '商品图片',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '商品总价',

  `num` int DEFAULT '1' COMMENT '商品数量',  

  `status` varchar(50)  DEFAULT 'wait' COMMENT '订单状态',

  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '售后订单明细';


CREATE TABLE IF NOT EXISTS `order_refund_address` (
  `id` int NOT NULL AUTO_INCREMENT,
  `refund_id` int DEFAULT NULL COMMENT '售后订单ID',
  `name` varchar(255)  DEFAULT NULL COMMENT '姓名',
  `phone` varchar(255)  DEFAULT NULL COMMENT '电话',
  `address` varchar(255)  DEFAULT NULL COMMENT '地址',
  `type` varchar(255)  DEFAULT 'user' COMMENT '类型 user是用户发过来的，admin是商家发给用户的',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '售后订单地址';

CREATE TABLE IF NOT EXISTS `order_refund_money` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL COMMENT '订单ID',
  `user_id` int DEFAULT NULL COMMENT '用户ID',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '退款金额',
  `status` varchar(50)  DEFAULT 'wait' COMMENT '状态',
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '售后订单金额';
