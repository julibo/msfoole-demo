<?php
/**
 * 消息通知类
 */

namespace App\Lib\Helper;

class Message
{
    const WEBHOOK = 'https://oapi.dingtalk.com/robot/send?access_token=84fe66b00d96a4492df3da0c3f8f49735c2b505c17c68f4f97cf77a73fda8336';

    const SMSKEY = 'b583e0692cd9dbc586ef';
    const SMSUSER = 'zgsof';

    public static function request_by_curl($remote_server, $post_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public static function sendDingRobotTxt(string $message)
    {
        $result = false;
        $data = array ('msgtype' => 'text','text' => array ('content' => $message));
        $data_string = json_encode($data);
        $apiData = self::request_by_curl(self::WEBHOOK, $data_string);
        $apiData = json_decode($apiData);
        if (!empty($apiData) && isset($apiData->errcode) && $apiData->err == 0) {
            $result = true;
        }
        return $result;
    }

    public static function Get($url)
    {
        $ch = curl_init();
        // curl_init()需要php_curl.dll扩展
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;
    }

    /**
     * http://www.smschinese.cn#zgsof#nana102030
     * @param $mobile
     * @param $txt
     * @return mixed
     */
    public static function sendSms($mobile, $txt)
    {
        $url = sprintf("http://utf8.api.smschinese.cn/?Uid=%s&Key=%s&smsMob=%s&smsText=%s",
            self::SMSUSER, self::SMSKEY, $mobile, $txt );
        $result = self::Get($url);
        return $result;
    }
}