<?php
namespace App\Controller;

use Julibo\Msfoole\HttpController as BaseController;

class Health extends BaseController
{
    protected function init()
    {
        // TODO: Implement init() method.
    }

    public function index()
    {
        return 'hello world!';
    }
}
