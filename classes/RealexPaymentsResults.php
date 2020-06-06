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

class RealexPaymentsResults
{
    private $_module;

    public function __construct($module)
    {
        $this->_module = $module;
    }
    public function getResults($POST)
    {
        $results = array();
        foreach ($POST as $key => $value) {
            $results[$key] = Tools::getValue($key);
        }
        return $results;
    }

    public function getLogs($results, $type = 'light')
    {
        $logs ="----- Payment Information -----\r\n";
        $results['CARDDIGITS'] = Tools::substr($results['CARDDIGITS'], -4);
        $results['SAVED_PMT_DIGITS'] = Tools::substr($results['SAVED_PMT_DIGITS'], -4);
        $results['EXPDATE'] = "***";
        $results['SAVED_PMT_EXPDATE'] = "***";
        $results['SHA1HASH'] = "***";
        if (!empty($results['HPP_CHOSEN_PMT_REF'])) {
            $results['HPP_PAYMENT_CHOICE'] = $results['HPP_CHOSEN_PMT_REF'];
        }
        switch ($results['ECI']) {
            case '2':
            case '5':
                $results['3D_SECURE_RESULT'] = "Customer Authenticated" ;
                break;
            case '1':
            case '6':
                $results['3D_SECURE_RESULT'] = "Authentication Attempt Acknowledged" ;
                break;
            default:
                $results['3D_SECURE_RESULT'] ="";
                break;
        }
        if (!empty($results['DCCCHOICE'])) {
            $results['DCC_SELECTED'] = $results['DCCCHOICE'];
            $results['DCC_RATE'] = $results['DCCRATE'];
            $results['DCC_CARD_HOLDER_AMOUNT'] = $results['DCCCARDHOLDERAMOUNT'];
            $results['DCC_CARD_HOLDER_CURRENCY'] = $results['DCCCARDHOLDERCURRENCY'];
        }
        $results['DCCCHOICE'] ='';
        $results['DCCRATE'] ='';
        $results['DCCCARDHOLDERAMOUNT'] ='';
        $results['DCCCARDHOLDERCURRENCY'] ='';
        $results['HPP_CHOSEN_PMT_REF'] = '';
        if ($type == 'light') {
            $toShow = array();
            $toShow[] = 'TYPEPROD';
            $toShow[] = 'ORDER_ID';
            $toShow[] = 'RESULT';
            $toShow[] = 'MESSAGE';
            $toShow[] = 'AUTHCODE';
            $toShow[] = 'AVSPOSTCODERESULT';
            $toShow[] = 'AVSADDRESSRESULT';
            $toShow[] = 'CVNRESULT';
            $toShow[] = 'CAVV';
            $toShow[] = 'XID';
            $toShow[] = 'ECI';
            $toShow[] = 'CARDTYPE';
            $toShow[] = 'CARDDIGITS';
            $toShow[] = 'CARDNAME';
            $toShow[] = 'HPP_FRAUDFILTER_RESULT';
            $toShow[] = 'HPP_PAYMENT_CHOICE';
            $toShow[] = '3D_SECURE_RESULT';
            $toShow[] = 'DCC_SELECTED';
            $toShow[] = 'DCC_RATE';
            $toShow[] = 'DCC_CARD_HOLDER_AMOUNT';
            $toShow[] = 'DCC_CARD_HOLDER_CURRENCY';
        }
        if ($this->_module->getConfig()->getProdEnabled()) {
            $results['TYPEPROD'] = "LIVE";
        } else {
            $results['TYPEPROD']  = "SANDBOX";
        }
        foreach ($results as $key => $value) {
            if ($type == 'light') {
                if (in_array($key, $toShow) && !empty($value)) {
                    $logs.= $key." : ".$value."\r\n";
                }
            } else {
                if (!empty($value)) {
                    $logs.= "[".$key."] : ".$value."\r\n";
                }
            }
        }
        return $logs;
    }

