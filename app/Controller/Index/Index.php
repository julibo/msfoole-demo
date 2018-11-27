<?php
namespace App\Controller\Index;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Robot as RobotServer;

class Index
{
    protected function init()
    {
        // TODO: Implement init() method.
    }

    public function index()
    {
        return 'hello world!';
    }

    /**
     * 支付回调
     */
    public function callback()
    {
        $result = RobotServer::getInstance()->callback();
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



}