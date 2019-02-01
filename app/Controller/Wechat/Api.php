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
        $this->wechat->cache = $this->cache;
        $input = $this->request->input;
        $this->wechat->setParam($this->request->getRequestMethod(), $input);
        $this->wechat->ip = $this->request->remote_addr;
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
                            'url' => $this->wechat->getOauthRedirect($callback, 'intro'),
                        ),
                        1 => array (
                            'type' => 'view',
                            'name' => '科室介绍',
                            'url' => $this->wechat->getOauthRedirect($callback, 'department'),
                        ),
                        2 => array (
                            'type' => 'view',
                            'name' => '名医专家',
                            'url' => $this->wechat->getOauthRedirect($callback, 'famous'),
                        ),
                        3 => array (
                            'type' => 'view',
                            'name' => '楼层分布',
                            'url' => $this->wechat->getOauthRedirect($callback, 'floor'),
                        ),
                        4 => array (
                            'type' => 'view',
                            'name' => '交通指南',
                            'url' => $this->wechat->getOauthRedirect($callback, 'guide'),
                        ),
                    ),
                ),
                1 => array (
                    'name' => '诊疗服务',
                    'sub_button' => array (
                        0 => array (
                            'type' => 'view',
                            'name' => '挂号',
                            'url' => $this->wechat->getOauthRedirect($callback, 'register'),
                        ),
                        1 => array (
                            'type' => 'view',
                            'name' => '门诊缴费',
                            'url' => $this->wechat->getOauthRedirect($callback, 'pay'),
                        ),
                        2 => array (
                            'type' => 'view',
                            'name' => '住院费预交',
                            'url' => $this->wechat->getOauthRedirect($callback, 'hospitalization'),
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
