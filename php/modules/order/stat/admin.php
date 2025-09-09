<?php

use lib\Time;

add_action("admin.index", function () {
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
        'status'=>'complete',
        'sys_tag' => 'admin',
    ]);

    // 今日销售额
    $todaySalesAmount = $orderModel->sum('amount', [
        'created_at[>=]' => $today[0],
        'created_at[<]' => $today[1],
        'sys_tag' => 'admin',
        'status'=>'complete',
    ]);
    $todaySales = '¥' . number_format($todaySalesAmount, 2);

    // 昨日订单数
    $yesterdayOrders = $orderModel->count([
        'created_at[>=]' => $yesterday[0],
        'created_at[<]' => $yesterday[1],
        'sys_tag' => 'admin',
        'status'=>'complete',
    ]);

    // 昨日销售额
    $yesterdaySalesAmount = $orderModel->sum('amount', [
        'created_at[>=]' => $yesterday[0],
        'created_at[<]' => $yesterday[1],
        'sys_tag' => 'admin',
        'status'=>'complete',
    ]);
    $yesterdaySales = '¥' . number_format($yesterdaySalesAmount, 2);

    // 本月订单数
    $thisMonthOrders = $orderModel->count([
        'created_at[>=]' => $thisMonth[0],
        'created_at[<]' => $thisMonth[1],
        'status'=>'complete',
        'sys_tag' => 'admin',
    ]);

    // 本月销售额
    $thisMonthSalesAmount = $orderModel->sum('amount', [
        'created_at[>=]' => $thisMonth[0],
        'created_at[<]' => $thisMonth[1],
        'sys_tag' => 'admin',
        'status'=>'complete',
    ]);
    $thisMonthSales = '¥' . number_format($thisMonthSalesAmount, 2);

    // 上月订单数
    $lastMonthOrders = $orderModel->count([
        'created_at[>=]' => $lastMonth[0],
        'created_at[<]' => $lastMonth[1],
        'status'=>'complete',
        'sys_tag' => 'admin',
    ]);

    // 上月销售额
    $lastMonthSalesAmount = $orderModel->sum('amount', [
        'created_at[>=]' => $lastMonth[0],
        'created_at[<]' => $lastMonth[1],
        'sys_tag' => 'admin',
        'status'=>'complete',
    ]);
    $lastMonthSales = '¥' . number_format($lastMonthSalesAmount, 2);


    // 待处理订单（状态为wait的订单）
    $pendingOrders = $orderModel->count([
        'status' => 'paid',
        'sys_tag' => 'admin',
    ]);

    $pendingOrdersAmount = $orderModel->sum('amount', [
        'status' => 'paid',
        'sys_tag' => 'admin',
    ]);
    $pendingOrdersAmount = '¥' . number_format($pendingOrdersAmount, 2);

    // 最近30天订单统计
    $xAxis = [];
    $lineCount = [];
    $lineSum = [];
    for ($i = 0; $i < 60; $i++) {
        $date = date('Y-m-d', strtotime("-$i day"));
        $xAxis[] = $date;
        $lineCount[] = $orderModel->count([
            'created_at[>=]' => $date . ' 00:00:00',
            'created_at[<]' => $date . ' 23:59:59',
            'sys_tag' => 'admin',
            'status'=>'complete',
        ]);
        $lineSum[] = $orderModel->sum('amount', [
            'created_at[>=]' => $date . ' 00:00:00',
            'created_at[<]' => $date . ' 23:59:59',
            'sys_tag' => 'admin',
            'status'=>'complete',
        ]);
    }
    $xAxis = array_reverse($xAxis);
    $lineCount = array_reverse($lineCount);
    $lineSum = array_reverse($lineSum);

    //按月
    $monthData = [];
    $xAxis = [];
    $lineCount = [];
    $lineSum = [];
    $all = Time::everyMonth(date("Y-01-01", time()), date("Y-m-d", time()));
    foreach ($all as $v) {
        $start = strtotime($v[0] . " 00:00:00");
        $end = strtotime($v[1] . " 23:59:59");
        $monthCount = $orderModel->count([
            'created_at[>=]' => $start,
            'created_at[<]' => $end,
            'sys_tag' => 'admin',
            'status'=>'complete',
        ]);
        $monthSum = $orderModel->sum('amount', [
            'created_at[>=]' => $start,
            'created_at[<]' => $end,
            'sys_tag' => 'admin',
            'status'=>'complete',
        ]);
        $xAxis[] = date("Y-m", $start);
        $lineCount[] = $monthCount;
        $lineSum[] = $monthSum;
    }
    $xAxisMonth = $xAxis;
    $lineCountMonth = $lineCount;
    $lineSumMonth = $lineSum;

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

    <div>
        <div class="stat-card">
            <?php
            $es = echats(['id' => 'order_stat_1', 'width' => '', 'height' => 400], [
                'title' => [
                    'text' => '订单'
                ],
                'yAxis' => "js:{}",
                'tooltip' => [
                    'trigger' => 'axis', // 触发类型：'item'（数据项图形触发）或 'axis'（坐标轴触发） 
                ],
                'grid' => [
                    'left' => '0%',    // 左边距
                    'right' => '0%',   // 右边距  
                    'containLabel' => true // 确保坐标轴标签不被截断
                ],
                'legend' => [
                    'data' => ['最近60天订单量', '最近60天订单金额'],
                ],
                'xAxis' => [
                    'data' => $xAxis
                ],
                'series' => [
                    [
                        'name' => '最近60天订单量',
                        'type' => 'line',
                        'data' => $lineCount,
                        'color' => '#1890FF',
                    ],
                    [
                        'name' => '最近60天订单金额',
                        'type' => 'line',
                        'data' => $lineSum,
                        'color' => '#FF7D4F',
                    ]
                ]
            ]);
            echo $es['html'];
            add_js($es['js']);
            ?>
        </div>


        <div class="stat-card mt-4">
            <?php
            $es = echats(['id' => 'order_stat_month_2', 'width' => '', 'height' => 400], [
                'title' => [
                    'text' => '今年订单统计'

                ],
                'yAxis' => "js:{}",
                'tooltip' => [
                    'trigger' => 'axis', // 触发类型：'item'（数据项图形触发）或 'axis'（坐标轴触发） 
                ],
                'grid' => [
                    'left' => '0%',    // 左边距
                    'right' => '0%',   // 右边距  
                    'containLabel' => true // 确保坐标轴标签不被截断
                ],
                'legend' => [
                    'data' => ['今年订单量', '今年订单金额'],
                ],
                'xAxis' => [
                    'data' => $xAxisMonth
                ],
                'series' => [
                    [
                        'name' => '今年订单量',
                        'type' => 'line',
                        'data' => $lineCountMonth,
                        'color' => '#1890FF',
                    ],
                    [
                        'name' => '今年订单金额',
                        'type' => 'line',
                        'data' => $lineSumMonth,
                        'color' => '#FF7D4F',
                    ]
                ]
            ]);
            echo $es['html'];
            add_js($es['js']);
            ?>
        </div>

    </div>
<?php
});
?>