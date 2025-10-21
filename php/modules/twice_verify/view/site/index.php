<?php
add_css("
.verify-container {
    max-width: 500px;
    margin: 50px auto;
}
.qr-code-img {
    width: 200px;
    height: 200px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
}
");

view_header(lang('验证两次验证'));
global $vue;
$vue->data("url", "");
$vue->data("countdown", $less_time);
$vue->data("countdownTimer", null);
$vue->created(['load()', 'startCountdown()']);
$vue->method("load()", "
ajax('/twice_verify/api/qr', {token: '" . $token . "'}, function(res) {
    if(res.code == 0) {
        _this.url = res.data;
    }
});
");

$vue->method("startCountdown()", "
if(_this.countdown > 0) {
    _this.countdownTimer = setInterval(function() {
        _this.countdown--;
        if(_this.countdown <= 1) {
            clearInterval(_this.countdownTimer);
            setTimeout(function() {
                window.history.back();
            }, 1000);
        }
    }, 1000);
}
");

$vue->method("formatTime(seconds)", "
var minutes = Math.floor(seconds / 60);
var remainingSeconds = seconds % 60;
return minutes + '分' + remainingSeconds + '秒';
");

$vue->method("submit()", "  
    ajax('/twice_verify/api/check', {code: this.form.code, token: '" . $token . "'}, function(res) {
        " . vue_message() . "
        if(res.code == 0){
            window.location.href = '/admin';
        }
    });
");

?>

<div class="verify-container card" style="padding: 20px;">
    <h4 class="text-center mb-4"><?= lang('两步验证') ?></h4>
    
    <!-- 倒计时显示 -->
    <div class="alert alert-warning text-center mb-4" v-if="countdown > 0">
        <i class="bi bi-clock"></i>
        <strong>页面将在 {{formatTime(countdown)}} 后失效</strong>
    </div>
    
    <div class="mb-3 text-center">
        <div class="input-group">
            <input autofocus
                type="text"
                class="form-control form-control-lg"
                id="verifyCode"
                placeholder="<?= lang('请输入Google Authenticator中的6位验证码') ?>"
                maxlength="6"
                v-model="form.code"
                required>
        </div>
    </div>

    <el-button @click="submit()"  class="btn btn-primary btn-lg w-100"  type="primary">
        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
        <span id="submitText"><?= lang('提交验证') ?></span>
    </el-button>
</div>

<?php
view_footer();
?>