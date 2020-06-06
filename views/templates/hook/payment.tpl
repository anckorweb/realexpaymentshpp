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

<div class="row">
	<div class="col-xs-12">
		<p class="payment_module">
			{if $lightbox}
			<a class="realexpayments iframerealex" href="{$link->getModuleLink('realexpaymentshpp', 'payment')|escape:'html'}?content_only=1" title="" rel="nofollow">
				{$payment_text|escape:'htmlall':'UTF-8'}
				{if $warning_save_card}
					<br/>
					<small>{l s='Your card will be automatically stored for future payments' mod='realexpaymentshpp'}</small>
				{/if}
			</a>

			{else}
			<a class="realexpayments" href="{$link->getModuleLink('realexpaymentshpp', 'payment')|escape:'html'}" title="">
				{$payment_text|escape:'htmlall':'UTF-8'}
				{if $warning_save_card}
					<br/>
					<small>{l s='Your card will be automatically stored for future payments' mod='realexpaymentshpp'}</small>
				{/if}
			</a>
			{/if}
		</p>
	</div>
</div>
