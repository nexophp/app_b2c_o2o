<?php

namespace modules\logistic\lib;

class Logistic
{
    public static function get($no, $type)
    {
        return AliyunLogistic::get($no, $type);
    }
}
