<?php
/**
 * 管理员登录记录
 */

namespace App\Model;

use Julibo\Msfoole\Facade\Log;

class Login  extends BaseModel
{
    public static $table = 'bx_manager_login';

    protected function init()
    {

    }

    /**
     * 最近10条登录记录
     * @param $mid
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLoginRecord($mid)
    {
        $result = $this->db->where('mid', $mid)
            ->order('id', 'desc')
            ->limit(10)
            ->select();
        Log::sql("查询登录记录：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * @param $mid
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLastLogin($mid)
    {
        $result = $this->db->where('mid', $mid)
            ->order('id', 'desc')
            ->find();
        Log::sql("查询登录记录：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 插入登录记录
     * @param $data
     * @return int|string
     */
    public function insetLogin($data)
    {
        $result = $this->db->data($data)
            ->insert();
        Log::sql("插入登录记录：" . $this->db->getLastSql());
        return $result;
    }

}