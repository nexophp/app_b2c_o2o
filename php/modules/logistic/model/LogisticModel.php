<?php

namespace modules\logistic\model;

class LogisticModel extends \core\AppModel
{
    protected $table = 'logistic';

    protected $field = [
        'name'  => '物流公司名称',
        'code' => '物流公司代码',
        'status' => '状态',
    ];

    protected $validate = [
        'required' => [
            'name',
            'code',
        ],
    ];

    protected $unique_message = [
        '物流公司名称已存在',
        '物流公司代码已存在',
    ];

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        // 可以在这里添加数据处理逻辑
    }

    /**
     * 获取物流公司名称
     */
    public function getAttrName()
    {
        return $this->data['name'];
    }

    /**
     * 获取物流公司代码
     */
    public function getAttrCode()
    {
        return $this->data['code'];
    }

    /**
     * 获取状态文本
     */
    public function getAttrStatusText()
    {
        return $this->data['status'] == 1 ? '启用' : '禁用';
    }
}