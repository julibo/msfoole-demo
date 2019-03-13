<?php
/**
 * 后台管理类
 */

namespace App\Service;

use Julibo\Msfoole\Exception;
use Julibo\Msfoole\Facade\Log;
use App\Model\Order as OrderModel;
use App\Model\Manager as ManagerModel;
use App\Model\Login as LoginModel;
use Julibo\Msfoole\Helper;
use App\Logic\PaymentApi;
use App\Logic\HospitalApi;
use App\Lib\Helper\Message;


class Admin extends BaseServer
{
    private $cache;

    protected function init()
    {

    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * 账户列表
     * @return mixed
     */
    public function getManager()
    {
        $result = ManagerModel::getInstance()->getManager();
        return $result;
    }

    /**
     * 新增账户
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function addManager(array $data)
    {
        if (empty($data) || empty($data['name']) || empty($data['password'])) {
            throw new Exception('缺少必要的参数', 1);
        }
        $manager = ManagerModel::getInstance()->findManager($data['name']);
        if ($manager) {
            throw new Exception('该管理员已存在', 2);
        }
        $data['password'] = md5($data['password']);
        $result = ManagerModel::getInstance()->addManager($data);
        return $result;
    }

    /**
     * 删除账户
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function delManager($id)
    {
        if (empty($id)) {
            throw new Exception('缺少必要的参数', 1);
        }
        $result = ManagerModel::getInstance()->delManager($id);
        return $result;
    }

    /**
     * 更新账户
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function updateManager(array $data)
    {
        if (empty($data) || empty($data['id'])) {
            throw new Exception('缺少必要的参数', 1);
        }
        if (!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        }
        $result = ManagerModel::getInstance()->saveManager($data);
        return $result;
    }

    /**
     * 最近登录记录
     * @param $mid
     * @return mixed
     * @throws Exception
     */
    public function getLoginRecord($mid)
    {
        if (empty($mid)) {
            throw new Exception('缺少必要的参数', 1);
        }
        $result = LoginModel::getInstance()->getLoginRecord($mid);
        return $result;
    }

    /**
     * 后台登录
     * @param $username
     * @param $password
     * @return mixed
     * @throws Exception
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            throw new Exception('账户和密码不能为空', 1);
        }
        $manager = ManagerModel::getInstance()->findManager($username);
        if (empty($manager)) {
            throw new Exception('账户或密码有误', 2);
        }
        if ($manager['password'] != md5($password)) {
            throw new Exception('账户或密码有误', 3);
        }
        $lastLogin = LoginModel::getInstance()->getLastLogin($manager['id']);
        if ($lastLogin) {
            $manager['login_ip'] = $lastLogin['login_ip'];
            $manager['login_time'] = $lastLogin['login_time'];
        }
        $data = [
            'login_ip' => '127.0.0.1',
            'mid' => $manager['id']
        ];
        LoginModel::getInstance()->insetLogin($data);
        $key = 'manager:' . $manager['name'];
        $this->cache->set($key, $manager);
        return $manager;
    }

    /**
     * 订单查询
     * @param array $params
     * @return array
     */
    public function getOrderList(array $params) : array
    {
        $pageNo = $params['pageNo'] ?? 1;
        $pageSize = $params['pageSize'] ?? 10;
        $condition = [
            'from' => date('Ymd', $params['from'] / 1000),
            'to' => date('Ymd', $params['to'] / 1000),
        ];
        if (!empty($params['group'])) {
            switch ($params['group']) {
                case 1:
                    $condition['group'] = [3, 4];
                    break;
                case 2:
                    $condition['group'] = [1, 7];
                    break;
                case 3:
                    $condition['group'] = [2, 5];
                    break;
                case 4:
                    $condition['group'] = [6];
                    break;
            }
        }
        if (!empty($params['source'])) {
            $condition['source'] = $params['source'];
        }
        if (!empty($params['method'])) {
            $condition['method'] = $params['method'];
        }
        if (!empty($params['user'])) {
            $condition['user'] = $params['user'];
        }
        if (!empty($params['no'])) {
            $condition['out_trade_no'] = $params['no'];
        }
        if (!empty($params['code'])) {
            $condition['code'] = $params['code'];
        }
        $result = OrderModel::getInstance()->getOrderList($condition, $pageNo, $pageSize);
        $result['sum'] = number_format($result['sum'] / 100, 2);
        if ($result && !empty($result['result'])) {
            foreach ($result['result'] as &$vo) {
                switch ($vo['group']) {
                    case 1:
                        $vo['group_des'] = '当日挂号';
                        break;
                    case 2:
                        $vo['group_des'] = '门诊缴费';
                        break;
                    case 3:
                        $vo['group_des'] = '预约挂号';
                        break;
                    case 4:
                        $vo['group_des'] = '预约挂号';
                        break;
                    case 5:
                        $vo['group_des'] = '门诊缴费';
                        break;
                    case 6:
                        $vo['group_des'] = '住院费预交';
                        break;
                    case 7:
                        $vo['group_des'] = '当日挂号';
                        break;
                }
                switch ($vo['source']) {
                    case 1:
                        $vo['source_des'] = '终端机';
                        break;
                    case 2:
                        $vo['source_des'] = 'web';
                        break;
                    case 3:
                        $vo['source_des'] = '微信';
                        break;
                }
                switch ($vo['method']) {
                    case 1:
                        $vo['method_des'] = '支付宝二维码';
                        break;
                    case 2:
                        $vo['method_des'] = '微信二维码';
                        break;
                    case 3:
                        $vo['method_des'] = '微信公众号';
                        break;
                }
                $vo['total_fee'] = number_format($vo['total_fee'] / 100, 2);
            }
        }
        return $result;
    }

    /**
     * 退款订单
     * @param array $params
     * @return array
     */
    public function getOrderRefund(array $params)
    {
        $pageNo = $params['pageNo'] ?? 1;
        $pageSize = $params['pageSize'] ?? 10;
        $condition = [
            'from' => date('Y-m-d 00:00:00', $params['from'] / 1000),
            'to' => date('Y-m-d 23:59:59', $params['to'] / 1000),
        ];
        if (!empty($params['group'])) {
            switch ($params['group']) {
                case 1:
                    $condition['group'] = [3, 4];
                    break;
                case 2:
                    $condition['group'] = [1, 7];
                    break;
                case 3:
                    $condition['group'] = [2, 5];
                    break;
                case 4:
                    $condition['group'] = [6];
                    break;
            }
        }
        if (!empty($params['source'])) {
            $condition['source'] = $params['source'];
        }
        if (!empty($params['method'])) {
            $condition['method'] = $params['method'];
        }
        if (!empty($params['user'])) {
            $condition['user'] = $params['user'];
        }
        if (!empty($params['no'])) {
            $condition['out_trade_no'] = $params['no'];
        }
        $result = OrderModel::getInstance()->getOrderRefund($condition, $pageNo, $pageSize);
        if ($result && !empty($result['result'])) {
            foreach ($result['result'] as &$vo) {
                switch ($vo['group']) {
                    case 1:
                        $vo['group_des'] = '当日挂号';
                        break;
                    case 2:
                        $vo['group_des'] = '门诊缴费';
                        break;
                    case 3:
                        $vo['group_des'] = '预约挂号';
                        break;
                    case 4:
                        $vo['group_des'] = '预约挂号';
                        break;
                    case 5:
                        $vo['group_des'] = '门诊缴费';
                        break;
                    case 6:
                        $vo['group_des'] = '住院费预交';
                        break;
                    case 7:
                        $vo['group_des'] = '当日挂号';
                        break;
                }
                switch ($vo['source']) {
                    case 1:
                        $vo['source_des'] = '终端机';
                        break;
                    case 2:
                        $vo['source_des'] = 'web';
                        break;
                    case 3:
                        $vo['source_des'] = '微信';
                        break;
                }
                switch ($vo['method']) {
                    case 1:
                        $vo['method_des'] = '支付宝二维码';
                        break;
                    case 2:
                        $vo['method_des'] = '微信二维码';
                        break;
                    case 3:
                        $vo['method_des'] = '微信公众号';
                        break;
                }
                $vo['total_fee'] = number_format($vo['total_fee'] / 100, 2);
            }
        }
        return $result;
    }

    /**
     * 异常订单数量
     * @return mixed
     */
    public function getAbnormalCount()
    {
        $result = OrderModel::getInstance()->getAbnormalCount();
        return $result;
    }

    /**
     * 异常订单
     * @param array $params
     * @return array
     */
    public function getOrderAbnormal(array $params)
    {
        $pageNo = $params['pageNo'] ?? 1;
        $pageSize = $params['pageSize'] ?? 10;
        $condition = [
            'from' => date('Ymd', $params['from'] / 1000),
            'to' => date('Ymd', $params['to'] / 1000),
        ];
        if (!empty($params['group'])) {
            switch ($params['group']) {
                case 1:
                    $condition['group'] = [3, 4];
                    break;
                case 2:
                    $condition['group'] = [1, 7];
                    break;
                case 3:
                    $condition['group'] = [2, 5];
                    break;
                case 4:
                    $condition['group'] = [6];
                    break;
            }
        }
        if (!empty($params['source'])) {
            $condition['source'] = $params['source'];
        }
        if (!empty($params['method'])) {
            $condition['method'] = $params['method'];
        }
        if (!empty($params['user'])) {
            $condition['user'] = $params['user'];
        }
        if (!empty($params['no'])) {
            $condition['out_trade_no'] = $params['no'];
        }
        $result = OrderModel::getInstance()->getOrderAbnormal($condition, $pageNo, $pageSize);
        if ($result && !empty($result['result'])) {
            foreach ($result['result'] as &$vo) {
                switch ($vo['group']) {
                    case 1:
                        $vo['group_des'] = '当日挂号';
                        break;
                    case 2:
                        $vo['group_des'] = '门诊缴费';
                        break;
                    case 3:
                        $vo['group_des'] = '预约挂号';
                        break;
                    case 4:
                        $vo['group_des'] = '预约挂号';
                        break;
                    case 5:
                        $vo['group_des'] = '门诊缴费';
                        break;
                    case 6:
                        $vo['group_des'] = '住院费预交';
                        break;
                    case 7:
                        $vo['group_des'] = '当日挂号';
                        break;
                }
                switch ($vo['source']) {
                    case 1:
                        $vo['source_des'] = '终端机';
                        break;
                    case 2:
                        $vo['source_des'] = 'web';
                        break;
                    case 3:
                        $vo['source_des'] = '微信';
                        break;
                }
                switch ($vo['method']) {
                    case 1:
                        $vo['method_des'] = '支付宝二维码';
                        break;
                    case 2:
                        $vo['method_des'] = '微信二维码';
                        break;
                    case 3:
                        $vo['method_des'] = '微信公众号';
                        break;
                }
                switch ($vo['status']) {
                    case 1:
                        $vo['status_des'] = '已付款';
                        break;
                    case 3:
                        $vo['status_des'] = '接口失败';
                        break;
                    case 4:
                        $vo['status_des'] = '退款失败';
                        break;
                }
                $vo['total_fee'] = number_format($vo['total_fee'] / 100, 2);
            }
        }
        return $result;
    }

    /**
     * 退款
     * @param $out_trade_no
     * @return bool
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refunding($out_trade_no)
    {
        # step 1 查询订单
        $order = OrderModel::getInstance()->getOrderByTradeNo($out_trade_no);
        if (empty($order)) {
            throw new Exception('该订单不存在', 1);
        }
        if (!in_array($order['status'], [1, 3, 4])) {
            throw new Exception('订单非异常订单', 2);
        }
        # step 2 退款操作
        // 原路返回款项
        $params = [
            'out_trade_no' => $order['out_trade_no'],
            'out_refund_no' => $order['out_trade_no'] . 'R',
            'total_fee' => $order['total_fee'],
            'refund_fee' => $order['total_fee'],
            'refund_channel' => 'ORIGINAL',
            'nonce_str' => Helper::guid()
        ];
        $refundResult = PaymentApi::getInstance()->submitRefund($params);
        if ($refundResult) {
            OrderModel::getInstance()->updateOrderStatus($order['id'], 5);
            $msg = '成都北新医院'. $order['body'] . $order['total_fee'] / 100  . '元退款成功，退款将按原路返回，预计7个工作日以内到账，感谢您的理解与支持。';
            # 短信通知
            $this->sendSms($order['user'], $msg);
            return true;
        } else {
            OrderModel::getInstance()->updateOrderStatus($order['id'], 4);
            return false;
        }
    }

    /**
     * 发送短信
     * @param $cardNo
     * @param $content
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendSms($cardNo, $content)
    {
        $user = HospitalApi::getInstance()->getUser($cardNo);
        if (!empty($user) && !empty($content) && !empty($user['mobile']) && preg_match("/^1[3456789]\d{9}$/", $user['mobile'])) {
            Log::debug('sendSMS:向{mobile}发送短信：{message}', ['mobile' => $user['mobile'], 'message' => $content]);
            Message::sendSms($user['mobile'], $content);
        }
    }

    /**
     * 统计月报
     * @return mixed
     */
    public function getReportMonthly()
    {
        $result = OrderModel::getInstance()->getReportMonthly();
        foreach ($result as &$vo) {
            $vo['total'] = $vo['total_fee'] / 100;
        }
        return $result;
    }

    /**
     * 统计日报
     * @param array $params
     * @return mixed
     */
    public function getReportDaily(array $params)
    {
        $pageNo = $params['pageNo'] ?? 1;
        $pageSize = $params['pageSize'] ?? 10;
        $result = OrderModel::getInstance()->getReportDaily($pageNo, $pageSize);
        foreach ($result['result'] as &$vo) {
            $vo['date'] = sprintf('%s-%s-%s', substr($vo['dates'], 0, 4), substr($vo['dates'], 4, 2), substr($vo['dates'], 6, 2));
            $vo['total'] = $vo['total_fee'] / 100;
        }
        return $result;
    }

    /**
     * 当日汇总
     * @return mixed
     */
    public function getTodaySummary()
    {
        $today = date('Ymd');
        $result = OrderModel::getInstance()->getTodaySummary($today);
        return $result;
    }

    /**
     * 近七天成交额趋势
     * @return mixed
     */
    public function getReportWeek()
    {
        $result = [];
        $startDay = date('Ymd', strtotime('-7 days'));
        $report = OrderModel::getInstance()->getReportWeek($startDay);
        for ($i = 7; $i >=1; $i--) {
            $result[7-$i] = [
                'name' => date('m/d', strtotime('-'.$i.' days')),
                'value' => 0
            ];
            $day = date('Ymd', strtotime('-'.$i.' days'));
            foreach ($report as $vo) {
                if ($vo['dates'] == $day) {
                    $result[7-$i]['value'] = $vo['total_fee'] / 100;
                }
            }
        }
        return $result;
    }

    /**
     * 近七天成交数趋势
     * @return mixed
     */
    public function getCountWeek()
    {
        $result = [];
        $startDay = date('Ymd', strtotime('-7 days'));
        $report = OrderModel::getInstance()->getCountWeek($startDay);
        for ($i = 7; $i >=1; $i--) {
            $result[7-$i] = [
                'name' => date('m/d', strtotime('-'.$i.' days')),
                'value' => 0
            ];
            $day = date('Ymd', strtotime('-'.$i.' days'));
            foreach ($report as $vo) {
                if ($vo['dates'] == $day) {
                    $result[7-$i]['value'] = $vo['count'];
                }
            }
        }
        return $result;
    }

    /**
     * 近七天成交比例
     * @return mixed
     */
    public function getRatioWeek()
    {
        $sum = 0;
        $result = [
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
        ];
        $startDay = date('Ymd', strtotime('-20 days'));
        $report = OrderModel::getInstance()->getRatioWeek($startDay);
        foreach ($report as $vo) {
            $sum += $vo['count'];
            if (in_array($vo['group'], [1, 7])) {
                $result[0] += $vo['count'];
            } else if (in_array($vo['group'], [2, 5])) {
                $result[1] += $vo['count'];
            } else if (in_array($vo['group'], [3, 4])) {
                $result[2] += $vo['count'];
            } else {
                $result[3] = $vo['count'];
            }
        }
        $result = [
            'guahao' => (int)(round($result[0]/$sum, 2) * 100),
            'menzhen' => (int)(round($result[1]/$sum, 2) * 100),
            'yuyue' => (int)(round($result[2]/$sum, 2) * 100),
            'yujiao' => (int)(round($result[3]/$sum, 2) * 100),
        ];
        return $result;
    }

    /**
     * 异常订单报表
     * @return mixed
     */
    public function getAbnormalReport()
    {
        $result = OrderModel::getInstance()->getAbnormalList();
        if (!empty($result)) {
            foreach ($result as &$vo) {
                switch ($vo['group']) {
                    case 1:
                        $vo['group_des'] = '当日挂号';
                        break;
                    case 2:
                        $vo['group_des'] = '门诊缴费';
                        break;
                    case 3:
                        $vo['group_des'] = '预约挂号';
                        break;
                    case 4:
                        $vo['group_des'] = '预约挂号';
                        break;
                    case 5:
                        $vo['group_des'] = '门诊缴费';
                        break;
                    case 6:
                        $vo['group_des'] = '住院费预交';
                        break;
                    case 7:
                        $vo['group_des'] = '当日挂号';
                        break;
                }
                switch ($vo['status']) {
                    case 1:
                        $vo['status_des'] = '已支付';
                        break;
                    case 3:
                        $vo['status_des'] = '操作失败';
                        break;
                    case 4:
                        $vo['status_des'] = '退款失败';
                        break;
                }
            }
        }
        return $result;
    }


}