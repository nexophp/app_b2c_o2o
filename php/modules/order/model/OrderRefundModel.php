<?php

namespace modules\order\model;

use modules\order\lib\OrderLogic;

class OrderRefundModel extends \core\AppModel
{
    protected $table = 'order_refund';

    protected $field = [
        'order_num' => '订单号',
        'type' => '售后类型', // refund:仅退款, return_refund:退货退款, exchange:换货 
        'reason' => '退款原因',
        'num' => '商品数量',
        'amount' => '退款金额',
        'desc' => '详细说明',
        'images' => '图片',
        'status' => '售后状态',
        'user_id' => '用户',
    ];

    protected $validate = [
        'required' => [
            'order_id',
            'type',
            'reason',
            'user_id'
        ]
    ];

    protected $has_many = [
        'items' => [OrderRefundItemModel::class, 'refund_id', 'id'],
    ];

    protected $has_one = [
        'order' => [OrderModel::class, 'order_id', 'id'],
    ];

    public function getAttrLogicInfo()
    {
        $order_num = $this->data['order_num'];
        if (!$order_num) {
            return [];
        }
        $logic_info = OrderLogic::get($order_num);
        return  $logic_info;
    }

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        $data['created_at_format'] = date('Y-m-d H:i:s', $data['created_at']);
        $data['updated_at_format'] = date('Y-m-d H:i:s', $data['updated_at']);
        $data['status_text'] = [
            'wait' => '待审核',
            'approved' => '通过',
            'rejected' => '拒绝',
            'cancel' => '取消',
        ][$data['status'] ?? ''] ?? '';
        $data['status_color'] = [
            'wait' => '#FF9900',
            'approved' => '#009900',
            'rejected' => '#FF0000',
            'cancel' => '#999999',
        ][$data['status'] ?? ''] ?? '';
    }

    public function beforeSave(&$data)
    {
        parent::beforeSave($data);
        // 设置默认值
        if (!isset($data['status'])) {
            $data['status'] = 'wait';
        }
        // 设置创建时间
        if (!$data['id']) {
            $data['created_at'] = time();
        }
        $data['updated_at'] = time();
    }
    /**
     * 售后订单审核
     */
    public function approve($id, $data = [])
    {
        db_action(function () use ($id, $data) {
            $refund = $this->find($id);
            if (!$refund ||  $refund->status != 'wait') {
                json_error(['msg' => lang('操作失败')]);
            }
            $amount = $refund->amount;
            $order = $refund->order;
            $this->update($data, ['id' => $id], true);
            OrderRefundItemModel::model()->update(['status' => $data['status']], ['refund_id' => $id], true);
            OrderModel::model()->update(['status' => 'complete'], ['id' => $order->id], true);
            $items = $refund->items;
            $status = $data['status'] ?? '';
            if ($status == 'approved') {
                //退款处理
                if ($amount > 0) {
                    $order->update([
                        'can_refund_amount' => bcsub($order->can_refund_amount, $amount, 2),
                        'has_refund_amount' => bcadd($order->has_refund_amount, $amount, 2),
                        'real_get_amount' => bcsub($order->real_get_amount, $amount, 2),
                    ], ['id' => $order->id], true);

                    db_insert("order_refund_money", [
                        'order_id' => $order->id,
                        'user_id' => $order->uid,
                        'amount' => $amount,
                        'created_at' => time(),
                    ]);
                }
            }
        });
    }

    public function getAttrTypeText()
    {
        $type_text = '';
        switch ($this->type) {
            case 'return':
                $type_text = '退货退款';
                break;
            case 'exchange':
                $type_text = '换货';
                break;
            case 'refund':
                $type_text = '退款';
                break;
            default:
                $type_text = '未知';
                break;
        }
        return $type_text;
    }
}
