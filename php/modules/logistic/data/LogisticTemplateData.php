<?php

namespace modules\logistic\data;

use modules\logistic\model\LogisticTemplateModel;
use modules\logistic\model\LogisticTemplateRegionModel;

class LogisticTemplateData
{
    /**
     * 重置模板数据
     */
    public static function resetData($data)
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'seller_id' => $data['seller_id'],
            'is_free_shipping' => $data['is_free_shipping'],
            'free_shipping_amount' => $data['free_shipping_amount'],
            'delivery_type' => $data['delivery_type'],
            'delivery_type_text' => self::getDeliveryTypeText($data['delivery_type']),
            'status' => $data['status'],
            'status_text' => $data['status'] == 1 ? '启用' : '禁用',
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'created_at_text' => $data['created_at'] ? date('Y-m-d H:i:s', $data['created_at']) : '',
            'updated_at_text' => $data['updated_at'] ? date('Y-m-d H:i:s', $data['updated_at']) : '',
            'regions' => $data['regions'],

        ];
    }

    /**
     * 重置区域数据
     */
    public static function resetRegionData($data)
    {
        return [
            'id' => $data['id'],
            'template_id' => $data['template_id'],
            'region_type' => $data['region_type'],
            'region_type_text' => self::getRegionTypeText($data['region_type']),
            'regions' => $data['regions'],
            'regions_str' => $data['regions_str'], 
            'first_item' => $data['first_item'],
            'first_fee' => $data['first_fee'],
            'additional_item' => $data['additional_item'],
            'additional_fee' => $data['additional_fee'],
            'is_free_shipping' => $data['is_free_shipping'],
            'is_free_shipping_text' => $data['is_free_shipping'] == 1 ? '是' : '否',
            'free_shipping_amount' => $data['free_shipping_amount'],

            'first_weight' => $data['first_weight'],
            'additional_weight' => $data['additional_weight'],
        ];
    }

    /**
     * 获取计费方式文本
     */
    public static function getDeliveryTypeText($type)
    {
        $types = [
            1 => '按件数',
            2 => '按重量',
            3 => '按体积',
        ];
        return $types[$type] ?? '未知';
    }

    /**
     * 获取区域类型文本
     */
    public static function getRegionTypeText($type)
    {
        $types = [
            1 => '默认区域',
            2 => '指定区域',
            3 => '偏远地区',
        ];
        return $types[$type] ?? '未知';
    }

    /**
     * 获取卖家的所有运费模板
     */
    public static function getSellerTemplates($seller_id)
    {
        $templates = LogisticTemplateModel::model()->findAll([
            'seller_id' => $seller_id,
            'ORDER' => ['id' => 'ASC']
        ]);

        $result = [];
        foreach ($templates as $template) {
            $item = self::resetData($template);
            // 获取模板区域设置
            $item['regions'] = self::getTemplateRegions($template['id']);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * 获取模板区域设置
     */
    public static function getTemplateRegions($template_id)
    {
        $regions = LogisticTemplateRegionModel::model()->findAll([
            'template_id' => $template_id,
            'ORDER' => ['region_type' => 'ASC']
        ]);

        $result = [];
        foreach ($regions as $region) {
            $result[] = self::resetRegionData($region);
        }

        return $result;
    }

    /**
     * 获取模板详情
     */
    public static function getTemplateDetail($template_id)
    {
        $template = LogisticTemplateModel::model()->find($template_id);
        if (!$template) {
            return null;
        }

        $data = self::resetData($template);
        $data['regions'] = self::getTemplateRegions($template_id);

        return $data;
    }

    /**
     * 计算运费
     * @param int $template_id 模板ID
     * @param int $province_id 省份ID
     * @param float $quantity 数量/重量/体积
     * @param float $order_amount 订单金额
     * @return float 运费
     */
    public static function calculateFee($template_id, $province_id, $quantity, $order_amount = 0)
    {
        $template = LogisticTemplateModel::model()->find($template_id);
        if (!$template) {
            return 0;
        }

        // 如果模板设置了包邮且订单金额满足包邮条件
        if ($template['is_free_shipping'] == 1 && $order_amount >= $template['free_shipping_amount']) {
            return 0;
        }

        // 获取适用的区域设置
        $region_setting = self::getApplicableRegion($template_id, $province_id);
        if (!$region_setting) {
            return 0;
        }

        // 如果区域设置了包邮且订单金额满足包邮条件
        if ($region_setting['is_free_shipping'] == 1 && $order_amount >= $region_setting['free_shipping_amount']) {
            return 0;
        }

        // 计算运费
        $fee = $region_setting['first_fee'];
        if ($quantity > $region_setting['first_item'] && $region_setting['additional_item'] > 0) {
            $additional_quantity = $quantity - $region_setting['first_item'];
            $additional_count = ceil($additional_quantity / $region_setting['additional_item']);
            $fee += $additional_count * $region_setting['additional_fee'];
        }

        return $fee;
    }

    /**
     * 获取适用的区域设置
     */
    private static function getApplicableRegion($template_id, $province_id)
    {
        // 先查找指定区域
        $regions = LogisticTemplateRegionModel::model()->findAll([
            'template_id' => $template_id,
            'region_type' => 2
        ]);

        foreach ($regions as $region) {
            $region_ids = json_decode($region['regions'], true) ?: [];
            if (in_array($province_id, $region_ids)) {
                return self::resetRegionData($region);
            }
        }

        // 查找偏远地区
        $remote_regions = LogisticTemplateRegionModel::model()->findAll([
            'template_id' => $template_id,
            'region_type' => 3
        ]);

        foreach ($remote_regions as $region) {
            $region_ids = json_decode($region['regions'], true) ?: [];
            if (in_array($province_id, $region_ids)) {
                return self::resetRegionData($region);
            }
        }

        // 最后返回默认区域
        $default_region = LogisticTemplateRegionModel::model()->find([
            'template_id' => $template_id,
            'region_type' => 1
        ]);

        return $default_region ? self::resetRegionData($default_region) : null;
    }
}
