<?php

namespace modules\payment\controller;

use modules\payment\lib\WeiXin;
use OpenApi\Attributes as OA;
 
#[OA\Tag(name: '微信支付', description: '微信支付接口')]
class ApiWeixinController extends \core\ApiController
{
    protected $need_login = false;
    /**
     * 微信支付回调通知
     */
    #[OA\Post(
        path: '/payment/api-weixin/notify',
        summary: '微信支付回调通知',
        description: '接收微信支付异步通知，处理支付结果',
        tags: ['微信支付']
    )]
    public function notify()
    {
        return WeiXin::notify();
    }
    /**
     * 查询支付订单
     */
    #[OA\Post(
        path: '/payment/api-weixin/query',
        summary: '查询支付订单',
        description: '根据商户订单号查询微信支付订单状态',
        tags: ['微信支付'],
        parameters: [
            new OA\Parameter(name: 'order_num', description: '订单号', in: 'query', schema: new OA\Schema(type: 'string')),
        ]  
    )]
    public function actionQuery()
    {
        $out_trade_no = $this->post_data['order_num'];
        if (!$out_trade_no) {
            json_error(['msg'=>lang('订单号不能为空')]);
        }
        $res = WeiXin::query($out_trade_no);
        json_success($res);
    }
    /**
     * H5支付下单
     */
    #[OA\Post(
        path: '/payment/api-weixin/h5',
        summary: 'H5支付下单',
        description: '创建H5支付订单，适用于移动端网页支付',
        tags: ['微信支付'],
        parameters: [
            new OA\Parameter(name: 'body', description: '订单描述', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'total_fee', description: '订单金额（单位：元）', in: 'query', schema: new OA\Schema(type: 'int')),
            new OA\Parameter(name: 'order_num', description: '订单号', in: 'query', schema: new OA\Schema(type: 'string')),
        ]
    )]
    public function actionH5()
    {
        $body = $this->post_data['body'];
        $total_fee = $this->post_data['total_fee'];
        $order_num = $this->post_data['order_num'];

        if (!$body || !$total_fee || !$order_num) {
            json_error(['msg'=>lang('参数不完整')]);
        }

        $req = [
            'body' => $body,
            'total_fee' => $total_fee,
            'order_num' => $order_num
        ];

        $res = WeiXin::h5($req);

        if (is_string($res)) {
            json_success($res);
        } else {
            json_error(['msg'=>$res['msg'] ?? '支付失败']);
        }
    }
    /**
     * Native支付下单
     */
    #[OA\Post(
        path: '/payment/api-weixin/native',
        summary: 'Native支付下单',
        description: '创建Native支付订单，返回二维码链接，适用于PC网站扫码支付',
        tags: ['微信支付'],
        parameters: [
            new OA\Parameter(name: 'body', description: '订单描述', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'total_fee', description: '订单金额（单位：元）', in: 'query', schema: new OA\Schema(type: 'int')),
            new OA\Parameter(name: 'order_num', description: '订单号', in: 'query', schema: new OA\Schema(type: 'string')),
        ]
    )]
    public function actionNative()
    {
        $body = $this->post_data['body'];
        $total_fee = $this->post_data['total_fee'];
        $order_num = $this->post_data['order_num'];

        if (!$body || !$total_fee || !$order_num) {
            json_error(['msg'=>lang('参数不完整')]);
        }

        $req = [
            'body' => $body,
            'total_fee' => $total_fee,
            'order_num' => $order_num
        ];

        $res = WeiXin::native($req);

        if (is_string($res)) {
            json_success($res);
        } else {
            json_error(['msg'=>$res['msg'] ?? '支付失败']);
        }
    }
    /**
     * JSAPI支付下单
     */
    #[OA\Post(
        path: '/payment/api-weixin/jsapi',
        summary: 'JSAPI支付下单',
        description: '创建JSAPI支付订单，适用于微信内网页支付',
        tags: ['微信支付'],
        parameters: [
            new OA\Parameter(name: 'body', description: '订单描述', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'total_fee', description: '订单金额（单位：元）', in: 'query', schema: new OA\Schema(type: 'int')),
            new OA\Parameter(name: 'order_num', description: '订单号', in: 'query', schema: new OA\Schema(type: 'string')),
        ], 
    )]
    public function actionJsapi()
    {
        $body = $this->post_data['body'];
        $total_fee = $this->post_data['total_fee'];
        $order_num = $this->post_data['order_num'];
        $openid = $this->post_data['openid'];

        if (!$body || !$total_fee || !$order_num || !$openid) {
            json_error('参数不完整');
        }

        $req = [
            'body' => $body,
            'total_fee' => $total_fee,
            'order_num' => $order_num,
            'openid' => $openid
        ];

        $res = WeiXin::jsapi($req);

        if (isset($res['code']) && $res['code'] === 0) {
            json_success($res['data']);
        } else {
            json_error(['msg'=>$res['msg'] ?? '支付失败']);
        }
    } 
    /**
     * POS扫码支付
     */
    #[OA\Post(
        path: '/payment/api-weixin/pos',
        summary: 'POS扫码支付',
        description: '扫描用户付款码进行支付，适用于线下收银场景',
        tags: ['微信支付'],
        parameters: [
            new OA\Parameter(name: 'order_num', description: '订单号', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'total_fee', description: '订单金额（单位：元）', in: 'query', schema: new OA\Schema(type: 'int')),
            new OA\Parameter(name: 'auth_code', description: '付款码', in: 'query', schema: new OA\Schema(type: 'string')),
        ], 
        
    )]
    public function actionPos()
    {
        $order_num = $this->post_data['order_num'];
        $total_fee = $this->post_data['total_fee'];
        $auth_code = $this->post_data['auth_code'];
        $body = $this->post_data['body'];
        $device_info = $this->post_data['device_info'] ?? 'sn2024';

        if (!$order_num || !$total_fee || !$auth_code || !$body) {
            json_error(['msg'=>lang('参数不完整')]);
        }

        $req = [
            'order_num' => $order_num,
            'total_fee' => $total_fee,
            'auth_code' => $auth_code,
            'body' => $body,
            'device_info' => $device_info
        ];

        $res = WeiXin::pos($req);

        if (isset($res['code']) && $res['code'] === 0) {
            json_success($res);
        } else {
            json_error(['msg'=>$res['err_code_des'] ?? '支付失败']);
        }
    }
     
}
