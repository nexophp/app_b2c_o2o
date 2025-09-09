<?php

namespace modules\wallet\model;

class WalletInModel extends WalletModel
{
    protected $table = 'wallet_cash_in';

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        $data['created_at_format'] = date('Y-m-d H:i', $data['created_at']);
        //wait success fail
        $data['status_text'] = $data['status'] == 'wait' ? '待确认' : ($data['status'] == 'success' ? '已确认' : '失败');

        $data['rate_txt'] = ($data['rate'] * 100). '%';
        $data['flag'] = 'in';
    }
}
