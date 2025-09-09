<?php

namespace modules\o2o\lib;

class Printer
{
    /**
     * 打印订单
     */
    public static function do($order_id)
    {
        $order = db_get_one("order", "*", ['id' => $order_id]);
        $all = db_get("order_item", "*", ['order_id' => $order_id]);
        $data = [];
        foreach ($all as $v) {
            $title = $v['title'];
            $spec = $v['spec'];
            $attr = $v['attr'];
            $num = $v['num'];
            $data[] = [
                'title' => $title . ' ' . $spec . ' ' . $attr,
                'num' => $num,
            ];
        }
        //平台打印机
        $printer_id = db_get_one("ticket", "id", [
            'status' => 'success',
        ]);
        do_ticket_print($printer_id, self::getData($order, $data));
    }

    public static function getData($order, $list)
    {
        $res = [
            [
                "title" => $order['order_num'],
                "tag" => 'cb|br',
            ],
            [
                'tag' => 'line|br'
            ],
            [
                "title" => $order['name'] . ' ' . $order['phone'],
                "tag" => 'br',
            ],
            [
                "title" => $order['address'],
                "tag" => 'br|br',
            ],
        ];
        if ($order['desc']) {
            $res[] = [
                'title' => "备注：" . $order['desc'],
                'tag' => 'l|br|br',
            ];
        }
        $res[] =  [
            'tag' => 'line|br'
        ];
        $res[] = [
            'top' => [
                'title' => '名称|*',
                'num' => '数量|1',
            ],
            'list' => $list
        ];
        $res[] =  [
            'tag' => 'line|br'
        ];
        $res[] = [
            'title' => "下单时间：" . date("Y-m-d H:i:s", $order['created_at']),
            'tag' => 'r|br',
        ];
        $res[] = [
            'tag' => 'cut|br'
        ];
        return $res;
    }
}
