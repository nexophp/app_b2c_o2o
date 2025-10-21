<?php
view_header('物流公司管理');
global $vue;
$url = '/logistic/admin/list';

// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("form", [
    'id' => '',
    'name' => '',
    'code' => '',
    'status' => 1
]);
$vue->data("formTitle", lang('添加物流公司'));

$vue->created(["load()"]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
");

$vue->method("showAdd()", "
this.form = {
    id: '', 
    name: '',
    code: '',
    status: '1',
};
this.formTitle = '".lang('添加物流公司')."';
this.dialogVisible = true;  
");

$vue->method("update_row(row)", " 
ajax('/logistic/admin/detail', {id: row.id}, function(res) { 
    if (res.code == 0) {  
        app.\$set(app, 'form', res.data); 
        app.formTitle = '".lang('编辑物流公司')."';
        app.dialogVisible = true;    
    }  
});
");

$vue->method("submit()", "
let url = this.form.id ? '/logistic/admin/edit' : '/logistic/admin/add'; 
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.dialogVisible = false;
        app.load_list();
    } 
});
");

$vue->method("delete_row(row)", "
this.\$confirm('".lang('确认删除该物流公司吗？')."', '".lang('提示')."', {
    confirmButtonText: '".lang('确定')."',
    cancelButtonText: '".lang('取消')."',
    type: 'warning'
}).then(() => {
    " . vue_message() . "
    ajax('/logistic/admin/delete', {id: row.id}, function(res) {
        if (res.code == 0) { 
            app.load_list();
        } 
    });
}).catch(() => {});
");

$vue->method("updateStatus(row)", "
let status = row.status == 1 ? 0 : 1;
ajax('/logistic/admin/update-status', {id: row.id, status: status}, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.load_list();
    } 
});
");

// 权限控制
$vue->data("can_add", has_access('logistic/admin/add'));
$vue->data("can_edit", has_access('logistic/admin/edit'));
$vue->data("can_del", has_access('logistic/admin/delete'));
?>

<div class="container-fluid">
    <?php
    echo element("filter", [
        'data' => 'list',
        'url' => $url,
        'is_page' => true,
        'init' => true,
        [
            'type' => 'input',
            'name' => 'name',
            'attr_element' => ['placeholder' => lang('物流公司名称')],
        ],
        [
            'type' => 'input',
            'name' => 'code',
            'attr_element' => ['placeholder' => lang('物流公司代码')],
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'value' => [['label' => lang('全部'), 'value' => ''], ['label' => lang('启用'), 'value' => '1'], ['label' => lang('禁用'), 'value' => '0']],
            'attr_element' => ['placeholder' => lang('状态')],
        ],
        [
            'type' => 'html',
            'html' => '<el-button type="primary" v-if="can_add" @click="showAdd()">' . lang('添加物流公司') . '</el-button>',
        ],
    ]);
    ?>
    
    <?php
    echo element('table', [
        ['name' => 'open', ':data' => 'list',":height"=>"height"], 
        ['name' => 'column', 'prop' => 'name', 'label' => lang('物流公司名称')],
        ['name' => 'column', 'prop' => 'code', 'label' => lang('物流公司代码'), 'width' => '150'],
        [
            'name' => 'column',
            'prop' => 'status',
            'label' => lang('状态'),
            'width' => '100',
            'tpl' => [[
                'type' => 'html',
                "html" => "
                        <el-switch v-model='scope.row.status' :active-value='1' :inactive-value='0' @change='updateStatus(scope.row)'></el-switch>
                    "
            ]]
        ],
        ['name' => 'column', 'prop' => 'created_at_text', 'label' => lang('创建时间'), 'width' => '180'], 
        [
            'name' => 'column',
            'label' => lang('操作'),
            'width' => '200',
            'tpl' => [
                ['name' => 'button', 'label' => lang('编辑'), "v-if" => "can_edit", '@click' => 'update_row(scope.row)', 'type' => 'primary', 'size' => 'small'],
                ['name' => 'button', 'label' => lang('删除'), "v-if" => "can_del", '@click' => 'delete_row(scope.row)', 'type' => 'danger', 'size' => 'small', 'style' => 'margin-left: 10px;'],
            ]
        ],
        ['name' => 'close'],
    ]);
    ?>
    
    <?php
    echo element("pager", [
        'data' => 'list',
        'per_page' => get_config('per_page'), 
        'url' => $url,
        'reload_data' => []
    ]);
    ?>
</div>

<!-- 物流公司表单对话框 -->
<el-dialog :title="formTitle" :visible.sync="dialogVisible" width="500px" :close-on-click-modal="false">
    
    <?php
    element\form::$model = 'form';
    echo element('form', [
        ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '120px'],
        [
            'type' => 'input',
            'name' => 'name',
            'label' => lang('物流公司名称'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入物流公司名称')], 
        ],
        [
            'type' => 'input',
            'name' => 'code',
            'label' => lang('物流公司代码'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入物流公司代码，如：SF、YTO等')],
        ],
        [
            'type' => 'radio',
            'name' => 'status',
            'label' => lang('状态'),
            'value' => [['label' => lang('启用'), 'value' => 1], ['label' => lang('禁用'), 'value' => 0]],
        ],
        ['type' => 'close']
    ]);
    ?>

    <div class="text-center mt-3">
        <a href="https://market.aliyun.com/apimarket/detail/cmapi021863" target="_blank" class="link">
            <?=lang('查看物流公司名称及代码')?>
        </a>
    </div>

    <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submit()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<?php view_footer(); ?>