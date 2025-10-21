<?php

namespace modules\logistic\controller;

use modules\logistic\data\LogisticTemplateData;
use modules\logistic\model\LogisticTemplateModel;
use modules\logistic\model\LogisticTemplateRegionModel;

class TemplateController extends \core\AdminController
{
    protected $model = [
        'template' => '\modules\logistic\model\LogisticTemplateModel',
        'template_region' => '\modules\logistic\model\LogisticTemplateRegionModel',
    ];

    /**
     * 运费模板列表页面
     * @permission 运费模板.管理
     */
    public function actionIndex()
    {
        // 页面渲染由前端处理
    }

    /**
     * 运费模板分页列表
     * @permission 运费模板.管理 运费模板.查看
     */
    public function actionList()
    {
        $where = [];
        $name = $this->post_data['name'] ?? '';
        $status = $this->post_data['status'] ?? '';
        $seller_id = $this->post_data['seller_id'] ?: 0;


        if ($name) {
            $where['name[~]'] = $name;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        if ($seller_id) {
            $where['seller_id'] = $seller_id;
        }
        $where['ORDER'] = ['id' => 'DESC'];

        $list = $this->model->template->pager($where);

        foreach ($list['data'] as &$v) {
            $v = LogisticTemplateData::resetData($v);
        }
        json($list);
    }

    /**
     * 添加运费模板
     * @permission 运费模板.管理 运费模板.添加
     */
    public function actionAdd()
    {
        $data = $this->post_data;
        $data['seller_id'] = $data['seller_id'] ?: 0;
        $data['created_at'] = time();
        $data['updated_at'] = time();

        // 检查名称是否已存在
        $exists = $this->model->template->find(['name' => $data['name'], 'seller_id' => $data['seller_id']]);
        if ($exists) {
            json_error(['msg' => lang('模板名称已存在')]);
        }

        // 区域设置
        $regions = $data['regions'] ?? [];
        unset($data['regions']);

        db_action(function () use ($data, $regions) {
            // 添加模板
            $template_id = $this->model->template->insert($data);

            // 添加区域设置
            $this->saveRegions($template_id, $regions);
        });

        json_success(['msg' => lang('添加成功')]);
    }

    /**
     * 编辑运费模板
     * @permission 运费模板.管理 运费模板.修改
     */
    public function actionEdit()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('模板ID不能为空')]);
        }

        $template = $this->model->template->find($id);
        if (!$template) {
            json_error(['msg' => lang('模板不存在')]);
        }

        // 检查权限
        if ($template['seller_id'] != $this->seller_id && !$this->isAdmin()) {
            json_error(['msg' => lang('无权操作此模板')]);
        }

        $data = $this->post_data;
        $data['updated_at'] = time();

        // 检查名称是否已存在（排除当前记录）
        $exists = $this->model->template->find([
            'name' => $data['name'],
            'seller_id' => $template['seller_id'],
            'id[!]' => $id
        ]);
        if ($exists) {
            json_error(['msg' => lang('模板名称已存在')]);
        }

        // 区域设置
        $regions = $data['regions'] ?? [];
        unset($data['regions']);

        db_action(function () use ($data, $id, $regions) {
            // 更新模板
            $this->model->template->update($data, ['id' => $id], true);

            // 删除旧的区域设置
            $this->model->template_region->delete(['template_id' => $id]);

            // 添加新的区域设置
            $this->saveRegions($id, $regions);
        });

        json_success(['msg' => lang('修改成功')]);
    }

    /**
     * 删除运费模板
     * @permission 运费模板.管理 运费模板.删除
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('模板ID不能为空')]);
        }

        $template = $this->model->template->find($id);
        if (!$template) {
            json_error(['msg' => lang('模板不存在')]);
        }

        // 检查权限
        if ($template['seller_id'] != $this->seller_id && !$this->isAdmin()) {
            json_error(['msg' => lang('无权操作此模板')]);
        }

        db_action(function () use ($id) {
            // 删除模板
            $this->model->template->delete(['id' => $id]);

            // 删除区域设置
            $this->model->template_region->delete(['template_id' => $id]);
        });

        json_success(['msg' => lang('删除成功')]);
    }

    /**
     * 获取运费模板详情
     * @permission 运费模板.管理 运费模板.查看
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('模板ID不能为空')]);
        }

        $template = $this->model->template->find($id);
        if (!$template) {
            json_error(['msg' => lang('模板不存在')]);
        }

        // 检查权限
        if ($template['seller_id'] != $this->seller_id && !$this->isAdmin()) {
            json_error(['msg' => lang('无权查看此模板')]);
        }

        $data = LogisticTemplateData::getTemplateDetail($id);
        if ($data) {
            json_success(['data' => $data]);
        } else {
            json_error(['msg' => lang('模板不存在')]);
        }
    }

    /**
     * 更新状态
     * @permission 运费模板.管理 运费模板.修改
     */
    public function actionUpdateStatus()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('模板ID不能为空')]);
        }

        $template = $this->model->template->find($id);
        if (!$template) {
            json_error(['msg' => lang('模板不存在')]);
        }

        // 检查权限
        if ($template['seller_id'] != $this->seller_id && !$this->isAdmin()) {
            json_error(['msg' => lang('无权操作此模板')]);
        }

        $status = $template->status;
        if ($status == 1) {
            $status = 0;
        } else {
            $status = 1;
        }

        $data = [
            'status' => $status,
            'updated_at' => time()
        ];

        $this->model->template->update($data, ['id' => $id], true);

        json_success(['msg' => lang('状态更新成功')]);
    }

    /**
     * 获取卖家的所有运费模板
     * @permission 运费模板.查看
     */
    public function actionGetSellerTemplates()
    {
        $seller_id = $this->post_data['seller_id'] ?? $this->seller_id;

        // 检查权限
        if ($seller_id != $this->seller_id && !$this->isAdmin()) {
            json_error(['msg' => lang('无权查看此卖家的模板')]);
        }

        $templates = $this->model->template->findAll([
            'seller_id' => $seller_id,
            'status' => 1,
            'ORDER' => ['name' => 'ASC']
        ]);

        $data = [];
        foreach ($templates as $template) {
            $data[] = [
                'label' => $template['name'],
                'value' => $template['id']
            ];
        }

        json_success(['data' => $data]);
    }

    /**
     * 计算运费
     * @permission 运费模板.查看
     */
    public function actionCalculateFee()
    {
        $template_id = $this->post_data['template_id'] ?? 0;
        $province_id = $this->post_data['province_id'] ?? 0;
        $quantity = $this->post_data['quantity'] ?? 1;
        $order_amount = $this->post_data['order_amount'] ?? 0;

        if (!$template_id || !$province_id) {
            json_error(['msg' => lang('参数错误')]);
        }

        $fee = LogisticTemplateData::calculateFee($template_id, $province_id, $quantity, $order_amount);

        json_success(['data' => ['fee' => $fee]]);
    }

    /**
     * 保存区域设置
     */
    private function saveRegions($template_id, $regions)
    {
        if (empty($regions)) {
            // 如果没有提供区域设置，创建一个默认区域
            $default_region = [
                'template_id' => $template_id,
                'region_type' => 1, // 默认区域
                'regions' => '[]',
                'first_item' => 1,
                'first_fee' => 10, // 默认10元
                'additional_item' => 1,
                'additional_fee' => 5, // 默认5元
                'is_free_shipping' => 0,
                'free_shipping_amount' => 0
            ];

            $this->model->template_region->insert($default_region);
            return;
        }

        foreach ($regions as $region) {
            $region_data = [
                'template_id' => $template_id,
                'region_type' => $region['region_type'],
                'regions' => is_array($region['regions']) ? json_encode($region['regions']) : $region['regions'], 
                'first_fee' => $region['first_fee'], // 首件  
                'additional_fee' => $region['additional_fee'],// 续件
                'is_free_shipping' => $region['is_free_shipping'] ?? 0,
                'free_shipping_amount' => $region['free_shipping_amount'] ?? 0,
                'first_weight' => $region['first_weight'] ?? 0, // 首重
                'additional_weight' => $region['additional_weight'] ?? 0, // 续重 
            ];

            $this->model->template_region->insert($region_data);
        }
    }

    /**
     * 检查是否为管理员
     */
    private function isAdmin()
    {
        return $this->admin_id > 0;
    }

    /**
     * 获取区域列表
     * @permission 运费模板.查看
     */
    public function  actionCascader()
    {
        $d = \element\form::get_city();
        return json_success(['data' => $d]);
    }
}
