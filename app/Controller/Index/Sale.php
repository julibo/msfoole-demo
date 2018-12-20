<?php
/**
 * 官网预约挂号
 */

namespace App\Controller\Index;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Sale as SaleService;

class Sale extends BaseController
{
    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * 预约挂号首页
     */
    public function index()
    {
        $cardNo = $this->user->cardno;
        $result = SaleService::getInstance()->getRecord($cardNo);
        return $result;
    }

    /**
     * 获取医院科室
     */
    public function getOffices()
    {
        $result = SaleService::getInstance()->getDepartment();
        return $result;
    }

    /**
     * 获取号源
     */
    public function getSource()
    {

    }

    /**
     * 订单预览
     */
    public function preview()
    {

    }

    /**
     * 创建订单，生成二维码
     */
    public function createOrder()
    {

    }

    /**
     * 刷新订单状态
     */
    public function refresh()
    {

    }

    /**
     * 取消预约
     */
    public function cancel()
    {

    }

}
