<?php
/**
 * 微信服务类
 */

namespace App\Service;

use Julibo\Msfoole\Exception;
use Julibo\Msfoole\Facade\Config;
use App\Logic\WechatApi;

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
     * @var
     */
    public $ip;

    /**
     * 初始化服务
     */
    public function init()
    {
        $options = Config::get('wechat.option');
        $this->weObj = new WechatApi($options);
    }

    /**
     * 设置缓存
     * @param $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function setIP($ip)
    {
        $this->ip = $ip;
    }

    /**
     * 参数传递
     * @param string $requestMethod
     * @param $input
     */
    public function setParam(string $requestMethod, $input)
    {
        $this->weObj->requestMethod = $requestMethod;
        $this->weObj->input = $input;
    }

    /**
     * 验证服务
     * @param bool $return
     * @return null
     */
    public function valid($return = true)
    {
        $result = $this->weObj->valid($return);
        if (!is_bool($result) === true) {
            echo $result;
            return null;
        }
        if ($result == true) {
            $openid = $this->weObj->getRev()->getRevFrom();
            if (!$this->cache->get($openid)) {
                $this->cache->set($openid, ['openid'=>$openid]);
            }
            $type = $this->weObj->getRevType();
            switch($type) {
                case WechatApi::MSGTYPE_EVENT: // EVENT_SUBSCRIBE: //  订阅
                    $eventType = $this->weObj->getRevEvent();
                    if ($eventType['event'] == WechatApi::EVENT_SUBSCRIBE) {
                        $this->subscribe();
                    }
                    break;
                default:
                    echo "success";
               }
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
        $token = $this->weObj->getOauthAccessToken();
        if ($token == false) {
            throw new Exception('换取网页授权失败', '20');
        }
        $user = $this->weObj->getOauthUserinfo($token['access_token'], $token['openid']);
        if ($user == false) {
            throw new Exception('拉取用户信息失败', '30');
        }
        $user['ip'] = $this->ip;
        // 缓存用户信息
        $this->cache->set($token['openid'], $user);
        // 用户授权成功
        $this->jumpUrl($params['state'], $token['openid']);
    }

    /**
     * 页面跳转
     * @param string $state
     * @param string $openid
     * @throws Exception
     */
    public function jumpUrl(string $state, string $openid)
    {
        $url = sprintf('%s/?token=%s&path=%s', Config::get('wechat.baseurl'), $openid, $state);
        throw new Exception($url, 301);
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

    /**
     * 关注后提示欢迎信息
     */
    public function subscribe()
    {
        $msg = Config::get('wechat.welcome');
        $this->weObj->text($msg)->reply();
    }

    /**
     * 发送文本客服消息
     * @param $openid
     * @param $msg
     * @return mixed
     */
    public function sendCustomMessageText($openid, $msg)
    {
        $result = $this->weObj->sendCustomMessage([
            'touser'=>$openid,
            'msgtype'=>'text',
            'text'=>[
                'content'=>$msg
            ]]);
        return $result;
    }

    /**
     * 预约挂号成功通知
     * @param $openid
     * @param $url
     * @param $name
     * @param $ksmc
     * @param $ysxm
     * @param $jzsj
     * @param $mzh
     * @return mixed
     */
    public function sendTemplateMessageOrder($openid, $url, $name, $cardNo, $ksmc, $ysxm, $jzsj, $mzh)
    {
        $template_id = Config::get('wechat.template.order');
        $data = [
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'color' => '#606266',
            'data' => [
                'first' => [
                    'value' => '预约挂号成功',
                    "color"=>"#67C23A"
                ],
                'keyword1' => [
                    'value' => $name,
                    "color"=>"#606266"
                ],
                'keyword2' => [
                    'value' => $cardNo,
                    "color"=>"#606266"
                ],
                'keyword3' => [
                    'value' => $ksmc,
                    "color"=>"#606266"
                ],
                'keyword4' => [
                    'value' => $ysxm,
                    "color"=>"#606266"
                ],
                'keyword5' => [
                    'value' => $jzsj,
                    "color"=>"#606266"
                ],
                'remark' => [
                    'value' => '您的门诊号为'.$mzh.'，请于'.$jzsj.'前来就诊',
                    "color"=>"#E6A23C"
                ],
            ]
        ];
        $result = $this->weObj->sendTemplateMessage($data);
        return $result;
    }


    /**
     * 门诊缴费成功通知
     * @param $openid
     * @param $url
     * @param $mzh
     * @param $money
     * @param $name
     * @return mixed
     */
    public function sendTemplateMessagePayment($openid, $url, $mzh, $money, $name)
    {
        $template_id = Config::get('wechat.template.payment');

        $data = [
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'color' => '#606266',
            'data' => [
                'first' => [
                    'value' => '门诊缴费成功',
                    "color"=>"#67C23A"
                ],
                'keyword1' => [
                    'value' => $mzh,
                    "color"=>"#606266"
                ],
                'keyword2' => [
                    'value' => $money,
                    "color"=>"#409EFF"
                ],
                'keyword3' => [
                    'value' => $name,
                    "color"=>"#606266"
                ],
                'remark' => [
                    'value' => '祝您早日康复',
                    "color"=>"#E6A23C"
                ],
            ]
        ];
        $result = $this->weObj->sendTemplateMessage($data);
        return $result;
    }

    /**
     * 住院费预交成功通知
     * @param $openid
     * @param $url
     * @param $name
     * @param $cardNo
     * @param $date
     * @param $money
     * @param $orderNo
     * @return mixed
     */
    public function sendTemplateHospital($openid, $url, $name, $cardNo, $date, $money, $orderNo)
    {
        $template_id = Config::get('wechat.template.hospital');

        $data = [
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'color' => '#606266',
            'data' => [
                'first' => [
                    'value' => '住院费预交成功',
                    "color"=>"#67C23A"
                ],
                'keyword1' => [
                    'value' => $name,
                    "color"=>"#606266"
                ],
                'keyword2' => [
                    'value' => $cardNo,
                    "color"=>"#606266"
                ],
                'keyword3' => [
                    'value' => $date,
                    "color"=>"#606266"
                ],
                'keyword4' => [
                    'value' => $money,
                    "color"=>"#409EFF"
                ],
                'keyword5' => [
                    'value' => $orderNo,
                    "color"=>"#606266"
                ],
                'remark' => [
                    'value' => '祝您早日康复',
                    "color"=>"#E6A23C"
                ],
            ]
        ];
        $result = $this->weObj->sendTemplateMessage($data);
        return $result;
    }

}