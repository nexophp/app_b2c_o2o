<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => '内容',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];


Menu::setGroup('admin');

// 添加顶级菜单
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('blog_admin', '内容', '/blog/blog', '', 0, 'source');

/**
 * 添加内容
 * @param string $title 标题
 * @param string $content 内容
 * @param int $type_id 分类id
 * @param int $user_id 用户id
 * @return void
 */
function add_blog($title, $content = '', $type_id = 0, $user_id = 0)
{
    if (!$title) {
        return false;
    }

    $model = modules\blog\model\BlogModel::model();
    if (!$model->findOne(['title' => $title])) {
        $model->insert([
            'title' => $title,
            'content' => $content,
            'type_id' => $type_id,
            'user_id' => $user_id,
            'status' => 'published',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }
}
/**
 * 获取内容
 * @param string $title 标题或ID
 * @return array
 */
function get_blog($title)
{
    if (is_numeric($title)) {
        $where['id'] = $title;
    } else {
        $where['title'] = $title;
    }
    $where['status'] = 'published';
    $model = modules\blog\model\BlogModel::model();
    $row = $model->findOne($where);
    if (!$row) {
        return;
    }
    modules\blog\data\BlogData::parseData($row);
    return $row;
}
