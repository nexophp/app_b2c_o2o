<?php

namespace modules\mall\controller;

class UniappController extends \core\AdminController
{
    protected $model = '\modules\mall\model\UniappPageModel';

    /**
     * 页面管理首页
     * @permission 小程序页面.管理 小程序页面.查看
     */
    public function actionIndex()
    {
        // 页面管理列表页面
    }

    /**
     * 获取页面列表
     * @permission 小程序页面.管理 小程序页面.查看
     */
    public function actionList()
    {
        $search = $this->post_data;

        $where = [];

        // 搜索条件
        if (!empty($search['name'])) {
            $where['name[~]'] = $search['name'];
        }

        if ($search['status']) {
            $where['status'] = $search['status'];
        }

        $where['ORDER'] = ['sort' => 'ASC', 'id' => 'DESC'];

        // 获取列表
        $list = $this->model->pager($where);
        $list['status_options'] = $this->model->getStatusOptions();

        json($list);
    }

    /**
     * 添加页面
     * @permission 小程序页面.管理
     */
    public function actionAdd()
    {
        $data = $this->post_data;

        // 验证必填字段
        if (empty($data['name'])) {
            json_error(['msg' => lang('页面名称不能为空')]);
        }
        if (empty($data['url'])) {
            json_error(['msg' => lang('页面URL不能为空')]);
        }

        // 检查URL是否重复
        $exists = $this->model->findOne(['url' => $data['url']]);
        if ($exists) {
            json_error(['msg' => lang('页面URL已存在')]);
        }

        $saveData = [
            'name' => $data['name'],
            'url' => $data['url'],
            'share_title' => $data['share_title'] ?? '',
            'share_image' => $data['share_image'] ?? '',
            'status' => $data['status'] ?? 1,
            'sort' => $data['sort'] ?? 0,
            'is_home' => $data['is_home'] ?: -1,

        ];
        if ($saveData['is_home'] == 1) {
            db_update("uniapp_page", ['is_home' => -1], ['id[>=]' => 1]);
        }
        $id = $this->model->insert($saveData);
        if ($id) {
            json_success(['id' => $id, 'msg' => lang('添加成功')]);
        } else {
            json_error(['msg' => lang('添加失败')]);
        }
    }

    /**
     * 编辑页面
     * @permission 小程序页面.管理
     */
    public function actionEdit()
    {
        $id = $this->post_data['id'] ?? 0;
        $data = $this->post_data;

        if (!$id) {
            json_error(['msg' => lang('页面ID不能为空')]);
        }

        // 验证必填字段
        if (empty($data['name'])) {
            json_error(['msg' => lang('页面名称不能为空')]);
        }
        if (empty($data['url'])) {
            json_error(['msg' => lang('页面URL不能为空')]);
        }

        // 检查URL是否重复（排除自己）
        $exists = $this->model->findOne([
            'url' => $data['url'],
            'id[!]' => $id
        ]);
        if ($exists) {
            json_error(['msg' => lang('页面URL已存在')]);
        }

        $saveData = [
            'name' => $data['name'],
            'url' => $data['url'],
            'share_title' => $data['share_title'] ?? '',
            'share_image' => $data['share_image'] ?? '',
            'status' => $data['status'] ?? 1,
            'sort' => $data['sort'] ?? 0,
            'is_home' => $data['is_home'] ?: -1,

        ];
        if ($saveData['is_home'] == 1) {
            db_update("uniapp_page", ['is_home' => -1], ['id[>=]' => 1]);
        }

        $result = $this->model->update($saveData, ['id' => $id]);
        if ($result) {
            json_success(['msg' => lang('更新成功')]);
        } else {
            json_error(['msg' => lang('更新失败')]);
        }
    }

