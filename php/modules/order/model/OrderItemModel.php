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
            'order_id',
            'product_id',
            'title',
            'price',
            'num'
        ]
    ];

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        // 格式化时间
        if (isset($data['created_at'])) {
            $data['created_at_format'] = date('Y-m-d H:i:s', $data['created_at']);
        }
        if (strpos($data['image'], '://') === false) {
            $data['image'] =  cdn() . $data['image'];
        }
    }

    public function beforeSave(&$data)
    {
        parent::beforeSave($data);
        
        if (isset($data['price']) && isset($data['num'])) {
            $data['amount'] = bcmul($data['price'], $data['num'], 2);
        }

        if (!$data['id']) {
            $data['created_at'] = time();
            $data['can_refund_num'] = $data['num'];
            $data['refund_num'] = 0;
            $data['can_refund_amount'] = $data['real_amount'];
            $data['has_refund_amount'] = 0;
            $data['real_get_amount'] = $data['real_amount'];
        }
        $data['updated_at'] = time();
    }
}
