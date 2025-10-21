<?php

namespace modules\banner\model;

class BannerModel extends \core\AppModel
{
    protected $table = 'banner';

    public function afterFind(&$data)
    {
        $data['type_text'] = self::getTypeOptions()[$data['type']] ?? $data['type'];
        $data['status_text'] = $data['status'] ? '启用' : '禁用';
        $data['created_at_text'] = date('Y-m-d H:i:s', $data['created_at']);
        $data['updated_at_text'] = date('Y-m-d H:i:s', $data['updated_at']);

        if ($data['start_time']) {
            $data['start_time_text'] = date('Y-m-d H:i:s', $data['start_time']);
        }

        if ($data['end_time']) {
            $data['end_time_text'] = date('Y-m-d H:i:s', $data['end_time']);
        }
    }
    /**
     * 获取Banner类型选项
     * @return array
     */
    public static function getTypeOptions()
    {
        return [
            'web' => '网页链接',
            'minapp' => '小程序页面'
        ];
    }
}
