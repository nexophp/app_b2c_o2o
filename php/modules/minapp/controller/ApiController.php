<?php

/**
 * 小程序
 * @author sunkangchina <68103403@qq.com> 
 * @date 2025
 */

namespace modules\minapp\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: '小程序', description: '小程序接口')]
class ApiController extends \core\ApiController
{
    protected $need_login = false;

    /**
     * 配置信息
     */
    #[OA\Get(
        path: '/minapp/api/qr',
        summary: '获取二维码',
        tags: ['小程序'],
    )]
    public function actionQr()
    { 
        $url = create_minapp_qr('pages/index/index', 'default');
        json_success([
            'data' => $url,
        ]);
    }
}
