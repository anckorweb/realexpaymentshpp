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

/**
 * Module configuration
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class RealexPaymentsConfig
{
    private $_defaults = array(
        'REALEXPAYMENTS_PRODUCTION'     => 0,
        'REALEXPAYMENTS_ENABLE_LOGS'     => 0,
        'REALEXPAYMENTS_MERCHANT_ID'    => '',
        'REALEXPAYMENTS_SHARED_SECRET'  => '',
        'REALEXPAYMENTS_REBATE_PASSWORD'=> '',
        'REALEXPAYMENTS_SUBACCOUNT'     => '',
        'REALEXPAYMENTS_URL_LIVE'       => 'https://pay.realexpayments.com/pay',
        'REALEXPAYMENTS_URL_SANDBOX'    => 'https://pay.sandbox.realexpayments.com/pay',
        'REALEXPAYMENTS_AUTO_SETTLE'    => '1',
        'REALEXPAYMENTS_HPP_LANG'    => 'customer',
        'REALEXPAYMENTS_HPP_LANG_ISO'    => '',
        'REALEXPAYMENTS_IFRAME'         =>'0',
        'REALEXPAYMENTS_IFRAME_TYPE'    =>'embed',
        'REALEXPAYMENTS_CARD_STORAGE'   => '0',
        'REALEXPAYMENTS_OFFER_SAVE_CARD'   => '0',
        'REALEXPAYMENTS_PENDING_STATUS'   => '2',
    );

    private function setMultiLanguagePaymentText()
    {
        $payment_text = array();
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $payment_text[$lang['id_lang']] = 'Pay by Credit or Debit Card';
        }
        return $payment_text;
    }

    private function _get($name)
    {
        $value = Configuration::get($name);
        if (is_null($value)) {
            $value = false;
        }
        if ($value === false) {
            $value = $this->_defaults[$name];
        }
        return $value;
    }

    private function _getMultilang($name)
    {
        $vals = array();
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            if (Configuration::get($name, $lang['id_lang'])) {
                $vals[$lang['id_lang']] = Configuration::get($name, $lang['id_lang']);
            }
        }
        $value = $vals;
        if (!count($value)) {
            $value = $this->setMultiLanguagePaymentText();
        }
        return $value;
    }

    public function getProdEnabled()
    {
        return $this->_get('REALEXPAYMENTS_PRODUCTION');
    }
    public function getLogsEnabled()
    {
        return $this->_get('REALEXPAYMENTS_ENABLE_LOGS');
    }

    public function getMerchantId()
    {
        return $this->_get('REALEXPAYMENTS_MERCHANT_ID');
    }

    public function getSharedSecret()
    {
        require_once(dirname(__FILE__) ."/encode.php");
        $encoder = new CryptData();
        return $encoder->decrypter($this->_get('REALEXPAYMENTS_SHARED_SECRET'));
    }

    public function getRebatePassword()
    {
        require_once(dirname(__FILE__) ."/encode.php");
        $encoder = new CryptData();
        return $encoder->decrypter($this->_get('REALEXPAYMENTS_REBATE_PASSWORD'));
    }

    public function getSubaccount()
    {
        return $this->_get('REALEXPAYMENTS_SUBACCOUNT');
    }

    public function getUrlSandbox()
    {
        return $this->_get('REALEXPAYMENTS_URL_SANDBOX');
    }

    public function getUrlLive()
    {
        return $this->_get('REALEXPAYMENTS_URL_LIVE');
    }
    public function getPaymentText()
    {
        return $this->_getMultiLang('REALEXPAYMENTS_PAYMENT_TEXT');
    }
    public function getHPPLang()
    {
        return $this->_get('REALEXPAYMENTS_HPP_LANG');
    }
    public function getHPPLangIso()
    {
        return $this->_get('REALEXPAYMENTS_HPP_LANG_ISO');
    }
    public function getAutoSettle()
    {
        return $this->_get('REALEXPAYMENTS_AUTO_SETTLE');
    }
    public function getIframeEnabled()
    {
        return $this->_get('REALEXPAYMENTS_IFRAME');
    }
    public function getIframeType()
    {
        return $this->_get('REALEXPAYMENTS_IFRAME_TYPE');
    }
    public function getCardStorageEnabled()
    {
        return $this->_get('REALEXPAYMENTS_CARD_STORAGE');
    }
    public function getOfferSaveCard()
    {
        return $this->_get('REALEXPAYMENTS_OFFER_SAVE_CARD');
    }
    public function getDelayedStatus()
    {
        return $this->_get('REALEXPAYMENTS_PENDING_STATUS');
    }

    public function getUrlFinal()
    {
        if ($this->getProdEnabled()) {
            return $this->getUrlLive();
        } else {
            return $this->getUrlSandbox();
        }
    }

    public function getUrlApiFinal()
    {
        if ($this->getProdEnabled()) {
            return "https://api.realexpayments.com/epage-remote.cgi";
        } else {
            return "https://api.sandbox.realexpayments.com/epage-remote.cgi";
        }
    }

    public function displayAsLightbox()
    {
        if ($this->getIframeEnabled() && $this->getIframeType() =="lightbox") {
            return true;
        }
        return false;
    }
}
