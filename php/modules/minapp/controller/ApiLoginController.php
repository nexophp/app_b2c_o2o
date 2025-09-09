<?php

/**
 * 小程序登录
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */

namespace modules\minapp\controller;

use OpenApi\Attributes as OA;
use modules\minapp\lib\MinApp;

#[OA\Tag(name: '小程序', description: '登录接口')]
class ApiLoginController extends \core\ApiController
{
    protected $need_login = false;

    /**
     * openid登录
     */
    #[OA\Get(
        path: '/minapp/api-login/openid',
        summary: '获取openid',
        tags: ['小程序'],
        parameters: [
            new OA\Parameter(name: 'code', description: '登录凭证（code）', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionOpenid()
    {
        $code = get_input('code');
        $utils = MinApp::init()->getUtils();
        $res = $utils->codeToSession($code);
        $openid = $res['openid'] ?? '';
        $unionid = $res['unionid'] ?? '';
        if (!$openid) {
            json_error(['msg' => lang('获取小程序openid失败')]);
        }
        $type = 'weixin';
        set_user_openid($openid, $unionid, $type, $this->uid);
        $data['token'] = add_user_login_his($this->uid);
        $user = get_user($this->uid);
        $data['phone'] = $user['phone'];
        $data['openid'] = $openid;
        json_success([
            'data' => $data
        ]);
    }

    /**
     * 绑定手机号
     */
    #[OA\Get(
        path: '/minapp/api-login/phone',
        summary: '绑定手机号',
        tags: ['小程序'],
        parameters: [
            new OA\Parameter(name: 'code', description: '登录凭证（code）', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'iv', description: '初始化向量（iv）', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'encryptedData', description: '加密数据（encryptedData）', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionPhone()
    {
        $code = get_input('code');
        $iv = get_input('iv');
        $encryptedData = get_input('encryptedData');
        $utils = MinApp::init()->getUtils();
        $res = $utils->codeToSession($code);
        $session_key = $res['session_key'] ?? '';
        $openid = $res['openid'] ?? '';
        $unionid = $res['unionid'] ?? '';
        if (!$session_key) {
            json_error(['msg' => lang('获取手机号失败')]);
        }
        $session = $utils->decryptSession($session_key, $iv, $encryptedData);
        $phone = $session['phoneNumber'] ?? '';
        $purePhoneNumber = $session['purePhoneNumber'] ?? '';
        $country_code = $session['countryCode'] ?? '';
        if (!$phone) {
            json_error(['msg' => lang('获取手机号失败')]);
        }
        $user = get_user(['phone' => $phone]);
        if (!$user) {
            $uid = db_insert('user', [
                'phone' => $phone,
                'created_at' => time(),
            ]);
        } else {
            $uid = $user['id'];
        }
        set_user_openid($openid, $unionid, 'weixin', $uid);
        $data['token'] = add_user_login_his($uid);
        $user = get_user($uid);
        $data['phone'] = $user['phone'];
        $data['openid'] = $openid;
        unset($data['password']);
        json_success([
            'data' => $data
        ]);
    }

    /**
     * 手机号验证码登录
     */
    #[OA\Get(
        path: '/minapp/api-login/phone-code',
        summary: '手机号验证码登录',
        tags: ['小程序'],
        parameters: [
            new OA\Parameter(name: 'phone', description: '手机号', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'code', description: '验证码', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
    )]
    public function actionPhoneCode()
    {
        $phone = get_input('phone');
        $code = get_input('code');
        if (!$phone) {
            json_error(['msg' => lang('手机号不能为空')]);
        }
        if (!$code) {
            json_error(['msg' => lang('验证码不能为空')]);
        }

        $user = db_get_one('user', "*", ['phone' => $phone]);
        if (!$user) {
            //不存在的帐号会自动创建新的帐号
            $uid = create_user([
                'phone' => $phone,
                'created_at' => time(),
            ], 'user');
        } else {
            $uid = $user['id'];
        }
        $code = get_input('code');
        $cache_login_code = "login:" . $phone;
        if (!is_local() && cache($cache_login_code) != $code) {
            json_error(['msg' => lang('验证码错误')]);
        }
        //登录
        $user = get_user($uid);
        $data = [];
        $data['token'] = add_user_login_his($uid);
        $data['phone'] = $user['phone'];
        $data['user_id'] = $uid;
        unset($data['password']);
        json_success([
            'data' => $data
        ]);
    }
}
