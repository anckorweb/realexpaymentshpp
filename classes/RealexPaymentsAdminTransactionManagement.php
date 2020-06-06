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

/**
 * Admin configuration page helper
 */
class RealexPaymentsAdminTransactionManagement extends RealexPaymentsHpp
{
    public function setRequest($realexOrder, $action)
    {
        $merchant_id = $this->getConfig()->getMerchantId();
        $subaccount = $this->getConfig()->getSubaccount();
        $secret = $this->getConfig()->getSharedSecret();
        $realexorderid = $realexOrder['realexpayments_order_id'];
        $pasref = $realexOrder['realexpayments_pasref'];
        $authcode = $realexOrder['realexpayments_authcode'];
        $refundhash = sha1($this->getConfig()->getRebatePassword());
        $currency = null;
        $originalamount ="";
        if ($action !='void') {
            $originalamount = RealexPaymentsTools::getAmountFormat($realexOrder['realexpayments_original_amount']);
        }
        $timestamp = RealexPaymentsTools::getTimestamp();
        if ($action =='rebate') {
            $currency = $realexOrder['realexpayments_original_currency'];
        }
        $sha1 = RealexPaymentsTools::getHashRequestTransactionManagement($timestamp, $merchant_id, $realexorderid, $originalamount, $secret, $currency);
        $xml = "<?xml version='1.0' encoding='UTF-8'?>
        <request type='".$action."' timestamp='".$timestamp."'>
        <merchantid>".$merchant_id."</merchantid>";
        if ($action !='rebate') {
            $xml.=     "<account>".$subaccount."</account>";
        }
        $xml.=     "<orderid>".$realexorderid."</orderid>";
        if ($action =='rebate') {
            $xml.=     "<amount currency='".$realexOrder['realexpayments_original_currency']."'>".$originalamount."</amount>";
        }
        $xml.=       "<pasref>".$pasref."</pasref>";
        if ($action !='void') {
            $xml.=      "<authcode>".$authcode."</authcode>";
        }
        if ($action =='settle') {
            $xml.=     "<amount>".$originalamount."</amount>";
        }
        if ($action =='rebate') {
            $xml.=      "<refundhash>".$refundhash."</refundhash>";
        }
        $xml.=      "<sha1hash>".$sha1."</sha1hash>
        </request>";
        return $xml;
    }

    public function sendRequest($request)
    {
        $url = $this->getConfig()->getUrlApiFinal();

//log admin Orders request


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $output = curl_exec($ch);
        curl_close($ch);
        $xm = simplexml_load_string($output);
        return $xm;
    }

    public function saveResult($id_order, $action, $amount, $result, $success)
    {
        Db::getInstance()->insert('realexpayments_transaction_history', array(
            'id_order' => (int)$id_order,
            'action'      => pSQL($action),
            'amount'      => pSQL($amount),
            'result'      => pSQL($result),
            'success'      => (int)$success,
            'date_add'      => date('Y-m-d H:i:s')
        ));
    }

    public function getHistory($id_order)
    {
        $values = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'realexpayments_transaction_history` WHERE id_order='.(int)$id_order." ORDER BY id_realexpayments_transaction_history DESC");
        foreach ($values as $key => $value) {
            $values[$key]['amount'] = number_format($value['amount'], 2);
        }
        return $values;
    }
}
