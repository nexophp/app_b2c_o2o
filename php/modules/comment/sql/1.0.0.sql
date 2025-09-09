CREATE TABLE IF NOT EXISTS `comment` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `content` text NOT NULL,
  `images` json DEFAULT NULL,
  `user_id` int(11) NOT NULL, 
  `created_at` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'wait',  
  `ip` varchar(20) DEFAULT NULL, 
  `is_delete` tinyint(1) DEFAULT 0, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `comment_reply` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `reply_id` int(11) DEFAULT NULL, 
  `content` text NOT NULL,
  `images` json DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'wait', 
  `ip` varchar(20) DEFAULT NULL,
  `to_user_id` int(11) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT 0, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;