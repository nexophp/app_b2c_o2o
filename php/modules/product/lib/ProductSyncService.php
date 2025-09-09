<?php

namespace modules\product\lib;

use modules\product\model\ProductModel;
use modules\product\model\ProductTypeModel;
use modules\product\model\ProductSpecModel;

/**
 * 商品同步服务类
 * 用于同步商品信息、分类信息和规格信息到数据库
 */
class ProductSyncService
{
    private $productModel;
    private $typeModel;
    private $specModel;
    
    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->typeModel = new ProductTypeModel();
        $this->specModel = new ProductSpecModel();
    }
    
    /**
     * 同步商品分类
     * @param array $typeData 分类数据
     * @return int 分类ID
     */
    public function syncProductType($typeData)
    {
        // 验证必填字段
        if (empty($typeData['title'])) {
            json_error(['msg'=>lang('分类名称不能为空')]);
        }
        
        if (empty($typeData['slug'])) {
            json_error(['msg'=>lang('分类别名不能为空')]);
        }
        
        // 设置默认值
        $data = array_merge([
            'pid' => 0,
            'sort' => 0,
            'status' => 'success',
            'tag' => 'product',
            'user_id' => 0,
            'created_at' => time(),
            'updated_at' => time()
        ], $typeData);
        
        // 检查是否已存在相同slug的分类
        $existType = $this->typeModel->findOne(['slug' => $data['slug']]);
        if ($existType) {
            // 如果存在，更新数据
            $data['updated_at'] = time();
            $this->typeModel->update($data, ['id' => $existType['id']]);
            return $existType['id'];
        } else {
            // 如果不存在，创建新分类
            return $this->typeModel->insert($data);
        }
    }
    
    /**
     * 同步商品信息
     * @param array $productData 商品数据
     * @param array $specs 规格数据（可选）
     * @return int 商品ID
     */
    public function syncProduct($productData, $specs = [])
    {
        // 验证必填字段
        if (empty($productData['title'])) {
            json_error(['msg'=>lang('商品名称不能为空')]);
        }
        
        // 设置默认值
        $data = array_merge([
            'type_id' => null,
            'type_id_last' => 0,
            'brand_id' => 0,
            'desc' => '',
            'body' => '',
            'image' => '',
            'images' => [],
            'product_num' => '',
            'sku' => '',
            'spec_type' => empty($specs) ? 1 : 2, // 1单规格，2多规格
            'sort' => 0,
            'status' => 'success',
            'tag' => 'product',
            'user_tag' => 'admin',
            'admin_id' => 0,
            'store_id' => 0,
            'seller_id' => 0,
            'user_id' => 0,
            'sales' => 0,
            'views' => 0,
            'market_price' => 0.00,
            'price' => 0.00,
            'stock' => 0,
            'comment' => 0,
            'created_at' => time(),
            'updated_at' => time()
        ], $productData);
        
        // 处理分类ID
        if (isset($data['type_id']) && is_array($data['type_id'])) {
            $data['type_id_last'] = end($data['type_id']);
            $data['type_id'] = json_encode($data['type_id'], JSON_NUMERIC_CHECK);
        }
        
        // 处理图片数组
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = json_encode($data['images']);
        }
        
        // 检查是否已存在相同SKU或商品编号的商品
        $existProduct = null;
        if (!empty($data['sku'])) {
            $existProduct = $this->productModel->findOne(['sku' => $data['sku']]);
        } elseif (!empty($data['product_num'])) {
            $existProduct = $this->productModel->findOne(['product_num' => $data['product_num']]);
        }
        
        if ($existProduct) {
            // 如果存在，更新商品数据
            $data['updated_at'] = time();
            $this->productModel->update($data, ['id' => $existProduct['id']]);
            $productId = $existProduct['id'];
        } else {
            // 如果不存在，创建新商品
            $productId = $this->productModel->insert($data);
        }
        
        // 同步规格信息
        if (!empty($specs)) {
            $this->syncProductSpecs($productId, $specs);
        }
        
        return $productId;
    }
    
    /**
     * 同步商品规格
     * @param int $productId 商品ID
     * @param array $specs 规格数据
     */
    public function syncProductSpecs($productId, $specs)
    {
        if (empty($specs) || !is_array($specs)) {
            return;
        }
        
        // 删除原有规格
        $this->specModel->delete(['product_id' => $productId]);
        
        // 添加新规格
        foreach ($specs as $spec) {
            // 验证规格数据
            if (empty($spec['title'])) {
                json_error(['msg'=>lang('规格名称不能为空')]);
            }
            
            if (!isset($spec['price']) || $spec['price'] < 0) {
                json_error(['msg'=>lang('规格价格不能为空且不能小于0')]);
            }
            
            // 设置默认值
            $specData = array_merge([
                'product_id' => $productId,
                'sku' => '',
                'image' => '',
                'market_price' => 0.00,
                'stock' => 0,
                'status' => 0
            ], $spec);
            
            $this->specModel->insert($specData);
        }
    }
    
    /**
     * 批量同步商品
     * @param array $products 商品数据数组
     * @return array 同步结果
     */
    public function batchSyncProducts($products)
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($products as $index => $productInfo) {
            try {
                $productData = $productInfo['product'] ?? [];
                $specs = $productInfo['specs'] ?? [];
                
                $productId = $this->syncProduct($productData, $specs);
                
                $results[] = [
                    'index' => $index,
                    'success' => true,
                    'product_id' => $productId,
                    'message' => '同步成功'
                ];
                $successCount++;
            } catch (\Exception $e) {
                $results[] = [
                    'index' => $index,
                    'success' => false,
                    'product_id' => null,
                    'message' => $e->getMessage()
                ];
                $errorCount++;
            }
        }
        
        return [
            'total' => count($products),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results
        ];
    }
    
    /**
     * 批量同步分类
     * @param array $types 分类数据数组
     * @return array 同步结果
     */
    public function batchSyncTypes($types)
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($types as $index => $typeData) {
            try {
                $typeId = $this->syncProductType($typeData);
                
                $results[] = [
                    'index' => $index,
                    'success' => true,
                    'type_id' => $typeId,
                    'message' => '同步成功'
                ];
                $successCount++;
            } catch (\Exception $e) {
                $results[] = [
                    'index' => $index,
                    'success' => false,
                    'type_id' => null,
                    'message' => $e->getMessage()
                ];
                $errorCount++;
            }
        }
        
        return [
            'total' => count($types),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results
        ];
    }
    
    /**
     * 根据商品编号获取商品信息
     * @param string $productNum 商品编号
     * @return array|null
     */
    public function getProductByNum($productNum)
    {
        return $this->productModel->findOne(['product_num' => $productNum]);
    }
    
    /**
     * 根据SKU获取商品信息
     * @param string $sku SKU
     * @return array|null
     */
    public function getProductBySku($sku)
    {
        return $this->productModel->findOne(['sku' => $sku]);
    }
    
    /**
     * 根据分类别名获取分类信息
     * @param string $slug 分类别名
     * @return array|null
     */
    public function getTypeBySlug($slug)
    {
        return $this->typeModel->findOne(['slug' => $slug]);
    }
}