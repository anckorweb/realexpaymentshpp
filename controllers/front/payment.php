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

class RealexPaymentsHppPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        parent::initContent();
        $cart = $this->context->cart;
        if ($cart->id !=0) {
            $realexpayments = new RealexPaymentsHpp();
            $url_redirect = $realexpayments->getConfig()->getUrlFinal();
            if (!$this->module->checkCurrency($cart)) {
                Tools::redirect('index.php?controller=order');
            }
            // $displayAsIframe = $realexpayments->getConfig()->getIframeEnabled();
            $fields = array();
            $redirect = false;
            if (!$realexpayments->getConfig()->getIframeEnabled()) {
                $redirect = true;
                $fields = $this->getFields($realexpayments);
            }
            $lightbox = false;
            if ($realexpayments->getConfig()->displayAsLightbox()) {
                $lightbox = true;
            }
            $this->context->smarty->assign(array(
                'fields' => $fields,
                'redirect' => $redirect,
                'url_redirect' => $url_redirect,
                'lightbox' => $lightbox,
                'iframe_src'=>$this->getIframeSrc($realexpayments),
                'amount'=>$cart->getOrderTotal(true, Cart::BOTH)
            ));
            $this->setTemplate('payment_execution.tpl');
        } else {
            $this->module->getRedirection('step1');
        }
    }

    public function getIframeSrc($realexpayments)
    {
        $url = $realexpayments->getConfig()->getUrlFinal();
        $fields = $this->getFields($realexpayments);


        if ($this->module->getConfig()->getLogsEnabled()) {
            $this->module->writeRequestLog($fields);
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $output = curl_exec($ch);
        $url_iframe = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if ($output !== false) {
            return $url_iframe;
        }
    }

    public function getFields($realexpayments)
    {
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $adress_shipping = new Address($cart->id_address_delivery);
        $country_shipping = new Country($adress_shipping->id_country);
        $adress_billing = new Address($cart->id_address_invoice);
        $country_billing = new Country($adress_billing->id_country);
        $timestamp = RealexPaymentsTools::getTimestamp();
        $merchant_id = $realexpayments->getConfig()->getMerchantId();
        $order_id = $cart->id.'-'.$timestamp;
        $currency = $this->context->currency->iso_code;
        $amount = RealexPaymentsTools::getAmountFormat($cart->getOrderTotal(true, Cart::BOTH));
        $shared_secret = $realexpayments->getConfig()->getSharedSecret();
        if ($realexpayments->getConfig()->getHPPLang() == 'customer') {
            $language_iso = $this->context->language->iso_code;
        } else {
            $language_iso = Tools::strtoupper($realexpayments->getConfig()->getHPPLangIso());
        }
        $server = (Configuration::get('PS_SSL_ENABLED'))?Tools::getShopDomainSsl(true, true).__PS_BASE_URI__:Tools::getShopDomain(true, true).__PS_BASE_URI__;
        $fields = array(
            'TIMESTAMP' => $timestamp,
            'MERCHANT_ID' => $merchant_id,
            'ACCOUNT' => $realexpayments->getConfig()->getSubaccount(),
            'ORDER_ID' => $order_id,
            'CURRENCY' => $currency,
            'AMOUNT' => $amount,
            'SHA1HASH' => RealexPaymentsTools::getHashRequestStandard($timestamp, $merchant_id, $order_id, $amount, $currency, $shared_secret),
            'AUTO_SETTLE_FLAG' => $realexpayments->getConfig()->getAutoSettle(),
            'HPP_LANG' => $language_iso,
            'HPP_CUSTOMER_EMAIL' => $customer->email,
            'HPP_CUSTOMER_FIRSTNAME' => $customer->firstname,
            'HPP_CUSTOMER_LASTNAME' => $customer->lastname,
            'SHIPPING_CODE' => $adress_shipping->postcode,
            'SHIPPING_CO' => $country_shipping->iso_code,
            'BILLING_CODE' => $adress_billing->postcode,
            'BILLING_CO' => $country_billing->iso_code,
            'MERCHANT_RESPONSE_URL' => $server.'module/'.$realexpayments->name.'/validation?content_only=1',
            'HPP_VERSION' => 2,
            'HPP_POST_DIMENSIONS' => $server
        );
        if (in_array($country_billing->iso_code, array('CA', 'US', 'GB'))) {
            $fields['BILLING_CODE'] = RealexPaymentsTools::getPostCodeForAvs($adress_billing, $country_billing->iso_code);
        }
        if ($realexpayments->getConfig()->getCardStorageEnabled()) {
            $payerref = new RealexPaymentsCards($realexpayments);
            $fields['OFFER_SAVE_CARD'] = ($realexpayments->getConfig()->getOfferSaveCard())?1:0;
            if ($ref = $payerref->getPayerRef($customer->id)) {
                $fields['PAYER_EXIST'] = 1;
                $fields['HPP_SELECT_STORED_CARD'] = $ref;
            } else {
                $fields['CARD_STORAGE_ENABLE'] = 1;
                $fields['PAYER_EXIST'] = 0;
            }
            $fields['SHA1HASH'] = RealexPaymentsTools::getHashRequestStoredCard($timestamp, $merchant_id, $order_id, $amount, $currency, $ref, $shared_secret);
        }
        return $fields;
    }
}
