<?php

use core\Menu; 

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => 'Banner图',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina',
	'depends' => [
		 
	],
	'level'=>0
];

Menu::setGroup('admin'); 
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('banner-admin', 'Banner图', '/banner/admin', '', 0, 'source');
 