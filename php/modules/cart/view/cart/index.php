<?php
add_css("
.el-pagination.is-background{
padding:0;
}
.product-card {
    border: 1px solid #e4e7ed;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
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

.attr-options {
    margin-bottom: 20px;
}

.attr-group {
    margin-bottom: 15px;
}

.attr-group h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #606266;
    font-weight: normal;
}

.attr-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.spec-option:hover {
    border-color: #409eff;
}

.spec-option.selected {
    border-color: #409eff;
    background: #ecf5ff;
    color: #409eff;
}
");
?>
<?php
view_header('购物车管理');
global $vue;
$url = '/cart/cart/list';

// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("userDialogVisible", false);
$vue->data("productDialogVisible", false);
$vue->data("editDialogVisible", false);
$vue->data("form", [
    'id' => '',
    'user_id' => '',
    'type' => 'normal',
    'num' => 0,
    'amount' => 0
]);
$vue->data("selectedUser", []);
$vue->data("selectedProducts", []);
$vue->data("selectedProduct", null);
$vue->data("selectedSpec", null);
$vue->data("specDialogVisible", false);
$vue->data("num", 1);
$vue->data("selectedAttrs", "{}");
$vue->data("userList", []);
$vue->data("productList", []);
$vue->data("userSearchPhone", "");
$vue->data("productSearchKeyword", "");
$vue->data("productCurrentPage", 1);
$vue->data("productPageSize", get_config('per_page')?:20);
$vue->data("productTotal", 0);
$vue->data("editForm", [
    'cart_id' => '',
    'product_id' => '',
    'quantity' => 1
]);
$vue->data("formTitle", lang('购物车详情'));

$vue->created(["load()"]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
");

$vue->method("view_cart(row)", " 
window.open('/cart/cart/view?id=' + row.id, '_blank');
");

$vue->method("delete_row(row)", "
this.\$confirm('" . lang('确认删除该购物车吗？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => { 
    ajax('/cart/cart/delete', {id: row.id}, function(res) {
        if (res.code == 0) { 
            app.load_list();
        } 
    });
}).catch(() => {});
");
 

$vue->method("add_cart()", "
this.selectedUser = [];
this.selectedProducts = [];
this.userDialogVisible = true;
");

$vue->method("search_user()", "
if (!this.userSearchPhone) {
    this.\$message.warning('" . lang('请输入手机号') . "');
    return;
}
ajax('/cart/cart/searchUser', {phone: this.userSearchPhone}, (res) => {
    if (res.code == 0) {
        this.userList = res.data;
    }
});
");

$vue->method("select_user(user)", "
this.selectedUser = user;
this.userDialogVisible = false;
this.productDialogVisible = true;
this.productSearchKeyword = '';
this.load_products();
");

$vue->method("load_products()", "
ajax('/cart/cart/products', {
    keyword: this.productSearchKeyword,
    page: this.productCurrentPage,
    per_page: this.productPageSize
}, (res) => {
    if (res.code == 0) {
        this.productList = res.data;
        this.productTotal = res.total || 0;
    }
});
");

$vue->method("select_product(product)", "
this.selectedProduct = product;
// 重置规格和属性选择状态
this.selectedSpec = null;
this.selectedAttrs = {};
this.num = 1;

if ((product.spec_type == 2 && product.spec && product.spec.length > 0) || (product.new_attr && Object.keys(product.new_attr).length > 0)) {
    this.setDefaultSpec();
    if (product.new_attr && Object.keys(product.new_attr).length > 0) {
        this.setDefaultAttr();
    }
    this.specDialogVisible = true;
} else {
    this.add_single_product();
}
");

$vue->method("setDefaultSpec()", "
if (this.selectedProduct.spec_type == 2 && this.selectedProduct.spec && this.selectedProduct.spec.length > 0) {
    const defaultSpec = this.selectedProduct.spec.find(spec => spec.is_default == '1' || spec.is_default == true);
    if (defaultSpec) {
        this.selectedSpec = defaultSpec;
    } else {
        this.selectedSpec = this.selectedProduct.spec[0];
    }
} else {
    this.selectedSpec = null;
}
");

$vue->method("setDefaultAttr()", "
this.selectedAttrs = {};
if (this.selectedProduct.new_attr && Object.keys(this.selectedProduct.new_attr).length > 0) {
    Object.keys(this.selectedProduct.new_attr).forEach(attrName => {
        const attrValues = this.selectedProduct.new_attr[attrName];
        if (attrValues && attrValues.length > 0) {
            this.\$set(this.selectedAttrs, attrName, attrValues[0]);
        }
    });
}
");

$vue->method("add_single_product()", "
let cartData = {
    user_id: this.selectedUser.id,
    product_id: this.selectedProduct.id,
    num: this.num
}; 
// 如果有选中的规格，添加规格名称到str_1
if (this.selectedSpec && this.selectedSpec.title) {
    cartData.str_1 = this.selectedSpec.title;
}
// 如果有选中的属性，添加属性信息到str_2
if (this.selectedAttrs && Object.keys(this.selectedAttrs).length > 0) {
    cartData.str_2 = Object.keys(this.selectedAttrs).map(key => this.selectedAttrs[key]).join(', ');
}

ajax('/cart/cart/add', cartData, (res) => {
    if (res.code == 0) {
        this.\$message.success('" . lang('添加成功') . "'); 
        this.specDialogVisible = false;
        this.productDialogVisible = false;
        this.load_list();
    }
});
");

$vue->method("confirm_spec()", "
if (this.selectedProduct.spec_type == 2 && this.selectedProduct.spec && this.selectedProduct.spec.length > 0 && !this.selectedSpec) {
    this.\$message.warning('" . lang('请选择规格') . "');
    return;
}
if (this.selectedProduct.new_attr && Object.keys(this.selectedProduct.new_attr).length > 0) {
    const requiredAttrs = Object.keys(this.selectedProduct.new_attr);
    const selectedAttrs = Object.keys(this.selectedAttrs);
    if (requiredAttrs.length !== selectedAttrs.length) {
        this.\$message.warning('" . lang('请选择所有属性') . "');
        return;
    }
}
this.add_single_product();
");

$vue->method("close_spec_dialog()", "
this.specDialogVisible = false;
");

$vue->method("increase_num()", "
this.num++;
");

$vue->method("decrease_num()", "
if (this.num > 1) {
    this.num--;
}
");

$vue->method("search_products()", "
this.productCurrentPage = 1;
this.load_products();
");

$vue->method("handleProductPageChange(page)", "
this.productCurrentPage = page;
this.load_products();
");

// 移除原有的add_to_cart方法，使用新的商品选择逻辑

$vue->method("edit_cart(row)", "
this.editForm.cart_id = row.id;
this.editForm.items = [{
    id: row.id,
    product_name: row.title,
    spec_title: row.str_1 || '',
    attr_text: row.str_2 || '',
    price: row.price,
    num: row.num
}];
this.editDialogVisible = true;
");

$vue->method("update_quantity(item)", "
if (item.num < 1) {
    this.\$message.warning('" . lang('数量不能小于1') . "');
    item.num = 1;
    return;
}
ajax('/cart/cart/update', {
    id: item.id,
    num: item.num
}, (res) => {
    if (res.code == 0) {
        this.\$message.success('" . lang('更新成功') . "');
        this.load_list();
    }
});
");

$vue->method("remove_item(item)", " 
    ajax('/cart/cart/delete', {
        id: item.id
    }, (res) => {
        if (res.code == 0) { 
            this.editDialogVisible = false;
            this.load_list();
        }
    }); 
");

// 权限控制
$vue->data("can_view", has_access('cart/cart/view'));
$vue->data("can_del", has_access('cart/cart/delete'));
$vue->data("can_add", has_access('cart/cart/add'));
$vue->data("can_edit", has_access('cart/cart/edit'));
?>

<div class="p-3">
    <?php
    echo element("filter", [
        'data' => 'list',
        'url' => $url,
        'is_page' => true,
        'init' => true,
        [
            'type' => 'input',
            'name' => 'phone',
            'attr_element' => ['placeholder' => lang('手机号（需完整输入）')],
        ],  
        [
            'type' => 'html',
            'html' => '<el-button type="primary" @click="add_cart()" v-if="can_add">' . lang('添加购物车') . '</el-button>',
        ], 
    ]);
    ?>

    <?php
    echo element('table', [
        ['name' => 'open', ':data' => 'list', 'ref' => 'table',':height'=>'height'], 
        ['name' => 'column', 'prop' => 'user_phone', 'label' => lang('手机号'), 'width' => '200'],

        ['name' => 'column', 'prop' => 'title', ":show-overflow-tooltip" => 'true', 'label' => lang('商品名称'), 'width' => ''],
        ['name' => 'column', 'prop' => 'str_1',":show-overflow-tooltip" => 'true', 'label' => lang('规格'), 'width' => ''],
        ['name' => 'column', 'prop' => 'str_2',":show-overflow-tooltip" => 'true', 'label' => lang('属性'), 'width' => ''],
        ['name' => 'column', 'prop' => 'num', 'label' => lang('数量'), 'width' => '100'],
        [
            'name' => 'column',
            'prop' => 'price',
            'label' => lang('单价'),
            'width' => '100',
            'tpl' => [[
                'type' => 'html',
                "html" => "¥{{scope.row.price}}"
            ]]
        ],
        [
            'name' => 'column',
            'prop' => 'amount',
            'label' => lang('小计'),
            'width' => '120',
            'tpl' => [[
                'type' => 'html',
                "html" => "¥{{(scope.row.price * scope.row.num).toFixed(2)}}"
            ]]
        ],
        
        [
            'name' => 'column',
            'label' => lang('操作'),
            'width' => '200',
            'tpl' => [
                ['name' => 'button', 'label' => lang('编辑'), "v-if" => "can_edit", '@click' => 'edit_cart(scope.row)', 'type' => 'success', 'size' => 'small', 'style' => 'margin-left: 10px;'],
                ['name' => 'button', 'label' => lang('删除'), "v-if" => "can_del", '@click' => 'delete_row(scope.row)', 'type' => 'danger', 'size' => 'small', 'style' => 'margin-left: 10px;'],
            ]
        ],
        ['name' => 'close'],
    ]);
    ?>

    <div class="mt-1">
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

<!-- 用户选择对话框 -->
<el-dialog title="<?= lang('选择用户') ?>" :visible.sync="userDialogVisible" width="600px" :close-on-click-modal="false">
    <div style="margin-bottom: 20px;">
        <el-input v-model="userSearchPhone" placeholder="<?= lang('请输入手机号') ?>" style="width: 300px; margin-right: 10px;"></el-input>
        <el-button type="primary" @click="search_user()"><?= lang('搜索') ?></el-button>
    </div>
    <el-table :data="userList" style="width: 100%" v-if="userList.length > 0">
        <el-table-column prop="phone" label="<?= lang('手机号') ?>" width="150"></el-table-column>
        <el-table-column prop="nickname" label="<?= lang('昵称') ?>" width="150"></el-table-column>
        <el-table-column prop="email" label="<?= lang('邮箱') ?>"></el-table-column>
        <el-table-column label="<?= lang('操作') ?>" width="100">
            <template slot-scope="scope">
                <el-button type="primary" size="small" @click="select_user(scope.row)"><?= lang('选择') ?></el-button>
            </template>
        </el-table-column>
    </el-table>
    <div v-else style="text-align: center; color: #999; padding: 20px;">
        <?= lang('未找到相关用户') ?>
    </div>
</el-dialog>

<!-- 商品选择对话框 -->
<el-dialog title="<?= lang('选择商品') ?>" :visible.sync="productDialogVisible" fullscreen :close-on-click-modal="false"
    :show-close="false">
    <div>
        <div style="margin-bottom: 20px;">
            <span><?= lang('已选择用户') ?>：{{selectedUser.nickname}} ({{selectedUser.phone}})</span>
        </div>
        <div style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <el-input @keyup.enter.native="search_products()" v-model="productSearchKeyword" placeholder="<?= lang('请输入商品名称') ?>" style="width: 300px; margin-right: 10px;"></el-input>
                    <el-button type="primary" @click="search_products()"><?= lang('搜索') ?></el-button>
                    <el-button class="me-2" type="default" @click="productDialogVisible = false"><?= lang('关闭') ?></el-button>
                </div>
                <div>
                    <el-pagination class=" el-pagination is-background"
                        @current-change="handleProductPageChange"
                        :current-page="productCurrentPage"
                        :page-size="productPageSize"
                        layout="prev, pager, next"
                        :total="productTotal">
                    </el-pagination>
                </div>
            </div>



        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;overflow-y: auto;">
            <div v-for="product in productList" :key="product.id"
                style="border: 1px solid #e4e7ed; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.3s;"
                @click="select_product(product)"
                @mouseover="$event.target.style.borderColor='#409eff'"
                @mouseout="$event.target.style.borderColor='#e4e7ed'">
                <div style="text-align: center; margin-bottom: 10px;">
                    <img :src="product.image"
                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;"
                        @error="$event.target.src='/misc/img/no-image.png'">
                </div>
                <div style="font-size: 14px; font-weight: bold; margin-bottom: 8px; text-align: center; height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                    {{product.title}}
                </div>
                <div style="color: #f56c6c; font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 5px;">
                    ¥{{product.price}}
                </div>
                <div style="color: #909399; font-size: 12px; text-align: center;">
                    <?= lang('库存') ?>：{{product.stock}}<?= lang('件') ?>
                </div>
                <div v-if="product.spec_type == 2 && product.spec && product.spec.length > 0"
                    style="color: #909399; font-size: 12px; text-align: center; margin-top: 5px;">
                    <?= lang('多规格商品') ?>
                </div>
            </div>
        </div>
        <div v-if="productList.length == 0" style="text-align: center; color: #999; padding: 40px;">
            <?= lang('暂无商品数据') ?>
        </div>
    </div>
 
</el-dialog>

<!-- 规格选择对话框 -->
<el-dialog title="<?= lang('选择规格') ?>" :visible.sync="specDialogVisible" width="600px" v-if="selectedProduct">
    <div style="display: flex; margin-bottom: 20px; padding: 15px; border: 1px solid #e4e7ed; border-radius: 8px;">
        <div style="margin-right: 15px;">
            <img :src="(selectedSpec && selectedSpec.image) ? selectedSpec.image : selectedProduct.image || '/misc/img/no-image.png'"
                style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;"
                @error="$event.target.src='/misc/img/no-image.png'">
        </div>
        <div style="flex: 1;">
            <div style="font-size: 16px; font-weight: bold; margin-bottom: 8px;">{{selectedProduct.title}}</div>
            <div style="color: #f56c6c; font-size: 18px; font-weight: bold; margin-bottom: 5px;">
                ¥{{selectedSpec ? selectedSpec.price : selectedProduct.price}}
            </div>
            <div style="color: #909399; font-size: 14px;" v-if="selectedSpec">
                <?= lang('库存') ?>：{{selectedSpec.stock}}件
            </div>
        </div>
    </div>

    <div style="margin-bottom: 20px;" v-if="selectedProduct.spec_type == 2 && selectedProduct.spec && selectedProduct.spec.length > 0">
        <div style="font-size: 16px; font-weight: bold; margin-bottom: 10px;"><?= lang('选择规格') ?></div>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <div v-for="(spec, index) in selectedProduct.spec" :key="index"
                style="padding: 8px 16px; border: 1px solid #e4e7ed; border-radius: 4px; cursor: pointer; transition: all 0.3s;"
                :style="{
                     borderColor: selectedSpec && selectedSpec.title == spec.title ? '#409eff' : '#e4e7ed',
                     backgroundColor: selectedSpec && selectedSpec.title == spec.title ? '#ecf5ff' : '#fff',
                     color: selectedSpec && selectedSpec.title == spec.title ? '#409eff' : '#333'
                 }"
                @click="selectedSpec = spec">
                {{spec.title}}
            </div>
        </div>
    </div>

    <!-- 属性选择 -->
    <div style="margin-bottom: 20px;" v-if="selectedProduct.new_attr && Object.keys(selectedProduct.new_attr).length > 0">
        <div style="font-size: 16px; font-weight: bold; margin-bottom: 10px;"><?= lang('选择属性') ?></div>
        <div v-for="(attrValues, attrName) in selectedProduct.new_attr" :key="attrName" style="margin-bottom: 15px;">
            <div style="font-size: 14px; color: #606266; margin-bottom: 8px;">{{attrName}}</div>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                <div v-for="attrValue in attrValues" :key="attrValue"
                    style="padding: 6px 12px; border: 1px solid #e4e7ed; border-radius: 4px; cursor: pointer; transition: all 0.3s; font-size: 14px;"
                    :style="{
                        borderColor: selectedAttrs[attrName] === attrValue ? '#409eff' : '#e4e7ed',
                        backgroundColor: selectedAttrs[attrName] === attrValue ? '#ecf5ff' : '#fff',
                        color: selectedAttrs[attrName] === attrValue ? '#409eff' : '#333'
                    }"
                    @click="$set(selectedAttrs, attrName, attrValue)">
                    {{attrValue}}
                </div>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: bold; margin-bottom: 10px;"><?= lang('购买数量') ?></div>
        <div style="display: flex; align-items: center;">
            <el-button size="small" @click="decrease_num()" :disabled="num <= 1">-</el-button>
            <span style="margin: 0 15px; font-size: 16px; min-width: 30px; text-align: center;">{{num}}</span>
            <el-button size="small" @click="increase_num()">+</el-button>
        </div>
    </div>

    <div slot="footer" class="dialog-footer">
        <el-button @click="close_spec_dialog()"><?= lang('取消') ?></el-button>
        <el-button type="primary" @click="confirm_spec()" 
            :disabled="(selectedProduct.spec_type == 2 && selectedProduct.spec && selectedProduct.spec.length > 0 && !selectedSpec) || (selectedProduct.new_attr && Object.keys(selectedProduct.new_attr).length > 0 && Object.keys(selectedAttrs).length !== Object.keys(selectedProduct.new_attr).length)">
            <?= lang('确认添加') ?>
        </el-button>
    </div>
</el-dialog>

<!-- 编辑购物车对话框 -->
<el-dialog title="<?= lang('编辑购物车项目') ?>" :visible.sync="editDialogVisible" width="600px">
    <div v-if="editForm.items && editForm.items.length > 0">
        <div v-for="item in editForm.items" :key="item.id" style="border: 1px solid #e4e7ed; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
            <div style="margin-bottom: 15px;">
                <strong><?= lang('商品名称') ?>：</strong>{{item.product_name}}
            </div>
            <div style="margin-bottom: 15px;" v-if="item.spec_title">
                <strong><?= lang('规格') ?>：</strong>{{item.spec_title}}
            </div>
            <div style="margin-bottom: 15px;" v-if="item.attr_text">
                <strong><?= lang('属性') ?>：</strong>{{item.attr_text}}
            </div>
            <div style="margin-bottom: 15px;">
                <strong><?= lang('单价') ?>：</strong>¥{{item.price}}
            </div>
            <div style="margin-bottom: 15px; display: flex; align-items: center;">
                <strong style="margin-right: 10px;"><?= lang('数量') ?>：</strong>
                <el-input-number v-model="item.num" :min="1" size="small" @change="update_quantity(item)"></el-input-number>
            </div>
            <div style="margin-bottom: 15px;">
                <strong><?= lang('小计') ?>：</strong>¥{{(item.price * item.num).toFixed(2)}}
            </div>
            <div style="text-align: right;">
                <el-button type="danger" size="small" @click="remove_item(item)"><?= lang('删除') ?></el-button>
            </div>
        </div>
    </div>
    <div slot="footer" class="dialog-footer">
        <el-button @click="editDialogVisible = false"><?= lang('关闭') ?></el-button>
    </div>
</el-dialog>


<?php view_footer(); ?>