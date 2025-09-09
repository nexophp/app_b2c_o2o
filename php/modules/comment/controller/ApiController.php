<?php

namespace modules\comment\controller;

/**
 * 评论接口
 */

use OpenApi\Attributes as OA;

#[OA\Tag(name: '评论', description: '评论接口')]
class ApiController extends \core\ApiController
{
    protected $model = [
        'comment' => 'modules\comment\model\CommentModel',
        'reply' => 'modules\comment\model\CommentReplyModel',
    ];
    /**
     * 评论列表
     */
    #[OA\Get(
        path: '/comment/api/index',
        summary: '获取评论列表',
        tags: ['评论'],
        parameters: [
            new OA\Parameter(name: 'type', description: '类型', in: 'query', schema: new OA\Schema(type: 'string', default: 'comment')),
            new OA\Parameter(name: 'nid', description: '关联ID', in: 'query', schema: new OA\Schema(type: 'integer')),

        ],
    )]
    public function actionIndex()
    {
        $type = $this->post_data['type'] ?? 'comment';
        $nid = $this->post_data['nid'] ?? 0;
        $where = [
            'type' => $type,
            'status' => 'complete',
            'ORDER' => ['id' => 'DESC'],
        ];
        if ($nid) {
            $where['nid'] = $nid;
        }  
        $res = $this->model->comment->pager($where);
        
        json($res);
    }
    /**
     * 评论回复列表
     */
    #[OA\Get(
        path: '/comment/api/reply',
        summary: '获取评论回复列表',
        tags: ['评论'],
        parameters: [
            new OA\Parameter(name: 'id', description: '评论ID', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
    )]
    public function actionReply()
    {
        $id = $this->post_data['id'] ?? 0;
        $where = [
            'comment_id' => $id,
            'status' => 'complete',
            'ORDER' => ['id' => 'DESC'],
        ];
        $res = $this->model->reply->pager($where);
        json($res);
    }
    /**
     * 发表评论
     */
    #[OA\Post(
        path: '/comment/api/do_comment',
        summary: '发表评论',
        tags: ['评论'],
        parameters: [
            new OA\Parameter(name: 'nid', description: '评论对象ID', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'type', description: '评论类型', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'content', description: '评论内容', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'images', description: '评论图片', in: 'query', schema: new OA\Schema(type: 'array')),
        ],
    )]
    public function actionDoComment()
    {
        $nid = $this->post_data['nid'] ?? 0;
        $type = $this->post_data['type'] ?? '';
        $content = $this->post_data['content'] ?? '';
        $images = $this->post_data['images'] ?? [];
        if (!$nid || !$type || !$content) {
            json_error(['msg' => lang('参数错误')]);
        }

        $data = [
            'nid' => $nid,
            'type' => $type,
            'content' => $content,
            'images' => $images,
            'created_at' => time(),
            'user_id' => $this->uid,
            'ip' => get_ip(),
        ];
        $this->model->comment->insert($data);
        json_success(['msg' => lang('评论成功')]);
    }
    /**
     * 评论回复
     */
    #[OA\Post(
        path: '/comment/api/do_reply',
        summary: '评论回复',
        tags: ['评论'],
        parameters: [
            new OA\Parameter(name: 'id', description: '评论ID', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'content', description: '回复内容', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'to_user_id', description: '回复用户ID', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'images', description: '回复图片', in: 'query', schema: new OA\Schema(type: 'array')),
        ],
    )]
    public function actionDoReply()
    {
        $id = $this->post_data['id'] ?? 0;
        $content = $this->post_data['content'] ?? '';
        $to_user_id = $this->post_data['to_user_id'] ?? 0;
        $images = $this->post_data['images'] ?? [];

        if (!$id || !$content) {
            json_error(['msg' => lang('参数错误')]);
        }
        $data = [
            'comment_id' => $id,
            'content' => $content,
            'to_user_id' => $to_user_id,
            'images' => $images,
            'created_at' => time(),
            'user_id' => $this->uid,
            'ip' => get_ip(),
        ];
        $this->model->reply->insert($data);
        json_success(['msg' => lang('回复成功')]);
    }
}
