# nexophp

- 安装 

~~~
composer install  --ignore-platform-reqs
~~~

- 配置重写

WEB目录指向`public`

~~~
location / {
  if (!-e $request_filename){
    rewrite ^(.*)$ /index.php last;
  }
}
~~~
 
# dev

~~~
composer update nexophp/* --ignore-platform-reqs -vvv
~~~


# 数据库config

return_reason

~~~
["商品质量问题","商品与描述不符","收到商品破损","发错商品","不喜欢\/不想要","其他原因"]
~~~

# 计划任务

退款

~~~
php cli order/refund
~~~

