<?php

/**
 * 阿里云-图片安全
 * https://yundun.console.aliyun.com/?p=cts
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\green_text\lib\Aliyun;

use AlibabaCloud\Green\Green;

class Image extends Text
{
    /**
     * $res = pack('safe.image','check',[$url]);
     */
    public static function check($url)
    {
        $key = md5($url);
        $cache_id = "ImageSafe:" . $key;
        $cache_data = cache($cache_id);
        if ($cache_data) {
            return self::data($cache_data);
        }
        add_log('阿里云-图片安全', ['url' => $url], 'ImageSafe');
        self::init();
        $task1 = array(
            'dataId' => $key,
            'url' => $url,
        );
        $response = Green::v20180509()->imageSyncScan()
            ->timeout(10) // 超时10秒，request超时设置，仅对当前请求有效。
            ->connectTimeout(3) // 连接超时3秒，当单位小于1，则自动转换为毫秒，request超时设置，仅对当前请求有效。
            ->body(json_encode(
                array(
                    'tasks' => array($task1),
                    'scenes' => array(
                        'porn',
                        'terrorism',
                    ),
                    'bizType' => 'default',
                )
            ))
            ->request();
        $arr = $response->toArray();
        if ($arr['code'] != 200) {
            add_log('阿里云-图片安全 检查失败', $url, 'error');
            return;
        }

        cache($cache_id, $arr);
        return self::data($arr);
    }
}
