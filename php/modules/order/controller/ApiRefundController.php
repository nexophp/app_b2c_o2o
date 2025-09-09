<?php

namespace modules\order\controller;

use modules\order\lib\OrderRefund;
use modules\order\model\OrderModel;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Tag(name: '退款', description: '退款管理接口')]
class ApiRefundController extends \core\ApiController
{

    protected $model = [
        'order' => '\modules\order\model\OrderModel',
        'order_item' => '\modules\order\model\OrderItemModel',
        'order_paid_info' => '\modules\order\model\OrderPaidInfoModel',
        'order_refund' => '\modules\order\model\OrderRefundModel',
    ];
    /**
     * 商家收货信息
     */
    #[OA\Get(
        path: '/order/api-refund/address',
        summary: '商家收货信息',
        tags: ['退款'],
    )]
    public function actionAddress()
    {
        $address = get_config('order_refund_address');
        return json_success(['data' => $address]);
    }

    /**
     * 创建退款申请
     */
    #[OA\Post(
        path: '/order/api-refund/create',
        summary: '创建退款申请',
        tags: ['退款'],
        parameters: [
            new OA\Parameter(name: 'order_id', description: '订单ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'order_item_ids', description: '订单明细ID', in: 'query', required: true, schema: new OA\Schema(type: 'array')),
            new OA\Parameter(name: 'reason', description: '退款原因', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'desc', description: '详细说明', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', description: '退款类型', in: 'query', schema: new OA\Schema(type: 'string', default: 'refund')),
            new OA\Parameter(name: 'images', description: '退款凭证', in: 'query', schema: new OA\Schema(type: 'array')),
            new OA\Parameter(name: 'address', description: '退换华地址', in: 'query', schema: new OA\Schema(type: 'array')),
        ],
    )]
    public function actionCreate()
    {
        // 验证订单是否属于当前用户
        $order_id = $this->post_data['order_id'] ?? 0;
        if (!$order_id) {
            return json_error(['msg' => lang('订单ID不能为空')]);
        }
        $order = $this->model->order->find(['id' => $order_id, 'user_id' => $this->uid], 1);
        if (!$order) {
            return json_error(['msg' => lang('订单不存在')]);
        }
        $order_id = $this->post_data['order_id'] ?? 0;
        $type = $this->post_data['type'] ?? 'refund'; // refund 退款  exchange 换货  return 退货退款 
        $order_item_ids = $this->post_data['order_item_ids'] ?? [];
        $reason = $this->post_data['reason'] ?? '';
        $desc = $this->post_data['desc'] ?? '';
        $images = $this->post_data['images'] ?? [];
        $address = $this->post_data['address'] ?? [];

        // 检查订单是否属于当前用户标签
        $order = $this->model->order->find([
            'id' => $order_id,
        ], 1);
        if (!$order) {
            json_error(['msg' => lang('订单不存在')]);
        }
        if($order->can_refund_amount <= 0){
            json_error(['msg' => lang('订单可退款金额不足')]);
        }
        $result = OrderRefund::create($order_id, $order_item_ids, $type, $reason, $desc, $images, $address);
        if (!$result['id']) {
            json_error(['msg' => lang('退款申请失败')]);
        }
        json_success(['data' => $result, 'msg' => lang('退款申请成功')]);
    }

    /**
     * 获取退款列表
     */
    #[OA\Get(
        path: '/order/api-refund/list',
        summary: '获取退款列表',
        tags: ['退款'],
        parameters: [
            new OA\Parameter(name: 'status', description: '退款状态', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', description: '页码', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', description: '每页数量', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
    )]
    public function actionList()
    {
        $where = ['user_id' => $this->uid];

        $status = $this->post_data['status'] ?? '';
        if ($status !== '') {
            $where['status'] = $status;
        }

        $result = $this->model->order_refund->pager($where);

        // 加载关联的订单信息
        foreach ($result['data'] as &$refund) {
            $refund->order;
            $refund->items;
        }

        return json_success($result);
    }

    /**
     * 获取退款详情
     */
    #[OA\Get(
        path: '/order/api-refund/detail',
        summary: '获取退款详情',
        tags: ['退款'],
        parameters: [
            new OA\Parameter(name: 'id', description: '退款ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            return json_error(['msg' => lang('退款ID不能为空')]);
        }

        $refund = $this->model->order_refund->find(['id' => $id, 'user_id' => $this->uid], 1);
        if (!$refund) {
            return json_error(['msg' => lang('退款记录不存在或无权限')]);
        }

        // 加载关联信息
        $refund->order;
        $refund->items;

        return json_success(['data' => $refund]);
    }
    /**
     * 取消退款申请
     * @param int $id 退款ID
     * @return array
     */
    #[OA\Post(
        path: '/order/api-refund/cancel',
        summary: '取消退款申请',
        tags: ['退款'],
        parameters: [
            new OA\Parameter(name: 'id', description: '退款ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionCancel()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            return json_error(['msg' => lang('退款ID不能为空')]);
        }
        $refund = $this->model->order_refund->find(['id' => $id, 'user_id' => $this->uid], 1);
        if (!$refund) {
            return json_error(['msg' => lang('退款记录不存在或无权限')]);
        }
        if ($refund->status != 'wait') {
            return json_error(['msg' => lang('退款申请已处理，不能取消')]);
        }
        $this->model->order_refund->update(['status' => 'cancel'], ['id' => $id], true);
        json_success(['msg' => lang('取消成功')]);
    }

    /**
     * 提交退货物流信息
     */
    #[OA\Post(
        path: '/order/api-refund/logic',
        summary: '提交退货物流信息',
        tags: ['退款'],
        parameters: [
            new OA\Parameter(name: 'refund_id', description: '退款ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'no', description: '物流单号', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', description: '物流公司', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionLogic()
    {
        // 验证退款记录是否属于当前用户
        $refund_id = $this->post_data['refund_id'] ?? 0;
        $no = $this->post_data['no'];
        $type = $this->post_data['type'];
        if (!$refund_id) {
            return json_error(['msg' => lang('退款ID不能为空')]);
        }
        if (!$no || !$type) {
            return json_error(['msg' => lang('物流单号和物流公司不能为空')]);
        }
        $refund = $this->model->order_refund->find(['id' => $refund_id, 'user_id' => $this->uid], 1);
        if (!$refund) {
            return json_error(['msg' => lang('退款记录不存在或无权限')]);
        }
        $order_num = $refund->order_num;
        $logic_type = 'order_refund_user';
        $logistic = db_get_one("order_logistic", "*", [
            'order_num' => $order_num,
            'no' => $no,
            'type' => $type,
            'logic_type' => $logic_type,
        ]);
        if (!$logistic) {
            db_insert("order_logistic", [
                'order_num' => $order_num,
                'no' => $no,
                'type' => $type,
                'logic_type' => $logic_type,
                'status' => 'wait',
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        } else {
            db_update("order_logistic", [
                'status' => 'wait',
                'updated_at' => time(),
            ], [
                'id' => $logistic->id,
            ], true);
        }
        json_success(['msg' => lang('提交成功')]);
    } 
   
}
