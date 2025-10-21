<?php
add_css(".page-element {
    cursor: pointer;
}
#app{
padding:0;
}
.delete-element{
    margin-top:4px;
    margin-right:4px;
}
.page-element:hover {
    background-color: #f8f9fa;
}
.page-element.selected {
    border-color: #007bff !important;
    background-color: #e7f1ff;
}
.phone-preview {
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
.ele-box{
position:relative;
}
.ele-del{
position:absolute;
top:3px;
right:3px;
}
");
view_header(lang('页面设计器'));
global $vue;

$js = "";
$elements = include __DIR__ . '/uniapp.php';

$vue->data("pageId", $page_id);
$vue->data("pageElements", $page_data);
$vue->data("selectedElement", null);
$vue->data("elements", $elements);
$vue->data("saving", false);

$vue->created(["loadPageDesign()", "initDrag()", "initBannerDrag()", "initMenuDrag()"]);


$vue->method("loadPageDesign()", " 
ajax('/mall/uniapp/get-design', {id: this.pageId}, function(res) { 
    app.pageElements = res.data; 
});
");

$vue->method("initDrag()", "
this.\$nextTick(() => {
    const container = document.querySelector('#page-elements-container');
    if (container) {
        Sortable.create(container, {
            draggable: '.page-element',
            handle: '.drag',
            onEnd: (evt) => {
                let list = this.pageElements;
                if (evt.oldIndex !== evt.newIndex) {
                    let newIndex = evt.newIndex;
                    let oldIndex = evt.oldIndex;
                    
                    let old = list[oldIndex];
                    list.splice(oldIndex, 1);
                    list.splice(newIndex, 0, old);
                    this.pageElements = [];
                    this.\$nextTick(() => {
                        this.pageElements = list;
                    });
                    this.\$forceUpdate();
                }
            }
        });
    }
});
");

$vue->method("saveDesign()", "
if (!this.pageId || this.saving) return;
this.saving = true;
ajax('/mall/uniapp/save-design', {
    id: this.pageId,
    design: this.pageElements
}, function(res) {
    app.saving = false;
    if (res.code == 0) {
        app.\$message.success('保存成功');
    } else {
        app.\$message.error(res.msg || '保存失败');
    }
});
");

$vue->method("addElement(element)", "
const newElement = {
    id: Date.now(),
    type: element.type,
    name: element.name,
    config: {}
};
if(!this.pageElements){
this.pageElements= [];
}
this.pageElements.push(newElement);
this.selectedElement = this.pageElements.length - 1;
");



$vue->method("removeElement(index)", "
this.\$confirm('确定要删除这个元素吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
}).then(() => {
    this.pageElements.splice(index, 1);
    if (this.selectedElement === index) {
        this.selectedElement = null;
    } else if (this.selectedElement > index) {
        this.selectedElement--;
    }
}).catch(() => {});
");

$vue->method("savePage()", " 
ajax('/mall/uniapp/save-design', {
    id: this.pageId,
    pageElements: this.pageElements 
},function(res){ 
    app.\$message.success('保存成功');
}); 
");


$vue->method("removeMenuItem(index)", "
this.pageElements[this.selectedElement].config.items.splice(index, 1);
");



?>

<div class="container-fluid p-0">
    <div class="row g-0" style="height: 100vh;">
        <!-- Left elements panel -->
        <div class="col-3 bg-light border-end" style="overflow-y: auto;">
            <div class="p-3">
                <h5 class="mb-3">页面元素</h5>
                <div class="list-group">
                    <button v-for="element in elements" :key="element.type"
                        class="list-group-item list-group-item-action"
                        @click="addElement(element)">
                        {{ element.name }}
                    </button>
                </div>
                <!-- <el-button class="mt-3" type="primary" @click="saveDesign" :loading="saving">
                    保存设计
                </el-button> -->
            </div>
        </div>

        <!-- Middle design area -->
        <div class="col-6 bg-white" style="overflow-y: auto;">
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">页面元素</h5>
                    <div> 
                        <el-button type="primary" @click="savePage()" :disabled="saving">
                           {{ saving ? '保存中...' : '保存' }}
                        </el-button>
                    </div>
                </div>

                <div class="phone-preview mx-auto" style="width: 375px;     padding: 10px;min-height: 667px; border: 2px solid #ddd; border-radius: 20px; background: #fff;" id="page-elements-container">
                    <div v-for="(element, index) in pageElements" :key="element.id"
                        class="page-element mb-2 p-2 border rounded position-relative"
                        :class="{ 'selected': selectedElement === index }"
                        @click="selectElement(index)">
                        <i class="bi bi-grip-vertical drag me-2" style="cursor: move;"></i>
                        {{ element.name }}
                        <button v-if="selectedElement === index"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 delete-element"
                            @click.stop="removeElement(index)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div v-if="pageElements.length === 0" class="text-center text-muted py-5">
                        <p>点击左侧元素添加到页面</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right attributes panel -->
        <div class="col-3 bg-light border-start" style="overflow-y: auto;">
            <div class="p-3">
                <h5 class="mb-3">元素属性</h5>
                <div v-if="selectedElement !== null && pageElements[selectedElement]">

                    <!-- Banner 配置 -->
                    <div v-if="pageElements[selectedElement]?.type === 'banner'">
                        <?php include __DIR__ . '/banner.php'; ?>
                    </div>

                    <!-- Menu 配置 -->
                    <div v-else-if="pageElements[selectedElement]?.type === 'menu'">
                        <?php include __DIR__ . '/menu.php'; ?>
                    </div>


                    <div v-else-if="pageElements[selectedElement]?.type === 'search'">
                        <?php include __DIR__ . '/search.php'; ?>
                    </div>

                    <div v-else-if="pageElements[selectedElement]?.type === 'product'">
                        <?php include __DIR__ . '/product.php'; ?>
                    </div>
 
                    <div v-else-if="pageElements[selectedElement]?.type === 'image_1'">
                        <?php include __DIR__ . '/image_1.php'; ?>
                    </div>

                    <div v-else-if="pageElements[selectedElement]?.type === 'image_2'">
                        <?php include __DIR__ . '/image_2.php'; ?>
                    </div>



                </div>
                <div v-else class="text-muted text-center py-5">
                    <p>选择元素查看属性</p>
                </div>
            </div>
        </div>
    </div>
</div>



<?php 

$vue->method("initBannerDrag()", "
this.\$nextTick(() => {
    const container = document.querySelector('#banner-images-container');
    if (container) {
        Sortable.create(container, {
            draggable: '.banner-image-item',
            onEnd: (evt) => {
                if (evt.oldIndex !== evt.newIndex && this.selectedElement !== null) {
                    let config = this.pageElements[this.selectedElement].config;
                    let oldIndex = evt.oldIndex;
                    let newIndex = evt.newIndex;
                    
                    let old = config[oldIndex];
                    config.splice(oldIndex, 1);
                    config.splice(newIndex, 0, old);
                    this.\$forceUpdate();
                }
            }
        });
    }
});
");

$vue->method("initMenuDrag()", "
this.\$nextTick(() => {
    const container = document.querySelector('#menu-items-container');
    if (container) {
        Sortable.create(container, {
            draggable: '.menu-item',
            onEnd: (evt) => {
                if (evt.oldIndex !== evt.newIndex && this.selectedElement !== null) {
                    let config = this.pageElements[this.selectedElement].config;
                    let oldIndex = evt.oldIndex;
                    let newIndex = evt.newIndex;
                    
                    let old = config[oldIndex];
                    config.splice(oldIndex, 1);
                    config.splice(newIndex, 0, old);
                    this.\$forceUpdate();
                }
            }
        });
    }
});
");

$vue->method("selectElement(index)", "
this.selectedElement = index; ".$js."
");


view_footer(); ?>