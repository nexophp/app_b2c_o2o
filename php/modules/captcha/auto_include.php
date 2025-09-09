<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => '验证码',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina'
];
 
 

add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="bi bi-geo me-2"></i>
            <a href="https://console.tianditu.gov.cn/api/key/" target="_blank"><?= lang('验证码') ?></a>
        </h6>

        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('驱动') ?>
                </label>
                <el-select v-model="form.captcha_drive" placeholder="">
                    <el-option label="<?=lang('腾讯验证码')?>" value="Tencent"></el-option> 
                </el-select>
            </div> 
        </div>

        <div class="row g-3" v-if="form.captcha_drive == 'Tencent'">
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
                    <?= lang('CaptchaAppId') ?>  <a href='https://console.cloud.tencent.com/captcha/graphical' target="_blank"><span class="bi bi-info-circle-fill"></span></a>
                </label>
                <input v-model="form.tencent_captcha_app_id" class="form-control" placeholder="">
            </div> 
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('AppSecretKey') ?>  
                </label>
                <input v-model="form.tencent_captcha_app_key" class="form-control" placeholder="">
            </div> 
		</div>
	</div>
<?php 
});

 


add_action("footer",function(&$data){
    if(!is_login_page()){
        return;
    }
    if(is_local()){
        return;
    }
    get_captcha_init(); 
    global $vue;
    $vue->data("click_type","login");
    $vue->method("login_click()"," 
        $('#t_captcha_input').trigger('click'); 
        this.click_type = 'login';
    ");
    $vue->method("code_click()"," 
        $('#t_captcha_input').trigger('click'); 
        this.click_type = 'code';
    ");
    $vue->method("after_captcha()"," 
        this.form.captcha = {
            appid:this.form.appid,
            ticket:this.form.ticket,
            randstr:this.form.randstr, 
        }; 
        if(this.click_type == 'login'){
            this.login();
        }else{
            this.sendCode();
        }
    ");
});

add_action("AppController.init",function(){
    if(!is_login_action()){
        return;
    }    
    if(is_local()){
        return;
    }
    try {
        get_captcha_check();
    } catch (\Throwable $e) {
        json_error(['msg'=>$e->getMessage()]);
    }
   
});
 