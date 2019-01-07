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
        $this->wechat->setParam($this->request->getRequestMethod(), $this->request->getParams());
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
                    'name' => '挂号',
                    'type' => 'view',
                    'url' => $this->wechat->getOauthRedirect($callback, 'register'),
                ),
                1 => array (
                    'name' => '缴费',
                    'type' => 'view',
                    'url' => $this->wechat->getOauthRedirect($callback, 'pay'),
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
                            4 => array (
                                'type' => 'view',
                                'name' => '我的医生',
                                'url' => $this->wechat->getOauthRedirect($callback, 'doctor'),
                            )
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
        $result = $this->wechat->getMenu(1,3);
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
