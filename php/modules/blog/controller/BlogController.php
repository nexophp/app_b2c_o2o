<?php

namespace modules\blog\controller;

use modules\blog\model\BlogTypeModel;

class BlogController extends \core\AdminController
{
    protected $model = '\modules\blog\model\BlogModel';
    /**
     * 博客列表页面
     * @permission 博客.管理
     */
    public function actionIndex() {}

    /**
     * 博客分页列表
     * @permission 博客.管理 博客.查看
     */
    public function actionList()
    {
        $where = [];
        $title = $this->post_data['title'] ?? '';
        $type_id = $this->post_data['type_id'] ?? '';
        $status = $this->post_data['status'] ?? '';

        if ($title) {
            $where['title[~]'] = $title;
        }
        if ($type_id) {
            $type_in = BlogTypeModel::model()->getTreeId($type_id) ?: [];
            $type_in[] = $type_id;
            $where['last_type_id'] = $type_in;
        } 
        if ($status !== '') {
            $where['status'] = $status;
        }

        $where['ORDER'] = [
            'is_top' => 'DESC',
            'sort' => 'DESC',
            'id' => 'DESC'
        ];

        $list = $this->model->pager($where);

        // 获取分类信息并格式化数据
        foreach ($list['data'] as &$v) {
        }

        json($list);
    }

    /**
     * 添加博客
     * @permission 博客.管理 博客.添加
     */
    public function actionAdd()
    {
        $data = $this->post_data;

        // 验证必填字段
        if (empty($data['title'])) {
            json_error(['msg' => lang('标题不能为空')]);
        }
        if (empty($data['content'])) {
            json_error(['msg' => lang('内容不能为空')]);
        }

        // 处理图片数据
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = json_encode($data['images']);
        } else {
            $data['images'] = json_encode([]);
        }

        $data['user_id'] = $this->uid;
        $data['created_at'] = time();
        $data['updated_at'] = time();

        // 设置默认值
        $data['views'] = $data['views'] ?? 0;
        $data['likes'] = $data['likes'] ?? 0;
        $data['comments'] = $data['comments'] ?? 0;
        $data['status'] = $data['status'] ?? 'draft';
        $data['type_id'] = $data['type_id'] ?? 0;
        $data['desc'] = $data['desc'] ?? '';
        $data['sort'] = $data['sort'] ?? 0;
        $data['is_top'] = $data['is_top'] ?? 0;
        $data['is_recommend'] = $data['is_recommend'] ?? 0;
        $data['admin_id'] = $this->uid;
        $data['sys_tag'] = 'blog';

        $id = $this->model->insert($data);
        if ($id) {
            json_success(['msg' => lang('添加成功'), 'id' => $id]);
        } else {
            json_error(['msg' => lang('添加失败')]);
        }
    }

    /**
     * 编辑博客
     * @permission 博客.管理 博客.修改
     */
    public function actionEdit()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('博客ID不能为空')]);
        }

        $data = $this->post_data;

        // 验证必填字段
        if (empty($data['title'])) {
            json_error(['msg' => lang('标题不能为空')]);
        }
        if (empty($data['content'])) {
            json_error(['msg' => lang('内容不能为空')]);
        }

        // 处理图片数据
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = json_encode($data['images']);
        }

        $data['updated_at'] = time();
        unset($data['id']); // 移除ID字段

        $this->model->update($data, ['id' => $id]);
        json_success(['msg' => lang('修改成功')]);
    }

    /**
     * 删除博客
     * @permission 博客.管理 博客.删除
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('博客ID不能为空')]);
        }

        $result = db_delete('blog', ['id' => $id]);
        if ($result) {
            json_success(['msg' => lang('删除成功')]);
        } else {
            json_error(['msg' => lang('删除失败')]);
        }
    }

    /**
     * 获取博客详情
     * @permission 博客.管理 博客.查看
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('博客ID不能为空')]);
        }

        $data = $this->model->findOne($id);
        if ($data) {

            json_success(['data' => $data]);
        } else {
            json_error(['msg' => lang('博客不存在')]);
        }
    }
}
