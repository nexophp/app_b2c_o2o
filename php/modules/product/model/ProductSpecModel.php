<?php

namespace modules\product\model;

class ProductSpecModel extends \core\AppModel
{
    protected $table = 'product_spec';

    public static function getTitle($values)
    { 
        return implode(" ", $values);
    }
}
