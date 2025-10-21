<?php
view_header('运费模板管理');
global $vue;
$url = '/logistic/template/list';

// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("regionDialogVisible", false);
$vue->data("currentTemplateId", 0);
$vue->data("form", [
    'id' => '',
    'name' => '',
    'is_free_shipping' => 0,
    'free_shipping_amount' => 0,
    'delivery_type' => 1,
    'status' => 1,
    'regions' => []
]);
$vue->data("regionForm", [
    'region_type' => 1,
    'regions' => [],
    'first_item' => 1,
    'first_fee' => 10,
    'additional_item' => 1,
    'additional_fee' => 5,
    'is_free_shipping' => 0,
    'free_shipping_amount' => 0
]);
$vue->data("formTitle", lang('添加运费模板'));
$vue->data("regionFormTitle", lang('添加区域设置'));
$vue->data("deliveryTypes", [
    ['label' => lang('按件数'), 'value' => 1], 
]);
$vue->data("regionTypes", [ 
    ['label' => lang('指定区域'), 'value' => 1],
    ['label' => lang('偏远地区'), 'value' => 2]
]);
$vue->data("provinces", []);
$vue->data("selectedRegions", []); 
$vue->watch("selectedRegions","
  handler(new_val,old_val){
    console.log('tset');
    this.cascader_change_1();
  }, 
  deep: true
"); 
$vue->data("editingRegionIndex", -1);

$vue->created(["load()", "loadProvinces()"]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
");

$vue->method("loadProvinces()", "
ajax('/address/api/get-provinces', {}, function(res) {
    if (res.code == 0) {
        app.provinces = res.data;
    }
});
");

$vue->method("showAdd()", "
this.form = {
    id: '', 
    name: '',
    is_free_shipping: 0,
    free_shipping_amount: 0,
    delivery_type: 1,
    status: '1',
    regions: []
};
this.formTitle = '" . lang('添加运费模板') . "';
this.dialogVisible = true;  
");

$vue->method("update_row(row)", " 
ajax('/logistic/template/detail', {id: row.id}, function(res) { 
    if (res.code == 0) {  
        app.\$set(app, 'form', res.data); 
        app.formTitle = '" . lang('编辑运费模板') . "';
        app.dialogVisible = true;    
    }  
});
");

$vue->method("submit()", "
let url = this.form.id ? '/logistic/template/edit' : '/logistic/template/add'; 
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.dialogVisible = false;
        app.load_list();
    } 
});
");

$vue->method("delete_row(row)", "
this.\$confirm('" . lang('确认删除该运费模板吗？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    " . vue_message() . "
    ajax('/logistic/template/delete', {id: row.id}, function(res) {
        if (res.code == 0) { 
            app.load_list();
        } 
    });
}).catch(() => {});
");

$vue->method("updateStatus(row)", "
let status = row.status == 1 ? 0 : 1;
ajax('/logistic/template/update-status', {id: row.id, status: status}, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.load_list();
    } 
});
");

$vue->method("showRegionDialog(type, index)", "
this.regionFormTitle = type === 'add' ? '" . lang('添加区域设置') . "' : '" . lang('编辑区域设置') . "';
if (type === 'add') {
    this.regionForm = {
        region_type: 1,
        regions: [],
        first_item: 1,
        first_fee: 10,
        additional_item: 1,
        additional_fee: 5,
        is_free_shipping: 0,
        free_shipping_amount: 0,
        level: 0,
        pid: 0
    };
    this.editingRegionIndex = -1;
    this.selectedRegions = [];
} else {
    this.regionForm = JSON.parse(JSON.stringify(this.form.regions[index]));
    this.editingRegionIndex = index;
    // 确保regions是数组
    if (!Array.isArray(this.regionForm.regions)) {
        this.regionForm.regions = [];
    } 
    this.selectedRegions = [];
    this.\$nextTick(() => {
        this.selectedRegions = [...this.regionForm.regions];
    });
}
this.regionDialogVisible = true;
");

$vue->method("submitRegion()", "
console.log(this.regionForm); 
this.selectedRegions = this.regionForm.regions; 

// 复制regionForm对象
let regionData = JSON.parse(JSON.stringify(this.regionForm));
 

// 更新level和pid
let len = this.selectedRegions.length;
if (len > 0) {
    regionData.level = len;
    regionData.pid = this.selectedRegions[len - 1];
} else {
    regionData.level = 0;
    regionData.pid = 0;
}

// 更新或添加到form.regions
if (this.editingRegionIndex >= 0) {
    this.form.regions.splice(this.editingRegionIndex, 1, regionData);
} else {
    this.form.regions.push(regionData);
}

// 关闭对话框
this.regionDialogVisible = false;
");

$vue->method("removeRegion(index)", "
this.form.regions.splice(index, 1);
");

$vue->method("getRegionTypeText(type)", "
let types = {
    1: '" . lang('指定区域') . "', 
    2: '" . lang('偏远地区') . "'
};
return types[type] || '" . lang('未知') . "';
");
 

$vue->method("getProvinceNames(regions)", "
if (!regions || regions.length === 0) return '" . lang('全国') . "';
let names = [];
for (let i = 0; i < regions.length; i++) {
    for (let j = 0; j < this.provinces.length; j++) {
        if (this.provinces[j].id == regions[i]) {
            names.push(this.provinces[j].name);
            break;
        }
    }
}
return names.join(', ');
");

$vue->method("getDeliveryTypeText(type)", "
let types = {
    1: '" . lang('按件数') . "', 
};
return types[type] || '" . lang('未知') . "';
");

$vue->method("getFirstItemLabel()", "
let type = this.form.delivery_type;
if (type == 1) return '" . lang('首件') . "'; 
return '" . lang('首件') . "';
");

$vue->method("getAdditionalItemLabel()", "
let type = this.form.delivery_type;
if (type == 1) return '" . lang('续件') . "'; 
return '" . lang('续件') . "';
");

// 权限控制
$vue->data("can_add", has_access('logistic/template/add'));
$vue->data("can_edit", has_access('logistic/template/edit'));
$vue->data("can_del", has_access('logistic/template/delete'));
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
            'attr_element' => ['placeholder' => lang('模板名称')],
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'value' => [['label' => lang('全部'), 'value' => ''], ['label' => lang('启用'), 'value' => '1'], ['label' => lang('禁用'), 'value' => '0']],
            'attr_element' => ['placeholder' => lang('状态')],
        ],
        [
            'type' => 'html',
            'html' => '<el-button type="primary" v-if="can_add" @click="showAdd()">' . lang('添加运费模板') . '</el-button>',
        ],
    ]);
    ?>

    <?php
    echo element('table', [
        ['name' => 'open', ':data' => 'list', ":height" => "height"],
        ['name' => 'column', 'prop' => 'name', 'label' => lang('模板名称')],
        ['name' => 'column', 'prop' => 'delivery_type_text', 'label' => lang('计费方式'), 'width' => '220'],
        
        [
            'name' => 'column',
            'prop' => 'status',
            'label' => lang('状态'),
            'width' => '100',
            'tpl' => [[
                'type' => 'html',
                "html" => "
                    <el-switch v-model='scope.row.status' active-value='1' inactive-value='0' @change='updateStatus(scope.row)'></el-switch>
                "
            ]]
        ],
        [
            'name' => 'column',
            'label' => lang('操作'),
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

<!-- 运费模板表单对话框 -->
<el-dialog :title="formTitle" :visible.sync="dialogVisible" width="90%" top="20px" :close-on-click-modal="false">

    <?php
    element\form::$model = 'form';
    echo element('form', [
        ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '120px'],
        [
            'type' => 'input',
            'name' => 'name',
            'label' => lang('模板名称'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入模板名称')],
        ],
        [
            'type' => 'select',
            'name' => 'delivery_type',
            'label' => lang('计费方式'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请选择计费方式'),],
            'value' => 'deliveryTypes'
        ],   
        [
            'type' => 'radio',
            'name' => 'status',
            'label' => lang('状态'),
            'value' => [['label' => lang('启用'), 'value' => '1'], ['label' => lang('禁用'), 'value' => '0']],
        ],
        ['type' => 'close']
    ]);
    ?>

    <!-- 区域设置 -->
    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4><?= lang('区域设置') ?></h4>
            <el-button type="primary" size="small" @click="showRegionDialog('add')"><?= lang('添加区域') ?></el-button>
        </div>

        <el-table :data="form.regions" border style="width: 100%">
            <el-table-column prop="region_type" :label="'<?= lang('区域类型') ?>'" width="200">
                <template slot-scope="scope">
                    {{ getRegionTypeText(scope.row.region_type) }}
                </template>
            </el-table-column>
            <el-table-column prop="regions" :label="'<?= lang('区域') ?>'" width="" :show-overflow-tooltip="true">
                <template slot-scope="scope">
                    {{ scope.row.regions_str}}
                </template>
            </el-table-column>
            <el-table-column :label="'<?= lang('首费') ?>'" width="220">
                <template slot-scope="scope">
                     {{ getFirstItemLabel() }} {{ scope.row.first_fee }} <?= lang('元') ?>
                </template>
            </el-table-column>
            <el-table-column :label="'<?= lang('续费') ?>'" width="220">
                <template slot-scope="scope">
                     {{ getAdditionalItemLabel() }} {{ scope.row.additional_fee }} <?= lang('元') ?>
                </template>
            </el-table-column>
            <el-table-column :label="'<?= lang('包邮') ?>'" width="200">
                <template slot-scope="scope">
                    <span v-if="scope.row.free_shipping_amount > 0">
                        <el-tag type="success" size="small"><?= lang('是') ?></el-tag>
                        <div><?= lang('满') ?>{{ scope.row.free_shipping_amount }}<?= lang('元') ?></div>
                    </span> 
                </template>
            </el-table-column>
            <el-table-column :label="'<?= lang('操作') ?>'" width="150">
                <template slot-scope="scope">
                    <el-button type="text" size="small" @click="showRegionDialog('edit', scope.$index)"><?= lang('编辑') ?></el-button>
                    <el-button type="text" size="small" class="text-danger" @click="removeRegion(scope.$index)"><?= lang('删除') ?></el-button>
                </template>
            </el-table-column>
        </el-table>
    </div>

    <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submit()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<!-- 区域设置对话框 -->
<el-dialog :title="regionFormTitle" :visible.sync="regionDialogVisible" width="90%" :close-on-click-modal="false" append-to-body top="20px">
     
    <?php
    element\form::$model = 'regionForm';
    echo element('form', [
        ['type' => 'open', 'model' => 'regionForm', 'label-position' => 'left', 'label-width' => '120px'],
        [
            'type' => 'select',
            'name' => 'region_type',
            'label' => lang('区域类型'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请选择区域类型')],
            'value' => 'regionTypes'
        ],
        [
            'type' => 'cascader',
            'name' => 'regions',
            'label' => lang('选择区域'),
            'attr_element' => [
                'placeholder' => lang('请选择区域'),
                'multiple' => true,
                'v-model' => 'selectedRegions',
                'v-if' => 'regionForm.region_type != 1'
            ],
            'url' => '/logistic/template/cascader',
            'attr_element' => [':props' => "{value:'id',label:'label',children:'children',multiple:true}"],
        ], 
        [
            'type' => 'input',
            'name' => 'first_fee',
            'label' => lang('首件(元)'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入首费'), 'type' => 'number', 'min' => 0, 'step' => 0.01],
        ], 
        [
            'type' => 'input',
            'name' => 'additional_fee',
            'label' => lang('续件(元)'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入续费'), 'type' => 'number', 'min' => 0, 'step' => 0.01],
        ], 
        //首重
        [
            'type' => 'input',
            'name' => 'first_weight',
            'label' => lang('首重(kg)'),
            'attr_element' => ['placeholder' => lang('请输入首重'), 'type' => 'number', 'min' => 0, 'step' => 0.01],
        ],
        //续重
        [
            'type' => 'input',
            'name' => 'additional_weight',
            'label' => lang('续重(kg)'),
            'attr_element' => ['placeholder' => lang('请输入续重'), 'type' => 'number', 'min' => 0, 'step' => 0.01],
        ],  

        [
            'type' => 'input',
            'name' => 'free_shipping_amount',
            'label' => lang('包邮金额'),
            'attr_element' => ['placeholder' => lang('请输入包邮金额'), 'type' => 'number', 'min' => 0],
            'v-if' => 'regionForm.is_free_shipping == 1'
        ],
        ['type' => 'close']
    ]);
    ?>

    <div slot="footer" class="dialog-footer">
        <el-button @click="regionDialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submitRegion()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<?php view_footer(); ?>