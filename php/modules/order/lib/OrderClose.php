<?php

namespace modules\order\lib;

class OrderClose
{
    /**
     * 取消订单
     */
    public static function do($order_id, $update_status = 'close')
    {
        self::returnCoupon($order_id);
        $items = db_get("order_item", '*', [
            'order_id' => $order_id,
        ]);
        if ($items) {
            foreach ($items as $v) {
                ProductStock::up($v);
                if ($update_status) {
                    db_update('order', [
                        'status' => $update_status,
                    ], [
                        'id' => $v['order_id'],
                    ]);
                }
            }
        }
    }
    /**
     * 优惠券退回
     */
    public static function returnCoupon($order_id)
    {
        $all = db_get("order_info", '*', [
            'order_id' => $order_id,
            'type' => 'coupon',
        ]);

        if ($all) {
            foreach ($all as $v) {
                db_update("coupon_user", [
                    'order_id' => 0,
                    'status' => 1,
                    'used_at' => '',
                ], ['id' => $v['nid']]);
            }
        }
    }
}
