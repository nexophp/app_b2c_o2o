<?php

namespace modules\order\cmd;

use modules\payment\lib\WeiXin;
use modules\payment\lib\AlipayRefund;

/**
 * 售后订单退款
 */
class Refund
{
    public function run()
    {
        /**
         *  售后管理中已通过的订单，将原路退款
         */
        $all = db_get('order_refund_money', "*", [
            'status' => 'wait',
            'LIMIT' => 1000,
        ]);
        $i = 0;
        if ($all) {
            foreach ($all as $v) {
                $order_id = $v['order_id'];
                $id = $v['id'];
                $order_info = db_get_one('order', '*', [
                    'id' => $order_id,
                ]);
                $payment_method = $order_info['payment_method'];
                $order_num = $order_info['order_num'];
                $real_amount = $order_info['real_amount'];
                $refund_amount = $v['amount'];
                /**
                 * weixin  alipay
                 */
                if ($payment_method == 'weixin') {
                    WeiXin::refund($order_num, $real_amount, $refund_amount, 'CNY', $refund_desc = '退款');
                    $i++;
                } elseif ($payment_method == 'alipay') {
                    AlipayRefund::run($order_num, $refund_amount);
                    $i++;
                }
                db_update("order_refund_money", ['status' => 'success'], ['id' => $id]);
                
            }
        }
        if ($i) {
            cli_success('售后订单退款成功，共退款' . $i . '条订单');
        } else {
            cli_success('售后订单退款，共退款0条订单');
        }
    }
}
