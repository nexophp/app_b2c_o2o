<?php

/**
 * 阿里云OSS
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\oss\lib;


use OSS\OssClient;

class Aliyun
{
    public static $obj;
    public static $oss_url = "";
    public static $bucket = ""; 

    public static function initAliyun()
    {
        if (!self::$obj) {
            $accessId = get_config('aliyun_accesskey_id');
            $accessKey = get_config('aliyun_accesskey_secret');
            $endPoint = get_config('oss_aliyun_endpoint');
            self::$bucket = get_config('oss_aliyun_bucket');
            self::$obj = new OssClient($accessId, $accessKey, $endPoint);
        }
        return self::$obj;
    }
    /**
     * 上传文件到阿里云OSS
     *
     * @param string $file
     * @param string $content
     * @return void
     */
    public static function upload($file, $object = '')
    {
        $object = Oss::getObject($file, $object);
        if (!file_exists($file)) {
            add_log("阿里云对象存储失败","文件不存在",'error');
            return;
        }
        if (substr($object, 0, 1) == '/') {
            $object = substr($object, 1);
        }
        $content = file_get_contents($file);
        $mime = mime_content_type($file);
        $options['content-type'] = $mime;
        $ossClient = self::initAliyun();
        $bucket_name = self::$bucket;
        //所有bucket
        $bucketListInfo = $ossClient->listBuckets();
        $bucketList = $bucketListInfo->getBucketList();
        $arr = [];
        foreach ($bucketList as $bucket) {
            $name = (string) $bucket->getName();
            $arr[$name] = $name;
        }
        if (!$arr[$bucket_name]) {
            $ossClient->createBucket($bucket_name);
        }
        $res = $ossClient->putObject($bucket_name, $object, $content, $options);
        if ($res['info']['url']) {
            $url = Oss::getObjectUrl($object);
            return $url;
        } else {
            return;
        }
    }
    public static function lists()
    {
        $ossClient = self::initAliyun();
        $listObjectInfo = $ossClient->listObjects(self::$bucket, [
            OssClient::OSS_MAX_KEYS => 1000,
            'delimiter' => '',
        ]);
        $objectList = $listObjectInfo->getObjectList();
        $key = [];
        foreach ($objectList as $objectInfo) {
            $key[] = $objectInfo->getKey();
        }
        return $key;
    }
}
