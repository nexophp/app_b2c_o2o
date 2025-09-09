<?php

namespace modules\comment\controller;


class AdminController extends \core\AdminController
{
    protected $model = [
        'comment' => 'modules\comment\model\CommentModel',
        'reply' => 'modules\comment\model\CommentReplyModel',
    ];

    /**
     * 评论管理首页
     * @permission 评论.管理 评论.查看
     */
    public function actionIndex() {}

    /**
     * 获取评论列表
     * @permission 评论.管理 评论.查看
     */
    public function actionList()
    {
        $params = $this->post_data ?: [];
        $where = [];
        if ($params['status']) {
            $where['status'] = $params['status'];
        }
        if($params['content']){
            $where['content[~]'] = $params['content'];
        }
        $where['ORDER'] = ['id'=>'DESC'];
        $result = $this->model->comment->pager($where);

        json($result);
    }
    /**
     * 添加回复
     * @permission 评论.管理 评论.回复
     */
    public function actionAddReply()
    {
        $params = $this->post_data ?: [];
        $comment_id = $params['comment_id'] ?? 0;
        if (!$comment_id) {
            json_error(['msg' => lang('参数错误')]);
        }
        if(!$params['content']){
            json_error(['msg' => lang('请输入回复内容')]);
        }
        $insert = [
            'comment_id' => $comment_id,
            'content' => $params['content'] ?: '',
            'images' => $params['images'] ?:[],
            'status' => 'wait',
            'created_at' => time(),
            'updated_at' => time(),
            'user_id' => $this->uid,
        ];
        $result = $this->model->reply->insert($insert);
        json_success(['msg' => lang('添加成功')]);
    }
    /**
     * 获取回复列表
     * @permission 评论.管理 评论.回复
     */
    public function actionReplyList()
    {
        $params = $this->post_data ?: [];
        $where = [];
        if ($params['status']) {
            $where['status'] = $params['status'];
        }
        if($params['content']){
            $where['content[~]'] = $params['content'];
        }
        $where['ORDER'] = ['id'=>'DESC'];
        $result = $this->model->reply->pager($where);

        json($result);
    }

    /**
     * 获取评论详情
     * @permission 评论.管理 评论.查看
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('参数错误')]);
        }

        $comment = $this->model->comment->findOne($id);
        if (!$comment) {
            json_error(['msg' => lang('评论不存在')]);
        }

        // 获取该评论的回复列表
        $params = [
            'comment_id' => $id,
            'ORDER'=>['id'=>'DESC'],
        ];
        $replies = $this->model->reply->find($params);
        $comment['replies'] = $replies;

        json_success(['data' => $comment]);
    }

    /**
     * 获取回复详情
     * @permission 评论.管理 评论.回复
     */
    public function actionReplyDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('参数错误')]);
        }

        $reply = $this->model->reply->findOne($id);
        if (!$reply) {
            json_error(['msg' => lang('回复不存在')]);
        }

        json_success(['data' => $reply]);
    }

    /**
     * 更新评论状态
     * @permission 评论.管理 评论.查看
     */
    public function actionUpdateStatus()
    {
        $id = $this->post_data['id'] ?? 0;
        $status = $this->post_data['status'] ?? 0;

        if (!$id || !$status) {
            json_error(['msg' => lang('参数错误')]);
        }

        $comment = $this->model->comment->findOne($id);
        if (!$comment) {
            json_error(['msg' => lang('评论不存在')]);
        }

        $result = $this->model->comment->update(['status' => $status], ['id' => $id], true);
        json_success(['msg' => lang('状态更新成功')]);
    }

    /**
     * 更新回复状态
     * @permission 评论.管理 评论.回复
     */
    public function actionUpdateReplyStatus()
    {
        $id = $this->post_data['id'] ?? 0;
        $status = $this->post_data['status'] ?? 0;

        if (!$id || !$status) {
            json_error(['msg' => lang('参数错误')]);
        }

        $reply = $this->model->reply->findOne($id);
        if (!$reply) {
            json_error(['msg' => lang('回复不存在')]);
        }

        $this->model->reply->update(['status' => $status], ['id' => $id], true);
        json_success(['msg' => lang('状态更新成功')]);
    }

    /**
     * 删除评论
     * @permission 评论.管理 评论.刪除
     */
    public function actionDelete()
    {
        $id = $this->request->post('id');
        if (!$id) {
            json_error(['msg' => lang('参数错误')]);
        }

        $comment = $this->model->comment->findOne($id);
        if (!$comment) {
            json_error(['msg' => lang('评论不存在')]);
        }

        $this->model->comment->delete(['id' => $id]);
        json_success(['msg' => lang('删除成功')]);
    }

    /**
     * 删除回复
     * @permission 评论.管理 评论.删除回复
     */
    public function actionDeleteReply()
    {
        $id = $this->request->post('id');
        if (!$id) {
            json_error(['msg' => lang('参数错误')]);
        }

        $reply = $this->model->reply->findOne($id);
        if (!$reply) {
            json_error(['msg' => lang('回复不存在')]);
        }

        $this->model->reply->delete(['id' => $id]);
        json_success(['msg' => lang('删除成功')]);
    }
}
