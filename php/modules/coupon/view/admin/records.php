<?php
add_css("
.info-item {
    margin-bottom: 10px;
}

.info-item label {
    font-weight: bold;
    color: #606266;
}

.box-card {
    margin-bottom: 20px;
}
");
view_header(lang('优惠券记录'));

global $vue;

$vue->data("coupon_id", $_GET['coupon_id'] ?? 0);
$vue->data("activeTab", "receive");
$vue->data("receiveList", []);
$vue->data("usedList", []);
$vue->data("couponInfo", []);
$vue->data("receivePage", 1);
$vue->data("usedPage", 1);
$vue->data("receiveTotal", 0);
$vue->data("usedTotal", 0);
$vue->data("pageSize", 20);

$vue->created(["loadCouponInfo()", "loadReceiveList()"]);

$vue->method("loadCouponInfo()", "
    ajax('/coupon/admin/detail', {id: this.coupon_id}, function(res) {
        if (res.code == 0) {
            app.couponInfo = res.data;
        }
    });
");

$vue->method("loadReceiveList(page)", "
    if (page) this.receivePage = page;
    ajax('/coupon/admin/receive-list', {
        coupon_id: this.coupon_id,
        page: this.receivePage,
        pre_page: this.pageSize
    }, function(res) { 
        app.receiveList = res.data;
        app.receiveTotal = res.total; 
    });
");

$vue->method("loadUsedList(page)", "
    if (page) this.usedPage = page;
    ajax('/coupon/admin/used-list', {
        coupon_id: this.coupon_id,
        page: this.usedPage,
        pre_page: this.pageSize
    }, function(res) { 
        app.usedList = res.data;
        app.usedTotal = res.total; 
    });
");

$vue->method("handleTabClick(tab)", "
    if (tab.name === 'used' && this.usedList.length === 0) {
        this.loadUsedList();
    }
");

$vue->method("handleReceivePageChange(page)", "
    this.loadReceiveList(page);
");

$vue->method("handleUsedPageChange(page)", "
    this.loadUsedList(page);
");

$vue->method("formatStatus(status)", "
    switch(status) {
        case 1: return {text: '未使用', type: 'success'};
        case 2: return {text: '已使用', type: 'info'};
        case -1: return {text: '已过期', type: 'warning'}; 
    }
");

?>

<div id="app">
    <!-- 优惠券信息 -->
    <el-card class="box-card" style="margin-bottom: 20px;">
        <div slot="header" class="clearfix">
            <span>优惠券信息</span>
        </div>
        <div v-if="couponInfo.id">
            <el-row :gutter="20">
                <el-col :span="6">
                    <div class="info-item">
                        <label>优惠券名称：</label>
                        <span>{{ couponInfo.name }}</span>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="info-item">
                        <label>类型：</label>
                        <span>{{ couponInfo.type_text }}</span>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="info-item">
                        <label>面值：</label>
                        <span>{{ couponInfo.value_text }}</span>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="info-item">
                        <label>状态：</label>
                        <el-tag :type="couponInfo.status == 1 ? 'success' : 'danger'">
                            {{ couponInfo.status == 1 ? '启用' : '禁用' }}
                        </el-tag>
                    </div>
                </el-col>
            </el-row>
        </div>
    </el-card>

    <el-card class="box-card" style="margin-bottom: 20px;" >
        <div slot="header" class="clearfix">
            <span>适用商品</span>
        </div>
        <div v-if="couponInfo.products && couponInfo.products.length>0">
            <el-tag v-for="item in couponInfo.products_info"  type="primary" style="margin-right: 10px;margin-bottom:10px;">{{ item.title }}</el-tag>
        </div>
        <div v-else>
            全部商品
        </div>
    </el-card>

    <!-- 记录列表 -->
    <el-card class="box-card">
        <el-tabs v-model="activeTab" @tab-click="handleTabClick">
            <!-- 领取记录 -->
            <el-tab-pane label="领取记录" name="receive">
                <el-table :data="receiveList" border style="width: 100%">
                    <el-table-column prop="id" label="ID" width="80"></el-table-column>
                    <el-table-column prop="user_id" label="用户ID" width="100"></el-table-column>
                    <el-table-column prop="user_info.phone" label="用户名" width="220"></el-table-column>
                    <el-table-column prop="status" label="状态" width="100">
                        <template slot-scope="scope">
                            <el-tag :type="formatStatus(scope.row.status).type">
                                {{ scope.row.status_text }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_at_text" label="领取时间" width="180"></el-table-column>
                    <el-table-column prop="expired_at_text" label="过期时间" width="180"></el-table-column>
                    <el-table-column prop="use_time" label="使用时间" width="180">
                        <template slot-scope="scope">
                            {{ scope.row.used_at_text || '-' }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="order_id" label="订单ID" width="">
                        <template slot-scope="scope">
                            {{ scope.row.order_id || '-' }}
                        </template>
                    </el-table-column>
                </el-table>

                <div class="mb5 " style="text-align: center; margin-top: 20px;">
                    <el-pagination background
                        @current-change="handleReceivePageChange"
                        :current-page="receivePage"
                        :page-size="pageSize"
                        layout="total, prev, pager, next, jumper"
                        :total="receiveTotal">
                    </el-pagination>
                </div>
            </el-tab-pane>

            <!-- 使用记录 -->
            <el-tab-pane label="使用记录" name="used">
                <el-table :data="usedList" border style="width: 100%"> 
                    <el-table-column prop="id" label="ID" width="80"></el-table-column>
                    <el-table-column prop="user_id" label="用户ID" width="100"></el-table-column>
                    <el-table-column prop="user_info.phone" label="用户名" width="220"></el-table-column>
                    <el-table-column prop="status" label="状态" width="100">
                        <template slot-scope="scope">
                            <el-tag :type="formatStatus(scope.row.status).type">
                                {{ scope.row.status_text }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_at_text" label="领取时间" width="180"></el-table-column>
                    <el-table-column prop="expired_at_text" label="过期时间" width="180"></el-table-column>
                    <el-table-column prop="use_time" label="使用时间" width="180">
                        <template slot-scope="scope">
                            {{ scope.row.used_at_text || '-' }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="order_id" label="订单ID" width="">
                        <template slot-scope="scope">
                            {{ scope.row.order_id || '-' }}
                        </template>
                    </el-table-column>
                </el-table>

                <div class="mb5 " style="text-align: center; margin-top: 20px;">
                    <el-pagination background
                        @current-change="handleUsedPageChange"
                        :current-page="usedPage"
                        :page-size="pageSize"
                        layout="total, prev, pager, next, jumper"
                        :total="usedTotal">
                    </el-pagination>
                </div>
            </el-tab-pane>
        </el-tabs>
    </el-card>
</div>


<?php view_footer(); ?>