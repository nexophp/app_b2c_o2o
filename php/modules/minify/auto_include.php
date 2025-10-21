<?php

use modules\minify\lib\Minify;


/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => '压缩css、js',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina'
];


add_action("css_code", function ($all) {});

add_action("js_files", function (&$all) {
	if (!get_config('minify_js')) {
		return;
	}
	$js = $all[0];
	$ret = Minify::js($js);
	if (!$ret) {
		return;
	}
	echo "<script src='$ret'></script>";
	$all[1] = true;
});

/**
 * 不要轻易合并css,因为每个css有自己的font,这样必须先修正css中的font路径才能合并
 * 
 */
add_action("css_files", function (&$all) {
	if (!get_config('minify_css')) {
		return;
	}
	$js = $all[0];
	$ret = Minify::css($js);
	if (!$ret) {
		return;
	}
	echo "<link rel='stylesheet' href='$ret'>";
	$all[1] = true;
});

add_action("vue", function (&$code) {
	if (!get_config('minify_js')) {
		return;
	}
	$ret = Minify::jsCode($code);
	if (!$ret) {
		return;
	}
	echo "<script src='$ret'></script>";
	$code = '';
});


add_action('admin.setting.form', function () {
?>
	<div class="mb-4">
		<h6 class="fw-bold mb-3 border-bottom pb-2">
			<i class="bi bi-wechat me-2"></i><?= lang('合并JS、CSS') ?>
		</h6>
		<div class="row g-3">
			<div class="col-md-3">
				<label class="form-label">
					<?= lang('合并js') ?>
				</label>
				<el-switch v-model="form.minify_js" type="checkbox" active-value="1" inactive-value="0"></el-switch>
			</div>
			<div class="col-md-3">
				<label class="form-label">
					<?= lang('合并css,注意处理css中的font路径') ?>

				</label>
				<el-switch v-model="form.minify_css" type="checkbox" active-value="1" inactive-value="0"></el-switch>
			</div>

		</div>
	</div>
<?php
});
