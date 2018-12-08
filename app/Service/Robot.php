<?php
/**
 * 自助挂号终端机
 */

namespace App\Service;

use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Facade\Log;
use Julibo\Msfoole\Helper;
use Julibo\Msfoole\Channel;
use App\Logic\HospitalApi;
use App\Logic\PaymentApi;
use App\Model\Order as OrderModel;
use App\Lib\Helper\Message;

class Robot extends BaseServer
{

    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * websocket 登陆
     * @param array $params
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login(array $params)
    {
        if (empty($params['token']) || empty($params['cardno']) || empty($params['timestamp']) || empty($params['sign'])) {
            return false;
        }
        $token = $params['token'];
        $cardno = $params['cardno'];
        $timestamp = $params['timestamp'];
        $sign = $params['sign'];

        if ($timestamp + 600 < time() ||  $timestamp - 600 > time() || Config::get('msfoole.websocket.vi') != $token) {
            return false;
        }
        if (Config::get('msfoole.websocket.sign') == null) {
            $pass = base64_encode(openssl_encrypt($cardno.$timestamp,"AES-128-CBC", Config::get('msfoole.websocket.key'),OPENSSL_RAW_DATA, $token));
            if ($pass != $sign) {
                return false;
            }
        } else {
            if (Config::get('msfoole.websocket.sign') != $sign) {
                return false;
            }
        }
        $user = HospitalApi::getInstance()->getUser($cardno);
        return $user;
    }

    /**
     * 获取当日挂号记录
     * @param string $cardno
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTodayRegister(string $cardno)
    {
         $result = [];
         $response = HospitalApi::getInstance()->apiClient('ghxx', ['kh'=>$cardno]);
         if (!empty($response) && !empty($response['item'])) {
             foreach ($response['item'] as $vo) {
                 if ($vo['yfsfy'] == "False")
                     array_push($result, $vo);
             }
         }
         return $result;
    }

    /**
     * 取消当日挂号
     * @param $cardno
     * @param null $mzh
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cancelReg($cardno, $mzh = null)
    {
        if (empty($cardno) || empty($mzh)) {
            throw new \Exception('缺少必要的参数', 200);
        }
        # 取得订单
        $morder = OrderModel::getInstance()->getOrderByCode($mzh);
        if (empty($morder)) {
            throw new \Exception('订单不存在', 210);
        }
        HospitalApi::getInstance()->apiClient('ghdjzf', ['kh'=>$cardno, 'mzh' => $mzh]);
        $params = [
            'out_trade_no' => $morder['out_trade_no'],
            'out_refund_no' => $morder['out_trade_no'] . 'R',
            'total_fee' => $morder['total_fee'],
            'refund_fee' => $morder['total_fee'],
            'refund_channel' => 'ORIGINAL',
            'nonce_str' => Helper::guid()
        ];
        $refundResult = PaymentApi::getInstance()->submitRefund($params);
        if ($refundResult) {
            OrderModel::getInstance()->updateOrderStatus($morder['id'], 5);
        } else {
            OrderModel::getInstance()->updateOrderStatus($morder['id'], 4);
            throw new \Exception('快速退款失败，将转由人工处理', 220);
        }
        return true;
    }

    /**
     * 获取医院科室列表
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDepartment() : array
    {
        $result = [];
        $response = HospitalApi::getInstance()->apiClient('ksxx');
        if (!empty($response) && !empty($response['item'])) {
            $result = $response['item'];
        }
        return $result;
    }

    /**
     * 获取科室号源列表
     * @param null $ksbm
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSourceList($ksbm = null) : array
    {
        $result = [];
        if (empty($ksbm)) {
            throw new \Exception('缺少必要的参数', 200);
        }
        $response = HospitalApi::getInstance()->apiClient('ysxx', ['ksbm'=>$ksbm]);
        if (!empty($response) && !empty($response['item'])) {
            $result = $response['item'];
        }
        return $result;
    }

    /**
     * 创建挂号订单
     * 先去创建本地订单，再来获得支付二维码
     * @param $cardno 卡号
     * @param $ysbh 医生编号
     * @param $bb 班次
     * @param $zfje 挂号费
     * @param $zfzl 支付方式
     * @param $body 订单描述
     * @param $token 用户标识
     * @param $ip 用户IP
     * @return mixed
     * @throws \Exception
     */
    public function createRegOrder($cardno, $ysbh, $bb, $zfje, $zfzl, $body, $token, $ip)
    {
        if (empty($cardno) || empty($ysbh) || empty($bb) || empty($zfje) || empty($zfzl)) {
            throw new \Exception('缺少必要的参数', 200);
        }
        $orderData = OrderModel::getInstance()->createRegOrder($cardno, $ysbh, $bb, $zfje, $zfzl, $body, $ip, 1, 1, 1, $token);
        if ($orderData == false) {
            throw new \Exception('订单创建失败', 210);
        }
        // $orderData['mch_create_ip'] = '114.215.190.171';
        $orderData['time_start'] = date('YmdHis', strtotime($orderData['time_start']));
        $orderData['time_expire'] = date('YmdHis', strtotime($orderData['time_expire']));
        $weixinResult = PaymentApi::getInstance()->createOrder($orderData, $zfzl);
        $result = $weixinResult['code_img_url'];
        return $result;
    }

