<?php

namespace modules\wallet\model;

class WalletOutModel extends WalletModel
{
    protected $table = 'wallet_cash_out';

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        $data['created_at_format'] = date('Y-m-d H:i', $data['created_at']);
        $data['status_text'] = $data['status'] == 'wait' ? '待审核' : ($data['status'] == 'success' ? '已打款' : '失败');
        $data['account_info'] = is_string($data['account']) ? json_decode($data['account'], true) : $data['account'];
        $data['type_icon'] = cdn()."/misc/img/".$data['type']."_pay.png";
        $data['flag'] = 'out';

        switch ($data['type']) {
            case 'weixin':
                $account_info = '微信账号：'.$data['account_info']['openid'];
                break;
            case 'alipay':
                $account_info = '支付宝账号：'.$data['account_info']['account'].'（'.$data['account_info']['realname'].'）';

                break;
            case 'bank':
                $account_info = '银行账号：'.$data['account_info']['bank_card'].'（'.$data['account_info']['card_holder'].'） 开户行：'.$data['account_info']['bank_name'];

                break;
            default:
                $account_info = '';
                break;
        }


        $data['account_info'] = $account_info;
    }
}
