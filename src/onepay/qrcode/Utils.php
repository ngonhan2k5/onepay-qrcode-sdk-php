<?php
/**
 * Created by ngonhan2k5
 * Date: 1/9/2018
 * Time: 11:24 PM
 */

namespace onepay\qrcode;


class Utils
{
    static $logConfig = array(
        'filePath' => false,
        'debug' => false
    );
    /**
     * @param $para
     * @return array
     */
    private static function paraFilter($para) {

        $para_filter = array();
        foreach($para as $key => $val) {
            if($key == "sign" || $key == "sign_type" || $val === null)
                continue;
            else
                $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * @param $para
     * @return mixed
     */
    private static function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * @param $para
     * @return bool|string
     */
    private static function createLinkstring($para) {
        $arg  = "";
        foreach ($para as $key => $val) {
            $arg.=$key."=".$val."&";
        }
        $arg = rtrim($arg, "&");
        return '{'.$arg.'}';
    }

    /**
     * @param $url
     * @param $cacert_url
     * @param $para
     * @param string $input_charset
     * @return mixed
     */
    static function getHttpResponsePOST($url, $data) {
        self::logResult('REQUEST:'.$url);
        $dataJson = json_encode($data);
        self::logResult('DATA');
        self::logResult($dataJson);
        $curl = curl_init($url);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
//        curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($dataJson))
        );
        curl_setopt($curl,CURLOPT_POSTFIELDS,$dataJson);
        $response = curl_exec($curl);
        self::logResult($response);
        //self::logResult(curl_error($curl), self::$debug);
        curl_close($curl);
        return $response;
    }

    /**
     * @return string
     */
    static function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * @param string $word
     * @param $filePath
     * @param bool $debug
     */
    static function logResult($data='', $config=null) {
        $config = ($config==null)?self::$logConfig: $config;
        if (is_array($data)){
            $data = print_r($data, true);
        }
        $logContent = strftime("%Y/%m/%d-%H:%M:%S", time()) . " " . $data . "\n";
        if ($config['logTarget']) {
            $fp = fopen($config['logTarget'], "a");
            flock($fp, LOCK_EX);
            fwrite($fp, $logContent);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        if ($config['debug'] || !$config['logTarget']){
            echo $logContent;
        }
    }

    private static function toSignData($data){
        $filted = self::paraFilter($data);
        $sorted = self::argSort($filted);
        $toSign = self::createLinkstring($sorted);
        self::logResult('queryString:'.$toSign);
        return strtoupper(hash('sha256', $toSign));
    }

    /**
     * @param $data
     * @param $private_key
     * @return string
     */
    public static function rsaSign($data, $private_key) {
        $res=openssl_get_privatekey($private_key);
        if($res) openssl_sign($data, $sign,$res);
        else exit("The format of your private_key is incorrect!");
        openssl_free_key($res);

        $sign = base64_encode(base64_encode($sign));
        return $sign;
    }

    /**
     * @param $data
     * @param $public_key
     * @param $sign
     * @return bool
     */
    public static function rsaVerify($data, $public_key, $sign)  {
        $res=openssl_get_publickey($public_key);
        if($res)
            $result = (bool)openssl_verify($data, base64_decode(base64_decode($sign)), $res);
        else
            exit("The format of your public_key is incorrect!");
        openssl_free_key($res);
        return $result;
    }

    /**
     * @param $data
     * @param $privateKeyPath
     * @return string
     */
    static function signSendData($reqData, $privateKeyPath){
        $toSignData = self::toSignData($reqData);
        self::logResult('PrivateKey:'.$privateKeyPath);
        $privateKey = file_get_contents($privateKeyPath);
        $reqData['sign'] = self::rsaSign($toSignData, $privateKey);
        return $reqData;
    }

    /**
     * @param $resData
     * @param $publicKeyPath
     * @param bool $deep: =true: check data, =false: only check certificated remote server
     * @return bool
     */
    static function verifyReceiveData($resData, $publicKeyPath, $deep=false){
        $sign = $resData['sign'];
        $toSignData = self::toSignData($resData);
        self::logResult('PublicKey:'.$publicKeyPath);
        $publicKey = file_get_contents($publicKeyPath);
        return self::rsaVerify($toSignData, $publicKey, $sign);
    }

    /**
     * @param $keysString
     */
    static function arrayCopy($srcArray, $keysString){
        $keys = explode(',', $keysString);
        return array_filter($srcArray, function($key) use ($keys){return in_array($key, $keys);}, ARRAY_FILTER_USE_KEY);
    }
}