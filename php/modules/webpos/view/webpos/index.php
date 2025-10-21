<?php
add_css("
.el-pagination.is-background{
padding:0;
}
.webpos-container {
    display: flex;
    height: calc(100vh - 60px);
    background: #f5f7fa;
}

.left-panel {
    flex: 2;
    padding: 20px;
    overflow-y: auto;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    margin-bottom: 20px; 
}

.right-panel {
    flex: 1;
    background: #fff;
    border-left: 1px solid #e4e7ed;
    display: flex;
    flex-direction: column;
}

.product-card {
    border: 1px solid #e4e7ed;
    border-radius: 8px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.3s;
    background: #fff;
}

.product-card:hover {
    border-color: #409eff;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.product-card-content {
    display: flex;
    align-items: center;
}

.product-image {
    width: 80px;
    height: 80px;
    border-radius: 4px;
    object-fit: cover;
    margin-right: 16px;
    background: #f5f7fa;
}

.product-info {
    flex: 1;
}

.product-title {
    font-size: 16px;
    font-weight: 500;
    color: #303133;
    margin-bottom: 8px;
    line-height: 1.4;
}

.product-price {
    font-size: 18px;
    color: #e6a23c;
    font-weight: 600;
    margin-bottom: 4px;
}

.cart-header {
    padding: 20px;
    border-bottom: 1px solid #e4e7ed;
}

.cart-content {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
}

.cart-footer {
    padding: 20px;
    border-top: 1px solid #e4e7ed;
    background: #fafafa;
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 50px;
    height: 50px;
    border-radius: 4px;
    object-fit: cover;
    margin-right: 12px;
    background: #f5f7fa;
}

.cart-item-info {
    flex: 1;
    margin-right: 12px;
}

.cart-item-controls {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.cart-item-name {
    font-size: 14px;
    color: #303133;
    margin-bottom: 4px;
}

.cart-item-price {
    font-size: 12px;
    color: #909399;
}

.num-control {
    display: flex;
    align-items: center;
    margin-right: 12px;
}

.num-btn {
    width: 24px;
    height: 24px;
    border: 1px solid #dcdfe6;
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.num-input {
    width: 40px;
    height: 24px;
    border: 1px solid #dcdfe6;
    border-left: none;
    border-right: none;
    text-align: center;
    font-size: 12px;
}

.pending-orders {
    margin-bottom: 20px;
}

.pending-order-item {
    padding: 8px 12px;
    margin-bottom: 8px;
    background: #f0f9ff;
    border: 1px solid #b3d8ff;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pending-order-item.active {
    background: #409eff;
    color: #fff;
}

.member-info {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 20px;
}

  .pending-orders {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.pending-order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 10px;
    margin-bottom: 5px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
}

.pending-order-item:hover {
    background: #e9ecef;
    border-color: #dee2e6;
}

.pending-order-item.active {
    background: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.pending-order-item.active:hover {
    background: #bbdefb;
}

.product-stock {
    font-size: 14px;
    color: #909399;
}

.product-category {
    font-size: 12px;
    color: #909399;
    background: #f4f4f5;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-block;
    margin-top: 8px;
}

.spec-dialog .el-dialog__body {
    padding: 20px;
}

.spec-product-info {
    display: flex;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ebeef5;
}

.spec-product-image {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 16px;
    background: #f5f7fa;
}

.spec-product-details h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #303133;
}

.spec-product-price {
    font-size: 20px;
    color: #e6a23c;
    font-weight: 600;
    margin-bottom: 8px;
}

.spec-product-stock {
    color: #909399;
    font-size: 14px;
}

.spec-options {
    margin-bottom: 20px;
}

.spec-options h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: #303133;
}

.spec-option {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 8px 8px 0;
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.spec-option:hover {
    border-color: #409eff;
}

.spec-option.selected {
    background: #409eff;
    color: #fff;
    border-color: #409eff;
}

.attr-options {
    margin-bottom: 20px;
}

.attr-group {
    margin-bottom: 16px;
}

.attr-group h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: #303133;
}

.attr-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    background: #fff;
}

.spec-option:hover {
    border-color: #409eff;
}

.spec-option.selected {
    border-color: #409eff;
    background: #ecf5ff;
    color: #409eff;
}

.num-control {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.num-control span {
    margin-right: 12px;
    font-size: 16px;
    color: #303133;
}

.num-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #dcdfe6;
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all 0.3s;
}

.num-btn:hover {
    border-color: #409eff;
    color: #409eff;
}

.num-btn:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.num-input {
    width: 60px;
    height: 32px;
    border: 1px solid #dcdfe6;
    border-left: none;
    border-right: none;
    text-align: center;
    font-size: 14px;
}

.spec-dialog-footer {
    text-align: right;
    padding-top: 20px;
    border-top: 1px solid #ebeef5;
}
");

view_header(lang('WebPos收银'));
global $vue;

// 初始化Vue数据
$vue->data("searchKeyword", "");
$vue->data("list", []);
$vue->data("memberPhone", "");
$vue->data("memberInfo", null);
$vue->data("cartItems", []);
$vue->data("selectedProduct", null);
$vue->data("selectedSpec", null);
$vue->data("selectedAttrs", "{}");
$vue->data("specDialogVisible", false);
$vue->data("num", 1);
$vue->data("totalAmount", 0);
// 挂单相关
$vue->data("pendingOrders", []);
$vue->data("currentOrderIndex", -1);

$vue->data("height", "");

$vue->created(["load_list()", 'load()']);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
this.wqFocus();
");

$url = '/webpos/webpos/products';
$vue->method("load_list()", " 
this.where_list.keyword = this.searchKeyword;
ajax('" . $url . "', this.where_list, (res) => {
    if (res.code == 0) {
        this.list = res.data;
        this.total_list = res.total;
    }
});
");

$vue->method("searchlist()", "
this.where_list.page = 1;
this.load_list(); 
");

$vue->method("resetSearch()", "
this.where_list.searchKeyword = '';
this.where_list.page = 1;
this.load_list(); 
");

$vue->method("pay_type", "");

$vue->method("searchMember()", "
if (!this.memberPhone) {
    this.\$message.warning('请输入手机号');
    return;
}
ajax('/webpos/webpos/qr', {qr: this.memberPhone,items:this.cartItems}, (res) => {
    if (res.code == 0) {
        let d = res.data;
        if(d.type == 'member'){
           app.memberInfo = d.member;  
           app.clearOrderPayment();
        } 
        if(d.type == 'product'){ 
            app.selectProduct(d.product);
            app.clearOrderPayment();
        }
        if(d.type == 'pay'){ 
            app.order_num = d.order_num;
            app.pay_amount = d.amount;
            app.pay_type = d.pay_type;
            app.checkOrderPayment();
        }    
        app.memberPhone = '';

    } else {
        if(res.msg){
            this.\$message.error(res.msg);
        }
    }
});
");



$vue->method("selectProduct(product)", "
this.selectedProduct = product;
this.setDefaultAttr();
if (product.spec_type === '2' && product.spec && product.spec.length > 0) {
    this.setDefaultSpec();
    this.specDialogVisible = true;
} else if (product.new_attr && Object.keys(product.new_attr).length > 0) {
    this.specDialogVisible = true;
    this.selectedAttrs = {};
    this.selectedSpec = null;
} else {
    this.selectedSpec = null;
    this.addToCart();
}
this.reset_order_num();
");

$vue->method("setDefaultSpec()", "
const defaultSpec = this.selectedProduct.spec.find(spec => spec.is_default == '1' || spec.is_default === true);
if (defaultSpec) {
    this.selectedSpec = defaultSpec;
} else if (this.selectedProduct.spec.length > 0) {
    this.selectedSpec = this.selectedProduct.spec[0];
}
");

$vue->method("setDefaultAttr()", "
this.selectedAttrs = {};
if (this.selectedProduct.new_attr) {
    Object.keys(this.selectedProduct.new_attr).forEach(attrName => {
        const attrValues = this.selectedProduct.new_attr[attrName];
        if (attrValues && attrValues.length > 0) {
            this.\$set(this.selectedAttrs, attrName, attrValues[0]);
        }
    });
}
");

$vue->method("addToCart()", "
let specTitle = this.selectedSpec ? this.selectedSpec.title : '';
let attrText = '';
if (this.selectedAttrs && Object.keys(this.selectedAttrs).length > 0) {
    attrText = Object.keys(this.selectedAttrs).map(key => this.selectedAttrs[key]).join(', ');
}

// 检查购物车中是否已存在相同商品、规格和属性
let existingItem = this.cartItems.find(item => 
    item.product_id === this.selectedProduct.id && 
    item.spec === specTitle &&
    item.attr === attrText
);

if (existingItem) {
    existingItem.num += this.num;
} else {
    let cartItem = {
        product_id: this.selectedProduct.id,
        title: this.selectedProduct.title,
        price: this.selectedSpec ? this.selectedSpec.price : this.selectedProduct.price,
        num: this.num,
        spec: specTitle,
        attr: attrText,
        selected_attrs: JSON.parse(JSON.stringify(this.selectedAttrs)),
        image: this.selectedProduct.image
    };
    this.cartItems.push(cartItem);
}

this.calculateTotal(); 
this.specDialogVisible = false;
this.num = 1;
");

$vue->method("confirmSpec()", "
if (this.selectedProduct.spec && this.selectedProduct.spec.length > 0 && !this.selectedSpec) {
    this.\$message.warning('" . lang('请选择规格') . "');
    return;
}
if (this.selectedProduct.new_attr && Object.keys(this.selectedProduct.new_attr).length > 0) {
    let hasUnselectedAttr = false;
    Object.keys(this.selectedProduct.new_attr).forEach(attrName => {
        if (!this.selectedAttrs[attrName]) {
            hasUnselectedAttr = true;
        }
    });
    if (hasUnselectedAttr) {
        this.\$message.warning('" . lang('请选择所有属性') . "');
        return;
    }
}
this.addToCart();
");

$vue->method("closeSpecDialog()", "
this.specDialogVisible = false;
");

$vue->method("increasenum()", "
this.num++;
");

$vue->method("decreasenum()", "
if (this.num > 1) {
    this.num--;
}
");

$vue->method("updatenum(item, change)", "
let newnum = item.num + change;
if (newnum < 1) {
    this.\$message.warning('" . lang('数量不能小于1') . "');
    return;
 }
item.num = newnum;
this.calculateTotal();
this.reset_order_num();

");

$vue->method("removeItem(item)", " 
    let index = this.cartItems.findIndex(cartItem => 
        cartItem.product_id === item.product_id && 
        cartItem.spec === item.spec &&
        cartItem.attr === item.attr
    );
    if (index > -1) {
        this.cartItems.splice(index, 1);
        this.calculateTotal(); 
    } 
    this.reset_order_num();
");

$vue->method("clearCart()", " 
    this.clearCartSilent(); 
    this.reset_order_num();
");

$vue->method("clearCartSilent()", "
this.cartItems = [];
this.totalAmount = 0;
this.memberInfo = null;
this.memberPhone = '';
");

$vue->method("calculateTotal()", "
this.totalAmount = this.cartItems.reduce((total, item) => {
    return total + (parseFloat(item.price) * parseInt(item.num));
}, 0);
");
$vue->data("show_pay_qr", false);
$vue->data("order_num", '');
$vue->data("pay_amount", 0);
$vue->method("reset_order_num()", "
    this.show_pay_qr = false;
    this.order_num = '';
    this.pay_amount = 0;
");
$vue->method("clean()", "
    this.clearMember();
    this.clearCartSilent(); 
    this.reset_order_num();
    if(this.loopInterval){
        clearInterval(this.loopInterval);
        this.loopInterval = '';
    }

");

// 添加挂单相关方法
$vue->method("addPendingOrder()", "
if (this.cartItems.length === 0) {
    this.\$message.warning('" . lang('商品为空，无法挂单') . "');
    return;
}

let orderName = '" . lang('挂单') . "' + (this.pendingOrders.length + 1);
let pendingOrder = {
    id: Date.now(),
    name: orderName,
    items: JSON.parse(JSON.stringify(this.cartItems)),
    member: this.memberInfo ? JSON.parse(JSON.stringify(this.memberInfo)) : null,
    total: this.totalAmount,
    createTime: new Date().toLocaleString()
};

this.pendingOrders.push(pendingOrder);
this.clearCart(); 
this.reset_order_num();

");

$vue->method("selectPendingOrder(order, index)", "
this.currentOrderIndex = index;
this.loadPendingOrder(order);
");

$vue->method("loadPendingOrder(order)", "
this.cartItems = JSON.parse(JSON.stringify(order.items));
this.memberInfo = order.member ? JSON.parse(JSON.stringify(order.member)) : null;
this.memberPhone = this.memberInfo ? this.memberInfo.phone : '';
this.calculateTotal(); 
");

$vue->method("removePendingOrder(order, index)", " 
    this.pendingOrders.splice(index, 1);
    if (this.currentOrderIndex === index) {
        this.currentOrderIndex = -1;
        this.clearCartSilent();
    } else if (this.currentOrderIndex > index) {
        this.currentOrderIndex--;
    } 
");
$vue->method("clearMember()", "
    this.memberInfo = null;
    this.memberPhone = '';
");

$vue->data("loopInterval", "");

$vue->method("clearOrderPayment()", "
    if(this.loopInterval){
        clearInterval(this.loopInterval);
        this.loopInterval = '';
    }
");
/**
 * 每秒检测订单是否支付
 */
$vue->method("checkOrderPayment()", "
    if(this.loopInterval){
        return;
    }
    this.loopInterval = setInterval(() => {
        if (this.order_num) {
            ajax('/webpos/webpos/check-order-payment', { order_num: this.order_num,pay_type:this.pay_type }, (res) => {
                if (res.code == 0) { 
                    this.\$message.success('订单已支付');
                    this.clean(); 
                }
            });
        }
    }, 1000);
");

//wq 鼠标进入自动选中
$vue->method("wqFocus()", "  
setTimeout(() => {
    this.\$refs['wq'].select();  
}, 100);
");
$vue->method("searchEnter()", "
    this.wqFocus();
    //把搜索出来的第一个商品添加到右边
    if(this.list.length > 0){
        this.selectProduct(this.list[0]);
        this.where_list.title = '';
        this.searchlist();
    }
");
?>

<div class="webpos-container">
    <!-- 左侧商品区域 -->
    <div class="left-panel">
        <!-- 搜索栏 -->
        <div class="search-bar" style="margin-bottom: 20px;">
            <div style="display: flex;justify-content: space-between;">
                <div>
                    <el-input ref="wq"
                        @keyup.enter.native="searchEnter()"
                        v-model="where_list.title"
                        placeholder="<?= lang('搜索商品名称或商品唯一码') ?>"
                        @input="searchlist()"
                        clearable
                        style="width: 300px; margin-right: 10px;">
                    </el-input>
                    <el-button @click="resetSearch()" type="info" plain><?= lang('重置') ?></el-button>
                </div>


                <!-- 分页 -->
                <div>
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



        <div class="product-grid">
            <div
                v-for="product in list"
                :key="product.id"
                class="product-card"
                @click="selectProduct(product)">
                <div class="product-card-content">
                    <img
                        :src="product.image"
                        :alt="product.title"
                        class="product-image">
                    <div class="product-info">
                        <div class="product-title">{{ product.title }}</div>
                        <div class="product-price">¥{{ product.price }}</div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- 右侧购物车区域 -->
    <div class="right-panel">
        <!-- 购物车头部 -->
        <div class="cart-header">
            <!-- 会员信息 -->
            <div class="member-section">
                <h4 style="margin: 0 0 10px 0; font-size: 14px;"><?= lang('扫商品条码、会员码、付款码') ?></h4>
                <div style="margin-bottom: 15px;">
                    <el-input
                        v-model="memberPhone"
                        ref="memberPhone"
                        placeholder="输入手机号或扫微信付款码"
                        @keyup.enter.native="searchMember"
                        size="small"
                        style="width: 260px; margin-right: 8px;">
                    </el-input>
                    <el-button @click="clearMember" size="small" type="error"><?= lang('清除') ?></el-button>
                </div>
                <div v-if="order_num" class="el-card shadow-sm p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-receipt me-2 text-primary"></i>
                        <span class="fw-bold">订单号：</span>
                        <span class="ms-2">{{ order_num }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-currency-yuan me-2 text-success"></i>
                        <span class="fw-bold">金额：</span>
                        <span class="ms-2 text-danger" style="font-size: 22px;">{{ pay_amount }} 元</span>
                    </div>
                </div>

                <div v-if="memberInfo" class="member-info">
                    会员：
                    <div style="font-size: 12px;">
                        <div><strong>{{ memberInfo.nickname || memberInfo.username }}</strong></div>
                        <div>{{ memberInfo.phone }}</div>
                    </div>
                </div>
            </div>

            <!-- 挂单管理 -->
            <div class="pending-orders">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="margin: 0; font-size: 14px;"><?= lang('挂单管理') ?></h4>
                    <el-button @click="addPendingOrder" size="mini" type="primary" :disabled="cartItems.length === 0">挂单</el-button>
                </div>
                <div v-for="(order, index) in pendingOrders" :key="order.id"
                    class="pending-order-item"
                    :class="{active: currentOrderIndex === index}"
                    @click="selectPendingOrder(order, index)">
                    <span>{{ order.name }} ({{ order.items.length }}<?= lang('件') ?>)</span>
                    <el-button @click.stop="removePendingOrder(order, index)" size="mini" type="danger" icon="el-icon-delete"></el-button>
                </div>
            </div>
        </div>

        <!-- 购物车内容 -->
        <div class="cart-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;"><?= lang('商品明细') ?> ({{ cartItems.length }})</h3>
                <el-button @click="clearCart()" type="danger" size="small" plain><?= lang('清空') ?></el-button>
            </div>

            <div v-if="cartItems.length === 0" class="empty-cart" style="text-align: center; padding: 40px; color: #909399;">
                <i class="el-icon-shopping-cart-2" style="font-size: 48px; margin-bottom: 10px;"></i>
                <p><?= lang('商品明细为空') ?></p>
            </div>

            <div v-for="item in cartItems" :key="item.product_id" class="cart-item">
                <img
                    :src="item.image || '/misc/img/no-image.png'"
                    :alt="item.title"
                    class="cart-item-image">
                <div class="cart-item-info">
                    <div class="cart-item-name">{{ item.title }}</div>
                    <div v-if="item.spec" class="item-spec" style="font-size: 12px; color: #909399;">规格: {{ item.spec }}</div>
                    <div v-if="item.attr" class="item-attr" style="font-size: 12px; color: #909399;">属性: {{ item.attr }}</div>
                    <div class="cart-item-price">¥{{ item.price }}</div>
                </div>

                <div class="cart-item-controls">
                    <div class="num-control">
                        <el-button
                            @click="updatenum(item, -1)"
                            size="mini"
                            icon="el-icon-minus"
                            :disabled="item.num <= 1">
                        </el-button>
                        <span style="margin: 0 10px; min-width: 30px; text-align: center;">{{ item.num }}</span>
                        <el-button
                            @click="updatenum(item, 1)"
                            size="mini"
                            icon="el-icon-plus">
                        </el-button>
                    </div>

                    <el-button
                        @click="removeItem(item)"
                        type="danger"
                        size="mini"
                        icon="el-icon-delete"
                        style="margin-left: 8px;">
                    </el-button>
                </div>
            </div>
        </div>


    </div>
</div>

<!-- 商品规格选择对话框 -->
<el-dialog
    title="<?= lang('选择规格') ?>"
    :visible.sync="specDialogVisible"
    width="500px"
    class="spec-dialog"
    :close-on-click-modal="false">

    <div v-if="selectedProduct" class="spec-content">
        <!-- 商品信息 -->
        <div class="spec-product-info">
            <img
                :src="selectedProduct.image || '/misc/img/no-image.png'"
                :alt="selectedProduct.title"
                class="spec-product-image">
            <div class="spec-product-details">
                <h3>{{ selectedProduct.title }}</h3>
                <div class="spec-product-price">¥{{ selectedSpec ? selectedSpec.price : selectedProduct.price }}</div>
            </div>
        </div>

        <!-- 规格选择 -->
        <div v-if="selectedProduct.spec && selectedProduct.spec.length > 0" class="spec-options">
            <h4><?= lang('选择规格') ?>:</h4>
            <div class="spec-list">
                <div
                    v-for="(spec, index) in selectedProduct.spec"
                    :key="index"
                    class="spec-option"
                    :class="{selected: selectedSpec && selectedSpec.title === spec.title}"
                    @click="selectedSpec = spec">
                    {{ spec.title }}
                    <span v-if="spec.price" style="margin-left: 8px; color: #e6a23c;">¥{{ spec.price }}</span>
                </div>
            </div>
        </div>

        <!-- 属性选择 -->
        <div v-if="selectedProduct.new_attr && Object.keys(selectedProduct.new_attr).length > 0" class="attr-options">
            <div v-for="(attrValues, attrName) in selectedProduct.new_attr" :key="attrName" class="attr-group">
                <h4>{{ attrName }}:</h4>
                <div class="attr-list">
                    <div
                        v-for="(attrValue, index) in attrValues"
                        :key="index"
                        class="spec-option"
                        :class="{selected: selectedAttrs[attrName] === attrValue}"
                        @click="$set(selectedAttrs, attrName, attrValue)">
                        {{ attrValue }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 数量选择 -->
        <div class="num-control">
            <span><?= lang('数量') ?>:</span>
            <div style="display: flex; align-items: center;">
                <button
                    class="num-btn"
                    @click="decreasenum()"
                    :disabled="num <= 1">
                    -
                </button>
                <input
                    v-model.number="num"
                    class="num-input"
                    type="number"
                    min="1">
                <button
                    class="num-btn"
                    @click="increasenum()">
                    +
                </button>
            </div>
        </div>
    </div>

    <div slot="footer" class="spec-dialog-footer">
        <el-button @click="closeSpecDialog()"><?= lang('取消') ?></el-button>
        <el-button type="primary" @click="confirmSpec()"><?= lang('确定') ?></el-button>
    </div>
</el-dialog>
<?php view_footer(); ?>