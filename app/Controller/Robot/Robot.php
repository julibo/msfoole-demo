<?php
/**
 * 自助挂号终端机
 */

namespace App\Controller\Robot;

use Julibo\Msfoole\WebsocketController as BaseController;
use App\Service\Robot as RobotServer;

class Robot extends BaseController
{

    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * 获取当前用户信息
     */
    public function getUserInfo()
    {
        $result = RobotServer::getInstance()->getUserInfo();
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
        $result = RobotServer::getInstance()->getSourceList();
        return $result;
    }

    /**
     * 获取医生详情
     */
    public function getDoctorInfo()
    {
        $result = RobotServer::getInstance()->register();
        return $result;
    }

    /**
     * 挂号预览
     */
    public function previewRegister()
    {
        $result = RobotServer::getInstance()->cancel();
        return $result;
    }

    /**
     * 创建订单
     */
    public function createOrder()
    {
        $result = RobotServer::getInstance()->createOrder();
        return $result;
    }

    /**
     * 支付回调
     */
    public function callback()
    {
        $result = RobotServer::getInstance()->callback();
        return $result;
    }

}