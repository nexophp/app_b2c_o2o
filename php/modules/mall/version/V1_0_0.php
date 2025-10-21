<?php

namespace modules\mall\version;

class V1_0_0
{
    public  static function install()
    {
        if (function_exists('add_blog')) {
            add_blog("隐私政策");
        }
    }
}
