<?php

namespace modules\payment\lib;


class AlipayNotify
{
    public static function run()
    {
        //支付宝公钥，账户中心->密钥管理->开放平台密钥，找到添加了支付功能的应用，根据你的加密类型，查看支付宝公钥
        $alipayPublicKey = get_config('alipay_public_key');

        $aliPay = new AlipayService($alipayPublicKey);
        //验证签名
        $result = $aliPay->rsaCheck($_POST, $_POST['sign_type']);
        if ($result === true && $_POST['trade_status'] == 'TRADE_SUCCESS') {
            $order_num = $_POST['out_trade_no'];
            $total_amount = $_POST['total_amount'];
            $data = [
                'order_num' => $order_num,
                'transaction_id' => $_POST['trade_no'],
                'payment_method' => 'alipay',
                'currency' => 'CNY',
                'amount' => $total_amount,
                'openid' => $_POST['buyer_user_id'],
                'paid_at' => $_POST['gmt_payment'],
                'app_id' => '',
                'type' => 'alipay',
            ];
            add_log('支付宝回调', $data, 'debug');
            do_action('payment_success', $data);
            echo 'success';
            exit();
        }
        echo 'error';
        exit();
    }
}

class AlipayService
{
    //支付宝公钥
    protected $alipayPublicKey;
    protected $charset;

    public function __construct($alipayPublicKey)
    {
        $this->charset = 'utf8';
        $this->alipayPublicKey = $alipayPublicKey;
    }

    /**
     *  验证签名
     **/
    public function rsaCheck($params)
    {
        $sign = $params['sign'];
        $signType = $params['sign_type'];
        unset($params['sign_type']);
        unset($params['sign']);
        return $this->verify($this->getSignContent($params), $sign, $signType);
    }

    function verify($data, $sign, $signType = 'RSA')
    {
        $pubKey = $this->alipayPublicKey;
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, version_compare(PHP_VERSION, '5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }
        //        if(!$this->checkEmpty($this->alipayPublicKey)) {
        //            //释放资源
        //            openssl_free_key($res);
        //        }
        return $result;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    public function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, $this->charset);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }
}
