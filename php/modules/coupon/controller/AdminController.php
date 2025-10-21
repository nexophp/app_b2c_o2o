<?php

namespace modules\coupon\controller;

use modules\coupon\model\CouponModel;
use modules\coupon\model\CouponUserModel;

class AdminController extends \core\AdminController
{
    protected $model = [
        'coupon' => "\modules\coupon\model\CouponModel",
        'coupon_user' => "\modules\coupon\model\CouponUserModel",
        'product' => "\modules\product\model\ProductModel",

    ];

    /**
     * 优惠券列表页面
     * @permission 优惠券.管理 优惠券.查看
     */
    public function actionIndex()
    {
        $list = $this->model->product->find([
            'status' => 'success',
            'ORDER' => [
                'sort' => 'DESC',
                'id' => 'DESC'
            ]
        ]);
        $products = [];
        foreach ($list as $v) {
            $products[] = [
                'label' => $v['title'],
                'value' => $v['id'],

            ];
        }

        $this->view_data['products'] = $products;
    }

    /**
     * 获取优惠券列表
     * @permission 优惠券.管理 优惠券.查看
     */
    public function actionList()
    {
        $where = [];
        $name = $this->post_data['name'] ?? '';
        $type = $this->post_data['type'] ?? '';
        $status = $this->post_data['status'] ?? '';

        if ($name) {
            $where['name[~]'] = $name;
        }
        if ($type) {
            $where['type'] = $type;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }

        $where['ORDER'] = ['id' => 'DESC'];
        $list = $this->model->coupon->pager($where);

        json($list);
    }

    /**
     * 添加优惠券
     * @permission 优惠券.管理
     */
    public function actionAdd()
    {
        $name = $this->post_data['name'] ?? '';
        $type = $this->post_data['type'] ?? 1;
        $value = $this->post_data['value'] ?? 0;
        $condition = $this->post_data['condition'] ?? 0;
        $seller_id = $this->post_data['seller_id'] ?? 0;
        $store_id = $this->post_data['store_id'] ?? 0;
        $products = $this->post_data['products'] ?? [];
        $types = $this->post_data['types'] ?? [];
        $days = $this->post_data['days'] ?? 0;

        if (empty($name)) {
            json_error(['msg' => lang('优惠券名称不能为空')]);
        }

        if ($value <= 0) {
            json_error(['msg' => lang('优惠券金额必须大于0')]);
        }

        // 折扣券的折扣比例不能大于1
        if ($type == 2 && $value >= 1) {
            json_error(['msg' => lang('折扣比例必须小于1')]);
        }

        if($type == 2 || !$products){
            json_error(['msg' => lang('请选择商品')]);
        }

        $data = [
            'name' => $name,
            'type' => $type,
            'value' => $value,
            'condition' => $condition,
            'days' => intval($days),
            'status' => 1,
            'created_at' => time(),
            'user_tag' => 'admin',
            'seller_id' => $seller_id,
            'store_id' => $store_id,
            'products' => json_encode($products),
            'types' => json_encode($types)
        ];

        $id = db_insert('coupon', $data);
        if ($id) {
            json_success(['msg' => lang('添加成功'), 'id' => $id]);
        } else {
            json_error(['msg' => lang('添加失败')]);
        }
    }

    /**
     * 删除优惠券
     * @permission 优惠券.管理
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('优惠券ID不能为空')]);
        }

        // 软删除
        db_update('coupon', ['status' => -1], ['id' => $id]);
        json_success(['msg' => lang('删除成功')]);
    }

    /**
     * 获取优惠券详情
     * @permission 优惠券.管理 优惠券.查看
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('优惠券ID不能为空')]);
        }

        $data = $this->model->coupon->findOne(['id' => $id]);
        if ($data) {
            json_success(['data' => $data]);
        } else {
            json_error(['msg' => lang('优惠券不存在')]);
        }
    }

    /**
     * 切换状态
     * @permission 优惠券.管理
     */
    public function actionToggleStatus()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('优惠券ID不能为空')]);
        }

        $coupon = db_get_one('coupon', ['id', 'status'], ['id' => $id]);
        if (!$coupon) {
            json_error(['msg' => lang('优惠券不存在')]);
        }

        $newStatus = $coupon['status'] == 1 ? 0 : 1;
        $result = db_update('coupon', ['status' => $newStatus], ['id' => $id]);
        json_success([
            'msg' => lang('状态更新成功'),
            'status' => $newStatus
        ]);
    }

    /**
     * 获取优惠券类型选项
     * @permission 优惠券.管理 优惠券.查看
     */
    public function actionGetTypeOptions()
    {
        $options = CouponModel::getTypeOptions();
        $result = [];
        foreach ($options as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        json_success(['data' => $result]);
    }

    /**
     * 获取用户优惠券列表
     * @permission 优惠券.管理 优惠券.查看
     */
    public function actionUserCouponList()
    {
        $where = [];
        $user_id = $this->post_data['user_id'] ?? '';
        $coupon_id = $this->post_data['coupon_id'] ?? '';
        $status = $this->post_data['status'] ?? '';

        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        if ($coupon_id) {
            $where['coupon_id'] = $coupon_id;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        $where['ORDER'] = ['id' => 'DESC'];
        $list = $this->model->coupon_user->pager($where);
        // 关联优惠券信息
        foreach ($list['data'] as &$item) {
            $coupon = $this->model->coupon->findOne(['id' => $item['coupon_id']]);
            if ($coupon) {
                $item['coupon_name'] = $coupon['name'];
            }
        }

        json($list);
    }




    /**
     * 获取优惠券领取记录
     * @permission 优惠券.管理 优惠券.查看
     */
    public function actionReceiveList()
    {
        $couponId = $this->post_data['coupon_id'] ?? 0;

        if (!$couponId) {
            json_error(['msg' => lang('参数错误')]);
        }
        $result = $this->model->coupon_user->pager([
            'coupon_id' => $couponId,
            'ORDER' => ['id' => 'DESC']
        ]);


        json($result);
    }

    /**
     * 获取优惠券使用记录
     * @permission 优惠券.管理 优惠券.查看
     */
    public function actionUsedList()
    {
        $couponId = $this->post_data['coupon_id'] ?? 0;
        if (!$couponId) {
            json_error(['msg' => lang('参数错误')]);
        }
        $result = $this->model->coupon_user->pager([
            'coupon_id' => $couponId,
            'status' => 2,
            'ORDER' => ['used_at' => 'DESC']
        ]);

        json($result);
    }

    /**
     * 优惠券记录页面
     * @permission 优惠券.管理 优惠券.查看
     */
    public function actionRecords() {}
}
