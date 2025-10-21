<?php

use core\Menu; 

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => '单用户商城',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina',
	'depends' => [
		'blog',
		'order',
		'product',
		'payment',
		'wallet'
	],
	'level'=>1000
];

Menu::setGroup('admin'); 
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('uniapp-page', '小程序页面', '/mall/uniapp/index', '', 10, 'source');
 


add_action('admin.setting.form', function () {
    global $vue;
    $vue->method("addReturnReason()","
        if(!this.form.return_reason){
            this.form.return_reason = [];
        }
        this.form.return_reason.push('');
    ");
    $vue->method("removeReturnReason(index)","
        this.form.return_reason.splice(index, 1);
    ");
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="bi bi-chat-left me-2"></i>
            <?= lang('商城') ?> 
        </h6> 

        <div class="row g-3 mb-3" >

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('搜索推荐词') ?>
                </label>
                <el-input v-model="form.search_keywords" placeholder="<?=lang('请输入搜索推荐词，多个词用逗号隔开')?>"></el-input>  

            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <?= lang('退换货原因') ?>
                </label>
                <div class="return-reason-manager">
                    <el-button type="primary" size="small" @click="addReturnReason" class="mb-2">
                        <i class="bi bi-plus"></i> 添加原因
                    </el-button>
                    <draggable 
                        v-model="form.return_reason" 
                        :options="{animation: 200, handle: '.drag-handle'}"
                        class="return-reason-list">
                        <div 
                            v-for="(reason, index) in form.return_reason" 
                            :key="index" 
                            class="return-reason-item d-flex align-items-center mb-2">
                            <i class="bi bi-grip-vertical drag-handle me-2 text-muted" style="cursor: move;"></i>
                            <el-input 
                                v-model="form.return_reason[index]" 
                                placeholder="请输入退换货原因"
                                class="flex-grow-1">
                            </el-input>
                            <el-button 
                                type="danger" 
                                size="small" 
                                @click="removeReturnReason(index)"
                                class="ms-2">
                                <i class="bi bi-trash"></i>
                            </el-button>
                        </div>
                    </draggable> 
                </div>
            </div>
        </div> 
    </div> 
<?php
});
