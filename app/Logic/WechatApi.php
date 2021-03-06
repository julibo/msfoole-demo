<?php
/**
 * 微信公众号
 */
namespace App\Logic;

use App\Lib\Wechat\Wechat;
use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Facade\Log;
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

    public function checkAuth($appid='',$appsecret='',$token='')
    {
        $authname = 'wechat_access_token'.$this->appid;
        if (false && $rs = $this->getCache($authname))  {
            $this->access_token = $rs;
            return $rs;
        } else {
            // $this->access_token = $this->http_get('http://45.40.202.228/wechat.php');
            // $this->setCache($authname, $this->access_token, 3600);
            $this->access_token = $this->http_get('http://45.40.202.228/wechat.php?token=61000caa27589aba2cfa42723506ec85');
            return $this->access_token;
        }
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