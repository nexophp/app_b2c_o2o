<div class="mb-3">
    <div v-if="!pageElements[selectedElement].config || pageElements[selectedElement].config.length === 0" class="text-muted text-center py-3">
        暂无图片，点击下方按钮添加
    </div>
    <div id="banner-images-container" v-if="pageElements[selectedElement].config && pageElements[selectedElement].config.length > 0">
        <div class="mb-2">
            <p>外样式</p>
            <textarea type="text" class="form-control form-control-sm" v-model="pageElements[selectedElement].css" placeholder=""></textarea>
        </div>

        <div class="mb-2">
            <p>内样式</p>
            <textarea type="text" class="form-control form-control-sm" v-model="pageElements[selectedElement].css_inner" placeholder=""></textarea>
        </div>

        <draggable v-model="pageElements[selectedElement].config">

            <div v-for="(v, imgIndex) in pageElements[selectedElement].config" class="ele-box banner-image-item mb-3 p-3 border rounded" >
                <div class="mb-2">
                    <img @click="selectBannerImage1(imgIndex)" v-if="v.image" :src="v.image" style="width: 100px; height: 100px; object-fit: cover;" class="border rounded">
                    <div @click="selectBannerImage1(imgIndex)" v-else class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 60px;">
                        <small class="text-muted">上传</small>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label" >跳转类型</label>
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
                <button class="btn btn-sm btn-outline-danger ele-del" @click="removeBannerImage1(imgIndex)">删除</button>
            </div>
        </draggable>
    </div>
    <button class="btn btn-sm btn-primary" @click="addBannerImage1()">添加图片</button>
</div>

<?php
$vue->method("addBannerImage1()", " 
    if (!this.pageElements[this.selectedElement].config || !Array.isArray(this.pageElements[this.selectedElement].config)) {
        this.\$set(this.pageElements[this.selectedElement], 'config', []);
    } 
    this.pageElements[this.selectedElement].config.push({
        image: '',
        url: ''
    });
    this.\$nextTick(() => {
        this.initBannerDrag();
    });
");

$vue->method("selectBannerImage1(index)", "
    ajax('/mall/uniapp/js', {elementIndex: this.selectedElement, itemIndex: index}, function(res) {
        if (res.code == 0) {
            let js = res.js;
            layer.open({
            type: 2,
            title: '" . lang('上传图片') . "',
            area: ['90%', '80%'],
            content: '/admin/media/index?js=' + encodeURIComponent(js),
            end: function() {
               
            }
        });
        }
    });

");

$vue->method("removeBannerImage1(index)","
    this.pageElements[this.selectedElement].config.splice(index, 1);
");