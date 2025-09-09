# 购物车模块 (Cart Module)

购物车管理模块，支持商品添加、删除、修改等功能。

## 功能特性

- 购物车管理：创建、查看、删除购物车
- 购物车项目管理：添加商品、修改数量、删除商品
- 用户购物车：支持用户级别的购物车管理
- 商品规格支持：支持商品规格参数存储
- 统计功能：购物车数据统计和分析
- API接口：完整的RESTful API支持

## 数据库表结构

### cart 表
- `id`: 主键
- `type`: 购物车类型
- `seller_id`: 卖家ID
- `user_id`: 用户ID
- `num`: 商品总数量
- `amount`: 总金额
- `created_at`: 创建时间
- `updated_at`: 更新时间

### cart_item 表
- `id`: 主键
- `cart_id`: 购物车ID
- `product_id`: 商品ID
- `title`: 商品标题
- `price`: 商品价格
- `num`: 商品数量
- `param_1`: 规格参数1 (JSON)
- `param_2`: 规格参数2 (JSON)
- `created_at`: 创建时间
- `updated_at`: 更新时间

## API 接口

### 获取购物车
```
GET /api/cart
```

### 添加商品到购物车
```
POST /api/cart/add
参数：
- product_id: 商品ID
- num: 数量
- param_1: 规格参数1 (可选)
- param_2: 规格参数2 (可选)
```

### 更新购物车项目数量
```
POST /api/cart/update
参数：
- id: 购物车项目ID
- num: 新数量
```

### 删除购物车项目
```
POST /api/cart/remove
参数：
- id: 购物车项目ID
```

### 批量删除购物车项目
```
POST /api/cart/batch-remove
参数：
- ids: 购物车项目ID数组
```

### 清空购物车
```
POST /api/cart/clear
```

### 获取购物车统计
```
GET /api/cart/count
```

## 后台管理

### 购物车列表
- 路径：`/cart/cart/index`
- 功能：查看所有购物车，支持筛选和搜索

### 购物车详情
- 路径：`/cart/cart/view`
- 功能：查看购物车详细信息和商品列表

### 购物车项目管理
- 路径：`/cart/cart/items`
- 功能：管理所有购物车项目

### 购物车统计
- 路径：`/cart/cart/stats`
- 功能：查看购物车相关统计数据

## 使用示例

### 添加商品到购物车
```php
use modules\cart\data\CartData;

$cartData = new CartData();
$result = $cartData->addToCart([
    'user_id' => 1,
    'product_id' => 100,
    'num' => 2,
    'param_1' => json_encode(['color' => '红色', 'size' => 'L']),
    'param_2' => json_encode(['material' => '棉质'])
]);
```

### 获取用户购物车
```php
use modules\cart\data\CartData;

$cartData = new CartData();
$cart = $cartData->getUserCart(1); // 用户ID为1的购物车
```

### 更新购物车项目数量
```php
use modules\cart\data\CartData;

$cartData = new CartData();
$result = $cartData->updateCartItem(1, 5); // 将ID为1的项目数量更新为5
```

## 依赖关系

- 依赖 `product` 模块获取商品信息
- 依赖 `user` 模块获取用户信息
- 使用 `AppModel` 基础模型类
- 使用 `ApiController` 基础API控制器

## 版本信息

- 版本：1.0.0
- 作者：sunkangchina
- 邮箱：68103403@qq.com
- 许可证：MIT