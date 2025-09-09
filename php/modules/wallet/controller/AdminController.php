<?php

namespace modules\wallet\controller;

class AdminController extends \core\AdminController
{
    protected $model = [
        'wallet' => '\modules\wallet\model\WalletModel',
        'wallet_in' => '\modules\wallet\model\WalletInModel',
        'wallet_out' => '\modules\wallet\model\WalletOutModel',
    ];

    public function actionIndex() { 
    }

    /**
     * 钱包列表
     * @permission 钱包.管理 钱包.查看
     */
    public function actionList()
    {
        $phone = $this->post_data['phone'] ?: '';

        $where = [];
        if ($phone) {
            $con = [];
            $con['OR'] = [
                'phone[~]' => $phone,
                'username[>]' => $phone,
            ];
            $where['LIMIT'] = 100;
            $user_id = db_get("user", "id", $con);
            if ($user_id) {
                $where['user_id'] = $user_id;
            } else {
                $where['user_id'] = 0;
            }
        }
        $where['ORDER'] = [
            'out_amount' => 'DESC',
            'id' => 'DESC'
        ];
        $all = $this->model->wallet->pager($where);

        json($all);
    }

    /**
     * 获取收入记录
     * @permission 钱包.管理 钱包.查看
     */
    public function actionInList()
    {
        $user_id = $this->post_data['user_id'] ?: 0;
        if (!$user_id) {
            json_error(['msg' => lang('用户ID不能为空')]);
        }
        $where = ['user_id' => $user_id];
        $where['ORDER'] = ['id' => 'DESC'];
        $list = $this->model->wallet_in->pager($where);
        json($list);
    }

    /**
     * 获取提现记录
     * @permission 钱包.管理 钱包.查看
     */
    public function actionOutList()
    {
        $user_id = $this->post_data['user_id'] ?: 0;

        if (!$user_id) {
            json_error(['msg' => lang('用户ID不能为空')]);
        }

        $where = ['user_id' => $user_id];
        $where['ORDER'] = ['id' => 'DESC'];
        $list = $this->model->wallet_out->pager($where);
        json($list);
    }

    /**
     * 同意提现并打款
     * @permission 钱包.管理
     */
    public function actionConfirmOut()
    {
        $out_id = $this->post_data['out_id'] ?: 0;

        if (!$out_id) {
            json_error(['msg' => lang('提现ID不能为空')]);
        }

        // 检查提现记录是否存在且状态为wait
        $out_record = db_get_one('wallet_cash_out', '*', [
            'id' => $out_id,
            'status' => 'wait'
        ]);

        if (!$out_record) {
            json_error(['msg' => lang('提现记录不存在或状态不正确')]);
        }

        try {
            confirm_wallet_out($out_id);
            json_success(['msg' => lang('提现确认成功')]);
        } catch (\Exception $e) {
            json_error(['msg' => $e->getMessage()]);
        }
    }
}
