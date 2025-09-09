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
}
