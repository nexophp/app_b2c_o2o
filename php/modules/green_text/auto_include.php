<?php

use modules\green_text\lib\Image;
use modules\green_text\lib\Text;

/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => '文本安全',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];

/**
 * 文本安全
 * @param string $text 文本，为数组时为图片
 * @return bool
 */
function green_text($text)
{
    if (is_array($text)) {
        foreach ($text as  $item) {
            if ($item && !Image::check($item)) {
                json_error(['msg' => lang('图片包含敏感内容')]);
            }
        }
    } else {
        if ($text && !Text::check($text)) {
            json_error(['msg' => lang('文本包含敏感内容')]);
        }
    }
}



add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="bi bi-chat-left me-2"></i>
            <?= lang('机器审核') ?> 
        </h6>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('是否开启审核') ?>
                </label>
                <el-switch class="ms-5" v-model="form.verify_text" active-value="1" inactive-value="0" active-text="开启" inactive-text="关闭"></el-switch>
            </div>
        </div>

        <div class="row g-3 mb-3" v-if="form.verify_text=='1'">

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('驱动') ?>
                </label>
                <el-select v-model="form.safe_drive" placeholder="">
                    <el-option label="<?= lang('阿里云') ?>" value="Aliyun"></el-option>
                    <el-option label="<?= lang('百度云') ?>" value="Baidu"></el-option>
                </el-select>
            </div>
        </div>

        <div class="row g-3" v-if="form.verify_text=='1' && form.safe_drive == 'Aliyun'">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('AccessKey ID') ?> <a href='https://ram.console.aliyun.com/profile/access-keys' target="_blank"><span class="bi bi-info-circle-fill"></span></a>
                </label>
                <input v-model="form.aliyun_accesskey_id" class="form-control" placeholder="">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('AccessKey Secret') ?>
                </label>
                <input v-model="form.aliyun_accesskey_secret" type="password" class="form-control" placeholder="">

            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('接入区域') ?>
                </label>
                <input v-model="form.aliyun_green_region_id" class="form-control" placeholder="cn-shanghai">
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('接入区域') ?>
                </label>
                <input v-model="form.aliyun_green_endpoint" class="form-control" placeholder="green-cip.cn-shanghai.aliyuncs.com">
            </div>
        </div>

        <div class="row g-3" v-if="form.verify_text=='1' && form.safe_drive == 'Baidu'">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('AppID') ?> <a href='https://console.bce.baidu.com/ai/#/ai/antiporn/app/list' target="_blank"><span class="bi bi-info-circle-fill"></span></a>
                </label>
                <input v-model="form.baidu_antiporn_app_id" class="form-control" placeholder="">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('API Key') ?>
                </label>
                <input v-model="form.baidu_antiporn_app_key" class="form-control" placeholder="">

            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('Secret Key') ?>
                </label>
                <input v-model="form.baidu_antiporn_app_secret" type="password" class="form-control" placeholder="">
            </div>

        </div>


    </div>
<?php
});
