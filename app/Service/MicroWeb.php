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

    /**
     * 创建门诊缴费订单
     * @param $openid
     * @param $cardNo
     * @param $mzh
     * @param $je
     * @param $is_raw
     * @param $body
     * @param $ip
     * @return array|bool
     * @throws Exception
     */
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

    /**
     * 获取报告
     * @param $cardNo
     * @return array|mixed
     * @throws Exception
     */
    public function report($cardNo)
    {
        if (empty($cardNo)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
//        $result = [];
//        $responseCheck = $this->hospitalApi->apiClient('jcxx', ['kh' => $cardNo]);
//        if (!empty($responseCheck) && !empty($responseCheck['item'])) {
//            $result['check'] = $responseCheck['item'];
//        } else {
//            $result['check'] = [];
//        }
//        $responseTest = $this->hospitalApi->apiClient('jyxx', ['kh' => $cardNo]);
//        if (!empty($responseTest) && !empty($responseTest['item'])) {
//            $result['test'] = $responseTest['item'];
//        } else {
//            $result['test'] = [];
//        }
        $value = '{"check":[{"mzh":"1812260003","ysxm":"\u9ad8\u667a\u4e09","jcxm":[{"jcxmmc":"\u4e24\u90e8\u4f4dB\u8d85","kdxh":"1949","jcjg":[{"zd":"\u53cc\u819d\u5173\u8282\u5404\u9aa8\u9aa8\u8d28\u672a\u89c1\u660e\u663e\u9aa8\u6298\u5f81\u8c61\u3002","jg":"\u9634\u6027","zdnr":"\u53cc\u819d\u5173\u8282\u5404\u9aa8\u9aa8\u8d28\u7ed3\u6784\u5b8c\u6574\uff0c\u9aa8\u76ae\u8d28\u8fde\u7eed\uff0c\u9aa8\u7eb9\u7406\u6e05\u6670\uff0c\u672a\u89c1\u9aa8\u8d28\u589e\u751f\u53ca\u7834\u574f\u5f81\u8c61\uff0c\u672a\u89c1\u9aa8\u6298\u5f81\u8c61\u3002","jcfs":"","jcbw":"\u53f3\u819d\u5173\u8282\u6444\u7247,\u5de6\u819d\u5173\u8282\u6444\u7247,"}]},{"jcxmmc":"\u4e09\u90e8\u4f4dB\u8d85","kdxh":"1950","jcjg":""}],"ghrq":"2018-12-26 00:00:00","byxm":"\u5218\u9752\u6d0b"}],"test":[{"mzh":"1812260003","ysxm":"\u9ad8\u667a\u4e09","jyxm":[{"jytmh":"180918000001870002","jyxmmc":"\u7535\u89e3\u8d28","shjg":"","jyjg":""},{"jytmh":"170513000582570004","jyxmmc":"\u809d\u529f","shjg":"","jyjg":[{"ITEMNAME":"\u51dd\u8840\u9176\u539f\u65f6\u95f4","SAMPLE_GROUP_NAME":"\u2605\u8840\u51dd\u2605","SAMPLE_GROUP_CODE":"40090","RESULT_STATUS":"","PATIENT_ID":"1812260003","SERIALNO":"170513000582570004","INSPECTION_DATE":"2017\/5\/14","SAMPLE_NO":"1","VISIT_ID":"1812260003","ITEMNO":"40010","RESULT":"10.5","REFRANGE":"10-16","UNIT":"S"},{"ITEMNAME":"\u56fd\u9645\u6807\u51c6\u5316\u6bd4\u503c","SAMPLE_GROUP_NAME":"\u2605\u8840\u51dd\u2605","SAMPLE_GROUP_CODE":"40090","RESULT_STATUS":"","PATIENT_ID":"1812260003","SERIALNO":"170513000582570004","INSPECTION_DATE":"2017\/5\/14","SAMPLE_NO":"1","VISIT_ID":"1812260003","ITEMNO":"40020","RESULT":"0.84","REFRANGE":"0.80-1.20","UNIT":""},{"ITEMNAME":"\u7ea4\u7ef4\u86cb\u767d\u539f","SAMPLE_GROUP_NAME":"\u2605\u8840\u51dd\u2605","SAMPLE_GROUP_CODE":"40090","RESULT_STATUS":"","PATIENT_ID":"1812260003","SERIALNO":"170513000582570004","INSPECTION_DATE":"2017\/5\/14","SAMPLE_NO":"1","VISIT_ID":"1812260003","ITEMNO":"40030","RESULT":"2.629","REFRANGE":"2.0-4.0","UNIT":"g\/L"},{"ITEMNAME":"\u51dd\u8840\u9176\u65f6\u95f4","SAMPLE_GROUP_NAME":"\u2605\u8840\u51dd\u2605","SAMPLE_GROUP_CODE":"40090","RESULT_STATUS":"","PATIENT_ID":"1812260003","SERIALNO":"170513000582570004","INSPECTION_DATE":"2017\/5\/14","SAMPLE_NO":"1","VISIT_ID":"1812260003","ITEMNO":"40040","RESULT":"13.6","REFRANGE":"10-20","UNIT":"S"},{"ITEMNAME":"\u90e8\u5206\u6d3b\u5316\u51dd\u8840\u6d3b\u9176\u65f6\u95f4","SAMPLE_GROUP_NAME":"\u2605\u8840\u51dd\u2605","SAMPLE_GROUP_CODE":"40090","RESULT_STATUS":"","PATIENT_ID":"1812260003","SERIALNO":"170513000582570004","INSPECTION_DATE":"2017\/5\/14","SAMPLE_NO":"1","VISIT_ID":"1812260003","ITEMNO":"40050","RESULT":"27.7","REFRANGE":"22-38","UNIT":"S"},{"ITEMNAME":"D-\u4e8c\u805a\u4f53","SAMPLE_GROUP_NAME":"D-\u4e8c\u805a\u4f53","SAMPLE_GROUP_CODE":"40080","RESULT_STATUS":"","PATIENT_ID":"1812260003","SERIALNO":"170513000582570004","INSPECTION_DATE":"2017\/5\/14","SAMPLE_NO":"1","VISIT_ID":"1812260003","ITEMNO":"40080","RESULT":"0.17","REFRANGE":"0-0.40","UNIT":"mg\/L"}]}],"ghrq":"2018-12-26 00:00:00","byxm":"\u5218\u9752\u6d0b"}]}';
        $result = json_decode($value, true);
        return $result;
    }

    /**
     * 检查报告详情
     * @param $cardNo
     * @param $mzh
     * @param $kdxh
     * @return array
     * @throws Exception
     */
    public function reportCheck($cardNo, $mzh, $kdxh)
    {
        if (empty($cardNo) || empty($mzh) || empty($kdxh)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $result = [];
        $report = $this->report($cardNo);
        if (!empty($report['check'])) {
            foreach ($report['check'] as $check) {
                if ($check['mzh'] == $mzh) {
                    foreach ($check['jcxm'] as $jcxm) {
                        if ($jcxm['kdxh'] == $kdxh) {
                            $result['byxm'] = $check['byxm'];
                            $result['mzh'] = $check['mzh'];
                            $result['ysxm'] = $check['ysxm'];
                            $result['ghrq'] = $check['ghrq'];
                            $result['jcxmmc'] = $jcxm['jcxmmc'];
                            $result['kdxh'] = $jcxm['kdxh'];
                            $result['jcjg'] = $jcxm['jcjg'];
                            break;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 检验详情
     * @param $cardNo
     * @param $mzh
     * @param $jytmh
     * @return array
     * @throws Exception
     */
    public function reportTest($cardNo, $mzh, $jytmh)
    {
        if (empty($cardNo) || empty($mzh) || empty($jytmh)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $result = [];
        $report = $this->report($cardNo);
        if (!empty($report['test'])) {
            foreach ($report['test'] as $test) {
                if ($test['mzh'] == $mzh) {
                    foreach ($test['jyxm'] as $jyxm) {
                        if ($jyxm['jytmh'] == $jytmh) {
                            $result['byxm'] = $test['byxm'];
                            $result['ghrq'] = $test['ghrq'];
                            $result['mzh'] = $test['mzh'];
                            $result['ysxm'] = $test['ysxm'];
                            $result['jytmh'] = $jyxm['jytmh'];
                            $result['jyxmmc'] = $jyxm['jyxmmc'];
                            $result['jyjg'] = $jyxm['jyjg'];
                            $result['shjg'] = $jyxm['shjg'];
                            break;
                        }
                    }
                }
            }
        }
        return $result;
    }

}