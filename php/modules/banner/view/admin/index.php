<?php
view_header('Banner管理');
global $vue;
$url = '/banner/admin/list';

// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("form", [
    'id' => '',
    'title' => '',
    'image' => '',
    'url' => '',
    'type' => 'web',
    'sort' => 0,
    'status' => 1,
    'description' => '',
    'start_time' => '',
    'end_time' => ''
]);
$vue->data("formTitle", "添加Banner");

$vue->created(["load()",]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
");

$vue->method("add()", "
this.form = {
    id: '',
    title: '',
    image: '',
    url: '',
    type: 'minapp', 
    status: '1', 
};
this.formTitle = '" . lang('添加Banner') . "';
this.dialogVisible = true;
");

$vue->method("edit_row(row)", "  
ajax('/banner/admin/detail', {id: row.id}, function(res) {  
    if (res.code == 0) { 
        app.form = res.data;
        app.formTitle = '" . lang('编辑Banner') . "';
        app.dialogVisible = true;
    }  
});
");

$vue->method("submit()", "
let url = this.form.id ? '/banner/admin/edit' : '/banner/admin/add'; 
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.dialogVisible = false;
        app.load_list();
    } 
});
");

$vue->method("delete_row(row)", "
this.\$confirm('" . lang('确认删除该Banner吗？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    " . vue_message() . "
    ajax('/banner/admin/delete', {id: row.id}, function(res) {
        if (res.code == 0) { 
            app.load_list();
        } 
    });
}).catch(() => {});
");

$vue->method("toggle_status(row)", "
ajax('/banner/admin/toggleStatus', {id: row.id}, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.load_list();
    } 
});
");

// 权限控制
$vue->data("can_add", false);
$vue->data("can_edit", false);
$vue->data("can_del", false);

if (has_access('banner/admin/add')) {
    $vue->data("can_add", true);
}
if (has_access('banner/admin/edit')) {
    $vue->data("can_edit", true);
}
if (has_access('banner/admin/delete')) {
    $vue->data("can_del", true);
}

// 拖拽排序
vue_el_table_drag($ele = '.table-drop', $data = 'list', $js = "
     let d = app.list;
     let ids = [];
     d.forEach(item => {
        ids.push(item.id);
     })
     ajax('/banner/admin/updateSort',{data:ids},function(res){
       
     });
");
?>

<?php
echo element("filter", [
    'data' => 'list',
    'url' => $url,
    'is_page' => true,
    'init' => true,
    [
        'type' => 'select',
        'name' => 'status',
        'attr_element' => [
            'placeholder' => lang('选择状态'),
        ],
        'value' => [
            ['label' => lang('全部状态'), 'value' => ''],
            ['label' => lang('启用'), 'value' => '1'],
            ['label' => lang('禁用'), 'value' => '0']
        ],
    ],
    [
        'type' => 'html',
        'html' => '<el-button type="primary" v-if="can_add" @click="add()" >添加Banner</el-button>',
    ],
]);

?>
<div class="table-drop mt-0">
    <?php
    echo element('table', [
        ['name' => 'open', ':data' => 'list', ':height' => 'height'],
        [
            'name' => 'column',
            'prop' => 'sort',
            'label' => lang('排序'),
            'width' => '80',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => " 
                    <span class='bi bi-arrows-move drag me-2'></span> 
                "
                ]
            ]
        ],
        [
            'name' => 'column',
            'prop' => 'image',
            'label' => lang('图片'),
            'width' => '100',
            'tpl' => [
                ['name' => 'html', 'html' => '<img v-if="scope.row.image" :src="scope.row.image" alt="' . lang('Banner图片') . '" style="width: 60px; height: 40px; object-fit: cover;" class="img-thumbnail">'],
            ],
        ],
        [
            'name' => 'column',
            'prop' => 'title',
            'label' => lang('标题'),
            'width' => '',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <div>
                        <strong>{{scope.row.title}}</strong> 
                    </div>
                "
                ]
            ]
        ],
        [
            'name' => 'column',
            'prop' => 'type',
            'label' => lang('类型'),
            'width' => '150',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <el-tag v-if='scope.row.type == \"minapp\"' type='info'>小程序页面</el-tag>
                    <el-tag v-else-if='scope.row.type == \"web\"' type='primary'>网页链接</el-tag>
                    <el-tag v-else-if='scope.row.type == \"minapp_jump\"' type='primary'>跳转小程序</el-tag>
                "
                ]
            ]
        ],

        [
            'name' => 'column',
            'prop' => 'status',
            'label' => lang('状态'),
            'width' => '100',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <el-tag v-if='scope.row.status == 1' type='success' @click='toggle_status(scope.row)' style='cursor: pointer;'>启用</el-tag>
                    <el-tag v-else type='danger' @click='toggle_status(scope.row)' style='cursor: pointer;'>禁用</el-tag>
                "
                ]
            ]
        ],


        [
            'name' => 'column',
            'prop' => 'id',
            'label' => lang('操作'),
            'width' => '220',
            'tpl' => [
                ['name' => 'button', 'label' => lang('编辑'), "v-if" => "can_edit", '@click' => 'edit_row(scope.row)', 'type' => 'primary', 'size' => 'small'],
                ['name' => 'button', 'label' => lang('删除'), "v-if" => "can_del", '@click' => 'delete_row(scope.row)', 'type' => 'danger', 'size' => 'small', 'style' => 'margin-left: 10px;'],
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

<!-- Banner表单对话框 -->
<el-dialog :title="formTitle" :visible.sync="dialogVisible" width="60%" top="20px" :close-on-click-modal="false">
    <?php
    element\form::$model = 'form';
    echo element('form', [
        ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '100px'],
        [
            'type' => 'input',
            'name' => 'title',
            'label' => lang('标题'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入标题')],
        ],
        [
            'type' => 'upload_media',
            'name' => 'image',
            'label' => lang('图片'),
            'multiple' => false,
            'attr' => ['required'],
        ],
        [
            'type' => 'radio',
            'name' => 'type',
            'label' => lang('类型'),
            'value' => [
                ['label' => lang('小程序页面'), 'value' => 'minapp'],
                ['label' => lang('跳转小程序'), 'value' => 'minapp_jump'],
                ['label' => lang('网页链接'), 'value' => 'web'],
            ],
            'attr' => ['required'],
        ],
        [
            'type' => 'input',
            'name' => 'app_id',
            'label' => lang('AppID'),
            'attr_element' => ['placeholder' => lang('请输入小程序AppID')],
            'attr' => ['required', "v-if" => "form.type == 'minapp_jump'"],
        ],

        [
            'type' => 'input',
            'name' => 'url',
            'label' => lang('链接'),
            'attr_element' => [],
            'attr' => ['required'],
        ],

        ['type' => 'close']
    ]);
    ?>
    <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false"><?= lang('取消') ?></el-button>
        <el-button type="primary" @click="submit()"><?= lang('确定') ?></el-button>
    </div>
</el-dialog>

<?php view_footer(); ?>