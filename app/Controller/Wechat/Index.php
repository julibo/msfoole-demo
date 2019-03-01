<?php
/**
 * 微信公众号网页版
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
        $this->wechat->setCache($this->cache);
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
     * 我的就诊卡
     */
    public function userCard()
    {
        $openid = $this->user['openid'];
        $result = $this->wechat->userCard($openid);
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
     * 查询住院信息
     */
    public function hospitalInfo()
    {
        $cardNo = $this->params['cardno'] ?? null;
        $result = $this->wechat->hospitalInfo($cardNo);
        return $result;
    }

    /**
     * 住院预交费记录
     * @return mixed
     */
    public function hospitalDetail()
    {
        $zyh = $this->params['zyh'] ?? null;
        $result = $this->wechat->hospitalDetail($zyh);
        return $result;
    }

    /**
     * 住院预交费订单
     */
    public function payHospital()
    {
        $cardNo = $this->params['cardno'] ?? null;
        $name = $this->params['name'] ?? null;
        $zyh = $this->params['zyh'] ?? null;
        $money = $this->params['money'] ?? null;
        $zfzl = $this->params['zfzl'] ?? null;
        $is_raw = $this->params['is_raw'] ?? null;
        $openid = $this->user['openid'];
        $body = "住院费预交";
        $ip = $this->user['ip'] ?? '127.0.0.1';
        $result = $this->wechat->payHospital($cardNo, $name, $zyh, $money, $zfzl, $is_raw, $openid, $body, $ip);
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
     * 创建订单
     */
    public function createOrder()
    {
        $openid = $this->user['openid'];
        $ip = $this->user['ip'] ?? '127.0.0.1';
        $kh = $this->params['cardno'] ?? null;
        $name = $this->params['name'] ?? null;
        $ysbh = $this->params['ysbh'] ?? null;
        $ysxm = $this->params['ysxm'] ?? '';
        $ysh_lx = $this->params['ysh_lx'] ?? null;
        $ghlb = $this->params['ghlb'] ?? null;
        $zzks = $this->params['zzks'] ?? null;
        $zzksmc = $this->params['zzksmc'] ?? '';
        $ghrq = $this->params['ghrq'] ?? null;
        $ghf = $this->params['ghf'] ?? null;
        $zfzl = $this->params['zfzl'] ?? null;
        $is_raw = $this->params['is_raw'] ?? 1;
        $body = '预约挂号费';
        $result = $this->wechat->createOrder($openid, $kh, $name, $ysbh, $ysxm, $zzks, $zzksmc, $ghrq, $ghlb, $ysh_lx, $zfzl, $ghf, $ip, $body, $is_raw);
        return $result;
    }

    /**
     * 查询订单状态
     */
    public function showRegResult()
    {
        $cardNo = $this->params['cardno'];
        $orderNo = $this->params['order'] ?? null;
        $result = $this->wechat->showRegResult($cardNo, $orderNo);
        return $result;
    }

    /**
     * 挂号记录
     */
    public function regRecord()
    {
        $openid = $this->user['openid'];
        $result = $this->wechat->regRecord($openid);
        return $result;
    }

    /**
     * 挂号详情
     */
    public function regDetail()
    {
        $cardNo = $this->params['cardno'] ?? null;
        $mzh = $this->params['mzh'] ?? null;
        $result = $this->wechat->regDetail($cardNo, $mzh);
        return $result;
    }

    /**
     * 待缴门诊
     */
    public function payList()
    {
        $openid = $this->user['openid'];
        $cardNo = $this->params['cardno'] ?? null;
        $result = $this->wechat->payList($openid, $cardNo);
        return $result;
    }

    /**
     * 门诊缴费详情
     * @return mixed
     */
    public function payDetail()
    {
        $cardNo = $this->params['cardno'] ?? null;
        $mzh = $this->params['mzh'] ?? null;
        $result = $this->wechat->payDetail($cardNo, $mzh);
        return $result;
    }

    /**
     * 缴费记录
     */
    public function payRecord()
    {
        $openid = $this->user['openid'];
        $result = $this->wechat->payRecord($openid);
        return $result;
    }

    /**
     * 创建缴费订单
     * @return mixed
     */
    public function createPayOrder()
    {
        $openid = $this->user['openid'];
        $cardNo = $this->params['cardno'] ?? null;
        $name = $this->params['name'] ?? null;
        $mzh = $this->params['mzh'] ?? null;
        $je = $this->params['je'] ?? null;
        $is_raw = $this->params['is_raw'] ?? null;
        $zfzl = $this->params['zfzl'] ?? null;
        $body = "门诊缴费";
        $ip = $this->user['ip'] ?? '127.0.0.1';
        $result = $this->wechat->createPayOrder($openid, $cardNo, $name, $mzh, $je, $zfzl, $is_raw, $body, $ip);
        return $result;
    }

    /**
     * 检查报告
     */
    public function report()
    {
        $cardNo = $this->params['cardno'] ?? null;
        $result = $this->wechat->report($cardNo);
        return $result;
    }

    /**
     * 检验报告详情
     * @return mixed
     */
    public function reportTest()
    {
        $cardNo = $this->params['cardno'] ?? null;
        $mzh = $this->params['mzh'] ?? null;
        $jytmh = $this->params['jytmh'] ?? null;
        $result = $this->wechat->reportTest($cardNo, $mzh, $jytmh);
        return $result;
    }

    /**
     * 检查报告详情
     * @return mixed
     */
    public function reportCheck()
    {
        $cardNo = $this->params['cardno'] ?? null;
        $mzh = $this->params['mzh'] ?? null;
        $kdxh = $this->params['kdxh'] ?? null;
        $result = $this->wechat->reportCheck($cardNo, $mzh, $kdxh);
        return $result;
    }

    /**
     * 获取科室医生
     * @return mixed
     */
    public function getDoctor()
    {
        $ksbm = $this->params['ksbm'] ?? null;
        $result = $this->wechat->getDoctor($ksbm);
        return $result;
    }

    /**
     * 创建当日挂号订单
     * @return mixed
     */
    public function createTodayOrder()
    {
        $openid = $this->user['openid'];
        $cardno = $this->params['cardno'] ?? null;
        $name = $this->params['name'] ?? null;
        $ysbh = $this->params['ysbh'] ?? null;
        $bb = $this->params['bb'] ?? null;
        $zfje = $this->params['zfje'] ?? null;
        $zfzl = $this->params['zfzl'] ?? null;
        $ksbm = $this->params['ksbm'] ?? null;
        $ksmc = $this->params['ksmc'] ?? null;
        $ysxm = $this->params['ysxm'] ?? null;
        $bbmc = $this->params['bbmc'] ?? null;
        $ghlb = $this->params['ghlb'] ?? null;
        $lbmc = $this->params['lbmc'] ?? null;
        $is_raw = $this->params['is_raw'] ?? 1;
        $body = '微信挂号费';
        $ip = $this->user['ip'] ?? '127.0.0.1';
        $result = $this->wechat->createTodayOrder($openid, $cardno, $name, $ysbh, $bb, $zfje, $zfzl, $ksbm, $ksmc, $ysxm, $bbmc, $ghlb, $lbmc, $is_raw, $ip, $body);
        return $result;
    }

    /**
     * 查询当日挂号订单
     */
    public function showTodayResult()
    {
        $cardNo = $this->params['cardno'];
        $orderNo = $this->params['order'] ?? null;
        $result = $this->wechat->showTodayResult($cardNo, $orderNo);
        return $result;
    }

}
