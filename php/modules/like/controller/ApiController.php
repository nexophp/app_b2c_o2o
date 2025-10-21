<?php

/**
 * 点赞 
 */

namespace modules\like\controller;

use OpenApi\Attributes as OA;
use modules\like\trait\Like;

#[OA\Tag(name: '点赞', description: '点赞接口')]
class ApiController extends \core\ApiController
{
    use Like;
    protected $need_login = true;
    /**
     * 分页
     */
    #[OA\Get(
        path: '/like/api/list',
        summary: '点赞数量',
        tags: ['点赞'],
        parameters: [
            new OA\Parameter(name: 'type', description: '类型', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ]
    )]
    public function actionList()
    {
        $this->list();
    }
    /**
     * 点赞数量
     */
    #[OA\Get(
        path: '/like/api/count',
        summary: '点赞数量',
        tags: ['点赞'],
        parameters: [
            new OA\Parameter(name: 'type', description: '类型', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'node_id', description: '节点ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ]
    )]
    public function actionCount()
    {
        $this->count();
    }
    /**
     * 是否点赞
     */
    #[OA\Get(
        path: '/like/api/check',
        summary: '当前用户是否点赞',
        tags: ['点赞'],
        parameters: [
            new OA\Parameter(name: 'type', description: '类型', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'node_id', description: '节点ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ]
    )]
    public function actionCheck()
    {
        $this->check();
    }
    /**
     * 点赞
     */
    #[OA\Get(
        path: '/like/api/add',
        summary: '当前用户点赞',
        tags: ['点赞'],
        parameters: [
            new OA\Parameter(name: 'type', description: '类型', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'node_id', description: '节点ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ]
    )]
    public function actionAdd()
    {
        $this->add();
    }
}
