<?php

namespace modules\ticket\controller;

use modules\ticket\lib\feie\Label;
use modules\ticket\lib\Feie;
use modules\ticket\lib\Printer;

class SiteController extends \core\AdminController
{
    /**
     * 飞蛾小票机打印测试
     */
    public  function actionIndex()
    {
        if (!is_admin()) {
            exit('非管理员不能打印');
        }
        $res = do_ticket_print(1, Printer::getData());
        pr($res);
    }

    /**
     * 飞蛾标签机打印测试
     */
    protected function testLabel($arr = [])
    {
        $sn = $arr['printer_no'] ?? '';
        $secret = $arr['printer_secret'] ?? '';

        Feie::$secret = $secret;
        Feie::$desc = $arr['title'] ?? '';

        $layoutData = [
            // 第一行：三列，未指定y，自动布局
            [
                ['title' => '订单001', 'font' => 12, 'widthScale' => 1, 'heightScale' => 1],
                ['title' => '五号桌', 'font' => 12, 'widthScale' => 1, 'heightScale' => 1, 'align' => 'center',],
                ['title' => '1/5', 'font' => 12, 'widthScale' => 1, 'heightScale' => 1, 'align' => 'right',]
            ],
            // 第二行：单列，指定y=15mm (120点)，居中
            [
                'title' => '可乐鸡翅',
                'font' => 12,
                'align' => 'center',
                'widthScale' => 1.2,
                'heightScale' => 1.2,
                'y' => 8 // 毫米
            ],
            // 第三行：单列，未指定y，自动布局
            [
                'title' => '备注：不要加辣！！！',
                'font' => 12,
                'align' => 'left',
                'y' => 18 // 毫米
            ],

            [
                ['title' => '孙先生', 'font' => 12, 'y' => 22, 'widthScale' => 1, 'heightScale' => 1],
                ['title' => '5213', 'font' => 12, 'y' => 22, 'widthScale' => 1, 'heightScale' => 1, 'align' => 'right',],
            ],

            // 第四行：底部对齐，指定y=25mm (200点)
            [
                'title' => date('Y-m-d H:i:s'),
                'font' => 10,
                'y' => 25,
            ],

        ];
        $data = Label::run($layoutData);
        return Feie::printLabel($sn, $data);
    }
}
