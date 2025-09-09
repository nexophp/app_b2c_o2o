<?php

namespace modules\wallet\model;

use modules\admin\model\UserModel;

class WalletModel extends \core\AppModel
{
    protected $table = 'wallet';

    protected $has_one = [
        'user' => [UserModel::class, 'user_id', 'id'],
    ];

    public function afterFind(&$data)
    {
        $user = $this->user;
        $user_phone = $user->phone;
        if(!$user_phone){
            $user_phone = $user->username;
        }
        $data['user_phone'] = $user_phone; 

    }
}
