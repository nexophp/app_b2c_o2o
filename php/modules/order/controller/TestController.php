<?php

namespace modules\order\controller;

use modules\order\lib\OrderCreator;

class TestController extends  \core\AdminController
{
    public function actionIndex()
    {
        $this->actionCreateTestOrders();
         
    }
    
    /**
     * 生成20条测试订单信息
     */
    public function actionCreateTestOrders()
    {
        exit();
        $orderCreator = new OrderCreator();
        $results = [];
        
        // 模拟用户ID列表
        $userIds = [1, 2, 3, 4, 5];
        
        // 模拟商品数据
        $products = [
            ['id' => 'P001', 'title' => 'iPhone 15 Pro', 'price' => 7999.00, 'image' => 'iphone15.jpg'],
            ['id' => 'P002', 'title' => 'MacBook Pro', 'price' => 12999.00, 'image' => 'macbook.jpg'],
            ['id' => 'P003', 'title' => 'AirPods Pro', 'price' => 1999.00, 'image' => 'airpods.jpg'],
            ['id' => 'P004', 'title' => 'iPad Air', 'price' => 4599.00, 'image' => 'ipad.jpg'],
            ['id' => 'P005', 'title' => 'Apple Watch', 'price' => 2999.00, 'image' => 'watch.jpg'],
            ['id' => 'P006', 'title' => '小米手机', 'price' => 2999.00, 'image' => 'xiaomi.jpg'],
            ['id' => 'P007', 'title' => '华为笔记本', 'price' => 5999.00, 'image' => 'huawei.jpg'],
            ['id' => 'P008', 'title' => '戴尔显示器', 'price' => 1599.00, 'image' => 'dell.jpg'],
            ['id' => 'P009', 'title' => '罗技鼠标', 'price' => 299.00, 'image' => 'mouse.jpg'],
            ['id' => 'P010', 'title' => '机械键盘', 'price' => 599.00, 'image' => 'keyboard.jpg']
        ];
        
        // 模拟收货地址
        $addresses = [
            ['name' => '张三', 'phone' => '13800138001', 'address' => '北京市朝阳区建国路1号'],
            ['name' => '李四', 'phone' => '13800138002', 'address' => '上海市浦东新区陆家嘴2号'],
            ['name' => '王五', 'phone' => '13800138003', 'address' => '广州市天河区珠江新城3号'],
            ['name' => '赵六', 'phone' => '13800138004', 'address' => '深圳市南山区科技园4号'],
            ['name' => '钱七', 'phone' => '13800138005', 'address' => '杭州市西湖区文三路5号']
        ];
        
        // 支付方式
        $paymentMethods = ['wechat', 'alipay', 'bank_card', 'balance'];
        
        // 订单类型
        $orderTypes = ['product', 'service', 'virtual'];
        
        // 订单状态
        $orderStatuses = ['wait', 'paid', 'shipped', 'completed'];
        
        for ($i = 1; $i <= 20; $i++) {
            // 随机选择用户
            $userId = $userIds[array_rand($userIds)];
            
            // 随机选择1-3个商品
            $itemCount = rand(1, 3);
            $selectedProducts = array_rand($products, $itemCount);
            if (!is_array($selectedProducts)) {
                $selectedProducts = [$selectedProducts];
            }
            
            $items = [];
            $totalAmount = 0;
            
            foreach ($selectedProducts as $productIndex) {
                $product = $products[$productIndex];
                $num = rand(1, 3);
                $price = $product['price'];
                $oriPrice = $price + rand(0, 500); // 原价稍高
                
                $items[] = [
                    'product_id' => $product['id'],
                    'title' => $product['title'],
                    'image' => $product['image'],
                    'ori_price' => $oriPrice,
                    'price' => $price,
                    'num' => $num,
                    'str_1' => '颜色:' . ['黑色', '白色', '金色', '银色'][array_rand(['黑色', '白色', '金色', '银色'])],
                    'str_2' => '规格:' . ['标准版', '高配版', '旗舰版'][array_rand(['标准版', '高配版', '旗舰版'])]
                ];
                
                $totalAmount += $price * $num;
            }
            
            // 随机选择地址
            $address = $addresses[array_rand($addresses)];
            
            // 随机折扣 (90%-100%)
            $discount = rand(90, 100) / 100;
            $realAmount = round($totalAmount * $discount, 2);
            
            $orderData = [
                'user_id' => $userId,
                'type' => $orderTypes[array_rand($orderTypes)],
                'seller_id' => rand(1, 5),
                'store_id' => rand(1, 10),
                'amount' => $totalAmount,
                'real_amount' => $realAmount,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'address_id' => rand(1, 100),
                'address' => $address['address'],
                'phone' => $address['phone'],
                'name' => $address['name'],
                'desc' => '测试订单备注信息 ' . $i,
                'status' => $orderStatuses[array_rand($orderStatuses)],
                'items' => $items
            ];
            
            $result = $orderCreator->create($orderData);
            
            $results[] = [
                'order_index' => $i,
                'result' => $result
            ];
            
            // 添加延时避免订单号重复
            usleep(10000); // 10毫秒
        }
        
        // 统计结果
        $successCount = 0;
        $failCount = 0;
        $errors = [];
        
        foreach ($results as $result) {
            if ($result['result']['code'] == 0) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = $result;
            }
        }
        
        $response = [
            'code' => 0,
            'msg' => '测试订单生成完成',
            'data' => [
                'total' => 20,
                'success' => $successCount,
                'fail' => $failCount,
                'results' => $results,
                'errors' => $errors
            ]
        ];
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * 快速生成简单测试订单
     */
    public function actionQuickTestOrders()
    {
        $orderCreator = new OrderCreator();
        $results = [];
        
        for ($i = 1; $i <= 20; $i++) {
            $userId = rand(1, 5);
            
            $items = [
                [
                    'product_id' => 'TEST_' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'title' => '测试商品 ' . $i,
                    'price' => rand(10, 1000),
                    'num' => rand(1, 5),
                    'image' => 'test_product_' . $i . '.jpg'
                ]
            ];
            
            $extra = [
                'name' => '测试用户' . $userId,
                'phone' => '138' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'address' => '测试地址 ' . $i,
                'desc' => '快速测试订单 ' . $i
            ];
            
            $result = $orderCreator->quickCreate($userId, $items, $extra);
            $results[] = $result;
            
            usleep(5000); // 5毫秒延时
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => 0,
            'msg' => '快速测试订单生成完成',
            'data' => $results
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}