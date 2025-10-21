<?php

namespace modules\product\model;

class ProductModel extends \core\AppModel
{
    protected $table = 'product';

    protected $field = [
        'title'  => '标题',
        'image' => '图片',
    ];

    protected $validate = [
        'required' => [
            'title',
            'image',
        ],
    ];

    protected $unique_message = [
        '标题已存在',
        '图片不能为空',
    ];

    protected $has_one = [
        'has_product_type' => [ProductTypeModel::class, 'type_id_last'],
    ];

    protected $has_many = [
        'product_spec' => [ProductSpecModel::class, 'product_id', 'id', ['LIMIT' => 500]],
    ];

    public function afterFind(&$data)
    {
        parent::afterFind($data);
        //$data['new_data'] = '新的值';
    }

    public function getAttrTitle()
    {
        return $this->data['title'];
    }

    public function getAttrShowPrice()
    { 
        if($this->spec_type == 2){
            //取规格里面金额最低的
            $product_spec = $this->product_spec;
            if($product_spec){
                $product_spec = $product_spec->toArray();
                $price = array_column($product_spec, 'price');
                $price = min($price);
                return $price;
            } 
        }else{
            return $this->price;
        } 
    }
    /**
     * 属性数组
     */
    public function getAttrNewAttr()
    {
        $new_attr = [];
        $attr = $this->attr;
        if ($attr) {
            foreach ($attr as $v) {
                if ($v['name'] && $v['values']) {
                    $new_arr = string_to_array($v['values']);
                    $new_attr[$v['name']] = array_unique($new_arr);
                }
            }
        }
        return $new_attr;
    }

    public function beforeSave(&$data)
    {
        parent::beforeSave($data);
        $data['images'] = $data['images'] ?: [];
        $type_id = $data['type_id'];
        if ($type_id && is_array($type_id)) {
            $data['type_id_last'] = end($type_id);
        }
        //单规格、多规格需要判断价格 
        if ($data['spec_type'] == 1) {
            if ($data['price'] <= 0) {
                if (!$data['ignore_price']) {
                    json_error(['msg' => lang('价格必填')]);
                }
            }
        } else {
            $flag = false;
            foreach ($data['spec'] as $v) {
                if ($v['price'] > 0) {
                    $flag = true;
                } else {
                    json_error(['msg' => lang('规格名、价格必填')]);
                }
            }
            if (!$flag) {
                json_error(['msg' => lang('规格名、价格必填')]);
            }
        }
        $attr = $data['attr'];
        if ($attr) {
            foreach ($attr as $v) {
                if ($v['name'] && $v['values']) {
                    $new_arr = string_to_array($v['values']);
                    $new_attr[] = [
                        'name' => $v['name'],
                        'values' => implode(" ", array_unique($new_arr)),
                    ];
                }
            }
            $data['attr'] = $new_attr;
        }
        global $admin_type;
        if ($admin_type) {
            $data['user_tag'] = $admin_type;
        }
        $this->checkSku($data);
    }
    /**
     * 检查商品唯一码是否存在
     */
    protected function checkSku($data)
    {
        $id = $data['id'] ?? 0;
        $spec_type = $data['spec_type'] ?? '';
        //多规格
        if ($spec_type == 2) {
            foreach ($data['spec'] as $v) {
                $sku = $v['sku'] ?? '';
                if (!$sku) {
                    continue;
                }
                $this->checkSkuExist($sku, $id);
            }
        } else {
            $sku = $data['sku'] ?? '';
            $this->checkSkuExist($sku, $id);
        }
    }
    /**
     * 检查商品唯一码是否存在
     */
    protected function checkSkuExist($sku, $id = 0)
    {
        if (!$sku) {
            return;
        }
        $where = [
            'sku' => $sku,
        ];
        $info = db_get_one("product_spec", "*", $where);
        if ($info && $info['product_id'] != $id) {
            json_error(['msg' => lang('商品唯一码已存在') . " " . $sku]);
        }
        $info = db_get_one("product", "*", $where);
        if ($info && $info['id'] != $id) {
            json_error(['msg' => lang('商品唯一码已存在') . " " . $sku]);
        }
    }



    public function afterSave($data)
    {
        $id = $data['id'];
        $spec = $data['spec'];
        $model = new ProductSpecModel();
        $model->delete(['product_id' => $id]);
      
        if ($spec) {
            foreach ($spec as $_spec) {
                $_spec['product_id'] = $id;
                $_spec['title'] = ProductSpecModel::getTitle($_spec['spec_values']);
                $model->insert($_spec);
            }
        }
    }
}
