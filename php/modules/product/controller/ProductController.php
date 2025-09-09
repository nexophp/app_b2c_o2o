<?php

namespace modules\product\controller;

use modules\product\data\ProductData;

class ProductController extends \core\AdminController
{
    protected $sys_tag = 'product';
    protected $user_tag = 'admin';

    protected $model = [
        'product' => '\modules\product\model\ProductModel',
        'product_spec' => '\modules\product\model\ProductSpecModel',
        'product_attr' => '\modules\product\model\ProductAttrModel',
        'product_type' => '\modules\product\model\ProductTypeModel',
        'product_brand' => '\modules\product\model\ProductBrandModel',
    ];
    /**
     * 商品列表页面
     * @permission 商品.管理
     */
    public function actionIndex() {}

    /**
     * 商品分页列表
     * @permission 商品.管理 商品.查看
     */
    public function actionList()
    {
        $where = [];
        $title = $this->post_data['title'] ?? '';
        $type_id = $this->post_data['type_id'] ?? '';
        $brand_id = $this->post_data['brand_id'] ?? '';
        $status = $this->post_data['status'] ?? '';
        $recommend = $this->post_data['recommend'] ?? '';
        if ($title) {
            $where['title[~]'] = $title;
        }
        if ($type_id) {
            $type_in = $this->model->product_type->getTreeId($type_id) ?: [];
            $type_in[] = $type_id;
            $where['type_id_last'] = $type_in;
        }
        if ($brand_id) {
            $where['brand_id'] = $brand_id;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        $where['ORDER'] = ['sort' => 'ASC', 'id' => 'DESC'];
        $where['sys_tag'] = $this->sys_tag;
        $where['user_tag'] = $this->user_tag;
        $lower = $this->post_data['lower'] ?: 0;
        $notice_stock = get_config('notice_stock') ?: 10;
        if ($lower) {
            $in_product_id_spec =  db_get('product_spec', 'product_id', [
                'stock[<]' => $notice_stock,
            ]) ?: [];
            $in_product_id = db_get('product', 'id', [
                'stock[<]' => $notice_stock,
                'stock[>=]' => 0,
            ]) ?: [];
            $in_product_id = array_merge($in_product_id, $in_product_id_spec);
            $where['id'] = $in_product_id;
        }
        if ($recommend) {
            $where['recommend'] = $recommend;
        }
        $list = $this->model->product->pager($where);

        foreach ($list['data'] as &$v) {
            $v = ProductData::resetData($v);
        }
        json($list);
    }

    /**
     * 添加商品
     * @permission 商品.管理 商品.添加
     */
    public function actionAdd()
    {
        $data = $this->post_data;
        $data['sys_tag'] = $this->sys_tag;
        $data['user_tag'] = $this->user_tag;

        db_action(function () use ($data) {
            $this->model->product->insert($data);
        });
        json_success(['msg' => lang('添加成功')]);
    }

    /**
     * 编辑商品
     * @permission 商品.管理 商品.修改
     */
    public function actionEdit()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('商品ID不能为空')]);
        }
        $data = $this->post_data;
        db_action(function () use ($data, $id) {
            $this->model->product->update($data, ['id' => $id]);
        });

        json_success(['msg' => lang('修改成功')]);
    }

    /**
     * 删除商品
     * @permission 商品.管理 商品.删除
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('商品ID不能为空')]);
        }

        $result = $this->model->product->delete(['id' => $id]);
        if ($result) {
            json_success(['msg' => lang('删除成功')]);
        } else {
            json_error(['msg' => lang('删除失败')]);
        }
    }

    /**
     * 获取商品详情
     * @permission 商品.管理 商品.查看
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('商品ID不能为空')]);
        }
        $data = $this->model->product->find($id);
        if ($data) {
            $data = ProductData::resetData($data);

            json_success(['data' => $data]);
        } else {
            json_error(['msg' => lang('商品不存在')]);
        }
    }
    /**
     * 商品状态切换
     * @permission 商品.管理
     */
    public function actionChangeStatus()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('商品ID不能为空')]);
        }
        $data = $this->model->product->find($id);
        if (!$data) {
            json_error(['msg' => lang('商品不存在')]);
        }
        if ($data['status'] == 'success') {
            $status = 'error';
        } else {
            $status = 'success';
        }
        db_action(function () use ($id, $status) {
            $this->model->product->update(['status' => $status], ['id' => $id], true);
        });
        json_success(['msg' => lang('操作成功')]);
    }
    /**
     * 商品推荐
     * @permission 商品.管理
     */
    public function actionRecommend()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('商品ID不能为空')]);
        }
        $data = $this->model->product->findOne(['id' => $id]);
        if (!$data) {
            json_error(['msg' => lang('商品不存在')]);
        }
        if ($data['recommend'] == 1) {
            $recommend = 0;
        } else {
            $recommend = 1;
        }
        $data = ['recommend' => $recommend];
        if ($recommend == 1) {
            $data['recommend_at'] = time();
        }
        $this->model->product->update($data, ['id' => $id], true);
        json_success(['msg' => lang('操作成功'), 'data' => $recommend]);
    }
}
