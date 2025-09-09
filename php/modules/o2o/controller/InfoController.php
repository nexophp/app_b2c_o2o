<?php

namespace modules\o2o\controller;


class InfoController extends \core\AdminController
{
    /**
     * 线下门店信息管理页面
     * @permission 线下门店信息.管理
     */
    public function actionIndex()
    {
        $arr = [
            'title',
            'address',
            'image',
            'business_start',
            'business_end',
            'status',
            'phone',
            'notice',
            'lat',
            'lng',
        ];
        foreach ($arr as $v) {
            $this->view_data["data"][$v] = get_config("plat_store_info_$v");
        }
    }

    /**
     * 线下门店信息保存
     * @permission 线下门店信息.管理
     */
    public function actionSave()
    {
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
            set_config("plat_store_info_$v", $this->post_data[$v]);
        }
        $address = $this->post_data['address'];
        $res = \lib\MapTiandi::getLat($address);
        $lat = $res['lat'];
        $lng = $res['lng'];
        set_config("plat_store_info_lat", $lat);
        set_config("plat_store_info_lng", $lng);
        json_success("保存成功");
    }
    /**
     * 线下门店状态改变
     * @permission 线下门店信息.管理
     */
    public function actionChangeStatus()
    {
        $status = $this->post_data['status'];
        set_config("plat_store_info_status", $status);
        json_success("保存成功");
    }
}
