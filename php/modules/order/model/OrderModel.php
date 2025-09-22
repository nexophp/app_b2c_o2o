<?php

namespace modules\order\model;

class OrderModel extends \core\AppModel
{
    protected $table = 'order';

    protected $field = [
        'order_num' => '订单号',
        'type' => '类型',
        'user_id' => '用户ID',
        'amount' => '商品总价',
        'real_amount' => '实际支付金额',
        'status' => '订单状态',
    ];

    protected $validate = [
        'required' => [
            'order_num',
            'user_id',
        ],
        'unique' => [
            ['order_num']
        ]
    ];

    protected $unique_message = [
        '订单号已存在'
    ];

    protected $has_many = [
        'products' => [OrderItemModel::class, 'order_id', 'id',],
        'paid_info' => [OrderPaidInfoModel::class, 'order_id', 'id',],
        'info' => [OrderInfoModel::class, 'order_id', 'id',],

    ];




    /**
     * 订单状态定义
     */
    public function getStatusMap()
    {
        return [
            'wait' => lang('待支付'),
            'paid' => lang('已支付'),
            'shipped' => lang('已发货'),
            'complete' => lang('已完成'),
            'cancel' => lang('已取消'),
            'close' => lang('已关闭'),
            'refund' => lang('已退款')
        ];
    }

    /**
     * 获取状态文本
     */
    public function getAttrStatusText($status = '')
    {
        $statusMap = $this->getStatusMap();
        $status = $status ?: $this->status;
        return $statusMap[$status] ?? $status;
    }

    /**
     * 获取状态选项（用于下拉框）
     */
    public function getStatusOptions()
    {
        $options = [];
        foreach ($this->getStatusMap() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        // 格式化时间 
        $data['created_at_format'] = date('Y-m-d H:i:s', $data['created_at']);
        $data['updated_at_format'] = date('Y-m-d H:i:s', $data['updated_at']);
        $data['status_text'] = $this->getAttrStatusText($data['status']);
        if ($data['sys_tag'] == 'product' && $data['status'] == 'paid') {
            $data['status_text'] = '待发货';
        }
        $data['ship_type_txt'] =  $this->getAttrShipTypeText($data['ship_type']);
        $info = $this->info;
        $data['info'] = $info?:[];


        $can_pay_time = 0;
        $order_auto_cancel_time = get_config('order_auto_cancel_time') ?: 5;
        $order_auto_cancel_time = $order_auto_cancel_time * 60;
        $created_at = $data['created_at'];
        $can_pay_time = $order_auto_cancel_time -  (time()  - $created_at);
        $can_pay_time = $can_pay_time > 0 ? $can_pay_time : 0;
        $data['can_pay_time'] = $can_pay_time;
    }

    public function getAttrShipTypeText($type = '')
    {
        $shipTypeMap = [
            'none' => lang('未发货'),
            'self' => lang('到店自提'),
            'express' => lang('快递发货'),
            'online' => lang('线上发货'),
        ];
        return $shipTypeMap[$type] ?? $type;
    }

    public function beforeSave(&$data)
    {
        parent::beforeSave($data);

        // 生成订单号
        if (empty($data['order_num'])) {
            $data['order_num'] = date('YmdHis') . rand(1000, 9999);
        }

        // 设置创建时间
        if (empty($data['id'])) {
            $data['created_at'] = time();
        }
        $data['updated_at'] = time();
    }

    /**
     * 获取支付方式列表
     * @return array
     */
    public function getPaymentTypes()
    {
        return [
            ['value' => 'offline_transfer', 'label' => lang('线下转账收款')], 
            ['value' => 'alipay', 'label' => lang('支付宝')],
            ['value' => 'weixin', 'label' => lang('微信')], 
        ];
    }

    /**
     * 处理订单支付
     * @param int $orderId 订单ID
     * @param string $paymentMethod 支付方式
     * @param float $amount 支付金额
     * @return array
     */
    public function processPayment($orderId, $paymentMethod, $amount)
    {
        // 获取订单信息
        $order = $this->findOne(['id' => $orderId]);
        if (!$order) {
            return ['code' => 250, 'msg' => lang('订单不存在')];
        }

        // 检查订单状态
        if ($order['status'] != 'wait') {
            return ['code' => 250, 'msg' => lang('订单状态不允许支付') . $order['status']];
        }

        // 验证支付金额必须等于订单实付金额
        if (abs($amount - $order['real_amount']) > 0.01) {
            return [
                'code' => 250,
                'msg' => lang('支付金额必须等于订单实付金额') . '￥' . $order['real_amount']
            ];
        }

        try {
            // 使用事务处理
            db_action(function () use ($orderId, $paymentMethod, $amount, $order) {
                // 更新订单状态为已支付
                $this->update([
                    'status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'updated_at' => time()
                ], ['id' => $orderId], true);

                // 记录支付信息
                $paymentData = [
                    'order_id' => $orderId,
                    'title' => lang('订单支付'),
                    'type' => $paymentMethod,
                    'amount' => $amount,
                    'created_at' => time()
                ];

                $orderPaymentModel = new OrderPaidInfoModel();
                $orderPaymentModel->insert($paymentData);
            });

            return ['code' => 0, 'msg' => lang('支付成功')];
        } catch (\Exception $e) {
            return ['code' => 250, 'msg' => lang('支付失败：') . $e->getMessage()];
        }
    }
}
