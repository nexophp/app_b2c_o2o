<?php

/**
 * 小程序
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */

namespace modules\minapp\lib;

use EasyWeChat\MiniApp\Application;

class MinApp
{

    /** 
     * 微信小程序强制刷新 access_token
     */
    public static function reloadToken()
    {
        $app = self::init();
        // 获取 access token 实例
        $accessToken = $app->access_token;
        $token = $accessToken->getToken(true);
    }
    /**
     * get_ability('login.weixin', 'init' );
     * 微信小程序 init
     */
    public static function init()
    {
        //getClient() getUtils() getServer()
        $app =  new Application([
            'app_id' => get_config('minapp_app_id'),
            'secret' => get_config('minapp_secret'),
            'token' => get_config('minapp_token'),
            'aes_key' => get_config('minapp_aes_key'),
            'use_stable_access_token' => false,
            'http' => [
                'timeout' => 5.0,
                'retry' => true,
            ]
        ]);
        return $app;
    }

    /**
     * 生成小程序二维码
     * 永久有效，数量暂无限制。
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getUnlimitedQRCode.html
     * 
     * @param string $page 页面路径，例如 pages/index/index
     * @param string $scene 二维码场景值，例如 scene=123
     * @param string $env_version 二维码环境版本，默认是正式版，可选值有 trial（体验版）、release（正式版）、develop（开发版）
     * @return string|bool 二维码图片路径或false
     */
    public static function qr($page = 'pages/index/index', $scene = '123456', $env_version = '')
    {
        if (!$env_version && is_local()) {
            $env_version = 'develop';
        }
        $app = self::init();
        $params = [];
        $params['page'] = $page;
        if ($scene) {
            $params['scene'] = $scene;
        }
        if ($env_version) {
            $params['env_version'] = $env_version;
        }
        $key = md5(get_config('minapp_app_id') . json_encode($params));
        $url = '/uploads/qrcode/' . $key . '.png';
        $file = WWW_PATH . $url;
        if (file_exists($file)) {
            return cdn() . $url;
        }
        $dir  = get_dir($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $params['check_path'] = false;
        try {
            $response = $app->getClient()->post('/wxa/getwxacodeunlimit', [
                'json' => $params,
            ]);
            $content = $response->getContent();
            $res = json_decode($content, true);
            if (is_array($res) && $res['errcode'] != 0) {
                add_log('生成小程序码错误', $res['errmsg'], 'error');
            } else {
                file_put_contents($file, $content);
                return cdn() . $url;
            }
        } catch (\Throwable $e) {
            $err = $e->getMessage();
            add_log('生成小程序码错误', $err, 'error');
        }
    }
}
