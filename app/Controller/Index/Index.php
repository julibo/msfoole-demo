<?php
namespace App\Controller\Index;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Sale as SaleService;

class Index extends BaseController
{
    private $server;

    protected function init()
    {
        // TODO: Implement init() method.
        $this->server = SaleService::getInstance();
        $this->server->cache = $this->cache;
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
        if ($number) {
            $number = str_pad($number,8,'0',STR_PAD_LEFT);
        }
        $result = $this->server->getCode($number);
        return $result;
    }

    /**
     * 登录
     */
    public function login()
    {
        $result = false;
        $number = $this->params['number'] ?? null;
        if ($number) {
            $number = str_pad($number,8,'0',STR_PAD_LEFT);
        }
        $code = $this->params['code'] ?? null;
        $user = $this->server->login($number, $code);
        if ($user) {
            $this->setToken($user);
            $result = true;
        }
        return $result;
    }

}
