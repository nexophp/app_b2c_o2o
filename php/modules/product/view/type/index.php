<?php
view_header('产品分类管理');
global $vue;
$url = '/product/type/list';

// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("form", [
    'id' => '',
    'name' => '',
    'pid' => 0,
    'image' => '',
    'description' => '',
    'sort' => 0,
    'status' => 1
]);
$vue->data("formTitle", "添加分类");
$vue->data("tree_data", []);

$vue->created(["load()",]);

$vue->method("load()", "
this.height = 'calc(100vh - ".get_config('admin_table_height')."px)';
");

$vue->method("load_tree()", "
ajax('/product/type/tree', {id:this.form.id||''}, function(res) {
    if (res.code == 0) {
        app.tree_data = res.data;
    }
});
");

$vue->method("add()", "
this.load_tree();
this.form = {
    id: '',
    name: '',
    pid: this.pid,
    image: '',
    description: '',
    sort: 0,
    status: 'success'
};
this.formTitle = '".lang('添加分类')."';
this.dialogVisible = true;
");

$vue->method("edit_row(row)", "  
ajax('/product/type/detail', {id: row.id}, function(res) {  
    if (res.code == 0) { 
        app.form = res.data;
        app.formTitle = '".lang('编辑分类')."';
        app.dialogVisible = true;
        app.load_tree();
    }  
});
");

$vue->method("submit()", "
let url = this.form.id ? '/product/type/edit' : '/product/type/add'; 
ajax(url, this.form, function(res) {
    ".vue_message()."
    if (res.code == 0) { 
        app.dialogVisible = false;
        app.load_list();
        app.load_tree();
    } 
});
");

$vue->method("delete_row(row)", "
this.\$confirm('".lang('确认删除该分类吗？')."', '".lang('提示')."', {
    confirmButtonText: '".lang('确定')."',
    cancelButtonText: '".lang('取消')."',
    type: 'warning'
}).then(() => {
    ".vue_message()."
    ajax('/product/type/delete', {id: row.id}, function(res) {
        if (res.code == 0) { 
            app.load_list();
            app.load_tree();
        } 
    });
}).catch(() => {});
");

// 权限控制
$vue->data("can_add", false);
$vue->data("can_edit", false);
$vue->data("can_del", false);

if(has_access('product/type/add')){
   $vue->data("can_add", true);
}
if(has_access('product/type/edit')){
   $vue->data("can_edit", true);
}
if(has_access('product/type/delete')){
   $vue->data("can_del", true);
}

vue_el_table_drag($ele='.table-drop',$data='list',$js = "
     let d = app.list;
     let ids = [];
     d.forEach(item => {
        ids.push(item.id);
     })
     ajax('/product/type/update-sort',{data:ids},function(res){
       
     });
"); 

$vue->data("pid",0);
$vue->method("showNext(pid)", "
     this.where_list = {
        pid: pid,
        page:1 
     }; 
     this.pid = pid;
     this.load_list();
");
?>

<?php
echo element("filter", [
    'data' => 'list',
    'url' => $url,
    'is_page' => true,
    'init' => true, 
    [
        'type' => 'input',
        'name' => 'name',
        'attr_element' => [
            'placeholder' => lang('分类名称'),
        ],
    ],
    [
        'type' => 'html', 
        'html' => '<el-button type="primary" v-if="can_add" @click="add()" >添加分类</el-button> 
            <el-button type="info" v-if="pid != 0" @click="showNext(0)" >返回上一级</el-button>
        ',
    ],
]);

?>
<div class="table-drop mt-0">
<?php
echo element('table', [
    ['name' => 'open', ':data' => 'list', ':height' => 'height'],
    ['name' => 'column', 'prop' => 'title', 'label' => lang('分类名称'), 'width' => '',
        'tpl' => [
           [
                'type'=>'html',
                "html"=>" 
                    <span class='bi bi-arrows-move drag me-2'></span>
                    <span v-if='scope.row.has_next' class='text-primary link' @click='showNext(scope.row.id)'>{{scope.row.title}}</span>
                    <span v-else>{{scope.row.title}}</span>
                "
            ]  
        ]
    ],
    ['name' => 'column', 'prop' => 'image', 'label' => lang('分类图片'), 'width' => '130',
        'tpl' => [
            ['name' => 'html', 'html' => '<img v-if="scope.row.image" :src="scope.row.image" alt="'.lang('分类图片').'" style="width: 50px; height: 50px;">'],
        ],
    ], 
    [
        'name' => 'column',
        'prop' => 'status',
        'label' => '状态',
        'width' => '100',
        'tpl' => [
            [
                'type'=>'html',
                "html"=>"
                    <el-tag v-if='scope.row.status == \"success\"' type='success'>".lang('启用')."</el-tag>
                    <el-tag v-else type='danger'>".lang('禁用')."</el-tag>
                "
            ]  
        ]
    ],
    [
        'name' => 'column',
        'prop' => 'id',
        'label' => lang('操作'),
        'width' => '200',
        'tpl' => [
            ['name' => 'button', 'label' => lang('编辑'), "v-if"=>"can_edit", '@click' => 'edit_row(scope.row)', 'type' => 'primary', 'size' => 'small'],
            ['name' => 'button', 'label' => lang('删除'), "v-if"=>"can_del", '@click' => 'delete_row(scope.row)', 'type' => 'danger', 'size' => 'small', 'style' => 'margin-left: 10px;'],
        ]
    ],
    ['name' => 'close'],
]);
?>
</div>

<?php
echo element("pager", [
    'data' => 'list',
    'per_page' => get_config('per_page'),
    'per_page_name' => 'per_page',
    'url' => $url,
    'reload_data' => []
]);
?>

<!-- 分类表单对话框 -->
<el-dialog :title="formTitle" :visible.sync="dialogVisible" width="50%" top="20px" :close-on-click-modal="false"> 
    <?php
    element\form::$model = 'form';
    echo element('form', [
        ['type' => 'open', 'model' => 'form','label-position'=>'left','label-width'=>'80px'],
        [
            'type' => 'input',
            'name' => 'title',
            'label' => lang('分类名称'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入分类名称')],
        ],
        [
            'type'=>'cascader','name'=>'pid','label'=>lang('上级分类'),  
            //'url'=>'/product/type/tree', 
            'options' => 'tree_data',
            'attr_element'=>[':props'=>"{value:'id',label:'title',checkStrictly: true}"],
        ], 
        [
            'type' => 'upload_media',
            'name' => 'image',
            'label' => lang('分类图片'), 
            'multiple' => false, 
        ],
        [
            'type' => 'text',
            'name' => 'description',
            'label' => lang('分类描述'),
            'attr_element' => [':rows' => 3],
        ], 
        [
            'type' => 'radio',
            'name' => 'status',
            'label' => lang('状态'),
            'value' => [['label' => lang('启用'), 'value' => 'success'], ['label' => lang('禁用'), 'value' => 'danger']],
        ],
        ['type' => 'close']
    ]);
    ?>
    <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false"><?=lang('取消')?></el-button>
        <el-button type="primary" @click="submit()"><?=lang('确定')?></el-button>
    </div>
</el-dialog>

<?php view_footer(); ?>