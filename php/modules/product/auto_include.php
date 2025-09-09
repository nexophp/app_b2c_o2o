<?php

use lib\Time;
use core\Menu;

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => '商品',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina'
];


Menu::setGroup('admin');

// 添加顶级菜单
Menu::add('source', '资源', '', 'bi-suit-club', 900);

Menu::add('product', '商品', '/product/product', '', 200, 'source');
Menu::add('product-type', '商品分类', '/product/type', '', 200, 'source');


include __DIR__ . '/stat/admin.php';


add_action('admin.setting.form', function () {
?>
	<div class="form-group mb-3">
		<h6 class="fw-bold mb-3 border-bottom pb-2">
			商品
		</h6>
		<label for="notice_stock"><?php echo lang('库存预警'); ?></label>
		<input type="number" class="form-control" v-model="form.notice_stock" placeholder="<?php echo lang('库存预警'); ?>">
	</div>
<?php
});
