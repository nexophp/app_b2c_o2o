<?php

/**
 * 商品库存
 */

namespace modules\product\lib;

class ProductStock
{

    /**
     * 检测库存是否充足
     */
    public static function check($item)
    {
        // 不检测库存
        $no_check_stock = get_config('no_check_stock');
        if ($no_check_stock == 1) {
            return true;
        }

        if ($item['spec']) {
            $where = [
                'title' => $item['spec'],
                'product_id' => $item['product_id'],
            ];
            $res = db_get_one('product_spec', '*', $where);
            $stock = $res['stock'];
            if ($stock < $item['num']) {
                return false;
            }
        } else {
            $where = [
                'id' => $item['product_id'],
            ];
            $res = db_get_one('product', '*', $where);
            $stock = $res['stock'];
            if ($stock < $item['num']) {
                return false;
            }
        }
        return true;
    }
    /**
     * 减少商品库存
     */
    public static function down($item, $action = '-')
    {
        $product_type = db_get_one("product", "product_type", ['id' => $item['product_id']]);
        if (strpos($product_type, 'product') === false) {
            return true;
        }
        // 不检测库存
        $no_check_stock = get_config('no_check_stock');
        if ($no_check_stock == 1) {
            return true;
        }
        // 减少商品库存
        if ($item['spec']) {
            $where = [
                'title' => $item['spec'],
                'product_id' => $item['product_id'],
                'FOR UPDATE' => TRUE,
            ];
            $res = db_get_one('product_spec', '*', $where);
            $stock = $res['stock'];
            $id = $res['id'];
            if ($action == '-' && $stock < $item['num']) {
                json_error(['msg' => $item['title'] . '【' . $res['title'] . "】" . lang('库存不足')]);
            }
            if ($action == '-') {
                $num = $stock - $item['num'];
            } else {
                $num = $stock + $item['num'];
            }
            $product = db_get_one('product', '*', [
                'id' => $item['product_id'],
                'FOR UPDATE' => TRUE,
            ]);

            if ($action == '-') {
                db_update('product', [
                    'sales' => $product['sales'] + $item['num'],
                ], ['id' => $item['product_id']]);
            } else {
                db_update('product', [
                    'sales' => $product['sales'] - $item['num'],
                ], ['id' => $item['product_id']]);
            }

            db_update('product_spec', [
                'stock' => $num,
            ], ['id' => $id]);
        } else {
            $where = [
                'id' => $item['product_id'],
                'FOR UPDATE' => TRUE,
            ];
            $res = db_get_one('product', '*', $where);
            $stock = $res['stock'];
            $id = $res['id'];
            if ($action == '-' && $stock < $item['num']) {
                json_error(['msg' => $res['title'] . lang('库存不足')]);
            }
            if ($action == '-') {
                $num = $stock - $item['num'];
            } else {
                $num = $stock + $item['num'];
            }
            if ($action == '-') {
                db_update('product', [
                    'stock' => $num,
                    'sales' => $res['sales'] + $item['num'],
                ], ['id' => $id]);
            } else {
                db_update('product', [
                    'stock' => $num,
                    'sales' => $res['sales'] - $item['num'],
                ], ['id' => $id]);
            }
        }
    }
    /**
     * 增加商品库存
     */
    public static function up($item)
    {
        self::down($item, '+');
    }
}
