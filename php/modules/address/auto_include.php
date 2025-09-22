<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => '收货地址',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];


/**
 * 直辖市 
 */
function get_china_direct_city()
{
    return [
        '北京市',
        '天津市',
        '上海市',
        '重庆市'
    ];
}
/**
 * 清理地址中的直辖市
 */
function get_clean_address($address)
{
    $citys = get_china_direct_city();
    foreach ($citys as $city) {
        if (strpos($address, $city) !== false) {
            $count = strlen($city);
            $address = substr($address, $count);
            return $address;
            break;
        }
    }
    return $address;
}
