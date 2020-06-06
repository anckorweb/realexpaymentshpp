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

{if $realexOrder}
<div class="panel" id="manage_realexpayments">
  <div class="panel-heading">
    <i class="icon-money"></i>
    {l s='Realex Payments Transaction Management'  mod='realexpaymentshpp'}
  </div>

  <form id="formRealexPayments"  method="post" action="{$current_index|escape:'htmlall':'UTF-8'}&amp;vieworder&amp;id_order={$order->id|escape:'htmlall':'UTF-8'}&amp;token={$smarty.get.token|escape:'html':'UTF-8'}#formAddPaymentPanel">
    <div class="table-responsive">
      {if $transaction_statut =="KO"}
        <p class="alert alert-danger">{$transaction_return|escape:'htmlall':'UTF-8'}</p>
        {else if $transaction_statut =="OK"}
        <p class="alert alert-success">{$transaction_return|escape:'htmlall':'UTF-8'}</p>
      {/if}
      <table class="table">
        <thead>
          <tr>
            <th><span class="title_box ">{l s='Amount' mod='realexpaymentshpp'}</span></th>
            <th><span class="title_box ">{l s='Currency' mod='realexpaymentshpp'}</span></th>
            <th><span class="title_box ">{l s='Action' mod='realexpaymentshpp'}</span></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="text" name="realexpayments_amount" value="{$original_amount|escape:'htmlall':'UTF-8'}" class="form-control fixed-width-md pull-left" /> </td>
            <td>{$realexOrder['realexpayments_original_currency']|escape:'htmlall':'UTF-8'}</td>
            <td class="actions">
              <button class="btn btn-primary" type="submit" name="realexpayments_transaction" value="settle">
                {l s='Settle' mod='realexpaymentshpp'}
              </button>
              <button class="btn btn-primary" type="submit" name="realexpayments_transaction" value="void">
                {l s='Void' mod='realexpaymentshpp'}
              </button>
              <button class="btn btn-primary" type="submit" name="realexpayments_transaction" value="rebate">
                {l s='Rebate' mod='realexpaymentshpp'}
              </button>
            </td>
          </tr>
          <tr>
            <td colspan="3"><em>{l s='You can Settle or Rebate a transaction for any amount up to 115% or the original value.' mod='realexpaymentshpp'} (Maximum : <strong>{$max_authorized|escape:'htmlall':'UTF-8'} {$realexOrder['realexpayments_original_currency']|escape:'htmlall':'UTF-8'} </strong>)</em></td>
          </tr>
        </tbody>
      </table>
    </div>
  </form>
</div>
<div class="panel" id="history_realexpayments">
  <div class="panel-heading">
    <i class="icon-money"></i>
    {l s='Realex Payments Transaction Management History' mod='realexpaymentshpp'}
  </div>

  <table class="table">
    <thead>
      <tr>
        <th><span class="title_box ">{l s='Date' mod='realexpaymentshpp'}</span></th>
        <th><span class="title_box ">{l s='Action' mod='realexpaymentshpp'}</span></th>
        <th><span class="title_box ">{l s='Amount' mod='realexpaymentshpp'}</span></th>
        <th><span class="title_box ">{l s='Result' mod='realexpaymentshpp'}</span></th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$transaction_history item=row key=key}
      <tr class="{if $row['success']}success{else}danger{/if}">
        <td>{dateFormat date=$row['date_add'] full=true}</td>
        <td>{$row['action']|escape:'htmlall':'UTF-8'}</td>
        <td>{$row['amount']|escape:'htmlall':'UTF-8'} {$realexOrder['realexpayments_original_currency']|escape:'htmlall':'UTF-8'}</td>
        <td>{$row['result']|escape:'htmlall':'UTF-8'}</td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>
{/if}
