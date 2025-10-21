<?php

namespace modules\minify\lib;

use MatthiasMullie\Minify as MinifyLib;

class Minify
{
    /**
     * 压缩css 
     */
    public static function css($files)
    {
        $files = array_unique($files);
        $sort = ['jquery', 'vue', 'element-ui', 'bootstrap'];
        foreach ($sort as $v) {
            if (in_array($v, $files)) {
                $key = array_search($v, $files);
                unset($files[$key]);
                array_unshift($files, $v);
            }
        }
        $md5 = md5(implode('', $files));
        $str = $md5 . '.css';
        $name = db_get_one("minify", 'name', ['md5' => $md5]);
        if ($name) {
            return $name;
        }
        $str = '/assets/dist/' . $str;
        $file = WWW_PATH . $str;
        if (file_exists($file)) {
            return $str;
        }
        $minifier = new MinifyLib\CSS();
        foreach ($files as $v) {
            $content = file_get_contents(WWW_PATH . $v);
            $minifier->add($content);
        }
        create_dir(get_dir($file));
        $minifier->minify($file);
        db_insert("minify", [
            'name' => $str,
            'md5' => $md5,
        ]);
        return $str;
    }

    /**
     * 压缩js
     */
    public static function js($files)
    {
        $files = array_unique($files);
        $sort = ['jquery', 'vue', 'element-ui', 'bootstrap'];
        foreach ($sort as $v) {
            if (in_array($v, $files)) {
                $key = array_search($v, $files);
                unset($files[$key]);
                array_unshift($files, $v);
            }
        }
        $md5 = md5(implode('', $files));
        $str = $md5 . '.js';
        $name = db_get_one("minify", 'name', ['md5' => $md5]);
        if ($name) {
            return $name;
        }
        $str = '/assets/dist/' . $str;
        $file = WWW_PATH . $str;
        if (file_exists($file)) {
            return $str;
        }
        $minifier = new MinifyLib\JS();
        foreach ($files as $v) {
            $content = file_get_contents(WWW_PATH . $v);
            $minifier->add($content);
        }
        create_dir(get_dir($file));
        $minifier->minify($file);
        db_insert("minify", [
            'name' => $str,
            'md5' => $md5,
        ]);
        return $str;
    }

    /**
     * 压缩js
     */
    public static function jsCode($code)
    {
        $lang = cookie('lang') ?: 'zh-cn';
        $md5 = md5(trim($lang . $code));
        $name = db_get_one("minify", 'name', ['md5' => $md5]);
        if ($name) {
            return $name;
        }

        $str = $md5 . '.js';
        $str = '/assets/dist/' . $str;
        $file = WWW_PATH . $str;
        if (file_exists($file)) {
            return $str;
        }
        $minifier = new MinifyLib\JS();
        $minifier->add($code);
        $minifier->minify($file);
        db_insert("minify", [
            'name' => $str,
            'md5' => $md5,
        ]);

        return $str;
    }
}
