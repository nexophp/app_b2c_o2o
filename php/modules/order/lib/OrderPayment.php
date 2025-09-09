<?php

namespace modules\order\lib;

use modules\order\model\OrderModel;
use modules\order\model\OrderPaidInfoModel;

class OrderPayment
{
    private $orderModel;
    private $orderPaidInfoModel;
    
    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderPaidInfoModel = new OrderPaidInfoModel();
    }
    
    /**
     * 添加订单支付信息
     * @param array $paymentData 支付信息数据
     * @return array 返回结果
     */
    public function addPaymentInfo($paymentData)
    {
        try {
            // 验证必要参数
            $this->validatePaymentData($paymentData);
            
            // 检查订单是否存在
            $order = $this->orderModel->find($paymentData['order_id']);
            if (!$order) {
                json_error(['msg' => lang('订单不存在')]);
            }
            
            // 插入支付信息
            $result = $this->orderPaidInfoModel->insert($paymentData);
            
            if ($result) {
                return [
                    'code' => 0,
                    'msg' => lang('支付信息添加成功'),
                    'data' => ['id' => $result]
                ];
            } else {
                json_error(['msg' => lang('支付信息添加失败')]);
            }
            
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * 批量添加订单支付信息
     * @param int $orderId 订单ID
     * @param array $paymentList 支付信息列表
     * @return array
     */
    public function batchAddPaymentInfo($orderId, $paymentList)
    {
        try {
            // 检查订单是否存在
            $order = $this->orderModel->find($orderId);
            if (!$order) {
                json_error(['msg' => lang('订单不存在')]);
            }
            
            $insertData = [];
            foreach ($paymentList as $payment) {
                $payment['order_id'] = $orderId;
                $this->validatePaymentData($payment);
                $insertData[] = $payment;
            }
            
            // 批量插入
            $result = $this->orderPaidInfoModel->inserts($insertData);
            
            if ($result) {
                return [
                    'code' => 0,
                    'msg' => lang('批量添加支付信息成功'),
                    'data' => ['count' => count($insertData)]
                ];
            } else {
                json_error(['msg' => lang('批量添加支付信息失败')]);
            }
            
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * 获取订单支付信息
     * @param int $orderId 订单ID
     * @return array
     */
    public function getOrderPaymentInfo($orderId)
    {
        try {
            // 修正：使用正确的查询方法
            $paymentList = $this->orderPaidInfoModel->find(['order_id' => $orderId]);
            
            return [
                'code' => 0,
                'msg' => lang('获取成功'),
                'data' => $paymentList
            ];
            
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * 删除订单支付信息
     * @param int $paymentId 支付信息ID
     * @return array
     */
    public function deletePaymentInfo($paymentId)
    {
        try {
            $result = $this->orderPaidInfoModel->del(['id' => $paymentId]);
            
            if ($result) {
                return [
                    'code' => 0,
                    'msg' => lang('删除成功'),
                    'data' => null
                ];
            } else {
                json_error(['msg' => lang('删除失败')]);
            }
            
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * 验证支付数据
     */
    private function validatePaymentData($paymentData)
    {
        $required = ['order_id', 'title', 'type', 'amount'];
        
        foreach ($required as $field) {
            if (!isset($paymentData[$field]) || $paymentData[$field] === '') {
                json_error(['msg' => lang("缺少必要参数").$field]);
            }
        }
        
        if (!is_numeric($paymentData['amount']) || $paymentData['amount'] < 0) {
            json_error(['msg' => lang('抵扣金额必须是大于等于0的数字')]);
        }
    }
}