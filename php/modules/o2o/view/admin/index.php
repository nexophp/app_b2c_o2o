<?php
view_header('订单管理');
global $vue;
$url = '/o2o/admin/list';

use modules\order\model\OrderModel;

$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("detailDialogVisible", false);
$vue->data("paymentDialogVisible", false);
$vue->data("paymentInfoDialogVisible", false);
$vue->data("logisticDialogVisible", false);
$vue->data("logisticInfoDialogVisible", false);
$vue->data("statsData", "[]");
$vue->data("orderDetail", "[]");
$vue->data("paymentTypes", "[]");
$vue->data("paymentInfo", "[]");
$vue->data("logisticInfo", "[]");
$vue->data("logisticData", "[]");
$vue->data("logisticCompanies", "[]"); // 快递公司列表
$vue->data("form", [
    'id' => '',
    'status' => ''
]);
$vue->data("paymentForm", [
    'order_id' => '',
    'type' => '',      // 支付方式
    'amount' => ''     // 支付金额
]);
$vue->data("logisticForm", [
    'order_num' => '',
    'no' => '',
    'type' => '',
    'id' => ''
]);

// 使用OrderModel的状态选项和支付方式
$orderModel = new OrderModel();
$vue->data("statusOptions", $orderModel->getStatusOptions());
$vue->data("paymentTypes", $orderModel->getPaymentTypes());

$vue->created(["load()", "loadStats()", "loadPaymentTypes()", "loadLogisticCompanies()", "loop()"]);

