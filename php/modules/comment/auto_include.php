<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => '评论',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina',
	// 依赖模块
	'depends' => [
		'green_text',
		'cart'
	],
];



Menu::setGroup('admin');
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('comment', '评论', '/comment/admin', 'bi-chat-dots', 60, 'source');

/**
 * 评论数
 */
function get_comment_count($nid, $type)
{
	$count = db_get_count("comment", ['nid' => $nid, 'type' => $type, 'status' => 'complete']);
	return $count;
}
