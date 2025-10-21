<div class="mb-3">
    <div v-if="!pageElements[selectedElement].config || pageElements[selectedElement].config.length === 0" class="text-muted text-center py-3">
        暂无菜单项，点击下方按钮添加
    </div>
    <div id="menu-items-container" v-if="pageElements[selectedElement].config && pageElements[selectedElement].config.length > 0">

        <div class="mb-2">
            <p>外样式</p>
            <textarea type="text" class="form-control form-control-sm" v-model="pageElements[selectedElement].css" placeholder=""></textarea>
        </div>

        <div class="mb-2">
            <p>内样式</p>
            <textarea type="text" class="form-control form-control-sm" v-model="pageElements[selectedElement].css_inner" placeholder=""></textarea>
        </div>
        

        <div v-for="(v, itemIndex) in pageElements[selectedElement].config" class="menu-item mb-3 p-3 border rounded ele-box" style="cursor: move;">
            <div class="mb-2">
                <img @click="selectMenuIcon(itemIndex)" v-if="v.image" :src="v.image" style="width: 50px; height: 50px; object-fit: cover;" class="border rounded">

                <div @click="selectMenuIcon(itemIndex)" v-else class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">

                    <small class="text-muted">上传</small>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">菜单名称</label>
                <input type="text" class="form-control form-control-sm" v-model="v.title" placeholder="请输入菜单名称">
            </div>
            <div class="mb-2">
                <label class="form-label">跳转类型</label>
                <select v-model="v.type" class="form-control form-control-sm">
                    <option value="minapp">小程序页面</option>
                    <option value="minapp_jump">跳转小程序</option>
                    <option value="web">网页链接</option>
                </select>
            </div>

            <div class="mb-2" v-if="v.type == 'minapp_jump'">
                <label class="form-label">小程序AppID</label>
                <input type="text" class="form-control form-control-sm" v-model="v.app_id" placeholder="请输入小程序AppID">
            </div>

            <div class="mb-2">
                <input type="text" class="form-control form-control-sm" v-model="v.url" placeholder="请输入跳转链接">
            </div>
            <button class="btn btn-sm btn-outline-danger ele-del" @click="removeMenuItem(itemIndex)">删除</button>

        </div>
    </div>
    <button class="btn btn-sm btn-primary" @click="addMenuItem()">添加菜单项</button>
</div>

<?php
$vue->method("addMenuItem()", " 
    if (!this.pageElements[this.selectedElement].config || !Array.isArray(this.pageElements[this.selectedElement].config)) {
        this.\$set(this.pageElements[this.selectedElement], 'config', []);
    } 
    this.pageElements[this.selectedElement].config.push({
        image: '',
        title: '菜单项',
        url: ''
    });
    this.\$nextTick(() => {
        this.initMenuDrag();
    });
");

$vue->method("selectMenuIcon(index)", "
    console.log('selectMenuIcon called with index:', index);
    console.log('selectedElement:', this.selectedElement);
    ajax('/mall/uniapp/js', {elementIndex: this.selectedElement, itemIndex: index}, function(res) {
        console.log('ajax response:', res);
        if (res.code == 0) {
            let js = res.js;
            layer.open({
            type: 2,
            title: '" . lang('选择图标') . "',
            area: ['90%', '80%'],
            content: '/admin/media/index?js=' + encodeURIComponent(js),
            end: function() {
               
            }
        });
        } else {
            console.error('ajax请求失败:', res);
            layer.msg('请求失败，请重试');
        }
    }, function(error) {
        console.error('ajax请求错误:', error);
        layer.msg('网络错误，请重试');
    });

");

$vue->method("removeMenuItem(index)", "
    this.pageElements[this.selectedElement].config.splice(index, 1);
");
