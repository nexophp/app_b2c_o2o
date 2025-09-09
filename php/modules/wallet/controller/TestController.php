<?php

namespace modules\wallet\controller;

class TestController extends \core\AdminController
{
    public function actionIndex()
    {
        if (!is_local()) {
            json_error(['msg' => '非本地环境不能调用']);
        }

        $data = [
            //用户ID
            'user_id' => 2,
            //订单金额
            'order_amount' => 100,
            //用户收入千分比  
            'rate' => 600,
            //描述
            'desc' => '测试',
            //wait 待处理 success 成功 fail 失败
            'status' => 'success',
            //订单号，可为空
            'order_num' => '2023080100050',
            //产品类型
            'type' => 'product',
        ];
        $res = add_wallet($data);

    }
}
