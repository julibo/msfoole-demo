<?php
/**
 * 扫码支付API
 */

namespace App\Logic;

use App\Lib\Pay\RequestHandler;
use App\Lib\Pay\ClientResponseHandler;
use App\Lib\Pay\PayHttpClient;
use App\Lib\Pay\Utils;
use Julibo\Msfoole\Facade\Config;

class PaymentApi
{
    private static $instance;

    private $resHandler = null;
    private $reqHandler = null;
    private $pay = null;
    private $cfg = null;

    public function __construct()
    {
        $this->Request();
    }

    public static function getInstance() : self
    {
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    public function Request()
    {
        $this->resHandler = new ClientResponseHandler();
        $this->reqHandler = new RequestHandler();
        $this->pay = new PayHttpClient();
        $this->cfg = Config::get('pay.weifutong');
        $this->reqHandler->setGateUrl($this->cfg['url']);

        $sign_type = $this->cfg['sign_type'];

        if ($sign_type == 'MD5') {
            $this->reqHandler->setKey($this->cfg['key']);
            $this->resHandler->setKey($this->cfg['key']);
            $this->reqHandler->setSignType($sign_type);
        } else if ($sign_type == 'RSA_1_1' || $sign_type == 'RSA_1_256') {
            $this->reqHandler->setRSAKey($this->cfg['private_rsa_key']);
            $this->resHandler->setRSAKey($this->cfg['public_rsa_key']);
            $this->reqHandler->setSignType($sign_type);
        }
    }

    public function createOrder($param)
    {
        $this->reqHandler->setReqParams($param,array('method'));
        $this->reqHandler->setParameter('service','pay.weixin.native');
        $this->reqHandler->setParameter('mch_id',$this->cfg['mchid']); //必填项，商户号，由威富通分配
        $this->reqHandler->setParameter('version',$this->cfg['version']);
        $this->reqHandler->setParameter('sign_type',$this->cfg['sign_type']);
        // $this->reqHandler->setParameter('limit_credit_pay', '1');

        //通知地址，必填项，接收威富通通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        $this->reqHandler->setParameter('notify_url',$this->cfg['notify_url']);
        $this->reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand())); //随机字符串，必填项，不长于 32 位
        $this->reqHandler->createSign();//创建签名

        $data = Utils::toXml($this->reqHandler->getAllParameters());
        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);
        if($this->pay->call()) {
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            if($this->resHandler->isTenpaySign()) {
                //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0) {
                    echo json_encode(array('code_img_url'=>$this->resHandler->getParameter('code_img_url'),
                        'code_url'=>$this->resHandler->getParameter('code_url'),
                        'code_status'=>$this->resHandler->getParameter('code_status'),
                        'type'=>$this->reqHandler->getParameter('service')), JSON_UNESCAPED_SLASHES);
                }else{
                    echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$this->resHandler->getParameter('err_code').' Error Message:'.$this->resHandler->getParameter('err_msg')));
                }
            }
            echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$this->resHandler->getParameter('status').' Error Message:'.$this->resHandler->getParameter('message')));
        }else{
            echo json_encode(array('status'=>500,'msg'=>'Response Code:'.$this->pay->getResponseCode().' Error Info:'.$this->pay->getErrInfo()));
        }

    }

    public function callback($xml)
    {
        $this->resHandler->setContent($xml);
        $this->resHandler->setKey($this->cfg['key']);
        if($this->resHandler->isTenpaySign()) {

            if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                //echo $this->resHandler->getParameter('status');
                // 11;
                //更改订单状态

                Utils::dataRecodes('接口回调收到通知参数',$this->resHandler->getAllParameters());
                echo 'success';
            }else{
                Utils::dataRecodes('接口回调收到通知参数failure1');
                echo 'failure1';
            }
        } else {
            Utils::dataRecodes('接口回调收到通知参数failure2');
            echo 'failure2';
        }
    }


}