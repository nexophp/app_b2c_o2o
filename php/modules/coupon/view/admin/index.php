<?php
view_header('优惠券管理');
global $vue;
$url = '/coupon/admin/list';

// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);

$vue->data("form", [
    'id' => '',
    'name' => '',
    'type' => 1,
    'value' => '',
    'condition' => '',
    'start_time' => '',
    'end_time' => '',
    'days' => '',
    'seller_id' => 0,
    'store_id' => 0,
    'products' => [],
    'types' => []
]);
$vue->data("formTitle", "添加优惠券");


$vue->created(["load()",]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
");

$vue->method("add()", "
this.form = {
    id: '',
    name: '',
    type: '1',
    value: '',
    condition: '',
    start_time: '',
    end_time: '',
    days: '',
    seller_id: 0,
    store_id: 0,
    products: [],
    types: []
};
this.formTitle = '" . lang('添加优惠券') . "';
this.dialogVisible = true;
");

$vue->method("edit_row(row)", "  
ajax('/coupon/admin/detail', {id: row.id}, function(res) {  
    if (res.code == 0) { 
        app.form = res.data;
        // 处理JSON字段
        if (app.form.products && typeof app.form.products === 'string') {
            app.form.products = JSON.parse(app.form.products);
        }
        if (app.form.types && typeof app.form.types === 'string') {
            app.form.types = JSON.parse(app.form.types);
        }
        app.formTitle = '" . lang('编辑优惠券') . "';
        app.dialogVisible = true;
    }  
});
");

$vue->method("submit()", "
let url = this.form.id ? '/coupon/admin/edit' : '/coupon/admin/add'; 
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.dialogVisible = false;
        app.load_list();
    } 
});
");

$vue->method("delete_row(row)", "
this.\$confirm('" . lang('确认删除该优惠券吗？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    " . vue_message() . "
    ajax('/coupon/admin/delete', {id: row.id}, function(res) {
        if (res.code == 0) { 
            app.load_list();
        } 
    });
}).catch(() => {});
");

$vue->method("toggle_status(row)", "
ajax('/coupon/admin/toggleStatus', {id: row.id}, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.load_list();
    } 
});
");



