<?php

namespace modules\logistic\lib;

use modules\order\lib\ProductStock;

class Shipping
{
    public static function do(&$shipping)
    {
        $items = $shipping['items'];
        $amount = $shipping['amount'];
        $real_amount = $shipping['real_amount'];
        $has_err = false;

        foreach ($items as $k => $v) {
            $product_id = $v['product_id'];
            $product = db_get_one("product", "*", ['id' => $product_id]);
            $items[$k]['has_err'] = false;
            if (!$product || $product['status'] != 'success') {
                $has_err = true;
                $items[$k]['has_err'] = $has_err;
                $items[$k]['err'] = lang("已下架");
                continue;
            }
            if (!ProductStock::check($v)) {
                $has_err = true;
                $items[$k]['has_err'] = $has_err;
                $items[$k]['err'] = lang("库存不足");
                continue;
            }

            $weight = $product['weight'];
            $length = $product['length'];
            $width = $product['width'];
            $height = $product['height'];
            $freight_template_id = $product['freight_template_id'];

            if ($freight_template_id) {
                $new_item[$freight_template_id][] = [
                    'index' => $k ?: 0,
                    'weight' => $weight,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'num' => $v['num'],
                    'amount' => $v['real_amount'],
                ];
            } else {
                $has_err = true;
                $items[$k]['has_err'] = $has_err;
                $items[$k]['err'] = lang("商品未设置运费模板");
                continue;
            }
        }
        if (!$new_item) {
            $shipping['has_err'] = $has_err;
            $shipping['items'] = $items;
            return;
        }
        $address_id = g("address_id");
        if (!$address_id) {
            return;
        }
        $address = db_get_one("user_address", "*", ['id' => $address_id]);
        $user_region = $address['region'];

        if (in_array($user_region[1], get_china_direct_city())) {
            $user_region[1] = '市辖区';
        }
        $shipping_amount = 0;
        $new_err = false;
        $has_calc_shipping = [];
        foreach ($new_item as $freight_template_id => $_items) {
            $all = \modules\logistic\data\LogisticTemplateData::getTemplateRegions($freight_template_id);
            $allow = [];
            $items_total_amount = 0;
            foreach ($_items as $vv) {
                $items_total_amount = bcadd($items_total_amount, $vv['amount'], 2);
            }
            $find_region = false;

            foreach ($all as $v) {
                $regions_str = $v['regions_str'];
                if ($regions_str) {
                    $arr = explode(",", $regions_str);
                    $allow = [];
                    foreach ($arr as $vv) {
                        $arr_next = explode('/', $vv);
                        $allow[] = $arr_next;
                    }
                    if ($has_calc_shipping[$freight_template_id]) {
                        continue;
                    }
                    if (is_region_in($user_region, $allow)) {
                        $find_region = true;
                        $first_fee = $v['first_fee'] ?: 0; //首件
                        $additional_fee = $v['additional_fee'] ?: 0; //续件
                        $first_weight = $v['first_weight'] ?: 0; //首重
                        $additional_weight = $v['additional_weight'] ?: 0; //续重
                        $free_shipping_amount = $v['free_shipping_amount'] ?: 0;
                        if ($free_shipping_amount > 0 && $items_total_amount >= $free_shipping_amount) {
                            $shipping_amount = 0;
                        } else {
                            $amount = \modules\logistic\lib\Money::calc($_items, $first_fee, $additional_fee, $first_weight, $additional_weight)['total_fee'] ?: 0;
                            $shipping_amount = bcadd($shipping_amount, $amount, 2);
                        }
                        $has_calc_shipping[$freight_template_id] = $amount;
                    }
                }
            }
            if (!$find_region) {
                $new_err = true;
                foreach ($_items as $v) {
                    $k = $v['index'];
                    $items[$k]['has_err'] = $new_err;
                    $items[$k]['err'] = lang("不支持的配送区域");
                }
            }
        }
        $shipping['has_err'] = $has_err;
        $shipping['items'] = $items;
        $shipping['amount'] = $shipping_amount;
        $shipping['real_amount'] =  bcadd($real_amount, $shipping_amount, 2);
    }
}