    /**
     * 支付回调
     * @param $xml
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function callbackWFT($xml)
    {
        $payRes = PaymentApi::getInstance()->callback($xml);
        if ($payRes) {
            $order = OrderModel::getInstance()->getOrderByTradeNo($payRes['orderID']);
            if ($order && $order['total_fee'] == $payRes['totalFee']) {
                if ($order['status'] == 0) {
                    // 更改订单状态
                    $updateResult = OrderModel::getInstance()->updateOrderStatus($order['id'], 1);
                    if ($updateResult) {
                        switch ($order['group']) {
                            case 1:// 挂号
                                $info = json_decode($order['info'], true);
                                $regResult = $this->register($info, $payRes['orderID'], $order['id']);
                                if ($regResult) {
                                    $notice = [
                                        'type' => 1, // websocket广播
                                        'client' => $order['client'],
                                        'group' => 1,
                                        'result' => 1,
                                        'title' => '挂号费',
                                        'amount' => '11.00',
                                        'office' => '科室',
                                        'doctor' => '杨爱国',
                                        'mzh' => $regResult,
                                    ];
                                    Channel::instance()->push($notice);
                                    $sms = [
                                        'type' => 2,
                                        'class' => self::class,
                                        'method' => 'sendSms',
                                        'parameter' => ['cardno'=>$info['cardno'], 'content'=>'挂号成功，请按时就诊']
                                    ];
                                    Channel::instance()->push($sms);
                                } else {
                                    $notice = [
                                        'type' => 1, // websocket广播
                                        'client' => $order['client'],
                                        'group' => 1,
                                        'result' => 0
                                    ];
                                    Channel::instance()->push($notice);
                                    $sms = [
                                        'type' => 2,
                                        'class' => static::class,
                                        'method' => 'sendSms',
                                        'parameter' => ['cardno'=>$info['cardno'], 'content'=>'挂号失败，挂号费将按原路返回']
                                    ];
                                    Channel::instance()->push($sms);
                                }
                            case 2:// 缴费
                                break;
                        }
                        return 'success';
                    }
                } else {
                    return 'success';
                }
            }
        }
    }

    /**
     * 预约挂号
     * @param array $info
     * @param string $orderNo
     * @param int $orderID
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function register(array $info, string $orderNo, int $orderID)
    {
        $result = false;
        $content = [
            "kh" => $info['cardno'],
            "ysbh" => $info['ysbh'],
            "bb" => $info['bb'],
            "zfje" => $info['zfje'],
            "zfzl" => $info['zfzl'],
            "sjh" => $orderNo
        ];
        Log::info('register:预约挂号发起--{message}', ['message' => json_encode($content)]);
        $response = HospitalApi::getInstance()->apiClient('ghdj', $content);
        if (!empty($response['item']['mzh'])) {
            Log::info('register:预约挂号成功--{message}', ['message' => json_encode($content)]);
            OrderModel::getInstance()->updateOrderStatus($orderID, 2, $response['item']['mzh']);
            $result = $response['item']['mzh'];
        } else {
            Log::info('register:预约挂号失败--{message}', ['message' => json_encode($content)]);
            OrderModel::getInstance()->updateOrderStatus($orderID, 3);
        }
        return $result;
    }

    /**
     * 根据卡号发送短信
     * @param string $cardNo
     * @param string $content
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendSms(string $cardNo, string $content)
    {
        $user = HospitalApi::getInstance()->getUser($cardNo);
        if (!empty($user) && !empty($content) && !empty($user['mobile']) && preg_match("/^1[3456789]\d{9}$/", $user['mobile'])) {
            Log::info('sendSMS:向{mobile}发送短信：{message}', ['mobile' => $user['mobile'], 'message' => $content]);
            Message::sendSms($user['mobile'], $content);
        }
    }

}

