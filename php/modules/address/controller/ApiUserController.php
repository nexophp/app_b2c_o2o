<?php

/**
 * 用户收货地址
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */

namespace modules\address\controller;

use modules\address\validate\UserAddressValidate;
use modules\address\data\UserAddressData;
use OpenApi\Attributes as OA;

#[OA\Tag(name: '用户收货地址', description: '')]
class ApiUserController extends \core\ApiController
{
    /**
     * 收货地址列表
     */
    #[OA\Get(path: '/address/user-address/index', summary: '收货地址列表', tags: ['用户收货地址'])]
    public function actionIndex()
    {
        $all = db_pager('user_address', "*", [
            'user_id' => $this->user_id,
            'ORDER' => ['id'=>'DESC'],
        ]);
        foreach ($all['data'] as &$v) {
            UserAddressData::data($v); 
        }
        json_success($all);
    }
    /**
     * 新增收货地址
     */
    #[OA\Post(path: '/address/user-address/create', summary: '新增收货地址', tags: ['用户收货地址'], parameters: [ 
        new OA\Parameter(name: 'name', description: '姓名', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'phone', description: '手机号', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'region', description: '区域', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'country', description: '国家', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'province', description: '省份', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'city', description: '城市', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'district', description: '区县', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'detail', description: '详细地址', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'is_default', description: '是否默认', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), 
    ])]
    public function actionCreate()
    {
        $data = get_input();
        $data['user_id'] = $this->user_id;
        $data['created_at'] = time();
        $is_default = get_input('is_default') ?: 0;
        if ($is_default) {
            db_update('user_address', [
                'is_default' => 0,
            ], [
                'user_id' => $this->user_id,
            ]);
        }
        UserAddressValidate::validate($data);
        db_insert('user_address', $data);
        json_success(['msg' => lang('新增成功')]);
    }
    /**
     * 更新收货地址
     */
    #[OA\Post(path: '/address/user-address/update', summary: '更新收货地址', tags: ['用户收货地址'], parameters: [ 
        new OA\Parameter(name: 'id', description: '地址ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
    ])]
    public function actionUpdate()
    {
        $data = get_input();
        $id = get_input('id');
        unset($data['id']);
        $row = db_get('user_address', "*", [
            'id' => $id,
            'user_id' => $this->user_id,
        ]);
        if (!$row) {
            json_error(lang('地址不存在'));
        }
        UserAddressValidate::validate($data);
        db_update('user_address', $data, [
            'id' => $id,
            'user_id' => $this->user_id,
        ]);
        json_success(['msg' => lang('更新成功')]);
    }
    /**
     * 删除收货地址
     */
    #[OA\Post(path: '/address/user-address/del', summary: '删除收货地址', tags: ['用户收货地址'], parameters: [ 
        new OA\Parameter(name: 'id', description: '地址ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
    ])]
    public function actionDel()
    {
        $id = get_input('id');
        db_delete('user_address', [
            'id' => $id,
            'user_id' => $this->user_id,
        ]);
        json_success(['msg' => lang('删除成功')]);
    }
    /**
     * 获取默认收货地址
     */
    #[OA\Get(path: '/address/user-address/default', summary: '获取默认收货地址', tags: ['用户收货地址'])]
    public function actionDefault()
    {
        $row = db_get_one('user_address', "*", [
            'user_id' => $this->user_id,
            'is_default' => 1,
        ]);
        if(!$row){
            json_error(['msg' => lang('默认地址不存在')]);
        }
        UserAddressData::data($row);
        json_success(['data' => $row]);
    }
    /**
     * 查看详情
     */
    #[OA\Get(path: '/address/user-address/view', summary: '查看详情', tags: ['用户收货地址'], parameters: [ 
        new OA\Parameter(name: 'id', description: '地址ID', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
    ])]
    public function actionView()
    {
        $id = get_input('id');
        $row = db_get_one('user_address', "*", [
            'id' => $id,
            'user_id' => $this->user_id,
        ]);
        $row['is_default'] = (string)$row['is_default'];
        json_success(['data' => $row]);
    }
}
