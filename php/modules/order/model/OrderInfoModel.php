<?php

namespace modules\order\model;

class OrderInfoModel extends \core\AppModel
{
    protected $table = 'order_info';

    public function afterFind(&$data)
    {
        $list = [
            'coupon' => lang('优惠券'),
        ];
        $data['title'] = $list[$data['type']] ?? $data['type'];
    }
}
