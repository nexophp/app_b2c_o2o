<?php

namespace modules\comment\model;

use modules\admin\model\UserModel;

class CommentReplyModel extends \core\AppModel
{
    protected $table = 'comment_reply';

    protected $has_one = [
        'user' => [UserModel::class, 'user_id', 'id'],
        'to_user' => [UserModel::class, 'to_user_id', 'id'],
    ];

    /**
     * 获取状态选项
     */
    public function getStatusOptions()
    {
        return [
            ['label' => lang('待审核'), 'value' => 'wait'],
            ['label' => lang('已通过'), 'value' => 'complete'],
            ['label' => lang('已拒绝'), 'value' => 'error']
        ];
    }

    public function beforeSave(&$data)
    {
        if ($data['content']) {
            green_text($data['content']);
        }
        if ($data['images'] && is_array($data['images'])) {
            green_text($data['images']);
        }
        $data['status'] = 'complete';
    }

    public function afterFind(&$data)
    {
        $statusMap = [
            'wait' => lang('待审核'),
            'complete' => lang('已通过'),
            'error' => lang('已拒绝')
        ];
        $data['status_text'] = $statusMap[$data['status']] ?? $data['status'];
        $data['created_at_text'] = date('Y-m-d H:i:s', $data['created_at']);
        $data['updated_at_text'] = date('Y-m-d H:i:s', $data['updated_at']);
        $user = get_user($data['user_id']);
        if ($user) {
            unset($user['password']);
        }
        $avatar = $user['avatar'] ?: cdn() . '/misc/img/avatar.png';
        $data['user_avatar'] = $avatar;
        $data['user_name'] = $user['nickname'] ?: '匿名用户';

        $data['timeago'] = timeago($data['created_at']);

        $type = g("comment_type") ?: 'default';
        if (function_exists('is_like')) {
            $data['is_like'] = is_like($data['id'], $type, $uid);
            $data['like'] = get_like_count($data['id'], $type);
        }
    }
}
