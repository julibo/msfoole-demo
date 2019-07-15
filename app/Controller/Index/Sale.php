<?php
/**
 * 官网预约挂号
 */

namespace App\Controller\Index;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Sale as SaleService;

class Sale extends BaseController
{
    private $server;

    protected function init()
    {
        $this->server = SaleService::getInstance();
        $this->server->setCache($this->cache);
    }

    /**
     * 获取用户信息
     * @return mixed
     */
     public function getUser()
     {
         return $this->user;
     }

    /**
     * 预约列表
     * @return mixed
     */
     public function index()
     {
         $cardNo = $this->user['cardno'] ?? null;
         $result = $this->server->getRecord($cardNo);
         return $result;
     }

    /**
     * 取消预约
     */
    public function cancel()
    {
        $hybh = $this->params['hybh'] ?? null;
        $sjh = $this->params['sjh'] ?? null;
        $result = SaleService::getInstance()->cancelNo($hybh, $sjh);
        return $result;
    }

    /**
     * 获取医院科室
     */
    public function getOffices()
    {
        $result = $this->server->getDepartment();
        return $result;
    }

    /**
     * 获取号源
     */
    public function getSource()
    {
        $appoint = $this->params['appoint'] ?? null;
        $ksbm = $this->params['ksbm'] ?? null;
        $result = $this->server->getSource($ksbm, $appoint);
        return $result;
    }

    public function getSourceDate()
    {
        $result = [];
        $weekarray = array("日","一","二","三","四","五","六");
        for($i = 1; $i < 8; $i++) {
            $date = date('Y-m-d', strtotime($i . ' days'));
            $showDate = date('m月d日', strtotime($i . ' days'));
            $week = '星期' . $weekarray[date("w", strtotime($i . ' days'))];
            $result[$date] = sprintf('%s %s', $showDate, $week);
        }
        return $result;
    }

    /**
     * 预约登记
     */
    public function checkIn()
    {
        $kh = $this->user['cardno'] ?? null;
        $ysbh = $this->params['ysbh'] ?? null;
        $zzks = $this->params['zzks'] ?? null;
        $ghrq = $this->params['ghrq'] ?? null;
        $ghlb = $this->params['ghlb'] ?? null;
        $ysh_lx = $this->params['ysh_lx'] ?? null;
        $result = $this->server->checkIn($kh, $ysbh, $zzks, $ghrq, $ghlb, $ysh_lx);
        return $result;
    }

    /**
     * 创建订单，生成二维码
     */
    public function createOrder()
    {
        $kh = $this->user['cardno'] ?? null;
        $ysbh = $this->params['ysbh'] ?? null;
        $zzks = $this->params['zzks'] ?? null;
        $ghrq = $this->params['ghrq'] ?? null;
        $ghlb = $this->params['ghlb'] ?? null;
        $ysh_lx = $this->params['ysh_lx'] ?? null;
        $ghf = $this->params['ghf'] ?? null;
        $zfzl = $this->params['zfzl'] ?? null;
        $ip = $this->user->ip ?? '127.0.0.1';
        $body = '预约挂号费';
        $result = SaleService::getInstance()->createOrder($kh, $ysbh, $zzks, $ghrq, $ghlb, $ysh_lx, $zfzl, $ghf, $ip, $body);
        return $result;
    }

    /**
     * 刷新订单状态
     */
    public function refresh()
    {
        $tradeNo = $this->params['tradeNo'];
        $cardNo = $this->user['cardno'] ?? null;
        $result = SaleService::getInstance()->getOrder($cardNo, $tradeNo);
        return $result;
    }

    /**
     * 检查报告
     */
    public function report()
    {
        $cardNo = $this->user['cardno'] ?? null;
        $result = SaleService::getInstance()->report($cardNo);
        return $result;
    }

}
