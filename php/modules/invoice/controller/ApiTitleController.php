<?php

namespace modules\invoice\controller;

use OpenApi\Attributes as OA;
use modules\invoice\data\InvoiceTitleData;

#[OA\Tag(name: '发票抬头', description: '发票抬头管理接口')]
class ApiTitleController extends \core\ApiController
{
    protected $need_login = true;
    protected $model = '\modules\invoice\model\InvoiceTitleModel';

    /**
     * 获取发票抬头列表
     */
    #[OA\Post(path: '/invoice/api-title/index', summary: '获取发票抬头列表', tags: ['发票抬头'])]
    public function actionIndex()
    {
        $where = [
            'user_id' => $this->uid, 
            'ORDER' => ['is_default' => 'DESC', 'id' => 'DESC']
        ];        
        $list = $this->model->pager($where);
        json($list);
    }

    /**
     * 创建发票抬头
     */
    #[OA\Post(path: '/invoice/api-title/create', summary: '创建发票抬头', tags: ['发票抬头'], parameters: [
        new OA\Parameter(name: 'type', description: '发票类型', in: 'query', required: true, schema: new OA\Schema(type: 'string', enum: ['personal', 'company'])),
        new OA\Parameter(name: 'title', description: '发票抬头', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'tax_number', description: '纳税人识别号', in: 'query', required: false, schema: new OA\Schema(type: 'string'))
    ])]
    public function actionCreate()
    {
        $data = $this->post_data;
        $data['user_id'] = $this->uid;
        
        // 数据验证
        $errors = InvoiceTitleData::validate($data);
        if ($errors) {
            json_error(['msg' => implode(', ', $errors)]);
        }
        
        // 设置默认值
        $data['status'] = 1;
        $data['is_default'] = $data['is_default'] ?? 0; 
        
        $id = $this->model->insert($data);
        json_success(['msg' => '创建成功', 'id' => $id]);
    }

    /**
     * 更新发票抬头
     */
    #[OA\Post(path: '/invoice/api-title/update', summary: '更新发票抬头', tags: ['发票抬头'], parameters: [
        new OA\Parameter(name: 'id', description: '发票抬头ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer'))
    ])]
    public function actionUpdate()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => '发票抬头ID不能为空']);
        }
        
        // 验证发票抬头是否属于当前用户
        if (!InvoiceTitleData::validateUserTitle($id, $this->uid)) {
            json_error(['msg' => '发票抬头不存在']);
        }
        
        $data = $this->post_data;
        unset($data['id'], $data['user_id']);
        
        // 数据验证
        if (isset($data['type']) || isset($data['title']) || isset($data['tax_number'])) {
            $errors = InvoiceTitleData::validate(array_merge(['user_id' => $this->uid], $data));
            if ($errors) {
                json_error(['msg' => implode(', ', $errors)]);
            }
        }
        
        $this->model->update($data, ['id' => $id],true);
        json_success(['msg' => '更新成功']);
    }

    /**
     * 删除发票抬头
     */
    #[OA\Post(path: '/invoice/api-title/delete', summary: '删除发票抬头', tags: ['发票抬头'], parameters: [
        new OA\Parameter(name: 'id', description: '发票抬头ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer'))
    ])]
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => '发票抬头ID不能为空']);
        }
        
        // 验证发票抬头是否属于当前用户
        if (!InvoiceTitleData::validateUserTitle($id, $this->uid)) {
            json_error(['msg' => '发票抬头不存在']);
        }
        
        // 软删除
        $this->model->delete(['deleted_at' => time()], ['id' => $id]);
        json_success(['msg' => '删除成功']);
    }

    /**
     * 获取发票抬头详情
     */
    #[OA\Post(path: '/invoice/api-title/detail', summary: '获取发票抬头详情', tags: ['发票抬头'], parameters: [
        new OA\Parameter(name: 'id', description: '发票抬头ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer'))
    ])]
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => '发票抬头ID不能为空']);
        }
        
        $title = $this->model->findOne(['id' => $id, 'user_id' => $this->uid]);
        if (!$title) {
            json_error(['msg' => '发票抬头不存在']);
        }
        
        json_success(['data' => $title]);
    }

    /**
     * 获取默认发票抬头
     */
    #[OA\Post(path: '/invoice/api-title/default', summary: '获取默认发票抬头', tags: ['发票抬头'])]
    public function actionDefault()
    {
        $title = InvoiceTitleData::getUserDefaultTitle($this->uid);
        if (!$title) {
            json_error(['msg' => '未找到默认发票抬头']);
        }
        
        json_success(['data' => $title]);
    }
    
    /**
     * 设置默认发票抬头
     */
    #[OA\Post(path: '/invoice/api-title/set-default', summary: '设置默认发票抬头', tags: ['发票抬头'], parameters: [
        new OA\Parameter(name: 'id', description: '发票抬头ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer'))
    ])]
    public function actionSetDefault()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => '发票抬头ID不能为空']);
        }
        
        $result = InvoiceTitleData::setAsDefault($id, $this->uid);
        if (!$result) {
            json_error(['msg' => '设置失败']);
        }
        
        json_success(['msg' => '设置成功']);
    }

    /**
     * 获取发票类型列表
     */
    #[OA\Post(path: '/invoice/api-title/types', summary: '获取发票类型列表', tags: ['发票抬头'])]
    public function actionTypes()
    {
        $typeMap = InvoiceTitleData::getTypeMap();
        $types = [];
        foreach ($typeMap as $value => $label) {
            $types[] = ['value' => $value, 'label' => $label];
        }
        
        json_success(['data' => $types]);
    }
}