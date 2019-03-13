<?php
/**
 * 公共网关
 */

namespace App\Controller\Admin;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Admin as AdminService;

class Gateway extends BaseController
{
    private $server;

    protected function init()
    {
        $this->server = AdminService::getInstance();
        $this->server->setCache($this->cache);
    }

    /**
     * 网关登录
     * @return bool
     */
    public function login()
    {
        $result = false;
        $username = $this->params['username'] ?? null;
        $password = $this->params['password'] ?? null;
        $user = $this->server->login($username, $password);
        if ($user) {
            $this->setToken($user);
            $result = true;
        }
        return $result;
    }
}