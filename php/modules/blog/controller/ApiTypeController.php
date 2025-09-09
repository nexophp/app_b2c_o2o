<?php

namespace modules\blog\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: '内容分类', description: '内容分类接口')]

class ApiTypeController extends \core\ApiController
{
    protected $need_login = false;
    protected $model = '\modules\blog\model\BlogTypeModel';
    /**
     * 列表 
     */
    #[OA\Post(path: '/blog/api-type/index', summary: '列表', tags: ['内容分类'], parameters: [
        new OA\Parameter(name: 'sys_tag', description: '系统分类', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
    ])]
    public function actionIndex()
    {
        $where['status'] = 1;
        $where['ORDER'] = [
            'sort' => 'DESC',
            'id' => 'ASC'
        ];
        $sys_tag = $this->post_data['sys_tag'] ?? '';
        if ($sys_tag) {
            $where['sys_tag'] = $sys_tag;
        }

        $all = $this->model->find($where);
        $list = [];
        foreach ($all as $v) {
            $list[] = [
                'id' => $v->id,
                'title' => $v->title,
            ];
        }
        json_success(['data' => $list]);
    }
}
