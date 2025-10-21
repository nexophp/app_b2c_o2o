<?php

namespace modules\webpos\controller;

use modules\order\trait\OrderTrait;

class AdminController extends \core\AdminController
{
    use OrderTrait;
    protected $user_tag = 'admin';
    protected $type = 'webpos';
    protected $model = [
        'order' => '\modules\order\model\OrderModel',
        'order_item' => '\modules\order\model\OrderItemModel',
        'order_paid_info' => '\modules\order\model\OrderPaidInfoModel',
        'order_logistic' => '\modules\order\model\OrderLogisticModel',
    ];

    /**
     * 订单管理页面
     * @permission 订单.管理
     */
    public function actionIndex() {}

    /**
     * 获取订单统计数据
     * @permission 订单.管理 订单.查看
     */
    public function actionStats()
    {
        $this->getStats();
    }

    /**
     * 获取订单列表
     * @permission 订单.管理 订单.查看
     */
    public function actionList()
    {
        $this->getList();
    }

    /**
     * 获取订单详情
     * @permission 订单.管理 订单.查看
     */
    public function actionDetail()
    {
        $this->getDetail();
    }

    /**
     * 更新订单状态
     * @permission 订单.管理 订单.修改
     */
    public function actionUpdateStatus()
    {
        $this->getUpdateStatus();
    }

    /**
     * 添加支付信息
     * @permission 订单.管理 订单.支付
     */
    public function actionAddPayment()
    {
        $this->getAddPayment();
    }

    /**
     * 获取支付方式列表
     * @permission 订单.管理 订单.查看
     */
    public function actionPaymentTypes()
    {
        $this->getPaymentTypes();
    }

    /**
     * 获取订单支付信息
     * @permission 订单.管理 订单.查看
     */
    public function actionPaymentInfo()
    {
        $this->getPaymentInfo();
    }
    /**
     * 添加物流信息
     * @permission 订单.管理 订单.发货
     */
    public function actionAddLogistic()
    {
        $id = $this->post_data['id'] ?: 0;
        if (!$id) {
            json_error('订单不存在');
        }
        db_update("order", [
            'status' => 'shipped',
            'updated_at' => time(),
        ], [
            'id' => $id,
        ]);
        add_log("order.{$id}", '添加物流信息', '商家已发货', 'info');
        json_success('商家已发货');
    }
    /**
     * 订单完成
     * @permission 订单.管理 订单.完成
     */
    public function actionComplete()
    {
        $id = $this->post_data['id'] ?: 0;
        if (!$id) {
            json_error('订单不存在');
        }
        db_update("order", [
            'status' => 'complete',
            'updated_at' => time(),
        ], [
            'id' => $id,
        ]);
        add_log("order.{$id}", '订单完成', '订单已完成', 'info');
        json_success('订单已完成');
    }
}
