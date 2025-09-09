<?php

namespace modules\wallet\lib;

class WalletOut
{
    /**
     * 提现
     */
    public static function add($opt)
    {
        db_action(function () use ($opt) {
            $user_id = $opt['user_id'] ?: 0;
            $seller_id = $opt['seller_id'] ?: 0;
            $store_id = $opt['store_id'] ?: 0;
            $amount = $opt['amount'] ?: 0;
            //提现方式
            $type = $opt['type'] ?: 'weixin';
            $rate = get_config('wallet_rate') ?: 10;
            if ($rate <= 0) {
                json_error(['msg' => lang('请先设置提现比例')]);
            }
            $wallet_min = get_config('wallet_min') ?: 10;
            if ($wallet_min <= 0) {
                json_error(['msg' => lang('请先设置提现最低金额')]);
            }
            if (isset($opt['rate'])) {
                $rate = $opt['rate'];
            }
            if ($rate < 0 && $rate >= 1000) {
                json_error(['msg' => lang('请先正确设置提现比例')]);
            }
            //提现帐号 [openid=>]
            $account = $opt['account'] ?: '';
            if ($amount < $wallet_min) {
                json_error(['msg' => lang('提现金额不能小于最低提现金额')]);
            }
            $where = [
                'user_id' => $user_id,
                'seller_id' => $seller_id,
                'store_id' => $store_id,
            ];
            $wallet = db_get_one("wallet", "*", $where);
            $can_out_amount = $wallet['can_out_amount'] ?: 0;
            $wait_out_amount = $wallet['wait_out_amount'] ?: 0;

            if ($can_out_amount < $amount) {
                json_error(['msg' => lang('余额不足')]);
            }
            if ($rate > 0 && $rate < 1000) {
                $rate_real = bcdiv($rate, 1000, 3);
                //手续费
                $rate_amount = bcmul($amount, $rate_real, 2);
            } else {
                $rate_amount = 0;
                $rate_real = 0;
            }
            //实际用户到手金额
            $real_amount = bcsub($amount, $rate_amount, 2);
            //写提现表
            $data = [
                'order_num' => create_order_num(),
                'status' => 'wait',
                'type' => $type,
                'user_id' => $user_id,
                'seller_id' => $seller_id,
                'store_id' => $store_id,
                'amount' => $amount,
                'rate' => $rate_real,
                'rate_amount' => $rate_amount,
                'real_amount' => $real_amount,
                'account' => $account,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            $nid = db_insert('wallet_cash_out', $data);
            db_insert('wallet_in_out', [
                'nid' => $nid,
                'type' => 'out',
                'user_id' => $user_id,
                'seller_id' => $seller_id,
                'store_id' => $store_id,
            ]);
            //更新钱包
            db_update('wallet', [
                'can_out_amount' => bcsub($can_out_amount, $amount, 2),
                'wait_out_amount' => bcadd($wait_out_amount, $amount, 2),
            ], $where);
        });
    }
    /**
     * 同意提现并打款
     */
    public static function confirm($out_id)
    {
        $res = db_get_one("wallet_cash_out", "*", [
            'id' => $out_id,
            'status' => 'wait',
        ]);
        if (!$res) {
            json_error(['msg' => lang('提现申请不存在')]);
        }
        $id = $res['id'];
        $real_amount = $res['real_amount'];
        $account = $res['account']; //数组openid
        $type = $res['type']; //weixin 
        //微信转帐代码
        $class = "\modules\wallet\lib\Transfer";
        $flag = false;
        if (class_exists($class) && method_exists($class, $type)) {
            $flag = $class::$type($id, $account, $real_amount);
        } else {
            json_error(['msg' => lang('提现方式不存在') . $type]);
        }
        if ($flag) {
            db_action(function () use ($id, $res) {
                db_update("wallet_cash_out", ['status' => 'success', 'updated_at' => time()], ['id' => $id]);
                $wallet = db_get_one("wallet", "*", [
                    'user_id' => $res['user_id'],
                    'seller_id' => $res['seller_id'],
                    'store_id' => $res['store_id'],
                ]);
                $out_amount =   $wallet['out_amount'] ?: 0;
                $wait_out_amount =   $wallet['wait_out_amount'] ?: 0;
                $real_amount =   $res['amount'];
                db_update('wallet', [
                    'out_amount' => bcadd($out_amount, $real_amount, 2),
                    'wait_out_amount' => bcsub($wait_out_amount, $real_amount, 2),
                ],  ['id' => $wallet['id']]);
            });
        } else {
            db_update("wallet_cash_out", ['status' => 'fail', 'updated_at' => time()], ['id' => $id]);
        }
        return $flag;
    }
}
