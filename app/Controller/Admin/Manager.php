<?php
/**
 * 管理员
 */

namespace App\Controller\Admin;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Admin as AdminService;

class Manager  extends BaseController
{
    private $server;

    protected function init()
    {
        $this->server = AdminService::getInstance();
        $this->server->setCache($this->cache);
    }

    /**
     * 账户管理页
     */
    public function index()
    {
        $result = $this->server->getManager();
        return $result;
    }

    /**
     * 新增账户
     */
    public function addManager()
    {
        $data = $this->params;
        $result = $this->server->addManager($data);
        return $result;
    }

    /**
     * 删除账户
     */
    public function delManager()
    {
        $id = $this->params['id'];
        $result = $this->server->delManager($id);
        return $result;
    }

    /**
     * 更新账户
     */
    public function updateManager()
    {
        $data = $this->params;
        $result = $this->server->updateManager($data);
        return $result;
    }

    /**
     * 最近登录记录
     */
    public function getLoginRecord()
    {
        $mid = $this->params['mid'];
        $result = $this->server->getLoginRecord($mid);
        return $result;
    }

    /**
     * 当前账户状态
     * @return mixed
     */
    public function currentUser()
    {
        $result = $this->user;
        return $result;
    }

}