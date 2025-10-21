<?php

namespace modules\coupon\lib;

class Coupon
{
    /**
     * 处理优惠券折扣并返回格式化的订单数据
     * @param array $data 输入数据，包含商品列表、订单总金额和可选的优惠券信息
     * @return array 格式化的订单数据，包含优惠后的商品价格
     */
    public static function do($data)
    {
        global $user_id; // 全局用户ID
        $items = $data['items']; // 商品列表
        $total_amount = $data['real_amount']; // 订单原始总金额
        $coupon = $data['coupon'] ?? null; // 可选的指定优惠券
        $total_num = array_sum(array_column($items, 'num')); // 总商品数量

        // 获取当前时间戳
        $now = time();

        // 初始化优惠券数据
        $best_coupon = null;
        $coupon_data = [];


        if ($coupon) {
            // 如果指定了优惠券，验证其有效性
            if (
                isset($coupon['id'], $coupon['coupon_id'], $coupon['type'], $coupon['value'], $coupon['condition']) &&
                $total_amount >= (float)$coupon['condition']
            ) {
                $best_coupon = $coupon;
            }
        } else {
            // 构建查询条件，获取可用优惠券
            $where = [
                'user_id' => $user_id, // 用户ID
                'status' => 1, // 优惠券状态：1表示可用
                'expired_at[>]' => $now, // 未过期
            ];
            // 从数据库获取可用优惠券列表
            $coupons = db_get('coupon_user', '*', $where) ?: [];
            // 寻找节省金额最多的优惠券
            $max_save = 0.00;
            foreach ($coupons as $coupon_item) {
                // 检查订单总金额是否满足优惠券最低使用条件
                if ($total_amount < (float)$coupon_item['condition']) {
                    continue;
                }
                // 根据优惠券类型计算节省金额
                if ($coupon_item['type'] == 1) {
                    // 满减优惠：直接减去固定金额
                    $save = (float)$coupon_item['value'];
                } else {
                    // 折扣优惠：计算折扣节省的金额
                    $save = bcmul($total_amount, bcsub(1, (float)$coupon_item['value'], 2), 2);
                }
                // 更新最优优惠券
                if ($save > $max_save) {
                    $max_save = $save;
                    $best_coupon = $coupon_item;
                }
            }
        }
        $new_can_coupon_amount = 0;
        // 计算优惠后的订单总金额
        if ($best_coupon) {
            $products = $best_coupon['products'];
            $types = $best_coupon['types'];
            //适用商品
            if ($products) {
                $max_coupon_amount = 0;
                foreach ($items as $v) {
                    $product_id = $v['product_id'];
                    if (in_array($product_id, $products)) {
                        $new_amount = bcmul($v['price'], $v['num'], 2);
                        if ($new_amount > $max_coupon_amount) {
                            $max_coupon_amount = $new_amount;
                        }
                    }
                }
                $new_can_coupon_amount = bcadd($new_can_coupon_amount, $max_coupon_amount, 2);
            }

            if ($best_coupon['type'] == 1) {
                // 满减优惠：总金额减去固定优惠金额
                $coupon_value = (float)$best_coupon['value'];
                $real_amount = bcsub($total_amount, $coupon_value, 2);
            } else {
                // 折扣优惠：总金额乘以折扣比例
                $coupon_value =  bcmul($new_can_coupon_amount, bcsub(1, $best_coupon['value'], 2), 2);
                $real_amount = bcsub($total_amount, $coupon_value, 2);
            }
            // 准备优惠券返回数据
            $coupon_data = [
                'id' => $best_coupon['id'],
                'coupon_id' => $best_coupon['coupon_id'],
                'type' => $best_coupon['type'],
                'value' => $best_coupon['value'],
                'amount' => $coupon_value,
                'condition' => (string)$best_coupon['condition'],
                'name' => db_get_one("coupon", "name", ['id' => $best_coupon['coupon_id']]) ?: '' // 防止查询失败
            ];
        } else {
            // 无可用优惠券，保持原总金额
            $real_amount = $total_amount;
        }
        // 确保优惠后总金额不小于0
        $real_amount = max(0, $real_amount);
        // 计算总优惠金额
        $discount_amount = bcsub($total_amount, $real_amount, 2);
        // 调用 calc 方法分配优惠到每个商品
        $new_items = self::calc($items, $discount_amount);
        //物流
        $has_err = false; 
        $shipping = [
            'items' => $new_items,
            'amount' => 0,
            'real_amount' => $real_amount,
            'has_err'=>$has_err,
        ];
        do_action("shipping", $shipping);
        $real_amount = $shipping['real_amount'] ?: $real_amount;
        $shipping_amount = $shipping['amount'] ?: 0;
        $items = $shipping['items'] ?: $new_items;
        // 格式化返回数据
        return [
            'has_err'=>$shipping['has_err']?:false,
            'items' => $items, // 优惠后的商品列表
            'amount' => bcmul($total_amount, 1, 2), // 订单原始总金额
            'real_amount' => $real_amount, // 优惠后总金额
            'coupon' => $coupon_data, // 使用的优惠券信息
            'shipping' => $shipping_amount, // 运费，固定为0
            'num' => $total_num, // 总商品数量
        ];
    }

