<?php

namespace modules\payment\controller;

use modules\payment\lib\WeiXin;
use OpenApi\Attributes as OA;
 
#[OA\Tag(name: '微信支付管理员', description: '退款转账')]
class ApiWeixinAdminController extends \core\AdminController
{
    protected $user_tag = 'admin';
    /**
     * 申请退款
     */
    #[OA\Post(
        path: '/payment/api-weixin-admin/refund',
        summary: '申请退款',
        description: '对已支付的订单申请退款',
        tags: ['微信支付管理员'],
        parameters: [
            new OA\Parameter(name: 'out_trade_no', description: '订单号', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'total_fee', description: '订单金额', in: 'query', schema: new OA\Schema(type: 'int')),
            new OA\Parameter(name: 'refund_amount', description: '退款金额', in: 'query', schema: new OA\Schema(type: 'int')),
        ]
    )]
    public function actionRefund()
    {
        $out_trade_no = $this->post_data['out_trade_no'];
        $total_fee = $this->post_data['total_fee'];
        $refund_amount = $this->post_data['refund_amount'];
        $refund_desc = $this->post_data['refund_desc'] ?? '退款';

        if (!$out_trade_no || !$total_fee || !$refund_amount) {
            json_error(['msg' => lang('参数不完整')]);
        }

        if ($refund_amount > $total_fee) {
            json_error(['msg' => lang('退款金额不能大于订单金额')]);
        }

        $currency = get_config('currency') ?: 'CNY';
        $res = WeiXin::refund($out_trade_no, $total_fee, $refund_amount, $currency, $refund_desc);

        if (isset($res['code']) && $res['code'] === 0) {
            json_success($res);
        } else {
            json_error(['msg' => $res['message'] ?? lang('退款失败')]);
        }
    }
    /**
     * 查询退款
     */
    #[OA\Post(
        path: '/payment/api-weixin-admin/refund-query',
        summary: '查询退款',
        description: '根据商户退款单号查询退款状态',
        tags: ['微信支付管理员'],
        parameters: [
            new OA\Parameter(name: 'out_refund_no', description: '退款单号', in: 'query', schema: new OA\Schema(type: 'string')),
        ]
    )]
    public function actionRefundQuery()
    {
        $out_refund_no = $this->post_data['out_refund_no'];

        if (!$out_refund_no) {
            json_error(['msg' => lang('退款单号不能为空')]);
        }

        $res = WeiXin::refundQuery($out_refund_no);
        json_success($res);
    }
    /**
     * 企业付款到零钱
     */
    #[OA\Post(
        path: '/payment/api-weixin-admin/transfer',
        summary: '企业付款到零钱',
        description: '向用户微信零钱转账',
        tags: ['微信支付管理员'],
        parameters: [
            new OA\Parameter(name: 'openid', description: '用户openid', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order_num', description: '订单号', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'amount', description: '转账金额', in: 'query', schema: new OA\Schema(type: 'int')),
            new OA\Parameter(name: 'desc', description: '转账描述', in: 'query', schema: new OA\Schema(type: 'string')),
        ]
    )]
    public function actionTransfer()
    {
        $openid = $this->post_data['openid'];
        $order_num = $this->post_data['order_num'];
        $amount = $this->post_data['amount'];
        $desc = $this->post_data['desc'] ?? '转账';

        if (!$openid || !$order_num || !$amount) {
            json_error(['msg' => lang('参数不完整')]);
        }

        if ($amount <= 0) {
            json_error(['msg' => lang('转账金额必须大于0')]);
        }

        $res = WeiXin::transfer($openid, $order_num, $amount, $desc);

        if (isset($res['return_code']) && $res['return_code'] === 'SUCCESS') {
            json_success($res);
        } else {
            json_error(['msg' => $res['return_msg'] ?? lang('转账失败')]);
        }
    }
}
