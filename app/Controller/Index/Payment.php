<?php
/**
 * 第三方支付回调
 */

namespace App\Controller\Index;

use Julibo\Msfoole\HttpController as BaseController;

use App\Service\Robot as RobotServer;

class Payment extends BaseController
{
    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * 支付回调
     */
    public function callbackWFT()
    {
        $input = $this->request->input;
        $result = RobotServer::getInstance()->callbackWFT($input);
        return $result;
    }
}
