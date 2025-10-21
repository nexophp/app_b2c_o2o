# 入门

## 安装 

- config.ini.php 配置

    修改`config.ini.dist.php`为`config.ini.php`,并正确配置其中`mysql`  `redis`信息.

    缓存依赖`redis`

- 执行sql/init.sql

    复制 `init.sql`  并执行

- 设置 `public` 为站点根目录

    配置Nginx重写 

    ~~~
    location / {
        if (!-e $request_filename){
            rewrite ^(.*)$ /index.php last;
        }
    }
    ~~~

- 777目录设置

    ~~~
    mkdir runtime 
    chmod -R 777 runtime
    mkdir public/uploads
    chmod -R 777 public/uploads 
    ~~~
  
网站即可正常访问。

    /admin 后台网址

## 目录说明 

- modules 官方内置应用模块
- app     用户自行开发的模块 

支持用户发布软件至`composer`  

代码结构 https://github.com/nexophp/demo_module

## 命名规则 

建议函数名以小写`_`组合，如 `get_ip()`

类名如 `SiteController` ，类中方法名以 `action` 开头，如 `actionIndex` 

除了控制器，其他类名并不强制。

## AUTOLOADER

自动加载

~~~
global $autoload;
$autoload->addPsr4('yourname\\', PATH . '/yourname/');
~~~

## 多语言

默认系统开启了多语言功能，首次访问将根据浏览器来加载对应的语言。

语言包`lang/zh-cn/app.php` ，语言包的目录结构如下所示

~~~
lang/
├── zh-cn/
│   ├── app.php
│   ├── admin.php
│   └── ...
├── en-us/
│   ├── app.php
│   ├── admin.php
│   └── ...
└── ...
~~~

调用翻译

~~~
lang('hello',$name = 'app');
~~~

其中`$name` 为语言包文件，如`app.php`。默认可不传`$name`

## 安装官方提供的包

使用 composer 安装官方模块

以`admin`为例

~~~
composer require nexophp/admin --ignore-platform-reqs
~~~

替换`admin`模块中的功能，可以在`app`目录下创建`controller` `view`来直接替换功能。

admin所在目录为 `vendor/nexophp/admin`


## 开发项目

如果是仅限公司内部使用，不打算开源的，直接在  `app` 下创建项目名称

~~~
app
    ├── your_project_name 项目名称
        ├── controller  控制器
        ├── data        数据处理
        ├── lib         类库
        ├── view        视图
        ├── ...
~~~

以控制器 `app\your_project_name\controller\SiteController` 为例

~~~
namespace app\your_project_name\controller;

class SiteController extends \core\AdminController
{
    /**
    * 访问 /your_project_name
    */
    public function actionIndex()
    {
        //这里什么都不写会自动渲染 view/site/index.php
    }
    /**
    * 访问 /your_project_name/site/demo-data
    */
    public function actionDemoData()
    {
        //这里什么都不写会自动渲染 view/site/demo-data.php
        //方法名采用驼峰命名法，url地址以-连接
    }
}
~~~



内置控制器

~~~
core\AppController    基础控制器
core\ApiController    接口
core\AdminController  管理员
~~~

## 控制器



~~~
<?php

namespace app\admin\controller;

use Route;

class SiteController extends \core\AppController
{
    //是否需要登录
    protected $need_login = true; 

    public function actionIndex()
    { 
        return view('index');
    }

    public function actionTestA()
    { 
        pr(Route::getActions());
        echo Route::url("/admin/site/test-a");
        
    }
}

~~~

`actionIndex` 对应的URL是 `/admin/site/index`

`actionTestA` 对应的URL是 `/admin/site/test-a` 


~~~
view('index')
~~~

将渲染 `app/admin/views/index.php` 文件


自定义路由到控制器

~~~
Route::all('/', function(){
   return Route::runController('app\site\controller\siteController', 'actionIndex');
});
~~~


### AdminController

~~~ 
namespace modules\your_project_name\controller;

class DemoController extends \core\AdminController
{
    /**
     * 商品列表页面
     * @permission 产品.管理 产品.查看
     */
    public function actionIndex() {}
    
    /**
     * 商品分页列表
     * @permission 产品.管理 产品.编辑
     */
     public function actionUpdate() {

     }

