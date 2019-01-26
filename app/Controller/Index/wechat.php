<?php
class Wechat
{
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';

    const AUTH_URL = '/token?grant_type=client_credential&';

    private $appid = 'wx37ba0e17a5e3e7c0';

    private $appsecret = '77a2e6c978f83d9a7d583c94d4a09190';

    private $access_token;

    public function checkAuth()
    {
        $appid = $this->appid;
        $appsecret = $this->appsecret;

        $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->access_token = $json['access_token'];
            return $this->access_token;
        }
        return false;
    }

    /**
     * GET 请求
     * @param $url
     * @return bool|string
     */
    private function http_get($url)
    {
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
}

$Wechat = new Wechat();
$accessToken = $Wechat->checkAuth();
echo $accessToken;