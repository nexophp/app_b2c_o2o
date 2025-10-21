<?php

namespace modules\webpos\cmd;

use modules\order\lib\OrderClose;

/**
 * 超时自动关闭订单
 */
class Close
{
    public function run()
    {
        /**
         * 超时自动关闭订单，未支付订单5分钟后关闭
         */
        $sec = get_config("order_auto_cancel_time") ?: 5;
        $sec = $sec * 60;
        $time = time() - $sec;

        $all = db_get('order', "*", [
            'created_at[<]' => $time,
            'status' => 'wait',
            'type' => 'webpos',
            'LIMIT' => 1000,
        ]);
        $i = 0;
        if ($all) {
            foreach ($all as $order) {
                $i++;
                OrderClose::do($order['id']);
            }
        }
        if ($i) {
            cli_success('超时自动关闭订单成功，共关闭' . $i . '条订单');
        } else {
            cli_success('超时自动关闭订单，共关闭0条订单');
        }
    }
}
