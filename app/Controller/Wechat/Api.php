<?php
/**
 * 微信接口入口方法
 */

namespace App\Controller\Wechat;

use App\Service\Wechat;
use Julibo\Msfoole\HttpController as BaseController;

class Api extends BaseController
{
    public $wechat;

    protected function init()
    {
        $this->wechat = Wechat::getInstance();
        $this->wechat->setParam($this->request->getRequestMethod(), $this->request->getParams())
    }

    public function index()
    {
        $result = Wechat::getInstance()->valid();
        return $result;
    }

}