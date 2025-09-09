<?php
view_header(lang('内容管理'));
global $vue;
$url = '/blog/blog/list';

// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("form", [
    'id' => '',
    'title' => '',
    'desc' => '',
    'content' => '',
    'type_id' => '',
    'status' => 'draft',
    'images' => [],
    'tags' => '',
    'sort' => 0,
    'is_top' => 0,
    'is_recommend' => 0
]);
$vue->data("formTitle", lang('添加内容'));
$vue->data("typeList", "[]");
$vue->data("typeListAll", "[]");
$vue->data("list", "[]");
$vue->data("page", 1);
$vue->data("limit", 20);
$vue->data("total", 0);

$vue->created(["load()", "loadTypes()"]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)'; 
");

$vue->method("loadTypes()", "
ajax('/blog/type/tree', {}, function(res) {
    if (res.code == 0) {
        app.typeList = res.data;
    }
});
ajax('/blog/type/tree', {has_all:1}, function(res) {
    if (res.code == 0) {
        app.typeListAll = res.data;
    }
});

");

$vue->method("showAdd()", "
this.loadTypes();
this.form = {
    id: '',
    title: '',
    desc: '',
    content: '',
    type_id: '',
    status: 'draft',
    images: [],
    tags: '',
    sort: 0,
    is_top: 0,
    is_recommend: 0
};
this.formTitle = '" . lang('添加内容') . "';
this.dialogVisible = true;
this.update_editor('content', '');
");

$vue->method("update_row(row)", "
ajax('/blog/blog/detail', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.\$set(app, 'form', res.data);
        app.update_editor('content', app.form.content);
        app.loadTypes();
        app.formTitle = '" . lang('编辑内容') . "';
        app.dialogVisible = true;
    }
});
");

$vue->method("submit()", "  
let url = this.form.id ? '/blog/blog/edit' : '/blog/blog/add';
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.dialogVisible = false;
        app.load_list();
    }
});
");

$vue->method("delete_row(row)", "
this.\$confirm('" . lang('确认删除该内容吗？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    ajax('/blog/blog/delete', {id: row.id}, function(res) {
        " . vue_message() . "
        if (res.code == 0) {
            app.load_list();
        }
    });
}).catch(() => {});
");

