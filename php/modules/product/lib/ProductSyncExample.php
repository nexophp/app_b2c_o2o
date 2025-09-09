<?php
exit();
/**
 * 商品同步服务使用示例
 * 演示如何使用ProductSyncService类进行商品、分类和规格的同步
 */
 
use modules\product\lib\ProductSyncService;

// 创建同步服务实例
$syncService = new ProductSyncService();

// 示例1: 同步商品分类
echo "=== 同步商品分类示例 ===\n";

try {
    // 单个分类同步
    $typeData = [
        'title' => '电子产品',
        'slug' => 'electronics',
        'description' => '各种电子产品分类',
        'image' => '/uploads/category/electronics.jpg',
        'pid' => 0, // 顶级分类
        'sort' => 10
    ];
    
    $typeId = $syncService->syncProductType($typeData);
    echo "分类同步成功，ID: {$typeId}\n";
    
    // 批量分类同步
    $types = [
        [
            'title' => '手机',
            'slug' => 'mobile-phone',
            'description' => '智能手机分类',
            'pid' => $typeId, // 作为电子产品的子分类
            'sort' => 1
        ],
        [
            'title' => '电脑',
            'slug' => 'computer',
            'description' => '电脑设备分类',
            'pid' => $typeId,
            'sort' => 2
        ]
    ];
    
    $typeResults = $syncService->batchSyncTypes($types);
    echo "批量分类同步结果: 成功 {$typeResults['success_count']} 个，失败 {$typeResults['error_count']} 个\n";
    
} catch (Exception $e) {
    echo "分类同步失败: " . $e->getMessage() . "\n";
}

// 示例2: 同步单规格商品
echo "\n=== 同步单规格商品示例 ===\n";

try {
    $productData = [
        'title' => 'iPhone 15 Pro',
        'desc' => '苹果最新款智能手机',
        'body' => '<p>详细的商品描述内容</p>',
        'image' => '/uploads/product/iphone15pro.jpg',
        'images' => ['/uploads/product/iphone15pro_1.jpg', '/uploads/product/iphone15pro_2.jpg'],
        'product_num' => 'IP15PRO001',
        'sku' => 'APPLE-IP15PRO-256GB',
        'type_id' => [$typeId], // 分类ID数组
        'price' => 8999.00,
        'market_price' => 9999.00,
        'stock' => 100,
        'spec_type' => 1 // 单规格
    ];
    
    $productId = $syncService->syncProduct($productData);
    echo "单规格商品同步成功，ID: {$productId}\n";
    
} catch (Exception $e) {
    echo "单规格商品同步失败: " . $e->getMessage() . "\n";
}

// 示例3: 同步多规格商品
echo "\n=== 同步多规格商品示例 ===\n";

try {
    $productData = [
        'title' => 'MacBook Pro',
        'desc' => '苹果专业级笔记本电脑',
        'body' => '<p>MacBook Pro详细介绍</p>',
        'image' => '/uploads/product/macbook_pro.jpg',
        'images' => ['/uploads/product/macbook_pro_1.jpg'],
        'product_num' => 'MBP2024001',
        'type_id' => [$typeId], // 分类ID
        'spec_type' => 2 // 多规格
    ];
    
    // 规格数据
    $specs = [
        [
            'title' => '14英寸 M3 512GB',
            'sku' => 'MBP-14-M3-512GB',
            'price' => 15999.00,
            'market_price' => 16999.00,
            'stock' => 50,
            'image' => '/uploads/product/macbook_14.jpg'
        ],
        [
            'title' => '16英寸 M3 Pro 1TB',
            'sku' => 'MBP-16-M3PRO-1TB',
            'price' => 19999.00,
            'market_price' => 21999.00,
            'stock' => 30,
            'image' => '/uploads/product/macbook_16.jpg'
        ]
    ];
    
    $productId = $syncService->syncProduct($productData, $specs);
    echo "多规格商品同步成功，ID: {$productId}\n";
    
} catch (Exception $e) {
    echo "多规格商品同步失败: " . $e->getMessage() . "\n";
}

// 示例4: 批量同步商品
echo "\n=== 批量同步商品示例 ===\n";

try {
    $products = [
        [
            'product' => [
                'title' => 'iPad Air',
                'desc' => '轻薄平板电脑',
                'product_num' => 'IPAD-AIR-001',
                'sku' => 'APPLE-IPAD-AIR-64GB',
                'price' => 4399.00,
                'stock' => 80,
                'type_id' => [$typeId]
            ]
        ],
        [
            'product' => [
                'title' => 'AirPods Pro',
                'desc' => '无线降噪耳机',
                'product_num' => 'AIRPODS-PRO-001',
                'price' => 1999.00,
                'stock' => 200,
                'type_id' => [$typeId]
            ],
            'specs' => [
                [
                    'title' => '标准版',
                    'sku' => 'AIRPODS-PRO-STANDARD',
                    'price' => 1999.00,
                    'stock' => 200
                ]
            ]
        ]
    ];
    
    $batchResults = $syncService->batchSyncProducts($products);
    echo "批量商品同步结果: 成功 {$batchResults['success_count']} 个，失败 {$batchResults['error_count']} 个\n";
    
    // 显示详细结果
    foreach ($batchResults['results'] as $result) {
        if ($result['success']) {
            echo "  商品 {$result['index']} 同步成功，ID: {$result['product_id']}\n";
        } else {
            echo "  商品 {$result['index']} 同步失败: {$result['message']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "批量商品同步失败: " . $e->getMessage() . "\n";
}

// 示例5: 查询功能
echo "\n=== 查询功能示例 ===\n";

try {
    // 根据商品编号查询
    $product = $syncService->getProductByNum('IP15PRO001');
    if ($product) {
        echo "根据商品编号查询到商品: {$product['title']}\n";
    }
    
    // 根据SKU查询
    $product = $syncService->getProductBySku('APPLE-IP15PRO-256GB');
    if ($product) {
        echo "根据SKU查询到商品: {$product['title']}\n";
    }
    
    // 根据分类别名查询
    $type = $syncService->getTypeBySlug('electronics');
    if ($type) {
        echo "根据别名查询到分类: {$type['title']}\n";
    }
    
} catch (Exception $e) {
    echo "查询失败: " . $e->getMessage() . "\n";
}

echo "\n=== 同步示例完成 ===\n";