<?php view_header(lang('钱包')) ?>
<?php
global $vue;
$url = '/wallet/admin/list';

$vue->created(["load()"]);
$vue->data("height", "");
$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)'; 
");
$vue->data("can_view", has_access('wallet/admin/index'));
$vue->data("can_edit", has_access('wallet/admin/edit'));
$vue->data("can_confirm", has_access('wallet/admin/confirm'));

$per_page = get_config('per_page');
// 弹出窗口相关数据
$vue->data("dialogVisible", false);
$vue->data("activeTab", "out");
$vue->data("currentUser", []);
$vue->data("inRecords", []);
$vue->data("outRecords", []);
$vue->data("inPagination", ['page' => 1, 'limit' => $per_page, 'total' => 0]);
$vue->data("outPagination", ['page' => 1, 'limit' => $per_page, 'total' => 0]);
$vue->data("loading", false);

// 查看详情方法
$vue->method("view(row)", "
this.currentUser = row;
this.dialogVisible = true;
this.activeTab = 'out';
this.loadOutRecords();
this.loadInRecords();
");

// 加载收入记录
$vue->method("loadInRecords()", "
this.loading = true;
ajax('/wallet/admin/in-list', {
    user_id: this.currentUser.user_id,
    page: this.inPagination.page,
    per_page: this.inPagination.limit
}, (res) => {
    this.inRecords = res.data;
    this.inPagination.total = res.total;
    this.loading = false;
});
");

// 加载提现记录
$vue->method("loadOutRecords()", " 
ajax('/wallet/admin/out-list', {
    user_id: this.currentUser.user_id,
    page: this.outPagination.page,
    per_page: this.outPagination.limit
}, (res) => {
    this.outRecords = res.data;
    this.outPagination.total = res.total;
    this.loading = false;
});
");

// 切换标签页
$vue->method("handleTabClick(tab)", "
if (tab.name === 'out' && this.outRecords.length === 0) {
    this.loadOutRecords();
}
");

// 确认提现
$vue->method("confirmOut(row)", "
this.\$confirm('确定要同意此提现申请并打款吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
}).then(() => {
    ajax('/wallet/admin/confirm-out', {
        out_id: row.id
    }, (res) => {
        this.\$message.success(res.msg || '操作成功');
        this.loadOutRecords();
        this.load_list();
        this.load();
    });
}).catch(() => {});
");

// 收入记录分页
$vue->method("handleInPageChange(page)", "
this.inPagination.page = page;
this.loadInRecords();
");

// 提现记录分页
$vue->method("handleOutPageChange(page)", "
this.outPagination.page = page;
this.loadOutRecords();
");
?>

<div class="row">
    <div class="col-12">
        <?php
        echo element("filter", [
            'data' => 'list',
            'url' => $url,
            'is_page' => true,
            'init' => true,
            [
                'type' => 'input',
                'name' => 'phone',
                'attr_element' => ['placeholder' => lang('搜索用户名或手机号')],
            ],
            [
                'type'=>'html',
                'html'=>"
                    <span style='line-height:30px;'>
                        ".wallet_notice()."
                    </span>
                "

            ]
        ]);
        ?>
    </div>
    <div class="col-12">
        <?php
        echo element('table', [
            ['name' => 'open', ':data' => 'list', 'ref' => 'table', ':height' => 'height'],
            ['name' => 'column', 'prop' => 'user_phone', 'label' => lang('用户'), 'width' => ''],
            ['name' => 'column', 'prop' => 'total_amount', 'label' => lang('总收入'), 'width' => ''],
            ['name' => 'column', 'prop' => 'in_amount', 'label' => lang('收入'), 'width' => ''],
            ['name' => 'column', 'prop' => 'wait_in_amount', 'label' => lang('收入待确认'), 'width' => ''],
            ['name' => 'column', 'prop' => 'can_out_amount', 'label' => lang('可提现'), 'width' => ''],
            ['name' => 'column', 'prop' => 'out_amount', 'label' => lang('已提现'), 'width' => ''],
            [
                'name' => 'column',
                'prop' => 'wait_out_amount',
                'label' => lang('提现待审核'),
                'width' => '',
                'tpl' => [
                    ['type' => 'html', 'html' => '
                        <el-tag v-if="scope.row.wait_out_amount > 0" type="danger">
                            {{scope.row.wait_out_amount}}
                        </el-tag>
                    ']
                ]

            ],



            [
                'name' => 'column',
                'label' => lang('操作'),
                'fixed' => 'right',
                'width' => '120',
                'tpl' => [
                    ['type' => 'html', 'html' => ' 
                            <el-button v-if="can_view" type="primary" size="small" @click="view(scope.row)">查看</el-button> 
                        ']
                ]
            ],
            ['name' => 'close'],
        ]);
        ?>
    </div>

    <?php
    echo element("pager", [
        'data' => 'list',
        'per_page' => $per_page,
        'url' => $url,
        'reload_data' => []
    ]);
    ?>

</div>

<!-- 查看详情弹出窗口 -->
<el-dialog title="<?= lang("钱包详情") ?>" :visible.sync="dialogVisible" width="90%" :close-on-click-modal="false" top="20px">
    <div v-if="currentUser.user_phone">
        <div class="mb-3">
            <h5><?= lang("用户") ?>：{{ currentUser.user_phone }}</h5>
            <div class="row">
                <div class="col-md-2">
                    <small class="text-muted"><?= lang("总收入") ?>：</small><br>
                    <strong class="text-success">{{ currentUser.total_amount }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted"><?= lang("已确认收入") ?>：</small><br>
                    <strong class="text-info">{{ currentUser.in_amount }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted"><?= lang("待确认收入") ?>：</small><br>
                    <strong class="text-warning">{{ currentUser.wait_in_amount }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted"><?= lang("可提现") ?>：</small><br>
                    <strong class="text-primary">{{ currentUser.can_out_amount }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted"><?= lang("已提现") ?>：</small><br>

                    <strong>{{ currentUser.out_amount }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted"><?= lang("待审核提现") ?>：</small><br>
                    <strong class="text-danger">{{ currentUser.wait_out_amount }}</strong>
                </div>
            </div>
        </div>

        <el-tabs v-model="activeTab" @tab-click="handleTabClick">
            <!-- 收入记录 -->
            <el-tab-pane label="<?= lang("收入记录") ?>" name="in">
                <el-table :data="inRecords" v-loading="loading" style="width: 100%">
                    <el-table-column prop="order_num" label="<?= lang("订单号") ?>" width=""></el-table-column>
                    <el-table-column prop="desc" label="<?= lang("描述") ?>" width="180"></el-table-column>
                    <el-table-column prop="order_amount" label="<?= lang("订单金额") ?>" width="100">
                        <template slot-scope="scope">
                            <span class="text-info">{{ scope.row.order_amount }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="rate" label="<?= lang("比例") ?>" width="100">
                        <template slot-scope="scope">
                            <span>{{ scope.row.rate}}%</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="amount" label="<?= lang("收入金额") ?>" width="100">

                        <template slot-scope="scope">
                            <span class="text-success">{{ scope.row.amount }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="<?= lang("状态") ?>" width="130">
                        <template slot-scope="scope">
                            <el-tag :type="scope.row.status === 'wait' ? 'warning' : (scope.row.status === 'success' ? 'success' : 'danger')">
                                {{ scope.row.status_text }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_at_format" label="<?= lang("创建时间") ?>" width="180"></el-table-column>
                </el-table>

                <div class="mt-3 text-center">
                    <el-pagination class="mb5" background

                        @current-change="handleInPageChange"
                        :current-page="inPagination.page"
                        :page-size="inPagination.limit"
                        layout="prev, pager, next"
                        :total="inPagination.total">
                    </el-pagination>
                </div>
            </el-tab-pane>

            <!-- 提现记录 -->
            <el-tab-pane label="<?= lang("提现记录") ?>" name="out">
                <el-table :data="outRecords" v-loading="loading" style="width: 100%">
                    <el-table-column prop="order_num" label="<?= lang("订单号") ?>" width="190"></el-table-column>
                    <el-table-column prop="type" label="<?= lang("提现方式") ?>" width="100">
                        <template slot-scope="scope">
                            <img :src="scope.row.type_icon" alt="" width="40" height="40">
                        </template>
                    </el-table-column>
                    <el-table-column prop="desc" label="<?= lang("帐号") ?>" width="">
                         <template slot-scope="scope">
                            {{scope.row.account_info}} 
                         </template>
                    </el-table-column>
                    <el-table-column prop="amount" label="<?= lang("提现金额") ?>" width="100">
                        <template slot-scope="scope">
                            <span class="text-warning">{{ scope.row.amount }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="rate" label="<?= lang("比例") ?>" width="100">
                        <template slot-scope="scope">
                            <span>{{ scope.row.rate}}%</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="rate_amount" label="<?= lang("手续费") ?>" width="100">

                        <template slot-scope="scope">
                            <span class="text-danger">{{ scope.row.rate_amount }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="real_amount" label="<?= lang("实际到账") ?>" width="100">
                        <template slot-scope="scope">
                            <span class="text-success">{{ scope.row.real_amount }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="<?= lang("状态") ?>" width="130">

                        <template slot-scope="scope">
                            <el-tag :type="scope.row.status === 'wait' ? 'warning' : (scope.row.status === 'success' ? 'success' : 'danger')">
                                {{ scope.row.status_text }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_at_format" label="<?= lang("申请时间") ?>" width="180"></el-table-column>

                    <el-table-column label="<?= lang("操作") ?>" width="160" v-if="can_confirm">

                        <template slot-scope="scope">
                            <el-button
                                v-if="scope.row.status === 'wait'"
                                type="primary"
                                size="mini"
                                @click="confirmOut(scope.row)">
                                <?= lang("同意并打款") ?>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="mt-3 text-center">
                    <el-pagination class="mb5" background
                        @current-change="handleOutPageChange"
                        :current-page="outPagination.page"
                        :page-size="outPagination.limit"
                        layout="prev, pager, next"
                        :total="outPagination.total">
                    </el-pagination>
                </div>
            </el-tab-pane>
        </el-tabs>
    </div>
</el-dialog>

<?php view_footer() ?>