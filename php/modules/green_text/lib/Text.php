<?php

/**
 * 文本安全  
 * @author sunkangchina <68103403@qq.com> 
 * @version 1.0
 */

namespace modules\green_text\lib;
 

/**
 * 内容安全
 */
class Text
{
    /**
     * 检查文本是否安全
     * @param string $content 文本内容
     * @return boolean
     */
    public static function check($content)
    {
        if(!get_config("verify_text")){
            return true;
        }
        $drive = get_config('safe_drive') ?: 'Aliyun';
        $cls = "\modules\comment\lib\\" . ucfirst($drive) . "\\Text";
        try {
            return $cls::check($content);
        } catch (\Throwable $th) {
            error_log("文本安全调用：" . $drive . ",发生一个错误" . $th->getMessage());
        }
    }
}
