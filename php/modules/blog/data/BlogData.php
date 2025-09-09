<?php

namespace modules\blog\data;

use lib\Html;

class BlogData
{
    /**
     * 接口数据
     */
    public static function parseData(&$data)
    {
        $images = $data['images'];
        if ($images) {
            foreach ($images as $k => $v) {
                $images[$k] = cdn() . $v;
            }
            $data['images'] = $images;
        }
        $body_html = $data['content'];
        $images =  Html::getImage($body_html);
        foreach ($images as $v) {
            if (strpos($v, '://') === false) {
                $new_v = cdn() . $v;
                $body_html = str_replace($v, $new_v, $body_html);
            }
        }
        $data['content'] = $body_html;
    }
    /**
     * 验证博客数据
     */
    public static function validate($data)
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = '标题不能为空';
        }

        if (empty($data['content'])) {
            $errors[] = '内容不能为空';
        }

        if (isset($data['type_id']) && !is_numeric($data['type_id'])) {
            $errors[] = '分类ID必须是数字';
        }

        return $errors;
    }
}
