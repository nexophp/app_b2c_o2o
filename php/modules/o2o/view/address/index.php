<?php

use modules\o2o\model\AddressModel;

view_header('配送地址管理');
global $vue;
$name = isset($name) ? $name : 'address'; // 获取控制器名称，默认为address
$url = '/o2o/' . $name . '/list';

$vue->data("height", "");
$vue->data("name", $name); // 添加控制器名称到Vue数据中
$vue->data("dialogVisible", false);
$vue->data("detailDialogVisible", false);
$vue->data("addDialogVisible", false);
$vue->data("addressDetail", []);
$vue->data("form", [
    'id' => '',
    'title' => '',
    'regions' => [],
    'status' => 'success'
]);
$vue->data("form", [
    'id' => '',
    'title' => '',
    'regions' => [],
    'status' => 'success'
]);

$vue->data("statusOptions", []);
$vue->data("regionOptions", []);

$vue->created(["load()", "loadStatusOptions()", "loadRegionOptions()"]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
");

// 加载状态选项
$vue->method("loadStatusOptions()", "
ajax('/o2o/" . $name . "/status-options', {}, function(res) {
    if (res.code == 0) {
        app.statusOptions = res.data;
    }
});
");

// 加载区域选项
$vue->method("loadRegionOptions()", "
ajax('/address/api/index', {}, function(res) {
    if (res.code == 0) {
        app.regionOptions = res.data;
    }
});
");

// 查看地址详情
$vue->method("viewDetail(row)", "
ajax('/o2o/" . $name . "/info', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.addressDetail = res.data;
        app.detailDialogVisible = true;
    }
});
");

// 添加地址
$vue->method("addAddress()", "
app.form = {
    title: '', 
    status: 'success'
};
app.addDialogVisible = true;
");

// 编辑地址
$vue->method("editAddress(row)", " 
ajax('/o2o/" . $name . "/info', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.form = res.data;
        app.addDialogVisible = true;
        app.load_list();
    }
});
");

// 提交地址表单
$vue->method("submitform()", "
let url = app.form.id ? '/o2o/" . $name . "/edit' : '/o2o/" . $name . "/add';
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.addDialogVisible = false;
        app.load_list();
    } else {
    }
});
");

// 删除地址
$vue->method("deleteAddress(row)", "
app.\$confirm('" . lang('确认删除该配送地址？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    ajax('/o2o/" . $name . "/delete', {id: row.id}, function(res) {
        " . vue_message() . "
        if (res.code == 0) {
           app.load_list();
        } else {
        }
    });
}).catch(() => {});
");

