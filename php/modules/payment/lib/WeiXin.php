<?php

/**
 * 支付
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */

namespace modules\payment\lib;

use EasyWeChat\Pay\Application;

class WeiXin
{
    public static $returnUrl;
    public static $cancelUrl;
    protected static $app;
    protected static $config;

    /**
     * app_id
     */
    public static function getAppId()
    {
        $app_id = get_config('minapp_app_id');
        return $app_id;
    }
    /**
     * H5 下单
     * H5支付是指商户在微信客户端外的移动端网页展示商品或服务，用户在前述页面确认使用微信支付时，商户发起本服务呼起微信客户端进行支付。
     * @param array $req
     * @return void
     */
    public static function h5($req = [])
    {
        return self::common($req, 'h5');
    }
    /**
     * Native 下单
     * Native支付适用于PC网站、实体店单品或订单、媒体广告支付等场景
     * @param array $req
     * @return void
     */
    public static function native($req = [])
    {
        return self::common($req, 'native');
    }
    /**
     * Native 下单
     *
     * @param array $req
     * @return void
     */
    public static function jsapi($req = [])
    {
        return self::common($req, 'jsapi');
    }

    /**
     *  JSAPI下单
     *  native
     *  h5
     *  app
     */
    public static function common($req = [
        'openid' => '',
        'body' => '',
        'total_fee' => '',
        'order_num' => '',
        'trade_type' => 'NATIVE',
    ], $pay_drive = 'jsapi')
    {
        self::init();
        $trade_type = $req['trade_type'] ?: '';
        if ($trade_type) {
            $pay_drive = $trade_type;
        }
        $body = $req["body"];
        $yuan = $req["total_fee"];
        $openid = $req['openid'] ?? '';
        $total_fee = $req["total_fee"] * 100;
        if (!$total_fee) {
            return;
        }
        $order_num = $req['order_num'];
        /**
         * https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_1.shtml
         * 如果要改造分帐、开票需要在这里生成支付订单时处理
         */
        $appid = self::getAppId();
        $pay_config = [
            'appid' => $appid,
            'mchid' => self::$config['mch_id'],
            'out_trade_no' => $order_num,
            'notify_url' => host() . url('/payment/api-weixin/notify', ['order_num' => $order_num]),
            "amount" => [
                "total" => (int) $total_fee,
                "currency" => get_config("currency"),
            ],
            //'re_user_name'=> '',
            'description' => $body,
            //交易结束时间
            //'time_expire'=>'',
        ];
        if ($openid) {
            $pay_config['payer']['openid'] = $openid;
        }
        //H5
        if ($pay_drive == 'h5') {
            unset($pay_config['payer']);
            $pay_config["scene_info"] = [
                "payer_client_ip" => get_ip(),
                "h5_info" => [
                    "type" => "Wap",
                ],
            ];
        }

        $res = self::$app->getClient()->postJson('v3/pay/transactions/' . $pay_drive, $pay_config)->toArray(false);

        if (isset($res['code_url'])) {
            //生成支付二维码
            // $res['code_url']
            //header('Content-Type: '.$result->getMimeType());
            //echo $result->getString();
            return $res['code_url'];
        }
        //除了H5其他的都有prepay_id
        if (isset($res['prepay_id']) || isset($res['h5_url'])) {
            $prepayId = $res['prepay_id'];
            $signType = 'RSA'; // 默认RSA，v2要传MD5
            if ($pay_drive != 'h5') {
                $config = self::$app->getUtils()->buildBridgeConfig($prepayId, $appid, $signType);
            }
            //H5返回支付URL
            if ($pay_drive == 'h5') {
                return $res['h5_url'];
            }
            return ['data' => $config, 'res' => $res, 'code' => 0];
        } else {
            return ['msg' => $res['message'], 'res' => $res, 'code' => 250];
        }
    }
    /**
     * 退款查寻
     */
    public static function refundQuery($out_refund_no)
    {
        self::init();
        $res = self::$app->getClient()->get("v3/refund/domestic/refunds/{$out_refund_no}", [])->toArray();
        if ($res['status'] == 'SUCCESS') {
            $success_time = $res['success_time'];
            if ($success_time) {
                $success_time = date("Y-m-d H:i:s", strtotime($success_time));
            } else {
                $success_time = now();
            }
        }
        return $res;
    }
    /**
     * 退款
     * totalFee  总金额
     * refundFee 退款金额
     * https://easywechat.com/6.x/pay/examples.html
     * https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_9.shtml
     * 
     */
    public static function refund($out_trade_no, $total_fee, $refund_amount, $currency, $refund_desc = '退款')
    {
        self::init();
        $fee = $refund_amount;
        $out_refund_no = 'T' . create_order_num();
        $totalFee = (int) ($total_fee * 100);
        $refundFee = (int) ($refund_amount * 100);
        $opt = [
            'out_refund_no' => $out_refund_no,
            'out_trade_no' => $out_trade_no,
            'amount' => [
                'refund' => $refundFee,
                'total' => $totalFee,
                "currency" => $currency,
            ],
            'reason' => $out_trade_no,
        ];
        $order_num = $out_trade_no;
        $res = self::$app->getClient()->postJson("v3/refund/domestic/refunds", $opt)->toArray(false);
        $status = $res['status'] ?? '';
        $message = $res['message'] ?? '';
        if ($status == 'PROCESSING') {
            $res['code'] = 0;
        } else {
            $res['code'] = 250;
            $res['message'] = $message;
        }
        return $res;
    }
    /**
     * 企业付款到零钱 
     * 不是 【企业转帐到零钱】
     * 这是两个产品，新开的微信支付只有 企业付款到零钱。
     */
    public static function transfer($openid, $order_num, $amount = 0.01, $desc = '转帐')
    {
        self::init();
        $response = self::$app->getClient()->post('/mmpaymkttransfers/promotion/transfers', [
            'body' => [
                'mch_appid' => self::getAppId(), //注意在配置文件中加上app_id
                'mchid' => self::$config['mch_id'], //商户号
                'partner_trade_no' => $order_num, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                'openid' => $openid, //用户openid
                'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                //'re_user_name' => '用户真实姓名',                  // 如果 check_name 设置为 FORCE_CHECK 则必填用户真实姓名
                'amount' => (int) ($amount * 100), //金额
                'desc' => $desc, // 企业付款操作说明信息。必填
            ],
        ]);
        $res = $response->toArray();
        return $res;
    }

