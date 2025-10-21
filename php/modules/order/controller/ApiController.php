<?php

namespace modules\order\controller;

use modules\order\trait\OrderTrait;
use modules\order\lib\OrderCreator;
use modules\order\lib\OrderConfirm;
use OpenApi\Attributes as OA;
use modules\order\lib\OrderLogic;
use modules\order\lib\OrderClose;
use modules\payment\lib\WeiXin;

#[OA\Tag(name: '订单', description: '订单管理接口')]
class ApiController extends \core\ApiController
{
    protected $sys_tag = '';
    protected $type = 'product';

    protected $model = [
        'order' => '\modules\order\model\OrderModel',
        'order_item' => '\modules\order\model\OrderItemModel',
        'order_paid_info' => '\modules\order\model\OrderPaidInfoModel',
        'order_refund' => '\modules\order\model\OrderRefundModel',
    ];

    public function before()
    {
        if (g('sys_tag')) {
            $this->sys_tag = g('sys_tag');
        }
        if (g('type')) {
            $this->type = g('type');
        }
    }
    /**
     * 是否购买过此商品 
     */
    #[OA\Get(
        path: '/order/api/has-buy',
        summary: '是否购买过此商品',
        tags: ['订单'],
        parameters: [
            new OA\Parameter(name: 'product_id', description: '商品ID', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],

    )]
    public function actionHasBuy()
    {
        $product_id = $this->post_data['product_id'];
        if (!$product_id) {
            json_error(['msg' => lang('商品ID不能为空')]);
        }
        $order_item = $this->model->order_item->findOne([
            'user_id' => $this->uid,
            'product_id' => $product_id,
        ]);
        if ($order_item) {
            json_success([]);
        }
        json_error([]);
    }
    /**
     * 订单统计 
     */
    #[OA\Get(
        path: '/order/api/stat',
        summary: '订单统计',
        tags: ['订单'],
    )]
    public function actionStat()
    {
        $data = get_order_stat_list($this->uid, $this->type, $this->sys_tag);
        return json_success(['data' => $data]);
    }
    /**
     * 获取订单列表
     */
    #[OA\Get(
        path: '/order/api/index',
        summary: '获取订单列表',
        tags: ['订单'],
        parameters: [
            new OA\Parameter(name: 'status', description: '订单状态', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionIndex()
    {
        $status = $this->post_data['status'] ?? '';

        $where = ['user_id' => $this->uid];
        if (!empty($status)) {
            $where['status'] = $status;
        }
        $where['ORDER'] = [
            'id' => 'DESC'
        ];
        $where['status[!]'] = ['cancel', 'delete'];
        if ($this->sys_tag) {
            $where['sys_tag'] = $this->sys_tag;
        }
        $where['type'] = $this->type;

        $all = $this->model->order->pager($where);

        foreach ($all['data'] as $key => &$value) {
            $value->products;
            if($value->status == 'wait'){
                WeiXin::query($value->order_num);
            }
        }

        json($all);
    }

    /**
     * 获取订单详情
     */
    #[OA\Get(
        path: '/order/api/view',
        summary: '获取订单详情',
        tags: ['订单'],
        parameters: [
            new OA\Parameter(name: 'id', description: '订单ID', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionView()
    {
        $id = $this->post_data['id'] ?? '';

        if (empty($id)) {
            return json_error(['msg' => lang('订单ID不能为空')]);
        }
        $where = [
            'id' => $id,
            'user_id' => $this->uid,
        ];
        $order = $this->model->order->findOne($where);
        if (!$order) {
            return json_error(['msg' => lang('订单不存在')]);
        }
        if ($order->status == 'delete') {
            return json_error(['msg' => lang('订单不存在')]);
        }
        $order->products;
        $order->logic_info = OrderLogic::get($order->order_num) ?? [];
        if ($order->can_pay_time == 0 && $order->status == 'wait') {
            db_action(function () use ($order) { 
                OrderClose::do($order->id);
            });
        }
        $order->products;

        $order->address = get_clean_address($order->address);
        return json_success(['data' => $order]);
    }

    /**
     * 创建订单
     */
    #[OA\Post(
        path: '/order/api/create',
        summary: '创建订单',
        tags: ['订单'],
        parameters: [
            new OA\Parameter(name: 'items', description: '商品列表', in: 'query', required: true, schema: new OA\Schema(type: 'array')),
            new OA\Parameter(name: 'type', description: '订单类型', in: 'query', schema: new OA\Schema(type: 'string', default: 'product')),

            new OA\Parameter(name: 'seller_id', description: '商家ID', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'store_id', description: '店铺ID', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'payment_method', description: '支付方式', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'address_id', description: '地址ID', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'address', description: '收货地址', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'phone', description: '收货电话', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'name', description: '收货人姓名', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'desc', description: '订单备注', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'amount', description: '订单金额', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'real_amount', description: '实际金额', in: 'query', schema: new OA\Schema(type: 'number')),

        ],
    )]
    public function actionCreate()
    {
        $orderData = [
            'user_id' => $this->uid,
            'type' => $this->post_data['type'] ?? 'product',
            'seller_id' => $this->post_data['seller_id'] ?? null,
            'store_id' => $this->post_data['store_id'] ?? null,
            'payment_method' => $this->post_data['payment_method'] ?: 'weixin',
            'address_id' => $this->post_data['address_id'] ?? null,
            'address' => $this->post_data['address'] ?? '',
            'phone' => $this->post_data['phone'] ?? '',
            'name' => $this->post_data['name'] ?? '',
            'desc' => $this->post_data['desc'] ?? '',
            'status' => 'wait',
            'items' => $this->post_data['items'] ?? [],
            'sys_tag' => $this->sys_tag,
            'shipping' => $this->post_data['shipping'] ?: 0,

        ];

        if (empty($orderData['items'])) {
            return json_error(['msg' => lang('商品信息不能为空')]);
        }
        if ($orderData['type'] == 'product') {
            if (!$orderData['name'] || !$orderData['phone'] || !$orderData['address']) {
                return json_error(['msg' => lang('请填写收货人姓名、手机号、地址')]);
            }
        }
        // 确认订单
        $confirm = OrderConfirm::confirm($orderData['items'], $orderData['address_id'] ?? 0);

        $orderData['amount'] = $confirm['amount'];
        $orderData['real_amount'] = $confirm['real_amount'];
        if (!$orderData['amount'] || !$orderData['real_amount']) {
            return json_error(['msg' => lang('订单金额不能为空')]);
        }
        if ($orderData['amount'] < 0 || $orderData['real_amount'] < 0) {
            return json_error(['msg' => lang('生成订单繁忙')]);
        }
        // 使用OrderCreator创建订单
        $result = [];
        db_action(function () use ($orderData, &$result) {
            $orderCreator = new OrderCreator();
            $result = $orderCreator->create($orderData);
        });
        $order_id = $result['id'];
        $order_num = $result['order_num'];

        if ($order_id) {
            return json_success([
                'data' => [
                    'id' => $order_id,
                    'order_num' => $order_num
                ],
                'msg' => lang('订单创建成功')
            ]);
        } else {
            return json_error(['msg' => $result['msg'] ?? lang('订单创建失败')]);
        }
    }

    /**
     * 更新订单状态
     */
    #[OA\Post(
        path: '/order/api/update',
        summary: '更新订单状态',
        tags: ['订单'],
        parameters: [
            new OA\Parameter(name: 'id', description: '订单ID', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', description: '订单状态', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionUpdate()
    {
        $id = $this->post_data['id'] ?? '';
        $status = $this->post_data['status'] ?? '';

        if (empty($id)) {
            return json_error(['msg' => lang('订单ID不能为空')]);
        }

        if (empty($status)) {
            return json_error(['msg' => lang('状态不能为空')]);
        }

        // 检查订单是否存在
        $order = $this->model->order->find(['id' => $id, 'user_id' => $this->uid], 1);
        if (!$order) {
            return json_error(['msg' => lang('订单不存在')]);
        }

        if (!in_array($status, ['delete', 'cancel', 'complete'])) {
            return json_error(['msg' => lang('状态错误')]);
        }

        if ($status == 'cancel' && in_array($order['status'], ['paid', 'complete'])) {
            return json_error(['msg' => lang('已支付或已完成订单不能取消')]);
        }

        db_action(function () use ($id, $status, &$result) {
            $result = $this->model->order->update(['status' => $status], ['id' => $id, 'user_id' => $this->uid], true);
            if ($status == 'cancel') {
                OrderClose::do($id, 'cancel');
            }
        });
        $msgs = [
            'delete' => '删除订单成功',
            'cancel' => '取消订单成功',
            'complete' => '确认收货成功',
        ];
        $msg = $msgs[$status] ?? '状态更新成功';
        return json_success(['msg' => lang($msg)]);
    }


    /**
     * 删除订单
     */
    #[OA\Post(
        path: '/order/api/delete',
        summary: '删除订单',
        tags: ['订单'],
        parameters: [
            new OA\Parameter(name: 'id', description: '订单ID', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? '';
        if (empty($id)) {
            return json_error(['msg' => lang('订单ID不能为空')]);
        }
        // 检查订单是否存在
        $order = $this->model->order->find(['id' => $id, 'user_id' => $this->uid], 1);
        if (!$order) {
            return json_error(['msg' => lang('订单不存在')]);
        }
        // 只能删除已取消或已完成的订单
        if (!in_array($order['status'], ['cancel', 'finish'])) {
            return json_error(['msg' => lang('只能删除已取消或已完成的订单')]);
        }
        $result = $this->model->order->update(['status' => 'delete'], ['id' => $id], true);
        if ($result) {
            return json_success(['msg' => lang('订单删除成功')]);
        } else {
            return json_error(['msg' => lang('订单删除失败')]);
        }
    }
    /**
     * 订单确认
     */
    #[OA\Post(
        path: '/order/api/confirm',
        summary: '订单确认',
        tags: ['订单'],
        parameters: [
            new OA\Parameter(name: 'items', description: '商品列表', in: 'query', required: true, schema: new OA\Schema(type: 'array')),
            new OA\Parameter(name: 'address_id', description: '地址ID', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'coupon_id', description: '优惠券ID', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionConfirm()
    {
        $items = $this->post_data['items'] ?? [];
        $addressId = $this->post_data['address_id'] ?? 0;
        $couponId = $this->post_data['coupon_id'] ?? 0;
        if (empty($items)) {
            return json_error(['msg' => lang('商品列表不能为空')]);
        }
        return json_success([
            'data' => OrderConfirm::confirm($items),
            'msg' => lang('订单确认成功')
        ]);
    }
    /**
     * 退款
     */
    #[OA\Post(
        path: '/order/api/refund',
        summary: '退款',
        tags: ['订单'],
        parameters: [
            new OA\Parameter(name: 'id', description: '订单ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionRefund()
    {
        $order_id = $this->post_data['id'] ?? 0;
        if (!$order_id) {
            return json_error(['msg' => lang('订单ID不能为空')]);
        }
        $order = $this->model->order->find(['id' => $order_id, 'user_id' => $this->uid], 1);
        if (!$order) {
            return json_error(['msg' => lang('订单不存在')]);
        }
        if ($order['status'] != 'paid') {
            return json_error(['msg' => lang('订单状态错误')]);
        }
        db_action(function () use ($order, $order_id) {
            $this->model->order->update(['status' => 'refund'], ['id' => $order_id], true);
            db_insert("order_refund_money", [
                'order_id' => $order_id,
                'user_id' => $this->uid,
                'amount' => $order->real_get_amount,
                'created_at' => time(),
            ]);
        });
        json_success(['msg' => lang('退款申请提交成功')]);
    }
}
