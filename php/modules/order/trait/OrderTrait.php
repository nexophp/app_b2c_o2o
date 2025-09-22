<?php

/**
 * 统一处理订单
 */

namespace modules\order\trait;

use lib\Time;
use modules\order\lib\OrderLogic;
use modules\order\lib\OrderClose;

trait OrderTrait
{
    /**
     * 查看物流
     */
    protected function getLogisticInfo()
    {
        $order_num = $this->post_data['order_num'] ?? '';
        if (!$order_num) {
            json_error(['msg' => lang('订单编号不能为空')]);
        }
        $result = OrderLogic::get($order_num);
        if ($result['data']['code'] == 250) {
            json_error(['msg' => $result['data']['msg']]);
        }
        $result['code'] = 0;
        json($result);
    }
    /**
     * 添加物流信息
     */
    protected function getAddLogistic()
    {
        $order_num = $this->post_data['order_num'] ?? '';
        $no = $this->post_data['no'] ?? '';
        $type = $this->post_data['type'] ?? '';
        if (!$order_num || !$no || !$type) {
            json_error(['msg' => lang('参数不完整')]);
        }
        $order = $this->model->order->findOne(['order_num' => $order_num]);
        if (!$order) {
            json_error(['msg' => lang('订单不存在')]);
        }
        // 检查物流单号是否已存在
        $existLogistic = $this->model->order_logistic->findOne(['order_num' => $order_num]);
        if ($existLogistic) {
            $id = $existLogistic['id'];
        }
        try {
            db_action(function () use ($order_num, $no, $type, $order, $id) {
                if ($id) {
                    $this->model->order_logistic->update([
                        'no' => $no,
                        'type' => $type,
                        'status' => 'shipped',
                        'updated_at' => time()
                    ], ['id' => $id], true);
                } else {
                    $logisticData = [
                        'order_num' => $order_num,
                        'no' => $no,
                        'type' => $type,
                        'status' => 'shipped',
                        'created_at' => time()
                    ];
                    $this->model->order_logistic->insert($logisticData);
                }

                // 更新订单状态为已发货
                if ($order['status'] == 'paid') {
                    $this->model->order->update([
                        'status' => 'shipped',
                        'updated_at' => time()
                    ], ['id' => $order['id']], true);
                }
            });

            json_success(lang('物流信息添加成功'));
        } catch (\Exception $e) {
            json_error(lang('添加失败：') . $e->getMessage());
        }
    }
    /**
     * 获取订单支付信息
     */
    protected function getPaymentInfo()
    {
        $order_id = $this->post_data['order_id'] ?? 0;
        if (!$order_id) {
            json_error(['msg' => lang('订单ID不能为空')]);
        }

        // 检查订单是否属于当前用户标签
        $where = ['id' => $order_id, 'user_tag' => $this->user_tag];

        $order = $this->model->order->findOne($where);
        if (!$order) {
            json_error(['msg' => lang('订单不存在')]);
        }

        // 获取支付信息
        $paymentInfo =  $order->paid_info;

        // 格式化时间
        foreach ($paymentInfo as &$payment) {
        }

        json_success(['data' => $paymentInfo]);
    }
    /**
     * 获取支付方式列表
     */
    protected function getPaymentTypes()
    {
        $paymentTypes = $this->model->order->getPaymentTypes();
        json_success(['data' => $paymentTypes]);
    }
    /**
     * 添加支付信息
     */
    protected function getAddPayment()
    {
        $order_id = $this->post_data['order_id'] ?? 0;
        $payment_method = $this->post_data['type'] ?? ''; // 前端传的是type
        $amount = floatval($this->post_data['amount'] ?? 0);

        if (!$order_id) {
            json_error(['msg' => lang('订单ID不能为空')]);
        }
        if (!$payment_method) {
            json_error(['msg' => lang('支付方式不能为空')]);
        }
        if ($amount <= 0) {
            json_error(['msg' => lang('支付金额必须大于0')]);
        }

        // 检查订单是否属于当前用户标签
        $where = ['id' => $order_id];


        $order = $this->model->order->findOne($where);
        if (!$order) {
            json_error(['msg' => lang('订单不存在')]);
        }

        // 处理支付
        $result = $this->model->order->processPayment($order_id, $payment_method, $amount);

        if ($result['code'] === 0) {
            json_success($result);
        } else {
            json_error($result);
        }
    }
    /**
     * 更新订单状态
     */
    protected function getUpdateStatus()
    {
        $id = $this->post_data['id'] ?? 0;
        $status = $this->post_data['status'] ?? '';

        if (!$id) {
            json_error(['msg' => lang('订单ID不能为空')]);
        }
        if (!$status) {
            json_error(['msg' => lang('订单状态不能为空')]);
        }

        // 检查订单是否属于当前用户标签
        $order = $this->model->order->find([
            'id' => $id,
        ]);
        if (!$order) {
            json_error(['msg' => lang('订单不存在')]);
        }

        $result = $this->model->order->update([
            'status' => $status,
            'updated_at' => time()
        ], ['id' => $id], true);

        if ($result) {
            json_success(['msg' => lang('状态更新成功')]);
        } else {
            json_error(['msg' => lang('状态更新失败')]);
        }
    }
    /**
     * 获取订单详情 
     */
    protected function getDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('订单ID不能为空')]);
        }
        $where = ['id' => $id, 'type' => $this->type, 'user_tag' => $this->user_tag];
        $order = $this->model->order->findOne($where);
        if (!$order) {
            json_error(['msg' => lang('订单不存在')]);
        }
        // 获取订单商品明细
        $order['items'] = $order->products;
        // 获取支付信息
        $paymentInfo = $order->paid_info; 
        // 为支付信息添加格式化时间 
        foreach ($paymentInfo as $payment) {
            $payment->type_info = $payment->type_info;
            $payment->created_at_format = date('Y-m-d H:i:s', $payment->created_at);
        }
        $order['payment_info'] = $paymentInfo;
        json_success(['data' => $order]);
    }

    /**
     * 获取订单统计数据
     */
    protected function getStats()
    {
        $stats = [];

        // 获取各时间段
        $timeRanges = [
            'today' => lang('今日'),
            'yesterday' => lang('昨日'),
            'week' => lang('本周'),
            'lastweek' => lang('上周'),
            'month' => lang('本月'),
            'lastmonth' => lang('上月')
        ];

        foreach ($timeRanges as $key => $label) {
            $timeRange = Time::get($key);
            $startTime = $timeRange[0];
            $endTime = $timeRange[1];
            // 订单数量统计
            $orderCount = $this->model->order->count([
                'created_at[>=]' => $startTime,
                'created_at[<=]' => $endTime,
                'type' => $this->type,
                'user_tag' => $this->user_tag,
            ]);

            // 订单金额统计
            $orderAmount = $this->model->order->sum('real_amount', [
                'created_at[>=]' => $startTime,
                'created_at[<=]' => $endTime,
                'status[!]' => ['wait', 'cancel', 'delete'],
                'type' => $this->type,
                'user_tag' => $this->user_tag,

            ]) ?: 0;

            $stats[] = [
                'label' => $label,
                'count' => $orderCount,
                'amount' => number_format($orderAmount, 2)
            ];
        }

        json_success(['data' => $stats]);
    }


    /**
     * 获取订单列表
     */
    protected function getList()
    {
        $where = [];

        // 添加用户标签判断
        if ($this->user_tag) {
            $where['user_tag'] = $this->user_tag;
        }
        if ($this->type) {
            $where['type'] = $this->type;
        }
        // 搜索条件
        $order_num = $this->post_data['order_num'] ?? '';
        $status = $this->post_data['status'] ?? '';
        $user_id = $this->post_data['user_id'] ?? '';
        $start_date = $this->post_data['start_date'] ?? '';
        $end_date = $this->post_data['end_date'] ?? '';

        if ($order_num) {
            $where['order_num[~]'] = $order_num;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        if ($start_date) {
            $where['created_at[>=]'] = strtotime($start_date . ' 00:00:00');
        }
        if ($end_date) {
            $where['created_at[<=]'] = strtotime($end_date . ' 23:59:59');
        }

        $where['ORDER'] = ['id' => 'DESC'];

        $list = $this->model->order->pager($where);

        // 格式化数据
        foreach ($list['data'] as &$order) {
            $order['created_at_format'] = date('Y-m-d H:i:s', $order['created_at']);
            $order['updated_at_format'] = $order['updated_at'] ? date('Y-m-d H:i:s', $order['updated_at']) : '';

            // 获取订单商品
            $order->items = $order->products;
        }

        json($list);
    }
}
