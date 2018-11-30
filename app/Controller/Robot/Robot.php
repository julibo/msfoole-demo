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
        $cardno = $this->user->cardno;
        $result = RobotServer::getInstance()->getTodayRegister($cardno);
        return $result;
    }

    /**
     * 取消挂号
     */
    public function cancelReg()
    {
        $cardno = $this->user->cardno;
        $mzh = $this->params['mzh'] ?? null;
        $result = RobotServer::getInstance()->cancelReg($cardno, $mzh);
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
     * 创建订单
     */
    public function createOrder()
    {
        $cardno = $this->user->cardno;
        $ysbh = $this->params['ysbh'] ?? null;
        $bb = $this->params['bb'] ?? null;
        $zfje = $this->params['zfje'] ?? null;
        $zfzl = $this->params['zfzl'] ?? null;
        $result = RobotServer::getInstance()->createOrder($cardno, $ysbh, $bb, $zfje, $zfzl);
        return $result;
    }

}