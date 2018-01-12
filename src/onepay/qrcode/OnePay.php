<?php
/**
 * Created by ngonhan
 * Date: 1/9/2018
 * Time: 11:03 PM
 */

namespace onepay\qrcode;


class OnePay
{
    private $_config;
    private $_defaultConfig = array(
        // URL use example value in document, need config when constructing
        'gateway' => 'https://ONEPAY.co.jp/gateway',
        'merchantCode' => ' 100253238966',
        'branchCode' => 'AShop001',
        'terminalCode' => ' BTer001',
        // Request body common
        'locale' => 'JAPAN',
        'timeZone' => 'p9',
        'currencyCode' => 'JPY',
        'payType' => '01', //Ali
        'appVersion' => 'linux-ali-1.0.0',

        'privateKeyPath' => 'private.pem',
        'publicKeyPath' => 'public.pem',
        'debug' => false,
        'logTarget' => 'output.log'
    );

    private $_commonBodyRequestParams;

    private $_commonUrlParams = array(
        'serviceMode' => 'B01',
        'signType' => '0',
        'isBackTran' => '0'
    );

    public function __construct($config=array()){
        $this->_config = array_merge($this->_defaultConfig, $config);
        $this->_commonBodyRequestParams = Utils::arrayCopy($this->_config, 'locale,merchantCode,branchCode,terminalCode,timeZone,currencyCode,payType,appVersion');
        Utils::$logConfig = Utils::arrayCopy($this->_config, 'logTarget,debug');

    }

    /**
     * Create token for api url
     * @return string merchantCode + branchCode + terminalCode + timestamp in YYYY/MM/DD + serial#
     */
    private function token(){
        $tokenParams = array (
            $this->_config['merchantCode'],
            $this->_config['branchCode'],
            $this->_config['terminalCode'],
            date('Ymd'),
            str_pad(Utils::getMillisecond(), 6, '0', STR_PAD_LEFT)
        );
        return implode('', $tokenParams);
    }

    /**
     * Create url for api call
     * @param $funcName
     * @return string
     */
    private function createUrl($funcName){
        $urlCommon = implode('/',  $this->_commonUrlParams);
        return implode('/', array($this->_config['gateway'], 'tqc'.ucwords($funcName), $urlCommon, $this->token()));
    }

    /**
     * @param $amount
     * @param int $validTime in minutes
     */
    public function pay($amount=0, $transNonce='12ibu5aiVcKdp5RxkhJA3', $validTime=2160){
        $url = $this->createUrl(__FUNCTION__);
        $sendParams = array(
            'amount' => $amount,
            'transNonce' => $transNonce,
            'validTime' => $validTime,
            'notifyUrl' => "http://api.test.pay.net/atinterface/receive_notify.htm"
        );
        $sendParams = array_merge($sendParams, $this->_commonBodyRequestParams);
        $sendParams = Utils::signSendData($sendParams, $this->_config['privateKeyPath']);
        return Utils::getHttpResponsePOST($url, $sendParams);
    }

    public function refund(){

    }

    public function reverse(){

    }

    public function orderQuery(){

    }

    public function orderDetailQuery(){

    }

    public function confirm(){

    }

    private function log($logData){
        Utils::logResult($logData, array($this->_config['logTarget'], $this->_config['debug']));
    }
}