<?php
$module_info = [
    'version' => '1.0.0',
    'title' => '二次验证',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];

/**
 * 验证两次验证
 * @return bool
 */
function check_twice_verify($secret, $code)
{
    $ga = new \modules\twice_verify\lib\GoogleAuthenticator();
    $checkResult = $ga->verifyCode($secret, $code, 2);
    if (!$checkResult) {
        return json_error(['msg' => lang('两次验证失败')]);
    }
}

add_action("admin.login", function ($user) {
    $user = get_user($user['id']);
    if (!$user['twice_verify']) {
        return true;
    }
    $token = aes_encode([
        'uid' => $user['id'],
        'time' => time(),
        'rand' => rand(100000, 999999),
        'exp' => time() + 60 * 5,
    ]);
    json_error(['jump' => url('/twice_verify/site/index', ['token' => $token])]);
});

add_action("bind_account", function () {
    global $vue;
    global $uid;
    $user = get_user($uid);
    $secret = $user['twice_verify'];
    $name = $user['phone'] ?: $user['username'];
    $name = $name ?: $user['id'];
    $vue->data("twice_verify_qr_url", "");
    $vue->data("has_twice_verify", "");
    $vue->created(['load_twice_verify()']);
    $vue->method("load_twice_verify()", "
    ajax('/twice_verify/api/user-qr', {name: '" . $name . "'}, function(res) {
        if(res.code == 0) {
            _this.twice_verify_qr_url = res.data;
            _this.has_twice_verify = res.has;
        }
    });
    ");

    $vue->method("check_twice_verify()", "  
        ajax('/twice_verify/api/bind', {code: this.form.twice_verify}, function(res) {
            " . vue_message() . "
            if(res.code == 0) {
                _this.load_twice_verify();
            }
        });
    ");

    $vue->method("unbind_twice_verify()", "
        this.\$confirm('确定要取消二次验证绑定吗？', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
        }).then(() => {
            ajax('/twice_verify/api/unbind', {}, function(res) {
                " . vue_message() . "
                if(res.code == 0) {
                    _this.load_twice_verify();
                }
            });
        }).catch(() => {});
    ");

    $vue->data("show_twice_verify", false);
    $vue->method("open_twice_verify()", "
        this.show_twice_verify = true;
        this.\$nextTick(() => {
            this.\$refs.input.focus();
        })
    ");
?>
    <li class="bind-item">
        <div class="d-flex align-items-center">
            <i class="bi bi-shield-lock bind-icon"></i>
            <div class="bind-info">
                <div class="bind-title"><?= lang("二次验证") ?></div>
                <div class="bind-status">

                </div>
            </div>
        </div>
        <div class="bind-action">
            <el-button v-if="has_twice_verify" type="primary" @click="open_twice_verify()">
                <?= lang("二次验证") ?>
                [<?= lang("已绑定") ?>]
            </el-button>
            <el-button v-else type="default" @click="open_twice_verify()">
                <?= lang("二次验证") ?>
                [<?= lang("未绑定") ?>]
            </el-button>
        </div>
    </li>

    <el-dialog
        :close-on-click-modal="false"
        title="<?= lang('二次验证') ?>"
        :visible.sync="show_twice_verify"
        width="90%">
        <div class="verify-container">
            <h4 class="text-center mb-4"><?= lang('两步验证') ?></h4>
            <p class="text-center text-muted mb-4"><?= lang('请使用Google Authenticator扫描二维码以获取验证码') ?></p>


            <div class="row g-3 mb-2" v-if="has_twice_verify">
                <div class="col-12">
                    <div class="alert alert-success d-flex align-items-center justify-content-between" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <span><strong><?= lang('二次验证已绑定') ?></strong></span>
                        </div>
                        <el-button
                            type="danger"
                            size="small"
                            @click="unbind_twice_verify()"
                            plain>
                            <?= lang('取消绑定') ?>
                        </el-button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-2" v-else>
                <div class="col-md-12 text-center">
                    <img :src="twice_verify_qr_url" alt="二维码" class="qr-code-img" id="verifyCodeImg" title="<?= lang('使用Google Authenticator扫描') ?>">
                    <el-input ref="input" type="number"
                        v-model="form.twice_verify"
                        placeholder="<?= lang('请输入Authenticator中的6位验证码') ?>"
                        maxlength="6"
                        clearable
                        size="large"
                        class="mb-3 mt-3">
                    </el-input>
                    <el-button
                        type="primary"
                        size="large"
                        @click="check_twice_verify"
                        :loading="false"
                        class="w-100">
                        <?= lang('验证') ?>
                    </el-button>
                </div>
            </div>


        </div>
    </el-dialog>

<?php
});
