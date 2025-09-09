# 微信支付 API 文档

## 概述

微信支付模块提供了完整的微信支付解决方案，支持多种支付方式和场景，包括：

- H5支付：适用于移动端网页支付
- Native支付：适用于PC网站扫码支付
- JSAPI支付：适用于微信内网页支付
- POS扫码支付：适用于线下收银场景
- 退款功能：支持全额和部分退款
- 企业付款到零钱：向用户转账

## 配置要求

在使用微信支付功能前，需要在系统设置中配置以下参数：

- `payment_config_weixin_mch_id`：微信商户号
- `payment_config_weixin_key_path`：商户私钥文件路径 (apiclient_key.pem)
- `payment_config_weixin_cert_path`：商户证书文件路径 (apiclient_cert.pem)
- `payment_config_weixin_secret_key`：API密钥 V3
- `payment_config_weixin_v2_secret_key`：API密钥 V2
- `payment_config_weixin_platform_cert`：微信支付平台证书路径
- `minapp_app_id`：微信小程序/公众号 AppID

## API 接口列表

### 1. 支付回调通知

**接口地址：** `POST /payment/api-weixin/notify`

**描述：** 接收微信支付异步通知，处理支付结果

**请求参数：** 微信支付回调数据（XML格式）

**响应：** 返回 "SUCCESS" 或 "FAIL"

### 2. 查询支付订单

**接口地址：** `POST /payment/api-weixin/query`

**描述：** 根据商户订单号查询微信支付订单状态

**请求参数：**
```json
{
    "out_trade_no": "202312010001"  // 商户订单号
}
```

**响应示例：**
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "trade_state": "SUCCESS",
        "transaction_id": "4200001234567890123",
        "out_trade_no": "202312010001",
        "amount": {
            "total": 100,
            "currency": "CNY"
        },
        "payer": {
            "openid": "oUpF8uMuAJO_M2pxb1Q9zNjWeS6o"
        }
    }
}
```

### 3. H5支付下单

**接口地址：** `POST /payment/api-weixin/h5`

**描述：** 创建H5支付订单，适用于移动端网页支付

**请求参数：**
```json
{
    "body": "测试商品",           // 商品描述
    "total_fee": 1.00,          // 支付金额(元)
    "order_num": "202312010001"  // 商户订单号
}
```

**响应示例：**
```json
{
    "code": 0,
    "msg": "success",
    "data": "https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx123456789&package=WAP"
}
```

### 4. Native支付下单

**接口地址：** `POST /payment/api-weixin/native`

**描述：** 创建Native支付订单，返回二维码链接，适用于PC网站扫码支付

**请求参数：**
```json
{
    "body": "测试商品",           // 商品描述
    "total_fee": 1.00,          // 支付金额(元)
    "order_num": "202312010001"  // 商户订单号
}
```

**响应示例：**
```json
{
    "code": 0,
    "msg": "success",
    "data": "weixin://wxpay/bizpayurl?pr=abc123"
}
```

### 5. JSAPI支付下单

**接口地址：** `POST /payment/api-weixin/jsapi`

**描述：** 创建JSAPI支付订单，适用于微信内网页支付

**请求参数：**
```json
{
    "body": "测试商品",                        // 商品描述
    "total_fee": 1.00,                       // 支付金额(元)
    "order_num": "202312010001",              // 商户订单号
    "openid": "oUpF8uMuAJO_M2pxb1Q9zNjWeS6o"  // 用户openid
}
```

**响应示例：**
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "appId": "wx1234567890123456",
        "timeStamp": "1640995200",
        "nonceStr": "abc123",
        "package": "prepay_id=wx123456789",
        "signType": "RSA",
        "paySign": "signature_string"
    }
}
```

### 6. 申请退款

**接口地址：** `POST /payment/api-weixin/refund`

**描述：** 对已支付的订单申请退款

**请求参数：**
```json
{
    "out_trade_no": "202312010001",    // 商户订单号
    "total_fee": 10.00,              // 订单总金额(元)
    "refund_amount": 5.00,           // 退款金额(元)
    "refund_desc": "用户申请退款"      // 退款原因（可选）
}
```

**响应示例：**
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "refund_id": "50000000382019052709732678859",
        "out_refund_no": "T202312010001",
        "status": "PROCESSING"
    }
}
```

### 7. 查询退款

**接口地址：** `POST /payment/api-weixin/refund-query`

**描述：** 根据商户退款单号查询退款状态

**请求参数：**
```json
{
    "out_refund_no": "T202312010001"  // 商户退款单号
}
```

**响应示例：**
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "status": "SUCCESS",
        "refund_id": "50000000382019052709732678859",
        "out_refund_no": "T202312010001",
        "success_time": "2023-12-01 15:30:00"
    }
}
```

