<?php

namespace modules\o2o\model;

class AddressModel extends \core\AppModel
{
    protected $table = 'o2o_address';

    protected $fields = [
        'id',
        'title',
        'regions',
        'status',
        'seller_id',
        'sys_tag',
        'created_at',
        'updated_at'
    ];

    protected $required = [
        'title',
        'regions'
    ];

    /**
     * 查询后处理数据
     */
    public function afterFind(&$data)
    {
        // 格式化时间戳
        if (isset($data['created_at'])) {
            $data['created_at_text'] = date('Y-m-d H:i', $data['created_at']);
        } 
        
        $regions = $data['regions'];
        $str = "";
        if ($regions) {
            $city = $regions[2];
            if (in_array($city, get_china_direct_city())) {
                $str = $regions[0] . $regions[2] . $regions[3];
            } else {
                $str = $regions[0] . $regions[1] . $regions[2];
            }
        }
        $data['address'] = $str . $data['detail'];
        return $data;
    }

    /**
     * 保存前处理数据
     */
    public function beforeSave(&$data)
    {
        // 设置时间戳
        $time = time();
        if (!$data['id']) {
            $data['created_at'] = $time;
        }
        $data['updated_at'] = $time;
        return $data;
    }

    /**
     * 获取状态选项
     */
    public function getStatusOptions()
    {
        return [
            ['value' => 'success', 'label' => '启用'],
            ['value' => 'error', 'label' => '禁用']
        ];
    }
}