    /** 
     * 支付异步通知 
     */
    public static function notify()
    {
        self::init();
        $app = self::$app;
        $server = $app->getServer();
        $server->handlePaid(function (\EasyWeChat\Pay\Message $message, \Closure $next) use ($app) {
            $out_trade_no = $message->out_trade_no;
            $transaction_id = $message->transaction_id;
            $openid = $message->payer['openid'] ?? '';
            $cache_key = "wx_pay:" . $transaction_id;
            $exists = cache($cache_key);
            if ($exists) {
                add_log("微信支付重复推送", 'transaction_id:' . $transaction_id, 'error');
                return;
            }
            cache($cache_key, 1, 86400 * 2);
            try {
                $app->getValidator()->validate($app->getRequest());
                self::query($out_trade_no);
            } catch (\Exception $e) {
                add_log('支付异步通知异常', $e->getMessage(), 'error');
            }
            return $next($message);
        });
        return $server->serve();
    }
    /***
     *  查询订单（商户订单号） 
     */
    public static function query($out_trade_no)
    {
        self::init();
        $response = self::$app->getClient()->get("v3/pay/transactions/out-trade-no/{$out_trade_no}", [
            'query' => [
                'mchid' => self::$app->getMerchant()->getMerchantId(),
            ],
        ]);
        $res = $response->toArray();
        if (strtoupper($res['trade_state']) == 'SUCCESS') {
            $success_time = $res['success_time'] ?? "";
            if ($success_time) {
                $success_time = strtotime($success_time);
            } else {
                $success_time = time();
            }
            $data = [
                'order_num' => $out_trade_no,
                'transaction_id' => $res['transaction_id'],
                'payment_method' => 'weixin',
                'currency' => $res['amount']['currency'],
                'amount' => bcdiv($res['amount']['total'], 100, 2),
                'openid' => $res['payer']['openid'],
                'paid_at' => $success_time,
                'app_id' => $res['appid'],
                'type' => 'weixin',
            ];
            add_log('微信支付查寻', $data, 'debug');
            do_action('payment_success', $data);
        }
        return $res;
    }
    /**
     * 扫用户付款码
     * https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1
     * $res = get_pack('pay.weixin', 'pos', [[
     *    'order_num' => $order_num,
     *    'total_fee' => $total_fee,
     *    'auth_code' => $auth_code,
     *    'body' => $body,
     * ]]);
     */
    public static function pos($req = [])
    {
        self::init();
        $order_num = $req['order_num'];
        $total_fee = $req['total_fee'];
        $auth_code = $req['auth_code'];
        $body = $req['body'];
        //扫码设备
        $device_info = $req['device_info'] ?: "sn2024";
        $appid = self::getAppId();
        $total_fee = bcmul($total_fee, 100, 2);
        $ip = get_ip();
        $pay_config = [
            'appid' => self::getAppId(),
            'mch_id' => self::$config['mch_id'],
            'device_info' => $device_info,
            'body' => $body,
            'out_trade_no' => $order_num,
            'total_fee' => (int) $total_fee,
            "spbill_create_ip" => $ip,
            'auth_code' => $auth_code,
        ];
        if (!$auth_code) {
            return false;
        }
        $res = self::$app->getClient()->post('/pay/micropay', [
            'body' => $pay_config,
        ])->toArray(false);
        if ($res['return_code'] == 'SUCCESS') {
            $paid_time = $res['time_end'];
            if ($paid_time) {
                $paid_time = $paid_time;
            } else {
                $paid_time = time();
            }
            $data = [
                'order_num' => $order_num,
                'transaction_id' => $res['transaction_id'],
                'payment_method' => 'weixin',
                'currency' => get_config("currency"),
                'amount' => floatval(bcmul($res['total_fee'], 100, 2)),
                'openid' => $res['payer']['openid'],
                'paid_at' => $paid_time,
                'app_id' => $res['appid'],
            ];
            add_action('payment_success', $data);
            $res['code'] = 0;
        } else {
            $res['code'] = 250;
        }
        return $res;
    }

