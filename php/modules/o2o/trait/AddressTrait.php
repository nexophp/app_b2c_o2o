<?php

namespace modules\o2o\trait;

trait AddressTrait
{
    /**
     * 获取地址列表
     */
    public function list()
    {

        $title = $this->post_data['title'];
        $status = $this->post_data['status'];

        $where = [];

        // 按简称搜索
        if ($title) {
            $where['title[~]'] = $title;
        }

        // 按状态搜索
        if ($status !== '') {
            $where['status'] = intval($status);
        }

        // 按商家ID搜索（如果有）
        $seller_id = $this->post_data['seller_id'] ?: 0;
        if ($seller_id) {
            $where['seller_id'] = $seller_id;
        }
        $where['ORDER'] = [
            'id' => 'DESC'
        ];
        $list = $this->model->address->pager($where);

        json_success($list);
    }

    /**
     * 获取地址详情
     */
    public function info()
    {
        $id = intval($this->post_data['id']);
        if (!$id) {
            json_error(lang('参数错误'));
        }

        $info = $this->model->address->findOne($id);
        if (!$info) {
            json_error(lang('地址不存在'));
        }

        json_success(['data' => $info]);
    }

    /**
     * 添加地址
     */
    public function add()
    {
        $data = $this->post_data;

        // 验证必填字段
        if (empty($data['title'])) {
            json_error(lang('请输入地址简称'));
        }

        if (empty($data['regions'])) {
            json_error(lang('请选择区域'));
        }

        // 验证简称唯一性
        $exists = $this->model->address->find(['title' => $data['title']]);
        if ($exists) {
            json_error(lang('地址简称已存在'));
        }

        // 设置商家ID（如果有）
        $seller_id = intval($this->post_data['seller_id']);
        if ($seller_id) {
            $data['seller_id'] = $seller_id;
        }

        $result = $this->model->address->insert($data);
        if ($result) {
            json_success(lang('添加成功'));
        } else {
            json_error(lang('添加失败'));
        }
    }

    /**
     * 编辑地址
     */
    public function edit()
    {
        $id = intval($this->post_data['id']);
        if (!$id) {
            json_error(lang('参数错误'));
        }

        $info = $this->model->address->find($id);
        if (!$info) {
            json_error(lang('地址不存在'));
        }

        $data = $this->post_data;

        // 验证必填字段
        if (empty($data['title'])) {
            json_error(lang('请输入地址简称'));
        }

        if (empty($data['regions'])) {
            json_error(lang('请选择区域'));
        }

        // 验证简称唯一性（排除当前记录）
        $exists = $this->model->address->find(['title' => $data['title'], 'id[!]' => $id]);
        if ($exists) {
            json_error(lang('地址简称已存在'));
        }

        $result = $this->model->address->update($data, ['id' => $id]);
        if ($result !== false) {
            json_success(lang('编辑成功'));
        } else {
            json_error(lang('编辑失败'));
        }
    }

    /**
     * 删除地址
     */
    public function delete()
    {
        $id = intval($this->post_data['id']);
        if (!$id) {
            json_error(lang('参数错误'));
        }

        $info = $this->model->address->find($id);
        if (!$info) {
            json_error(lang('地址不存在'));
        }

        $result = $this->model->address->delete(['id' => $id]);
        if ($result) {
            json_success(lang('删除成功'));
        } else {
            json_error(lang('删除失败'));
        }
    }

    /**
     * 获取状态选项
     */
    public function statusOptions()
    {
        $options = $this->model->address->getStatusOptions();
        json_success(['data' => $options]);
    }
}