    /**
     * 商品分页列表
     * @permission 产品.管理 产品.查看
     */
    public function actionTree()
    {
        $where = ['ORDER' => ['sort' => 'ASC', 'id' => 'ASC']];
        $id = $this->post_data['id'] ?? 0;
        if ($id) {
            $where['id[!]'] = $id;
        }
        $list = db_get('product_type', ['id', 'title', 'pid'], $where);
        if (!$list) {
            json_success(['data' => '']);
        }
        $tree = array_to_tree($list, 'id', 'pid', 'children');
        $tree = array_values($tree);
        json_success(['data' => $tree]);
    }
}
~~~




### ApiController

使用接口不需要权限

~~~
namespace app\your_project_name\controller;

class ApiController extends \core\ApiController
{
    protected $need_login = false;
    public function actionIndex()
    {
        json_success(['msg'=>lang('访问成功')]);
    }
}
~~~


## 开源包

~~~
modules
    ├── your_project_name 项目名称
        ├── controller  控制器
        ├── data        数据处理
        ├── lib         类库
        ├── view        视图
        ├── ...
~~~

## 数据库 

配置数据库

修改 `config.dist.php` 为 `config.php`

~~~
/**
 * 数据库配置
 */
$config['db_host'] = '127.0.0.1';
$config['db_user'] = 'root';
$config['db_pwd']  = '111111';
$config['db_name'] = 'nexo';
$config['db_port'] = '3306'; 
~~~

基于 https://medoo.in/api/where

分页

~~~ 
db_pager('table_name','*',[
    'ORDER'=>['id'=>'DESC'], 
]);
~~~

查寻多条记录

~~~
db_get('table_name','*',[
    'ORDER'=>['id'=>'DESC'],
    'LIMIT'=>10,
]);
~~~

写入

~~~
db_insert('table_name',$data = [
    'title'=>'测试',
    'content'=>'测试内容',
]);
~~~

更新

~~~
db_update('table_name',$data = [
    'title'=>'测试2',
    'content'=>'测试内容2',
],$where = [
    'id'=>1,
]);
~~~

删除

~~~
db_delete('table_name',$where = [
    'id'=>1,
]);
~~~

事务

 
~~~
db_action(function()use($data){
    db_insert('table_name',$data);
});
~~~

id 锁

~~~
db_for_update($table,$id)
~~~



## 缓存

依赖 `redis`

~~~
cache('key','value',$expire = 3600);
cache('key');
cache_delete('key');
~~~

## Model

数据库字段不要使用mysql关键字，否则会有问题。
注意： `GROUP` `ORDER` `DESC` `ASC` 需大写

model中查寻请使用 `find()`  `pager()` 方法  

~~~
$where = [];
findOne($where)  查找一条记录
find($where,1)  同findOne
find($where)  查找多条记录
pager($where)  分页
~~~

返回的数据支持对象或数组形式访问，默认不会加载关联数据，具体查看下面的关联部分。

控制器中使用模型

~~~
namespace app\your_project_name\controller;

class SiteController extends \core\AdminController
{
    protected $model = '\modules\your_project_name\model\DemoModel';
    public function actionIndex()
    {
        $list = $this->model->find();
    }
}
~~~

多个model

~~~
namespace app\your_project_name\controller;

class SiteController extends \core\AdminController
{
    protected $model = [
        'demo' => '\modules\your_project_name\model\DemoModel',
    ];
    public function actionIndex()
    {
        $list = $this->model->demo->find();
    }
}
~~~

查寻

~~~
//多条记录
find(['title[~]'=>'test']); 
//返回一条记录 $id是int类型
find($id) 
//返回一条记录
find(['name'=>'t'],$limit=1)  
//分页
pager($where = []) 
//sum 
sum($filed,$where = [])
//count 
count($where = []) 
//删除
del($where = [])
//max
max($filed,$where = [])
//min 
$model->min($filed,$where = []) 
~~~

`$where` 

~~~
'user_name[REGEXP]' => '[a-z0-9]*'
'user_name[FIND_IN_SET]'=>(string)10
'user_name[RAW]' => '[a-z0-9]*'
"age[+]" => 1
//like查寻
'product_num[~]' => 379, 
//等于查寻
'product_num' => 3669, 
//大于查寻
'id[>]' => 1,
'id[>=]' => 1,
'id[<]' => 1,
'id[<=]' => 1,
~~~


