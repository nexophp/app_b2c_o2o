<?php
view_header(lang('小程序页面管理'));
global $vue;
$url = '/mall/uniapp/list';

// 初始化Vue数据
$vue->data("dialogVisible", false);
$vue->data("form", [
    'id' => '',
    'name' => '',
    'url' => '',
    'share_title' => '',
    'share_image' => '',
    'status' => 1,
    'sort' => 0
]);
$vue->data("formTitle", lang('添加页面'));

$vue->created(["load_list()"]);

$vue->method("showAdd()", "
this.form = {
    id: '', 
    name: '',
    url: '',
    share_title: '',
    share_image: '',
    status: '1',
    is_home: '-1',
    sort: 0
};
this.formTitle = '" . lang('添加页面') . "';
this.dialogVisible = true;
");

$vue->method("update_row(row)", "
ajax('/mall/uniapp/detail', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.\$set(app, 'form', res.data);
        app.formTitle = '" . lang('编辑页面') . "';
        app.dialogVisible = true;
    }
});
");

$vue->method("submit()", "
let url = this.form.id ? '/mall/uniapp/edit' : '/mall/uniapp/add';
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.dialogVisible = false;
        app.load_list();
    }
});
");

$vue->method("delete_row(row)", "
this.\$confirm('" . lang('确认删除该页面吗？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    ajax('/mall/uniapp/delete', {id: row.id}, function(res) {
        " . vue_message() . "
        if (res.code == 0) {
            app.load_list();
        }
    });
}).catch(() => {});
");

$vue->method("designPage(row)", "
let url = '/mall/uniapp/design?id=' + row.id;
layer.open({
    type: 2,
    title: '".lang('设计页面')." - ' + row.name,

    shadeClose: true,
    shade: 0.8,
    area: ['100%', '100%'],
    content: url
});

");

// 权限控制
$vue->data("can_add", has_access('mall/uniapp/add'));
$vue->data("can_edit", has_access('mall/uniapp/edit'));
$vue->data("can_del", has_access('mall/uniapp/delete'));
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
            'attr_element' => ['placeholder' => lang('页面名称')],
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'value' => [['label' => lang('全部'), 'value' => ''], ['label' => lang('启用'), 'value' => '1'], ['label' => lang('禁用'), 'value' => '-1']],
            'attr_element' => ['placeholder' => lang('页面状态')],
        ],
        [
            'type' => 'html',
            'html' => '<el-button type="primary" v-if="can_add" @click="showAdd()">' . lang('添加页面') . '</el-button>',
        ],
    ]);
    ?>
    
    <?php
    echo element('table', [
        ['name' => 'open', ':data' => 'list'],
        ['name' => 'column', 'prop' => 'id', 'label' => 'ID', 'width' => '80'],
        ['name' => 'column', 'prop' => 'name', 'label' => lang('页面名称')],
        ['name' => 'column', 'prop' => 'url', 'label' => lang('页面URL')],
        ['name' => 'column', 'prop' => 'share_title', 'label' => lang('分享标题')],
        ['name' => 'column', 'prop' => 'is_home', 'label' => lang('是否首页'),
            'tpl' => [[
                'type' => 'html',
                "html" => "
                        <el-tag v-if='scope.row.is_home == 1' type='success'>" . lang('是') . "</el-tag>
                        <el-tag v-else type='danger'>" . lang('否') . "</el-tag>
                    "
            ]]

        ],

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
            'label' => lang('操作'),
            'width' => '280',
            'tpl' => [
                ['name' => 'button', 'label' => lang('设计'), "v-if" => "can_edit", '@click' => 'designPage(scope.row)', 'type' => 'primary', 'size' => 'small'],
                ['name' => 'button', 'label' => lang('编辑'), "v-if" => "can_edit", '@click' => 'update_row(scope.row)', 'type' => 'success', 'size' => 'small', 'style' => 'margin-left: 5px;'],
                ['name' => 'button', 'label' => lang('删除'), "v-if" => "can_del", '@click' => 'delete_row(scope.row)', 'type' => 'danger', 'size' => 'small', 'style' => 'margin-left: 5px;'],
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

<!-- 页面表单对话框 -->
<el-dialog :title="formTitle" :visible.sync="dialogVisible" width="600px" :close-on-click-modal="false">
    <?php
    element\form::$model = 'form';
    echo element('form', [
        ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '100px'],
        [
            'type' => 'input',
            'name' => 'name',
            'label' => lang('页面名称'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入页面名称')],
        ],
        [
            'type' => 'input',
            'name' => 'url',
            'label' => lang('页面URL'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('如: index')],
        ],
        [
            'type' => 'input',
            'name' => 'share_title',
            'label' => lang('分享标题'),
            'attr_element' => ['placeholder' => lang('请输入分享标题')],
        ],
        [
            'type' => 'upload_media',
            'name' => 'share_image',
            'label' => lang('分享图片'),
            'url' => '/admin/upload',
            'max' => 1,
        ],
        [
            'type' => 'radio',
            'name' => 'is_home',
            'label' => lang('是否首页'),
            'value' => [['label' => lang('是'), 'value' => 1], ['label' => lang('否'), 'value' => -1]],
        ],
        [
            'type' => 'radio',
            'name' => 'status',
            'label' => lang('状态'),
            'value' => [['label' => lang('启用'), 'value' => 1], ['label' => lang('禁用'), 'value' => -1]],
        ],
         
        ['type' => 'close']
    ]);
    ?>
    <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submit()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<?php view_footer(); ?>