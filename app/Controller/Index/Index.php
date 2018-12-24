<?php
namespace App\Controller\Index;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Sale as SaleService;

class Index extends BaseController
{
    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * 健康检查
     */
    public function health()
    {
        return 'hello world!';
    }

    /**
     * 默认方法
     * @return string
     */
    public function index()
    {
        return 'hello world!';
    }

    /**
     * 获取短信验证码
     */
    public function getCode()
    {
        $number = $this->params['number'] ?? null;
        $result = SaleService::getInstance()->getCode($number);
        return $result;
    }

    /**
     * 登录
     */
    public function login()
    {
        $result = false;
        $number = $this->params['number'] ?? null;
        $code = $this->params['code'] ?? null;
        $user = SaleService::getInstance()->login($number, $code);
        if ($user) {
            $this->setToken($user);
            $result = true;
        }
        return $result;
    }

    /**
     * 预约挂号首页
     */
    public function test()
    {
        $cardNo = '00000005';
        $result = SaleService::getInstance()->getRecord($cardNo);
        return $result;
        // $cardNo = $this->user->cardno;
//        $cardNo = '00000005';
//        $result = SaleService::getInstance()->getSource($cardNo);
//        $result = SaleService::getInstance()->getSource("010101", '2018-12-23');
//        $params = [
//            "kh"=> "00000005",
//            "ysbh"=>"2",
//            "zzks"=>"010101",
//            "ghrq"=> "2018-12-23",
//            "ghlb"=>"1",
//            "ysh_lx"=>"1",
//        ];
//        $result = SaleService::getInstance()->checkIn($params['kh'], $params['ysbh'], $params['zzks'], $params['ghrq'], $params['ghlb'], $params['ysh_lx']);
//        return $result;
//        $result = SaleService::getInstance()->receiveNo(62, 123456, 1, 1);
//       return $result;
//        $result = SaleService::getInstance()->cancelNo(64);
//        return $result;
    }
}
