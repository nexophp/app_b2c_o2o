<?php

/**
 * 点赞 
 */

namespace modules\like\controller;

use OpenApi\Attributes as OA;
use modules\like\trait\Like;

#[OA\Tag(name: 'PC端点赞', description: '点赞接口')]
class UserController extends \core\AppController
{
    use Like;
    protected $need_login = true;
    protected function init()
    {
        parent::init();
        if (!$this->uid) {
            json_error('请先登录');
        }
    }
    /**
     * 分页
     */
    #[OA\Get(
        path: '/like/user/list',
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
        path: '/like/user/count',
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
        path: '/like/user/check',
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
        path: '/like/user/add',
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