$vue->method("view_coupon_records(row)", "
// 使用layer.open打开查看页面
layer.open({
    type: 2,
    title: '优惠券记录 - ' + row.name,
    shadeClose: true,
    shade: 0.8,
    area: ['90%', '90%'],
    content: '/coupon/admin/records?coupon_id=' + row.id
});
");

// 权限控制
$vue->data("can_add", false);
$vue->data("can_edit", false);
$vue->data("can_del", false);

if (has_access('coupon/admin/add')) {
    $vue->data("can_add", true);
}
if (has_access('coupon/admin/edit')) {
    $vue->data("can_edit", true);
}
if (has_access('coupon/admin/delete')) {
    $vue->data("can_del", true);
}
?>

<?php
echo element("filter", [
    'data' => 'list',
    'url' => $url,
    'is_page' => true,
    'init' => true,
    [
        'type' => 'input',
        'name' => 'name',
        'attr_element' => [
            'placeholder' => lang('优惠券名称'),
        ],
    ],
    [
        'type' => 'select',
        'name' => 'type',
        'attr_element' => [
            'placeholder' => lang('选择类型'),
        ],
        'value' => [
            ['label' => lang('全部类型'), 'value' => ''],
            ['label' => lang('满减券'), 'value' => '1'],
            ['label' => lang('折扣券'), 'value' => '2']
        ],
    ],
    [
        'type' => 'select',
        'name' => 'status',
        'attr_element' => [
            'placeholder' => lang('选择状态'),
        ],
        'value' => [
            ['label' => lang('全部状态'), 'value' => ''],
            ['label' => lang('启用'), 'value' => '1'],
            ['label' => lang('禁用'), 'value' => '0']
        ],
    ],
    [
        'type' => 'html',
        'html' => '<el-button type="primary" v-if="can_add" @click="add()" >添加优惠券</el-button>',
    ],
]);

?>
<div class="mt-0">
    <?php
    echo element('table', [
        ['name' => 'open', ':data' => 'list', ':height' => 'height'],
        [
            'name' => 'column',
            'prop' => 'name',
            'label' => lang('优惠券名称'),
            'width' => '',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <div>
                        <strong>{{scope.row.name}}</strong> 
                    </div>
                "
                ]
            ]
        ],
        [
            'name' => 'column',
            'prop' => 'type',
            'label' => lang('类型'),
            'width' => '160',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <el-tag v-if='scope.row.type == 1' type='success'>满减券</el-tag>
                    <el-tag v-else-if='scope.row.type == 2' type='warning'>折扣券</el-tag>
                "
                ]
            ]
        ],
        [
            'name' => 'column',
            'prop' => 'value',
            'label' => lang('面值'),
            'width' => '200',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <span v-if='scope.row.type == 1'>￥{{scope.row.value}}</span>
                    <span v-else-if='scope.row.type == 2'>{{(scope.row.value * 10).toFixed(1)}}折</span>
                "
                ]
            ]
        ],
        [
            'name' => 'column',
            'prop' => 'condition',
            'label' => lang('使用条件'),
            'width' => '',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <span v-if='scope.row.condition > 0'>满￥{{scope.row.condition}}</span>
                    <span v-else>无门槛</span>
                "
                ]
            ]
        ],
        [
            'name' => 'column',
            'prop' => 'days',
            'label' => lang('有效天数'),
            'width' => '160',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <span v-if='scope.row.days > 0'>{{scope.row.days}}天</span>
                    <span v-else>-</span>
                "
                ]
            ]
        ],
        [
            'name' => 'column',
            'prop' => 'status',
            'label' => lang('状态'),
            'width' => '100',
            'tpl' => [
                [
                    'type' => 'html',
                    "html" => "
                    <el-tag v-if='scope.row.status == 1' type='success' @click='toggle_status(scope.row)' style='cursor: pointer;'>启用</el-tag>
                    <el-tag v-else type='danger' @click='toggle_status(scope.row)' style='cursor: pointer;'>禁用</el-tag>
                "
                ]
            ]
        ],
        
        [
            'name' => 'column',
            'prop' => 'id',
            'label' => lang('操作'),
            'width' => '200',
            'tpl' => [
                ['name' => 'button', 'label' => lang('查看'), '@click' => 'view_coupon_records(scope.row)', 'type' => 'info', 'size' => 'small', 'style' => 'margin-left: 5px;'],
                ['name' => 'button', 'label' => lang('删除'), "v-if" => "can_del", '@click' => 'delete_row(scope.row)', 'type' => 'danger', 'size' => 'small', 'style' => 'margin-left: 5px;'],
            ]
        ],
        ['name' => 'close'],
    ]);
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

<!-- 优惠券表单对话框 -->
<el-dialog :title="formTitle" :visible.sync="dialogVisible" width="60%" top="20px" :close-on-click-modal="false">
    <?php
    element\form::$model = 'form';
    echo element('form', [
        ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '120px'],
        [
            'type' => 'input',
            'name' => 'name',
            'label' => lang('优惠券名称'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('请输入优惠券名称')],
        ],
        [
            'type' => 'radio',
            'name' => 'type',
            'label' => lang('类型'),
            'value' => [
                ['label' => lang('满减券'), 'value' => 1],
                ['label' => lang('折扣券'), 'value' => 2],
            ],
        ],
        [
            'type' => 'input',
            'name' => 'value',
            'label' => lang('面值'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('满减券输入金额，折扣券输入折扣(如0.8)')],
        ],
        [
            'type' => 'input',
            'name' => 'condition',
            'label' => lang('使用条件'),
            'attr_element' => ['placeholder' => lang('满多少可用，0表示无门槛')],
        ],

        [
            'type' => 'input',
            'name' => 'days',
            'label' => lang('有效天数'),
            'attr' => ['required'],
            'attr_element' => ['placeholder' => lang('优惠券有效天数')],
        ],

        [
            'type' => 'select',
            'name' => 'products',
            'label' => lang('适用商品'),
            'value' => $products,
            'attr_element' => ['multiple'],
        ],
        [
            'type' => 'input',
            'name' => 'total',
            'label' => lang('优惠券总数量'),
            'attr_element' => ['placeholder' => lang('优惠券总数量,0表示无数量限制')],
            'attr' => ['required'],
        ],



        ['type' => 'close']
    ]);
    ?>
    <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false"><?= lang('取消') ?></el-button>
        <el-button type="primary" @click="submit()"><?= lang('确定') ?></el-button>
    </div>
</el-dialog>


<?php view_footer(); ?>