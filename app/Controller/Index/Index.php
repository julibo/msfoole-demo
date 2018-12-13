<?php
namespace App\Controller\Index;

use Julibo\Msfoole\HttpController as BaseController;

class Index extends BaseController
{
    protected function init()
    {
        // TODO: Implement init() method.
    }

    public function index()
    {
        return 'hello world!';
    }

    public function login()
    {
        $this->setToken(['name'=>'carson', 'age'=>30]);
    }

    public function getUser()
    {
        return $this->getUserByToken();
    }

}
