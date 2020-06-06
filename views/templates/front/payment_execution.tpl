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

<div id="realexpaymentsblock">
  {if $redirect}
    {l s='Please wait ...' mod='realexpaymentshpp'}
    <form id="redirect_form" action="{$url_redirect|escape:'htmlall':'UTF-8'}" method="post">
    {foreach from=$fields key=field  item=value}
      <input type="hidden" name="{$field|escape:'htmlall':'UTF-8'}" value="{$value|escape:'htmlall':'UTF-8'}">
    {/foreach}
    </form>
    <script type="text/javascript">
        document.getElementById('redirect_form').submit();
    </script>
  {else}
    {if !$lightbox}
      {capture name=path}
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='realexpaymentshpp'}">{l s='Checkout' mod='realexpaymentshpp'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Payment' mod='realexpaymentshpp'}
      {/capture}
      <h1 class="page-heading">
          {l s='Order summary' mod='realexpaymentshpp'}
      </h1>
      {assign var='current_step' value='payment'}
      {include file="$tpl_dir./order-steps.tpl"}
    {/if}
    {if !$lightbox}
    <p style="font-weight: bold;">
    	{l s='The total amount of your order is' mod='realexpaymentshpp'}
    	<span id="amount" class="price">{displayPrice price=$amount}</span>
    	{if $use_taxes == 1}
        	{l s='(tax incl.)' mod='realexpaymentshpp'}
        {/if}
    </p>
        <p class="cart_navigation clearfix" id="cart_navigation">
      <a class="button-exclusive btn btn-default" href="{if !$lightbox}{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}{else}#{/if}" {if $lightbox} onClick="window.parent.jQuery.fancybox.close()" {/if}>
          <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='realexpaymentshpp'}
      </a>
    </p>
  {/if}
      {if $lightbox}
    <p class="cart_navigation clearfix" id="cart_navigation">
      <a class="button-exclusive btn btn-default" href="{if !$lightbox}{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}{else}#{/if}" {if $lightbox} onClick="window.parent.jQuery.fancybox.close()" {/if}>
          <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='realexpaymentshpp'}
      </a>
    </p>
    {/if}
    <iframe src="{$iframe_src|escape:'htmlall':'UTF-8'}" width="100%" id="iframerealex" scrolling="no">
      
    </iframe>

  {/if}
</div>
<script type="text/javascript">
  window.addEventListener('message', handleMessage, false);
  function handleMessage(event) {  
    var jsonvar = JSON.parse(event.data);
    //console.log("data");
    //console.log(jsonvar);
    var iframeH = jsonvar.iframe.height;
    var iframeW = jsonvar.iframe.width;
    document.getElementById("iframerealex").style.height = iframeH;
    document.getElementById("postPAResToMPIForm").css("overflow","scroll");
    var fancy = document.getElementsByClassName("fancybox-inner");
    //console.log(fancy);
    parent.jQuery.fancybox.update();
    setTimeout(function(){

    },2000)
    //fancy[0].style.height = iframeH;
    set
  }
</script>