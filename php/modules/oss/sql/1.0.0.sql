CREATE TABLE IF NOT EXISTS `oss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drive` varchar(255) NOT NULL DEFAULT '' COMMENT '驱动', 
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'url', 
  `created_at` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;