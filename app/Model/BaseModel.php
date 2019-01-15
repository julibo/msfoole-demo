<?php
/**
 * 模型层基类
 */

namespace App\Model;

use think\Db;
use Julibo\Msfoole\Facade\Config;

abstract class BaseModel
{

    /**
     * 表名
     * @var
     */
    protected static $table;

    /**
     * @var \think\db\Query
     */
    protected $db;

    /**
     * 构造方法
     * BaseModel constructor.
     * @param $table
     * @param array $config
     */
    private function __construct($table, $config = [])
    {
        $defaultConfig = Config::get('database.default');
        $config = array_merge($defaultConfig, $config);
        Db::setConfig($config);
        $this->db = Db::table($table);
        $this->init();
    }

    /**
     * 初始化
     * @return mixed
     */
    abstract protected function init();

    /**
     * 实例化
     * @param array $config
     * @return mixed
     */
    public static function getInstance($config = [])
    {
        return new static(static::$table, $config);
    }
}
