<?php
/**

 * NOTICE OF LICENSE

 *

 * This file is licenced under the Software License Agreement.

 * With the purchase or the installation of the software in your application

 * you accept the licence agreement.

 *

 * You must not modify, adapt or create derivative works of this source code

 *

 *  @author    Coccinet

 *  @copyright 2017 Coccinet

 *  @license   LICENSE.txt

 */
 
if (!defined('_PS_VERSION_')) {
    exit;
}
class RealexPaymentsTools
{
    public static function getTimestamp()
    {
        $date = new DATETIME();
        return $date->format('YmdHis');
    }

    public static function getAmountFormat($amount)
    {
        $tab = explode('.', $amount);
        if (count($tab) == 1) {
            return $tab[0].'00';
        } else {
            if (Tools::strlen(($tab[1])) == 1) {
                $amount = $tab[0].$tab[1].'0';
            } else {
                $amount = $tab[0].$tab[1];
            }
        }
            return $amount;
    }

    public static function getHashRequestStandard($timestamp, $merchant_id, $order_id, $amount, $currency, $shared_secret)
    {
        $string = sha1($timestamp.".".$merchant_id.".".$order_id.".".$amount.".".$currency);

        return sha1($string.".".$shared_secret);
    }
    public static function getHashRequestStoredCard($timestamp, $merchant_id, $order_id, $amount, $currency, $payer_ref, $shared_secret)
    {
        $string = sha1($timestamp.".".$merchant_id.".".$order_id.".".$amount.".".$currency.".".$payer_ref.".");

        return sha1($string.".".$shared_secret);
    }
    public static function getHashRequestPayerEdit($timestamp, $merchant_id, $order_id, $amount, $currency, $payer_ref, $shared_secret)
    {
        $string = sha1($timestamp.".".$merchant_id.".".$order_id.".".$amount.".".$currency.".".$payer_ref);

        return sha1($string.".".$shared_secret);
    }
    public static function getHashRequestTransactionManagement($timestamp, $merchant_id, $order_id, $amount, $shared_secret, $currency = null)
    {
        $string = sha1($timestamp.".".$merchant_id.".".$order_id.".".$amount.".".$currency.".");
        return sha1($string.".".$shared_secret);
    }
    public static function getHashResponse($timestamp, $merchant_id, $order_id, $result, $message, $pasref, $authcode, $shared_secret)
    {
        $string = sha1($timestamp.".".$merchant_id.".".$order_id.".".$result.".".$message.".".$pasref.".".$authcode);
        return sha1($string.".".$shared_secret);
    }

    public static function getPostCodeForAvs($adress, $country)
    {
        switch ($country) {
            case 'US':
            case 'CA':
                $postcode = str_replace(' ', '', $adress->postcode)."|".$adress->address1;
                break;
            case 'GB':
                $postcode = preg_replace('~\D~', '', $adress->postcode)."|".preg_replace('~\D~', '', $adress->address1);
                break;
        }
        return $postcode;
    }
}
