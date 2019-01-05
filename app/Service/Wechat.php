<?php
/**
 * 微信服务类
 */

namespace App\Service;


use Julibo\Msfoole\Exception;
use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Cache;
use Julibo\Msfoole\Facade\Log;
use App\Logic\WechatApi;
use App\Model\WechatCard as WechatCardModel;


class Wechat extends BaseServer
{
    /**
     * @var
     */
    public $weObj;

    /**
     * @var
     */
    public $cache;

    /**
     * 初始化服务
     */
    public function init()
    {
        $options = Config::get('wechat.option');
        $this->weObj = new WechatApi($options);
        $cacheConfig = Config::get('cache.default') ?? [];
        $this->cache = new Cache($cacheConfig);
    }

    /**
     * 参数传递
     * @param string $requestMethod
     * @param array $param
     */
    public function setParam(string $requestMethod, array $param)
    {
        $this->weObj->requestMethod = $requestMethod;
        $_GET = $param;
    }

    /**
     * 验证服务
     * @param bool $return
     * @return null
     */
    public function valid($return = true)
    {
        $result = $this->weObj->valid($return);
        if ($result === true) {
            return $result;
        } else if ($result === false) {
            return $result;
        } else {
            echo $result;
            return null;
        }
    }

    /**
     * 菜单链接
     * @param array $params
     * @throws Exception
     */
    public function bridge(array $params)
    {
        $_GET = $params;
        $token = $this->weObj->getOauthAccessToken();
        if ($token == false) {
            throw new Exception('换取网页授权失败', '20');
        }
        $user = $this->weObj->getOauthUserinfo($token['access_token'], $token['openid']);
        if ($user == false) {
            throw new Exception('拉取用户信息失败', '30');
        }
        // 缓存用户信息
        $this->cache->set($token['access_token'], $user);
        // 用户授权成功
        $this->jumpUrl($params['state'], $user['openid']);
    }

    /**
     * 页面跳转
     * @param string $state
     * @param string $openid
     * @throws Exception
     */
    public function jumpUrl(string $state, string $openid)
    {
        $url = Config::get('wechat.baseurl');
        switch ($state) {
            case 'register' :
                $url .= '/register';
                break;
            case 'pay' :
                $url .= '/pay';
                break;
            case 'userCard' :
                $url .= '/user/card';
                break;
            case 'regRecord' :
                $url .= '/user/regRecord';
                break;
            case 'payRecord' :
                $url .= '/user/payRecord';
                break;
            case 'report' :
                $url .= '/user/report';
                break;
            case 'doctor' :
                $url .= '/user/doctor';
                break;
            default:
                $url .= '/user/index';
                break;
        }
        throw new Exception($url . '?token='.$openid, 301);
    }

    /**
     * 魔术方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $result = $this->weObj->$name(...$arguments);
        return $result;
    }

    /**
     * 创建菜单
     * @param array $data
     * @return mixed
     */
    public function createMenu(array $data)
    {
        $result = $this->weObj->createMenu($data);
        return $result;
    }



}