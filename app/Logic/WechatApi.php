<?php
/**
 * 微信公众号
 */
namespace App\Logic;

use App\Lib\Wechat\Wechat;
use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Facade\Log;
use Julibo\Msfoole\Exception;
use Julibo\Msfoole\Cache;

class WechatApi extends Wechat
{
    private $cache;

    public function __construct($options)
    {
        parent::__construct($options);
        $cacheConfig = Config::get('cache.default') ?? [];
        $this->cache = new Cache($cacheConfig);
    }

    /**
     * log overwrite
     * @see Wechat::log()
     */
    protected function log ($log)
    {
        if ($this->debug) {
            Log::debug('wechat：{msg}', ['msg'=>$log]);
        }
        return false;
    }

    /**
     * 重载设置缓存
     * @param string $cachename
     * @param mixed $value
     * @param int $expired
     * @return boolean
     */
    protected function setCache($cachename,$value,$expired)
    {
        return $this->cache->set($cachename, $value, $expired);
    }

    /**
     * 重载获取缓存
     * @param string $cachename
     * @return mixed
     */
    protected function getCache($cachename)
    {
        return $this->cache->get($cachename);
    }

    /**
     * 重载清除缓存
     * @param string $cachename
     * @return boolean
     */
    protected function removeCache($cachename)
    {
        return $this->cache->del($cachename);
    }
}