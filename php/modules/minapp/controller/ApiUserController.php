<?php

/**
 * 用户信息
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */

namespace modules\minapp\controller;

use modules\minapp\lib\MinApp;
use OpenApi\Attributes as OA;

#[OA\Tag(name: '小程序', description: '用户接口')] 
class ApiUserController extends \core\ApiController
{
    protected $need_login = false;

    /**
     * 用户信息
     */
    #[OA\Get(
        path: '/minapp/api-user/info',
        summary: '获取用户信息',
        tags: ['小程序'],
    )]
    public function actionInfo()
    {
        $user = $this->user_info;
        unset($user['password']);
        json_success([
            'data' => $user
        ]);
    }
    /**
     * 设置用户信息
     */
    #[OA\Get(
        path: '/minapp/api-user/set-info',
        summary: '设置用户信息',
        tags: ['小程序'],
        parameters: [
            new OA\Parameter(name: 'field', description: '字段', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'value', description: '值', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionSetInfo()
    {
        $uid = $this->uid;
        if (!$uid) {
            json_error(['msg' => lang('用户不存在')]);
        }
        $field = get_input('field');
        $value = get_input('value');
        if (!$field) {
            json_error(['msg' => lang('参数错误')]);
        }
        set_user_info($uid, $field, $value);
        json_success(['msg' => lang('设置成功')]);
    }
}
