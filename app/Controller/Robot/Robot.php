<?php
/**
 * 自助挂号终端机
 */

namespace App\Controller\Robot;

use Julibo\Msfoole\WebsocketController as BaseController;
use App\Service\Robot as RobotServer;

class Robot extends BaseController
{

    /**
     * 获取当前用户信息
     */
    public function getUserInfo()
    {
        $result = $this->user;
        return $result;
    }

    /**
     * 获取挂号信息
     */
    public function getTodayRegister()
    {
        $cardNo = $this->user->cardno;
        $result = RobotServer::getInstance()->getTodayRegister($cardNo);
        return $result;
    }

    /**
     * 取消挂号
     */
    public function cancelReg()
    {
        $cardNo = $this->user->cardno;
        $mzh = $this->params['mzh'] ?? null;
        $result = RobotServer::getInstance()->cancelReg($cardNo, $mzh);
        return $result;
    }

    /**
     * 获取医院科室列表
     */
    public function getDepartment()
    {
        $result = RobotServer::getInstance()->getDepartment();
        return $result;
    }

    /**
     * 获取号源列表
     */
    public function getSourceList()
    {
        $ksbm = $this->params['ksbm'] ?? null;
        $result = RobotServer::getInstance()->getSourceList($ksbm);
        return $result;
    }

    /**
     * 创建挂号订单
     */
    public function createRegOrder()
    {
        $cardNo = $this->user->cardno;
        $ip = $this->user->ip ?? '127.0.0.1';
        $ysbh = $this->params['ysbh'] ?? null;
        $bb = $this->params['bb'] ?? null;
        $zfje = $this->params['zfje'] ?? null;
        $zfzl = $this->params['zfzl'] ?? null;
        $body = '挂号费';
        $result = RobotServer::getInstance()->createRegOrder($cardNo, $ysbh, $bb, $zfje, $zfzl, $body, $this->token, $ip);
        return $result;
    }

    /**
     * 获取待缴费记录
     */
    public function getPayment()
    {
        $cardNo = $this->user->cardno;
        $result = RobotServer::getInstance()->getPayment($cardNo);
        return $result;
    }

    /**
     * 创建门诊缴费订单
     */
    public function createPayOrder()
    {
        $cardNo = $this->user->cardno;
        $ip = $this->user->ip ?? '127.0.0.1';
        $body = '门诊缴费';
        $mzh = $this->params['mzh'] ?? null;
        $zfje = $this->params['zfje'] ?? null;
        $zfzl = $this->params['zfzl'] ?? null;
        $result = RobotServer::getInstance()->createPayOrder($cardNo, $mzh, $zfje, $zfzl, $body, $ip, $this->token);
        return $result;
    }

    /**
     * 取消门诊缴费
     */
    public function cancelPay()
    {

    }
}
