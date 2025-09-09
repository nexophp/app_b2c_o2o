<?php

namespace modules\ticket\lib;

class Info
{
    /**
     * 飞鹅打印机详情
     */
    public static function getFeieInfo($sn, $secret = '')
    {
        $info = Feie::status($sn);
        if ($info == 'online') {
            return true;
        } else {
            return false;
        }
    }
}
