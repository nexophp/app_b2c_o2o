CREATE TABLE IF NOT EXISTS `o2o_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '地址简称',
  `regions` text COMMENT '配送区域(JSON格式)',
  `detail` varchar(255) COMMENT '详细地址',
  `lat` decimal(10,6)  NULL DEFAULT '0.000000' COMMENT '纬度',
  `lng` decimal(10,6)  NULL DEFAULT '0.000000' COMMENT '经度',
  `status` varchar(20) NOT NULL DEFAULT 'success' COMMENT '状态(success:启用 error:禁用)',
  `seller_id` int(11) NOT NULL DEFAULT '0' COMMENT '商家ID',
  `sys_tag` varchar(50) NOT NULL DEFAULT 'o2o' COMMENT '系统标签',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='O2O配送地址表';