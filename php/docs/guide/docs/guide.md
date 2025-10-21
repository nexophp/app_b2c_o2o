# 指南
 
## 登录 

~~~
//登录
$user = get_user($uid);
$data = [];
$data['token'] = add_user_login_his($uid);
$data['phone'] = $user['phone'];
$data['user_id'] = $uid;
unset($data['password']);
json_success([
    'data' => $data
]);
~~~
 

## 判断是否有权限 

~~~
has_access($str)
~~~

同 `if_access($str)`

判断是否有权限，user表id为1是超管，将会跳过权限检查。

`$str`是url，如 `admin/site/index`

当不希望判断权限时需在控制器加

~~~
/**
* 请求前，什么都不写则不检查权限
*/
public function before(){
    
}
~~~

## 添加菜单

~~~

use core\Menu;

// 设置分组（可选，默认为'admin'）
Menu::setGroup('admin');

// 添加顶级菜单
Menu::add('system', '系统管理', '', 'bi-gear', 50);

// 添加子菜单（使用'system'作为parent_name，而不是$topId）
Menu::add('module', '模块', '/admin/module', '', 100, 'system');
Menu::add('setting', '设置', '/admin/setting', '', 50, 'system');
Menu::add('user', '用户管理', '/admin/user', '', 30, 'system');
Menu::add('role', '角色管理', '/admin/role', '', 20, 'system');
 
~~~

## vue
 

vue 2
~~~
$vue =  new Vue; 
~~~

### index

~~~
<el-table-column type="index" label="序号" :index="indexMethod" width="80">
</el-table-column>
~~~

### 时间区间

~~~
$vue->search_date = [
  '今天',
  '昨天',
  '本周',
  '上周',
  '上上周',
  '本月',
  '上月',
  '上上月',
  '本年'=>'今年', 
  '上年'=>'去年',
  '上上年',
  '最近一个月',
  '最近两个月',
  
  '最近三个月',
  '第一季度', 
  '第二季度', 
  '第三季度', 
  '第四季度', 
];
//限制在这个时间之前的无法选择
$vue->start_date = '2023-11-01';

$vue->addDate();

~~~
  
date-picker

~~~
<el-date-picker   v-model="where.date" value-format="yyyy-MM-dd" :picker-options="pickerOptions" size="medium" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期">
</el-date-picker>
~~~
 
### data 
~~~
$vue->data('text','welcome');
~~~

### created

