<?php

namespace modules\invoice\model;

use modules\admin\model\UserModel;
use modules\order\model\OrderModel;

class InvoiceOrderModel extends \core\AppModel
{
    protected $table = 'invoice_order';

    protected $field = [
        'user_id' => '用户ID',
        'invoice_id' => '发票ID',
        'order_id' => '订单ID',
        'type' => '发票类型',
        'title' => '发票抬头',
        'tax_no' => '纳税人识别号',
        'status' => '状态',
    ];

    protected $validate = [
        'required' => [
            'user_id',
            'order_id',
            'type',
            'title'
        ],
        'unique' => [
            ['order_id'] // 一个订单只能开一次发票
        ]
    ];

    protected $unique_message = [
        '该订单已开具发票'
    ];

    protected $has_one = [
        'user' => [UserModel::class, 'user_id', 'id'],
        'invoice' => [InvoiceModel::class, 'invoice_id', 'id'],
        'order' => [OrderModel::class, 'order_id', 'id'],
    ];

    /**
     * 发票类型定义
     */
    public function getTypeMap()
    {
        return [
            'personal' => '个人',
            'company' => '企业',
            'special' => '专用发票'
        ];
    }

    /**
     * 状态定义
     */
    public function getStatusMap()
    {
        return [
            0 => '已删除',
            1 => '待开具',
            2 => '已开具',
            3 => '开具失败',
            4 => '已作废'
        ];
    }
}
