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
 * Admin configuration page helper
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RealexPaymentsAdminConfig extends RealexPaymentsHpp
{
    public $html ='';

    public function getContent()
    {
        $this->html.= $this-> _displayRealexPaymentsInfos();
        $this->html.='<ul class="nav nav-tabs" id="tabConfig">
        <li class="active">
        <a href="#configPrin">
        MAIN SETTINGS
        </a>
        </li>
        <li>
        <a href="#configSec">
        CREDENTIALS
        </a>
        </li>
        <li>
        <a href="#failures">
        DECLINES REPORT
        </a>
        </li>
        </ul>';
        $this->html.= "<div class='tab-content panel'>";
        $this->html.= "<div class='tab-pane active' id='configPrin'>";
        $this->html.= $this-> _renderFormPrin();
        $this->html.= "</div>";
        $this->html.= "<div class='tab-pane' id='configSec'>";
        $this->html.= $this-> _renderFormSec();
        $this->html.= "</div>";
        $this->html.= "<div class='tab-pane' id='failures'>";
        $this->html.= $this-> _displayRealexPaymentsFailures();
        $this->html.= "</div>";
        $this->html.= "</div>";
        $this->html.= "<script>
        $('#tabConfig a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        })
        $('ul.nav-tabs > li > a').on('shown.bs.tab', function(e) {    
            var id = $(e.target).attr('href').substr(1);
            console.log(id);
            window.location.hash = id;
        });

        // on load of the page: switch to the currently selected tab
        var hash = window.location.hash;
        $('#tabConfig a[href=\"' + hash + '\"]').tab('show');
        </script>";

        return $this->html;
    }

    protected function _displayRealexPaymentsFailures()
    {
        $failures = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'realexpayments_failures` ORDER BY id DESC');
        $this->smarty->assign(array(
            'failures' => $failures,
        ));
        return $this->display(dirname(dirname(__FILE__)).'/'.$this->name.'.php', 'failures.tpl');
    }
    protected function _displayRealexPaymentsInfos()
    {
        if (Configuration::get('PS_SSL_ENABLED')) {
            $link_request = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'module/'.$this->name.'/payment';
            $link_response = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'module/'.$this->name.'/validation';
        } else {
            $link_request = Tools::getShopDomain(true, true).__PS_BASE_URI__.'module/'.$this->name.'/payment';
            $link_response = Tools::getShopDomain(true, true).__PS_BASE_URI__.'module/'.$this->name.'/validation';
        }
        $this->smarty->assign(array(
            'link_request' => $link_request,
            'link_response' => $link_response
        ));

        return $this->display(dirname(dirname(__FILE__)).'/'.$this->name.'.php', 'infos.tpl');
    }

    protected function _renderFormPrin()
    {
        $this->context->controller->addJS($this->_path.'views/js/realexpayments.js', 'all');

        $disabled_iframe = (Configuration::get('PS_SSL_ENABLED') || !$this->_config->getProdEnabled())?false:true;
        // $disabled = false;
        $orderstates = OrderState::getOrderStates((int)Context::getContext()->language->id);
        $options_orderState = array();
        foreach ($orderstates as $orderstate) {
            $options_orderState[] = array(
                "id" => (int)$orderstate['id_order_state'],
                "name" => $orderstate['name']
            );
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Realex Payments Module Main Configuration'),
                    'icon' => 'icon-gear'
                ),
                'input' => array(
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Mode'),
                        'name' => 'REALEXPAYMENTS_PRODUCTION',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Sandbox')
                            ),
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Live')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable logs'),
                        'name' => 'REALEXPAYMENTS_ENABLE_LOGS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Merchant ID'),
                        'name' => 'REALEXPAYMENTS_MERCHANT_ID',
                        'required' => true
                    ),

                    array(
                        'type' => 'text',
                        'label' => $this->l('Subaccount'),
                        'name' => 'REALEXPAYMENTS_SUBACCOUNT',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Live URL'),
                        'name' => 'REALEXPAYMENTS_URL_LIVE',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox URL'),
                        'name' => 'REALEXPAYMENTS_URL_SANDBOX',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Payment Choice Description'),
                        'name' => 'REALEXPAYMENTS_PAYMENT_TEXT',
                        'required' => true,
                        'lang' => true
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('HPP Language'),
                        'name' => 'REALEXPAYMENTS_HPP_LANG',
                        'values' => array(
                            array(
                                'id' => 'customer',
                                'value' => "customer",
                                'label' => $this->l('Customer language')
                            ),
                            array(
                                'id' => 'force',
                                'value' => "force",
                                'label' => $this->l('Language Code')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => '',
                        'name' => 'REALEXPAYMENTS_HPP_LANG_ISO',
                        'required' => false,
                        'class'    => 'md',
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Transaction Type'),
                        'name' => 'REALEXPAYMENTS_AUTO_SETTLE',
                        'values' => array(
                            array(
                                'id' => 'settle_auto',
                                'value' => "1",
                                'label' => $this->l('Auto-Settle')
                            ),
                            array(
                                'id' => 'settle_delay',
                                'value' => "0",
                                'label' => $this->l('Delayed Settlement')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Delayed Settlement Status'),
                        'name' => 'REALEXPAYMENTS_PENDING_STATUS',
                        'required' => true,
                        'options' => array(
                            'query' => $options_orderState,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('iFrame'),
                        'name' => 'REALEXPAYMENTS_IFRAME',
                        'disabled' => $disabled_iframe,
                        'desc' => $this->l('SSL must be enabled to choose Iframe mode, except in sandbox mode'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('If iFrame is Enabled'),
                        'name' => 'REALEXPAYMENTS_IFRAME_TYPE',
                        'values' => array(
                            array(
                                'id' => 'iframetype_embed',
                                'value' => "embed",
                                'label' => $this->l('Embedded')
                            ),
                            array(
                                'id' => 'iframetype_lightbox',
                                'value' => "lightbox",
                                'label' => $this->l('Lightbox')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Card Storage'),
                        'name' => 'REALEXPAYMENTS_CARD_STORAGE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Offer Save Card'),
                        'name' => 'REALEXPAYMENTS_OFFER_SAVE_CARD',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
                ),
                );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmitPrin';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }

    protected function _renderFormSec()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Realex Payments Module Credentials Configuration'),
                    'icon' => 'icon-gear'
                ),
                'input' => array(
                    array(
                        'type' => 'password',
                        'label' => $this->l('Shared secret'),
                        'name' => 'REALEXPAYMENTS_SHARED_SECRET',
                        'required' => true
                    ),
                    array(
                        'type' => 'password',
                        'label' => $this->l('Rebate Password '),
                        'name' => 'REALEXPAYMENTS_REBATE_PASSWORD',
                        'required' => true
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmitSec';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }



    public function getConfigFieldsValues()
    {
        $values = array();
        $values['REALEXPAYMENTS_PRODUCTION'] = $this->_config->getProdEnabled();
        $values['REALEXPAYMENTS_ENABLE_LOGS'] = $this->_config->getLogsEnabled();
        $values['REALEXPAYMENTS_MERCHANT_ID'] = $this->_config->getMerchantId();
        $values['REALEXPAYMENTS_SHARED_SECRET'] = $this->_config->getSharedSecret();
        $values['REALEXPAYMENTS_REBATE_PASSWORD'] = $this->_config->getRebatePassword();
        $values['REALEXPAYMENTS_SUBACCOUNT'] = $this->_config->getSubaccount();
        $values['REALEXPAYMENTS_URL_SANDBOX'] = $this->_config->getUrlSandbox();
        $values['REALEXPAYMENTS_URL_LIVE'] = $this->_config->getUrlLive();
        $values['REALEXPAYMENTS_PAYMENT_TEXT'] = $this->_config->getPaymentText();
        $values['REALEXPAYMENTS_HPP_LANG'] = $this->_config->getHPPLang();
        $values['REALEXPAYMENTS_HPP_LANG_ISO'] = $this->_config->getHPPLangIso();
        $values['REALEXPAYMENTS_IFRAME'] = $this->_config->getIframeEnabled();
        $values['REALEXPAYMENTS_IFRAME_TYPE'] = $this->_config->getIframeType();
        $values['REALEXPAYMENTS_AUTO_SETTLE'] = $this->_config->getAutoSettle();
        $values['REALEXPAYMENTS_CARD_STORAGE'] = $this->_config->getCardStorageEnabled();
        $values['REALEXPAYMENTS_OFFER_SAVE_CARD'] = $this->_config->getOfferSaveCard();
        if ($this->_isValidState($this->_config->getDelayedStatus())) {
            $values['REALEXPAYMENTS_PENDING_STATUS'] = $this->_config->getDelayedStatus();
        } else {
            $values['REALEXPAYMENTS_PENDING_STATUS'] = Configuration::get('PS_OS_PAYMENT');
        }
        $languages = Language::getLanguages(false);
        $fields = array();
        $translations = $this->_config->getPaymentText();
        foreach ($languages as $lang) {
            $fields['REALEXPAYMENTS_PAYMENT_TEXT'][$lang['id_lang']] = $translations[$lang['id_lang']];
        }
        $values['REALEXPAYMENTS_PAYMENT_TEXT'] = $fields['REALEXPAYMENTS_PAYMENT_TEXT'];
        return $values;
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmitPrin')) {
            Configuration::updateValue('REALEXPAYMENTS_PRODUCTION', Tools::getValue('REALEXPAYMENTS_PRODUCTION'));
            Configuration::updateValue('REALEXPAYMENTS_ENABLE_LOGS', Tools::getValue('REALEXPAYMENTS_ENABLE_LOGS'));
            Configuration::updateValue('REALEXPAYMENTS_MERCHANT_ID', Tools::getValue('REALEXPAYMENTS_MERCHANT_ID'));

            Configuration::updateValue('REALEXPAYMENTS_SUBACCOUNT', Tools::getValue('REALEXPAYMENTS_SUBACCOUNT'));
            Configuration::updateValue('REALEXPAYMENTS_URL_LIVE', Tools::getValue('REALEXPAYMENTS_URL_LIVE'));
            Configuration::updateValue('REALEXPAYMENTS_URL_SANDBOX', Tools::getValue('REALEXPAYMENTS_URL_SANDBOX'));
            Configuration::updateValue('REALEXPAYMENTS_HPP_LANG', Tools::getValue('REALEXPAYMENTS_HPP_LANG'));
            if ($this->_config->getHPPLang() =='customer') {
                Configuration::updateValue('REALEXPAYMENTS_HPP_LANG_ISO', '');
            } else {
                Configuration::updateValue('REALEXPAYMENTS_HPP_LANG_ISO', Tools::getValue('REALEXPAYMENTS_HPP_LANG_ISO'));
            }
            $languages = Language::getLanguages(false);
            $values = array();
            foreach ($languages as $lang) {
                $values['REALEXPAYMENTS_PAYMENT_TEXT'][$lang['id_lang']] = Tools::getValue('REALEXPAYMENTS_PAYMENT_TEXT_'.$lang['id_lang']);
            }
            Configuration::updateValue('REALEXPAYMENTS_PAYMENT_TEXT', $values['REALEXPAYMENTS_PAYMENT_TEXT']);
            Configuration::updateValue('REALEXPAYMENTS_AUTO_SETTLE', Tools::getValue('REALEXPAYMENTS_AUTO_SETTLE'));
            if (Configuration::get('PS_SSL_ENABLED') || !$this->_config->getProdEnabled()) {
                Configuration::updateValue('REALEXPAYMENTS_IFRAME', Tools::getValue('REALEXPAYMENTS_IFRAME'));
            } else {
                Configuration::updateValue('REALEXPAYMENTS_IFRAME', 0);
            }
            Configuration::updateValue('REALEXPAYMENTS_IFRAME_TYPE', Tools::getValue('REALEXPAYMENTS_IFRAME_TYPE'));
            Configuration::updateValue('REALEXPAYMENTS_CARD_STORAGE', Tools::getValue('REALEXPAYMENTS_CARD_STORAGE'));
            if (!$this->_config->getCardStorageEnabled()) {
                Configuration::updateValue('REALEXPAYMENTS_OFFER_SAVE_CARD', 0);
            } else {
                Configuration::updateValue('REALEXPAYMENTS_OFFER_SAVE_CARD', Tools::getValue('REALEXPAYMENTS_OFFER_SAVE_CARD'));
            }
            Configuration::updateValue('REALEXPAYMENTS_PENDING_STATUS', Tools::getValue('REALEXPAYMENTS_PENDING_STATUS'));
            $this->html .= $this->displayConfirmation($this->l('Main Settings updated'));
        }
        if (Tools::isSubmit('btnSubmitSec')) {
            require_once(dirname(__FILE__) ."/encode.php");
            $encoder = new CryptData();
            $secret = Tools::getValue('REALEXPAYMENTS_SHARED_SECRET') ;
            $rebate = Tools::getValue('REALEXPAYMENTS_REBATE_PASSWORD');
            Configuration::updateValue('REALEXPAYMENTS_SHARED_SECRET', $encoder->crypter($secret));
            Configuration::updateValue('REALEXPAYMENTS_REBATE_PASSWORD', $encoder->crypter($rebate));

            $this->html .= $this->displayConfirmation($this->l('Credentials Settings updated'));
        }
        if (Tools::isSubmit('btnClearRecords')) {
            $this->cleanFails();
            $this->html .= $this->displayConfirmation($this->l('Failures records cleared'));
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
}
