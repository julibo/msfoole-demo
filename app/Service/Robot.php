<?php
/**
 * 自助挂号终端机
 */

namespace App\Service;

use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Facade\Log;
use Julibo\Msfoole\Helper;
use Julibo\Msfoole\Channel;
use Julibo\Msfoole\Exception;
use App\Logic\HospitalApi;
use App\Logic\PaymentApi;
use App\Model\Order as OrderModel;
use App\Lib\Helper\Message;
use App\Validator\Feedback;

class Robot extends BaseServer
{
    private $hospitalApi;

    protected function init()
    {
        $this->hospitalApi = HospitalApi::getInstance();
    }

    /**
     * websocket 登陆
     * @param array $params
     * @return bool
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
        $user = $this->hospitalApi->getUser($cardno);
        return $user;
    }

    /**
     * 获取当日挂号记录
     * @param string $cardno
     * @return array
     */
    public function getTodayRegister(string $cardno)
    {
         $result = [];
         $response = $this->hospitalApi->apiClient('ghxx', ['kh'=>$cardno]);
         if (!empty($response) && !empty($response['item'])) {
             foreach ($response['item'] as $vo) {
                 if ($vo['yfsfy'] == "False") {
                     $vo['ghrq'] = date('Y-m-d', strtotime($vo['ghrq']));
                     $vo['hj'] = sprintf('￥%s', $vo['hj']);
                     array_push($result, $vo);
                 }
             }
         }
         return $result;
    }

    /**
     * 取消当日挂号
     * @param $cardno
     * @param $mzh
     * @return bool
     * @throws \Exception
     */
    public function cancelReg($cardno, $mzh)
    {
        if (empty($cardno) || empty($mzh)) {
            throw new \Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        # 取得订单
        $morder = OrderModel::getInstance()->getOrderByCode($mzh);
        if (empty($morder)) {
            throw new \Exception('该订单不存在', Feedback::$Exception['SERVICE_SQL_ERROR']['code']);
        }
        $this->hospitalApi->apiClient('ghdjzf', ['kh'=>$cardno, 'mzh' => $mzh]);
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
            throw new \Exception('快速退款失败，将转由人工处理', Feedback::$Exception['SERVICE_API_ERROR']['code']);
        }
        return true;
    }

    /**
     * 获取医院科室列表
     * @return array
     */
    public function getDepartment()
    {
        $result = [];
        $response = $this->hospitalApi->apiClient('ksxx');
        if (!empty($response) && !empty($response['item'])) {
            $result = $response['item'];
        }
        return $result;
    }

