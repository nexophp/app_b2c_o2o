<?php

/**
 * 阿里云-文本安全
 * https://yundun.console.aliyun.com/?p=cts
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\green_text\lib\Aliyun;

use AlibabaCloud\Green\Green;
use AlibabaCloud\Client\AlibabaCloud;

class Text
{

    public static function init()
    {
        AlibabaCloud::accessKeyClient(get_config('aliyun_accesskey_id'), get_config('aliyun_accesskey_secret'))
            ->timeout(10) // 超时10秒，使用该客户端且没有单独设置的请求都使用此设置。
            ->connectTimeout(3) // 连接超时3秒，当单位小于1，则自动转换为毫秒，使用该客户端且没有单独设置的请求都使用此设置。
            ->regionId(get_config('aliyun_green_region_id')?:'cn-shanghai')
            ->regionEndpoint(get_config('aliyun_green_endpoint')?:'green-cip.cn-shanghai.aliyuncs.com')
            ->asDefaultClient();
    }

    public static function check($content)
    {
        $key = md5($content);
        $cache_id = "TextSafe:" . $key;
        $cache_data = cache($cache_id);
        if ($cache_data) {
            return self::data($cache_data);
        }
        add_log('阿里云-文本安全', ['content' => $content], 'TextSafe');

        self::init();
        $task1 = array(
            'dataId' => $key,
            'content' => $content,
        );
        /**
         * 文本垃圾检测：antispam。
         **/
        $result = Green::v20180509()->textScan()
            ->timeout(10) // 超时10秒，request超时设置，仅对当前请求有效。
            ->connectTimeout(3) // 连接超时3秒，当单位小于1，则自动转换为毫秒，request超时设置，仅对当前请求有效。
            ->body(json_encode(array('tasks' => array($task1), 'scenes' => array('antispam'), 'bizType' => 'default')))
            ->request();
        $arr = $result->toArray();
        if ($arr['code'] != 200) {
            add_log('阿里云-文本安全 检查失败', $content, 'error');
            return;
        }
        cache($cache_id, $arr);
        return self::data($arr);
    }

    public static function data($arr)
    {
        foreach ($arr['data'] as $k => $v) {
            foreach ($v['results'] as $k1 => $v1) {
                if ($v1['suggestion'] != 'pass') {
                    return false;
                }
            }
        }
        return true;
    }
}
