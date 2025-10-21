<?php

namespace modules\mall\model;

class UniappPageModel extends \core\AppModel
{
    protected $table = 'uniapp_page';

    public function beforeSave(&$data)
    {
        $time = time();
        if (!isset($data['created_at'])) {
            $data['created_at'] = $time;
        }
        $data['updated_at'] = $time;
    }

    public function afterFind(&$data)
    {
        parent::afterFind($data);

        // 格式化时间
        if ($data['created_at']) {
            $data['created_at_text'] = date('Y-m-d H:i:s', $data['created_at']);
        }
        if ($data['updated_at']) {
            $data['updated_at_text'] = date('Y-m-d H:i:s', $data['updated_at']);
        }

        // 状态文本
        $data['status_text'] = $data['status'] ? '启用' : '禁用';

        $data['status'] = (string)$data['status'];
    }

    /**
     * 获取状态选项
     */
    public static function getStatusOptions()
    {
        return [
            1 => '启用',
            0 => '禁用'
        ];
    }
}
