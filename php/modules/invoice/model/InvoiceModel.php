<?php

namespace modules\invoice\model;

use modules\admin\model\UserModel;

class InvoiceModel extends \core\AppModel
{
    protected $table = 'invoice';

    protected $field = [
        'user_id' => '用户ID',
        'type' => '发票类型',
        'title' => '发票抬头',
        'content' => '发票内容',
        'amount' => '发票金额',
        'status' => '状态',
    ];

    protected $validate = [
        'required' => [
            'user_id',
            'type',
            'title',
            'content',
            'amount'
        ],
    ];

    protected $unique_message = [
        '用户ID不能为空',
        '发票类型不能为空',
        '发票抬头不能为空',
        '发票内容不能为空',
        '发票金额不能为空'
    ];

    protected $has_one = [
        'user' => [UserModel::class, 'user_id', 'id'],
    ];

    protected $has_many = [
        'orders' => [InvoiceOrderModel::class, 'invoice_id', 'id'],
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
            1 => '正常',
            2 => '已开具',
            3 => '已作废'
        ];
    }

    /**
     * 数据查找后处理
     */
    public function afterFind(&$data)
    {
        // 格式化时间
        if ($data['created_at']) {
            $data['created_at_format'] = date('Y-m-d H:i:s', $data['created_at']);
        }
        if ($data['updated_at']) {
            $data['updated_at_format'] = date('Y-m-d H:i:s', $data['updated_at']);
        }

        // 处理发票类型显示
        $typeMap = $this->getTypeMap();
        $data['type_text'] = $typeMap[$data['type']] ?? $data['type'];

        // 处理状态显示
        $statusMap = $this->getStatusMap();
        $data['status_text'] = $statusMap[$data['status']] ?? '未知';

        // 格式化金额
        if ($data['amount']) {
            $data['amount_format'] = number_format($data['amount'], 2);
        }

        // 获取用户信息
        if ($this->user) {
            $data['user_name'] = $this->user->username ?? '';
            $data['user_phone'] = $this->user->phone ?? '';
        }
    }

    /**
     * 保存前处理
     */
    public function beforeSave(&$data)
    {
        // 设置创建时间
        if (!isset($data['id'])) {
            $data['created_at'] = time();
        }
        
        // 设置更新时间
        $data['updated_at'] = time();

        // 验证金额格式
        if (isset($data['amount'])) {
            $data['amount'] = floatval($data['amount']);
            if ($data['amount'] <= 0) {
                throw new \Exception('发票金额必须大于0');
            }
        }

        // 验证发票类型
        if (isset($data['type'])) {
            $typeMap = $this->getTypeMap();
            if (!array_key_exists($data['type'], $typeMap)) {
                throw new \Exception('无效的发票类型');
            }
        }
    }

    
}