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

<div class="panel" id="failures_realexpayments">
  <div class="panel-heading">
    <i class="icon-bug"></i>
    {l s='Realex Payments Declines Report' mod='realexpaymentshpp'}
  </div>  
  
  <div class="table-responsive">
		<table class="table">
			<thead>
				<tr>
					<th><span class="title_box ">{l s='Date' mod='realexpaymentshpp'}</span></th>
					<th><span class="title_box ">{l s='Result Code' mod='realexpaymentshpp'}</span></th>
					<th><span class="title_box ">{l s='Result Message' mod='realexpaymentshpp'}</span></th>
					<th><span class="title_box ">{l s='Cart ID' mod='realexpaymentshpp'}</span></th>
					<th><span class="title_box ">{l s='Order ID' mod='realexpaymentshpp'}</span></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$failures item=failure}
					<tr>
						<td>{dateFormat date=$failure['date_add'] full=true}</td>
						<td>{$failure['error_code']|escape:'htmlall':'UTF-8'}</td>
						<td>{$failure['error_message']|escape:'htmlall':'UTF-8'}</td>
						<td>{$failure['cart_id']|escape:'htmlall':'UTF-8'}</td>            
						<td>{$failure['order_id']|escape:'htmlall':'UTF-8'}</td>            
					</tr>
				{/foreach}
			</tbody>
		</table>
  </div>
  <br/><br/>
  <form method="post" action="">
    <button type="submit" value="1" name="btnClearRecords" class="btn btn-default pull-right">
      <i class="process-icon-save"></i> Clear records
    </button>
  </form>
  <div class="clearfix"></div>
</div>