<?php
/**
 * 后台订单管理
 */

namespace App\Controller\Admin;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Admin as AdminService;

class Order extends BaseController
{
    private $server;

    protected function init()
    {
        $this->server = AdminService::getInstance();
        $this->server->setCache($this->cache);
    }

    /**
     * 订单列表
     * @return mixed
     */
    public function getOrderList()
    {
        $result = $this->server->getOrderList($this->params);
        return $result;
    }

    /**
     * 退款订单
     * @return mixed
     */
    public function getOrderRefund()
    {
        $result = $this->server->getOrderRefund($this->params);
        return $result;
    }

    /**
     * 异常订单
     * @return mixed
     */
    public function getOrderAbnormal()
    {
        $result = $this->server->getOrderAbnormal($this->params);
        return $result;
    }

    /**
     * 退款
     * @return mixed
     */
    public function refunding()
    {
        $out_trade_no = $this->params['no'];
        $result = $this->server->refunding($out_trade_no);
        return $result;
    }

    /**
     * 异常订单数量
     * @return mixed
     */
    public function getAbnormalCount()
    {
        $result = $this->server->getAbnormalCount($this->params);
        return $result;
    }

    /**
     * 统计日报
     */
    public function getReportDaily()
    {
        $result = $this->server->getReportDaily($this->params);
        return $result;
    }

    /**
     * 统计月报
     */
    public function getReportMonthly()
    {
        $result = $this->server->getReportMonthly();
        return $result;
    }

    /**
     * 当日汇总
     */
    public function getTodaySummary()
    {
        $result = $this->server->getTodaySummary();
        return $result;
    }

    /**
     * 近七天成交额趋势
     * @return mixed
     */
    public function getReportWeek()
    {
        $result = $this->server->getReportWeek();
        return $result;
    }

    /**
     * 近七天成交数趋势
     * @return mixed
     */
    public function getCountWeek()
    {
        $result = $this->server->getCountWeek();
        return $result;
    }

    /**
     * 近七天成交类型比例
     * @return mixed
     */
    public function getRatioWeek()
    {
        $result = $this->server->getRatioWeek();
        return $result;
    }

    /**
     * 异常订单报表
     * @return mixed
     */
    public function getAbnormalReport()
    {
        $result = $this->server->getAbnormalReport();
        return $result;
    }
}