<?php

namespace modules\logistic\data;

use modules\logistic\model\LogisticModel;

class LogisticData
{
    /**
     * 重置数据
     */
    public static function resetData($data)
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'code' => $data['code'],
            'status' => $data['status'],
            'status_text' => $data['status'] == 1 ? '启用' : '禁用',
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'created_at_text' => $data['created_at'] ? date('Y-m-d H:i:s', $data['created_at']) : '',
            'updated_at_text' => $data['updated_at'] ? date('Y-m-d H:i:s', $data['updated_at']) : '',

            
        ];
    }

    /**
     * 获取所有启用的物流公司
     */
    public static function getActiveList()
    {
        $all = LogisticModel::model()->findAll([
            'status' => 1,
            'ORDER' => ['id' => 'ASC']
        ]);
        $list = [];
        foreach ($all as $value) {
            $list[] = self::resetData($value);
        }
        return $list;
    }

    /**
     * 根据代码获取物流公司信息
     */
    public static function getByCode($code)
    {
        $data = LogisticModel::model()->find(['code' => $code]);
        return $data ? self::resetData($data) : null;
    }
}