-- 小程序页面管理表
CREATE TABLE IF NOT EXISTS `uniapp_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '页面名称',
  `url` varchar(255) NOT NULL COMMENT '页面URL',
  `share_title` varchar(100) DEFAULT NULL COMMENT '分享标题',
  `share_image` varchar(255) DEFAULT NULL COMMENT '分享图片',
  `page_data` longtext COMMENT '页面设计数据(JSON)',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 1启用 0禁用',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  `is_home` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否首页',

  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='小程序页面管理';
