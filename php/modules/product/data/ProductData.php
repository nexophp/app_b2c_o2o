<?php

namespace modules\product\data;

use modules\product\model\ProductModel;
use modules\product\model\ProductTypeModel;
use lib\Html;

class ProductData
{
    /**
     * 获取分类下的商品
     * @param int $type_id 分类id
     * @param bool $deep 是否递归获取子分类
     */
    public static function getByType($type_id, $deep = false)
    {
        if ($deep) {
            $type_ids = ProductTypeModel::model()->getTreeId($type_id);
        } else {
            $type_ids = [$type_id];
        }
        $all = ProductModel::model()->findAll([
            'type_id_last' => $type_ids,
            'status' => 'success',
            'ORDER' => [
                'sort' => 'DESC',
                'id' => 'DESC'
            ],
            'LIMIT' => 50
        ]);
        $list = [];
        foreach ($all as $key => $value) {
            $list[] = ProductData::resetData($value);
        }
        return $list;
    }
    /**
     * 重置数据
     */
    public static function resetData($data)
    {
        $is_api = is_api(); 
        $product_spec = $data->product_spec;
        $spec = [];
        $default_price = '';
        $top_price = '';
        if ($product_spec) {
            foreach ($product_spec as $v) {
                $row = [
                    'id' => $v['id'],
                    'title' => $v['title'],
                    'image' => $v['image'],
                    'market_price' => $v['market_price'],
                    'price' => $v['price'],
                    'stock' => $v['stock'],
                    'sku' => $v['sku'],
                    'status' => (string)$v['status'],
                    'is_default' => (string)$v['is_default'], 

                ];
                if (!$top_price) {
                    $top_price = $row['price'];
                }
                if ($row['is_default'] == 1) {
                    $default_price = $row['price'];
                }
                if ($is_api) {
                    $row['image'] = cdn() . $row['image'];
                }

                $spec[] =  $row;
            }
        }
        $type_title = ProductTypeModel::model()->find(['id' => $data->type_id_last], 1)->title ?? '';
        $images = $data['images'] ?: [];
        $body_html = $data['body'];
        $images = Html::getImage($body_html);
        foreach ($images as $v) {
            if (strpos($v, '://') === false) {
                $new_v = cdn() . $v;
                $body_html = str_replace($v, $new_v, $body_html);
            }
        }

        $data = [
            'id' => $data['id'],
            'title' => $data['title'],
            'desc' => $data['desc'],
            'body' => $data['body'],
            'body_html' => $body_html,
            'image' => $data['image'],
            'images' => $images,
            'spec_type' => (string)$data['spec_type'],
            'spec' => $spec,
            'type_id' => $data['type_id'] ?: [],
            'brand_id' => $data['brand_id'],
            'status' => $data['status'],
            'price' => $data->price,
            'sku' => $data->sku,
            'stock' => $data->stock,
            'type_title' => $type_title,
            'attr' => $data->attr,
            'new_attr' => $data->new_attr,
            'product_type' => $data->product_type,
            'point' => $data->point,
            'sales' => $data->sales,
            'weight' => $data->weight,
            'weight_title' => $data->weight.' '.lang('千克'),
            //运费模板 
            'freight_template_id' => $data->freight_template_id,
            'length' => $data->length,
            'width' => $data->width,
            'height' => $data->height,
            'recommend' => $data->recommend,

        ];
        if ($is_api) {
            $data['image'] = cdn() . $data['image'];
            $data['images'] = $images; 
        }
        if ($data['spec_type'] == 2) {
            $data['price'] = $default_price ?: $top_price;
        }
        return $data;
    }
}
