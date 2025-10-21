<?php

namespace modules\mall\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: '商城页面', description: '商城页面接口')]

class ApiUniappController extends \core\ApiController
{
    protected $need_login = false;
    protected $model = '\modules\mall\model\UniappPageModel';
    /**
     * 商城页面规格
     */
    #[OA\Get(
        path: '/mall/api/index',
        summary: '商城页面规格',
        tags: ['商城页面']
    )]
    public function actionIndex()
    {
        $where = [];
        $url = $this->post_data['url'] ?: $_GET['url'];
        if ($url) {
            $where['url'] = $url;
        } else {
            $where['is_home'] = 1;
        }
        $where['status'] = 1;
        $res = $this->model->findOne($where);
        $page_data = $res['page_data'];
        foreach ($page_data as $k => $v) {
            if ($v['config']['image']) {
                $page_data[$k]['config']['image'] = cdn() . $v['config']['image'];
            } else {
                foreach ($v['config'] as $k2 => $v2) {
                    if ($v2['image']) {
                        $page_data[$k]['config'][$k2]['image'] = cdn() . $v2['image'];
                    }
                }
            }
        }
        $res['page_data'] = $page_data;
        if ($res['share_image']) {
            $res['share_image'] = cdn() . $res['share_image'];
        }

        json_success(['data' => $res]);
    }
    /**
     * 退换货原因
     */
    #[OA\Get(
        path: '/mall/api-uniapp/return-reason',
        summary: '退换货原因',
        tags: ['商城页面']
    )]
    public function actionReturnReason()
    {
        $res = get_mall_order_return_reason();
        json_success(['data' => $res]);
    }
}
