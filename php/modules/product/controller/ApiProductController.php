<?php

namespace modules\product\controller;

use modules\product\data\ProductData;
use OpenApi\Attributes as OA;
use modules\product\lib\ProductStock;


#[OA\Tag(name: '商品', description: '商品接口')]
class ApiProductController extends \core\ApiController
{
    protected $need_login = false;
    protected $product_type = 'product';
    protected $user_tag = 'admin';
    protected $model = [
        'product' => '\modules\product\model\ProductModel',
        'product_spec' => '\modules\product\model\ProductSpecModel',
        'product_type' => '\modules\product\model\ProductTypeModel',
        'product_brand' => '\modules\product\model\ProductBrandModel',
    ];
    /**
     * 
     * 获取商品
     */
    #[OA\Get(
        path: '/product/api-product',
        summary: '获取产品信息',
        tags: ['商品'],
        parameters: [
            new OA\Parameter(name: 'title', description: '产品名称', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type_id', description: '产品类型', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'product_id', description: '产品ID', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', description: '只显示条数', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionIndex()
    {
        $where = [];
        $title = $this->post_data['title'] ?? '';
        $type_id = $this->post_data['type_id'] ?? '';
        $brand_id = $this->post_data['brand_id'] ?? '';
        $status = $this->post_data['status'] ?? '';
        $product_id = $this->post_data['product_id'] ?: '';
        $limit = $this->post_data['limit'] ?: '';
        $sort = $this->post_data['sort'];
        if ($title) {
            $where['title[~]'] = $title;
        }
        if ($type_id) {
            if (is_array($type_id)) {
                $type_id_in  = [];
                foreach ($type_id as $tid) {
                    $type_in = $this->model->product_type->getTreeId($tid) ?: [];
                    $type_in[] = $tid;
                    $type_id_in = array_merge($type_id_in, $type_in);
                }
                $where['type_id_last'] = $type_id_in;
            } else {
                $type_in = $this->model->product_type->getTreeId($type_id) ?: [];
                $type_in[] = $type_id;
                $where['type_id_last'] = $type_in;
            }
        }
        if ($brand_id) {
            $where['brand_id'] = $brand_id;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        $where['ORDER'] = ['sort' => 'DESC', 'id' => 'DESC'];
        if($sort){
            switch($sort){
                case 'sales':
                    $where['ORDER'] = ['sales' => 'DESC', 'id' => 'DESC'];
                    break;

                case 'recent':
                    $where['ORDER'] = ['id' => 'DESC'];
                    break;
                case 'recommend':
                    $where['ORDER'] = ['recommend' => 'DESC','recommend_at'=>'DESC', 'id' => 'DESC'];
                    break;
            }

        }
        $where['product_type'] = $this->product_type;
        $where['user_tag'] = $this->user_tag;
        $where['status'] = 'success';
        if ($product_id) {
            $where['id'] = $product_id;
        }
        if ($limit) {
            $where['LIMIT'] = $limit;
        }
        if ($product_id || $limit) {

            $all = $this->model->product->find($where);
            $list = [];
            foreach ($all as $v) {
                $list[] = ProductData::resetData($v);
            }
            json(['data' => $list]);
        }
        $list = $this->model->product->pager($where);
        foreach ($list['data'] as &$v) {
            $v = ProductData::resetData($v);
        }
        json($list);
    }
    /**
     * 获取商品详情
     */
    #[OA\Get(
        path: '/product/api-product/view',
        summary: '获取产品详情',
        tags: ['商品'],
        parameters: [
            new OA\Parameter(name: 'id', description: '产品ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionView()
    {
        $id = $this->post_data['id'] ?? '';
        if ($id) {
            $where = ['id' => $id, 'product_type' => $this->product_type];
            $info = $this->model->product->findOne($where);
            if ($info) {
                $info = ProductData::resetData($info);
                json_success(['data' => $info]);
            }
        }
        json_error(['msg' => lang('参数错误')]);
    }

    /**
     * 检测库存
     */
    #[OA\Get(
        path: '/product/api-product/check-stock',
        summary: '检测库存',
        tags: ['商品'],
        parameters: [
            new OA\Parameter(name: 'product_id', description: '产品ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'spec', description: '规格', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'num', description: '数量', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionCheckStock()
    {
        $id = $this->post_data['product_id'] ?? '';
        $spec = $this->post_data['spec'] ?? '';
        $num = $this->post_data['num'] ?: 1;
        $info = $this->model->product->findOne(['id' => $id]);
        if (!$info) {
            json_error(['msg' => lang('参数错误')]);
        }
        $item = [
            'product_id' => $id,
            'spec' => $spec,
            'num' => $num,
        ];
        if (!ProductStock::check($item)) {
            json_error(['msg' => lang('库存不足')]);
        }
        json_success([]);
    }
}
