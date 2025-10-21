<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => '支付',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];



add_action('admin.setting.form', function () {
    global $vue;
    $vue->method('uploadFile(field)', " 
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.pem,.key,.crt,.cer';
        fileInput.onchange = (e) => {
            if (e.target.files.length > 0) {
                const formData = new FormData();
                formData.append('file', e.target.files[0]);   
                const that = this; 
                $.ajax({
                    url: '/admin/upload',
                    type: 'POST',
                    data: formData,
                    processData: false, 
                    contentType: false,  
                    success: function(res)  {
                        res = JSON.parse(res);
                        " . vue_message() . " 
                        let url = res.data.url; 
                        if (res.code == 0 && res.data && res.data.url) {
                            that.form[field] = res.data.url;   
                            that.\$forceUpdate();
                        }
                    },
                    error: function() {
                        that.\$message.error('" . lang("上传失败") . "');
                    }
                });
            }
        };
        fileInput.click(); 
    ");
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="bi bi-credit-card me-2"></i><?= lang('微信支付') ?>

        </h6>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('商户号') ?>
                </label>
                <input v-model="form.payment_config_weixin_mch_id" class="form-control" placeholder="mch_id">
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    apiclient_key.pem
                </label>
                <div class="input-group">
                    <input v-model="form.payment_config_weixin_key_path" class="form-control" placeholder="" readonly>
                    <button class="btn btn-outline-secondary" type="button" @click="uploadFile('payment_config_weixin_key_path')">
                        <i class="bi bi-upload"></i> <?= lang('上传') ?>
                    </button>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    apiclient_cert.pem
                </label>
                <div class="input-group">
                    <input v-model="form.payment_config_weixin_cert_path" class="form-control" placeholder="" readonly>
                    <button class="btn btn-outline-secondary" type="button" @click="uploadFile('payment_config_weixin_cert_path')">
                        <i class="bi bi-upload"></i> <?= lang('上传') ?>
                    </button>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('API秘钥 V3') ?>
                </label>
                <input v-model="form.payment_config_weixin_secret_key" class="form-control" placeholder="">
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('API秘钥 V2') ?>
                </label>
                <input v-model="form.payment_config_weixin_v2_secret_key" class="form-control" placeholder="">
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('平台证书') ?>
                </label>
                <div class="input-group">
                    <input v-model="form.payment_config_weixin_platform_cert" class="form-control" placeholder="" readonly>
                    <button class="btn btn-outline-secondary" type="button" @click="uploadFile('payment_config_weixin_platform_cert')">
                        <i class="bi bi-upload"></i> <?= lang('上传') ?>
                    </button>
                </div>
            </div>

        </div>
    </div>
<?php

});

// 添加上传文件的方法
add_action('admin.setting.methods', function () {
?>

<?php
});



add_action('admin.setting.form', function () {
    global $vue;
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            支付宝条码支付

        </h6>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">
                    当面付应用APPID
                </label>
                <input v-model="form.alipay_appid" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    当面付应用私钥
                </label>
                <textarea v-model="form.alipay_rsa_private_key" class="form-control"></textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    支付宝公钥
                </label>
                <textarea v-model="form.alipay_public_key" class="form-control"></textarea>
            </div>

        </div>
    </div>

<?php
});
