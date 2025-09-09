<?php 
namespace app\demo\controller;
use modules\product\lib\ProductSyncService;

class SiteController extends \core\AdminController
{   
    /**
     * 导入商品数据
     * https://apifox.com/apidoc/shared/fa9274ac-362e-4905-806b-6135df6aa90e/api-24945669
     */
    public function actionIndex()
    {
       $url = 'http://pcapi-xiaotuxian-front-devtest.itheima.net/home/category/head'; 
       $data = curl_get($url);
       $list = $data['result'];
       
       $syncService = new ProductSyncService();
       $typeResults = [];
       $productResults = [];
        
       foreach($list as $v){
           // 同步主分类
            $mainImage = $v['picture'] ?? '';
            if (!empty($mainImage) && strpos($mainImage, '://') !== false) {
                $mainImage = download_file($mainImage);
                
            }
            
            $mainTypeData = [
                'title' => $v['name'],
                'slug' => 'category_' . $v['id'],
                'pid' => 0,
                'image' => $mainImage,
                'sort' => 0
            ];
           
           try {
               $mainTypeId = $syncService->syncProductType($mainTypeData);
               $typeResults[] = ['type' => 'main', 'name' => $v['name'], 'id' => $mainTypeId, 'status' => 'success'];
               
               // 同步子分类
               if (!empty($v['children']) && is_array($v['children'])) {
                   foreach ($v['children'] as $child) {
                       $childImage = $child['picture'] ?? '';
                        if (!empty($childImage) && strpos($childImage, '://') !== false) {
                            $childImage = download_file($childImage);
                        }
                        
                        $childTypeData = [
                            'title' => $child['name'],
                            'slug' => 'category_' . $child['id'],
                            'pid' => $mainTypeId,
                            'image' => $childImage,
                            'sort' => 0
                        ];
                       
                       try {
                           $childTypeId = $syncService->syncProductType($childTypeData);
                           $typeResults[] = ['type' => 'child', 'name' => $child['name'], 'id' => $childTypeId, 'status' => 'success'];
                       } catch (\Exception $e) {
                           $typeResults[] = ['type' => 'child', 'name' => $child['name'], 'error' => $e->getMessage(), 'status' => 'error'];
                       }
                   }
               }
               
               // 同步商品
               if (!empty($v['goods']) && is_array($v['goods'])) {
                   foreach ($v['goods'] as $goods) {
                       $productImage = $goods['picture'] ?? '';
                       
                        if (!empty($productImage) && strpos($productImage, '://') !== false) { 
                            $productImage = download_file($productImage); 

                        }
                        
                        $productData = [
                            'title' => $goods['name'],
                            'desc' => $goods['desc'] ?? '',
                            'image' => $productImage,
                            'product_num' => 'goods_' . $goods['id'],
                            'sku' => 'sku_' . $goods['id'],
                            'price' => floatval($goods['price'] ?? 0),
                            'market_price' => floatval($goods['price'] ?? 0),
                            'type_id' => [$mainTypeId],
                            'type_id_last' => $mainTypeId,
                            'stock' => 100, // 默认库存
                            'sales' => intval($goods['orderNum'] ?? 0)
                        ]; 
                       try {
                           $productId = $syncService->syncProduct($productData);
                           $productResults[] = ['name' => $goods['name'], 'id' => $productId, 'status' => 'success'];
                       } catch (\Exception $e) {
                           $productResults[] = ['name' => $goods['name'], 'error' => $e->getMessage(), 'status' => 'error'];
                       }
                   }
               }
               
           } catch (\Exception $e) {
               $typeResults[] = ['type' => 'main', 'name' => $v['name'], 'error' => $e->getMessage(), 'status' => 'error'];
           }
       }
       
       // 输出同步结果
       $result = [
           'message' => '数据同步完成',
           'type_results' => $typeResults,
           'product_results' => $productResults,
           'summary' => [
               'total_types' => count($typeResults),
               'success_types' => count(array_filter($typeResults, function($r) { return $r['status'] === 'success'; })),
               'total_products' => count($productResults),
               'success_products' => count(array_filter($productResults, function($r) { return $r['status'] === 'success'; }))
           ]
       ];
       
       json_success($result);
    }
}