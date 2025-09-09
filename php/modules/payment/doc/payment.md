# 支付

## 微信支付

`payment_success` action说明 

~~~
$data = [
    'order_num' => $out_trade_no,
    'transaction_id' => $res['transaction_id'],
    'payment_method' => 'weixin',
    'currency' => $res['amount']['currency'],
    'amount' => bcdiv($res['amount']['total'], 100, 2),
    'openid' => $res['payer']['openid'],
    'paid_at' => $success_time,
    'app_id' => $res['appid'],
    'type'=>'weixin',
]; 
add_log('微信支付查寻', $data, 'debug');
do_action('payment_success', $data);
~~~

