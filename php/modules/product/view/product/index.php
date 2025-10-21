<?php
view_header('商品管理');
global $vue;
$url = '/product/product/list';
$get_freight_template = [];
do_action('get_freight_template', $get_freight_template);
$vue->data("freight_template", $get_freight_template);
// 初始化Vue数据
$vue->data("height", "");
$vue->data("dialogVisible", false);
$vue->data("selectedTypeId", ""); // 添加选中的分类ID
$vue->data("form", [
    'id' => '',
    'name' => '',
    'type_id' => '',
    'brand_id' => '',
    'sku' => '',
    'price' => 0,
    'market_price' => 0,
    'cost_price' => 0,
    'stock' => 0,
    'images' => [],
    'description' => '',
    'specifications' => [],
    'attributes' => [],
    'sort' => 0,
    'status' => 1
]);
$vue->data("formTitle", lang('添加商品'));
$vue->data("tree_data", []);
$vue->data("brand_data", []);

$vue->created(["load()", "load_tree()"]);

$vue->method("load()", "
this.height = 'calc(100vh - " . get_config('admin_table_height') . "px)';
");

$vue->method("load_tree()", " 
ajax('/product/type/tree', {has_all:1}, function(res) { 
   let data = res.data;
   app.tree_data = data;

});
");

// 添加分类选择方法
$vue->method("selectType(data)", "
app.where_list.type_id = data.id; 
app.load_list();
");

$vue->method("showAdd()", "
this.load_tree();
this.form = {
    id: '', 
    type_id: '',
    brand_id: '',
    sku: '',
    price: 0,
    market_price: 0,
    cost_price: 0,
    stock: 0,  
    status: 'success',
    //默认单规格
    spec_type:'1'
};
this.formTitle = '" . lang('添加商品') . "';
this.dialogVisible = true;  
this.update_editor('desc','');
");

$vue->data("form_only_stock", false);
$vue->method("update_row(row)", " 
this.form_only_stock = false;
this.edit_row(row);
");

$vue->method("update_stock(row)", "
this.form_only_stock = true;
this.edit_row(row);
");

$vue->method("edit_row(row)", " 
ajax('/product/product/detail', {id: row.id}, function(res) { 
    if (res.code == 0) {  
        app.\$set(app, 'form', res.data); 
        app.update_editor('desc', app.form.desc);   
        app.load_tree();
        app.formTitle = '" . lang('编辑商品') . "';
        app.dialogVisible = true;    
    }  
});
");

$vue->method("submit()", "
let url = this.form.id ? '/product/product/edit' : '/product/product/add'; 
ajax(url, this.form, function(res) {
    " . vue_message() . "
    if (res.code == 0) { 
        app.dialogVisible = false;
        app.load_list();
    } 
});
");

// 商品状态切换方法
$vue->method("change_status(row)", "
ajax('/product/product/change-status', {id: row.id}, function(res) {
    if (res.code == 0) { 
        app.load_list();
    }
});
");

$vue->method("delete_row(row)", "
this.\$confirm('" . lang('确认删除该商品吗？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => { 
    ajax('/product/product/delete', {id: row.id}, function(res) {
        " . vue_message() . "
        if (res.code == 0) { 
            app.load_list();
        } 
    });
});
");

// 权限控制
$vue->data("can_add", has_access('product/product/add'));
$vue->data("can_edit", has_access('product/product/edit'));
$vue->data("can_del", has_access('product/product/delete'));

$vue->data('notice_stock', get_config('notice_stock') ?: 10);
$vue->method("searchLower()", "
if(!this.where_list.lower){
    this.where_list.lower = 1;
}else{
    this.where_list.lower = 0;
}
this.load_list();
");
$vue->method("searchRem(status)", "
this.where_list.recommend = status;
this.load_list();
");

$vue->method("showSpec(row)", "

");
$vue->method("do_recommend(row)", "
ajax('/product/product/recommend', {id: row.id}, function(res) {
    if (res.code == 0) { 
        app.load_list();
    }
});
");
?>

<!-- 左右布局容器 -->
<div class="d-flex" style="height:calc(100vh - 40px);">
    <!-- 左侧分类树 -->
    <div class="border-end p-3 overflow-auto" style="width: 250px;">
        <h4><?php echo lang('商品分类'); ?></h4>
        <el-tree :data="tree_data"
            node-key="id"
            :props="{children: 'children', label: 'title'}"
            @node-click="selectType"
            :highlight-current="true"
            :expand-on-click-node="false"
            :default-expand-all="true"
            class="custom-tree">
        </el-tree>
    </div>

    <!-- 右侧内容区域 -->
    <div class="flex-fill ms-3 overflow-auto">
        <?php
        echo element("filter", [
            'data' => 'list',
            'url' => $url,
            'is_page' => true,
            'init' => true,
            [
                'type' => 'input',
                'name' => 'title',
                'attr_element' => ['placeholder' => lang('商品名称')],
            ],
            [
                'type' => 'hidden',
                'name' => 'type_id',
                'v-model' => 'selectedTypeId',
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'value' => [['label' => lang('全部'), 'value' => ''], ['label' => lang('上架'), 'value' => 'success'], ['label' => lang('下架'), 'value' => 'error']],
                'attr_element' => ['placeholder' => lang('商品状态')],
            ],
            [
                'type' => 'html',
                'html' => '<el-button type="primary" v-if="can_add" @click="showAdd()">' . lang('添加商品') . '</el-button>
                <el-button type="warning" @click="searchLower()" v-if="!where_list.lower">查寻低库存</el-button>
                <el-button type="success" @click="searchLower(0)" v-else>查寻全部</el-button>
                <el-button type="default" @click="searchRem(1)" v-if="!where_list.recommend">查寻推荐</el-button>
                <el-button type="default" @click="searchRem(0)" v-else>查寻全部</el-button>
                ',
            ],
        ]);
        ?>

        <?php
        $html = "";
        $html = "  
        <table class='table  table-bordered' v-if='scope.row.spec_type == 2'>        
            <thead>
                <tr>
                    <th v-for='v in scope.row.spec_names'>{{v.name}}</th>
                    <th>SKU</th>
                    <th>库存</th>
                    <th>价格</th>
                </tr>
            </thead>
            <tbody>
            <tr v-for='row in scope.row.spec'>
                <th v-for='(v,index) in scope.row.spec_names'>{{row.spec_values[index]}}</th>
                <th>{{row.sku}}</th>
                <th>{{row.stock}}</th>
                <th>{{row.price}}</th>
            </tr>
            </tbody>
        </table>
        <div v-else>
            <span>库存：{{scope.row.stock}}</span>
        </div>
        ";
        echo element('table', [
            ['name' => 'open', ':data' => 'list', ":height" => "height"],
            [
                'name' => 'column',
                'prop' => '',
                'label' => '',
                'type' => 'expand',
                'tpl' => [
                    [
                        "type" => 'html',
                        "html" => $html
                    ]
                ]
            ],
            [
                'name' => 'column',
                'prop' => 'title',
                'label' => '标题',
            ],
            [
                'name' => 'column',
                'prop' => 'image',
                'label' => lang('图片'),
                'width' => '80',
                'tpl' => [[
                    'type' => 'html',
                    "html" => "<el-image style='width: 50px; height: 50px;' v-if='scope.row.image' :src='scope.row.image' :preview-src-list='[scope.row.image]'></el-image>"
                ]]
            ],

            ['name' => 'column', 'prop' => 'weight_title', 'label' => lang('重量'), 'width' => '200'],
            ['name' => 'column', 'prop' => 'show_price', 'label' => lang('价格'), 'width' => ''],

            [
                'name' => 'column',
                'prop' => 'status',
                'label' => lang('状态'),
                'width' => '100',
                'tpl' => [[
                    'type' => 'html',
                    "html" => "
                            <el-tag @click='change_status(scope.row)' class='hand' v-if='scope.row.status == \"success\"' type='success'>" . lang('上架') . "</el-tag>
                            <el-tag @click='change_status(scope.row)' class='hand' v-else type='danger'>" . lang('下架') . "</el-tag>
                        "
                ]]
            ],
            [
                'name' => 'column',
                'prop' => 'recommend',
                'label' => lang('推荐'),
                'width' => '130',
                'tpl' => [
                    [
                        'type' => 'html',
                        "html" => "
                        <el-tag @click='do_recommend(scope.row)' v-if='scope.row.recommend == 1' type='primary'>" . lang('推荐') . "</el-tag>
                        <el-tag @click='do_recommend(scope.row)' v-else type='info'>" . lang('设为推荐') . "</el-tag>
                    "
                    ]
                ]
            ],
            [
                'name' => 'column',
                'label' => lang('操作'),
                'width' => '200',
                'tpl' => [
                    ['name' => 'button', 'label' => lang('编辑'), "v-if" => "can_edit", '@click' => 'update_row(scope.row)', 'type' => 'primary', 'size' => 'small'],
                    ['name' => 'button', 'label' => lang('删除'), "v-if" => "can_edit", '@click' => 'delete_row(scope.row)', 'type' => 'danger', 'size' => 'small'],
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
</div>

<?php
$vue->opt['is_editor'] = true;
$vue->data("activeName", "product");
?>

<!-- 商品表单对话框 -->
<el-dialog @opened="open_editor" :title="formTitle" :visible.sync="dialogVisible" width="90%" top="10px" :close-on-click-modal="false">
    <div class="container-fluid ">
        <div class="row ">
            <!-- 左侧表单区域 -->
            <div class="col-12 pe-4 overflow-auto">
                <el-tabs v-model="activeName" type="border-card">
                    <el-tab-pane label="商品" name="product">
                        <?php
                        element\form::$model = 'form';
                        echo element('form', [
                            ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '100px'],
                            [
                                'type' => 'cascader',
                                'name' => 'type_id',
                                'label' => lang('商品分类'),
                                'options' => 'tree_data',
                                'attr_element' => [':props' => "{value:'id',label:'title',checkStrictly: true}"],
                                'attr' => ['v-if' => '!form.id || !form_only_stock']
                            ],
                            [
                                'type' => 'input',
                                'name' => 'title',
                                'label' => lang('商品名称'),
                                'attr' => ['required', 'v-if' => '!form_only_stock'],
                                'attr_element' => ['placeholder' => lang('请输入商品名称')],
                            ],
                            [
                                'type' => 'upload_media',
                                'name' => 'image',
                                'attr' => ['required', 'v-if' => '!form_only_stock'],
                                'label' => lang('商品主图'),
                                'url' => '/admin/upload',
                                'max' => 1,
                            ],
                            [
                                'type' => 'upload_media',
                                'name' => 'images',
                                'label' => lang('商品图片'),
                                'url' => '/admin/upload',
                                'multiple' => true,
                                'max' => 5,
                                'attr' => ['v-if' => '!form_only_stock'],
                            ],
                            [
                                'type' => 'editor',
                                'name' => 'body',
                                'label' => lang('商品详情'),
                                'attr' => ['v-if' => '!form_only_stock'],
                            ],
                            ['type' => 'close']
                        ]);
                        ?>
                    </el-tab-pane>
                    <el-tab-pane label="规格" name="specification">
                        <?php
                        echo element('form', [
                            ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '100px'],
                            [
                                'type' => 'spec',
                                'name' => 'spec_type,spec',
                                'label' => lang('商品规格'),
                                'attr' => ['image', 'stock', 'status'],
                            ],
                            ['type' => 'close']
                        ]);
                        ?>
                    </el-tab-pane>
                    <el-tab-pane label="属性" name="attribute">
                        <?php
                        echo element('form', [
                            ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '100px'],
                            [
                                'type' => 'attribute',
                                'name' => 'attr',
                                'label' => lang('商品属性'),
                                'attr' => ['v-if' => '!form_only_stock'],
                            ],
                            ['type' => 'close']
                        ]);
                        ?>
                    </el-tab-pane>
                    <el-tab-pane label="运费" name="freight">
                        <?php
                        echo element('form', [
                            ['type' => 'open', 'model' => 'form', 'label-position' => 'left', 'label-width' => '180px'],
                            [
                                'type' => 'number',
                                'name' => 'weight',
                                'label' => lang('商品重量（kg）'),
                            ],
                            [
                                'type' => 'number',
                                'name' => 'length',
                                'label' => lang('长度（cm）'),
                            ],
                            [
                                'type' => 'number',
                                'name' => 'width',
                                'label' => lang('宽度（cm）'),
                            ],
                            [
                                'type' => 'number',
                                'name' => 'height',
                                'label' => lang('高度（cm）'),
                            ],

                            [
                                'type' => 'select',
                                'name' => 'freight_template_id',
                                'label' => lang('运费模板'),
                                'value' => 'freight_template',
                            ],
                            ['type' => 'close']
                        ]);
                        ?>
                    </el-tab-pane>

                </el-tabs>
            </div>

        </div>



    </div>
    <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submit()"><?php echo lang('确定'); ?></el-button>
    </div>

</el-dialog>

<?php view_footer(); ?>