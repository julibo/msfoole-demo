<?php
/**
 * 管理员账户
 */

namespace App\Model;

use Julibo\Msfoole\Facade\Log;

class Manager extends BaseModel
{
    public static $table = 'bx_manager_account';

    protected function init()
    {

    }

    /**
     * 管理员列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getManager()
    {
        $result = $this->db->select();
        Log::sql("查询管理员列表：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * @param $name
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function findManager($name)
    {
        $result = $this->db->where('name', $name)->find();
        Log::sql("查询管理员：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 删除管理员
     * @param $id
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\PDOException
     */
    public function delManager($id)
    {
        $result = $this->db->where('id',$id)
            ->where('super',0)
            ->delete();
        Log::sql("删除管理员：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * @param array $data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\db\exception\PDOException
     */
    public function saveManager(array $data)
    {
        $result = $this->db->update($data);
        Log::sql("更新管理员：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * @param array $data
     * @return int|string
     */
    public function addManager(array $data)
    {
        $result = $this->db->data($data, true)
            ->insert();
        Log::sql("创建管理员：" . $this->db->getLastSql());
        return $result;
    }
}