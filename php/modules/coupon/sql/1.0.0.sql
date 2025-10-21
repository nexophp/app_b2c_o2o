CREATE TABLE IF NOT EXISTS `coupon` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL COMMENT '优惠券名称',
    `type` int(11) NOT NULL COMMENT '优惠券类型，1满减，2折扣', 
    `value` decimal(10,2) NOT NULL COMMENT '优惠券金额，满减时为减金额，折扣时为折扣比例，例如100元满减10元，折扣为0.9',
    `condition` decimal(10,2) NOT NULL COMMENT '优惠券最低使用金额', 
    `days` int(11) NOT NULL COMMENT '优惠券有效天数',    
    `status` int(11)  NULL DEFAULT 1 COMMENT '优惠券状态,1可用，-1已删除 ',
    `created_at` int(11) NOT NULL COMMENT '创建时间', 
    `user_tag` varchar(255) NULL DEFAULT 'admin' COMMENT '默认admin平台',
    `seller_id` int(11) NULL DEFAULT 0 COMMENT '优惠券所属商家，0为全部',
    `store_id` int(11) NULL DEFAULT 0 COMMENT '优惠券所属店铺，0为全部',
    `products` JSON COMMENT '优惠券适用商品',
    `types` JSON COMMENT '优惠券适用类型', 
    `total` int(11) NULL DEFAULT 0 COMMENT '优惠券总数量',
    `used_total` int(11) NULL DEFAULT 0 COMMENT '优惠券已使用数量',
    `left_total` int(11) NULL DEFAULT 0 COMMENT '优惠券剩余数量',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `coupon_user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `coupon_id` int(11) NOT NULL COMMENT '优惠券id',
    `type` int(11) NOT NULL COMMENT '优惠券类型，1满减，2折扣', 
    `value` decimal(10,2) NOT NULL COMMENT '优惠券金额，满减时为减金额，折扣时为折扣比例，例如100元满减10元，折扣为0.9',
    `condition` decimal(10,2) NOT NULL COMMENT '优惠券最低使用金额',
    `user_id` int(11) NOT NULL COMMENT '用户id',
    `created_at` int(11) NOT NULL COMMENT '领取时间',
    `used_at` int(11) NULL COMMENT '使用时间',
    `expired_at` int(11) NULL COMMENT '过期时间',
    `order_id` int(11) NULL COMMENT '订单id',
    `status` int(11)  NULL DEFAULT 1 COMMENT '优惠券状态,1可用，2已使用，-1已过期', 
    `products` JSON COMMENT '优惠券适用商品',
    `types` JSON COMMENT '优惠券适用类型', 
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

