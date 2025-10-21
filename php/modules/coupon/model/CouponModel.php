<?php

namespace modules\coupon\model;

class CouponModel extends \core\AppModel
{
    protected $table = 'coupon';

    public function afterFind(&$data)
    {
        $data['type_text'] = self::getTypeOptions()[$data['type']] ?? $data['type'];
        $data['status_text'] = self::getStatusOptions()[$data['status']] ?? $data['status'];
        $data['created_at_text'] = date('Y-m-d H:i:s', $data['created_at']);
        $data['start_time_text'] = date('Y-m-d H:i:s', $data['start_time']);
        $data['end_time_text'] = date('Y-m-d H:i:s', $data['end_time']);

        // 判断是否过期
        $data['is_expired'] = time() > $data['end_time'];

        // 格式化金额显示
        if ($data['type'] == 1) {
            $data['value_text'] = '￥' . $data['value'];
        } else {
            $data['value_text'] = ($data['value'] * 10) . '折';
        }

        $data['condition_text'] = $data['condition'] > 0 ? '满' . $data['condition'] . '元可用' : '无门槛';

        $data['type'] = (string)$data['type'];
        $products =  $data['products'];
        $products_info = [];
        if ($products) {
            $products_info = db_get("product", "*", [
                'id' => $products
            ]);
        }

        $data['products_info'] = $products_info;
    }

    /**
     * 获取优惠券类型选项
     * @return array
     */
    public static function getTypeOptions()
    {
        return [
            1 => '满减券',
            2 => '折扣券'
        ];
    }

    /**
     * 获取状态选项
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            1 => '启用',
            -1 => '已删除'
        ];
    }
}
