<?php

namespace modules\order\lib;

use modules\order\model\OrderModel;
use modules\order\model\OrderItemModel;

class OrderCreator
{
    private $orderModel;
    private $orderItemModel;
    //检测价格
    public $check = true;
    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
    }

    /**
     * 创建订单
     * @param array $orderData 订单数据
     * @return array 返回创建结果  id order_num
     * 
     * 参数格式示例：
     * [
     *     'user_id' => 1,
     *     'type' => 'product',
     *     'seller_id' => 1,
     *     'store_id' => 1,
     *     'amount' => 100.00,
     *     'real_amount' => 95.00,
     *     'payment_method' => 'wechat',
     *     'address_id' => 1,
     *     'address' => '北京市朝阳区xxx',
     *     'phone' => '13800138000',
     *     'name' => '张三',
     *     'coupon' => ['id'=>1,'coupon_id'=>1],
     *     'desc' => '备注信息',
     *     'items' => [
     *         [
     *             'product_id' => 'P001',
     *             'title' => '商品名称',
     *             'image' => 'image.jpg',
     *             'ori_price' => 50.00,
     *             'price' => 45.00,
     *             'num' => 2,
     *             'str_1' => '规格1',
     *             'str_2' => '规格2'
     *         ]
     *     ]
     * ]
     */
    public function create($orderData)
    {
        $items = $orderData['items'];
        if (!$items) {
            json_error(['msg' => '订单明细不能为空']);
        }
        $items_count = count($items); 
        $res =  OrderConfirm::confirm($items);
        $real_amount = $res['real_amount'];
        $amount = $res['amount'];
        $items = $res['items'];
        $orderData['real_amount'] = $real_amount;
        $orderData['amount'] = $amount;
        $orderData['items'] = $items;
        $orderMainData = $this->prepareOrderMainData($orderData); 

        $orderId = $this->orderModel->insert($orderMainData);
        if (!$orderId) {
            json_error(['msg' => lang('订单创建失败')]);
        }
        if ($items_count != count($items)) {
            json_error(['msg' => '订单明细数量错误']);
        }
        /**
         * 写入订单明细表
         */
        foreach ($items as $v) {
            $v['order_id'] = $orderId; 
            $this->orderItemModel->insert($v);
        }

        do_action('order.create', $orderId);
        return [
            'id' => $orderId,
            'order_num' => $orderMainData['order_num']
        ];
    }

    /**
     * 准备订单主表数据
     */
    private function prepareOrderMainData($orderData)
    {

        $orderNum = $this->generateOrderNumber();

        return [
            'order_num' => $orderNum,
            'sys_tag' => $orderData['sys_tag'] ?? 'product',
            'type' => $orderData['type'] ?? 'product',
            'type_1' => $orderData['type_1'] ?? null,
            'seller_id' => $orderData['seller_id'] ?? null,
            'store_id' => $orderData['store_id'] ?? null,
            'user_id' => $orderData['user_id'],
            'num' => $this->calculateTotalNum($orderData['items']),
            'amount' => $orderData['amount'],
            'real_amount' => $orderData['real_amount'],
            'desc' => $orderData['desc'] ?? '',
            'status' => $orderData['status'] ?? 'wait',
            'payment_method' => $orderData['payment_method'] ?? null,
            'address_id' => $orderData['address_id'] ?? null,
            'address' => $orderData['address'] ?? '',
            'phone' => $orderData['phone'] ?? '',
            'name' => $orderData['name'] ?? '',
            'ship_status' => $orderData['ship_status'] ?? 'none',
            'in_wallet' => $orderData['in_wallet'] ?? 0,
            'can_refund_amount' =>  $orderData['real_amount'],
            'has_refund_amount' => 0,
            'real_get_amount' => $orderData['real_amount'], // 初始值与real_amount一样
            'is_lock' => 0,
            'lock_at' => null,
            'created_at' => time(),
            'updated_at' => time()
        ];
    }

    /**
     * 创建订单明细
     */
    private function createOrderItems($orderId, $orderNum, $orderData)
    {
        global $user_id;
        $items = [];
        $old_items = $orderData['items'];
        $res = OrderConfirm::confirm($old_items);
        foreach ($res['items'] as $item) {
            $itemData = $item;
            $itemData['order_id'] = $orderId;
            $itemData['order_num'] = $orderNum;
            $itemData['order_num'] = $orderData['type'] ?? 'product';
            $itemData['status'] = $orderData['status'] ?? 'wait';
            $itemData['type_1'] = $orderData['type_1'] ?? null;
            $itemData['seller_id'] = $orderData['seller_id'] ?? null;
            $itemData['store_id'] = $orderData['store_id'] ?? null;
            $itemData['user_id'] = $orderData['user_id'] ?? null;
            $itemData['str_1'] = $item['str_1'] ?? '';
            $itemData['str_2'] = $item['str_2'] ?? '';
            $itemData['can_refund_num'] = $item['num'];
            $itemData['refund_num'] = 0;
            $itemData['can_refund_amount'] = $item['real_amount'];
            $itemData['real_get_amount'] = $item['real_amount'];
            $itemData['has_refund_amount'] = 0;
            $itemData['created_at'] = time();
            $itemData['updated_at'] = time();

            if ($item['param_1'] && is_array($item['param_1'])) {
                $itemData['param_1'] = $item['param_1'];
            }
            if ($item['param_2'] && is_array($item['param_2'])) {
                $itemData['param_2'] = $item['param_2'];
            }
            $items[] = $itemData;
            ProductStock::down($item);
        }

        // 批量插入订单明细
        $this->orderItemModel->inserts($items);
        //处理coupon
        $nid = g('coupon')['id'];
        $coupon_value = g('coupon')['amount'];
        if ($nid) {
            $coupon = db_get_one("coupon_user", "*", ['id' => $nid]);

            if ($coupon['status'] != 1) {
                json_error(['msg' => lang('优惠券已使用')]);
            }
            if ($coupon['user_id'] != $user_id) {
                json_error(['msg' => lang('优惠券不是当前用户的')]);
            }

            db_insert("order_info", [
                'order_id' => $orderId,
                'nid' => $nid,
                'type' => 'coupon',
                'value' => $coupon_value,
            ]);

            db_update("coupon_user", [
                'order_id' => $orderId,
                'status' => -1,
                'used_at' => time(),
            ], ['id' => $nid]);
        }

        $shipping = g("shipping");
        if ($shipping) {
            db_insert("order_info", [
                'order_id' => $orderId,
                'nid' => 0,
                'type' => 'shipping',
                'value' => $shipping,
            ]);
        }
    }

    /**
     * 生成订单号
     */
    private function generateOrderNumber()
    {
        return create_order_num();
    }

    /**
     * 计算商品总数量
     */
    private function calculateTotalNum($items)
    {
        $totalNum = 0;
        foreach ($items as $item) {
            $totalNum += $item['num'];
        }
        return $totalNum;
    }
}
