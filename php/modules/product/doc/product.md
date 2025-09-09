# 商品同步服务文档

## 概述

`ProductSyncService` 是一个用于同步商品信息、分类信息和规格信息到数据库的服务类。它提供了完整的商品数据同步解决方案，支持单个和批量操作。

## 功能特性

- ✅ 商品分类同步（支持层级分类）
- ✅ 商品信息同步（单规格/多规格）
- ✅ 商品规格同步
- ✅ 批量同步操作
- ✅ 智能去重（基于SKU/商品编号）
- ✅ 数据验证
- ✅ 错误处理
- ✅ 查询功能

## 类文件位置

```
modules/product/lib/ProductSyncService.php
```

## 基本用法

### 1. 引入和初始化

```php
use modules\product\lib\ProductSyncService;

// 创建同步服务实例
$syncService = new ProductSyncService();
```

### 2. 同步商品分类

#### 单个分类同步

```php
// 分类数据
$typeData = [
    'title' => '电子产品',
    'slug' => 'electronics',
    'description' => '各种电子产品分类',
    'image' => '/uploads/category/electronics.jpg',
    'pid' => 0, // 顶级分类
    'sort' => 10
];

// 同步分类
$typeId = $syncService->syncProductType($typeData);
echo "分类同步成功，ID: {$typeId}";
```

#### 批量分类同步

```php
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
echo "批量分类同步结果: 成功 {$typeResults['success_count']} 个，失败 {$typeResults['error_count']} 个";
```

### 3. 同步单规格商品

```php
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
echo "单规格商品同步成功，ID: {$productId}";
```

### 4. 同步多规格商品

```php
// 商品基本信息
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
echo "多规格商品同步成功，ID: {$productId}";
```

### 5. 批量同步商品

```php
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
echo "批量商品同步结果: 成功 {$batchResults['success_count']} 个，失败 {$batchResults['error_count']} 个";

// 显示详细结果
foreach ($batchResults['results'] as $result) {
    if ($result['success']) {
        echo "商品 {$result['index']} 同步成功，ID: {$result['product_id']}";
    } else {
        echo "商品 {$result['index']} 同步失败: {$result['message']}";
    }
}
```

### 6. 查询功能

```php
// 根据商品编号查询
$product = $syncService->getProductByNum('IP15PRO001');
if ($product) {
    echo "根据商品编号查询到商品: {$product['title']}";
}

// 根据SKU查询
$product = $syncService->getProductBySku('APPLE-IP15PRO-256GB');
if ($product) {
    echo "根据SKU查询到商品: {$product['title']}";
}

// 根据分类别名查询
$type = $syncService->getTypeBySlug('electronics');
if ($type) {
    echo "根据别名查询到分类: {$type['title']}";
}
```

## API 方法说明

### 分类相关方法

#### `syncProductType($typeData)`

同步商品分类信息

**参数：**
- `$typeData` (array): 分类数据数组

**必填字段：**
- `title`: 分类名称
- `slug`: 分类别名

**可选字段：**
- `description`: 分类描述
- `image`: 分类图片
- `pid`: 父分类ID（默认0）
- `sort`: 排序（默认0）
- `status`: 状态（默认'success'）

**返回值：** 分类ID

#### `batchSyncTypes($types)`

批量同步分类

**参数：**
- `$types` (array): 分类数据数组

**返回值：** 同步结果数组

### 商品相关方法

#### `syncProduct($productData, $specs = [])`

同步商品信息

**参数：**
- `$productData` (array): 商品数据
- `$specs` (array): 规格数据（可选）

**必填字段：**
- `title`: 商品名称

**重要字段：**
- `product_num`: 商品编号（用于去重）
- `sku`: 商品SKU（用于去重）
- `type_id`: 分类ID数组
- `spec_type`: 规格类型（1单规格，2多规格）
- `price`: 商品价格
- `stock`: 库存

**返回值：** 商品ID

#### `batchSyncProducts($products)`

批量同步商品

**参数：**
- `$products` (array): 商品数据数组，每个元素包含 `product` 和可选的 `specs`

**返回值：** 同步结果数组

### 规格相关方法

#### `syncProductSpecs($productId, $specs)`

同步商品规格

**参数：**
- `$productId` (int): 商品ID
- `$specs` (array): 规格数据数组

**规格必填字段：**
- `title`: 规格名称
- `price`: 规格价格

### 查询方法

#### `getProductByNum($productNum)`

根据商品编号查询商品

#### `getProductBySku($sku)`

根据SKU查询商品

#### `getTypeBySlug($slug)`

根据分类别名查询分类

## 数据表结构

### 商品表 (product)

主要字段：
- `id`: 主键
- `title`: 商品名称
- `desc`: 商品描述
- `body`: 商品详情
- `image`: 主图
- `images`: 图片列表（JSON）
- `product_num`: 商品编号
- `sku`: 商品SKU
- `type_id`: 分类ID（JSON）
- `type_id_last`: 最后一级分类ID
- `spec_type`: 规格类型（1单规格，2多规格）
- `price`: 价格
- `market_price`: 市场价
- `stock`: 库存
- `status`: 状态

### 分类表 (product_type)

主要字段：
- `id`: 主键
- `title`: 分类名称
- `slug`: 分类别名
- `description`: 分类描述
- `image`: 分类图片
- `pid`: 父分类ID
- `sort`: 排序
- `status`: 状态

### 规格表 (product_spec)

主要字段：
- `id`: 主键
- `product_id`: 商品ID
- `title`: 规格名称
- `sku`: 规格SKU
- `image`: 规格图片
- `price`: 规格价格
- `market_price`: 市场价
- `stock`: 库存
- `status`: 状态

## 注意事项

1. **去重机制**：系统会根据 `sku` 或 `product_num` 判断商品是否已存在，存在则更新，不存在则新增

2. **分类处理**：`type_id` 支持数组格式，系统会自动转换为JSON存储，并设置 `type_id_last` 为最后一级分类

3. **规格处理**：多规格商品必须设置 `spec_type = 2` 并提供规格数据

4. **图片处理**：`images` 字段支持数组格式，系统会自动转换为JSON存储

5. **错误处理**：所有验证错误都会通过 `json_error()` 返回，符合框架规范

6. **时间戳**：系统会自动设置 `created_at` 和 `updated_at` 时间戳

## 完整示例

参考文件：`modules/product/lib/ProductSyncExample.php`

该文件包含了所有功能的完整使用示例，可以直接运行查看效果。