<?php

/**
 * 文本安全  
 * https://console.bce.baidu.com/ai/#/ai/antiporn/app/list
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\green_text\lib\Baidu;
 
class Text
{
    public static $client;
    public static function init()
    {
        self::$client = new \AipContentCensor(get_config('baidu_antiporn_app_id'), get_config('baidu_antiporn_app_key'), get_config('baidu_antiporn_app_secret'));
        return self::$client;
    }
    
    public static function check($content)
    {
        $key = md5($content);
        $cache_id = "TextSafe:" . $key;
        $cache_data = cache($cache_id);
        if ($cache_data) {
            return self::data($cache_data);
        }
        add_log('百度云-文本安全', ['content' => $content],'TextSafe'); 
        $arr = self::init()->textCensorUserDefined($content); 
        if (isset($arr['error_code'])) { 
            $arr['content'] = $content; 
            add_log('百度云-文本安全 检查失败', $arr,'error'); 
            return;
        }
        cache($cache_id, $arr);
        return self::data($arr);
    }

    public static function data($arr)
    {
        $conclusion = $arr['conclusion'] ?? '';
        if ($conclusion == '合规') {
            return true;
        }
        return false;
    }
}
