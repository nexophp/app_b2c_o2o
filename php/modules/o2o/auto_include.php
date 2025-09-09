<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => '本地O2O小店',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];


Menu::setGroup('admin');

// 添加顶级菜单
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('o2o-address', '配送地址', '/o2o/address', '', 1000, 'source');
Menu::add('o2o-info', '门店信息', '/o2o/info', '', 1000, 'source');
Menu::add('o2o-order', '订单', '/o2o/admin', '', 1000, 'source');
//删除商城订单菜单  
add_action("remove_admin_menu",function(&$remove){
    $remove[] = 'order-admin';
    //$remove[] = 'order-refund';
});



/**
 * 支付成功后打印小票
 */
add_action("order.paid", function ($order_id) {
    modules\o2o\lib\Printer::do($order_id);
});
/**
 * 创建订单后测试打印
 */
add_action("order.create", function ($order_id) {
    //modules\o2o\lib\Printer::do($order_id);
});

add_action("header_center", function () {
    global $vue;
    $vue->method("o2o_change_plat_statuc(status)", "
        ajax('/o2o/info/change-status',{
            status:status
        },(res)=>{
            window.location.reload();
        });
    ");
    $business_start = get_config("plat_store_info_business_start");
    $business_end = get_config("plat_store_info_business_end");
    $status = get_config("plat_store_info_status");
    if ($status == 1) {
        echo '<el-tag type="success" class="link hand" @click="o2o_change_plat_statuc(-1)">店铺营业中</el-tag>';
    } else {
        echo '<el-tag type="danger" class="link hand" @click="o2o_change_plat_statuc(1)">今日未营业</el-tag>';
    }
    $hours = '';
    if ($business_start == $business_end) {
        $hours = '24小时营业';
    } else {
        $hours = $business_start . '-' . $business_end;
    }
    if ($business_start) {
        echo '<span class="ms-2">营业时间：' . $hours . "</span>";
    }
});


add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2"> 
            线下门店
        </h6>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('配送范围（公里）') ?>
                </label>
                <input v-model="form.delivery_range" class="form-control" placeholder="<?=lang('配送范围')?>">
            </div> 
		</div>
	</div>
<?php 
});
