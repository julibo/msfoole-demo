<?php
/**
 * 官网预约挂号服务
 */
namespace App\Service;

class Sale extends BaseServer
{

    protected function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * 通过卡号或手机号查询用户
     * 查询到用户信息，将用户信息缓存起来
     * @param string $number
     * @return bool
     */
    public function getCode($number) : bool
    {
        // 通过卡号或手机号查询用户

        // 将用户和随机码缓存起来
//        if ($user) {
//
//        }
        return true;
    }

    /**
     * 根据编号和随机码组成的key查询对应用户信息，完成登录验证
     * @param $number
     * @param $code
     * @return array
     */
    public function login($number, $code) : array
    {

    }

    /**
     * 获取预约挂号记录
     */
    public function getRecord()
    {


    }

    /**
     * 获取医院科室列表
     */
    public function getOffices() : array
    {
        return [];
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
