<?php
/**
 * 微信接口入口方法
 */

namespace App\Controller\Wechat;

use App\Service\Wechat;

class Api
{
    public function index()
    {
        $result = Wechat::getInstance()->valid();
        return $result;
    }

}