<?php

namespace modules\coupon\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: '优惠券', description: '优惠券接口')]
class ApiController extends \core\ApiController
{
    protected $need_login = false;
    protected $model = [
        'coupon' => "\modules\coupon\model\CouponModel",
        'coupon_user' => "\modules\coupon\model\CouponUserModel",
    ];

    /**
     * 可领取优惠券列表
     */
    #[OA\Get(
        path: '/coupon/api/guest-list',
        summary: '获取可领取优惠券列表',
        tags: ['优惠券'],
    )]
    public function actionGuestList()
    {
        $where = [
            'ORDER' => ['id' => 'DESC'],
            'status' => 1,
        ];
        $all = $this->model->coupon->pager($where);
        $uid = $this->uid;
        foreach ($all['data'] as &$v) {
            $v['is_received'] = 0;
            if ($uid) {
                $is_receive = $this->model->coupon_user->findOne(['coupon_id' => $v['id'], 'user_id' => $uid]);
                $v['is_received'] = $is_receive ? 1 : 0;
            }
        }
        json($all);
    }
    /**
     * 我的优惠券列表
     */
    #[OA\Get(
        path: '/coupon/api/count',
        summary: '获取我的优惠券数量',
        tags: ['优惠券'],
    )]
    public function actionCount()

    {
        $user_id = $this->user_id;
        if (!$user_id) {
            json_error([
                'msg' => lang('请先登录'),
            ]);
        }
        //更新过期的优惠券
        $this->model->coupon_user->updateExpired($user_id);
        $where = [
            'user_id' => $user_id,
            'ORDER' => ['id' => 'DESC'],
            'status' => 1,
        ];
        $count = $this->model->coupon_user->count($where);
        json_success([
            'data' => $count,
        ]);
    }
    /**
     * 我的优惠券列表
     */
    #[OA\Get(
        path: '/coupon/api/my-list',
        summary: '获取我的优惠券列表',
        tags: ['优惠券'],
    )]
    public function actionMyList()
    {
        $user_id = $this->user_id;
        if (!$user_id) {
            json_error([
                'msg' => lang('请先登录'),
            ]);
        }
        //更新过期的优惠券
        $this->model->coupon_user->updateExpired($user_id);

        $where = [
            'user_id' => $user_id,
            'ORDER' => ['id' => 'DESC'],
        ];
        $status = $this->post_data['status'] ?? 0;
        switch ($status) {
            case "available":
                $where['status'] = 1;
                break;
            case "used":
                $where['status'] = 2;
                break;
            case "expired":
                $where['status'] = -1;
                break;
        }
        $items = g("items");
        if ($items) {
            $product_id = array_column($items, 'product_id'); 
            $all = $this->model->coupon_user->find($where);
            if($all){
                $all = $all->toArray();
            }
            foreach ($all as $k => $v) {
                $products = $v['products']; 
                //适用商品 
                if ($products) { 
                    //判断数组是否有交集
                    if(!array_intersect($product_id, $products)){
                        unset($all[$k]);
                    } 
                }else{
                    //全部商品  
                    unset($all[$k]);
                }
            } 
            if($all){
                $all = array_values($all);
            }
            json_success(['data' => $all]);
        }
        $all = $this->model->coupon_user->pager($where);
        json($all);
    }

    /**
     * 领取优惠券
     */
    #[OA\Post(
        path: '/coupon/api/receive',
        summary: '领取优惠券',
        tags: ['优惠券'],
        parameters: [
            new OA\Parameter(name: 'id', description: '优惠券ID', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionReceive()
    {
        $user_id = $this->user_id;
        if (!$user_id) {
            json_error([
                'msg' => lang('请先登录'),
            ]);
        }
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('优惠券不存在')]);
        }
        $where = [
            'id' => $id,
            'status' => 1,
        ];
        $info = $this->model->coupon->findOne($where);
        if (!$info) {
            json_error([
                'msg' => lang('优惠券不存在'),
            ]);
        }
        $user_id = $this->user_id;
        $where = [
            'user_id' => $user_id,
            'coupon_id' => $id,
        ];
        $user = $this->model->coupon_user->findOne($where);
        if ($user) {
            json_error([
                'msg' => lang('您已领取过该优惠券'),
            ]);
        }
        $expired_at = time() + $info['days'] * 886400;
        $data = [
            'user_id' => $user_id,
            'coupon_id' => $id,
            'type' => $info['type'],
            'value' => $info['value'],
            'condition' => $info['condition'],

            'expired_at' => $expired_at,
            'status' => 1, //1可用，2已用 -1 过期
            'created_at' => time(),
            'products' => $info['products'],
            'types' => $info['types'],
        ];
        $this->model->coupon_user->insert($data);
        json([
            'code' => 0,
            'msg' => '领取成功',
        ]);
    }
}