~~~
[
    'ORDER'=>['id'=>'DESC'],
    'GROUP'=>'title'
    //多字段
    'GROUP'=>['status','title']
]
~~~

OR 

~~~
$where = []; 
$where['OR'] = [
	'product_num[~]'=>379,
	'product_num[>]'=>366,
];
$where['LIMIT'] = 10;
$where['ORDER'] = ['id'=>'DESC']; 
~~~



~~~
//(...  AND ...) OR (...  AND ...)
"OR #1" => [
    "AND #2" => $where,
    "AND #3" => $or_where
    ]
];
//(... OR  ...) AND (...  OR ...)
"AND #1" => [
    "OR #2" => $where,
    "OR #3" => $or_where
    ]
];
~~~

写入

~~~
insert($data)
update($data,$where = [])
~~~

验证

~~~
protected $field = [
    'name'  => '姓名',
    'phone' => '手机号',
    'email' => '邮件',
];

protected $validate = [
    'required'=>[
        'name','phone','email',
    ],
    'email'=>[
        ['email'],
    ],
    'phonech'=>[
        ['phone']
    ],
    'unique'=>[
        ['phone',],
        ['email',], 
    ]
]; 

protected $unique_message = [
    '手机号已存在',
    '邮件已存在',
];

~~~


关联 

~~~

protected $has_one = [
    'type' => [ProductTypeModel::class, 'type_id'],
];

protected $has_many = [
    'product_info' => [ProductInfoModel::class, 'product_id', 'id', ['LIMIT' => 2]],
    'product_attr' => [ProductAttrModel::class, 'product_id', 'product_id', ['LIMIT' => 2]],
];

~~~

关联查寻

~~~
$model = ProductModel::model()->find();
$model->type;
$model->product_attr;
$model->product_info;
~~~

设置字段值

~~~
public function afterFind(&$data)
{
    parent::afterFind($data);
    //$data['new_data'] = '新的值';
} 
public function getAttrDemoTest()
{
    return 'ss';
}

public function getAttrTitle()
{
    return $this->data['title'] . ' - ' . 'Default Title';
}
~~~

可直接用 `$model->title` 或 `$model['title']` 

model 事件
~~~
/**
* 查寻前
*/
public function beforeFind(&$where){
}
/**
* 查寻后
*/
public function afterFind(&$data){
}

/**
* 写入数据前
*/
public function beforeInsert(&$data){
}
/**
* 写入数据后
*/
public function afterInsert($id){
}

/**
* 更新数据前
*/
public function beforeUpdate(&$data,$where){
}
/**
* 更新数据后
*/
public function afterUpdate($row_count,$data,$where){
}
/**
* 删除前
*/
public function beforeDelete(&$where)
{        
}
/**
* 删除后
*/
public function afterDelete($where)
{        
}
~~~

## 生成订单号

~~~
create_order_num();
~~~

## CRUL 

GET

~~~
curl_get($url, $params = [], $click_option = [])
~~~


POST 

~~~
curl_post($url, $params = [], $click_option = [])
~~~
 

PUT

~~~
curl_put($upload_url, $local_file, $timeout = 300)
~~~

$local_file 是本地文件路径，$upload_url 是上传到的地址。

## 请求参数


GET 

~~~
get_query($key)
~~~

POST

~~~
get_post($key)
~~~

INPUT

~~~
get_input($key)
~~~

get post input

~~~
g($key)
~~~

其中 `$key` 可不传


## 设置首页路由

~~~
set_config('home_class','app/site/controller/siteController');
~~~

模块中应使用 

~~~
add_to_home("默认站点","app/site/controller/DemoController");
~~~


## 网站备案信息输出

~~~
<?= \modules\admin\lib\Beian::output();?>
~~~

## 官方源码

直接查看 `vendor/nexophp` 目录下的代码，但不要修改 vendor下的代码。
 
## phpdoc 生成文档 

https://phpdoc.org/

生成文档

~~~
php phpDocumentor.phar run -d  /绝对路径/vendor/nexophp -t  ./phpdoc
~~~

将在当前目录下生成 `phpdoc` 目录，打开 `phpdoc/index.html` 即可查看。

> 内置大量函数，建议使用phpdoc查看。