    /**
     * 获取科室号源列表
     * @param null $ksbm
     * @return array
     * @throws \Exception
     */
    public function getSourceList($ksbm = null)
    {
        $result = [];
        if (empty($ksbm)) {
            throw new \Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $response = $this->hospitalApi->apiClient('ysxx', ['ksbm'=>$ksbm]);
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
     * @throws Exception
     */
    public function createRegOrder($cardno, $ysbh, $bb, $zfje, $zfzl, $body, $token, $ip)
    {
        if (empty($cardno) || empty($ysbh) || empty($bb) || empty($zfje) || empty($zfzl)) {
            throw new \Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $orderData = OrderModel::getInstance()->createRegOrder($cardno, $ysbh, $bb, $zfje, $zfzl, $body, $ip, 1, 1, 1, $token);
        if ($orderData == false) {
            throw new Exception('订单创建失败', Feedback::$Exception['SERVICE_SQL_ERROR']['code']);
        }
        $orderData['time_start'] = date('YmdHis', strtotime($orderData['time_start']));
        $orderData['time_expire'] = date('YmdHis', strtotime($orderData['time_expire']));
        $weixinResult = PaymentApi::getInstance()->createOrder($orderData, $zfzl);
        $result = $weixinResult['code_img_url'];
        return $result;
    }

    /**
     * 挂号单处理
     * @param array $order
     */
    private function regOrderHandle(array $order)
    {
        $info = json_decode($order['info'], true);
        $regResult = $this->register($info, $order['out_trade_no'], $order['id']);
        if ($regResult) {
            $notice = [
                'type' => 1, // websocket广播
                'client' => $order['client'],
                'group' => 1,
                'result' => 1,
                'body' => ['mzh' => $regResult, 'lx' => 1],
                'mzh' => $regResult,
            ];
            Channel::instance()->push($notice);
            $sms = [
                'type' => 2,
                'class' => self::class,
                'method' => 'sendSms',
                'parameter' => ['cardno'=>$info['cardno'], 'content'=>'挂号成功，欢迎就诊']
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
    }

    /**
     * 门诊缴费处理
     * @param array $order
     */
    private function payOrderHandle(array $order)
    {
        $info = json_decode($order['info'], true);
        $payResult = $this->payment($info, $order['id']);
        if ($payResult) {
            $notice = [
                'type' => 1, // websocket广播
                'client' => $order['client'],
                'group' => 2,
                'result' => 1,
                'body' => ['skbs' => $payResult['skbs'], 'lx' => 2],
                'ksbs' => $payResult['skbs']
            ];
            Channel::instance()->push($notice);
            $sms = [
                'type' => 2,
                'class' => self::class,
                'method' => 'sendSms',
                'parameter' => ['cardno'=>$info['cardno'], 'content'=>'缴费成功，祝您早日康复！']
            ];
            Channel::instance()->push($sms);
        } else {
            $notice = [
                'type' => 1, // websocket广播
                'client' => $order['client'],
                'group' => 2,
                'result' => 0
            ];
            Channel::instance()->push($notice);
            $sms = [
                'type' => 2,
                'class' => static::class,
                'method' => 'sendSms',
                'parameter' => ['cardno'=>$info['cardno'], 'content'=>'缴费失败，该款项将按原路返回']
            ];
            Channel::instance()->push($sms);
        }
    }

    /**
     * 支付回调
     * @param $xml
     * @return string
     * @throws Exception
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
                            case 1: // 终端机挂号
                                $this->regOrderHandle($order);
                                break;
                            case 2: // 终端机缴费
                                $this->payOrderHandle($order);
                                break;
                            case 3: // web预约挂号
                                $this->saleOrderHandle($order);
                                break;
                            case 4: // 微信预约挂号
                                $this->wehcatRegHandle($order);
                                break;
                            case 5: // 微信门诊缴费
                                $this->wechatPayHandle($order);
                                break;
                            case 6: // 微信住院费预交
                                $this->wechatHospitalHandle($order);
                                break;
                            case 7: // 微信挂号
                                $this->wechatTodayHandle($order);
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
     */
    private function register(array $info, $orderNo, $orderID)
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
        $response = $this->hospitalApi->apiClient('ghdj', $content);
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
     * 门诊缴费
     * @param array $info
     * @param int $orderID
     * @return bool
     */
    private function payment(array $info, $orderID)
    {
        $result = false;
        $content = [
            "mzh" => $info['mzh'],
            "zfje" => $info['zfje'],
            "zfzl" => $info['zfzl'],
            "sjh" => $info['sjh']
        ];
        Log::info('payment:门诊缴费发起--{message}', ['message' => json_encode($content)]);
        $response = $this->hospitalApi->apiClient('mzsf', $content);
        if (!empty($response) && !empty($response['item'])) {
            Log::info('payment:门诊缴费成功--{message},返回记录--{response}', ['message' => json_encode($content), 'response'=>json_encode($response)]);
            OrderModel::getInstance()->updateOrderStatus($orderID, 2, $response['item']['skbs']);
            $result = $response['item'];
        } else {
            Log::info('payment:门诊缴费失败--{message},返回记录--{response}', ['message' => json_encode($content), 'response'=>json_encode($response)]);
            OrderModel::getInstance()->updateOrderStatus($orderID, 3);
        }
        return $result;
    }

    /**
     * 根据卡号发送短信
     * @param string $cardNo
     * @param string $content
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendSms($cardNo, $content)
    {
        $user = HospitalApi::getInstance()->getUser($cardNo);
        if (!empty($user) && !empty($content) && !empty($user['mobile']) && preg_match("/^1[3456789]\d{9}$/", $user['mobile'])) {
            Log::debug('sendSMS:向{mobile}发送短信：{message}', ['mobile' => $user['mobile'], 'message' => $content]);
            Message::sendSms($user['mobile'], $content);
        }
    }

    /**
     * 通过卡号查询缴费列表
     * @param string $cardNo
     * @return array
     */
    public function getPayment($cardNo)
    {
        $result = [];
        $response = $this->hospitalApi->apiClient('getjfmx', ['kh'=>$cardNo]);
        if (!empty($response) && !empty($response['item'])) {
            $result = $response['item'];
            foreach ($result as &$vo) {
                $vo['ghrq'] = date('Y-m-d', strtotime($vo['ghrq']));
                $vo['money'] = sprintf('￥%s', $vo['je']);
            }
        }
        return $result;
    }

    /**
     * 创建门诊缴费订单
     * @param string $cardNo 卡号
     * @param string $mzh 门诊号
     * @param float $zfje 支付金额
     * @param int $zfzl 支付种类
     * @param string $body 描述
     * @param string $ip IP地址
     * @param string $token 客户标识
     * @return mixed
     * @throws Exception
     */
    public function createPayOrder($cardNo, $mzh, $zfje, $zfzl, $body, $ip, $token)
    {
        if (empty($cardNo) || empty($mzh) || empty($zfje) || empty($zfzl) || empty($body) || empty($ip) || empty($token)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $orderData = OrderModel::getInstance()->createPayOrder($cardNo, $mzh, $zfje, $zfzl, $body, $ip, 1, 1, $token);
        if ($orderData == false) {
            throw new Exception('订单创建失败', Feedback::$Exception['SERVICE_SQL_ERROR']);
        }
        $orderData['time_start'] = date('YmdHis', strtotime($orderData['time_start']));
        $orderData['time_expire'] = date('YmdHis', strtotime($orderData['time_expire']));
        $payResult = PaymentApi::getInstance()->createOrder($orderData, $zfzl);
        $result = $payResult['code_img_url'];
        return $result;
    }

    /**
     * 取消门诊缴费
     * @param string $cardNo 卡号
     * @param string $orderID 单号
     * @param string $skbs 收款标识
     * @param float $zfje 支付金额
     * @return bool
     * @throws Exception
     */
    public function cancelPay($cardNo, $orderID, $skbs, $zfje)
    {
        if (empty($cardNo) || empty($orderID) || empty($skbs) || empty($zfje)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        # 取得订单
        $orderResult = OrderModel::getInstance()->getOrderByTradeNo($orderID);
        if (empty($orderResult) || $orderResult['user'] != $cardNo || $orderResult['total_fee'] != $zfje * 100 ||
            $orderResult['code'] != $skbs) {
            throw new Exception(Feedback::$Exception['SERVICE_AUTH_ERROR']['msg'], Feedback::$Exception['SERVICE_AUTH_ERROR']['code']);
        }
        $this->hospitalApi->apiClient('qxmzsf', ['skbs'=>$skbs, 'zfje' => $zfje]);
        $params = [
            'out_trade_no' => $orderID,
            'out_refund_no' => $orderID . 'R',
            'total_fee' => $orderResult['total_fee'],
            'refund_fee' => $orderResult['total_fee'],
            'refund_channel' => 'ORIGINAL',
            'nonce_str' => Helper::guid()
        ];
        $refundResult = PaymentApi::getInstance()->submitRefund($params);
        if ($refundResult) {
            OrderModel::getInstance()->updateOrderStatus($orderResult['id'], 5);
        } else {
            OrderModel::getInstance()->updateOrderStatus($orderResult['id'], 4);
            throw new Exception('快速退款失败，将转由人工处理', Feedback::$Exception['SERVICE_API_ERROR']['code']);
        }
        return true;
    }

    /**
     * 预约挂号
     * @param array $order
     * @throws Exception
     */
    private function saleOrderHandle(array $order)
    {
        $result = false;
        $info = json_decode($order['info'], true);
        $responseDj = $this->hospitalApi->apiClient('yydj', [
            'kh' => $info['kh'],
            'ysbh' => $info['ysbh'],
            'zzks' => $info['zzks'],
            'ghrq' => $info['ghrq'],
            'ghlb' => $info['ghlb'],
            'ysh_lx' => $info['ysh_lx'],
        ]);
        if (!empty($responseDj) && $responseDj['hybh']) {
            $responseQh = $this->hospitalApi->apiClient('yydj_qh', [
                'hybh' => $responseDj['hybh'],
                'sjh' => $order['out_trade_no'],
                'zfzl' => $info['zfzl'],
                'zfje' => $info['zfje']
            ]);
            if (!empty($responseQh) && $responseQh['mzh']) {
                $result = true;
            }
        }
        if ($result) {
            $sms = [
                'type' => 2,
                'class' => self::class,
                'method' => 'sendSms',
                'parameter' => ['cardno'=>$info['kh'], 'content'=>'预约挂号成功，欢迎按时就诊']
            ];
            Channel::instance()->push($sms);
            OrderModel::getInstance()->updateOrderStatus($order['id'], 2, $responseQh['mzh']);
        } else {
            $sms = [
                'type' => 2,
                'class' => static::class,
                'method' => 'sendSms',
                'parameter' => ['cardno'=>$info['kh'], 'content'=>'预约挂号失败，挂号费将按原路返回']
            ];
            Channel::instance()->push($sms);
            // 原路返回
            $params = [
                'out_trade_no' => $order['out_trade_no'],
                'out_refund_no' => $order['out_trade_no'] . 'R',
                'total_fee' => $order['total_fee'],
                'refund_fee' => $order['total_fee'],
                'refund_channel' => 'ORIGINAL',
                'nonce_str' => Helper::guid()
            ];
            $refundResult = PaymentApi::getInstance()->submitRefund($params);
            if ($refundResult) {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 5);
            } else {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 4);
                throw new Exception('快速退款失败，将转由人工处理', Feedback::$Exception['SERVICE_API_ERROR']['code']);
            }
        }
    }

    /**
     * 微信预约挂号处理
     * @param array $order
     */
    public function wehcatRegHandle(array $order)
    {
        // 完成预约挂号
        $result = false;
        $info = json_decode($order['info'], true);
        $responseDj = $this->hospitalApi->apiClient('yydj', [
            'kh' => $info['kh'],
            'ysbh' => $info['ysbh'],
            'zzks' => $info['zzks'],
            'ghrq' => $info['ghrq'],
            'ghlb' => $info['ghlb'],
            'ysh_lx' => $info['ysh_lx'],
        ]);
        if (!empty($responseDj) && $responseDj['hybh']) {
            $responseQh = $this->hospitalApi->apiClient('yydj_qh', [
                'hybh' => $responseDj['hybh'],
                'sjh' => $order['out_trade_no'],
                'zfzl' => $info['zfzl'],
                'zfje' => $info['zfje']
            ]);
            if (!empty($responseQh) && $responseQh['mzh']) {
                $result = true;
            }
        }
        if ($result) {
            OrderModel::getInstance()->updateOrderStatus($order['id'], 2, $responseQh['mzh']);
            // 推送模板消息
            if ($info['ysh_lx'] == 1) {
                $jzsj = $info['ghrq'] . " 上午";
            } else if ($info['ysh_lx'] == 2) {
                $jzsj = $info['ghrq'] . " 下午";
            } else {
                $jzsj = $info['ghrq'];
            }
            $openid = $info['openid'];
            $name = $info['name'];
            $cardNo = $info['kh'];
            $ksmc = $info['zzksmc'];
            $ysxm = $info['ysxm'];
            $mzh = $responseQh['mzh'];
            $url = sprintf('%s/?token=%s&path=%s&order=%s&cardno=%s',
                Config::get('wechat.baseurl'), $openid, 'regResult', $order['out_trade_no'], $info['kh']);
            Wechat::getInstance()->sendTemplateMessageOrder($openid, $url, $name, $cardNo, $ksmc, $ysxm, $jzsj, $mzh);
        } else {
            // 原路返回款项
            $params = [
                'out_trade_no' => $order['out_trade_no'],
                'out_refund_no' => $order['out_trade_no'] . 'R',
                'total_fee' => $order['total_fee'],
                'refund_fee' => $order['total_fee'],
                'refund_channel' => 'ORIGINAL',
                'nonce_str' => Helper::guid()
            ];
            $refundResult = PaymentApi::getInstance()->submitRefund($params);
            if ($refundResult) {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 5);
                $msg = '预约挂号失败，挂号费已原路返回，预计7个工作日到账。';
            } else {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 4);
                $msg = '预约挂号失败，快速退款失败，将转由人工处理，预计7个工作日到账。';
            }
            Wechat::getInstance()->sendCustomMessageText($info['openid'], $msg);
        }
    }

    /**
     * 微信门诊缴费
     * @param array $order
     */
    public function wechatPayHandle(array $order)
    {
        // 完成门诊缴费
        $info = json_decode($order['info'], true);
        $payResult = $this->payment($info, $order['id']);
        if ($payResult) {
            // 推送模板消息
            $openid = $info['openid'];
            $mzh = $info['mzh'];
            $skbs = $payResult['skbs'];
            $money = "￥". $info['zfje'];
            $name = $info['name'];
            $url = sprintf('%s/?token=%s&path=%s&mzh=%s&cardno=%s&skbs=%s',
                Config::get('wechat.baseurl'), $openid, 'payDetails', $mzh, $info['kh'], $skbs);
            Wechat::getInstance()->sendTemplateMessagePayment($openid, $url, $skbs, $money, $name);
        } else {
            // 原路返回款项
            $params = [
                'out_trade_no' => $order['out_trade_no'],
                'out_refund_no' => $order['out_trade_no'] . 'R',
                'total_fee' => $order['total_fee'],
                'refund_fee' => $order['total_fee'],
                'refund_channel' => 'ORIGINAL',
                'nonce_str' => Helper::guid()
            ];
            $refundResult = PaymentApi::getInstance()->submitRefund($params);
            if ($refundResult) {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 5);
                $msg = '门诊缴费失败，款项已原路返回，预计7个工作日到账。';
            } else {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 4);
                $msg = '门诊缴费失败，快速退款失败，将转由人工处理，预计7个工作日到账。';
            }
            Wechat::getInstance()->sendCustomMessageText($info['openid'], $msg);
        }
    }

    /**
     * 微信住院费预交
     * @param array $order
     */
    public function wechatHospitalHandle(array $order)
    {
        $info = json_decode($order['info'], true);
        $payResult = $this->payHospital($info, $order['id']);
        if ($payResult) {
            // 推送模板消息
            $openid = $info['openid'];
            $name = $info['name'];
            $cardNo = $info['kh'];
            $date = date('Y-m-d');
            $money = "￥". $info['zfje'];
            $orderNo = $info['sjh'];
            $url = sprintf('%s/?token=%s&path=%s&zyh=%s',
                Config::get('wechat.baseurl'), $openid, 'hospitalDetail', $info['zyh']);
            Wechat::getInstance()->sendTemplateHospital($openid, $url, $name, $cardNo, $date, $money, $orderNo);
        } else {
            // 原路返回款项
            $params = [
                'out_trade_no' => $order['out_trade_no'],
                'out_refund_no' => $order['out_trade_no'] . 'R',
                'total_fee' => $order['total_fee'],
                'refund_fee' => $order['total_fee'],
                'refund_channel' => 'ORIGINAL',
                'nonce_str' => Helper::guid()
            ];
            $refundResult = PaymentApi::getInstance()->submitRefund($params);
            if ($refundResult) {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 5);
                $msg = '住院费预交失败，款项已原路返回，预计7个工作日到账。';
            } else {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 4);
                $msg = '住院费预交失败，快速退款失败，将转由人工处理，预计7个工作日到账。';
            }
            Wechat::getInstance()->sendCustomMessageText($info['openid'], $msg);
        }
    }

    /**
     * 住院费预交
     * @param array $info
     * @param int $orderID
     * @return bool
     */
    private function payHospital(array $info, $orderID)
    {
        $result = false;
        $content = [
            "zyh" => $info['zyh'],
            "zfje" => $info['zfje'],
            "zfzl" => $info['zfzl'],
            "sjh" => $info['sjh']
        ];
        Log::info('payHospital:住院费预交发起--{message}', ['message' => json_encode($content)]);
        $response = $this->hospitalApi->apiClient('zyjk', $content);
        if (!empty($response) && !empty($response['jksjh'])) {
            Log::info('payHospital:住院费预交成功--{message},返回记录--{response}', ['message' => json_encode($content), 'response'=>json_encode($response)]);
            OrderModel::getInstance()->updateOrderStatus($orderID, 2, $response['jksjh']);
            $result = $response['jksjh'];
        } else {
            Log::info('payHospital:住院费预交失败--{message},返回记录--{response}', ['message' => json_encode($content), 'response'=>json_encode($response)]);
            OrderModel::getInstance()->updateOrderStatus($orderID, 3);
        }
        return $result;
    }

    /**
     * 微信挂号处理
     * @param array $order
     */
    public function wechatTodayHandle(array $order)
    {
        // 完成预约挂号
        $result = false;
        $info = json_decode($order['info'], true);
        $response = $this->hospitalApi->apiClient('ghdj', [
            'kh' => $info['kh'],
            'ysbh' => $info['ysbh'],
            'bb' => $info['bb'],
            'zfje' => $info['zfje'],
            'zfzl' => $info['zfzl'],
            'sjh' => $info['sjh'],
        ]);
        if (!empty($response) && !empty($response['item']) && !empty($response['item']['mzh'])) {
            $result = $response['item']['mzh'];
        }
        if ($result) {
            OrderModel::getInstance()->updateOrderStatus($order['id'], 2, $result);
            // 推送模板消息
            if ($info['bb'] == 2) {
                $jzsj = "当日上午";
            } else if ($info['bb'] == 3) {
                $jzsj = "当日下午";
            } else {
                $jzsj = "当日全天";
            }
            $openid = $info['openid'];
            $name = $info['name'];
            $cardNo = $info['kh'];
            $ksmc = $info['ksmc'];
            $ysxm = $info['ysxm'];
            $mzh = $result;
            $url = sprintf('%s/?token=%s&path=%s&order=%s&cardno=%s',
                Config::get('wechat.baseurl'), $openid, 'todayResults', $order['out_trade_no'], $info['kh']);
            Wechat::getInstance()->sendTemplateMessageToday($openid, $url, $name, $cardNo, $ksmc, $ysxm, $jzsj, $mzh);
        } else {
            // 原路返回款项
            $params = [
                'out_trade_no' => $order['out_trade_no'],
                'out_refund_no' => $order['out_trade_no'] . 'R',
                'total_fee' => $order['total_fee'],
                'refund_fee' => $order['total_fee'],
                'refund_channel' => 'ORIGINAL',
                'nonce_str' => Helper::guid()
            ];
            $refundResult = PaymentApi::getInstance()->submitRefund($params);
            if ($refundResult) {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 5);
                $msg = '预约挂号失败，挂号费已原路返回，预计7个工作日到账。';
            } else {
                OrderModel::getInstance()->updateOrderStatus($order['id'], 4);
                $msg = '预约挂号失败，快速退款失败，将转由人工处理，预计7个工作日到账。';
            }
            Wechat::getInstance()->sendCustomMessageText($info['openid'], $msg);
        }
    }
}
