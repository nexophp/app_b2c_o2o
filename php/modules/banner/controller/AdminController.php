<?php

namespace modules\banner\controller;

use modules\banner\model\BannerModel;

class AdminController extends \core\AdminController
{
    protected $model = "\modules\banner\model\BannerModel";

    /**
     * Banner列表页面
     * @permission Banner.管理 Banner.查看
     */
    public function actionIndex() {}

    /**
     * 获取Banner列表
     * @permission Banner.管理 Banner.查看
     */
    public function actionList()
    {
        $where = [];
        $title = $this->post_data['title'] ?? '';
        $type = $this->post_data['type'] ?? '';
        $status = $this->post_data['status'] ?? '';
        if ($title) {
            $where['title[~]'] = $title;
        }
        if ($type) {
            $where['type'] = $type;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        $where['ORDER'] = ['sort' => 'DESC', 'id' => 'DESC'];
        $list = $this->model->pager($where);
        // 处理数据
        foreach ($list['data'] as &$item) {
        }

        json($list);
    }

    /**
     * 添加Banner
     * @permission Banner.管理
     */
    public function actionAdd()
    {
        $title = $this->post_data['title'] ?? '';
        $image = $this->post_data['image'] ?? '';
        $url = $this->post_data['url'] ?? '';
        $type = $this->post_data['type'] ?? 'web';
        $app_id = $this->post_data['app_id'] ?? '';

        if (empty($title)) {
            json_error(['msg' => lang('Banner标题不能为空')]);
        }

        if (empty($image)) {
            json_error(['msg' => lang('Banner图片不能为空')]);
        }

        $data = [
            'title' => $title,
            'image' => $image,
            'url' => $url,
            'type' => $type,
            'status' => 1,
            'user_id' => $this->uid,
            'created_at' => time(),
            'updated_at' => time(),
            'app_id' => $app_id
        ];

        $id = db_insert('banner', $data);
        if ($id) {
            json_success(['msg' => lang('添加成功'), 'id' => $id]);
        } else {
            json_error(['msg' => lang('添加失败')]);
        }
    }

    /**
     * 编辑Banner
     * @permission Banner.管理
     */
    public function actionEdit()
    {
        $id = $this->post_data['id'] ?? 0;
        $title = $this->post_data['title'] ?? '';
        $image = $this->post_data['image'] ?? '';
        $url = $this->post_data['url'] ?? '';
        $type = $this->post_data['type'] ?? 'web';
        $app_id = $this->post_data['app_id'] ?? '';

        if (!$id) {
            json_error(['msg' => lang('Banner ID不能为空')]);
        }

        if (empty($title)) {
            json_error(['msg' => lang('Banner标题不能为空')]);
        }

        if (empty($image)) {
            json_error(['msg' => lang('Banner图片不能为空')]);
        }

        $data = [
            'title' => $title,
            'image' => $image,
            'url' => $url,
            'type' => $type,
            'updated_at' => time(),
            'app_id' => $app_id

        ];
        db_update('banner', $data, ['id' => $id]);
        json_success(['msg' => lang('更新成功')]);
    }

    /**
     * 删除Banner
     * @permission Banner.管理
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('Banner ID不能为空')]);
        }

        $result = db_delete('banner', ['id' => $id]);
        json_success(['msg' => lang('删除成功')]);
    }

    /**
     * 获取Banner详情
     * @permission Banner.管理 Banner.查看
     */
    public function actionDetail()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('Banner ID不能为空')]);
        }

        $data = $this->model->findOne(['id' => $id]);
        if ($data) {
            json_success(['data' => $data]);
        } else {
            json_error(['msg' => lang('Banner不存在')]);
        }
    }

    /**
     * 更新排序
     * @permission Banner.管理
     */
    public function actionUpdateSort()
    {
        $data = $this->post_data['data'] ?? [];
        if (empty($data)) {
            json_error(['msg' => lang('排序数据不能为空')]);
        }
        $sort = count($data) + 10;
        foreach ($data as $id) {
            db_update("banner", ['sort' => $sort--], ['id' => $id]);
        }

        json_success(['msg' => lang('排序更新成功')]);
    }

    /**
     * 切换状态
     * @permission Banner.管理
     */
    public function actionToggleStatus()
    {
        $id = $this->post_data['id'] ?? 0;
        if (!$id) {
            json_error(['msg' => lang('Banner ID不能为空')]);
        }

        $banner = db_get_one('banner', ['id', 'status'], ['id' => $id]);
        if (!$banner) {
            json_error(['msg' => lang('Banner不存在')]);
        }

        $newStatus = $banner['status'] ? 0 : 1;
        $result = db_update('banner', [
            'status' => $newStatus,
            'updated_at' => time()
        ], ['id' => $id]);

        if ($result) {
            json_success([
                'msg' => lang('状态更新成功'),
                'status' => $newStatus
            ]);
        } else {
            json_error(['msg' => lang('状态更新失败')]);
        }
    }

    /**
     * 获取Banner类型选项
     * @permission Banner.管理 Banner.查看
     */
    public function actionGetTypeOptions()
    {
        $options = BannerModel::getTypeOptions();
        $result = [];
        foreach ($options as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        json_success(['data' => $result]);
    }
}