### 8. POS扫码支付

**接口地址：** `POST /payment/api-weixin/pos`

**描述：** 扫描用户付款码进行支付，适用于线下收银场景

**请求参数：**
```json
{
    "order_num": "202312010001",           // 商户订单号
    "total_fee": 1.00,                    // 支付金额(元)
    "auth_code": "134567890123456789",     // 用户付款码
    "body": "测试商品",                    // 商品描述
    "device_info": "sn2024"               // 设备号（可选）
}
```

**响应示例：**
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "transaction_id": "4200001234567890123",
        "return_code": "SUCCESS"
    }
}
```

### 9. 企业付款到零钱

**接口地址：** `POST /payment/api-weixin/transfer`

**描述：** 向用户微信零钱转账

**请求参数：**
```json
{
    "openid": "oUpF8uMuAJO_M2pxb1Q9zNjWeS6o",  // 用户openid
    "order_num": "T202312010001",             // 商户订单号
    "amount": 1.00,                          // 转账金额(元)
    "desc": "转账"                           // 转账描述（可选）
}
```

**响应示例：**
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "return_code": "SUCCESS",
        "payment_no": "1000018301201407033233368018"
    }
}
```

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 250 | 支付失败或业务错误 |
| 400 | 参数错误 |
| 500 | 系统错误 |

## 支付流程说明

### H5支付流程
1. 前端调用 `/payment/api-weixin/h5` 接口创建支付订单
2. 获取支付链接，跳转到微信支付页面
3. 用户完成支付
4. 微信异步通知 `/payment/api-weixin/notify`
5. 系统处理支付结果，触发 `payment_success` 事件

### Native支付流程
1. 前端调用 `/payment/api-weixin/native` 接口创建支付订单
2. 获取二维码链接，生成二维码供用户扫描
3. 用户扫码支付
4. 微信异步通知 `/payment/api-weixin/notify`
5. 系统处理支付结果，触发 `payment_success` 事件

### JSAPI支付流程
1. 前端调用 `/payment/api-weixin/jsapi` 接口创建支付订单
2. 获取支付参数，调用微信JSAPI发起支付
3. 用户完成支付
4. 微信异步通知 `/payment/api-weixin/notify`
5. 系统处理支付结果，触发 `payment_success` 事件

## 事件说明

### payment_success 事件

当支付成功时，系统会触发 `payment_success` 事件，传递以下数据：

```php
$data = [
    'order_num' => $out_trade_no,           // 商户订单号
    'transaction_id' => $transaction_id,     // 微信支付订单号
    'payment_method' => 'weixin',           // 支付方式
    'currency' => 'CNY',                    // 货币类型
    'amount' => 1.00,                       // 支付金额
    'openid' => $openid,                    // 用户openid
    'paid_at' => $timestamp,                // 支付时间戳
    'app_id' => $app_id,                    // 应用ID
    'type' => 'weixin'                      // 支付类型
];
```

可以通过 `add_action('payment_success', $callback)` 监听此事件。

## 注意事项

1. **证书配置**：确保微信支付证书文件路径正确且文件可读
2. **回调地址**：确保回调地址可以被微信服务器访问
3. **金额单位**：API接口中金额单位为元，内部会自动转换为分
4. **订单号唯一性**：确保商户订单号在系统中唯一
5. **安全性**：生产环境中请使用HTTPS协议
6. **日志记录**：系统会自动记录支付相关日志，便于问题排查

## 开发示例

### 前端调用示例（JavaScript）

```javascript
// H5支付
fetch('/payment/api-weixin/h5', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        body: '测试商品',
        total_fee: 1.00,
        order_num: '202312010001'
    })
})
.then(response => response.json())
.then(data => {
    if (data.code === 0) {
        // 跳转到支付页面
        window.location.href = data.data;
    }
});

// JSAPI支付
fetch('/payment/api-weixin/jsapi', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        body: '测试商品',
        total_fee: 1.00,
        order_num: '202312010001',
        openid: 'user_openid'
    })
})
.then(response => response.json())
.then(data => {
    if (data.code === 0) {
        // 调用微信支付
        wx.chooseWXPay({
            ...data.data,
            success: function(res) {
                // 支付成功
            }
        });
    }
});
```

### 后端监听支付成功事件（PHP）

```php
// 监听支付成功事件
add_action('payment_success', function($data) {
    if ($data['payment_method'] === 'weixin') {
        // 处理微信支付成功逻辑
        $order_num = $data['order_num'];
        $amount = $data['amount'];
        $transaction_id = $data['transaction_id'];
        
        // 更新订单状态
        // 发送通知
        // 其他业务逻辑
    }
});
```