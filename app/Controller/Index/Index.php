<?php
namespace App\Controller\Index;

use Julibo\Msfoole\HttpController as BaseController;
use App\Service\Sale as SaleService;
use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Cache;

class Index extends BaseController
{
    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * 健康检查
     */
    public function health()
    {
        return 'hello world!';
    }

    /**
     * 默认方法
     * @return string
     */
    public function index()
    {
        $cacheConfig = Config::get('cache.default') ?? [];
        $cache = new Cache($cacheConfig);
        $cache->set('123456', json_decode('{"openid":"123456","nickname": "carson","sex":"1","province":"PROVINCE","city":"CITY","country":"COUNTRY","headimgurl":"http://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46"}'));
        return 'hello world!';
    }

    /**
     * 获取短信验证码
     */
    public function getCode()
    {
        $number = $this->params['number'] ?? null;
        $result = SaleService::getInstance()->getCode($number);
        return $result;
    }

    /**
     * 登录
     */
    public function login()
    {
        $result = false;
        $number = $this->params['number'] ?? null;
        $code = $this->params['code'] ?? null;
        $user = SaleService::getInstance()->login($number, $code);
        if ($user) {
            $this->setToken($user);
            $result = true;
        }
        return $result;
    }
}
