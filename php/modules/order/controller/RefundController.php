<?php

namespace modules\order\controller;

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
        if($status){
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
    public function actionDetail(){
        $id = $this->post_data['id'] ?? '';
        if(!$id){
            json_error(['msg' => lang('参数错误')]);
        }
        $detail = $this->model->order_refund->find($id);
        if(!$detail){
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
    public function actionApprove(){
        $id = $this->post_data['id'] ?? '';
        $status = $this->post_data['status'] ?? '';
        $remark = $this->post_data['remark'] ?? '';
        if(!$status || !$id){
            json_error(['msg' => lang('参数错误')]);
        }
        $data = [
            'status' => $status,
            'admin_desc' => $remark,
        ];
        $this->model->order_refund->approve($id,$data);
        json_success(['msg' => lang('操作成功')]);
    }
}
