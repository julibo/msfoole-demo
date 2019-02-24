<?php
/**
 * 服务基类
 */

namespace App\Service;

abstract class BaseServer
{
    protected static $instance = [];

    public function __construct()
    {
        $this->init();
    }

    public static function getInstance() :self
    {
        return new static;
    }

    abstract protected function init();
}
