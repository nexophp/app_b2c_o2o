<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => 'Webpos',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina'
];


Menu::setGroup('admin');

// 添加顶级菜单
Menu::add('source', '资源', '', 'bi-suit-club', 900);

Menu::add('webpos', '收银', '/webpos/webpos', '', 100, 'source');

Menu::add('webpos-order', '收银订单', '/webpos/admin', '', 100, 'source');

/**
 * 是否是支付宝付款码
 * https://opendocs.alipay.com/open/194/106039/
 * 25-30 开头 ，16-24 位
 */
function get_webpos_code_is_alipay($code)
{
	$top = substr($code, 0, 2);
	$len = strlen($code);
	if ($top >= 25 && $top <= 30 && $len >= 16 && $len <= 24) {
		return true;
	} else {
		return false;
	}
}
/**
 * 是否是微信付款码
 * https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=5_1
 * 用户付款码规则：18位纯数字，前缀以10、11、12、13、14、15开头
 */
function get_webpos_code_is_weixin($code)
{
	$top = substr($code, 0, 2);
	$len = strlen($code);
	if ($top >= 10 && $top <= 15 && $len == 18) {
		return true;
	} else {
		return false;
	}
}
