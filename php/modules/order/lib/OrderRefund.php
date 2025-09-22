<?php

/**
 * 订单退款
 */

namespace modules\order\lib;

use modules\order\model\OrderRefundModel;
use modules\order\model\OrderItemModel;
use modules\order\model\OrderRefundItemModel;
use modules\order\model\OrderRefundAddressModel;
use modules\order\model\OrderModel;

class OrderRefund
{
    /**
     * 取消退款时，订单明细数量回滚
     * @param array $items order_refund_item表
     */
    public static function returnItems($items)
    {
        foreach ($items as $v) {
            $order_id = $v['order_id'];
            $order_item_id = $v['order_item_id'];
            $order_item = OrderItemModel::model()->find($order_item_id, 1);
            $order = OrderModel::model()->find($order_id, 1);

            OrderItemModel::model()->update([
                'can_refund_num' => $order_item['can_refund_num'] + $v['num'],
                'refund_num' => $order_item['refund_num'] - $v['num'],
                //已售后金额
                'has_refund_amount' => bcsub($order_item['has_refund_amount'], $v['amount'], 2),
                //实收金额
                'real_get_amount' => bcadd($order_item['real_get_amount'], $v['amount'], 2),
            ], [
                'id' => $v['order_item_id'],
            ], true);
            OrderModel::model()->update([
                'can_refund_amount' => bcadd($order['can_refund_amount'], $v['amount'], 2),
                'has_refund_amount' => bcsub($order['has_refund_amount'], $v['amount'], 2),
                'real_get_amount' => bcadd($order['real_get_amount'], $v['amount'], 2),
            ], [
                'id' => $order_id,
            ], true);
        }
    }
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
        //检测售后数量是否足够
        $item_ids = array_column($order_item_ids, 'id');
        $items = OrderItemModel::model()->find(['id' => $item_ids]);

        // 创建ID到数量的映射
        $item_num_map = [];
        foreach ($order_item_ids as $item) {
            $item_num_map[$item['id']] = $item['num'];
        }

        // 检查每个商品的可售后数量
        foreach ($items as $item) {
            $request_num = $item_num_map[$item['id']];
            if ($request_num > $item['can_refund_num']) {
                json_error('商品「' . $item['title'] . '」可售后数量不足，最多可售后' . $item['can_refund_num'] . '件');
            }
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
            $item_ids = array_column($order_item_ids, 'id');
            $items = OrderItemModel::model()->find(['id' => $item_ids]);

            // 创建ID到数量的映射
            $item_num_map = [];
            foreach ($order_item_ids as $item) {
                $item_num_map[$item['id']] = $item['num'];
            }

            foreach ($items as $v) {
                $price = $v['price'];
                $num = $item_num_map[$v['id']]; // 使用传入的数量
                if ($num <= 0) {
                    json_error('商品「' . $v['title'] . '」售后数量不能小于0');
                }
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
                if (in_array($type, ['return', 'refund'])) {
                    //更新订单明细
                    OrderItemModel::model()->update([
                        'can_refund_num' => $v['can_refund_num'] - $num,
                        'refund_num' => $v['refund_num'] + $num,
                        'has_refund_amount' => bcadd($v['has_refund_amount'], $refund_amount, 2),
                        'can_refund_amount' => bcsub($v['can_refund_amount'], $refund_amount, 2),
                        'real_get_amount' => bcsub($v['real_get_amount'], $refund_amount, 2),
                    ], [
                        'id' => $v['id'],
                    ], true);
                    // 更新订单
                    OrderModel::model()->update([
                        'has_refund_amount' => bcadd($v['has_refund_amount'], $refund_amount, 2),
                        'can_refund_amount' => bcsub($v['can_refund_amount'], $refund_amount, 2),
                        'real_get_amount' => bcsub($v['real_get_amount'], $refund_amount, 2),
                    ], [
                        'id' => $order_id,
                    ], true);
                }
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
