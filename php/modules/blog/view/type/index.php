<?php
view_header('博客分类管理');
global $vue;
$url = '/blog/type/list';

// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("form", [
    'id' => '',
    'title' => '',
    'description' => '',
    'sort' => 0,
    'status' => 1
]);
$vue->data("formTitle", lang('添加分类'));
$vue->data("where_list", [
    'title' => ''
]);
$vue->data("list", []);
$vue->data("page", 1);
$vue->data("limit", 20);
$vue->data("total", 0);

$vue->created(["load()"]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
this.load_list();
");

$vue->method("load_list()", "
let params = Object.assign({}, this.where_list, {
    page: this.page,
    limit: this.limit
});
ajax('" . $url . "', params, function(res) {
    if (res.code == 0) {
        app.list = res.data;
        app.total = res.total;
    }
});
");

$vue->method("showAdd()", "
this.form = {
    id: '',
    title: '',
    description: '',
    sort: 0,
    status: '1'
};
this.formTitle = '" . lang('添加分类') . "';
this.dialogVisible = true;
");

$vue->method("update_row(row)", "
ajax('/blog/type/detail', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.\$set(app, 'form', res.data);
        app.formTitle = '" . lang('编辑分类') . "';
        app.dialogVisible = true;
    }
});
");

$vue->method("submit()", "
let url = this.form.id ? '/blog/type/edit' : '/blog/type/add';
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.dialogVisible = false;
        app.load_list();
    }
});
");

$vue->method("delete_row(row)", "
this.\$confirm('" . lang('确认删除该分类吗？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    ajax('/blog/type/delete', {id: row.id}, function(res) {
        " . vue_message() . "
        if (res.code == 0) {
            app.load_list();
        }
    });
}).catch(() => {});
");

$vue->method("reset_search()", "
this.where_list = {
    title: ''
};
this.load_list();
");

$vue->method("handleSizeChange(val)", "
this.limit = val;
this.load_list();
");

$vue->method("handleCurrentChange(val)", "
this.page = val;
this.load_list();
");

// 权限控制
$vue->data("can_add", has_access('blog/type/add'));
$vue->data("can_edit", has_access('blog/type/edit'));
$vue->data("can_del", has_access('blog/type/delete'));

?>

<div class="page-container"> 
    
    <div class="page-content">
        <?php
        echo element("filter", [
            'data' => 'list',
            'url' => $url,
            'is_page' => true,
            'init' => true,
            [
                'type' => 'input',
                'name' => 'title',
                'attr_element' => ['placeholder' => lang('分类名称')],
            ],
            [
                'type' => 'html',
                'html' => '<el-button type="primary" v-if="can_add" @click="showAdd()">' . lang('添加分类') . '</el-button>',
            ],
        ]);
        ?>

        <?php
        echo element('table', [
            ['name' => 'open', ':data' => 'list', ":height" => "height"],
            ['name' => 'column', 'prop' => 'id', 'label' => 'ID', 'width' => '80'],
            ['name' => 'column', 'prop' => 'title', 'label' => lang('分类名称'), 'min-width' => '200'],
            ['name' => 'column', 'prop' => 'description', 'label' => lang('描述'), 'min-width' => '200', 'show-overflow-tooltip' => true],
            ['name' => 'column', 'prop' => 'sort', 'label' => lang('排序'), 'width' => '100'],
            [
                'name' => 'column',
                'prop' => 'status',
                'label' => lang('状态'),
                'width' => '100',
                'tpl' => [[
                    'type' => 'html',
                    "html" => "
                        <el-tag v-if='scope.row.status == 1' type='success'>" . lang('启用') . "</el-tag>
                        <el-tag v-else type='danger'>" . lang('禁用') . "</el-tag>
                    "
                ]]
            ],
            [
                'name' => 'column',
                'prop' => 'created_at',
                'label' => lang('创建时间'),
                'width' => '180',
                'tpl' => [[
                    'type' => 'html',
                    "html" => "{{ new Date(scope.row.created_at * 1000).toLocaleString() }}"
                ]]
            ],
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
</div>

<!-- 分类表单对话框 -->
<el-dialog :title="formTitle" :visible.sync="dialogVisible" width="600px" :close-on-click-modal="false">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php
                element\form::$model = 'form';
                echo element('form', [
                    ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '100px'],
                    [
                        'type' => 'input',
                        'name' => 'title',
                        'label' => lang('分类名称'),
                        'attr' => ['required'],
                        'attr_element' => ['placeholder' => lang('请输入分类名称')],
                    ],
                    [
                        'type' => 'textarea',
                        'name' => 'description',
                        'label' => lang('描述'),
                        'attr_element' => ['rows' => 3, 'placeholder' => lang('请输入分类描述')],
                    ],
                    [
                        'type' => 'number',
                        'name' => 'sort',
                        'label' => lang('排序'),
                        'attr_element' => [':min' => 0],
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
            </div>
        </div>
    </div>
    <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submit()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<?php
view_footer();
?>