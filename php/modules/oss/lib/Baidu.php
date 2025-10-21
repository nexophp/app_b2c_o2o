<?php

/**
 *  百度云BOS
 *  https://console.bce.baidu.com/bos/#/bos/new/overview
 *  https://cloud.baidu.com/doc/BOS/s/bjwvys425
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\oss\lib;

include __DIR__ . '/BaiduBce.phar';

use BaiduBce\Auth\signOptions;
use BaiduBce\Services\Bos\BosClient;
use BaiduBce\Services\Bos\BosOptions;

use BaiduBce\Services\Bos\StorageClass;

class Baidu
{
    public static $bucket;
    public static $client;

    public static function init()
    {
        $BOS_CONFIG = [
            'credentials' => [
                'ak' => get_config("baidu_access_key"),
                'sk' => get_config("baidu_secret_key"),
            ],
            'endpoint' => get_config('oss.baidu.endpoint'),
        ];
        self::$bucket = get_config('oss.baidu.bucket');
        self::$client = new BosClient($BOS_CONFIG);
    }
    /**
     * 上传
     * @param string $local_url 本地URL
     * @param string $remote_url 上传到远程地址
     * @return array
     */
    public static function upload($file, $object = '')
    {
        self::init();
        $object = Oss::getObject($file, $object);
        if (!file_exists($file)) {
            error_log("oss baidu file not exists:{$file}");
            return;
        }
        if (substr($object, 0, 1) == '/') {
            $object = substr($object, 1);
        }
        $content = file_get_contents($file);
        $mime = mime_content_type($file);
        self::init();
        try {
            $options = array(
                BosOptions::STORAGE_CLASS => StorageClass::STANDARD,
                BosOptions::CONTENT_TYPE => $mime,
            );
            $res = self::$client->putObjectFromString(self::$bucket, $object, $content, $options);
            $url = Oss::getObjectUrl($object);
            return $url;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            add_log("百度云对象存储失败",$msg,'error');
        }
    }
    /**
     * 生成链接
     */
    public static function getUrl($key, $is_private = false)
    {
        self::init();
        $opt = [];
        if ($is_private) {
            $signOptions = array(
                SignOptions::TIMESTAMP => new \DateTime(),
                SignOptions::EXPIRATION_IN_SECONDS => 300,
            );
            $opt = [BosOptions::SIGN_OPTIONS => $signOptions];
        }
        $url = self::$client->generatePreSignedUrl(self::$bucket, $key, $opt);
        return $url;
    }
}
