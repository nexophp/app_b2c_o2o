<?php

namespace modules\wallet\version;

class V1_0_0
{
    public  static function install()
    {
        if (function_exists('add_blog')) {
            add_blog("提现说明");
        }
    }
}
