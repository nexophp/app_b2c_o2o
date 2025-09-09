<?php

namespace modules\wallet\lib;

class Transfer
{
    public static $cash_out_id;
    public static $account;
    public static $amount;
    public static $order_num;

    /**
     * 转帐记录
     */
    public static function weixin($cash_out_id, $account, $amount)
    {
        self::$cash_out_id = $cash_out_id;
        self::$account = $account;
        self::$amount = $amount;

        if (!self::check($cash_out_id)) {
            return;
        }
        $openid = $account['openid'] ?: "";
        if (!$openid) {
            json_error(['msg' => '请先绑定微信']);
        }
        self::$order_num = create_order_num();
        \modules\payment\lib\WeiXin::transfer($openid, self::$order_num, $amount, $desc = '转帐');
        self::insert('weixin');
        return true;
    }

    /**
     * 银行转帐
     */
    public static function bank($cash_out_id, $account, $amount)
    {
        self::$cash_out_id = $cash_out_id;
        self::$account = $account;
        self::$amount = $amount;

        if (!self::check($cash_out_id)) {
            return;
        }
        self::$order_num = create_order_num();
        // 自行线下转帐
        self::insert('bank');
        return true;
    }


    public static function insert($type)
    {
        db_insert('wallet_transfer', [
            'order_num' => self::$order_num,
            'nid' => self::$cash_out_id,
            'type' => $type,
            'account' => self::$account,
            'amount' => self::$amount,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public static function check($cash_out_id)
    {
        $res = db_get_one("wallet_transfer", "*", [
            'nid' => $cash_out_id,
        ]);
        if ($res) {
            add_log("提现确认重复操作", "提现订单ID:{$cash_out_id} 已存在转账记录", 'error');
            return;
        }
        return true;
    }
}
