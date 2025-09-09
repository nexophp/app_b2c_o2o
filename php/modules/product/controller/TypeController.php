<?php

namespace modules\product\controller;

use modules\product\data\TypeData;

class TypeController extends \core\AdminController
{
    /**
     * 分类列表页面
     * @permission 商品.管理 商品.查看
     */
    public function actionIndex() {}
    /**
     * cascader
     * @permission 商品.管理 商品.查看
     */
    public function actionTree()
    {
        $where = ['ORDER' => ['sort' => 'ASC', 'id' => 'ASC']];
        $id = $this->post_data['id'] ?? 0;
        if ($id) {
            $where['id[!]'] = $id;
        }
        $list = db_get('product_type', ['id', 'title', 'pid'], $where);
        if (!$list) {
            json_success(['data' => '']);
        }
        $tree = array_to_tree($list, 'id', 'pid', 'children');
        $tree = array_values($tree);
        if(g('has_all')){
            //向tree的最上面添加一个全部
            array_unshift($tree, [
                'id' => 0,
                'title' => lang('全部'), 
            ]);
        }
        json_success(['data' => $tree]);
    }
    /**
     * 获取分类列表
     * @permission 商品.管理 商品.查看
     */
    public function actionList()
    {
        $where = [];
        $title = $this->post_data['name'] ?? '';

        if ($title) {
            $where['title[~]'] = $title;
        }

        $where['ORDER'] = ['sort' => 'DESC', 'id' => 'ASC'];
        $pid = $this->post_data['pid'] ?? 0;
        $where['pid'] = $pid;
        $list = db_pager('product_type', '*', $where);
        foreach ($list['data'] as $key => &$item) {
            TypeData::get($item);
        }
        json($list);
    }

    /**
     * 添加分类
     * @permission 商品.管理
     */
    public function actionAdd()
    {
        $title = $this->post_data['title'] ?? '';
        $pid = $this->post_data['pid'] ?? 0;
        $image = $this->post_data['image'] ?? '';
        $description = $this->post_data['description'] ?? '';
        $sort = $this->post_data['sort'] ?? 0;
        $status = $this->post_data['status'] ?? 1;

        if (empty($title)) {
            json_error(['msg' => lang('分类名称不能为空')]);
        }

        $data = [
            'title' => $title,
            'pid' => $pid,
            'image' => $image,
            'description' => $description,
            'sort' => $sort,
            'status' => $status,
            'created_at' => time(),
            'updated_at' => time()
        ];

        $id = db_insert('product_type', $data);
        if ($id) {
            json_success(['msg' => lang('添加成功'), 'id' => $id]);
        } else {
            json_error(['msg' => lang('添加失败')]);
        }
    }

    /**
     * 编辑分类
     * @permission 商品.管理
     */
    public function actionEdit()
    {
        $id = $this->post_data['id'] ?? 0;
        $title = $this->post_data['title'] ?? '';
        $pid = $this->post_data['pid'] ?? 0;
        $image = $this->post_data['image'] ?? '';
        $description = $this->post_data['description'] ?? '';
        $sort = $this->post_data['sort'] ?? 0;
        $status = $this->post_data['status'] ?? 1;

        if (!$id) {
            json_error(['msg' => lang('分类ID不能为空')]);
        }

        if (empty($title)) {
            json_error(['msg' => lang('分类名称不能为空')]);
        }

        $data = [
            'title' => $title,
            'pid' => $pid,
            'image' => $image,
            'description' => $description,
            'sort' => $sort,
            'status' => $status,
            'updated_at' => time()
        ];

        $result = db_update('product_type', $data, ['id' => $id]);
        if ($result) {
            json_success(['msg' => lang('更新成功')]);
        } else {
            json_error(['msg' => lang('更新失败')]);
        }
    }

    /**
     * 删除分类
     * @permission 商品.管理
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('分类ID不能为空')]);
        }

        // 检查是否有子分类
        $hasChildren = db_get_one('product_type', 'id', ['pid' => $id]);
        if ($hasChildren) {
            json_error(['msg' => lang('该分类下有子分类，无法删除')]);
        }

        // 检查是否有商品使用该分类
        $hasProducts = db_get_one('product', 'id', ['type_id' => $id]);
        if ($hasProducts) {
            json_error(['msg' => lang('该分类下有商品，无法删除')]);
        }

        $result = db_delete('product_type', ['id' => $id]);
        if ($result) {
            json_success(['msg' => lang('删除成功')]);
        } else {
            json_error(['msg' => lang('删除失败')]);
        }
    }

    /**
     * 获取分类详情
     * @permission 商品.管理
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('分类ID不能为空')]);
        }

        $data = db_get_one('product_type', '*', ['id' => $id]);
        if ($data) {
            json_success(['data' => $data]);
        } else {
            json_error(['msg' => lang('分类不存在')]);
        }
    }

    /**
     * 更新排序
     * @permission 商品.管理
     */
    public function actionUpdateSort()
    {
        $data = $this->post_data['data'] ?? [];
        if (empty($data)) {
            json_error(['msg' => lang('排序数据不能为空')]);
        }
        $count = count($data);
        foreach ($data as $id) {
            db_update(
                'product_type',
                ['sort' => $count--],
                ['id' => $id]
            );
        }
        json_success(['msg' => lang('排序更新成功')]);
    }
}
