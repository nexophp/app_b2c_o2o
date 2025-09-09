<?php

namespace modules\cart\controller;

use modules\cart\trait\CartTrait;


class CartController extends \core\AdminController
{
    use CartTrait;
    /**
     * 检查商品是否存在
     */
    protected $check_product = true;
    /**
     * 模型
     */
    protected $model = [ 
        'cart_item' => '\modules\cart\model\CartItemModel',
        'product' => '\modules\product\model\ProductModel',
        'spec' => '\modules\product\model\ProductSpecModel',
        'user' => '\modules\admin\model\UserModel',
    ];

    /**
     * 购物车列表
     */
    public function actionIndex() {}
    /**
     * 购物车列表AJAX
     */
    public function actionList()
    {
        $this->list($this->post_data);
    }

    /**
     * 删除购物车
     */
    public function actionDelete()
    {
        $this->delete($this->post_data['id']);
    } 
    
    /**
     * 搜索用户
     */
    public function actionSearchUser()
    {
        $this->searchUser($this->post_data['phone']);
    }

    /**
     * 获取商品列表
     */
    public function actionProducts()
    {
        $this->products();
    }

    /**
     * 添加到购物车
     */
    public function actionAdd()
    { 
        $this->add($this->post_data);
    } 

    /**
     * 更新商品数量
     */
    public function actionUpdate()
    {
        $this->update($this->post_data['id'], $this->post_data['num']);
    }

    
}
