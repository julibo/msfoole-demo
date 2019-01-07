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
        $name = md5(static::class);
        if (empty(self::$instance[$name])) {
            self::$instance[$name] = new static;
        }
        return self::$instance[$name];
    }

    abstract protected function init();
}

