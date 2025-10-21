<?php

namespace modules\logistic\controller;

use modules\logistic\data\LogisticData;
use modules\logistic\model\LogisticModel;

class AdminController extends \core\AdminController
{
    protected $model = [
        'logistic' => '\modules\logistic\model\LogisticModel',
    ];

    /**
     * 物流公司列表页面
     * @permission 物流.管理
     */
    public function actionIndex() 
    {
        // 检查是否有数据，如果没有则初始化默认数据
        $this->initDefaultData();
    }

    /**
     * 物流公司分页列表
     * @permission 物流.管理 物流.查看
     */
    public function actionList()
    {
        $where = [];
        $name = $this->post_data['name'] ?? '';
        $code = $this->post_data['code'] ?? '';
        $status = $this->post_data['status'] ?? '';

        if ($name) {
            $where['name[~]'] = $name;
        }
        if ($code) {
            $where['code[~]'] = $code;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        $where['ORDER'] = ['id' => 'ASC'];

        $list = $this->model->logistic->pager($where);

        foreach ($list['data'] as &$v) {
            $v = LogisticData::resetData($v);
        }
        json($list);
    }

    /**
     * 添加物流公司
     * @permission 物流.管理 物流.添加
     */
    public function actionAdd()
    {
        $data = $this->post_data;
        $data['created_at'] = time();
        $data['updated_at'] = time();
        
        // 检查代码是否已存在
        $exists = $this->model->logistic->find(['code' => $data['code']]);
        if ($exists) {
            json_error(['msg' => lang('物流公司代码已存在')]);
        }
        
        db_action(function () use ($data) {
            $this->model->logistic->insert($data);
        });
        json_success(['msg' => lang('添加成功')]);
    }

    /**
     * 编辑物流公司
     * @permission 物流.管理 物流.修改
     */
    public function actionEdit()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('物流公司ID不能为空')]);
        }
        
        $data = $this->post_data;
        $data['updated_at'] = time();
        
        // 检查代码是否已存在（排除当前记录）
        $exists = $this->model->logistic->find(['code' => $data['code'], 'id[!]' => $id]);
        if ($exists) {
            json_error(['msg' => lang('物流公司代码已存在')]);
        }
        
        db_action(function () use ($data, $id) {
            $this->model->logistic->update($data, ['id' => $id],true);
        });

        json_success(['msg' => lang('修改成功')]);
    }

    /**
     * 删除物流公司
     * @permission 物流.管理 物流.删除
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('物流公司ID不能为空')]);
        }

        $this->model->logistic->delete(['id' => $id]); 
        json_success(['msg' => lang('删除成功')]);
    }

    /**
     * 获取物流公司详情
     * @permission 物流.管理 物流.查看
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('物流公司ID不能为空')]);
        }
        $data = $this->model->logistic->find($id);
        if ($data) {
            $data = LogisticData::resetData($data);
            $data['status'] = (string)$data['status'];
            json_success(['data' => $data]);
        } else {
            json_error(['msg' => lang('物流公司不存在')]);
        }
    }

    /**
     * 更新状态
     * @permission 物流.管理 物流.修改
     */
    public function actionUpdateStatus()
    {
        $id = $this->post_data['id'] ?? 0; 
        if (!$id) {
            json_error(['msg' => lang('物流公司ID不能为空')]);
        }
        $res = $this->model->logistic->find($id);
        if (!$res) {
            json_error(['msg' => lang('物流公司不存在')]);
        }
        $status= $res->status;
        if($status == 1){
            $status = 0;
        }else{
            $status = 1;
        }
        $data = [
            'status' => $status,
            'updated_at' => time()
        ];
         
        $this->model->logistic->update($data, ['id' => $id],true); 
        
        json_success(['msg' => lang('状态更新成功')]);
    }

    /**
     * 获取所有启用的物流公司
     * @permission 物流.查看
     */
    public function actionGetAll()
    {
        $list = $this->model->logistic->find(['status' => 1, 'ORDER' => ['name' => 'ASC']]);
        $data = [];
        foreach ($list as $item) {
            $data[] = [
                'label' => $item['name'],
                'value' => $item['code']
            ];
        }
        json_success(['data' => $data]);
    }

    /**
     * 初始化默认数据
     */
    private function initDefaultData()
    {
        // 检查是否已有数据
        $count = $this->model->logistic->count();
        if ($count > 0) {
            return;
        }

        // 默认物流公司数据
        $defaultData = [
            ['name' => '中通快递', 'code' => 'zto', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '圆通速递', 'code' => 'yto', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '申通快递', 'code' => 'sto', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '韵达快递', 'code' => 'yunda', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '顺丰速运', 'code' => 'sfexpress', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '百世快递', 'code' => 'htky', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '德邦快递', 'code' => 'deppon', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '京东快递', 'code' => 'jd', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '极兔速递', 'code' => 'jitu', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '邮政速递物流', 'code' => 'ems', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
            ['name' => '邮政包裹', 'code' => 'chinapost', 'status' => 1, 'created_at' => time(), 'updated_at' => time()],

        ];

        // 批量插入数据
        foreach ($defaultData as $data) {
            db_insert('logistic', $data);
        }
    }
}