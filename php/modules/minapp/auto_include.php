<?php 

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0', 
	'title' => '小程序',
	'description' => '', 
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina'
];  

add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="bi bi-wechat me-2"></i><?= lang('微信小程序') ?> 
        </h6>
		<div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('小程序appid') ?>
                </label>
               <input v-model="form.minapp_app_id" type="text" class="form-control" placeholder="">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('小程序secret') ?>
                </label>
               <input v-model="form.minapp_secret" type="text" class="form-control" placeholder="">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('小程序token') ?>
                </label>
               <input v-model="form.minapp_token" type="text" class="form-control" placeholder="">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('小程序aes_key') ?>
                </label>
               <input v-model="form.minapp_aes_key" type="text" class="form-control" placeholder="">
            </div>
        </div>
    </div>
<?php 
});
