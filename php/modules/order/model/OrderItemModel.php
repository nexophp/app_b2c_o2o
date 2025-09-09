<?php

namespace modules\order\model;

class OrderItemModel extends \core\AppModel
{
    protected $table = 'order_item';

    protected $field = [
        'order_id' => '订单ID',
        'product_id' => '商品ID',
        'title' => '商品名称',
        'price' => '商品单价',
        'num' => '商品数量',
        'amount' => '商品总价',
    ];

    protected $validate = [
        'required' => [
            'order_id', 'product_id', 'title', 'price', 'num'
        ]
    ];

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        // 格式化时间
        if (isset($data['created_at'])) {
            $data['created_at_format'] = date('Y-m-d H:i:s', $data['created_at']);
        }
    }

    public function beforeSave(&$data)
    {
        parent::beforeSave($data);
        
        // 计算总价
        if (isset($data['price']) && isset($data['num'])) {
            $data['amount'] = $data['price'] * $data['num'];
        }
        
        // 设置创建时间
        if (empty($data['id'])) {
            $data['created_at'] = time();
        }
        $data['updated_at'] = time();
    }
}