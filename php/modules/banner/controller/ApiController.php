<?php

/**
 * Banner管理
 * @author sunkangchina <68103403@qq.com> 
 * @date 2025
 */

namespace modules\banner\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Banner', description: 'Banner接口')]
class ApiController extends \core\ApiController
{
    protected $need_login = false;
    /**
     * Banner列表，小程序
     */
    #[OA\Get(path: '/banner/api/index', summary: 'Banner列表', tags: ['Banner'])]
    public function actionIndex()
    {
        $data = db_get('banner', [
            'ORDER' => [
                'sort' => 'DESC',
                'id' => 'DESC',
            ],
            'status' => 1
        ]);
        $list = [];
        foreach ($data as $v) {
            $list[] = [
                'title' => $v['title'],
                'image' => cdn() . $v['image'],
                'type' => $v['type'],
                'app_id' => $v['app_id'],
                'url' => $v['url'],
            ];
        }
        json_success(['data' => $list]);
    }
}
