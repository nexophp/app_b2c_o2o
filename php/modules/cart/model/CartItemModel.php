<?php

namespace modules\cart\model;

class CartItemModel extends \core\AppModel
{
    protected $table = 'cart_item';

    protected $field = [ 
        'type' => '类型',
        'user_id' => '用户ID',
        'product_id' => '商品ID',
        'title' => '商品名称',
        'price' => '商品单价',
        'num' => '商品数量',
        'amount' => '商品总价',
    ];

    protected $validate = [
        'required' => [
            'product_id', 'title', 'price', 'num'
        ],
    ]; 

    public function beforeSave(&$data)
    {
        parent::beforeSave($data);
        
        if (!isset($data['created_at'])) {
            $data['created_at'] = time();
        }
        if($data['price'] && $data['num']){
            $data['amount'] = bcmul($data['price'], $data['num'], 2);
        }
        $data['updated_at'] = time();  
    }

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        
        if (isset($data['created_at']) && is_numeric($data['created_at'])) {
            $data['created_at_format'] = date('Y-m-d H:i', (int)$data['created_at']);
        }
        if (isset($data['updated_at']) && is_numeric($data['updated_at'])) {
            $data['updated_at_format'] = date('Y-m-d H:i', (int)$data['updated_at']);
        }

        $product_id = $data['product_id'];
        $product = db_get_one('product',"*", ['id' => $product_id]);
        $data['product_status'] = $product['status'];
    } 
     
}