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
class RealexPaymentsCards
{
    private $_module;

    public function __construct($module)
    {
        $this->_module = $module;
    }

    public function getPayerRef($id_customer)
    {
        $value = Db::getInstance()->getValue('select payer_ref from `'._DB_PREFIX_.'realexpayments_payerref` WHERE id_customer='.(int)$id_customer);
        return $value;
    }

    public function addPayer($id_customer, $ref, $id_order)
    {
// $target = Tools::getValue('id');
// $name = Tools::getValue('name');
        if (Db::getInstance()->insert('realexpayments_payerref', array(
            'id_customer' => (int)$id_customer,
            'payer_ref'      => pSQL($ref),
            'date_add'      => date('Y-m-d H:i:s')
        ))) {
            $this->setPayerEdit($id_customer, $ref, $id_order);
        }
    }

    public function setPayerEdit($id_customer, $ref, $id_order)
    {
        $customer = new Customer($id_customer);
        $order = new Order($id_order);
        $address = new Address($order->id_address_invoice);
        $state = new State($address->id_state);
        $country = new Country($address->id_country);
        $timestamp = RealexPaymentsTools::getTimestamp();
        $merchant_id = $this->_module->getConfig()->getMerchantId();
        $subaccount = $this->_module->getConfig()->getSubaccount();
        $gender = new Gender($customer->id_gender);
        $secret = $this->_module->getConfig()->getSharedSecret();
        $amount = "";
        $orderid = "";
        $currency = "";
        $sha1 = RealexPaymentsTools::getHashRequestPayerEdit($timestamp, $merchant_id, $orderid, $amount, $currency, $ref, $secret);
        $xml = "<?xml version='1.0' encoding='UTF-8'?>
        <request type='payer-edit' timestamp='".$timestamp."'>
        <merchantid>".$merchant_id."</merchantid>
        <account>".$subaccount."</account>
        <payer ref='".$ref."' type='Retail'>
        <title>".$gender->name."</title>
        <firstname>".$customer->firstname."</firstname>
        <surname>".$customer->lastname."</surname>
        <company>".$customer->company."</company>
        <address>
        <line1>".$address->address1."</line1>
        <line2>".$address->address2."</line2>
        <city>".$address->city."</city>
        <county>".$state->getNameById($address->id_state)."</county>
        <postcode>".$address->postcode."</postcode>
        <country code='".$country->iso_code."'>".$country->name[$customer->id_lang]."</country>
        </address>
        <phonenumbers>
        <home>".$address->phone."</home>
        <mobile>".$address->phone_mobile."</mobile>
        </phonenumbers>
        <email>".$customer->email."</email>
        <vatnumber>".$address->vat_number."</vatnumber>
        </payer>
        <sha1hash>".$sha1."</sha1hash>
        </request>";
        $url = $this->_module->getConfig()->getUrlApiFinal();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_exec($ch);
        curl_close($ch);
    }
}
