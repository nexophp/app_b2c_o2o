<?php

/**
 * 飞蛾打印机
 */

namespace modules\ticket\lib;

use modules\ticket\lib\feie\Feie58;
use modules\ticket\lib\feie\Label;

class Feie
{
    /**
     * 机器背面的秘钥
     */
    public static $secret;
    /**
     * 打印机描述
     */
    public static $desc;
    /**
     * 58mm 小票打印
     */
    public static function print($sn, $data, $times = 1)
    {
        $client = self::init();
        $client->sn = $sn;
        self::add($sn);
        $client->backUrl = host() . '/ticket/feie/notify';
        $res = $client->print($data, $times);
        if ($res['ret'] != 0) {
            throw new \Exception($res['msg']);
        }
        return true;
    }
    /**
     * 标签打印
     */
    public static function printLabel($sn, $data, $times = 1)
    {
        $client = self::init();
        $client->sn = $sn;
        self::add($sn);
        $client->backUrl = host() . '/ticket/feie/notify';
        $res = $client->printMsgLabel($sn, $data, $times);
        if ($res['ret'] != 0) {
            throw new \Exception($res['msg']);
        }
        return true;
    }
    /**
     * 打印任务状态查询
     */
    public static function query($orderid)
    {
        $client = self::init();
        $res = $client->query($orderid);
        if ($res['ret'] != 0) {
            throw new \Exception($res['msg']);
        }
        return true;
    }

    /**
     * 清空打印机
     */
    public static function clear($sn)
    {
        $client = self::init();
        $res = $client->clear($sn);
        if ($res['ret'] != 0) {
            throw new \Exception($res['msg']);
        }
        return true;
    }

    /**
     * 状态
     */
    public static function status($sn)
    {
        $client = self::init();
        $res = $client->get_status($sn);
        if ($res['ret'] != 0) {
            add_log('飞蛾打印机状态获取失败:' . $sn, 'error');
            return false;
        }
        $str = $res['data'] ?? '';
        if ($str && strpos($str, '在线') !== false) {
            return 'online';
        } else {
            return 'offline';
        }
    }

    private static function init()
    {
        $client = new Feie58;
        $client->user = get_config('feie58_user');
        $client->ukey = get_config('feie58_ukey'); 
        return $client;
    }

    public static function add($sn)
    {
        $client = self::init();
        $info = $client->info($sn); 
        $code = $info['ret'] ?? '';
        if ($code != 0) {
            $key = $sn . "#" . self::$secret . "#" . self::$desc;
            $res = $client->add($key);
            $err = $res['data']['no'][0]??'';
            if($err){
                json_error($err); 
            } 
            return true;
        }
    }
}
