<?php

namespace modules\logistic\model;

class LogisticTemplateRegionModel extends \core\AppModel
{
    protected $table = 'logistic_template_region';
    public static $city_data = [];
    protected $field = [
        'template_id'  => '模板ID',
        'region_type' => '区域类型', // 1: 默认区域, 2: 指定区域, 3: 偏远地区
        'regions' => '区域列表', // 省份ID列表，JSON格式
        'first_item' => '首件/首重/首体积',
        'first_fee' => '首件/首重/首体积费用',
        'additional_item' => '续件/续重/续体积',
        'additional_fee' => '续件/续重/续体积费用',
        'is_free_shipping' => '是否包邮', // 0: 不包邮, 1: 包邮
        'free_shipping_amount' => '包邮金额', // 满多少包邮
    ];

    protected $validate = [
        'required' => [
            'template_id',
            'region_type',
        ],
    ];

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        // 解析区域列表  
        $data['is_free_shipping'] = (string)$data['is_free_shipping'];
        $regions = $data['regions'] ?: [];
        $text = "";
        if ($regions) {
            $text = "";
        }
        if (!self::$city_data) {
            self::$city_data = \element\form::get_city();
        }

        $data['regions_str'] = self::convertRegionsToPath($regions, self::$city_data);
    }

    /**
     * 将地区ID数组转换为完整的地区路径字符串
     */
    public static function convertRegionsToPath($regionsArray, $cityData)
    {
        $paths = [];

        foreach ($regionsArray as $regionIds) {
            $path = self::buildPathByLevel($regionIds, $cityData, 0);
            if ($path) {
                $paths[] = $path;
            }
        }

        return implode(', ', $paths);
    }

    /**
     * 按层级构建路径
     */
    private static function buildPathByLevel($regionIds, $data, $currentIndex)
    {
        if ($currentIndex >= count($regionIds)) {
            return '';
        }

        $currentId = $regionIds[$currentIndex];

        // 在当前层级的数据中查找
        foreach ($data as $region) {
            if ($region['id'] == $currentId || (string)$region['id'] === (string)$currentId) {
                $currentName = $region['name'];

                // 如果有下一级且当前地区有子数据
                if ($currentIndex + 1 < count($regionIds) && !empty($region['children'])) {
                    $nextPath = self::buildPathByLevel($regionIds, $region['children'], $currentIndex + 1);
                    return $nextPath ? $currentName . '/' . $nextPath : $currentName;
                }

                return $currentName;
            }
        }

        return null;
    }

    /**
     * 获取区域类型文本
     */
    public function getAttrRegionTypeText()
    {
        $types = [
            1 => '默认区域',
            2 => '指定区域',
            3 => '偏远地区',
        ];
        return $types[$this->data['region_type']] ?? '未知';
    }

    /**
     * 获取是否包邮文本
     */
    public function getAttrIsFreeShippingText()
    {
        return $this->data['is_free_shipping'] == 1 ? '是' : '否';
    }
}
