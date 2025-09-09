<?php

use lib\Time;
use core\Menu;
use modules\order\lib\OrderPayment;

/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => '订单',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];


Menu::setGroup('admin');

// 添加顶级菜单
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('order-admin', '订单', '/order/admin', '', 1000, 'source');
Menu::add('order-refund', '售后', '/order/refund', '', 200, 'source');


/**
 * 支付成功回调
 */
add_action("payment_success", function ($data) {
    $order_num = $data['order_num'];
    $order_id = db_get_one("order", "id", ['order_num' => $order_num]);
    $payment = new OrderPayment();
    $payment->addPaymentInfo([
        'order_id' => $order_id,
        'title' => '线上支付',
        'type' => $data['type'],
        'amount' => $data['amount'],
    ]);
    db_update("order", ['status' => 'paid'], ['id' => $order_id], true);
    do_action("order.paid",$order_id);
});



add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="bi bi-geo-alt-fill me-2"></i><?= lang('订单') ?>
        </h6> 
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold m-0"> 
                    <?= lang('收货地址') ?>
                </label>
                <textarea v-model="form.order_refund_address" class="form-control" placeholder=""> </textarea>

            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold m-0"> 
                    <?= lang('自动取消订单时间,单位分钟') ?>
                </label>
                <input type="number" v-model="form.order_auto_cancel_time" class="form-control" placeholder=""> </input>
            </div> 

        </div>
    <?php
});

include __DIR__ . '/stat/admin.php';
include __DIR__ . '/stat/store.php';
