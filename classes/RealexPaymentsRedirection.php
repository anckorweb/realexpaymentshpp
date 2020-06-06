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

class RealexPaymentsRedirection
{
    private $_module;

    public function __construct($module)
    {
        $this->_module = $module;
    }
    public function getRedirect($action, $cart = null, $customer = null, $error = null)
    {
        $controller_order = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
        $url = (Configuration::get('PS_SSL_ENABLED'))?Tools::getShopDomainSsl(true, true).__PS_BASE_URI__."/":Tools::getShopDomain(true, true).__PS_BASE_URI__."/";
        switch ($action) {
            case 'error':
                $url.= 'index.php?controller='.$controller_order.'&step=3&realexpayments_error='.$error;
                break;
            case 'step1':
                $url.= 'index.php?controller='.$controller_order.'&step=1';
                break;
            case 'confirmation':
                $url .= 'index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->_module->id.'&id_order='.$this->_module->currentOrder.'&key='.$customer->secure_key;
                break;
            default:
                $url.= 'index.php?controller='.$controller_order.'&step=1';
                break;
        }
        echo "<script>window.top.location.href = '".$url."'</script>";
    }
}
