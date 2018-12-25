<?php
/**
 * 微信服务类
 */

namespace App\Service;

use App\Logic\WechatApi;
use Julibo\Msfoole\Facade\Config;

class Wechat extends BaseServer
{
    public $weObj;

    public function init()
    {
        $options = Config::get('wechat.option');
        $this->weObj = new WechatApi($options);
    }

    public function setParam($requestMethod, $param)
    {
        $this->weObj->requestMethod = $requestMethod;
        $_GET = $param;
    }

    public function valid($return = true)
    {
        $this->weObj->valid($return);
    }




}