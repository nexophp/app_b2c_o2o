<?php

namespace modules\blog\controller;

use OpenApi\Attributes as OA;
use modules\blog\data\BlogData;

#[OA\Tag(name: '内容', description: '内容接口')]
class ApiController extends \core\ApiController
{
    protected $need_login = false;
    protected $model = [
        'blog' => '\modules\blog\model\BlogModel',
        'type' => '\modules\blog\model\BlogTypeModel',
    ];
    /**
     * 列表 
     */
    #[OA\Post(path: '/blog/api/index', summary: '列表', tags: ['内容'], parameters: [
        new OA\Parameter(name: 'type_id', description: '分类ID', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
    ])]
    public function actionIndex()
    {
        $type_id = $this->post_data['type_id'];
        $where = [];
        if ($type_id) {
            $in = $this->model->type->getTreeId($type_id);
            $where['last_type_id'] = $in;
        }
        $where['status'] = 'published';
        $where['ORDER'] = [
            'is_top' => 'DESC',
            'sort' => 'DESC',
            'id' => 'DESC'
        ];

        $all = $this->model->blog->pager($where);
        foreach ($all['data'] as &$row) {
            BlogData::parseData($row);
        }
        json($all);
    }
    /**
     * 详情
     */
    #[OA\Post(path: '/blog/api/view', summary: '详情', tags: ['内容'], parameters: [
        new OA\Parameter(name: 'id', description: 'ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'title', description: '标题', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
    ])]
    public function actionView()
    {
        $id = $this->post_data['id'];
        $title = $this->post_data['title'];
        if (!$id && !$title) {
            json_error(['msg' => lang('请求异常')]);
        }
        $where = [];
        if ($id) {
            $where['id'] = $id;
        }
        if ($title) {
            $where['title'] = $title;
        }
        $where['status'] = 'published'; 
        $row = $this->model->blog->findOne($where);
        if (!$row) {
            json_error(['msg' => lang('请求异常')]);
        }
        BlogData::parseData($row);
        json_success(['data' => $row]);
    }
}
