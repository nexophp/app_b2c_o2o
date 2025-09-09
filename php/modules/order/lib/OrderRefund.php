<?php

/**
 * 订单退款
 */

namespace modules\order\lib;

use modules\order\model\OrderRefundModel;
use modules\order\model\OrderItemModel;
use modules\order\model\OrderRefundItemModel;
use modules\order\model\OrderRefundAddressModel;

class OrderRefund
{
    /**
     * 创建退款订单
     * @param int $order_id 订单ID
     * @param array $order_item_ids 订单明细ID
     * @param string $type 退款类型
     * @param string $reason 退款原因
     * @param string $desc 退款描述
     * @param array $images 退款图片
     * @return array
     */
    public static function create($order_id, $order_item_ids, $type, $reason, $desc = '',  $images = [], $address = [])
    {
        //检测是否存在售后记录
        $refunds = OrderRefundItemModel::model()->find([
            'order_item_id' => $order_item_ids,
            'status[!]' => 'cancel',
        ]);
        if ($refunds) {
            json_error(lang('订单已存在售后记录'));
        }
        $refund_id = 0;
        db_action(function () use ($order_id, $order_item_ids, $type, $reason, $desc, $images, &$refund_id, $address) { 
            global $uid;
            $refund_id = OrderRefundModel::model()->insert([
                'order_id' => $order_id,
                'order_num' => create_order_num(),
                'type' => $type,
                'reason' => $reason,
                'desc' => $desc,
                'images' => $images,
                'user_id' => $uid,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
            $total_refund_amount = 0;
            $total = 0;
            $items = OrderItemModel::model()->find(['id' => $order_item_ids]);
            foreach ($items as $v) {
                $price = $v['price'];
                $num = $v['num'];
                $refund_amount = bcmul($price, $num, 2);
                $total_refund_amount = bcadd($total_refund_amount, $refund_amount, 2);
                $total = $total + $num;
                OrderRefundItemModel::model()->insert([
                    'refund_id' => $refund_id,
                    'order_item_id' => $v['id'],
                    'order_id' => $order_id,
                    'price' => $price,
                    'num' => $num,
                    'amount' => $refund_amount,
                    'image' => $v['image'] ?? '',
                    'title' => $v['title'] ?? '',
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
            }
            //退款、退货退款 时需要计算退款金额
            if ($type == 'refund' || $type == 'return') {
                OrderRefundModel::model()->update([
                    'num' => $total,
                    'amount' => $total_refund_amount,
                ], [
                    'id' => $refund_id,
                ], true);
            }

            if ($address) {
                OrderRefundAddressModel::model()->insert([
                    'refund_id' => $refund_id,
                    'name' => $address['address'] ?? '',
                    'phone' => $address['phone'] ?? '',
                    'address' => $address['address'] ?? '',
                ]);
            }
        });
        return [
            'id' => $refund_id,
        ];
    }
}