$vue->method("load()", "
this.height = 'calc(100vh - 130px - " . get_config('admin_table_height') . "px)';
");

// 加载统计数据
$vue->method("loadStats()", "
ajax('/o2o/admin/stats', {}, function(res) {
    if (res.code == 0) {
        app.statsData = res.data;
    }
});
");

$vue->method("loadPaymentTypes()", "
ajax('/o2o/admin/payment-types', {}, function(res) {
    if (res.code == 0) {
        app.paymentTypes = res.data; // 修改：直接使用res.data
    }
});
");

// 加载快递公司列表
$vue->method("loadLogisticCompanies()", "
ajax('/logistic/api/support', {}, function(res) {
    if (res.code == 0) {
        app.logisticCompanies = res.data;
    }
});
");


// 添加支付信息
$vue->method("addPayment(row)", " 
ajax('/o2o/admin/detail', {id: row.id}, function(res) {
    if (res.code == 0) { 
        app.paymentForm.order_id = row.id;
        app.paymentForm.title = '';
        app.paymentForm.type = '';
        app.paymentForm.amount = res.data.real_amount;  
        app.paymentForm.remark = '';
        app.paymentDialogVisible = true;
    }
});
");

// 查看支付信息
$vue->method("viewPaymentInfo(row)", "
ajax('/o2o/admin/payment-info', {order_id: row.id}, function(res) {
    if (res.code == 0) {
        app.paymentInfo = res.data;  
        app.paymentInfoDialogVisible = true;
    }
});
");

// 查看订单详情
$vue->method("viewDetail(row)", " 
ajax('/o2o/admin/detail', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.orderDetail = res.data;
        app.detailDialogVisible = true; 
    }
});
");

// 更新订单状态
$vue->method("updateStatus(row)", "
    app.form.id = row.id;
    app.form.status = row.status;
    app.dialogVisible = true;
");

// 提交状态更新
$vue->method("submitStatus()", "
ajax('/o2o/admin/update-status', app.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.dialogVisible = false;
        app.load_list();
    }
});
");
// 提交支付信息
$vue->method("submitPayment()", "
ajax('/o2o/admin/add-payment', app.paymentForm, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.paymentDialogVisible = false;
        app.load_list();
    }
});
");
$vue->data("activeTab", "payment");
// 查看支付信息
$vue->method("viewPaymentInfo(row)", "
ajax('/o2o/admin/payment-info', {order_id: row.id}, function(res) {
    if (res.code == 0) {
        app.paymentInfo = res.data; 
        app.activeTab = 'payment';
        app.paymentInfoDialogVisible = true;  
        app.activeTab = 'payment';
        app.paymentInfoDialogVisible = true;
    }
});

");


// 添加物流信息
$vue->method("addLogistic(row)", "  
layer.confirm('确定发货吗？', {
    title:'发货',
    btn: ['确定', '取消'] //按钮
}, function(){ 
    ajax('/o2o/admin/add-logistic', {id: row.id}, function(res) {
        " . vue_message() . "
        app.load_list();
        layer.closeAll();
    });  
});


");
$vue->method("doComplete(row)", "  
layer.confirm('确定订单完成吗？', {
    title:'订单完成',
    btn: ['确定', '取消'] //按钮
}, function(){ 
    ajax('/o2o/admin/complete', {id: row.id}, function(res) {
        " . vue_message() . "
        app.load_list();
        layer.closeAll();
    });  
});


");


// 权限控制
$vue->data("can_view", has_access('o2o/admin/detail'));
$vue->data("can_edit", has_access('o2o/admin/updateStatus'));
$vue->data("can_payment", has_access('o2o/admin/addPayment'));



// 添加处理下拉菜单命令的方法
$vue->method("handleCommand(command)", " 
let action = command.action;
let row = command.row;
switch(action) {
    case 'viewDetail':
        this.viewDetail(row); 
        break;
    case 'updateStatus':
        this.updateStatus(row);
        break;
    case 'addPayment':
        this.addPayment(row);
        break;
    case 'viewPaymentInfo':
        this.viewPaymentInfo(row);
        break; 
}
");
/**
 * 定时加载 load_list 
 */
$vue->method("loop()", " 
    this.loop_data = setInterval(() => {
        this.load_list();
    }, 5000);
");


?>

<div class="container-fluid">
    <!-- 统计卡片 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="row" v-if="statsData.length > 0">
                <div class="col-md-2" v-for="(stat, index) in statsData" :key="index">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">{{ stat.label }}</h5>
                            <p class="card-text">
                                <strong>{{ stat.count }}</strong> 单<br>
                                <small class="text-muted">￥{{ stat.amount }}</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 订单列表 -->
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
                    'name' => 'order_num',
                    'attr_element' => ['placeholder' => lang('订单号')],
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'value' => [
                        ['label' => lang('全部'), 'value' => ''],
                        ['label' => lang('待支付'), 'value' => 'wait'],
                        ['label' => lang('已支付'), 'value' => 'paid'],
                        ['label' => lang('已发货'), 'value' => 'shipped'],
                        ['label' => lang('已完成'), 'value' => 'complete'],
                        ['label' => lang('已取消'), 'value' => 'cancel']
                    ],
                    'attr_element' => ['placeholder' => lang('订单状态')],
                ],
                [
                    'type' => 'date',
                    'name' => 'start_date',
                    'attr_element' => ['placeholder' => lang('开始日期')],
                ],
                [
                    'type' => 'date',
                    'name' => 'end_date',
                    'attr_element' => ['placeholder' => lang('结束日期')],
                ],
            ]);
            ?>

            <?php
            echo element('table', [
                ['name' => 'open', ':data' => 'list', ':height' => 'height'],
                ['name' => 'column', 'prop' => 'order_num', 'label' => lang('订单号'), 'width' => '200'],
                [
                    'name' => 'column',
                    'prop' => 'real_amount',
                    'label' => lang('实付金额'),
                    'width' => '',
                    'tpl' => [
                        ['type' => 'html', 'html' => '
                            <el-tooltip effect="dark" placement="top">
                                <div slot="content">
                                    <div style="line-height: 1.5;">
                                        <div><strong>订单金额：</strong>￥{{ scope.row.amount }}</div>
                                        <div><strong>应付金额：</strong>￥{{ scope.row.real_amount }}</div>
                                        <div><strong>实收金额：</strong>￥{{ scope.row.real_get_amount }}</div>
                                        <div v-if="scope.row.has_refund_amount > 0"><strong>已退款金额：</strong>￥{{ scope.row.has_refund_amount }}</div>
                                        
                                    </div>
                                </div>
                                <span style="cursor: pointer; border-bottom: 1px dashed #409EFF;">
                                    <span class="text-success">￥{{ scope.row.real_amount }}</span>
                                </span>
                            </el-tooltip>
                        ']
                    ]
                ],
                ['name' => 'column', 'prop' => 'real_get_amount', 'label' => lang('实收金额'), 'width' => ''],
                [
                    'name' => 'column',
                    'prop' => 'status',
                    'label' => lang('状态'),
                    'width' => '160',
                    'tpl' => [[
                        'type' => 'html',
                        "html" => "
                            <el-tag v-if='scope.row.status == \"wait\"' type='warning'>" . lang('待支付') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"paid\"' type='success'>" . lang('已支付') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"shipped\"' type='primary'>" . lang('已发货') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"complete\"' type='success'>" . lang('已完成') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"cancel\"' type='danger'>" . lang('已取消') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"refund\"' type='danger'>" . lang('已退款') . "</el-tag>
                            <el-tag v-else>{{ scope.row.status_text }}</el-tag>
                        "
                    ]]
                ],
                ['name' => 'column', 'prop' => 'created_at_format', 'label' => lang('创建时间'), 'width' => '200'],
                [
                    'name' => 'column',
                    'label' => lang('操作'),
                    'fixed' => 'right',
                    'width' => '200',
                    'tpl' => [
                        ['type' => 'html', 'html' => ' 
                            <el-button 
                                v-if="can_view" 
                                type="text" 
                                size="small" 
                                @click="viewDetail(scope.row)"
                            >
                                <i class="el-icon-view"></i>' . lang("查看") . '
                            </el-button> 
                            <el-button 
                                v-if="can_payment && scope.row.status == \'wait\'" 
                                type="text" 
                                size="small" 
                                @click="addPayment(scope.row)"
                            >
                                <i class="el-icon-money"></i>' . lang("支付") . '
                            </el-button>
                             
                            
                            <el-button 
                                v-if="can_edit && (scope.row.status == \'paid\' )" 
                                type="text" 
                                size="small" 
                                @click="addLogistic(scope.row)"
                            >
                                <i class="el-icon-truck"></i>' . lang("发货") . '</el-button>
                            
                           <el-button 
                                v-if="can_edit && (scope.row.status == \'shipped\' )" 
                                type="text" 
                                style="color:red;"
                                size="small" 
                                @click="doComplete(scope.row)"
                            >
                                ' . lang("确认订单完成") . '</el-button> 

                        ']
                    ]
                ],
                ['name' => 'close'],
            ]);
            ?>
        </div>
    </div>
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

<!-- 订单详情弹窗 -->
<el-dialog title="<?php echo lang('订单详情'); ?>" top="20px" :visible.sync="detailDialogVisible" width="80%">
    <div v-if="orderDetail.id">
        <div class="row">
            <div class="col-md-6">
                <h5><?php echo lang('订单信息'); ?></h5>
                <table class="table table-bordered mt-2">
                    <tr>
                        <td><?php echo lang('订单号'); ?></td>
                        <td>{{ orderDetail.order_num }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('订单状态'); ?></td>
                        <td>{{ orderDetail.status_text }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('订单金额'); ?></td>
                        <td>￥{{ orderDetail.amount }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('实付金额'); ?></td>
                        <td>￥{{ orderDetail.real_amount }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('可退金额'); ?></td>
                        <td>￥{{ orderDetail.can_refund_amount }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('已退金额'); ?></td>
                        <td>￥{{ orderDetail.has_refund_amount }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('创建时间'); ?></td>
                        <td>{{ orderDetail.created_at_format }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5><?php echo lang('收货信息'); ?></h5>
                <table class="table table-bordered mt-2">
                    <tr>
                        <td><?php echo lang('收货人'); ?></td>
                        <td>{{ orderDetail.name }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('联系电话'); ?></td>
                        <td>{{ orderDetail.phone }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('收货地址'); ?></td>
                        <td>{{ orderDetail.address }}</td>
                    </tr>
                </table>
                <h5><?php echo lang('备注'); ?></h5>
                <div class="mt-2">
                    {{ orderDetail.desc }}
                </div>
            </div>
        </div>

        <h5><?php echo lang('商品明细'); ?></h5>
        <table class="table table-bordered mt-2">
            <thead>
                <tr>
                    <th><?php echo lang('商品名称'); ?></th>
                    <th><?php echo lang('单价'); ?></th>
                    <th><?php echo lang('数量'); ?></th>
                    <th><?php echo lang('小计'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in orderDetail.items" :key="item.id">
                    <td>
                        <div style="display: flex; align-items: center;">
                            <div style="margin-right: 10px;">
                                <img :src="item.image" alt="" width="50" height="50">
                            </div>
                            <div>
                                {{ item.title }}
                                <p v-if="item.spec||''">{{ item.spec }}</p>
                                <p v-if="item.attr||''">{{ item.attr }}</p>
                            </div>
                        </div>
                    </td>
                    <td>￥{{ item.price }}</td>
                    <td>{{ item.num }}</td>
                    <td>￥{{ item.amount }}</td>
                </tr>
            </tbody>
        </table>

        <h5 v-if="orderDetail.payment_info && orderDetail.payment_info.length > 0"><?php echo lang('支付信息'); ?></h5>
        <table class="table table-bordered mt-2" v-if="orderDetail.payment_info && orderDetail.payment_info.length > 0">
            <thead>
                <tr>
                    <th><?php echo lang('支付方式'); ?></th>
                    <th><?php echo lang('支付标题'); ?></th>
                    <th><?php echo lang('支付金额'); ?></th>
                    <th><?php echo lang('备注'); ?></th>
                    <th><?php echo lang('支付时间'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="payment in orderDetail.payment_info" :key="payment.id">
                    <td>{{ payment.type_info }}</td>
                    <td>{{ payment.title }}</td>
                    <td>￥{{ payment.amount }}</td>
                    <td>{{ payment.remark }}</td>
                    <td>{{ payment.created_at_format }}</td>
                </tr>
            </tbody>
        </table>

    </div>
</el-dialog>

<!-- 状态更新弹窗 -->
<el-dialog title="<?php echo lang('更新订单状态'); ?>" :visible.sync="dialogVisible" width="400px">
    <el-form :model="form">
        <el-form-item label="<?php echo lang('订单状态'); ?>">
            <el-select v-model="form.status" placeholder="<?php echo lang('请选择状态'); ?>">
                <el-option label="<?php echo lang('待支付'); ?>" value="wait"></el-option>
                <el-option label="<?php echo lang('已支付'); ?>" value="paid"></el-option>
                <el-option label="<?php echo lang('已发货'); ?>" value="shipped"></el-option>
                <el-option label="<?php echo lang('已完成'); ?>" value="completed"></el-option>
                <el-option label="<?php echo lang('已取消'); ?>" value="cancelled"></el-option>
            </el-select>
        </el-form-item>
    </el-form>
    <div slot="footer">
        <el-button @click="dialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submitStatus()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<!-- 添加支付信息弹窗 -->
<el-dialog title="<?php echo lang('添加支付信息'); ?>" :visible.sync="paymentDialogVisible" width="500px">
    <el-form :model="paymentForm" label-width="100px">
        <el-form-item label="<?php echo lang('支付方式'); ?>" required>
            <el-select v-model="paymentForm.type" placeholder="<?php echo lang('请选择支付方式'); ?>" style="width: 200px;">
                <el-option v-for="type in paymentTypes" :key="type.value" :label="type.label" :value="type.value"></el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="<?php echo lang('支付金额'); ?>" required>
            <el-input v-model="paymentForm.amount" disabled style="width: 200px;">
                <template slot="append">
                    <?= lang('元'); ?>
                </template>
            </el-input>
        </el-form-item>
    </el-form>
    <div slot="footer">
        <el-button @click="paymentDialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submitPayment()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<!-- 支付信息查看弹窗 -->
<el-dialog title="<?php echo lang('支付信息'); ?>" :visible.sync="paymentInfoDialogVisible" width="80%">
    <el-tabs v-model="activeTab">
        <!-- 支付信息选项卡 -->
        <el-tab-pane label="<?php echo lang('支付信息'); ?>" name="payment">
            <table class="table table-bordered" v-if="paymentInfo && paymentInfo.length > 0">
                <thead>
                    <tr>
                        <th><?php echo lang('支付标题'); ?></th>
                        <th><?php echo lang('支付方式'); ?></th>
                        <th><?php echo lang('支付金额'); ?></th>
                        <th><?php echo lang('备注'); ?></th>
                        <th><?php echo lang('支付时间'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="payment in paymentInfo" :key="payment.id">
                        <td>{{ payment.title }}</td>
                        <td>{{ payment.type_info }}</td>
                        <td>￥{{ payment.amount }}</td>
                        <td>{{ payment.remark }}</td>
                        <td>{{ payment.created_at_format }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="text-center py-4">
                <p><?php echo lang('暂无支付信息'); ?></p>
            </div>
        </el-tab-pane>

    </el-tabs>
</el-dialog>



<?php view_footer(); ?>