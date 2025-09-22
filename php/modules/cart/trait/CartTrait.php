<?php

namespace modules\cart\trait;

use modules\product\data\ProductData;
use modules\admin\model\UserModel;

trait CartTrait
{
    /**
     * 购物车选中的列表
     */
    protected function selected($data = [])
    {
        $type = $this->post_data['type'] ?? 'product';
        $where = ['type' => $type];
        $where['user_id'] = $this->uid;
        $where['ORDER'] = ['id' => 'DESC'];
        $where['selected'] = 1;
        $data = $this->model->cart_item->find($where);
        if ($data) {
            $data = $data->toArray();
        }
        $list['data'] = $data;
        return json_success($list);
    }
    /**
     * API购物车列表
     */
    protected function index($data = [])
    {
        $type = $this->post_data['type'] ?? 'product';
        $where = ['type' => $type];
        $where['user_id'] = $this->uid;
        $where['ORDER'] = ['id' => 'DESC'];
        $data = $this->model->cart_item->find($where);
        if ($data) {
            $data = $data->toArray();
        }
        $error = [];
        $selected_count = 0;
        $total_count = 0;
        $selected_amount = 0;
        if ($data) {
            foreach ($data as $key => $val) {
                if ($val['product_status'] != 'success') {
                    $error[] = $data[$key];
                    unset($data[$key]);
                }
                $data[$key]['selected'] = (string)$val['selected'];
                if ($val['selected'] == 1) {
                    $selected_count++;
                    $amount = bcmul($val['price'], $val['num'], 2);
                    $selected_amount = bcadd($selected_amount, $amount, 2);
                }
                $total_count++;
            }
        }
        $list['data'] = $data;
        $where['selected'] = 1;
        $total_amount =  $this->model->cart_item->sum('amount', $where);
        $count =  $this->model->cart_item->count($where);
        $list['total_num'] =  $count;
        $list['total_amount'] = $total_amount;
        if ($data && $count == count($data)) {
            $list['selected'] = 1;
        } else {
            $list['selected'] = 0;
        }
        if ($error) {
            foreach ($error as $v) {
                db_update('cart_item', ['selected' => 0], ['id' => $v['id']]);
            }
        }
        $list['error'] = $error; 
        /**
         * 1 部分选中 2 全部
         */
        if ($selected_count > 0) {
            $list['selected_checkbox'] = 1;
            if ($selected_count == $total_count) {
                $list['selected_checkbox'] = 2;
            }
        } else {
            $list['selected_checkbox'] = 0;
        }
        $list['selected_count'] = $selected_count;
        $list['selected_amount'] = $selected_amount;

        return json_success($list);
    }
    /**
     * admin购物车列表
     * 仅提供给CartController使用，平台管理用户购物车。
     */
    protected function list($opt = [])
    {
        $where = [];
        $phone = $opt['phone'] ?? ''; // 改为手机号搜索
        $type = $opt['type'] ?? 'product';
        $start_time = $opt['start_time'] ?? '';
        $end_time = $opt['end_time'] ?? '';
        $uid = $opt['user_id'] ?? 0;

        // 如果有手机号搜索，先根据手机号找到用户ID
        if ($phone) {
            $user = UserModel::model()->find(['phone' => $phone], 1);
            if ($user) {
                $where['user_id'] = $user->id;
            } else {
                // 如果找不到用户，返回空结果
                json_success([
                    'data' => [],
                    'total' => 0,
                    'search' => [
                        'phone' => $phone,
                        'type' => $type,
                        'start_time' => $start_time,
                        'end_time' => $end_time
                    ]
                ]);
                return;
            }
        }
        if ($type) {
            $where['type'] = $type;
        }
        if ($start_time) {
            $where['created_at[>=]'] = strtotime($start_time);
        }
        if ($end_time) {
            $where['created_at[<=]'] = strtotime($end_time . ' 23:59:59');
        }
        if ($uid) {
            $where['user_id'] = $uid;
        }

        $where['ORDER'] = ['id' => 'DESC'];
        $list = $this->model->cart_item->pager($where);

        $selected_count = 0;
        $total_count = 0;
        // 获取用户信息，显示手机号
        $selected_amount = 0;
        foreach ($list['data'] as &$item) {
            if (isset($item['user_id'])) {
                $user = UserModel::model()->find(['id' => $item['user_id']], 1);
                if ($user) {
                    $item['user_phone'] = $user->phone ?? ''; // 添加手机号字段
                    $item['user_info'] = [
                        'id' => $user->id,
                        'username' => $user->username ?? '',
                        'nickname' => $user->nickname ?? '',
                        'phone' => $user->phone ?? ''
                    ];
                }
            }
            $item['selected'] = (string)$item['selected'];
            if ($item['selected'] == 1) {
                $selected_count++;
                $amount = bcmul($item['price'], $item['num'], 2);
                $selected_amount = bcadd($selected_amount, $amount, 2);
            }
            $total_count++;
        }
        $list['search'] = [
            'phone' => $phone, // 改为手机号
            'type' => $type,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];
        /**
         * 1 部分选中 2 全部
         */
        if ($selected_count > 0) {
            $list['selected'] = 1;
            if ($selected_count == $total_count) {
                $list['selected'] = 2;
            }
        } else {
            $list['selected'] = 0;
        }
        $list['selected_count'] = $selected_count;
        $list['selected_amount'] = $selected_amount;
        json_success($list);
    }
    /**
     * 删除购物车
     */
    protected function delete($id, $user_id = '')
    {
        if (!$id) {
            json_error(lang('参数错误'));
        }
        $where = ['id' => $id];
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        $cart = $this->model->cart_item->find($where);
        if (!$cart) {
            return json_error([lang('购物车不存在')]);
        }
        // 删除购物车项目
        $this->model->cart_item->delete(['id' => $id]);
        return json_success(['msg' => lang('删除成功')]);
    }
    /**
     * 搜索用户
     */
    protected function searchUser($phone)
    {
        if (!$phone) {
            return json_error(['msg' => lang('请输入手机号')]);
        }

        $users = $this->model->user->findAll([
            'phone[~]' => $phone,
            'LIMIT' => 10
        ]);

        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user['id'],
                'phone' => $user['phone'] ?? '',
                'nickname' => $user['nickname'] ?? '',
                'email' => $user['email'] ?? ''
            ];
        }

        return json_success(['data' => $result]);
    }

    /**
     * 获取商品列表
     */
    protected function products($keyword = '')
    {
        $where = ['status' => 'success']; // 只获取启用的商品

        if ($keyword) {
            $where['title[~]'] = $keyword;
        }

        $where['LIMIT'] = 20;
        $where['ORDER'] = ['sort' => 'DESC', 'id' => 'DESC'];

        // 关联查询商品规格
        $list = $this->model->product->pager($where);

        foreach ($list['data'] as &$product) {
            $product = ProductData::resetData($product);
        }

        return json_success($list);
    }

    /**
     * 添加到购物车
     */
    protected function add($data = [])
    {
        $type = $data['type'] ?? 'product';
        $user_id = $data['user_id'] ?: $this->user_id;
        $product_id = $data['product_id'] ?? '';
        $num = $data['num'] ?? 1;
        $str_1 = $data['str_1'] ?? '';
        $str_2 = $data['str_2'] ?? '';
        $param_1 = $data['param_1'] ?? [];
        $param_2 = $data['param_2'] ?? [];
        $title = $data['title'] ?? '';
        $price = $data['price'] ?? 0;
        $image = $data['image'] ?? '';
        $spec = $data['spec'] ?? '';
        $attr = $data['attr'] ?? '';
        if ($product_id) {
            $product_info = $this->model->product->findOne(['id' => $product_id]);
            if ($product_info) {
                $product_info = ProductData::resetData($product_info);
                $image = $image ?: $product_info['image'];
                $price = $price ?: $product_info['price'];
                $title = $product_info['title'];
                if ($str_1) {
                    $spec_price = array_column($product_info['spec'], 'price', 'title');
                    $spec_image = array_column($product_info['spec'], 'image', 'title');
                    $price = $spec_price[$str_1] ?? $price;
                    if ($spec_image) {
                        $image = $spec_image[$str_1] ?? $image;
                    }
                }
            }
        }

        if (!$user_id) {
            return json_error(['msg' => lang('参数错误')]);
        }
        $where = ['user_id' => $user_id, 'type' => $type];

        if ($product_id) {
            $where['product_id'] = $product_id;
        }
        if ($title) {
            $where['title'] = $title;
        }
        if ($str_1) {
            $where['str_1'] = $str_1;
        }
        if ($str_2) {
            $where['str_2'] = $str_2;
        }
        if ($spec) {
            $where['spec'] = $spec;
        }
        if ($attr) {
            $where['attr'] = $attr;
        }
        $existing_item = $this->model->cart_item->findOne($where);
        if ($existing_item) {
            $this->model->cart_item->update([
                'num' => $num,
                'updated_at' => time()
            ], ['id' => $existing_item->id], true);
        } else {
            $cart_item_data = [
                'type' => $type,
                'user_id' => $user_id,
                'product_id' => $product_id,
                'title' => $title,
                'price' => $price,
                'amount' => bcmul($price, $num, 2),
                'image' => $image,
                'num' => $num,
                'str_1' => $str_1,
                'str_2' => $str_2,
                'param_1' => $param_1,
                'param_2' => $param_2,
                'spec' => $spec,
                'attr' => $attr,
                'created_at' => time(),
                'updated_at' => time()
            ];
            $this->model->cart_item->insert($cart_item_data);
        }
        return json_success(['msg' => lang('成功添加商品到购物车')]);
    }
    /**
     * 更新商品数量
     */
    protected function update($item_id, $num, $user_id = '')
    {
        if (!$num) {
            json_error(['msg' => lang('参数错误')]);
        }
        if ($num <= 0) {
            $this->delete($item_id);
            return;
        }
        if (!$item_id) {
            return json_error(['msg' => lang('参数错误')]);
        }

        $item = $this->model->cart_item->findOne([
            'id' => $item_id
        ]);

        if (!$item) {
            return json_error(['msg' => lang('购物车商品不存在')]);
        }
        $where = [
            'id' => $item_id,
        ];
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        $this->model->cart_item->update([
            'num' => $num,
            'amount' => bcmul($item->price, $num, 2),
            'updated_at' => time()
        ], $where, true);

        return json_success(['msg' => lang('更新成功')]);
    }
    /**
     * 清空购物车
     */
    protected function clear($user_id, $type = 'product')
    {
        $where = [
            'user_id' => $user_id,
            'type' => $type,
        ];
        $this->model->cart_item->delete($where);
        return json_success(['msg' => lang('清空成功')]);
    }
    /**
     * 全选/取消全选
     */
    protected function selectAll($user_id,  $selected, $type = 'product')
    {
        $this->model->cart_item->update([
            'selected' => $selected,
        ], [
            'user_id' => $user_id,
            'type' => $type,
        ], true);
        return json_success(['msg' => lang('操作成功')]);
    }
}
