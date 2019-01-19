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
            $type = $this->weObj->getRev()->getRevType();
            switch($type) {
                case WechatApi::EVENT_SUBSCRIBE: //  订阅
                    $this->subscribe();
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
        throw new Exception($url . '/?token='.$openid, 301);
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
     * @return mixed
     */
    public function sendTemplateMessageOrder($openid, $url, $name, $ksmc, $ysxm)
    {
        $template_id = Config::get('wechat.template.order');

        $data = [
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'color' => '#606266',
            'data' => [
                'title' => [
                    'value' => '预约挂号成功通知',
                    "color"=>"#67C23A"
                ],
                'name' => [
                    'value' => '于占伟',
                    "color"=>"#606266"
                ],
                'ksmc' => [
                    'value' => '门诊内科',
                    "color"=>"#606266"
                ],
                'mzlx' => [
                    'value' => '普通门诊',
                    "color"=>"#606266"
                ],
                'jzsj' => [
                    'value' => '2019-01-13',
                    "color"=>"#606266"
                ],
                'jzdd' => [
                    'value' => '内科三诊室',
                    "color"=>"#606266"
                ],
                'remark' => [
                    'value' => '您的就诊序号为29，无需区号，请与1月23日上午前来就诊',
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
     * @param $name
     * @param $ksmc
     * @param $ysxm
     * @return mixed
     */
    public function sendTemplateMessagePayment($openid, $url, $name, $ksmc, $ysxm)
    {
        $template_id = Config::get('wechat.template.payment');

        $data = [
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'color' => '#606266',
            'data' => [
                'title' => [
                    'value' => '预约挂号成功通知',
                    "color"=>"#67C23A"
                ],
                'name' => [
                    'value' => '于占伟',
                    "color"=>"#606266"
                ],
                'ksmc' => [
                    'value' => '门诊内科',
                    "color"=>"#606266"
                ],
                'mzlx' => [
                    'value' => '普通门诊',
                    "color"=>"#606266"
                ],
                'jzsj' => [
                    'value' => '2019-01-13',
                    "color"=>"#606266"
                ],
                'jzdd' => [
                    'value' => '内科三诊室',
                    "color"=>"#606266"
                ],
                'remark' => [
                    'value' => '您的就诊序号为29，无需区号，请与1月23日上午前来就诊',
                    "color"=>"#E6A23C"
                ],
            ]
        ];
        $result = $this->weObj->sendTemplateMessage($data);
        return $result;
    }

}