<?php

namespace modules\ticket\model;

class TicketModel extends \core\AppModel
{
    protected $table = 'ticket';

    protected $field = [
        'title' => '打印机名称',
        'code' => '打印机编码',
        'secret' => '打印机密钥',
        'type' => '打印机类型',
        'status' => '打印机状态',
        'seller_id' => '关联商家ID',
        'store_id' => '店铺ID',
        'sys_tag' => '系统标签',
        'remark' => '备注'
    ];

    protected $validate = [
        'required' => [
            'title',
            'code', 
            'type'
        ], 
    ];
 
    /**
     * 打印机类型定义
     */
    public function getTypeMap()
    {
        return [
            'Feie' => lang('飞鹅打印机'), 
        ];
    }

    /**
     * 获取打印机类型选项
     */
    public function getTypeOptions()
    {
        $typeMap = $this->getTypeMap();
        $options = [];
        foreach ($typeMap as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $options;
    }

    /**
     * 打印机状态定义
     */
    public function getStatusMap()
    {
        return [
            'success' => lang('正常'),
            'error' => lang('异常'),
            'offline' => lang('离线')
        ];
    }

    /**
     * 获取打印机状态选项
     */
    public function getStatusOptions()
    {
        $statusMap = $this->getStatusMap();
        $options = [];
        foreach ($statusMap as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $options;
    }

    /**
     * 数据查找后处理
     */
    public function afterFind(&$data)
    {
        if (isset($data['created_at'])) {
            $data['created_at_format'] = date('Y-m-d H:i:s', $data['created_at']);
        }
        if (isset($data['updated_at']) && $data['updated_at']) {
            $data['updated_at_format'] = date('Y-m-d H:i:s', $data['updated_at']);
        }
        
        // 格式化类型显示
        if (isset($data['type'])) {
            $typeMap = $this->getTypeMap();
            $data['type_text'] = $typeMap[$data['type']] ?? '';
        }
        
        // 格式化状态显示
        if (isset($data['status'])) {
            $statusMap = $this->getStatusMap();
            $data['status_text'] = $statusMap[$data['status']] ?? '';
        }
    }

    /**
     * 数据保存前处理
     */
    public function beforeSave(&$data)
    { 
        if (!$data['id']) {
            $data['created_at'] = time();
        } 
        $data['updated_at'] = time();
        global $admin_type;
        $data['sys_tag'] = $admin_type; 
    }
}