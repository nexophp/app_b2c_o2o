<?php

namespace modules\ticket\trait;

trait TicketTrait
{
    /**
     * 获取打印机列表 
     */
    protected function list()
    {
        $where = [];
        $search = $this->post_data ?? [];

        // 搜索条件
        if (!empty($search['title'])) {
            $where['title[~]'] = $search['title'];
        }

        if (!empty($search['code'])) {
            $where['code[~]'] = $search['code'];
        }

        if (!empty($search['type'])) {
            $where['type'] = $search['type'];
        }

        if (!empty($search['device_status'])) {
            $where['device_status'] = $search['device_status'];
        }

        $where['sys_tag'] = $this->user_tag;
        $where['ORDER'] = ['id' => 'DESC'];

        $this->commWhere($where);
        $all = $this->model->ticket->pager($where);

        foreach ($all['data'] as &$v) {
            $v->created_at_text = date('Y-m-d H:i', $v->created_at);
            if ($v->updated_at) {
                $v->updated_at_text = date('Y-m-d H:i', $v->updated_at);
            }
            $type = $v['type'];
            $class = '\\modules\\ticket\\lib\\Info';
            $name = "get" . $type . "Info";
            $status = $class::$name($v['code'], $v['secret']); 
            if ($status) {
                db_update("ticket", ["device_status" => 'online'], ["id" => $v['id']]);
            } else {
                db_update("ticket", ["device_status" => 'offline'], ["id" => $v['id']]);
            }
        }

        json($all);
    }

    /**
     * 获取打印机详情
     */
    protected function info()
    {
        $id = $this->post_data['id'] ?? 0;

        if (!$id) {
            json_error(['msg' => '参数错误']);
        }

        $where['id'] = $id;
        $where['sys_tag'] = $this->user_tag;
        $this->commWhere($where);
        $v = $this->model->ticket->findOne($where);

        if (!$v) {
            json_error(['msg' => lang('打印机不存在')]);
        }

        $v->created_at_text = date('Y-m-d H:i', $v->created_at);
        if ($v->updated_at) {
            $v->updated_at_text = date('Y-m-d H:i', $v->updated_at);
        }

        json_success(['msg' => '获取成功', 'data' => $v]);
    }

    /**
     * 添加打印机
     */
    protected function add()
    {
        $data = $this->post_data;

        // 验证必填字段
        if (empty($data['title'])) {
            json_error(['msg' => lang('打印机名称不能为空')]);
        }

        if (empty($data['code'])) {
            json_error(['msg' => lang('打印机编码不能为空')]);
        }

        if (empty($data['secret'])) {
            json_error(['msg' => lang('打印机密钥不能为空')]);
        }

        if (empty($data['type'])) {
            json_error(['msg' => lang('打印机类型不能为空')]);
        }

        $where['code'] = $data['code'];
        $this->commWhere($where);
        $where['sys_tag'] = $this->user_tag;

        // 检查编码是否已存在
        $exists = $this->model->ticket->findOne($where);
        if ($exists) {
            json_error(['msg' => lang('该打印机编码已存在')]);
        }

        // 设置默认值
        $data['sys_tag'] = $this->user_tag;
        $data['user_id'] = $this->user_id;
        $data['created_at'] = time();
        $data['updated_at'] = time();
        $data['seller_id'] = $this->seller_id ?: 0;
        $data['store_id'] = $this->store_id ?: 0;
        $data['status'] = $data['status'] ?: 'success';

        $value = $this->where_value;
        if ($value) {
            $data[$this->where_key] = $this->$value;
        }

        $id = $this->model->ticket->insert($data);
        add_ticket_print($id);
        json_success(['msg' => lang('添加成功'), 'id' => $id]);
    }

    /**
     * 编辑打印机
     */
    protected function edit()
    {
        $id = $this->post_data['id'] ?? 0;
        $data = $this->post_data;

        if (!$id) {
            json_error(['msg' => lang('参数错误')]);
        }

        // 验证打印机是否存在
        $where['id'] = $id;
        $this->commWhere($where);

        $ticket = $this->model->ticket->findOne($where);
        if (!$ticket) {
            json_error(['msg' => lang('打印机不存在')]);
        }

        // 验证必填字段
        if (empty($data['title'])) {
            json_error(['msg' => lang('打印机名称不能为空')]);
        }

        if (empty($data['code'])) {
            json_error(['msg' => lang('打印机编码不能为空')]);
        }

        if (empty($data['type'])) {
            json_error(['msg' => lang('打印机类型不能为空')]);
        }

        $where = ['code' => $data['code'], 'id[!]' => $id];
        $this->commWhere($where);
        // 检查编码是否已存在（排除当前记录）
        $exists = $this->model->ticket->findOne($where);
        if ($exists) {
            json_error(['msg' => lang('该打印机编码已存在')]);
        }

        // 更新时间
        $data['updated_at'] = time();

        // 移除不需要更新的字段
        unset($data['id'], $data['created_at']);

        $this->model->ticket->update($data, ['id' => $id]);

        add_ticket_print($id);

        json_success(['msg' => lang('编辑成功')]);
    }

    /**
     * 删除打印机
     */
    protected function delete()
    {
        $id = $this->post_data['id'] ?? 0;

        if (!$id) {
            json_error(['msg' => lang('参数错误')]);
        }

        // 验证打印机是否存在
        $where['id'] = $id;
        $this->commWhere($where);

        $ticket = $this->model->ticket->findOne($where);
        if (!$ticket) {
            json_error(['msg' => lang('打印机不存在')]);
        }

        $this->model->ticket->delete($where);

        json_success(['msg' => lang('删除成功')]);
    }

    /**
     * 获取打印机类型选项
     */
    protected function typeOptions()
    {
        $options = $this->model->ticket->getTypeOptions();
        json_success(['data' => $options]);
    }

    /**
     * 获取打印机状态选项
     */
    protected function statusOptions()
    {
        $options = $this->model->ticket->getStatusOptions();
        json_success(['data' => $options]);
    }

    protected function commWhere(&$where)
    {
        $where['sys_tag'] = $this->user_tag;
        $where['seller_id'] = $this->seller_id ?: 0;
        $where['store_id'] = $this->store_id ?: 0;
    }
}
