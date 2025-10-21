<?php

namespace modules\logistic\model;

class LogisticTemplateModel extends \core\AppModel
{
    protected $table = 'logistic_template';

    protected $field = [
        'name'  => '模板名称',
        'seller_id' => '卖家ID',
        'is_free_shipping' => '是否包邮',
        'free_shipping_amount' => '包邮金额',
        'delivery_type' => '计费方式', // 1: 按件数, 2: 按重量, 3: 按体积
        'status' => '状态',
    ];

    protected $validate = [
        'required' => [
            'name',
            'seller_id',
            'delivery_type',
        ],
    ];

    protected $unique_message = [
        '模板名称已存在',
    ];

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        // 可以在这里添加数据处理逻辑
        $data['status'] = (string)$data['status'];
        $data['is_free_shipping'] = (string)$data['is_free_shipping'];
        
    }

    /**
     * 获取模板名称
     */
    public function getAttrName()
    {
        return $this->data['name'];
    }

    /**
     * 获取卖家ID
     */
    public function getAttrSellerId()
    {
        return $this->data['seller_id'];
    }

    /**
     * 获取是否包邮
     */
    public function getAttrIsFreeShipping()
    {
        return $this->data['is_free_shipping'];
    }

    /**
     * 获取包邮金额
     */
    public function getAttrFreeShippingAmount()
    {
        return $this->data['free_shipping_amount'];
    }

    /**
     * 获取计费方式
     */
    public function getAttrDeliveryType()
    {
        return $this->data['delivery_type'];
    }

    /**
     * 获取计费方式文本
     */
    public function getAttrDeliveryTypeText()
    {
        $types = [
            1 => '按件数',
            2 => '按重量',
            3 => '按体积',
        ];
        return $types[$this->data['delivery_type']] ?? '未知';
    }

    /**
     * 获取状态文本
     */
    public function getAttrStatusText()
    {
        return $this->data['status'] == 1 ? '启用' : '禁用';
    }
 
}