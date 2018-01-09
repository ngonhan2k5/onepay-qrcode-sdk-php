<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 1/9/2018
 * Time: 11:24 PM
 */

namespace onepay\qrcode;


class Utils
{
    /**
     * @param $para
     * @return array
     */
    static function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * @param $para
     * @return mixed
     */
    static function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * @param $para
     * @return bool|string
     */
    static function createLinkstring($para) {
        $arg  = "";
        foreach ($para as $key => $val) {
            $arg.=$key."=".$val."&";
        }
        $arg = substr($arg,0,strlen($arg)-2);
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        return $arg;
    }

    /**
     * @param $url
     * @param $cacert_url
     * @param $para
     * @param string $input_charset
     * @return mixed
     */
    static function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {
        if (trim($input_charset) != '') {
            $url = $url."_input_charset=".$input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);
        curl_setopt($curl, CURLOPT_HEADER, 0 );
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$para);
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );
        curl_close($curl);
        return $responseText;
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
     */
    static function logResult($word='') {
        $fp = fopen("log.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"Time：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}