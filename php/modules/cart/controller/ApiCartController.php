<?php

namespace modules\cart\controller;

use modules\cart\trait\CartTrait;
use OpenApi\Attributes as OA;

#[OA\Tag(name: '购物车', description: '购物车接口')]
class ApiCartController extends \core\ApiController
{
    use CartTrait;
    /**
     * 检查商品是否存在
     */
    protected $check_product = true;
    /**
     * 登录验证
     */
    protected $need_login = true;
    /**
     * 模型
     */
    protected $model = [
        'cart_item' => '\modules\cart\model\CartItemModel',
        'product' => '\modules\product\model\ProductModel',
        'spec' => '\modules\product\model\ProductSpecModel',
        'user' => '\modules\admin\model\UserModel',
    ];

    public function before()
    {
        if (!$this->uid) {
            return json_error(['msg' => lang('请先登录')]);
        }
    }

    /**
     * 获取购物车列表
     */
    #[OA\Get(
        path: '/cart/api-cart',
        summary: '获取购物车列表',
        tags: ['购物车'],
        parameters: [
            new OA\Parameter(name: 'type', description: '类型', in: 'query', schema: new OA\Schema(type: 'string', default: 'product')),
        ],
    )]
    public function actionIndex()
    {
        $this->index();
    }
    /**
     * 获取购物车列表
     */
    #[OA\Get(
        path: '/cart/api-cart/index-selected',
        summary: '获取购物车列表',
        tags: ['购物车'], 
    )]
    public function actionIndexSelected()
    {
        $this->selected();
    }


    /**
     * 添加商品到购物车
     */
    #[OA\Post(
        path: '/cart/api-cart/add',
        summary: '添加商品到购物车',
        tags: ['购物车'],
        parameters: [
            new OA\Parameter(name: 'product_id', description: '商品ID', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'num', description: '数量', in: 'query', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'str_1', description: '规格名称', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'str_2', description: '属性信息', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', description: '类型', in: 'query', schema: new OA\Schema(type: 'string', default: 'product')),
        ],
    )]
    public function actionAdd()
    {
        $data = [
            'user_id' => $this->uid,
            'product_id' => $this->post_data['product_id'] ?? '',
            'num' => (int)($this->post_data['num'] ?? 1),
            'spec' => $this->post_data['spec'] ?? '',
            'attr' => $this->post_data['attr'] ?? '',
            'str_1' => $this->post_data['str_1'] ?? '',
            'str_2' => $this->post_data['str_2'] ?? '',
        ];
        return $this->add($data);
    }

    /**
     * 更新购物车项数量
     */
    #[OA\Post(
        path: '/cart/api-cart/update',
        summary: '更新购物车项数量',
        tags: ['购物车'],
        parameters: [
            new OA\Parameter(name: 'id', description: '购物车项ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'num', description: '数量', in: 'query', required: true, schema: new OA\Schema(type: 'integer', minimum: 0)),
        ],
    )]
    public function actionUpdate()
    {
        $item_id = (int)($this->post_data['id'] ?? 0);
        $num = (int)($this->post_data['num'] ?? 0);

        return $this->update($item_id, $num, $this->uid);
    }
    /**
     * 选中购物车项
     */
    #[OA\Post(
        path: '/cart/api-cart/selected',
        summary: '选中购物车项',
        tags: ['购物车'],
        parameters: [
            new OA\Parameter(name: 'id', description: '购物车项ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'selected', description: '是否选中', in: 'query', required: true, schema: new OA\Schema(type: 'integer', minimum: 0, maximum: 1)),
        ],
    )]
    public function actionSelected()
    {
        $item_id = (int)($this->post_data['id'] ?? 0);
        $selected = (int)($this->post_data['selected'] ?? 0);

        if (!$item_id) {
            return json_error(['msg' => lang('缺少购物车项ID')]);
        }

        $this->model->cart_item->update([
            'selected' => $selected,
        ], [
            'id' => $item_id,
            'user_id' => $this->uid,
        ], true);
        json_success(['msg' => lang('操作成功')]);
    }
    /**
     * 全选
     */
    #[OA\Post(
        path: '/cart/api-cart/select-all',
        summary: '全选|全不选',
        tags: ['购物车'],
        parameters: [
            new OA\Parameter(name: 'selected', description: '是否选中', in: 'query', required: true, schema: new OA\Schema(type: 'integer', minimum: 0, maximum: 1)),
        ],
    )]
    public function actionSelectAll()
    {
        $selected = (int)($this->post_data['selected'] ?? 0);
        $this->model->cart_item->update([
            'selected' => $selected,
        ], [
            'user_id' => $this->uid,
        ], true);
        json_success(['msg' => lang('操作成功')]);
    }

    /**
     * 删除购物车项
     */
    #[OA\Post(
        path: '/cart/api-cart/delete',
        summary: '删除购物车项',
        tags: ['购物车'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionDelete()
    {
        $item_id = $this->post_data['id'];
        if (!$item_id) {
            return json_error(['msg' => lang('缺少购物车项ID')]);
        }
        return $this->delete($item_id, $this->uid);
    }

    /**
     * 清空购物车
     */
    #[OA\Post(
        path: '/cart/api-cart/clear',
        summary: '清空购物车',
        tags: ['购物车'],
        parameters: [
            new OA\Parameter(name: 'type', description: '类型', in: 'query', schema: new OA\Schema(type: 'string', default: 'product')),
        ],
    )]
    public function actionClear()
    {
        $type = $this->post_data['type'] ?? 'product';
        $user_id = $this->uid;
        $this->clear($user_id, $type);
    }
    /**
     * 获取购物车统计信息
     */
    #[OA\Get(
        path: '/cart/api-cart/count',
        summary: '获取购物车统计信息',
        tags: ['购物车'],
        parameters: [
            new OA\Parameter(name: 'type', description: '类型', in: 'query', schema: new OA\Schema(type: 'string', default: 'product')),
        ],
    )]
    public function actionCount()
    {
        $type = $this->post_data['type'] ?? 'product';
        $user_id = $this->uid;

        $count = $this->model->cart_item->count(
            [
                'user_id' => $user_id,
                'type' => $type,
            ]
        );

        return json_success([
            'data' =>  [
                'count' => $count,
            ]
        ]);
    }
}
