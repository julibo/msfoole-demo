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
     * 实例载体
     * @var array
     */
    protected static $instanse = [];

    /**
     * 表名
     * @var
     */
    protected $table;

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
        if (empty($config)) {
            $config = [
                'params' => [
                    \PDO::ATTR_PERSISTENT   => true,
                    \PDO::ATTR_CASE         => \PDO::CASE_LOWER,
                ]
            ];
        }
        $defaultConfig = Config::get('database.default');
        $config = array_merge($defaultConfig, $config);
        Db::setConfig($config);
        $this->table = $table;
        $this->db = Db::table($this->table);
    }

    /**
     * 初始化
     * @return mixed
     */
    abstract protected function init();

    /**
     * 实例化
     * @param $table
     * @param array $config
     * @return mixed
     */
    public static function getInstance($table, $config = [])
    {
        if (empty(self::$instanse[$table])) {
            self::$instanse[$table] = new static($table, $config);
        }
        return self::$instanse[$table];
    }
}