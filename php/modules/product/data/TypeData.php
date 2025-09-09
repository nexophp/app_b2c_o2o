<?php

namespace modules\product\data;

class TypeData
{
    /**
     * 图片带域名
     */
    public static function image(&$data)
    {
        if (!$data || !is_array($data)) {
            return;
        }
        if ($data['image']) {
            $data['image'] = cdn() . $data['image'];
        }
    }
    /**
     * 单行数据
     */
    public static function get(&$data)
    {
        if (!$data || !is_array($data)) {
            return;
        }
        $data['has_next'] = 0;
        $data['has_prev'] = 0;
        $id = $data['id'];
        if ($id) {
            $data['has_next'] = db_get_count('product_type', ['pid' => $id]);
            $data['has_prev'] = $data['pid'] ? 1 : 0;
        }
    }
}
