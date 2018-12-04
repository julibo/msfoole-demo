<?php
/**
 * 服务基类
 */

namespace App\Service;

abstract class BaseServer
{
    protected static $instance;

    public function __construct()
    {
        $this->init();
    }

    public static function getInstance() :self
    {
        if (!isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    abstract protected function init();
}
