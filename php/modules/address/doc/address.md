# 收货地址

address

~~~
//用户地址
address: {
    //获取地址列表
    index: domain + '/address/api-user/index',
    //获取城市列表
    city: domain + '/address/api/index',
    //获取地址详情
    view: domain + '/address/api-user/view',
    //获取默认地址
    default: domain + '/address/api-user/default',
    //创建地址
    create: domain + '/address/api-user/create',
    //更新地址
    update: domain + '/address/api-user/update',
    //删除地址
    del: domain + '/address/api-user/del',
},
~~~

字段 

| 字段       | 事例值               | 描述        |
|-------------|---------------------|--------------------|
| city        | 北京市              | 城市                |
| detail      | 具体的地址          | 详细地址             |
| district    | 西城区              | 区县               |
| id          | 1                   | 地址id             |
| is_default  | 1                   | 是否默认           |
| name        | 大先生              | 收货人姓名         |
| phone       | 19900000000         | 收货人手机号       |
| province    | 北京市              | 省份               |
| region      | ["北京市", "北京市", "东城区"] | 区域     |


index 列表返回有 `full_address` 完整的收货地址