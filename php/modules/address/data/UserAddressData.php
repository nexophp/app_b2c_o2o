<?php

namespace modules\address\data;

class UserAddressData
{
    public static function data(&$data)
    {
        $province = $data['province'] ?? '';
        $city = $data['city'] ?? '';
        $district = $data['district'] ?? ''; 
        $str_2 = $data['str_2'] ?? '';
        if ($city == '市辖区') {
            $city = '';
        }
        $data['address'] = $province . $city . $district .  $str_2 . $data['detail'];
    }
}