    /**
     * 删除页面
     * @permission 小程序页面.管理
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;

        if (!$id) {
            json_error(['msg' => lang('页面ID不能为空')]);
        }

        $result = $this->model->del(['id' => $id]);
        if ($result) {
            json_success(['msg' => lang('删除成功')]);
        } else {
            json_error(['msg' => lang('删除失败')]);
        }
    }

    /**
     * 获取页面详情
     * @permission 小程序页面.管理 小程序页面.查看
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;

        if (!$id) {
            json_error(['msg' => lang('页面ID不能为空')]);
        }

        $page = $this->model->findOne(['id' => $id]);
        if (!$page) {
            json_error(['msg' => lang('页面不存在')]);
        }

        json_success(['data' => $page]);
    }

    /**
     * 设计页面
     * @permission 小程序页面.管理
     */
    public function actionDesign()
    {
        $id = (int)$_GET['id'];

        if (!$id) {
            json_error(['msg' => lang('页面ID不能为空')]);
        }

        $page = $this->model->findOne(['id' => $id]);
        if (!$page) {
            json_error('页面不存在');
        }

        // 解析页面数据 
        $page_data = $page['page_data'];
        $this->view_data['page_id'] = $id;
        $this->view_data['page'] = $page;
        $this->view_data['page_data'] = $page_data;
    }

    /**
     * 保存页面设计数据
     * @permission 小程序页面.管理
     */
    public function actionSaveDesign()
    {
        $data = $this->post_data;

        $id = $data['id'] ?? 0;
        $page_data = $data['pageElements'] ?? [];

        if (!$id) {
            json_error('页面ID不能为空');
        }

        $page = $this->model->findOne(['id' => $id]);
        if (!$page) {
            json_error(['msg' => lang('页面不存在')]);
        }

        // 保存页面设计数据，需要JSON编码
        db_update("uniapp_page", ['page_data' => json_encode($page_data)], ['id' => $id]);

        json_success(['msg' => lang('保存成功')]);
    }

    /**
     * 获取页面设计数据
     * @permission 小程序页面.管理 小程序页面.查看
     */
    public function actionGetDesign()
    {
        $id = $this->post_data['id'] ?? 0;

        if (!$id) {
            json_error('页面ID不能为空');
        }

        $page = $this->model->findOne(['id' => $id]);
        if (!$page) {
            json_error('页面不存在');
        }

        // 解析页面数据 
        $page_data = $page['page_data'] ?: [];

        json_success(['data' => $page_data]);
    }


    /**
     * 获取Menu图标选择JS
     * @permission 小程序页面.管理
     */
    public function actionJs()
    {
        $elementIndex = $this->post_data['elementIndex'] ?? 0;
        $itemIndex = $this->post_data['itemIndex'] ?? 0;

        $js = "
            parent.layer.closeAll(); 
            if(data && data.url) {
                parentVue.pageElements[{$elementIndex}].config[{$itemIndex}].image = data.url;
                parentVue.\$forceUpdate();
            }
        ";

        $encodedJs = aes_encode($js);

        json_success(['js' => $encodedJs]);
    }
    /**
     * 商品
     * @permission 小程序页面.管理
     */
    public function actionProduct()
    {
        $all = db_get("product", "*", [
            'status' => 'success',
            'sys_tag' => 'product',
            'ORDER' => [
                'sort' => 'DESC',
                'id' => 'DESC'
            ]
        ]);
        $list = [];
        foreach ($all as $v) {
            $list[] = [
                'id' => (string)$v['id'],
                'title' => $v['title']
            ];
        }
        json_success(['data' => $list]);
    }
    /**
     * 商品分类 
     * @permission 小程序页面.管理
     */
    public function actionCategory()
    {
        $all = db_get("product_type", "*", [
            'status' => 'success',
            'sys_tag' => 'product',
            'ORDER' => [
                'sort' => 'DESC',
                'id' => 'DESC'
            ]
        ]);
        $list = [];
        foreach ($all as $v) {
            $list[] = [
                'id' => (string)$v['id'],
                'title' => $v['title']
            ];
        }
        json_success(['data' => $list]);
    }
}
