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

class RealexPaymentsHppValidationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        echo "<p style='color:#777;font-weight: bold;font-size:13px;'>".$this->module->l('Please wait while you are redirected to the confirmation payment page ...', 'validation')."</p>";
        die();
    }

    /**
    * @see FrontController::postProcess()
    */
    public function postProcess()
    {
        $results = $this->module->getResults($_POST);
        $id_cart    = explode('-', $results['ORDER_ID']);
        $order_id = $results['ORDER_ID']; //toDo
        $cart   = new Cart($id_cart[0]);
        $currency = new Currency($cart->id_currency);
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            $this->module->getRedirection('step1');
            exit;
        }
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            $this->module->getRedirection('error');
            exit;
        }
        $this->context->currency->id = $cart->id_currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $hash_response = RealexPaymentsTools::getHashResponse($results['TIMESTAMP'], $results['MERCHANT_ID'], $results['ORDER_ID'], $results['RESULT'], $results['MESSAGE'], $results['PASREF'], $results['AUTHCODE'], $this->module->getConfig()->getSharedSecret());
        $statut_payment = Configuration::get('PS_OS_ERROR');
        $extra_vars = array();
        if ($this->module->getConfig()->getLogsEnabled()) {
            $this->module->writeLogs($results);
        }
        if ($hash_response === $results['SHA1HASH']) {
            switch ($results['RESULT']) {
                case '00':
                    $extra_vars['transaction_id'] = $results['ORDER_ID'];
                    $logsLight = $this->module->getLogs($results, 'light');
                    $orderStatePending = new OrderState(Configuration::get('REALEXPAYMENTS_PENDING_STATUS'));
                    if ($orderStatePending->id && !$this->module->getConfig()->getAutoSettle()) {
                        $statut_payment = Configuration::get('REALEXPAYMENTS_PENDING_STATUS');
                    } else {
                        $statut_payment = Configuration::get('PS_OS_PAYMENT');
                    }
                    $this->module->validateOrder($cart->id, $statut_payment, $total, $this->module->displayName, $logsLight, $extra_vars, (int)$cart->id_currency, false, $customer->secure_key);
                    $realexOrder = new RealexPaymentsOrders();
                    $realexOrder->addOrder(Order::getOrderByCartId($cart->id), $results['PASREF'], $results['AUTHCODE'], $results['ORDER_ID'], $cart->getOrderTotal(true, Cart::BOTH), $currency->iso_code);
                    if ($this->module->getConfig()->getCardStorageEnabled()) {
                        if ($results['REALWALLET_CHOSEN'] == 1 && (isset($results['PAYER_SETUP']) && $results['PAYER_SETUP'] == "00")) {
                            $payerref = new RealexPaymentsCards($this->module);
                            $payerref->addPayer($customer->id, $results['SAVED_PAYER_REF'], Order::getOrderByCartId($cart->id));
                        }
                    }
                    $this->module->getRedirection('confirmation', $cart, $customer);
                    break;
                default:
                    $this->module->saveFailure($results['RESULT'], $results['MESSAGE'], $cart->id, $order_id);
                    $this->module->getRedirection('error', null, null, $results['RESULT']);
                    break;
            }
        } else {
            $this->module->saveFailure($results['RESULT'], 'HASH ERROR', $cart->id, $order_id);
            $this->module->getRedirection('error', $cart, $customer, 'HASH');
        }
    }
}
