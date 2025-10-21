<?php

namespace modules\minify\cmd;

/**
 * 清理js css 缓存文件 
 */
class clean
{
    public function run($name = 'demo')
    {
        //删除 assets/dist下css js
        $dir = WWW_PATH . '/assets/dist';
        $files = scandir($dir);
        foreach ($files as $v) {
            if ($v == '.' || $v == '..') {
                continue;
            }
            if (strpos($v, '.css') !== false) {
                unlink($dir . '/' . $v);
            }
            if (strpos($v, '.js') !== false) {
                unlink($dir . '/' . $v);
            }
        }
        db_delete("minify", ['id[>=]' => 1]);
        cli_success('清理成功');
    }
}
