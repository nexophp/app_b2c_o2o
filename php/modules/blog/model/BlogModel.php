<?php

namespace modules\blog\model;

class BlogModel extends \core\AppModel
{
    protected $table = 'blog';

    protected $has_one = [
        'type' => [BlogTypeModel::class, 'last_type_id', 'id']
    ];

    public function beforeSave(&$data)
    {
        $type_id = $data['type_id'] ?: [];
        $data['last_type_id'] =  0;
        if (is_array($type_id)) {
            $data['last_type_id'] = end($type_id);
        }
    }

    public function afterFind(&$data)
    {
        $data['type_title'] = $this->type->title ?: "";
        // 格式化时间
        if ($data['created_at']) {
            $data['created_at_format'] = date('Y-m-d H:i', $data['created_at']);
        }
        if ($data['updated_at']) {
            $data['updated_at_format'] = date('Y-m-d H:i', $data['updated_at']);
        }

        // 处理状态显示
        if ($data['status']) {
            $statusMap = [
                'draft' => '草稿',
                'published' => '已发布',
                'archived' => '已归档'
            ];
            $data['status_text'] = $statusMap[$data['status']] ?? $data['status'];
        }
    }
}
