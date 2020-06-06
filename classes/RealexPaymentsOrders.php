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
class RealexPaymentsOrders
{
    public function addOrder($id_order, $pasref, $authcode, $realexorderid, $amount, $currency)
    {
// $target = Tools::getValue('id');
// $name = Tools::getValue('name');
        Db::getInstance()->insert('realexpayments_orders', array(
            'realexpayments_ps_order_id' => (int)$id_order,
            'realexpayments_pasref'      => pSQL($pasref),
            'realexpayments_authcode'      => pSQL($authcode),
            'realexpayments_order_id'      => pSQL($realexorderid),
            'realexpayments_original_amount'  => (float)$amount,
            'realexpayments_original_currency'      => pSQL($currency),
        ));
    }

    public function getOrder($id_order)
    {
        $value = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'realexpayments_orders` WHERE realexpayments_ps_order_id='.(int)$id_order);
        return $value;
    }
}
