<?php
 
/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => 'OSS',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];



add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('OSS对象存储') ?>
                </label>
                <el-select v-model="form.oss_drive" placeholder="">
                    <el-option label="<?= lang('本地') ?>" value="Local"></el-option>
                    <el-option label="<?= lang('阿里云') ?>" value="Aliyun"></el-option>
                    <el-option label="<?= lang('腾讯云') ?>" value="Tencent"></el-option>
                    <el-option label="<?= lang('又拍云') ?>" value="UpYun"></el-option>
                </el-select>
            </div>
        </div> 
        

        <div class="row g-3" v-if="form.oss_drive == 'UpYun'">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('ServerName') ?> <a href='https://console.upyun.com/services/file/' target="_blank"><span class="bi bi-info-circle-fill"></span></a>
                </label>
                <input v-model="form.oss_upyun_server_name" class="form-control" placeholder="">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('Account') ?>
                </label>
                <input v-model="form.oss_upyun_account" class="form-control" placeholder="">
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('Password') ?>
                </label>
                <input v-model="form.oss_upyun_password" class="form-control" placeholder="">
            </div>
 
        </div> 


        <div class="row g-3" v-if="form.oss_drive == 'Tencent'">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('SecretId') ?> <a href='https://console.cloud.tencent.com/cam/capi' target="_blank"><span class="bi bi-info-circle-fill"></span></a>
                </label>
                <input v-model="form.tencent_secret_id" class="form-control" placeholder="">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('SecretKey') ?>
                </label>
                <input v-model="form.tencent_secret_key" class="form-control" placeholder="">
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('Bucket') ?>
                </label>
                <input v-model="form.oss_tencent_bucket" class="form-control" placeholder="">
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('Region') ?>
                </label>
                <input v-model="form.oss_tencent_region" class="form-control" placeholder="">
            </div>

        </div> 


        <div class="row g-3" v-if="form.oss_drive == 'Aliyun'">
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
                    <?= lang('endpoint') ?>
                </label>
                <input v-model="form.oss_aliyun_endpoint" class="form-control" placeholder="ap-guangzhou">
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('Bucket') ?>
                </label>
                <input v-model="form.oss_aliyun_bucket" class="form-control" placeholder="">
            </div>
        </div>

    </div>
<?php
});

/**
 * 上传到oss
 * @param string $local_file 本地文件完整地址
 * @return string
 */
function oss_upload($local_file, $object = '')
{
    return modules\oss\lib\Oss::upload($local_file, $object);
}

add_action("upload.success", function ($data) {
    $oss_drive = get_config('oss_drive');
    if ($oss_drive && $oss_drive != 'Local') {
        //上传到oss
        $url = $data['url'];
        $res = db_get_one("oss", "id", [
            'url' => $url,
            'drive' => $oss_drive,
        ]);
        if (!$res) {
            $file = WWW_PATH . $url;
            if (!file_exists($file)) {
                $file = PATH . $url;
            }
            if (oss_upload($file, $url)) {
                db_insert("oss", [
                    'drive' => $oss_drive,
                    'url' => $url,
                    'created_at' => time(),
                ]);
            }
        }
    }
});
