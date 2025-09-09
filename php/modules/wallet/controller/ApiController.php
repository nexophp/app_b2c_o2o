<?php

namespace modules\wallet\controller;

use OpenApi\Attributes as OA;

#[OA\Tag(name: '钱包', description: '钱包接口')]
class ApiController extends \core\ApiController
{
    protected $model = [
        'wallet' => '\modules\wallet\model\WalletModel',
        'wallet_in' => '\modules\wallet\model\WalletInModel',
        'wallet_out' => '\modules\wallet\model\WalletOutModel',
    ];
    /**
     * 收入概况
     */
    #[OA\Post(path: '/wallet/api/index', summary: '收入列表', tags: ['钱包'])]
    public function actionIndex()
    {
        $data = $this->model->wallet->findOne(['user_id' => $this->uid]);
        json_success(['data' => $data]);
    }
    /**
     * 收入提现记录
     */
    #[OA\Post(path: '/wallet/api/in-out', summary: '收入提现记录', tags: ['钱包'])]
    public function actionInOut()
    {
        $all = db_pager('wallet_in_out', [
            'user_id' => $this->user_id,
            'ORDER' => [
                'id' => 'DESC',
            ]
        ]);
        $list = [];
        foreach ($all['data'] as $v) {
            if ($v['type'] == 'in') {
                $v['type_text'] = '收入';
                $row = $this->model->wallet_in->findOne($v['nid']);
                $row['flag'] = 'in';
                $list[] = $row;
            } else {
                $v['type_text'] = '提现';
                $row = $this->model->wallet_out->findOne($v['nid']);
                $row['flag'] = 'out';
                $list[] = $row;
            }
        }
        $all['data'] = $list;
        json($all);
    }
    /**
     * 收入列表
     */
    #[OA\Post(path: '/wallet/api/in', summary: '收入列表', tags: ['钱包'])]
    public function actionIn()
    {
        $data = $this->model->wallet_in->pager([
            'user_id' => $this->user_id,
            'ORDER' => [
                'id' => 'DESC',
            ]
        ]);
        json($data);
    }
    /**
     * 提现列表
     */
    #[OA\Post(path: '/wallet/api/out', summary: '提现列表', tags: ['钱包'])]
    public function actionOut()
    {
        $data = $this->model->wallet_out->pager([
            'user_id' => $this->user_id,
            'ORDER' => [
                'id' => 'DESC',
            ]
        ]);

        json($data);
    }
    /**
     * 发起提现
     */
    #[OA\Post(path: '/wallet/api/do-out', summary: '发起提现', tags: ['钱包'], parameters: [
        new OA\Parameter(name: 'amount', description: '金额', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'desc', description: '描述', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'type', description: '类型', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'account', description: '账号', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
    ])]
    public function actionDoOut()
    {
        $amount = $this->post_data['amount'];
        $type = $this->post_data['type'];
        $account = $this->post_data['account'];
        $desc = $this->post_data['desc'] ?: '主动发起';
        $insert = [
            'user_id' => $this->uid,
            'amount' => $amount,
            'desc' => $desc,
            'type' => $type,
            'account' => $account,
            'status' => 'wait',
        ];

        add_wallet_out($insert);
        json_success(['msg' => lang('提现申请已提交')]);
    }
}
