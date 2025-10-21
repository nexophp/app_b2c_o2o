<?php

namespace modules\logistic\controller;
  
use OpenApi\Attributes as OA;
use modules\logistic\lib\AliyunLogistic;

#[OA\Tag(name: '物流查寻', description: '物流查寻接口')] 
class ApiController extends \core\ApiController
{
    protected $need_login = false; 
     protected $model = [
        'logistic' => '\modules\logistic\model\LogisticModel',
    ];
    /**
     * 支持的物流公司
     */
    #[OA\Get(
        path: '/logistic/api/support',
        summary: '支持的物流公司',
        tags: ['物流查寻'],
    )]
    public function actionSupport(){
        $list = $this->model->logistic->find([
            'status' => 1,
        ]);
        $data = [];
        foreach($list as $v){
            $data[] = [
                'label' => $v['name'],
                'value' => $v['code'],
            ];
        }
        json_success(['data'=>$data]);
    }
    /**
     * 物流查寻
     */
    #[OA\Get(
        path: '/logistic/api/index',
        summary: '物流查寻',
        tags: ['物流查寻'],
        parameters: [
            new OA\Parameter(name: 'no', description: '物流单号', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', description: '类型', in: 'query', schema: new OA\Schema(type: 'string', default: 'product')),
        ],
    )]
    public function actionIndex(){
        $no = $this->post_data['no']?:'780098068058:1234';
        $type = $this->post_data['type']?:'zto';
        $list = AliyunLogistic::get($no, $type);
        json($list);
    }
}
