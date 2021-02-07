<?php


namespace App\Model;

use Julibo\Msfoole\Facade\Log;

class XinGuan extends BaseModel
{
    public static $table = 'bx_xinguan';

    protected function init()
    {

    }

    /**
     * 新冠初筛
     * @param array $params
     * @return int|string
     */
    public function chushai(array $params)
    {
        $result = $this->db
            ->insert([
                'card_no'  => $params['cardNo'],
                'card_name' => $params['cardName'],
                'id_card' => $params['idCard'],
                'congye' => $params['congye'],
                'juzhu' => $params['juzhu'],
                'jiechu' => $params['jiechu'],
                'shequ' => $params['shequ'],
                'juji' => $params['juji'],
                'jiezhong' => $params['jiezhong'],
                'date' => date('Ymd')
            ]);
        Log::sql("提交新冠初筛：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 查询重复记录
     */
    public function findRepeat($idCard)
    {
        $result = $this->db
            ->where('id_card', $idCard)
            ->where('date', date('Ymd'))
            ->find();
        Log::sql("查询重复记录：" . $this->db->getLastSql());
        return $result;
    }
}
