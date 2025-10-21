<?php

/**
 * 图片安全 
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\green_text\lib;

class Image
{
    /**
     * 检查图片是否安全
     * @param string $url 图片url
     * @return boolean
     */
    public static function check($url)
    {
        if(!get_config("verify_text")){
            return true;
        }

        $drive = get_config('safe_drive') ?: 'Aliyun';
        $cls = "\modules\green_text\lib\\" . ucfirst($drive) . "\\Image";
        if (!class_exists($cls)) {
            error_log("图片安全调用：" . $drive . ",类不存在");
        }

        try {
            return $cls::check($url);
        } catch (\Throwable $th) {
            error_log("图片安全调用：" . $drive . ",发生一个错误" . $th->getMessage());
        }
    }
}
