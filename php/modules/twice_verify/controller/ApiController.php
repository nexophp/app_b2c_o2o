<?php

namespace modules\twice_verify\controller;

class ApiController extends \core\ApiController
{
    protected $need_login = false;
    protected $ga;
    public function init()
    {
        parent::init();
        $this->ga = new \modules\twice_verify\lib\GoogleAuthenticator();
    }
    /**
     * 生成图形验证码
     */
    public function actionUserQr()
    {
        $uid = $this->uid;
        if (!$uid) {
            json_error(['msg' => lang('请先登录')]);
        }
        $user = get_user($uid);
        $name = get_data_by_key($user, ['phone', 'email', 'username', 'id']);
        $name = get_config("app_name") . ":" . $name;

        $secret = $user['twice_verify'];
        $has = false;
        if (!$secret) {
            $secret = $this->ga->createSecret();
        } else {
            $has = true;
        }
        $qrCodeUrl = $this->ga->getQRCodeGoogleUrl($name, $secret);
        cache('twice_verify_' . $uid, $secret, 600);
        json_success(['data' => $qrCodeUrl, 'has' => $has]);
    }
    /**
     * 验证两次验证
     */
    public function actionCheck()
    {
        $code = $this->input_data['code'];
        $token = $this->input_data['token'];
        if (!$code || !$token) {
            json_error(['msg' => lang('请输入验证码')]);
        }
        $token = aes_decode($token);
        if (!$token) {
            json_error(['msg' => lang('参数错误')]);
        }
        $uid = $token['uid'];
        $time = $token['time'];
        if (!$time) {
            json_error(['msg' => lang('参数错误')]);
        }
        if ($time < time() - 600) {
            json_error(['msg' => lang('参数错误')]);
        }
        $user = get_user($uid);
        $secret = $user['twice_verify'];
        if (!$secret) {
            json_error(['msg' => lang('两次验证未绑定')]);
        }
        check_twice_verify($secret, $code);
        $time = get_admin_login_cookie_time();
        cookie('uid', $uid, $time);
        json_success(['msg' => lang('验证成功')]);
    }
    /**
     * 绑定两次验证
     */
    public function actionBind()
    {
        if (!$this->uid) {
            json_error(['msg' => lang('请先登录')]);
        }
        $code = $this->input_data['code'];
        if (!$code) {
            json_error(['msg' => lang('请输入验证码')]);
        }
        $secret = cache('twice_verify_' . $this->uid);
        if (!$secret) {
            json_error(['msg' => lang('两次验证失败')]);
        }
        check_twice_verify($secret, $code);
        set_user_info($this->uid, 'twice_verify', $secret);
        json_success(['msg' => lang('绑定成功')]);
    }
    /**
     * 解除绑定
     */
    public function actionUnbind()
    {
        if (!$this->uid) {
            json_error(['msg' => lang('请先登录')]);
        }
        set_user_info($this->uid, 'twice_verify', '');
        json_success(['msg' => lang('解除绑定成功')]);
    }
}
