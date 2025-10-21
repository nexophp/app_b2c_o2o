<?php

namespace modules\ticket\controller;

use modules\ticket\trait\TicketTrait;

class AdminController extends \core\AdminController
{
    use TicketTrait;
    
    protected $model = [
        'ticket' => '\modules\ticket\model\TicketModel', 
    ];

    // 添加用户标签属性
    protected $user_tag = 'admin'; 

    /**
     * 打印机管理页面
     * @permission 小票机.管理
     */
    public function actionIndex()
    {
        $this->view_data['name'] = 'admin';
    }

    /**
     * 获取打印机列表
     * @permission 小票机.管理 小票机.查看
     */
    public function actionList()
    {
        $this->list();
    }

    /**
     * 获取打印机详情
     * @permission 小票机.管理 小票机.查看
     */
    public function actionInfo()
    {
        $this->info();
    }

    /**
     * 添加打印机
     * @permission 小票机.管理
     */
    public function actionAdd()
    {
        $this->add();
    }

    /**
     * 编辑打印机
     * @permission 小票机.管理
     */
    public function actionEdit()
    {
        $this->edit();
    }

    /**
     * 删除打印机
     * @permission 小票机.管理
     */
    public function actionDelete()
    {
        $this->delete();
    }

    /**
     * 获取打印机类型选项
     * @permission 小票机.管理 小票机.查看
     */
    public function actionTypeOptions()
    {
        $this->typeOptions();
    }

    /**
     * 获取打印机状态选项
     * @permission 小票机.管理 小票机.查看
     */
    public function actionStatusOptions()
    {
        $this->statusOptions();
    }
}