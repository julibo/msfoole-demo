<?php
/**
 * 自助挂号终端机
 */

namespace App\Service;

use App\Logic\HospitalApi;

class Robot extends BaseServer
{

    protected function init()
    {
        // TODO: Implement init() method.

    }

    /**
     * 获取医院科室列表
     */
    public function getDepartment() : array
    {
        $result = HospitalApi::getInstance()->apiClient('ksxx');
        return $result;
    }

    /**
     * 获取科室号源列表
     */
    public function getSourceList() : array
    {
        $result = HospitalApi::getInstance()->apiClient('ysxx', ['ksbm'=>'010101']);
        return $result;
    }

    /**
     * 获取病员信息
     */
    public function getUserInfo() : array
    {
        $result = HospitalApi::getInstance()->apiClient('byxx', ['kh'=>'00000005']);
        return $result;
    }

    public function register()
    {
        $content = [
            "kh" => "00000005",
            "ysbh" => "1",
            "bb" => "2",
            "zfje" => "11",
            "zfzl" => "1",
            "sjh" => "a0001"
        ];
        $result = HospitalApi::getInstance()->apiClient('ghdj', $content);
        return $result;
    }

    public function cancel()
    {
        $content = [
            "kh"=> "00000005",
		    "mzh"=> "1811240003"
        ];
        $result = HospitalApi::getInstance()->apiClient('ghdjzf', $content);
        return $result;
    }

}