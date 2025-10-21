<?php

namespace modules\twice_verify\controller;

class SiteController extends \core\AppController
{

    public function actionIndex()
    {
        $token = $_GET['token'];
        if (!$token) {
            exit();
        }
        $res = aes_decode($token);
        if (!$res) {
            show_error(lang('token解码失败'));
        }
        if ($res['exp'] < time()) {
            show_error(lang('token已过期'));
        }
        $this->view_data['less_time'] = $res['exp'] - time();
        $this->view_data['token'] = $token;
    }
}
