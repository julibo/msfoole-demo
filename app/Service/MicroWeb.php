<?php
/**
 * 微信公众号网页版
 */

namespace App\Service;

use Julibo\Msfoole\Exception;
use App\Model\WechatCard as WechatCardModel;
use App\Model\Order as OrderModel;
use App\Validator\Feedback;
use App\Logic\HospitalApi;
use App\Logic\PaymentApi;

class MicroWeb extends BaseServer
{
    /**
     * @var
     */
    public $cache;

    /**
     * @var
     */
    public $hospitalApi;

    /**
     * 初始化服务
     */
    public function init()
    {
        $this->hospitalApi = HospitalApi::getInstance();
    }

    /**
     * 绑定就诊卡
     * @param string $openid
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function bindCard(string $openid, array $params)
    {
        if (empty($params) || empty($params['name']) || empty($params['cardno']) || empty($params['idcard']) ||
            empty($params['mobile'])) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        // 通过卡号查询用户信息
        $user = $this->hospitalApi->getUser($params['cardno']);
        if (empty($user) || $user['xm'] != $params['name']) {
            throw new Exception('卡号与姓名不匹配', Feedback::$Exception['SERVICE_DATA_ERROR']['code']);
        }
        $list = WechatCardModel::getInstance()->getBindCard($openid);
        $params['default'] = 1;
        if (!empty($list)) {
            foreach ($list as $v) {
                if ($v['cardno'] == $params['cardno']) {
                    throw new Exception('该卡已被绑定', Feedback::$Exception['SERVICE_DATA_ERROR']['code']);
                }
                if ($v['default'] == 1) {
                    $params['default'] = 0;
                }
            }
        }
        $result = WechatCardModel::getInstance()->bindCard($openid, $params);
        return $result;
    }

    /**
     * 查询用户就诊卡
     * @param string $openid
     * @return mixed
     * @throws Exception
     */
    public function userCard(string $openid)
    {
        if (empty($openid)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $result = WechatCardModel::getInstance()->getBindCard($openid);
        return $result;
    }

    /**
     * 修改默认就诊卡
     * @param string $openid
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function defaultCard(string $openid, string $id)
    {
        if (empty($id)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $result = WechatCardModel::getInstance()->defaultCard($openid, $id);
        return $result;
    }

    /**
     * 查看就诊卡详情
     * @param string $openid
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function showCard(string $openid, string $id)
    {
        if (empty($id)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $result = WechatCardModel::getInstance()->showCard($openid, $id);
        return $result;
    }

    /**
     * 解绑就诊卡
     * @param string $openid
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function delCard(string $openid, string $id)
    {
        if (empty($id)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $card = $this->showCard($openid, $id);
        if (empty($card)) {
            throw new Exception(Feedback::$Exception['HANDLE_DADA_CHECK']['msg'], Feedback::$Exception['HANDLE_DADA_CHECK']['code']);
        }
        if ($card['default']) {
            throw new Exception('默认就诊卡不能被删除', Feedback::$Exception['HANDLE_ABNORMAL']['code']);
        }
        $result = WechatCardModel::getInstance()->delCard($openid, $id);
        return $result;
    }

    /**
     * 获取科室信息
     * @return array
     */
    public function getOffice()
    {
        $result = [];
        $response = $this->hospitalApi->apiClient('ksxx');
        if (!empty($response) && !empty($response['item'])) {
            $result = $response['item'];
        }
        return $result;
    }

    /**
     * 获取号源
     * @param string $ksbm
     * @param null $appoint
     * @return array
     */
    public function getSource(string $ksbm, $appoint = null)
    {
        if (empty($appoint)) {
            $kssj = date('Y-m-d', strtotime('1 days'));
            $jssj = date('Y-m-d', strtotime('7 days'));
        } else {
            $kssj = $appoint;
            $jssj = $appoint;
        }
        $result = [];
        $response = $this->hospitalApi->apiClient('getyyhy', ['kssj' => $kssj, 'jssj' => $jssj, 'ksbm' => $ksbm]);
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
                $weekarray = array("日", "一", "二", "三", "四", "五", "六");
                $week = $weekarray[date("w", strtotime($vo['ghrq']))];
                array_push($result[$vo['ysbh']]['plan'], [
                    'date' => $date,
                    'showDate' => $showDate,
                    'week' => '星期' . $week,
                    'total' => $vo['amyys'],
                    'surplus' => $vo['amyys'] - $vo['amyyy'],
                    'ysh_lx' => 1,
                    'showTime' => '上午'
                ], [
                    'date' => $date,
                    'showDate' => $showDate,
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


    public function createOrder($openid, $cardNo, $ysbh, $ysxm, $zzks, $zzksmc, $ghrq, $ghlb, $ysh_lx, $zfzl, $zfje, $ip, $body, $is_raw = 0)
    {
        if (empty($openid) || empty($cardNo) || empty($ysbh) || empty($zzks) || empty($ghrq) || empty($ghlb) ||
            empty($ysh_lx) || empty($body) || empty($ip) || empty($zfje)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $orderData = OrderModel::getInstance()->createWechatOrder($openid, $cardNo, $ysbh, $ysxm, $zzks, $zzksmc, $ghrq, $ghlb, $ysh_lx, $zfzl, $zfje, $body, $ip);
        if ($orderData == false) {
            throw new Exception(Feedback::$Exception['SERVICE_SQL_ERROR']['msg'], Feedback::$Exception['SERVICE_SQL_ERROR']['code']);
        }
        $orderData['is_raw'] = $is_raw;
        $orderData['time_start'] = date('YmdHis', strtotime($orderData['time_start']));
        $orderData['time_expire'] = date('YmdHis', strtotime($orderData['time_expire']));
        $payResult = PaymentApi::getInstance()->createOrder($orderData, $zfzl);
        if ($payResult == false) {
            $result = false;
        } else {
            $result = [
                'pay_info' => $payResult['pay_info'],
                'is_raw' => $is_raw,
                'token_id' => $payResult['token_id'],
                'order' => $orderData['out_trade_no']
            ];
        }
        return $result;
    }

    /**
     * 查询订单结果
     * @param $cardNo
     * @param $orderNo
     * @return array
     * @throws Exception
     */
    public function showRegResult($cardNo, $orderNo)
    {
        // step 1 查询订单
        $order = OrderModel::getInstance()->getOrderByTradeAndCard($cardNo, $orderNo);
        if (empty($order)) {
            throw new Exception('订单没有找到', Feedback::$Exception['HANDLE_DADA_CHECK']['code']);
        }
        $order['info'] = json_decode($order['info'], true);
        $kzsj = $order['info']['ghrq'];
        if ($order['info']['ysh_lx'] == 1) {
            $kzsj .= ' 上午';
        } else if ($order['info']['ysh_lx'] == 2) {
            $kzsj .= ' 下午';
        }
        $order['kzsj'] = $kzsj;
        if ($order['status'] != 2) {
            return $order;
        } else {
            // step 2 查询用户预约记录
            $result = [];
            $response = $this->hospitalApi->apiClient('yydjcx', ['kh' => $cardNo]);
            if (!empty($response) && !empty($response['item'])) {
                foreach ($response['item'] as $vo) {
                    if ($vo['mzh'] == $order['code']) {
                        $result = $vo;
                        break;
                    }
                }
            }
            return $result;
        }
    }

    /**
     * 查询挂号详情
     * @param $cardNo
     * @param $mzh
     * @return array
     */
    public function regDetail($cardNo, $mzh)
    {
        $result = [];
        $response = $this->hospitalApi->apiClient('yydjcx', ['kh' => $cardNo]);
        if (!empty($response) && !empty($response['item'])) {
            foreach ($response['item'] as $vo) {
                if ($vo['mzh'] == $mzh) {
                    $kzsj = $vo['ghrq'];
                    if ($vo['ysh_lx'] == 1) {
                        $kzsj .= ' 上午';
                    } else if ($vo['ysh_lx'] == 2) {
                        $kzsj .= ' 下午';
                    }
                    $vo['kzsj'] = $kzsj;
                    $result = $vo;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * 查询微信号下所有挂号记录
     * @param $openid
     * @return array
     */
    public function regRecord($openid)
    {
        $result = [];
        $cards = WechatCardModel::getInstance()->getBindCard($openid);
        if (!empty($cards)) {
            foreach ($cards as $card) {
                $response = $this->hospitalApi->apiClient('yydjcx', ['kh' => $card['cardno']]);
                if (!empty($response) && !empty($response['item'])) {
                    foreach ($response['item'] as &$vo) {
                        $vo['cardno'] = $card['cardno'];
                    }
                    $result = array_merge($result, $response['item']);
                }
            }
        }
        return $result;
    }

    /**
     * 获取待缴门诊
     * @param $openid
     * @param null $cardNo
     * @return array
     */
    public function payList($openid, $cardNo = null)
    {
        $result = [];
        if (empty($cardNo)) {
            $card = WechatCardModel::getInstance()->getDefaultCard($openid);
            if (!empty($card)) {
                $cardNo = $card['cardno'];
            }
        }
        if (!empty($cardNo)) {
            $response = $this->hospitalApi->apiClient('getjfmx', ['kh' => $cardNo]);
            if (!empty($response) && !empty($response['item'])) {
                foreach ($response['item'] as $vo) {
                    if (empty($vo['skbs'])) {
                        $vo['ghrq'] = date('Y-m-d', strtotime($vo['ghrq']));
                        $vo['money'] = sprintf('￥%s', $vo['je']);
                        array_push($result, $vo);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 缴费明细
     * @param $cardNo
     * @param $mzh
     * @return array
     * @throws Exception
     */
    public function payDetail($cardNo, $mzh)
    {
        if (empty($cardNo) || empty($mzh)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $result = [];
        $response = $this->hospitalApi->apiClient('getjfmx', ['kh' => $cardNo]);
        if (!empty($response) && !empty($response['item'])) {
            foreach ($response['item'] as $vo) {
                if ($vo['mzh'] == $mzh) {
                    $vo['ghrq'] = date('Y-m-d', strtotime($vo['ghrq']));
                    $vo['je'] = sprintf('￥%s', $vo['je']);
                    $result = $vo;
                }
            }
        }
        return $result;
    }

    /**
     * 缴费记录
     * @param $openid
     * @return array
     */
    public function payRecord($openid)
    {
        $result = [];
        $cards = WechatCardModel::getInstance()->getBindCard($openid);
        if (!empty($cards)) {
            foreach ($cards as $card) {
                $response = $this->hospitalApi->apiClient('getjfmx', ['kh' => $card['cardno']]);
                if (!empty($response) && !empty($response['item'])) {
                    foreach ($response['item'] as $vo) {
                        if (empty($vo['skbs'])) {
                            $vo['cardno'] = $card['cardno'];
                            array_push($result, $vo);
                        }
                    }
                }
            }
        }
        return $result;
    }


    public function createPayOrder($openid, $cardNo, $mzh, $je, $is_raw, $body, $ip)
    {
        if (empty($openid) || empty($cardNo) || empty($mzh) || empty($je) || !isset($is_raw)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        // 支付种类
        $zfzl = 3;
        $orderData = OrderModel::getInstance()->createWechatPayOrder($openid, $cardNo, $mzh, $je, $zfzl, $body, $ip);
        if ($orderData == false) {
            throw new Exception(Feedback::$Exception['SERVICE_SQL_ERROR']['msg'], Feedback::$Exception['SERVICE_SQL_ERROR']['code']);
        }
        $orderData['is_raw'] = $is_raw;
        $orderData['time_start'] = date('YmdHis', strtotime($orderData['time_start']));
        $orderData['time_expire'] = date('YmdHis', strtotime($orderData['time_expire']));
        $payResult = PaymentApi::getInstance()->createOrder($orderData, $zfzl);
        if ($payResult == false) {
            $result = false;
        } else {
            $result = [
                'pay_info' => $payResult['pay_info'],
                'is_raw' => $is_raw,
                'token_id' => $payResult['token_id'],
                'order' => $orderData['out_trade_no']
            ];
        }
        return $result;


    }

}