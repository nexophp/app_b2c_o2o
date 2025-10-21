<?php

/**
 *  腾讯云COS
 *  https://cloud.tencent.com/document/product/436/12265
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\oss\lib;
 
class Tencent
{
    public static $bucket;
    public static function init()
    { 
        self::$bucket = get_config('oss_tencent_bucket');
        $secretId = get_config('tencent_secret_id');
        $secretKey = get_config('tencent_secret_key');
        $region = get_config('oss_tencent_region');
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $region,
                'schema' => 'https',
                'credentials' => array(
                    'secretId' => $secretId,
                    'secretKey' => $secretKey,
                ),
            )
        );
        return $cosClient;
    }

    public static function create($bucket_name)
    {
        $cosClient = self::init();
        try {
            $bucket = $bucket_name; //存储桶名称 格式：BucketName-APPID
            $result = $cosClient->createBucket(array('Bucket' => $bucket));
            //请求成功
        } catch (\Exception $e) {
            add_log("腾讯云对象存储失败","oss tencent create bucket fail:" . $e->getMessage(),'error');
        }
    }
    /**
     * 上传文件
     * @param $file 本地/uploads/……
     * @param $object 远程保存路径
     */
    public static function upload($file, $object = '')
    {
        $object = Oss::getObject($file, $object);
        if (!file_exists($file)) {
            add_log("腾讯云对象存储失败","oss upload:" . $file . " not exist",'error');
            return;
        }
        if (substr($object, 0, 1) == '/') {
            $object = substr($object, 1);
        }
        $content = file_get_contents($file);
        $mime = mime_content_type($file);
        $options['ContentType'] = $mime;
        $cosClient = self::init();
        try {
            $bucket = self::$bucket;
            $res = $cosClient->upload(
                $bucket,
                $object,
                $content,
                $options
            );
            try {
                $url = $cosClient->getObjectUrlWithoutSign($bucket, $object);
                $url = Oss::getObjectUrl($object);
                return $url;
            } catch (\Exception $e) {
                add_log("腾讯云对象存储失败","oss tencent upload:" . $e->getMessage(),'error');
            }
        } catch (\Exception $e) {
            add_log("腾讯云对象存储失败","oss tencent upload:" . $e->getMessage(),'error');
        }
    }

    /**
     * 列表
     */
    public static function lists()
    {
        $cosClient = self::init();
        $keys = [];
        try {
            $bucket = self::$bucket; //存储桶名称 格式：BucketName-APPID
            $prefix = ''; //列出对象的前缀
            $marker = ''; //上次列出对象的断点
            while (true) {
                $result = $cosClient->listObjects(array(
                    'Bucket' => $bucket,
                    'Marker' => $marker,
                    'MaxKeys' => 1000, //设置单次查询打印的最大数量，最大为1000
                ));
                if (isset($result['Contents'])) {
                    foreach ($result['Contents'] as $rt) {
                        $keys[] = $rt['Key'];
                    }
                }
                $marker = $result['NextMarker']; //设置新的断点
                if (!$result['IsTruncated']) {
                    break; //判断是否已经查询完
                }
            }
        } catch (\Exception $e) {
            add_log("腾讯云对象存储失败","oss tencent lists:" . $e->getMessage(),'error');
        }
        return $keys;
    }
    /**
     * 删除所有
     */
    public static function deleteAll()
    {
        $all = self::lists();
        $cosClient = self::init();
        $bucket = self::$bucket;
        if ($all) {
            $keys = [];
            foreach ($all as $key) {
                $keys[] = ['Key' => $key];
            }
            $res = $cosClient->deleteObjects(array(
                'Bucket' => $bucket,
                'Objects' => $keys,
            ));
            return $res;
        }
    }

    /**
     * 下载文件
     */
    public static function download($object)
    {
        $cosClient = self::init();
        $result = $cosClient->getObject(array(
            'Bucket' => $bucket,
            'Key' => $object
        ));
        $body = $result['Body'];
        return $body;
    }
}
