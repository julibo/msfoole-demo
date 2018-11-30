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
        $class = get_called_class();
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new static;
        }
        return self::$instance[$class];
    }

    abstract protected function init();
}