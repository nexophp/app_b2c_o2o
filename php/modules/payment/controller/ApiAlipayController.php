<?php

namespace modules\payment\controller;

use modules\payment\lib\AlipayNotify;
use modules\payment\lib\AlipayBarcode;

use OpenApi\Attributes as OA;

#[OA\Tag(name: '支付宝支付', description: '支付宝支付接口')]
class ApiAlipayController extends \core\ApiController
{
    protected $need_login = false;
    /**
     * 微信支付回调通知
     */
    #[OA\Post(
        path: '/payment/api-alipay/notify',
        summary: '支付宝支付回调通知',
        description: '接收支付宝支付异步通知，处理支付结果',
        tags: ['支付宝支付']
    )]
    public function notify()
    {
        return AlipayNotify::run(function ($order_num, $total_amount) { 
        });
    }

    /**
     * POS扫码支付
     */
    #[OA\Post(
        path: '/payment/api-alipay/pos',
        summary: 'POS扫码支付',
        description: '扫描用户付款码进行支付，适用于线下收银场景',
        tags: ['支付宝支付'],
        parameters: [
            new OA\Parameter(name: 'order_num', description: '订单号', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'total_fee', description: '订单金额（单位：元）', in: 'query', schema: new OA\Schema(type: 'int')),
            new OA\Parameter(name: 'auth_code', description: '付款码', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'body', description: '订单描述', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionPos()
    {
        $order_num = $this->post_data['order_num'];
        $total_fee = $this->post_data['total_fee'];
        $auth_code = $this->post_data['auth_code'];
        $body = $this->post_data['body'] ?: 'pos支付';
        $device_info = $this->post_data['device_info'] ?? 'sn2024';

        if (!$order_num || !$total_fee || !$auth_code || !$body) {
            json_error(['msg' => lang('参数不完整')]);
        }

        $req = [
            'order_num' => $order_num,
            'total_fee' => $total_fee,
            'auth_code' => $auth_code,
            'body' => $body,
            'device_info' => $device_info
        ];

        $res = AlipayBarcode::pos($req);

        if (isset($res['code']) && $res['code'] === 0) {
            json_success($res);
        } else {
            json_error(['msg' => $res['err_code_des'] ?? '支付失败']);
        }
    }
}
