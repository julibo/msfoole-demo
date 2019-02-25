<?php
/**
 * 微信公众号平台
 */

namespace App\Controller\Wechat;

use App\Service\Wechat;
use Julibo\Msfoole\HttpController as BaseController;
use Julibo\Msfoole\Facade\Config;

class Api extends BaseController
{
    /**
     * @var
     */
    public $wechat;

    /**
     * 初始化操作
     * @return mixed|void
     */
    protected function init()
    {
        $this->wechat = Wechat::getInstance();
        $this->wechat->setIP($this->request->remote_addr);
        $this->wechat->setCache($this->cache);
        $this->wechat->setParam($this->request->getRequestMethod(), $this->request->input);
    }

    /**
     * JS-SDK签名
     * @return mixed
     */
    public function signature()
    {
        $url = $this->params['url'];
        return $this->wechat->getJsSign($url);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $result = $this->wechat->valid();
        return $result;
    }

    /**
     * @return mixed
     */
    public function bridge()
    {
        $result = $this->wechat->bridge($this->params);
        return $result;
    }

    /**
     * 创建菜单
     * @return mixed
     */
    public function createMenu()
    {
        $callback = Config::get('wechat.baseurl') . '/Wechat/Api/bridge';
        $data = array (
      	    'button' => array (
                0 => array (
                    'name' => '医院概况',
                    'sub_button' => array (
                        0 => array (
                            'type' => 'view',
                            'name' => '医院简介',
                            'url' => 'https://mp.weixin.qq.com/s/njEgKwbzdqRrEw06GPbgLA',
                        ),
                        1 => array (
                            'type' => 'view',
                            'name' => '科室介绍',
                            'url' => 'https://mp.weixin.qq.com/s/YObnWbM1QrMx7yKVP72kSQ',
                        ),
                        2 => array (
                            'type' => 'view',
                            'name' => '医生简介',
                            'url' => 'https://mp.weixin.qq.com/s/xHRhW21PJ1ydEm_Y8Ilt3A',
                        ),
                        3 => array (
                            'type' => 'view',
                            'name' => '楼层分布',
                            'url' => 'https://mp.weixin.qq.com/s/isRsKv_zsU-DgFiNXlS3ww',
                        ),
                        4 => array (
                            'type' => 'view',
                            'name' => '交通导航',
                            'url' => $this->wechat->getOauthRedirect($callback, 'navigation'),
                        ),
                    ),
                ),
                1 => array (
                    'name' => '诊疗服务',
                    'sub_button' => array (
                        0 => array (
                            'type' => 'view',
                            'name' => '当日挂号',
                            'url' => $this->wechat->getOauthRedirect($callback, 'todayReg'),
                        ),
                        1 => array (
                            'type' => 'view',
                            'name' => '预约挂号',
                            'url' => $this->wechat->getOauthRedirect($callback, 'register'),
                        ),
                        2 => array (
                            'type' => 'view',
                            'name' => '门诊缴费',
                            'url' => $this->wechat->getOauthRedirect($callback, 'pay'),
                        ),
                        3 => array (
                            'type' => 'view',
                            'name' => '住院费预交',
                            'url' => $this->wechat->getOauthRedirect($callback, 'hospitalization'),
                        ),
                        4 => array (
                            'type' => 'view',
                            'name' => '健康宣教',
                            'url' => 'https://mp.weixin.qq.com/s/v0sHVhXD89p3exMJpr1ClA',
                        )
                    ),
                ),
     	        2 => array (
                    'name' => '我的',
                        'sub_button' => array (
                            0 => array (
                                'type' => 'view',
                                'name' => '我的就诊卡',
                                'url' => $this->wechat->getOauthRedirect($callback, 'userCard'),
                            ),
                            1 => array (
                                'type' => 'view',
                                'name' => '挂号记录',
                                'url' => $this->wechat->getOauthRedirect($callback, 'regRecord'),
                            ),
                            2 => array (
                                'type' => 'view',
                                'name' => '缴费记录',
                                'url' => $this->wechat->getOauthRedirect($callback, 'payRecord'),
                            ),
                            3 => array (
                                'type' => 'view',
                                'name' => '检查报告',
                                'url' => $this->wechat->getOauthRedirect($callback, 'report'),
                            ),
                    ),
     	        ),
      	    ),
      	);
        $this->wechat->createMenu($data);
        return null;
    }

    /**
     * 查询菜单
     * @return mixed
     */
    public function getMenu()
    {
        $result = $this->wechat->getMenu();
        return $result;
    }

    /**
     * 删除菜单
     * @return mixed
     */
    public function deleteMenu()
    {
        $result = $this->wechat->deleteMenu();
        return $result;
    }

}
