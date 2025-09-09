<?php

namespace modules\o2o\controller;

use modules\o2o\trait\AddressTrait;

class AddressController extends \core\AdminController
{
    use AddressTrait;
    
    protected $model = [
        'address' => '\modules\o2o\model\AddressModel',
    ];

    // 添加用户标签属性
    protected $user_tag = 'admin'; 

    /**
     * 收货地址管理页面
     * @permission 收货地址.管理
     */
    public function actionIndex()
    {
        $this->view_data['name'] = 'address';
    }

    /**
     * 获取收货地址列表
     * @permission 收货地址.管理 收货地址.查看
     */
    public function actionList()
    {
        $this->list();
    }

    /**
     * 获取收货地址详情
     * @permission 收货地址.管理 收货地址.查看
     */
    public function actionInfo()
    {
        $this->info();
    }

    /**
     * 添加收货地址
     * @permission 收货地址.管理
     */
    public function actionAdd()
    {
        $this->add();
    }

    /**
     * 编辑收货地址
     * @permission 收货地址.管理
     */
    public function actionEdit()
    {
        $this->edit();
    }

    /**
     * 删除收货地址
     * @permission 收货地址.管理
     */
    public function actionDelete()
    {
        $this->delete();
    }

    /**
     * 获取状态选项
     * @permission 收货地址.管理 收货地址.查看
     */
    public function actionStatusOptions()
    {
        $this->statusOptions();
    }

    /**
     * 获取省市区级联数据
     * @permission 收货地址.管理 收货地址.查看
     */
    public function actionCascader()
    {
        $this->cascader();
    }
}