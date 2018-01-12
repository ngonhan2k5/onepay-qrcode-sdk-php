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
$result = $api->pay(1);
$oderId = $result['data']['result']['orderId'];
$api->refund(1, $oderId);
$api->reverse($oderId);
$api->orderQuery($oderId);
$api->orderDetailQuery($oderId);
$api->confirm($oderId);

