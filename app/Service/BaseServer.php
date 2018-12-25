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
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }
        return new static;
    }

    abstract protected function init();
}

