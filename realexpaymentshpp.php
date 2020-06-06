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
require_once(dirname(__FILE__).'/classes/common.inc.php');

class RealexPaymentsHpp extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();
    protected $_config;

    public function __construct()
    {
        $this->name = 'realexpaymentshpp';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.1';
        $this->author = 'Coccinet';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;
        $this->_config = new RealexPaymentsConfig();
        $this->module_key = '2c42e3cfe750f660dc270e7a9715f577';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Realex Payments HPP');
        $this->description = $this->l('Accept payments via the Realex HPP, while minimising your PCI Compliance requirements.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7.99.99');

        if (!function_exists('curl_version')) {
            $this->warning = $this->l('cURL librairy is not available.');
        } elseif (!Configuration::get('PS_REWRITING_SETTINGS')) {
            $this->warning = $this->l('URL Rewriting must be enabled before using this module.');
        } elseif (Configuration::get('REALEXPAYMENTS_MERCHANT_ID') == ""
                || Configuration::get('REALEXPAYMENTS_SUBACCOUNT') == ""
                || Configuration::get('REALEXPAYMENTS_SHARED_SECRET') =="") {
            $this->warning = $this->l('Realex Payments details must be configured before using this module.');
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        require_once(dirname(__FILE__) . '/classes/RealexPaymentsInstaller.php');
        $installer = new RealexPaymentsInstaller();
        $hooks = array('payment', 'paymentReturn', 'header','displayShoppingCart','DisplayAdminOrderLeft','actionObjectLanguageAddAfter');
        if (!$installer->installModule($this, $hooks) || !$this->cleanLogs()) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('REALEXPAYMENTS_PRODUCTION')
            || !Configuration::deleteByName('REALEXPAYMENTS_ENABLE_LOGS')
            || !Configuration::deleteByName('REALEXPAYMENTS_MERCHANT_ID')
            || !Configuration::deleteByName('REALEXPAYMENTS_SHARED_SECRET')
            || !Configuration::deleteByName('REALEXPAYMENTS_REBATE_PASSWORD')
            || !Configuration::deleteByName('REALEXPAYMENTS_SUBACCOUNT')
            || !Configuration::deleteByName('REALEXPAYMENTS_URL_LIVE')
            || !Configuration::deleteByName('REALEXPAYMENTS_URL_SANDBOX')
            || !Configuration::deleteByName('REALEXPAYMENTS_PAYMENT_TEXT')
            || !Configuration::deleteByName('REALEXPAYMENTS_HPP_LANG')
            || !Configuration::deleteByName('REALEXPAYMENTS_HPP_LANG_ISO')
            || !Configuration::deleteByName('REALEXPAYMENTS_AUTO_SETTLE')
            || !Configuration::deleteByName('REALEXPAYMENTS_IFRAME')
            || !Configuration::deleteByName('REALEXPAYMENTS_IFRAME_TYPE')
            || !Configuration::deleteByName('REALEXPAYMENTS_CARD_STORAGE')
            || !Configuration::deleteByName('REALEXPAYMENTS_OFFER_SAVE_CARD')
            || !$this->cleanLogs()
            || !parent::uninstall()) {
            return false;
        }
            return true;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    protected function _postValidation()
    {
        if (Tools::isSubmit('btnSubmitPrin')) {
            if (!Configuration::get('PS_REWRITING_SETTINGS')) {
                $this->_postErrors[] = $this->l('URL Rewriting must be enabled before using this module.');
            }
            if (!function_exists('curl_version')) {
                $this->_postErrors[] = $this->l('cURL librairy is not available.');
            }
            if (!Tools::getValue('REALEXPAYMENTS_MERCHANT_ID')) {
                $this->_postErrors[] = $this->l('Merchant ID is required.');
            }
            if (!Tools::getValue('REALEXPAYMENTS_SUBACCOUNT')) {
                $this->_postErrors[] = $this->l('Subaccount is required');
            }
            if (!Tools::getValue('REALEXPAYMENTS_URL_LIVE')) {
                $this->_postErrors[] = $this->l('Live URL is required');
            }
            if (!Tools::getValue('REALEXPAYMENTS_URL_SANDBOX')) {
                $this->_postErrors[] = $this->l('Sandbox URL is required');
            }
            if (!Configuration::get('PS_REWRITING_SETTINGS')) {
                $this->_postErrors[] = $this->l('URL Rewriting must be enabled before using this module.');
            }
            if (!function_exists('curl_version')) {
                $this->_postErrors[] = $this->l('cURL librairy is not available.');
            }
            if (Tools::getValue('REALEXPAYMENTS_HPP_LANG') !='customer' && !Tools::getValue('REALEXPAYMENTS_HPP_LANG_ISO')) {
                $this->_postErrors[] = $this->l('HPP language is required');
            }
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                if (!Tools::getValue('REALEXPAYMENTS_PAYMENT_TEXT_'.$lang['id_lang'])) {
                    $this->_postErrors[] = $this->l('Payment text is required for '.$lang['iso_code']);
                }
            }
        }
        if (Tools::isSubmit('btnSubmitSec')) {
            if (!Tools::getValue('REALEXPAYMENTS_SHARED_SECRET')) {
                $this->_postErrors[] = $this->l('Shared secret is required.');
            }
            if (!Tools::getValue('REALEXPAYMENTS_REBATE_PASSWORD')) {
                $this->_postErrors[] = $this->l('Rebate Password is required.');
            }
        }
    }

    public function getContent()
    {
        if (Tools::getValue('actionClear')) {
            $this->cleanFails();
        }
        require_once(dirname(__FILE__) . '/classes/RealexPaymentsAdminConfig.php');
        $admin = new RealexPaymentsAdminConfig($this);
        if (Tools::isSubmit('btnSubmitPrin') || Tools::isSubmit('btnSubmitSec') || Tools::isSubmit('btnClearRecords')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $admin->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }
        $this->_html .= $admin->getContent($this->_config);
        return $this->_html;
    }

    public function hookActionObjectLanguageAddAfter($params)
    {
        $values = array();
        $values['REALEXPAYMENTS_PAYMENT_TEXT'][(int)$params['object']->id] = 'Pay by Credit or Debit Card';
        return Configuration::updateValue('REALEXPAYMENTS_PAYMENT_TEXT', $values['REALEXPAYMENTS_PAYMENT_TEXT']);
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $lightbox = false;
        if ($this->_config->displayAsLightbox()) {
            $lightbox = true;
        }
        $payment_text_array = $this->_config->getPaymentText();
        if (!$payment_text_array[$this->context->language->id]) {
            $payment_text = "Pay By Credit or Debit Card";
        } else {
            $payment_text = $payment_text_array[$this->context->language->id];
        }
        $this->smarty->assign(array(
            'warning_save_card' => ($this->_config->getCardStorageEnabled() && !$this->_config->getOfferSaveCard())?true:false,
            'payment_text' => $payment_text,
            'lightbox' => $lightbox,
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        $state = $params['objOrder']->getCurrentState();
        if (in_array($state, array(Configuration::get('PS_OS_PAYMENT'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('REALEXPAYMENTS_PENDING_STATUS')))) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'status' => 'ok',
                'id_order' => $params['objOrder']->id
            ));
            if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference)) {
                $this->smarty->assign('reference', $params['objOrder']->reference);
            }
        } else {
            $this->smarty->assign('status', 'failed');
        }
        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookHeader($params)
    {
        $this->context->controller->addCSS($this->_path.'views/css/realexpayments.css', 'all');
        $this->context->controller->addJS($this->_path.'views/js/realexpayments.js', 'all');
    }

    public function hookDisplayShoppingCart()
    {
        if (Tools::getValue('realexpayments_error')) {
// $error = Tools::getValue('realexpayments_error');
            $this->context->controller->errors[] = $this->l('Your transaction was unsuccessful, please try again or use an alternate form of payment.');
            unset($_SERVER['HTTP_REFERER']);
            $this->context->smarty->assign('server', $_SERVER);
        }
    }

    public function hookDisplayAdminOrderLeft($params)
    {
        require_once(dirname(__FILE__) . '/classes/RealexPaymentsAdminTransactionManagement.php');
        $transaction = new RealexPaymentsAdminTransactionManagement();
        $order = new Order($params['id_order']);
        $realexPaymentsOrder = new RealexPaymentsOrders();
        $realexOrder = $realexPaymentsOrder->getOrder($order->id);
        $obj_currency = new Currency($order->id_currency);
        // $currency = $obj_currency->sign;
        $transaction_return = "";
        $transaction_statut = "";
        $original_amount = $realexOrder['realexpayments_original_amount'];
        $request_set = false;
        // $new_state_success = false;
        if (Tools::getValue('realexpayments_amount')) {
            $realexOrder['realexpayments_original_amount'] = str_replace(',', '.', Tools::getValue('realexpayments_amount'));
        }
        if (Tools::getValue('realexpayments_transaction')) {
            $request_set = Tools::getValue('realexpayments_transaction');
            switch ($request_set) {
                case 'settle':
                    $message_success = $this->l('Settlement Successful');
                    break;
                case 'void':
                    $message_success = $this->l('Void Successful');
                    break;
                case 'rebate':
                    $message_success = $this->l('Rebate Successful');
                    break;
                default:
                    $request_set = false;
                    break;
            }
        }
        if ($request_set) {
            $request = $transaction->setRequest($realexOrder, $request_set);
            $xml_result = $transaction->sendRequest($request);
            $timestamp_result = $xml_result->attributes()->timestamp;
            $merchantid_result = $xml_result->merchantid;
            $orderid_result = $xml_result->orderid;
            $hash_result = $xml_result->sha1hash;
            $result_result = $xml_result->result;
            $message_result = $xml_result->message;
            $pasref_result = $xml_result->pasref;
            $authcode_result = $xml_result->authcode;
            $hash_response = RealexPaymentsTools::getHashResponse($timestamp_result, $merchantid_result, $orderid_result, $result_result, $message_result, $pasref_result, $authcode_result, $this->getConfig()->getSharedSecret());
            $success = 0;
            switch ($result_result) {
                case '00':
                    if ($hash_response == $hash_result) {
                        $transaction_statut = "OK";
                        $transaction_return = $message_success;
                        $success = 1;
                    } else {
                        $transaction_statut = "KO";
                        $transaction_return = $this->l('Error')." : ".$this->l('Hash Error');
                    }
                    break;
                default:
                    $transaction_statut = "KO";
                    $transaction_return = $this->l('Error')." : ".$message_result;
                    break;
            }
            $amountToSave = $request_set !='void'?$realexOrder['realexpayments_original_amount']:$original_amount;
            $transaction->saveResult($order->id, $request_set, $amountToSave, $transaction_return, $success);
        }
        $this->smarty->assign('current_index', 'index.php?controller=AdminOrders');
        $this->smarty->assign('order', $order);
        $this->smarty->assign('original_amount', number_format($original_amount));
        $this->smarty->assign('max_authorized', round($original_amount*1.15, 2));
        $this->smarty->assign('realexOrder', $realexOrder);
        $this->smarty->assign('transaction_return', $transaction_return);
        $this->smarty->assign('transaction_statut', $transaction_statut);
        $this->smarty->assign('transaction_history', $transaction->getHistory($order->id));
        return $this->display(__FILE__, 'adminorder.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getResults($post)
    {
        $results = new RealexPaymentsResults($this);
        return $results->getResults($post);
    }

    public function getLogs($results)
    {
        $logs = new RealexPaymentsResults($this);
        return $logs->getLogs($results);
    }
    public function writeLogs($results)
    {
        $logs = new RealexPaymentsResults($this);
        return $logs->writeLogs($results);
    }
    public function writeRequestLog($results)
    {
        $logs = new RealexPaymentsResults($this);
        return $logs->writeRequestLog($results);
    }

    public function saveFailure($code, $message, $cart, $order_id)
    {
        $logs = new RealexPaymentsResults($this);
        return $logs->saveFailure($code, $message, $cart, $order_id);
    }

    public function getRedirection($action, $cart = null, $customer = null, $error = null)
    {
        $redirect = new RealexPaymentsRedirection($this);
        return $redirect->getRedirect($action, $cart, $customer, $error);
    }

    public function cleanLogs()
    {
        $file = fopen(dirname(__FILE__)."/logs/logs.txt", "w");
        fclose($file);
        return true;
    }
    public function cleanFails()
    {
        Db::getInstance()->delete('realexpayments_failures');
    }
}
