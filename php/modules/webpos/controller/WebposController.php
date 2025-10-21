<?php

namespace modules\webpos\controller;

use core\AppController;
use modules\product\data\ProductData;
use modules\payment\lib\WeiXin;
use modules\payment\lib\AlipayBarcode;
use modules\payment\lib\AlipayQuery;


class WebposController extends AppController
{
    protected $sys_tag = 'webpos';
    /**
     * 模型
     */
    protected $model = [
        'product' => '\modules\product\model\ProductModel',
        'user' => '\modules\admin\model\UserModel',
        'order' => '\modules\order\model\OrderModel',

    ];

    /**
     * 收银台首页
     * @permission 收银.管理 收银.查看
     */
    public function actionIndex() {}

    /**
     * 获取商品列表
     * @permission 收银.管理 收银.查看
     */
    public function actionProducts()
    {
        $title = $this->post_data['title'] ?? '';
        $where = [];
        $whereOr = [];
        if ($title) {
            $whereOr['title[~]'] =  $title;
            $whereOr['sku[~]'] =  $title;
        }
        $where['status'] = 'success';
        $where['ORDER'] = [
            'sort' => 'DESC',
            'id' => 'DESC',
        ];
        $where['product_type'] = 'product';
        //$where['spec_type'] = 1;
        if ($whereOr) {
            $where['OR'] = $whereOr;
        }

        $list = $this->model->product->pager($where);
        foreach ($list['data'] as &$item) {
            $item = ProductData::resetData($item);
        }

        return json_success($list);
    }
    /**
     * 创建订单
     */
    protected function createOrder($items, $time)
    {
        $orderCreator = new \modules\order\lib\OrderCreator();
        if (!$items) {
            return json_error(['msg' => lang('请选择商品')]);
        }
        $cache_key = "webpos_order_" . md5(json_encode($items)) . '_' . $time;
        $cache_data = cache($cache_key);
        if ($cache_data) {
            return $cache_data;
        }
        $new_items = [];
        $amount = 0; 
        foreach ($items as $v) {
            $new_items[] = [
                'product_id' => $v['product_id'],
                'title' => $v['title'],
                'price' => $v['price'],
                'num' => $v['num'],
                'image' => $v['image'],
                'spec' => $v['spec'] ?? '',
                'attr' => $v['attr'] ?? '',
            ];
            $row_amount = bcmul($v['price'], $v['num'], 2);
            $amount = bcadd($amount, $row_amount, 2);
        }

        $real_amount = $amount;
        $user_id = $this->post_data['user_id'] ?: -1;
        // 创建订单
        $orderData = [
            'user_id' => $user_id,
            'type' => 'webpos',
            'amount' => $amount,
            'real_amount' => $real_amount,
            'items' => $new_items
        ];

        $res = $orderCreator->create($orderData);
        $order_num = $res['order_num'];
        if ($order_num) {
            $order = $this->model->order->findOne(['order_num' => $order_num]);
            $amount = $order->real_amount;
            cache($cache_key, [
                'order_num' => $order_num,
                'amount' => $amount,
            ], 600);
            return [
                'order_num' => $order_num,
                'amount' => $amount,
            ];
        }
    }
    /**
     * 检查订单支付状态
     */
    public function actionCheckOrderPayment()
    {
        $order_num = $this->post_data['order_num'] ?? '';
        if (!$order_num) {
            json_error(['msg' => lang('请输入订单号')]);
        }
        $order = $this->model->order->findOne(['order_num' => $order_num]);
        if (!$order) {
            json_error(['msg' => lang('订单不存在')]);
        }
        $pay_type = $this->post_data['pay_type'];
        if ($pay_type  == 'weixin') {
            //检测微信支付
            try {
                $pay = WeiXin::query($order_num);
                if ($pay['is_paid']) {
                    db_update('order', [
                        'status' => 'complete',
                    ], [
                        'order_num' => $order_num,
                    ]);
                    json_success(['msg' => lang('订单已支付')]);
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        } else if ($pay_type  == 'alipay') {
            //检测支付宝支付
            try {
                $pay = AlipayQuery::run($order_num);
                if ($pay['is_paid']) {
                    db_update('order', [
                        'status' => 'complete',
                    ], [
                        'order_num' => $order_num,
                    ]);
                    json_success(['msg' => lang('订单已支付')]);
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
  

        if ($order->status == 'paid' || $order->status == 'complete') {
            json_success(['msg' => lang('订单已支付')]);
        } else {
            json_error(['msg' => lang('订单未支付')]);
        }
    }


    /**
     * 扫描二维码
     * @permission 收银.管理 
     */
    public function actionQr()
    {
        $qr = $this->post_data['qr'] ?? '';
        $items = $this->post_data['items'] ?? '';
        if (!$qr) {
            return json_error(['msg' => lang('请扫码')]);
        }

        //微信支付码
        if (get_webpos_code_is_weixin($qr)) {
            $time = time();
            $order = $this->createOrder($items, $time);
            $order_num = $order['order_num'] ?? '';
            if (!$order_num) {
                return json_error(['msg' => lang('请先点击结算按钮')]);
            }
            $res = $this->model->order->findOne(['order_num' => $order_num]);
            if (!$res) {
                return json_error(['msg' => lang('订单不存在')]);
            }
            if ($res->status != 'wait') {
                return json_error(['msg' => lang('订单已支付')]);
            }
            $total_fee = $res->real_amount;
            try {
                Weixin::pos([
                    'order_num' => $order_num,
                    'total_fee' => $total_fee,
                    'auth_code' => $qr,
                    'body' => 'pos支付',
                ]);
            } catch (\Throwable $th) {
            }


            return json_success(['data' => [
                'type' => 'pay',
                'pay_type' => 'weixin',
                'amount' => $total_fee,
                'order_num' => $order_num,
            ]]);
        }

        //支付宝支付
        if (get_webpos_code_is_alipay($qr)) {
            $time = time();
            $order = $this->createOrder($items, $time);
            $order_num = $order['order_num'] ?? '';
            if (!$order_num) {
                return json_error(['msg' => lang('请先点击结算按钮')]);
            }
            $res = $this->model->order->findOne(['order_num' => $order_num]);
            if (!$res) {
                return json_error(['msg' => lang('订单不存在')]);
            }
            if ($res->status != 'wait') {
                return json_error(['msg' => lang('订单已支付')]);
            }
            $total_fee = $res->real_amount;
            try {
                AlipayBarcode::run([
                    'order_num' => $order_num,
                    'total_fee' => $total_fee,
                    'auth_code' => $qr,
                    'body' => 'pos支付',
                ]);
            } catch (\Throwable $th) {
            }
            return json_success(['data' => [
                'type' => 'pay',
                'pay_type' => 'alipay',
                'amount' => $total_fee,
                'order_num' => $order_num,
            ]]);
        }

        //判断是商品码
        $res = get_product_by_sku($qr);
        if ($res) {
            return json_success(['data' => [
                'type' => 'product',
                'product' => $res
            ]]);
        }

        //判断是手机号
        $member = $this->model->user->findOne(['phone' => $phone]);
        if (!$member) {
            return json_error(['msg' => lang('会员不存在')]);
        }
        unset($member['password']);
        return json_success(['data' => [
            'type' => 'member',
            'member' => $member
        ]]);
    }
}
