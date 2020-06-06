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
class RealexPaymentsInstaller
{
    public function installModule($module, $hooks)
    {

        foreach ($hooks as $hook) {
            if (!$module->registerHook($hook)) {
                return false;
            }
        }
        if (!$this->_installDb()) {
            return false;
        }
        if (!$this->_updateConfig($module)) {
            return false;
        }
        if (!$this->_createPendingStatus($module)) {
            return false;
        }
        return true;
    }

    private function _installDb()
    {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'realexpayments_payerref` (
                `id_realexpayments_payerref` INT(10) NOT NULL AUTO_INCREMENT,
                `id_customer` INT(10) NOT NULL,
                `payer_ref` VARCHAR(50) NOT NULL,
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id_realexpayments_payerref`),
                INDEX `id_customer` (`id_customer`)
            )
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
        ) || !Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'realexpayments_orders` (
                `realexpayments_ps_order_id` INT(11) NOT NULL,
                `realexpayments_pasref` VARCHAR(50) NOT NULL,
                `realexpayments_authcode` VARCHAR(50) NOT NULL,
                `realexpayments_order_id` VARCHAR(50) NOT NULL,
                `realexpayments_original_amount` FLOAT NOT NULL,
                `realexpayments_original_currency` VARCHAR(50) NOT NULL,
                UNIQUE INDEX `realexpayments_ps_order_id` (`realexpayments_ps_order_id`)
            )
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
        ) || !Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'realexpayments_transaction_history` (
                `id_realexpayments_transaction_history` INT(11) NOT NULL AUTO_INCREMENT,
                `id_order` INT(11) NOT NULL,
                `action` VARCHAR(50) NOT NULL,
                `amount` FLOAT NOT NULL,
                `result` VARCHAR(255) NOT NULL,
                `success` INT(1) NOT NULL DEFAULT "0",
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id_realexpayments_transaction_history`),
                INDEX `id_order` (`id_order`)
            )
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
        )  || !Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'realexpayments_failures` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `error_code` VARCHAR(50) NOT NULL,
                `error_message` VARCHAR(50) NOT NULL,
                `cart_id` INT(11) NOT NULL,
                `order_id` VARCHAR(50) NOT NULL,
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id`)
            )
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
        )) {
            return false;
        }
        return true;
    }

    private function _updateConfig($module)
    {
        if (!Configuration::updateValue('REALEXPAYMENTS_PRODUCTION', $module->getConfig()->getProdEnabled())
            || !Configuration::updateValue('REALEXPAYMENTS_ENABLE_LOGS', $module->getConfig()->getLogsEnabled())
            || !Configuration::updateValue('REALEXPAYMENTS_MERCHANT_ID', $module->getConfig()->getMerchantId())
            || !Configuration::updateValue('REALEXPAYMENTS_SHARED_SECRET', $module->getConfig()->getSharedSecret())
            || !Configuration::updateValue('REALEXPAYMENTS_REBATE_PASSWORD', $module->getConfig()->getRebatePassword())
            || !Configuration::updateValue('REALEXPAYMENTS_SUBACCOUNT', $module->getConfig()->getSubaccount())
            || !Configuration::updateValue('REALEXPAYMENTS_URL_SANDBOX', $module->getConfig()->getUrlSandbox())
            || !Configuration::updateValue('REALEXPAYMENTS_PAYMENT_TEXT', $module->getConfig()->getPaymentText())
            || !Configuration::updateValue('REALEXPAYMENTS_HPP_LANG', $module->getConfig()->getHPPLang())
            || !Configuration::updateValue('REALEXPAYMENTS_HPP_LANG_ISO', $module->getConfig()->getHPPLangIso())
            || !Configuration::updateValue('REALEXPAYMENTS_URL_LIVE', $module->getConfig()->getUrlLive())
            || !Configuration::updateValue('REALEXPAYMENTS_IFRAME', $module->getConfig()->getIframeEnabled())
            || !Configuration::updateValue('REALEXPAYMENTS_IFRAME_TYPE', $module->getConfig()->getIframeType())
            || !Configuration::updateValue('REALEXPAYMENTS_AUTO_SETTLE', $module->getConfig()->getAutoSettle())
            || !Configuration::updateValue('REALEXPAYMENTS_CARD_STORAGE', $module->getConfig()->getCardStorageEnabled())
            || !Configuration::updateValue('REALEXPAYMENTS_OFFER_SAVE_CARD', $module->getConfig()->getOfferSaveCard())
        ) {
            return false;
        }
        return true;
    }

    private function _createPendingStatus($module)
    {
        if (!$this->_isValidState(Configuration::get('REALEXPAYMENTS_PENDING_STATUS'))) {
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = 'Payment accepted / Waiting for settlement';
            }
            $orderState->send_email = false;
            $orderState->color = '#DDEEFF';
            $orderState->hidden = false;
            $orderState->module_name = $module->name;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->invoice = false;
            $orderState->paid = false;
            if ($orderState->add()) {
                if ($this->_copyOrderStateImage($orderState->id) && (Configuration::updateValue('REALEXPAYMENTS_PENDING_STATUS', (int)$orderState->id))) {
                    return true;
                }
            }
        } else {
            return true;
        }
    }

    private function _isValidState($id)
    {
        if (empty($id)) {
            return false;
        }
        $value = Db::getInstance()->getValue('select 1 from `'._DB_PREFIX_.'order_state` WHERE id_order_state='.(int)$id);
        return $value !== false;
    }

    private function _copyOrderStateImage($orderStateId)
    {
        $src = dirname(dirname(__FILE__)).'/img/orderState.gif';
        $dst = dirname(dirname(__FILE__)).'/../../img/os/'.((int)$orderStateId).'.gif';
        copy($src, $dst);
        return true;
    }
}
