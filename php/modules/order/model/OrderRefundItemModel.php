<?php

namespace modules\order\model;

class OrderRefundItemModel extends \core\AppModel
{
    protected $table = 'order_refund_item';

    protected $field = [
        'order_id' => '原订单ID',
        'order_num' => '原订单号',
        'seller_id' => '商家ID',
        'store_id' => '门店ID',
        'user_id' => '用户ID',
        'order_item_id' => '订单商品ID',
        'title' => '商品名称',
        'ori_price' => '原始单价',
        'price' => '商品单价',
        'image' => '商品图片',
        'num' => '商品数量',
        'amount' => '商品总价',
    ];

    protected $validate = [
        'required' => [
            'order_id', 'order_item_id', 'title', 'price', 'num'
        ]
    ];

    protected $belongs_to = [
        'refund' => [OrderRefundModel::class, 'order_num', 'order_num'],
    ];

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        
        // 格式化时间
        if (isset($data['created_at'])) {
            $data['created_at_format'] = date('Y-m-d H:i:s', $data['created_at']);
        }
        if (isset($data['updated_at']) && $data['updated_at']) {
            $data['updated_at_format'] = date('Y-m-d H:i:s', $data['updated_at']);
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