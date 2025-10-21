<?php

use core\Menu;
use modules\coupon\model\CouponModel;

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => '优惠券',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina',
	// 依赖模块
	'depends' => [
		'product',
		'order'
	],
];



Menu::setGroup('admin');
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('coupon', '优惠券', '/coupon/admin', 'bi-chat-dots', 190, 'source');

add_action("order.confirm", function (&$data) {
	$select_coupon = g("select_coupon");
	$coupon = g("coupon");
	if ($select_coupon || $coupon) {
		if($coupon){
			$data['coupon'] = $coupon;
		} 
		$data = modules\coupon\lib\Coupon::do($data);
	}
});
