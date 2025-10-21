<?php

/**
 *  又拍云
 *  https://console.upyun.com/services/file/
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\oss\lib;

use app\helper\File;
use UpYun as UpYunDrive;
class Upyun
{
    public static function init()
    { 
        $client = new UpYunDrive(get_config('oss_upyun_server_name'), get_config('oss_upyun_account'), get_config('oss_upyun_password'));
        return $client;
    }
    /**
     * 上传
     */
    public static function upload($file, $object = '')
    {
        $client = self::init();
        $object = Oss::getObject($file, $object);
        if (!file_exists($file)) {
            add_log('又拍云对象存储失败',"文件不存在",'error'); 
            return;
        }
        if (substr($object, 0, 1) != '/') {
            $object = '/' . $object;
        }
        $content = file_get_contents($file);
        $mime = mime_content_type($file);
        $options['content-type'] = $mime;
        try {
            $res = $client->writeFile($object, $content);
            if ($res) {
                $url = Oss::getObjectUrl($object);
                return $url;
            }
        } catch (\Exception $e) {
            add_log('又拍云对象存储失败',"oss tencent upload:" . $e->getMessage(),'error');
        }
    }
    /**
     * 列表
     */
    public static function lists($path = '/')
    {
        $client = self::init();
        $all = $client->getList($path);
        $list = [];
        foreach ($all as $v) {
            if ($v['type'] == 'folder') {
                $new_path = $path . $v['name'] . '/';
                $list = array_merge($list, self::lists($new_path));
            } else {
                $list[] = $path . $v['name'];
            }
        }
        return $list;
    }

    /**
     * 删除
     */
    public static function deleteAll()
    {
        $client = self::init();
        $all = self::lists();
        if ($all) {
            $dirs = [];
            foreach ($all as $v) {
                $dirs[] = File::getDir($v);
                $client->delete($v);
            }
            foreach ($dirs as $dir) {
                $client->delete($dir);
            }
        }
    }
}