    /**
     * 计算订单优惠分摊（使用bcmath精确计算）
     * @param array $items 商品列表，包含 product_id, title, image, price, num, amount
     * @param string $total_discount 总优惠金额（字符串形式避免精度问题）
     * @return array 包含分摊后的商品列表
     */
    public static function calc($items, $total_discount)
    {
        // 设置bcmath计算精度（小数点后2位）
        bcscale(2);

        // 初始化返回结果
        $new_items = [];
        $total_amount = '0.00';

        // 计算订单总金额（使用amount字段）
        foreach ($items as $item) {
            $total_amount = bcadd($total_amount, (string)$item['amount'], 2);
        }

        // 计算每个商品的分摊优惠
        $remaining_discount = $total_discount;

        for ($i = 0; $i < count($items); $i++) {
            $item = $items[$i];
            $item_amount = (string)$item['amount']; // 商品原始总金额
            // 计算可能的其他折扣（例如其他券导致 price 低于原始价格）
            $original_amount = bcmul((string)$item['price'], (string)$item['num'], 2);
            $other_discount = bcsub($original_amount, $item_amount, 2);

            if ($i < count($items) - 1) {
                // 前n-1个商品按比例分摊优惠券优惠
                $ratio = $total_amount !== '0.00' ? bcdiv($item_amount, $total_amount, 4) : '0.00'; // 防止除零
                $coupon_discount = bcmul($ratio, $total_discount, 2);
            } else {
                // 最后一个商品承担剩余优惠券优惠，处理除不尽问题
                $coupon_discount = $remaining_discount;
            }

            // 确保优惠券优惠金额不超过商品金额
            if (bccomp($coupon_discount, $item_amount, 2) > 0) {
                $coupon_discount = $item_amount;
            }

            // 计算总折扣（优惠券折扣 + 其他折扣）
            $item_discount = bcadd($coupon_discount, $other_discount, 2);

            // 计算实际支付金额
            $item_final_amount = bcsub($item_amount, $coupon_discount, 2);

            // 计算实际单价：实际支付金额 / 数量
            $item_real_price = $item['num'] > 0 ? bcdiv($item_final_amount, (string)$item['num'], 2) : '0.00';
 
            // 构造商品数据
            $new_items[] = [
                'product_id' => $item['product_id'],
                'title' => $item['title'],
                'image' => $item['image'],
                'ori_price' => (string)$item['price'], // 原始单价
                'real_price' => $item_real_price, // 优惠后单价
                'num' => $item['num'], // 数量
                'spec' => $item['spec'], // 规格
                'attr' => $item['attr'], // 属性 
                'ori_amount' => $item_amount, // 原始总价
                'real_amount' => $item_final_amount, // 优惠后总价
                'discount_amount' => $item_discount // 总折扣金额（优惠券+其他折扣）
            ];

            // 更新剩余优惠券优惠金额
            $remaining_discount = bcsub($remaining_discount, $coupon_discount, 2);
        }

        return $new_items;
    }
}
