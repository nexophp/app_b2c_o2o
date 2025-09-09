<?php

/**
 * 点赞 
 */

namespace modules\like\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: '点赞', description: '点赞接口')]
class ApiController extends \core\ApiController
{
    protected $need_login = true;
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
        $type = $this->post_data['type'];
        $node_id = $this->post_data['node_id'] ?: 0;
        if (!$node_id || !$type) {
            json_error(['msg' => lang('参数错误')]);
        }
        $count = db_get_count("like", ['type' => $type, 'node_id' => $node_id]);
        json_success(['data' => $count]);
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
        $type = $this->post_data['type'];
        $node_id = $this->post_data['node_id'] ?: 0;
        if (!$node_id || !$type) {
            json_error(['msg' => lang('参数错误')]);
        }
        $find = db_get_one("like", "*", ['type' => $type, 'node_id' => $node_id, 'user_id' => $this->uid]);
        if ($find && $find['status'] == 1) {
            json_success(['msg' => lang('已点赞')]);
        } else {
            json_error(['msg' => lang('未点赞')]);
        }
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
        $type = $this->post_data['type'];
        $node_id = $this->post_data['node_id'] ?: 0;
        if (!$node_id || !$type) {
            json_error(['msg' => lang('参数错误')]);
        }
        $find = db_get_one("like", "*", ['type' => $type, 'node_id' => $node_id, 'user_id' => $this->uid]);
        if (!$find) {
            db_insert("like", [
                'user_id' => $this->uid,
                'type' => $type,
                'node_id' => $node_id,
                'ip' => get_ip(),
                'status' => 1,
                'create_at' => time(),
                'update_at' => time(),
            ]);
        } else {
            $status = $find['status'] ? 0 : 1;
            if ($status == 1) {
                $status = 0;
            } else {
                $status = 1;
            }
            db_update("like", [
                'status' => $status,
                'update_at' => time(),
            ], ['id' => $find['id']]);
        }
        json_success(['msg' => lang('操作成功')]);
    }
}
