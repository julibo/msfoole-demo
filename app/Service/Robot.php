<?php
/**
 * 自助挂号终端机
 */

namespace App\Service;

use App\Logic\HospitalApi;
use App\Logic\PaymentApi;
use Julibo\Msfoole\Facade\Config;
use App\Model\Order as OrderModel;
use App\Model\Bill as BillModel;
use Julibo\Msfoole\Facade\Log;
use Julibo\Msfoole\Helper;

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
//         $result = [];
//         $response = HospitalApi::getInstance()->apiClient('ghxx', ['kh'=>$cardno]);
//         if (!empty($response) && !empty($response['item'])) {
//             foreach ($response['item'] as $vo) {
//                 if ($vo['yfsfy'] == "False")
//                     array_push($result, $vo);
//             }
//         }
//         return $result;
        $response = '{"item":[{"mzh":"1807240010","yfsfy":"False","hj":"11","ysxm":"系统用户1","ysbm":"1","ydzf":"5.00","ghrq":"2018-11-25 12:28:02","ghlb":"主任医师","dabh":"15928089191","byxm":"杨刚"},{"mzh":"1807240012","yfsfy":"False","hj":"11","ysxm":"孙中军","ysbm":"3","ydzf":"3.00","ghrq":"2018-11-25 12:28:02","ghlb":"主任医师","dabh":"15928089191","byxm":"杨刚"}],"count":"2"}';
        $response = json_decode($response, true);
        $result = $response['item'];
        return $result;
    }

    /**
     * 取消当日挂号
     * @param $cardno
     * @param null $mzh
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cancelReg($cardno, $mzh = null)
    {
        if (empty($cardno) || empty($mzh)) {
            throw new \Exception('缺少必要的参数', 200);
        }
        HospitalApi::getInstance()->apiClient('ghdjzf', ['kh'=>$cardno, 'mzh' => $mzh]);
        throw new \Exception('结果超出预期', 201);
    }

    /**
     * 获取医院科室列表
     */
    public function getDepartment() : array
    {
        $result = [];
//        $response = HospitalApi::getInstance()->apiClient('ksxx');
//        var_dump($response);
//        if (!empty($response) && !empty($response['item'])) {
//            $result = $response['item'];
//        }
        $result = json_decode('[{"ksbm":"1","ksmc":"内科","kswz":"一楼"},{"ksbm":"2","ksmc":"外科","kswz":"一楼"},{"ksbm":"3","ksmc":"急诊科","kswz":"一楼"}]');
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
//        if (empty($ksbm)) {
//            throw new \Exception('缺少必要的参数', 200);
//        }
//        $response = HospitalApi::getInstance()->apiClient('ysxx', ['ksbm'=>$ksbm]);
//        var_dump($response);
//        if (!empty($response) && !empty($response['item'])) {
//            $result = $response['item'];
//        }
        $result = json_decode('[{"ghs":"50","photoUrl":"http:\/\/cdfzyz.xicp.net:38700\/zykc\/image\/1.jpg","xm":"系统用户1","bb":"2","ysjs":"ttttttttttyuyturtyur","ysbh":"1","syhs":"60","lbmc":"主任医师","ghlb":"1","ghfy":"11","bbmc":"上午班"},{"ghs":"50","photoUrl":"http:\/\/cdfzyz.xicp.net:38700\/zykc\/image\/1.jpgimage\/2.jpg","xm":"高智三","bb":"1","ysjs":"医生技术哦","ysbh":"2","syhs":"60","lbmc":"主任医师","ghlb":"1","ghfy":"0.01","bbmc":"全班"},{"ghs":"50","photoUrl":"http:\/\/cdfzyz.xicp.net:38700\/zykc\/image\/1.jpgimage\/2.jpgimage\/35.jpg","xm":"陈永朴","bb":"1","ysjs":"","ysbh":"35","syhs":"60","lbmc":"主任医师","ghlb":"1","ghfy":"0.02","bbmc":"全班"}]');
        return $result;
    }

    /**
     * 创建订单
     * 先去创建本地订单，再来获得支付二维码
     * @param $cardno
     * @param $ysbh
     * @param $bb
     * @param $zfje
     * @param $zfzl
     * @throws \Exception
     * @return array|void
     */
    public function createOrder($cardno, $ysbh, $bb, $zfje, $zfzl)
    {
        $result = false;
        if (empty($cardno) || empty($ysbh) || empty($bb) || empty($zfje) || empty($zfzl)) {
            throw new \Exception('缺少必要的参数', 200);
        }
        $orderData = OrderModel::getInstance()->createRegOrder($cardno, $ysbh, $bb, $zfje, $zfzl);
        if ($orderData == false) {
            throw new \Exception('订单创建失败', 210);
        }
        switch ($zfzl) {
            case 1:
                break;
            case 2:
                $orderData['total_fee']= 1;
                $orderData['mch_create_ip'] = '114.215.190.171';
                $orderData['time_start'] = date('YmdHis', strtotime($orderData['time_start']));
                $orderData['time_expire'] = date('YmdHis', strtotime($orderData['time_expire']));
                $weixinResult = PaymentApi::getInstance()->createOrder($orderData);
                $result = $weixinResult['code_img_url'];
                break;
            case 3:
                break;
        }
        return $result;
    }

    /**
     * 支付回调
     * @param $xml
     * @return string
     */
    public function callbackWFT($xml)
    {
        return PaymentApi::getInstance()->callback($xml);
    }


    /**
     * todo
     * 挂号
     */
    public function register()
    {
        $content = [
            "kh" => "00000005",
            "ysbh" => "1",
            "bb" => "2",
            "zfje" => "11",
            "zfzl" => "1",
            "sjh" => "a0001"
        ];
        $result = HospitalApi::getInstance()->apiClient('ghdj', $content);
        return $result;
    }

}