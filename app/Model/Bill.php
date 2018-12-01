<?php
/**
 * 对账单
 */

namespace App\Model;


class Bill extends BaseModel
{

    public static $table = 'bx_bill';

    protected function init()
    {

    }

    /**
     * 添加交易记录
     * @param $payTime
     * @param $orderNo
     * @param $office
     * @param $doctor
     * @param $patient
     * @param $money
     * @param int $type
     * @return int|string
     */
    public function insertBill($payTime, $orderNo, $office, $doctor, $patient, $money, $type = 0 )
    {
        $year = date('Y', $payTime);
        $month = date('Ym', $payTime);
        $date = date('Y-m-d', $payTime);

        $data = [
            'year' => $year,
            'month' => $month,
            'date' => $date,
            'pay_time' => date('Y-m-d H:i:s', $payTime),
            'order_no' => $orderNo,
            'office' => $office,
            'doctor' => $doctor,
            'patient' => $patient,
            'money' => $money,
            'type' => $type,
        ];

        $insertResult = $this->db->data($data)->insert();
        return $insertResult;
    }

    /**
     * 审核对账单
     * @param $id
     * @param $status
     * @param $operator
     * @return int|string
     * @throws \think\Exception
     * @throws \think\db\exception\PDOException
     */
    public function updateDate($id, $status, $operator)
    {
        $result = $this->db
            ->where('id', $id)
            ->update([
                'operator' => $operator,
                'status'  => $status
            ]);
        return $result;
    }
}