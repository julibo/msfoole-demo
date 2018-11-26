<?php
/**
 * 医院API
 */

namespace App\Logic;

use GuzzleHttp\Client;
use Julibo\Msfoole\Facade\Config;

class HospitalApi
{
    private static $instance;

    private $apiHost;
    private $apiUser;
    private $client;

    private function __construct()
    {
        $this->apiHost = Config::get('api.hospital.host');
        $this->apiUser = Config::get('api.hospital.user');
    }

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
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apiClient($code, $content = "")
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
        $response = $this->client->request('POST', $this->apiHost, [
            'body' => json_encode($body)
        ]);
        echo $data = $response->getBody();
        $data = json_decode($data, true);
        if (empty($data) || !isset($data['errorcode']) || !isset($data['response'])) {
            throw new \Exception('接口异常', 100);
        }
        if ($data['errorcode'] != 0) {
            throw new \Exception($data['msg'], $data['errorcode']);
        }
        $result = $data['response'];
        return $result;
    }

}