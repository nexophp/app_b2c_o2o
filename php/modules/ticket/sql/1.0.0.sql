CREATE TABLE IF NOT EXISTS `ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '打印机名称',
  `code` varchar(255) NOT NULL COMMENT '打印机编码', 
  `secret` varchar(255) NOT NULL COMMENT '打印机密钥',
  `type` varchar(20) NOT NULL COMMENT '打印机类型', 
  `status` varchar(50) DEFAULT 'success' COMMENT '打印机状态',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11)  NULL COMMENT '更新时间',
  `sys_tag` varchar(50) DEFAULT 'admin' COMMENT '系统标签',
  `seller_id` int(11) NULL DEFAULT 0 COMMENT '卖家ID',
  `store_id` int(11) NULL DEFAULT 0 COMMENT '店铺ID',
  `remark` text COMMENT '备注',
  `device_status` varchar(50) DEFAULT 'success' COMMENT '设备状态',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='小票打印机';
