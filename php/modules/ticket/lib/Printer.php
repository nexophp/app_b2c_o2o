<?php

namespace modules\ticket\lib;

class Printer
{
    /**
     * 添加打印机
     */
    public static function addFeie($sn, $secret, $title)
    {
        Feie::$secret = $secret;
        Feie::$desc = $title;
        return Feie::add($sn);
    }

    /**
     * 飞鹅打印机详情
     */
    public static function doFeie($sn, $secret, $data)
    {
        Feie::$secret = $secret;
        return Feie::print($sn, $data);
    }

    /**
     * 测试数据
     */
    public static function getData()
    {
        return  [
            [
                "title" => '标题',
                'tag' => 'cb|br',
            ],
            [
                "title" => '123465',
                'tag' => 'code_int|br',
            ],
            [
                'tag' => 'line|br'
            ],
            [
                'top' => [
                    'title' => '名称|*',
                    'price' => '单价|2',
                    'num' => '数量|1',
                ],
                'list' => [
                    [
                        'title' => '酸菜鱼',
                        'price' => '100.4',
                        'num' => '10',
                    ],
                    [
                        'title' => '可乐鸡翅+蒜蓉蒸扇贝',
                        'price' => '10.3',
                        'num' => '6',
                    ],
                    [
                        'title' => '紫苏焖鹅+梅菜肉饼+椒盐虾+北京烤鸭',
                        'price' => '10.0',
                        'num' => '8',
                    ],
                ]
            ],
        ];
    }
}
