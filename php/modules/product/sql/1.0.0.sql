CREATE TABLE IF NOT EXISTS `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_type` varchar(50) DEFAULT 'product' COMMENT '商品类型',
  `weight` decimal(10,2) DEFAULT '0.00' COMMENT '商品重量',
  `freight_template_id` int DEFAULT '0' COMMENT '运费模板ID',
  
  `type_id` JSON DEFAULT NULL COMMENT '商品分类',
  `type_id_last` int DEFAULT '0' COMMENT '商品分类',
  `brand_id` int DEFAULT '0' COMMENT '商品品牌',   

  `title` varchar(255)  NOT NULL COMMENT '商品名称',
  `desc` text  COMMENT '商品描述',
  `body` text  COMMENT '商品详情',
  `image` varchar(255)  DEFAULT NULL COMMENT '商品主图',
  `images` JSON DEFAULT NULL COMMENT '商品图片列表',  
 
  `product_num` varchar(255)  DEFAULT NULL COMMENT '商品编号', 
  `sku` varchar(255)  DEFAULT NULL COMMENT '商品SKU', 
  `spec_type` int DEFAULT '1' COMMENT '商品规格类型 1单规格，2多规格', 

  `sort` int DEFAULT '0' COMMENT '商品排序',  
  `status` varchar(20)  DEFAULT 'wait' COMMENT '商品状态,wait待审核,success审核通过,error审核拒绝', 
 
  `user_tag` varchar(20)  DEFAULT 'admin' COMMENT '商品属于谁',
  `admin_id` int DEFAULT '0' COMMENT '管理员ID',
  `store_id` int DEFAULT '0' COMMENT '门店ID',
  `seller_id` int DEFAULT '0' COMMENT '商家ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '创建者',  
  
  
  `sales` int DEFAULT '0' COMMENT '商品销量', 
  `views` int DEFAULT '0' COMMENT '商品浏览量',
  `market_price` decimal(10,2) DEFAULT '0.00' COMMENT '市场价',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '商品价格',
  `stock` int DEFAULT '0' COMMENT '商品库存',

  `score` decimal(3,1) DEFAULT '5.0' COMMENT '商品评分',

  `comment` int DEFAULT '0' COMMENT '商品评论数',

  `sys_tag` varchar(50) DEFAULT 'product' COMMENT '系统标签',

  `attr` JSON DEFAULT NULL COMMENT '商品属性',
  
  `point` int DEFAULT '0' COMMENT '商品积分',

  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NOT NULL COMMENT '更新时间',

  `length` int(11) DEFAULT '0.00' COMMENT '商品长度',
  `width` int(11) DEFAULT '0.00' COMMENT '商品宽度',
  `height` int(11) DEFAULT '0.00' COMMENT '商品高度', 

  `recommend` int DEFAULT '0' COMMENT '商品推荐',
  `recommend_at` int DEFAULT '0' COMMENT '推荐时间',
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '商品表';
 

CREATE TABLE IF NOT EXISTS `product_spec` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL COMMENT '商品ID',
  `title` varchar(255)  DEFAULT NULL COMMENT '规格名称',  
  `sku` varchar(255)  DEFAULT NULL COMMENT '商品唯一码',  
  `image` varchar(255)  DEFAULT NULL COMMENT '规格图片', 
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '商品价格',
  `market_price` decimal(10,2) DEFAULT '0.00' COMMENT '市场价',
  `stock` int DEFAULT '0' COMMENT '商品库存', 
  `status` int DEFAULT '0' COMMENT '规格状态 0正常 1删除', 
  `is_default` int DEFAULT '0' COMMENT '是否默认规格',
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT '商品规格表';
 




CREATE TABLE IF NOT EXISTS `product_brand` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255)  NOT NULL COMMENT '品牌名称',
  `slug` varchar(255)  NOT NULL COMMENT '品牌别名', 
  `image` varchar(255)  DEFAULT NULL COMMENT '品牌图片',
  `status` varchar(20)  DEFAULT 'wait' COMMENT '品牌状态,wait待审核,success审核通过,error审核拒绝', 
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '创建者',
  `sys_tag` varchar(50) DEFAULT 'product' COMMENT '系统标签',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NOT NULL COMMENT '更新时间', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT '品牌表';



CREATE TABLE IF NOT EXISTS `product_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255)  NOT NULL COMMENT '分类名称',
  `slug` varchar(255)  NOT NULL COMMENT '分类别名',
  `description` text  COMMENT '分类描述',
  `image` varchar(255)  DEFAULT NULL COMMENT '分类图片',
  `pid` int NOT NULL DEFAULT '0', 
  `user_id` int NOT NULL DEFAULT '0' COMMENT '创建者',
  `sort` int NOT NULL DEFAULT '0' COMMENT '排序',
  `status` varchar(20)  DEFAULT 'wait' COMMENT '分类状态,wait待审核,success审核通过,error审核拒绝', 
  `sys_tag` varchar(50) DEFAULT 'product' COMMENT '系统标签',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NOT NULL COMMENT '更新时间', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4  COMMENT '商品分类表';


