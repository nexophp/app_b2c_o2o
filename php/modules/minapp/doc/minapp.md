# 小程序接口

minapp

~~~
//登录
login: {
    //微信小程序openid
    openid: domain + '/minapp/api-login/openid',
    //微信小程序手机号授权登录
    weixin: domain + '/minapp/api-login/phone',
},
~~~


## 取用户信息

~~~
/minapp/api-user/info
~~~

返回值
~~~

~~~


## 设置用户信息

~~~
/minapp/api-user/set-info
~~~

参数： 

- field: 字段
- value: 值
