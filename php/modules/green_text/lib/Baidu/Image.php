<?php

/**
 * 图片安全 
 * https://console.bce.baidu.com/ai/#/ai/antiporn/app/list
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\green_text\lib\Baidu;


class Image extends Text
{

    public static function check($url)
    {
        $key = md5($url);
        $cache_id = "ImageSafe:" . $key;
        $cache_data = cache($cache_id);
        if ($cache_data) {
            return self::data($cache_data);
        }
        add_log('百度云-图片安全', ['url' => $url], 'ImageSafe');
        $result = self::init()->imageCensorUserDefined($url);
        if (isset($result['error_code'])) {
            add_log('百度云-图片安全 检查失败', $url, 'error');
            return;
        }
        cache($cache_id, $result);
        return self::data($result);
    }
}
