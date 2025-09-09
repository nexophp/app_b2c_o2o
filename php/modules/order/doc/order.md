# 订单模块文档
 
## 数据库表结构

### 订单主表 (order)
- 存储订单基本信息
- 包含订单状态、金额、地址等信息
- **重要字段**：
  - `can_refund_amount`: 可退款金额
  - `has_refund_amount`: 已退款金额
  - `real_get_amount`: 实际获得金额

### 订单明细表 (order_item)
- 存储订单商品详情
- 关联订单主表

### 订单支付信息表 (order_paid_info)
- 存储订单支付相关信息
- 如优惠券、折扣等

### 订单退款表 (order_refund)
- 存储退款申请信息
- 包含退款原因、状态等

### 订单退款明细表 (order_refund_detail)
- 存储退款商品详情
- 关联退款表和订单明细表

## 使用示例

### 创建订单

```php
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
 
```

### 订单支付信息管理

```php
// 添加支付信息
$payment = new \modules\order\lib\OrderPayment();
$result = $payment->addPaymentInfo([
    'order_id' => 1,
    'title' => lang('优惠券抵扣'),
    'type' => 'coupon',
    'amount' => 10.00
]);

// 批量添加支付信息
$result = $payment->batchAddPaymentInfo(1, [
    [
        'title' => lang('优惠券抵扣'),
        'type' => 'coupon',
        'amount' => 10.00
    ],
    [
        'title' => lang('积分抵扣'),
        'type' => 'points',
        'amount' => 5.00
    ]
]);

// 获取订单支付信息 - 使用正确的查询方法
$result = $payment->getOrderPaymentInfo(1);
// 内部使用: $this->orderPaidInfoModel->find(['order_id' => $orderId])
```

### 订单退款处理

```php
// 创建退款申请 - 包含完整的金额验证
$refund = new \modules\order\lib\OrderRefund();
$result = $refund->createRefund([
    'user_id' => 1,
    'order_id' => 1,
    'amount' => 50.00,  // 会自动验证是否超过可退款金额
    'reason' => lang('商品质量问题'),
    'desc' => lang('商品有瑕疵'),
    'images' => ['image1.jpg', 'image2.jpg'],
    'items' => [
        ['order_item_id' => 1, 'num' => 1]
    ]
]);

// 审核退款申请
$result = $refund->approveRefund(1, 'approved', lang('审核通过'));

// 获取退款列表 - 使用正确的分页方法
$result = $refund->getRefundList([
    'user_id' => 1,
    'status' => 'wait'
], 1, 20); 

// 获取退款详情 - 使用正确的关联查询
$result = $refund->getRefundDetail(1);
// 内部使用: $this->orderRefundModel->relation(['order', 'refund_details'])->find($refundId)
```
 
 
## 退款金额验证逻辑

退款时会进行完整的金额验证：

```php
// 检查可退款金额
$canRefundAmount = $order['can_refund_amount'] ?? ($order['real_get_amount'] - $order['has_refund_amount']);
if ($refundData['amount'] > $canRefundAmount) {
    throw new \Exception(lang('退款金额不能超过可退款金额'));
}
```
    