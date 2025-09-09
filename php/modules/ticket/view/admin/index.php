<?php

use modules\ticket\model\TicketModel;

view_header('打印机管理');
global $vue;
$name = isset($name) ? $name : 'admin'; // 获取角色名称，默认为admin
$url = '/ticket/' . $name . '/list';

$vue->data("height", "");
$vue->data("name", $name); // 添加角色名称到Vue数据中
$vue->data("dialogVisible", false);
$vue->data("detailDialogVisible", false);
$vue->data("addDialogVisible", false);
$vue->data("ticketDetail", []);
$vue->data("form", [
    'id' => '',
    'title' => '',
    'code' => '',
    'secret' => '',
    'type' => '',
    'status' => 'success',
    'remark' => ''
]);
$vue->data("addForm", [
    'id' => '',
    'title' => '',
    'code' => '',
    'secret' => '',
    'type' => '',
    'status' => 'success',
    'remark' => ''
]);

$vue->data("typeOptions", []);
$vue->data("statusOptions", []);

$vue->created(["load()", "loadTypeOptions()", "loadStatusOptions()"]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
");

// 加载打印机类型选项
$vue->method("loadTypeOptions()", "
ajax('/ticket/" . $name . "/typeOptions', {}, function(res) {
    if (res.code == 0) {
        app.typeOptions = res.data;
    }
});
");

// 加载打印机状态选项
$vue->method("loadStatusOptions()", "
ajax('/ticket/" . $name . "/statusOptions', {}, function(res) {
    if (res.code == 0) {
        app.statusOptions = res.data;
    }
});
");

// 查看打印机详情
$vue->method("viewDetail(row)", "
ajax('/ticket/" . $name . "/info', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.ticketDetail = res.data;
        app.detailDialogVisible = true;
    }
});
");

// 添加打印机
$vue->method("addTicket()", "
app.addForm = { 
    title: '',
    code: '',
    secret: '',
    type: '',
    status: 'success',
    remark: ''
};
app.addDialogVisible = true;
");

// 编辑打印机
$vue->method("editTicket(row)", "
ajax('/ticket/" . $name . "/info', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.addForm = res.data;
        app.addDialogVisible = true;
        app.load_list();
    }
});
");

// 提交打印机表单
$vue->method("submitform()", "
let url = app.addForm.id ? '/ticket/" . $name . "/edit' : '/ticket/" . $name . "/add';
ajax(url, app.addForm, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.addDialogVisible = false; 
        app.load_list();
    } else { 
    }
});
");

// 删除打印机
$vue->method("deleteTicket(row)", "
app.\$confirm('" . lang('确认删除该打印机？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    ajax('/ticket/" . $name . "/delete', {id: row.id}, function(res) {
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
    case 'editTicket':
        this.editTicket(row);
        break;
    case 'deleteTicket':
        this.deleteTicket(row);
        break;
}
");

// 权限控制
$vue->data("can_view", has_access('ticket/' . $name . '/info'));
$vue->data("can_edit", has_access('ticket/' . $name . '/edit'));
$vue->data("can_add", has_access('ticket/' . $name . '/add'));
$vue->data("can_delete", has_access('ticket/' . $name . '/delete'));
?>

<div class="container-fluid">
    <!-- 打印机列表 -->
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
                    'html' => '<el-button class="me-2" v-if="can_add" type="primary" @click="addTicket()">
                <i class="el-icon-plus"></i> ' . lang('添加打印机') . '
            </el-button>'
                ],
                [
                    'type' => 'input',
                    'name' => 'title',
                    'attr_element' => ['placeholder' => lang('打印机名称')],
                ],
                [
                    'type' => 'input',
                    'name' => 'code',
                    'attr_element' => ['placeholder' => lang('打印机编码')],
                ],
                [
                    'type' => 'select',
                    'name' => 'type',
                    'value' => [
                        ['label' => lang('全部'), 'value' => ''],
                        ['label' => '飞鹅打印机', 'value' => 'Feie'],
                    ],
                    'attr_element' => ['placeholder' => lang('打印机类型')],
                ],
                [
                    'type' => 'select',
                    'name' => 'device_status',
                    'value' => [
                        ['label' => lang('全部'), 'value' => ''], 
                        ['label' => '在线', 'value' => 'online'],
                        ['label' => '离线', 'value' => 'offline']
                    ],
                    'attr_element' => ['placeholder' => lang('打印机状态')],
                ],
            ]);
            ?>
        </div>
        <div class="col-12">
            <?php
            $columns = [
                ['name' => 'open', ':data' => 'list', 'ref' => 'table', ':height' => 'height'],
                ['name' => 'column', 'prop' => 'title', 'label' => lang('打印机名称'), 'width' => ''],
                ['name' => 'column', 'prop' => 'code', 'label' => lang('打印机编码'), 'width' => '150'],
                [
                    'name' => 'column',
                    'prop' => 'type',
                    'label' => lang('类型'),
                    'width' => '180',
                    'tpl' => [[
                        'type' => 'html',
                        "html" => "
                            <el-tag v-if='scope.row.type == \"Feie\"' type='primary'>飞鹅打印机</el-tag> 
                            <span v-else>{{ scope.row.type }}</span>
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
                            <el-tag v-if='scope.row.status == \"success\"' type='success'>正常</el-tag>
                            <el-tag v-else-if='scope.row.status == \"error\"' type='danger'>异常</el-tag>
                            <el-tag v-else-if='scope.row.status == \"offline\"' type='info'>离线</el-tag>
                            <span v-else>{{ scope.row.status }}</span>
                        "
                    ]]
                ],
                [
                    'name' => 'column',
                    'prop' => 'device_status',
                    'label' => lang('状态'),
                    'width' => '100',
                    'tpl' => [[
                        'type' => 'html',
                        "html" => "
                            <el-tag v-if='scope.row.device_status == \"online\"' type='success'>在线</el-tag>
                            <el-tag v-else-if='scope.row.device_status == \"offline\"' type='danger'>离线</el-tag> 
                            <span v-else>待连接</span>
                        "
                    ]]
                ],


            ];
            do_action("ticket.columns", $columns);
            $columns[] = [
                'name' => 'column',
                'label' => lang('操作'),
                'fixed' => 'right',
                'width' => '120',
                'tpl' => [[
                    'type' => 'html',
                    'html' => '
                    <el-dropdown @command="handleCommand" trigger="click">
                        <el-button type="primary" size="small">
                            ' . lang('操作') . ' 
                        </el-button>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item v-if="can_view" :command="{action: \'viewDetail\', row: scope.row}">' . lang('查看详情') . '</el-dropdown-item>
                            <el-dropdown-item v-if="can_edit" :command="{action: \'editTicket\', row: scope.row}">' . lang('编辑') . '</el-dropdown-item>
                            <el-dropdown-item v-if="can_delete" :command="{action: \'deleteTicket\', row: scope.row}" divided>' . lang('删除') . '</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
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

<!-- 打印机详情对话框 -->
<el-dialog
    title="<?= lang('打印机详情') ?>"
    :visible.sync="detailDialogVisible"
    width="700px"
    :close-on-click-modal="false"
    :close-on-press-escape="false">
    <div v-if="ticketDetail">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <!-- 第一列 -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded mb-3">
                            <h6 class="mb-3 text-primary"><?= lang('基本信息') ?></h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted"><?= lang('打印机名称') ?></dt>
                                <dd class="col-sm-8">{{ ticketDetail.title }}</dd>

                                <dt class="col-sm-4 text-muted"><?= lang('打印机编码') ?></dt>
                                <dd class="col-sm-8">{{ ticketDetail.code }}</dd>

                                <dt class="col-sm-4 text-muted"><?= lang('打印机密钥') ?></dt>
                                <dd class="col-sm-8">{{ ticketDetail.secret }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- 第二列 -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded mb-3">
                            <h6 class="mb-3 text-primary"><?= lang('状态信息') ?></h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted"><?= lang('打印机类型') ?></dt>
                                <dd class="col-sm-8">{{ ticketDetail.type_text }}</dd>

                                <dt class="col-sm-4 text-muted"><?= lang('状态') ?></dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-success">{{ ticketDetail.status_text }}</span>
                                </dd>

                                <dt class="col-sm-4 text-muted"><?= lang('创建时间') ?></dt>
                                <dd class="col-sm-8">{{ ticketDetail.created_at_format }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- 备注（全宽） -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded">
                            <h6 class="mb-3 text-primary"><?= lang('备注') ?></h6>
                            <p class="mb-0">{{ ticketDetail.remark || '无备注信息' }}</p>
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

<!-- 添加/编辑打印机对话框 -->
<el-dialog
    :title="addForm.id ? '<?= lang('编辑打印机') ?>' : '<?= lang('添加打印机') ?>'"
    :visible.sync="addDialogVisible"
    width="600px"
    :close-on-click-modal="false"
    :close-on-press-escape="false">
    <el-form :model="addForm" label-width="120px">
        <el-form-item label="<?= lang('打印机类型') ?>" required>
            <el-select v-model="addForm.type" placeholder="<?= lang('请选择打印机类型') ?>" style="width: 100%">
                <el-option
                    v-for="item in typeOptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                </el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="<?= lang('打印机名称') ?>" required>
            <el-input v-model="addForm.title" placeholder="<?= lang('请输入打印机名称') ?>"></el-input>
        </el-form-item>
        <el-form-item label="<?= lang('打印机编码') ?>" required>
            <el-input v-model="addForm.code" placeholder="<?= lang('请输入打印机编码') ?>"></el-input>
        </el-form-item>
        <el-form-item label="<?= lang('打印机密钥') ?>">
            <el-input v-model="addForm.secret" placeholder="<?= lang('请输入打印机密钥') ?>"></el-input>
        </el-form-item>

        <!-- <el-form-item label="<?= lang('状态') ?>">
            <el-select v-model="addForm.status" placeholder="<?= lang('请选择状态') ?>" style="width: 100%">
                <el-option
                    v-for="item in statusOptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                </el-option>
            </el-select>
        </el-form-item> -->
        <el-form-item label="<?= lang('备注') ?>">
            <el-input
                type="textarea"
                v-model="addForm.remark"
                :rows="3"
                placeholder="<?= lang('请输入备注') ?>">
            </el-input>
        </el-form-item>
    </el-form>
    <div slot="footer" class="dialog-footer">
        <el-button @click="addDialogVisible = false"><?= lang('取消') ?></el-button>
        <el-button type="primary" @click="submitform()"><?= lang('确定') ?></el-button>
    </div>
</el-dialog>

<?php view_footer() ?>