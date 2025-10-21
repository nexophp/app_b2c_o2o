<div class="mb-2">
    <p>样式</p>
    <textarea type="text" class="form-control form-control-sm" v-model="pageElements[selectedElement].css" placeholder="请输入css"></textarea>
</div>

<div class="mb-2">
    <label>商品显示类型</label>
    <select class="form-control form-control-sm" v-model="pageElements[selectedElement].show_type">
        <option value="list">列表</option>
        <option value="grid">网格</option>
    </select>
</div>

<div class="mb-2">
    <label>商品</label>
    <select @change="select_ele_product_type()" class="form-control form-control-sm" v-model="pageElements[selectedElement].product_type">
        <option value="all">所有商品</option>
        <option value="category">指定分类</option>
        <option value="product">指定商品</option>
    </select>
</div>

<div class="mb-2" v-if="pageElements[selectedElement].product_type == 'category'">

    <div>分类</div>
    <div>
        <el-select multiple v-model="pageElements[selectedElement].category_id" style="width: 100%;">
            <el-option value="0">请选择分类</el-option>
            <el-option
                v-for="item in category_list"
                :key="item.id"
                :label="item.title"
                :value="item.id">
            </el-option>

        </el-select>
    </div>
</div>



<div class="mb-2" v-if="pageElements[selectedElement].product_type == 'product'">
    <div>商品</div>
    <div>
        <el-select v-model="pageElements[selectedElement].product_id" multiple style="width: 100%;">
            <el-option
                v-for="item in product_list"
                :key="item.id"
                :label="item.title"
                :value="item.id">
            </el-option>

        </el-select>
    </div>
</div>


<div class="mb-2"  v-if="pageElements[selectedElement].product_type == 'category'" > 
    <label>显示数量</label> 
    <input type="number" class="form-control form-control-sm" v-model="pageElements[selectedElement].limit"> 
    </input>
</div>


<?php
$vue->data("category_list", "[]");
$vue->data("product_list", "[]");

$code = " 
    let selectedElement = this.selectedElement;
    if(this.pageElements[selectedElement].product_type == 'category') {
        //发送ajax取商品分类
        ajax('/mall/uniapp/category', {}, function(res) {
            if (res.code == 0) {
                app.category_list = res.data; 
            }
        }); 
    } else if(this.pageElements[selectedElement].product_type == 'product') {
        //发送ajax取商品
        ajax('/mall/uniapp/product', {}, function(res) {
            if (res.code == 0) {
                app.product_list = res.data; 
            }
        }); 
    } 
";

$js .= $code;

$vue->method("select_ele_product_type()", $code);
