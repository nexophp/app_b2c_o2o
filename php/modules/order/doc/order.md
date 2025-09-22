# 订单模块文档

### 平台主动创建售后订单

~~~
do_action("order.refund.search_order", $order);
~~~


### 创建订单

~~~
// 基本使用
$orderCreator = new \modules\order\lib\OrderCreator();
// 创建订单
$orderData = [
    'user_id' => 1,
    'type' => 'product',
    'amount' => 100.00,
    'real_amount' => 95.00,
    'address' => lang('北京市朝阳区xxx'),
    'phone' => '13800138000',
    'name' => lang('张三'),
    'items' => [
        [
            'product_id' => 'P001',
            'title' => lang('商品名称'),
            'price' => 50.00,
            'num' => 2
        ]
    ]
];
$result = $orderCreator->create($orderData); 
~~~
 