<?php
/**
 * Created by ngonhan2k5
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
        'appVersion' => 'linux-ali-1.0.0',

        'payType' => '01',

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
        $this->_commonBodyRequestParams = Utils::arrayCopy($this->_config, 'locale,merchantCode,branchCode,terminalCode,timeZone,currencyCode,appVersion');
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

    private function transNonce($funcName, $handleId){
        $map = array (
            'pay' => 'PAY',
            'refund' => 'RFD',
            'reverse' => 'RVS',
        );
        return $map[$funcName].'-'.$handleId.'-'.Utils::getMillisecond();
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
     * Prepare param, url and post
     * @param $funcName
     * @param $sendParams
     * @return mixed
     */
    private function request($funcName, $sendParams){
        $url = $this->createUrl($funcName);
        $this->log('REQUEST:'.$url);

        $_sendParams = array_merge($this->_commonBodyRequestParams, $sendParams);
        $_sendParams = Utils::signSendData($_sendParams, $this->_config['privateKeyPath']);

        $this->log('DATA');
        $this->log($_sendParams);

        return Utils::getHttpResponsePOST($url, $_sendParams);
    }

    /**
     * @param int $amount
     * @param string $transNonce
     * @param int $validTime
     * @param string $payType = 01 ALI
     */
    public function pay($amount=0, $validTime=2160, $handleId='ALI0001'){

        return $this->request(__FUNCTION__, array(
            'payType' => $this->_config['payType'],
            'amount' => $amount,
            'transNonce' => $this->transNonce(__FUNCTION__, $handleId),
            'validTime' => $validTime,
            'notifyUrl' => "http://api.test.pay.net/atinterface/receive_notify.htm"
        ));

    }

    public function refund($amount, $orderId, $handleId='ALI0001'){

        return $this->request(__FUNCTION__, array(
            'refundAmount' => $amount,
            'transNonce' => $this->transNonce(__FUNCTION__, $handleId),
            'orderId' => $orderId,
            'refundReason' => 'test',
        ));

    }

    public function reverse($orderId){

        return $this->request(__FUNCTION__, array(
            'orderId' => $orderId,
            'currencyCode' => null, // remove param
        ));

    }

    public function orderQuery($orderId=''){
        return $this->request(__FUNCTION__, array(
//            'payType' => $this->_config['payType'],
            'orderId' => $orderId,
//            'qryToken' => '10028222269100101201801121515752575144',
                'merchantCode' => null,
//                'userCode' => 'bax03777wwdaf4kgwzif6055',
//                'start' => 1,
//                'limit' => 7,
//                'startDate' => '20180110',
//                'endDate' => '20180115',
//                'payType' => '',
            'timeZone' => null,
            'appVersion' => null,
            'currencyCode' => null, // remove param

        ));
    }

    public function orderDetailQuery($orderId, $transType='01'){
        return $this->request(__FUNCTION__, array(
            'orderId' => $orderId,
            'transType' => $transType,
//            'transSerial' => 'J1AP20180112200052632q6a',
//            'qryToken' => 'J1AP20180112200052632q6a',
            'isQueryOnly' => '0'
        ));
    }

    public function confirm($orderId){
        return $this->request(__FUNCTION__, array(
            'orderId' => $orderId,
//            'transSerial' => 'J1AP20180112200052632q6a',
//            'qryToken' => 'J1AP20180112200052632q6a',
            'isQueryOnly' => '0'
        ));
    }

    private function log($logData){
        Utils::logResult($logData, Utils::arrayCopy($this->_config, 'logTarget,debug'));
    }
}