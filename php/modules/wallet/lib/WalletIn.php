<?php

namespace modules\wallet\lib;

class WalletIn
{
    /**
     * 收入
     * @param int $user_id 用户id
     * @param int $amount 金额
     * @param int $order_amount 订单金额
     * @param int $percent 比例 千分比 
     * @param string $desc 描述
     * @param string $status 状态  success wait
     * @param string $order_num 订单号
     * @param string $type 类型
     * @param int $seller_id 卖家id
     * @param int $store_id 店铺id
     * @return void
     */
    public static function add($opt = [])
    {
        db_action(function () use ($opt) {
            $user_id = $opt['user_id'] ?: 0;
            $order_amount = $opt['order_amount'] ?: 0;
            $rate = $opt['rate'] ?: 0;
            $desc = $opt['desc'] ?: '';
            $status = $opt['status'] ?: 'success';
            $order_num = $opt['order_num'] ?: create_order_num();
            $type = $opt['type'] ?: 'product';
            $seller_id = $opt['seller_id'] ?: 0;
            $store_id = $opt['store_id'] ?: 0;
            $rate_real = 0;
            if ($rate > 0 && $rate <= 1000) {
                $rate_real = bcdiv($rate, 1000, 3);
                $amount = bcmul($order_amount, $rate_real, 2);
            } else {
                json_error(['msg' => lang('比例错误,请输入1-1000之间的数字')]);
            }
            if (!in_array($status, ['wait', 'success'])) {
                json_error(['msg' => lang('状态错误')]);
            }
            $wallet_cash_in_data = [
                'user_id' => $user_id,
                'seller_id' => $seller_id,
                'store_id' => $store_id,
                'order_amount' => $order_amount,
                'amount' => $amount,
                'rate' => $rate_real,
                'desc' => $desc,
                'status' => $status,
                'order_num' => $order_num,
                'type' => $type,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            $nid = db_insert("wallet_cash_in", $wallet_cash_in_data);
            db_insert('wallet_in_out', [
                'nid' => $nid,
                'type' => 'in', 
                'user_id'=>$user_id,
                'seller_id'=>$seller_id,
                'store_id'=>$store_id, 
            ]);
            $where = [
                'user_id' => $user_id,
                'seller_id' => $seller_id,
                'store_id' => $store_id,
            ];
            $wallet = db_get_one('wallet', "*", $where);
            if (!$wallet) {
                $total_amount = 0;
                $in_amount = 0;
                $wait_in_amount = 0;
                $can_out_amount = 0;
                $out_amount = 0;
                $wait_out_amount = 0;
                $wallet_data = [
                    'user_id' => $user_id,
                    'seller_id' => $seller_id,
                    'store_id' => $store_id,
                    'total_amount' => $total_amount,
                    'in_amount' => $in_amount,
                    'wait_in_amount' => $wait_in_amount,
                    'can_out_amount' => $can_out_amount,
                    'out_amount' => $out_amount,
                    'wait_out_amount' => $wait_out_amount,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                $wallet_id = db_insert("wallet", $wallet_data);
            } else {
                $wallet_id = $wallet['id'];
                $wait_in_amount = $wallet['wait_in_amount'];
                $total_amount = $wallet['total_amount'];
                $in_amount = $wallet['in_amount'];
                $can_out_amount = $wallet['can_out_amount'];
                $out_amount = $wallet['out_amount'];
                $wait_out_amount = $wallet['wait_out_amount'];
            }
            if ($status == 'wait') {
                $wait_in_amount =  bcadd($wait_in_amount, $amount, 2);
                $total_amount =  bcadd($total_amount, $amount, 2);
                db_update('wallet', [
                    'wait_in_amount' => $wait_in_amount,
                    'total_amount' => $total_amount,
                ], [
                    'id' => $wallet_id,
                ]);
            } elseif ($status == 'success') {
                $in_amount =  bcadd($in_amount, $amount, 2);
                $total_amount =  bcadd($total_amount, $amount, 2);
                $can_out_amount =  bcadd($can_out_amount, $amount, 2);

                db_update('wallet', [
                    'in_amount' => $in_amount,
                    'total_amount' => $total_amount,
                    'can_out_amount' => $can_out_amount,
                ], [
                    'id' => $wallet_id,
                ]);
                
            }
        });
    }
    /**
     * 确认收入
     * wait状态下的订单转in_amount
     */
    public static function confirm($order_num, $type = 'product') 
    {
        $all = db_get("wallet_cash_in", "*", [
            'order_num' => $order_num,
            'type' => $type,
            'status' => 'wait',
        ]);
        if (!$all) {
            json_error(['msg' => lang('订单不存在')]);
        }

        db_action(function () use ($all) {
            foreach ($all as $res) {
                $user_id = $res['user_id'];
                $seller_id = $res['seller_id'];
                $store_id = $res['store_id'];
                $amount = $res['amount'];
                $where = [
                    'user_id' => $user_id,
                    'seller_id' => $seller_id,
                    'store_id' => $store_id,
                ];
                $wallet = db_get_one("wallet", "*", $where);
                if (!$wallet) {
                    json_error(['msg' => lang('钱包不存在')]);
                }
                $wait_in_amount = $wallet['wait_in_amount'];
                $in_amount = $wallet['in_amount'];
                $wait_in_amount = bcsub($wait_in_amount, $amount, 2);
                $in_amount = bcadd($in_amount, $amount, 2);

                $can_out_amount = $wallet['can_out_amount'];
                $can_out_amount = bcadd($can_out_amount, $amount, 2);

                db_update('wallet', [
                    'wait_in_amount' => $wait_in_amount,
                    'can_out_amount' => $can_out_amount,
                    'in_amount' => $in_amount,
                ], [
                    'id' => $wallet['id'],
                ]);
                db_update('wallet_cash_in', [
                    'status' => 'success',
                    'updated_at' => time(),
                ], [
                    'id' => $res['id'],
                ]);
            }
        });
    }
}
