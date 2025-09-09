<?php

namespace modules\order\model;

use core\AppModel;

class OrderPaidInfoModel extends AppModel
{
    protected $table = 'order_paid_info';
    
    protected $field = [
        'order_id' => '订单ID',
        'title' => '名称',
        'type' => '类型',
        'num' => '数量',
        'amount' => '抵扣金额'
    ];
    
    protected $validate = [
        'required' => [
            'order_id', 'title', 'type', 'amount'
        ]
    ];
    
    // 关联订单表
    protected $has_one = [
        'order' => [OrderModel::class, 'order_id', 'id']
    ];
    
    public function getAttrTypeInfo(){
        $array = OrderModel::model()->getPaymentTypes();
        $result = array_column($array, 'label', 'value');
        return $result[$this->type]??'未知'; 
    }

    /**
     * 查询后处理
     */
    public function afterFind(&$data)
    {
        if (isset($data['amount'])) {
            $data['amount'] = floatval($data['amount']);
        }
        if (isset($data['num'])) {
            $data['num'] = intval($data['num']);
        }
        return $data;
    }
    
    /**
     * 保存前处理
     */
    public function beforeSave(&$data)
    {
        // 设置默认值
        if (!isset($data['type'])) {
            $data['type'] = 'coupon';
        }
        if (!isset($data['num'])) {
            $data['num'] = 1;
        }
        if (!isset($data['amount'])) {
            $data['amount'] = 0.00;
        }
        
        return $data;
    }
}