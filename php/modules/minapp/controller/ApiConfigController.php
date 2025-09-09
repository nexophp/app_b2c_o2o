<?php

/**
 * 配置信息
 * @author sunkangchina <68103403@qq.com> 
 * @date 2025
 */

namespace modules\minapp\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: '小程序配置', description: '配置接口')]
class ApiConfigController extends \core\ApiController
{
    protected $need_login = false;

    /**
     * 配置信息
     */
    #[OA\Get(
        path: '/minapp/api-config/info',
        summary: '获取配置信息',
        tags: ['小程序配置'],
    )]
    public function actionInfo()
    {
        $config = get_config([
            'app_name',
            'app_phone',
            'app_beian',
            'app_ga_beian',
            'wallet_rate',
            'wallet_min',
            'search_keywords',
        ]);
        $search_keywords = $config['search_keywords'];
        if ($search_keywords) {
            $config['search_keywords'] =  string_to_array($search_keywords);
        }

        json_success([
            'data' => $config
        ]);
    }
}
