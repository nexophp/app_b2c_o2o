<?php

namespace modules\blog\controller;

use modules\blog\model\BlogTypeModel;

class TypeController extends \core\AdminController
{
    protected $model = "\modules\blog\model\BlogTypeModel";
    /**
     * 分类列表页面
     * @permission 博客.管理 博客.查看
     */
    public function actionIndex() {}

    /**
     * 获取分类树形结构
     * @permission 博客.管理 博客.查看
     */
    public function actionTree()
    {
        $where = ['ORDER' => ['sort' => 'DESC', 'id' => 'ASC']];
        $id = $this->post_data['id'] ?? 0;
        if ($id) {
            $where['id[!]'] = $id;
        }
        $list = db_get('blog_type', ['id', 'title'], $where);
        if (!$list) {
            json_success(['data' => []]);
        }

        if (g('has_all')) {
            // 向列表最上面添加一个全部
            array_unshift($list, [
                'id' => 0,
                'title' => lang('全部'),
            ]);
        }
        json_success(['data' => $list]);
    }

    /**
     * 获取分类列表
     * @permission 博客.管理 博客.查看
     */
    public function actionList()
    {
        $where = [];
        $title = $this->post_data['title'] ?? '';

        if ($title) {
            $where['title[~]'] = $title;
        }

        $where['ORDER'] = ['sort' => 'DESC', 'id' => 'ASC'];
        $list = db_pager('blog_type', '*', $where);

        json($list);
    }

    /**
     * 添加分类
     * @permission 博客.管理
     */
    public function actionAdd()
    {
        $title = $this->post_data['title'] ?? '';

        if (empty($title)) {
            json_error(['msg' => lang('分类名称不能为空')]);
        }

        $data = [
            'title' => $title,
            'status' => $this->post_data['status'] ?? '1',
            'sort' => $this->post_data['sort'] ?? 0,
            'created_at' => time(),
            'updated_at' => time()
        ];

        $id = $this->model->insert($data);
        json_success(['msg' => lang('添加成功'), 'id' => $id]);
    }

    /**
     * 编辑分类
     * @permission 博客.管理
     */
    public function actionEdit()
    {
        $id = $this->post_data['id'] ?? 0;
        $title = $this->post_data['title'] ?? '';

        if (!$id) {
            json_error(['msg' => lang('分类ID不能为空')]);
        }

        if (empty($title)) {
            json_error(['msg' => lang('分类名称不能为空')]);
        }

        $data = [
            'title' => $title,
            'status' => $this->post_data['status'] ?? '1',
            'sort' => $this->post_data['sort'] ?? 0,
            'updated_at' => time()
        ];

        $result = $this->model->update($data, ['id' => $id], true);
        if ($result) {
            json_success(['msg' => lang('更新成功')]);
        } else {
            json_error(['msg' => lang('更新失败')]);
        }
    }

    /**
     * 删除分类
     * @permission 博客.管理
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('分类ID不能为空')]);
        }

        // 检查是否有博客使用该分类
        $type_in = BlogTypeModel::model()->getTreeId($id) ?: [];
        $type_in[] = $id;

        $hasBlogs = db_get_one('blog', 'id', ['last_type_id' => $type_in]);
        if ($hasBlogs) {
            json_error(['msg' => lang('该分类下有博客，无法删除')]);
        }

        $result = $this->model->delete(['id' => $id]);
        json_success(['msg' => lang('删除成功')]);
    }

    /**
     * 获取分类详情
     * @permission 博客.管理
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('分类ID不能为空')]);
        }

        $data = $this->model->findOne($id);
        if ($data) {
            json_success(['data' => $data]);
        } else {
            json_error(['msg' => lang('分类不存在')]);
        }
    }
}
