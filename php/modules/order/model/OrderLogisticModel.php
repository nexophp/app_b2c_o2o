<?php

namespace modules\order\model;

class OrderLogisticModel extends \core\AppModel
{
    protected $table = 'order_logistic';

    protected $field = [
        'order_num' => '订单编号',
        'no' => '物流单号',
        'type' => '物流类型',
        'data' => '物流数据',
        'status' => '物流状态',
    ];

    protected $validate = [
        'required' => [
            'order_num', 'no', 'type'
        ],
        'unique' => [
            ['no']
        ]
    ];

    protected $unique_message = [
        '物流单号已存在'
    ];

    /**
     * 物流状态定义
     */
    public static function getStatusMap()
    {
        return [
            'wait' => '待发货',
            'shipped' => '已发货',
            'transit' => '运输中',
            'delivering' => '派送中',
            'complete' => '已签收',
            'exception' => '异常'
        ];
    }
    
    /**
     * 获取状态文本
     */
    public static function getStatusText($status)
    {
        $statusMap = self::getStatusMap();
        return $statusMap[$status] ?? $status;
    }
    
    /**
     * 获取状态选项（用于下拉框）
     */
    public static function getStatusOptions()
    {
        $options = [];
        foreach (self::getStatusMap() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }

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
        
        // 状态文本
        $data['status_text'] = self::getStatusText($data['status']);
         
    }

    public function beforeSave(&$data)
    {
        parent::beforeSave($data);
        
        // 设置创建时间
        if (empty($data['id'])) {
            $data['created_at'] = time();
        }
        $data['updated_at'] = time();
        
        // 处理JSON数据
        if (isset($data['logistic_data']) && is_array($data['logistic_data'])) {
            $data['data'] = json_encode($data['logistic_data'], JSON_UNESCAPED_UNICODE);
            unset($data['logistic_data']);
        }
    }
    
    /**
     * 根据订单号获取物流信息
     */
    public function getByOrderNum($orderNum)
    {
        return $this->findOne(['order_num' => $orderNum]);
    }
    
    /**
     * 根据物流单号获取物流信息
     */
    public function getByNo($no)
    {
        return $this->findOne(['no' => $no]);
    }
    
    /**
     * 更新物流状态
     */
    public function updateStatus($id, $status, $data = null)
    {
        $updateData = [
            'status' => $status,
            'updated_at' => time()
        ];
        
        if ($data !== null) {
            $updateData['data'] = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
        }
        
        return $this->update($updateData, ['id' => $id]);
    }
}