// 添加处理下拉菜单命令的方法
$vue->method("handleCommand(command)", "
let action = command.action;
let row = command.row; 
switch(action) {
    case 'viewDetail':
        this.viewDetail(row);
        break;
    case 'editAddress':
        this.editAddress(row);
        break;
    case 'deleteAddress':
        this.deleteAddress(row);
        break;
}
");

// 权限控制
$vue->data("can_view", has_access('o2o/' . $name . '/info'));
$vue->data("can_edit", has_access('o2o/' . $name . '/edit'));
$vue->data("can_add", has_access('o2o/' . $name . '/add'));
$vue->data("can_delete", has_access('o2o/' . $name . '/delete'));

?>

<div class="container-fluid">
    <!-- 配送地址列表 -->
    <div class="row">
        <div class="col-12 mb-3">
            <?php
            echo element("filter", [
                'data' => 'list',
                'url' => $url,
                'is_page' => true,
                'init' => true,
                [
                    'type' => 'html',
                    'html' => '<el-button class="me-2" v-if="can_add" type="primary" @click="addAddress()">
                <i class="el-icon-plus"></i> ' . lang('添加配送地址') . '
            </el-button>'
                ],
                [
                    'type' => 'input',
                    'name' => 'title',
                    'attr_element' => ['placeholder' => lang('地址简称')],
                ], 
            ]);
            ?>
        </div>
        <div class="col-12">
            <?php
            $columns = [
                ['name' => 'open', ':data' => 'list', 'ref' => 'table', ':height' => 'height'],
                ['name' => 'column', 'prop' => 'title', 'label' => lang('地址简称'), 'width' => '300', ':show-overflow-tooltip' => 'true'],
                [
                    'name' => 'column',
                    'prop' => 'address',
                    'label' => lang('配送区域'),
                    'width' => '',
                    ':show-overflow-tooltip' => 'true'
                ],
                [
                    'name' => 'column',
                    'prop' => 'status',
                    'label' => lang('状态'),
                    'width' => '100',
                    'tpl' => [[
                        'type' => 'html',
                        "html" => "
                            <el-tag v-if='scope.row.status == \"success\"' type='success'>正常</el-tag>
                            <el-tag v-else-if='scope.row.status == \"error\"' type='danger'>禁用</el-tag>
                            <span v-else>{{ scope.row.status }}</span>
                        "
                    ]]
                ],
                ['name' => 'column', 'prop' => 'created_at_text', 'label' => lang('创建时间'), 'width' => '180'],
            ];
            do_action("o2o.address.columns", $columns);
            $columns[] = [
                'name' => 'column',
                'label' => lang('操作'),
                'fixed' => 'right',
                'width' => '220',
                'tpl' => [[
                    'type' => 'html',
                    'html' => '
                    <el-button v-if="can_edit" type="primary" size="small" @click="editAddress(scope.row)">编辑</el-button>
                    <el-button v-if="can_delete" type="danger" size="small" @click="deleteAddress(scope.row)">删除</el-button> 
                    '
                ]]
            ];

            echo element('table', $columns);
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
    </div>
</div>

<!-- 配送地址详情对话框 -->
<el-dialog
    title="<?= lang('配送地址详情') ?>"
    :visible.sync="detailDialogVisible"
    width="700px"
    :close-on-click-modal="false"
    :close-on-press-escape="false">
    <div v-if="addressDetail">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <!-- 第一列 -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded mb-3">
                            <h6 class="mb-3 text-primary"><?= lang('基本信息') ?></h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted"><?= lang('地址简称') ?></dt>
                                <dd class="col-sm-8">{{ addressDetail.title }}</dd>

                                <dt class="col-sm-4 text-muted"><?= lang('状态') ?></dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-success">{{ addressDetail.status_text }}</span>
                                </dd>

                                <dt class="col-sm-4 text-muted"><?= lang('创建时间') ?></dt>
                                <dd class="col-sm-8">{{ addressDetail.created_at_format }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- 第二列 -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded mb-3">
                            <h6 class="mb-3 text-primary"><?= lang('配送区域') ?></h6>

                            <div class="text-muted">
                                {{ addressDetail.address }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div slot="footer" class="dialog-footer">
        <el-button @click="detailDialogVisible = false"><?= lang('关闭') ?></el-button>
    </div>
</el-dialog>

<!-- 添加/编辑配送地址对话框 -->
<el-dialog
    :title="form.id ? '<?= lang('编辑配送地址') ?>' : '<?= lang('添加配送地址') ?>'"
    :visible.sync="addDialogVisible"
    width="600px"
    :close-on-click-modal="false"
    :close-on-press-escape="false">

    <?php
    element\form::$model = 'form';
    echo element('form', [
        ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '100px'],
        ['type' => 'input', 'name' => 'title', 'label' => lang('地址简称'), 'attr' => ['required']],
        [
            'type' => 'cascader',
            'name' => 'regions',
            'label' => lang('配送区域'),
            'options' => 'regionOptions',
            'attr_element' => [':props' => "{value:'label',label:'label',checkStrictly: false}"],
            'attr' => ['required'],
        ],
        ['type' => 'input', 'name' => 'detail', 'label' => lang('详细地址'), 'attr' => ['required']],

        ['type' => 'select', 'name' => 'status', 'label' => lang('状态'), 'value' => 'statusOptions'],

        ['type' => 'close']
    ]);
    ?>
    <div slot="footer" class="dialog-footer">
        <el-button @click="addDialogVisible = false"><?= lang('取消') ?></el-button>
        <el-button type="primary" @click="submitform()"><?= lang('确定') ?></el-button>
    </div>
</el-dialog>

<?php view_footer() ?>