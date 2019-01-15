<?php
/**
 * 微信绑卡模型
 */

namespace App\Model;

use Julibo\Msfoole\Facade\Log;

class WechatCard  extends BaseModel
{
    public static $table = 'bx_wechat_card';

    protected function init()
    {

    }

    /**
     * 查看绑卡列表
     * @param string $openid
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getBindCard(string $openid)
    {
        $result = $this->db
            ->where('openid', $openid)
            ->select();
        Log::sql("查询已绑就诊卡：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 绑定就诊卡
     * @param string $openid
     * @param array $params
     * @return int|string
     */
    public function bindCard(string $openid, array $params)
    {
        $result = $this->db
            ->insert([
                'openid'  => $openid,
                'cardno' => $params['cardno'],
                'name' => $params['name'],
                'idcard' => $params['idcard'],
                'mobile' => $params['mobile'],
                'default' => $params['default']
            ]);
        Log::sql("绑定就诊卡：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 设置默认就诊卡
     * @param string $openid
     * @param string $id
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\PDOException
     */
    public function defaultCard(string $openid, string $id)
    {
        $this->db
            ->where('openid', $openid)
            ->update([
                'default' => 0
            ]);

        $result = $this->db
            ->where('id', $id)
            ->update([
                'default' => 1
            ]);
        Log::sql("设置默认就诊卡：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 查看就诊卡
     * @param string $openid
     * @param string $id
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function showCard(string $openid, string $id)
    {
        $result = $this->db
            ->where('id', $id)
            ->where('openid', $openid)
            ->find();
        Log::sql("查询就诊卡：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 解绑就诊卡
     * @param string $openid
     * @param string $id
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\PDOException
     */
    public function delCard(string $openid, string $id)
    {
        $result = $this->db
            ->where('id', $id)
            ->where('openid', $openid)
            ->delete();
        Log::sql("解绑就诊卡：" . $this->db->getLastSql());
        return $result;
    }


    public function getDefaultCard(string $openid)
    {
        $result = $this->db
            ->where('default', 1)
            ->where('openid', $openid)
            ->find();
        Log::sql("查询默认就诊卡：" . $this->db->getLastSql());
        return $result;
    }

}