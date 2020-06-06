{*
* 2017 Coccinet
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* DISCLAIMER
*
*  @author Coccinet
*  @copyright  2017 Coccinet
*}

{if $status == 'ok'}
<p class="success_message"><strong>{l s='Thank you.' mod='realexpaymentshpp'}<br><br>{l s='Your payment has been successful and the order will now be processed.' mod='realexpaymentshpp'}</strong></p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order.' mod='realexpaymentshpp'}
	</p>
	<p class="warning">
		{l s='If you think this is an error, feel free to contact our' mod='realexpaymentshpp'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='realexpaymentshpp'}</a>.
	</p>
{/if}
