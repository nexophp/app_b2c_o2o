<?php

namespace modules\order\lib;

class OrderConfirm
{
    /**
     * 确认订单，记录金额
     */
    public static function confirm($items, $addressId = 0)
    {
        // 计算商品总价
        $real_amount = 0;
        $total_num = 0;
        $new_items = [];
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? '';
            $num = intval($item['num'] ?? 1);
            $price = floatval($item['price'] ?? 0);
            if (empty($productId) || $num <= 0 || $price <= 0) {
                continue;
            }
            $item_amount = bcmul($price, $num, 2);
            $real_amount = bcadd($real_amount, $item_amount, 2);
            $total_num = bcadd($total_num, $num);
            $price = bcmul($price,1,2);
            $new_items[] = [
                'product_id' => $productId,
                'title' => $item['title'] ?? '',
                'image' => $item['image'] ?? '',
                'spec' => $item['spec'] ?? '',
                'attr' => $item['attr'] ?? '',
                'price' => $price,
                'real_price' => $price,
                'num' => $num,
                'amount' => $item_amount,
                'real_amount' => $item_amount,
                'spec' => $item['spec'] ?? ''
            ];
        }
        $data = [
            'items' => $new_items,
            'amount' => bcmul($real_amount, 1, 2),
            'real_amount' => $real_amount,
            'coupon' => [],
            'shipping' => 0,
            'num' => $total_num,
        ];
        do_action("order.confirm", $data);
        return $data;
    }
}
