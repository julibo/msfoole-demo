<?php
/**
 * 后台管理类
 */

namespace App\Service;

use Julibo\Msfoole\Exception;

class Admin extends BaseServer
{
    private $cache;

    protected function init()
    {

    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function login($username, $password)
    {
        $result = [];
        if ($username == 'admin' && $password == '123456') {
            $result = [
                'username' => 'admin',
                'role' => '管理员',
                'nickname' => 'carson'
            ];
        }
        return $result;
    }

}