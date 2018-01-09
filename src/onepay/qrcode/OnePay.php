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
        'gateway' => 'https://ONEPAY.co.jp/gateway',
        'merchantCode' => '100282222692',
        'branchCode' => '001',
        'terminalCode' => '01',
    );

    private $_commonParams = array(
        'serviceMode' => 'B01',
        'signType' => '0',
        'isBackTran' => '0'
    );

    public function __construct($config){
        $this->_config = array_merge($this->_defaultConfig, $config);
    }

    public function token(){
        $tokenParams = array (
            $this->_config['merchantCode'],
            $this->_config['branchCode'],
            $this->_config['terminalCode'],
            date('Ymd'),
            str_pad(Utils::getMillisecond(), 6, '0', STR_PAD_LEFT)
        );
        return implode('', $tokenParams);
    }

    public function pay(){
        $urlCommon = implode('',  $this->_commonParams);
        return $url = implode('/', array($this->_config['gateway'], $urlCommon, $this->token()));
    }

    public function refund(){

    }

    public function remove(){

    }

    public function detail(){

    }

    public function transactionSerialDetail(){

    }

    public function check(){

    }
}