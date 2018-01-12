<?php
/**
 * Created by ngonhan2k5
 * Date: 1/10/2018
 * Time: 12:42 AM
 */

require_once 'vendor/autoload.php';

$config = array(
    'debug' => true,
    'gateway' => 'http://',
    'merchantCode' => '100282232622',
    'branchCode' => '001',
    'terminalCode' => '01',
    'privateKeyPath' => 'private.pem'
);

$api = new \onepay\qrcode\OnePay($config);
var_dump($api->pay(1));
