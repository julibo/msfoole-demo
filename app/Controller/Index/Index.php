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
}
