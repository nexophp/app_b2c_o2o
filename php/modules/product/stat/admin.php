<?php

use lib\Time;


add_action("admin.index", function () {


    // 获取商品模型
    $productModel = new \modules\product\model\ProductModel();

    // 获取时间范围
    $thisMonth = Time::get('month');
    $lastMonth = Time::get('lastmonth');

    // 商品总数
    $totalProducts = $productModel->count([
        'sys_tag' => 'product',
        'user_tag' => 'admin'
    ]);

    // 本月新增商品
    $thisMonthProducts = $productModel->count([
        'created_at[>=]' => $thisMonth[0],
        'created_at[<]' => $thisMonth[1],
        'sys_tag' => 'product',
        'user_tag' => 'admin'
    ]);

    // 上月新增商品
    $lastMonthProducts = $productModel->count([
        'created_at[>=]' => $lastMonth[0],
        'created_at[<]' => $lastMonth[1],
        'sys_tag' => 'product',
        'user_tag' => 'admin'
    ]);

    // 计算增长
    $productGrowth = $thisMonthProducts - $lastMonthProducts;
    $productGrowthText = $productGrowth >= 0 ? "+{$productGrowth}" : "{$productGrowth}";

    // 热销商品（销量大于100的商品）
    $hotProducts = $productModel->count([
        'sales[>]' => 100,
        'sys_tag' => 'product',
        'user_tag' => 'admin'
    ]);

    // 库存预警（库存小于10的商品）
    $lowStockProducts = $productModel->count([
        'stock[<]' => 10,
        'sys_tag' => 'product',
        'user_tag' => 'admin'
    ]);

?>
    <h2 class="mb-4 mt-4"><?= lang('商品统计概览') ?></h2>

    <div class="stats-container">
        <!-- 商品总数 -->
        <div class="stat-card blue">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('商品总数') ?></h3>
                    <h2 class="stat-value"><?= $totalProducts ?></h2>
                    <p class="stat-change <?= $productGrowth >= 0 ? 'increase' : 'decrease' ?>">
                        <?= $productGrowthText ?> <?= lang('较上月') ?>
                    </p>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>

        <!-- 热销商品 -->
        <div class="stat-card green">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('热销商品') ?></h3>
                    <h2 class="stat-value"><?= $hotProducts ?></h2>
                    <p class="stat-description"><?= lang('月销') ?> > 100<?= lang('件') ?></p>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-fire"></i>
                </div>
            </div>
        </div>

        <!-- 库存预警 -->
        <div class="stat-card orange">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('库存预警') ?></h3>
                    <h2 class="stat-value"><?= $lowStockProducts ?></h2>
                    <p class="stat-description"><?= lang('库存') ?> < 10<?= lang('件') ?></p>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <!-- 新品上架 -->
        <div class="stat-card red">
            <div class="stat-content">
                <div class="stat-text">
                    <h3 class="stat-title"><?= lang('新品上架') ?></h3>
                    <h2 class="stat-value"><?= $thisMonthProducts ?></h2>
                    <p class="stat-change increase"><?= lang('本月新增') ?></p>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-stars"></i>
                </div>
            </div>
        </div>
    </div>

<?php
});
