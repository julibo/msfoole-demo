<?php
/**
 * 微信公众号网页
 */

namespace App\Controller\Wechat;

use App\Service\MicroWeb;
use Julibo\Msfoole\HttpController as BaseController;

class Index extends BaseController
{
    /**
     * @var
     */
    public $wechat;

    /**
     * 初始化服务
     * @return mixed|void
     */
    protected function init()
    {
        $this->wechat = MicroWeb::getInstance();
        $this->wechat->cache = $this->cache;
    }

    /**
     * 我的就诊卡
     */
    public function userCard()
    {
        $openid = $this->user['openid'];
        $result = $this->wechat->userCard($openid);
        return $result;
    }

    /**
     * 显示卡片信息
     * @return mixed
     */
    public function showCard()
    {
        $openid = $this->user['openid'];
        $id = $this->params['id'] ?? null;
        $result = $this->wechat->showCard($openid, $id);
        return $result;
    }

    /**
     * 解绑就诊卡
     * @return mixed
     */
    public function delCard()
    {
        $openid = $this->user['openid'];
        $id = $this->params['id'] ?? null;
        $result = $this->wechat->delCard($openid, $id);
        return $result;
    }

    /**
     * 修改默认卡片
     */
    public function defaultCard()
    {
        $openid = $this->user['openid'];
        $id = $this->params['id'] ?? null;
        $result = $this->wechat->defaultCard($openid, $id);
        return $result;
    }

    /**
     * 绑定就诊卡
     */
    public function bindCard()
    {
        $openid = $this->user['openid'];
        $params = $this->params;
        $result = $this->wechat->bindCard($openid, $params);
        return $result;
    }

    /**
     * 获取科室
     */
    public function office()
    {
        $result = $this->wechat->getOffice();
        return $result;
    }

    /**
     * 获取号源日期列表
     * @return array
     */
    public function getSourceDate()
    {
        $result = [];
        $weekarray = array("日","一","二","三","四","五","六");
        for($i = 1; $i < 8; $i++) {
            $date = date('Y-m-d', strtotime($i . ' days'));
            $showDate = date('m/d', strtotime($i . ' days'));
            $week = '星期' . $weekarray[date("w", strtotime($i . ' days'))];
            $result[$date] = [
                'showDate' => $showDate,
                'week' => $week
            ];
        }
        return $result;
    }

    /**
     * 获取号源
     */
    public function getSource()
    {
        $appoint = $this->params['appoint'] ?? null;
        $ksbm = $this->params['ksbm'] ?? null;
        $result = $this->wechat->getSource($ksbm, $appoint);
        return $result;
    }







    /**
     * 挂号记录
     */
    public function regRecord()
    {
        $params = $this->params;
        $result = $this->wechat->regRecord($params);
        return $result;
    }

    /**
     * 缴费记录
     */
    public function payRecord()
    {
        $params = $this->params;
        $result = $this->wechat->payRecord($params);
        return $result;
    }

    /**
     * 检查报告
     */
    public function report()
    {
        $params = $this->params;
        $result = $this->wechat->report($params);
        return $result;
    }

    /**
     * 我的医生
     */
    public function doctor()
    {
        $params = $this->params;
        $result = $this->wechat->doctor($params);
        return $result;
    }



    /**
     * 缴费
     */
    public function pay()
    {
        $params = $this->params;
        $result = $this->wechat->pay($params);
        return $result;
    }


}