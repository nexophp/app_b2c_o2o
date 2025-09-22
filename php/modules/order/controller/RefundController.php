<?php

namespace modules\order\controller;

use modules\order\lib\OrderRefund;

class RefundController extends \core\AdminController
{
    protected $model = [
        'order' => '\modules\order\model\OrderModel',
        'order_item' => '\modules\order\model\OrderItemModel',
        'order_paid_info' => '\modules\order\model\OrderPaidInfoModel',
        'order_logistic' => '\modules\order\model\OrderLogisticModel',
        'order_refund' => '\modules\order\model\OrderRefundModel',
        'order_refund_item' => '\modules\order\model\OrderRefundItemModel',
    ];

    /**
     * 显示订单管理页面
     * @permission 售后.管理 售后.查看
     * @return mixed
     */
    public function actionIndex() {}

    /**
     * 售后订单列表
     * @permission 售后.管理 售后.查看
     * @return mixed
     */
    public function actionList()
    {
        $order_num = $this->post_data['order_num'] ?? '';
        $status = $this->post_data['status'] ?? '';
        $where  = [
            'ORDER' => ['id' => 'DESC'],
        ];
        if ($order_num) {
            $where['order_num[~]'] = $order_num;
        }
        if ($status) {
            $where['status'] = $status;
        }
        $list = $this->model->order_refund->pager($where);
        foreach ($list['data'] as &$v) {

            $v->order;
        }
        json_success($list);
    }
    /**
     * 售后订单详情
     * @permission 售后.管理 售后.查看
     * @return mixed
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? '';
        if (!$id) {
            json_error(['msg' => lang('参数错误')]);
        }
        $detail = $this->model->order_refund->find($id);
        if (!$detail) {
            json_error(['msg' => lang('售后订单不存在')]);
        }
        $detail->items;
        $detail->order;
        $detail->type_text = $detail->type_text;
        json_success(['data' => $detail]);
    }
    /**
     * 售后订单审核
     * @permission 售后.管理 售后.审核
     */
    public function actionApprove()
    {
        $id = $this->post_data['id'] ?? '';
        $status = $this->post_data['status'] ?? '';
        $remark = $this->post_data['remark'] ?? '';
        if (!$status || !$id) {
            json_error(['msg' => lang('参数错误')]);
        }
        $data = [
            'status' => $status,
            'admin_desc' => $remark,
        ];
        $this->model->order_refund->approve($id, $data);
        json_success(['msg' => lang('操作成功')]);
    }

    /**
     * 搜索订单
     * @permission 售后.管理 售后.创建
     */
    public function actionSearchOrder()
    {
        $order_num = $this->post_data['order_num'] ?? '';
        if (!$order_num) {
            json_error(['msg' => lang('订单号不能为空')]);
        }
        $order = $this->model->order->find([
            'order_num' => $order_num,
            'can_refund_amount[>]' => 0,
            'status' => [
                'paid',
                'complete',
            ]
        ], 1);
        if (!$order) {
            json_error(['msg' => lang('未找到可进行售后的订单')]);
        }
        // 获取订单商品
        $order_items = $this->model->order_item->find(['order_id' => $order->id]);
        $order->items = $order_items;
        do_action("order.refund.search_order", $order);
        json_success(['data' => $order]);
    }

    /**
     * 创建售后订单
     * @permission 售后.管理 售后.创建
     */
    public function actionCreate()
    {
        $order_id = $this->post_data['order_id'] ?? 0;
        $order_item_ids = $this->post_data['order_item_ids'] ?? [];
        $reason = $this->post_data['reason'] ?: '主动退款';
        $desc = '平台主动售后'; // 固定描述
        $type = $this->post_data['type'] ?? 'refund';

        if (!$order_id) {
            json_error(['msg' => lang('订单ID不能为空')]);
        }

        if (empty($order_item_ids)) {
            json_error(['msg' => lang('请选择要售后的商品')]);
        }

        if (!$reason) {
            //json_error(['msg' => lang('售后原因不能为空')]);
        }

        // 检查订单是否存在
        $order = $this->model->order->find(['id' => $order_id], 1);
        if (!$order) {
            json_error(['msg' => lang('订单不存在')]);
        }

        if ($order->can_refund_amount <= 0) {
            json_error(['msg' => lang('订单可退款金额不足')]);
        }

        $result = OrderRefund::create($order_id, $order_item_ids, $type, $reason, $desc, [], []);
        if (!$result['id']) {
            json_error(['msg' => lang('售后申请失败')]);
        }

        json_success(['data' => $result, 'msg' => lang('售后申请成功')]);
    }
}
