<?php
view_header('售后');
global $vue;
$url = '/order/refund/list';
 
use modules\order\model\OrderModel;
 
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("detailDialogVisible", false);
$vue->data("paymentDialogVisible", false);
$vue->data("refundDialogVisible", false);
$vue->data("refundApproveDialogVisible", false);
$vue->data("detailDialogVisible", false);
$vue->data("logisticInfoDialogVisible", false);
$vue->data("logisticInfo", null);
$vue->data("paymentInfoDialogVisible", false);
$vue->data("logisticDialogVisible", false);
$vue->data("logisticInfoDialogVisible", false);
// 移除统计数据
$vue->data("orderDetail", []);
$vue->data("paymentTypes", []);
$vue->data("paymentInfo", []);
$vue->data("refundList", []);
$vue->data("logisticInfo", []);
$vue->data("orderList", []); // 订单列表
$vue->data("orderItems", []); // 订单商品列表
$vue->data("selectedOrder", null); // 选中的订单
$vue->data("orderSelectDialogVisible", false); // 选择订单弹窗
$vue->data("itemSelectDialogVisible", false); // 选择商品弹窗
$vue->data("logisticData", []);
$vue->data("logisticCompanies", []); // 快递公司列表
$vue->data("logisticForm", [
    'refund_id' => '',
    'order_num' => '',
    'no' => '',
    'type' => '',
    'id' => ''
]);
$vue->data("logisticDialogVisible", false); // 物流信息弹窗
$vue->data("form", [
    'id' => '',
    'status' => ''
]);
$vue->data("paymentForm", [
    'order_id' => '',
    'type' => '',      // 支付方式
    'amount' => ''     // 支付金额
]);
$vue->data("refundForm", [
    'order_id' => '',
    'order_num' => '',
    'amount' => '',
    'reason' => '',
    'desc' => '',
    'type' => 'refund_only',
    'refund_type' => 'full', // full: 整单退款, partial: 部分商品退款
    'selected_items' => [], // 选中的商品
    'receiver_name' => '', // 换货收货人
    'receiver_phone' => '', // 换货收货电话
    'receiver_address' => '', // 换货收货地址
    'items' => []
]);
$vue->data("refundApproveForm", [
    'id' => '',
    'status' => 'approved',
    'remark' => ''
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

$vue->created(["load()",  "loadLogisticCompanies()"]);

$vue->method("load()", "
this.height = 'calc(100vh - 130px - " . get_config('admin_table_height') . "px)';
");

// 加载快递公司列表
$vue->method("loadLogisticCompanies()", "
ajax('/logistic/api/support', {}, function(res) {
    if (res.code == 0) {
        app.logisticCompanies = res.data;
    }
});
");
 
$vue->data("statusOptions", [
    ['value' => 'pending', 'label' => lang('待审核')],
    ['value' => 'approved', 'label' => lang('已同意')],
    ['value' => 'rejected', 'label' => lang('已拒绝')],
    ['value' => 'processing', 'label' => lang('处理中')],
    ['value' => 'complete', 'label' => lang('已完成')],
    ['value' => 'cancel', 'label' => lang('已取消')]
]); 

// 显示创建售后弹窗
$vue->method("showCreateRefundDialog()", "
app.refundForm.order_id = '';
app.refundForm.order_num = '';
app.refundForm.amount = '';
app.refundForm.reason = '';
app.refundForm.items = [];
app.refundDialogVisible = true;
");

// 查看售后详情
$vue->method("viewRefundDetail(row)", "
ajax('/order/refund/detail', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.orderDetail = res.data;
        app.detailDialogVisible = true;
    } else {
        app.\$message.error(res.msg || '加载详情失败');
    }
});
");

// 查看退货物流
$vue->method("viewReturnLogistics(row)", "
ajax('/order/refund/logistics', {refund_id: row.id}, function(res) {
    if (res.code == 0) {
        app.logisticInfo = res.data;
        app.logisticData = res.data.data || {};
        app.logisticInfoDialogVisible = true;
    } else {
        app.\$message.warning('暂无退货物流信息');
    }
});
"); 
 

// 审核退款
$vue->method("approveRefund(refundId)", "
app.refundApproveForm.id = refundId;
app.refundApproveForm.status = 'approved';
app.refundApproveForm.remark = '';
app.refundApproveDialogVisible = true;
");

// 提交退款审核
$vue->method("submitRefundApprove()", "
ajax('/order/refund/approve', app.refundApproveForm, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.refundApproveDialogVisible = false;
        app.load_list();
    }
});
");
 

// 权限控制
$vue->data("can_view", has_access('order/refund/detail'));
$vue->data("can_create", has_access('order/refund/create'));
$vue->data("can_approve", has_access('order/refund/approve'));
$vue->data("can_logistics", has_access('order/refund/logistics'));



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
    case 'createRefund':
        this.createRefund(row);
        break;
}
");
?>

<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">售后管理</h4>
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
                    'attr_element' => ['placeholder' => lang('售后单号')],
                ], 
                [
                    'type' => 'select',
                    'name' => 'status',
                    'value' => [
                        ['label' => lang('全部状态'), 'value' => ''],
                        ['label' => lang('待审核'), 'value' => 'pending'],
                        ['label' => lang('已通过'), 'value' => 'approved'],
                        ['label' => lang('已拒绝'), 'value' => 'rejected'],
                        ['label' => lang('已完成'), 'value' => 'complete'],
                        ['label' => lang('已取消'), 'value' => 'cancel']
                    ],
                    'attr_element' => ['placeholder' => lang('售后状态')],
                ],
            ]);
            ?>
            
            <?php
            echo element('table', [
                ['name' => 'open', ':data' => 'list',':height'=>'height'],
                ['name' => 'column', 'prop' => 'order_num', 'label' => lang('售后单号'), 'width' => '180'],
                ['name' => 'column', 'prop' => 'order.order_num', 'label' => lang('订单号'), 'width' => '180'],  
                ['name' => 'column', 'prop' => 'amount', 'label' => lang('售后金额'), 'width' => '120',
                    'tpl' => [
                        ['type' => 'html', 'html' => '
                            <span class="text-danger" v-if="scope.row.amount>0">￥{{ scope.row.amount }}</span>
                        ']
                    ]
                ],
                ['name' => 'column', 'prop' => 'type', 'label' => lang('售后类型'), 'width' => '120',
                    'tpl' => [
                        ['type' => 'html', 'html' => '
                            <el-tag v-if="scope.row.type == \'refund\'" type="danger">退款</el-tag> 
                            <el-tag v-else-if="scope.row.type == \'return\'" type="success">退货退款</el-tag>
                            <el-tag v-else-if="scope.row.type == \'exchange\'" type="info">换货</el-tag>
                            <el-tag v-else>{{ scope.row.type }}</el-tag>
                        ']
                    ]
                ],
                ['name' => 'column', 'prop' => 'reason', 'label' => lang('售后原因'), 'width' => ''],
                [
                    'name' => 'column',
                    'prop' => 'status',
                    'label' => lang('状态'),
                    'width' => '120',
                    'tpl' => [[
                        'type' => 'html',
                        "html" => "
                            <el-tag v-if='scope.row.status == \"wait\"' type='warning'>" . lang('待审核') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"approved\"' type='success'>" . lang('已通过') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"rejected\"' type='danger'>" . lang('已拒绝') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"completed\"' type='info'>" . lang('已完成') . "</el-tag>
                            <el-tag v-else-if='scope.row.status == \"cancel\"' type='info'>" . lang('已取消') . "</el-tag>
                            <el-tag v-else>{{ scope.row.status_text }}</el-tag>
                        "
                    ]]
                ],
                ['name' => 'column', 'prop' => 'created_at_format', 'label' => lang('申请时间'), 'width' => '180'],
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
                                @click="viewRefundDetail(scope.row)"
                            >
                                <i class="el-icon-view"></i>'.lang("查看详情").'</el-button> 
                            <el-button 
                                v-if="can_approve && scope.row.status == \'wait\'" 
                                type="text" 
                                size="small" 
                                @click="approveRefund(scope.row.id)"
                            >
                                <i class="el-icon-check"></i>'.lang("审核").'</el-button>
                            
                            <el-button 
                                v-if="scope.row.type == \'return_refund\' && scope.row.return_status == \'wait_return\' " 
                                type="text" 
                                size="small" 
                                @click="viewReturnLogistics(scope.row)"
                            >
                                <i class="el-icon-truck"></i>'.lang("退货物流").'</el-button>
                            
                            <el-button 
                                v-if="scope.row.type == \'exchange\' && scope.row.ship_status == \'wait_ship\' && can_logistics" 
                                type="text" 
                                size="small" 
                                @click="addLogistic(scope.row)"
                            >
                                <i class="el-icon-plus"></i>'.lang("发货").'</el-button>

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
  
<!-- 退款审核弹窗 -->
<el-dialog title="<?php echo lang('退款审核'); ?>" :visible.sync="refundApproveDialogVisible" width="500px">
    <el-form :model="refundApproveForm" label-width="100px">
        <el-form-item label="<?php echo lang('审核状态'); ?>">
            <el-select v-model="refundApproveForm.status" placeholder="<?php echo lang('请选择审核状态'); ?>">
                <el-option label="<?php echo lang('审核通过'); ?>" value="approved"></el-option>
                <el-option label="<?php echo lang('审核拒绝'); ?>" value="rejected"></el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="<?php echo lang('审核备注'); ?>">
            <el-input v-model="refundApproveForm.remark" type="textarea" placeholder="<?php echo lang('请输入审核备注'); ?>"></el-input>
        </el-form-item>
    </el-form>
    <div slot="footer">
        <el-button @click="refundApproveDialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submitRefundApprove()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>
  

<!-- 添加物流信息弹窗 -->
<el-dialog :title="logisticForm.id ? '<?php echo lang('编辑物流信息'); ?>' : '<?php echo lang('添加物流信息'); ?>'" :visible.sync="logisticDialogVisible" width="500px">
    <el-form :model="logisticForm" label-width="100px">
        <el-form-item label="<?php echo lang('订单号'); ?>">
            <el-input v-model="logisticForm.order_num" disabled></el-input>
        </el-form-item>
        <el-form-item label="<?php echo lang('物流单号'); ?>" required>
            <el-input v-model="logisticForm.no" placeholder="<?php echo lang('请输入物流单号'); ?>"></el-input>
        </el-form-item>
        <el-form-item label="<?php echo lang('物流公司'); ?>" required>
            <el-select v-model="logisticForm.type" placeholder="<?php echo lang('请选择物流公司'); ?>">
                <el-option 
                    v-for="company in logisticCompanies" 
                    :key="company.value" 
                    :label="company.label" 
                    :value="company.value">
                </el-option>
            </el-select>
        </el-form-item>
    </el-form>
    <div slot="footer">
        <el-button @click="logisticDialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submitLogistic()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<!-- 售后详情弹窗 -->
<el-dialog 
  title="<?= lang('售后详情') ?>" 
  :visible.sync="detailDialogVisible" 
  width="80%"
  top="20px"
>
  <div v-if="orderDetail">
    <!-- 基础信息表格 -->
    <el-table 
      :data="[orderDetail]" 
      border 
      style="margin-bottom: 20px;"
      :show-header="false"
    >
      <el-table-column prop="refund_no" label="<?= lang('售后单号') ?>">
        <template slot-scope="{row}">
          <div style="display: flex; align-items: center;">
            <span style="font-weight: bold; margin-right: 10px;"><?= lang('售后单号') ?>:</span>
            <el-tag type="info" size="small">{{ row.order_num }}</el-tag>
          </div>
        </template>
      </el-table-column>
      <el-table-column prop="order_no" label="<?= lang('订单号') ?>">
        <template slot-scope="{row}">
          <div style="display: flex; align-items: center;" v-if="row.order">
            <span style="font-weight: bold; margin-right: 10px;"><?= lang('订单号') ?>:</span>
            {{ row.order.order_num||'' }}
          </div>
        </template>
      </el-table-column>
      <el-table-column prop="type_text" label="<?= lang('售后类型') ?>">
        <template slot-scope="{row}">
          <div style="display: flex; align-items: center;">
            <span style="font-weight: bold; margin-right: 10px;"><?= lang('售后类型') ?>:</span>
            <el-tag :type="row.type === 'refund' ? 'danger' : 'warning'" size="small">
              {{ row.type_text }}
            </el-tag>
          </div>
        </template>
      </el-table-column>
      <el-table-column prop="amount" label="<?= lang('售后金额') ?>">
        <template slot-scope="{row}">
          <div style="display: flex; align-items: center;">
            <span style="font-weight: bold; margin-right: 10px;"><?= lang('售后金额') ?>:</span>
            <span style="color: #F56C6C;" v-if="row.amount>0">¥{{ row.amount }}</span>
          </div>
        </template>
      </el-table-column>
    </el-table>

    <!-- 商品信息表格 -->
    <el-table 
      :data="orderDetail.items" 
      border 
      style="margin-bottom: 20px;"
      v-if="orderDetail.items"
    >
      <el-table-column 
        prop="title" 
        label="<?= lang('退货商品') ?>" 
        width=""
      >
        <template slot-scope="{row}">
          <div style="display: flex; align-items: center;">
            <el-image 
              :src="row.image" 
              style="width:60px;height:60px;margin-right:10px;"
              :preview-src-list="[row.image]"
            ></el-image>
            <span>{{ row.title }}</span>
          </div>
        </template>
      </el-table-column>
      <el-table-column prop="price" label="<?= lang('单价') ?>" width="120" align="center">
        <template slot-scope="{row}">
          ¥{{ row.price }}
        </template>
      </el-table-column>
      <el-table-column prop="num" label="<?= lang('数量') ?>" width="100" align="center">
        {{ orderDetail.num }}
      </el-table-column>
    </el-table>

    <!-- 详细说明 -->
    <el-card v-if="orderDetail.desc" style="margin-bottom: 20px;">
      <div slot="header">
        <span style="font-weight: bold;"><?= lang('详细说明') ?></span>
      </div>
      <div style="padding: 15px;">
        {{ orderDetail.desc }}
      </div>
    </el-card>

    <!-- 图片凭证 -->
    <el-card v-if="orderDetail.images && orderDetail.images.length">
      <div slot="header">
        <span style="font-weight: bold;"><?= lang('凭证图片') ?></span>
      </div>
      <div style="padding: 15px; display: flex; flex-wrap: wrap;">
        <el-image 
          v-for="(img, idx) in orderDetail.images" 
          :key="idx"
          :src="img"
          style="width:120px;height:120px;margin-right:10px;margin-bottom:10px;"
          :preview-src-list="orderDetail.images"
        ></el-image>
      </div>
    </el-card>
  </div>
 
</el-dialog>

<!-- 退货物流弹窗 -->
<el-dialog title="<?php echo lang('退货物流信息'); ?>" :visible.sync="logisticInfoDialogVisible" width="600px">
    <div v-if="logisticInfo">
        <el-descriptions title="<?php echo lang('物流信息'); ?>" :column="2" border>
            <el-descriptions-item label="<?php echo lang('快递公司'); ?>">{{ logisticInfo.company_name }}</el-descriptions-item>
            <el-descriptions-item label="<?php echo lang('快递单号'); ?>">{{ logisticInfo.tracking_no }}</el-descriptions-item>
            <el-descriptions-item label="<?php echo lang('发货时间'); ?>">{{ logisticInfo.ship_time }}</el-descriptions-item>
            <el-descriptions-item label="<?php echo lang('物流状态'); ?>">{{ logisticInfo.status_text }}</el-descriptions-item>
        </el-descriptions>
        <div v-if="logisticInfo.traces && logisticInfo.traces.length > 0" style="margin-top: 20px;">
            <h4><?php echo lang('物流轨迹'); ?></h4>
            <el-timeline>
                <el-timeline-item v-for="trace in logisticInfo.traces" :key="trace.time" :timestamp="trace.time">
                    {{ trace.desc }}
                </el-timeline-item>
            </el-timeline>
        </div>
    </div>
    <div slot="footer">
        <el-button @click="logisticInfoDialogVisible = false"><?php echo lang('关闭'); ?></el-button>
    </div>
</el-dialog>

<!-- 查看物流信息弹窗 -->
<el-dialog title="<?php echo lang('物流信息'); ?>" top="20px" :visible.sync="logisticInfoDialogVisible" width="800px">
    <div v-if="logisticInfo.id">
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="mb-2"><?php echo lang('物流基本信息'); ?></h6>
                <table class="table table-bordered">
                    <tr><td><?php echo lang('物流单号'); ?></td><td>{{ logisticInfo.no }}</td></tr>
                    <tr><td><?php echo lang('物流公司'); ?></td><td>{{ logisticInfo.type }}</td></tr>
                    <tr><td><?php echo lang('物流状态'); ?></td><td>{{ logisticInfo.data.status || '' }}</td></tr>
                    <tr><td><?php echo lang('创建时间'); ?></td><td>{{ logisticInfo.created_at_format }}</td></tr>
                </table>
            </div>
            <div class="col-md-6" v-if="logisticData.title">
                <h6 class="mb-2"><?php echo lang('快递公司信息'); ?></h6>
                <table class="table table-bordered">
                    <tr><td><?php echo lang('快递公司'); ?></td><td>{{ logisticData.title }}</td></tr>
                    <tr><td><?php echo lang('官网'); ?></td><td>{{ logisticData.site_url }}</td></tr>
                    <tr><td><?php echo lang('客服电话'); ?></td><td>{{ logisticData.site_phone }}</td></tr>
                    <tr><td><?php echo lang('配送员电话'); ?></td><td>{{ logisticData.phone }}</td></tr>
                    <tr><td><?php echo lang('运输时长'); ?></td><td>{{ logisticData.take_time }}</td></tr>
                </table>
            </div>
        </div>
        
        <div v-if="logisticData.list && logisticData.list.length > 0">
            <h6 class="mb-2"><?php echo lang('物流轨迹'); ?></h6>
            <el-timeline>
                <el-timeline-item 
                    v-for="(item, index) in logisticData.list" 
                    :key="index"
                    :timestamp="item.time"
                    placement="top"
                >
                    <el-card>
                        <h6>{{ item.status }}</h6>
                        <p>{{ item.title }}</p>
                    </el-card>
                </el-timeline-item>
            </el-timeline>
        </div>
        
        <div class="text-center mt-3">
            <el-button type="primary" @click="addLogistic({order_num: logisticInfo.order_num}, logisticInfo)"><?php echo lang('编辑物流'); ?></el-button>
            <el-button 
                v-if="logisticInfo.status != 'complete' && logisticData.status == '已签收'" 
                @click="updateLogisticStatus(logisticInfo.id, 'complete')" 
                type="success"
            >
                <i class="el-icon-check"></i> <?php echo lang('标记为已签收'); ?>
            </el-button>
        </div>
    </div>
</el-dialog>

<?php view_footer(); ?>
 