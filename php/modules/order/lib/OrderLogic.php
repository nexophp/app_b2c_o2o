<?php

/**
 * 订单物流
 */

namespace modules\order\lib;

use modules\logistic\lib\Logistic;
use modules\order\model\OrderLogisticModel;

class OrderLogic
{
    public static function get($order_num)
    {
        if (!$order_num) {
            return;
        }
        $order = OrderLogisticModel::model()->findOne(['order_num' => $order_num]);
        if (!$order) {
            return;
        }
        $logisticInfo = [];
        $no = $order['no'];
        $type = $order['type'];
        if ($no && $type) {
            $logisticInfo = Logistic::get($no, $type);
            if ($logisticInfo) {
                $status = 'shipped';
                if ($logisticInfo['status'] == '已签收') {
                    $status = 'complete';
                }
                if ($order->data['status'] != 'complete') {
                    OrderLogisticModel::model()->update([
                        'data' => $logisticInfo,
                        'status' => $status,
                    ], ['id' => $order['id']], true);
                }
            }
        }
        return [
            'no' => $no,
            'type' => $type,
            'data' => $logisticInfo,
            'created_at_format' => date('Y-m-d H:i:s', $order['created_at']),
            'updated_at_format' => date('Y-m-d H:i:s', $order['updated_at']), 
        ];
    }
}
