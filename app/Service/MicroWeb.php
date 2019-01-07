<?php
/**
 * Created by PhpStorm.
 * User: carson
 * Date: 2018/12/28
 * Time: 2:53 PM
 */

namespace App\Service;

use Julibo\Msfoole\Exception;
use App\Model\WechatCard as WechatCardModel;
use App\Validator\Feedback;
use App\Logic\HospitalApi;

class MicroWeb extends BaseServer
{
    /**
     * @var
     */
    public $cache;

    /**
     * @var
     */
    public $wechatCardModel;

    /**
     * @var
     */
    public $hospitalApi;

    /**
     * 初始化服务
     */
    public function init()
    {
        $this->wechatCardModel = WechatCardModel::getInstance();
        $this->hospitalApi = HospitalApi::getInstance();
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
        $result = $this->wechatCardModel->getBindCard($openid);
        return $result;
    }

    /**
     * 查看就诊卡详情
     * @param string $openid
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function showCard(string $openid, string $id) {
        if (empty($id)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $result = $this->wechatCardModel->showCard($openid, $id);
        return $result;
    }

    /**
     * 解绑就诊卡
     * @param string $openid
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function delCard(string $openid, string $id) {
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
        $result = $this->wechatCardModel->delCard($openid, $id);
        return $result;
    }

    /**
     * 修改默认就诊卡
     * @param string $openid
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function defaultCard(string $openid, string $id) {
        if (empty($id)) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        $result = $this->wechatCardModel->defaultCard($openid, $id);
        return $result;
    }


    public function bindCard(string $openid, array $params)
    {
        if (empty($params) || empty($params['name']) || empty($params['cardno']) || empty($params['idcard'])  ||
            empty($params['idcard']) || empty($params['mobile'])) {
            throw new Exception(Feedback::$Exception['PARAMETER_MISSING']['msg'], Feedback::$Exception['PARAMETER_MISSING']['code']);
        }
        // 通过卡号查询用户信息
        $user = $this->hospitalApi->getUser($params['cardno']);
        if (empty($user) || $user['xm'] != $params['name']) {
            throw new Exception('卡号与姓名不匹配', 20);
        }
        $list = $this->wechatCardModel->getBindCard($openid);
        if (empty($list)) {
            $params['default'] = 1;
        } else {
            $params['default'] = 1;
            foreach ($list as $v) {
                if ($v['default'] == 1) {
                    $params['default'] = 0;
                }
                if ($v['cardno'] == $params['cardno']) {
                    throw new Exception('该卡已被绑定', 21);
                }
            }
        }
        $result = WechatCardModel::getInstance()->bindCard($openid, $params);
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
        $response = $this->hospitalApi->apiClient('getyyhy', ['kssj' =>$kssj, 'jssj'=>$jssj, 'ksbm'=>$ksbm]);
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
                        // 'photo' => $vo['photoUrl'],
                        'photo' => 'https://ss1.bdstatic.com/70cFvXSh_Q1YnxGkpoWK1HF6hhy/it/u=652584500,304181435&fm=200&gp=0.jpg',
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
}