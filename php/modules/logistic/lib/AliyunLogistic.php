<?php

/**
 * https://market.aliyun.com/apimarket/detail/cmapi021863
 * 阿里云物流查寻
 */

namespace modules\logistic\lib;

class AliyunLogistic
{
    /**
     * 阿里云物流查寻
     */
    public static function get($no, $type)
    {
        $cache_key = 'AliyunLogistic:' . $no . $type;
        $list = cache($cache_key);
        if ($list) {
            $list['cache'] = 1;
            return $list;
        }
        $url = "https://wuliu.market.alicloudapi.com/kdi";
        $res = curl_aliyun($url, [
            'no' => $no,
            'type' => $type,
        ], 'GET');
        if ($res['code'] != 0) {
            return $res;
        }
        $find_list = $res['result']['list'] ?? [];
        $new_list = [];
        if (!$find_list) {
            return [
                'code' => 250,
                'msg' => $res['msg'] ?? '未查找到物流信息',
            ];
        }
        foreach ($find_list as $v) {
            $time_arr = explode(' ', $v['time']);
            $new_list[] = [
                'title' => $v['status'] ?? '',
                'status' => self::parseTitle($v['status'] ?? ''),
                'time' => $v['time'],
                'time_arr' => $time_arr,
            ];
        }
        $status_arr = [
            0 => '快递收件(揽件)',
            1 => '在途中',
            2 => '正在派件',
            3 => '已签收',
            4 => '派送失败',
            5 => '疑难件',
            6 => '退件签收',
        ];
        $list = [];
        $list['status'] = $status_arr[$res['result']['deliverystatus'] ?? ''] ?? '';
        $list['no'] = $res['result']['number'];
        $list['title'] = $res['result']['expName'] ?? '';
        $list['site_url'] = $res['result']['expSite'] ?? '';
        $list['site_phone'] = $res['result']['expPhone'] ?? '';
        $list['phone'] = $res['result']['courierPhone'] ?? '';
        $list['take_time'] = $res['result']['takeTime'] ?? '';
        $list['logo'] = $res['result']['logo'] ?? '';
        $list['list'] = $new_list;
        $list['code'] = 0;
        if (in_array($list['status'], ['已签收', '退件签收'])) {
            cache($cache_key, $list);
        } else {
            cache($cache_key, $list, 300);
        }

        return $list;
    }

    /**
     * 解析标题
     */
    public static function parseTitle($remark = '')
    {
        if (!$remark) {
            return;
        }
        $status = '';
        if (
            strpos($remark, '签收') !== false || strpos($remark, '代收') !== false
            || strpos($remark, '代签收') !== false
            || strpos($remark, '快件已放在') !== false
            || strpos($remark, '已派送成功') !== false
            || strpos($remark, '已派送至本人') !== false
            || strpos($remark, '经客户同意') !== false

        ) {
            $status = '已签收';
        } else if (strpos($remark, '派件') !== false || strpos($remark, '派送') !== false) {
            $status = '派送中';
        } else if (
            strpos($remark, '发往') !== false || strpos($remark, '离开') !== false  || strpos($remark, '到达') !== false ||
            strpos($remark, '发件') !== false || strpos($remark, '到件') !== false || strpos($remark, '运输中') !== false
        ) {
            $status = '运输中';
        } else if (
            strpos($remark, '揽收') !== false || strpos($remark, '收取') !== false ||
            strpos($remark, '收件') !== false
        ) {
            $status = '已揽收';
        } else if (strpos($remark, '取消') !== false) {
            $status = '已取消';
        }
        return $status;
    }
}