$vue->method("reset_search()", "
this.where_list = {
    title: '',
    type_id: '',
    status: ''
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

$vue->method("getStatusTag(status)", "
const statusMap = {
    'draft': { type: 'info', text: '" . lang('草稿') . "' },
    'published': { type: 'success', text: '" . lang('已发布') . "' },
    'archived': { type: 'warning', text: '" . lang('已归档') . "' }
};
return statusMap[status] || { type: 'info', text: status };
");

// 权限控制
$vue->data("can_add", has_access('blog/blog/add'));
$vue->data("can_edit", has_access('blog/blog/edit'));
$vue->data("can_del", has_access('blog/blog/delete'));

$vue->method("selectType(data)", "
app.where_list.type_id = data.id; 
app.load_list();
");

$vue->method("open_type()", "
layer.open({
    type: 2,
    title: '" . lang('分类管理') . "',
    shadeClose: true,
    shade: 0.8,
    area: ['80%', '90%'],
    content: '/blog/type/index',
    end: function() {
         app.loadTypes();
    }
});
");

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
                'attr_element' => ['placeholder' => lang('内容标题')],
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'value' => [['label' => lang('全部'), 'value' => ''], ['label' => lang('草稿'), 'value' => 'draft'], ['label' => lang('已发布'), 'value' => 'published'], ['label' => lang('已归档'), 'value' => 'archived']],
                'attr_element' => ['placeholder' => lang('内容状态')],
            ],
            [
                'type' => 'html',
                'html' => '<el-button type="primary" v-if="can_add" @click="showAdd()">' . lang('添加内容') . '</el-button>',
            ],
        ]);
        ?>
        <!-- 左右布局容器 -->
        <div class="d-flex" style="height:calc(100vh - 40px);">
            <!-- 左侧分类树 -->
            <div class="border-end p-3 overflow-auto" style="width: 250px;">
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold"><?php echo lang('分类'); ?></div>
                        <div>
                            <span  @click="open_type" class="btn btn-sm btn-outline-secondary">管理</span>
                        </div>
                    </div>
                </div>
                <el-tree :data="typeListAll"
                    node-key="id"
                    :props="{children: 'children', label: 'title'}"
                    @node-click="selectType"
                    :highlight-current="true"
                    :expand-on-click-node="false"
                    :default-expand-all="true"
                    class="custom-tree">
                </el-tree>
            </div>

            <!-- 右侧内容区域 -->
            <div class="flex-fill ms-3 overflow-auto">
                <?php
                echo element('table', [
                    ['name' => 'open', ':data' => 'list', ":height" => "height"],
                    ['name' => 'column', 'prop' => 'title', 'label' => lang('标题'),'show-overflow-tooltip' => true, 'min-width' => '200'],
                    ['name' => 'column', 'prop' => 'type_title', 'label' => lang('分类'), 'width' => '120'], 
                    [
                        'name' => 'column',
                        'prop' => 'status',
                        'label' => lang('状态'),
                        'width' => '160',
                        'tpl' => [[
                            'type' => 'html',
                            "html" => "
                        <el-tag v-if='scope.row.status == \"draft\"' type='info'>" . lang('草稿') . "</el-tag>
                        <el-tag v-else-if='scope.row.status == \"published\"' type='success'>" . lang('已发布') . "</el-tag>
                        <el-tag v-else-if='scope.row.status == \"archived\"' type='warning'>" . lang('已归档') . "</el-tag>
                        <el-tag v-else type='info'>{{ scope.row.status }}</el-tag>
                    "
                        ]]
                    ],
                    ['name' => 'column', 'prop' => 'views', 'label' => lang('浏览量'), 'width' => '100'],
                    ['name' => 'column', 'prop' => 'likes', 'label' => lang('点赞数'), 'width' => '100'],
                    ['name' => 'column', 'prop' => 'comments', 'label' => lang('评论数'), 'width' => '100'],
                    [
                        'name' => 'column',
                        'prop' => 'created_at',
                        'label' => lang('创建时间'),
                        'width' => '180',
                        'tpl' => [[
                            'type' => 'html',
                            "html" => "{{ scope.row.created_at_format }}"
                        ]]
                    ],
                    [
                        'name' => 'column',
                        'label' => lang('操作'),
                        'fixed'=>'right',
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
    </div>
</div>

<?php
$vue->opt['is_editor'] = true;
?>

<!-- 内容表单对话框 -->
<el-dialog @opened="open_editor" :title="formTitle" :visible.sync="dialogVisible" width="90%" top="10px" :close-on-click-modal="false">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 pe-4 overflow-auto">
                <?php
                element\form::$model = 'form';
                echo element('form', [
                    ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '100px'],
                    [
                        'type' => 'input',
                        'name' => 'title',
                        'label' => lang('内容标题'),
                        'attr' => ['required'],
                        'attr_element' => ['placeholder' => lang('请输入内容标题')],
                    ],
                    [
                        'type' => 'cascader',
                        'name' => 'type_id',
                        'label' => lang('内容分类'),
                        'options' => 'typeList',
                        'attr_element' => [':props' => "{value:'id',label:'title',checkStrictly: true}"],
                        'attr' => ['required'],
                    ],
                    [
                        'type' => 'upload_media',
                        'name' => 'images',
                        'label' => lang('内容图片'),
                        'url' => '/admin/upload',
                        'multiple' => true,
                        'max' => 5,
                    ],
                    [
                        'type' => 'textarea',
                        'name' => 'desc',
                        'label' => lang('内容描述'),
                        'attr_element' => ['rows' => 3, 'placeholder' => lang('请输入内容描述')],
                    ],
                    [
                        'type' => 'radio',
                        'name' => 'status',
                        'label' => lang('内容状态'),
                        'value' => [['label' => lang('草稿'), 'value' => 'draft'], ['label' => lang('已发布'), 'value' => 'published'], ['label' => lang('已归档'), 'value' => 'archived']],
                    ],
                    [
                        'type' => 'editor',
                        'name' => 'content',
                        'label' => lang('内容内容'),
                        'attr' => ['required'],
                    ],
                    [
                        'type' => 'number',
                        'name' => 'sort',
                        'label' => lang('排序'),
                        'attr_element' => [':min' => 0],
                    ],
                    [
                        'type' => 'switch',
                        'name' => 'is_top',
                        'label' => lang('置顶'),
                        'attr_element' => [':active-value' => 1, ':inactive-value' => 0],
                    ],
                    [
                        'type' => 'switch',
                        'name' => 'is_recommend',
                        'label' => lang('推荐'),
                        'attr_element' => [':active-value' => 1, ':inactive-value' => 0],
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