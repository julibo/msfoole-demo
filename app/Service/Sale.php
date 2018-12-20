<?php
/**
 * 官网预约挂号服务
 */
namespace App\Service;

use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Cache;
use Julibo\Msfoole\Exception;
use App\Lib\Helper\Message;
use App\Logic\HospitalApi;

class Sale extends BaseServer
{

    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     *  通过卡号或手机号查询用户
     * 查询到用户信息，将用户信息缓存起来
     * @param $number
     * @return bool
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCode($number) : bool
    {
        // 通过卡号或手机号查询用户
        $user = HospitalApi::getInstance()->getUser($number);
        // 将用户和随机码缓存起来
        if (empty($user['mobile'])) {
            throw new Exception('手机号码没有绑定', 88);
        }
        $code = rand(100000,999999);
        $msgResult = Message::sendSms($user['mobile'], sprintf("您正在使用手机认证登录服务，您的短信验证码为%s。验证码切勿告知他人，以免造成不必要的困扰，此验证码10分钟内有效。", $code));
        $cacheConfig = Config::get('cache.default') ?? [];
        $cache = new Cache($cacheConfig);
        $info = [
            'user' => $user,
            'code' => $code,
        ];
        $cache->set($number, $info, 600);
        return $msgResult > 0 ? true : false;
    }

    /**
     * 根据编号和随机码组成的key查询对应用户信息，完成登录验证
     * @param $number
     * @param $code
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login($number, $code) : array
    {
        $result = [];
        $cacheConfig = Config::get('cache.default') ?? [];
        $cache = new Cache($cacheConfig);
        $user = $cache->get($number);
        if (!empty($user) && $user['code'] == $code) {
            $result = HospitalApi::getInstance()->getUser($number);
        }
        return $result;
    }

    /**
     * 获取预约挂号记录
     */
    public function getRecord()
    {


    }

    /**
     * 获取医院科室列表
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Julibo\Msfoole\Exception
     */
    public function getDepartment() : array
    {
        $result = [];
        $response = HospitalApi::getInstance()->apiClient('ksxx');
        if (!empty($response) && !empty($response['item'])) {
            $result = $response['item'];
        }
        return $result;
    }

    /**
     * 获取号源
     */
    public function getSource()
    {

    }

    /**
     * 创建订单，生成二维码
     */
    public function createOrder()
    {

    }

    /**
     * 刷新订单状态
     */
    public function refresh()
    {

    }

    /**
     * 取消预约
     */
    public function cancel()
    {

    }

}
