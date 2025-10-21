<?php

use lib\Time;
use core\Menu;
use modules\product\model\ProductModel;

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

Menu::add('product', '商品', '/product/product', '', 2000, 'source');
Menu::add('product-type', '商品分类', '/product/type', '', 2000, 'source');


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

/**
 * 根据商品唯一码获取商品信息
 * @param string $sku 商品唯一码
 * @return array 商品信息
 */
function get_product_by_sku($sku)
{
	$info = db_get_one("product_spec", "*", [
		'sku' => $sku,
	]);
	if ($info) {
		$data =  ProductModel::model()->findOne($info['product_id']);
		return [
			'id' => $info['product_id'],
			'sku' => $info['sku'],
			'title' => $data->title,
			'spec' => $info['title'],
			'price' => $info['price'],
			'stock' => $info['stock'],
			'image' => $info['image'],
		];
	}
	$info = ProductModel::model()->findOne([
		'sku' => $sku,
	]);
	if ($info) {
		return [
			'id' => $info['id'],
			'sku' => $info['sku'],
			'title' => $info['title'],
			'price' => $info['price'],
			'stock' => $info['stock'],
			'image' => $info['image'],
		];
	}
}

/**
 * 获取商品分类
 * @return array 商品分类
 */
function get_product_type($where = [])
{
	$where['ORDER'] = [
		'sort' => 'ASC',
		'id' => 'ASC'
	];
	$type =  modules\product\model\ProductTypeModel::model()->find($where);
	return $type;
}
/**
 * 搜索商品分类id时调用
 * @param int|array $type_id 分类id
 * @return array $where
 */
function get_product_type_where($type_id, &$where)
{
	if (is_array($type_id)) {
		$type_id_in  = [];
		foreach ($type_id as $tid) {
			$type_in = modules\product\model\ProductTypeModel::model()->getTreeId($tid) ?: [];
			$type_in[] = $tid;
			$type_id_in = array_merge($type_id_in, $type_in);
		}
		if ($type_id_in) {
			$type_id_in = array_unique($type_id_in);
		}
		$where['type_id_last'] = $type_id_in;
	} else {
		$type_in = modules\product\model\ProductTypeModel::model()->getTreeId($type_id) ?: [];
		$type_in[] = $type_id;
		if ($type_in) {
			$type_in = array_unique($type_in);
		}
		$where['type_id_last'] = $type_in;
	}
}
/**
 * 获取商品详情
 * @param int $product_id 商品ID
 * @return array 商品详情
 */
function get_product_view($product_id)
{
	if (is_array($product_id)) {
		return ProductModel::model()->find(['id' => $product_id]);
	}
	return ProductModel::model()->find(['id' => $product_id], 1);
}
/**
 * 获取商品价格
 * @param int $product_id 商品ID
 * @param string $spec 商品规格
 * @return float 商品价格
 */
function get_product_price($product_id, $spec = '')
{ 
	if (!$spec) {
		$res = db_get_one("product", "*", [
			'id' => $product_id,
		]);
		return $res['price'] ?? 0;
	} else {
		$spec = trim($spec); 
		$res = db_get_one("product_spec", "*", [
			'product_id' => $product_id,
			'title' => $spec,
		]);
		return $res['price'] ?? 0;
	}
}
