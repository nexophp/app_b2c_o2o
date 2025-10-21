<?php

namespace modules\like\trait;

trait Like
{
    /**
     * 我的点赞
     */
    protected function list($ret_json = true)
    {
        $type = $this->post_data['type'];
        if (!$type) {
            json_error(['msg' => '参数错误']);
        }
        $list = get_like_pager($this->uid, $type);
        if ($list['data']) {
            foreach ($list['data'] as &$val) {
                do_action("like.row", $val);
            }
        }
        if ($ret_json) {
            json($list);
        } else {
            return $list;
        }
    }
    /**
     * 点赞数量
     */
    protected function count()
    {
        $type = $this->post_data['type'];
        $node_id = $this->post_data['node_id'] ?: 0;
        if (!$node_id || !$type) {
            json_error(['msg' => lang('参数错误')]);
        }
        $count = db_get_count("like", ['type' => $type, 'node_id' => $node_id, 'status' => 1]);
        json_success(['data' => $count]);
    }
    /**
     * 是否点赞
     */
    protected function check()
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
    protected function add()
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
            $status = $find['status'];
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
        $count = db_get_count("like", ['type' => $type, 'node_id' => $node_id, 'status' => 1]);
        json_success(['msg' => lang('操作成功'), 'data' => $count]);
    }
}
