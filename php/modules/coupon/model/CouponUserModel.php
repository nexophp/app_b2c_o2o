<?php

namespace modules\coupon\model;

class CouponUserModel extends \core\AppModel
{
    protected $table = 'coupon_user';

    protected $has_one = [
        'coupon' => [CouponModel::class, 'coupon_id', 'id'],
    ];

    public function afterFind(&$data)
    {
        $data['type_text'] = CouponModel::getTypeOptions()[$data['type']] ?? $data['type'];
        $data['status_text'] = self::getStatusOptions()[$data['status']] ?? $data['status'];
        $data['created_at_text'] = date('Y-m-d H:i:s', $data['created_at']);
        if ($data['used_at']) {
            $data['used_at_text'] = date('Y-m-d H:i:s', $data['used_at']);
        }
        if ($data['expired_at']) {
            $data['expired_at_text'] = date('Y-m-d H:i:s', $data['expired_at']);
        }
        // 判断是否过期
        $data['is_expired'] = $data['expired_at'] && time() > $data['expired_at'];
        // 格式化金额显示
        if ($data['type'] == 1) {
            $data['value_text'] = '￥' . $data['value'];
        } else {
            $data['value_text'] = ($data['value'] * 10) . '折';
        }
        $data['condition_text'] = $data['condition'] > 0 ? '满' . $data['condition'] . '元可用' : '无门槛';

        $data['class'] = '';
        if ($data['status'] == 2) {
            $data['class'] = 'used';
        } else if ($data['status'] == -1) {
            $data['class'] = 'expired';
        }
        $user_info = get_user($data['user_id']);
        unset($user_info['password']);
        if ($user_info['phone']) {
            $user_info['phone'] = \lib\DataMasker::phone($user_info['phone']);
        }
        $data['user_info'] = $user_info;

        $products = $data['products'];
        $str = '';
        if ($products) {
            $all = db_get("product", "*", ['id' => $products]);
            $str = '';
            foreach ($all as $v) {
                $str .= $v['title'] . ',';
            }
            $str = rtrim($str, ',');
            $data['products_text'] = $str;
        }
        if ($str) {
        } else {
            $str = "所有商品";
        }
        $data['products_text'] = $str;
        $data['name'] = $this->coupon->name;
    }

    /**
     * 获取状态选项
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            1 => '可用',
            2 => '已使用',
            -1 => '已过期'
        ];
    }

    /**
     * 更新过期的优惠券
     * @param int $user_id
     * @return void
     */
    public function updateExpired($user_id)
    {
        $this->update(['status' => -1], ['user_id' => $user_id, 'expired_at' => ['<', time()]], true);
    }
}
