<?php
/**
 * 官网预约挂号服务
 */
namespace App\Service;

use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Cache;
use Julibo\Msfoole\Exception;
use App\Lib\Helper\Message;
use App\Logic\HospitalApi;
use App\Logic\PaymentApi;
use App\Model\Order as OrderModel;


class Sale extends BaseServer
{

    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     *  通过卡号或手机号查询用户
     * 查询到用户信息，将用户信息缓存起来
     * @param $number
     * @return bool
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCode($number) : bool
    {
        // 通过卡号或手机号查询用户
        $user = HospitalApi::getInstance()->getUser($number);
        // 将用户和随机码缓存起来
        if (empty($user['mobile'])) {
            throw new Exception('手机号码没有绑定', 88);
        }
        $code = rand(1000,9999);
        $msgResult = Message::sendSms($user['mobile'], sprintf("您正在使用手机认证登录服务，您的短信验证码为%s。验证码切勿告知他人，以免造成不必要的困扰，此验证码10分钟内有效。", $code));
        $cacheConfig = Config::get('cache.default') ?? [];
        $cache = new Cache($cacheConfig);
        $info = [
            'user' => $user,
            'code' => $code,
        ];
        $cache->set($number, $info, 600);
        return $msgResult > 0 ? true : false;
    }

    /**
     * 根据编号和随机码组成的key查询对应用户信息，完成登录验证
     * @param $number
     * @param $code
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login($number, $code) : array
    {
        $result = [];
        $cacheConfig = Config::get('cache.default') ?? [];
        $cache = new Cache($cacheConfig);
        $user = $cache->get($number);
        if (!empty($user) && $user['code'] == $code) {
            $result = HospitalApi::getInstance()->getUser($number);
        }
        return $result;
    }

    /**
     * 获取预约挂号记录
     * @param string $cardNo
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRecord(string $cardNo)
    {
        $result = [];
        $response = HospitalApi::getInstance()->apiClient('yydjcx', ['kh'=>$cardNo]);
        if (!empty($response) && !empty($response['item'])) {
            $result = $response['item'];
        }
        return $result;
    }

    /**
     * 取消预约
     * @param string $hybh
     * @param string $sjh
     * @return bool
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cancelNo(string $hybh, string $sjh)
    {
        if (empty($hybh)) {
            throw new Exception('缺少必要的参数', 22);
        }
        if (empty($sjh)) {
            HospitalApi::getInstance()->apiClient('qxyydj', [
                'hybh' => $hybh
            ]);
            return true;
        } else {


        }
    }


    /**
     * 获取医院科室列表
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Julibo\Msfoole\Exception
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
     * 获取号源
     * @param $ksbm
     * @param null $appoint
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSource($ksbm, $appoint = null)
    {
        if (empty($appoint)) {
            $kssj = date('Y-m-d', strtotime('1 days'));
            $jssj = date('Y-m-d', strtotime('7 days'));
        } else {
            $kssj = $appoint;
            $jssj = $appoint;
        }
        $result = [];
        $response = HospitalApi::getInstance()->apiClient('getyyhy', ['kssj' =>$kssj, 'jssj'=>$jssj, 'ksbm'=>$ksbm]);
        if (!empty($response) && !empty($response['item'])) {
            foreach ($response['item'] as $vo) {
                if (empty($result[$vo['ysbh']])) {
                    $result[$vo['ysbh']] = [
                        'ysbh' => $vo['ysbh'],
                        'ysxm' => $vo['ysxm'],
                        'ghlb' => $vo['ghlb'],
                        'ghlbmc' => $vo['ghlbmc'],
                        'zzks' => $vo['zzks'],
                        'zzksmc' => $vo['zzksmc'],
                        'ghf' => $vo['ghf'],
                        'xh' => $vo['xh'],
                        'photo' => $vo['photoUrl'],
                        'intro' => $vo['__COLUMN1'] ? mb_substr($vo['__COLUMN1'], 0, 120, 'utf-8') : '',
                    ];
                    $result[$vo['ysbh']]['plan'] = [];
                }
                $date = date('Y-m-d', strtotime($vo['ghrq']));
                $showDate = date('m月d日', strtotime($vo['ghrq']));
                $weekarray = array("日","一","二","三","四","五","六");
                $week = $weekarray[date("w", strtotime($vo['ghrq']))];
                array_push($result[$vo['ysbh']]['plan'], [
                    'date' => $date,
                    'showDate'=> $showDate,
                    'week' => '星期' . $week,
                    'total' => $vo['amyys'],
                    'surplus' => $vo['amyys'] - $vo['amyyy'],
                    'ysh_lx' => 1,
                    'showTime' => '上午'
                ], [
                    'date' => $date,
                    'showDate'=> $showDate,
                    'week' => '星期' . $week,
                    'total' => $vo['pmyys'],
                    'surplus' => $vo['pmyys'] - $vo['pmyyy'],
                    'ysh_lx' => 2,
                    'showTime' => '下午'
                ]);
            }
        }
        return $result;
    }

    /**
     * 预约登记
     * @param $kh
     * @param $ysbh
     * @param $zzks
     * @param $ghrq
     * @param $ghlb
     * @param $ysh_lx
     * @return array|string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkIn($kh, $ysbh, $zzks, $ghrq, $ghlb, $ysh_lx)
    {
        if (empty($kh) || empty($ysbh) || empty($zzks) || empty($ghrq) || empty($ghlb) || empty($ysh_lx)) {
            throw new Exception('缺少必要的参数', 22);
        }
        $result = '';
        $response = HospitalApi::getInstance()->apiClient('yydj', [
            'kh' => $kh,
            'ysbh' => $ysbh,
            'zzks' => $zzks,
            'ghrq' => $ghrq,
            'ghlb' => $ghlb,
            'ysh_lx' => $ysh_lx,
        ]);
        if (!empty($response) && $response['hybh']) {
            $result = $response['hybh'];
        }
        return $result;
    }

    /**
     * 预约取号
     * @param $hybh
     * @param $sjh
     * @param $zfzl
     * @param $zfje
     * @return string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function receiveNo($hybh, $sjh, $zfzl, $zfje)
    {
        if (empty($hybh) || empty($sjh) || empty($zfzl) || empty($zfje)) {
            throw new Exception('缺少必要的参数', 22);
        }
        $result = '';
        $response = HospitalApi::getInstance()->apiClient('yydj_qh', [
            'hybh' => $hybh,
            'sjh' => $sjh,
            'zfzl' => $zfzl,
            'zfje' => $zfje
        ]);
        if (!empty($response) && $response['mzh']) {
            $result = $response['mzh'];
        }
        return $result;
    }

    /**
     * 创建订单，生成二维码
     * @param $cardNo
     * @param $ysbh
     * @param $zzks
     * @param $ghrq
     * @param $ghlb
     * @param $ysh_lx
     * @param $zfzl
     * @param $zfje
     * @param $ip
     * @param $body
     * @return mixed
     * @throws Exception
     */
    public function createOrder($cardNo, $ysbh, $zzks, $ghrq, $ghlb, $ysh_lx, $zfzl, $zfje, $ip, $body)
    {
        if (empty($cardNo) || empty($ysbh) || empty($zzks) || empty($ghrq) || empty($ghlb) || empty($ysh_lx) ||
            empty($body) || empty($ip) || empty($zfje)) {
            throw new Exception('缺少必要的参数', 20);
        }
        $orderData = OrderModel::getInstance()->createSaleOrder($cardNo, $ysbh, $zzks, $ghrq, $ghlb, $ysh_lx, $zfzl, $zfje, $body, $ip);
        if ($orderData == false) {
            throw new Exception('订单创建失败', 21);
        }
        $orderData['time_start'] = date('YmdHis', strtotime($orderData['time_start']));
        $orderData['time_expire'] = date('YmdHis', strtotime($orderData['time_expire']));
        $payResult = PaymentApi::getInstance()->createOrder($orderData, $zfzl);
        $result = [
            'codeUrl' => $payResult['code_img_url'],
            'tradeNo' => $orderData['out_trade_no']
        ];
        return $result;
    }

    /**
     * 查询订单
     * @param $cardNo
     * @param $tradeNo
     * @return mixed
     * @throws Exception
     */
    public function getOrder($cardNo, $tradeNo)
    {
        if (empty($cardNo) || empty($tradeNo)) {
            throw new Exception('缺少必要的参数', 22);
        }
        $result = OrderModel::getInstance()->getOrderByTradeAndCard($cardNo, $tradeNo);
        return $result;
    }

}
