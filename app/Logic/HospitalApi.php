<?php
/**
 * 医院API
 */

namespace App\Logic;

use GuzzleHttp\Client;
use Julibo\Msfoole\Facade\Config;
use Julibo\Msfoole\Facade\Log;
use App\Validator\Feedback;
use Julibo\Msfoole\Exception;

class HospitalApi
{
    private static $instance;

    private $apiHost;
    private $apiUser;
    private $client;

    /**
     * 构造函数
     */
    private function __construct()
    {
        $this->apiHost = Config::get('api.hospital.host');
        $this->apiUser = Config::get('api.hospital.user');
    }

    /**
     * 实例化
     * @return HospitalApi
     */
    public static function getInstance() : self
    {
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     * API接口调用方法
     * @param $code
     * @param string $content
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apiClient($code, $content = "") : array
    {
        $this->client = new Client(['cookies' => false]);
        $body = [
            'head' => [
                'username' => $this->apiUser,
                'timestamp' => sprintf('%d1000', time()),
                'code' => $code,
                'sig' => '',
            ],
            'content' => $content
        ];
        $body = json_encode($body);
        $time = microtime(true);
        Log::debug('HospitalApi:发起请求，入参：{body}', ['body'=>$body]);
        $response = $this->client->request('POST', $this->apiHost, [
            'body' => $body
        ]);
        $data = $response->getBody();
        $data = json_decode($data, true);
        Log::info('HospitalApi:获取结果，入参：{body}，接口返回：{data}，耗时：{time}秒', ['body'=>$body, 'data'=>json_encode($data), 'time'=>microtime(true) - $time]);
        if (empty($data) || !isset($data['errorcode']) || !isset($data['response'])) {
            throw new Exception(Feedback::$Exception['INTERFACE_EXCEPTION']['msg'], Feedback::$Exception['INTERFACE_EXCEPTION']['code']);
        }
        if ($data['errorcode'] != 0) {
            throw new Exception($data['msg'], $data['errorcode']);
        }
        $result = $data['response'] ?: [];
        return $result;
    }

    /**
     * 通过卡号查询用户信息
     * @param string $cardNo
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser(string $cardNo) : array
    {
        $response = $this->apiClient('byxx', ['kh'=>$cardNo]);
        $result = $response['item'];
        $result['cardno'] = $cardNo;
        if (preg_match("/^1[3456789]\d{9}$/", $result['dh'])) {
            $result['mobile'] = $result['dh'];
        }
        return $result;
    }
}
