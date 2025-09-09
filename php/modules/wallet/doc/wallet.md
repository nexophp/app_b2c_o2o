## 钱包


### 钱包收入

添加收入

~~~
$data = [
    //用户ID
    'user_id' => 1,
    //订单金额
    'order_amount' => 100,
    //用户收入千分比  
    'rate' => 600,
    //描述
    'desc' => '测试',
    //wait 待处理 success 成功 fail 失败
    'status' => 'wait',
    //订单号，可为空
    'order_num' => '202308010001',
    //产品类型
    'type' => 'product',
];
$res = add_wallet($data);
~~~


收入入帐
仅当订单号状态为 `wait` 时有效

~~~
confirm_wallet($order_num);
~~~

### 发起提现申请

~~~
add_wallet_out([
    'user_id' => 1,
    'amount' => 10,
    'desc' => '测试',
    //提现手续费千分比
    'rate' => 6,
    'type' => 'weixin',
    'account' => ['openid' => '123456'],
]);
~~~


### 同意提现并打款

~~~
confirm_wallet_out($out_id)
~~~