<?php

namespace modules\o2o\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'O2O', description: 'O2O接口')]

class ApiController extends \core\ApiController
{
    protected $need_login = false;
    protected $model = [
        'address' => '\modules\o2o\model\AddressModel',
    ];
    /**
     * 获取O2O配置
     */
    #[OA\Get(
        path: '/o2o/api/address',
        summary: '可选地址（小区）',
        tags: ['O2O']
    )]
    public function actionAddress()
    {
        $data = $this->model->address->find([
            'status' => 'success',
            'ORDER' => ['id' => 'DESC']
        ]);
        json_success(['data' => $data]);
    }
    /**
     * 获取O2O配置
     */
    #[OA\Get(
        path: '/o2o/api/index',
        summary: '获取O2O配置',
        tags: ['O2O']
    )]
    public function actionIndex()
    {
        $data = [];
        $arr = [
            'title',
            'address',
            'image',
            'business_start',
            'business_end',
            'status',
            'phone',
            'notice',
        ];
        foreach ($arr as $v) {
            $data[$v] = get_config("plat_store_info_$v");
        }
        $hours = '';
        if ($data['business_start'] == $data['business_end']) {
            $hours = '24小时营业';
        } else {
            $hours = $data['business_start'] . '-' . $data['business_end'];
        }
        $data['image'] = cdn() . $data['image'];
        $data['hours'] = $hours;
        $data['delivery_range'] = get_config('delivery_range');
        $can_buy = true;
        $now = date('H:i');
        if ($now < $data['business_start'] || $now > $data['business_end']) {
            $can_buy = false;
        }
        if ($data['status'] != 1) {
            $can_buy = false;
        }
        $data['can_buy'] = $can_buy;
        json_success(['data' => $data]);
    }
}
