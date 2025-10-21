<?php

/**
 * 云存储
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\oss\lib;

class Oss
{
    public static function init()
    {
        $c = get_config('oss_drive') ?: 'Tencent';
        $cls = '\\modules\\oss\\lib\\' . ucfirst($c);
        return $cls;
    }

    public static function upload($file, $object = '')
    {
        $cls = self::init();
        return $cls::upload($file, $object);
    }

    public static function getObjectUrl($object)
    {
        if (substr($object, 0, 1) != '/') {
            $object = '/' . $object;
        }
        return $object;
    }

    public static function getObject($file, $object)
    {
        $object = str_replace(PATH, '', $file);
        if (substr($object, 0, 7) == '/public') {
            $object = substr($object, 7);
        }
        return $object;
    }
}
