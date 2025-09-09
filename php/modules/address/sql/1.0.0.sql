CREATE TABLE IF NOT EXISTS `address` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '地名', 
  `pid` int DEFAULT NULL COMMENT '父ID', 
  `created_at` int DEFAULT NULL COMMENT '创建时间',
  `updated_at` int DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT '收货地址';

CREATE TABLE IF NOT EXISTS `user_address` (
  `id` int NOT NULL AUTO_INCREMENT, 
  `name` varchar(255) DEFAULT '' COMMENT '姓名',
  `phone` varchar(255) DEFAULT '' COMMENT '手机号',
  `region` varchar(255) DEFAULT '' COMMENT '区域', 
  `country` varchar(255) DEFAULT '中国' COMMENT '国家',
  `province` varchar(255) DEFAULT '' COMMENT '省份',
  `city` varchar(255) DEFAULT '' COMMENT '城市',
  `district` varchar(255) DEFAULT '' COMMENT '区县',
  `detail` varchar(255) DEFAULT '' COMMENT '详细地址',
  `user_id` int DEFAULT NULL COMMENT '用户ID',
  `is_default` tinyint(1) DEFAULT 0 COMMENT '是否默认',
  `created_at` int DEFAULT NULL COMMENT '创建时间',
  `updated_at` int DEFAULT NULL COMMENT '更新时间',
  `str_1` varchar(255) DEFAULT '' COMMENT '',
  `str_2` varchar(255) DEFAULT '' COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT '用户收货地址';

 