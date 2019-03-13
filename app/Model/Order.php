<?php
/**
 * 订单模型类
 */

namespace App\Model;

use think\Db;
use Julibo\Msfoole\Helper;
use Julibo\Msfoole\Facade\Log;

class Order extends BaseModel
{

    public static $table = 'bx_orders';

    protected function init()
    {

    }

    /**
     * 生成订单ID
     * @param $cardno
     * @return string
     */
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
     * @param $body 订单描述
     * @param $ip 客户端IP
     * @param int $group 订单类型， 1=挂号，2=缴费
     * @param int $type 接口， 1=威富通
     * @param int $source 订单来源，1=终端机
     * @param string $client 客户标识
     * @return array|bool
     */
    public function createRegOrder($cardno, $ysbh, $bb, $zfje, $zfzl, $body, $ip, $group = 1, $source = 1, $type = 1, $client = '')
    {
        $info = ['cardno' => $cardno, 'ysbh' => $ysbh, 'bb' => $bb, 'zfje' => $zfje, 'zfzl'=> $zfzl];
        $nonce_str = Helper::guid();
        $orderID = $this->getOrderID($cardno);
        $data = [
            'out_trade_no' => $orderID,
            'user' => $cardno,
            'group' => $group,
            'info' => json_encode($info),
            'type' => $type,
            'source' => $source,
            'client' => $client,
            'method' => $zfzl,
            'body' => $body,
            'total_fee' => $zfje * 100,
            'mch_create_ip' => $ip,
            'time_start' => date('Y-m-d H:i:s'),
            'time_expire' => date('Y-m-d H:i:s', strtotime('10 minute')),
            'nonce_str' => $nonce_str,
            'months' => date('Ym'),
            'dates' => date('Ymd'),
        ];
        $insertResult = $this->db->data($data)->insert();
        Log::sql("创建挂号订单：" . $this->db->getLastSql());
        if ($insertResult) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 根据单号查询订单
     * @param $out_trade_no
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderByTradeNo($out_trade_no)
    {
        $result = $this->db
            ->where('out_trade_no', $out_trade_no)
            ->find();
        Log::sql("根据单号查询订单：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 订单列表查询
     * @param array $condition
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderList(array $condition, $pageNo, $pageSize)
    {
        $query = $this->db
            ->where('dates', 'between', [$condition['from'], $condition['to']])
            ->where('status', 2);
        if (!empty($condition['group'])) {
            $query->where('group', 'in', $condition['group']);
        }
        if (!empty($condition['source'])) {
            $query->where('source', '=', $condition['source']);
        }
        if (!empty($condition['user'])) {
            $query->where('user', '=', $condition['user']);
        }
        if (!empty($condition['out_trade_no'])) {
            $query->where('out_trade_no', '=', $condition['out_trade_no']);
        }
        if (!empty($condition['code'])) {
            $query->where('code', '=', $condition['code']);
        }
        $result = $query->order('create_time', 'desc')
            ->page($pageNo, $pageSize)
            ->select();
        Log::sql("后台成交订单查询：" . $this->db->getLastSql());
        $total = $query->count('id');
        $sum = $query->sum('total_fee');
        return [
            'total' => $total,
            'result' => $result,
            'sum' => $sum
        ];
    }

    /**
     * 退款订单
     * @param array $condition
     * @param $pageNo
     * @param $pageSize
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderRefund(array $condition, $pageNo, $pageSize)
    {
        $query = $this->db
            ->whereTime('update_time', [$condition['from'], $condition['to']])
            ->where('status', 5);
        if (!empty($condition['group'])) {
            $query->where('group', 'in', $condition['group']);
        }
        if (!empty($condition['source'])) {
            $query->where('source', '=', $condition['source']);
        }
        if (!empty($condition['user'])) {
            $query->where('user', '=', $condition['user']);
        }
        if (!empty($condition['out_trade_no'])) {
            $query->where('out_trade_no', '=', $condition['out_trade_no']);
        }
        $result = $query->order('create_time', 'desc')
            ->page($pageNo, $pageSize)
            ->select();
        $total = $query->count('id');
        Log::sql("后台退款订单查询：" . $this->db->getLastSql());
        return [
            'total' => $total,
            'result' => $result
        ];
    }

    /**
     * 异常订单
     * @param array $condition
     * @param $pageNo
     * @param $pageSize
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderAbnormal(array $condition, $pageNo, $pageSize)
    {
        $query = $this->db
            ->where('dates', 'between', [$condition['from'], $condition['to']])
            ->where('status', 'in', [1, 3, 4]);
        if (!empty($condition['group'])) {
            $query->where('group', 'in', $condition['group']);
        }
        if (!empty($condition['source'])) {
            $query->where('source', '=', $condition['source']);
        }
        if (!empty($condition['user'])) {
            $query->where('user', '=', $condition['user']);
        }
        if (!empty($condition['out_trade_no'])) {
            $query->where('out_trade_no', '=', $condition['out_trade_no']);
        }
        $result = $query->order('create_time', 'desc')
            ->page($pageNo, $pageSize)
            ->select();
        $total = $query->count('id');
        Log::sql("后台异常订单查询：" . $this->db->getLastSql());
        return [
            'total' => $total,
            'result' => $result
        ];
    }

    /**
     * 异常订单统计
     * @return int|string
     */
    public function getAbnormalCount()
    {
        $query = $this->db
            ->where('status', 'in', [1, 3, 4]);
        $total = $query->count('id');
        Log::sql("后台异常订单统计：" . $this->db->getLastSql());
        return $total;
    }

    /**
     * 统计月报
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getReportMonthly()
    {
        $month = sprintf('%s%s', date('Y') - 2, date('m'));
        $result = $this->db
            ->field('months, sum(total_fee) as total_fee')
            ->where('months', '>', $month)
            ->where('status', 2)
            ->order('months', 'desc')
            ->group('months')
            ->select();
        Log::sql("后台订单月报：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 统计日报
     * @param $pageNo
     * @param $pageSize
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getReportDaily($pageNo, $pageSize)
    {
        $result = $this->db
            ->field('dates, sum(total_fee) as total_fee')
            ->where('status', 2)
            ->group('dates')
            ->order('dates', 'desc')
            ->page($pageNo, $pageSize)
            ->select();
        $total = Db::query("select count(*) as total from (select `dates` from `bx_orders` where status = 2 group by dates) a");
        Log::sql("后台订单日报：" . $this->db->getLastSql());
        return [
            'total' => $total[0]['total'] ?? 0,
            'result' => $result
        ];
    }

    /**
     * 更新订单状态及操作码
     * @param $id
     * @param int $status
     * @param int $code
     * @return int|string
     * @throws \think\Exception
     * @throws \think\db\exception\PDOException
     */
    public function updateOrderStatus($id, $status = 1, $code = 0)
    {
        $result = $this->db
            ->where('id', $id)
            ->update([
                'status'  => $status,
                'code' => $code,
            ]);
        Log::sql("更新订单状态及操作码：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 通过操作码取得订单
     * @param $code
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderByCode($code)
    {
        $result = $this->db
            ->where('code', $code)
            ->find();
        Log::sql("通过操作码取得订单：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 创建门诊缴费订单
     * @param $cardno 卡号
     * @param $mzh 门诊号
     * @param $zfje 金额
     * @param $zfzl 支付种类
     * @param $body 描述
     * @param $ip IP
     * @param int $source 来源 1= 终端机
     * @param int $type 支付平台 1= 威富通
     * @param string $client 用户标识
     * @return array|bool
     */
    public function createPayOrder($cardno, $mzh, $zfje, $zfzl, $body, $ip, $source = 1, $type = 1, $client = '')
    {
        $nonce_str = Helper::guid();
        $orderID = $this->getOrderID($cardno);
        $info = ['cardno' => $cardno, 'mzh' => $mzh, 'zfje' => $zfje, 'zfzl' => $zfzl, 'sjh' => $orderID];
        $data = [
            'out_trade_no' => $orderID,
            'user' => $cardno,
            'group' => 2,
            'info' => json_encode($info),
            'type' => $type,
            'source' => $source,
            'client' => $client,
            'method' => $zfzl,
            'body' => $body,
            'total_fee' => $zfje * 100,
            'mch_create_ip' => $ip,
            'time_start' => date('Y-m-d H:i:s'),
            'time_expire' => date('Y-m-d H:i:s', strtotime('10 minute')),
            'nonce_str' => $nonce_str,
            'months' => date('Ym'),
            'dates' => date('Ymd'),
        ];
        $insertResult = $this->db->data($data)->insert();
        Log::sql("创建缴费订单：" . $this->db->getLastSql());
        if ($insertResult) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     *  创建预约挂号订单
     * @param $cardno
     * @param $ysbh
     * @param $zzks
     * @param $ghrq
     * @param $ghlb
     * @param $ysh_lx
     * @param $zfzl
     * @param $zfje
     * @param $body
     * @param $ip
     * @param int $group
     * @param int $source
     * @param int $type
     * @return array|bool
     */
    public function createSaleOrder($cardno, $ysbh, $zzks, $ghrq, $ghlb, $ysh_lx, $zfzl, $zfje, $body, $ip, $group = 3, $source = 2, $type = 1)
    {
        $info = ['kh' => $cardno, 'ysbh' => $ysbh, 'zzks' => $zzks, 'ghrq'=>$ghrq, 'ghlb'=>$ghlb, 'ysh_lx'=>$ysh_lx, 'zfje' => $zfje, 'zfzl'=> $zfzl];
        $nonce_str = Helper::guid();
        $orderID = $this->getOrderID($cardno);
        $data = [
            'out_trade_no' => $orderID,
            'user' => $cardno,
            'group' => $group,
            'info' => json_encode($info),
            'type' => $type,
            'source' => $source,
            'method' => $zfzl,
            'body' => $body,
            'total_fee' => $zfje * 100,
            'mch_create_ip' => $ip,
            'time_start' => date('Y-m-d H:i:s'),
            'time_expire' => date('Y-m-d H:i:s', strtotime('10 minute')),
            'nonce_str' => $nonce_str,
            'months' => date('Ym'),
            'dates' => date('Ymd'),
        ];
        $insertResult = $this->db->data($data)->insert();
        Log::sql("创建挂号订单：" . $this->db->getLastSql());
        if ($insertResult) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 根据单号和卡号查询订单
     * @param $cardNo
     * @param $out_trade_no
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderByTradeAndCard($cardNo, $out_trade_no)
    {
        $result = $this->db
            ->where('out_trade_no', $out_trade_no)
            ->where('user', $cardNo)
            ->find();
        Log::sql("根据单号查询订单：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 创建微信挂号订单
     * @param $openid
     * @param $cardno
     * @param $ysbh
     * @param $zzks
     * @param $ghrq
     * @param $ghlb
     * @param $ysh_lx
     * @param $zfzl
     * @param $zfje
     * @param $body
     * @param $ip
     * @param int $group
     * @param int $source
     * @param int $type
     * @return array|bool
     */
    public function createWechatOrder($openid, $cardno, $name, $ysbh, $ysxm, $zzks, $zzksmc, $ghrq, $ghlb, $ysh_lx, $zfzl, $zfje, $body, $ip, $group = 4, $source = 3, $type = 1)
    {
        $info = ['openid' => $openid, 'kh' => $cardno, 'name'=>$name, 'ysbh' => $ysbh, 'ysxm' => $ysxm, 'zzks' => $zzks, 'zzksmc' => $zzksmc, 'ghrq'=>$ghrq, 'ghlb'=>$ghlb, 'ysh_lx'=>$ysh_lx, 'zfje' => $zfje, 'zfzl'=> $zfzl];
        $nonce_str = Helper::guid();
        $orderID = $this->getOrderID($cardno);
        $data = [
            'out_trade_no' => $orderID,
            'user' => $cardno,
            'group' => $group,
            'info' => json_encode($info),
            'type' => $type,
            'source' => $source,
            'method' => $zfzl,
            'body' => $body,
            'total_fee' => $zfje * 100,
            'mch_create_ip' => $ip,
            'time_start' => date('Y-m-d H:i:s'),
            'time_expire' => date('Y-m-d H:i:s', strtotime('10 minute')),
            'nonce_str' => $nonce_str,
            'months' => date('Ym'),
            'dates' => date('Ymd'),
        ];
        $insertResult = $this->db->data($data)->insert();
        Log::sql("创建预约挂号订单：" . $this->db->getLastSql());
        if ($insertResult) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     * @param $openid
     * @param $cardNo
     * @param $name
     * @param $mzh
     * @param $zfje
     * @param $zfzl
     * @param $body
     * @param $ip
     * @param int $group
     * @param int $source
     * @param int $type
     * @return array|bool
     */
    public function createWechatPayOrder($openid, $cardNo, $name, $mzh, $zfje, $zfzl, $body, $ip, $group = 5, $source = 3, $type = 1)
    {
        $nonce_str = Helper::guid();
        $orderID = $this->getOrderID($cardNo);
        $info = ['openid' => $openid, 'kh' => $cardNo, 'name'=>$name, 'mzh' => $mzh, 'zfje' => $zfje, 'zfzl'=> $zfzl, 'sjh' => $orderID];
        $data = [
            'out_trade_no' => $orderID,
            'user' => $cardNo,
            'group' => $group,
            'info' => json_encode($info),
            'type' => $type,
            'source' => $source,
            'method' => $zfzl,
            'body' => $body,
            'total_fee' => $zfje * 100,
            'mch_create_ip' => $ip,
            'time_start' => date('Y-m-d H:i:s'),
            'time_expire' => date('Y-m-d H:i:s', strtotime('10 minute')),
            'nonce_str' => $nonce_str,
            'months' => date('Ym'),
            'dates' => date('Ymd'),
        ];
        $insertResult = $this->db->data($data)->insert();
        Log::sql("创建微信门诊缴费订单：" . $this->db->getLastSql());
        if ($insertResult) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     * @param $cardNo
     * @param $name
     * @param $zyh
     * @param $money
     * @param $zfzl
     * @param $openid
     * @param $body
     * @param $ip
     * @param int $group
     * @param int $source
     * @param int $type
     * @return array|bool
     */
    public function createHospitalOrder($cardNo, $name, $zyh, $money, $zfzl, $openid, $body, $ip, $group = 6, $source = 3, $type = 1)
    {
        $nonce_str = Helper::guid();
        $orderID = $this->getOrderID($cardNo);
        $info = ['openid' => $openid, 'kh' => $cardNo, 'name'=>$name, 'zyh' => $zyh, 'zfje' => $money, 'zfzl'=> $zfzl, 'sjh' => $orderID];
        $data = [
            'out_trade_no' => $orderID,
            'user' => $cardNo,
            'group' => $group,
            'info' => json_encode($info),
            'type' => $type,
            'source' => $source,
            'method' => $zfzl,
            'body' => $body,
            'total_fee' => $money * 100,
            'mch_create_ip' => $ip,
            'time_start' => date('Y-m-d H:i:s'),
            'time_expire' => date('Y-m-d H:i:s', strtotime('10 minute')),
            'nonce_str' => $nonce_str,
            'months' => date('Ym'),
            'dates' => date('Ymd'),
        ];
        $insertResult = $this->db->data($data)->insert();
        Log::sql("创建微信住院费预交订单：" . $this->db->getLastSql());
        if ($insertResult) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     * @param $openid
     * @param $cardNo
     * @param $name
     * @param $ysbh
     * @param $bb
     * @param $zfje
     * @param $zfzl
     * @param $ksbm
     * @param $ksmc
     * @param $ysxm
     * @param $bbmc
     * @param $ghlb
     * @param $lbmc
     * @param $ip
     * @param $body
     * @param int $group
     * @param int $source
     * @param int $type
     * @return array|bool
     */
    public function createWechatTodayOrder($openid, $cardNo, $name, $ysbh, $bb, $zfje, $zfzl, $ksbm, $ksmc, $ysxm, $bbmc, $ghlb, $lbmc, $ip, $body, $group = 7, $source = 3, $type = 1)
    {
        $nonce_str = Helper::guid();
        $orderID = $this->getOrderID($cardNo);
        $info = ['openid' => $openid, 'kh' => $cardNo, 'name' => $name, 'ysbh' => $ysbh, 'bb' => $bb, 'zfje' => $zfje, 'zfzl'=> $zfzl,
            'ksbm' => $ksbm, 'ksmc' => $ksmc, 'ysxm' => $ysxm, 'bbmc' => $bbmc, 'lbmc' => $lbmc, 'ghlb' => $ghlb,
            'sjh' => $orderID];
        $data = [
            'out_trade_no' => $orderID,
            'user' => $cardNo,
            'group' => $group,
            'info' => json_encode($info),
            'type' => $type,
            'source' => $source,
            'method' => $zfzl,
            'body' => $body,
            'total_fee' => $zfje * 100,
            'mch_create_ip' => $ip,
            'time_start' => date('Y-m-d H:i:s'),
            'time_expire' => date('Y-m-d H:i:s', strtotime('10 minute')),
            'nonce_str' => $nonce_str,
            'months' => date('Ym'),
            'dates' => date('Ymd'),
        ];
        $insertResult = $this->db->data($data)->insert();
        Log::sql("创建微信挂号订单：" . $this->db->getLastSql());
        if ($insertResult) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 当日汇总
     * @param $today
     * @return array
     */
    public function getTodaySummary($today)
    {
        $query = $this->db
            ->where('dates', $today)
            ->where('status', 2);
        $total = $query->sum('total_fee');
        $count = $query->count();
        $sum = Db::query("select count(*) as total from (select `user` from `bx_orders` where status = 2 and dates = ".$today." group by `user`) a");
        return [
            'total' => $total / 100,
            'count' => $count,
            'sum' => $sum[0]['total']
        ];
    }

    /**
     * 七日订单汇总
     * @param $startDay
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getReportWeek($startDay)
    {
        $result = $this->db
            ->field('dates, sum(total_fee) as total_fee')
            ->where('dates', '>=', $startDay)
            ->where('status', 2)
            ->group('dates')
            ->order('dates asc')->select();
        Log::sql("七日订单额汇总：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 七日订单汇总
     * @param $startDay
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCountWeek($startDay)
    {
        $result = $this->db
            ->field('dates, count(*) as count')
            ->where('dates', '>=', $startDay)
            ->where('status', 2)
            ->group('dates')
            ->order('dates asc')->select();
        Log::sql("七日订单数汇总：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 七日订单汇总
     * @param $startDay
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRatioWeek($startDay)
    {
        $result = $this->db
            ->field('`group`, count(*) as count')
            ->where('dates', '>=', $startDay)
            ->where('status', 2)
            ->group('`group`')->select();
        Log::sql("七日订单率汇总：" . $this->db->getLastSql());
        return $result;
    }

    /**
     * 异常订单报告
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAbnormalList()
    {
        $result = $this->db
            ->where('status', 'in', [1, 3, 4])
            ->order('id', 'desc')
            ->limit(10)
            ->select();
        Log::sql("异常订单报告：" . $this->db->getLastSql());
        return $result;
    }

}
