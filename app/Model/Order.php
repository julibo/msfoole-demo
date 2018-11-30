<?php
/**
 * 订单模型类
 */

namespace App\Model;

use Julibo\Msfoole\Helper;

class Order extends BaseModel
{

    protected function init()
    {

    }

    private function getOrderID($cardno)
    {
        $cardno = substr($cardno, -5);
        $time = dechex(microtime(true)*1000 - strtotime(date('Y-m-d')) * 1000);
        $orderid = sprintf('%s%s%07s', date('ymd'), $cardno, $time);
        return $orderid;
    }

    /**
     * 创建挂号订单
     * @param $cardno 卡号
     * @param $ysbh 医生编号
     * @param $bb 班别
     * @param $zfje 金额
     * @param $zfzl 支付方式
     * @param int $type
     * @param int $source
     * @return array|bool
     */
    public function createRegOrder($cardno, $ysbh, $bb, $zfje, $zfzl, $type = 1, $source = 1)
    {
        $info = ['cardno' => $cardno, 'ysbh' => $ysbh, 'bb' => $bb, 'zfje' => $zfje, 'zfzl'=> $zfzl];
        $nonce_str = Helper::guid();
        $orderID = $this->getOrderID($cardno);
        $data = [
            'out_trade_no' => $orderID,
            'group' => 1,
            'info' => json_encode($info),
            'type' => $type,
            'source' => $source,
            'method' => $zfzl,
            'body' => '挂号费',
            'total_fee' => $zfje * 100,
            'time_start' => date('Y-m-d H:i:s'),
            'time_expire' => date('Y-m-d H:i:s', strtotime('2 minute')),
            'nonce_str' => $nonce_str,
        ];

        $insertResult = $this->db->data($data)->insert();
        if ($insertResult) {
            return $data;
        } else {
            return false;
        }
    }
}