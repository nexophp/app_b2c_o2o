<?php

namespace modules\invoice\model;

class InvoiceTitleModel extends \core\AppModel
{
    protected $table = 'invoice_title';
    
    
    public function afterFind(&$data)
    {
        // 格式化时间
        if ($data['created_at']) {
            $data['created_at_format'] = date('Y-m-d H:i:s', $data['created_at']);
        }
        if ($data['updated_at']) {
            $data['updated_at_format'] = date('Y-m-d H:i:s', $data['updated_at']);
        }
        
        // 处理类型显示
        if ($data['type']) {
            $typeMap = [
                'personal' => '个人',
                'company' => '企业'
            ];
            $data['type_text'] = $typeMap[$data['type']] ?? $data['type'];
        }
        
        // 处理状态显示
        if (isset($data['status'])) {
            $statusMap = [
                0 => '禁用',
                1 => '启用'
            ];
            $data['status_text'] = $statusMap[$data['status']] ?? $data['status'];
        }
    }
    
    public function beforeSave(&$data)
    {
        $now = time();
        
        if (!isset($data['id'])) {
            $data['created_at'] = $now;
        }
        $data['updated_at'] = $now;
        
        // 如果设置为默认，需要将其他的设为非默认
        if (isset($data['is_default']) && $data['is_default'] == 1 && isset($data['user_id'])) {
            $this->update(['is_default' => 0], ['user_id' => $data['user_id']],true);
        }
    }
}