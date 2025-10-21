CREATE TABLE IF NOT EXISTS `banner` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'Banner标题',
  `image` varchar(255) DEFAULT NULL COMMENT 'Banner图片',
  `app_id` varchar(500) DEFAULT NULL COMMENT '小程序AppID',
  `url` varchar(500) DEFAULT NULL COMMENT '跳转链接',
  `type` varchar(50) DEFAULT 'web' COMMENT 'Banner类型：web网页，minapp小程序',
  `sort` int DEFAULT '0' COMMENT '排序权重，数值越大越靠前',
  `status` tinyint DEFAULT '1' COMMENT '状态：0禁用，1启用', 
  `click_count` int DEFAULT '0' COMMENT '点击次数',
  `user_id` int DEFAULT '0' COMMENT '创建者ID',
  `sys_tag` varchar(50) DEFAULT 'admin' COMMENT '系统标签',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='Banner轮播图表';
 