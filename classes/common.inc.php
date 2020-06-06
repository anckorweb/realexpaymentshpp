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
 * Base includes
 */
$dir = dirname(__FILE__).'/';
require_once($dir.'RealexPaymentsConfig.php');
require_once($dir.'RealexPaymentsTools.php');
require_once($dir.'RealexPaymentsResults.php');
require_once($dir.'RealexPaymentsRedirection.php');
require_once($dir.'RealexPaymentsCards.php');
require_once($dir.'RealexPaymentsOrders.php');
