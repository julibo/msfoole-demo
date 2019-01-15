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
use Julibo\Msfoole\Facade\Log;
use Julibo\Msfoole\Exception;

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

    public static function getInstance($force = true) : self
    {
        if (is_null(self::$instance) || $force) {
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

    public function createOrder(array $param, int $service)
    {
        try {
            Log::info('Refund:订单创建开始：{message}', ['message'=>json_encode($param)]);
            $this->reqHandler->setReqParams($param, array('method'));
            switch ($service) {
                case 1:
                    $this->reqHandler->setParameter('service','pay.alipay.native');
                    break;
                case 2:
                    $this->reqHandler->setParameter('service','pay.weixin.native');
                    break;
                case 3:
                    $this->reqHandler->setParameter('service', 'pay.weixin.jspay');
                    break;
            }
            $this->reqHandler->setParameter('mch_id',$this->cfg['mchid']); //必填项，商户号，由威富通分配
            $this->reqHandler->setParameter('version',$this->cfg['version']);
            $this->reqHandler->setParameter('sign_type',$this->cfg['sign_type']);
            $this->reqHandler->setParameter('op_user_id',$this->cfg['op_user_id']);//必填项，操作员帐号,默认为商户号
            $this->reqHandler->setParameter('limit_credit_pay', $this->cfg['limit_credit_pay']);

            //通知地址，必填项，接收威富通通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
            $this->reqHandler->setParameter('notify_url',$this->cfg['notify_url']);
            $this->reqHandler->setParameter('nonce_str', $param['nonce_str']); //随机字符串，必填项，不长于 32 位
            $this->reqHandler->createSign();//创建签名
            $data = Utils::toXml($this->reqHandler->getAllParameters());
            $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);
            if($this->pay->call()) {
                $this->resHandler->setContent($this->pay->getResContent());
                $this->resHandler->setKey($this->reqHandler->getKey());
                if($this->resHandler->isTenpaySign()) {
                    // 当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                    if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0) {
                        if ($service == 3) {
                            return array('pay_info'=>$this->resHandler->getParameter('pay_info'),
                                'token_id'=>$this->resHandler->getParameter('token_id'));
                        } else {
                            return array('code_img_url'=>$this->resHandler->getParameter('code_img_url'),
                                'code_url'=>$this->resHandler->getParameter('code_url'),
                                'code_status'=>$this->resHandler->getParameter('code_status'),
                                'type'=>$this->reqHandler->getParameter('service'));
                        }
                    }else{
                        throw new Exception($this->resHandler->getParameter('err_msg'), 540);
                    }
                }
                throw new Exception($this->resHandler->getParameter('message'), 550);
            }else{
                throw new Exception($this->pay->getErrInfo(), 560);
            }
        } catch (\Throwable $e) {
            Log::info('PayCode:二维码创建失败：message-{message},code--{code}', ['message'=>$e->getMessage(), 'code'=>$e->getCode()]);
            return false;
        }
    }

    public function callback($xml)
    {
        $this->resHandler->setContent($xml);
        $this->resHandler->setKey($this->cfg['key']);
        // 日志记录
        Log::info('PayCall:接口回调收到通知参数：{message}', ['message'=>json_encode($this->resHandler->getAllParameters())]);
        if($this->resHandler->isTenpaySign()) {
            if($this->resHandler->getParameter('status') === '0' && $this->resHandler->getParameter('result_code') === '0' &&
                $this->resHandler->getParameter('pay_result') === '0') {
                // 查询对应的订单
                return [
                    'orderID' =>  $this->resHandler->getParameter('out_trade_no'),
                    'totalFee' => $this->resHandler->getParameter('total_fee')
                ];
            }else{
                return false;
            }
        } else {
            return false;
        }
    }

    public function submitRefund($params)
    {
        try {
            Log::info('Refund:退款提交开始：{message}', ['message'=>json_encode($params)]);
            $this->reqHandler->setReqParams($params,array('method'));
            $reqParam = $this->reqHandler->getAllParameters();
            if(empty($reqParam['transaction_id']) && empty($reqParam['out_trade_no'])){
                throw new \Exception('请输入商户订单号!', 500);
            }
            $this->reqHandler->setParameter('version',$this->cfg['version']);
            $this->reqHandler->setParameter('service','unified.trade.refund');//接口类型：unified.trade.refund
            $this->reqHandler->setParameter('mch_id',$this->cfg['mchid']);//必填项，商户号，由威富通分配
            $this->reqHandler->setParameter('nonce_str', $params['nonce_str']);//随机字符串，必填项，不长于 32 位
            $this->reqHandler->setParameter('op_user_id',$this->cfg['op_user_id']);//必填项，操作员帐号,默认为商户号
            $this->reqHandler->setParameter('sign_type',$this->cfg['sign_type']);

            $this->reqHandler->createSign();//创建签名
            $data = Utils::toXml($this->reqHandler->getAllParameters());//将提交参数转为xml，目前接口参数也只支持XML方式

            $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);
            if($this->pay->call()){
                $this->resHandler->setContent($this->pay->getResContent());
                $this->resHandler->setKey($this->reqHandler->getKey());
                if($this->resHandler->isTenpaySign()){
                    if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                        $res = array('transaction_id'=>$this->resHandler->getParameter('transaction_id'),
                            'out_trade_no'=>$this->resHandler->getParameter('out_trade_no'),
                            'out_refund_no'=>$this->resHandler->getParameter('out_refund_no'),
                            'refund_id'=>$this->resHandler->getParameter('refund_id'),
                            'refund_channel'=>$this->resHandler->getParameter('refund_channel'),
                            'refund_fee'=>$this->resHandler->getParameter('refund_fee'),
                            'coupon_refund_fee'=>$this->resHandler->getParameter('coupon_refund_fee'));
                        Log::info('Refund:退款提交成功：{message}', ['message'=>json_encode($res)]);
                        return true;
                    }else{
                        throw new Exception($this->resHandler->getParameter('err_msg'), 510);
                    }
                }
                throw new Exception($this->resHandler->getParameter('message'), 520);
            }else{
                throw new Exception($this->pay->getErrInfo(), 530);
            }
        } catch (\Throwable $e) {
            Log::info('Refund:退款提交失败：message-{message},code--{code}', ['message'=>$e->getMessage(), 'code'=>$e->getCode()]);
            return false;
        }
    }
}
