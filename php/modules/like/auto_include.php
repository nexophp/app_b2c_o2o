<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => '点赞',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina'
];

/**
 * 获取用户点赞列表
 * @param int $uid 用户ID
 * @param string $type 类型
 * @return array
 */
function get_like_pager($uid, $type)
{
	$list = db_pager("like", "*", [
		'type' => $type,
		'user_id' => $uid,
		'status' => 1,
		'ORDER' => [
			'id' => 'DESC'
		]
	]);
	return $list;
}

/**
 * 获取点赞数量
 * @param string $type 类型
 * @param int $node_id 节点ID
 * @return int
 */
function get_like_count($node_id, $type)
{
	$count = db_get_count("like", ['type' => $type, 'node_id' => $node_id, 'status' => 1]);
	return $count;
}

/**
 * 是否点赞
 * @param int $node_id 节点ID
 * @param string $type 类型
 * @return bool
 */
function is_like($node_id, $type, $uid)
{
	$find = db_get_one("like", "*", ['type' => $type, 'node_id' => $node_id, 'user_id' => $uid, 'status' => 1]);
	return $find ? true : false;
}