    public static function init()
    {
        self::$returnUrl = url('payment/weixin/return');
        $mch_id = get_config('payment_config_weixin_mch_id');
        if (!$mch_id) {
            throw new \Exception('请先配置微信支付');
        }
        $mch_id = (string)$mch_id;
        $config = [
            'mch_id' => $mch_id,
            // 商户证书
            'private_key' => PATH . get_config('payment_config_weixin_key_path'),
            'certificate' => PATH . get_config('payment_config_weixin_cert_path'),
            // v3 API 秘钥
            'secret_key' => PATH . get_config('payment_config_weixin_secret_key'),
            // v2 API 秘钥
            'v2_secret_key' => PATH . get_config('payment_config_weixin_v2_secret_key'),
            // 平台证书：微信支付 APIv3 平台证书，需要使用工具下载
            // 下载工具：https://github.com/wechatpay-apiv3/CertificateDownloader
            'platform_certs' => [
                // 请使用绝对路径
                PATH . get_config('payment_config_weixin_platform_cert'),

            ],
            /**
             * 接口请求相关配置，超时时间等，具体可用参数请参考：
             * https://github.com/symfony/symfony/blob/5.3/src/Symfony/Contracts/HttpClient/HttpClientInterface.php
             */
            'http' => [
                'throw' => true, // 状态码非 200、300 时是否抛出异常，默认为开启
                'timeout' => 5.0,
                // 'base_uri' => 'https://api.mch.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
            ],
        ];
        $app = new Application($config);
        self::$app = $app;
        self::$config = $config;
    }
}
