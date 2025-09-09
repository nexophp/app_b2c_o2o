<?php

use lib\Time;

add_action("store.index", function () {
    global $store_id;
    // 获取订单模型
    $orderModel = new \modules\order\model\OrderModel();

    // 获取时间范围
    $thisMonth = Time::get('month');
    $lastMonth = Time::get('lastmonth');
    $today = Time::get('today');
    $yesterday = Time::get('yesterday');

    // 今日订单数
    $todayOrders = $orderModel->count([
        'created_at[>=]' => $today[0],
        'created_at[<]' => $today[1],
        'status[!]' => ['wait', 'cancel', 'delete'],
        'store_id' => $store_id,
    ]);

    // 今日销售额
    $todaySalesAmount = $orderModel->sum('amount', [
        'created_at[>=]' => $today[0],
        'created_at[<]' => $today[1],
        'store_id' => $store_id,
        'status[!]' => ['wait', 'cancel', 'delete'],
    ]);
    $todaySales = '¥' . number_format($todaySalesAmount, 2);

    // 昨日订单数
    $yesterdayOrders = $orderModel->count([
        'created_at[>=]' => $yesterday[0],
        'created_at[<]' => $yesterday[1],
        'store_id' => $store_id,
        'status[!]' => ['wait', 'cancel', 'delete'],
    ]);

    // 昨日销售额
    $yesterdaySalesAmount = $orderModel->sum('amount', [
        'created_at[>=]' => $yesterday[0],
        'created_at[<]' => $yesterday[1],
        'store_id' => $store_id,
        'status[!]' => ['wait', 'cancel', 'delete'],
    ]);
    $yesterdaySales = '¥' . number_format($yesterdaySalesAmount, 2);

    // 本月订单数
    $thisMonthOrders = $orderModel->count([
        'created_at[>=]' => $thisMonth[0],
        'created_at[<]' => $thisMonth[1],
        'status[!]' => ['wait', 'cancel', 'delete'],
        'store_id' => $store_id,
    ]);

    // 本月销售额
    $thisMonthSalesAmount = $orderModel->sum('amount', [
        'created_at[>=]' => $thisMonth[0],
        'created_at[<]' => $thisMonth[1],
        'store_id' => $store_id,
        'status[!]' => ['wait', 'cancel', 'delete'],
    ]);
    $thisMonthSales = '¥' . number_format($thisMonthSalesAmount, 2);

    // 上月订单数
    $lastMonthOrders = $orderModel->count([
        'created_at[>=]' => $lastMonth[0],
        'created_at[<]' => $lastMonth[1],
        'status[!]' => ['wait', 'cancel', 'delete'],
        'store_id' => $store_id,
    ]);

    // 上月销售额
    $lastMonthSalesAmount = $orderModel->sum('amount', [
        'created_at[>=]' => $lastMonth[0],
        'created_at[<]' => $lastMonth[1],
        'store_id' => $store_id,
        'status[!]' => ['wait', 'cancel', 'delete'],
    ]);
    $lastMonthSales = '¥' . number_format($lastMonthSalesAmount, 2);


    // 待处理订单（状态为wait的订单）
    $pendingOrders = $orderModel->count([
        'status' => 'wait',
        'store_id' => $store_id,
    ]);

    $pendingOrdersAmount = $orderModel->sum('amount', [
        'status' => 'wait',
        'store_id' => $store_id,
    ]);
    $pendingOrdersAmount = '¥' . number_format($pendingOrdersAmount, 2);


?>
    <h2 class="mb-4 mt-4"><?= lang('订单统计概览') ?></h2>

    <div class="stats-container">

        <!-- 待处理订单 -->
        <div class="stat-card warning">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('待处理订单') ?></h3>
                    <div class="stat-value"><?= $pendingOrders ?></div>
                    <div class="stat-value"><?= $pendingOrdersAmount ?></div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-bar-chart"></i>
                </div>
            </div>
        </div>

        <!-- 今日订单与销售额 -->
        <div class="stat-card blue">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('今日') ?></h3>
                    <div class="stat-value"><?= $todayOrders ?></div>
                    <div class="stat-value"><?= $todaySales ?></div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-bar-chart"></i>
                </div>
            </div>
        </div>

        <!-- 昨日订单与销售额 -->
        <div class="stat-card orange">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('昨日') ?></h3>
                    <div class="stat-value"><?= $yesterdayOrders ?></div>
                    <div class="stat-value"><?= $yesterdaySales ?></div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-cart"></i>
                </div>
            </div>
        </div>

        <!-- 本月订单与销售额 -->
        <div class="stat-card purple">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('本月') ?></h3>
                    <div class="stat-value"><?= $thisMonthOrders ?></div>
                    <div class="stat-value"><?= $thisMonthSales ?></div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-calendar-month"></i>
                </div>
            </div>
        </div>

        <!-- 上月订单 -->
        <div class="stat-card red">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('上月') ?></h3>
                    <div class="stat-value"><?= $lastMonthOrders ?></div>
                    <div class="stat-value"><?= $lastMonthSales ?></div>

                </div>
                <div class="stat-icon">
                    <i class="bi bi-calendar"></i>
                </div>
            </div>
        </div>



    </div>
<?php
});
?>