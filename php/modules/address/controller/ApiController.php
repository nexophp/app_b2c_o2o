<?php

/**
 * 区域管理
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */

namespace modules\address\controller;
use OpenApi\Attributes as OA;

#[OA\Tag(name: '城市地址', description: '城市地址接口')]
class ApiController extends \core\ApiController
{
    protected $need_login = false;
    /**
     * 省份列表，小程序
     */
    #[OA\Get(path: '/address/api/index', summary: '省份列表', tags: ['城市地址'])] 
    public function actionIndex()
    {
        $file = PATH . '/vendor/nexophp/element/data/city.json';
        $data = file_get_contents($file);
        $data = json_decode($data, true);
        json_success(['data' => $data]);
    }
}