~~~ 
$vue->created(['load()']);
$vue->method('load()',"

");

~~~

### mounted 
~~~
$vue->mounted("name1","
  alert(2);
")
~~~
其中`name1`是`key`,用于生成唯一的js

### watch

~~~
$vue->watch("page(new_val,old_val)","
  console.log('watch');
  console.log(old_val);
  console.log(new_val);
")
~~~

~~~ 
$vue->watch("where.per_page","
  handler(new_val,old_val){
    console.log('watch');
    console.log(old_val);
    console.log(new_val);
  },  
"); 
~~~

~~~
$vue->watch("where","
  handler(new_val,old_val){
    console.log('watch');
    console.log(old_val.per_page);
    console.log(new_val.per_page);
  }, 
  deep: true
");
~~~

 

##  wangeditor 富文本

如`body`字段

在html中
~~~
<?=$vue->editor($field)?>
~~~

设置编辑器值

~~~
app.update_editor('$field', '演示内容');   
~~~
 
## 压缩JS

安装 

~~~
yarn add --dev javascript-obfuscator
~~~

配置
~~~
$config['vue_encodejs'] = true;
$config['vue_ignore'] = ['文件'];
~~~

 

### 一般函数

每个季度开始、结束时间
~~~
vue_get_jidu_array($year)
~~~

某月的最后一天
~~~
vue_get_last_day($month = '2023-07')
~~~
 

## vue导入文件

~~~
$import = $vue->get_import([
    'js'=>" alert('导入成功');";
    'upload_url'=>'/sys/upload/one',
    'parse_url'=>'/product_quality/goods/import_parse',
    'save_url'=>'/product_quality/goods/import_parse_save',
    'label'=>'导入xls',
    'table_body'=>'
    <el-table-column   prop="desc"  label="产品名称" width=""></el-table-column>
    <el-table-column   prop="reg_num"  label="状态" width="">
         <template slot-scope="scope">
            <span v-if="scope.row.is_err">
              <div v-html="scope.row.err" style="color:red;font-size:12px;"></div>
            </span>
            <span v-else style="color:green;font-size:12px;">
                可导入
            </span>
         </template>
    </el-table-column>
    '
]); 
~~~

返回 `html` `pop_html`

~~~
<?php 
$html = $import['html'];
?>
<?=$import['pop_html']?>
~~~


接口处理
import_parse
~~~
  $url = $this->input['url'];
  $file = PATH.$url;
  if(!file_exists($file)){
    return json_error(['msg'=>'操作异常']);
  }
  $all = \lib\Xls::load($file,[ 
        '产品名称'   => 'desc',
        '规格型号'   => 'spec',
        '批号'       => 'product_ph',
        '生产日期'   => 'produce_date',
        '失效日期'   => 'invalid_date',
        '唯一码'     => 'uuid',
        '注册证号'     => 'reg_num',
  ]);
  foreach($all as $k=>$v){ 
  }
  if(!$all){
    return json_error(['msg'=>'导入的文件数据异常']);
  } 
  foreach($all as $k=>&$v){
  
  if($res){
    $err[] = "唯一码已存在";
  }
  $v['is_err'] = false;
  if($err){
    $v['err'] = implode("<br>",$err);
    $v['is_err'] = true;
  }
}
if($all){
  $all = array_values($all);
}
return json_success(['data'=>$all]); 
~~~
 


## 分页、表格、搜索

搜索 

~~~
<?php 
$url = '/site/welcome/index';
echo element("filter",[ 
    'data'=>'list',
    'url'=>$url,
    'is_page'=>true,
    'init'=>true,
    [
        'type'=>'input','name'=>'title',
        'attr_element'=>[
            'placeholder'=>'名称',
        ],
    ],
]); 
?>
~~~

其中`data`为`list`,将自动触发 `load_list()` 


表格

~~~
<?php 
echo element('table',[
    ['name'=>'open',':data'=>'list',':height'=>'height'],
    ['name'=>'column','prop'=>'title','label'=>'名称','width'=>''],
    ['name'=>'column','prop'=>'count','label'=>'成员数','width'=>''],
    ['name'=>'column','prop'=>'count','label'=>'操作','width'=>'100',
      'tpl'=>[
          ['name'=>'button','label'=>'成员','@click'=>'show_user(scope.row)'],
          ['name'=>'button','label'=>'编辑','@click'=>'edit(scope.row)','style'=>'margin-left: 20px;'],
       ]
    ],
    ['name'=>'close'],
]);
?> 
~~~

点击表格展开后显示表格或HTML

~~~
<?php 
echo element('table', [
    ['name' => 'open',':data' => 'load_list',':height' => 'height','default-expand-all'],
    ['name' => 'column','prop' => 'order_num','label' => '',
     'type' => 'expand',
     'tpl'=> [
       [ 
            "type"=>'html', 
            "html"=>element('table', [
                ['name' => 'open',':data' => 'scope.row.detail'],
                ['name' => 'column','prop' => 'sale_order_num','label' => '销售单号','width' => '200'],
                ['name' => 'column','prop' => 'customer_name','label' => '客户','width' => ''],
                ['name' => 'column','prop' => 'product_name','label' => '产品名称','width' => '200'],
                ['name' => 'column','prop' => 'product_num','label' => '产品编号','width' => '200', 
                    "tpl"=>[
                        [
                            'type'=>'html',
                            "html"=>"
                               <span :style='\"background:\"+scope.row.product_num_color'> {{scope.row.product_num}}</span>
                            "
                        ]
                    ]
                ],
                ['name' => 'column','prop' => 'product_ph','label' => '批号','width' => '200'],
                ['name' => 'column','prop' => 'product_unit','label' => '单位','width' => '80'], 
                ['name' => 'close'],
            ]) 
        ]
     ]
    ],
    ['name' => 'column','prop' => 'order_num','label' => '入库单号','width' => '200'],
    ['name' => 'column','prop' => 'num','label' => '数量','width' => ''],
    ['name' => 'close'],
]);
?>

~~~

其中`tpl`也支持直接写`type`

~~~
"tpl"=>[ 
    //'type'=>'html', //此行可以没有
    "html"=>"
       <span :style='\"background:\"+scope.row.product_num_color'> {{scope.row.product_num}}</span>
    " 
]
~~~

## vue element 分页

~~~
<?php 
echo element("pager", [
    'data' => 'list',
    'per_page' => get_config('per_page'), 
    'url' => $url,
    'reload_data' => []
]);
?> 
~~~
 

vue3 

本系统默认使用vue2

~~~ 
\element\table::$scope = '#default';
~~~

表单

~~~
element\form::$model = 'form';
echo element('form',[ 
    ['type'=>'open','model'=>'form','label-width'=>'180px'],
    [
        'type'=>'input','name'=>'title','label'=>'标题',
        'attr'=>['required'], 
        'attr_element'=>['placeholder'=>'演示标题'],
    ],
    ['type'=>'close']
]);
~~~


## 演示element ui表单

~~~
<?php view_header(lang('小程序管理'))?>

<el-form ref="form"  label-width="180px" label-position='top'>

<div>
    <div>单图：</div>
    <?php vue_upload_image('a')?>
</div>
<div class="mt-2">
    <div>多图：</div>
    <?php vue_upload_images('b')?>
</div>
<div class="mt-2">
    <el-button @click="save" type='primary'>在console中查看数据</el-button>
</div>




<?php 
element\form::$model = 'form';
global $vue; 
$vue->created(['load()']);  
$vue->method('load()',"
    app.update_editor('editor_1', '演示内容');   
");
?>
demo_data
<?=$vue->editor('demo_data')?>

<?php 
echo element('form',[ 
    ['type'=>'open','model'=>'form',],
    [
        'type'=>'editor','name'=>'editor_1','label'=>'WangEditor 5', 
    ],
    [
        'type'=>'editor','name'=>'editor_2','label'=>'WangEditor 5', 
    ],
    [
        'type'=>'input','name'=>'title','label'=>'标题',
        'attr'=>['required'], 
        'attr_element'=>['placeholder'=>'演示标题'],
    ],
    [
        'type'=>'color','name'=>'aa31','label'=>'color', 
    ],
    [
        'type'=>'datetime','name'=>'aa32','label'=>'datetime', 
    ],
    [
        'type'=>'time','name'=>'aa33','label'=>'time', 
    ],
    [
        'type'=>'tag','name'=>'tag','label'=>'tag', 
    ],
    [
        'type'=>'upload','name'=>'file_name','label'=>'文件上传','multiple',
        'mime'=>'pdf,doc,docx,xls,xlsx,ppt,pptx', 
        'num'=>20,
    ],
    [
        'type'=>'spec','name'=>'is_spec,sku','label'=>'sku', 
        'attr'=>['image','stock','status'],        
        'js'=>"app.add_media('upload_spec');"
    ],
    [
        'type'=>'checkbox','name'=>'checkbox','label'=>'多选',
        'value'=>[['label'=>'选项1','value'=>1],['label'=>'选项2','value'=>2],], 
    ],
    [
        'type'=>'radio','name'=>'radio','label'=>'radio',
        'value'=>[['label'=>'选项1','value'=>1],['label'=>'选项2','value'=>2],], 
    ],
    
    [
        'type'=>'text','name'=>'text','label'=>'text', 
        'attr'=>['required',],
        'attr_element'=>[':rows'=>10],
    ],

    [
        'type'=>'editor','name'=>'editor','label'=>'editor', 
    ],

    

    [
        'type'=>'attribute','name'=>'attribute','label'=>'attribute', 
        'value'=>[ ['label'=>'选项1','value'=>1],['label'=>'选项2','value'=>2],],  
    ],

    [
        'type'=>'select','name'=>'select1','label'=>'select单选', 
        'value'=>[ ['label'=>'选项1','value'=>1],['label'=>'选项2','value'=>2],],  
    ],
    [
        'type'=>'select','name'=>'select2','label'=>'select多选', 
        'value'=>[ ['label'=>'选项1','value'=>1],['label'=>'选项2','value'=>2],], 
        'attr_element'=>['multiple'],
    ],
    [
        'type'=>'date','name'=>'date1','label'=>'时间', 
        'attr'=>['title'=>''], 
        'attr_element'=>[':picker-options'=>'pickerOptions','align'=>"center"],
    ],
    [
        'type'=>'autocomplete','name'=>'title_auto','label'=>'autocomplete', 
        'url'=>'/demo_module/site/autocomplete',  
    ],

    [
        'type'=>'cascader','name'=>'title_casc','label'=>'cascader',  
        'url'=>'/demo_module/site/cascader', 
        //'options' => 'tree_data', option url任选一个
        // :props ,checkStrictly: true
        'attr_element'=>[':props'=>"{value:'id',label:'label'}"],
    ],

    [
        'type'=>'upload','name'=>'fiel','label'=>'上传', 
        'url'=>'/admin/upload',
        'mime'=>'jpg',
        'multiple', 
    ],
    ['type'=>'close']
]);
global $vue;
$vue->formData("editor","123465");
$vue->method("save()","
console.log(this.form);
");
?>




</el-form>

<?php view_footer()?>

~~~

对应演示控制器

~~~
<?php

namespace modules\demo_module\controller;
/**
* \core\AppController
* \core\ApiController  
* \core\AdminController
*/
class SiteController extends \core\AppController
{
    public function actionIndex(){
        
    }
	/**
     * autocomplete
     * @permission 小程序.管理
     */
    public function actionAutocomplete()
    {
        $arr[] = ['id' => 1, 'value' => 'test'];
        $arr[] = ['id' => 2, 'value' => 'test22'];
        json($arr);
    }
    /**
     * cascader
     * @permission 小程序.管理
     */
    public function actionCascader()
    {
        $d = \element\form::get_city();
        json_success(['data' => $d]);
    }
}

~~~

 

## vue图片上传

~~~
<div>
    单图：
    <?php vue_upload_image('image')?>
</div>
<div>

多图：
    <?php vue_upload_images('image')?>
</div>

~~~

对应 `v-model` 为 `form.image` 

## 多文件上传组件（带数量限制）

~~~
vue_upload_files($name = 'files', $top = 'form', $mime = '', $max_files = 1)
~~~
    
## 小程序二维码

~~~
create_minapp_qr($page = 'pages/index/index', $scene = '', $env_version = '')
~~~

## QR

~~~
$qr = lib\Qr::create([
    'content' => host() . url('/mall_pc/qr/index'),  
    //'title'=>'中文',
    //'logo'=>WWW_PATH.'/misc/img/ali_pay.png',
]);
~~~

## 条形码

~~~
$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
echo $generator->getBarcode('081231723897', $generator::TYPE_CODE_128);exit;
~~~

## 分页

~~~
$vue->method("load()", "
    this.loading = true; 
    const status = this.statusTabs[this.currentTab].status;
    const params = {
        page: this.page,
        per_page: this.per_page,
        status: status
    }; 
    ajax('/order/api-refund/list', params, function(res){
        _this.list = res.data || [];

        //以下不用修改
        _this.last_page = res.last_page || 1; 
        _this.loading = false;
        _this.init_pager();
    });
");
~~~

显示分页

~~~
<?= \lib\Pager::vue(); ?>
~~~