    public function getRequestLog($results)
    {

        $logs ="----- HPP Request Information -----\r\n";
        $logs .="['TIMESTAMP'] => ".$results["TIMESTAMP"]."\r\n";
        $logs .="['MERCHANT_ID'] => ".$results["MERCHANT_ID"]."\r\n";
        $logs .="['ACCOUNT'] => ".$results["ACCOUNT"]."\r\n";
        $logs .="['ORDER_ID'] => ".$results["ORDER_ID"]."\r\n";
        $logs .="['CURRENCY'] => ".$results["CURRENCY"]."\r\n";
        $logs .="['AMOUNT'] => ".$results["AMOUNT"]."\r\n";
        if ($this->_module->getConfig()->getProdEnabled()) {
            $logs .="['SHA1HASH'] => ******** \r\n";
        } else {
            $logs .="['SHA1HASH'] => ".$results["SHA1HASH"]."\r\n";
        }
        $logs .="['AUTO_SETTLE_FLAG'] => ".$results["AUTO_SETTLE_FLAG"]."\r\n";
        $logs .="['HPP_LANG'] => ".$results["HPP_LANG"]."\r\n";
        $logs .="['HPP_CUSTOMER_EMAIL'] => ".$results["HPP_CUSTOMER_EMAIL"]."\r\n";
        $logs .="['HPP_CUSTOMER_FIRSTNAME'] => ".$results["HPP_CUSTOMER_FIRSTNAME"]."\r\n";
        $logs .="['HPP_CUSTOMER_LASTNAME'] => ".$results["HPP_CUSTOMER_LASTNAME"]."\r\n";
        $logs .="['SHIPPING_CODE'] => ".$results["SHIPPING_CODE"]."\r\n";
        $logs .="['SHIPPING_CO'] => ".$results["SHIPPING_CO"]."\r\n";
        $logs .="['BILLING_CODE'] => ".$results["BILLING_CODE"]."\r\n";
        $logs .="['BILLING_CO'] => ".$results["BILLING_CO"]."\r\n";
        $logs .="['MERCHANT_RESPONSE_URL'] => ".$results["MERCHANT_RESPONSE_URL"]."\r\n";
        $logs .="['HPP_VERSION'] => ".$results["HPP_VERSION"]."\r\n";
        $logs .="['HPP_POST_DIMENSIONS'] => ".$results["HPP_POST_DIMENSIONS"]."\r\n";
        $logs .="['OFFER_SAVE_CARD'] => ".$results["OFFER_SAVE_CARD"]."\r\n";
        $logs .="['PAYER_EXIST'] => ".$results["PAYER_EXIST"]."\r\n";
        $logs .="['HPP_SELECT_STORED_CARD'] => ".$results["HPP_SELECT_STORED_CARD"]."\r\n";
        return $logs;
    }

    public function writeLogs($results)
    {
        $date = new DateTime();
        $logsFull = $date->format('Y-m-d H:i:s')." ";
        $logsFull .= $this->getLogs($results, 'full');
        $logsFull .= "\r\n";
        $file = fopen(dirname(__FILE__)."/../logs/logs.txt", "a+") or die(dirname(__FILE__)."/../logs/logs.txt");
        fwrite($file, $logsFull);
        fclose($file);
    }

    public function writeRequestLog($results)
    {
        $date = new DateTime();
        $logsFull = $date->format('Y-m-d H:i:s')."\r\n";
        $logsFull .= $this->getRequestLog($results);
        $logsFull .= "\r\n";
        $file = fopen(dirname(__FILE__)."/../logs/logs.txt", "a+") or die(dirname(__FILE__)."/../logs/logs.txt");
        fwrite($file, $logsFull);
        fclose($file);
    }

    public function saveFailure($code, $message, $cart, $order_id)
    {
        Db::getInstance()->insert('realexpayments_failures', array(
            'error_code'      => pSQL($code),
            'error_message'      => pSQL($message),
            'cart_id'      => (int)$cart,
            'order_id'      => pSQL($order_id),
            'date_add'      => date('Y-m-d H:i:s')
        ));
    }
}
