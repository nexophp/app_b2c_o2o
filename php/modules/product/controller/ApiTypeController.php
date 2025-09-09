<?php

namespace modules\product\controller;

use modules\product\data\TypeData; 
use modules\product\data\ProductData;

use OpenApi\Attributes as OA;
#[OA\Tag(name: '商品分类', description: '分类接口')] 
class ApiTypeController extends \core\ApiController
{
    protected $need_login = false;
    protected $model = [
        'product_type' => '\modules\product\model\ProductTypeModel',
    ];
    /**
     * 获取分类 
     */
    #[OA\Get(
        path: '/product/api-type',
        summary: '获取分类信息',
        tags: ['商品分类'],
    )]
    public function actionIndex()
    {
        $list = db_get('product_type', ['id', 'title', 'pid', 'image'], [
            'status' => 'success',
            'ORDER' => [
                'sort' => 'DESC',
                'id' => 'ASC'
            ]
        ]);
        foreach ($list as $key => &$value) { 
            $value['products'] = ProductData::getByType($value['id'],true);
            TypeData::image($value);
        }
        $tree = array_to_tree($list, 'id', 'pid', 'children');
        $tree = array_values($tree);
        json_success(['data' => $tree]);
    }
}
