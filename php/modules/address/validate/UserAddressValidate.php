<?php

namespace modules\address\validate;

class UserAddressValidate
{
    public static function validate($data)
    {
        $vali = validate(
            [
                'name' => lang('姓名'),
                'phone' => lang('手机号'),
                'province' => lang('省份'),
                'city' => lang('城市'),
                'district' => lang('区县'),
                'detail' => lang('详细地址')
            ],
            $data,
            ['required' => ['name', 'phone', 'province', 'city', 'district', 'detail']]
        );
        if ($vali) {
            json($vali);
        }
    }
}
